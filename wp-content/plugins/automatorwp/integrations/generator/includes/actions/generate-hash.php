<?php
/**
 * Generate Hash
 *
 * @package     AutomatorWP\Integrations\Generator\Actions\Generate_Hash
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly

if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Generator_Generate_Hash extends AutomatorWP_Integration_Action {

    public $integration = 'generator';
    public $action = 'generator_generate_hash';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Generate a hash', 'automatorwp' ),
            'select_option'     => __( 'Generate a <strong>hash</strong>', 'automatorwp' ),
            /* translators: %1$s: Hash algorithm. */
            'edit_label'        => sprintf( __( 'Generate a hash with %1$s', 'automatorwp' ), '{random_hash}' ),
            /* translators: %1$s: Hash algorithm. */
            'log_label'         => sprintf( __( 'Generate a hash with %1$s', 'automatorwp' ), '{random_hash}' ),
            'options'           => array(
                'random_hash' => array(
                    'from'      => 'algorithm',
                    'default'   => __( 'algorithm', 'automatorwp' ),
                    'fields' => array(
                        'data' => array(
                            'name' => __( 'Data:', 'automatorwp' ),
                            'desc' => __( 'Data to be hashed.', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                        'algorithm' => array(
                            'name' => __( 'Algorithm:', 'automatorwp' ),
                            'type' => 'select',
                            'classes' => 'automatorwp-selector',
                            'options_cb' => 'automatorwp_generator_get_algorithms',
                            'attributes' => array(
                                'data-placeholder'       => __( 'Select an algorithm', 'automatorwp' ),
                            ),
                            'default' => '',
                        ),
                     ) )
            ),
            'tags'  => function_exists( 'automatorwp_generator_get_actions_random_hash_tags' ) ? automatorwp_generator_get_actions_random_hash_tags() : array(),
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
        $data = $action_options['data'];
        $algorithm = $action_options['algorithm'];
        
        // Bail if not data
        if ( empty( $data ) ) {
            $this->result = __( 'Please, insert data to be hashed', 'automatorwp' );
            return;
        }
        
        // Bail if not algorithm
        if ( empty( $algorithm ) ) {
            $this->result = __( 'Please, select an algorithm', 'automatorwp' );
            return;
        }

        // Get the algorithms
        $all_algorithms = hash_algos();

        $this->random_hash = hash( $all_algorithms[$algorithm], $data ); 
        
        $this->result = sprintf( __( '%s', 'automatorwp' ), $this->random_hash );

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
        if( ! function_exists( 'automatorwp_generator_get_actions_random_hash_tags' ) ) : ?>
            <div class="automatorwp-notice-warning" style="margin-top: 10px; margin-bottom: 0;">
                <?php echo 
                    __( 'The generate a hash function was not found and may have been accidentally removed by a security plugin on your site.<br>', 'automatorwp' ); ?>
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
        if( ! function_exists( 'automatorwp_generator_get_actions_random_hash_tags' ) ) : ?>
            <div id="message-error" class="notice notice-error is-dismissible">
            <?php echo 
                    __( '<strong>AutomatorWP found an issue:</strong><br>', 'automatorwp' ); ?>
                <?php echo 
                    __( 'The generate a hash function was not found and may have been accidentally removed by a security plugin on your site.<br>', 'automatorwp' ); ?>
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
        $log_meta['random_hash'] = ( isset( $this->random_hash ) ? $this->random_hash : '' );

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

new AutomatorWP_Generator_Generate_Hash();