<?php
/**
 * Delete Post
 *
 * @package     AutomatorWP\Integrations\WordPress\Triggers\Delete_Post
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WordPress_Delete_Post extends AutomatorWP_Integration_Trigger {

    /**
     * Initialize the trigger
     *
     * @since 1.0.0
     */
    public function __construct( $integration ) {

        $this->integration = $integration;
        $this->trigger = $integration . '_delete_post';

        parent::__construct();

    }

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User deletes a post', 'automatorwp' ),
            'select_option'     => __( 'User deletes <strong>a post</strong>', 'automatorwp' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'User deletes a post %1$s time(s)', 'automatorwp' ), '{times}' ),
            'log_label'         => __( 'User deletes a post', 'automatorwp' ),
            'action'            => array(
                'trashed_post',
                'before_delete_post'
            ),
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_utilities_post_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $post_id The post ID
     * @param object|string $post_data
     */
    public function listener( $post_id = 0, $post_data = null ) {

        $post = get_post( $post_id );

        if ( ! $post ) {
            return;
        }

        if ( $post_data === null ) {
            return;
        }
       
        // Bail if not is a post
        if( $post->post_type !== 'post' ) {
            return;
        }
        
        // trashed_post hook returns string with previous status
        // before_delete_post hook returns WP_post with eliminated post data
        $post_status = ( is_object( $post_data ) ) ? $post_data->post_status : $post_data;

        if( ! in_array( $post_status, array( 'publish', 'private' ) ) ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $post->post_author,
            'post_id' => $post->ID,
        ) );

    }

}

new AutomatorWP_WordPress_Delete_Post( 'wordpress' );
new AutomatorWP_WordPress_Delete_Post( 'posts' );