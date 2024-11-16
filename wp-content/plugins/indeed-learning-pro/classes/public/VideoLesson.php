<?php
namespace Indeed\Ulp\PublicSection;

class VideoLesson
{
    private $lessonId             = 0;
    private $settings             = [];

    public function __construct( $lessonId=0 )
    {
        $this->lessonId = $lessonId;
        $this->settings = \DbUlp::getPostMetaGroup( $lessonId, 'video_lesson_settings' );
        $this->settings['service_type'] = '';
        if ( strpos( $this->settings['ulp_lesson_video_target'], 'youtube' ) !== false ){
            $this->settings['service_type'] = 'youtube';
        } else if ( strpos( $this->settings['ulp_lesson_video_target'], 'vimeo' ) !== false  ){
            $this->settings['service_type'] = 'vimeo';
        }
        $this->includeJS();
    }

    public function includeJS()
    {
        global $wp_version;
        $settings = [
                        'autoplay'            => $this->settings['ulp_lesson_video_autoplay'],
                        'autocomplete'        => $this->settings['ulp_lesson_video_autocomplete'],
                        'lessonId'            => $this->lessonId,
                        'width'               => $this->settings['ulp_lesson_video_width'],
                        'height'              => $this->settings['ulp_lesson_video_height'],
        ];
        if ( $this->settings['service_type'] == 'youtube' ){
            $settings[ 'target' ] = $this->getYoutubeVideoCode();
            wp_enqueue_script( 'ulp_youtube_api', 'https://www.youtube.com/iframe_api', [], false, ['strategy' => 'async'] );
            wp_register_script( 'ulp_youtube', ULP_URL . 'assets/js/youtube.js', [], '3.6', false );

            if ( version_compare ( $wp_version , '5.7', '>=' ) ){
                wp_localize_script( 'ulp_youtube', 'ulp_youtube_settings', $settings );
            } else {
                wp_localize_script( 'ulp_youtube', 'ulp_youtube_settings', json_encode( $settings ) );
            }

            wp_enqueue_script( 'ulp_youtube' );
        } else if ( $this->settings['service_type'] == 'vimeo' ){
            $settings[ 'target' ] = $this->getVimeoVideoCode();
            wp_enqueue_script( 'ulp_vimeo_api', 'https://player.vimeo.com/api/player.js', [], false, false );
            wp_register_script( 'ulp_vimeo', ULP_URL . 'assets/js/vimeo.js', [], 3.6, false );
            if ( version_compare ( $wp_version , '5.7', '>=' ) ){
                wp_localize_script( 'ulp_vimeo', 'ulp_vimeo_settings', $settings );
            } else {
                wp_localize_script( 'ulp_vimeo', 'ulp_vimeo_settings', json_encode( $settings ) );
            }

            wp_enqueue_script( 'ulp_vimeo' );
        }
    }

    public function getYoutubeVideoCode()
    {
        if ( strpos( $this->settings['ulp_lesson_video_target'], 'v=' ) !== false ){
            $codeParts = explode( 'v=', $this->settings['ulp_lesson_video_target'] );
            if ( isset( $codeParts[1] ) && strpos( $codeParts[1], '&' ) !== false ){
                $segments = explode( '&', $codeParts[1] );
                return isset( $segments[0] ) ? $segments[0] : '';
            }
            return isset( $codeParts[1] ) ? $codeParts[1] : '';
        } else if ( strpos( $this->settings['ulp_lesson_video_target'], 'embed' ) !== false ) {
            /// handle embed
            $codeParts = explode( 'embed/', $this->settings['ulp_lesson_video_target']);
            return isset( $codeParts[1] ) ? $codeParts[1] : '';
        }
        return '';
    }

    public function getVimeoVideoCode()
    {
        return $this->settings['ulp_lesson_video_target'];
    }

    public function setLessonId( $lessonId=0 )
    {
        $this->lessonId = $lessonId;
        return $this;
    }

    public function getLessonId()
    {
        return $this->lessonId;
    }

    public function setSettings( $settings=[] )
    {
        $this->settings = $settings;
        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function isYoutube()
    {
        if ( $this->settings['service_type'] == 'youtube' ){
            return true;
        }
        return false;
    }

    public function isVimeo()
    {
        if ( $this->settings['service_type'] == 'vimeo' ){
            return true;
        }
        return false;
    }

}
