<?php

/**
 * Plugin Name:       AIKit
 * Plugin URI:        https://codecanyon.net/item/aikit-wordpress-ai-writing-assistant-using-gpt3/40507643
 * Description:       AIKit is your WordPress AI assistant, powered by OpenAI's GPT, Anthropic Claude, DALL.E & StabilityAI's Stable Diffusion.
 * Version:           4.17.1
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Domain Path:       /languages
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/includes/constants.php';
require __DIR__ . '/includes/page.php';
require __DIR__ . '/includes/string-processing.php';
require __DIR__ . '/includes/openai/tokenization/merges.php';
require __DIR__ . '/includes/openai/tokenization/vocab.php';
require __DIR__ . '/includes/openai/tokenization/config.php';
require __DIR__ . '/includes/openai/tokenization/tokenizer.php';
require __DIR__ . '/includes/openai/prompt-manager.php';
require __DIR__ . '/includes/openai/initial-prompts.php';
require __DIR__ . '/includes/import-export.php';
require __DIR__ . '/includes/openai/chatbot/chatbot-settings.php';
require __DIR__ . '/includes/openai/chatbot/chatbot.php';
require __DIR__ . '/includes/openai/embeddings/common.php';
require __DIR__ . '/includes/openai/embeddings/embeddings-local-connector.php';
require __DIR__ . '/includes/openai/embeddings/embeddings-qdrant-connector.php';
require __DIR__ . '/includes/openai/embeddings/embeddings-connector.php';
require __DIR__ . '/includes/openai/embeddings/embeddings.php';
require __DIR__ . '/includes/openai/fine-tuner/fine-tune-job-builder.php';
require __DIR__ . '/includes/openai/post-finder.php';
require __DIR__ . '/includes/openai/fine-tuner/fine-tuner.php';
require __DIR__ . '/includes/openai/auto-writer/auto-writer-form.php';
require __DIR__ . '/includes/openai/auto-writer/auto-writer-prompts.php';
require __DIR__ . '/includes/openai/auto-writer/auto-writer.php';
require __DIR__ . '/includes/openai/repurposer/youtube-subtitles.php';
require __DIR__ . '/includes/openai/repurposer/repurposer-prompts.php';
require __DIR__ . '/includes/openai/repurposer/repurposer.php';
require __DIR__ . '/includes/openai/rss/rss.php';
require __DIR__ . '/includes/openai/comments/comments-prompts.php';
require __DIR__ . '/includes/openai/comments/comments.php';
require __DIR__ . '/includes/elevenlabs/audio-player.php';
require __DIR__ . '/includes/tts/text-to-speech.php';
require __DIR__ . '/includes/elevenlabs/requests.php';
require __DIR__ . '/includes/admin.php';
require __DIR__ . '/includes/common.php';
require __DIR__ . '/includes/facade.php';
require __DIR__ . '/includes/openai/requests.php';
require __DIR__ . '/includes/anthropic/requests.php';
require __DIR__ . '/includes/stabilityai/utils.php';
require __DIR__ . '/includes/stabilityai/requests.php';


function aikit_block_assets( $hook ) {

    $dependencies = require __DIR__ . '/fe/build/index.asset.php';

	aikit_add_inline_js_object();

    wp_register_style( 'aikit_index_css', plugin_dir_url( __FILE__ ) . 'fe/build/style-index.css', false, $dependencies['version'] );
    wp_enqueue_style ( 'aikit_index_css' );
}

add_action( 'enqueue_block_assets', 'aikit_block_assets' );


add_action( 'init', 'aikit_load_textdomain' );

$aikit_tss = AIKIT_Text_To_Speech::get_instance(); // to initialize the hooks
$aikit_comments = AIKIT_Comments::get_instance(); // to initialize the hooks

function aikit_load_textdomain() {
    if ( ! is_admin() ) {
        return;
    }

    // get current language
    $currentLanguage = get_locale();

    if (strlen($currentLanguage) > 2) {
        $currentLanguage = explode('_', $currentLanguage)[0];
    }

    // load language regardless of locale
    load_textdomain( 'aikit', __DIR__ . "/languages/$currentLanguage.mo" );
}

/* Add admin notice */
add_action( 'admin_notices', 'aikit_admin_configure_notice' );


/**
 * Admin Notice on Activation.
 * @since 0.1.0
 */
