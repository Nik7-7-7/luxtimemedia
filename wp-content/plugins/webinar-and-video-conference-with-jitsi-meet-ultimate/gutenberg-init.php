<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;

// Make sure the same class is not loaded twice
if (!class_exists('Jitsi_Meet_WP_Gutenberg')) {
    /**
     * Main Jitsi Pro Class
     *
     * The main class that initiates and runs the Jitsi PRO plugin.
     *
     * @since 1.0.0
     */
    final class Jitsi_Meet_WP_Gutenberg
    {
        /**
         * Instance
         *
         * Holds a single instance of the `Jitsi_Meet_WP` class.
         *
         * @since 1.0.0
         *
         * @access private
         * @static
         *
         * @var Jitsi_Meet_WP A single instance of the class.
         */
        private static $_instance = null;

        /**
         * Instance
         *
         * Ensures only one instance of the class is loaded or can be loaded.
         *
         * @return Jitsi_Meet_WP An instance of the class.
         * @since 1.0.0
         *
         * @access public
         * @static
         *
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Clone
         *
         * Disable class cloning.
         *
         * @return void
         * @since 1.0.0
         *
         * @access protected
         *
         */
        public function __clone()
        {
            // Cloning instances of the class is forbidden
            _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'jitsi-pro'), '1.0.0');
        }

        /**
         * Wakeup
         *
         * Disable unserializing the class.
         *
         * @return void
         * @since 1.7.0
         *
         * @access protected
         *
         */
        public function __wakeup()
        {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, esc_html__('Cheatin&#8217; huh?', 'jitsi-pro'), '1.0.0');
        }

        /**
         * Constructor
         *
         * Initialize the Jitsi PRO plugins.
         *
         * @since 1.0.0
         *
         * @access public
         */
        public function __construct()
        {
            add_action('enqueue_block_editor_assets', [$this, 'jitsi_pro_gutenberg_blocks']);
            add_action('admin_enqueue_scripts', [$this, 'jitsi_pro_gutenberg_editor_assets']);
            add_action('wp_enqueue_scripts', [$this, 'jitsi_pro_gutenberg_front_assets']);
        }

        /**
         * Enqueue block script
         *
         * @since 1.0.0
         *
         * @access public
         */
        public function jitsi_pro_gutenberg_blocks()
        {
            wp_enqueue_script('jitsi-pro-block', plugins_url('/blocks/dist/blocks.build.js', __FILE__), array('wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n'), filemtime(plugin_dir_path(__FILE__) . '/blocks/dist/blocks.build.js'));
        }

        /**
         * Enqueue editor assets
         *
         * @since 1.0.0
         *
         * @access public
         */
        public function jitsi_pro_gutenberg_editor_assets()
        {
            wp_enqueue_style('jitsi-pro-editor-css', plugins_url('/blocks/dist/blocks.editor.build.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . '/blocks/dist/blocks.editor.build.css'));
        }

        public function jitsi_pro_generate_jwt()
        {
            $prefix = 'jitsi_opt_';
            $token = get_transient('jitsi_saved_jwt');
            $private_key = get_option($prefix . 'private_key', '');
            $API_KEY = get_option($prefix . 'api_key', '');
            $APP_ID = get_option($prefix . 'app_id', '');

            if (!$private_key || !$API_KEY || !$APP_ID) {
                return '';
            }

            if (false === $token && !empty($private_key)) {
                //Getting configuration
                $API_KEY = $API_KEY;
                $APP_ID = $APP_ID;

                $admin_avatar = '';
                $admin_name   = '';
                $admin_email  = '';

                if (is_user_logged_in()) {
                    $current_user = wp_get_current_user();
                    $admin_avatar = get_avatar_url($current_user->ID);
                    $admin_name = $current_user->display_name;
                    $admin_email = $current_user->user_email;
                }

                $USER_EMAIL = get_option($prefix . 'user_email', '');
                $USER_NAME = get_option($prefix . 'user_name', '');
                $USER_IS_MODERATOR = get_option($prefix . 'user_is_moderator', 1) ? true : false;
                $USER_AVATAR_URL = get_option($prefix . 'user_avatar', JITSI_ULTIMATE_URL . '/assets/img/avatar.png');
                $LIVESTREAMING_IS_ENABLED = get_option($prefix . 'enable_livestream', 1) ? true : false;
                $RECORDING_IS_ENABLED = get_option($prefix . 'enable_recording', 1) ? true : false;
                $OUTBOUND_IS_ENABLED = get_option($prefix . 'enable_outbound', 1) ? true : false;
                $TRANSCRIPTION_IS_ENABLED = get_option($prefix . 'enable_transcription', 1) ? true : false;
                $EXP_DELAY_SEC = 7200;
                $NBF_DELAY_SEC = 10;

                function create_jaas_token(
                    $api_key,
                    $app_id,
                    $user_email,
                    $user_name,
                    $user_is_moderator,
                    $user_avatar_url,
                    $live_streaming_enabled,
                    $recording_enabled,
                    $outbound_enabled,
                    $transcription_enabled,
                    $exp_delay,
                    $nbf_delay,
                    $private_key
                ) {
                    $payload = array(
                        'iss' => 'chat',
                        'aud' => 'jitsi',
                        'exp' => time() + $exp_delay,
                        'nbf' => time() - $nbf_delay,
                        'room' => '*',
                        'sub' => $app_id,
                        'context' => [
                            'user' => current_user_can('edit_posts') ? [
                                'moderator' => $user_is_moderator ? "true" : "false",
                                'email' => $user_email,
                                'name' => $user_name,
                                'avatar' => $user_avatar_url
                            ] : [
                                'moderator' => "false",
                            ],
                            'features' => [
                                'recording' => $recording_enabled ? "true" : "false",
                                'livestreaming' => $live_streaming_enabled ? "true" : "false",
                                'transcription' => $transcription_enabled ? "true" : "false",
                                'outbound-call' => $outbound_enabled ? "true" : "false"
                            ]
                        ]
                    );
                    return JWT::encode($payload, $private_key, "RS256", $api_key);
                }

                $token = create_jaas_token(
                    $API_KEY,
                    $APP_ID,
                    $USER_EMAIL ? $USER_EMAIL : $admin_email,
                    $USER_NAME ? $USER_NAME : $admin_name,
                    $USER_IS_MODERATOR,
                    $USER_AVATAR_URL ?  $USER_AVATAR_URL : $admin_avatar,
                    $LIVESTREAMING_IS_ENABLED,
                    $RECORDING_IS_ENABLED,
                    $OUTBOUND_IS_ENABLED,
                    $TRANSCRIPTION_IS_ENABLED,
                    $EXP_DELAY_SEC,
                    $NBF_DELAY_SEC,
                    $private_key
                );

                set_transient('jitsi_saved_jwt', $token, 10);
            }

            return $token;
        }

        /**
         * Enqueue frontend assets
         *
         * @since 1.0.0
         *
         * @access public
         */
        public function jitsi_pro_gutenberg_front_assets()
        {
            wp_enqueue_style('jitsi-pro', plugins_url('/blocks/dist/blocks.style.build.css', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . '/blocks/dist/blocks.style.build.css'));
            if (is_singular()) {
                wp_enqueue_script('jitsi-api', plugins_url('/blocks/dist/external_api.js', __FILE__), array(), false);
                wp_enqueue_script('jitsi-countdown', plugins_url('/blocks/dist/jquery.countdown.min.js', __FILE__), array('jquery'), false);
                wp_register_script('jitsi-script', plugins_url('/blocks/dist/jitsi.js', __FILE__), array('jquery', 'wp-blocks'), filemtime(plugin_dir_path(__FILE__) . '/blocks/dist/jitsi.js'));
                wp_localize_script('jitsi-script', 'jitsi_pro', array(
                    'appid'                            => get_option('jitsi_opt_app_id', ''),
                    'api_select'                    => get_option('jitsi_opt_select_api', 'jaas'),
                    'custom_domain'                 => get_option('jitsi_opt_custom_domain', 'meet.jit.si'),
                    'jwt'                             => $this->jitsi_pro_generate_jwt(),
                    'sitename'                        => get_bloginfo('name'),
                    'ajaxurl'                       => admin_url('admin-ajax.php'),
                    'start_audio_only'                => get_option('jitsi_opt_start_audio_only', 0),
                    'start_audio_muted'                => get_option('jitsi_opt_start_audio_muted', 10),
                    'start_local_audio_muted'        => get_option('jitsi_opt_start_local_audio_muted', 0),
                    'start_silent'                    => get_option('jitsi_opt_start_silent', 0),
                    'video_resolution'                => get_option('jitsi_opt_video_resolution', 720),
                    'maxfullresolutionparticipant'    => get_option('jitsi_opt_maxfullresolutionparticipant', 2),
                    'disableSimulcast'                => get_option('jitsi_opt_disableSimulcast', 0),
                    'startVideoMuted'                => get_option('jitsi_opt_startVideoMuted', 10),
                    'startWithVideoMuted'            => get_option('jitsi_opt_startWithVideoMuted', 0),
                    'startScreenSharing'            => get_option('jitsi_opt_startScreenSharing', 0),
                    'enable_recording'                 => get_option('jitsi_opt_enable_recording', 1),
                    'liveStreamingEnabled'            => get_option('jitsi_opt_enable_livestream', 1),
                    'enable_transcription'            => get_option('jitsi_opt_enable_transcription', 1),
                    'enableWelcomePage'                => get_option('jitsi_opt_enableWelcomePage', 1),
                    'meeting_width'                    => get_option('jitsi_opt_width', 1080),
                    'meeting_height'                => get_option('jitsi_opt_height', 720),
                    'invite'                        => get_option('jitsi_opt_invite', 1)
                ));
                wp_enqueue_script('jitsi-script');
            }
        }
    }
}
