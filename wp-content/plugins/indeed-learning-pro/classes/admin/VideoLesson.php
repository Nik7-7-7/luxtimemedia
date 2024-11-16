<?php
namespace Indeed\Ulp\Admin;

class VideoLesson
{
    public function __construct()
    {
        add_action( 'add_meta_boxes', [ $this, 'registerMetaBox' ] );
        add_action( 'save_post', [ $this, 'saveMetaValue'], 99, 1 );
    }

    public function registerMetaBox()
    {
        add_meta_box( 'ulp_video_lesson_meta_box',
                       esc_html__( 'Ultimate Learning Pro - Video Lesson', 'ulp' ),
                      [ $this, 'printMetaBox' ],
                      'ulp_lesson',
                      'normal',
                      'high'
        );
    }

    public function printMetaBox()
    {
        global $post;

        if(!isset($post->ID)){
  				return;
  			}
        $settings = \DbUlp::getPostMetaGroup( $post->ID, 'video_lesson_settings' );
        $data = [
            'settings'      => $settings,
        ];
        $view = new \ViewUlp();
        $output = $view->setTemplate( ULP_PATH . 'views/admin/video_lesson_settings.php' )->setContentData( $data )->getOutput();
        echo esc_ulp_content( $output );
    }

    public function saveMetaValue( $postId=0 )
    {
        if ( !$postId || \DbUlp::getPostTypeById( $postId ) != 'ulp_lesson' ){
            return;
        }
        $settings = \DbUlp::getPostMetaGroup( $postId, 'video_lesson_settings' );
        foreach ( $settings as $key => $value ){
            if ( !isset($_POST[$key]) ){
               continue;
            }
            update_post_meta( $postId, $key, ulp_sanitize_array($_POST[$key]) );
        }
    }


}
