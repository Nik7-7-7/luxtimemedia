<?php

function aikit_get_ai_text_generation_model_map () {
    return [
        'openai' => aikit_rest_openai_get_available_models('text', true),
        'anthropic' => AIKIT_ANTHROPIC_MODELS,
    ];
}

function aikit_image_generation_request($prompt, $dimensions='1024x1024') {
    ###['image-generation-request']

    $default_api = get_option('aikit_setting_default_image_generation_api');

    if ($default_api == 'openai') {
        return aikit_openai_image_generation_request($prompt, $dimensions);
    } else if ($default_api == 'stability-ai') {
        $size = 'medium';
        if ($dimensions == '1024x1024') {
            $size = 'large';
        }

        return aikit_stability_ai_image_generation_request($prompt, $size);
    } else {
        return new WP_Error( 'image_generation_error', json_encode([
            'message' => 'no default image generation api set',
        ]), array( 'status' => 500 ) );
    }
}


function aikit_upload_file_by_url( $image_url, $dimensions, $imagePrompt) {

    $imagePromptWithOnlyLetters = preg_replace('/[^A-Za-z0-9\- ]/', '', $imagePrompt);
    $imagePromptWithOnlyLetters = str_replace(' ', '-', $imagePromptWithOnlyLetters);
    $imagePromptWithOnlyLetters = substr($imagePromptWithOnlyLetters, 0, 40);
    $imagePromptWithOnlyLetters = strtolower($imagePromptWithOnlyLetters);

    // it allows us to use download_url() and wp_handle_sideload() functions
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

    // download to temp dir
    $temp_file = download_url( $image_url );

    // add extension to temp file (get it from mime type)
    $mime_type = mime_content_type( $temp_file );
    $extension = explode( '/', $mime_type )[1];

    $newFilename = $imagePromptWithOnlyLetters . '-' . $dimensions . '-' . rand( 0, 99999999 ) . '.' . $extension;

    rename ( $temp_file, $newFilename );
    $temp_file = $newFilename;

    if( is_wp_error( $temp_file ) ) {
        return false;
    }

    // move the temp file into the uploads directory
    $file = array(
        'name'     => basename( $temp_file ),
        'type'     => mime_content_type( $temp_file ),
        'tmp_name' => $temp_file,
        'size'     => filesize( $temp_file ),
    );

    $sideload = wp_handle_sideload(
        $file,
        array(
            'test_form'   => false // no needs to check 'action' parameter
        )
    );

    if( ! empty( $sideload[ 'error' ] ) ) {
        // you may return error message if you want
        return false;
    }

    aikit_reconnect_db_if_needed();

    // it is time to add our uploaded image into WordPress media library
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => $sideload[ 'url' ],
            'post_mime_type' => $sideload[ 'type' ],
            'post_title'     => basename( $sideload[ 'file' ] ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $sideload[ 'file' ]
    );

    if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
        return false;
    }

    // update medatata, regenerate image sizes
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
    );

    return [
        'id' => $attachment_id,
        'url' => wp_get_attachment_image_url($attachment_id, explode('x', $dimensions))
    ];
}