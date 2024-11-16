<?php

add_action( 'rest_api_init', function () {
    register_rest_route( 'aikit/stability-ai/v1', '/generate-images', array(
        'methods' => 'POST',
        'callback' => 'aikit_rest_stability_ai_generate_images',
        'permission_callback' => function () {
            return is_user_logged_in() && current_user_can( 'edit_posts' );
        }
    ));
} );

function aikit_rest_stability_ai_generate_images ($data) {

    ###['stabilityai-generate-images']

    $size = $data['size'] ?? 'medium';
    $text = $data['text'] ?? '';

    if (empty($data['text'])) {
        return new WP_Error('missing_param', 'Missing text parameter', array('status' => 400));
    }

    try {
        $imagePrompt = aikit_openai_generate_image_prompt($text);

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        // if user wasn't subscribed to openai, we can use the text as prompt
        $imagePrompt = $text;
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        $imagePrompt = $text;
    }

    $imageCount = intval($data['count']) ?? 1;

    $width = 512;
    $height = 512;

    if ($size == 'large') {
        $width = 1024;
        $height = 1024;
    } else if (trim($size) == 'xlarge landscape') {
        $width = 1344;
        $height = 768;
    }

    $engine = get_option('aikit_setting_stabilityai_default_engine');
    $sampler = get_option('aikit_setting_stabilityai_default_sampler');
    $steps = intval(get_option('aikit_setting_stabilityai_default_steps'));
    $cfg = intval(get_option('aikit_setting_stabilityai_default_cfg_scale'));
    $seed = intval(get_option('aikit_setting_stabilityai_default_seed'));

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();
    try {

        $imageResponse = $client->request( 'POST', 'https://api.stability.ai/v1/generation/' . $engine . '/text-to-image', [
            'body'    => json_encode( [
                'samples' => $imageCount,
                'width' => $width,
                'height' => $height,
                'sampler' => $sampler,
                'steps' => $steps,
                'cfg_scale' => $cfg,
                'seed' => $seed,
                'text_prompts' => [
                    [
                        'text' => $imagePrompt,
                        'weight' => 1,
                    ],
                ]
            ] ),
            'headers' => [
                'Authorization' => get_option( 'aikit_setting_stabilityai_key' ),
                'Content-Type'  => 'application/json',
            ],
        ] );

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        return new WP_Error( 'stability_ai_error', json_encode([
            'message' => 'error while calling stability ai',
            'responseBody' => $e->getResponse()->getBody()->getContents(),
        ]), array( 'status' => 500 ) );
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        return new WP_Error( 'stability_ai_error', json_encode([
            'message' => 'error while calling stability ai',
            'responseBody' => $e->getMessage(),
        ]), array( 'status' => 500 ) );
    }

    $body = $imageResponse->getBody();
    $json = json_decode($body, true);
    $data = $json['artifacts'] ?? [];

    $images = array();
    foreach ($data as $image) {
        $imageUrl = aikit_upload_base64_image($image['base64'], $imagePrompt);
        if ($imageUrl) {
            $images[] = $imageUrl;
        }
    }

    return new WP_REST_Response([
        'images' => $images,
    ], 200);
}


function aikit_stability_ai_image_generation_request ($prompt, $size) {

    $width = 512;
    $height = 512;

    if ($size == 'large') {
        $width = 1024;
        $height = 1024;
    }

    $engine = get_option('aikit_setting_stabilityai_default_engine');
    $sampler = get_option('aikit_setting_stabilityai_default_sampler');
    $steps = intval(get_option('aikit_setting_stabilityai_default_steps'));
    $cfg = intval(get_option('aikit_setting_stabilityai_default_cfg_scale'));
    $seed = intval(get_option('aikit_setting_stabilityai_default_seed'));

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    $imageResponse = $client->request( 'POST', 'https://api.stability.ai/v1/generation/' . $engine . '/text-to-image', [
        'body'    => json_encode( [
            'samples' => 1,
            'width' => $width,
            'height' => $height,
            'sampler' => $sampler,
            'steps' => $steps,
            'cfg_scale' => $cfg,
            'seed' => $seed,
            'text_prompts' => [
                [
                    'text' => $prompt,
                    'weight' => 1,
                ],
            ]
        ] ),
        'headers' => [
            'Authorization' => get_option( 'aikit_setting_stabilityai_key' ),
            'Content-Type'  => 'application/json',
        ],
    ] );

    $body = $imageResponse->getBody();
    $json = json_decode($body, true);
    $data = $json['artifacts'] ?? [];

    $images = array();
    foreach ($data as $image) {
        $imageUrl = aikit_upload_base64_image($image['base64'], $prompt);
        if ($imageUrl) {
            $images[] = $imageUrl;
        }
    }

    return $images;
}


function aikit_upload_base64_image( $base64_image, $imagePrompt) {

    $imagePromptWithOnlyLetters = preg_replace('/[^A-Za-z0-9\- ]/', '', $imagePrompt);
    $imagePromptWithOnlyLetters = str_replace(' ', '-', $imagePromptWithOnlyLetters);
    $imagePromptWithOnlyLetters = substr($imagePromptWithOnlyLetters, 0, 40);
    $imagePromptWithOnlyLetters = strtolower($imagePromptWithOnlyLetters);

    // it allows us to use download_url() and wp_handle_sideload() functions
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

    // save base64 image to temp file
    $temp_file = tempnam(sys_get_temp_dir(), 'ai-image-pro');
    file_put_contents($temp_file, base64_decode($base64_image));

    // add extension to temp file (get it from mime type)
    $mime_type = mime_content_type( $temp_file );
    $extension = explode( '/', $mime_type )[1];

    $newFilename = $imagePromptWithOnlyLetters . '-' . rand( 0, 99999999 ) . '.' . $extension;

    rename ( $temp_file, $newFilename );
    $temp_file = $newFilename;

    if( is_wp_error( $temp_file ) ) {
        return false;
    }

    $imageDimensions = getimagesize($temp_file);
    $width = $imageDimensions[0] ?? 512;
    $height = $imageDimensions[1] ?? 512;

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
        'url' => wp_get_attachment_image_url($attachment_id, array($width, $height)),
    ];
}