<?php
namespace Indeed\Ihc;
/*
@since 8.0
*/

class WpLogin
{

    public function __construct()
    {
        $enabled = get_option( 'ihc_wp_login_custom_css' );
        if ( !$enabled ){
            return;
        }
        add_action( 'login_init', array( $this, 'loginInit' ), 9999 );
		    add_action( 'login_head', array( $this, 'loginHead' ), 9999 );
		    add_action( 'login_footer', array( $this, 'loginFooter' ), 9999 );

    }

	public function loginInit()
    {
		wp_enqueue_script('jquery');
	}

    public function loginHead()
    {
        wp_enqueue_style( 'ihc_wp_login_style', IHC_URL . 'assets/css/wp_login_custom.css', array(), '12.6', 'all' );
        $customLogo = get_option( 'ihc_wp_login_logo_image' );
        if ( $customLogo ):?>
        <?php
        $custom_css = '';
        $custom_css .= "
        body.login div#login h1 a{
          background: url(".$customLogo.") top center no-repeat !important;
        }
        ";
        wp_register_style( 'dummy-handle', false );
      	wp_enqueue_style( 'dummy-handle' );
      	wp_add_inline_style( 'dummy-handle', stripslashes($custom_css) );
        endif;
        wp_enqueue_script( 'ihc-wp-login', IHC_URL . 'assets/js/wp-login.js', [ 'jquery' ], '12.6' );

    }

	public function loginFooter()
    {
	}

}
