<?php
/**
 * Plugin Name: Flying Pages: Preload Pages for Faster Navigation & Improved User Experience
 * Plugin URI: https://wordpress.org/plugins/flying-pages/
 * Description: Preload pages intelligently to boost site speed and enhance user experience by loading pages before users click, ensuring instant page transitions.
 * Author: WP Speed Matters
 * Author URI: https://wpspeedmatters.com/
 * Version: 2.4.6
 * Text Domain: flying-pages
 */

// If this file is called directly, abort.
if (! defined('WPINC')) die;

// Define constant with current version
if (!defined('FLYING_PAGES_VERSION'))
    define('FLYING_PAGES_VERSION', '2.4.6');

include('init-config.php');
include('settings/index.php');
include('inject-js.php');
include('shortcuts.php');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'flying_pages_add_action_links');