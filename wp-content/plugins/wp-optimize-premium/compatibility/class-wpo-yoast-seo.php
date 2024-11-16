<?php
if (!defined('ABSPATH')) {
	die('No direct access allowed');
}

if (!class_exists('WPO_Yoast_SEO')) :

/**
 * Adds compatibility for Yoast SEO plugin
 */
class WPO_Yoast_SEO {

	/**
	 * Constructor
	 */
	private function __construct() {
		if (!defined('WPSEO_VERSION')) return;
		add_filter('wpo_get_posts_content_images_from_plugins', array($this, 'get_posts_content_images'), 10, 2);
	}

	/**
	 * Returns singleton instance
	 *
	 * @return WPO_Yoast_SEO
	 */
	public static function instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Appends images array with images found in yoast seo plugin
	 *
	 * @param array $images
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_posts_content_images($images, $post_id) {
		$yoast_seo_facebook_images = get_post_meta($post_id, '_yoast_wpseo_opengraph-image-id', false);
		$yoast_seo_twitter_images = get_post_meta($post_id, '_yoast_wpseo_twitter-image-id', false);
		return array_merge($images, $yoast_seo_facebook_images, $yoast_seo_twitter_images);
	}
}

endif;
