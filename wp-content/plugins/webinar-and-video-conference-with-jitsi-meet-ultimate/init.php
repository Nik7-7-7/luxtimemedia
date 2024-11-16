<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Make sure the same class is not loaded twice
if (!class_exists('Jitsi_Meet_WP')) {
    /**
     * Main Jitsi Pro Class
     *
     * The main class that initiates and runs the Jitsi PRO plugin.
     *
     * @since 1.0.0
     */
    final class Jitsi_Meet_WP
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
         * @since  1.0.0
         *
         * @access public
         * @static
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
         * @since  1.0.0
         *
         * @access protected
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
         * @since  1.7.0
         *
         * @access protected
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
            add_action('init', [$this, 'i18n']);
            add_action('admin_enqueue_scripts', [$this, 'jitsi_pro_editor_scripts']);
            $this->jitsi_pro_manu_page_settings();
            add_action('updated_option', [$this, 'jitsi_pro_option_callback'], 10, 3);
            add_shortcode('jitsi-meet-wp', [$this, 'jitsi_shortcode_render']);
            add_shortcode('jitsi-meet-wp-meeting', [$this, 'jitsi_meeting_shortcode_render']);
            add_action('init', [$this, 'jitsi_tinymce_buttons']);
            $this->jitsi_init_elementor();
            if ($this->is_ultimate_active()) {
                $this->jitsi_woocommerce_purchasable();
                //$this->jitsi_multivendor_integration();
                $this->jitsi_buddypress_integration();
            }
        }


        /**
         * Load Textdomain
         *
         * Load plugin localization files.
         *
         * @since 1.0.0
         *
         * @access public
         */
        public function i18n()
        {
            load_plugin_textdomain('jitsi-pro', false, plugin_basename(dirname(__FILE__)) . '/languages');
        }

        public function is_ultimate_active()
        {
            global $jitsi_meet_license;

            if (!$jitsi_meet_license) {
                return false;
            }

            $is_ultimate_plan = $jitsi_meet_license->is_valid_by('title', 'jitsi-meet-ultimate-lifetime') || $jitsi_meet_license->is_valid_by('title', 'jitsi-meet-ultimate-yearly');
            return $jitsi_meet_license->is_valid() && $is_ultimate_plan;
        }

        public function jitsi_pro_manu_page_settings()
        {
            include_once JITSI_ULTIMATE_FILE_PATH . 'inc/admin-settings.php';
            include_once JITSI_ULTIMATE_FILE_PATH . 'inc/jitsi-pro-post.php';
            include_once JITSI_ULTIMATE_FILE_PATH . 'inc/helper.php';
            if (get_option('jitsi_meet_welcome_redirect_pro', false) && get_option('jitsi_meet_welcome_redirect_pro', false) != 'occured') {
                if (!isset($_GET['page']) || !($_GET['page'] == 'jitsi-meet-welcome')) {
                    wp_redirect(admin_url('admin.php?page=jitsi-meet-welcome'));
                    die();
                }
                update_option('jitsi_meet_welcome_redirect_pro', 'occured');
            }
        }

        public function jitsi_pro_option_callback($option, $old_value, $value)
        {
            if (strpos($option, 'jitsi_opt_') === 0) {
                delete_transient('jitsi_saved_jwt');
            }
        }

        /**
         * Editor Scripts
         */

        public function jitsi_pro_editor_scripts()
        {
            wp_enqueue_style('jitsi-timepicker', plugins_url('/blocks/dist/jquery.datetimepicker.min.css', __FILE__));
            wp_enqueue_script('jitsi-timepicker-js', plugins_url('/blocks/dist/jquery.datetimepicker.full.js', __FILE__), array('jquery'));
            wp_enqueue_script('jitsi-admin-script', plugins_url('/blocks/dist/jitsi.admin.js', __FILE__), array(), filemtime(plugin_dir_path(__FILE__) . '/blocks/dist/jitsi.admin.js'));
            wp_localize_script(
                'jitsi-admin-script',
                'jitsi_pro',
                array(
                    'sitename'                        => get_bloginfo('name'),
                    'siteurl'                        => get_site_url(),
                    'plugin_url'                    => plugins_url('/', __FILE__),
                    'appid'                            => get_option('jitsi_opt_app_id', ''),
                    'mce_btn_name'                    => esc_html__('Meeting', 'jitsi-pro'),
                    'mce_btn_title'                    => esc_html__('Add a Jitsi Meeting', 'jitsi-pro'),
                    'mce_btn_icon'                    => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 512 512" height="512" viewBox="0 0 512 512" width="512"><g><path fill="black" d="m296 256c0-22.056-17.944-40-40-40s-40 17.944-40 40 17.944 40 40 40 40-17.944 40-40zm-40 20c-11.028 0-20-8.972-20-20s8.972-20 20-20 20 8.972 20 20-8.972 20-20 20z"/><path fill="black" d="m70 476h141c5.522 0 10-4.477 10-10s-4.478-10-10-10h-141c-24.146 0-44.35-17.206-48.995-40.01h469.99c-4.645 22.804-24.849 40.01-48.995 40.01h-141c-5.522 0-10 4.477-10 10s4.478 10 10 10h141c38.598 0 70-31.402 70-70 0-5.523-4.478-10-10-10h-35v-260c0-5.523-4.478-10-10-10h-71v-80c0-3.688-2.03-7.077-5.281-8.817-3.252-1.74-7.199-1.549-10.266.497l-74.453 49.635v-41.315c0-5.523-4.478-10-10-10h-150c-5.522 0-10 4.477-10 10v80h-71c-5.522 0-10 4.477-10 10v260h-35c-5.522 0-10 4.477-10 10 0 38.598 31.402 70 70 70zm236-80h-100v-30c0-27.57 22.43-50 50-50s50 22.43 50 50zm60-331.315v82.63l-61.973-41.315zm-220-8.685h130v100h-130zm-81 90h61v20c0 5.523 4.478 10 10 10h150c5.522 0 10-4.477 10-10v-41.315l74.453 49.635c3.073 2.049 7.019 2.234 10.266.497 3.251-1.74 5.281-5.129 5.281-8.817v-20h61v250h-121v-30c0-38.598-31.402-70-70-70s-70 31.402-70 70v30h-121z"/><circle cx="256" cy="466" r="10"/></g></svg>'),
                    'meeting_width'                    => get_option('jitsi_opt_width', 1080),
                    'meeting_height'                => get_option('jitsi_opt_height', 720),
                    'enablewelcomepage'             => get_option('jitsi_opt_enableWelcomePage', 1) ? 1 : 0,
                    'startaudioonly'                => get_option('jitsi_opt_start_audio_only', 0) ? 1 : 0,
                    'startaudiomuted'               => get_option('jitsi_opt_start_audio_muted', 10),
                    'startwithaudiomuted'           => get_option('jitsi_opt_start_local_audio_muted', 0) ? 1 : 0,
                    'startsilent'                   => get_option('jitsi_opt_start_silent', 0) ? 1 : 0,
                    'resolution'                    => get_option('jitsi_opt_video_resolution', 720),
                    'maxfullresolutionparticipant'  => get_option('jitsi_opt_maxfullresolutionparticipant', 2),
                    'startvideomuted'               => get_option('jitsi_opt_startVideoMuted', 0) ? 1 : 0,
                    'startscreensharing'            => get_option('jitsi_opt_startScreenSharing', 0) ? 1 : 0,
                    'livestreamingenabled'          => get_option('jitsi_opt_enable_livestream', 1) ? 1 : 0,
                    'filerecordingsenabled'         => get_option('jitsi_opt_enable_recording', 1) ? 1 : 0,
                    'startwithvideomuted'           => get_option('jitsi_opt_startWithVideoMuted', 0) ? 1 : 0,
                    'transcribingenabled'           => get_option('jitsi_opt_enable_transcription', 1) ? 1 : 0,
                    'disablesimulcast'              => get_option('jitsi_opt_disableSimulcast', 0) ? 1 : 0,
                    'invite'                        => get_option('jitsi_opt_invite', 1) ? 1 : 0
                )
            );
        }

        //Tinymce buttons
        public function jitsi_tinymce_buttons()
        {
            if (!current_user_can('edit_posts') && !current_user_can('edit_pages')) {
                return;
            }

            if (get_user_option('rich_editing') !== 'true') {
                return;
            }

            add_filter('mce_external_plugins', [$this, 'jitsi_add_buttons']);
            add_filter('mce_buttons', [$this, 'jitsi_register_buttons']);
        }

        public function jitsi_add_buttons($plugin_array)
        {
            $plugin_array['jitsibutton'] = JITSI_ULTIMATE_URL . '/blocks/dist/jitsi.tinymce.js';
            return $plugin_array;
        }

        public function jitsi_register_buttons($buttons)
        {
            array_push($buttons, 'jitsibutton');
            return $buttons;
        }


        /**
         * Shortcode
         */

        public function jitsi_shortcode_render($args)
        {
            $paramArr = array(
                'name'                            => 'SampleJitsiMeetRoom',
                'width'                         => get_option('jitsi_opt_width', 700),
                'height'                         => get_option('jitsi_opt_height', 700),
                'startaudioonly'                => get_option('jitsi_opt_start_audio_only', 0),
                'startaudiomuted'                => get_option('jitsi_opt_start_audio_muted', 10),
                'startwithaudiomuted'            => get_option('jitsi_opt_start_local_audio_muted', 0),
                'startsilent'                    => get_option('jitsi_opt_start_silent', 0),
                'resolution'                    => get_option('jitsi_opt_video_resolution', 720),
                'maxfullresolutionparticipant'    => get_option('jitsi_opt_maxfullresolutionparticipant', 2),
                'disablesimulcast'                => get_option('jitsi_opt_disableSimulcast', 0),
                'startvideomuted'                => get_option('jitsi_opt_startVideoMuted', 10),
                'startwithvideomuted'            => get_option('jitsi_opt_startWithVideoMuted', 0),
                'startscreensharing'            => get_option('jitsi_opt_startScreenSharing', 0),
                'filerecordingsenabled'            => get_option('jitsi_opt_enable_recording', 1),
                'transcribingenabled'            => get_option('jitsi_opt_enable_transcription', 1),
                'livestreamingenabled'            => get_option('jitsi_opt_enable_livestream', 1),
                'enablewelcomepage'                => get_option('jitsi_opt_enableWelcomePage', 1),
                'invite'                        => get_option('jitsi_opt_invite', 1),
            );

            // if (is_user_logged_in()) {
            //     $current_user = wp_get_current_user();
            //     $userInfo = $current_user->display_name . ',' . $current_user->user_email;
            //     $paramArr['userinfo'] = $userInfo;
            // }

            $params = extract(shortcode_atts($paramArr, $args));

            $prefix = 'jitsi_opt_';

            $extra_data = '';

            foreach ($paramArr as $key => $value) {
                if (isset($$key)) {
                    $extra_data .= 'data-' . $key . '="' . $$key . '"';
                }
            }

            $extra_message = '';

            if (!get_option($prefix . 'api_key', '')) {
                $extra_data .= 'data-loadfree="true"';
                $extra_message = 'API key is missing. Loaded meeting via free api';
            }

            if (!get_option($prefix . 'app_id', '')) {
                $extra_data .= 'data-loadfree="true"';
                $extra_message = 'APP id is missing. Loaded meeting via free api';
            }

            if (!get_option($prefix . 'private_key', '')) {
                $extra_data .= 'data-loadfree="true"';
                $extra_message = 'Private key is missing. Loaded meeting via free api';
            }

            $output = sprintf(
                '<div 
					class="jitsi-wrapper" 
					style="width:%1$spx"
					%2$s
				></div><p class="extra-meesage">%3$s</p>',
                $width,
                $extra_data,
                $extra_message
            );
            return $output;
        }

        /**
         * Meeting Shortcode
         */
        public function jitsi_meeting_shortcode_render($args)
        {
            $paramArr = array(
                'id'                            => 0,
                'width'                         => get_option('jitsi_opt_width', 700),
                'height'                         => get_option('jitsi_opt_height', 700),
                'startaudioonly'                => get_option('jitsi_opt_start_audio_only', 0),
                'startaudiomuted'                => get_option('jitsi_opt_start_audio_muted', 10),
                'startwithaudiomuted'            => get_option('jitsi_opt_start_local_audio_muted', 0),
                'startsilent'                    => get_option('jitsi_opt_start_silent', 0),
                'resolution'                    => get_option('jitsi_opt_video_resolution', 720),
                'maxfullresolutionparticipant'    => get_option('jitsi_opt_maxfullresolutionparticipant', 2),
                'disablesimulcast'                => get_option('jitsi_opt_disableSimulcast', 0),
                'startvideomuted'                => get_option('jitsi_opt_startVideoMuted', 10),
                'startwithvideomuted'            => get_option('jitsi_opt_startWithVideoMuted', 0),
                'startscreensharing'            => get_option('jitsi_opt_startScreenSharing', 0),
                'filerecordingsenabled'            => get_option('jitsi_opt_enable_recording', 1),
                'transcribingenabled'            => get_option('jitsi_opt_enable_transcription', 1),
                'livestreamingenabled'            => get_option('jitsi_opt_enable_livestream', 1),
                'enablewelcomepage'                => get_option('jitsi_opt_enableWelcomePage', 1),
                'invite'                        => get_option('jitsi_opt_invite', 1)
            );

            // if (is_user_logged_in()) {
            //     $current_user = wp_get_current_user();
            //     $userInfo = $current_user->display_name . ',' . $current_user->user_email;
            //     $paramArr['userinfo'] = $userInfo;
            // }

            if (!isset($args['id'])) {
                return sprintf('<div class="jitsi-wrapper-error">%1$s</div>', esc_html__('Post id missing on shortcode. Use id attribute.', 'jitsi-pro'));
            }

            $post_meta = get_post_meta($args['id'], 'jitsi_pro__meeting_settings', true);

            foreach ($paramArr as $key => $value) {
                if (isset($post_meta['jitsi_pro__' . $key])) {
                    $paramArr[$key] = $post_meta['jitsi_pro__' . $key];
                }
            }

            $params = extract(shortcode_atts($paramArr, $args));
            $meeting = get_post($id);
            $prefix = 'jitsi_opt_';

            if (empty($meeting)) {
                return sprintf('<div class="jitsi-wrapper-error">%1$s</div>', esc_html__('No post found', 'jitsi-pro'));
            }

            if (!$meeting->post_type || $meeting->post_type != 'meeting') {
                return sprintf('<div class="jitsi-wrapper-error">%1$s</div>', esc_html__('Wrong post selected', 'jitsi-pro'));
            }

            $extra_data = '';
            foreach ($paramArr as $key => $value) {
                if (isset($$key)) {
                    $extra_data .= 'data-' . $key . '="' . $$key . '"';
                }
            }

            $extra_message = '';
            if (!get_option($prefix . 'api_key', '')) {
                $extra_data .= 'data-loadfree="true"';
                $extra_message = 'API key is missing. Loaded meeting via free api';
            }

            if (!get_option($prefix . 'app_id', '')) {
                $extra_data .= 'data-loadfree="true"';
                $extra_message = 'APP id is missing. Loaded meeting via free api';
            }

            if (!get_option($prefix . 'private_key', '')) {
                $extra_data .= 'data-loadfree="true"';
                $extra_message = 'Private key is missing. Loaded meeting via free api';
            }

            $attende_login = false;

            if (isset($_POST['meeting_password'])) {
                if (!wp_verify_nonce($_POST['nonce'], "jitsi_meeting_login_nonce")) {
                    exit("No naughty business please");
                }

                if (!isset($_POST['meeting_email'])) {
                    exit("No naughty business please");
                }
                $attendee = get_post_meta(get_the_ID(), 'registered_attendee', true);
                if ($_POST['meeting_password'] == $post_meta['jitsi_pro__password'] && in_array($_POST['meeting_email'], $attendee)) {
                    $attende_login = true;
                }
            }

            if (!function_exists('jitsi_meeting_output_shortcode')) {
                function jitsi_meeting_output_shortcode($args)
                {
                    ob_start();
                    $gmt_offset = get_option('gmt_offset');
                    $gmt_offset_val = $gmt_offset * 60 * 60;

                    $paramArr = array(
                        'id'                            => 0,
                        'width'                         => get_option('jitsi_opt_width', 700),
                        'height'                         => get_option('jitsi_opt_height', 700),
                        'startaudioonly'                => get_option('jitsi_opt_start_audio_only', 0),
                        'startaudiomuted'                => get_option('jitsi_opt_start_audio_muted', 10),
                        'startwithaudiomuted'            => get_option('jitsi_opt_start_local_audio_muted', 0),
                        'startsilent'                    => get_option('jitsi_opt_start_silent', 0),
                        'resolution'                    => get_option('jitsi_opt_video_resolution', 720),
                        'maxfullresolutionparticipant'    => get_option('jitsi_opt_maxfullresolutionparticipant', 2),
                        'disablesimulcast'                => get_option('jitsi_opt_disableSimulcast', 0),
                        'startvideomuted'                => get_option('jitsi_opt_startVideoMuted', 10),
                        'startwithvideomuted'            => get_option('jitsi_opt_startWithVideoMuted', 0),
                        'startscreensharing'            => get_option('jitsi_opt_startScreenSharing', 0),
                        'filerecordingsenabled'            => get_option('jitsi_opt_enable_recording', 1),
                        'transcribingenabled'            => get_option('jitsi_opt_enable_transcription', 1),
                        'livestreamingenabled'            => get_option('jitsi_opt_enable_livestream', 1),
                        'enablewelcomepage'                => get_option('jitsi_opt_enableWelcomePage', 1),
                        'invite'                        => get_option('jitsi_opt_invite', 1)
                    );

                    // if (is_user_logged_in()) {
                    //     $current_user = wp_get_current_user();
                    //     $userInfo = $current_user->display_name . ',' . $current_user->user_email;
                    //     $paramArr['userinfo'] = $userInfo;
                    // }

                    $post_meta = get_post_meta($args['id'], 'jitsi_pro__meeting_settings', true);

                    foreach ($paramArr as $key => $value) {
                        if (isset($post_meta['jitsi_pro__' . $key])) {
                            $paramArr[$key] = $post_meta['jitsi_pro__' . $key];
                        }
                    }

                    $params = extract(shortcode_atts($paramArr, $args));
                    $meeting = get_post($id);
                    $prefix = 'jitsi_opt_';

                    $extra_data = '';
                    foreach ($paramArr as $key => $value) {
                        if (isset($$key)) {
                            $extra_data .= 'data-' . $key . '="' . $$key . '"';
                        }
                    }

                    $extra_message = '';
                    if (!get_option($prefix . 'api_key', '')) {
                        $extra_data .= 'data-loadfree="true"';
                        $extra_message = 'API key is missing. Loaded meeting via free api';
                    }

                    if (!get_option($prefix . 'app_id', '')) {
                        $extra_data .= 'data-loadfree="true"';
                        $extra_message = 'APP id is missing. Loaded meeting via free api';
                    }

                    if (!get_option($prefix . 'private_key', '')) {
                        $extra_data .= 'data-loadfree="true"';
                        $extra_message = 'Private key is missing. Loaded meeting via free api';
                    }

                    $saved_time = $post_meta['jitsi_pro__start_time'];
                    $meeting_running = false;
                    $meeting = get_post($args['id']);

                    if (isset($post_meta['jitsi_pro__recurring']) && $post_meta['jitsi_pro__recurring']) {
                        $recuring_meeting_data = get_post_meta($args['id'], 'jitsi_pro__meeting_recurring_data', true) ? get_post_meta($args['id'], 'jitsi_pro__meeting_recurring_data', true) : [];

                        switch ($post_meta['jitsi_pro__recurring_repeat']) {
                            case 'day':
                                $saved_time = $post_meta['jitsi_pro__recurrence_time'];
                                if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                    $nexttime = 24 * 60 * 60;
                                }
                                break;
                            case 'week':
                                $saved_time = $post_meta['jitsi_pro__recurrence_time'];
                                $dayname = $post_meta['jitsi_pro__recurring_on_weekday'];
                                if (strtolower(date('l')) == $dayname) {
                                    if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                        $nexttime = 7 * 24 * 60 * 60;
                                    }
                                } else {
                                    $saved_time = date('Y-m-d H:i', strtotime('next ' . $dayname . $post_meta['jitsi_pro__recurrence_time']));
                                }
                                break;
                            case 'month':
                                $saved_timeval = explode(':', $post_meta['jitsi_pro__recurrence_time']);
                                $dateofmonth = $post_meta['jitsi_pro__recurring_on_monthly'];
                                if (date('j') == $dateofmonth) {
                                    if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m') + 1, $dateofmonth, date('Y')));
                                    }
                                } elseif (date('j') > $dateofmonth) {
                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m') + 1, $dateofmonth, date('Y')));
                                } else {
                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m'), $dateofmonth, date('Y')));
                                }
                                break;
                            case 'year':
                                $saved_timeval = explode(':', $post_meta['jitsi_pro__recurrence_time']);
                                $monthofyear = $post_meta['jitsi_pro__recurring_on_yearly'];
                                $dateofmonth = $post_meta['jitsi_pro__recurring_on_yearly_date'];
                                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')));
                                if (date('n') == $monthofyear) {
                                    if (date('j') == $dateofmonth) {
                                        if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                            $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y') + 1));
                                        }
                                    }
                                    if (date('j') > $dateofmonth) {
                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y') + 1));
                                    }
                                }

                                if (date('n') > $monthofyear) {
                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y') + 1));
                                }
                                break;
                            default:
                                $saved_time = $post_meta['jitsi_pro__recurrence_time'];
                        }

                        if (((isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'start_time']) || (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)) && !isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time']))) {
                            $meeting_running = true;
                        }
                    } else {
                        if (!$post_meta['jitsi_pro__end_time'] && (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)) {
                            $meeting_running = true;
                        }
                    }

                    if ($meeting_running) {
                        printf('<div class="jitsi-wrapper" data-name="%1$s" style="width:%2$spx" %3$s></div><p class="extra-message">%4$s</p>', $meeting->post_title, $width, $extra_data, $extra_message);
                    } else {

                    ?>
                        <div class="jitsi-container">
                            <div class="jitsi-row">
                                <div class="jitsi-col-8">
                                    <h4 style="text-align:center">Meeting is not running now</h4>
                                    <div class="jitsi-single-widget jitsi-single-widget-countdown">
                                        <?php if (isset($post_meta['jitsi_pro__recurring']) && $post_meta['jitsi_pro__recurring']) {
                                            switch ($post_meta['jitsi_pro__recurring_repeat']) {
                                                case 'day':
                                                    $saved_time = $post_meta['jitsi_pro__recurrence_time'];
                                                    if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                                        $nexttime = 24 * 60 * 60;
                                                    }
                                                    break;
                                                case 'week':
                                                    $saved_time = $post_meta['jitsi_pro__recurrence_time'];
                                                    $dayname = $post_meta['jitsi_pro__recurring_on_weekday'];
                                                    if (strtolower(date('l')) == $dayname) {
                                                        if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                                            $nexttime = 7 * 24 * 60 * 60;
                                                        }
                                                    } else {
                                                        $saved_time = date('Y-m-d H:i', strtotime('next ' . $dayname . $post_meta['jitsi_pro__recurrence_time']));
                                                    }
                                                    break;
                                                case 'month':
                                                    $saved_timeval = explode(':', $post_meta['jitsi_pro__recurrence_time']);
                                                    $dateofmonth = $post_meta['jitsi_pro__recurring_on_monthly'];
                                                    if (date('j') == $dateofmonth) {
                                                        if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                                            $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m') + 1, $dateofmonth, date('Y')));
                                                        }
                                                    } elseif (date('j') > $dateofmonth) {
                                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m') + 1, $dateofmonth, date('Y')));
                                                    } else {
                                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, date('m'), $dateofmonth, date('Y')));
                                                    }
                                                    break;
                                                case 'year':
                                                    $saved_timeval = explode(':', $post_meta['jitsi_pro__recurrence_time']);
                                                    $monthofyear = $post_meta['jitsi_pro__recurring_on_yearly'];
                                                    $dateofmonth = $post_meta['jitsi_pro__recurring_on_yearly_date'];
                                                    $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y')));
                                                    if (date('n') == $monthofyear) {
                                                        if (date('j') == $dateofmonth) {
                                                            if (isset($recuring_meeting_data['jitsi_pro__' . date("Y-m-d") . 'end_time'])) {
                                                                $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y') + 1));
                                                            }
                                                        }
                                                        if (date('j') > $dateofmonth) {
                                                            $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y') + 1));
                                                        }
                                                    }

                                                    if (date('n') > $monthofyear) {
                                                        $saved_time = date('Y-m-d H:i', mktime($saved_timeval[0], $saved_timeval[1], 0, $monthofyear, $dateofmonth, date('Y') + 1));
                                                    }
                                                    break;
                                                default:
                                                    $saved_time = $post_meta['jitsi_pro__recurrence_time'];
                                            }
                                        ?>
                                            <h4 class="jitsi-widget-title"><?php esc_html_e('Next meeting on', 'jitsi-pro'); ?></h4>
                                            <div class="jitsi-widget-inner">
                                                <?php if ((array_key_exists('jitsi_pro__' . date("Y-m-d") . 'start_time', $recuring_meeting_data) || (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)) && array_key_exists('jitsi_pro__' . date("Y-m-d") . 'end_time', $recuring_meeting_data)) { ?>
                                                    <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', (strtotime($saved_time) - $gmt_offset_val) + $nexttime); ?>"></div>
                                                <?php } elseif ((array_key_exists('jitsi_pro__' . date("Y-m-d") . 'start_time', $recuring_meeting_data) || (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) >= 0)) && !(array_key_exists('jitsi_pro__' . date("Y-m-d") . 'end_time', $recuring_meeting_data))) { ?>
                                                    <div class="jitsi-countdown"><span class="jitsi-countedown-block"><span class="jitsi-countdown-value"><?php _e('Meeting is running', 'jitsi-pro'); ?></span><span class="jitsi-countdown-label"><?php _e('The meeting is started and running', 'jitsi-pro'); ?></span></span></div>
                                                <?php } else { ?>
                                                    <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', strtotime($saved_time) - $gmt_offset_val); ?>"></div>
                                                <?php } ?>
                                            </div>
                                        <?php } else { ?>
                                            <h4 class="jitsi-widget-title"><?php esc_html_e('Time to go', 'jitsi-pro'); ?></h4>
                                            <div class="jitsi-widget-inner">
                                                <?php if (!isset($post_meta['jitsi_pro__end_time']) || !$post_meta['jitsi_pro__end_time']) { ?>
                                                    <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', strtotime($post_meta['jitsi_pro__start_time']) - $gmt_offset_val); ?>"></div>
                                                    <?php } else {
                                                    if (isset($post_meta['jitsi_pro__booked_meeting']) && $post_meta['jitsi_pro__booked_meeting']) {
                                                    ?>
                                                        <div class="jitsi-countdown" data-time="<?php echo date('n/d/Y g:i:s A T', strtotime($post_meta['jitsi_pro__start_time']) - $gmt_offset_val); ?>"></div>
                                                    <?php
                                                    } else {
                                                    ?>
                                                        <div class="jitsi-countdown"><span class="jitsi-countedown-block"><span class="jitsi-countdown-value"><?php _e('Meeting is finished'); ?></span><span class="jitsi-countdown-label"><?php _e('You are late for the meeting. The meeting was helded', 'jitsi-pro'); ?></span></span></div>
                                                <?php
                                                    }
                                                } ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }

                    return ob_get_clean();
                }
            }
        
            $meet_instance = new Jitsi_Meet_WP();
            $is_ultimate = $meet_instance->is_ultimate_active();
            $product_of_meeting = get_post_meta($args['id'], '_product_id', true);
            if ($is_ultimate && $product_of_meeting) {
                if ($post_meta['jitsi_pro__host'] == get_current_user_id()) {
                    $output = jitsi_meeting_output_shortcode($args);
                } else {
                    if ($attende_login) {
                        $output = jitsi_meeting_output_shortcode($args);
                    } else {
                        ob_start();
                    ?>
                        <h4><?php _e('Signin with registration detail to attend the meeting:', 'jitsi-pro'); ?></h4>
                        <form id="attendee_login_form" method="post">
                            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_login_nonce"); ?>" />
                            <input type="email" name="meeting_email" placeholder="<?php esc_attr_e('Meeting Email', 'jitsi-pro'); ?>" />
                            <input type="password" name="meeting_password" placeholder="<?php esc_attr_e('Meeting Password', 'jitsi-pro'); ?>" />
                            <button type="submit"><?php _e('Signin to Meeting', 'jitsi-pro'); ?></button>
                        </form>
                        <h4><?php _e('Or, Buy the ticket for the meeting:', 'jitsi-pro'); ?></h4>
                        <a class="jitsi-buy-btn" href="<?php echo get_permalink($product_of_meeting); ?>"><?php _e('Buy Now', 'jitsi-pro'); ?></a>
                        <?php
                        $output = ob_get_clean();
                    }
                }
            } else {             
                if (isset($post_meta['jitsi_pro__should_register']) && $post_meta['jitsi_pro__should_register']) {

                    if (!($post_meta['jitsi_pro__host'] == get_current_user_id()) && !$post_meta['jitsi_pro__end_time']) {
                        if ($attende_login) {
                            $output = jitsi_meeting_output_shortcode($args);
                        } else {
                            ob_start();
                        ?>
                            <h4><?php _e('Signin with registration detail to attend the meeting:', 'jitsi-pro'); ?></h4>
                            <form id="attendee_login_form" method="post">
                                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_login_nonce"); ?>" />
                                <div class="jitsi-form-row">
                                    <div class="jitsi-form-col">
                                        <input type="email" name="meeting_email" placeholder="<?php esc_attr_e('Meeting Email', 'jitsi-pro'); ?>" />
                                    </div>
                                    <div class="jitsi-form-col">
                                        <input type="password" name="meeting_password" placeholder="<?php esc_attr_e('Meeting Password', 'jitsi-pro'); ?>" />
                                    </div>
                                    <div class="jitsi-form-col">
                                        <button type="submit"><?php _e('Signin to Meeting', 'jitsi-pro'); ?></button>
                                    </div>
                                </div>
                            </form>
                            <h4><?php _e('Or, Register to join the meeting:', 'jitsi-pro'); ?></h4>
                            <form id="attendee_registration_form" method="post">
                                <p class="form-message"></p>
                                <input type="hidden" name="action" value="register_to_meeting" />
                                <input type="hidden" name="post_id" value="<?php the_ID(); ?>" />
                                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("jitsi_meeting_register_nonce"); ?>" />
                                <div class="jitsi-form-row">
                                    <div class="jitsi-form-col">
                                        <input type="text" name="meeting_rname" id="meeting_rname" placeholder="<?php esc_html_e('Your Name', 'jitsi-pro'); ?>" />
                                    </div>
                                    <div class="jitsi-form-col">
                                        <input type="email" name="meeting_remail" id="meeting_remail" placeholder="<?php esc_attr_e('Your Email', 'jitsi-pro'); ?>" />
                                    </div>
                                    <div class="jitsi-form-col">
                                        <button type="submit"><?php _e('Register to Meeting', 'jitsi-pro'); ?></button>
                                    </div>
                                </div>
                            </form>
                        <?php
                            $output = ob_get_clean();
                        }
                    }
                    if ($post_meta['jitsi_pro__host'] == get_current_user_id() && !$post_meta['jitsi_pro__end_time']) {
                        $output = jitsi_meeting_output_shortcode($args);
                    }
                } elseif (isset($post_meta['jitsi_pro__booked_meeting']) && $post_meta['jitsi_pro__booked_meeting']) {
                    if (get_current_user_id() == $options['jitsi_pro__booked_for']) {
                        if (((strtotime(date("Y-m-d H:i:s")) + $gmt_offset_val) - strtotime($saved_time)) < 0) {
                            return sprintf('%1$s <dd class="jitsi-usertime" data-time="%2$s"></dd>', __('You are too early. Your schedule is:', 'jitsi-pro'), date('n/d/Y g:i:s A T', (strtotime($saved_time) - $gmt_offset_val) + $nexttime));
                        } else {
                            return jitsi_meeting_output_shortcode();
                        }
                    } elseif ($options['jitsi_pro__host'] == get_current_user_id()) {
                        return jitsi_meeting_output_shortcode();
                    } else {
                        return __('Sorry this is a booked meeting', 'jitsi-pro');
                    }
                } else {
                    $output = jitsi_meeting_output_shortcode($args);
                }
            }

            return $output;
        }

        /**
         * Elementor
         */
        public function jitsi_init_elementor()
        {
            // Check if Elementor installed and activated
            if (!did_action('elementor/loaded')) {
                return;
            }
            add_action('elementor/widgets/widgets_registered', [$this, 'jitsi_el_widgets_registered']);
        }

        public function jitsi_el_widgets_registered()
        {
            $this->jitsi_el_include_widgets();
            $this->jitsi_el_register_widgets();
        }

        private function jitsi_el_include_widgets()
        {
            include_once __DIR__ . '/inc/elementor.php';
        }

        private function jitsi_el_register_widgets()
        {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Jitsi\Widgets\Jitsi_Elementor());
        }

        private function plugin_is_active($plugin)
        {
            $active_plugins = (array) get_option('active_plugins', array());
            return in_array($plugin, $active_plugins) || array_key_exists($plugin, $active_plugins);
        }

        /**
         * WooCommerce Integration 
         */
        public function jitsi_woocommerce_purchasable()
        {
            if (!$this->is_ultimate_active()) {
                return false;
            }

            if ($this->plugin_is_active('woocommerce-bookings/woocommerce-bookings.php')) {
                include_once __DIR__ . '/inc/woocommerce-booking.php';
            }

            include_once __DIR__ . '/inc/woocommerce.php';
        }


        /**
         * Multivendor integration
         */
        public function jitsi_multivendor_integration()
        {
            if (!$this->is_ultimate_active()) {
                return false;
            }

            if (function_exists('dokan')) {
                include_once __DIR__ . '/inc/dokan.php';
            }
        }

        public function jitsi_buddypress_integration()
        {
            if (!$this->is_ultimate_active()) {
                return false;
            }

            if (function_exists('bp_is_active')) {
                include_once __DIR__ . '/inc/buddypress.php';
            }
        }
    }
}
