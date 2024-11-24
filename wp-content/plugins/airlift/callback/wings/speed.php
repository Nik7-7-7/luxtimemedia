<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('BVSpeedCallback')) :
require_once dirname( __FILE__ ) . '/../../wp_cache.php';
class BVSpeedCallback extends BVCallbackBase {

	const SPEED_WING_VERSION = 1.6;
	public $settings;
	public $bvinfo;
	public $db;

	public function __construct($callback_handler) {
		$this->settings = $callback_handler->settings;
		$this->bvinfo = new ALInfo($callback_handler->settings);
		$this->db = $callback_handler->db;
	}

	public function get_third_party_urls_to_exclude($params) {
		$post_ids_to_reject = array();
		$request_urls_to_reject = array();

		if (empty($params)) {
			return $request_urls_to_reject;
		}

		if (class_exists('WooCommerce') && function_exists('wc_get_page_id') && isset($params["woocommerce_keys_to_ignore"])) {
			$keys_to_check = $params["woocommerce_keys_to_ignore"];
			foreach ($keys_to_check as $key) {
				array_push($post_ids_to_reject, wc_get_page_id($key));
			}
		}

		if (function_exists('bigcommerce_init') && isset($params["big_woocommerce_keys_to_ignore"])) {
			$keys_to_check = $params["big_woocommerce_keys_to_ignore"];
			foreach ($keys_to_check as $key) {
				array_push($post_ids_to_reject, $this->settings->getOption($key));
			}
		}

		foreach ($post_ids_to_reject as $post_id) {
			if (!$post_id || $post_id <= 0 || (int) $this->settings->getOption('page_on_front') === $post_id ||
				'publish' !== get_post_status($post_id)) {
				continue;
			}
			array_push($request_urls_to_reject, wp_parse_url(get_permalink($post_id), PHP_URL_PATH));
		}

		return $request_urls_to_reject;
	}

	public function arrayContainsValue($params, $key, $value) {
			return isset($params[$key]) && is_array($params[$key]) && in_array($value, $params[$key]);
	}

	public function clearVarnishCache($params) {
		$request_args = isset($params['request_args']) ? $params['request_args'] : null;
		$purge_url = isset($params['purge_url']) ? $params['purge_url'] : false;
		if ($request_args && $purge_url) {
			$remote_info = wp_remote_request($purge_url, $request_args);
			return $remote_info;
		}
		return "Unable to clear varnish cache due to invalid params";
	}

