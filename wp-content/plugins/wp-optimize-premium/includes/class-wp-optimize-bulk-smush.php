<?php
if (!defined('ABSPATH')) die('Access denied.');

if (!class_exists('WP_Optimize_Bulk_Smush')) :

class WP_Optimize_Bulk_Smush {
	
	/**
	 * Stores an array of attachment IDs that are compressed by ewww image optimize
	 *
	 * @var array $ewww_io_compressed_images
	 */
	private $ewww_io_compressed_images = array();
	
	/**
	 * Constructor
	 */
	private function __construct() {
		$this->ewww_io_compressed_images = $this->get_ewww_io_compressed_images();
		// Add custom bulk action to media library
		add_filter('bulk_actions-upload', array($this, 'add_image_compression_bulk_action'));
		add_action('admin_footer-upload.php', array($this, 'add_smush_popup_template'));

		// Add new media filter to media library
		add_action('restrict_manage_posts', array($this, 'add_media_filter_dropdown'));
		add_action('parse_query', array($this, 'filter_media_library_query'));
		add_filter('posts_results', array($this, 'filter_media_library_results'), 10, 2);
	}
	
	/**
	 * Returns singleton instance
	 *
	 * @return WP_Optimize_Bulk_Smush
	 */
	public static function get_instance() {
		static $_instance = null;
		if (null === $_instance) {
			$_instance = new self();
		}
		return $_instance;
	}
	
	/**
	 * Adds `Compress/Restore` options to bulk action dropdown
	 *
	 * @param string[] $bulk_actions An array of bulk actions
	 *
	 * @return string[]
	 */
	public function add_image_compression_bulk_action($bulk_actions) {
		if (!current_user_can(WP_Optimize()->capability_required())) return $bulk_actions;
		// Escaping here because core doesn't do that
		// https://github.com/WordPress/wordpress-develop/blob/6.4.1/src/wp-admin/includes/class-wp-list-table.php#L616
		$bulk_actions['wp_optimize_bulk_compression'] = esc_html__('Compress', 'wp-optimize');
		$bulk_actions['wp_optimize_bulk_restore'] = esc_html__('Restore original', 'wp-optimize');
		return $bulk_actions;
	}
	
	/**
	 * Adds compress popup template
	 */
	public function add_smush_popup_template() {
		if (current_user_can(WP_Optimize()->capability_required())) {
			WP_Optimize()->include_template('images/smush-popup.php');
		}
	}
	
	/**
	 * Adds a new filter to media library to filter compressed/uncompressed images
	 */
	public function add_media_filter_dropdown() {
		$screen = get_current_screen();
		
		if (null === $screen || 'upload' !== $screen->id) return;
		if (!current_user_can(WP_Optimize()->capability_required())) return;
		$status = isset($_REQUEST['wpo_image_optimization_status']) ? sanitize_text_field(wp_unslash($_REQUEST['wpo_image_optimization_status'])) : 0; // phpcs:ignore WordPress.Security.NonceVerification -- retaining status
		$dropdown_options = array(
			'0' => __('All Media Files', 'wp-optimize'),
			'compressed' => __('Compressed', 'wp-optimize'),
			'uncompressed' => __('Uncompressed', 'wp-optimize'),
		);
		WP_Optimize()->include_template("images/upload.php", false, array('status' => $status, 'dropdown_options' => $dropdown_options));
	}
	
	/**
	 * Filters media library items based on compressed/uncompressed status
	 *
	 * @param WP_Query $query WordPress query object
	 *
	 * @return void
	 */
	public function filter_media_library_query($query) {
		global $pagenow, $typenow;
		if (!$query->is_main_query()) return;
		
		if ('upload.php' !== $pagenow) return;
		
		if ('attachment' === $typenow && isset($_GET['wpo_image_optimization_status'])) {
			if (!current_user_can(WP_Optimize()->capability_required())) {
				die('You are not allowed to run this command.');
			}
			$nonce = isset($_GET['wpo_media_filter_nonce']) ? sanitize_key($_GET['wpo_media_filter_nonce']) : '';
			if (!wp_verify_nonce($nonce, 'wpo_media_filter_nonce')) {
				die('Security check failed.');
			}

			$filter = sanitize_text_field(wp_unslash($_GET['wpo_image_optimization_status']));
			$allowed_filters = array('compressed', 'uncompressed');
			if (!in_array($filter, $allowed_filters)) return;
			
			$meta_key = 'smush-complete';
			$meta_query = array();
			
			if ('compressed' === $filter) {
				$meta_query[] = array(
					array(
						'key' => $meta_key,
						'value' => '1',
					),
				);
			} elseif ('uncompressed' === $filter) {
				$meta_query[] = Updraft_Smush_Manager()->get_uncompressed_images_meta_query();
			}
			
			$query->set('post_mime_type', 'image');
			$query->set('meta_query', $meta_query);
		}
	}
	
	/**
	 * Filter media library query results
	 */
	public function filter_media_library_results($posts, $query) {
		global $pagenow, $typenow;
		
		if (!$query->is_main_query() || 'upload.php' !== $pagenow || 'attachment' !== $typenow) return $posts;
		if (isset($_GET['wpo_image_optimization_status'])) {
			if (!current_user_can(WP_Optimize()->capability_required())) {
				die('You are not allowed to run this command.');
			}
			$nonce = isset($_GET['wpo_media_filter_nonce']) ? sanitize_key($_GET['wpo_media_filter_nonce']) : '';
			if (!wp_verify_nonce($nonce, 'wpo_media_filter_nonce')) {
				die('Security check failed.');
			}
			
			$filter = sanitize_text_field(wp_unslash($_GET['wpo_image_optimization_status']));
			$allowed_filters = array('compressed', 'uncompressed');
			if (!in_array($filter, $allowed_filters)) {
				return $posts;
			}

			return array_values(array_filter($posts, function($post) {
				return !in_array($post->ID, $this->ewww_io_compressed_images);
			}));

		}
		return $posts;
	}
	
	/**
	 * Retrieves an array of attachment IDs that are compressed by ewww image optimize
	 *
	 * @return array
	 */
	private function get_ewww_io_compressed_images() {
		if (!WP_Optimize()->get_db_info()->table_exists('ewwwio_images')) return array();
		
		global $wpdb;
		return $wpdb->get_col("SELECT DISTINCT(attachment_id) FROM {$wpdb->prefix}ewwwio_images WHERE gallery='media'");
		
	}
}
endif;
