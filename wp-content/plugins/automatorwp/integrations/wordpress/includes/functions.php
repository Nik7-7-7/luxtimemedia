<?php
/**
 * Functions
 *
 * @package     AutomatorWP\WordPress\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get terms
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_wordpress_options_cb_term( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any term', 'automatorwp' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $term_id ) {

            // Skip option none
            if( $term_id === $none_value ) {
                continue;
            }

            $options[$term_id] = automatorwp_wordpress_get_term_name( $term_id );
        }
    }

    return $options;

}

/**
* Get the term name
*
* @since 1.0.0
* 
* @param string $term_id
*
* @return array
*/
function automatorwp_wordpress_get_term_name( $term_id ) {

    // Empty title if no ID provided
    if( absint( $term_id ) === 0 ) {
        return '';
    }

    $term = get_term( $term_id );
    $term_name = $term->name . ' (' . $term->taxonomy . ')';

    return $term_name;

}



/**
* Get the lists/audiences
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_wordpress_get_terms() {

    $terms = array();

    $taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
    
    foreach ($taxonomies as $taxonomy) {
    
        $terms_taxonomy = get_terms(array(
            'taxonomy' => $taxonomy->name,
            'hide_empty' => false,
        ));

        // Añadir términos al array
        foreach ($terms_taxonomy as $term) {
            $terms[] = array(
                'id' => $term->term_id,
                'name' => $term->name . ' (' . $term->taxonomy . ')',
            );
        }
    }

    return $terms;
}