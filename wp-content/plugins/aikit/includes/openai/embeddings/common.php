<?php

function aikit_build_embeddings_vector($string)
{
    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    try {
        $imageResponse = $client->request( 'POST', 'https://api.openai.com/v1/embeddings', [
            'body'    => json_encode( [
                'model'  => AIKIT_Embeddings::OPENAI_EMBEDDINGS_MODEL_NAME,
                'input'  => $string,
            ] ),
            'headers' => [
                'Authorization' => 'Bearer ' . get_option( 'aikit_setting_openai_key' ),
                'Content-Type'  => 'application/json',
            ],
        ] );

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        return new WP_Error( 'openai_error', json_encode([
            'message' => 'error while calling openai embeddings',
            'responseBody' => $e->getResponse()->getBody()->getContents(),
        ]), array( 'status' => 500 ) );
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        return new WP_Error( 'openai_error', json_encode([
            'message' => 'error while calling openai embeddings',
            'responseBody' => $e->getMessage(),
        ]), array( 'status' => 500 ) );
    }

    $body = $imageResponse->getBody();
    $json = json_decode($body, true);
    $data = $json['data'] ?? [];

    if (empty($data)) {
        throw new Exception('OpenAI error - could not get data. Error details: ' . json_encode($json));
    }

    $data = $data[0] ?? [];

    $embedding = $data['embedding'] ?? [];

    if (empty($embedding)) {
        throw new Exception('OpenAI error - could not get embeddings. Error details: ' . json_encode($json));
    }

    return $embedding;
}


function aikit_adjust_string_to_max_token_count($string, $max_token_count = 2000)
{
    $tokenizer = new AIKIT_Gpt3Tokenizer(new AIKIT_Gpt3TokenizerConfig());
    $token_count = $tokenizer->count($string);

    if ($token_count <= $max_token_count) {
        return [$string];
    }

    $pieces_count = ceil($token_count / $max_token_count) + 1;
    $pieces = array();

    $string_parts = preg_split('/\s+/u', $string);
    $string_parts_count = count($string_parts);

    for ($i = 0; $i < $pieces_count; $i++) {
        $start_index = floor($i * $string_parts_count / $pieces_count);
        $end_index = floor(($i + 1) * $string_parts_count / $pieces_count);
        $pieces[] = implode(' ', array_slice($string_parts, $start_index, $end_index - $start_index));
    }

    return $pieces;
}
