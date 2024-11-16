<?php
namespace Indeed\Ihc;
/*
@since 7.4
*/
class Filters
{

    public function __construct()
    {
        add_filter( 'wp_nav_menu_objects', array( $this, 'ihc_filter_public_nav_menu' ), 999, 1 );
        add_filter( 'ihc_filter_restriction', [ $this, 'filterGeneralRestrictions' ], 1, 1 );
        add_filter( 'ihc_filter_restriction', [ $this, 'stopRestrictionsOnPagesWithUmpQueryArgs' ], 2, 2 );
    }

    /**
     * @param array
     * @return array
     */
    public function ihc_filter_public_nav_menu( $items=array() )
    {
        if ( !$items ){
            return $items;
        }
        foreach ($items as $itemData){
            if (stripos( $itemData->url, '?ihc-modal=login' )){
                $itemData->url = ''; // #
                $itemData->classes[] = 'ihc-modal-trigger-login';
                add_action( 'get_footer', array($this, 'ihc_insert_modal_login'), 999, 1 );
                continue;
            }
            if (stripos( $itemData->url, '?ihc-modal=register' )){
                $itemData->url = '';// #createuser
                $itemData->classes[] = 'ihc-modal-trigger-register';
                add_action( 'get_footer', array($this, 'ihc_insert_modal_register'), 999, 1 );
                continue;
            }
        }
        return $items;
    }

    public function ihc_insert_modal_login( $name='' )
    {
        echo ihc_login_popup( array('trigger' => 'ihc-modal-trigger-login') );
    }

    public function ihc_insert_modal_register( $name='' )
    {
        echo ihc_register_popup( array('trigger' => 'ihc-modal-trigger-register') );
    }

    /**
     * @param boolean
     * @return boolean
     */
    public function filterGeneralRestrictions( $filterOn=true )
    {
        $enabled = get_option( 'ihc_security_allow_search_engines' );
        if ( !$enabled ){
            return $filterOn;
        }
        $userAgent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        if ( $userAgent == '' ){
            return $filterOn;
        }
        $userAgent = strtolower( $userAgent );
        $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
        if ( $ip == '' ){
            return $filterOn;
        }
        $host = gethostbyaddr( $ip );

        if ( strpos( $userAgent, 'google')!==false && preg_match('/\.googlebot|google\.com$/i', $host ) ){
            // it's google
            return false;
        }

        if ( strpos( $userAgent, 'yandex')!==false && preg_match('/\.yandex\.ru$/i', $host ) ){
            // it's yandex
            return false;
        }

        if ( strpos( $userAgent, 'msn')!==false && preg_match('/\.search\.msn\.com$/i', $host ) ){
            // it's msn
            return false;
        }

        if ( strpos( $userAgent, 'ask')!==false && preg_match('/\.ask\.com$/i', $host ) ){
            // it's ask
            return false;
        }

        if ( strpos( $userAgent, 'yahoo')!==false && preg_match('/\.crawl\.yahoo\.net$/i', $host ) ){
            // it's yahoo
            return false;
        }

        return $filterOn;
    }

    /**
     * @param bool
     * @param int
     * @return bool
     */
    public function stopRestrictionsOnPagesWithUmpQueryArgs( $restrictionOn=true, $postId=0 )
    {
        $homepage = get_home_url();
        // since version 11.8
        if ( !isset( $_SERVER['HTTP_HOST'] ) || !isset( $_SERVER['REQUEST_URI'] ) ){
            return $restrictionOn;
        }
        $currentUri = IHC_PROTOCOL . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_text_field($_SERVER['REQUEST_URI']);
        $currentUri = rtrim( $currentUri, '/' );
        $homepage = rtrim( $homepage, '/' );
        if ( strcmp( $homepage, $currentUri ) !== 0 ){
            return $restrictionOn;
        }

        if ( isset( $_GET['ihc_action'] ) && $_GET['ihc_action'] != '' ){
            return false;
        }
        return $restrictionOn;
    }

}
