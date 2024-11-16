<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WPO_Cache_Preloader_Premium')) :

class WPO_Cache_Preloader_Premium {
	
	/**
	 * Constructor
	 */
	private function __construct() {
		add_filter('post_row_actions', array($this, 'add_preload_this_cache_link'), 10, 2);
		add_filter('page_row_actions', array($this, 'add_preload_this_cache_link'), 10, 2);
	}
	
	/**
	 * Returns singleton instance
	 *
	 * @return WPO_Cache_Preloader_Premium
	 */
	public static function get_instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}
	
	/**
	 * Adds preload cache link to row actions
	 *
	 * @param string[] $actions An array of row action links
	 * @param WP_Post  $post    The post object
	 *
	 * @return string[]
	 */
	public function add_preload_this_cache_link($actions, $post) {
		if (!WP_Optimize()->get_page_cache()->is_enabled() || !current_user_can(WP_Optimize()->capability_required())) return $actions;
		if ('publish' !== $post->post_status) return $actions;
		
		$post_id = $post->ID;
		$url = get_permalink($post_id);
		$path = WPO_Page_Cache::get_full_path_from_url($url);
		
		if (WPO_Page_Cache::is_cache_empty($path)) {
			$text = __('Preload cache', 'wp-optimize');
			$actions['wpo_preload_cache'] = $this->get_preload_link($post_id, $text);
		}
		
		return $actions;
	}
	
	/**
	 * Preload post/page of given ID
	 *
	 * @param int $post_id
	 * @return bool
	 */
	public function single_page_cache_preload($post_id) {
		$url = get_permalink($post_id);
		if (!defined('WPO_CACHE_FILES_DIR') || empty($url)) return false;
		$preloader = WP_Optimize_Page_Cache_Preloader::instance();
		$preloader->preload_desktop($url);
		$preloader->preload_mobile($url);
		$preloader->preload_amp($url);
		$path = WPO_Page_Cache::get_full_path_from_url($url);
		return !WPO_Page_Cache::is_cache_empty($path);
	}
	
	/**
	 * Gets preload link html
	 *
	 * @param int    $post_id Post ID
	 * @param string $text    Anchor text of the link
	 *
	 * @return string
	 */
	private function get_preload_link($post_id, $text) {
		return sprintf('<a data-post_id="%1$s" href="#">%2$s</a>', esc_attr($post_id), esc_html($text));
	}
}

endif;
