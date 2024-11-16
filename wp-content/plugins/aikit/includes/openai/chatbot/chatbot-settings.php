<?php

class AIKIT_Chatbot_Settings extends AIKIT_Page {
    // singleton
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Chatbot_Settings();
        }

        return self::$instance;
    }

    private $chatbot = null;
    private $embeddings = null;

    public function __construct()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'aikit/chatbot/v1', '/settings', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'manage_options' );
                }
            ));

        });
        $this->chatbot = AIKIT_Chatbot::get_instance();
        $this->embeddings = AIKIT_Embeddings::get_instance();
    }

    public function handle_request($data)
    {
        update_option('aikit_setting_chatbot_model', $data['chatbot_model']);
        update_option('aikit_setting_chatbot_log_messages', boolval($data['chatbot_log_messages']));
        update_option('aikit_setting_chatbot_prompt_stop_sequence', $data['chatbot_prompt_stop_sequence']);
        update_option('aikit_setting_chatbot_completion_stop_sequence', $data['chatbot_completion_stop_sequence']);
        update_option('aikit_setting_chatbot_show_on', $data['chatbot_show_on']);
        update_option('aikit_setting_chatbot_enabled', boolval($data['chatbot_enabled']));
        update_option('aikit_setting_chatbot_voice_enabled', boolval($data['chatbot_voice_enabled']));
        update_option('aikit_setting_chatbot_default_view', $data['chatbot_default_view']);
        update_option('aikit_setting_chatbot_context', $data['chatbot_context']);
        update_option('aikit_setting_chatbot_is_page_content_aware', boolval($data['chatbot_is_page_content_aware']));
        update_option('aikit_setting_chatbot_max_response_tokens', intval($data['chatbot_max_response_tokens']));
        update_option('aikit_setting_chatbot_show_only_for_roles', $data['chatbot_show_only_for_roles']);
        update_option('aikit_setting_chatbot_appearance_title', $data['chatbot_appearance_title']);
        update_option('aikit_setting_chatbot_appearance_input_placeholder', $data['chatbot_appearance_input_placeholder']);
        update_option('aikit_setting_chatbot_appearance_start_message', $data['chatbot_appearance_start_message']);
        update_option('aikit_setting_chatbot_appearance_main_color', $data['chatbot_appearance_main_color']);
        update_option('aikit_setting_chatbot_appearance_secondary_color', $data['chatbot_appearance_secondary_color']);
        update_option('aikit_setting_chatbot_appearance_title_color', $data['chatbot_appearance_title_color']);
        update_option('aikit_setting_chatbot_appearance_ai_message_bubble_color', $data['chatbot_appearance_ai_message_bubble_color']);
        update_option('aikit_setting_chatbot_appearance_ai_message_text_color', $data['chatbot_appearance_ai_message_text_color']);
        update_option('aikit_setting_chatbot_appearance_user_message_bubble_color', $data['chatbot_appearance_user_message_bubble_color']);
        update_option('aikit_setting_chatbot_appearance_user_message_text_color', $data['chatbot_appearance_user_message_text_color']);
        update_option('aikit_setting_chatbot_use_embeddings', $data['chatbot_use_embeddings']);
        update_option('aikit_setting_chatbot_embeddings_answer_formulation_prompt', $data['chatbot_embeddings_answer_formulation_prompt']);

        if ($data['chatbot_use_embeddings'] == '0') {
            update_option('aikit_setting_chatbot_selected_embedding', '');
        } else {
            update_option('aikit_setting_chatbot_selected_embedding', $data['chatbot_selected_embedding']);
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Settings saved'
        ), 200 );
    }

    public function render()
    {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Chatbot', 'aikit' ); ?></h1>
            <p>
                <?php echo esc_html__( 'AIKit Chatbot allows you to create a chatbot that can be used on your website. You, or your users can use it to answer questions and provide support about your products or services.', 'aikit' ); ?>
            </p>

            <?php $this->show_settings()  ?>
        </div>
        <?php
    }

    private function show_settings()
    {
        $key = get_option('aikit_setting_openai_key');
        $deactivated_form_class = empty($key) ? 'aikit-deactivated-form' : '';

        if (empty($key)) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php echo esc_html__( 'Please set your OpenAI API key in the AIKit settings page in order to use Chatbot.', 'aikit' ); ?>
                </p>
            </div>
            <?php
        }

        $models = aikit_rest_openai_get_available_models();
        $models = $models === false ? [] : $models;

        if (empty($models)) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php echo esc_html__( 'Please make sure you are using a valid OpenAI API key to be able to load all available models.', 'aikit' ); ?>
                </p>
            </div>
            <?php

            $models = aikit_openai_get_default_model_list();
        }

        // remove duplicates
        $models = array_unique($models);

        $models = array_combine($models, $models);

        $available_wp_roles = get_editable_roles();
        $roles_array = [
                'all' => 'All',
        ];
        foreach ($available_wp_roles as $role => $role_data) {
            $roles_array[$role] = $role_data['name'];
        }

        $selected_model = get_option('aikit_setting_chatbot_model');

        $prompt_stop_sequence = get_option('aikit_setting_chatbot_prompt_stop_sequence');
        $completion_stop_sequence = get_option('aikit_setting_chatbot_completion_stop_sequence');

        $show_chatbot_on = get_option('aikit_setting_chatbot_show_on');
        $show_on_options = [
            'frontend' => __('Frontend Only', 'aikit'),
            'admin' => __('Admin Panel Only', 'aikit'),
            'all' => __('Admin Panel & Frontend', 'aikit'),
            'shortcode' => __('Only using shortcode', 'aikit'),
        ];

        $chatbot_enabled = get_option('aikit_setting_chatbot_enabled');
        $chatbot_voice_enabled = get_option('aikit_setting_chatbot_voice_enabled');
        $chatbot_default_view = get_option('aikit_setting_chatbot_default_view');
        $chatbot_context = get_option('aikit_setting_chatbot_context');
        $chatbot_is_page_content_aware = get_option('aikit_setting_chatbot_is_page_content_aware');
        $max_response_tokens = get_option('aikit_setting_chatbot_max_response_tokens');
        $show_only_for_roles = get_option('aikit_setting_chatbot_show_only_for_roles');
        $chatbot_log_messages = get_option('aikit_setting_chatbot_log_messages');

        $chatbot_appearance_title = get_option('aikit_setting_chatbot_appearance_title');
        $chatbot_appearance_input_placeholder = get_option('aikit_setting_chatbot_appearance_input_placeholder');
        $chatbot_appearance_start_message = get_option('aikit_setting_chatbot_appearance_start_message');
        $chatbot_appearance_main_color = get_option('aikit_setting_chatbot_appearance_main_color');
        $chatbot_appearance_secondary_color = get_option('aikit_setting_chatbot_appearance_secondary_color');
        $chatbot_appearance_title_color = get_option('aikit_setting_chatbot_appearance_title_color');
        $chatbot_appearance_ai_message_bubble_color = get_option('aikit_setting_chatbot_appearance_ai_message_bubble_color');
        $chatbot_appearance_ai_message_text_color = get_option('aikit_setting_chatbot_appearance_ai_message_text_color');
        $chatbot_appearance_user_message_bubble_color = get_option('aikit_setting_chatbot_appearance_user_message_bubble_color');
        $chatbot_appearance_user_message_text_color = get_option('aikit_setting_chatbot_appearance_user_message_text_color');

        $chatbot_use_embeddings = get_option('aikit_setting_chatbot_use_embeddings');
        $chatbot_embeddings_answer_formulation_prompt = get_option('aikit_setting_chatbot_embeddings_answer_formulation_prompt');
        $chatbot_chatbot_selected_embedding = get_option('aikit_setting_chatbot_selected_embedding');

        if (empty($chatbot_embeddings_answer_formulation_prompt)) {
            $chatbot_embeddings_answer_formulation_prompt = AIKIT_DEFAULT_SETTING_CHATBOT_EMBEDDINGS_ANSWER_FORMULATION_PROMPT;
        }

        $completed_embeddings = $this->embeddings->get_embeddings();

        $completed_embeddings_list = [
            '' => __('Select Embedding', 'aikit'),
        ];
        foreach ($completed_embeddings as $completed_embedding) {
            $completed_embeddings_list[$completed_embedding->id] = $completed_embedding->name;
        }

        if (empty($max_response_tokens)) {
            $max_response_tokens = 150;
        }

        if (empty($selected_model)) {
            $selected_model = 'gpt-3.5-turbo';
        }

        $active_tab = isset( $_GET['action'] )  ? $_GET['action'] : 'chat_settings';

        ?>
        <ul class="nav nav-tabs aikit-chatbot-settings-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'chat_settings' ? 'active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=aikit_chatbot&action=chat_settings' ); ?>"><?php echo esc_html__( 'Chatbot Settings', 'aikit' ); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'history' || $active_tab == 'view_conversation' ? 'active' : ''; ?>" aria-current="page" href="<?php echo admin_url( 'admin.php?page=aikit_chatbot&action=history' ); ?>"><?php echo esc_html__( 'Chatbot Conversation History', 'aikit' ); ?></a>
            </li>
        </ul>

        <div class="aikit-settings-content">

        <?php
            if ($active_tab == 'chat_settings') {
        ?>
        <form id="aikit-chatbot-form" class="<?php echo $deactivated_form_class ?>" action="<?php echo get_site_url(); ?>/?rest_route=/aikit/chatbot/v1/settings" method="post">
            <h4><?php echo esc_html__( 'General settings', 'aikit' ); ?></h4>
            <div class="row mt-4">
                <div class="col">
                    <?php
                        $this->_radio_button_set(
                            'aikit-chatbot-enabled',
                            __('Enable Chatbot', 'aikit'),
                            [
                                '1' => __('Yes', 'aikit'),
                                '0' => __('No', 'aikit'),
                            ],
                           $chatbot_enabled ? '1' : '0'
                        );
                    ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <?php
                    $this->_radio_button_set(
                        'aikit-chatbot-voice-enabled',
                        __('Enable Voice (Speech to text)', 'aikit'),
                        [
                            '1' => __('Yes', 'aikit'),
                            '0' => __('No', 'aikit'),
                        ],
                        $chatbot_voice_enabled ? '1' : '0'
                    );
                    ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <?php
                    $this->_radio_button_set(
                        'aikit-chatbot-default-view',
                        __('Default view', 'aikit'),
                        [
                            'collapsed' => __('Collapsed', 'aikit'),
                            'expanded' => __('Expanded', 'aikit'),
                        ],
                        $chatbot_default_view === 'expanded' ? 'expanded' : 'collapsed'
                    );
                    ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col">
                    <?php
                    $this->_radio_button_set(
                        'aikit-chatbot-log-messages',
                        __('Log messages', 'aikit'),
                        [
                            '1' => __('Yes', 'aikit'),
                            '0' => __('No', 'aikit'),
                        ],
                        $chatbot_log_messages ? '1' : '0',
                        'If enabled, all user conversations with Chatbot will be logged in the database. This can be useful to see whether your Chatbot reacts as expected to your user questions (and act accordingly). Depending on your traffic, this can generate a lot of data, so please be mindful of that.'
                    );
                    ?>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="border alert alert-light mb-2 p-2" role="alert">
                        <i class="bi bi-info-circle"></i>
                        <small>
                            <?php echo esc_html__( 'To include Chatbot in any specific page, you can use the shortcode: ', 'aikit' ); ?>
                            <code>[aikit_chatbot]</code>
                            <?php echo esc_html__( 'If you want to display the chatbot inside the page (inline) instead of on the side, pass the inline parameter to the shotcode like this:', 'aikit' ); ?>
                            <code>[aikit_chatbot inline='true']</code>
                        </small>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col">
                    <?php
                        $this->_drop_down(
                            'aikit-chatbot-model',
                            __('Chatbot model', 'aikit'),
                            $models,
                            $selected_model,
                            __('For best results, please use chat models like gpt-3.5-turbo or gpt-4.', 'aikit'),
                        );
                    ?>
                </div>
                <div class="col">
                    <?php
                        $this->_drop_down(
                            'aikit-chatbot-show-on',
                            __('Show Chatbot on', 'aikit'),
                            $show_on_options,
                            $show_chatbot_on,
                        );
                    ?>
                </div>

                <div class="col">
                    <?php
                    $this->_text_box(
                        'aikit-chatbot-max-response-tokens',
                        __('Max response tokens', 'aikit'),
                        null,
                        'number',
                        $max_response_tokens,
                        1,
                        1000,
                        1
                    );
                    ?>
                </div>

            </div>

            <div class="row mb-4">
                <div class="col">
                    <?php
                    $this->_text_box(
                        'aikit-chatbot-prompt-stop-sequence',
                        __('Prompt stop sequence (Optional)', 'aikit'),
                        null,
                        'text',
                        $prompt_stop_sequence,
                        null,
                        null,
                        null,
                        __('Please set this only if you are using a fine-tuned model. Leave empty if you are using any of the built-in models. Prompt stop sequence is used to mark the stop of the prompt.', 'aikit'),
                    );
                    ?>
                </div>
                <div class="col">
                    <?php
                    $this->_text_box(
                        'aikit-chatbot-completion-stop-sequence',
                        __('Completion stop sequence (Optional)', 'aikit'),
                        null,
                        'text',
                        $completion_stop_sequence,
                        null,
                        null,
                        null,
                        __('Please set this only if you are using a fine-tuned model. Leave empty if you are using any of the built-in models. Completion stop sequence is used to mark the stop of the completion.', 'aikit'),
                    );
                    ?>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <?php
                    $this->_radio_button_set(
                        'aikit-chatbot-use-embeddings',
                        __('Use embeddings', 'aikit'),
                        [
                            '1' => __('Yes', 'aikit'),
                            '0' => __('No', 'aikit'),
                        ],
                        $chatbot_use_embeddings ? '1' : '0',
                        'Embeddings allow you to store your own data in a way so that it can be used to answer Chatbot messages using similarity/semantic search (searching for meaning instead of exact words). Embeddings are currently the best and cheapest way to allow your Chatbot to answer your user questions about your products or services based on your own data.',
                        'https://youtu.be/VKtTOJ4MmJY',
                        __('Watch tutorial video', 'aikit')
                    );
                    ?>

                    <div class="aikit-embedding-options">
                        <div class="row mb-2">
                            <div class="col">
                                <?php
                                $this->_drop_down(
                                    'aikit-chatbot-selected-embedding',
                                    __('Embedding', 'aikit'),
                                    $completed_embeddings_list,
                                    $chatbot_chatbot_selected_embedding,
                                    __('Select the embedding you want to use to answer user questions. If you don\'t find your embedding in the list, please make sure the embedding creation process is already complete.', 'aikit'),
                                );
                                ?>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col">
                                <?php
                                $this->_text_area(
                                    'aikit-chatbot-embeddings-answer-formulation-prompt',
                                    __('Embedding Answer Formulation Prompt', 'aikit'),
                                    $chatbot_embeddings_answer_formulation_prompt,
                                    esc_html__('Embedding Answer formulation prompt will be used to formulate the answer to the user question along with the result of the embedding semantic search. What happens is the following. A user asks a question in the Chatbot, then using embeddings, a semantic search (search by meaning) occurs to retrieve the closest answer to the user question. Then, the answer formulation prompt is used to formulate the answer to the user question. For example, if the user asks "How much does your service cost?", the semantic search will retrieve the closest answer to this question, for example "Our prices start at $10 per month and you get a discount if you pay annually.". Then, the answer formulation prompt will be used to formulate the answer to the user question, for example "Service cost starts at $10 per month".', 'aikit') .
                                    '<br>' . esc_html__('Make sure to include the following placeholders in your prompt: ', 'aikit') . '<code>[[result]]</code>' . esc_html__(' and ', 'aikit') . '<code>[[question]]</code>' . esc_html__('. These placeholders will be replaced with the result (from vector database) and the question (asked by user) respectively.', 'aikit'),
                                    false
                                );
                                ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="row mb-4">
                <div class="col">
                    <?php
                        $this->_text_area(
                                'aikit-chatbot-context',
                            __('Chatbot Context', 'aikit'),
                            $chatbot_context,
                            __('You can use this field to set the behaviour of the chatbot. For example, use something like "You are a helpful assistant." or "Answer in the style of Shakespeare." .', 'aikit'),
                        );
                    ?>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col">
                    <?php
                    $this->_drop_down(
                        'aikit-chatbot-show-only-for-roles',
                        __('Show Chatbot only for user role', 'aikit'),
                        $roles_array,
                        $show_only_for_roles,
                    );
                    ?>
                </div>
            </div>

            <div class="row mb-4 aikit-page-content-aware-container">
                <div class="col">
                    <?php
                    $this->_check_box(
                        'aikit-chatbot-is-page-content-aware',
                        __('Page content aware?', 'aikit'),
                        $chatbot_is_page_content_aware,
                        __('If enabled, the chatbot will be able to use the content of the current page to generate better responses. Important: it will increase your API costs!', 'aikit'),
                    );
                    ?>
                </div>
            </div>

            <input type="hidden" id="aikit-message-select-embedding" value="<?php echo esc_attr( __('Please select an embedding from embedding list.', 'aikit') ); ?>">

            <hr class="mb-4"/>

            <h4><?php echo esc_html__( 'Appearance', 'aikit' ); ?></h4>

            <div class="row mb-2">
                <div class="col">
                    <div class="row mb-2">
                        <div class="col">
                            <?php
                            $this->_text_box(
                                'aikit-chatbot-appearance-title',
                                __('Title', 'aikit'),
                                'title',
                                'text',
                                $chatbot_appearance_title
                            );
                            ?>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <?php
                            $this->_text_box(
                                'aikit-chatbot-appearance-input-placeholder',
                                __('Input text placeholder', 'aikit'),
                                'inputPlaceholder',
                                'text',
                                $chatbot_appearance_input_placeholder
                            );
                            ?>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <?php
                            $this->_text_box(
                                'aikit-chatbot-appearance-start-message',
                                __('Start Message', 'aikit'),
                                'startMessage',
                                'text',
                                $chatbot_appearance_start_message

                            );
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3 mt-3">
                        <div class="col">
                            <?php
                            $this->_color_picker(
                                'aikit-chatbot-appearance-main-color',
                                __('Main Color', 'aikit'),
                                'mainColor',
                                $chatbot_appearance_main_color,
                                AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_MAIN_COLOR

                            );
                            ?>
                        </div>
                        <div class="col">
                            <?php
                            $this->_color_picker(
                                'aikit-chatbot-appearance-secondary-color',
                                __('Secondary Color', 'aikit'),
                                'secondaryColor',
                                $chatbot_appearance_secondary_color,
                                AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_SECONDARY_COLOR

                            );
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <?php
                            $this->_color_picker(
                                'aikit-chatbot-appearance-title-color',
                                __('Title Color', 'aikit'),
                                'titleColor',
                                $chatbot_appearance_title_color,
                                AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_TITLE_COLOR
                            );
                            ?>
                        </div>
                        <div class="col">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <?php
                            $this->_color_picker(
                                'aikit-chatbot-appearance-ai-message-bubble-color',
                                __('AI Message Bubble Color', 'aikit'),
                                'aiMessageBubbleColor',
                                $chatbot_appearance_ai_message_bubble_color,
                                AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_AI_MESSAGE_BUBBLE_COLOR

                            );
                            ?>
                        </div>
                        <div class="col">
                            <?php
                            $this->_color_picker(
                                'aikit-chatbot-appearance-ai-message-text-color',
                                __('AI Message Text Color', 'aikit'),
                                'aiMessageTextColor',
                                $chatbot_appearance_ai_message_text_color,
                                AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_AI_MESSAGE_TEXT_COLOR
                            );
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <?php
                            $this->_color_picker(
                                'aikit-chatbot-appearance-user-message-bubble-color',
                                __('User Message Bubble Color', 'aikit'),
                                'userMessageBubbleColor',
                                $chatbot_appearance_user_message_bubble_color,
                                AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_USER_MESSAGE_BUBBLE_COLOR

                            );
                            ?>
                        </div>
                        <div class="col">
                            <?php
                            $this->_color_picker(
                                'aikit-chatbot-appearance-user-message-text-color',
                                __('User Message Text Color', 'aikit'),
                                'userMessageTextColor',
                                $chatbot_appearance_user_message_text_color,
                                AIKIT_DEFAULT_SETTING_CHATBOT_APPEARANCE_USER_MESSAGE_TEXT_COLOR
                            );
                            ?>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col">
                            <a class="aikit-reset-colors float-end" href="#"><i class="bi bi-palette"></i> <?php echo esc_html__( 'Reset Colors', 'aikit' ); ?></a>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <?php
                        $this->chatbot->render(true);
                    ?>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <button class="btn btn-sm btn-primary aikit-chatbot-save-settings" type="submit"><i class="bi bi-check-lg"></i> <?php echo esc_html__( 'Save Settings', 'aikit' ); ?></button>
                    </div>
                </div>

            </div>
        </form>

        <?php
            } elseif ($active_tab == 'history') {
                $this->render_history_tab();
            } elseif ($active_tab == 'view_conversation') {
                $this->render_view_conversation_tab();
            }
        ?>
        </div>
        <?php

    }

    public function render_view_conversation_tab()
    {
        ?>
        <div class="tab-content">
            <div class="row">
                <div class="col mb-2">
                    <a href="<?php echo admin_url( 'admin.php?page=aikit_chatbot&action=history' ); ?>" class="aikit-history-back"><?php echo esc_html__( '« Back to History', 'aikit' ); ?></a>
                </div>
            </div>
            <div class="tab-pane fade show active" id="aikit-chatbot-conversation" role="tabpanel" aria-labelledby="aikit-chatbot-conversation-tab">
                <?php
                $conv_id = isset($_GET['conv_id']) ? intval($_GET['conv_id']) : null;
                if (empty($conv_id)) {
                    echo '<div class="alert alert-danger">' . esc_html__('Please select a conversation.', 'aikit') . '</div>';
                } else {
                    global $wpdb;
                    $table_name = $wpdb->prefix . AIKIT_Chatbot::TABLE_NAME_CHAT_MESSAGES;
                    $conversation_messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_name} WHERE conversation_id = %d ORDER BY id ASC", $conv_id));

                    if (empty($conversation_messages)) {
                        echo '<div class="alert alert-danger">' . esc_html__('No messages found for this conversation.', 'aikit') . '</div>';
                    } else {
                        $html = '<div class="aikit-chatbot-conversation-container">';
                        foreach ($conversation_messages as $message) {
                            $message_sender = $message->message_sender;
                            $message_text = $message->message;

                            $message_class = 'aikit-chatbot-conversation-message';
                            if ($message_sender == 'user') {
                                $message_class .= ' aikit-chatbot-conversation-message-user';
                                $message_text = '<b>' . __('User', 'aikit') . '</b>: ' . esc_html($message_text);
                            } else {
                                $message_class .= ' aikit-chatbot-conversation-message-ai';
                                $message_text = '<b>' . __('AI', 'aikit') . '</b>: ' . esc_html($message_text);
                            }

                            $html .= '<div class="' . $message_class . '">';
                            $html .= '<div class="aikit-chatbot-conversation-message-text">' . $message_text . '</div>';
                            $html .= '</div>';

                        }
                        $html .= '</div>';

                        echo $html;
                    }
                }
                ?>
            </div>
        </div>
        <?php

    }

    public function render_history_tab()
    {
        $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 25;
        $columns = [
            esc_html__('User IP Address', 'aikit'),
            esc_html__('User', 'aikit'),
            esc_html__('Date', 'aikit'),
        ];
        $html = '<table class="table" id="aikit-chatbot-conversations">
            <thead>
            <tr>';

        foreach ($columns as $column) {
            $html .= '<th scope="col">' . $column . '</th>';
        }

        $html .= '
            </tr>
            </thead>
            <tbody>';

        // get all jobs from DB
        global $wpdb;
        $table_name = $wpdb->prefix . AIKIT_Chatbot::TABLE_NAME_CHAT_CONVERSATIONS;

        // prepared statement to prevent SQL injection with pagination
        $conversations = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d, %d", ($paged - 1) * $per_page, $per_page));

        if (empty($conversations)) {
            $html .= '<tr>
                <td colspan="' . count($columns) . '">' . esc_html__('No entries found', 'aikit') . '</td>
            </tr>';
        }

        $page_url = get_admin_url() . 'admin.php?page=aikit_chatbot&action=view_conversation';
        $history_url = get_admin_url() . 'admin.php?page=aikit_chatbot&action=history';

        foreach ($conversations as $conversation) {
            $wp_user = get_user_by('id', $conversation->user_id);
            $username = $wp_user === false ? '-' : $wp_user->user_login;
            $current_page_url = $page_url . '&conv_id=' . $conversation->id;
            $html .= '<tr>
                <td>' . '<a href="' . $current_page_url . '">' . esc_html($conversation->user_ip) . '</a></td>
                <td>' . (!empty($conversation->user_id) ? '<a href="' . get_edit_user_link($conversation->user_id) . '">' . $username . '</a>' : '-') . '</td>
                <td>' . (empty($conversation->date_created) ? '-' : aikit_date($conversation->date_created)) . '</td>       
            </tr>';
        }

        // pagination
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_pages = ceil($total / $per_page);

        if ($total_pages > 1) {
            $html .= '<tr>
            <td colspan="' . count($columns) . '">';

            // previous page

            $html .= '<div class="aikit-pagination">';
            if ($paged > 1) {
                $html .= '<a href="' . $history_url . '&paged=' . ($paged - 1) . '">' . __('« Previous', 'aikit') . '</a>';
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                // add class to current page
                $current_page_class = '';
                if ($paged == $i) {
                    $current_page_class = 'aikik-pagination-current';
                }

                $html .= '<a class="' . $current_page_class . '" href="' . $history_url . '&paged=' . $i . '" data-page="' . $i . '">' . $i . '</a>';
            }

            // next page
            if ($paged < $total_pages) {
                $html .= '<a href="' . $history_url . '&paged=' . ($paged + 1) . '">' . __('Next »', 'aikit') . '</a>';
            }

            $html .= '</div>';

            $html .= '</td>
            </tr>';
        }

        $html .= '</tbody>
        
        </table>';

        echo $html;
    }

    public function enqueue_scripts($hook)
    {
        if ( 'aikit_page_aikit_chatbot' !== $hook ) {
            return;
        }

        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_style( 'aikit_bootstrap_css', plugins_url( '../../css/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( '../../css/bootstrap-icons.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_repurposer_css', plugins_url( '../../css/chatbot-settings.css', __FILE__ ), array(), $version );

        wp_enqueue_script( 'aikit_bootstrap_js', plugins_url('../../js/bootstrap.bundle.min.js', __FILE__ ), array(), $version );
        wp_enqueue_script( 'aikit_jquery_ui_js', plugins_url('../../js/jquery-ui.min.js', __FILE__ ), array('jquery'), $version );
        wp_enqueue_script( 'aikit_repurposer_js', plugins_url( '../../js/chatbot-settings.js', __FILE__ ), array( 'jquery' ), array(), $version );
    }
}
