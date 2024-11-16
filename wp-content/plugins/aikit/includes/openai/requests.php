<?php

const AIKIT_IMAGE_DESCRIPTION_GENERATION_MODEL = 'gpt-3.5-turbo';
const AIKIT_IMAGE_GENERATE_PROMPT_WORD_COUNT_THRESHOLD = 10;


function aikit_rest_openai_generate_images ($data) {
    ###['openai-generate-images']

	$count = $data['count'] ?? 1;
	$size = $data['size'] ?? 'small';
	$text = $data['text'] ?? '';

	if ( empty( $data['text'] ) ) {
		return new WP_Error( 'missing_param', 'Missing text parameter', array( 'status' => 400 ) );
	}

    try {
        $imagePrompt = aikit_openai_generate_image_prompt($text);

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        return new WP_Error( 'openai_error', json_encode([
            'message' => 'error while calling openai',
            'responseBody' => $e->getResponse()->getBody()->getContents(),
        ]), array( 'status' => 500 ) );
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        return new WP_Error( 'openai_error', json_encode([
            'message' => 'error while calling openai',
            'responseBody' => $e->getMessage(),
        ]), array( 'status' => 500 ) );
    }

	$dimensions = '256x256';
	if ($size == 'medium') {
		$dimensions = '512x512';
	} else if ($size == 'large') {
		$dimensions = '1024x1024';
	} else if ($size == 'xlarge landscape') {
        $dimensions = '1792x1024';
    } else if ($size == 'xlarge portrait') {
        $dimensions = '1024x1792';
    }

    $imagePrompt = aikit_apply_image_styles_to_prompt($imagePrompt);
    $image_model = get_option('aikit_setting_openai_image_model') ?? 'dall-e-2';

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();
	try {
        $request_body = [
            'model'      => $image_model,
            'prompt'      => $imagePrompt,
            'n'          => intval($count),
            'size' => $dimensions,
            'response_format' => 'url',
        ];

        if ($image_model == 'dall-e-3') {
            $request_body['quality'] = get_option('aikit_setting_openai_image_quality') === 'hd' ? 'hd' : 'standard';
            $request_body['style'] = get_option('aikit_setting_openai_image_style');
        }

		$imageResponse = $client->request( 'POST', 'https://api.openai.com/v1/images/generations', [
			'body'    => json_encode( $request_body ),
			'headers' => [
				'Authorization' => 'Bearer ' . get_option( 'aikit_setting_openai_key' ),
				'Content-Type'  => 'application/json',
			],
		] );

	} catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
		return new WP_Error( 'openai_error', json_encode([
			'message' => 'error while calling openai',
			'responseBody' => $e->getResponse()->getBody()->getContents(),
		]), array( 'status' => 500 ) );
	} catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
		return new WP_Error( 'openai_error', json_encode([
			'message' => 'error while calling openai',
			'responseBody' => $e->getMessage(),
		]), array( 'status' => 500 ) );
	}

	$body = $imageResponse->getBody();
	$json = json_decode($body, true);
	$data = $json['data'] ?? [];

	$images = array();
	foreach ($data as $image) {
		$imageUrl = aikit_upload_file_by_url($image['url'], $dimensions, $imagePrompt);
		if ($imageUrl) {
			$images[] = $imageUrl;
		}
	}

	return new WP_REST_Response([
		'images' => $images,
		'prompt' => $imagePrompt,
	], 200);
}

function aikit_openai_get_max_tokens_for_model($model) {
    if (strpos($model, 'gpt-3.5-turbo-16k') === 0) {
        return 2048;
    }

    if ($model == 'text-davinci-002' || $model == 'text-davinci-003' || strpos($model, 'gpt-3.5-turbo') === 0) {
        return 4000;
    }

    if (strpos($model, 'gpt-4-32k') === 0) {
        return 32000;
    }

    if (strpos($model, 'gpt-4') === 0) {
        return 8000;
    }

    return 2000;
}

