<?php

/**
 * Booking Cancelled
 *
 * @package     AutomatorWP\Integrations\BookingCalendar\Triggers\Booking_Cancelled
 * @author      AutomatorWP <contact@automatorwp.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_BookingCalendar_Booking_Cancelled extends AutomatorWP_Integration_Trigger {

    public $integration = 'wp_booking_calendar';
    public $trigger = 'wp_booking_calendar_booking_cancelled';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'A booking was cancelled', 'automatorwp' ),
            'select_option'     => __( 'A <strong>booking</strong> was cancelled', 'automatorwp' ),
            /* translators: %1$s: Booking ID. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'Booking %1$s was cancelled %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Booking ID. */
            'log_label'         => sprintf( __( 'Booking %1$s was cancelled', 'automatorwp' ), '{post}' ),
            'action'            => 'wpbc_move_booking_to_trash',
            'function'          => array( $this, 'listener'),
            'priority'          => 10,
            'accepted_args'     => 2,
            'options'           => array(
                'post' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'post',
                    'name'              => __( 'Booking:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any booking', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_wp_booking_calendar_get_bookings',
                    'options_cb'        => 'automatorwp_wp_booking_calendar_options_cb_booking',
                    'default'           => 'any'
                ) ),
                'times' => automatorwp_utilities_times_option()
            ),
            'tags' => array_merge(
                automatorwp_wp_booking_calendar_get_booking_tags(),
                automatorwp_utilities_times_tag()
            ),
        ) );
        
    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param array $params Booking parameters passed to the listener
     * @param array $action_result Result of the action
     */
    public function listener( $params, $action_result ) {

        $user_id = get_current_user_id();
        $booking_id = $params['booking_id'];
        $after_action_result = $action_result['after_action_result'];

        // Login is required
        if ( $user_id === 0 ) {
            return;
        }

        // Checks if the booking was successfully cancelled
        if ( $after_action_result ) {
            // Triggers the event
            automatorwp_trigger_event(array(
                'trigger'     => $this->trigger,
                'user_id'     => $user_id,
                'booking_id'  => $booking_id,
            ) );
        }

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

        // Don't deserve if post is not received
        if( ! isset( $event['booking_id'] ) ) {
            return false;
        }

        // Bail if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && absint( $event['booking_id'] ) !== absint( $trigger_options['post'] ) ) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['booking_id'] = ( isset( $event['booking_id'] ) ? $event['booking_id'] : '' );

        return $log_meta;

    }
}

new AutomatorWP_BookingCalendar_Booking_Cancelled();
