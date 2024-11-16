<?php
/**
 * Functions
 *
 * @package     AutomatorWP\pretty-link\Functions
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the links
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_pretty_link_get_links() {
    $links = array();
    $linksController = new PrliLocalApiController();
    $all_links = $linksController->get_all_links();

    foreach ($all_links as $link) {
        $links[] = array(
            'id' => $link['id'],
            'name' => $link['name'],
        );
    }
    return $links;
}

/**
 * Options callback for select fields assigned to links
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_pretty_link_options_cb_link( $field ) {
    
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any link', 'automatorwp');
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label);

    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }
    }

    foreach( $value as $link_id ) {

        // Skip option none
        if( $link_id === $none_value) {
            continue;
        }

        $options[$link_id] = automatorwp_pretty_link_get_link_title( $link_id );
    }

    return $options;
}

/**
 * Get link title of specified link id
 *
 * @since 1.0.0
 *
 * @param int $link_id
 *
 * @return array
 */
function automatorwp_pretty_link_get_link_title( $link_id ) {

    // Empty title if no ID provided
    if( absint( $link_id ) === 0 ) {
        return '';
    }

    $prliLink = new PrliLink();
    $one_link = $prliLink->get_one_by( 'id', $link_id );
    $link_data = json_decode(json_encode($one_link), true);
    $link_name = $link_data['name'];
    
    return $link_name;

}

/**
 * Get link title of specified link id
 *
 * @since 1.0.0
 *
 * @param int $link_id
 *
 * @return array
 */
function automatorwp_pretty_link_get_link_data( $link_id ) {

    // Empty title if no ID provided
    if( absint( $link_id ) === 0 ) {
        return '';
    }
    $prliLink = new PrliLink();
    $one_link = $prliLink->get_one_by( 'id', $link_id );
    $link_data = json_decode(json_encode($one_link), true);
    return $link_data;

}
/**
 * Generate random slug in case the field is empty
 *
 * @since 1.0.0
 *
 * @return String
 */
function automatorwp_pretty_link_generate_random_slug() {
    $prliLink = new PrliLink();
    return $prliLink->generateValidSlug();
}

/**
 * Options callback for select redirection types of pretty links
 *
 * @since 1.0.0
 *
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_pretty_link_get_all_redirection_options_cb( $field ) {
    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any points type', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    return array_merge(
        $options,
        array(
            '307' => '307 (Temporary)',
            '302' => '302 (Temporary)',
            '301' => '301 (Permanent)'
        )
    );
}