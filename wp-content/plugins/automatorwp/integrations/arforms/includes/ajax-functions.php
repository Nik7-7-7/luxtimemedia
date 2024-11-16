<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\ARForms\includes\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting forms
 *
 * @since 1.0.0
 */
function automatorwp_arforms_ajax_get_forms() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );
    
    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $forms = automatorwp_arforms_get_forms( );

    $results = array();

    // Parse form results to match select2 results
    foreach ( $forms as $form ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $service['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }
        
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
add_action( 'wp_ajax_automatorwp_arforms_get_forms', 'automatorwp_arforms_ajax_get_forms' );