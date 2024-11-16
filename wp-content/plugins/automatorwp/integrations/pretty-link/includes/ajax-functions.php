<?php
/**
 * Ajax Functions
 *
 * @package     AutomatorWP\pretty-link\Ajax_Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Ajax function for selecting groups
 *
 * @since 1.0.0
 */
function automatorwp_pretty_link_ajax_get_links() {
    // Security check, forces to die if not security passed
    check_ajax_referer( 'automatorwp_admin', 'nonce' );

    global $wpdb;

    // Pull back the search string
    $search = isset( $_REQUEST['q'] ) ? $wpdb->esc_like( sanitize_text_field( $_REQUEST['q'] ) ) : '';

    $links = automatorwp_pretty_link_get_links();

    foreach ( $links as $link ) {

        if( ! empty( $search ) ) {
            if( strpos( strtolower( $link['name'] ), strtolower( $search ) ) === false ) {
                continue;
            }
        }
        
        $results[] = array(
            'id' => $link['id'],
            'text' => $link['name']
        );
    }

    // Prepend option none
    $results = automatorwp_ajax_parse_extra_options( $results );

    // Return our results
    wp_send_json_success( $results );
    die;
}
add_action( 'wp_ajax_automatorwp_pretty_link_get_links', 'automatorwp_pretty_link_ajax_get_links' );