<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

delete_option('berqwp_enable_sandbox');
delete_option('berqwp_webp_max_width');
delete_option('berqwp_webp_quality');
delete_option('berqwp_image_lazyloading');
delete_option('berqwp_disable_webp');
delete_option('berqwp_enable_cdn');
delete_option('berqwp_preload_fontfaces');
delete_option('berqwp_disable_emojis');
delete_option('berqwp_lazyload_youtube_embed');
delete_option('berqwp_javascript_execution_mode');
delete_option('berqwp_enable_preload_mostly_used_font');
delete_option('berqwp_interaction_delay');
delete_option('berq_opt_mode');
delete_option('berq_ignore_urls_params');
delete_option('berq_exclude_urls');
delete_option('berqwp_license_key');
delete_option('berqwp_site_url');
delete_option('berqwp_post_type_names');
delete_option('berqwp_optimize_post_types');
delete_option('berqwp_optimize_taxonomies');

// Remove advanced-cache.php file
$dropin_file = ABSPATH . 'wp-content/advanced-cache.php';
if (file_exists($dropin_file)) {
    unlink($dropin_file);
}