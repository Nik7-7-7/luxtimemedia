<?php

function aikit_elevenlabs_get_models() {

    ###['elevenlabs-get-models']
    $default_models = [
        'eleven_multilingual_v2' => 'Eleven Multilingual v2',
        'eleven_multilingual_v1' => 'Eleven Multilingual v1',
        'eleven_monolingual_v1' => 'Eleven English v1',
    ];

    $api_key = get_option( 'aikit_setting_elevenlabs_api_key' );

    if (empty($api_key)) {
        return $default_models;
    }

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();
    try {

        $response = $client->request( 'GET', 'https://api.elevenlabs.io/v1/models', [
            'headers' => [
                'xi-api-key' => $api_key,
                'Content-Type'  => 'application/json',
            ],
        ] );

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        return $default_models;
        // todo: log in the central log
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        return $default_models;
        // todo: log in the central log
    }

    $body = $response->getBody();
    $jsonResult = json_decode($body, true);

    $resultModels = array();
    foreach ($jsonResult as $model) {
        $resultModels[$model['model_id']] = $model['name'];
    }

    return $resultModels;
}

function aikit_elevenlabs_get_voices () {

    ###['elevenlabs-get-voices']

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();
    $default_voices = [
        '21m00Tcm4TlvDq8ikWAM' => 'Rachel',
        '2EiwWnXFnvU5JabPnv8n' => 'Clyde',
        'AZnzlk1XvdvUeBnXmlld' => 'Domi',
        'CYw3kZ02Hs0563khs1Fj' => 'Dave',
        'D38z5RcWu1voky8WS1ja' => 'Fin',
        'EXAVITQu4vr4xnSDxMaL' => 'Bella',
        'ErXwobaYiN019PkySvjV' => 'Antoni',
        'GBv7mTt0atIp3Br8iCZE' => 'Thomas',
        'IKne3meq5aSn9XLyUdCD' => 'Charlie',
        'LcfcDJNUP1GQjkzn1xUU' => 'Emily',
    ];

    $api_key = get_option( 'aikit_setting_elevenlabs_api_key' );


    if (empty($api_key)) {
        return $default_voices;
    }

    try {

        $response = $client->request( 'GET', 'https://api.elevenlabs.io/v1/voices', [
            'headers' => [
                'xi-api-key' => $api_key,
                'Content-Type'  => 'application/json',
            ],
        ] );

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        return $default_voices;

        // todo: log in the central log
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        return $default_voices;
        // todo: log in the central log
    }

    $body = $response->getBody();
    $jsonResult = json_decode($body, true);

    $resultVoices = array();
    foreach ($jsonResult['voices'] as $voice) {
        $accent = $voice['labels']['accent'] ?? '';
        $accent = $accent ? ' - ' . $accent : '';

        $gender = $voice['labels']['gender'] ?? '';
        $gender = $gender ? ' - ' . $gender : '';

        $age = $voice['labels']['age'] ?? '';
        $age = $age ? ' - ' . $age : '';

        $resultVoices[$voice['voice_id']] = $voice['name'] . $accent . $gender . $age;
    }

    asort($resultVoices);

    return $resultVoices;
}

function aikit_elevenlabs_get_user_info () {
    ###['elevenlabs-get-user-info']

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    $api_key = get_option( 'aikit_setting_elevenlabs_api_key' );

    try {

        $response = $client->request( 'GET', 'https://api.elevenlabs.io/v1/user', [
            'headers' => [
                'xi-api-key' => $api_key,
                'Content-Type'  => 'application/json',
            ],
        ] );

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        throw $e;
        // todo: log in the central log
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        throw $e;
        // todo: log in the central log
    }

    $body = $response->getBody();
    $jsonResult = json_decode($body, true);

    return $jsonResult;
}

function aikit_elevenlabs_text_to_speech ($string) {

    ###['elevenlabs-text-to-speech']

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    $voice = get_option('aikit_setting_elevenlabs_voice');
    $model = get_option('aikit_setting_elevenlabs_model');
    try {
        $response = $client->request( 'POST', 'https://api.elevenlabs.io/v1/text-to-speech/' . $voice, [
            'body'    => json_encode( [
                'text' => $string,
                'model_id' => $model,
            ] ),
            'headers' => [
                'xi-api-key' => get_option( 'aikit_setting_elevenlabs_api_key' ),
                'Content-Type'  => 'application/json',
            ],
        ] );

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        throw $e;
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        throw $e;
    }

    // response is a binary file
    $body = $response->getBody();
    $data = $body->getContents();

    $file_name_prefix = preg_replace('/[^A-Za-z0-9\- ]/', '', strtolower(substr($string, 0, 50)));
    $file_name_prefix = str_replace(' ', '-', $file_name_prefix);
    $random = rand( 0, 99999999 );

    $temp_file = tempnam(sys_get_temp_dir(), $file_name_prefix . '-' . $random .'-tts') . '.mp3';
    file_put_contents($temp_file, $data);

    $file = array(
        'name'     => basename( $temp_file ),
        'type'     => 'audio/mpeg',
        'tmp_name' => $temp_file,
        'size'     => filesize( $temp_file ),
    );

    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

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

    // add meta data to attachment
    update_post_meta( $attachment_id, 'aikit_elevenlabs_hash', md5($string) );

    return [
        'id' => $attachment_id,
        'url' => wp_get_attachment_url($attachment_id),
    ];
}
