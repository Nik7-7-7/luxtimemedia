<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpPublicLesson')){
	 return;
}
if (!class_exists('UlpPublicCustomPostType')){
	 require_once ULP_PATH . 'classes/Abstracts/UlpPublicCustomPostType.class.php';
}
class UlpPublicLesson extends UlpPublicCustomPostType{
	/**
	 * @var int
	 */
	protected $post_id = 0;
	/**
	 * @var int
	 */
	private $uid = 0;
	/**
	 * @var object
	 */
	private $activity_object = null;
	/**
	 * @var int
	 */
	private $course_id = 0;
	/**
	 * @var string
	 */
	private $feature_image = '';
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
		$this->activity_object = new DbActivityUlp();
		$this->uid = ulp_get_current_user();
		$this->setPostId();
		$this->course_id = $this->_setCourseId();//DbUlp::getCourseForItem($this->post_id);
		$this->setURL();
		$this->saveViewLeason();
	}

	private function _setCourseId()
	{
			global $wp_query;
			$queryVarName = get_option('ulp_course_custom_query_var');
			$courseSlug = isset($wp_query->query_vars[$queryVarName]) ? $wp_query->query_vars[$queryVarName] : '';
			if ($courseSlug){
					$courseId = DbUlp::getPostIdByTypeAndName('ulp_course', $courseSlug);
					return $courseId;
			}
			return 0;
	}

	public function getCourseId()
	{
			return $this->course_id;
	}

	public function getLessonId()
	{
			return $this->post_id;
	}

	/**
	 * insert into DB that User has view this lesson
	 * @param none
	 * @return none
	 */
	private function saveViewLeason(){
		if ($this->uid && $this->post_id){
			if (!$this->activity_object->getItem($this->uid, $this->post_id, 'view_lesson')){
				/// insert activity
				$time = date('Y-m-d H:i:s', time() );
				$this->activity_object->saveItem($this->uid, $this->post_id, 'ulp_lesson', 'view_lesson', '', $time, 1);
			}
		}
	}
	/**
	 * Generates button to complete Lesson
	 * @param none
	 * @return string
	 */
	public function CompleteButton(){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$DbUserEntitiesRelations = new DbUserEntitiesRelations();
		if (!$DbUserEntitiesRelations->userCanSeeCourse($this->uid, $this->course_id)){
				return;
		}

		$duration_value = get_post_meta($this->post_id, 'ulp_lesson_duration', TRUE);
		$duration_type = get_post_meta($this->post_id, 'ulp_lesson_duration_type', TRUE);

		$difference = ulp_get_seconds_by_time_value_and_type($duration_value, $duration_type);
		$lesson_start_time = strtotime($this->activity_object->getItemTime($this->uid, $this->post_id, 'view_lesson'));
		$now = time();
		$data = array(
						'is_completed' => $this->activity_object->is_lesson_completed($this->uid, $this->post_id),
						'lesson_id' => $this->post_id,
		);
		$data['seconds_remain'] = $lesson_start_time + $difference - $now;

		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/lesson/complete_button.php';
		$template_file = apply_filters('indeed_lesson-complete_button', $template_file, basename($template_file));
		$view->setTemplate($template_file);
		$view->setContentData($data);
		return $view->getOutput();
	}
	/**
	 * @param none
	 * @return string
	 */
	public function Navigation(){
		$uid = ulp_get_current_user();

		$all_items = DbUlp::getAllItemsForCourse($this->course_id);
		$next_item = ulp_get_elem_from_array('next', $all_items, $this->post_id);
		$next_item = apply_filters( 'ulp_filter_navigation_items', $next_item, $this->course_id, $all_items, $this->post_id, $uid, 'next' );
		$prev_item = ulp_get_elem_from_array('prev', $all_items, $this->post_id);
		$prev_item = apply_filters( 'ulp_filter_navigation_items', $prev_item, $this->course_id, $all_items, $this->post_id, $uid, 'prev' );

		$next_permalink = FALSE;
		$prev_permalink = FALSE;
		$next_label = FALSE;
		$prev_label = FALSE;
		if ($next_item){
				$postType = DbUlp::getPostTypeById($next_item);
				if ($postType=='ulp_lesson'){
					$next_permalink = Ulp_Permalinks::getForLesson($next_item, $this->course_id);
				} else if ($postType=='ulp_quiz') {
						$next_permalink = Ulp_Permalinks::getForQuiz($next_item, $this->course_id);
				}
				$next_label = DbUlp::getPostTitleByPostId($next_item);
		}
		if ($prev_item){
				$postType = DbUlp::getPostTypeById($prev_item);
				if ($postType=='ulp_lesson'){
						$prev_permalink = Ulp_Permalinks::getForLesson($prev_item, $this->course_id);
				} else if ($postType=='ulp_quiz') {
						$prev_permalink = Ulp_Permalinks::getForQuiz($prev_item, $this->course_id);
				}
				$prev_label = DbUlp::getPostTitleByPostId($prev_item);
		}
		$data = array(
						'is_completed' => $this->activity_object->is_lesson_completed($this->uid, $this->post_id),
						'prev_url' => $prev_permalink,
						'prev_label' => $prev_label,
						'next_url' => $next_permalink,
						'next_label' => $next_label,
		);
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/lesson/navigation.php';
		$template_file = apply_filters('indeed_lesson-navigation', $template_file, basename($template_file));
		$view->setTemplate($template_file);
		$view->setContentData($data);
		return $view->getOutput();
	}
	/**
	 * @param none
	 * @return string
	 */
	public function CoursePermalink(){
		$enable = get_post_meta($this->post_id, 'ulp_lesson_show_back_to_course_link', TRUE);
		if (!$enable || $this->course_id==0){
			return '';
		}
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/sections/link.php';
		$template_file = apply_filters('indeed_templates_sections-link', $template_file, basename($template_file));
		$url = \Ulp_Permalinks::getForCourse($this->course_id);//get_permalink($this->course_id);
		$url = add_query_arg(['subtab' => 'curriculum'], $url);
		$data = array(
						'label' => get_the_title($this->course_id),
						'url' => $url,
						'target' => '',
						'class' => '',
						'id' => '',
		);
		$view->setTemplate($template_file);
		$view->setContentData($data);
		return $view->getOutput();
	}
	public function FeatureImage(){
			if ($this->feature_image){
					return $this->feature_image;
			} else {
					$this->feature_image = DbUlp::getFeatImage($this->post_id);
				  return $this->feature_image;
			}
	}
}