function aikit_admin_configure_notice() {

    global $pagenow;

    if ($pagenow !== 'plugins.php') {
        return;
    }

    $openAiKey = get_option( 'aikit_setting_openai_key' );

    if (strlen($openAiKey) == 0) {
        ?>
        <div id="aikit-notice" class="updated notice is-dismissible">

            <div class="aikit-notice-txt">
                <p>
                    <?php echo esc_html__('Thank you for using AIKit! Please consider entering your OpenAI key in order to start leveraging AI content generation.', 'aikit')?>
                </p>
            </div>
            <div class="aikit-btn-container">
                <a href="<?php echo admin_url( 'admin.php?page=aikit' ); ?>" id="aikit-btn"><?php echo esc_html__('Configure AIKit', 'aikit')?></a>
            </div>
        </div>
        <?php
    }
}

function aikit_init() {
     if ( ! is_admin() ) {
         return;
     }
    // Register our script just like we would enqueue it - for WordPress references
    $dependencies = require __DIR__ . '/fe/build/index.asset.php';
    wp_register_script( 'aikit_index_js', plugin_dir_url( __FILE__ ) . 'fe/build/index.js', $dependencies['dependencies'], $dependencies['version'] );

    wp_set_script_translations( 'aikit_index_js', 'aikit', plugin_dir_path( __FILE__ ) . 'languages' );

    if (aikit_get_plugin_version() !== get_option('aikit_plugin_version')) {
        aikit_set_default_settings();
        $auto_writer = AIKIT_Auto_Writer::get_instance();
        $auto_writer->do_db_migration();
        $repurposer = AIKIT_Repurposer::get_instance();
        $repurposer->do_db_migration();
        $rss = AIKIT_RSS::get_instance();
        $rss->do_db_migration();
        $fine_tuner = AIKIT_Fine_Tuner::get_instance();
        $fine_tuner->do_db_migration();
        $embeddings = AIKIT_Embeddings::get_instance();
        $embeddings->do_db_migration();
        $chat = AIKIT_Chatbot::get_instance();
        $chat->do_db_migration();
        $tts = AIKIT_Text_To_Speech::get_instance();
        $tts->do_db_migration();
        $comments = AIKIT_Comments::get_instance();
        $comments->do_db_migration();

        $auto_writer->activate_scheduler();
        $repurposer->activate_scheduler();
        $rss->activate_scheduler();
        $fine_tuner->activate_scheduler();
        $embeddings->activate_scheduler();
        $tts->activate_scheduler();
        $comments->activate_scheduler();
        update_option('aikit_plugin_version', aikit_get_plugin_version());
    }
}

add_action( 'init', 'aikit_init' );

// register an uninstall hook
register_uninstall_hook( __FILE__, 'aikit_uninstall' );

