<?php
/**
 * Add user form
 *
 * @package     AutomatorWP\Integrations\ConvertKit\Actions\Add_User_Form
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_ConvertKit_Add_User_Form extends AutomatorWP_Integration_Action {

    public $integration = 'convertkit';
    public $action = 'convertkit_add_user_form';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add user to form', 'automatorwp' ),
            'select_option'     => __( 'Add <strong>user</strong> to <strong>form</strong>', 'automatorwp' ),
            /* translators: %1$s: Form name. */
            'edit_label'        => sprintf( __( 'Add user to %1$s', 'automatorwp' ), '{form}' ),
            /* translators: %1$s: Form name. */
            'log_label'         => sprintf( __( 'Add user to %1$s', 'automatorwp' ), '{form}' ),
            'options'           => array(
                'form' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'form',
                    'option_default'    => __( 'Select a form', 'automatorwp-hubspot' ),
                    'name'              => __( 'Form:', 'automatorwp-hubspot' ),
                    'action_cb'         => 'automatorwp_convertkit_get_forms',
                    'options_cb'        => 'automatorwp_convertkit_options_cb_form',
                    'default'           => ''
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
        $form_id = $action_options['form'];        
        $user = get_user_by ( 'ID', $user_id );
        
        $this->result = '';

        // Bail if form is empty
        if ( empty ( $form_id ) ){
            return;
        }

        // Bail if ConvertKit not configured
        if( ! automatorwp_convertkit_get_api() ) {
            $this->result = __( 'ConvertKit integration not configured in AutomatorWP settings.', 'automatorwp' );
            return;
        }
       
        $status_code = automatorwp_convertkit_add_subscriber_form( $form_id, $user->user_email, $user->first_name );
        
        if ( !isset( $status_code ) || $status_code !== 200 ) {
            $this->result = __( 'Unexpected error. The subscriber has not been added.', 'automatorwp' );
            return;
        } 

        $form_name = automatorwp_convertkit_get_form_name( $form_id );

        $this->result = sprintf( __( 'An email was send to %s to confirm the subscription to %s', 'automatorwp' ), $user->user_email, $form_name );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();

    }

    /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     * @param stdClass  $object     The trigger/action object
     * @param string    $item_type  The object type (trigger|action)
     */
    public function configuration_notice( $object, $item_type ) {

        // Bail if action type don't match this action
        if( $item_type !== 'action' ) {
            return;
        }

        if( $object->type !== $this->action ) {
            return;
        }

        // Warn user if the authorization has not been setup from settings
        if( ! automatorwp_convertkit_get_api() ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo sprintf(
                    __( 'You need to configure the <a href="%s" target="_blank">ConvertKit settings</a> to get this action to work.', 'automatorwp' ),
                    get_admin_url() . 'admin.php?page=automatorwp_settings&tab=opt-tab-convertkit'
                ); ?>
                <?php echo sprintf(
                    __( '<a href="%s" target="_blank">Documentation</a>', 'automatorwp' ),
                    'https://automatorwp.com/docs/convertkit/'
                ); ?>
            </div>
        <?php endif;

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

new AutomatorWP_ConvertKit_Add_User_Form();