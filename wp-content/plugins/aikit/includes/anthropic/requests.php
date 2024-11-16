<?php

function aikit_rest_anthropic_do_request($data, $type, $language )
{
    $prompt_manager = new AIKit_Prompt_Manager();
    $promptsObject = $prompt_manager->get_prompts_by_language($language);

    if ( ! isset( $promptsObject[$type] ) ) {
        return new WP_Error( 'invalid_type', 'Invalid type', array( 'status' => 400 ) );
    }

    if ( ! isset( $data['text'] ) ) {
        return new WP_Error( 'missing_param', 'Missing text parameter', array( 'status' => 400 ) );
    }

    $prompt = $promptsObject[$type]['prompt'];

    $context = $data['context'] ?? [];

    if ($promptsObject[$type]['requiresTextSelection']) {
        $prompt = aikit_add_selected_text_to_prompt($prompt, $data['text']);
    }

    if ( ! empty( $context ) && isset($context['postTitle']) ) {
        $prompt = aikit_add_post_title_to_prompt($prompt, $context['postTitle']);
    }

    if ( ! empty( $context ) && isset($context['textBeforeSelection']) ) {
        $prompt = aikit_add_text_before_to_prompt($prompt, $context['textBeforeSelection']);
    }

    if ( ! empty( $context ) && isset($context['textAfterSelection']) ) {
        $prompt = aikit_add_text_after_to_prompt($prompt, $context['textAfterSelection']);
    }

    $tokenizer = AIKIT_Gpt3Tokenizer::getInstance();

    $text = $data['text'];
    $client = new \AIKit\Dependencies\GuzzleHttp\Client();
    $model = get_option('aikit_setting_anthropic_model');

    $promptWordLengthType = $promptsObject[$type]['wordLength']['type'];
    $promptWordLength = $promptsObject[$type]['wordLength']['value'];

    if ($promptWordLengthType == AIKIT_WORD_LENGTH_TYPE_FIXED) {
        $maxTokensToGenerate = intval($promptWordLength * 1.33);
    } else {
        $maxTokensToGenerate = intval($tokenizer->count($text) * $promptWordLength * 1.33);
    }

    $theoreticalMaxTokensToGenerate = aikit_anthropic_get_max_tokens_for_model($model);

    $actualMaxTokensToGenerate = min($maxTokensToGenerate, $theoreticalMaxTokensToGenerate);

    if ($actualMaxTokensToGenerate < 0) {
        return new WP_Error( 'openai_error', json_encode([
            'message' => 'error while calling anthropic',
            'responseBody' => "Text is longer than model's context. Please try again with a shorter prompt.",
        ]));
    }

    try {

        $res = $client->request('POST', aikit_get_anthropic_text_completion_endpoint(), [
            'body' => aikit_anthropic_build_text_generation_request_body($model, $prompt, $actualMaxTokensToGenerate),
            'headers' => [
                'anthropic-version' => AIKIT_ANTHROPIC_VERSION,
                'x-api-key' => get_option('aikit_setting_anthropic_key'),
                'Content-Type' => 'application/json',
            ],
        ]);
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        return new WP_Error( 'anthropic_error', json_encode([
            'message' => 'error while calling anthropic',
            'responseBody' => $e->getResponse()->getBody()->getContents(),
        ]), array( 'status' => 500 ) );
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        return new WP_Error( 'anthropic_error', json_encode([
            'message' => 'error while calling anthropic',
            'responseBody' => $e->getMessage(),
        ]), array( 'status' => 500 ) );
    }

    $body = $res->getBody();
    $json = json_decode($body, true);

    return aikit_anthropic_parse_text_generation_response($json, $maxTokensToGenerate, $theoreticalMaxTokensToGenerate);
}

function aikit_anthropic_parse_text_generation_response ($responseJson, $maxTokensToGenerate, $theoreticalMaxTokensToGenerate) {
    $content = $responseJson['content'];

    if ( count( $content ) == 0 ) {
        return new WP_Error( 'no_choices', 'No completions found, please try again using different text.', array( 'status' => 400 ) );
    }

    $resultText = $content[0]['text'];

    return new WP_REST_Response([
        'text' => $resultText,
        'tokens' => min(
            $maxTokensToGenerate,
            $theoreticalMaxTokensToGenerate
        )
    ], 200);
}

