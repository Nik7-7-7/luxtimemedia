<?php

if (!defined('ABSPATH')) die('No direct access allowed');

/**
 * All cache commands that are intended to be available for calling from any sort of control interface (e.g. wp-admin, UpdraftCentral) go in here. All public methods should either return the data to be returned, or a WP_Error with associated error code, message and error data.
 */
class WP_Optimize_Cache_Commands_Premium extends WP_Optimize_Cache_Commands {

	/**
	 * Command to disable caching/lazy-load for the selected post
	 *
	 * @param {array} $params ['post_id' => (int), 'meta_key' => '_wpo_disable_caching | _wpo_disable_lazyload', 'disable' => (bool)]
	 * @return array
	 */
	public function change_post_disable_option($params) {

		$accepted_keys = array('_wpo_disable_caching', '_wpo_disable_lazyload');

		$meta_key = isset($params['meta_key']) ? $params['meta_key'] : '_wpo_disable_caching';

		if (!in_array($meta_key, $accepted_keys)) {
			return array(
				'result' => false,
				'messages' => array(),
				'errors' => array(
					'Not accepted meta_key value',
				)
			);
		}

		if (!isset($params['post_id'])) {
			return array(
				'result' => false,
				'messages' => array(),
				'errors' => array(
					'No post was indicated.',
				)
			);
		}

		$post_id = $params['post_id'];
		$disable = isset($params['disable']) && ('false' != $params['disable']);

		if ($disable) {
			update_post_meta($post_id, $meta_key, $disable);
		} else {
			delete_post_meta($post_id, $meta_key);
		}

		$disable_caching = get_post_meta($post_id, $meta_key, true);

		if ($disable_caching) {
			WPO_Page_Cache::delete_single_post_cache($post_id);
		}

		return array(
			'result' => true,
			'disabled' => (bool) $disable_caching,
		);
	}

	/**
	 * Get list of posts. Used in select2 autocomplete.
	 *
	 * @param array $params ['page' => (int), 'search' => (string) ]
	 * @return array
	 */
	public function get_posts_list($params) {

		$page_size = 5;
		$page = isset($params['page']) ? (int) $params['page'] : 1;
		$search = isset($params['search']) ? $params['search'] : '';
		
		$args = array(
			'post_type' => get_post_types(array('public' => true)),
			'order' => 'ASC',
			'orderby' => 'title',
			'posts_per_page' => $page_size,
			'offset' => $page_size * ($page - 1),
		);

		if ('' != $search) {
			$args['s'] = $search;
		}

		$results = array();

		$loop = new WP_Query($args);
		
		while ($loop->have_posts()) {
			$loop->the_post();

			$post_type_label = '';
			$post_type = get_post_type();
			if ($post_type) {
				$post_type_obj = get_post_type_object($post_type);
				if ($post_type_obj) {
					$post_type_label = $post_type_obj->labels->singular_name;
				}
			}

			$results[] = array('id' => get_the_ID(), 'text' => '['.$post_type_label.'] '.get_the_title());
		}

		$response =	array(
			'results' => $results,
		);

		if ($page * $page_size < $loop->found_posts) {
			$response['pagination'] = array(
				'more' => true,
			);
		}
		
		return $response;
	}

	/**
	 * Update always purge post settings (from post edit page).
	 *
	 * @param array $params ['post_id' => (int), 'always_purge_post_type' => (array), 'always_purge_post_id' => (array)]
	 * @return array
	 */
	public function always_purge_post_update($params) {
		if (!isset($params['post_id'])) {
			return array(
				'result' => false,
				'messages' => array(),
				'errors' => array(
					'No post was indicated.',
				)
			);
		}

		$config = WP_Optimize()->get_page_cache()->config->get();

		$post_id = $params['post_id'];
		$post_type = $params['post_type'];

		$always_purge_post_type = isset($config['always_purge_post_type']) ? $config['always_purge_post_type'] : array();
		$always_purge_post_id = isset($config['always_purge_post_id']) ? $config['always_purge_post_id'] : array();

		$updated = false;
		foreach ($always_purge_post_id as $i => $p_id) {
			if ($post_id != $p_id) continue;

			// when not empty post type and item was not updated before then update it
			// otherwise delete it from settings array
			if (!$updated && !empty($post_type)) {
				$updated = true;
				$always_purge_post_type[$i] = $post_type;
			} else {
				unset($always_purge_post_type[$i]);
				unset($always_purge_post_id[$i]);
			}
		}

		// if settings item was not found in the current settings then add it.
		if (!$updated && !empty($post_type)) {
			$always_purge_post_type[] = $post_type;
			$always_purge_post_id[] = $post_id;
		}

		$config['always_purge_post_type'] = array_values($always_purge_post_type);
		$config['always_purge_post_id'] = array_values($always_purge_post_id);

		return array(
			'result' => WP_Optimize()->get_page_cache()->config->update($config),
		);
	}
	
	/**
	 * Preload single page/post cache
	 *
	 * @param array $data Data from the request
	 * @return array
	 */
	public function single_page_cache_preload($data) {
		$success = WPO_Cache_Preloader_Premium::get_instance()->single_page_cache_preload(absint($data['post_id']));
		$unsuccessful_message = __('The attempt to preload the cache was unsuccessful.', 'wp-optimize');
		$unsuccessful_message .= ' ';
		$unsuccessful_message .= __('Please try again.', 'wp-optimize');
		$unsuccessful_message .= ' ';
		$unsuccessful_message .= __('If the issue persists, it is because this page is not meant to be cached and you can ignore this.', 'wp-optimize');
		$message = $success ? __('The page cache was successfully preloaded', 'wp-optimize') : $unsuccessful_message;
		return array(
			'success' => $success,
			'message' => $message,
		);
	}
	
	/**
	 * Purge single page/post cache
	 *
	 * @param array $data Data from the request
	 * @return array
	 */
	public function single_page_cache_purge($data) {
		$success = WPO_Cache_Purge_Premium::get_instance()->single_page_cache_purge(absint($data['post_id']));
		if (!is_bool($success)) {
			return $success;
		}
		$unsuccessful_message = __('The attempt to purge the cache was unsuccessful.', 'wp-optimize');
		$unsuccessful_message .= ' ';
		$unsuccessful_message .= __('Please try again.', 'wp-optimize');
		$unsuccessful_message .= ' ';
		$unsuccessful_message .= __('If the issue persists, verify that the uploads (media) folder for your WordPress installation has file permissions set to allow the website (i.e. PHP) to write to it.', 'wp-optimize');
		$message = $success ? __('The page cache was successfully purged', 'wp-optimize') : $unsuccessful_message;
		return array(
			'success' => $success,
			'message' => $message,
		);
	}
}