$aikit_openai_models_cache = array();
function aikit_rest_openai_get_available_models($for = 'text', $from_cache = false, $is_checking_api_key = false) {

    $api_key = get_option( 'aikit_setting_openai_key' );
    global $aikit_openai_models_cache;

    if (!empty($aikit_openai_models_cache)) {
        return aikit_openai_apply_model_filters($aikit_openai_models_cache, $for);
    }

    $cached_models = get_option('aikit_openai_models_cache');
    if (!empty($cached_models) && $from_cache) {
        return aikit_openai_apply_model_filters($cached_models, $for);
    }

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    try {
        $res = $client->request('GET', 'https://api.openai.com/v1/models', [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
        ]);
    } catch (\Throwable $e) {
        if ($is_checking_api_key) {
            return false;
        }

        return aikit_openai_get_default_model_list();
    }

    if ($res->getStatusCode() !== 200) {
        if ($is_checking_api_key) {
            return false;
        }

        return aikit_openai_get_default_model_list();
    }

    $body = json_decode($res->getBody(), true);

    if (!isset($body['data'])) {
        return false;
    }

    $models = [];
    foreach ($body['data'] as $model) {
        $models[] = $model['id'];
    }

    // sort models by name
    sort($models);

    $aikit_openai_models_cache = $models;
    // cache in options
    update_option('aikit_openai_models_cache', $models);

    return aikit_openai_apply_model_filters($models, $for);
}

function aikit_openai_get_default_model_list() {
    return [
        'gpt-3.5-turbo',
        'gpt-3.5-turbo-0301',
        'text-davinci-003',
        'text-curie-001',
        'text-babbage-001',
        'text-ada-001',
        'text-davinci-001',
        'davinci',
        'davinci-instruct-beta',
        'curie-instruct-beta',
        'curie',
        'babbage',
        'ada',
    ];
}

function aikit_openai_apply_model_filters ($models, $for ) {
    if ($for == 'text') {
        $models = aikit_openai_filter_models_for_text($models);
    } else if ($for == 'embeddings') {
        $models = aikit_openai_filter_models_for_embeddings($models);
    } else if ($for == 'images') {
        $models = aikit_openai_filter_models_for_images($models);
    } else if ($for == 'tts') {
        $models = aikit_openai_filter_models_for_tts($models);
    } else if ($for == 'stt') {
        $models = aikit_openai_filter_models_for_stt($models);
    } else if ($for == 'fine-tuning') {
        $models = aikit_openai_filter_models_for_fine_tuning($models);
    }

    return $models;
}

function aikit_openai_filter_models_for_text($models) {
    $prefixes = [
        'gpt',
        'davinci',
        'curie',
        'babbage',
        'ada',
        'text-curie',
        'text-babbage',
        'text-ada',
    ];

    return aikit_openai_filter_models($models, $prefixes);
}

function aikit_openai_filter_models_for_embeddings($models)
{
    $prefixes = [
        'text-embedding',
    ];

    return aikit_openai_filter_models($models, $prefixes);
}

function aikit_openai_filter_models_for_fine_tuning($models)
{
    // for previously fine-tuned models
    $prefixes = [
        'davinci:',
        'curie:',
        'babbage:',
        'ada:',
    ];

    $fine_tuning_models = [
        'davinci',
        'curie',
        'babbage',
        'ada',
    ];

    return array_merge($fine_tuning_models, aikit_openai_filter_models($models, $prefixes));
}

function aikit_openai_filter_models_for_images($models)
{
    $prefixes = [
        'dall-e',
    ];

    return aikit_openai_filter_models($models, $prefixes);
}

function aikit_openai_filter_models_for_tts($models) {
    $prefixes = [
        'tts'
    ];

    return aikit_openai_filter_models($models, $prefixes);
}

function aikit_openai_filter_models_for_stt($models) {
    $prefixes = [
        'whisper'
    ];

    return aikit_openai_filter_models($models, $prefixes);
}

function aikit_openai_filter_models ($models, $prefixes) {
    $filteredModels = [];
    foreach ($models as $model) {
        foreach ($prefixes as $prefix) {
            if (strpos($model, $prefix) === 0) {
                $filteredModels[] = $model;
                break;
            }
        }
    }

    return $filteredModels;
}

