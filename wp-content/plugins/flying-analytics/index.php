<?php
/**
 * Plugin Name: Flying Analytics: Self-Host Google Analytics v4 with Speed Optimization
 * Plugin URI: https://wordpress.org/plugins/flying-analytics/
 * Description: Self-host Google Analytics v4 or use Minimal Analytics, a lightweight gtag.js alternative, to improve site speed and enhance privacy.
 * Author: WP Speed Matters
 * Author URI: https://wpspeedmatters.com/
 * Version: 2.0.0
 * Text Domain: flying-analytics
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

// Define constant with current version
if (!defined('FLYING_ANALYTICS_VERSION')) {
    define('FLYING_ANALYTICS_VERSION', '2.0.0');
}

include('init-config.php');
include('settings/index.php');
include('inject-js.php');
include('shortcuts.php');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flying_analytics_add_shortcuts');