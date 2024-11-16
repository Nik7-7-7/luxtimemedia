<?php
namespace Indeed\Uap;

class UploadFilesSecurity
{
    public function __construct()
    {
        if ( is_admin() ){
            return;
        }
        add_action( 'init', array( $this, 'process' ), 999 );
        add_action( 'uap_on_register_action', array( $this, 'removeCookieAndMediaHash'), 999 );
    }

    public function process()
    {
        global $indeed_db;
        $currentUri = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if ( !uapIsRegisterPage($currentUri) ){
            return;
        }
        $registerFields = $indeed_db->register_get_custom_fields();
        $key = uap_array_value_exists( $registerFields, 'file', 'type' );
        do {
            $hash = uap_random_string( 18 );
        } while ( $indeed_db->doesMediaHashExists( $hash ) );
        $indeed_db->saveMediaHash( $hash );
        setcookie( 'uapMedia', $hash, time() + 3600, COOKIEPATH, COOKIE_DOMAIN, false );
    }

    public function removeCookieAndMediaHash()
    {
        global $indeed_db;
        if ( !isset( $_COOKIE['uapMedia'] ) ){
            return;
        }
        $hash = $_COOKIE['uapMedia'];
        if ( $indeed_db->doesMediaHashExists( $hash ) ){
            $indeed_db->deleteMediaHash( $hash );
        }
        unset( $_COOKIE['uapMedia'] );
    }
}