function aikit_anthropic_chat_generation_request($prompt, $maxTokens, $context = '', $context_messages = [], $temperature = 0.7)
{
    try {
        $model = get_option('aikit_setting_anthropic_model');

        $client = new \AIKit\Dependencies\GuzzleHttp\Client();

        $res = $client->request('POST', aikit_get_anthropic_text_completion_endpoint(), [
            'body' => aikit_anthropic_build_chat_generation_request_body($model, $prompt, $maxTokens, $context, $context_messages),
            'headers' => [
                'anthropic-version' => AIKIT_ANTHROPIC_VERSION,
                'x-api-key' => get_option('aikit_setting_anthropic_key'),
                'Content-Type' => 'application/json',
            ],
        ]);

        $body = $res->getBody();
        $json = json_decode($body, true);
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $message .= "\n\nPrompt: " . $prompt;
        $message .= "\n\nModel: " . $model;
        $message .= "\n\nMax tokens: " . $maxTokens;

        throw new \Exception($message);
    }

    return aikit_anthropic_parse_text_generation_response($json, $maxTokens, $maxTokens);
}

function aikit_anthropic_build_chat_generation_request_body ($model, $prompt, $maxTokensToGenerate, $context = '', $context_messages = []) {
    $messages = [];

    if (!empty($context)) {
        $messages[] = [
            'role' => 'user',
            'content' => $context,
        ];
    }

    foreach ($context_messages as $context_message) {
        $role = $context_message['role'] ?? 'user';
        $message = $context_message['message'] ?? '';

        $messages[] = [
            'role' => $role == 'user' ? 'user' : 'assistant',
            'content' => $message,
        ];
    }

    $messages[] = [
        'role' => 'user',
        'content' => $prompt,
    ];

    $result = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => intval($maxTokensToGenerate),
    ];

    return json_encode($result);
}

function aikit_anthropic_get_text_generation_based_from_response ($responseJson) {
    $content = $responseJson['content'];

    if ( count( $content ) == 0 ) {
        return new WP_Error( 'no_choices', 'No completions found, please try again using different text.', array( 'status' => 400 ) );
    }

    return $content[0]['text'];
}

function aikit_anthropic_text_generation_request($prompt, $maxTokens, $model = null)
{
    try {
        if (empty($model)) {
            $model = get_option('aikit_setting_anthropic_model');
        }

        $maxTokens = min($maxTokens, aikit_anthropic_get_max_tokens_for_model($model));
        $client = new \AIKit\Dependencies\GuzzleHttp\Client();

        $res = $client->request('POST', aikit_get_anthropic_text_completion_endpoint(), [
            'body' => aikit_anthropic_build_text_generation_request_body($model, $prompt, $maxTokens),
            'headers' => [
                'anthropic-version' => AIKIT_ANTHROPIC_VERSION,
                'x-api-key' => get_option('aikit_setting_anthropic_key'),
                'Content-Type' => 'application/json',
            ],
        ]);

        $body = $res->getBody();
        $json = json_decode($body, true);
    } catch (\Throwable $e) {
        $message = $e->getMessage();
        $message .= "\n\nPrompt: " . $prompt;
        $message .= "\n\nModel: " . $model;
        $message .= "\n\nMax tokens: " . $maxTokens;

        throw new \Exception($message);
    }

    return aikit_anthropic_get_text_generation_based_from_response($json);
}

function aikit_get_anthropic_text_completion_endpoint () {
    return 'https://api.anthropic.com/v1/messages';
}

function aikit_anthropic_build_text_generation_request_body ($model, $prompt, $maxTokensToGenerate) {

    $messages = [];

    $messages[] = [
        'role' => 'user',
        'content' => $prompt,
    ];

    $result = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => intval($maxTokensToGenerate),
    ];


    return json_encode($result);
}

function aikit_anthropic_get_max_tokens_for_model($model) {

    return 4096;
}