<?php

class AIKit_Admin {
/**
     * The single instance of the class.
     *
     * @var AIKit_Admin
     */
    protected static $_instance = null;

    /**
     * @var AIKit_Prompt_Manager
     */
    private $prompt_manager;
    private $export_import_manager;

    private $languages = [];

    private $auto_writer;

    private $repurposer;

    private $comments;

    private $rss;

    private $chatbot;

    private $chatbot_settings;

    private $fine_tuner;

    private $embeddings;

    private $text_to_speech;

    /**
     * Main AIKit_Admin Instance.
     *
     * Ensures only one instance of AIKit_Admin is loaded or can be loaded.
     *
     * @static
     * @return AIKit_Admin - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();

            self::$_instance->languages = [
                'en' => [
                    'translatedName' => __('English', 'aikit') . ' (English)',
                    'name' => 'English'
                ],
                'de' => [
                    'translatedName' => __('German', 'aikit') . ' (Deutsch)',
                    'name' => 'Deutsch'
                ],
                'fr' => [
                    'translatedName' => __('French', 'aikit') . ' (Français)',
                    'name' => 'Français',
                ],
                'es' => [
                    'translatedName' => __('Spanish', 'aikit') . ' (Español)',
                    'name' => 'Español',
                ],
                'it' => [
                    'translatedName' => __('Italian', 'aikit') . ' (Italiano)',
                    'name' => 'Italiano',
                ],
                'pt' => [
                    'translatedName' => __('Portuguese', 'aikit') . ' (Português)',
                    'name' => 'Português',
                ],
                'nl' => [
                    'translatedName' => __('Dutch', 'aikit') . ' (Nederlands)',
                    'name' => 'Dutch',
                ],
                'pl' => [
                    'translatedName' => __('Polish', 'aikit') . ' (Polski)',
                    'name' => 'Polski',
                ],
                'ru' => [
                    'translatedName' => __('Russian', 'aikit') . ' (Русский)',
                    'name' => 'Русский',
                ],
                'ja' => [
                    'translatedName' => __('Japanese', 'aikit') . ' (日本語)',
                    'name' => '日本語',
                ],
                'zh' => [
                    'translatedName' => __('Chinese', 'aikit') . ' (中文)',
                    'name' => '中文',
                ],
                'br' => [
                    'translatedName' => __('Brazilian Portuguese', 'aikit') . ' (Português Brasileiro)',
                    'name' => 'Português Brasileiro',
                ],
                'tr' => [
                    'translatedName' => __('Turkish', 'aikit') . ' (Türkçe)',
                    'name' => 'Türkçe',
                ],
                'ar' => [
                    'translatedName' => __('Arabic', 'aikit') . ' (العربية)',
                    'name' => 'العربية',
                ],
                'ko' => [
                    'translatedName' => __('Korean', 'aikit') . ' (한국어)',
                    'name' => '한국어',
                ],
                'hi' => [
                    'translatedName' => __('Hindi', 'aikit') . ' (हिन्दी)',
                    'name' => 'हिन्दी',
                ],
                'id' => [
                    'translatedName' => __('Indonesian', 'aikit') . ' (Bahasa Indonesia)',
                    'name' => 'Bahasa Indonesia',
                ],
                'sv' => [
                    'translatedName' => __('Swedish', 'aikit') . ' (Svenska)',
                    'name' => 'Svenska',
                ],
                'da' => [
                    'translatedName' => __('Danish', 'aikit') . ' (Dansk)',
                    'name' => 'Dansk',
                ],
                'fi' => [
                    'translatedName' => __('Finnish', 'aikit') . ' (Suomi)',
                    'name' => 'Suomi',
                ],
                'no' => [
                    'translatedName' => __('Norwegian', 'aikit') . ' (Norsk)',
                    'name' => 'Norsk',
                ],
                'ro' => [
                    'translatedName' => __('Romanian', 'aikit') . ' (Română)',
                    'name' => 'Română',
                ],
                'ka' => [
                    'translatedName' => __('Georgian', 'aikit') . ' (ქართული)',
                    'name' => 'ქართული',
                ],
                'vi' => [
                    'translatedName' => __('Vietnamese', 'aikit') . ' (Tiếng Việt)',
                    'name' => 'Tiếng Việt',
                ],
                'hu' => [
                    'translatedName' => __('Hungarian', 'aikit') . ' (Magyar)',
                    'name' => 'Magyar',
                ],
                'bg' => [
                    'translatedName' => __('Bulgarian', 'aikit') . ' (Български)',
                    'name' => 'Български',
                ],
                'el' => [
                    'translatedName' => __('Greek', 'aikit') . ' (Ελληνικά)',
                    'name' => 'Ελληνικά',
                ],
                'fa' => [
                    'translatedName' => __('Persian', 'aikit') . ' (فارسی)',
                    'name' => 'فارسی',
                ],
                'sk' => [
                    'translatedName' => __('Slovak', 'aikit') . ' (Slovenčina)',
                    'name' => 'Slovenčina',
                ],
                'cs' => [
                    'translatedName' => __('Czech', 'aikit') . ' (Čeština)',
                    'name' => 'Čeština',
                ],
                'lt' => [
                    'translatedName' => __('Lithuanian', 'aikit') . ' (Lietuvių)',
                    'name' => 'Lietuvių',
                ],
                'ca' => [
                    'translatedName' => __('Catalan', 'aikit') . ' (Català)',
                    'name' => 'Català',
                ],
                'hr' => [
                    'translatedName' => __('Croatian', 'aikit') . ' (Hrvatski)',
                    'name' => 'Hrvatski',
                ],
                'uk' => [
                    'translatedName' => __('Ukrainian', 'aikit') . ' (Українська)',
                    'name' => 'Українська',
                ],
                'he' => [
                    'translatedName' => __('Hebrew', 'aikit') . ' (עברית)',
                    'name' => 'עברית',
                ],
                'th' => [
                    'translatedName' => __('Thai', 'aikit') . ' (ไทย)',
                    'name' => 'ไทย',
                ],
            ];
        }
        return self::$_instance;
    }

    /**
     * AIKit_Admin Constructor.
     */
    public function __construct() {
        $this->prompt_manager = AIKit_Prompt_Manager::get_instance();
        $this->export_import_manager = AIKit_Import_Export_Manager::get_instance($this->prompt_manager);
        $this->auto_writer = AIKIT_Auto_Writer::get_instance();
        $this->repurposer = AIKit_Repurposer::get_instance();
        $this->rss = AIKit_RSS::get_instance();
        $this->chatbot = AIKit_Chatbot::get_instance();
        $this->chatbot_settings  = AIKit_Chatbot_Settings::get_instance();
        $this->fine_tuner = AIKit_Fine_Tuner::get_instance();
        $this->embeddings = AIKIT_Embeddings::get_instance();
        $this->text_to_speech = AIKIT_Text_To_Speech::get_instance();
        $this->comments = AIKit_Comments::get_instance();
        $this->init();
    }

