<?php
/**
 * View Post Tag
 *
 * @package     AutomatorWP\Integrations\WordPress\Triggers\View_Post_Tag
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WordPress_View_Post_Tag extends AutomatorWP_Integration_Trigger {

    /**
     * Initialize the trigger
     *
     * @since 1.0.0
     */
    public function __construct( $integration ) {

        $this->integration = $integration;
        $this->trigger = $integration . '_view_post_tag';

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
            'label'             => __( 'User views a post of a tag', 'automatorwp' ),
            'select_option'     => __( 'User views a post of <strong>a tag</strong>', 'automatorwp' ),
            /* translators: %1$s: Term title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'User views a post of %1$s %2$s time(s)', 'automatorwp' ), '{term}', '{times}' ),
            /* translators: %1$s: Term title. */
            'log_label'         => sprintf( __( 'User views a post of %1$s', 'automatorwp' ), '{term}' ),
            'action'            => 'template_redirect',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'term' => automatorwp_utilities_term_option( array(
                    'name'              => __( 'Tag:', 'automatorwp' ),
                    'option_none_label' => __( 'any tag', 'automatorwp' ),
                    'taxonomy'          => 'post_tag',
                ) ),
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
     */
    public function listener() {

        global $post;

        // Bail if in admin area
        if( is_admin() ) {
            return;
        }

        // Bail if not post instanced
        if( ! $post instanceof WP_Post ) {
            return;
        }

        // Bail if post type is not a post
        if( $post->post_type !== 'post' ) {
            return;
        }

        $terms_ids = automatorwp_get_term_ids( $post->ID, 'post_tag' );

        // Bail if post isn't assigned to any tag
        if( empty( $terms_ids ) ) {
            return;
        }

        $user_id = get_current_user_id();

        // Bail if user is not logged in
        if( $user_id === 0 ) {
            return;
        }

        automatorwp_trigger_event( array(
            'trigger'   => $this->trigger,
            'user_id'   => $user_id,
            'post_id'   => $post->ID,
            'terms_ids' => $terms_ids,
        ) );

    }

    /**
     * User deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if user deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                          True if user deserves trigger, false otherwise
     */
    public function user_deserves_trigger( $deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Don't deserve if post and terms IDs are not received
        if( ! isset( $event['post_id'] ) && ! isset( $event['terms_ids'] ) ) {
            return false;
        }

        // Don't deserve if term doesn't match with the trigger option
        if( ! automatorwp_terms_matches( $event['terms_ids'], $trigger_options['term'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

}

new AutomatorWP_WordPress_View_Post_Tag( 'wordpress' );
new AutomatorWP_WordPress_View_Post_Tag( 'posts' );