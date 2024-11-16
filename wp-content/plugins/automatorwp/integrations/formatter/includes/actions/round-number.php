<?php
/**
 * Round Number
 *
 * @package     AutomatorWP\Integrations\Formatter\Actions\Round_Number
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Formatter_Round_Number extends AutomatorWP_Integration_Action {

    public $integration = 'formatter';
    public $action = 'formatter_round_number';
    public $formatted = '';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Round number into format', 'automatorwp' ),
            'select_option'     => __( 'Round <strong>number</strong> into <strong>format</strong>', 'automatorwp' ),
            /* translators: %1$s: Number. %2$s: Format. */
            'edit_label'        => sprintf( __( 'Round %1$s into %2$s', 'automatorwp' ), '{number}', '{format}' ),
            /* translators: %1$s: Number. %2$s: Format. */
            'log_label'         => sprintf( __( 'Round %1$s into %2$s', 'automatorwp' ), '{number}', '{format}' ),
            'options'           => array(
                'number' => array(
                    'from'  => 'number',
                    'default' => __( 'number', 'automatorwp' ),
                    'fields' => array(
                        'number' => array(
                            'name' => __( 'Number:', 'automatorwp' ),
                            'desc' => __( 'Number to round.', 'automatorwp' ),
                            'type' => 'text',
                            'required' => true,
                            'default' => ''
                        ),
                     )
                ),
                'format' => array(
                    'from'  => 'format',
                    'default' => __( 'format', 'automatorwp' ),
                    'fields' => array(
                        'format' => array(
                            'name' => __( 'Format:', 'automatorwp' ),
                            'type' => 'select',
                            'classes' => 'automatorwp-selector',
                            'options_cb' => 'automatorwp_formatter_get_number_formats',
                            'attributes' => array(
                                'data-placeholder'       => __( 'Select a format', 'automatorwp' ),
                            ),
                            'default' => '',
                        ),
                    )
                ),
            ),
            'tags'  => automatorwp_formatter_get_actions_string_tags()
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $number = (float)$action_options['number'];
        $format = $action_options['format'];

        // Bail if not number
        if ( empty( $number ) ) {
            $this->result = __( 'Please, insert a number to be rounded', 'automatorwp' );
            return;
        }

        // Bail if not position
        if ( empty( $format ) ) {
            $this->result = __( 'Please, select a format', 'automatorwp' );
            return;
        }

        $this->formatted = automatorwp_formatter_get_formatted_number( $format, $number );

        $this->result = sprintf( __( '%s', 'automatorwp' ), $this->formatted );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store the action's result
        $log_meta['result'] = $this->result;
        $log_meta['formatted'] = ( isset( $this->formatted ) ? $this->formatted : '' );

        return $log_meta;
    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an action
        if( $log->type !== 'action' ) {
            return $log_fields;
        }

        // Bail if action type don't match this action
        if( $object->type !== $this->action ) {
            return $log_fields;
        }

        $log_fields['result'] = array(
            'name' => __( 'Result:', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;
    }

}

new AutomatorWP_Formatter_Round_Number();