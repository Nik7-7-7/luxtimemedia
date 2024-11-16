<?php

if (!defined('ABSPATH')) die('Access denied.');

if (!class_exists('WPO_ACF')) :

class WPO_ACF {

	/**
	 * ACF field type
	 *
	 * @var string
	 */
	private $acf_field_type;

	/**
	 * Constructor
	 */
	private function __construct() {
		if (!class_exists('ACF')) return;

		add_filter('wpo_get_posts_content_images_from_plugins', array($this, 'get_posts_content_images'), 10, 3);

		// Used in get_single_image_ids_in_post_meta() (images.php)
		add_filter('wpo_find_used_images_in_post_meta', array($this, 'filter_acf_image_field_names'));
		// Used in get_multiple_image_ids_in_post_meta() (images.php)
		add_filter('wpo_get_multiple_image_ids_in_post_meta', array($this, 'filter_get_acf_gallery_field_names'));
	}

	/**
	 * Returns singleton instance
	 *
	 * @return WPO_ACF
	 */
	public static function instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}

	/**
	 * Appends images array with images found in ACF content
	 *
	 * @param array $images
	 * @param int $post_id
	 * @param string $post_content
	 *
	 * @return array
	 */
	public function get_posts_content_images($images, $post_id, $post_content) {
		$unused_images_in_post = $this->get_image_ids_from_acf_blocks($post_content);
		return array_merge($images, $unused_images_in_post);
	}

	/**
	 * Adds the names of ACF images fields to the provided array.
	 *
	 * @param array $names
	 *
	 * @return array
	 */
	public function filter_acf_image_field_names($names) {
		if (!is_array($names)) $names = array();
		return array_merge($names, $this->get_acf_image_field_names());
	}

	/**
	 * Adds the names of ACF gallery fields to the provided array.
	 *
	 * @param array $names
	 *
	 * @return array
	 */
	public function filter_get_acf_gallery_field_names($names) {
		if (!is_array($names)) $names = array();
		return array_merge($names, $this->get_acf_gallery_field_names());
	}

	/**
	 * Get list of all ACF field names with images/galleries/repeaters
	 *
	 * @return array
	 */
	private function get_acf_all_field_names() {
		static $field_names;

		if (!is_array($field_names)) {
			$field_names = array_merge($this->get_acf_image_field_names(), $this->get_acf_gallery_field_names());

			foreach ($field_names as $key => $field_name) {
				// Remove names with the '_' prefix if a corresponding name without the prefix exists in the list.
				// These fields do not contain useful information for us.
				if ('_' == $field_name[0] && array_search(substr($field_name, 1), $field_names)) unset($field_names[$key]);
			}

			$field_names = array_values($field_names);
		}

		return $field_names;
	}

	/**
	 * Get the ACF image fields.
	 * We need this function as ACF's `acf_get_raw_fields` isn't capable of
	 * handling nested `image` fields in `repeater` fields
	 *
	 * @return array An array of name of image fields
	 */
	private function get_acf_image_field_names() {
		if (!function_exists('acf_get_raw_fields')) return array();

		global $wpdb;

		static $acf_image_field_names = null;

		if (is_array($acf_image_field_names)) return $acf_image_field_names;

		$acf_fields = acf_get_raw_fields('');
		$acf_field_names = array();
		$nestable_fields = array_filter($acf_fields, function($field) {
			return 'repeater' == $field['type'] || 'group' == $field['type'];
		});
	
		if (count($nestable_fields)) {

			// get nestable fields such as repeater, group
			$nestable_fields_by_id = array();
			foreach ($nestable_fields as $field) {
				$nestable_fields_by_id[$field['ID']] = $field;
			}

			$where = array();

			foreach ($acf_fields as $field) {
				if ('image' != $field['type']) continue;

				$parent = $field['parent'];
				// if current field has nestable field parent the we push the field name to where statement
				if (array_key_exists($parent, $nestable_fields_by_id)) {
					$where[] = '(meta_key LIKE "'.esc_sql($wpdb->esc_like($nestable_fields_by_id[$parent]['name'])).'%'.esc_sql($wpdb->esc_like($field['name'])).'")';
				}
			}

			if (!empty($where)) {
				$sql = "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE ".join(" OR ", $where);
				$acf_field_names_postmeta = $wpdb->get_col($sql);
				$acf_field_names = empty($acf_field_names_postmeta) ? $acf_field_names : array_merge($acf_field_names, $acf_field_names_postmeta);
			}
		}

		$acf_image_field_names = array_merge($acf_field_names, $this->get_acf_field_names());
		return $acf_image_field_names;
	}

	/**
	 * Get the acf meta field names
	 *
	 * @param string $field_type
	 * @return array
	 */
	private function get_acf_field_names($field_type = 'image') {
		if (!function_exists('acf_get_raw_fields')) return array();
		$this->acf_field_type = $field_type;
		static $acf_image_fields = array();
		// Get all ACF fields
		if (empty($acf_image_fields)) $acf_image_fields = acf_get_raw_fields($field_type);
		if (!is_array($acf_image_fields)) return array();
		// Pluck the meta names and types
		return array_keys(array_filter(wp_list_pluck($acf_image_fields, 'type', 'name'), array($this, 'filter_acf_fields_per_type')));
	}

	/**
	 * Filters the ACF fields array
	 * Called in get_acf_field_names by array_filter
	 *
	 * @param string $type
	 * @return boolean
	 */
	private function filter_acf_fields_per_type($type) {
		return $type == $this->acf_field_type;
	}

	/**
	 * Get the ACF gallery fields.
	 * We need this function as ACF's `acf_get_raw_fields` isn't capable of
	 * handling nested `gallery` fields in `repeater` fields
	 *
	 * @return array An array of name of gallery fields
	 */
	private function get_acf_gallery_field_names() {
		if (!function_exists('acf_get_raw_fields')) return array();

		global $wpdb;
		
		static $acf_gallery_field_names = null;

		if (is_array($acf_gallery_field_names)) return $acf_gallery_field_names;
				
		$acf_fields = acf_get_raw_fields('');
		$nestable_fields = array_filter($acf_fields, function($field) {
			return 'repeater' == $field['type'] || 'group' == $field['type'];
		});
	
		$gallery_fields = array();
		foreach ($acf_fields as $field) {
			if ('gallery' == $field['type']) {
				$gallery_fields[] = $field['name'];
			}
		}
		if (count($nestable_fields) && count($gallery_fields)) {
			// Do the nested stuff
			$where = array();
			foreach ($gallery_fields as $gallery_field) {
				$gallery_field = esc_sql($wpdb->esc_like($gallery_field));
				$where[] = "(`meta_key` LIKE '%{$gallery_field}%')";
			}
			global $wpdb;
			$sql = "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE ".join(' OR ', $where);
			$acf_gallery_field_names = $wpdb->get_col($sql);
		} else {
			$acf_gallery_field_names = $this->get_acf_field_names('gallery');
		}

		return $acf_gallery_field_names;
	}

	/**
	 * Get image ids from acf blocks
	 *
	 * @param string $post_content
	 * @return array $acf_image_ids
	 */
	private function get_image_ids_from_acf_blocks($post_content) {
		$acf_image_ids = array();
		$acf_blocks = $this->get_acf_blocks_from_post_content($post_content);
		$acf_block_field_names = $this->get_acf_all_field_names();

		foreach ($acf_blocks as $acf_block) {
			$acf_block_data = $acf_block['attrs']['data'];
			foreach ($acf_block_data as $key => $value) {
				if (array_search($key, $acf_block_field_names) && !empty($value)) {
					// for galleries there is an array
					if (is_array($value)) {
						$acf_image_ids = array_merge($acf_image_ids, $value);
					} else {
						$acf_image_ids[] = $value;
					}
				}
			}
		}

		return $acf_image_ids;
	}

	/**
	 * Get list of ACF blocks in post content
	 *
	 * @param string $post_content
	 * @return array
	 */
	private function get_acf_blocks_from_post_content($post_content) {
		// Only available from WP 5.0
		if (!function_exists('parse_blocks')) return array();

		$blocks = parse_blocks($post_content);
		return array_filter($blocks, function($block) {
			return empty($block['blockName']) ? false : substr($block['blockName'], 0, 4) === 'acf/';
		});
	}
}
endif;
