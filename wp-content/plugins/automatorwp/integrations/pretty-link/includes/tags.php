<?php
/**
 * Tags
 *
 * @package     AutomatorWP\Pretty-Link\Tags
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Link tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_pretty_link_get_link_tags() {
    
    return array(
        'link_name' => array(
            'label' => __('Pretty Link name', 'automatorwp-pro'),
            'type' => 'text',
            'preview' => 'The pretty link name'
        ),
        'link_redirection_method' => array(
            'label' => __('Redirection method', 'automatorwp-pro'),
            'type' => 'text',
            'preview' => 'The redirection Method'
        ),
        'link_target' => array(
            'label' => __('Pretty Link target', 'automatorwp-pro'),
            'type' => 'text',
            'preview' => 'Pretty link target link'
        ),
        'link_slug' => array(
            'label' => __('Pretty Link', 'automatorwp-pro'),
            'type' => 'text',
            'preview' => 'Pretty Link'
        )
        );
}

/**
 * Custom trigger tag replacement
 *
 * @since 1.0.0
 *
 * @param string    $replacement    The tag replacement
 * @param string    $tag_name       The tag name (without "{}")
 * @param stdClass  $trigger        The trigger object
 * @param int       $user_id        The user ID
 * @param string    $content        The content to parse
 * @param stdClass  $log            The last trigger log object
 *
 * @return string
 */
function automatorwp_pretty_link_get_trigger_link_tag_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {
    
    $trigger_args = automatorwp_get_trigger( $trigger->type );

    // Skip if trigger is not from this integration
    if( $trigger_args['integration'] !== 'pretty_link' ) {
        return $replacement;
    }


    switch($tag_name) {
        case 'link_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'link_name', true );
            break;
        case 'link_redirection_method':
            $replacement = automatorwp_get_log_meta( $log->id, 'link_redirection_method', true);
            break;
        case 'link_target':
            $replacement = automatorwp_get_log_meta( $log->id, 'link_target', true);
            break;
        case 'link_slug':
            $replacement = automatorwp_get_log_meta( $log->id, 'link_slug', true);
            break;
    }
    return $replacement;
}
add_filter( 'automatorwp_get_trigger_tag_replacement', 'automatorwp_pretty_link_get_trigger_link_tag_replacement', 10, 6 );