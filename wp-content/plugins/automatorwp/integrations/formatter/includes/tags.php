<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Formatter\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// GLOBAL TAGS

/**
 * Calculator tags
 *
 * @since 1.0.0
 *
 * @param array $tags The tags
 *
 * @return array
 */
function automatorwp_formatter_get_tags( $tags ) {

    $tags['formatter'] = array(
        'label' => 'Formatter',
        'tags'  => array(),
        'icon'  => AUTOMATORWP_FORMATTER_URL . 'assets/formatter.svg',
    );

    $tags['formatter']['tags']['floor( VALUE )'] = array(
        'label'     => __( 'Floor', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'Rounds a number to the next lowest integer value (eg: 4.3 to 4), replace "VALUE" by the value of your choice.', 'automatorwp' ),
    );

    $tags['formatter']['tags']['ceil( VALUE )'] = array(
        'label'     => __( 'Ceil', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'Rounds a number to the next highest integer value (eg: 4.3 to 5), replace "VALUE" by the value of your choice.', 'automatorwp' ),
    );

    $tags['formatter']['tags']['lowercase( VALUE )'] = array(
        'label'     => __( 'Lowercase', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'Format to lowercase, replace "VALUE" by the value of your choice.', 'automatorwp' ),
    );

    $tags['formatter']['tags']['uppercase( VALUE )'] = array(
        'label'     => __( 'Uppercase', 'automatorwp' ),
        'type'      => 'text',
        'preview'   => __( 'Format to uppercase, replace "VALUE" by the value of your choice.', 'automatorwp' ),
    );

    return $tags;

}
add_filter( 'automatorwp_get_tags', 'automatorwp_formatter_get_tags' );

/**
 * Calculator tags names
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_formatter_get_tags_names() {

    $tags = automatorwp_formatter_get_tags( array() );
    $tags_names = array();

    foreach( $tags['formatter']['tags'] as $tag => $args ) {
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
function automatorwp_formatter_skip_tag_replacement( $skip, $tag_name, $automation_id, $user_id, $content ) {

    foreach( automatorwp_formatter_get_tags_names() as $tag ) {
        if( automatorwp_starts_with( $tag_name, $tag . '(' ) ) {
            return true;
        }
    }

    return $skip;

}
add_filter( 'automatorwp_skip_tag_replacement', 'automatorwp_formatter_skip_tag_replacement', 10, 5 );

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
function automatorwp_formatter_post_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $replacements = array();

    if( empty( $parsed_content ) ) {
        return $parsed_content;
    }

    // Get the functions
    preg_match_all( "/\{\s*(.*?)\s*\(\s*(.*)\s*\)\s*\}/", $parsed_content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $i => $function ) {
            // Skip if not is our function
            if( ! in_array( $function, automatorwp_formatter_get_tags_names() ) ) {
                continue;
            }

            $parsed_params = automatorwp_parse_automation_tags( $automation_id, $user_id, $matches[2][$i] );
            $params = automatorwp_get_tag_parameters_to_array( $function, $parsed_params );
            $value = $params[0];

            switch ( $function ) {
                case 'floor':
                    $value = floor( (float) $value );
                    break;
                case 'ceil':
                    $value = ceil( (float) $value );
                    break;
                case 'lowercase':
                    $value = strtolower( $value );
                    break;
                case 'uppercase':
                    $value = strtoupper( $value );
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
add_filter( 'automatorwp_post_parse_automation_tags', 'automatorwp_formatter_post_parse_automation_tags', 10, 5 );

// ACTION TAGS

/**
 * Formatted string tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_formatter_get_actions_string_tags() {

    return array(
        'formatted' => array(
            'label'     => __( 'Formatted', 'automatorwp-pro' ),
            'type'      => 'text',
            'preview'   => 'The formatted value',
        ),
    );

}

/**
 * Custom action formatted string tag replacement
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
function automatorwp_formatter_get_action_string_tag_replacement( $replacement, $tag_name, $action, $user_id, $content, $log ) {


    $action_args = automatorwp_get_action( $action->type );

    // Skip if action is not from this integration
    if( $action_args['integration'] !== 'formatter' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'formatted':
            $replacement = automatorwp_get_log_meta( $log->id, 'formatted', true );
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_formatter_get_action_string_tag_replacement', 10, 6 );
