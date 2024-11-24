<?php

if (!class_exists('ALValidator')) :
	class ALValidator {
		public $cache_config;
		public $ignore_files;
		public $ignore_extensions;
		public $allowed_methods;
		public $allowed_query_params;
		public $cache_ssl;
		public $ignored_request_uri_regex;
		public $ignored_cookies;
		public $ignored_user_agents;
		public $do_not_cache;
		public $ignored_query_params;
		public $ignore_all_query_params;

		public function __construct($config) {
			$cache_config = isset($config['cache_params']) && is_array($config['cache_params']) ? $config['cache_params'] : null;
			if (!isset($cache_config)) {
				$this->$do_not_cache = true;
			} else {
				$this->ignore_files = isset($cache_config['ignore_files']) ? $cache_config['ignore_files'] : array();
				$this->ignore_extensions = isset($cache_config['ignore_extensions']) ? $cache_config['ignore_extensions'] : array();
				$this->allowed_methods = isset($cache_config['allowed_methods']) ? $cache_config['allowed_methods'] : array();
				$this->allowed_query_params = isset($cache_config['allowed_query_params']) ? $cache_config['allowed_query_params'] : array();
				$this->cache_ssl = isset($cache_config['cache_ssl']) ? $cache_config['cache_ssl'] : array();
				$this->ignored_request_uri_regex = isset($cache_config['ignored_request_uri_regex']) ? $cache_config['ignored_request_uri_regex'] : array();
				$this->ignored_cookies = isset($cache_config['ignored_cookies']) ? $cache_config['ignored_cookies'] : array();
				$this->ignored_user_agents = isset($cache_config['ignored_user_agents']) ? $cache_config['ignored_user_agents'] : array();
				$this->ignored_query_params = isset($cache_config['ignored_query_params']) ? $cache_config['ignored_query_params'] : array();
				$this->ignore_all_query_params = isset($cache_config['ignore_all_query_params']) ? $cache_config['ignore_all_query_params'] : false;
			}
		}

		public function isIgnoredFile() {
			$request_uri = ALCacheHelper::getRequestUriBase();
			foreach ($this->ignore_files as $file) {
				if (strpos($request_uri, '/' . $file)) {
					return true;
				}
			}
			return false;
		}

		public function isIgnoredExtension() {
			$request_uri = ALCacheHelper::getRequestUriBase();
			if (strtolower($request_uri) === '/index.php') {
				return false;
			}
			$extension = pathinfo($request_uri, PATHINFO_EXTENSION);
			return $extension && in_array($extension, $this->ignore_extensions);
		}

		public function isIgnoredRequestMethod() {
			if (in_array($_SERVER['REQUEST_METHOD'], $this->allowed_methods)) {
				return false;
			}
			return true;
		}

		public function isIgnoredQueryString() {
			$params = ALCacheHelper::getQueryParams();
			if (!$params) {
				return false;
			}
			if (!!$this->ignore_all_query_params) {
				return true;
			}
			if (array_intersect_key($params, array_flip($this->ignored_query_params))) {
				return true;
			}
			if (array_intersect_key($params, array_flip($this->allowed_query_params))) {
				return false;
			}
			return false;
		}

		public function canCacheSSL() {
			if (function_exists('is_ssl')) {
				return !is_ssl() || $this->cache_ssl;
			}
			return true;
		}

		public function isIgnoredRequestURI() {
			$request_uri = ALCacheHelper::getRequestURIBase();
			foreach ($this->ignored_request_uri_regex as $regex) {
				if (ALHelper::safePregMatch($regex, $request_uri)) {
					return true;
				}
			}
			return false;
		}

		public function hasIgnoredCookies() {
			if (!is_array($_COOKIE)) {
				return true;
			}
			foreach (array_keys($_COOKIE) as $cookie_name) {
				foreach ($this->ignored_cookies as $ignored_cookie) {
					if (ALHelper::safePregMatch($ignored_cookie, $cookie_name)) {
						return true;
					}
				}
			}
			return false;
		}

		public function hasIgnoredUserAgents() {
			if (!isset($_SERVER['HTTP_USER_AGENT'])) {
				return true;
			}
			foreach ($this->ignored_user_agents as $ignored_ua) {
				if(ALHelper::safePregMatch($ignored_ua, $_SERVER['HTTP_USER_AGENT'])) {
					return true;
				}
			}
			return false;
		}

		public function hasDonotCachepage() {
			if (defined('AL_DONOTCACHEPAGE') && AL_DONOTCACHEPAGE) {
				return true;
			}
			return false;
		}

		public function checkIfSearchQuery() {
			global $wp_query;
			if (!isset($wp_query)) {
				return false;
			}
			return $wp_query->is_search();
		}

		public function canCacheBuffer($buffer) {
			if (strlen($buffer) <= 255 || http_response_code() !== 200 || $this->hasDonotCachePage() || $this->checkIfSearchQuery()) {
				return false;
			}
			return true;
		}

		public function canCachePage() {
			if ($this->do_not_cache || $this->isIgnoredFile() || $this->isIgnoredExtension() || $this->isIgnoredRequestMethod() ||
					is_admin() || $this->isIgnoredQueryString() || !$this->canCacheSSL() ||
					$this->isIgnoredRequestURI() || $this->hasIgnoredCookies() || $this->hasIgnoredUserAgents()) {
				return false;
			}
			return true;
		}
	}
endif;