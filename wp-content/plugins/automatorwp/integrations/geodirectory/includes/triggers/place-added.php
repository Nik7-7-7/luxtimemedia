<?php

/**
 * Place Added
 *
 * @package     AutomatorWP\Integrations\GeoDirectory\Triggers\Place_Added
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_GeoDirectory_Place_Added extends AutomatorWP_Integration_Trigger
{

    public $integration = 'geodirectory';
    public $trigger = 'geodirectory_place_added';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {

        automatorwp_register_trigger($this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'User adds a new place', 'automatorwp' ),
            'select_option'     => __( 'User adds a new <strong>place</strong>', 'automatorwp' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'User adds a new place %1$s time(s)', 'automatorwp' ), '{times}' ),
            'log_label'         => __( 'User adds a new place', 'automatorwp' ),
            'action'            => 'geodir_post_published',
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
        ));
    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array $data
     */
    public function listener($gd_post, $data)
    {
        // Bail if place id is not provided
        if ( empty( $data ) ) {
            return;
        }

        $user_id = get_current_user_id();

        // Bail if user is not logged
        if ($user_id === 0) {
            return;
        }

        $post_id = $gd_post->ID;

        automatorwp_trigger_event(array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'post_id'       => $post_id
        ));
    }

}

new AutomatorWP_GeoDirectory_Place_Added();
