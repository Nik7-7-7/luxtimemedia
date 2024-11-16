<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('PostSettingsPanel')){
	 require_once ULP_PATH . 'classes/Abstracts/PostSettingsPanel.class.php';
}
if (class_exists('CertificatesSettingsPanel')){
	 return;
}
class CertificatesSettingsPanel extends PostSettingsPanel{
	/**
	 * @var string
	 */
	public $post_type = 'ulp_certificate';
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
				$this->post_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;
		}
		if (isset($_POST['submit'])){
			$prev_value = DbUlp::get_courses_for_certificate($this->post_id);
			$courses = ulp_sanitize_array( $_POST['ulp_course_certificate'] );
			foreach ( $courses as $course_id){
					if ($course_id>-1){
							$key = array_search($course_id, $prev_value);
							if ($key!==FALSE){
									unset($prev_value [$key]);
									continue;
							}
							update_post_meta($course_id, 'ulp_course_certificate', $this->post_id);
					}
			}
			if (count($prev_value)){
				foreach ($prev_value as $k=>$v){
						update_post_meta($v, 'ulp_course_certificate', '');
				}
			}
		}
		$this->options ['ulp_course_certificate'] = DbUlp::get_courses_for_certificate($this->post_id);
		$this->options ['courses'] = DbUlp::getAllCourses();
		$this->options ['form_submit_url'] = admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_certificate');
		$this->view_file = ULP_PATH . 'views/admin/post_panel-ulp_certificate.php';
	}
}
