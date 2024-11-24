<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('ALThirdPartyCache')) :
	class ALThirdPartyCache {
		function startThirdPartyHostCaching() {
			$cache_fname = dirname( __FILE__ ) . "/cache.php";

			if (file_exists($cache_fname)) {
				require_once $cache_fname;
				$socache = new ALCache();
				$socache->parseAirliftParamsHeader();
				if (isset($socache->airlift_print_buffer) && !empty($socache->airlift_print_buffer)) {
					ob_start([$socache, 'serveBuffer']);
				}
				else {
					ob_start([$socache, 'optimizePage']);
				}
			}
		}
	}
endif;