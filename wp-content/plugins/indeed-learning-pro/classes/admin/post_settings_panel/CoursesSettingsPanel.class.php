<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('PostSettingsPanel')){
	 require_once ULP_PATH . 'classes/Abstracts/PostSettingsPanel.class.php';
}
if (class_exists('CoursesSettingsPanel')){
	 return;
}
class CoursesSettingsPanel extends PostSettingsPanel{
	/**
	 * @var string
	 */
	public $post_type = 'ulp_course';
	/**
	 * @var string
	 */
	public $view_file = '';
	/**
	 * @param none
	 * @return none
	 */
	public function __construct($post_id=0){
		if (!empty($post_id)){
				$this->post_id = $post_id;
		} else {
				$this->post_id = isset($_GET['id']) ? $_GET['id'] : 0;
		}
 		/// set the options here
 		$this->options = DbUlp::getPostMetaGroup($this->post_id, 'course_special_settings');
		if (isset($_POST['submit'])){
				$this->doSave( ulp_sanitize_array($_POST) );
		}
		$this->options = DbUlp::getPostMetaGroup($this->post_id, 'course_special_settings');
		if (get_option('ulp_course_difficulty_enable')){
				$this->options ['course_difficulty_types'] = DbUlp::get_course_difficulty_types();
		}
		$this->options ['ulp_course_time_period_enable'] = get_option('ulp_course_time_period_enable');
		$this->options['coming_soon'] = get_option('ulp_coming_soon_enabled');
		$this->options['form_submit_url'] = admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_course');
		$this->view_file = ULP_PATH . 'views/admin/post_panel-ulp_course.php';
	}
}
