<?php
if (!defined('ABSPATH')) exit;
if (!class_exists('ALWPCache')) :
class ALWPCache {
	public static $so_cache_path =  WP_CONTENT_DIR . '/cache/airlift';
	public static $cache_path =  WP_CONTENT_DIR . '/cache';

	private static function getConfigFilePath() {
		if (is_writable(ABSPATH . 'wp-config.php')) {
			return ABSPATH . 'wp-config.php';
		}
		if (file_exists(dirname(ABSPATH) . '/wp-config.php') && !file_exists(dirname(ABSPATH) . '/wp-settings.php')) {
			return dirname(ABSPATH) . '/wp-config.php';
		}
		return false;
	}

	public static function enableCache($info) {
		$resp = array();
		$resp['wp_config_updated'] = self::updateWpConfig('true', $info);
		$resp['advanced_cache_updated'] = self::copyAdvancedCacheFile();
		return $resp;
	}

	public static function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object)) {
						self::rrmdir($dir . "/" . $object);
					} else {
						unlink($dir . "/" . $object);
					}
				}
			}
			return rmdir($dir);
		} else {
			return true;
		}
	}

	public static function disableCache($info) {
		$resp = array();
		self::rrmdir(self::$so_cache_path);
		$resp['wp_config_updated'] = self::updateWpConfig('false', $info);
		$resp['advanced_cache_updated'] = self::removeAdvancedCacheFileContent();
		return $resp;
	}

	public static function updateWpConfig($value, $info) {
		$config_file_path = self::getConfigFilePath();

		if (!$config_file_path) {
			return false;
		}

		$content = file_get_contents($config_file_path);

		$is_cache_present = ALHelper::safePregMatch('/^\s*define\(\s*\'WP_CACHE\'\s*,\s*(?<value>[^\s\)]*)\s*\)/m', $content, $current_value);
		if (isset($current_value['value']) && !empty($current_value['value']) && $value === $current_value['value']) {
			return true;
		}
		$constant = "define('WP_CACHE', {$value} ); // Added by {$info->brandname}";
		if (!$is_cache_present) {
			$config_content = preg_replace("/(<\?php)/i", "<?php\r\n{$constant}\r\n", $content);
		} elseif ( isset($current_value['value']) && !empty($current_value['value']) && $current_value['value'] !== $value) {
			$config_content = preg_replace("/^\s*define\(\s*\'WP_CACHE\'\s*,\s*([^\s\)]*)\s*\).+/m", $constant, $content);
		}
		if (file_put_contents($config_file_path, $config_content) !== false) {
			return true;
		}
		return false;
	}

	public static function copyAdvancedCacheFile() {
		$contents = file_get_contents(WP_CONTENT_DIR . '/plugins/airlift/buffer/advanced-cache.php');
		if (file_put_contents(WP_CONTENT_DIR . "/advanced-cache.php", $contents) !== false) {
			return true;
		}
		return false;
	}

	public static function removeAdvancedCacheFileContent() {
		if (!file_exists(WP_CONTENT_DIR . "/advanced-cache.php")) {
			return true;
		}
		if (file_put_contents(WP_CONTENT_DIR . "/advanced-cache.php", "") !== false) {
			return true;
		}
		return false;
	}
}

endif;