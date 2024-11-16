<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('PostSettingsPanel')){
	 require_once ULP_PATH . 'classes/Abstracts/PostSettingsPanel.class.php';
}
if (class_exists('QuizesSettingsPanel')){
	 return;
}
class QuizesSettingsPanel extends PostSettingsPanel{
	/**
	 * @var string
	 */
	public $post_type = 'ulp_quiz';
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
		$this->options = DbUlp::getPostMetaGroup($this->post_id, 'quiz_special_settings');
		if (isset($_POST['submit'])){
			$_POST = ulp_sanitize_array( $_POST );
			$this->doSave($_POST);
		}
		$this->options = DbUlp::getPostMetaGroup($this->post_id, 'quiz_special_settings');
		$this->options['form_submit_url'] = admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_quiz');
		$this->view_file = ULP_PATH . 'views/admin/post_panel-ulp_quiz.php';
	}
}
