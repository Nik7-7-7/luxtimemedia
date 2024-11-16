<?php

add_action( 'rest_api_init', function () {
    register_rest_route( 'aikit/openai/v1', '/autocomplete', array(
        'methods' => 'POST',
        'callback' => 'aikit_rest_ai_autocomplete',
        'permission_callback' => function () {
            return is_user_logged_in() && current_user_can( 'edit_posts' );
        }
    ));

	register_rest_route( 'aikit/openai/v1', '/generate-images', array(
		'methods' => 'POST',
		'callback' => 'aikit_rest_openai_generate_images',
		'permission_callback' => function () {
			return is_user_logged_in() && current_user_can( 'edit_posts' );
		}
	));
} );

function aikit_rest_ai_autocomplete($data ) {
    ###['openai-generate-text']

    $type = $data['type'] ?? '';

    $language = $data['language'] ?? 'en';

    $ai_provider = get_option('aikit_setting_default_ai_text_provider') ?? AIKIT_DEFAULT_AI_TEXT_PROVIDER;

    switch ($ai_provider) {
        case 'anthropic':
            return aikit_rest_anthropic_do_request( $data, $type, $language );
        case 'openai':
            return aikit_rest_openai_do_request( $data, $type, $language );
        default:
            return new WP_Error( 'ai_provider_error', json_encode([
                'message' => 'no default ai provider set',
            ]), array( 'status' => 500 ) );
    }
}

function aikit_ai_text_generation_request($prompt, $maxTokens, $temperature = 0.7, $model = null, $provider = null)
{
    ###['openai-generate-text-2']

    $ai_provider = $provider ?? get_option('aikit_setting_default_ai_text_provider') ?? AIKIT_DEFAULT_AI_TEXT_PROVIDER;

    switch ($ai_provider) {
        case 'anthropic':
            return aikit_anthropic_text_generation_request($prompt, $maxTokens, $model);
        case 'openai':
            return aikit_openai_text_generation_request($prompt, $maxTokens, $temperature, $model);
        default:
            return new WP_Error( 'ai_provider_error', json_encode([
                'message' => 'no default ai provider set',
            ]), array( 'status' => 500 ) );
    }
}

function aikit_ai_chat_generation_request($prompt, $maxTokens, $context = '', $context_messages = [], $temperature = 0.7, $provider = null)
{
    ###['openai-generate-text-3']

    $ai_provider = $provider ?? get_option('aikit_setting_default_ai_text_provider') ?? AIKIT_DEFAULT_AI_TEXT_PROVIDER;

    switch ($ai_provider) {
        case 'anthropic':
            return aikit_anthropic_chat_generation_request($prompt, $maxTokens, $context, $context_messages, $temperature);
        case 'openai':
            return aikit_openai_chat_generation_request($prompt, $maxTokens, $context, $context_messages, $temperature);
        default:
            return new WP_Error( 'ai_provider_error', json_encode([
                'message' => 'no default ai provider set',
            ]), array( 'status' => 500 ) );
    }
}

function aikit_add_selected_text_to_prompt ($prompt, $selected_text) {
    return str_replace('[[text]]', $selected_text, $prompt);
}

function aikit_add_post_title_to_prompt ($prompt, $post_title) {
    return str_replace('[[post_title]]', $post_title, $prompt);
}

function aikit_add_text_before_to_prompt ($prompt, $text_before)
{
    return str_replace('[[text_before]]', $text_before, $prompt);
}

function aikit_add_text_after_to_prompt ($prompt, $text_after)
{
    return str_replace('[[text_after]]', $text_after, $prompt);
}

function aikit_apply_image_styles_to_prompt($prompt) {
    $stylesArray = get_option( 'aikit_setting_images_styles' );
    $stylesArray = explode("\n", $stylesArray);
    $imagePrompt = rtrim(rtrim($prompt), '.');

    if (!empty($stylesArray)) {
        $style = $stylesArray[array_rand($stylesArray)];
        $imagePrompt .= ', ' . $style;
    }

    $imagePrompt = str_replace('"', '', $imagePrompt);

    return str_replace("'", '', $imagePrompt);
}

function aikit_get_language_used() {
    // get language from the saved settings
    return get_option('aikit_setting_openai_language', 'en');
}

function aikit_calculate_word_count_utf8($str) {
    return count(preg_split('~[^\p{L}\p{N}\']+~u', $str));
}
