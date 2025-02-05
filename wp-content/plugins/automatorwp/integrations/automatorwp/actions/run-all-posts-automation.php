<?php
/**
 * Run All Posts Automation
 *
 * @package     AutomatorWP\Integrations\WordPress\Actions\Run_All_Posts_Automation
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WordPress_Run_All_Posts_Automation extends AutomatorWP_Integration_Action {

    /**
     * Initialize the action
     *
     * @since 1.0.0
     */
    public function __construct( $integration ) {

        $this->integration = $integration;
        $this->action = $integration . '_run_all_posts_automation';

        parent::__construct();

    }

    /**
     * The action result
     *
     * @since 1.0.0
     *
     * @var string $result
     */
    public $result = '';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Run all posts automation', 'automatorwp' ),
            'select_option'     => __( 'Run <strong>all posts</strong> automation', 'automatorwp' ),
            /* translators: %1$s: Hook name. */
            'edit_label'        => sprintf( __( 'Run %1$s automation', 'automatorwp' ), '{automation}' ),
            /* translators: %1$s: Hook name */
            'log_label'         => sprintf( __( 'Run %1$s automation', 'automatorwp' ), '{automation}' ),
            'options'           => array(
                'automation' => automatorwp_utilities_automation_option( array(
                    'option_none_label' => __( 'any', 'automatorwp' ),
                    'option_none_value' => 'all-posts',
                    'default' => 'all-posts',
                ) ),
            ),
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
        $automation_id = absint( $action_options['automation'] );

        // Bail if empty automation ID
        if( $automation_id === 0 || $action_options['automation'] === 'all-posts' ) {
            $this->result = __( 'No automation selected.', 'automatorwp' );
            return;
        }

        $automation = automatorwp_get_automation_object( $automation_id );

        // Bail if not automation found
        if( ! $automation ) {
            $this->result = __( 'Automation not found.', 'automatorwp' );
            return;
        }

        // Bail if automation is not an all-posts automation
        if(  $automation->type !== 'all-posts' ) {
            $this->result = __( 'Automation selected is not an all posts automation.', 'automatorwp' );
            return;
        }

        // Run the automation (automation will get called itself every 60 seconds)
        $result = automatorwp_run_automation( $automation->id );

        if( $result ) {
            $this->result = __( 'Automation executed successfully.', 'automatorwp' );
        } else {
            $this->result = automatorwp_get_run_automation_error();
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Get objects query args
        add_filter( 'automatorwp_ajax_get_objects_query_args', array( $this, 'get_objects_query_args' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    public function get_objects_query_args( $ct_query_args, $ct_table ) {

        if( $ct_table->name !== 'automatorwp_automations' ) {
            return $ct_query_args;
        }

        if( isset( $_REQUEST['option_none_value'] ) && $_REQUEST['option_none_value'] === 'all-posts' ) {
            $ct_query_args['type'] = 'all-posts';
        }

        return $ct_query_args;

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

        $log_meta['result'] = $this->result;

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

new AutomatorWP_WordPress_Run_All_Posts_Automation( 'automatorwp' );
new AutomatorWP_WordPress_Run_All_Posts_Automation( 'run_now' );