    /**
     * Initialize the AIKit_Admin class.
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->auto_writer, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->repurposer, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->rss, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->chatbot_settings, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->fine_tuner, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->embeddings, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->text_to_speech, 'enqueue_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this->comments, 'enqueue_scripts' ) );

        $this->chatbot->init();
    }

    public function enqueue_scripts($hook)
    {
        if ( 'aikit_page_aikit' !== $hook && 'toplevel_page_aikit' !== $hook) {
            return;
        }

        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_style( 'aikit_bootstrap_css', plugins_url( 'css/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( 'css/bootstrap-icons.css', __FILE__ ), array(), $version );

        wp_enqueue_script( 'aikit_bootstrap_js', plugins_url('js/bootstrap.bundle.min.js', __FILE__ ), array(), $version );
    }

    public function get_languages ()
    {
        return $this->languages;
    }

    /**
     * Add options page.
     */
    public function admin_menu() {

        ###['aikit-menu']

        add_menu_page(
            esc_html__('AIKit Writer', 'aikit'),
            esc_html__('AIKit', 'aikit'),
            'edit_posts',
            'aikit_auto_writer',
            array( $this->auto_writer, 'render_auto_writer' ),
            AIKIT_LOGO_BASE64,
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Auto Writer', 'aikit'),
            esc_html__('Auto Writer', 'aikit'),
            'edit_posts',
            'aikit_auto_writer',
            array( $this->auto_writer, 'render_auto_writer' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Scheduler', 'aikit'),
            esc_html__('Scheduler', 'aikit'),
            'edit_posts',
            'aikit_scheduler',
            array( $this->auto_writer, 'render_scheduled_generators' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Repurpose', 'aikit'),
            esc_html__('Repurpose', 'aikit'),
            'edit_posts',
            'aikit_repurpose',
            array( $this->repurposer, 'render' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('AI Comments', 'aikit'),
            esc_html__('AI Comments', 'aikit'),
            'edit_posts',
            'aikit_comments',
            array( $this->comments, 'render' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('RSS', 'aikit'),
            esc_html__('RSS', 'aikit'),
            'edit_posts',
            'aikit_rss',
            array( $this->rss, 'render' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Embeddings', 'aikit'),
            esc_html__('Embeddings', 'aikit'),
            'manage_options',
            'aikit_embeddings',
            array( $this->embeddings, 'render' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Chatbot', 'aikit'),
            esc_html__('Chatbot', 'aikit'),
            'manage_options',
            'aikit_chatbot',
            array( $this->chatbot_settings, 'render' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Fine-tune Models', 'aikit'),
            esc_html__('Fine-tune Models', 'aikit'),
            'manage_options',
            'aikit_fine_tune',
            array( $this->fine_tuner, 'render' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Text to Speech', 'aikit'),
            esc_html__('Text to Speech', 'aikit'),
            'manage_options',
            'aikit_text_to_speech',
            array( $this->text_to_speech, 'render' )
        );

        add_submenu_page(
                'aikit_auto_writer',
            esc_html__('Add/Edit AI Menu Prompts', 'aikit'),
            esc_html__('AI Menu Prompts', 'aikit'),
            'edit_posts',
            'aikit_prompts',
            array( $this, 'prompts_page' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('AIKit Settings', 'aikit'),
            esc_html__('Settings', 'aikit'),
            'manage_options',
            'aikit',
            array( $this, 'options_page' )
        );

        add_submenu_page(
            'aikit_auto_writer',
            esc_html__('Export/Import Settings', 'aikit'),
            esc_html__('Export/Import Settings', 'aikit'),
            'manage_options',
            'aikit_export_import_settings',
            array( $this->export_import_manager, 'export_import_settings_page' )
        );
    }

    /**
     * Options page callback.
     */
    public function options_page() {

        $audio_player_message = get_option('aikit_setting_audio_player_message');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post" id="aikit-settings-form">
                <?php
                // output security fields for the registered setting "aikit_options"
                settings_fields( 'aikit_options' );

                ?>
                <ul class="nav nav-tabs" id="aikit-settings-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ai-text-generation-tab" data-bs-toggle="tab" data-bs-target="#ai-text-generation-tab-pane" type="button" role="tab" aria-controls="ai-text-generation-tab-pane" aria-selected="true"><i class="bi bi-robot"></i> <?php echo esc_html__( 'AI Text Generation', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="openai-tab" data-bs-toggle="tab" data-bs-target="#openai-tab-pane" type="button" role="tab" aria-controls="openai-tab-pane" aria-selected="true"><i class="bi bi-robot"></i> <?php echo esc_html__( 'OpenAI', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="anthropic-tab" data-bs-toggle="tab" data-bs-target="#anthropic-tab-pane" type="button" role="tab" aria-controls="anthropic-tab-pane" aria-selected="true"><i class="bi bi-robot"></i> <?php echo esc_html__( 'Anthropic', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="stabilityai-tab" data-bs-toggle="tab" data-bs-target="#stabilityai-tab-pane" type="button" role="tab" aria-controls="stabilityai-tab-pane" aria-selected="false"><i class="bi bi-card-image"></i> <?php echo esc_html__( 'StabilityAI', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="elevenlabs-tab" data-bs-toggle="tab" data-bs-target="#elevenlabs-tab-pane" type="button" role="tab" aria-controls="elevenlabs-tab-pane" aria-selected="false"><i class="bi bi-mic-fill"></i> <?php echo esc_html__( 'Eleven Labs', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="qdrant-tab" data-bs-toggle="tab" data-bs-target="#qdrant-tab-pane" type="button" role="tab" aria-controls="qdrant-tab-pane" aria-selected="false"><i class="bi bi-chat-dots-fill"></i> <?php echo esc_html__( 'Qdrant', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rapidapi-tab" data-bs-toggle="tab" data-bs-target="#rapidapi-tab-pane" type="button" role="tab" aria-controls="rapidapi-tab-pane" aria-selected="false"><i class="bi bi-youtube"></i> <?php echo esc_html__( 'Rapid API', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="image-generation-tab" data-bs-toggle="tab" data-bs-target="#image-generation-tab-pane" type="button" role="tab" aria-controls="image-generation-tab-pane" aria-selected="false"><i class="bi bi-brush-fill"></i> <?php echo esc_html__( 'Image Generation', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tts-tab" data-bs-toggle="tab" data-bs-target="#tts-tab-pane" type="button" role="tab" aria-controls="tts-tab-pane" aria-selected="false"><i class="bi bi-soundwave"></i> <?php echo esc_html__( 'Text-to-Speech', 'aikit' ); ?></button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-tab-pane" type="button" role="tab" aria-controls="general-tab-pane" aria-selected="false"><i class="bi bi-gear-fill"></i> <?php echo esc_html__( 'General', 'aikit' ); ?></button>
                    </li>
                </ul>
                <div class="tab-content aikit-tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="ai-text-generation-tab-pane" role="tabpanel" aria-labelledby="ai-text-generation-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_ai_text_generation_settings' ); ?>
                    </div>
                    <div class="tab-pane fade" id="openai-tab-pane" role="tabpanel" aria-labelledby="openai-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_openai_settings' ); ?>
                        <?php do_settings_sections( 'aikit_openai_settings_text' ); ?>
                        <?php do_settings_sections( 'aikit_openai_settings_image' ); ?>
                        <?php do_settings_sections( 'aikit_openai_settings_tts' ); ?>
                    </div>
                    <div class="tab-pane fade" id="anthropic-tab-pane" role="tabpanel" aria-labelledby="anthropic-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_anthropic_settings' ); ?>
                    </div>
                    <div class="tab-pane fade" id="stabilityai-tab-pane" role="tabpanel" aria-labelledby="stabilityai-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_stabilityai_settings' ); ?>
                    </div>
                    <div class="tab-pane fade" id="rapidapi-tab-pane" role="tabpanel" aria-labelledby="rapidapi-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_rapidapi_settings' ); ?>
                    </div>
                    <div class="tab-pane fade" id="qdrant-tab-pane" role="tabpanel" aria-labelledby="qdrant-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_qdrant_settings' ); ?>
                    </div>
                    <div class="tab-pane fade" id="elevenlabs-tab-pane" role="tabpanel" aria-labelledby="elevenlabs-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_elevenlabs_settings' ); ?>
                    </div>
                    <div class="tab-pane fade" id="image-generation-tab-pane" role="tabpanel" aria-labelledby="image-generation-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_image_generation_settings' ); ?>
                    </div>
                    <div class="tab-pane fade" id="tts-tab-pane" role="tabpanel" aria-labelledby="tts-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_tts_generation_settings' ); ?>
                        <h5 class="audio-player-preview"><?php echo esc_html__( 'Audio Player Preview:', 'aikit' ); ?></h5>
                        <span class="aikit-audio-player-message" style="text-align: center; align-content: center; width: 100%; margin-left: 20px; font-size: 14px;"><?php echo esc_html($audio_player_message) ?></span>
                        <?php echo $this->text_to_speech->generate_audio_player_preview_iframe(); ?>
                    </div>
                    <div class="tab-pane fade" id="general-tab-pane" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                        <?php do_settings_sections( 'aikit_general_settings' ); ?>
                    </div>
                </div>

                <?php
                // output setting sections and their fields

                // output save settings button
                submit_button( esc_html__( 'Save Settings', 'aikit' ) );
                ?>
            </form>
        </div>
        <?php
    }

    private function reset_prompts() {
        $prompts = AIKIT_INITIAL_PROMPTS;
        $promptsByLang = $this->prompt_manager->build_prompts_by_language($prompts);

        // save all prompts for all languages in a single option
        update_option('aikit_prompts', $prompts);

        foreach ($promptsByLang as $lang => $obj) {
            // save prompts for each language as options
            update_option('aikit_prompts_' . $lang, $obj);
        }
    }

    private function transform_post_request_and_save_prompts() {
        $result = array();
        $postData = $_POST;
        $postPrompts = json_decode(stripslashes($postData['prompts']), true);

        foreach ($postPrompts as $operationId => $obj) {

            foreach ($obj['languages'] as $lang => $langObj) {
                $result[$operationId]['languages'][$lang] = array(
                    'menuTitle' => stripslashes($langObj['menu_title']),
                    'prompt' => stripslashes($langObj['prompt']),
                );
            }

            if ($obj['word_length_type'] === 'fixed') {
                $result[$operationId]['wordLength'] = array(
                    'type' => AIKIT_WORD_LENGTH_TYPE_FIXED,
                    'value' => max(intval($obj['word_length_fixed']), 0),
                );
            } else {
                $result[$operationId]['wordLength'] = array(
                    'type' => AIKIT_WORD_LENGTH_TYPE_WORD_COUNT_MULTIPLIER,
                    'value' => max(floatval($obj['word_length_relative']), 1),
                );
            }

            $result[$operationId]['requiresTextSelection'] = $obj['requires_text_selection'] === 'on';

            $result[$operationId]['icon'] = $this->get_icon_for_prompt($operationId);
            $result[$operationId]['generatedTextPlacement'] = $this->get_generated_text_placement_for_prompt($operationId);
            $result[$operationId]['temperature'] = $this->get_temperature_for_prompt($obj['temperature']);

        }

        // get a vertical slice array for all prompts for a given language
        $promptsByLang = $this->prompt_manager->build_prompts_by_language($result);

        // save all prompts for all languages in a single option
        update_option('aikit_prompts', $result);

        foreach ($promptsByLang as $lang => $obj) {
            // save prompts for each language as options
            update_option('aikit_prompts_' . $lang, $obj);
        }
    }

    private function get_temperature_for_prompt($temperature)
    {
        if (floatval($temperature) < 0 || floatval($temperature) > 1) {
            return 0.7;
        }

        return $temperature;
    }

    private function get_icon_for_prompt ($operationId)
    {
        if (isset(AIKIT_INITIAL_PROMPTS[$operationId])) {
            return AIKIT_INITIAL_PROMPTS[$operationId]['icon'];
        }

        return 'custom';
    }

    private function get_generated_text_placement_for_prompt ($operationId)
    {
        if (isset(AIKIT_INITIAL_PROMPTS[$operationId])) {
            return AIKIT_INITIAL_PROMPTS[$operationId]['generatedTextPlacement'];
        }

        return 'below';
    }

    /**
     * Prompts page callback.
     */
    public function prompts_page() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['reset']) && $_POST['reset'] === '1') {
                $this->reset_prompts();
            } else {
                $this->transform_post_request_and_save_prompts();
            }
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="" method="post" id="aikit-prompts-form">
                <?php
                settings_fields( 'aikit_prompts' );

                $defaultLanguage = aikit_get_language_used();

                $allPrompts = $this->prompt_manager->get_all_prompts();

                ?>

                <div class="row mt-2">
                    <p>
                        <?php echo esc_html__( 'Here you can edit or add new prompts that would appear in the "AI" dropdown menu. You can also reorder the prompts by dragging and dropping them in the order you wish. ', 'aikit' ); ?>
                        <?php echo esc_html__( 'There are lots of online resources that could help and give you tips & trick on how to best edit prompts. Simply search YouTube/Google for "Prompt engineering" to gain more knowledge on the topic.', 'aikit' ); ?>
                    </p>
                </div>
                <div class="aikit-prompts-top-bar">
                    <button type="button" class="btn btn-outline-primary float-start" id="aikit-add-prompt">
                        <?php echo esc_html__( 'Add Prompt', 'aikit' ); ?>
                    </button>
                    <button class="btn btn-outline-danger" id="aikit-reset-prompts" type="submit" data-confirm-message="<?php echo esc_html__( 'Resetting prompts will remove all changes you made in this screen, and will bring back the builtin prompts that AIKit provides out of the box. Are you sure you want to proceed?', 'aikit' ); ?>">
                        <?php echo esc_html__( 'Reset Prompts', 'aikit' ); ?>
                    </button>
                </div>

                <div id="aikit-prompts-accordion">
                    <?php

                    foreach ($allPrompts as $promptKey => $promptObject) {
                        $languages = $promptObject['languages'];
                        // push the default language to the top of the list
                        $languages = array($defaultLanguage => $languages[$defaultLanguage]) + $languages;
                    ?>
                        <div class="group">
                            <h3>
                                <span class="aikit-prompt-icon">
                                    <?php
                                        $icon = $promptObject['icon'];
                                        $iconPath = plugins_url('icons/' . $icon . '.svg', __FILE__);
                                    ?>
                                    <img src="<?php echo esc_url($iconPath); ?>" alt="<?php echo esc_attr($icon); ?>">
                                </span>
                                <span class="aikit-prompt-accordion-header"><?php echo esc_html__($languages[$defaultLanguage]['menuTitle'], 'aikit'); ?></span>
                                <img class="aikit-remove-prompt" alt="" data-confirm-message="<?php echo esc_html__( 'Are you sure you want to remove this prompt?', 'aikit' ); ?>" src="data:image/svg+xml;base64,PCEtLSBVcGxvYWRlZCB0bzogU1ZHIFJlcG8sIHd3dy5zdmdyZXBvLmNvbSwgVHJhbnNmb3JtZWQgYnk6IFNWRyBSZXBvIE1peGVyIFRvb2xzIC0tPgo8c3ZnIHdpZHRoPSI4MDBweCIgaGVpZ2h0PSI4MDBweCIgdmlld0JveD0iMCAwIDE2IDE2IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiPgoNPGcgZmlsbD0iIzAwMDAwMCI+Cg08cGF0aCBkPSJNMTEuMjggNC43MmEuNzUuNzUgMCAwMTAgMS4wNkw5LjA2IDhsMi4yMiAyLjIyYS43NS43NSAwIDExLTEuMDYgMS4wNkw4IDkuMDZsLTIuMjIgMi4yMmEuNzUuNzUgMCAwMS0xLjA2LTEuMDZMNi45NCA4IDQuNzIgNS43OGEuNzUuNzUgMCAwMTEuMDYtMS4wNkw4IDYuOTRsMi4yMi0yLjIyYS43NS43NSAwIDAxMS4wNiAweiIvPgoNPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBkPSJNLjI1IDhhNy43NSA3Ljc1IDAgMTExNS41IDBBNy43NSA3Ljc1IDAgMDEuMjUgOHpNOCAxLjc1YTYuMjUgNi4yNSAwIDEwMCAxMi41IDYuMjUgNi4yNSAwIDAwMC0xMi41eiIgY2xpcC1ydWxlPSJldmVub2RkIi8+Cg08L2c+Cg08L3N2Zz4=" />

                            </h3>
                            <div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-check">
                                            <input name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][requires_text_selection]" class="form-check-input mt-0 requires-text-selection-input" type="checkbox" id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][requires_text_selection]" <?php checked(1, $promptObject['requiresTextSelection']); ?>>
                                            <label class="form-check-label" for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][requires_text_selection]">
                                                <?php echo esc_html__( 'Requires text selection', 'aikit' ); ?>
                                            </label>
                                            <div class="form-text">
                                                <?php echo esc_html__( 'Choose this option if you want to enforce text selection in the text editor. Most of the time you will want to leave this option selected. Deselect it only if you are adding a prompt that doesn\'t require input from author, like if you want OpenAI to generate text about random topic for example.', 'aikit' ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="row mb-3">
                                            <label class="col-sm-4 col-form-label" for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][temperature]">
                                                <?php echo esc_html__( 'Temperature', 'aikit' ); ?>
                                            </label>
                                            <div class="col-sm-3">
                                                <input type="number" min="0" max="1" step="0.1" class="form-control form-control-sm mt-0" id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][temperature]" value="<?php echo isset($promptObject['temperature']) ? esc_html__($promptObject['temperature'], 'aikit') : 0.7 ?>" name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][temperature]">
                                            </div>
                                            <div class="form-text">
                                                <?php echo esc_html__( 'Controls randomness: Lowering results in less random completions. As the temperature approaches zero, the model will become deterministic and repetitive.', 'aikit' ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <label class="mt-4">
                                    <?php echo esc_html__( 'Number of words to generate', 'aikit' ); ?>
                                </label>

                                <div class="row">
                                    <div class="card col-sm-4 m-2 h-25 text-length-card fixed-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input mt-0" type="radio" name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_type]" value="fixed" <?php checked(AIKIT_WORD_LENGTH_TYPE_FIXED, $promptObject['wordLength']['type']) ?>  id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_type_fixed]" >
                                                <label class="form-check-label" for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_type_fixed]">
                                                    <?php echo esc_html__( 'Fixed number of words', 'aikit' ); ?>
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label col-form-label-sm" for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_fixed]">
                                                    <?php echo esc_html__( 'Number of words', 'aikit' ); ?>
                                                </label>
                                                <div class="col-sm-3">
                                                    <input type="number" class="form-control form-control-sm mt-0" id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_fixed]" placeholder="400" value="<?php echo ($promptObject['wordLength']['type'] == AIKIT_WORD_LENGTH_TYPE_FIXED) ? esc_html__($promptObject['wordLength']['value'], 'aikit') : '' ?>" name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_fixed]">
                                                </div>
                                                <div class="form-text">
                                                    <?php echo esc_html__( 'Choose this option if you want to generate a fixed number of words, regardless of how long the selected text is. This is helpful for certain types of prompts, like generating a paragraph on a certain topic for example.', 'aikit' ); ?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="card col-sm-4 m-2 text-length-card relative-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input mt-0" type="radio" name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_type]" value="relative" <?php checked(AIKIT_WORD_LENGTH_TYPE_WORD_COUNT_MULTIPLIER, $promptObject['wordLength']['type']) ?>  id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_type_relative]">
                                                <label class="form-check-label" for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_type_relative]">
                                                    <?php echo esc_html__( 'Relative to length of text selected', 'aikit' ); ?>
                                                </label>
                                            </div>
                                            <div class="row mb-3">
                                                <label class="col-sm-4 col-form-label col-form-label-sm" for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_relative]">
                                                    <?php echo esc_html__( 'Multiplier', 'aikit' ); ?>
                                                </label>
                                                <div class="col-sm-4">
                                                    <input type="range" step="0.1" min="1" max="6" class="form-range aikit-slider mt-0" id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_relative]" value="<?php echo ($promptObject['wordLength']['type'] == AIKIT_WORD_LENGTH_TYPE_WORD_COUNT_MULTIPLIER) ? esc_html__($promptObject['wordLength']['value'], 'aikit') : '1' ?>" name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][word_length_relative]">
                                                </div>
                                                <div class="col-sm-2">
                                                    <span class="slider-value"></span>
                                                </div>
                                                <div class="form-text">
                                                    <?php echo esc_html__( 'Choose this option if you want to calculate the length of the generated words relative to the length of words selected. 1x = same length as select text, 2x means two times, etc. Summarization is a good candidate to use this option for.', 'aikit' ); ?>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>

                                <h6 class="mt-4">
                                    <?php echo esc_html__( 'Prompts', 'aikit' ); ?>
                                </h6>
                                <div class="tabs">
                                    <ul>
                                        <?php
                                        foreach ($languages as $language => $languageData) {
                                            ?>
                                            <li><a href="#<?php echo esc_html__($promptKey, 'aikit') ?>_tabs_<?php echo esc_html__($language, 'aikit') ?>"><?php echo esc_html__( $this->languages[$language]['name'], 'aikit') ?></a></li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                    <?php
                                    foreach ($languages as $language => $languageData) {
                                        ?>
                                        <div id="<?php echo esc_html__($promptKey, 'aikit') ?>_tabs_<?php echo esc_html__($language, 'aikit') ?>">
                                            <div class="form-floating mb-3">
                                                <input type="text" class="form-control menu-title-input" id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][languages][<?php echo esc_html__($language, 'aikit') ?>][menu_title]" name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][languages][<?php echo esc_html__($language, 'aikit') ?>][menu_title]" value="<?php echo esc_html__($languageData['menuTitle'], 'aikit'); ?>" placeholder="<?php echo esc_html__( 'Menu title', 'aikit' ); ?>" />
                                                <label for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][languages][<?php echo esc_html__($language, 'aikit') ?>][menu_title]">
                                                    <?php echo esc_html__( 'Menu title', 'aikit' ); ?>
                                                </label>
                                                <div class="form-text">
                                                    <?php echo esc_html__( 'This is title that will appear in the AI menu for this prompt.', 'aikit' ); ?>
                                                </div>
                                            </div>

                                            <div class="form-floating">
                                                <textarea class="form-control prompt-textarea" placeholder="Leave a comment here" name="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][languages][<?php echo esc_html__($language, 'aikit') ?>][prompt]" id="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][languages][<?php echo esc_html__($language, 'aikit') ?>][prompt]" cols="30" rows="10"><?php echo esc_html__($languageData['prompt'], 'aikit'); ?></textarea>
                                                <label for="prompts[<?php echo esc_html__($promptKey, 'aikit') ?>][languages][<?php echo esc_html__($language, 'aikit') ?>][prompt]">
                                                    <?php echo esc_html__( 'Prompt', 'aikit' ); ?>
                                                </label>
                                                <div class="form-text">
                                                    <?php echo esc_html__( 'If this prompt requires text selection, the phrase', 'aikit' ); ?>
                                                    <code>[[text]]</code>
                                                    <?php echo esc_html__( 'will be replaced by the selected text before doing the request. Make sure to include it in your prompt.', 'aikit' ); ?>
                                                    <?php echo esc_html__( 'You can also add', 'aikit' ); ?>
                                                    <code>[[post_title]]</code>, <code>[[text_before]]</code>, <code>[[text_after]]</code>
                                                    <?php echo esc_html__( 'to include the title of the post, the text before the selected text or the text after the selected text in the prompt to give AI more context of what you write about.', 'aikit' ); ?>

                                                </div>
                                            </div>

                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                    <?php
                    }
                    ?>

                </div>
                <input type="button" id="aiKitPromptsSubmit" class="button button-primary" value="<?php echo esc_html__( 'Save Settings', 'aikit' ) ?>">
            </form>

            <div class="group-template">
                <h3>
                    <span class="aikit-prompt-icon">
                        <?php
                            $iconPath = plugins_url('icons/custom.svg', __FILE__);
                        ?>
                        <img src="<?php echo esc_url($iconPath); ?>" >
                    </span>
                    <span class="aikit-prompt-accordion-header"></span>
                    <img class="aikit-remove-prompt" alt="" data-confirm-message="<?php echo esc_html__( 'Are you sure you want to remove this prompt?', 'aikit' ); ?>" src="data:image/svg+xml;base64,PCEtLSBVcGxvYWRlZCB0bzogU1ZHIFJlcG8sIHd3dy5zdmdyZXBvLmNvbSwgVHJhbnNmb3JtZWQgYnk6IFNWRyBSZXBvIE1peGVyIFRvb2xzIC0tPgo8c3ZnIHdpZHRoPSI4MDBweCIgaGVpZ2h0PSI4MDBweCIgdmlld0JveD0iMCAwIDE2IDE2IiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiPgoNPGcgZmlsbD0iIzAwMDAwMCI+Cg08cGF0aCBkPSJNMTEuMjggNC43MmEuNzUuNzUgMCAwMTAgMS4wNkw5LjA2IDhsMi4yMiAyLjIyYS43NS43NSAwIDExLTEuMDYgMS4wNkw4IDkuMDZsLTIuMjIgMi4yMmEuNzUuNzUgMCAwMS0xLjA2LTEuMDZMNi45NCA4IDQuNzIgNS43OGEuNzUuNzUgMCAwMTEuMDYtMS4wNkw4IDYuOTRsMi4yMi0yLjIyYS43NS43NSAwIDAxMS4wNiAweiIvPgoNPHBhdGggZmlsbC1ydWxlPSJldmVub2RkIiBkPSJNLjI1IDhhNy43NSA3Ljc1IDAgMTExNS41IDBBNy43NSA3Ljc1IDAgMDEuMjUgOHpNOCAxLjc1YTYuMjUgNi4yNSAwIDEwMCAxMi41IDYuMjUgNi4yNSAwIDAwMC0xMi41eiIgY2xpcC1ydWxlPSJldmVub2RkIi8+Cg08L2c+Cg08L3N2Zz4=" />
                </h3>

                <div>

                    <div class="row">
                        <div class="col">
                            <div class="form-check">
                                <input name="prompts[__PROMPT_KEY__][requires_text_selection]" class="form-check-input mt-0 requires-text-selection-input" type="checkbox" id="prompts[__PROMPT_KEY__][requires_text_selection]" checked>
                                <label class="form-check-label" for="prompts[__PROMPT_KEY__][requires_text_selection]">
                                    <?php echo esc_html__( 'Requires text selection', 'aikit' ); ?>
                                </label>
                                <div class="form-text">
                                    <?php echo esc_html__( 'Choose this option if you want to enforce text selection in the text editor. Most of the time you will want to leave this option selected. Deselect it only if you are adding a prompt that doesn\'t require input from author, like if you want OpenAI to generate text about random topic for example.', 'aikit' ); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col">
                            <div class="row mb-3">
                                <label class="col-sm-4 col-form-label" for="prompts[__PROMPT_KEY__][temperature]">
                                    <?php echo esc_html__( 'Temperature', 'aikit' ); ?>
                                </label>
                                <div class="col-sm-3">
                                    <input type="number" min="0" max="1" step="0.1" class="form-control form-control-sm mt-0" id="prompts[__PROMPT_KEY__][temperature]" value="0.7" name="prompts[__PROMPT_KEY__][temperature]">
                                </div>
                                <div class="form-text">
                                    <?php echo esc_html__( 'Controls randomness: Lowering results in less random completions. As the temperature approaches zero, the model will become deterministic and repetitive.', 'aikit' ); ?>
                                </div>
                            </div>
                        </div>

                    </div>



                    <label class="mt-4">
                        <?php echo esc_html__( 'Number of words to generate', 'aikit' ); ?>
                    </label>

                    <div class="row">
                        <div class="card col-sm-4 m-2 h-25 text-length-card fixed-card">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input mt-0" type="radio" name="prompts[__PROMPT_KEY__][word_length_type]" value="fixed" checked  id="prompts[__PROMPT_KEY__][word_length_type_fixed]" >
                                    <label class="form-check-label" for="prompts[__PROMPT_KEY__][word_length_type_fixed]">
                                        <?php echo esc_html__( 'Fixed number of words', 'aikit' ); ?>
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="prompts[__PROMPT_KEY__][word_length_fixed]">
                                        <?php echo esc_html__( 'Number of words', 'aikit' ); ?>
                                    </label>
                                    <div class="col-sm-3">
                                        <input type="number" class="form-control form-control-sm mt-0" id="prompts[__PROMPT_KEY__][word_length_fixed]" placeholder="400" value="400" name="prompts[__PROMPT_KEY__][word_length_fixed]">
                                    </div>
                                    <div class="form-text">
                                        <?php echo esc_html__( 'Choose this option if you want to generate a fixed number of words, regardless of how long the selected text is. This is helpful for certain types of prompts, like generating a paragraph on a certain topic for example.', 'aikit' ); ?>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card col-sm-4 m-2 text-length-card relative-card">
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input mt-0" type="radio" name="prompts[__PROMPT_KEY__][word_length_type]" value="relative" id="prompts[__PROMPT_KEY__][word_length_type_relative]">
                                    <label class="form-check-label" for="prompts[__PROMPT_KEY__][word_length_type_relative]">
                                        <?php echo esc_html__( 'Relative to length of text selected', 'aikit' ); ?>
                                    </label>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label col-form-label-sm" for="prompts[__PROMPT_KEY__][word_length_relative]">
                                        <?php echo esc_html__( 'Multiplier', 'aikit' ); ?>
                                    </label>
                                    <div class="col-sm-4">
                                        <input type="range" step="0.1" min="1" max="6" class="form-range aikit-slider mt-0" id="prompts[__PROMPT_KEY__][word_length_relative]" value="1" name="prompts[__PROMPT_KEY__][word_length_relative]">
                                    </div>
                                    <div class="col-sm-2">
                                        <span class="slider-value"></span>
                                    </div>
                                    <div class="form-text">
                                        <?php echo esc_html__( 'Choose this option if you want to calculate the length of the generated words relative to the length of words selected. 1x = same length as select text, 2x means two times, etc. Summarization is a good candidate to use this option for.', 'aikit' ); ?>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>


                    <h6 class="mt-4">
                        <?php echo esc_html__( 'Prompts', 'aikit' ); ?>
                    </h6>
                    <div class="tabs">
                        <ul>
                            <?php
                            foreach ($languages as $language => $languageData) {
                                ?>
                                <li><a href="#__PROMPT_KEY___tabs_<?php echo esc_html__($language, 'aikit') ?>"><?php echo esc_html__( $this->languages[$language]['name'], 'aikit') ?></a></li>
                                <?php
                            }
                            ?>
                        </ul>
                        <?php
                        foreach ($languages as $language => $languageData) {
                            ?>
                            <div id="__PROMPT_KEY___tabs_<?php echo esc_html__($language, 'aikit') ?>">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control menu-title-input" id="prompts[__PROMPT_KEY__][languages][<?php echo esc_html__($language, 'aikit') ?>][menu_title]" name="prompts[__PROMPT_KEY__][languages][<?php echo esc_html__($language, 'aikit') ?>][menu_title]" value="" placeholder="<?php echo esc_html__( 'Menu title', 'aikit' ); ?>" />
                                    <label for="prompts[__PROMPT_KEY__][languages][<?php echo esc_html__($language, 'aikit') ?>][menu_title]">
                                        <?php echo esc_html__( 'Menu title', 'aikit' ); ?>
                                    </label>
                                    <div class="form-text">
                                        <?php echo esc_html__( 'This is title that will appear in the AI menu for this prompt.', 'aikit' ); ?>
                                    </div>
                                </div>


                                <div class="form-floating">
                                    <textarea class="form-control prompt-textarea" placeholder="Leave a comment here" name="prompts[__PROMPT_KEY__][languages][<?php echo esc_html__($language, 'aikit') ?>][prompt]" id="prompts[__PROMPT_KEY__][languages][<?php echo esc_html__($language, 'aikit') ?>][prompt]" cols="30" rows="10"></textarea>
                                    <label for="prompts[__PROMPT_KEY__][languages][<?php echo esc_html__($language, 'aikit') ?>][prompt]">
                                        <?php echo esc_html__( 'Prompt', 'aikit' ); ?>
                                    </label>
                                    <div class="form-text">
                                        <?php echo esc_html__( 'If this prompt requires text selection, the phrase', 'aikit' ); ?>
                                        <code>[[text]]</code>
                                        <?php echo esc_html__( 'will be replaced by the selected text before doing the request. Make sure to include it in your prompt.', 'aikit' ); ?>
                                        <?php echo esc_html__( 'You can also add', 'aikit' ); ?>
                                        <code>[[post_title]]</code>, <code>[[text_before]]</code>, <code>[[text_after]]</code>
                                        <?php echo esc_html__( 'to include the title of the post, the text before the selected text or the text after the selected text in the prompt to give AI more context of what you write about.', 'aikit' ); ?>
                                    </div>
                                </div>

                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>



            </div>
        </div>
        <?php
    }

    /**
     * Register settings.
     */
    public function register_settings() {

        add_settings_section(
            'aikit_settings_section_openai_text',
            '<i class="bi bi-pencil-square"></i> ' .esc_html__( 'Text Generation Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_openai_text_callback'),
            'aikit_openai_settings_text'
        );

        add_settings_section(
            'aikit_settings_section_openai_image',
            '<i class="bi bi-card-image"></i> ' . esc_html__( 'Image Generation Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_openai_image_callback'),
            'aikit_openai_settings_image'
        );

        add_settings_section(
            'aikit_settings_section_openai_tts',
            '<i class="bi bi-megaphone"></i> ' . esc_html__( 'Text-to-Speech Generation Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_openai_tts_callback'),
            'aikit_openai_settings_tts'
        );

        add_settings_section(
            'aikit_settings_section_openai',
            '<i class="bi bi-key"></i> ' . esc_html__( 'OpenAI Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_openai_callback'),
            'aikit_openai_settings'
        );

        // OpenAI Key
        register_setting('aikit_options', 'aikit_setting_openai_key');

        add_settings_field(
            'aikit_settings_openai_key',
            esc_html__( 'OpenAI Key', 'aikit' ),
            array ($this, 'aikit_settings_openai_key_callback'),
            'aikit_openai_settings',
            'aikit_settings_section_openai'
        );

        /// RapidAPI settings
        add_settings_section(
            'aikit_settings_section_rapidapi',
            esc_html__( 'RapidAPI Settings', 'aikit' ),
            false,
            'aikit_rapidapi_settings'
        );
        // RapidAPI key
        register_setting('aikit_options', 'aikit_setting_rapidapi_key');

        add_settings_field(
            'aikit_settings_rapidapi_key',
            esc_html__( 'Rapid API Key', 'aikit' ),
            array ($this, 'aikit_settings_rapidapi_key_callback'),
            'aikit_rapidapi_settings',
            'aikit_settings_section_rapidapi'
        );

        // OpenAI Model
        register_setting('aikit_options', 'aikit_setting_openai_model');

        add_settings_field(
            'aikit_settings_openai_model',
            esc_html__( 'Text Generation Model', 'aikit' ),
            array ($this, 'aikit_settings_openai_model_callback'),
            'aikit_openai_settings_text',
            'aikit_settings_section_openai_text'
        );

        // OpenAI Language used for content generation
        register_setting('aikit_options', 'aikit_setting_openai_language');

        add_settings_field(
            'aikit_settings_openai_language',
            esc_html__( 'Language for text generation', 'aikit' ),
            array ($this, 'aikit_settings_openai_language_callback'),
            'aikit_openai_settings_text',
            'aikit_settings_section_openai_text'
        );

        // OpenAI system message
        register_setting('aikit_options', 'aikit_setting_openai_system_message');
        add_settings_field(
            'aikit_settings_openai_system_message',
            esc_html__( 'OpenAI System Message', 'aikit' ),
            array ($this, 'aikit_settings_openai_system_message_callback'),
            'aikit_openai_settings_text',
            'aikit_settings_section_openai_text'
        );

        // OpenAI Max Tokens Multiplier
        register_setting('aikit_options', 'aikit_setting_openai_max_tokens_multiplier');

        add_settings_field(
            'aikit_settings_openai_max_tokens_multiplier',
            esc_html__( 'Max Tokens Multiplier (text length)', 'aikit' ),
            array ($this, 'aikit_settings_openai_max_tokens_multiplier_callback'),
            'aikit_openai_settings_text',
            'aikit_settings_section_openai_text'
        );

        // Prompt stop sequence
        register_setting('aikit_options', 'aikit_setting_prompt_stop_sequence');

        add_settings_field(
            'aikit_settings_prompt_stop_sequence',
            esc_html__( 'Prompt Stop Sequence', 'aikit' ),
            array ($this, 'aikit_settings_prompt_stop_sequence_callback'),
            'aikit_openai_settings_text',
            'aikit_settings_section_openai_text'
        );

        // Completion stop sequence
        register_setting('aikit_options', 'aikit_setting_completion_stop_sequence');

        add_settings_field(
            'aikit_settings_completion_stop_sequence',
            esc_html__( 'Completion Stop Sequence', 'aikit' ),
            array ($this, 'aikit_settings_completion_stop_sequence_callback'),
            'aikit_openai_settings_text',
            'aikit_settings_section_openai_text'
        );

        register_setting('aikit_options', 'aikit_setting_openai_image_model');

        add_settings_field(
            'aikit_settings_openai_image_model',
            esc_html__( 'Image Generation Model', 'aikit' ),
            array ($this, 'aikit_settings_openai_image_model_callback'),
            'aikit_openai_settings_image',
            'aikit_settings_section_openai_image'
        );

        register_setting('aikit_options', 'aikit_setting_openai_image_quality');

        add_settings_field(
            'aikit_settings_openai_image_quality',
            esc_html__( 'Image Preferred Quality', 'aikit' ),
            array ($this, 'aikit_settings_openai_image_quality_callback'),
            'aikit_openai_settings_image',
            'aikit_settings_section_openai_image'
        );

        register_setting('aikit_options', 'aikit_setting_openai_image_style');

        add_settings_field(
            'aikit_settings_openai_image_style',
            esc_html__( 'Image Preferred Style', 'aikit' ),
            array ($this, 'aikit_settings_openai_image_style_callback'),
            'aikit_openai_settings_image',
            'aikit_settings_section_openai_image'
        );


        register_setting('aikit_options', 'aikit_setting_openai_tts_model');
        add_settings_field(
            'aikit_settings_openai_tts_model',
            esc_html__( 'Text-to-Speech Generation Model', 'aikit' ),
            array ($this, 'aikit_settings_openai_tts_model_callback'),
            'aikit_openai_settings_tts',
            'aikit_settings_section_openai_tts'
        );

        register_setting('aikit_options', 'aikit_setting_openai_tts_voice');
        add_settings_field(
            'aikit_settings_openai_tts_voice',
            esc_html__( 'Text-to-Speech Voice', 'aikit' ),
            array ($this, 'aikit_settings_openai_tts_voice_callback'),
            'aikit_openai_settings_tts',
            'aikit_settings_section_openai_tts'
        );

//        // Autocompleted text background color
//        register_setting('aikit_options', 'aikit_setting_autocompleted_text_background_color');
//
//        add_settings_field(
//            'aikit_settings_autocompleted_text_background_color',
//            esc_html__( 'Autocompleted Text Background Color', 'aikit' ),
//            array ($this, 'aikit_settings_autocompleted_text_background_color_callback'),
//            'aikit_openai_settings',
//            'aikit_settings_section_openai'
//        );

        ///////////////////////////////
        // General settings
        ///////////////////////////////
        add_settings_section(
            'aikit_settings_section_general',
            esc_html__( 'General Settings', 'aikit' ),
            false,
            'aikit_general_settings'
        );

        // Elementor support
        register_setting('aikit_options', 'aikit_setting_elementor_supported');
        add_settings_field(
            'aikit_settings_elementor_supported',
            esc_html__( 'Elementor support', 'aikit' ),
            array ($this, 'aikit_settings_elementor_supported_callback'),
            'aikit_general_settings',
            'aikit_settings_section_general'
        );


        ///////////////////////////////
        /// AI Text Generation
        ///////////////////////////////

        add_settings_section(
            'aikit_settings_section_ai_text_generation',
            esc_html__( 'AI Text Generation Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_ai_text_generation_callback'),
            'aikit_ai_text_generation_settings'
        );

        register_setting('aikit_options', 'aikit_setting_default_ai_text_provider');

        add_settings_field(
            'aikit_settings_default_ai_text_provider',
            esc_html__( 'Default AI Text Provider', 'aikit' ),
            array ($this, 'aikit_settings_default_ai_text_provider_callback'),
            'aikit_ai_text_generation_settings',
            'aikit_settings_section_ai_text_generation'
        );

        ///////////////////////////////
        // Anthropic settings
        ///////////////////////////////
        add_settings_section(
            'aikit_settings_section_anthropic',
            '<i class="bi bi-key"></i> ' . esc_html__( 'Anthropic Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_anthropic_callback'),
            'aikit_anthropic_settings'
        );

        // Anthropic Key
        register_setting('aikit_options', 'aikit_setting_anthropic_key');

        add_settings_field(
            'aikit_settings_anthropic_key',
            esc_html__( 'Anthropic Key', 'aikit' ),
            array ($this, 'aikit_settings_anthropic_key_callback'),
            'aikit_anthropic_settings',
            'aikit_settings_section_anthropic'
        );

        // Anthropic Model

        register_setting('aikit_options', 'aikit_setting_anthropic_model');

        add_settings_field(
            'aikit_settings_anthropic_model',
            esc_html__( 'Text Generation Model', 'aikit' ),
            array ($this, 'aikit_settings_anthropic_model_callback'),
            'aikit_anthropic_settings',
            'aikit_settings_section_anthropic'
        );

        ///////////////////////////////
        // StabilityAI settings
        ///////////////////////////////
        add_settings_section(
            'aikit_settings_section_stabilityai',
            esc_html__( 'StabilityAI Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_stabilityai_callback'),
            'aikit_stabilityai_settings'
        );

        // key
        register_setting('aikit_options', 'aikit_setting_stabilityai_key');
        add_settings_field(
            'aikit_settings_stabilityai_key',
            esc_html__( 'StabilityAI Key', 'aikit' ),
            array ($this, 'aikit_settings_stabilityai_key_callback'),
            'aikit_stabilityai_settings',
            'aikit_settings_section_stabilityai'
        );

        // default engine
        register_setting('aikit_options', 'aikit_setting_stabilityai_default_engine');
        add_settings_field(
            'aikit_settings_stabilityai_default_engine',
            esc_html__( 'StabilityAI Default Engine', 'aikit' ),
            array ($this, 'aikit_settings_stabilityai_default_engine_callback'),
            'aikit_stabilityai_settings',
            'aikit_settings_section_stabilityai'
        );

        // default sampler
        register_setting('aikit_options', 'aikit_setting_stabilityai_default_sampler');
        add_settings_field(
            'aikit_settings_stabilityai_default_sampler',
            esc_html__( 'StabilityAI Default Sampler', 'aikit' ),
            array ($this, 'aikit_settings_stabilityai_default_sampler_callback'),
            'aikit_stabilityai_settings',
            'aikit_settings_section_stabilityai'
        );

        // default steps
        register_setting('aikit_options', 'aikit_setting_stabilityai_default_steps');
        add_settings_field(
            'aikit_settings_stabilityai_default_steps',
            esc_html__( 'StabilityAI Default Steps', 'aikit' ),
            array ($this, 'aikit_settings_stabilityai_default_steps_callback'),
            'aikit_stabilityai_settings',
            'aikit_settings_section_stabilityai'
        );

        // default cfg scale
        register_setting('aikit_options', 'aikit_setting_stabilityai_default_cfg_scale');
        add_settings_field(
            'aikit_settings_stabilityai_default_cfg_scale',
            esc_html__( 'StabilityAI Default Cfg Scale', 'aikit' ),
            array ($this, 'aikit_settings_stabilityai_default_cfg_scale_callback'),
            'aikit_stabilityai_settings',
            'aikit_settings_section_stabilityai'
        );

        // default seed
        register_setting('aikit_options', 'aikit_setting_stabilityai_default_seed');
        add_settings_field(
            'aikit_settings_stabilityai_default_seed',
            esc_html__( 'StabilityAI Default Seed', 'aikit' ),
            array ($this, 'aikit_settings_stabilityai_default_seed_callback'),
            'aikit_stabilityai_settings',
            'aikit_settings_section_stabilityai'
        );

        ///////////////////////////////
        // Text-to-Speech generation settings
        ///////////////////////////////
        add_settings_section(
            'aikit_settings_section_tts_generation',
            esc_html__( 'Text-to-Speech Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_tts_callback'),
            'aikit_tts_generation_settings'
        );

        // default TTS API
        register_setting('aikit_options', 'aikit_setting_default_tts_api');
        add_settings_field(
            'aikit_setting_default_tts_api',
            esc_html__( 'Default Text-to-Speech API', 'aikit' ),
            array ($this, 'aikit_setting_default_tts_api_callback'),
            'aikit_tts_generation_settings',
            'aikit_settings_section_tts_generation'
        );

        // Audio Player primary color
        register_setting('aikit_options', 'aikit_setting_audio_player_primary_color');
        add_settings_field(
            'aikit_setting_audio_player_primary_color',
            esc_html__( 'Audio Player Primary Color', 'aikit' ),
            array ($this, 'aikit_setting_audio_player_primary_color_callback'),
            'aikit_tts_generation_settings',
            'aikit_settings_section_tts_generation'
        );

        // Audio Player secondary color
        register_setting('aikit_options', 'aikit_setting_audio_player_secondary_color');
        add_settings_field(
            'aikit_setting_audio_player_secondary_color',
            esc_html__( 'Audio Player Secondary Color', 'aikit' ),
            array ($this, 'aikit_setting_audio_player_secondary_color_callback'),
            'aikit_tts_generation_settings',
            'aikit_settings_section_tts_generation'
        );

        // Audio Player message
        register_setting('aikit_options', 'aikit_setting_audio_player_message');
        add_settings_field(
            'aikit_setting_audio_player_message',
            esc_html__( 'Audio Player Message', 'aikit' ),
            array ($this, 'aikit_setting_audio_player_message_callback'),
            'aikit_tts_generation_settings',
            'aikit_settings_section_tts_generation'
        );

        ///////////////////////////////
        // Image generation settings
        ///////////////////////////////
        add_settings_section(
            'aikit_settings_section_image_generation',
            esc_html__( 'Image Generation Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_image_generation_callback'),
            'aikit_image_generation_settings'
        );

        // default image generation API
        register_setting('aikit_options', 'aikit_setting_default_image_generation_api');
        add_settings_field(
            'aikit_setting_default_image_generation_api',
            esc_html__( 'Default Image Generation API', 'aikit' ),
            array ($this, 'aikit_setting_default_image_generation_api_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        // Image sizes
        register_setting('aikit_options', 'aikit_setting_images_size_small');
        add_settings_field(
            'aikit_setting_images_size_small',
            esc_html__( 'Image sizes available', 'aikit' ),
            array ($this, 'aikit_setting_images_size_small_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        register_setting('aikit_options', 'aikit_setting_images_size_medium');
        add_settings_field(
            'aikit_setting_images_size_medium',
            '',
            array ($this, 'aikit_setting_images_size_medium_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        register_setting('aikit_options', 'aikit_setting_images_size_large');
        add_settings_field(
            'aikit_setting_images_size_large',
            '',
            array ($this, 'aikit_setting_images_size_large_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        register_setting('aikit_options', 'aikit_setting_images_size_xlarge_1344x768');
        add_settings_field(
            'aikit_setting_images_size_xlarge_1344x768',
            '',
            array ($this, 'aikit_setting_images_size_xlarge_1344x768_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        register_setting('aikit_options', 'aikit_setting_images_size_xlarge_1792x1024');
        add_settings_field(
            'aikit_setting_images_size_xlarge_1792x1024',
            '',
            array ($this, 'aikit_setting_images_size_xlarge_1792x1024_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        register_setting('aikit_options', 'aikit_setting_images_size_xlarge_1024x1792');
        add_settings_field(
            'aikit_setting_images_size_xlarge_1024x1792',
            '',
            array ($this, 'aikit_setting_images_size_xlarge_1024x1792_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        // Image counts for each size
        register_setting('aikit_options', 'aikit_setting_images_counts');
        add_settings_field(
            'aikit_setting_images_counts',
            esc_html__( 'Image counts for each size', 'aikit' ),
            array ($this, 'aikit_setting_images_counts_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        // Image generation styles
        register_setting('aikit_options', 'aikit_setting_images_styles');
        add_settings_field(
            'aikit_setting_images_styles',
            esc_html__( 'Image generation styles', 'aikit' ),
            array ($this, 'aikit_setting_images_styles_callback'),
            'aikit_image_generation_settings',
            'aikit_settings_section_image_generation'
        );

        //////////////////////////////////
        /// Qdrant settings
        //////////////////////////////////
        add_settings_section(
            'aikit_settings_section_qdrant',
            esc_html__( 'Qdrant Settings (Embeddings)', 'aikit' ),
            array ($this, 'aikit_settings_section_qdrant_callback'),
            'aikit_qdrant_settings'
        );

        // Qdrant host
        register_setting('aikit_options', 'aikit_setting_qdrant_host');
        add_settings_field(
            'aikit_setting_qdrant_host',
            esc_html__( 'Qdrant Host (Cluster URL)', 'aikit' ),
            array ($this, 'aikit_setting_qdrant_host_callback'),
            'aikit_qdrant_settings',
            'aikit_settings_section_qdrant'
        );

        // Qdrant API key
        register_setting('aikit_options', 'aikit_setting_qdrant_api_key');
        add_settings_field(
            'aikit_setting_qdrant_api_key',
            esc_html__( 'Qdrant API Key', 'aikit' ),
            array ($this, 'aikit_setting_qdrant_api_key_callback'),
            'aikit_qdrant_settings',
            'aikit_settings_section_qdrant'
        );

        //////////////////////////////////
        /// ElevenLabs settings
        //////////////////////////////////

        add_settings_section(
            'aikit_settings_section_elevenlabs',
            esc_html__( 'Eleven Labs Settings', 'aikit' ),
            array ($this, 'aikit_settings_section_elevenlabs_callback'),
            'aikit_elevenlabs_settings'
        );

        // ElevenLabs API key
        register_setting('aikit_options', 'aikit_setting_elevenlabs_api_key');
        add_settings_field(
            'aikit_setting_elevenlabs_api_key',
            esc_html__( 'Eleven Labs API Key', 'aikit' ),
            array ($this, 'aikit_setting_elevenlabs_api_key_callback'),
            'aikit_elevenlabs_settings',
            'aikit_settings_section_elevenlabs'
        );

        // Model
        register_setting('aikit_options', 'aikit_setting_elevenlabs_model');
        add_settings_field(
            'aikit_setting_elevenlabs_model',
            esc_html__( 'Eleven Labs Model', 'aikit' ),
            array ($this, 'aikit_setting_elevenlabs_model_callback'),
            'aikit_elevenlabs_settings',
            'aikit_settings_section_elevenlabs'
        );

        // Voice
        register_setting('aikit_options', 'aikit_setting_elevenlabs_voice');
        add_settings_field(
            'aikit_setting_elevenlabs_voice',
            esc_html__( 'Eleven Labs Voice', 'aikit' ),
            array ($this, 'aikit_setting_elevenlabs_voice_callback'),
            'aikit_elevenlabs_settings',
            'aikit_settings_section_elevenlabs'
        );

    }

    function aikit_settings_section_ai_text_generation_callback()
    {
        echo '<p>' . esc_html__( 'These settings are used for text generation.', 'aikit' ) . '</p>';
    }

    function aikit_settings_default_ai_text_provider_callback()
    {
        $setting = get_option('aikit_setting_default_ai_text_provider');
        ?>
        <select id="aikit_setting_default_ai_text_provider" name="aikit_setting_default_ai_text_provider">
            <?php
            foreach (AIKIT_AI_TEXT_PROVIDERS as $provider => $name) {
                ?>
                <option value="<?php echo esc_attr( $provider ); ?>" <?php selected( $setting, $provider ); ?>><?php echo esc_html( $name ); ?></option>
                <?php
            }
            ?>
        </select>

        <p>
            <?php esc_html_e('This is the default AI text provider that will be used for text generation (when no provider is specified).', 'aikit'); ?>
        </p>
        <?php
    }

    function aikit_settings_section_anthropic_callback()
    {
        echo '<p>' . esc_html__( 'These settings are used for text generation.', 'aikit' ) . '</p>';
    }

    function aikit_settings_anthropic_key_callback()
    {
        $setting = get_option('aikit_setting_anthropic_key');
        ?>
        <input type="text" id="aikit_setting_anthropic_key" name="aikit_setting_anthropic_key" value="<?php echo esc_attr( $setting ); ?>" class="regular-text">
        <p>
            <?php esc_html_e('This is the API key that will be used for text generation. You can generate one here:', 'aikit'); ?> <a href="https://console.anthropic.com/" target="_blank">https://console.anthropic.com/</a>
        </p>
        <?php

    }

    function aikit_settings_anthropic_model_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('aikit_setting_anthropic_model');
        $models = AIKIT_ANTHROPIC_MODELS;

        ?>
        <select id="aikit_setting_anthropic_model" name="aikit_setting_anthropic_model">
            <?php
            foreach ($models as $model) {
                ?>
                <option value="<?php echo esc_attr( $model ); ?>" <?php selected( $setting, $model ); ?>><?php echo esc_html( $model ); ?></option>
                <?php
            }
            ?>
        </select>
        <p>
            <?php esc_html_e('This is the model that will be used for text generation.', 'aikit'); ?>
        </p>
        <?php
    }


    function aikit_settings_section_tts_callback () {
        echo '<p>' . esc_html__( 'These settings are used for text-to-speech generation and audio player customization.', 'aikit' ) . '</p>';
    }

    function aikit_settings_section_openai_text_callback() {
        echo '<p> ' . esc_html__( 'These settings are used for text generation.', 'aikit' ) . '</p>';

    }

    function aikit_settings_section_openai_image_callback() {
        echo '<p>' . esc_html__( 'These settings are used for image generation.', 'aikit' ) . '</p>';

    }

    function aikit_settings_section_openai_tts_callback() {
        echo '<p>' . esc_html__( 'These settings are used for text-to-speech generation.', 'aikit' ) . '</p>';
    }

    function aikit_setting_default_tts_api_callback() {
        $setting = get_option('aikit_setting_default_tts_api');
        ?>
            <div class="mb-2">
                <input type="radio" id="aikit_setting_default_tts_api_elevenlabs" name="aikit_setting_default_tts_api" value="elevenlabs" <?php checked( $setting, 'elevenlabs' ); ?>>
                <label for="aikit_setting_default_tts_api_elevenlabs"><?php esc_html_e('Eleven Labs', 'aikit'); ?></label><br>
            </div>
            <div class="mb-2">
                <input type="radio" id="aikit_setting_default_tts_api_openai" name="aikit_setting_default_tts_api" value="openai" <?php checked( $setting, 'openai' ); ?>>
                <label for="aikit_setting_default_tts_api_openai"><?php esc_html_e('OpenAI', 'aikit'); ?></label><br>
            </div>

            <p>
                <?php esc_html_e('This is the default API that will be used for text-to-speech generation.', 'aikit'); ?>
            </p>
        <?php
    }

    function aikit_settings_openai_tts_model_callback() {
        $setting = get_option('aikit_setting_openai_tts_model');

        $models = $this->aikit_openai_get_tts_models();
        ?>
            <select id="aikit_setting_openai_tts_model" name="aikit_setting_openai_tts_model">
                <?php
                foreach ($models as $model) {
                    ?>
                    <option value="<?php echo esc_attr( $model ); ?>" <?php selected( $setting, $model ); ?>><?php echo esc_html( $model ); ?></option>
                    <?php
                }
                ?>
            </select>
            <p>
                <?php esc_html_e('This is the OpenAI model that will be used to generate the audio.', 'aikit'); ?>
            </p>
        <?php
    }

    function aikit_settings_openai_tts_voice_callback()
    {
        $setting = get_option('aikit_setting_openai_tts_voice');

        $voices = $this->aikit_openai_get_tts_voices();
        ?>
            <select id="aikit_setting_openai_tts_voice" name="aikit_setting_openai_tts_voice">
                <?php
                foreach ($voices as $voice) {
                    ?>
                    <option value="<?php echo esc_attr( $voice ); ?>" <?php selected( $setting, $voice ); ?>><?php echo esc_html( $voice ); ?></option>
                    <?php
                }
                ?>
            </select>
            <p>
                <?php esc_html_e('This is the OpenAI voice that will be used to generate the audio. For easier selection you can listen to the voices here:', 'aikit'); ?> <a href="https://platform.openai.com/docs/guides/text-to-speech/voice-options" target="_blank">https://platform.openai.com/docs/guides/text-to-speech/voice-options</a>
            </p>
        <?php

    }

    function aikit_openai_get_tts_voices()
    {
        return [
            'alloy', 'echo', 'fable', 'onyx', 'nova', 'shimmer'
        ];
    }

    function aikit_openai_get_tts_models() {
        $default_models = ['tts-1-hd', 'tts-1'];

        $models = aikit_rest_openai_get_available_models('tts');

        return $models === false ? $default_models : $models;
    }


    function aikit_settings_openai_image_quality_callback() {
        $setting = get_option('aikit_setting_openai_image_quality');
        ?>
            <select id="aikit_setting_openai_image_quality" name="aikit_setting_openai_image_quality">
                <option value="sd" <?php selected( $setting, 'sd' ); ?>><?php echo esc_html__( 'Standard definition', 'aikit' ); ?></option>
                <option value="hd" <?php selected( $setting, 'hd' ); ?>><?php echo esc_html__( 'High Definition', 'aikit' ); ?></option>
            </select>
            <p>
                <small>
                    <?php esc_html_e('Currently only "dalle.e 3" model supports high definition image generation.', 'aikit'); ?>
                </small>
            </p>
        <?php
    }

    function aikit_settings_openai_image_style_callback () {
        $setting = get_option('aikit_setting_openai_image_style');
        ?>
            <select id="aikit_setting_openai_image_style" name="aikit_setting_openai_image_style">
                <option value="natural" <?php selected( $setting, 'natural' ); ?>><?php echo esc_html__( 'Natural', 'aikit' ); ?></option>
                <option value="vivid" <?php selected( $setting, 'vivid' ); ?>><?php echo esc_html__( 'Vivid', 'aikit' ); ?></option>
            </select>
            <p>
                <small>
                    <?php esc_html_e('Currently only "dalle.e 3" model supports image styles.', 'aikit'); ?>
                </small>
            </p>
        <?php
    }

    function aikit_setting_elevenlabs_model_callback() {
        $setting = get_option('aikit_setting_elevenlabs_model');

        $models = aikit_elevenlabs_get_models();
        ?>
            <select id="aikit_setting_elevenlabs_model" name="aikit_setting_elevenlabs_model">
                <?php
                foreach ($models as $id => $name) {
                    ?>
                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $setting, $id ); ?>><?php echo esc_html( $name ); ?></option>
                    <?php
                }
                ?>
            </select>
            <p>
                <?php esc_html_e('This is the Eleven Labs model that will be used to generate the audio.', 'aikit'); ?>
            </p>
        <?php
    }

    function aikit_setting_elevenlabs_voice_callback () {
        $setting = get_option('aikit_setting_elevenlabs_voice');

        $voices = aikit_elevenlabs_get_voices();
        ?>
            <select id="aikit_setting_elevenlabs_voice" name="aikit_setting_elevenlabs_voice">
                <?php
                foreach ($voices as $id => $name) {
                    ?>
                    <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $setting, $id ); ?>><?php echo esc_html( $name ); ?></option>
                    <?php
                }
                ?>
            </select>
            <p>
                <?php esc_html_e('This is the Eleven Labs voice that will be used to generate the audio. Please try available voices ', 'aikit'); echo '<a href="https://elevenlabs.io/speech-synthesis" target="_blank">' . esc_html__( 'here', 'aikit' ) . '</a>'; ?>.
            </p>
        <?php
    }

    function aikit_setting_audio_player_primary_color_callback() {
        $setting = get_option('aikit_setting_audio_player_primary_color');
        ?>
            <input type="color" id="aikit_setting_audio_player_primary_color" name="aikit_setting_audio_player_primary_color" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>" />
            <p>
                <?php esc_html_e('This is the primary color of the audio player.', 'aikit'); ?>
            </p>
        <?php
    }

    function aikit_setting_audio_player_secondary_color_callback() {
        $setting = get_option('aikit_setting_audio_player_secondary_color');
        ?>
            <input type="color" id="aikit_setting_audio_player_secondary_color" name="aikit_setting_audio_player_secondary_color" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>" />
            <p>
                <?php esc_html_e('This is the secondary color of the audio player.', 'aikit'); ?>
            </p>
        <?php
    }

    function aikit_setting_audio_player_message_callback() {
        $setting = get_option('aikit_setting_audio_player_message');
        ?>
            <input size="100" type="text" id="aikit_setting_audio_player_message" name="aikit_setting_audio_player_message" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>" />
            <p>
                <?php esc_html_e('This is the message that will be displayed in the audio player.', 'aikit'); ?>
            </p>
        <?php
    }

    function aikit_settings_section_elevenlabs_callback() {
        echo '<p id="aikit-elevenlabs-settings">' . '<a href="https://eleven-labs.com/" target="_blank">Eleven Labs</a> '  . esc_html__( 'provides one of the best and human-like AI text-to-speech and voice cloning services.', 'aikit' ) . '</p>';
    }

    function aikit_setting_elevenlabs_api_key_callback() {
        $setting = get_option('aikit_setting_elevenlabs_api_key');
        ?>
            <input size="100" type="text" id="aikit_setting_elevenlabs_api_key" name="aikit_setting_elevenlabs_api_key" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>" />
            <p>
                <?php esc_html_e('You can get your ElevenLabs API key from your ', 'aikit'); echo '<a href="https://elevenlabs.io/" target="_blank">' . esc_html__( 'Eleven Labs account', 'aikit' ) . '</a>'; ?>.
            </p>
        <?php
    }

    function aikit_settings_section_qdrant_callback() {
        echo '<p id="aikit-qdrant-settings">' . '<a href="https://qdrant.io/" target="_blank">Qdrant</a> '  . esc_html__( 'is a vector search engine. It is used to store and query embeddings, which allow you to do similarity search and can be used along with AIKit Chatbot to efficiently answer your users\' questions around your product or services.', 'aikit' ) . '</p>';
    }

    function aikit_setting_qdrant_host_callback() {
        $setting = get_option('aikit_setting_qdrant_host');
        ?>
            <input size="100" type="text" id="aikit_setting_qdrant_host" name="aikit_setting_qdrant_host" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>" />
            <p>
                <?php esc_html_e('You can get your Qdrant host address from your ', 'aikit'); echo '<a href="https://cloud.qdrant.io/" target="_blank">' . esc_html__( 'Qdrant account', 'aikit' ) . '</a>'; ?>.
            </p>
        <?php
    }

    function aikit_setting_qdrant_api_key_callback() {
        $setting = get_option('aikit_setting_qdrant_api_key');
        ?>
            <input size="100" type="text" id="aikit_setting_qdrant_api_key" name="aikit_setting_qdrant_api_key" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>" />
            <p>
                <?php esc_html_e('You can get your API key from your ', 'aikit'); echo '<a href="https://cloud.qdrant.io/" target="_blank">' . esc_html__( 'Qdrant account', 'aikit' ) . '</a>'; ?>.
                <?php esc_html_e('Leave empty if you are using a hosted instance with no API key.', 'aikit'); ?>
            </p>
        <?php
    }

    function aikit_setting_default_image_generation_api_callback() {
        $setting = get_option('aikit_setting_default_image_generation_api');
        ?>
            <div class="mb-2">
                <input type="radio" id="aikit_setting_image_generation_api_openai" name="aikit_setting_default_image_generation_api" value="openai" <?php checked( $setting, 'openai' ); ?>>
                <label for="aikit_setting_image_generation_api_openai"><?php esc_html_e('OpenAI', 'aikit'); ?> - DALL.E </label><br>
            </div>
            <div class="mb-2">
                <input type="radio" id="aikit_setting_image_generation_api_stabilityai" name="aikit_setting_default_image_generation_api" value="stability-ai" <?php checked( $setting, 'stability-ai' ); ?>>
                <label for="aikit_setting_image_generation_api_stabilityai"><?php esc_html_e('Stability.ai', 'aikit'); ?> (Stable Diffusion)</label><br>
            </div>

        <p>
            <small>
            <?php echo esc_html__('Select the default image generation API. This is the image generation API that will be used in the background jobs like auto writer and repurposing jobs.', 'aikit'); ?>
            </small>
        </p>

        <?php
    }

    function aikit_settings_section_image_generation_callback() {
        echo '<p>' . esc_html__('Here you can adjust general settings for image generation.', 'aikit') .'</p>';
    }

    function aikit_settings_section_stabilityai_callback() {
        echo '<p><a target="_blank" href="https://stability.ai/">' . esc_html__('Stability.ai') . '</a> ' . esc_html__('is a Stable Diffusion based AI image generator. It is a great alternative to OpenAI for image generation. Here you can adjust the settings for the StabilityAI.', 'aikit') .'</p>';
    }

    function aikit_settings_stabilityai_key_callback() {
        $setting = get_option('aikit_setting_stabilityai_key');
        ?>
            <input size="100" type="text" name="aikit_setting_stabilityai_key" value="<?php echo esc_attr($setting); ?>" />
            <p>
                <small>
                <?php echo esc_html__('Please enter your StabilityAI key here. You can get your key from the StabilityAI website.', 'aikit'); ?>
                </small>
            </p>
        <?php
    }

    function aikit_settings_stabilityai_default_engine_callback() {
        $value = get_option('aikit_setting_stabilityai_default_engine');

        ?>
        <select name="aikit_setting_stabilityai_default_engine">
            <option value="stable-diffusion-v1-6" <?php selected('stable-diffusion-v1-6', $value, true); ?>><?php echo esc_html( 'Stable Diffusion v1.6' ); ?></option>
            <option value="stable-diffusion-xl-1024-v1-0" <?php selected('stable-diffusion-xl-1024-v1-0', $value, true); ?>><?php echo esc_html( 'Stable Diffusion XL v1.0' ); ?></option>
            <option value="stable-diffusion-xl-1024-v0-9" <?php selected('stable-diffusion-xl-1024-v0-9', $value, true); ?>><?php echo esc_html( 'Stable Diffusion XL v0.9' ); ?></option>
            <option value="stable-diffusion-xl-beta-v2-2-2" <?php selected('stable-diffusion-xl-beta-v2-2-2', $value, true); ?>><?php echo esc_html( 'Stable Diffusion v2.2.2-XL Beta' ); ?></option>
        </select>
        <p>
            <small>
                <?php
                echo esc_html__('The default model that will be used when generating an image using Stability.ai.', 'aikit');
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_stabilityai_default_sampler_callback() {
        $value = get_option('aikit_setting_stabilityai_default_sampler');
        ?>
        <select name="aikit_setting_stabilityai_default_sampler">
            <option value="DDIM" <?php selected('DDIM', $value, true); ?>><?php echo esc_html( 'DDIM' ); ?></option>
            <option value="DDPM" <?php selected('DDPM', $value, true); ?>><?php echo esc_html( 'DDPM'); ?></option>
            <option value="K_DPMPP_2M" <?php selected('K_DPMPP_2M', $value, true); ?>><?php echo esc_html( 'K_DPMPP_2M'); ?></option>
            <option value="K_DPMPP_2S_ANCESTRAL" <?php selected('K_DPMPP_2S_ANCESTRAL', $value, true); ?>><?php echo esc_html( 'K_DPMPP_2S_ANCESTRAL'); ?></option>
            <option value="K_DPM_2" <?php selected('K_DPM_2', $value, true); ?>><?php echo esc_html__( 'K_DPM_2'); ?></option>
            <option value="K_DPM_2_ANCESTRAL" <?php selected('K_DPM_2_ANCESTRAL', $value, true); ?>><?php echo esc_html( 'K_DPM_2_ANCESTRAL'); ?></option>
            <option value="K_EULER" <?php selected('K_EULER', $value, true); ?>><?php echo esc_html( 'K_EULER'); ?></option>
            <option value="K_EULER_ANCESTRAL" <?php selected('K_EULER_ANCESTRAL', $value, true); ?>><?php echo esc_html( 'K_EULER_ANCESTRAL' ); ?></option>
            <option value="K_HEUN" <?php selected('K_HEUN', $value, true); ?>><?php echo esc_html( 'K_HEUN'); ?></option>
            <option value="K_LMS" <?php selected('K_LMS', $value, true); ?>><?php echo esc_html( 'K_LMS' ); ?></option>
        </select>
        <p>
            <small>
                <?php
                echo esc_html__('A sampler determines how the image is "calculated". A sampler processes an input (prompt) to produce an output (image). Since these samplers are different mathematically, they will produce difference results for the same prompt.', 'aikit');
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_stabilityai_default_steps_callback() {
        $value = get_option('aikit_setting_stabilityai_default_steps');
        ?>
        <input type="number" step="1" min="10" max="150" name="aikit_setting_stabilityai_default_steps" value="<?php echo $value; ?>" />
        <p>
            <small>
                <?php
                echo esc_html__("Generation steps control how many times the image is sampled. Increasing the number of steps might give you better results, up to a point where there're diminishing returns. More steps would also cost you more.", 'aikit');
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_stabilityai_default_cfg_scale_callback() {
        $setting = get_option('aikit_setting_stabilityai_default_cfg_scale');
        ?>
            <input size="100" type="number" min="0" max="35" name="aikit_setting_stabilityai_default_cfg_scale" value="<?php echo esc_attr($setting); ?>" />
            <p>
                <small>
                <?php echo esc_html__("Prompt strength (CFG scale) controls how much the final image will adhere to your prompt. Lower values would give the model more \"creativity\", while higher values will produce a final image that's close to your prompt.", 'aikit'); ?>
                </small>
            </p>
        <?php
    }

    function aikit_settings_stabilityai_default_seed_callback() {
        $setting = get_option('aikit_setting_stabilityai_default_seed');
        ?>
            <input size="100" type="number" min="0" max="4294967295" name="aikit_setting_stabilityai_default_seed" value="<?php echo esc_attr($setting); ?>" />
            <p>
                <small>
                <?php echo esc_html__('Seed is a number used to initialize the image generation. Using a certain seed with same settings will produce the same image. "0" means a random seed will be used everytime.', 'aikit'); ?>
                </small>
            </p>
        <?php
    }

    function aikit_settings_section_openai_callback() {
        echo '<p>' . esc_html__('Adjust the plugin to your needs by editing the settings here.', 'aikit') .'</p>';
    }

    function aikit_settings_prompt_stop_sequence_callback() {
        $setting = get_option('aikit_setting_prompt_stop_sequence');
        ?>
            <input size="100" type="text" name="aikit_setting_prompt_stop_sequence" value="<?php echo esc_attr($setting); ?>" />
            <p>
                <small>
                <?php echo esc_html__('Please set this only if you are using a fine-tuned model. Leave empty if you are using any of the built-in models. Prompt stop sequence is used to mark the stop of the prompt.', 'aikit'); ?>
                </small>
            </p>
        <?php
    }

    function aikit_settings_completion_stop_sequence_callback() {
        $setting = get_option('aikit_setting_completion_stop_sequence');
        ?>
            <input size="100" type="text" name="aikit_setting_completion_stop_sequence" value="<?php echo esc_attr($setting); ?>" />
            <p>
                <small>
                <?php echo esc_html__('Please set this only if you are using a fine-tuned model. Leave empty if you are using any of the built-in models. Completion stop sequence is used to mark the stop of the completion.', 'aikit'); ?>
                </small>
            </p>
        <?php
    }

    function aikit_settings_rapidapi_key_callback() {
        $setting = get_option('aikit_setting_rapidapi_key');
        ?>
            <input size="100" type="text" name="aikit_setting_rapidapi_key" value="<?php echo esc_attr($setting); ?>" />
            <p>
                <small>
                <?php echo esc_html__('Enter your RapidAPI key for', 'aikit'); ?>
                <a target="_blank" href="https://rapidapi.com/yashagarwal/api/subtitles-for-youtube"><?php echo esc_html__('Subtitles for YouTube', 'aikit'); ?></a>
                <?php echo esc_html__(' here. This API is used to read YouTube video subtitles to allow you to fetch the content of videos and repurpose/spin them and create posts based on them in your website.', 'aikit'); ?>
                <?php echo esc_html__('"Subtitles for YouTube" API offers a generous 100 free requests per day which will be enough for most users.', 'aikit'); ?>
                <?php echo esc_html__('If you would like to repurpose videos, please', 'aikit'); ?>
                <a href="https://rapidapi.com/yashagarwal/api/subtitles-for-youtube/pricing" target="_blank"><?php echo esc_html__('subscribe', 'aikit'); ?></a>
                <?php echo esc_html__('to a plan, then enter your API key here.', 'aikit'); ?>
                <small>
            </p>
        <?php
    }

    function aikit_settings_elementor_supported_callback () {
        $setting = get_option('aikit_setting_elementor_supported');

        ?>
        <input type="checkbox" id="aikit_setting_elementor_supported" name="aikit_setting_elementor_supported" value="1" <?php checked(1, $setting, true); ?>>
        <label for="aikit_setting_elementor_supported"><?php echo esc_html__('Elementor supported?', 'aikit'); ?></label>
        <p>
            <small>
                <?php
                echo esc_html__('When this is enabled, you will be able to use AIKit right inside Elementor editor using a widget called "AIKit Editor".', 'aikit');
                ?>
            </small>

        </p>
        <?php
    }

    function aikit_setting_images_styles_callback () {
        // styles will be a text area, one style per line

        $setting = get_option('aikit_setting_images_styles');

        if (isset($setting) && !empty($setting)) {
            $setting = explode("\n", $setting);
            $setting = array_map('trim', $setting);

            // remove empty lines
            $setting = array_filter($setting, function($value) {
                return !empty($value);
            });

	        $setting = implode("\n", $setting);

            update_option('aikit_setting_images_styles', $setting);
        }

        ?>
        <textarea name="aikit_setting_images_styles" id="aikit_setting_images_styles" cols="60" rows="10"><?php echo esc_attr($setting); ?></textarea>

        <p>
            <small>
	            <?php
	            echo esc_html__('Image styles are phrases that will be added at the end an image\'s prompt to change the look of the image. You can use it to maintain a certain style of images for your posts. If you like colorful images, you can add "colourful" as one of the styles. If you want to appear as if they were drawn by leonardo davinci, add "by leonardo davinci" as a style. Each line would be considered a different style and AIKit will choose a random style out of this list each time it generates an image and append it at the end of the prompt. ', 'aikit');
	            ?>
            </small>
        </p>
        <?php


    }

    function aikit_setting_images_counts_callback() {
	    $setting = get_option('aikit_setting_images_counts');

        // make sure that the value is a comma separated list of integers
        if (isset($setting) && !empty($setting)) {
            $setting = explode(',', $setting);
            $setting = array_map('intval', $setting);

            // minimum of 1 and maximum of 10
            $setting = array_map(function($value) {
                return max(1, min(10, $value));
            }, $setting);

            $setting = implode(',', $setting);

            // update the option
            update_option('aikit_setting_images_counts', $setting);
        } else {
            $setting = AIKIT_DEFAULT_SETTING_IMAGES_COUNTS;
	        update_option('aikit_setting_images_counts', $setting);
        }

        ?>
        <input size="50" type="text" name="aikit_setting_images_counts" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
        <p>
            <small>
			    <?php
			    echo esc_html__('This controls the number of image you want to generate for each image size, these will appear as options in the AI image generation menu. Enter comma-separated numbers. Example: "1,2,4". Maximum allowed number is 10.', 'aikit');
			    ?>
            </small>
        </p>
        <?php
    }

    function aikit_setting_images_size_small_callback() {
        $setting = get_option('aikit_setting_images_size_small');

        ?>
            <input type="checkbox" id="aikit_setting_images_size_small" name="aikit_setting_images_size_small" value="1" <?php checked(1, $setting, true); ?>>
            <label for="aikit_setting_images_size_small"><?php echo esc_html__('Small', 'aikit'); ?> (256x256) - <?php echo esc_html__('Only available for Dall.e 2', 'aikit'); ?></label>
        <p>
            <small>
                <?php
                echo esc_html__('This controls the size of the images generated. If you want to have the option to generate small images in the AI image generation menu, check this box.', 'aikit');
                ?>
            </small>

        </p>
        <?php
    }

    function aikit_setting_images_size_medium_callback () {
        $setting = get_option('aikit_setting_images_size_medium');

        ?>

            <input type="checkbox" id="aikit_setting_images_size_medium" name="aikit_setting_images_size_medium" value="1" <?php checked(1, $setting, true); ?>>
            <label for="aikit_setting_images_size_medium"><?php echo esc_html__('Medium', 'aikit'); ?> (512x512) - <?php echo esc_html__('Only available for Dall.e 2 & Stable Diffusion', 'aikit'); ?></label>

        <p>
            <small>
                <?php
                echo esc_html__('If you want to have the option to generate medium images in the AI image generation menu, check this box.', 'aikit');
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_setting_images_size_large_callback () {
        $setting = get_option('aikit_setting_images_size_large');

        ?>

            <input type="checkbox" id="aikit_setting_images_size_large" name="aikit_setting_images_size_large" value="1" <?php checked(1, $setting, true); ?>>
            <label for="aikit_setting_images_size_large"><?php echo esc_html__('Large', 'aikit'); ?> (1024x1204) - <?php echo esc_html__('Available for all Dall.e & Stable Diffusion models.', 'aikit'); ?></label>

        <p>
            <small>
                <?php
                echo esc_html__('If you want to have the option to generate large images in the AI image generation menu, check this box.', 'aikit');
                ?>
            </small>

        </p>
        <?php
    }

    function aikit_setting_images_size_xlarge_1792x1024_callback() {
        $setting = get_option('aikit_setting_images_size_xlarge_1792x1024');

        ?>

        <input type="checkbox" id="aikit_setting_images_size_xlarge_1792x1024" name="aikit_setting_images_size_xlarge_1792x1024" value="1" <?php checked(1, $setting, true); ?>>
        <label for="aikit_setting_images_size_xlarge_1792x1024"><?php echo esc_html__(' X Large', 'aikit'); ?> (1792x1024) - <?php echo esc_html__('Only available for Dall.e 3', 'aikit'); ?></label>

        <p>
            <small>
                <?php
                echo esc_html__('If you want to have the option to generate x large images (landscape) in the AI image generation menu, check this box.', 'aikit');
                ?>
            </small>

        </p>
        <?php
    }

    function aikit_setting_images_size_xlarge_1024x1792_callback() {
        $setting = get_option('aikit_setting_images_size_xlarge_1024x1792');

        ?>

        <input type="checkbox" id="aikit_setting_images_size_xlarge_1024x1792" name="aikit_setting_images_size_xlarge_1024x1792" value="1" <?php checked(1, $setting, true); ?>>
        <label for="aikit_setting_images_size_xlarge_1024x1792"><?php echo esc_html__(' X Large', 'aikit'); ?> (1024x1792) - <?php echo esc_html__('Only available for Dall.e 3', 'aikit'); ?></label>

        <p>
            <small>
                <?php
                echo esc_html__('If you want to have the option to generate x large images (portrait) in the AI image generation menu, check this box.', 'aikit');
                ?>
            </small>

        </p>
        <?php
    }

    function aikit_setting_images_size_xlarge_1344x768_callback() {
        $setting = get_option('aikit_setting_images_size_xlarge_1344x768');

        ?>

        <input type="checkbox" id="aikit_setting_images_size_xlarge_1344x768" name="aikit_setting_images_size_xlarge_1344x768" value="1" <?php checked(1, $setting, true); ?>>
        <label for="aikit_setting_images_size_xlarge_1344x768"><?php echo esc_html__(' X Large', 'aikit'); ?> (1344x768) - <?php echo esc_html__('Only available for Stable Diffusion "SDXL" model', 'aikit'); ?></label>

        <p>
            <small>
                <?php
                echo esc_html__('If you want to have the option to generate x large images (portrait) in the AI image generation menu, check this box.', 'aikit');
                ?>
            </small>

        </p>
        <?php
    }

    function aikit_settings_openai_key_callback() {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('aikit_setting_openai_key');
        // output the field
        if (!empty($setting)) {
            $fetchedModels = aikit_rest_openai_get_available_models('text', false, true);
            if ($fetchedModels === false) {
                update_option('aikit_setting_openai_key_valid', false);
                // show a notice to the user that the key is invalid
                echo '<p class="aikit-invalid-key">' . esc_html__('The OpenAI key is invalid. Make sure you have entered the correct key.', 'aikit') . '</p>';
            } else {
                // add an option marking that the key is valid
                update_option('aikit_setting_openai_key_valid', true);
                // store the fetched models in the database
                update_option('aikit_setting_openai_available_models', $fetchedModels);
                // show a notice to the user that the key is valid
                echo '<p class="aikit-valid-key">' . esc_html__('The OpenAI key is valid.', 'aikit') . '</p>';
            }
        } else {
            // remove the option marking that the key is valid
            update_option('aikit_setting_openai_key_valid', false);
        }
        ?>
        <input size="100" type="text" name="aikit_setting_openai_key" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
        <p>
            <small>
                <?php
                echo esc_html__('For instructions on how to get an OpenAI key, please visit ', 'aikit') . '<a href="https://getaikit.com/docs/getting-started" target="_blank">https://getaikit.com/docs/getting-started</a>';
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_openai_language_callback() {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('aikit_setting_openai_language');
        // output the field

        // default to english
        if (empty($setting)) {
            $setting = 'en';
        }

        // output the field
        ?>
        <select name="aikit_setting_openai_language">
            <?php
            foreach ($this->languages as $language => $languageObj) {
                ?>
                <option value="<?php echo esc_html__($language); ?>" <?php echo $setting === $language ? 'selected' : ''; ?>><?php echo esc_html__($languageObj['translatedName']); ?></option>
                <?php
            }
            ?>
        </select>
        <p>
            <small>
                <?php
                echo esc_html__('The language of the text you want to generate.', 'aikit');
                ?>
                <br>
                <?php
                echo esc_html__(' For consistent autocomplete results, make sure that the text you write in your post is written in the same language you picked here.', 'aikit');
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_openai_image_model_callback () {
        $setting = get_option('aikit_setting_openai_image_model');

        $models = $this->aikit_get_image_models();

        ?>
        <select name="aikit_setting_openai_image_model">
            <?php foreach ($models as $model) { ?>
                <option value="<?php echo esc_html__($model, 'aikit'); ?>" <?php echo $setting == $model ? 'selected' : ''; ?>><?php echo esc_html__($model, 'aikit'); ?></option>
            <?php }
            ?>
        </select>
        <p>
            <small>
                <?php
                echo esc_html__('"Dall.e 3" is currently the most capable image generation model.', 'aikit');

                echo esc_html__(' For more information, see ', 'aikit') . '<a href="https://platform.openai.com/docs/models" target="_blank">https://platform.openai.com/docs/models</a>.';
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_get_image_models () {
        $default_models = ['dall-e-2', 'dall-e-3'];

        $models = aikit_rest_openai_get_available_models('images');

        return $models === false ? $default_models : array_unique(array_merge($models, $default_models));
    }

    function aikit_settings_openai_model_callback() {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('aikit_setting_openai_model');

        $defaultModels = aikit_openai_get_default_model_list();

        $fetchedModels = get_option('aikit_setting_openai_available_models');
        if ($fetchedModels === false) {
            $fetchedModels = [];
        }

        $allModels = array_merge($defaultModels, $fetchedModels);

        ?>
        <select name="aikit_setting_openai_model">
            <?php foreach ($allModels as $model) { ?>
                <option value="<?php echo esc_html__($model, 'aikit'); ?>" <?php echo $setting == $model ? 'selected' : ''; ?>><?php echo esc_html__($model, 'aikit'); ?></option>
            <?php }
            ?>
        </select>
        <p>
            <small>
                <?php
                echo esc_html__('Some models are more capable than others. For example, the "gpt-3.5-turbo" model provides good balance between cost and value right now.', 'aikit');

                echo esc_html__(' For more information, see ', 'aikit') . '<a href="https://platform.openai.com/docs/models" target="_blank">https://platform.openai.com/docs/models</a>.';
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_openai_max_tokens_multiplier_callback() {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('aikit_setting_openai_max_tokens_multiplier');
        ?>
        <input type="range" min="0" max="30" value="<?php echo isset( $setting ) && !empty($setting)? esc_attr( $setting ) : '0'; ?>" class="aikit-slider" id="aikit_setting_openai_max_tokens_multiplier" name="aikit_setting_openai_max_tokens_multiplier">
         <span id="aikit_setting_openai_max_tokens_multiplier_value"></span>
        <p>
            <small>
                <?php
                echo esc_html__('AIKit\'s builtin prompts are already preconfigured to generate a sensible number of words depending on the prompt.
                However, if you want to change the number of words generated, you can do so here. The slider is a multiplier of the number of tokens that AIKit
                would normally generate. For example, if a request would normally generate 100 words, you can set the multiplier to 2x and AIKit will generate 200 words instead.', 'aikit');
                ?>
             </small>
         </p>
        <p>
            <small>
                <?php
                echo esc_html__('Think of this as a global way to increase/decrease the length of generated text for all existing autocomplete options/prompts at once.', 'aikit');
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_autocompleted_text_background_color_callback() {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('aikit_setting_autocompleted_text_background_color');
        ?>
        <select name="aikit_setting_autocompleted_text_background_color" id="aikit_setting_autocompleted_text_background_color">
            <option value="">None</option>
            <option value="#D1E4DD" <?php echo $setting == '#D1E4DD' ? 'selected' : ''; ?>><?php echo esc_html__('Green', 'aikit'); ?></option>
            <option value="#D1DFE4" <?php echo $setting == '#D1DFE4' ? 'selected' : ''; ?>><?php echo esc_html__('Blue', 'aikit'); ?></option>
            <option value="#E4D1D1" <?php echo $setting == '#E4D1D1' ? 'selected' : ''; ?>><?php echo esc_html__('Red', 'aikit'); ?></option>
            <option value="#E4DAD1" <?php echo $setting == '#E4DAD1' ? 'selected' : ''; ?>><?php echo esc_html__('Orange', 'aikit'); ?></option>
            <option value="#D1D1E4" <?php echo $setting == '#D1D1E4' ? 'selected' : ''; ?>><?php echo esc_html__('Purple', 'aikit'); ?></option>
        </select>
        <p>
            <small>
                <?php
                echo esc_html__('If you prefer to have the autocompleted text stand out more, you can choose a background color for the autocompleted text.', 'aikit');
                ?>
            </small>
        </p>
        <?php
    }

    function aikit_settings_openai_system_message_callback () {
        $setting = get_option('aikit_setting_openai_system_message');
        ?>
        <textarea name="aikit_setting_openai_system_message" id="aikit_setting_openai_system_message" rows="5" cols="50"><?php echo isset( $setting ) && !empty($setting)? esc_attr( $setting ) : ''; ?></textarea>
        <p>
            <small>
                <?php
                echo esc_html__('System message help set the behaviour of the model. You can use it to ask the model to mimic a certain style or take a certain perspective for all text generations. For example, if you set this to "Shakespeare\' style", the mode will follow the style of Shakespeare in all text generations when possible. System message should work ONLY with GPT-4 and to a lesser extent with gpt-3.5-turbo models.', 'aikit');
                ?>
            </small>
        </p>
        <?php
    }
}

$AI_kit_admin = AIKit_Admin::instance();

add_action('admin_init', array ($AI_kit_admin, 'register_settings'));


add_filter( 'nonce_life', function () {
    return 60 * 60 * 24 * 7; // 1 week
} );

function aikit_enqueue_admin_scripts( $hook ) {
    if ( 'aikit_page_aikit' != $hook && 'plugins.php' != $hook && 'aikit_page_aikit_prompts' != $hook ) {
        return;
    }

	$version = aikit_get_plugin_version();
	if ($version === false) {
		$version = rand( 1, 10000000 );
	}

	if ('aikit_page_aikit_prompts' == $hook) {

        wp_enqueue_script( 'aikit_jquery_js', rtrim(plugin_dir_url( __FILE__ ), '/') . '/js/jquery-3.6.0.min.js', array(), $version );
        wp_enqueue_script( 'aikit_jquery_ui_js', rtrim(plugin_dir_url( __FILE__ ), '/') . '/js/jquery-ui.min.js', array(), $version );
        wp_enqueue_script( 'aikit_prompts', plugins_url( 'js/prompts.js', __FILE__ ), array( 'jquery' ), $version, true );
        wp_enqueue_script( 'aikit_icons', rtrim(plugin_dir_url( __FILE__ ), '/') . '/../fe/src/icons.js',  array(), $version );
        wp_enqueue_style( 'aikit_jquery_ui_css', rtrim(plugin_dir_url( __FILE__ ), '/') . '/css/jquery-ui.min.css', array(), $version );
        wp_enqueue_style( 'aikit_prompts_css', rtrim(plugin_dir_url( __FILE__ ), '/') . '/css/prompts.css', array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_css', rtrim(plugin_dir_url( __FILE__ ), '/') . '/css/bootstrap.min.css', array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_js', rtrim(plugin_dir_url( __FILE__ ), '/') . '/js/bootstrap.bundle.min.js', array(), $version );

        return;
    }

    wp_enqueue_script( 'aikit_admin_js', rtrim(plugin_dir_url( __FILE__ ), '/') . '/js/admin.js', array(), $version );
    wp_enqueue_style( 'aikit_admin_css', rtrim(plugin_dir_url( __FILE__ ), '/') . '/css/admin.css', array(), $version );
}

add_action( 'admin_enqueue_scripts', 'aikit_enqueue_admin_scripts' );
