<?php
namespace Jitsi\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;
use Elementor\Group_Control_Typography;



// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Text Typing Effect
 *
 * Elementor widget for text typing effect.
 *
 * @since 1.7.0
 */
class Jitsi_Elementor extends Widget_Base {

    public function __construct($data = [], $args = null) {
        parent::__construct($data, $args);  
     }
  
     public function get_script_depends() {
         return [ 'jitsi-api', 'jitsi-script' ];
     }

    public function get_name() {
        return 'jitsi_elementor';
    }

    public function get_title() {
        return esc_html__( 'Jitsi Meet', 'jitsi-meet-wp' );
    }

    public function get_icon() {
        return 'eicon-video-camera';
    }

    public function get_keywords() {
        return [ 'jitsi', 'meeting', 'conference'];
    }

    public function get_categories() {
        return [ 'basic' ];
    }

    protected function _register_controls() {

        // -------------------  Default Section  -----------------------//
        $this->start_controls_section(
            'Configuration',
            [
                'label' => esc_html__( 'Configuration', 'jitsi-pro' ),
            ]
        );

        $this->add_control(
			'form_post',
			[
				'label' => __( 'Form Post?', 'jitsi-pro' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-pro' ),
				'label_off' => __( '', 'jitsi-pro' ),
				'default' => false,
			]
		);

        $this->add_control(
            'name',
            [
                'label' => esc_html__( 'Name', 'jitsi-meet-wp' ),
                'type' => Controls_Manager::TEXT,
                'default' => 'SampleJitsiMeetRoom',
                'placeholder' => esc_html__( 'Meeting name here', 'jitsi-meet-wp' ),
                'condition' => [
					'form_post!' => 'yes'
				],
            ]
        );

        $this->add_control(
            'post_id',
            [
                'label' => esc_html__( 'Select Post', 'jitsi-meet-wp' ),
                'type' => Controls_Manager::SELECT,
                'options' => $this->jitsi_get_post(),
                'condition' => [
					'form_post' => 'yes'
				],
            ]
        );

        $this->add_control(
            'width',
            [
                'label' => esc_html__( 'Width', 'jitsi-meet-wp' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 100,
				'max' => 2000,
				'step' => 5,
				'default' => 1080,
                'selectors' => [
                    '{{WRAPPER}} .jitsi-wrapper' => 'width: {{VALUE}}px;'
                ],
            ]
        );

        $this->add_control(
            'height',
            [
                'label' => esc_html__( 'Height', 'jitsi-meet-wp' ),
                'type' => Controls_Manager::NUMBER,
                'min' => 100,
				'max' => 2000,
				'step' => 5,
				'default' => 720
            ]
        );
        
        $this->add_control(
			'startaudioonly',
			[
				'label' => __( 'Start audio only', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => true,
			]
		);

        $this->add_control(
			'startaudiomuted',
			[
				'label' => __( 'Start audio muted', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'step' => 1,
				'default' => 10
			]
		);

        $this->add_control(
			'startwithaudiomuted',
			[
				'label' => __( 'Start with audio muted', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => false,
			]
		);

        $this->add_control(
			'startsilent',
			[
				'label' => __( 'Start with silent', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => false,
			]
		);

        $this->add_control(
            'resolution',
            [
                'label' => esc_html__( 'Resolution', 'jitsi-meet-wp' ),
                'type' => Controls_Manager::SELECT,
                'options' => [
                    480 => '480p',
                    720 => '720p',
                    1080 => '1080p',
                    1440 => '1440p',
                    2160 => '2160p',
                    4320 => '4320p'
                ],
                'default'   => 720
            ]
        );

        $this->add_control(
			'maxfullresolutionparticipant',
			[
				'label' => __( 'Max full resolution', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'step' => 1,
				'default' => 2
			]
		);

        $this->add_control(
			'disablesimulcast',
			[
				'label' => __( 'Disable Simulcast', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => false
			]
		);

        $this->add_control(
			'startvideomuted',
			[
				'label' => __( 'Video Muted', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'step' => 1,
				'default' => 10
			]
		);

        $this->add_control(
			'startwithvideomuted',
			[
				'label' => __( 'Start with video muted', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => true
			]
		);

        $this->add_control(
			'startscreensharing',
			[
				'label' => __( 'Start screen sharing', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => false
			]
		);

        $this->add_control(
			'filerecordingsenabled',
			[
				'label' => __( 'File Recording', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => true
			]
		);

        $this->add_control(
			'transcribingenabled',
			[
				'label' => __( 'Transcribing', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => true
			]
		);

        $this->add_control(
			'livestreamingenabled',
			[
				'label' => __( 'Live Streaming', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => true
			]
		);

        $this->add_control(
			'enablewelcomepage',
			[
				'label' => __( 'Welcome Page', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => true
			]
		);

        $this->add_control(
			'invite',
			[
				'label' => __( 'Enable Inviting', 'jitsi-meet-wp' ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( '', 'jitsi-meet-wp' ),
				'label_off' => __( '', 'jitsi-meet-wp' ),
				'return_value' => true,
				'default' => true,
			]
		);


        $this->end_controls_section();
    }

    public function jitsi_get_post($id = null){
        if($id){
            return get_post($id);
        }
        $toSelect = [];
        $meetingposts = get_posts( array('numberposts' => -1, 'post_type'   => 'meeting') );
        foreach($meetingposts as $post){
            $toSelect[$post->ID] = $post->post_title;
        }
        return $toSelect;
    }

    protected function render() {

        $settings = $this->get_settings();

        $paramArr = array(
            'name'							=> 'SampleJitsiMeetRoom',
            'width' 						=> get_option('jitsi_opt_width', 700),
            'height' 						=> get_option('jitsi_opt_height', 700),
            'startaudioonly'				=> get_option('jitsi_opt_start_audio_only', 0),
            'startaudiomuted'				=> get_option('jitsi_opt_start_audio_muted', 10),
            'startwithaudiomuted'			=> get_option('jitsi_opt_start_local_audio_muted', 0),
            'startsilent'					=> get_option('jitsi_opt_start_silent', 0),
            'resolution'					=> get_option('jitsi_opt_video_resolution', 720),
            'maxfullresolutionparticipant'	=> get_option('jitsi_opt_maxfullresolutionparticipant', 2),
            'disablesimulcast'				=> get_option('jitsi_opt_disableSimulcast', 0),
            'startvideomuted'				=> get_option('jitsi_opt_startVideoMuted', 10),
            'startwithvideomuted'			=> get_option('jitsi_opt_startWithVideoMuted', 0),
            'startscreensharing'			=> get_option('jitsi_opt_startScreenSharing', 0),
            'filerecordingsenabled'			=> get_option('jitsi_opt_enable_recording', 1),
            'transcribingenabled'			=> get_option('jitsi_opt_enable_transcription', 1),
            'livestreamingenabled'			=> get_option('jitsi_opt_enable_livestream', 1),
            'enablewelcomepage'				=> get_option('jitsi_opt_enableWelcomePage', 1),
            'invite'                        => get_option('jitsi_opt_invite', 1) ? 1 : 0
        );

        $prefix = 'jitsi_opt_';
        if ($settings['form_post'] == 'yes') {
            if (!isset($settings['post_id'])) {
                printf('<div class="jitsi-wrapper-error">%1$s</div>', esc_html__('Post id missing on shortcode. Use id attribute.', 'jitsi-pro'));
            } else {
                $post_meta = get_post_meta($settings['post_id'], 'jitsi_pro__meeting_settings', true);
                foreach ($paramArr as $key => $value) {
                    if (isset($post_meta['jitsi_pro__' . $key])) {
                        $paramArr[$key] = $post_meta['jitsi_pro__' . $key];
                    }
                }
                foreach ($paramArr as $key=>$value) {
                    if (isset($settings[$key])) {
                        $paramArr[$key] = $settings[$key];
                    }
                }
                $extra_data = '';
                foreach ($paramArr as $key => $value) {
                    if (isset($paramArr[$key])) {
                        $extra_data .= 'data-' . $key . '="'.($value ? $value : '0').'"';
                    }
                }

                $paramArr['name'] = $this->jitsi_get_post($settings['post_id'])->post_title;

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

                printf(
                    '<div 
                        class="jitsi-wrapper" 
                        data-name="%1$s" 
                        %2$s
                    ></div><p class="extra-message">%3$s</p>',
                    $paramArr['name'],
                    $extra_data,
                    $extra_message
                );
            }
        } else {
            foreach ($paramArr as $key=>$value) {
                if (isset($settings[$key])) {
                    $paramArr[$key] = $settings[$key];
                }
            }
            $extra_data = '';                
            foreach ($paramArr as $key => $value) {
                if (isset($paramArr[$key])) {
                    $extra_data .= 'data-' . $key . '="'.($value ? $value : '0').'"';
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

            printf(
                '<div 
                    class="jitsi-wrapper" 
                    data-name="%1$s" 
                    %2$s
                ></div><p class="extra-message">%3$s</p>',
                $paramArr['name'],
                $extra_data,
                $extra_message
            );
        }
    }
}
