<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('UlpPostAbstract')){
	 require_once ULP_PATH . 'classes/Abstracts/UlpPostAbstract.class.php';
}
if (class_exists('UlpCourse')){
	 return;
}
class UlpCourse extends UlpPostAbstract{
	/**
	 * @var string
	 */
	protected $post_type = 'ulp_course';
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
	 * @var array
	 */
	protected $children_counts = array();
	/**
	 * @var string
	 */
	protected $feature_image = '';
	/**
	 * @var string
	 */
	protected $author_name = '';
	public $author_id				= null;
	/**
	 * @var string
	 */
	protected $author_image = '';
	protected $price = null;
	protected $is_enrolled = null;
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
		return DbUlp::getPostMetaGroup($this->post_id, 'course_special_settings');
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
	 * @return int
	 */
	public function TotalModules(){
		if (empty($this->children_counts)){

			$this->children_counts = $this->CountChildren();

		}

		return isset( $this->children_counts['total_modules'] ) ? $this->children_counts['total_modules'] : 0;

	}
	/**
	 * @param none
	 * @return int
	 */
	public function TotalLessons(){
		if (empty($this->children_counts)){
			$this->children_counts = $this->CountChildren();
		}

		return isset($this->children_counts['lessons']) ? $this->children_counts['lessons'] : 0;
	}
	/**
	 * @param none
	 * @return int
	 */
	public function TotalQuizes(){
		if (empty($this->children_counts)){
			$this->children_counts = $this->CountChildren();
		}
		return isset( $this->children_counts['quizes'] ) ? $this->children_counts['quizes'] : 0;
	}
	/**
	 * @param none
	 * @return int
	 */
	public function TotalStudents(){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$DbUserEntitiesRelations = new DbUserEntitiesRelations();
		return $DbUserEntitiesRelations->getCountUsersForCourse($this->post_id);
	}
	/**
	 * @param none
	 * @return float
	 */
	public function Rating(){
			require_once ULP_PATH . 'classes/Db/Db_Ulp_Course_Reviews.class.php';
			$Db_Ulp_Course_Reviews = new Db_Ulp_Course_Reviews();
			$value = $Db_Ulp_Course_Reviews->getRatingAverageForCourse($this->post_id);
			if ( $value !== null && $value !== false && $value !== '' ){
					return number_format( $value, 1 );
			}
			return 0;
	}

	public function RatingPercentages()
	{
		require_once ULP_PATH . 'classes/Db/Db_Ulp_Course_Reviews.class.php';
		$values = [
				1 => '0%',
				2 => '0%',
				3 => '0%',
				4 => '0%',
				5 => '0%',
		];
		$Db_Ulp_Course_Reviews = new Db_Ulp_Course_Reviews();
		$countAll = $Db_Ulp_Course_Reviews->countAllByCourse($this->post_id);
		if (empty($countAll)){
				return $values;
		}
		$data = $Db_Ulp_Course_Reviews->getCountsOfStarPossibleValues($this->post_id);
		if (empty($data)){
				return $values;
		}
		foreach ($data as $key=>$value){
				$values [$key] = round($value * 100 / $countAll, 0,PHP_ROUND_HALF_DOWN ) . '%';
		}
		return $values;
	}

	/**
	 * @param none
	 * @return float
	 */
	public function MaxEnrolledStudents(){
		return get_post_meta($this->post_id, 'ulp_course_max_students', TRUE);
	}

