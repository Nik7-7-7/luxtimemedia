<?php
/**
 * Functions
 *
 * @package     AutomatorWP\weForms\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
 * Get form fields values
 *
 * @since 1.0.0
 *
 * @param array $fields
 *
 * @return array
 */
function automatorwp_weforms_get_form_fields_values( $form, $entry_id ) {

    $form_fields = array();

    $obj_form = new weForms_Form($form);
    $obj_entry = new weForms_Form_Entry($entry_id, $obj_form);
    $name_fields = $obj_entry->get_form( $entry_id )->get_field_values();


    // Loop all fields
    foreach ( $name_fields as $field_name => $value ) {
        
        if( ( isset( $value['template'] ) && $value['template'] === 'name_field' )
            || ( isset( $value['template'] ) && $value['template'] === 'checkbox_field' ) ) {

            $field_value = weforms_get_entry_meta( $entry_id, $field_name, true );
            $field_value = explode( '| ', $field_value );
            $form_fields[$field_name] = $field_value;

            ( $value['template'] === 'name_field' ) && $form_fields['full_name'] = implode(' ', $field_value );

        }else {
            $field_value = weforms_get_entry_meta( $entry_id, $field_name, true );
            $form_fields[$field_name] = $field_value;
        }

    }
    

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
function automatorwp_weforms_parse_automation_tags( $parsed_content, $replacements, $automation_id, $user_id, $content ) {

    $new_replacements = array();

    // Get automation triggers to pass their tags
    $triggers = automatorwp_get_automation_triggers( $automation_id );

    foreach( $triggers as $trigger ) {

        $trigger_args = automatorwp_get_trigger( $trigger->type );

        // Skip if trigger is not from this integration
        if( $trigger_args['integration'] !== 'weforms' ) {
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
add_filter( 'automatorwp_parse_automation_tags', 'automatorwp_weforms_parse_automation_tags', 10, 5 );