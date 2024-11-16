<?php

/**
 * Plugin Name:       Webinar and Video Conference with Jitsi Meet Ultimate
 * Plugin URI:        https://wppool.dev/webinar-and-video-conference-with-jitsi-meet
 * Description:       Host live webinars, conferences, online classes, video calls directly on your WordPress website with gutenberg block
 * Version:           1.2.2
 * Author:            WPPOOL
 * Author URI:        https://wppool.dev
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       jitsi-pro
 * Requires at least: 5.0
 * Tested up to: 	  6.2
 */


// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
update_option( 'appsero_' . md5( 'webinar-and-video-conference-with-jitsi-meet-ultimate' ) . '_manage_license', [
    'key'              => '**********',
    'status'           => 'activate',
    'remaining'        => '5',
    'activation_limit' => '5',
    'expiry_days'      => false,
    'recurring'        => false,
] );
update_option('jitsi_ultimate_license_is_valid', 1);
define('JITSI_ULTIMATE_VERSION', '1.2.2');
define('JITSI_ULTIMATE_REQUIRED_MINIMUM_VERSION', '1.2.2');
define('JITSI_ULTIMATE__FILE__', __FILE__);
define('JITSI_ULTIMATE_DIR_PATH', plugin_dir_path(JITSI_ULTIMATE__FILE__));
define('JITSI_ULTIMATE_FILE_PATH', plugin_dir_path(__FILE__));
define('JITSI_ULTIMATE_URL', plugins_url('', __FILE__));

function jitsi_ultimate_begin() {

    /**
     * Check for Jitsi Addons existence
     * And prevent further execution if doesn't exist.
     */
    $pluginList = get_option('active_plugins');
    $jitsi_free = 'webinar-and-video-conference-with-jitsi-meet/jitsi-meet-wp.php';
    if (!in_array($jitsi_free, $pluginList)) {
        add_action('admin_notices', 'jitsi_ultimate_missing_jitsi_notice');
        return;
    }

    /**
     * Check for Jitsi Addons required version
     * And prevent further execution if doesn't match.
     */
    if (!version_compare(JITSI_MEET_WP_VERSION, JITSI_ULTIMATE_REQUIRED_MINIMUM_VERSION, '>=')) {
        add_action('admin_notices', 'jitsi_ultimate_required_version_missing_notice');
        return;
    }

    /**
     * Check if Licence form is submitted or changed
     */
    if (isset($_POST['_action'])) {
        if ($_POST['_action'] == 'active' || $_POST['_action'] == 'deactive' || $_POST['_action'] == 'refresh') {
            header('Location: ' . $_SERVER['REQUEST_URI']);
        }
    }

    /**
     * Initialize the plugin tracker
     *
     * @return void
     */
    function appsero_init_tracker_jitsi_meet_pro()
    {

        if (!class_exists('Appsero\Client')) {

            if (file_exists(JITSI_MEET_WP_DIR_PATH . '/inc/appsero/src/Client.php')) {
                require_once JITSI_MEET_WP_DIR_PATH . '/inc/appsero/src/Client.php';
            } elseif (file_exists(JITSI_MEET_WP_DIR_PATH . '/appsero/src/Client.php')) {
                require_once JITSI_MEET_WP_DIR_PATH . '/appsero/src/Client.php';
            } else {
                return false;
            }
        }

        $client = new Appsero\Client('58e348bb-f483-424b-9b06-6ff54d232d82', 'Jitsi Meet Ultimate', JITSI_ULTIMATE__FILE__);

        // Active insights
        $client->insights()->init();
        $client->updater();

        global $jitsi_meet_license;
        $jitsi_meet_license = $client->license();
        update_option($client->slug . '_allow_tracking', 'yes');

        // Active license page and checker
        $args = array(
            'type'        => 'submenu',
            'menu_title'  => __('License Activation', 'jitsi-pro'),
            'page_title'  => __('License Activation - Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro'),
            'menu_slug'   => 'jitsi-meet-pro-license',
            'parent_slug' => 'jitsi-meet',
            'position'    => 10
        );

        $client->license()->add_settings_page($args);
    }
    appsero_init_tracker_jitsi_meet_pro();

    function jitsi_pro_check_licence_and_activate()
    {
        global $jitsi_meet_license;
        $dir_path = JITSI_MEET_WP_DIR_PATH;

        if (!$jitsi_meet_license->is_valid()) {
            $dir_path = JITSI_MEET_WP_DIR_PATH;
        } else {
            $dir_path = JITSI_ULTIMATE_DIR_PATH;
        }

        if (!function_exists('register_block_type')) {
            include_once $dir_path . 'init.php';
            Jitsi_Meet_WP::instance();
        } else {
            include_once $dir_path . 'init.php';
            Jitsi_Meet_WP::instance();
            include_once $dir_path . 'gutenberg-init.php';
            Jitsi_Meet_WP_Gutenberg::instance();
        }
    }
    jitsi_pro_check_licence_and_activate();
}