	/**
	 * @param none
	 * @return float
	 */
	public function PassingValue(){
		return get_post_meta($this->post_id, 'ulp_course_assessments_passing_value', TRUE);
	}
	/**
	 * @param none
	 * @return array
	 */
	public function CountChildren(){
		require_once ULP_PATH . 'classes/Db/DbCoursesModulesUlp.class.php';
		$DbCoursesModulesUlp = new DbCoursesModulesUlp();
		$modules = $DbCoursesModulesUlp->getAllModulesForCourse($this->post_id);

		$data['total_modules'] = count($modules);
		if ($data['total_modules']){
			$data['lessons'] = 0;
			$data['quizes'] = 0;
			require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
			$DbModuleItems = new DbModuleItems();
			foreach ($modules as $module){
				$data['lessons'] += $DbModuleItems->getCountModuleItems($module['module_id'], 'ulp_lesson');
				$data['quizes'] += $DbModuleItems->getCountModuleItems($module['module_id'], 'ulp_quiz');
			}
			return $data;
		}
		return FALSE;
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function IsEntrolled(){
		if ($this->is_enrolled===null){
				require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
				$DbUserEntitiesRelations = new DbUserEntitiesRelations();
				return $DbUserEntitiesRelations->isUserEnrolledOnCourse($this->uid, $this->post_id);
		}
		return $this->is_enrolled;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function EntrolledDate($print_time=TRUE){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$object = new DbUserEntitiesRelations();
		$time = $object->getRelationColValue($this->uid, $this->post_id, 'start_time');
		if ($time){
				return ulp_print_date_like_wp($time, $print_time);
		} else {
				return '';
		}
	}

	/**
	 * @param none
	 * @return string
	 */
	public function ExpireDate($print_time=TRUE){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$object = new DbUserEntitiesRelations();
		$time = $object->getRelationColValue($this->uid, $this->post_id, 'end_time');
		if ($time){
				return ulp_print_date_like_wp($time, $print_time);
		} else {
				return '';
		}
	}
		/**
		 * Wrapp function for "Access item(lesson or quiz) only if the previous is completed " course special option.
		 * Student ca
		 * @param none
		 * @return boolean
		 */
		 public function can_access_any_item(){
			 	if ($this->post_metas['ulp_course_access_item_only_if_prev']){
						return 0;
				}
				return 1;
		 }
		 public function FeatureImage(){
			 	if ($this->feature_image){
						return $this->feature_image;
				} else {
					$this->feature_image = DbUlp::getFeatImage($this->post_id);
					return $this->feature_image;
				}
		 }
		 public function Progress(){
			 	require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
				$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
			 	return $UsersCoursesActionsUlp->getProgress($this->uid, $this->post_id) . '%';
		 }
		 public function AuthorName(){
			 	if ($this->author_name) {
						return $this->author_name;
				} else {
						$this->author_id = DbUlp::getPostAuthor($this->post_id);
						$this->author_name = DbUlp::getUsernameByUID($this->author_id);
						return $this->author_name;
				}
		 }
		 public function AuthorImage(){
			 if ($this->author_image) {
					 return $this->author_image;
			 } else {
				     $this->author_id = DbUlp::getPostAuthor($this->post_id);
					 $this->author_image = DbUlp::getAuthorImage($this->author_id);
					 return $this->author_image;
			 }
		 }
		 public function AuthorID(){
			 	if ($this->author_id) {
						return $this->author_id;
				} else {
						$this->author_id = DbUlp::getPostAuthor($this->post_id);
						return $this->author_id;
				}
		 }
		 public function Price($raw=FALSE){

			 	if ($this->price===null){
						if ($this->post_metas['ulp_course_payment']==0){
								$this->price = esc_html__('Free', 'ulp');
						} else {
								$this->price = ulp_format_price($this->post_metas['ulp_course_price']);
						}
				}
				if ($raw){
					 return $this->post_metas['ulp_course_price'];
				}
				$this->price = apply_filters('ulp_filter_price_html', $this->price, $this->post_id);
				return $this->price;
		 }
		 public function IsFree(){
		 		return $this->post_metas['ulp_course_payment'];
		 }

		 public function user_can_entroll(){
					$object = new UlpOrder();
			 		$user_can_enroll = $object->got_access($this->uid, $this->post_id);
			 		if ($this->post_metas['ulp_course_payment']==0 || $this->post_metas['ulp_course_price']==0 || $user_can_enroll){
							return TRUE;
					}
					return FALSE;
		 }
		 public function IsFeatured(){
			 	return get_post_meta($this->post_id, 'ulp_course_featured', true);
		 }

		 public function RewardPoints(){
			 	if(isset($this->post_metas['ulp_post_reward_points']) && $this->post_metas['ulp_post_reward_points'] > 0){
						return $this->post_metas['ulp_post_reward_points'];
				}
				return FALSE;
		 }
		 /**
		  * @param none
			* @return array
			*/
		 public function Additional_Instructors(){
			 	$data = get_post_meta($this->post_id, 'ulp_additional_instructors', TRUE);
				if ($data){
						return explode(',', $data);
				}
				return FALSE;
		 }
		 public function IsCompleted(){
			 	/// todo
				 require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
				 $UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
				 return $UsersCoursesActionsUlp->IsCourseCompleted($this->uid, $this->post_id);
		 }
		public function CourseResult(){
			 	/// todo
				 require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
				 $UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
				 return $UsersCoursesActionsUlp->GetCourseResult($this->uid, $this->post_id);
		 }
		 public function Difficulty(){
			 	if (get_option('ulp_course_difficulty_enable') && !empty($this->post_metas ['ulp_course_difficulty'])){
						$difficulty = DbUlp::get_course_difficulty_types();
						return isset($difficulty[$this->post_metas ['ulp_course_difficulty']]) ? $difficulty[$this->post_metas ['ulp_course_difficulty']] : '';
				}
				return '';
		 }
		 public function CourseTimePeriod(){
			 if (get_option('ulp_course_time_period_enable') && !empty($this->post_metas ['ulp_course_time_period_duration']) && !empty($this->post_metas ['ulp_course_time_period_duration_type']) ){
						$type_of_time = ulp_get_time_types();
					  return $this->post_metas ['ulp_course_time_period_duration'] . ' ' . $type_of_time [$this->post_metas ['ulp_course_time_period_duration_type']];
			 }
			 return '';
		 }

		 public function ComingSoonDescription()
		 {
				 	$data = get_post_meta($this->post_id, 'ulp_course_coming_soon_message', TRUE);
					$data = stripslashes($data);
					$data = indeed_format_str_like_wp($data);
					return $data;
		 }

		 public function comingSoonEndTime()
		 {
				 $data = get_post_meta($this->post_id, 'ulp_course_coming_soon_end_time', TRUE);
				 return $data;
		 }

		 public function comingSoonShowCountdown()
		 {
				 $data = get_post_meta($this->post_id, 'ulp_course_coming_soon_show_count_down', TRUE);
				 return $data;
		 }

		public function PostStatus()
		{
				if (isset($this->post_main_data->post_status)){
						return $this->post_main_data->post_status;
				}
				return \DbUlp::getPostColumnByID($this->post_id, 'post_status');
		}
}
