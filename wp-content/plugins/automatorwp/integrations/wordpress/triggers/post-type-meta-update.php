<?php
/**
 * Post Type Meta Update
 *
 * @package     AutomatorWP\Integrations\WordPress\Triggers\Post_Type_Meta_Update
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WordPress_Post_Type_Meta_Update extends AutomatorWP_Integration_Trigger {

    /**
     * Initialize the trigger
     *
     * @since 1.0.0
     */
    public function __construct( $integration ) {

        $this->integration = $integration;
        $this->trigger = $integration . '_post_type_meta_update';

        parent::__construct();

    }

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'Post of a type meta gets updated with a value', 'automatorwp' ),
            'select_option'     => __( 'Post of a type <strong>meta gets updated</strong> with a value', 'automatorwp' ),
            /* translators: %1$s: Post type. %2$s: Key. %3$s: Condition. %4$s: Value. %5$s: Number of times. */
            'edit_label'        => sprintf( __( '%1$s meta %2$s gets updated with a value %3$s %4$s %5$s time(s)', 'automatorwp' ), '{post_type}', '{meta_key}', '{condition}', '{meta_value}', '{times}' ),
            /* translators: %1$s: Post type. %2$s: Key. %3$s: Condition. %4$s: Value. */
            'log_label'         => sprintf( __( '%1$s meta %2$s gets updated with a value %3$s %4$s', 'automatorwp' ), '{post_type}', '{meta_key}', '{condition}', '{meta_value}' ),
            'action'            => 'updated_post_meta',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 4,
            'options'           => array(
                'condition' => automatorwp_utilities_condition_option(),
                'post_type' => array(
                    'from' => 'post_type',
                    'fields' => array(
                        'post_type' => array(
                            'name' => __( 'Post type:', 'automatorwp' ),
                            'type' => 'select',
                            'classes' => 'automatorwp-selector',
                            'options_cb' => array( $this, 'options_cb_post_types' ),
                            'option_none' => true,
                            'option_none_label' => __( 'Post of any type', 'automatorwp' ),
                            'default' => 'any'
                        ),
                    )
                ),
                'meta_key' => array(
                    'from' => 'meta_key',
                    /* translators: Refers to meta key */
                    'default' => __( 'with any key', 'automatorwp' ),
                    'fields' => array(
                        'meta_key' => array(
                            'name' => __( 'Meta key:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
                'meta_value' => array(
                    'from' => 'meta_value',
                    'default' => __( 'any value', 'automatorwp' ),
                    'fields' => array(
                        'meta_value' => array(
                            'name' => __( 'Meta value:', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    )
                ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'updated_meta_key' => array(
                        'label'     => __( 'Updated meta key', 'automatorwp' ),
                        'type'      => 'text',
                        'preview'   => __( 'Key of the updated meta', 'automatorwp' ),
                    ),
                    'updated_meta_value' => array(
                        'label'     => __( 'Updated meta value', 'automatorwp' ),
                        'type'      => 'text',
                        'preview'   => __( 'Value of the updated meta', 'automatorwp' ),
                    ),
                ),
                automatorwp_utilities_post_tags(),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Options callback for post type options
     *
     * @since 1.0.0
     *
     * @param stdClass $field
     *
     * @return array
     */
    public function options_cb_post_types( $field ) {
        // Setup vars
        $none_value = 'any';
        $none_label = __( 'a post of any type', 'automatorwp' );
        $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );

        // Get all public post types which means they are visitable
        $public_post_types = get_post_types( array( 'public' => true ), 'objects' );

        foreach( $public_post_types as $post_type => $post_type_object ) {
            $options[$post_type] = $post_type_object->labels->singular_name;
        }

        return $options;
    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int    $meta_id     ID of updated metadata entry.
     * @param int    $object_id   ID of the object metadata is for.
     * @param string $meta_key    Metadata key.
     * @param mixed  $meta_value  Metadata value. Serialized if non-scalar.
     */
    public function listener( $meta_id, $object_id, $meta_key, $meta_value ) {

        $post = get_post( $object_id );

        /**
         * User ID for post updated triggers
         *
         * @since 1.0.0
         *
         * @param int    $user_id    The user ID
         * @param int    $post_ID    The post ID
         * @param string $trigger    The trigger
         */
        $user_id = apply_filters( 'automatorwp_post_updated_user_id', $post->post_author, $object_id, $this->trigger );

        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'user_id'       => $user_id,
            'post_id'       => $object_id,
            'post_type'     => $post->post_type,
            'meta_key'      => $meta_key,
            'meta_value'    => $meta_value,
        ) );

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
        if( ! isset( $event['post_id'] ) && ! isset( $event['meta_key'] ) && ! isset( $event['meta_value'] ) ) {
            return false;
        }

        $post = get_post( absint( $event['post_id'] ) );

        // Don't deserve if post doesn't exists
        if( ! $post ) {
            return false;
        }

        $post_type = $trigger_options['post_type'];

        // Don't deserve if post doesn't match with the trigger option
        if( $post_type !== 'any' && $post->post_type !== $post_type ) {
            return false;
        }

        // Don't deserve if key doesn't matches with the trigger option
        if( $trigger_options['meta_key'] !== '' && $trigger_options['meta_key'] !== $event['meta_key'] ) {
            return false;
        }

        // Don't deserve if value doesn't matches with the trigger option
		if ( $trigger_options['meta_value'] !== '' && ! automatorwp_condition_matches( $event['meta_value'], $trigger_options['meta_value'], $trigger_options['condition'] ) ) {
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

        // Tags replacement
        add_filter( 'automatorwp_get_trigger_tag_replacement', array( $this, 'tags_replacement' ), 10, 6 );

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom tags replacement
     *
     * @since 1.0.0
     *
     * @param string    $replacement    The tag replacement
     * @param string    $tag_name       The tag name (without "{}")
     * @param stdClass  $trigger        The trigger object
     * @param int       $user_id        The user ID
     * @param string    $content        The content to parse
     * @param stdClass  $log            The last trigger log object
     *
     * @return string
     */
    function tags_replacement( $replacement, $tag_name, $trigger, $user_id, $content, $log ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $replacement;
        }

        switch( $tag_name ) {
            case 'updated_meta_key':
                $replacement = automatorwp_get_log_meta( $log->id, 'updated_meta_key', true );
                break;
            case 'updated_meta_value':
                $replacement = automatorwp_get_log_meta( $log->id, 'updated_meta_value', true );
                break;
        }

        return $replacement;

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

        $log_meta['updated_meta_key'] = ( isset( $event['updated_meta_key'] ) ? $event['updated_meta_key'] : '' );
        $log_meta['updated_meta_value'] = ( isset( $event['updated_meta_value'] ) ? $event['updated_meta_value'] : '' );

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

        // Bail if log is not assigned to an trigger
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        // Bail if trigger type don't match this trigger
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['updated_meta_key'] = array(
            'name' => __( 'Updated meta key', 'automatorwp' ),
            'desc' => __( 'Key of the updated meta.', 'automatorwp' ),
            'type' => 'text',
        );

        $log_fields['updated_meta_value'] = array(
            'name' => __( 'Updated meta value', 'automatorwp' ),
            'desc' => __( 'Value of the updated meta.', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_WordPress_Post_Type_Meta_Update( 'wordpress' );
new AutomatorWP_WordPress_Post_Type_Meta_Update( 'posts' );