function aikit_uninstall() {
    delete_option( 'aikit_setting_openai_key' );
    delete_option( 'aikit_plugin_version' );
    delete_option( 'aikit_setting_openai_key_valid' );
    delete_option( 'aikit_setting_openai_language' );
    delete_option( 'aikit_setting_openai_model' );
    delete_option( 'aikit_setting_openai_image_model' );
    delete_option( 'aikit_setting_openai_image_quality' );
    delete_option( 'aikit_setting_openai_image_style' );
    delete_option( 'aikit_setting_openai_tts_voice' );
    delete_option( 'aikit_setting_default_tts_api' );
    delete_option( 'aikit_setting_openai_tts_model' );
    delete_option( 'aikit_setting_openai_available_models' );
    delete_option( 'aikit_setting_autocompleted_text_background_color' );
    delete_option( 'aikit_setting_openai_max_tokens_multiplier' );
    delete_option( 'aikit_setting_images_size_small' );
    delete_option( 'aikit_setting_images_size_medium' );
    delete_option( 'aikit_setting_images_size_large' );
    delete_option( 'aikit_setting_images_counts' );
    delete_option( 'aikit_setting_images_styles' );
    delete_option( 'aikit_setting_elementor_supported' );
    delete_option( 'aikit_setting_openai_system_message' );
    delete_option( 'aikit_setting_prompt_stop_sequence' );
    delete_option( 'aikit_setting_completion_stop_sequence' );
    delete_option( 'aikit_setting_chatbot_enabled' );
    delete_option( 'aikit_setting_chatbot_default_view' );
    delete_option( 'aikit_setting_chatbot_model' );
    delete_option( 'aikit_setting_chatbot_show_on' );
    delete_option( 'aikit_setting_chatbot_context' );
    delete_option( 'aikit_setting_chatbot_is_page_content_aware' );
    delete_option( 'aikit_setting_chatbot_max_response_tokens' );
    delete_option( 'aikit_setting_chatbot_show_only_for_roles' );
    delete_option( 'aikit_setting_chatbot_appearance_title' );
    delete_option( 'aikit_setting_chatbot_appearance_input_placeholder' );
    delete_option( 'aikit_setting_chatbot_appearance_start_message' );
    delete_option( 'aikit_setting_chatbot_appearance_width' );
    delete_option( 'aikit_setting_chatbot_appearance_main_color' );
    delete_option( 'aikit_setting_chatbot_appearance_title_color' );
    delete_option( 'aikit_setting_chatbot_appearance_ai_message_bubble_color' );
    delete_option( 'aikit_setting_chatbot_appearance_ai_message_text_color' );
    delete_option( 'aikit_setting_chatbot_appearance_user_message_bubble_color' );
    delete_option( 'aikit_setting_chatbot_appearance_user_message_text_color' );
    delete_option( 'aikit_setting_stabilityai_default_engine' );
    delete_option( 'aikit_setting_stabilityai_default_sampler' );
    delete_option( 'aikit_setting_stabilityai_default_steps' );
    delete_option( 'aikit_setting_stabilityai_default_cfg_scale' );
    delete_option( 'aikit_setting_stabilityai_default_seed' );
    delete_option( 'aikit_setting_qdrant_host' );
    delete_option( 'aikit_setting_qdrant_api_key' );
    delete_option( 'aikit_setting_chatbot_use_embeddings' );
    delete_option( 'aikit_setting_chatbot_embeddings_answer_formulation_prompt' );
    delete_option( 'aikit_setting_chatbot_selected_embedding' );


    delete_option( 'aikit_prompts' );

	$languages = AIKit_Admin::instance()->get_languages();

    foreach ($languages as $language => $obj) {
        delete_option( 'aikit_prompts_' . $language );
    }

    $auto_writer = AIKIT_Auto_Writer::get_instance();
    $auto_writer->deactivate_scheduler();

    $repurposer = AIKIT_Repurposer::get_instance();
    $repurposer->deactivate_scheduler();

    $rss = AIKIT_RSS::get_instance();
    $rss->deactivate_scheduler();

    $fine_tuner = AIKIT_Fine_Tuner::get_instance();
    $fine_tuner->deactivate_scheduler();

    $embeddings = AIKIT_Embeddings::get_instance();
    $embeddings->deactivate_scheduler();

    $tts = AIKIT_Text_To_Speech::get_instance();
    $tts->deactivate_scheduler();

    $comments = AIKIT_Comments::get_instance();
    $comments->deactivate_scheduler();
}

register_deactivation_hook(
    __FILE__,
    'aikit_on_deactivation'
);

function aikit_on_deactivation() {
    $auto_writer = AIKIT_Auto_Writer::get_instance();
    $auto_writer->deactivate_scheduler();
    $repurposer = AIKIT_Repurposer::get_instance();
    $repurposer->deactivate_scheduler();
    $rss = AIKIT_RSS::get_instance();
    $rss->deactivate_scheduler();
    $fine_tuner = AIKIT_Fine_Tuner::get_instance();
    $fine_tuner->deactivate_scheduler();
    $embeddings = AIKIT_Embeddings::get_instance();
    $embeddings->deactivate_scheduler();
    $tts = AIKIT_Text_To_Speech::get_instance();
    $tts->deactivate_scheduler();
    $comments = AIKIT_Comments::get_instance();
    $comments->deactivate_scheduler();
}

register_activation_hook(
	__FILE__,
	'aikit_on_activation'
);

function aikit_on_activation() {
    aikit_set_default_settings();
    $auto_writer = AIKIT_Auto_Writer::get_instance();
    $auto_writer->activate_scheduler();
    $repurposer = AIKIT_Repurposer::get_instance();
    $repurposer->activate_scheduler();
    $rss = AIKIT_RSS::get_instance();
    $rss->activate_scheduler();
    $fine_tuner = AIKIT_Fine_Tuner::get_instance();
    $fine_tuner->activate_scheduler();
    $embeddings = AIKIT_Embeddings::get_instance();
    $embeddings->activate_scheduler();
    $tts = AIKIT_Text_To_Speech::get_instance();
    $tts->activate_scheduler();
    $comments = AIKIT_Comments::get_instance();
    $comments->activate_scheduler();
}

