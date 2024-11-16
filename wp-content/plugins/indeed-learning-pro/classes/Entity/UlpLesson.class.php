<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('UlpPostAbstract')){
	 require_once ULP_PATH . 'classes/Abstracts/UlpPostAbstract.class.php';
}
if (class_exists('UlpLesson')){
	 return;
}
class UlpLesson extends UlpPostAbstract{
	/**
	 * @var string
	 */
	protected $post_type = 'ulp_lesson';
	/**
	 * @var int
	 */
	protected $post_id = 0;
	/**
	 * @param int
	 */
	protected $uid = 0;
	/**
	 * @var object
	 */
	protected $post_main_data = null;
	/**
	 * @var array
	 */
	protected $post_metas = null;
	/**
	 * @var object
	 */
	protected $additional_infos = null;
	protected $courseId = 0;

	/**
	 * @param int
	 * @param bool
	 * @param int
	 * @return none
	 */
	public function __construct($input=0, $run_queries=TRUE, $courseId=0){
		$this->post_id = $input;
		$this->uid = ulp_get_current_user();
		$this->courseId = $courseId;
		if ($run_queries){
			$this->run_queries();
		}
	}

	/**
	 * @param none
	 * @return none
	 */
	public function run_queries(){
		global $wpdb;
		$posts = $wpdb->prefix . 'posts';
		$postmeta = $wpdb->prefix . 'postmeta';
		$this->post_main_data = get_post($this->post_id);
		$this->post_metas = $this->getAllMetasFromDb();
		$this->additional_infos = $this->getAllAdditionalInfos();
	}
	/**
	 * @param none
	 * @return array
	 */
	private function getAllMetasFromDb(){
		return DbUlp::getPostMetaGroup($this->post_id, 'lesson_special_settings');
	}
	/**
	 * @param none
	 * @return array
	 */
	private function getAllAdditionalInfos(){
		return array();
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function is_completed(){
		global $wpdb;
		if ($this->uid && $this->post_id){
			$table = $wpdb->prefix . 'ulp_activity';
			$q = $wpdb->prepare("SELECT id from $table WHERE uid=%d AND entity_id=%d AND action='complete_lesson' ", $this->uid, $this->post_id);
			$data = $wpdb->get_var($q);
			if ($data!=null){
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	 * @param none
	 * @return number
	 */
	public function RewardPoints(){
		return $this->post_metas['ulp_post_reward_points'];
	}
	/**
	 * @param none
	 * @return number
	 */
	public function Duration(){
		return $this->post_metas['ulp_lesson_duration'];
	}
	/**
	 * @param none
	 * @return string
	 */
	public function DurationType(){
		return $this->post_metas['ulp_lesson_duration_type'];
	}
	/**
	 * @param none
	 * @return string
	 */
	public function hasPreview(){
		return $this->post_metas['ulp_lesson_preview'];
	}
	/**
	 * @param none
	 * @return string
	 */
	public function showBackToCourseBttn(){
		return $this->post_metas['ulp_lesson_show_back_to_course_link'];
	}
	/**
	 * Return course for lesson.
	 * @param none
	 * @return int
	 */
	public function LessonParent(){
		return $this->GetParent();
	}

	public function isVideo()
	{
			return get_post_meta( $this->post_id, 'ulp_lesson_is_video' );
	}
}
