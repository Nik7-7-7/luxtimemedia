<?php
namespace Indeed\Ihc\Services;
/*
@since 7.4
*/
class DirectLogin
{
    private $settings                   = array();
    private $userMetaNameToken          = 'direct_link_token';
    private $userMetaNameTokenTimeout   = 'direct_link_token_timeout';
    private $defaultRedirect            = '';

    public function __construct()
    {
        $this->settings = ihc_return_meta_arr('direct_login');
        $this->defaultRedirect = get_option('ihc_general_login_redirect');
        if ( $this->defaultRedirect ){
            $this->defaultRedirect = get_permalink( $this->defaultRedirect );
        } else {
            $this->defaultRedirect = get_option('siteurl');
        }
    }

    public function isActive()
    {
        if ( !$this->settings['ihc_direct_login_enabled'] ){
            return false;
        }
        return true;
    }

    public function handleRequest( $token='' )
    {
        if ( !$this->isActive() || !$token){
            wp_safe_redirect($this->defaultRedirect);
        }
        $uid = $this->checkToken( $token );
        if ($uid){
            $this->doLogin( $uid );
        }
        wp_safe_redirect($this->defaultRedirect);
    }

    public function getDirectLoginLinkForUser( $uid=0, $timeout=86400 )
    {
        if ( !$uid ){
            return false;
        }
        $token = $this->generateToken( $uid, $timeout );
        $url = get_option('siteurl');
        if ( substr( $url, -1 ) != '/' ){
            $url .= '/';
        }
        return add_query_arg( array('ihc_action' => 'dl', 'token' => $token), $url );
    }

    public function generateToken( $uid=0, $timeout=0 )
    {
        $until = indeed_get_unixtimestamp_with_timezone() + $timeout;
        $token = 'ump' . $uid . hash( 'sha256', indeed_get_unixtimestamp_with_timezone() ) . $uid; 
        $token = hash( 'haval160,4', $token );
        update_user_meta( $uid, $this->userMetaNameToken, $token );
        update_user_meta( $uid, $this->userMetaNameTokenTimeout, $until );
        return $token;
    }

    public function checkToken( $token='' )
    {
        $token = sanitize_text_field( $token );
        $uid = \Ihc_Db::directLoginGetUserByToken($token);
        if ( $uid && \Ihc_Db::directLoginIsTokenActive($token) ){
            return $uid;
        }
        return 0;
    }

    public function doLogin( $uid=0 )
    {
        $this->resetTokenForUser( $uid );
        wp_clear_auth_cookie();
        wp_set_current_user( $uid );
        wp_set_auth_cookie( $uid );

        $redirectTo = get_site_url();
        wp_safe_redirect( $redirectTo );
        exit();
    }

    public function resetTokenForUser( $uid=0 )
    {
        delete_user_meta( $uid, $this->userMetaNameToken );
        delete_user_meta( $uid, $this->userMetaNameTokenTimeout );
    }
}