	public function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (is_dir($dir . "/" . $object) && !is_link($dir . "/" . $object)) {
						$this->rrmdir($dir . "/" . $object);
					} else {
						unlink($dir . "/" . $object);
					}
				}
			}
			return rmdir($dir);
		} else {
			return false;
		}
	}

	public function updateBVTableContent($table, $value, $filter) {
		$this->db->query("UPDATE $table SET $value $filter;");
	}

	public function insertBVTableContent($table, $fields, $value) {
		$this->db->query("INSERT INTO $table $fields values $value;");
	}

	public function insertConfigRows($table, $fields, $values) {
		if (is_array($values)) {
			foreach ($values as $value) {
				$value = base64_decode($value);
				$this->insertBVTableContent($table, $fields, $value);
			}
		}
	}

	public function updateConfigRows($table, $value, $filters) {
		if (is_array($filters)) {
			foreach ($filters as $filter) {
				$filter = base64_decode($filter);
				$this->updateBVTableContent($table, $value, $filter);
			}
		}
	}

	public function deleteConfigRows($table, $rmfilters) {
		if (is_array($rmfilters)) {
			foreach ($rmfilters as $rmfilter) {
				$rmfilter = base64_decode($rmfilter);
				$this->db->deleteBVTableContent($table, $rmfilter);
			}
		}
	}

	public function clearKinstaCacheUrl($url) {
		$url = trailingslashit($url) . 'kinsta-clear-cache/';
		return wp_remote_get($url, ['blocking' => false, 'timeout'  => 0.01]);
	}

	public function clearHostCacheUrl($purge_params, $url) {
		# XNOTE: need to handle for other hosts url as well
		if ($url) {
			$host_resp = array();
			switch ($purge_params['purge_host']) {
			case 'kinsta':
				$host_resp = $this->clearKinstaCacheUrl($url);
				break;
			default:
				$host_resp = $this->clearCompleteHostCache($purge_params);
			}
			return $host_resp;
		}
		return false;
	}

	public function clearCacheUrl($purge_url) {
		if (isset($purge_url)) {
			$parsed_url = parse_url($purge_url);
			$query = $parsed_url["query"];
			if (!empty($query)) {
				$query = "#" . $query;
			}
			if (!empty($parsed_url["path"])){
				return $this->rrmdir(ALCacheHelper::getCacheBasePath() . $parsed_url["host"] . $parsed_url["path"] . $query);
			}
		}
		return false;
	}

	public function clearPostsSpecificCache($purge_params) {
		$post_resp = array();
		if (isset($purge_params['clear_cached_post_ids']) && is_array($purge_params['clear_cached_post_ids'])) {
			$post_resp['post_ids_cache_purged'] = array();
			foreach ($purge_params['clear_cached_post_ids'] as $id) {
				$post_resp['post_ids_cache_purged'][$id] = array();
				$post_resp['post_ids_cache_purged'][$id][$id] = $this->clearCacheUrl(get_permalink($id));
				if (isset($purge_params['purge_host'])) {
					$post_resp['post_ids_cache_purged'][$id]['host_resp'] = $this->clearHostCacheUrl($purge_params, get_permalink($id));
				}
			}
		}
		if (isset($purge_params['clear_cached_urls']) && is_array($purge_params['clear_cached_urls'])) {
			$post_resp['urls_cache_purged'] = array();
			foreach ($purge_params['clear_cached_urls'] as $url) {
				$post_resp['urls_cache_purged'][$url] = array();
				$post_resp['post_ids_cache_purged'][$url][$url] = $this->clearCacheUrl($url);
				if (isset($purge_params['purge_host'])) {
					$post_resp['urls_cache_purged'][$url]['host_resp'] = $this->clearHostCacheUrl($purge_params, $url);
				}
			}
		}
		return $post_resp;
	}

	public function clear_batcache_cache() {
		global $batcache;
		$resp = false;
		if ( isset( $batcache ) && is_object( $batcache ) && method_exists( $batcache, 'flush' ) ) {
			if ( isset($_SERVER['HTTP_HOST']) ) {
				$resp = $batcache->flush( 'host', $_SERVER['HTTP_HOST'] );
			}
			if ( isset($batcache->key) && isset($batcache->group) ) {
				if ( $resp ) {
					$resp = $batcache->flush( $batcache->key, $batcache->group );
				}
			}
		}
		return $resp;
	}

	public function clear_siteground_dynamic_cache($hostname, $main_path, $url, $args, $request) {
		$host_resp = array();
		$site_tools_sock_file = '/chroot/tmp/site-tools.sock';

		if (!file_exists($site_tools_sock_file) || empty($args)) {
			$host_resp['cache_purge_status'] = false;
			$host_resp['site_tools_sock_file_found'] = false;
			return $host_resp;
		}
		$fp = stream_socket_client('unix://' . $site_tools_sock_file, $errno, $errstr, 5);
		if ( false === $fp ) {
			$host_resp['cache_purge_status'] = false;
			$host_resp['unix_socket_connection_success'] = false;
			return $host_resp;
		}

		$host_resp['unix_socket_connection_success'] = true;

		fwrite($fp, json_encode($request, JSON_FORCE_OBJECT) . "\n");

		$response = fgets($fp, 32 * 1024);

		fclose($fp);

		$result = @json_decode($response, true);

		if (false === $result || isset($result['err_code'])) {
			$host_resp['cache_purge_fail_resp'] = $result;
		}

		$host_resp['cache_purge_status'] = $result;

		return $host_resp;
	}

	public function clearCompleteHostCache($purge_params) {
		$host_resp = array();
		switch ($purge_params['purge_host']) {
		case 'cloudways':
		case 'flywheel':
		case 'dreamhost':
		case 'liquidweb':
			if (isset($purge_params['varnish_params'])) {
				$host_resp['varnish_cache'] = $this->clearVarnishCache($purge_params['varnish_params']);
			}
			break;
		case 'pagely':
			if (class_exists( 'PagelyCachePurge')) {
				$purger = new PagelyCachePurge();
				$purger->purgeAll();
				$host_resp['pagely_cache'] = true;
			} else {
				$host_resp['pagely_cache'] = false;
			}
			break;
		case 'kinsta':
			global $kinsta_cache;

			if (!empty($kinsta_cache->kinsta_cache_purge)) {
				$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
				$host_resp['kinsta_cache'] = true;
			} else {
				$host_resp['kinsta_cache'] = false;
			}
			break;
		case 'savvii':
			do_action('warpdrive_domain_flush');
			$host_resp['savvii_cache'] = true;
			break;
		case 'wpengine':
			if (method_exists('WpeCommon', 'purge_varnish_cache')) {
				WpeCommon::purge_varnish_cache();
				$host_resp['wpengine_cache'] = true;
			} else {
				$host_resp['wpengine_cache'] = false;
			}
			break;
		case 'siteground':
			if (isset($purge_params['dynamic_cache_params'])) {
				$dyn_cache_params = $purge_params['dynamic_cache_params'];
				$hostname = $dyn_cache_params['hostname'];
				$main_path = $dyn_cache_params['main_path'];
				$url = $dyn_cache_params['url'];
				$args = $dyn_cache_params['args'];
				$request = $dyn_cache_params['request'];
				$host_resp['siteground_cache'] = $this->clear_siteground_dynamic_cache($hostname, $main_path, $url, $args, $request);
			}
			break;
		case 'pressable':
			$host_resp['pressable'] = $this->clear_batcache_cache();
			break;
		case 'automattic':
			$host_resp['automattic'] = $this->clear_batcache_cache();
			break;
		}
		return $host_resp;
	}

	function process($request) {
		$params = $request->params;
		switch ($request->method) {
		case 'updtoptn':
			$option = 'alcacheconfig';
			$this->settings->updateOption($option, $params['config']);
			$resp = array("status" => $this->settings->getOption($option));
			break;
		case 'insrtcnf':
			$pdata = $params["pdata"];
			$pdata["config"] = $params["pdata"]["config"];
			$pdata["similar_post_ids"] = $params["pdata"]["similar_post_ids"];
			$table = $this->db->getBVTable("airlift_config");
			$columns = " (url, similar_post_ids, category, category_value, config, time)";
			$values = " VALUES ( '" . $pdata["url"] . "', '" . $pdata["similar_post_ids"] . "', " . $pdata["category"] . ", '" . $pdata["category_value"] . "', '" . addslashes($pdata["config"]) . "', " . time() . " )";
			$query = "INSERT INTO " . $table . $columns . $values;
			$resp = array("status" => $this->db->query($query));
			break;
		case 'remcnf':
			$pdata = $params["pdata"];
			$table = $this->db->getBVTable("airlift_config");
			if (is_array($pdata["key_value"])) {
				$escaped_key_values = array_map('addslashes', $pdata["key_value"]);
				$key_value_in_clause = "'" . implode("','", $escaped_key_values) . "'";
				$query = "DELETE FROM " . $table . " WHERE " . $pdata["key_name"] . " IN (" . $key_value_in_clause . ")";
				$resp = array("status" => $this->db->query($query));
			}
			break;
		case 'prge_cach':
			$resp = array();
			if (isset($params['purge_cache_params'])) {
				$purge_params = $params['purge_cache_params'];
			} else {
				$resp['invalid_params'] = true;
				break;
			}

			if (isset($purge_params['clear_cached_post_ids']) || isset($purge_params['clear_cached_urls'])) {
				$post_resp = $this->clearPostsSpecificCache($purge_params);
				if (!empty($post_resp)) {
					$resp['post_resp'] = $post_resp;
				}
			}

			if (isset($purge_params['purge_all']) && isset($purge_params['purge_host'])) {
				$host_resp = $this->clearCompleteHostCache($purge_params);
				if (!empty($host_resp)) {
					$resp['host_resp'] = $host_resp;
				}
			}
			
			if ($this->arrayContainsValue($purge_params, 'options', 'purge_object')) {
				if (function_exists('wp_cache_flush')) {
					$resp['object_cache_purged'] = wp_cache_flush();
				} else {
					$resp['object_cache_purged'] = false;
				}
			}

			if ($this->arrayContainsValue($purge_params, 'options', 'purge_complete_cache_storage')) {
				$resp['purge_complete_cache_storage'] = $this->rrmdir(ALWPCache::$cache_path);
			}

			if ($this->arrayContainsValue($purge_params, 'options', 'purge_alcache')) {
				$resp['purge_alcache'] = $this->rrmdir(ALWPCache::$so_cache_path);
			}
			break;
		case "actvt_cach":
			$resp = array("status" => ALWPCache::enableCache($this->bvinfo));
			break;
		case "deactvt_cach":
			$resp = array("status" => ALWPCache::disableCache($this->bvinfo));
			break;
		case "gt_othr_excld_attrs":
			$resp = array("urls_to_exclude" => $this->get_third_party_urls_to_exclude($params));
			break;
		case "dsbl_et_cache":
			if (isset($params["et_metaname"]) && isset($params["et_option_name"]) &&
					isset($params["et_option_value"])) {
				$et_metaname = $params["et_metaname"];
				$et_option_name = $params["et_option_name"];
				$et_option_value = $params["et_option_value"];
				$et_settings = $this->settings->getOption($et_metaname);
				if (isset($et_settings) && is_array($et_settings)) {
					$et_settings[$et_option_name] = $et_option_value;
					$resp = array("status" => $this->settings->updateOption($et_metaname, $et_settings));
				}
			}
			break;
		}
		return $resp;
	}
}
endif;