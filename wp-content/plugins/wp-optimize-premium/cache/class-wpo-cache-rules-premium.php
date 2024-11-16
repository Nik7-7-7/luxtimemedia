<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * Page caching rules and exceptions
 */
if (!class_exists('WPO_Cache_Rules_Premium')) :

class WPO_Cache_Rules_Premium {

	/**
	 * Cache config object
	 *
	 * @var array
	 */
	private $config;

	private function __construct() {
		$this->config = WPO_Cache_Config::instance()->get();
		add_action('wp_after_insert_post', array($this, 'maybe_setup_cron_job_to_purge_product'), 10, 2);
		add_action('wpo_purge_product', array($this, 'purge_product_on_sale'), 10, 1);
		add_filter('wpo_purge_cache_hooks', array($this, 'add_purge_cache_actions'));
	}

	public static function get_instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * May be setup a cron job to purge product that is on scheduled sale
	 *
	 * @param int $post_id
	 * @param object $post
	 *
	 * @return void
	 */
	public function maybe_setup_cron_job_to_purge_product($post_id, $post) {
		if (!class_exists('WooCommerce')) return;

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || 'publish' !== $post->post_status) return;

		$product = wc_get_product($post);
		if (!is_a($product, 'WC_Product')) return;

		if ('' === $product->get_sale_price()) {
			if (wp_next_scheduled('wpo_purge_product', array($post_id) )) {
				wp_clear_scheduled_hook('wpo_purge_product', array($post_id));
			}
		}

		try {
			wp_clear_scheduled_hook('wpo_purge_product', array($post_id));
			// Set up a cron job to purge cache on sale from timestamp
			$product_date_on_sale_from = $product->get_date_on_sale_from();
			$product_date_on_sale_to = $product->get_date_on_sale_to();
			if (null !== $product_date_on_sale_from) {
				$sale_start_date = new DateTime($product_date_on_sale_from);
				wp_schedule_single_event( $sale_start_date->getTimestamp(), 'wpo_purge_product', array($post_id));
			}
			if (null !== $product_date_on_sale_to) {
				$sale_end_date = new DateTime($product_date_on_sale_to);
				wp_schedule_single_event( $sale_end_date->getTimestamp(), 'wpo_purge_product', array($post_id));
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}

	/**
	 * Purge the product on sale
	 *
	 * @param int $post_id
	 *
	 * @return void
	 */
	public function purge_product_on_sale($post_id) {
		if (empty($this->config['enable_page_caching'])) return;
		if (!class_exists('WooCommerce')) return;

		$product = wc_get_product($post_id);
		if (!is_a($product, 'WC_Product')) return;
		WPO_Page_Cache::delete_single_post_cache($post_id);
	}

	/**
	 * Add actions to the wpo_purge_cache_hooks filter
	 *
	 * @param array $actions Array of actions to be purged
	 *
	 * @return array
	 */
	public function add_purge_cache_actions($actions) {
		$actions[] = 'getwooplugins_settings_saved';
		return $actions;
	}
}
endif;
