<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\ConvertKit\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * AJAX handler for the authorize action
 *
 * @since 1.0.0
 */
function automatorwp_convertkit_ajax_authorize() {
    // Security check
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    $prefix = 'automatorwp_convertkit_';

    $key = sanitize_text_field( $_POST['key'] );
    $secret = sanitize_text_field( $_POST['secret'] );
   
    // Check parameters given
    if( empty( $key ) || empty( $secret ) ) {
        wp_send_json_error( array( 'message' => __( 'All fields are required to connect with ConvertKit', 'automatorwp-convertkit' ) ) );
        return;
    }

    $status_secret = automatorwp_convertkit_check_api_secret( $secret );
    $status_key = automatorwp_convertkit_check_api_key( $key );

    if ( empty( $status_secret ) || empty ( $status_key ) ) {
        return;
    }

    $settings = get_option( 'automatorwp_settings' );

    // Save API key and API secret
    $settings[$prefix . 'key'] = $key;
    $settings[$prefix . 'secret'] = $secret;

    // Update settings
    update_option( 'automatorwp_settings', $settings );
    $admin_url = str_replace( 'http://', 'http://', get_admin_url() )  . 'admin.php?page=automatorwp_settings&tab=opt-tab-convertkit';
   
    wp_send_json_success( array(
        'message' => __( 'Correct data to connect with ConvertKit', 'automatorwp-convertkit' ),
        'redirect_url' => $admin_url
    ) );

}
add_action( 'wp_ajax_automatorwp_convertkit_authorize',  'automatorwp_convertkit_ajax_authorize' );

/**
 * Ajax function for selecting forms
 *
 * @since 1.0.0
 */
function automatorwp_convertkit_ajax_get_forms() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $forms = automatorwp_convertkit_get_forms( );

    $results = array();

    // Parse form results to match select2 results
    foreach ( $forms as $form ) {

        $results[] = array(
            'id' => $form['id'],
            'text' => $form['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_convertkit_get_forms', 'automatorwp_convertkit_ajax_get_forms' );

/**
 * Ajax function for selecting sequences
 *
 * @since 1.0.0
 */
function automatorwp_convertkit_ajax_get_sequences() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $sequences = automatorwp_convertkit_get_sequences( );

    $results = array();

    // Parse sequence results to match select2 results
    foreach ( $sequences as $sequence ) {

        $results[] = array(
            'id' => $sequence['id'],
            'text' => $sequence['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_convertkit_get_sequences', 'automatorwp_convertkit_ajax_get_sequences' );

/**
 * Ajax function for selecting tags
 *
 * @since 1.0.0
 */
function automatorwp_convertkit_ajax_get_tags() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $tags = automatorwp_convertkit_get_tags( );

    $results = array();

    // Parse tag results to match select2 results
    foreach ( $tags as $tag ) {

        $results[] = array(
            'id' => $tag['id'],
            'text' => $tag['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_convertkit_get_tags', 'automatorwp_convertkit_ajax_get_tags' );