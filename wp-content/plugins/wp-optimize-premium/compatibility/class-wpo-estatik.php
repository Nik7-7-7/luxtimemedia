<?php
if (!defined('ABSPATH')) {
	die('No direct access allowed');
}

if (!class_exists('WPO_Estatik')) :

/**
 * Adds compatibility for Estatik plugin
 */
class WPO_Estatik {

	/**
	 * Constructor
	 */
	private function __construct() {
		if (!class_exists('Estatik')) return;
		add_filter('wpo_get_plugin_images_from_meta', array($this, 'get_posts_content_images'));
	}

	/**
	 * Returns singleton instance
	 *
	 * @return WPO_Estatik
	 */
	public static function instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Appends images array with images found in estatik content
	 *
	 * @param array $images An array of attachment IDs
	 *
	 * @return array
	 */
	public function get_posts_content_images($images) {
		global $wpdb;
		$es_property_gallery = $wpdb->get_results($query = $wpdb->prepare("SELECT post_id FROM {$wpdb->base_prefix}postmeta WHERE meta_key = %s AND meta_value IN (%s, %s)", 'es_attachment_type', 'gallery', 'floor_plans'), ARRAY_A);
		$es_property_gallery = array_column($es_property_gallery, 'post_id');
		return array_merge($images, $es_property_gallery);
	}
}

endif;
