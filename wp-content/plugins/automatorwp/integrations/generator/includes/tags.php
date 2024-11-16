<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Generator\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// GLOBAL TAGS

/**
 * Generator tags
 *
 * @since 1.0.0
 *
 * @param array $tags The tags
 *
 * @return array
 */
function automatorwp_generator_get_tags( $tags ) {

    $tags['generator'] = array(
        'label' => 'Generator',
        'tags'  => array(),
        'icon'  => AUTOMATORWP_GENERATOR_URL . 'assets/generator.svg',
    );

    $tags['generator']['tags']['generate_hash( VALUE : ALGORITHM )'] = array(
        'label'     => __( 'Hash', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'Generate a hash from the given value, replace "VALUE" by the value of your choice. Replace "ALGORITHM" by the algorithm of your choice. By default MD5.', 'automatorwp' ),
    );

    $tags['generator']['tags']['generate_nonce( ACTION )'] = array(
        'label'     => __( 'Nonce', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'Generates a WordPress nonce, replace "ACTION" by the action name of your choice.', 'automatorwp' ),
    );

    return $tags;

}
add_filter( 'automatorwp_get_tags', 'automatorwp_generator_get_tags' );

/**
 * Calculator tags names
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_generator_get_tags_names() {

    $tags = automatorwp_generator_get_tags( array() );
    $tags_names = array();

    foreach( $tags['generator']['tags'] as $tag => $args ) {
        $tags_names[] = explode( '(', $tag )[0];
    }

    return $tags_names;

}

/**
 * Skip tags replacements
 *
 * @since 1.0.0
 *
 * @param bool      $skip           If tag should get skipped, by default false
 * @param string    $tag_name       The tag name (without "{}")
 * @param int       $automation_id  The automation ID
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 *
 * @return bool
 */
function automatorwp_generator_skip_tag_replacement( $skip, $tag_name, $automation_id, $user_id, $content ) {

    foreach( automatorwp_generator_get_tags_names() as $tag ) {
        if( automatorwp_starts_with( $tag_name, $tag . '(' ) ) {
            return true;
        }
    }

    return $skip;

}
add_filter( 'automatorwp_skip_tag_replacement', 'automatorwp_generator_skip_tag_replacement', 10, 5 );

/**
 * Parse tags
 *
 * @since 1.0.0
 *
 * @param string    $parsed_content     Content parsed
 * @param array     $replacements       Automation replacements
 * @param int       $automation_id      The automation ID
 * @param int       $user_id            The user ID
 * @param string    $content            The content to parse
 *
 * @return string
 */
function automatorwp_generator_post_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $replacements = array();

    if( empty( $parsed_content ) ) {
        return $parsed_content;
    }

    // Get the functions
    preg_match_all( "/\{\s*(.*?)\s*\(\s*(.*)\s*\)\s*\}/", $parsed_content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $i => $function ) {
            // Skip if not is our function
            if( ! in_array( $function, automatorwp_generator_get_tags_names() ) ) {
                continue;
            }

            $parsed_params = automatorwp_parse_automation_tags( $automation_id, $user_id, $matches[2][$i] );
            $params = automatorwp_get_tag_parameters_to_array( $function, $parsed_params );
            $value = $params[0];

            switch ( $function ) {
                case 'generate_hash':
                    $algorithm = isset( $params[1] ) ? strtolower( $params[1] ) : 'md5';

                    $all_algorithms = hash_algos();
                    $index = array_search( $algorithm, $all_algorithms );

                    if( $index === false ) {
                        $index = 2; // md5 index
                    }

                    $value = hash( $all_algorithms[$index], $value );
                    break;
                case 'generate_nonce':
                    $value = wp_create_nonce( $value );
                    break;
            }

            $replacements[$matches[0][$i]] = $value;
        }

    }

    if( count( $replacements ) > 0 ) {
        $tags = array_keys( $replacements );

        // Replace all tags by their replacements
        $parsed_content = str_replace( $tags, $replacements, $parsed_content );
    }

    return $parsed_content;
}
add_filter( 'automatorwp_post_parse_automation_tags', 'automatorwp_generator_post_parse_automation_tags', 10, 5 );

// ACTION TAGS

/**
 * Random hash tag
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_generator_get_actions_random_hash_tags() {

    return array(
        'random_hash' => array(
            'label'     => __( 'Random hash', 'automatorwp-pro' ),
            'type'      => 'text',
            'preview'   => 'random_hash',
        ),
    );

}

/**
 * Custom action random hash tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $action         The action object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last action log object
 *
 * @return string
 */
function automatorwp_generator_get_action_random_hash_tag_replacement( $replacement, $tag_name, $action, $user_id, $content, $log ) {


    $action_args = automatorwp_get_action( $action->type );

    // Skip if action is not from this integration
    if( $action_args['integration'] !== 'generator' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'random_hash':
            $replacement = automatorwp_get_log_meta( $log->id, 'random_hash', true );
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_generator_get_action_random_hash_tag_replacement', 10, 6 );

/**
 * Random nonce tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_generator_get_actions_random_nonce_tags() {

    return array(
        'random_nonce' => array(
            'label'     => __( 'Random nonce', 'automatorwp-pro' ),
            'type'      => 'text',
            'preview'   => 'random_nonce',
        ),
    );

}

/**
 * Custom action random nonce tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $action         The action object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last action log object
 *
 * @return string
 */
function automatorwp_generator_get_action_random_nonce_tag_replacement( $replacement, $tag_name, $action, $user_id, $content, $log ) {


    $action_args = automatorwp_get_action( $action->type );

    // Skip if action is not from this integration
    if( $action_args['integration'] !== 'generator' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'random_nonce':
            $replacement = automatorwp_get_log_meta( $log->id, 'random_nonce', true );
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_generator_get_action_random_nonce_tag_replacement', 10, 6 );