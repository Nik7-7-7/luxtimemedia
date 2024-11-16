<?php defined('ABSPATH') or die;
/**
 * Plugin Name: FluentBooking Pro
 * Description: The Pro version of FluentBooking Plugin
 * Version: 1.5.25
 * Author: WPManageNinja LLC
 * Author URI: https://fluentbooking.com
 * Plugin URI: https://fluentbooking.com
 * License: GPLv2 or later
 * Text Domain: fluent-booking-pro
 * Domain Path: /language
 * Requires Plugins: fluent-booking
 */

if (defined('FLUENT_BOOKING_PRO_DIR_FILE')) {
    return;
}

define('FLUENT_BOOKING_PRO_DIR_FILE', __FILE__);
define('FLUENT_BOOKING_PRO_DIR', plugin_dir_path(__FILE__));
define('FLUENT_BOOKING_PRO_VERSION', '1.5.25');

require __DIR__ . '/vendor/autoload.php';

call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__ . '/boot/app.php'));
