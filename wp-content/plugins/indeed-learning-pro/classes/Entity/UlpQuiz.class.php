<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('UlpPostAbstract')){
	 require_once ULP_PATH . 'classes/Abstracts/UlpPostAbstract.class.php';
}
if (class_exists('UlpQuiz')){
	 return;
}
class UlpQuiz extends UlpPostAbstract{
	/**
	 * @var string
	 */
	protected $post_type = 'ulp_quiz';
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
	/**
	 * @var float
	 */
	private $grade = NULL;
	/**
	 * @var boolean
	 */
	private $quiz_passed = NULL;
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
		
	}
	/**
	 * @param none
	 * @return array
	 */
	public function getAllMetasFromDb(){
		return DbUlp::getPostMetaGroup($this->post_id, 'quiz_special_settings');
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function is_passed(){
		if ($this->quiz_passed===NULL){
			require_once ULP_PATH . 'classes/public/UlpQuizActions.class.php';
			$UlpQuizActions = new UlpQuizActions();
			$UlpQuizActions->setUID($this->uid);
			$UlpQuizActions->setQID($this->post_id);
			$this->quiz_passed = $UlpQuizActions->getQuizPassedOrNot();
		}
		return $this->quiz_passed;
	}
	/**
	 * @param none
	 * @return number
	 */
	public function Grade(){
		if (null==$this->grade){
			$return = $this->grade = DbUlp::userGetQuizGrade($this->uid, $this->post_id);
		} else {
			$return = $this->grade;
		}
		if ($return!==FALSE && $return!==NULL){
			if ($this->post_metas['ulp_quiz_grade_type']=='percentage'){
				$return .= '%';
			} else {
					$return .= esc_html__(' points', 'ulp');
			}
		}
		return $return;
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
		return $this->post_metas['quiz_time'];
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function has_grade(){
		if (null==$this->grade){
			return $this->Grade();
		}
		return $this->grade;
	}
}
