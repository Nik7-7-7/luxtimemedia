<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('PublicInitUlp')){
	 return;
}
class PublicInitUlp{
	/**
	 * @var bool
	 */
	private $do_check = TRUE;
	/**
	 * @var bool
	 */
	private $access = TRUE;
	/**
	 * @var string
	 */
	private $url = '';
	/**
	 * @var int
	 */
	private $uid = 0;
	/**
	 * @var int
	 */
	private $post_id = 0;
	/**
	 * @var string
	 */
	private $post_type = '';
	/**
	 * @var array
	 */
	private $settings = array();
	/**
	 * @var bool
	 */
	private $is_plugin_post_type = FALSE;
	/**
	 * @var array
	 */
	private $plugin_post_types = array('ulp_lesson', 'ulp_quiz', 'ulp_question', 'ulp_certificate'); // 'ulp_course',
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$this->setTheSettings();
		$this->uid = ulp_get_current_user();
		if ( ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				$this->post_id = ulp_get_post_id_from_url($this->url);
		}
		$this->checkIfIsAdmin();
		$this->setPostType();
		$this->isPluginPostType();
		$this->doesUserCanSeeThisPost();
		$this->doRedirect();
		if ( ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ){
				$this->loadTemplate();
		}
	}
	/**
	 * @param none
	 * @return none
	 */
	private function setTheSettings(){
		$this->url = ULP_CURRENT_URI;
		$this->settings = array('redirect_to' => ''); ///must be dynamic
	}
	/**
	 * @param none
	 * @return none
	 */
	private function checkIfIsAdmin(){
		if (in_array('administrator', wp_get_current_user()->roles)){
			$this->do_check = FALSE; /// admin can view anything
		}
	}
	/**
	 * @param none
	 * @return none
	 */
	private function setPostType(){
		$this->post_type = DbUlp::getPostTypeById($this->post_id);
	}
	/**
	 * @param none
	 * @return none
	 */
	private function isPluginPostType(){
		if ($this->post_id && $this->post_type && in_array($this->post_type, $this->plugin_post_types) ){
			$this->is_plugin_post_type = TRUE;
		}
	}
	/**
	 * @param none
	 * @return none
	 */
	private function doesUserCanSeeThisPost(){
		if (!$this->do_check){
			 return;
		}
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$DbUserEntitiesRelations = new DbUserEntitiesRelations();
		if ($this->is_plugin_post_type){
			switch ($this->post_type){
				case 'ulp_lesson':
					$preview = get_post_meta($this->post_id, 'ulp_lesson_preview', TRUE);
					if ($preview){
						/// Preview
						$this->access = TRUE;
					} else {
						$course_id = DbUlp::getCoursesForQuizId($this->post_id);
						if (empty($course_id)){
								$this->access = FALSE;
						}
						foreach ($course_id as $obsject){
							if ($DbUserEntitiesRelations->userCanSeeCourse($this->uid, $obsject['course_id'])){
								$this->access = TRUE;
								break;
							} else {
								$this->access = FALSE;
							}
						}

					}
					break;
				case 'ulp_quiz':
				case 'ulp_question':
				case 'ulp_certificate':
					$course_id = DbUlp::getCoursesForQuizId($this->post_id);
					if (empty($course_id)){
							$this->access = FALSE;
					}
					foreach ($course_id as $obsject){
						if ($DbUserEntitiesRelations->userCanSeeCourse($this->uid, $obsject['course_id'])){
							$this->access = TRUE;
							break;
						} else {
							$this->access = FALSE;
						}
					}
					break;
				}
		}
	}
	/**
	 * Do redirect if user can't see this post
	 * @param none
	 * @return none
	 */
	private function doRedirect(){
			if (!$this->do_check){
				 return;
			}
			if (!$this->access){
					$redirect_to = get_option('ulp_default_redirect');
					if ($redirect_to>0){
							$url = get_permalink($redirect_to);
					}
					if (empty($url)){
							$url = get_home_url();
					}
					wp_safe_redirect($url);
					exit;
			}
			/// REDIRECT FROM USER PAGE if user is not student
			$user_profile_page = get_option('ulp_default_page_student_profile');
			if ($user_profile_page && $this->post_id==$user_profile_page){
					$uid = empty($this->uid) ? 0 : $this->uid;
					require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
					$DbUserEntitiesRelations = new DbUserEntitiesRelations();
					if (!$DbUserEntitiesRelations->is_user_student($uid)){
							$page_redirect_id = get_option('ulp_user_profile_redirect');
							if ($page_redirect_id && $page_redirect_id>-1){
									$target_permalink = get_permalink($page_redirect_id);
									if ($target_permalink){
											wp_safe_redirect($target_permalink);
											exit;
									}
							}
					}
			}
	}
	/**
	 * @param none
	 * @return none
	 */
	private function loadTemplate(){
			require_once ULP_PATH . 'classes/public/UlpLoadTemplates.class.php';
			$object = new UlpLoadTemplates($this->post_type, $this->post_id);
	}
}
