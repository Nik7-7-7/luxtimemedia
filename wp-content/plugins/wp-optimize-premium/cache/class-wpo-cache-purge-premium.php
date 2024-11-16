<?php

if (!defined('ABSPATH')) die('No direct access allowed');

if (!class_exists('WPO_Cache_Purge_Premium')) :

class WPO_Cache_Purge_Premium {
	
	/**
	 * Constructor
	 */
	private function __construct() {
		add_filter('post_row_actions', array($this, 'add_purge_this_cache_link'), 10, 2);
		add_filter('page_row_actions', array($this, 'add_purge_this_cache_link'), 10, 2);
	}
	
	/**
	 * Returns singleton instance
	 *
	 * @return WPO_Cache_Purge_Premium
	 */
	public static function get_instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}
	
	/**
	 * Adds purge cache link to row actions
	 *
	 * @param string[] $actions An array of row action links
	 * @param WP_Post  $post    The post object
	 *
	 * @return string[]
	 */
	public function add_purge_this_cache_link($actions, $post) {
		if (!WP_Optimize()->get_page_cache()->can_purge_cache()) return $actions;
		if ('publish' !== $post->post_status) return $actions;
		
		$post_id = $post->ID;
		$url = get_permalink($post_id);
		$path = WPO_Page_Cache::get_full_path_from_url($url);
		
		if (!WPO_Page_Cache::is_cache_empty($path)) {
			$text = __('Purge cache', 'wp-optimize');
			$actions['wpo_purge_cache'] = $this->get_purge_link($post_id, $text);
		}
		
		return $actions;
	}

	/**
	 * Purge post/page of given ID
	 *
	 * @param int $post_id
	 * @return array | bool
	 */
	public function single_page_cache_purge($post_id) {
		if (!WP_Optimize()->get_page_cache()->can_purge_cache()) {
			return array(
				'success' => false,
				'message' => __("You don't have permission to purge the cache.", 'wp-optimize'),
			);
		}
		return WPO_Page_Cache::delete_single_post_cache($post_id);
	}
	
	/**
	 * Gets purge link html
	 *
	 * @param string $post_id Post ID
	 * @param string $text    Anchor text of the link
	 *
	 * @return string
	 */
	private function get_purge_link($post_id, $text) {
		return sprintf('<a data-post_id="%1$s" href="#">%2$s</a>', esc_attr($post_id), esc_html($text));
	}
}

endif;
