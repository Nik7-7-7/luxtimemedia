<?php
/**
 * Functions
 *
 * @package     AutomatorWP\ConvertKit\Functions
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Helper function to check API secret
 *
 * @since 1.0.0
 * 
 * @param string    $secret API secret
 *
 * @return array|false
 */
function automatorwp_convertkit_check_api_secret( $secret ) {

    $return = false;

    $response = wp_remote_get( 'https://api.convertkit.com/v3/account?api_secret=' . $secret, array() );

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== $status_code ) {
        wp_send_json_error (array( 'message' => __( 'Please, check your API secret', 'automatorwp-convertkit' ) ) );
        return $return;
	} else {
        $return = true;
    }

    return $return;

}

/**
 * Helper function to check API key
 *
 * @since 1.0.0
 * 
 * @param string    $key API key
 *
 * @return array|false
 */
function automatorwp_convertkit_check_api_key( $key ) {

    $return = false;

    $response = wp_remote_get( 'https://api.convertkit.com/v3/forms?api_key=' . $key, array() );

	$status_code = wp_remote_retrieve_response_code( $response );

	if ( 200 !== $status_code ) {
        wp_send_json_error (array( 'message' => __( 'Please, check your API key', 'automatorwp-convertkit' ) ) );
        return $return;
	} else {
        $return = true;
    }

    return true;

}

/**
 * Helper function to get the ConvertKit API parameters
 *
 * @since 1.0.0
 *
 * @return array|false
 */
function automatorwp_convertkit_get_api() {

    $key = automatorwp_convertkit_get_option( 'key', '' );
    $secret = automatorwp_convertkit_get_option( 'secret', '' );
    $url = 'https://api.convertkit.com/v3';

    if( empty( $key ) || empty( $secret ) ) {
        return false;
    }

    return array(
        'key'       => $key,
        'secret'    => $secret,
        'url'       => $url,
    );

}

/**
 * Get forms from ConvertKit
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_convertkit_options_cb_form( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any form', 'automatorwp-convertkit' );
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
            
            $options[$form_id] = automatorwp_convertkit_get_form_name( $form_id );
        }
    }

    return $options;

}

/**
* Get forms from ConvertKit
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_convertkit_get_forms( ) {

    $forms = array();

    $api = automatorwp_convertkit_get_api();

    if( ! $api ) {
        return $forms;
    }

    $response = wp_remote_get( $api['url'] . '/forms', array(
        'body' => array(
            'api_key' => $api['key'],
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response['forms'] as $form ){
        $forms[] = array(
            'id' => $form['id'],
            'name' => $form['name'],
        );

    }

    return $forms;

}

/**
* Get form name
*
* @since 1.0.0
*
* @param int    $form_id         ID Form
*
* @return string
*/
function automatorwp_convertkit_get_form_name( $form_id ) {

    // Empty title if no ID provided
    if( absint( $form_id ) === 0 ) {
        return '';
    }

    $forms = automatorwp_convertkit_get_forms();
    $form_name = '';

    foreach( $forms as $form ) {

        if( absint( $form['id'] ) === absint( $form_id ) ) {
            $form_name = $form['name'];
            break;
        }

    }

    return $form_name;

}