function aikit_set_default_settings () {

	if (get_option('aikit_setting_images_size_small') === false) {
		update_option('aikit_setting_images_size_small', AIKIT_DEFAULT_SETTING_IMAGES_SIZES_SMALL);
	}

	if (get_option('aikit_setting_images_size_medium') === false) {
		update_option('aikit_setting_images_size_medium', AIKIT_DEFAULT_SETTING_IMAGES_SIZES_MEDIUM);
	}

	if (get_option('aikit_setting_images_size_large') === false) {
		update_option('aikit_setting_images_size_large', AIKIT_DEFAULT_SETTING_IMAGES_SIZES_LARGE);
	}

	if (get_option('aikit_setting_images_counts') === false) {
		update_option('aikit_setting_images_counts', AIKIT_DEFAULT_SETTING_IMAGES_COUNTS);
	}

    if (get_option('aikit_setting_images_styles') === false) {
		update_option('aikit_setting_images_styles', AIKIT_DEFAULT_SETTING_IMAGES_STYLES);
	}

    if (get_option('aikit_setting_openai_language') === false) {
        update_option('aikit_setting_openai_language', AIKIT_DEFAULT_SETTING_SELECTED_LANGUAGE);
    }

    if (get_option('aikit_setting_elementor_supported') === false) {
        update_option('aikit_setting_elementor_supported', AIKIT_DEFAULT_SETTING_ELEMENTOR_SUPPORTED);
    }

    if (get_option('aikit_setting_chatbot_enabled') === false) {
        update_option('aikit_setting_chatbot_enabled', AIKIT_DEFAULT_SETTING_CHATBOT_ENABLED);
    }

    if (get_option('aikit_setting_chatbot_voice_enabled') === false) {
        update_option('aikit_setting_chatbot_voice_enabled', AIKIT_DEFAULT_SETTING_CHATBOT_VOICE_ENABLED);
    }

    if (get_option('aikit_setting_chatbot_default_view') === false) {
        update_option('aikit_setting_chatbot_default_view', AIKIT_DEFAULT_SETTING_DEFAULT_VIEW);
    }

    if (get_option('aikit_setting_chatbot_model') === false) {
        update_option('aikit_setting_chatbot_model', AIKIT_DEFAULT_SETTING_CHATBOT_MODEL);
    }

    if (get_option('aikit_setting_chatbot_show_on') === false) {
        update_option('aikit_setting_chatbot_show_on', AIKIT_DEFAULT_SETTING_CHATBOT_SHOW_ON);
    }

    if (get_option('aikit_setting_chatbot_context') === false) {
        update_option('aikit_setting_chatbot_context', AIKIT_DEFAULT_SETTING_CHATBOT_CONTEXT);
    }

    if (get_option('aikit_setting_chatbot_is_page_content_aware') === false) {
        update_option('aikit_setting_chatbot_is_page_content_aware', AIKIT_DEFAULT_SETTING_CHATBOT_IS_PAGE_CONTENT_AWARE);
    }

    if (get_option('aikit_setting_chatbot_max_response_tokens') === false) {
        update_option('aikit_setting_chatbot_max_response_tokens', AIKIT_DEFAULT_SETTING_CHATBOT_MAX_RESPONSE_TOKENS);
    }

    if (get_option('aikit_setting_chatbot_show_only_for_roles') === false) {
        update_option('aikit_setting_chatbot_show_only_for_roles', AIKIT_DEFAULT_SETTING_CHATBOT_SHOW_ONLY_FOR_ROLES);
    }

    if (get_option('aikit_setting_chatbot_appearance_title') === false) {
        update_option('aikit_setting_chatbot_appearance_title', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_TITLE);
    }

    if (get_option('aikit_setting_chatbot_appearance_input_placeholder') === false) {
        update_option('aikit_setting_chatbot_appearance_input_placeholder', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_INPUT_PLACEHOLDER);
    }

    if (get_option('aikit_setting_chatbot_appearance_start_message') === false) {
        update_option('aikit_setting_chatbot_appearance_start_message', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_START_MESSAGE);
    }

    if (get_option('aikit_setting_chatbot_appearance_main_color') === false) {
        update_option('aikit_setting_chatbot_appearance_main_color', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_MAIN_COLOR);
    }

    if (get_option('aikit_setting_chatbot_appearance_secondary_color') === false) {
        update_option('aikit_setting_chatbot_appearance_secondary_color', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_SECONDARY_COLOR);
    }

    if (get_option('aikit_setting_chatbot_appearance_title_color') === false) {
        update_option('aikit_setting_chatbot_appearance_title_color', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_TITLE_COLOR);
    }

    if (get_option('aikit_setting_chatbot_appearance_ai_message_bubble_color') === false) {
        update_option('aikit_setting_chatbot_appearance_ai_message_bubble_color', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_AI_MESSAGE_BUBBLE_COLOR);
    }

    if (get_option('aikit_setting_chatbot_appearance_ai_message_text_color') === false) {
        update_option('aikit_setting_chatbot_appearance_ai_message_text_color', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_AI_MESSAGE_TEXT_COLOR);
    }

    if (get_option('aikit_setting_chatbot_appearance_user_message_bubble_color') === false) {
        update_option('aikit_setting_chatbot_appearance_user_message_bubble_color', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_USER_MESSAGE_BUBBLE_COLOR);
    }

    if (get_option('aikit_setting_chatbot_appearance_user_message_text_color') === false) {
        update_option('aikit_setting_chatbot_appearance_user_message_text_color', AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_USER_MESSAGE_TEXT_COLOR);
    }

    if (get_option('aikit_setting_stabilityai_default_engine') === false) {
        update_option('aikit_setting_stabilityai_default_engine', AIKIT_DEFAULT_SETTING_STABILITYAI_ENGINE);
    }

    if (get_option('aikit_setting_stabilityai_default_sampler') === false) {
        update_option('aikit_setting_stabilityai_default_sampler', AIKIT_DEFAULT_SETTING_STABILITYAI_SAMPLER);
    }

    if (get_option('aikit_setting_stabilityai_default_steps') === false) {
        update_option('aikit_setting_stabilityai_default_steps', AIKIT_DEFAULT_SETTING_STABILITYAI_STEPS);
    }

    if (get_option('aikit_setting_stabilityai_default_cfg_scale') === false) {
        update_option('aikit_setting_stabilityai_default_cfg_scale', AIKIT_DEFAULT_SETTING_STABILITYAI_CFG_SCALE);
    }

    if (get_option('aikit_setting_stabilityai_default_seed') === false) {
        update_option('aikit_setting_stabilityai_default_seed', AIKIT_DEFAULT_SETTING_STABILITYAI_SEED);
    }

    if (get_option('aikit_setting_default_image_generation_api') === false) {
        update_option('aikit_setting_default_image_generation_api', AIKIT_DEFAULT_SETTING_IMAGE_GENERATION_API);
    }

    if (get_option('aikit_setting_chatbot_use_embeddings') === false) {
        update_option('aikit_setting_chatbot_use_embeddings', AIKIT_DEFAULT_SETTING_CHATBOT_USE_EMBEDDINGS);
    }

    if (get_option('aikit_setting_chatbot_embeddings_answer_formulation_prompt') === false) {
        update_option('aikit_setting_chatbot_embeddings_answer_formulation_prompt', AIKIT_DEFAULT_SETTING_CHATBOT_EMBEDDINGS_ANSWER_FORMULATION_PROMPT);
    }

    if (get_option('aikit_setting_chatbot_log_messages') === false) {
        update_option('aikit_setting_chatbot_log_messages', AIKIT_DEFAULT_SETTING_CHATBOT_LOG_MESSAGES);
    }

    if (get_option('aikit_setting_audio_player_primary_color') === false) {
        update_option('aikit_setting_audio_player_primary_color', AIKIT_DEFAULT_SETTING_AUDIO_PLAYER_PRIMARY_COLOR);
    }

    if (get_option('aikit_setting_audio_player_secondary_color') === false) {
        update_option('aikit_setting_audio_player_secondary_color', AIKIT_DEFAULT_SETTING_AUDIO_PLAYER_SECONDARY_COLOR);
    }

    if (get_option('aikit_setting_audio_player_message') === false) {
        update_option('aikit_setting_audio_player_message', AIKIT_DEFAULT_SETTING_AUDIO_PLAYER_MESSAGE);
    }

    if (get_option('aikit_setting_openai_image_model') === false) {
        update_option('aikit_setting_openai_image_model', AIKIT_DEFAULT_SETTING_OPENAI_IMAGE_MODEL);
    }

    if (get_option('aikit_setting_openai_image_quality') === false) {
        update_option('aikit_setting_openai_image_quality', AIKIT_DEFAULT_SETTING_OPENAI_IMAGE_QUALITY);
    }

    if (get_option('aikit_setting_openai_image_style') === false) {
        update_option('aikit_setting_openai_image_style', AIKIT_DEFAULT_SETTING_OPENAI_IMAGE_STYLE);
    }

    if (get_option('aikit_setting_openai_tts_model') === false) {
        update_option('aikit_setting_openai_tts_model', AIKIT_DEFAULT_SETTING_OPENAI_TTS_MODEL);
    }

    if (get_option('aikit_setting_openai_tts_voice') === false) {
        update_option('aikit_setting_openai_tts_voice', AIKIT_DEFAULT_SETTING_OPENAI_TTS_VOICE);
    }

    if (get_option('aikit_setting_default_tts_api') === false) {
        update_option('aikit_setting_default_tts_api', AIKIT_DEFAULT_SETTING_DEFAULT_TTS_API);
    }

    if (get_option('aikit_setting_default_ai_text_provider') === false) {
        update_option('aikit_setting_default_ai_text_provider', AIKIT_DEFAULT_AI_TEXT_PROVIDER);
    }

    if (get_option('aikit_setting_anthropic_model') === false) {
        update_option('aikit_setting_anthropic_model', AIKIT_ANTHROPIC_DEFAULT_MODEL);
    }
}

