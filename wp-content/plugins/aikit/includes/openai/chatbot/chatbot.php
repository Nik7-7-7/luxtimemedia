<?php

class AIKIT_Chatbot {

    const TABLE_NAME_CHAT_CONVERSATIONS = 'aikit_chat_conversations';
    const TABLE_NAME_CHAT_MESSAGES = 'aikit_chat_messages';

    private static $instance = null;

    private $tokenizer;
    private $embeddings = null;
    private $embeddings_connector = null;

    private $already_rendered = false;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Chatbot();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'aikit/chatbot/v1', '/chat', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_request'),
                'permission_callback' => function () {
                    return $this->should_show_chatbot();
                }
            ));

            register_rest_route( 'aikit/chatbot/v1', '/speech-to-text', array(
                'methods' => 'POST',
                'callback' => array($this, 'speech_to_text'),
                'permission_callback' => function () {
                    return $this->should_show_chatbot();
                }
            ));
        });

        add_action('admin_head', array($this, 'add_inline_script'));
        add_action('wp_head', array($this, 'add_inline_script'));

        $this->tokenizer = AIKIT_Gpt3Tokenizer::getInstance();
        $this->embeddings = AIKIT_Embeddings::get_instance();
        $this->embeddings_connector = AIKIT_Embeddings_Connector::get_instance();

        add_shortcode('aikit_chatbot', array($this, 'chatbot_shortcode'));

    }

    public function speech_to_text($data)
    {
        $chatbot_voice_enabled = get_option('aikit_setting_chatbot_voice_enabled');

        if (!$chatbot_voice_enabled) {
            return new WP_REST_Response([
                'message' => 'Voice is not enabled',
            ], 400);
        }

        if (!isset($_FILES['audio'])) {
            return new WP_REST_Response([
                'message' => 'Missing audio file',
            ], 400);
        }

        // Get the uploaded file
        $audio = $_FILES['audio'];

        $temp_file = $audio['tmp_name'];

        try {
            $text = aikit_openai_speech_to_text($temp_file);
        } catch (Throwable $e) {
            // only if admin

            $is_admin = current_user_can('manage_options');
            $message = $is_admin ? 'Only admins can see this error msg: ' . $e->getMessage() : 'An error occurred while generating the response.';

            return new WP_REST_Response([
                'message' => $message,
            ], 500);
        }

        return new WP_REST_Response([
            'message' => $text,
        ], 200);
    }

    public function chatbot_shortcode($args)
    {
        return $this->render(false, true, ($args['inline'] ?? false) == 'true');
    }

    public function do_db_migration()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . self::TABLE_NAME_CHAT_CONVERSATIONS;
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            chat_id varchar(255) NOT NULL,
            date_created datetime DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            user_ip varchar(255) DEFAULT NULL,
            UNIQUE INDEX aikit_chat_id_index (chat_id),
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $table_name = $wpdb->prefix . self::TABLE_NAME_CHAT_MESSAGES;
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            conversation_id bigint(20) NOT NULL,
            message TEXT NOT NULL,
            message_sender varchar(255) NOT NULL,
            date_created datetime DEFAULT NULL,
            INDEX aikit_conversation_id_index (conversation_id),
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function handle_request($data)
    {
        $previous_messages = json_decode($data['previous_messages'], true);
        $page_content = $data['page_content'];
        $message = $data['message'];
        $chat_id = get_option('aikit_setting_chatbot_log_messages') == '1' ? $data['chat_id'] : false;

        $chatbot_use_embeddings = get_option('aikit_setting_chatbot_use_embeddings');
        if ($chatbot_use_embeddings == '1') {
            return $this->handle_chatbot_with_embeddings_request($message, $previous_messages, $chat_id);
        }

        return $this->handle_chatbot_without_embeddings_request($message, $previous_messages, $page_content, $chat_id);
    }

    public function handle_chatbot_with_embeddings_request($message, $previous_messages = [], $chat_id = false)
    {
        try {
            $selected_embedding_id = get_option('aikit_setting_chatbot_selected_embedding');
            $chatbot_max_tokens = intval(get_option('aikit_setting_chatbot_max_response_tokens'));
            $chatbot_context = get_option('aikit_setting_chatbot_context');
            $chatbot_model = get_option('aikit_setting_chatbot_model');

            $results = $this->embeddings_connector->query_embeddings($selected_embedding_id, $message);

            if (count($results) > 0) {
                $result = $results[0];
            } else {
                $result = '';
            }

            $answer_formulating_prompt = get_option('aikit_setting_chatbot_embeddings_answer_formulation_prompt');
            $answer_formulating_prompt = str_replace('[[question]]', $message, $answer_formulating_prompt);
            $answer_formulating_prompt = str_replace('[[result]]', $result, $answer_formulating_prompt);

            $previous_messages_token_count = $this->count_tokens_in_array($previous_messages);
            $chatbot_context_token_count = $this->count_tokens_in_string($chatbot_context);
            $message_token_count = $this->count_tokens_in_string($answer_formulating_prompt);

            $model_max_tokens = aikit_openai_get_max_tokens_for_model($chatbot_model);

            $total_token_count = $previous_messages_token_count + $chatbot_context_token_count + $message_token_count + $chatbot_max_tokens;

            if ($total_token_count > $model_max_tokens) {
                // remove words from page_content until we are under the limit
                while ($total_token_count > $model_max_tokens && !empty($page_content)) {
                    $page_content = $this->remove_last_word($page_content);
                    $page_content_token_count = $this->count_tokens_in_string($page_content);
                    $total_token_count = $previous_messages_token_count + $chatbot_context_token_count + $message_token_count + $chatbot_max_tokens;
                }

                if ($total_token_count > $model_max_tokens) {
                    // remove words from chatbot_context until we are under the limit
                    while ($total_token_count > $model_max_tokens && !empty($chatbot_context)) {
                        $chatbot_context = $this->remove_last_word($chatbot_context);
                        $chatbot_context_token_count = $this->count_tokens_in_string($chatbot_context);
                        $total_token_count = $previous_messages_token_count + $chatbot_context_token_count + $message_token_count + $chatbot_max_tokens;
                    }
                }
            }

            $result_message = aikit_ai_chat_generation_request(
                $answer_formulating_prompt,
                $chatbot_max_tokens,
                $chatbot_context,
                $previous_messages,
            );

            if ($chat_id !== false) {
                $this->log_conversation_messages($message, $result_message, $chat_id);
            }

            return new WP_REST_Response([
                'message' => $result_message,
            ], 200);

        } catch (Throwable $e) {
            $is_admin = current_user_can('manage_options');
            $message = $is_admin ? $e->getMessage() : 'An error occurred while generating the response.';
            return new WP_REST_Response([
                'message' => $message,
                'show_in_alert' => $is_admin,
                'trace' => $is_admin ? $e->getTraceAsString() : '',
                'chat_error' => __('Error 190: An error occurred while generating the response, please try again later.', 'aikit'),
            ], 500);
        }
    }

    public function handle_chatbot_without_embeddings_request($message, $previous_messages = [], $page_content = '', $chat_id = false)
    {
        $chatbot_model = get_option('aikit_setting_chatbot_model');
        $chatbot_context = get_option('aikit_setting_chatbot_context');
        $chatbot_is_page_content_aware = boolval(get_option('aikit_setting_chatbot_is_page_content_aware'));
        $chatbot_max_tokens = intval(get_option('aikit_setting_chatbot_max_response_tokens'));

        $previous_messages_token_count = $this->count_tokens_in_array($previous_messages);
        $chatbot_context_token_count = $this->count_tokens_in_string($chatbot_context);
        $page_content_token_count = $this->count_tokens_in_string($page_content);
        $message_token_count = $this->count_tokens_in_string($message);

        $model_max_tokens = aikit_openai_get_max_tokens_for_model($chatbot_model);

        $total_token_count = $previous_messages_token_count + $chatbot_context_token_count + $page_content_token_count + $message_token_count + $chatbot_max_tokens;

        if ($total_token_count > $model_max_tokens) {
            // remove messages from previous_messages until we are under the limit
            while ($total_token_count > $model_max_tokens && !empty($previous_messages)) {
                array_shift($previous_messages);
                $previous_messages_token_count = $this->count_tokens_in_array($previous_messages);
                $total_token_count = $previous_messages_token_count + $chatbot_context_token_count + $page_content_token_count + $message_token_count + $chatbot_max_tokens;
            }

            if ($total_token_count > $model_max_tokens) {
                // remove words from page_content until we are under the limit
                while ($total_token_count > $model_max_tokens && !empty($page_content)) {
                    $page_content = $this->remove_last_word($page_content);
                    $page_content_token_count = $this->count_tokens_in_string($page_content);
                    $total_token_count = $previous_messages_token_count + $chatbot_context_token_count + $page_content_token_count + $message_token_count + $chatbot_max_tokens;
                }
            }

            if ($total_token_count > $model_max_tokens) {
                // remove words from chatbot_context until we are under the limit
                while ($total_token_count > $model_max_tokens && !empty($chatbot_context)) {
                    $chatbot_context = $this->remove_last_word($chatbot_context);
                    $chatbot_context_token_count = $this->count_tokens_in_string($chatbot_context);
                    $total_token_count = $previous_messages_token_count + $chatbot_context_token_count + $page_content_token_count + $message_token_count + $chatbot_max_tokens;
                }
            }
        }

        if ($chatbot_is_page_content_aware && (!empty($page_content) || !empty($chatbot_context))) {
            $chatbot_context = $chatbot_context . "\n\n" . $page_content;
        }

        try {
            $result_message = aikit_ai_chat_generation_request(
                $message,
                $chatbot_max_tokens,
                $chatbot_context,
                $previous_messages,
            );
        } catch (Throwable $e) {
            $is_admin = current_user_can('manage_options');
            $message = $is_admin ? $e->getMessage() : 'An error occurred while generating the response.';
            return new WP_REST_Response([
                'message' => $message,
                'show_in_alert' => $is_admin,
                'chat_error' => __('Error 200: An error occurred while generating the response, please try again.', 'aikit'),
            ], 500);
        }

        if ($chat_id !== false) {
            $this->log_conversation_messages($message, $result_message, $chat_id);
        }

        return new WP_REST_Response([
            'message' => $result_message,
        ], 200);
    }

    private function remove_last_word($str)
    {
        $str = trim($str);
        $words = explode(' ', $str);

        if (count($words) <= 1) {
            return '';
        }

        array_pop($words);
        return implode(' ', $words);
    }

    private function count_tokens_in_string($str)
    {
        return $this->tokenizer->count($str);
    }

    private function count_tokens_in_array($arr) {
        $count = 0;

        foreach ($arr as $item) {
            $count += $this->count_tokens_in_string($item['message'] ?? '');
        }

        return $count;
    }

    public function add_inline_script()
    {
        $nonce = wp_create_nonce('wp_rest');
        $vars = array(
            'nonce'  =>  $nonce,
            'siteUrl' => get_site_url(),
            'pageContent' => $this->get_current_page_content(),
            'mainColor' => get_option('aikit_setting_chatbot_appearance_main_color'),
            'secondaryColor' => get_option('aikit_setting_chatbot_appearance_secondary_color'),
            'aiMessageBubbleColor' => get_option('aikit_setting_chatbot_appearance_ai_message_bubble_color'),
            'aiMessageTextColor' => get_option('aikit_setting_chatbot_appearance_ai_message_text_color'),
            'userMessageBubbleColor' => get_option('aikit_setting_chatbot_appearance_user_message_bubble_color'),
            'userMessageTextColor' => get_option('aikit_setting_chatbot_appearance_user_message_text_color'),
            'titleColor' => get_option('aikit_setting_chatbot_appearance_title_color'),
            'title' => get_option('aikit_setting_chatbot_appearance_title'),
            'startMessage' => get_option('aikit_setting_chatbot_appearance_start_message'),
        );

        wp_add_inline_script( 'aikit-chatbot', 'var aikitChatbot =' . json_encode($vars) );

        wp_enqueue_script( 'aikit-chatbot');
    }

    private function get_current_page_content()
    {
        if (!get_option('aikit_setting_chatbot_is_page_content_aware')) {
            return '';
        }

        if (is_admin()) {
            return '';
        }

        $page_id = get_the_ID();
        $content = get_post_field('post_content', $page_id);

        // strip shortcodes
        $content = strip_shortcodes($content);

        // strip tags
        return strip_tags($content);
    }

    public function init()
    {
        $show_on = get_option('aikit_setting_chatbot_show_on');

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        if ($show_on == 'all') {
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        } else if ($show_on == 'frontend') {
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        } else if ($show_on == 'admin') {
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        }

        // if current page is aikit_chatbot, return
        if ($this->is_chatbot_settings_page()) {
            return;
        }

        if ($show_on == 'all') {
            add_action( 'admin_footer', array( $this, 'render' ) );
            add_action( 'wp_footer', array( $this, 'render' ) );
        } else if ($show_on == 'frontend') {
            add_action( 'wp_footer', array( $this, 'render' ) );
        } else if ($show_on == 'admin') {
            add_action( 'admin_footer', array( $this, 'render' ) );
        }
    }

    private function is_chatbot_settings_page()
    {
        return isset($_GET['page']) && $_GET['page'] == 'aikit_chatbot';
    }

    private function should_show_chatbot($live_preview = false)
    {
        if ($live_preview) {
            return true;
        }

        if (!get_option('aikit_setting_chatbot_enabled')) {
            return false;
        }

        $should_show_for_roles = get_option('aikit_setting_chatbot_show_only_for_roles');

        if ($should_show_for_roles != 'all') {
            $current_user_roles = wp_get_current_user()->roles;
            $should_show = false;
            foreach ($current_user_roles as $role) {
                if ($role == $should_show_for_roles) {
                    $should_show = true;
                    break;
                }
            }

            if (!$should_show) {
                return false;
            }
        }

        return true;
    }

    public function render($live_preview = false, $return = false, $inline = false)
    {
        if (!$this->should_show_chatbot($live_preview)) {
            return '';
        }

        if ($this->already_rendered) {
            return '';
        }

        $this->already_rendered = true;

        $chatbot_appearance_title = get_option('aikit_setting_chatbot_appearance_title');
        $chatbot_appearance_start_message = get_option('aikit_setting_chatbot_appearance_start_message');

        $chatbot_voice_enabled = get_option('aikit_setting_chatbot_voice_enabled');

        $default_view = get_option('aikit_setting_chatbot_default_view');

        $default_view = $inline ? 'expanded' : $default_view;

        $input_placeholder = get_option('aikit_setting_chatbot_appearance_input_placeholder');
        $chat_id = $this->generate_chat_id();

        $classes = $inline ? 'inline' : '';

        $minimize_button = $inline ? '' : '<button class="aikit-exit-chat" type="button" aria-label="Exit Chat">
            <svg class="aikit-down-arrow" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
                <path d="M0 0h24v24H0z" fill="none"></path>
                <path d="M11.67 3.87L9.9 2.1 0 12l9.9 9.9 1.77-1.77L3.54 12z"></path>
            </svg>
            <span>Minimize</span>
        </button>';

        $html = '
        <div class="aikit-chat-widget d-none ' . $classes .' " data-default-view="' . esc_attr($default_view) . '" data-chat-id="' . esc_attr($chat_id) . '"
            data-validation-no-mic="' . esc_attr__('Could not get permission to use your mic.', 'aikit') . '"
            >
            <div class="aikit-chat-container">
                <div class="aikit-chat">
                    <div class="aikit-chat-header">
                        <h2 class="aikit-chat-welcome">
                            <span>
                                ' . esc_html($chatbot_appearance_title) . ' 
                            </span>
                        </h2>
                        ' . $minimize_button . '
                    </div>
                    <div id="aikit-conversation">
                        <div id="aikit-messages">
                            <div class="aikit-message aikit-message-bot">
                                <span class="aikit-message-content">
                                    ' . esc_html($chatbot_appearance_start_message) . '
                                </span>
                            </div>';

        if ($live_preview) {
                $html .= '<div class="aikit-message aikit-message-user" >
                    <span class="aikit-message-content">
                        ' . esc_html__('Can you please help me with...', 'aikit') . '
                    </span>
                </div>
                <div class="aikit-message aikit-message-bot">
                    <span class="aikit-message-content">
                        <span class="aikit-typing">
                            <span class="aikit-dot"></span>
                            <span class="aikit-dot"></span>
                            <span class="aikit-dot"></span>
                        </span>
                    </span>
                </div>';
        }

        $html .= '
                        </div>
                    </div>
                    <div class="aikit-input-group">
                        <div class="aikit-footer-input-wrapper">
                            <hr>
                            <div class="aikit-input-row">';

        if ($chatbot_voice_enabled) {
            $html .= '<button class="aikit-voice-button" id="aikit-voice-button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="aikit-mic-icon bi bi-mic-fill" viewBox="0 0 16 16">
                                          <path d="M5 3a3 3 0 0 1 6 0v5a3 3 0 0 1-6 0z"/>
                                          <path d="M3.5 6.5A.5.5 0 0 1 4 7v1a4 4 0 0 0 8 0V7a.5.5 0 0 1 1 0v1a5 5 0 0 1-4.5 4.975V15h3a.5.5 0 0 1 0 1h-7a.5.5 0 0 1 0-1h3v-2.025A5 5 0 0 1 3 8V7a.5.5 0 0 1 .5-.5"/>
                                    </svg>
                                    <div class="aikit-pulse-ring"></div>
                                    <div class="aikit-loading" data-visible="0"></div>
                                </button>';
            }

        $html .=  '<textarea id="aikit-new-message-textarea" rows="1" placeholder="' . esc_attr($input_placeholder) . '" tabindex="0"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="aikit-button">
                <button type="button" class="aikit-button-body aikit-chatbot-send-button" tabindex="0">
                    <i>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
                            <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
                        </svg>
                    </i>
                </button>
            </div>

            <div class="aikit-button aikit-open-chat-button">
                <button type="button" class="aikit-button-body" tabindex="0">
                    <i>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-fill" viewBox="0 0 16 16">
                            <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z"/>
                        </svg>
                    </i>
                </button>
            </div>
        </div>';

        if ($return) {
            return $html;
        }

        echo $html;
    }

    public function enqueue_scripts($hook)
    {
        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        if (get_option('aikit_setting_chatbot_enabled') || is_admin()) {
            wp_enqueue_script( 'aikit-record-rtc', plugins_url( '../../js/record-rtc.js', __FILE__ ), array(), $version, true );
            wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( '../../css/bootstrap-icons.css', __FILE__ ), array(), $version );

            wp_enqueue_style('aikit-chatbot', plugins_url('../../css/chatbot.css', __FILE__), array(), $version);
            wp_enqueue_script('aikit-chatbot', plugins_url('../../js/chatbot.js', __FILE__), array('jquery'), $version, true);
        }
    }

    private function log_conversation_messages($user_message, $bot_message, $chat_id)
    {
        // if chat_id is not set, return
        if (empty($chat_id)) {
            return;
        }

        $user_id = get_current_user_id();
        $user_ip = $_SERVER['REMOTE_ADDR'];

        global $wpdb;

        // find conversation by chat_id
        $conversation_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}" . self::TABLE_NAME_CHAT_CONVERSATIONS . " WHERE chat_id = %s",
            $chat_id
        ));

        $should_add_start_message = false;
        if ($conversation_id === null) {
            $should_add_start_message = true;
        }

        // add conversation if it doesn't exist (sql injection safe)
        $wpdb->query($wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}" . self::TABLE_NAME_CHAT_CONVERSATIONS . " (chat_id, date_created, user_id, user_ip) VALUES (%s, %s, %d, %s)",
            $chat_id,
            current_time('mysql', true),
            $user_id,
            $user_ip
        ));

        // get conversation id
        $conversation_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}" . self::TABLE_NAME_CHAT_CONVERSATIONS . " WHERE chat_id = %s",
            $chat_id
        ));

        if ($should_add_start_message) {
            // make sure that the chatbot start message is added to the conversation only when user starts to use the chatbot

            $chatbot_appearance_start_message = get_option('aikit_setting_chatbot_appearance_start_message');
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}" . self::TABLE_NAME_CHAT_MESSAGES . " (conversation_id, message, message_sender, date_created) VALUES (%d, %s, %s, %s)",
                $conversation_id,
                $chatbot_appearance_start_message,
                'chatbot',
                current_time('mysql', true)
            ));
        }


        if (!empty($user_message)) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}" . self::TABLE_NAME_CHAT_MESSAGES . " (conversation_id, message, message_sender, date_created) VALUES (%d, %s, %s, %s)",
                $conversation_id,
                $user_message,
                'user',
                current_time('mysql', true)
            ));
        }

        if (!empty($bot_message)) {
            $wpdb->query($wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}" . self::TABLE_NAME_CHAT_MESSAGES . " (conversation_id, message, message_sender, date_created) VALUES (%d, %s, %s, %s)",
                $conversation_id,
                $bot_message,
                'chatbot',
                current_time('mysql', true)
            ));
        }
    }

    private function generate_chat_id()
    {
        return bin2hex(random_bytes(32));
    }
}