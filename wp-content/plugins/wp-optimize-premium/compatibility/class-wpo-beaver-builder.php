<?php
if (!defined('ABSPATH')) {
	die('No direct access allowed');
}

if (!class_exists('WPO_Beaver_Builder')) :

/**
 * Adds compatibility for Beaver Builder plugin
 */
class WPO_Beaver_Builder {

	/**
	 * Constructor
	 */
	private function __construct() {
		if (!class_exists('FLBuilderLoader')) return;
		add_filter('wpo_get_posts_content_images_from_plugins', array($this, 'get_posts_content_images'), 10, 2);
	}

	/**
	 * Returns singleton instance
	 *
	 * @return WPO_Beaver_Builder
	 */
	public static function instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Appends images array with images found in beaver builder content
	 *
	 * @param array $images
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_posts_content_images($images, $post_id) {
		$beaver_builder_data = get_post_meta($post_id, '_fl_builder_data', true);
		return array_merge($images, $this->get_beaver_builder_images($beaver_builder_data));
	}

	/**
	 * Get image IDs of images used in beaver builder
	 *
	 * @param array|object $data
	 *
	 * @return array An array of image ids used in beaver builder
	 */
	private function get_beaver_builder_images($data) {
		$images = array();

		if (is_array($data) || is_object($data)) {
			foreach ($data as $key => $value) {
				if (is_array($value) && preg_match('/^carousel_photos$|^ss_photos$/', $key)) {
					$images = array_merge($images, $value);
				} elseif (is_array($value) || is_object($value)) {
					$images = array_merge($images, $this->get_beaver_builder_images($value));
				} elseif (is_string($key) && preg_match('/bg_image$/', $key)) {
					if (!empty($value)) {
						$images[] = $value;
					}
				}
			}
		}

		return $images;
	}
}

endif;