function aikit_add_inline_js_object () {
    $aikit_build_plugin_js_config = aikit_build_plugin_js_config();

	wp_add_inline_script( 'aikit_index_js', 'var aikit =' . json_encode($aikit_build_plugin_js_config) );

	wp_enqueue_script( 'aikit_index_js');
}

function aikit_build_plugin_js_config() {
    $isOpenAIKeyValid = boolval(get_option( 'aikit_setting_openai_key_valid' ));
    $selectedLanguage = get_option( 'aikit_setting_openai_language' );

    $availableSizes = [];

    if (get_option('aikit_setting_images_size_small')) {
        $availableSizes['small'] = '256x256';
    }

    if (get_option('aikit_setting_images_size_medium')) {
        $availableSizes['medium'] = '512x512';
    }

    if (get_option('aikit_setting_images_size_large')) {
        $availableSizes['large'] = '1024x1024';
    }

    if (get_option('aikit_setting_images_size_xlarge_1792x1024')) {
        $availableSizes['xlarge landscape'] = '1792x1024';
    }

    if (get_option('aikit_setting_images_size_xlarge_1344x768')) {
        $availableSizes['xlarge landscape '] = '1344x768';
    }

    if (get_option('aikit_setting_images_size_xlarge_1024x1792')) {
        $availableSizes['xlarge portrait'] = '1024x1792';
    }

    $stabilityAIKey = get_option( 'aikit_setting_stabilityai_key' );
    $stabilityAIModel = get_option( 'aikit_setting_stabilityai_default_engine' );

    $nonce = wp_create_nonce('wp_rest' );
    $aiKitScriptVars = array(
        'nonce'  =>  $nonce,
        'siteUrl' => get_site_url(),
        'pluginUrl' => plugin_dir_url( __FILE__ ),
        'autocompletedTextBackgroundColor' => get_option('aikit_setting_autocompleted_text_background_color'),
        'isOpenAIKeyValid' => $isOpenAIKeyValid,
        'isStabilityAIKeySet' => !empty($stabilityAIKey),
        'selectedLanguage' => $selectedLanguage,
        'prompts' => AIKit_Prompt_Manager::get_instance()->get_prompts_for_frontend($selectedLanguage),
        'imageGenerationOptions' => [
            'counts' => aikit_openai_get_model_allowed_image_generation_counts(),
            'sizes' => array_intersect_key($availableSizes, aikit_openai_get_model_allowed_resolutions()),
            'stabilityAISizes' => array_intersect_key(
                $availableSizes,
                aikit_stability_ai_get_model_allowed_resolutions($stabilityAIModel),
            ),
            'stabilityAICounts' => explode(',', get_option('aikit_setting_images_counts')),
        ],
    );

    ###['openai-key-valid']

    return $aiKitScriptVars;
}

