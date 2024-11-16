<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Multiple_Instructors')){
   return;
}
class Ulp_Multiple_Instructors{
    public function __construct(){
        if (get_option('ulp_multiple_instructors_enable')){
            add_action('add_meta_boxes', array($this, 'multiple_instructors'));
            add_action('save_post', array($this, 'save_multiple_instructors'), 1, 1000);
        }
    }
    public function multiple_instructors(){
        add_meta_box( 'ulp_multiple_instructors',  esc_html__('Ultimate Learning Pro - Additional Instructors', 'ulp'), array($this, 'meta_box_html'), 'ulp_course', 'normal', 'default', '' );
    }
    public function meta_box_html(){
        global $post;
        $data = array(
            'instructors' => DbUlp::getAllInstructors(),
            'ulp_additional_instructors' => get_post_meta($post->ID, 'ulp_additional_instructors', TRUE),
        );
        $data ['value_as_array'] = explode(',', $data['ulp_additional_instructors']);
        $view = new ViewUlp();
    		$view->setTemplate(ULP_PATH . 'views/admin/meta_box_multiple_instructors.php');
    		$view->setContentData($data);
    		echo esc_ulp_content($view->getOutput());
    }
    public function save_multiple_instructors(){
        global $post;
        if (isset($post->post_type) && $post->post_type=='ulp_course' && isset($_POST['ulp_additional_instructors']) && !empty($post->ID)){
            update_post_meta($post->ID, 'ulp_additional_instructors', ulp_sanitize_array($_POST['ulp_additional_instructors']) );
        }
    }
}