function aikit_rest_openai_do_request ($data, $type, $language ) {

    $prompt_manager = new AIKit_Prompt_Manager();
    $promptsObject = $prompt_manager->get_prompts_by_language($language);

    if ( ! isset( $promptsObject[$type] ) ) {
        return new WP_Error( 'invalid_type', 'Invalid type', array( 'status' => 400 ) );
    }

    if ( ! isset( $data['text'] ) ) {
        return new WP_Error( 'missing_param', 'Missing text parameter', array( 'status' => 400 ) );
    }

    $prompt = $promptsObject[$type]['prompt'];
    $temperature = floatval($promptsObject[$type]['temperature'] ?? 0.7);

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
	$model = get_option('aikit_setting_openai_model');
    $prompt_stop_sequence = get_option('aikit_setting_prompt_stop_sequence');
    $completion_stop_sequence = get_option('aikit_setting_completion_stop_sequence');

    if (empty($completion_stop_sequence) || strpos($model, ':') === false) {
        $completion_stop_sequence = null;
    }

    // all fine-tuning models have a colon in their name
    if (!empty($prompt_stop_sequence) && strpos($model, ':') !== false) {
        $prompt .= $prompt_stop_sequence;
    }

    $maxTokenMultiplier = intval(1 + intval(get_option( 'aikit_setting_openai_max_tokens_multiplier' ) ?? 0) / 10);

    $promptWordLengthType = $promptsObject[$type]['wordLength']['type'];
    $promptWordLength = $promptsObject[$type]['wordLength']['value'];

    if ($promptWordLengthType == AIKIT_WORD_LENGTH_TYPE_FIXED) {
        $maxTokensToGenerate = intval($promptWordLength * 1.33);
    } else {
        $maxTokensToGenerate = intval($tokenizer->count($text) * $promptWordLength * 1.33);
    }

    $maxTokensToGenerate *= $maxTokenMultiplier;

	$theoreticalMaxTokensToGenerate = aikit_openai_get_max_tokens_for_model($model) - $tokenizer->count($prompt) - $tokenizer->count($text);

	$actualMaxTokensToGenerate = min($maxTokensToGenerate, $theoreticalMaxTokensToGenerate);

	if ($actualMaxTokensToGenerate < 0) {
		return new WP_Error( 'openai_error', json_encode([
			'message' => 'error while calling openai',
			'responseBody' => "Text is longer than model's context. Please try again with a shorter prompt.",
		]));
	}

    try {

        $res = $client->request('POST', aikit_get_openai_text_completion_endpoint($model), [
            'body' => aikit_openai_build_text_generation_request_body($model, $prompt, $actualMaxTokensToGenerate, $temperature, $completion_stop_sequence),
            'headers' => [
                'Authorization' => 'Bearer ' . get_option('aikit_setting_openai_key'),
                'Content-Type' => 'application/json',
            ],
        ]);
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        return new WP_Error( 'openai_error', json_encode([
            'message' => 'error while calling openai',
            'responseBody' => $e->getResponse()->getBody()->getContents(),
        ]), array( 'status' => 500 ) );
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        return new WP_Error( 'openai_error', json_encode([
            'message' => 'error while calling openai',
            'responseBody' => $e->getMessage(),
        ]), array( 'status' => 500 ) );
    }

    $body = $res->getBody();
    $json = json_decode($body, true);

    return aikit_openai_parse_text_generation_response($json, $model, $maxTokensToGenerate, $theoreticalMaxTokensToGenerate);
}

function aikit_get_openai_text_completion_endpoint ($model) {
    if (strpos($model, 'gpt-3.5-turbo') === 0 || strpos($model, 'gpt-4') === 0) {
        return 'https://api.openai.com/v1/chat/completions';
    }

    return 'https://api.openai.com/v1/completions';
}

function aikit_openai_build_text_generation_request_body ($model, $prompt, $maxTokensToGenerate, $temperature = 0.7, $stopSequence = null) {
    if (strpos($model, 'gpt-3.5-turbo') === 0 || strpos($model, 'gpt-4') === 0) {
        $messages = [];

        $systemMessage = get_option('aikit_setting_openai_system_message');
        if (!empty($systemMessage)) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemMessage,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $prompt,
        ];

        $result = [
            'model' => $model,
            'messages' => $messages,
            "temperature" => $temperature,
            'max_tokens' => intval($maxTokensToGenerate),
        ];

        if (!empty($stopSequence)) {
            $result['stop'] = $stopSequence;
        }

        return json_encode($result);
    }

    $result = [
        'model' => $model,
        'prompt' => $prompt,
        "temperature" => $temperature,
        'max_tokens' => intval($maxTokensToGenerate),
    ];

    if (!empty($stopSequence)) {
        $result['stop'] = $stopSequence;
    }

    return json_encode($result);
}