function aikit_get_plugin_version() {
	$plugin_data = array();
	if ( !function_exists( 'get_plugin_data' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	$plugin_data = get_plugin_data( __FILE__ );

    $plugin_version = $plugin_data['Version'] ?? false;

    return $plugin_version;
}

/**
 * Classic Editor
 */

function aikit_classic_buttons($buttons) {
	array_push($buttons, 'aikit_classic_button_text', 'aikit_classic_button_images');

	return $buttons;
}
add_filter('mce_buttons', 'aikit_classic_buttons');

function aikit_classic_mce_css($mce_css) {
	if (! empty($mce_css)) {
		$mce_css .= ',';
	}
	$mce_css .= plugins_url('includes/css/classic.css', __FILE__);

	return $mce_css;
}
add_filter('mce_css', 'aikit_classic_mce_css');

function aikit_classic_mce_plugin($plugin_array) {
    global $pagenow;

    if ($pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'edit-tags.php' || $pagenow == 'term.php') {
	    $plugin_array['aikit_classic'] = plugins_url('includes/js/classic.js', __FILE__);
    }

	return $plugin_array;
}
add_filter('mce_external_plugins', 'aikit_classic_mce_plugin');


add_action('admin_head', 'aikit_classic_mce_inline_script');

function aikit_classic_mce_inline_script() {
    global $pagenow;

    if ($pagenow !== 'post.php' &&
        $pagenow !== 'post-new.php' &&
        $pagenow !== 'edit-tags.php' &&
        $pagenow !== 'term.php' &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_auto_writer') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_scheduler') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_repurpose') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_rss') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_chatbot') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_fine_tune') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_embeddings') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_text_to_speech') &&
        !(isset($_GET['page']) && $_GET['page'] === 'aikit_comments')
    ) {
        return;
    }

	aikit_add_inline_js_object();
}

