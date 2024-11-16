<?php
if (!defined('ABSPATH')) {
	die('No direct access allowed');
}

/**
 * Adds compatibility for WooCommerce Multilingual & Multicurrency plugin.
 */
class WPO_WCML_Compatibility {

	/**
	 * Instance of this class
	 *
	 * @var WPO_WCML_Compatibility|null
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_filter('wcml_user_store_strategy', array($this, 'wcml_user_store_strategy'));
		add_filter('wpo_cache_cookies', array($this, 'add_cookies_to_wpo_config'), 10, 2);
		add_action('update_option__wcml_settings', array($this, 'wcml_options_updated'), 10, 2);
		add_action('deactivate_woocommerce-multilingual/wpml-woocommerce.php', array($this, 'wcml_deactivated'));
	}

	/**
	 * Returns singleton instance.
	 *
	 * @return WPO_WCML_Compatibility
	 */
	public static function instance() {
		if (null === self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Sets the currency information store strategy for the WCML plugin to 'cookie' for users.
	 *
	 * This method updates the currency information store strategy to 'cookie' for users, as opposed
	 * to the default strategy of 'session'.
	 *
	 * @param string $strategy The current currency store strategy.
	 *
	 * @return string The updated currency store strategy.
	 */
	public function wcml_user_store_strategy($strategy) {
		if ($this->is_multi_currency_enabled() && WP_Optimize()->get_page_cache()->is_enabled()) {
			return 'cookie';
		}

		return $strategy;
	}

	/**
	 * Adds the WCML currency cookie name to the WPO configuration array, which allows for the creation
	 * of separate cache files for each currency.
	 *
	 * @param array $cookies The current list of cookies in the WPO configuration.
	 * @param array $config  The current WPO configuration.
	 *
	 * @return array The updated list of cookies in the WPO configuration.
	 */
	public function add_cookies_to_wpo_config($cookies, $config) {
		if ($this->is_wcml_active() && $this->is_multi_currency_enabled() && !empty($config['enable_page_caching'])) {
			$cookies[] = 'wcml_client_currency';
		}
		return $cookies;
	}

	/**
	 * Updates WPO configuration when WCML's multi-currency mode settings are updated.
	 *
	 * @param mixed  $old_data Old option data.
	 * @param mixed  $data     New option data.
	 */
	public function wcml_options_updated($old_data, $data) {
		$old_value = isset($old_data['enable_multi_currency']) ? $old_data['enable_multi_currency'] : null;
		$new_value = isset($data['enable_multi_currency']) ? $data['enable_multi_currency'] : null;

		if ($old_value !== $new_value) {
			WP_Optimize()->get_page_cache()->update_cache_config();
		}
	}

	/**
	 * Removes filters and updates WPO configuration when WCML plugin is deactivated.
	 */
	public function wcml_deactivated() {
		remove_filter('wpo_cache_cookies', array($this, 'add_cookies_to_wpo_config'), 10, 2);
		WP_Optimize()->get_page_cache()->update_cache_config();
	}

	/**
	 * Checks if WCML plugin is active.
	 *
	 * @return bool
	 */
	private function is_wcml_active() {
		return defined('WCML_VERSION');
	}

	/**
	 * Checks if multi-currency support is enabled in the WCML plugin settings.
	 *
	 * @return bool
	 */
	private function is_multi_currency_enabled() {
		$wcml_settings = get_option('_wcml_settings', array());
		return isset($wcml_settings['enable_multi_currency']) && $wcml_settings['enable_multi_currency'];
	}
}