add_action('plugins_loaded', 'jitsi_ultimate_begin', 20);

$pluginList = get_option('active_plugins');
$jitsi_free = 'webinar-and-video-conference-with-jitsi-meet/jitsi-meet-wp.php';
$jitsi_pro  = 'webinar-and-video-conference-jitsi-meet-pro/jitsi-meet-wp.php';

if (!in_array($jitsi_pro, $pluginList) && in_array($jitsi_free , $pluginList)) {
    function redirectOnActivation() {
        global $jitsi_meet_license;

        $redirect_to_jitsi_page = get_option('redirect_to_jitsi_ultimate_page', 0);
        $redirect_url = '';

        if ($jitsi_meet_license->is_valid()) {
            $redirect_url = 'admin.php?page=jitsi-pro-apis';
            update_option('jitsi_ultimate_license_is_valid', absint(1));
        } else {
            $redirect_url = 'admin.php?page=jitsi-meet-pro-license';
            update_option('jitsi_ultimate_license_is_valid', absint(0));
        }

        if ($redirect_to_jitsi_page == 1) {
            update_option('redirect_to_jitsi_ultimate_page', absint(0));
            wp_safe_redirect(admin_url($redirect_url));
            exit;
        }
    }

    add_action('admin_init','redirectOnActivation');
}

register_activation_hook(__FILE__, function () {
    update_option('jitsi_ultimate_version', JITSI_ULTIMATE_VERSION);
    update_option('redirect_to_jitsi_ultimate_page', absint(1));
    update_option('jitsi_ultimate_activate', absint(1));

    if (get_option('jitsi_meet_welcome_redirect_pro') != 'occured') {
        add_option('jitsi_meet_welcome_redirect_pro', true);
    }
});

register_deactivation_hook(__FILE__, function () {
    update_option('jitsi_ultimate_activate', absint(0));
});

/**
 * Jitsi Addons missing notice for admin panel.
 *
 * @return void
 */
function jitsi_ultimate_missing_jitsi_notice()
{
    $notice = sprintf(
        __('%1$s requires %2$s to be installed and activated. Please install %3$s', 'jitsi-pro'),
        '<strong>' . __('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') . '</strong>',
        '<strong>' . __('Webinar and Video Conference with Jitsi Meet', 'jitsi-pro') . '</strong>',
        '<a target="_blank" rel="noopener" href="' . esc_url(admin_url('plugin-install.php?s=Webinar+and+Video+Conference+with+Jitsi+Meet&tab=search&type=term')) . '">' . __('Webinar and Video Conference with Jitsi Meet', 'jitsi-pro') . '</a>'
    );

    printf('<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $notice);
}

/**
 * Jitsi Addons version incompatibility notice for admin panel.
 *
 * @return void
 */
function jitsi_ultimate_required_version_missing_notice()
{
    $notice = sprintf(
        __('%1$s requires %2$s version %3$s or greater. Please update your %2$s', 'jitsi-pro'),
        '<strong>' . __('Webinar and Video Conference with Jitsi Meet Premium', 'jitsi-pro') . '</strong>',
        '<strong>' . __('Webinar and Video Conference with Jitsi Meet', 'jitsi-pro') . '</strong>',
        JITSI_ULTIMATE_REQUIRED_MINIMUM_VERSION
    );

    printf('<div class="notice notice-error is-dismissible"><p>%1$s</p></div>', $notice);
}
