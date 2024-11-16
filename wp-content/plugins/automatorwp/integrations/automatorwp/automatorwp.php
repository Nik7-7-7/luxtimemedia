<?php
/**
 * AutomatorWP
 *
 * @package     AutomatorWP\Integrations\AutomatorWP
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Triggers
require_once plugin_dir_path( __FILE__ ) . 'triggers/complete-automation.php';
require_once plugin_dir_path( __FILE__ ) . 'triggers/user-created.php';
require_once plugin_dir_path( __FILE__ ) . 'triggers/post-created.php';
require_once plugin_dir_path( __FILE__ ) . 'triggers/all-users.php';
require_once plugin_dir_path( __FILE__ ) . 'triggers/all-posts.php';
// Actions
require_once plugin_dir_path( __FILE__ ) . 'actions/anonymous-user.php';
require_once plugin_dir_path( __FILE__ ) . 'actions/redirect-user.php';
require_once plugin_dir_path( __FILE__ ) . 'actions/call-function.php';
require_once plugin_dir_path( __FILE__ ) . 'actions/do-action.php';
require_once plugin_dir_path( __FILE__ ) . 'actions/run-all-users-automation.php';
require_once plugin_dir_path( __FILE__ ) . 'actions/run-all-posts-automation.php';
// Filters
require_once plugin_dir_path( __FILE__ ) . 'filters/flat-condition.php';

/**
 * Registers this integration
 *
 * @since 1.0.0
 */
function automatorwp_register_automatorwp_integration() {

    automatorwp_register_integration( 'automatorwp', array(
        'label' => 'AutomatorWP',
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/automatorwp.svg',
    ) );

    // Groups
    automatorwp_register_integration( 'code', array(
        'label' => __( 'Code', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/code.svg',
    ) );

    automatorwp_register_integration( 'comments', array(
        'label' => __( 'Comments', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/comments.svg',
    ) );

    automatorwp_register_integration( 'emails', array(
        'label' => __( 'Emails', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/emails.svg',
    ) );

    automatorwp_register_integration( 'posts', array(
        'label' => __( 'Posts', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/posts.svg',
    ) );

    automatorwp_register_integration( 'redirect', array(
        'label' => __( 'Redirect', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/redirect.svg',
    ) );

    automatorwp_register_integration( 'users', array(
        'label' => __( 'Users', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/users.svg',
    ) );

    automatorwp_register_integration( 'button', array(
        'label' => __( 'Button', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/button.svg',
    ) );

    automatorwp_register_integration( 'link', array(
        'label' => __( 'Link', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/link.svg',
    ) );

    automatorwp_register_integration( 'multimedia_content', array(
        'label' => __( 'Multimedia Content', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/multimedia-content.svg',
    ) );

    automatorwp_register_integration( 'run_now', array(
        'label' => __( 'Run Now', 'automatorwp' ),
        'icon'  => plugin_dir_url( __FILE__ ) . 'assets/run-now.svg',
    ) );

}
add_action( 'automatorwp_init', 'automatorwp_register_automatorwp_integration', 1 );