function aikit_openai_parse_text_generation_response ($responseJson, $model, $maxTokensToGenerate, $theoreticalMaxTokensToGenerate) {
    $choices = $responseJson['choices'];

    if ( count( $choices ) == 0 ) {
        return new WP_Error( 'no_choices', 'No completions found, please try again using different text.', array( 'status' => 400 ) );
    }

    $resultText = aikit_openai_get_text_generation_based_on_model($model, $choices);

    return new WP_REST_Response([
        'text' => $resultText,
        'tokens' => min(
            $maxTokensToGenerate,
            $theoreticalMaxTokensToGenerate
        )
    ], 200);
}

function aikit_openai_get_text_generation_based_on_model ($model, $choices) {
    if (strpos($model, 'gpt-3.5-turbo') === 0 || strpos($model, 'gpt-4') === 0 ) {
        return trim($choices[0]['message']['content']);
    }

    return trim($choices[0]['text']);
}

function aikit_openai_text_generation_request($prompt, $maxTokens, $temperature = 0.7, $model = null)
{
    try {
        if (empty($model)) {
            $model = get_option('aikit_setting_openai_model');
        }

        $prompt_stop_sequence = get_option('aikit_setting_prompt_stop_sequence');
        $completion_stop_sequence = get_option('aikit_setting_completion_stop_sequence');
        if (empty($completion_stop_sequence) || strpos($model, ':') === false) {
            $completion_stop_sequence = null;
        }

        // all fine-tuning models have a colon in their name
        if (!empty($prompt_stop_sequence) && strpos($model, ':') !== false) {
            $prompt .= $prompt_stop_sequence;
        }

        $maxTokens = min($maxTokens, aikit_openai_get_max_tokens_for_model($model));
        $client = new \AIKit\Dependencies\GuzzleHttp\Client();

        $res = $client->request('POST', aikit_get_openai_text_completion_endpoint($model), [
            'body' => aikit_openai_build_text_generation_request_body($model, $prompt, $maxTokens, $temperature, $completion_stop_sequence),
            'headers' => [
                'Authorization' => 'Bearer ' . get_option('aikit_setting_openai_key'),
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

    $choices = $json['choices'];

    return aikit_openai_get_text_generation_based_on_model($model, $choices);
}

function aikit_openai_chat_generation_request($prompt, $maxTokens, $context = '', $context_messages = [], $temperature = 0.7)
{
    try {
        $model = get_option('aikit_setting_chatbot_model');

        $prompt_stop_sequence = get_option('aikit_setting_chatbot_prompt_stop_sequence');
        $completion_stop_sequence = get_option('aikit_setting_chatbot_completion_stop_sequence');
        if (empty($completion_stop_sequence) || strpos($model, ':') === false) {
            $completion_stop_sequence = null;
        }

        // all fine-tuning models have a colon in their name
        if (!empty($prompt_stop_sequence) && strpos($model, ':') !== false) {
            $prompt .= $prompt_stop_sequence;
        }

        $client = new \AIKit\Dependencies\GuzzleHttp\Client();

        $res = $client->request('POST', aikit_get_openai_text_completion_endpoint($model), [
            'body' => aikit_openai_build_chat_generation_request_body($model, $prompt, $maxTokens, $context, $context_messages, $temperature, $completion_stop_sequence),
            'headers' => [
                'Authorization' => 'Bearer ' . get_option('aikit_setting_openai_key'),
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

    $choices = $json['choices'];

    return aikit_openai_get_text_generation_based_on_model($model, $choices);
}


function aikit_openai_build_chat_generation_request_body ($model, $prompt, $maxTokensToGenerate, $context = '', $context_messages = [], $temperature = 0.7, $stopSequence = null) {
    if (strpos($model, 'gpt-3.5-turbo') === 0 || strpos($model, 'gpt-4') === 0) {
        $messages = [];

        if (!empty($context)) {
            $messages[] = [
                'role' => 'system',
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
            "temperature" => $temperature,
            'max_tokens' => intval($maxTokensToGenerate),
        ];

        if (!empty($stopSequence)) {
            $result['stop'] = $stopSequence;
        }

        return json_encode($result);
    }

    $result = [
        'model' => $model,
        'prompt' => $prompt,
        "temperature" => $temperature,
        'max_tokens' => intval($maxTokensToGenerate),
    ];

    if (!empty($stopSequence)) {
        $result['stop'] = $stopSequence;
    }

    return json_encode($result);
}

function aikit_openai_image_generation_request($prompt, $dimensions='1024x1024') {

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    $image_prompt = aikit_apply_image_styles_to_prompt($prompt);

    $image_model = get_option('aikit_setting_openai_image_model') ?? 'dall-e-2';

    $request_body = [
        'model'      => $image_model,
        'prompt'      => $image_prompt,
        'n'          => 1,
        'size' => $dimensions,
        'response_format' => 'url',
    ];

    if ($image_model == 'dall-e-3') {
        $request_body['quality'] = get_option('aikit_setting_openai_image_quality') === 'hd' ? 'hd' : 'standard';
        $request_body['style'] = get_option('aikit_setting_openai_image_style');
    }

    $imageResponse = $client->request( 'POST', 'https://api.openai.com/v1/images/generations', [
        'body'    => json_encode($request_body),
        'headers' => [
            'Authorization' => 'Bearer ' . get_option( 'aikit_setting_openai_key' ),
            'Content-Type'  => 'application/json',
        ],
    ]);

    $body = $imageResponse->getBody();
    $json = json_decode($body, true);
    $data = $json['data'] ?? [];

    $images = array();
    foreach ($data as $image) {
        $imageUrl = aikit_upload_file_by_url($image['url'], $dimensions, $image_prompt);
        if ($imageUrl) {
            $images[] = $imageUrl;
        }
    }

    return $images;
}

function aikit_openai_generate_image_prompt ($text) {

    // if text has less than 10 words, then return the text as is
    $wordCount = aikit_calculate_word_count_utf8($text);
    if ($wordCount <= AIKIT_IMAGE_GENERATE_PROMPT_WORD_COUNT_THRESHOLD) {
        return $text;
    }

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();
    $model = AIKIT_IMAGE_DESCRIPTION_GENERATION_MODEL;
    $prompt = sprintf("Describe an image that would be best fit for this text:\n\n %s\n\n----\nCreative image description in one sentence of 6 words:\n", $text);
    $maxTokens = 50;

    $res = $client->request( 'POST', aikit_get_openai_text_completion_endpoint($model), [
        'body'    => aikit_openai_build_text_generation_request_body($model, $prompt, $maxTokens, 0.7),
        'headers' => [
            'Authorization' => 'Bearer ' . get_option( 'aikit_setting_openai_key' ),
            'Content-Type'  => 'application/json',
        ],
    ] );

    $body = $res->getBody();
    $json = json_decode($body, true);

    $choices = $json['choices'];

    if ( count( $choices ) == 0 ) {
        return new WP_Error( 'no_choices', 'Could not generate image prompt', array( 'status' => 400 ) );
    }

    if (strpos($model, 'gpt-3.5-turbo') === 0 || strpos($model, 'gpt-4') === 0) {
        $imagePrompt = trim($choices[0]['message']['content']);
    } else {
        $imagePrompt = trim($choices[0]['text']);
    }

    return $imagePrompt;
}

function aikit_openai_text_to_speech($string) {
    ###['openai-text-to-speech']

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    $voice = get_option('aikit_setting_openai_tts_voice');
    $model = get_option('aikit_setting_openai_tts_model');

    try {
        $response = $client->request( 'POST', 'https://api.openai.com/v1/audio/speech', [
            'body'    => json_encode( [
                'input' => $string,
                'model' => $model,
                'voice' => $voice,
            ] ),
            'headers' => [
                'Authorization' => 'Bearer ' . get_option( 'aikit_setting_openai_key' ),
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
    update_post_meta( $attachment_id, 'aikit_openai_hash', md5($string) );

    return [
        'id' => $attachment_id,
        'url' => wp_get_attachment_url($attachment_id),
    ];
}

function aikit_openai_speech_to_text($file_path) {
    ###['openai-speech-to-text']

    $client = new \AIKit\Dependencies\GuzzleHttp\Client();

    try {
        $response = $client->request( 'POST', 'https://api.openai.com/v1/audio/transcriptions', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => \AIKit\Dependencies\GuzzleHttp\Psr7\Utils::tryFopen($file_path, 'r'),
                    'filename' => 'audio.webm',
                    'headers'  => [
                        'Content-Type' => 'audio/webm',
                    ],
                ],
                [
                     'name'     => 'model',
                     'contents' => 'whisper-1'
                ],
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . get_option( 'aikit_setting_openai_key' ),
            ],
        ] );

    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\ClientException $e) {
        throw $e;
    } catch (\AIKit\Dependencies\GuzzleHttp\Exception\GuzzleException $e) {
        throw $e;
    }

    $body = $response->getBody();
    $json = json_decode($body, true);

    return $json['text'];
}