/**
 * Get sequences from ConvertKit
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_convertkit_options_cb_sequence( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any form', 'automatorwp-convertkit' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $sequence_id ) {

            // Skip option none
            if( $sequence_id === $none_value ) {
                continue;
            }
            
            $options[$sequence_id] = automatorwp_convertkit_get_sequence_name( $sequence_id );
        }
    }

    return $options;

}

/**
* Get sequences from ConvertKit
*
* @since 1.0.0
*
* @return array
*/
function automatorwp_convertkit_get_sequences( ) {

    $sequences = array();

    $api = automatorwp_convertkit_get_api();

    if( ! $api ) {
        return $sequences;
    }

    $response = wp_remote_get( $api['url'] . '/sequences', array(
        'body' => array(
            'api_key' => $api['key'],
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response['courses'] as $sequence ){
        $sequences[] = array(
            'id' => $sequence['id'],
            'name' => $sequence['name'],
        );

    }

    return $sequences;

}

/**
* Get sequence name
*
* @since 1.0.0
*
* @param int    $sequence_id         ID Sequence
* 
* @return string
*/
function automatorwp_convertkit_get_sequence_name( $sequence_id ) {

    // Empty title if no ID provided
    if( absint( $sequence_id ) === 0 ) {
        return '';
    }

    $sequences = automatorwp_convertkit_get_sequences();
    $sequence_name = '';

    foreach( $sequences as $sequence ) {

        if( absint( $sequence['id'] ) === absint( $sequence_id ) ) {
            $sequence_name = $sequence['name'];
            break;
        }

    }

    return $sequence_name;

}

/**
* Add subscriber to form
*
* @since 1.0.0
*
* @param int $form_id           ID form
* @param string $email          Subscriber email
* @param string $first_name     Subscriber name
*
* @return int
*/
function automatorwp_convertkit_add_subscriber_form( $form_id, $email, $first_name = '' ) {

    $api = automatorwp_convertkit_get_api();

    if( ! $api ) {
        return false;
    }

    $response = wp_remote_post( $api['url'] . '/forms/' . $form_id . '/subscribe', array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'charset'       => 'utf-8'
        ),
        'body' => json_encode( array(
            'api_key' => $api['key'],
            'email' => $email,
            'first_name' => ( !empty( $first_name ) ? $first_name : '' ),
        )
    ) ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;

}

/**
* Add subscriber to sequence
*
* @since 1.0.0
*
* @param int $sequence_id   ID sequence
* @param string $email  Subscriber email
* @param string $first_name     Subscriber name
*
* @return int
*/
function automatorwp_convertkit_add_subscriber_sequence( $sequence_id, $email, $first_name = '' ) {

    $api = automatorwp_convertkit_get_api();

    if( ! $api ) {
        return false;
    }

    $response = wp_remote_post( $api['url'] . '/sequences/' . $sequence_id . '/subscribe', array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'charset'       => 'utf-8'
        ),
        'body' => json_encode( array(
            'api_key' => $api['key'],
            'email' => $email,
            'first_name' => ( !empty( $first_name ) ? $first_name : '' ),
        )
    ) ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;

}

/**
 * Get tags from ConvertKit
 *
 * @since 1.0.0
 * 
 * @param stdClass $field
 *
 * @return array
 */
function automatorwp_convertkit_options_cb_tag( $field ) {

    // Setup vars
    $value = $field->escaped_value;
    $none_value = 'any';
    $none_label = __( 'any tag', 'automatorwp-convertkit' );
    $options = automatorwp_options_cb_none_option( $field, $none_value, $none_label );
    
    if( ! empty( $value ) ) {
        if( ! is_array( $value ) ) {
            $value = array( $value );
        }

        foreach( $value as $tag_id ) {

            // Skip option none
            if( $tag_id === $none_value ) {
                continue;
            }
            
            $options[$tag_id] = automatorwp_convertkit_get_tag_name( $tag_id );
        }
    }

    return $options;

}

/**
* Get tags from ConvertKit
*
* @since 1.0.0
*
* @param string $search
* @param int $page
*
* @return array
*/
function automatorwp_convertkit_get_tags() {

    $tags = array();

    $api = automatorwp_convertkit_get_api();

    if( ! $api ) {
        return $tags;
    }

    $response = wp_remote_get( $api['url'] . '/tags', array(
        'body' => array(
            'api_key' => $api['key'],
        )
    ) );

    $response = json_decode( wp_remote_retrieve_body( $response ), true  );

    foreach ( $response['tags'] as $tag ){
        $tags[] = array(
            'id' => $tag['id'],
            'name' => $tag['name'],
        );

    }

    return $tags;

}

/**
* Get tag name
*
* @since 1.0.0
*
* @param int    $tag_id         ID tag
* 
* @return string
*/
function automatorwp_convertkit_get_tag_name( $tag_id ) {

    // Empty title if no ID provided
    if( absint( $tag_id ) === 0 ) {
        return '';
    }

    $tags = automatorwp_convertkit_get_tags();
    $tag_name = '';

    foreach( $tags as $tag ) {

        if( absint( $tag['id'] ) === absint( $tag_id ) ) {
            $tag_name = $tag['name'];
            break;
        }

    }

    return $tag_name;

}

/**
 * Add tag to subscriber
 *
 * @since 1.0.0
 *
 * @param string    $email          Subscriber email
 * @param string    $tag_id         Tag ID
 * @param string    $first_name     Subscriber name
 * 
 * @return int
 */
function automatorwp_convertkit_add_subscriber_tag( $email, $tag_id, $first_name = '' ){

    $api = automatorwp_convertkit_get_api();

    if( ! $api ) {
        return false;
    }

    $response = wp_remote_post( $api['url'] . '/tags/' . $tag_id . '/subscribe', array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'charset'       => 'utf-8'
        ),
        'body' => json_encode( array(
            'api_secret' => $api['secret'],
            'email' => $email,
            'first_name' => ( !empty( $first_name ) ? $first_name : '' ),
        )
    ) ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;

}

/**
 * Remove tag from subscriber
 *
 * @since 1.0.0
 *
 * @param string    $email      Subscriber email
 * @param string    $tag_id     Tag ID
 * 
 * @return int
 */
function automatorwp_convertkit_remove_subscriber_tag( $email, $tag_id ){

    $api = automatorwp_convertkit_get_api();

    if( ! $api ) {
        return false;
    }

    $response = wp_remote_post( $api['url'] . '/tags/' . $tag_id . '/unsubscribe', array(
        'headers' => array(
            'Content-Type'  => 'application/json',
            'charset'       => 'utf-8'
        ),
        'body' => json_encode( array(
            'api_secret' => $api['secret'],
            'email' => $email,
        )
    ) ) );

    $status_code = wp_remote_retrieve_response_code( $response );

    return $status_code;

}
