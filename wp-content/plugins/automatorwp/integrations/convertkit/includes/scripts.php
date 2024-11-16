<?php
/**
 * Scripts
 *
 * @package     AutomatorWP\ConvertKit\Scripts
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Register admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_convertkit_admin_register_scripts() {

    // Use minified libraries if SCRIPT_DEBUG is turned off
    $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

    // Stylesheets
    wp_register_style( 'automatorwp-convertkit-css', AUTOMATORWP_CONVERTKIT_URL . 'assets/css/automatorwp-convertkit' . $suffix . '.css', array(), AUTOMATORWP_CONVERTKIT_VER, 'all' );

    // Scripts
    wp_register_script( 'automatorwp-convertkit-js', AUTOMATORWP_CONVERTKIT_URL . 'assets/js/automatorwp-convertkit' . $suffix . '.js', array( 'jquery' ), AUTOMATORWP_CONVERTKIT_VER, true );
    
}
add_action( 'admin_init', 'automatorwp_convertkit_admin_register_scripts' );

/**
 * Enqueue admin scripts
 *
 * @since       1.0.0
 * @return      void
 */
function automatorwp_convertkit_admin_enqueue_scripts( $hook ) {

    // Stylesheets
    wp_enqueue_style( 'automatorwp-convertkit-css' );

    wp_localize_script( 'automatorwp-convertkit-js', 'automatorwp_convertkit', array(
        'nonce' => automatorwp_get_admin_nonce(),
    ) );

    wp_enqueue_script( 'automatorwp-convertkit-js' );

}
add_action( 'admin_enqueue_scripts', 'automatorwp_convertkit_admin_enqueue_scripts', 100 );