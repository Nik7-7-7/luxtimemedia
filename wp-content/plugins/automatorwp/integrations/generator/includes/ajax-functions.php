<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\FluentForm\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting algorithms
 *
 * @since 1.0.0
 */
function automatorwp_generator_ajax_get_algorithms() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( $_REQUEST['q'] ) : '';

    $results = array();

    // Get the algorithms
    $algorithms = hash_algos();

    foreach( $algorithms as $key => $value ) {
        $results[] = array(
            'id' => $key,
            'text' => $value,
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_get_ajax_results_option_none( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_generator_get_algorithms', 'automatorwp_generator_ajax_get_algorithms', 5 );