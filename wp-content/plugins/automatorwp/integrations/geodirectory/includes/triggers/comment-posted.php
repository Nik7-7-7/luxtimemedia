<?php

/**
 * Comment_Posted
 *
 * @package     AutomatorWP\Integrations\GeoDirectory\Triggers\Comment_Posted
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

class AutomatorWP_GeoDirectory_Comment_Posted extends AutomatorWP_Integration_Trigger
{

    public $integration = 'geodirectory';
    public $trigger = 'geodirectory_comment_posted';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register()
    {
        automatorwp_register_trigger($this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __('User reviews a place', 'automatorwp'),
            'select_option'     => __('User <strong>reviews</strong> a place', 'automatorwp'),
            /* translators: %1$s: Place name. %2$s: Number of times. */
            'edit_label'        => sprintf(__('User reviews %1$s %2$s time(s)', 'automatorwp'), '{post}', '{times}'),
            /* translators: %1$s: Place name. */
            'log_label'         => sprintf(__('User reviews %1$s', 'automatorwp'), '{post}'),
            'action'            => 'geodir_after_save_comment',
            'function'          => array($this, 'listener'),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __('Place:', 'automatorwp'),
                    'option_none_label' => __('any place', 'automatorwp'),
                    'post_type'         => 'gd_place'
                ) ),
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
     * @param array $review
     */
    public function listener( $review )
    {

        // Bail if place id is not provided
        if ( empty( $review ) ) {
            return;
        }

        //bail if user is not logged
        $user_id = get_current_user_id();

        if ( $user_id === 0 ) {
            return;
        }

        $post_id = $review['comment_post_ID'];

        automatorwp_trigger_event(array(
            'trigger'     => $this->trigger,
            'user_id'     => $user_id,
            'post_id'     => $post_id,
        ));

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
    public function user_deserves_trigger($deserves_trigger, $trigger, $user_id, $event, $trigger_options, $automation)
    {
        // Don't deserve if post is not received
        if ( !isset( $event['post_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( ! automatorwp_posts_matches( $event['post_id'], $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;
    }
}

new AutomatorWP_GeoDirectory_Comment_Posted();
