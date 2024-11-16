<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\Integrations\WordPress\Ajax_Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting lists
 *
 * @since 1.0.0
 */
function automatorwp_wordpress_ajax_get_terms() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $terms = automatorwp_wordpress_get_terms();
    
    $results = array();

    // Parse terms results to match select2 results
    foreach ( $terms as $term ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $term['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }

        $results[] = array(
            'id'   => $term['id'],
            'text' => $term['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;

}
add_action( 'wp_ajax_automatorwp_wordpress_get_terms', 'automatorwp_wordpress_ajax_get_terms' );