add_action('admin_enqueue_scripts', 'aikit_classic_mce_enqueue_scripts');

function aikit_classic_mce_enqueue_scripts() {
    global $pagenow;

    if ($pagenow !== 'post.php' && $pagenow !== 'post-new.php' && $pagenow !== 'edit-tags.php' && $pagenow !== 'term.php') {
        return;
    }

    wp_enqueue_style('aikit_classic_mce_css', plugins_url('includes/css/classic.css', __FILE__));
}

/**
 * Elementor
 */

function register_aikit_elementor_widget( $widgets_manager ) {

    if (!get_option('aikit_setting_elementor_supported')) {
        return;
    }

    require_once __DIR__ . '/includes/integration/elementor-editor.php';

    $widgets_manager->register( new \AIKit_Elementor_Editor() );

}

add_action( 'elementor/widgets/register', 'register_aikit_elementor_widget' );


function register_aikit_new_controls( $controls_manager ) {

    if (!get_option('aikit_setting_elementor_supported')) {
        return;
    }

    require_once __DIR__ . '/includes/integration/hidden-control.php';

    $controls_manager->register( new AIKit_Elementor_Editor_Control(
        aikit_build_plugin_js_config()
    ) );

}

add_action( 'elementor/controls/register', 'register_aikit_new_controls' );

add_filter('cron_schedules', 'aikit_register_custom_cron_schedules' );

function aikit_register_custom_cron_schedules( $schedules ) {
    $schedules['every_5_minutes'] = array(
        'interval' => 60 * 5,
        'display'  => __( 'Every 5 Minutes' ),
    );

    $schedules['every_10_minutes'] = array(
        'interval' => 60 * 10,
        'display'  => __( 'Every 10 Minutes' ),
    );

    return $schedules;
}

function aikit_date($date)
{
    if (!is_numeric($date)) {
        $date = strtotime($date);
    }

    $format = get_option('time_format') . ' ' . get_option('date_format');

    return wp_date($format, $date);
}

function aikit_reconnect_db_if_needed()
{
    global $wpdb;

    // for servers with tight mysql connection time
    if (!$wpdb->check_connection()) {
        $wpdb->db_connect();
    }
}

###['admin-notice']

###['disable-password-reset']
