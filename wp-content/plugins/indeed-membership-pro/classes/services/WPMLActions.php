<?php
namespace Indeed\Ihc\Services;

class WPMLActions
{

    public function __construct()
    {

        /// save user language
        add_action( 'ump_on_register_action', array( $this, 'saveUserLanguage' ), 999, 1 );
        add_action( 'ump_on_update_action', array( $this, 'saveUserLanguage' ), 999, 1 );

        /// register notifications
        add_action( 'ihc_save_notification_action', array( $this, 'registerNotifications'), 99, 1 );
        /// register taxes
        add_action( 'ihc_save_tax_action', array( $this, 'registerTaxes'), 99, 1 );

        add_action( 'ihc_action_admin_dashboard', array( $this, 'registerNotifications'), 99, 1 );
        add_action( 'ihc_action_admin_dashboard', array( $this, 'registerTaxes'), 99, 1 );

    }

    /// use ihc_save_notification_action just for trigger, we'll ignore the param
    public function registerNotifications( $data=null )
    {
        global $wpdb;
        if ( !defined('ICL_SITEPRESS_VERSION') ){ // wpml is not active
            return;
        }
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT notification_type, level_id, subject, message, pushover_message FROM {$wpdb->prefix}ihc_notifications;";
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return;
        }
        $domain = 'ihc';
        
        foreach ( $data as $object ){
            $name = $object->notification_type . '_title_' . $object->level_id;
            if ( function_exists( 'icl_st_is_registered_string' ) && icl_st_is_registered_string( $domain, $name ) !== null ){
                continue;
            }
            do_action( 'wpml_register_single_string', $domain, $name, $object->subject );
            $name = $object->notification_type . '_message_' . $object->level_id;
            do_action( 'wpml_register_single_string', $domain, $name, $object->message );
            $name = $object->notification_type . '_pushover_message_' . $object->level_id;
            do_action( 'wpml_register_single_string', $domain, $name, $object->pushover_message );
        }
    }

    public function registerTaxes( $data=null )
    {
        global $wpdb;
        //No query parameters required, Safe query. prepare() method without parameters can not be called
        $query = "SELECT id, label, description FROM {$wpdb->prefix}ihc_taxes;";
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return;
        }
        $domain = 'ihc';
        foreach ( $data as $object ){
                $name = $object->id . '_label';
            do_action( 'wpml_register_single_string', $domain, $name, $object->label );
                $name = $object->id . '_description';
            do_action( 'wpml_register_single_string', $domain, $name, $object->description );
        }
    }

    public function saveUserLanguage( $uid=0 )
    {
        if ( !$uid ){
            return false;
        }
        $language = indeed_get_current_language_code();
        return update_user_meta( $uid, 'ihc_locale_code', $language );
    }


}
