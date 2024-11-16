<?php
/**
 * Submit Form
 *
 * @package     AutomatorWP\Integrations\QSM\Triggers\Submit_Form
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_QSM_Anonymous_Submit_Quiz extends AutomatorWP_Integration_Trigger {

    public $integration = 'qsm';
    public $trigger = 'qsm_anonymous_quiz_submitted';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Guest submits a quiz', 'automatorwp' ),
            'select_option'     => __( 'Guest submits <strong>a quiz</strong>', 'automatorwp' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'Guest submits %1$s %2$s time(s)', 'automatorwp' ), '{post}', '{times}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'Guest submits %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'qsm_quiz_submitted',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 4,
            'options'           => array(
                'post' => automatorwp_utilities_post_option( array(
                    'name'              => __('Quiz:', 'automatorwp'),
                    'option_none_label' => __('any quiz', 'automatorwp'),
                    'post_type'         => 'qsm_quiz'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_qsm_get_quiz_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param $results_array
     * @param $results_id
     * @param $qmn_quiz_options
     * @param $qmn_array_for_variables
     */
    public function listener( $results_array, $results_id, $qmn_quiz_options, $qmn_array_for_variables ) {

        
        $user_id = get_current_user_id();
        $quiz_id = $qmn_quiz_options->quiz_id;

        // Bail if user is logged in
        if ( $user_id !== 0 ) {
            return;
        }

        // Bail if not all details provided
        if ( empty( $quiz_id ) ) {
            return;
        }

        // Get quiz post ID
        $post_id = automatorwp_qsm_get_quiz_post_id( $quiz_id );

        // Bail if no post id
        if ( empty( $post_id ) ) {
            return;
        }

        // Quiz name for tags
        $quiz_name = $qmn_quiz_options->quiz_name;
        $points = $qmn_array_for_variables['total_correct'];

        // Trigger submit form event
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'post_id'       => $post_id[0],
            'quiz_id'       => $quiz_id,
            'quiz_name'     => $quiz_name,
            'points'        => $points,
        ) );

    }

    /**
     * Anonymous deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if anonymous deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                         True if anonymous deserves trigger, false otherwise
     */
    public function anonymous_deserves_trigger( $deserves_trigger, $trigger, $event, $trigger_options, $automation ) {

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

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_anonymous_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['quiz_id'] = ( isset( $event['quiz_id'] ) ? $event['quiz_id'] : '' );
        $log_meta['quiz_name'] = ( isset( $event['quiz_name'] ) ? $event['quiz_name'] : '' );
        $log_meta['points'] = ( isset( $event['points'] ) ? $event['points'] : '' );

        return $log_meta;

    }

}

new AutomatorWP_QSM_Anonymous_Submit_Quiz();