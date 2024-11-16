<?php
/*
Plugin Name: TranslatePress - Business
Plugin URI: https://translatepress.com/
Description: Experience a better way of translating your WordPress site using a visual front-end translation editor, with full support for WooCommerce and site builders.
Version: 1.4.8
Author: Cozmoslabs, Razvan Mocanu, Madalin Ungureanu
Author URI: https://cozmoslabs.com/
Text Domain: translatepress-multilingual
Domain Path: /languages
License: GPL2

== Copyright ==
Copyright 2017 Cozmoslabs (www.cozmoslabs.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

update_option( 'trp_license_key', 'B5E0B5F8DD8689E6ACA49DD6E6E1A930' );
update_option( 'trp_license_status', 'valid' );
update_option('trp_license_details', array('valid' => array((object) array('success' => true, 'license' => 'valid', 'item_id' => 0, 'item_name' => 'TranslatePress+Business', 'error' => '', 'checksum' => 'B5E0B5F8DD8689E6ACA49DD6E6E1A930', 'expires' => '2074-07-04'))));

add_filter('pre_http_request', function($preempt, $parsed_args, $url) {
    if ($parsed_args['method'] === 'POST' && strpos($url, 'https://translatepress.com') !== false) {
        $response_array = [
            "success" => true,
            "license" => "valid",
            "item_id" => false,
            "item_name" => "TranslatePress+Business",
            "error" => "",
            "checksum" => "B5E0B5F8DD8689E6ACA49DD6E6E1A930"
        ];

        $response_body = json_encode($response_array);

        return [
            'headers' => [],
            'body' => $response_body,
            'response' => [
                'code' => 200,
                'message' => 'OK'
            ],
        ];
    }
    return $preempt;
}, 10, 3);

if( !class_exists('TRP_Handle_Included_Addons') ){
    class TRP_Handle_Included_Addons{

        const TP_MINIMUM_VERSION = '2.8.7'; // Minimum version of TranslatePress - Multilingual
        function __construct(){

            // Stop TP from redirecting to default language when subdirectory is on but minimum version not met
            add_filter( 'trp_allow_language_redirect', array( $this, 'stop_redirecting_if_tp_minimum_version_not_met'), 99999, 1 );

            // Both trp_run_translatepress_hooks and trp_main_plugin_minimum_version_check run on priority 1, we need to make sure that the latter runs first so no errors occur
            remove_action(  'plugins_loaded', 'trp_run_translatepress_hooks', 1 );

            add_action( 'plugins_loaded', [ $this, 'trp_main_plugin_minimum_version_check' ], 1 );

            if ( function_exists( 'trp_run_translatepress_hooks' ) )
                add_action('plugins_loaded', 'trp_run_translatepress_hooks',1 );

            //disable old addons and create database entries for add-ons status
            add_action( 'plugins_loaded', array( $this, 'disable_old_add_ons' ), 12 );

            //activate an add-on when you press the button from the add-ons page
            add_action( 'trp_add_ons_activate', array( $this, 'trp_activate_add_ons' ) );
            //deactivate an add-on when you press the button from the add-ons page
            add_action( 'trp_add_ons_deactivate', array( $this, 'trp_deactivate_add_ons' ) );
            //show the button in the add-ons page with the correct action
            add_filter( 'trp_add_on_is_active', array( $this, 'trp_check_add_ons_activation' ) , 10, 2 );

            add_action( 'admin_notices', array( $this, 'trp_main_plugin_notice' ) );

            //include add-on files that contain activation hooks even when add-ons are deactivated
            $this->include_mandatory_addon_files();

            //include the addons from the main plugin if they are activated
            $this->include_addons();
        }

        /**
         * Stop TP from redirecting to default language when subdirectory is on but minimum version not met
         */
        public function stop_redirecting_if_tp_minimum_version_not_met( $redirect ) {
            if ( !defined('TRP_PLUGIN_VERSION' ) || version_compare( TRP_PLUGIN_VERSION, self::TP_MINIMUM_VERSION, '>=' ) )
                return $redirect; // do nothing

            return false;
        }

        /**
         * Add a notice if the TranslatePress main plugin is active and right version
         */
        function trp_main_plugin_notice(){
            $tp_installation_status = $this->install_activate_tp();
            if ( $tp_installation_status !== 'no_action_requested' ){
                if ( $tp_installation_status === 'plugin_activated' ) {
                    echo '<div class="notice updated is-dismissible "><p>' . esc_html__('Plugin activated.', 'translatepress-multilingual') . '</p></div>';
                }
                if ( $tp_installation_status === 'error_activating' ) {
                    echo '<div class="notice notice-error is-dismissible "><p>' . wp_kses( sprintf( __('Could not install. Try again from <a href="%s" >Plugins Dashboard.</a>', 'translatepress-multilingual'), admin_url('plugins.php') ), array('a' => array( 'href' => array() ) ) ) . '</p></div>';
                }
            }

            if( !defined( 'TRP_PLUGIN_VERSION' ) ){
                if ( $tp_installation_status === 'no_action_requested') {
                    echo '<div class="notice notice-info is-dismissible"><p>';
                    printf( esc_html__( 'Please install and activate the TranslatePress - Multilingual plugin', 'translatepress-multilingual' ) );
                    echo '</p>';
                    echo '<p><a href="' . esc_url( add_query_arg( array( 'action' => 'trp_install_tp_plugin', 'nonce' => wp_create_nonce( 'trp_install_tp_plugin' ) ) ) ) . '" type="button" class="button-primary">' . esc_html__( 'Install & Activate', 'translatepress-multilingual' ) . '</a></p>';
                    echo '</div>';
                }
            }
            else{
                if( version_compare( TRP_PLUGIN_VERSION, '2.0.5', '<' ) ){
                    echo '<div class="notice notice-info is-dismissible"><p>';
                    echo esc_html( sprintf(__('Please update the TranslatePress - Multilingual plugin to version 2.0.5 at least for %s to work properly', 'translatepress-multilingual'), TRANSLATE_PRESS ) );
                    echo '</p></div>';
                }
            }
        }

        /**
         * Check if the main plugin is updated to the minimum version required by TranslatePress Pro
         *
         * Block TranslatePress from loading in case it's not
         *
         * @return void
         */
        function trp_main_plugin_minimum_version_check(){
            if ( TRANSLATE_PRESS === 'TranslatePress - Dev' ) return;

            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            if ( !defined('TRP_PLUGIN_VERSION' ) || version_compare( TRP_PLUGIN_VERSION, self::TP_MINIMUM_VERSION, '>=' ) )
                return; // do nothing

            add_action( 'admin_notices', [ $this, 'trp_show_minimum_version_warning' ], 10 );
            add_filter( 'trp_allow_tp_to_run', '__return_false' ); // Stop TP from loading anything
        }

        function trp_show_minimum_version_warning() {
            $update_url = admin_url('plugins.php?s=translatepress&plugin_status=all');
            $current_url = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( $_SERVER['REQUEST_URI'] ) : '';

            // Check if the current URL contains 's=translatepress&plugin_status=all'
            $hide_button = strpos($current_url, 's=translatepress&plugin_status=all') !== false;

            echo '<div class="notice notice-error" style="display: flex; align-items: center; padding: 10px;">
              <p style="margin: 0; padding-right: 10px;">' . wp_kses( sprintf(
                    __( 'Please update TranslatePress - Multilingual to version %2$s or newer. Your currently installed version of TranslatePress - Multilingual is no longer compatible with the current version of %1$s.',
                        'translatepress-multilingual' ),
                    TRANSLATE_PRESS,
                    self::TP_MINIMUM_VERSION
                ), [] ) . ' ' .
                esc_html__( 'All TranslatePress functionalities are disabled until then.', 'translatepress-multilingual' ) . '</p>' .
                ( !$hide_button ? '<a href="' . esc_url( $update_url ) . '" class="button-primary" style="margin-left: 10px;">' . esc_html__( 'Update Now', 'translatepress-multilingual' ) . '</a>' : '' ) . /* phpcs:ignore */ /* everything is escaped or pure html */
                '</div>';
        }

        /**
         * Function that determines if an add-on is active or not
         * @param $bool
         * @param $slug
         * @return mixed
         */
        function trp_check_add_ons_activation( $bool, $slug ){
            $trp_add_ons_settings = get_option( 'trp_add_ons_settings', array() );
            if( !empty( $trp_add_ons_settings[$slug] ) )
                $bool = $trp_add_ons_settings[$slug];

            return $bool;
        }

        /**
         * Function that activates a PB add-on
         */
        function trp_activate_add_ons( $slug ){
            $this->trp_activate_or_deactivate_add_on( $slug, true );
        }

        /**
         * Function that deactivates a PB add-on
         */

        function trp_deactivate_add_ons( $slug ){
            $this->trp_activate_or_deactivate_add_on( $slug, false );
        }


        /**
         * Function used to activate or deactivate a PB add-on
         */
        function trp_activate_or_deactivate_add_on( $slug, $action ){
            $trp_add_ons_settings = get_option( 'trp_add_ons_settings', array() );
            $trp_add_ons_settings[$slug] = $action;
            update_option( 'trp_add_ons_settings', $trp_add_ons_settings );
        }


        /**
         * Check if an addon was active as a slug before it was programmatically deactivated by us
         * On the plugin updates, where we transitioned add-ons we save the status in an option 'trp_old_add_ons_status'
         * @param $slug
         * @return false
         */
        function was_addon_active_as_plugin( $slug ){
            $old_add_ons_status = get_option( 'trp_old_add_ons_status' );
            if( isset( $old_add_ons_status[$slug] ) )
                return $old_add_ons_status[$slug];
            else
                return false;
        }

        /**
         * Function that returns the slugs of old addons that were plugins
         * @return string[]
         */
        function get_old_addons_slug_list(){
            $old_addon_list = array(
                'tp-add-on-automatic-language-detection/tp-automatic-language-detection.php',
                'tp-add-on-browse-as-other-roles/tp-browse-as-other-role.php',
                'tp-add-on-deepl/index.php',
                'tp-add-on-extra-languages/tp-extra-languages.php',
                'tp-add-on-navigation-based-on-language/tp-navigation-based-on-language.php',
                'tp-add-on-seo-pack/tp-seo-pack.php',
                'tp-add-on-translator-accounts/index.php',
            );

            return $old_addon_list;
        }


        /**
         * Deactivate the old addons as plugins
         */
        function disable_old_add_ons(){

            //if it's triggered in the frontend we need this include
            if( !function_exists('is_plugin_active') )
                include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            $old_addons_list = $this->get_old_addons_slug_list();
            $deactivated_addons = 0;

            $old_add_ons_status = get_option( 'trp_old_add_ons_status', array() );

            foreach( $old_addons_list as $addon_slug ){
                if( is_plugin_active($addon_slug) ){

                    if( !isset( $old_add_ons_status[$addon_slug] ) )//construct here the old add-ons status just once
                        $old_add_ons_status[$addon_slug] = true;

                    if( is_multisite() ){
                        if( is_plugin_active_for_network($addon_slug) )
                            deactivate_plugins($addon_slug, true);
                        else
                            deactivate_plugins($addon_slug, true, false);
                    }
                    else {
                        deactivate_plugins($addon_slug, true);
                    }
                    $deactivated_addons++;
                }
                else{
                    if( !isset( $old_add_ons_status[$addon_slug] ) )
                        $old_add_ons_status[$addon_slug] = false;
                }
            }
            if ( isset( $_GET['activate'] ) && $deactivated_addons === 1 ){
                add_action( 'load-plugins.php',
                    function(){
                        add_action( 'in_admin_header',
                            function(){
                                add_filter( 'gettext', array( $this, 'disable_old_add_ons_notice' ), 99, 3 );
                            }
                        );
                    }
                );
            } elseif ( isset( $_GET['activate-multi'] ) && $deactivated_addons !== 0 ){
                add_action( 'admin_notices', array( $this, 'disable_old_add_ons_notice_multi' ) );
            }


            if( !empty( $old_add_ons_status ) ){
                $old_add_ons_option = get_option( 'trp_old_add_ons_status', array() );
                if( empty( $old_add_ons_option ) )
                    update_option( 'trp_old_add_ons_status', $old_add_ons_status );//this should not change

                $add_ons_settings = get_option( 'trp_add_ons_settings', array() );
                if( empty( $add_ons_settings ) ) {
                    //activate by default a couple of add-ons
                    $old_add_ons_status['tp-add-on-browse-as-other-roles/tp-browse-as-other-role.php'] = true;
                    $old_add_ons_status['tp-add-on-deepl/index.php'] = true;
                    $old_add_ons_status['tp-add-on-extra-languages/tp-extra-languages.php'] = true;

                    update_option('trp_add_ons_settings', $old_add_ons_status);//this should be set just once
                }
            }
        }

        /**
         * Modify the output of the notification when trying to activate an old addon
         * @param $translated_text
         * @param $untranslated_text
         * @param $domain
         * @return mixed|string
         */
        function disable_old_add_ons_notice( $translated_text, $untranslated_text, $domain )
        {
            $old = array(
                "Plugin activated."
            );

            $new = "This TranslatePress add-on has been migrated to the main plugin and is no longer used. You can delete it.";

            if ( in_array( $untranslated_text, $old, true ) )
            {
                $translated_text = $new;
                remove_filter( current_filter(), __FUNCTION__, 99 );
            }
            return $translated_text;
        }

        /**
         * Modify the output of the notification when trying to activate an old addon
         */
        function disable_old_add_ons_notice_multi() {
            ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php _e( 'This TranslatePress add-on has been migrated to the main plugin and is no longer used. You can delete it.', 'translatepress-multilingual' ); ?></p>
            </div>
            <?php
        }


        /**
         * Function that includes the add-ons from the main plugin
         */
        function include_addons(){

            $add_ons_settings = get_option( 'trp_add_ons_settings', array() );

            if( !empty( $add_ons_settings ) ){
                foreach( $add_ons_settings as $add_on_slug => $add_on_enabled ){
                    if( $add_on_enabled ){

                        //include here the advanced addons
                        $seo_addon_dir = "seo-pack";
                        $option = get_option( 'trp_advanced_settings', true );
                        if ( isset( $option['load_legacy_seo_pack'] ) && $option['load_legacy_seo_pack'] === 'yes' ){
                            $seo_addon_dir = "seo-pack-legacy";
                        }

                        if( $add_on_slug === 'tp-add-on-seo-pack/tp-seo-pack.php' && file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-advanced/'. $seo_addon_dir .'/tp-seo-pack.php') )
                            require_once plugin_dir_path(__FILE__) . '/add-ons-advanced/'. $seo_addon_dir .'/tp-seo-pack.php';
                        if( $add_on_slug === 'tp-add-on-extra-languages/tp-extra-languages.php' && file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-advanced/extra-languages/tp-extra-languages.php') )
                            require_once plugin_dir_path(__FILE__) . '/add-ons-advanced/extra-languages/tp-extra-languages.php';

                        //include here the PRO addons
                        if( $add_on_slug === 'tp-add-on-automatic-language-detection/tp-automatic-language-detection.php' && file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-pro/automatic-language-detection/tp-automatic-language-detection.php') )
                            require_once plugin_dir_path(__FILE__) . '/add-ons-pro/automatic-language-detection/tp-automatic-language-detection.php';
                        if( $add_on_slug === 'tp-add-on-browse-as-other-roles/tp-browse-as-other-role.php' && file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-pro/browse-as-other-roles/tp-browse-as-other-role.php') )
                            require_once plugin_dir_path(__FILE__) . '/add-ons-pro/browse-as-other-roles/tp-browse-as-other-role.php';
                        if( $add_on_slug === 'tp-add-on-deepl/index.php' && file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-pro/deepl/index.php') )
                            require_once plugin_dir_path(__FILE__) . '/add-ons-pro/deepl/index.php';
                        if( $add_on_slug === 'tp-add-on-navigation-based-on-language/tp-navigation-based-on-language.php' && file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-pro/navigation-based-on-language/tp-navigation-based-on-language.php') )
                            require_once plugin_dir_path(__FILE__) . '/add-ons-pro/navigation-based-on-language/tp-navigation-based-on-language.php';
                        if( $add_on_slug === 'tp-add-on-translator-accounts/index.php' && file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-pro/translator-accounts/index.php') )
                            require_once plugin_dir_path(__FILE__) . '/add-ons-pro/translator-accounts/index.php';
                    }
                }
            }

        }

        /**
         * Include add-on files that contain activation hooks even when add-ons are deactivated
         *
         * Necessary in order to perform actions during the operation of activation or deactivation of that add-on
         */
        function include_mandatory_addon_files(){
            if( file_exists(plugin_dir_path( __FILE__ ) . '/add-ons-pro/translator-accounts/includes/class-translator-accounts-activator.php') )
                require_once plugin_dir_path(__FILE__) . '/add-ons-pro/translator-accounts/includes/class-translator-accounts-activator.php';

            if ( file_exists( plugin_dir_path( __FILE__ ) . '/add-ons-advanced/seo-pack/tp-seo-pack-activator.php' ) ) {
                require_once plugin_dir_path( __FILE__ ) . '/add-ons-advanced/seo-pack/tp-seo-pack-activator.php';
            }
        }

        /**
         * If action and nonce are set, attempt installing and activating TP Free
         *
         * @return string 'no_action_requested' || 'error_activating' || 'plugin_activated'
         */
        public function install_activate_tp(){
            if ( isset( $_REQUEST['trp_install_tp_plugin_success'] ) && $_REQUEST['trp_install_tp_plugin_success'] === 'true' ){
                return 'plugin_activated';
            }

            if (
                isset( $_REQUEST['action'] ) && !empty($_REQUEST['nonce']) && $_REQUEST['action'] === 'trp_install_tp_plugin' &&
                !isset( $_REQUEST['trp_install_tp_plugin_success']) &&
                current_user_can( 'manage_options' ) &&
                wp_verify_nonce( sanitize_text_field( $_REQUEST['nonce'] ), 'trp_install_tp_plugin' )
            ) {
                $plugin_slug = 'translatepress-multilingual/index.php';

                $installed = true;
                if ( !$this->is_plugin_installed( $plugin_slug ) ){
                    $plugin_zip = 'https://downloads.wordpress.org/plugin/translatepress-multilingual.zip';
                    $installed = $this->install_plugin($plugin_zip);
                }

                if ( !is_wp_error( $installed ) && $installed ) {
                    $activate = activate_plugin( $plugin_slug );

                    if ( is_null( $activate ) ) {
                        wp_safe_redirect( add_query_arg( 'trp_install_tp_plugin_success', 'true' ) );
                        return 'plugin_activated';
                    }
                }

                return 'error_activating';
            }

            return 'no_action_requested';
        }

        /**
         * Check if plugin is installed
         *
         * @param $plugin_slug
         * @return bool
         */
        public function is_plugin_installed( $plugin_slug ) {
            if ( !function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $all_plugins = get_plugins();

            if ( !empty( $all_plugins[ $plugin_slug ] ) ) {
                return true;
            }

            return false;
        }

        /**
         * Install plugin by providing downloadable zip address
         *
         * @param $plugin_zip
         * @return array|bool|WP_Error
         */
        public function install_plugin( $plugin_zip ) {
            include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            wp_cache_flush();
            $upgrader  = new Plugin_Upgrader();

            // do not output any messages
            $upgrader->skin = new Automatic_Upgrader_Skin();

            $installed = $upgrader->install( $plugin_zip );
            return $installed;
        }
    }

    $trp_add_ons_handler = new TRP_Handle_Included_Addons();
}



/** Initialize update here in the main plugin file. It is a must **/
function trp_business_update(){
    // this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
    define('TRP_BUSINESS_STORE_URL', 'https://translatepress.com');
    // the name of your product. This should match the download name in EDD exactly
    define('TRP_BUSINESS_ITEM_NAME', 'TranslatePress Business');
    if (class_exists('TRP_EDD_SL_Plugin_Updater')) {
        // retrieve our license key from the DB
        $license_key = trim(get_option('trp_license_key'));
        // setup the updater
        $edd_updater = new TRP_EDD_SL_Plugin_Updater(TRP_BUSINESS_STORE_URL, __FILE__, array(
                'version' => '1.4.8',        // current version number
                'license' => $license_key,    // license key (used get_option above to retrieve from DB)
                'item_name' => TRP_BUSINESS_ITEM_NAME,    // name of this plugin
                'item_id' => '222',
                'author' => 'Cozmoslabs',  // author of this plugin
                'beta' => false
            )
        );
    }
}
add_action( 'plugins_loaded', 'trp_business_update', 0 );
/** End the update initialization here **/


if( !defined( 'TRANSLATE_PRESS' ) )
    define( 'TRANSLATE_PRESS', 'TranslatePress - Business' );

register_activation_hook(__FILE__, 'trp_business_activate');
function trp_business_activate( $network_wide ) {
    if( !function_exists('is_plugin_active') )
        include_once( ABSPATH . '/wp-admin/includes/plugin.php' );

    if( is_plugin_active('translatepress-personal/index.php') || is_plugin_active('translatepress-developer/index.php') ){
        set_transient( 'trp_deactivate_business', true );
    }else{
        delete_transient( 'trp_deactivate_business' );
    }
}


add_action('admin_notices', 'trp_business_admin_notice');
function trp_business_admin_notice(){
    $trp_deactivate_business = get_transient( 'trp_deactivate_business' );
    if( $trp_deactivate_business ){

        $other_plugin_name = '';
        if( is_plugin_active('translatepress-personal/index.php') )
            $other_plugin_name = 'TranslatePress - Personal';
        else if( is_plugin_active('translatepress-developer/index.php') )
            $other_plugin_name = 'TranslatePress - Developer';
        ?>
        <div class="error">
            <p>
                <?php
                /* translators: %s is the plugin version name */
                echo wp_kses_post( sprintf( __( '%s is also activated. You need to deactivate it before activating this version of the plugin.', 'translatepress-multilingual'), $other_plugin_name ) );
                ?>
            </p>
        </div>
        <?php
        delete_transient( 'trp_deactivate_business' );
    }
}

add_action( 'admin_init', 'trp_business_plugin_deactivate' );
function trp_business_plugin_deactivate() {

    $trp_deactivate_business = get_transient( 'trp_deactivate_business' );
    if( $trp_deactivate_business ){
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    unset($_GET['activate']);
}
