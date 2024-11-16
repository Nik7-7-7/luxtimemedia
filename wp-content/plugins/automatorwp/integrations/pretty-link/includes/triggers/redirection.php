<?php 
/**
 * Detect when a Pretty Link is used
 *
 * @package     AutomatorWP\Integrations\pretty-link\Triggers\redirection
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Pretty_Link_Redirection extends AutomatorWP_Integration_Trigger {

    public $integration = 'pretty_link';
    public $trigger = 'pretty_link_redirection';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __('User clicks a pretty link', 'automatorwp'),
            'select_option'     => __( 'User clicks a <strong>pretty link</strong>', 'automatorwp' ),
            'edit_label'        => sprintf( __('User clicks %1$s %2$s time(s)', 'automatorwp'), '{link}', '{times}' ),
            'log_label'         => sprintf( __('User clicks %1$s', 'automatorwp'), '{link}' ),
            'action'            => 'prli_record_click',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'link' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'link',
                    'name'              => __( 'Link:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any link', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_pretty_link_get_links',
                    'options_cb'        => 'automatorwp_pretty_link_options_cb_link',
                    'default'           => 'any'
                )),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                automatorwp_pretty_link_get_link_tags(),
                automatorwp_utilities_times_tag()
            )
        ));
    }

     /**
     * Trigger listener
     *
     * @since 1.0.0
     */
    public function listener($arrayInfo) {
        // Login is required
        if ( ! is_user_logged_in() ) {
            return;
        }
        $user_id = get_current_user_id();

        $link_data = automatorwp_pretty_link_get_link_data( $arrayInfo['link_id'] );

        //link tags
        $link_name = $link_data["name"];
        $link_redirection_method = $link_data["redirect_type"];
        $link_target = $link_data["url"];
        $link_slug = $link_data["slug"];

        //Trigger clicked pretty link event
        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'user_id' => $user_id,
            'link_id' => $arrayInfo['link_id'],
            'link_name' => $link_name,
            'link_redirection_method' => $link_redirection_method,
            'link_target' => $link_target,
            'link_slug' => $link_slug
        ) );

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
        if( ! isset( $event['link_id'] ) ) {
            return false;
        }

        // Don't deserve if post doesn't match with the trigger option
        if( $trigger_options['link'] !== 'any' && absint( $event['link_id'] ) !== absint( $trigger_options['link'] ) ) {
            return false;
        }
        return $deserves_trigger;

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
        $log_meta['link_name'] = ( isset( $event['link_name'] ) ? $event['link_name'] : '' );
        $log_meta['link_redirection_method'] = ( isset( $event['link_redirection_method'] ) ? $event['link_redirection_method'] : '' );
        $log_meta['link_target'] = ( isset( $event['link_target'] ) ? $event['link_target'] : '' );
        $log_meta['link_slug'] = ( isset( $event['link_slug'] ) ? $event['link_slug'] : '' );

        return $log_meta;
    }
}

new AutomatorWP_Pretty_Link_Redirection();