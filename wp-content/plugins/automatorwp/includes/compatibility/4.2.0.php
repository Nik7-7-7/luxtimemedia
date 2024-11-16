<?php
/**
 * AutomatorWP 4.2.0 compatibility functions
 *
 * @package     AutomatorWP\4.2.0
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       4.2.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the trigger tags replacements
 *
 * @since 1.0.0
 *
 * @param stdClass  $trigger    The trigger object
 * @param int       $user_id    The user ID
 * @param string    $content    The content to parse
 *
 * @return array
 */
function automatorwp_get_trigger_tags_replacements_old( $trigger, $user_id, $content = '' ) {

    // Get the last completion log for this trigger (where data for tags replacement is)
    $log = automatorwp_get_trigger_last_completion_log( $trigger, $user_id, $content );
    
    if( ! $log ) {
        return array();
    }

    ct_setup_table( 'automatorwp_logs' );

    $replacements = array();
   
    // Look for trigger tags
    preg_match_all( "/\{" . $trigger->id . ":\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $tag_name ) {

            $replacements[$tag_name] = automatorwp_get_trigger_tag_replacement( $tag_name, $trigger, $user_id, $content, $log );

        }

    }
    
    return $replacements;

}

/**
 * Get the post meta tags replacements
 *
 * @since 1.1.0
 *
 * @param int       $trigger_id The trigger ID
 * @param int       $post_id    The post ID
 * @param string    $content    The content to parse
 *
 * @return array
 */
function automatorwp_get_post_meta_tags_replacements_old( $trigger_id = 0, $post_id = 0, $content = '' ) {

    $replacements = array();

    // Look for post meta tags
    preg_match_all( "/\{" . $trigger_id . ":post_meta:\s*(.*?)\s*\}/", $content, $matches );

    if( is_array( $matches ) && isset( $matches[1] ) ) {

        foreach( $matches[1] as $meta_key ) {
            // Replace {ID:post_meta:KEY} by the post meta value
            $replacements['{' . $trigger_id . ':post_meta:' . $meta_key . '}'] = get_post_meta( $post_id, $meta_key, true );
        }

    }

    /**
     * Filter to set custom post meta tags replacements
     *
     * @since 1.1.0
     *
     * @param array     $replacements   Replacements
     * @param int       $post_id        The post ID
     * @param string    $content        The content to parse
     *
     * @return array
     */
    return apply_filters( 'automatorwp_get_post_meta_tags_replacements_old', $replacements, $post_id, $content );

}
