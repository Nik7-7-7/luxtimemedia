<?php

if (!defined('ABSPATH')) exit;
require_once dirname(__FILE__) . '/optimizer.php';
require_once dirname(__FILE__) . '/validator.php';
require_once dirname(__FILE__) . '/helper.php';
require_once dirname(__FILE__) . './../wp_settings.php';

if (!class_exists('ALCache')) :

	class ALCache {
		public $optimizer;
		public $validator;
		public $cache_filepath;
		public $cache_filepath_gzip;
		public $alsettings;
		public $airlift_print_buffer;
		public $airlift_fname;
		public $alinfo;
		public static $cacheconfig = "alcacheconfig";
		public static $airlift_optimization_option = "apply_airlift_optimizations";

		public function __construct() {
			$this->cache_filepath = $this->getCachePath();
			$this->cache_filepath_gzip = $this->cache_filepath . '_gzip';
			$this->alsettings = new ALWPSettings();
		}

		public function resetLowerCase($matches) {
			return strtolower($matches[0]);
		}

		public function getCachePath() {
			$request_uri_path = ALCacheHelper::getRequestCachePath();
			$filename = 'index';
			if (function_exists('is_ssl') && is_ssl()) {
				$filename .= '-https';
			}
			$request_uri_path = preg_replace_callback('/%[0-9A-F]{2}/', array($this, 'resetLowerCase'), $request_uri_path);
			$request_uri_path = str_replace('?', '#', $request_uri_path);
			$request_uri_path .= '/' . $filename . '.html';
			return $request_uri_path;
		}

		public function getIfModifiedSince() {
			if (function_exists('apache_request_headers')) {
				$headers = apache_request_headers();
				return isset($headers['If-Modified-Since']) ? $headers['If-Modified-Since'] : '';
			}
			return isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : '';
		}

		public function serveCacheFile($read_from_gzip) {
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($this->cache_filepath)) . ' GMT');
			$if_modified_since = $this->getIfModifiedSince();
			if ($if_modified_since && (strtotime($if_modified_since) === @filemtime($this->cache_filepath))) {
				header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
				header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				header('Cache-Control: no-cache, must-revalidate');
				exit;
			}
			$read_from_gzip ? readgzfile($this->cache_filepath_gzip) : readfile($this->cache_filepath);
			exit;
		}

		public function sanitizeBuffer($buffer, $is_gzip_buffer) {
			$sanitized_buffer = $buffer;
			if ($is_gzip_buffer) {
				$sanitized_buffer = gzdecode($sanitized_buffer);
			}
			return $sanitized_buffer;
		}

		public function parseAirliftParamsHeader() {
			if (isset($_SERVER['HTTP_AIRLIFT_PARAMS_HEADER'])) {
				$airlift_params_header = $_SERVER['HTTP_AIRLIFT_PARAMS_HEADER'];
				$parsed_airlift_headers = json_decode($airlift_params_header, true);
				if ($parsed_airlift_headers !== null && is_array($parsed_airlift_headers)) {
					if (isset($parsed_airlift_headers['airlift_print_buffer'])) {
						$this->airlift_print_buffer = $parsed_airlift_headers['airlift_print_buffer'];
					}
					if (isset($parsed_airlift_headers['airlift_fname'])) {
						$this->airlift_fname = $parsed_airlift_headers['airlift_fname'];
					}
				}
			} else {
				if (isset($_GET['bv_print_buffer']) && !empty($_GET['bv_print_buffer'])){
					$this->airlift_print_buffer = $_GET['bv_print_buffer'];
				}
				if (isset($_GET['fname']) && !empty($_GET['fname'])) {
					$this->airlift_fname = $_GET['fname'];
				}
			}
		}

		public function startCaching() {
			if (isset($_GET['al_debug_mode']) && !empty($_GET['al_debug_mode'])) {
				ob_start([$this, 'optimizePage']);
				return;
			}

			$this->parseAirliftParamsHeader();
			if (isset($this->airlift_print_buffer) && !empty($this->airlift_print_buffer)) {
				ob_start([$this, 'serveBuffer']);
				return;
			}
			if ($this->canPerformPageCaching()) {
				$accept_encoding = null;
				if (array_key_exists('HTTP_ACCEPT_ENCODING', $_SERVER)) {
					$accept_encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
				}
				$read_from_gzip = $accept_encoding && false !== strpos($accept_encoding, 'gzip');
				if ($read_from_gzip && is_readable($this->cache_filepath_gzip)) {
					$this->serveCacheFile($read_from_gzip);
				}
				if (is_readable($this->cache_filepath)) {
					$this->serveCacheFile($read_from_gzip);
				}
				ob_start([$this, 'optimizePageAndSaveCache']);
			} else {
				ob_start([$this, 'optimizePage']);
			}
		}

		public function isBufferInGzipFormat($buffer) {
			$magic_number = substr($buffer, 0, 2);
			return ($magic_number === "\x1f\x8b") ? true : false;
		}

		public function serveBuffer($buffer) {
			$original_buffer = $buffer;
			$is_gzip_buffer = $this->isBufferInGzipFormat($buffer);
			if ($is_gzip_buffer) {
				$buffer = $this->sanitizeBuffer($buffer, $is_gzip_buffer);
				if ($buffer === false) {
					return $original_buffer;
				}
			}
			if (isset($this->airlift_fname) && !empty($this->airlift_fname)) {
				$fname = md5($this->airlift_fname);
				$fullpath = ALCacheHelper::getCacheBasePath() . 'buffer/';
				if ((file_exists($fullpath) && is_writable($fullpath)) || mkdir($fullpath, 0755, true)) {
					file_put_contents($fullpath . $fname, $buffer);
				}
			}
			if ($is_gzip_buffer) {
				$buffer = gzencode($buffer, 6);
			}
			return $buffer;
		}

		public function writeCacheFile($content) {
			file_put_contents($this->cache_filepath, $content);
			$writtenContent = file_get_contents($this->cache_filepath);
			if ($writtenContent === false || !is_string($writtenContent) ||  strlen($writtenContent) !== strlen($content)) {
				unlink($this->cache_filepath);
			}

			if (function_exists('gzencode')) {
				$gzippedContent = gzencode($content, 6);
				file_put_contents($this->cache_filepath_gzip, $gzippedContent);
				$writtenGzippedContent = file_get_contents($this->cache_filepath_gzip);
				if ($writtenGzippedContent === false || !is_string($writtenGzippedContent) || strlen($writtenGzippedContent) !== strlen($gzippedContent)) {
					unlink($this->cache_filepath_gzip);
				}
			}
		}

		public function canPerformPageCaching() {
			if (array_key_exists('al_cache_skip_cookies', $GLOBALS) && is_array($GLOBALS['al_cache_skip_cookies']) && is_array($_COOKIE)) {
				$cookie_keys = array_keys($_COOKIE);
				foreach ($cookie_keys as $cookie) {
					if (in_array($cookie, $GLOBALS['al_cache_skip_cookies'], true)) {
						return false;
					}
				}
			}

			return true;
		}

		public function can_apply_optimization() {
			if (isset($_GET['al_debug_mode']) && !empty($_GET['al_debug_mode'])) {
				return true;
			}

			if($this->alsettings->getOption(self::$airlift_optimization_option) === "true") {
				return true;
			}
			
			return false;
		}

		public function optimizePage($buffer) {
			$apply_airlift_optimization = $this->can_apply_optimization();
			if(!$apply_airlift_optimization) {
				return $buffer;
			}

			$original_buffer = $buffer;
			$is_gzip_buffer = $this->isBufferInGzipFormat($buffer);
			if ($is_gzip_buffer) {
				$buffer = $this->sanitizeBuffer($buffer, $is_gzip_buffer);
				if ($buffer === false) {
					return $original_buffer;
				}
			}
			$config = $this->alsettings->getOption(self::$cacheconfig);

			if ($config == false) {
				return $buffer;
			}

			$this->validator = new ALValidator($config);
			$this->alinfo = new ALInfo($this->alsettings);
			$this->optimizer = new ALOptimizer($config, $this->alinfo);
			if (!$this->validator->canCacheBuffer($buffer) || !$this->validator->canCachePage()) {
				return $buffer;
			}

			$buffer = $this->optimizer->optimizeBuffer($buffer);
			$optimized_buffer_copy = $buffer;
			if ($is_gzip_buffer) {
				$buffer = gzencode($buffer, 6);
				if ($buffer === false || !is_string($buffer) || strlen($buffer) == 0) {
					$buffer = $optimized_buffer_copy;
					$buffer = $buffer . '<!-- BUFFER_IS_NOT_GZIP_ENCODED -->';
					header_remove('Content-Encoding');
				}
			}
			return $buffer;
		}

		public function optimizePageAndSaveCache($buffer) {
			$apply_airlift_optimization = $this->can_apply_optimization();
			if(!$apply_airlift_optimization) {
				return $buffer;
			}

			$original_buffer = $buffer;
			$is_gzip_buffer = $this->isBufferInGzipFormat($buffer);
			if ($is_gzip_buffer) {
				$buffer = $this->sanitizeBuffer($buffer, $is_gzip_buffer);
				if ($buffer === false) {
					return $original_buffer;
				}
			}
			$config = $this->alsettings->getOption(self::$cacheconfig);
			if ($config == false) {
				return $buffer;
			}

			$this->validator = new ALValidator($config);
			$this->alinfo = new ALInfo($this->alsettings);
			$this->optimizer = new ALOptimizer($config, $this->alinfo);
			if (!$this->validator->canCacheBuffer($buffer) || !$this->validator->canCachePage()) {
				return $buffer;
			}

			$buffer = $this->optimizer->optimizeBuffer($buffer);
			$optimized_buffer_copy = $buffer;
			$cache_dir_path = dirname($this->cache_filepath);
			if ((file_exists($cache_dir_path) && is_writable($cache_dir_path)) || mkdir($cache_dir_path, 0755, true)) {
				$this->writeCacheFile($buffer);
			}
			if ($is_gzip_buffer) {
				$buffer = gzencode($buffer, 6);
				if ($buffer === false || !is_string($buffer) || strlen($buffer) == 0) {
					$buffer = $optimized_buffer_copy;
					$buffer = $buffer . '<!-- BUFFER_IS_NOT_GZIP_ENCODED -->';
					header_remove('Content-Encoding');
				}
			}
			return $buffer;
		}
	}
endif;