<?php
/**
 * Functions
 *
 * @package     AutomatorWP\ARForms\Functions
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get plugin version
 *
 * @since 1.0.0
 *
 * @return string
 */
function automatorwp_arforms_get_plugin_version() {

    global $wpdb;

    // Check installed versions
    if( is_plugin_active( 'arforms-form-builder/arforms-form-builder.php' ) && is_plugin_active( 'arforms/arforms.php' ) ) {
        $version = "pro";
    } elseif( !is_plugin_active( 'arforms-form-builder/arforms-form-builder.php' ) && is_plugin_active( 'arforms/arforms.php' ) ) {
		$version = "only_pro";
	}
	else {
        $version = "lite";
    }

    return $version;

}

/**
 * Get forms from ARForm
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_arforms_options_cb_form( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any form', 'automatorwp-ameliabooking' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $form_id ) {

            // Skip option none
            if( $form_id === $none_value ) {
                continue;
            }
            
            $options[$form_id] = automatorwp_arforms_get_form_name( $form_id );
        }
    }

    return $options;

}

/**
 * Get forms
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_arforms_get_forms( ) {

    global $wpdb;

    $all_forms = array();

    $version = automatorwp_arforms_get_plugin_version();

    // Check installed versions to get table
    if( $version === "pro" ) {
        $table_name = "{$wpdb->prefix}arf_forms";
		$sql_query = $wpdb->prepare( "SELECT id, name FROM {$table_name} WHERE is_template = 0" );
    } elseif ( $version === "only_pro" ) {
		$table_name = "{$wpdb->prefix}arf_forms";
		$sql_query = $wpdb->prepare( "SELECT id, name FROM {$table_name} WHERE is_template = 0 AND arf_is_lite_form = 0" );
	} 
	else {
        $table_name = "{$wpdb->prefix}arflite_forms";
		$sql_query = "SELECT id, name FROM " . $table_name;
    }

    //$sql_query = $wpdb->prepare( "SELECT id, name FROM {$table_name}" );
    $results = $wpdb->get_results( $sql_query );

    foreach ( $results as $form ) {
        $all_forms[] = array(
            'id' => $form->id,
            'name' => $form->title,
        );
    }
    
    return $all_forms;

}

/**
 * Get form name
 *
 * @since 1.0.0
 *
 * @return array
 */
function automatorwp_arforms_get_form_name( $form_id ) {

    global $wpdb;

    $version = automatorwp_arforms_get_plugin_version();

    // Check installed versions to get table
    if( $version === "pro" || $version === "only_pro" ) {
        $table_name = "{$wpdb->prefix}arf_forms";
    } else {
        $table_name = "{$wpdb->prefix}arflite_forms";
    }
    
    $sql_query = $wpdb->prepare( "SELECT name FROM $table_name WHERE id = %d", $form_id );
    $form_name = $wpdb->get_var( $sql_query );
    
    return $form_name;

}

/**
 * Get form fields values
 *
 * @since 1.0.0
 *
 * @param array $field_data
 * @param string $field_name
 * @param string $field_value
 *
 * @return array
 */
function automatorwp_arforms_get_form_fields_values( $form_id, $item_meta_values ) {
    
    global $wpdb;
    
    $form_fields = array();

    $version = automatorwp_arforms_get_plugin_version();
    
    // Check installed versions to get table
    if( $version === "pro" || $version === "only_pro" ) {
        $table_name = "{$wpdb->prefix}arf_fields";
    } else {
        $table_name = "{$wpdb->prefix}arflite_fields";
    }

    $sql_query = $wpdb->prepare( "SELECT id, name FROM $table_name WHERE form_id = %d", $form_id );
    $field_data = $wpdb->get_results( $sql_query );

    foreach( $field_data as $field ) {

        $name = $field->name;
        $value = $item_meta_values[$field->id];

        $form_fields[$name] = $value;
    }

    // Check for AutomatorWP 1.4.4
    if( function_exists( 'automatorwp_utilities_pull_array_values' ) ) {
        $form_fields = automatorwp_utilities_pull_array_values( $form_fields );
    }

    return $form_fields;

}

/**
 * Custom tags replacements
 *
 * @since 1.0.0
 *
 * @param string    $parsed_content     Content parsed
 * @param array     $replacements       Automation replacements
 * @param int       $automation_id      The automation ID
 * @param int       $user_id            The user ID
 * @param string    $content            The content to parse
 *
 * @return string
 */
function automatorwp_arforms_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $new_replacements = array();

    // Get automation triggers to pass their tags
    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        $trigger_args = automatorwp_get_trigger( $trigger->type );

        // Skip if trigger is not from this integration
        if( $trigger_args['integration'] !== 'arforms' ) {
            continue;
        }

        // Get the last trigger log (where data for tags replacement will be get
        $log = automatorwp_get_user_last_completion( $trigger->id, $user_id, 'trigger' );

        if( ! $log ) {
            continue;
        }

        ct_setup_table( 'automatorwp_logs' );
        $form_fields = ct_get_object_meta( $log->id, 'form_fields', true );
        ct_reset_setup_table();

        // Skip if not form fields
        if( ! is_array( $form_fields ) ) {
            continue;
        }

        // Look for form field tags
        preg_match_all( "/\{t:" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches );
        
        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                // Replace {t:ID:form_field:NAME} by the field value
                if( isset( $form_fields[$field_name] ) ) {
                    $new_replacements['{t:' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }

        }

        // Look for form field tags
        preg_match_all( "/\{" . $trigger->id . ":form_field:\s*(.*?)\s*\}/", $parsed_content, $matches );

        if( is_array( $matches ) && isset( $matches[1] ) ) {

            foreach( $matches[1] as $field_name ) {
                // Replace {ID:form_field:NAME} by the field value
                if( isset( $form_fields[$field_name] ) ) {
                    $new_replacements['{' . $trigger->id . ':form_field:' . $field_name . '}'] = $form_fields[$field_name];
                }
            }

        }

    }

    if( count( $new_replacements ) ) {

        $tags = array_keys( $new_replacements );

        // Replace all tags by their replacements
        $parsed_content = str_replace( $tags, $new_replacements, $parsed_content );

    }

    return $parsed_content;

}
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_arforms_parse_automation_tags', 10, 5 );