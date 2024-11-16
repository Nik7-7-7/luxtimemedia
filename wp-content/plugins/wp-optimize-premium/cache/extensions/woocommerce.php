<?php

if (!defined('ABSPATH')) die('No direct access allowed');

add_filter('wpo_cache_page_force', 'wpo_cache_aelia_currency_support');

/**
 * Returns true if we need to cache the current POST request from Aelia Currency.
 *
 * @param bool $status
 * @return bool
 */
function wpo_cache_aelia_currency_support($status) {
	// When the Aelia Currency caching option is enabled, we check $_POST for the posted currency,
	// and if a value is posted, we put it into the $_COOKIE array to be handled later by
	// the page cache filename generator function.
	// We don't need to handle any other values because they are already in the $_COOKIE.
	if (!empty($GLOBALS['wpo_cache_config']['enable_cache_aelia_currency']) && $GLOBALS['wpo_cache_config']['enable_cache_aelia_currency']) {
		if (isset($_POST['aelia_cs_currency'])) {
			$_COOKIE['aelia_cs_selected_currency'] = filter_var($_POST['aelia_cs_currency'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			// True means that we cache the current POST request.
			return true;
		}
	}
	return $status;
}
