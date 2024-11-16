<?php
/**
 * Generate Nonce
 *
 * @package     AutomatorWP\Integrations\Generator\Actions\Generate_Nonce
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Generator_Generate_Nonce extends AutomatorWP_Integration_Action {

    public $integration = 'generator';
    public $action = 'generator_generate_nonce';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Generate a nonce', 'automatorwp' ),
            'select_option'     => __( 'Generate a <strong>nonce</strong>', 'automatorwp' ),
            /* translators: %1$s: Nonce action. */
            'edit_label'        => sprintf( __( 'Generate a nonce for the action %1$s', 'automatorwp' ), '{random_nonce}' ),
            /* translators: %1$s: Nonce action. */
            'log_label'         => sprintf( __( 'Generate a nonce for the action %1$s', 'automatorwp' ), '{random_nonce}' ),
            'options'           => array(
                'random_nonce' => array(
                    'default'   => __( 'action', 'automatorwp' ),
                    'fields' => array(
                        'action' => array(
                            'name' => __( 'Action:', 'automatorwp' ),
                            'desc' => __( 'Name of the action the nonce is for.', 'automatorwp' ),
                            'type' => 'text',
                            'default' => 'my-nonce'
                        ),
                     ) )
            ),
            'tags'  => function_exists( 'automatorwp_generator_get_actions_random_nonce_tags' ) ? automatorwp_generator_get_actions_random_nonce_tags() : array(),
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

        $this->result = '';
        
        // Shorthand
        $action = $action_options['action'];
        
        // Bail if not action
        if ( empty( $action ) ) {
            $this->result = __( 'Please, insert an action to generate the nonce', 'automatorwp' );
            return;
        }

       
        $this->random_nonce = wp_create_nonce();
        
        $this->result = sprintf( __( '%s', 'automatorwp' ), $this->random_nonce );

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Configuration notice
        add_filter( 'automatorwp_automation_ui_after_item_label', array( $this, 'configuration_notice' ), 10, 2 );
		
		// Admin notice
		add_action( 'admin_notices', array( $this, 'admin_notice' ) );

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

        // Warn user if file was deleted
        if( ! function_exists( 'automatorwp_generator_get_actions_random_nonce_tags' ) ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo 
                    __( 'The generate a nonce function was not found and may have been accidentally removed by a security plugin on your site.<br>', 'automatorwp' ); ?>
                <?php echo
                    __( 'Please report this to the security plugin to add an exception to AutomatorWP and reinstall AutomatorWP to restore the deleted code.', 'automatorwp' ); ?>
            </div>
        <?php endif;

    }
	
	 /**
     * Configuration notice
     *
     * @since 1.0.0
     *
     */
    public function admin_notice( ) {
		
		// Warn user if file was deleted
        if( ! function_exists( 'automatorwp_generator_get_actions_random_nonce_tags' ) ) : ?>
            <div id="message-error" class="notice notice-error is-dismissible">
            <?php echo 
                    __( '<strong>AutomatorWP found an issue:</strong><br>', 'automatorwp' ); ?>
                <?php echo 
                    __( 'The generate a nonce function was not found and may have been accidentally removed by a security plugin on your site.<br>', 'automatorwp' ); ?>
                <?php echo
                    __( 'Please report this to the security plugin to add an exception to AutomatorWP and reinstall AutomatorWP to restore the deleted code.', 'automatorwp' ); ?>
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
        $log_meta['random_nonce'] = ( isset( $this->random_nonce ) ? $this->random_nonce : '' );

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

new AutomatorWP_Generator_Generate_Nonce();