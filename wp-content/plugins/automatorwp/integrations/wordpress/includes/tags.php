<?php
/**
 * Tags
 *
 * @package     AutomatorWP\BuddyBoss\Tags
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Action user fields tags
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_wordpress_get_actions_user_tags() {

    return array(
        'user_id' => array(
            'label'     => __( 'User ID', 'automatorwp-buddyboss' ),
            'type'      => 'integer',
            'preview'   => '123',
        ),
        'user_login' => array(
            'label'     => __( 'Username', 'automatorwp-buddyboss' ),
            'type'      => 'string',
            'preview'   => 'Username',
        ),
        'first_name' => array(
            'label'     => __( 'First name', 'automatorwp-buddyboss' ),
            'type'      => 'string',
            'preview'   => 'First name',
        ),
        'last_name' => array(
            'label'     => __( 'Last name', 'automatorwp-buddyboss' ),
            'type'      => 'string',
            'preview'   => 'Last name',
        ),
        'user_email' => array(
            'label'     => __( 'User email', 'automatorwp-buddyboss' ),
            'type'      => 'string',
            'preview'   => 'contact@automatorwp.com',
        ),
    );

}

/**
 * Custom action profile_field tag replacement
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
function automatorwp_wordpress_get_actions_user_tags_replacement( $replacement, $tag_name, $action, $user_id, $content, $log ) {

    $action_args = automatorwp_get_action( $action->type );

    // Skip if trigger is not from this integration
    if( $action_args['integration'] !== 'wordpress' ) {
        return $replacement;
    }

    switch( $tag_name ) {
        case 'user_id':
            $replacement = automatorwp_get_log_meta( $log->id, 'user_id', true );
            break;
        case 'user_login':
            $replacement = automatorwp_get_log_meta( $log->id, 'user_login', true );
            break;
        case 'first_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'first_name', true );
            break;
        case 'last_name':
            $replacement = automatorwp_get_log_meta( $log->id, 'last_name', true );
            break;
        case 'user_email':
            $replacement = automatorwp_get_log_meta( $log->id, 'user_email', true );
            break;
    }

    return $replacement;

}
add_filter( 'automatorwp_get_action_tag_replacement', 'automatorwp_wordpress_get_actions_user_tags_replacement', 10, 6 );