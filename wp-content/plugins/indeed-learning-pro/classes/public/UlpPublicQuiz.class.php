<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpPublicQuiz')){
	 return;
}
class UlpPublicQuiz{
	/**
	 * @var string
	 */
	private $url = '';
	/**
	 * @var string
	 */
	private $clean_url = '';
	/**
	 * @var int
	 */
	private $quiz_id = 0;
	/**
	 * @var array
	 */
	private $metas = array();
	/**
	 * @var int
	 */
	private $current_question_id = 0;
	/**
	 * @var array
	 */
	private $quiz_questions = array();
	/**
	 * @var string
	 */
	private $content = '';
	/**
	 * @var string
	 */
	private $pagination = '';
	/**
	 * @var string
	 */
	private $total_questions = '';

	protected $courseId = 0;
	protected $questionQuerySlugName = 'ulp-question';

	public $cleam_url = null;

	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$this->courseId = $this->_setCourseId();
		$this->quiz_id = $this->setPostId();
		$this->questionQuerySlugName = $this->_setQuestionQuerySlugName();
		$this->url = ULP_CURRENT_URI;
		$this->cleam_url = $this->getCleanURL();
		$this->setMetas();
		$this->setCurrentQuestionIdFromURL();
		$this->setQuizQuestions();
		$this->setPagination();
		$this->setOutput();
	}
	/**
	 * @param none
	 * @return int
	 */
	private function setPostId(){
		global $post;
		return $post->ID;
	}

	public function getQuizId()
	{
			return $this->quiz_id;
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

	private function _setQuestionQuerySlugName()
	{
			$queryVarName = get_option('ulp_question_custom_query_var');
			if ($queryVarName){
					return $queryVarName;
			}
			return $this->questionQuerySlugName;
	}

	/**
	 * @param none
	 * @return none
	 */
	public function setQuizId($input=0){
		$this->quiz_id = $input;
	}
	/**
	 * @param none
	 * @return array
	 */
	private function setMetas(){
		$this->metas = DbUlp::getPostMetaGroup($this->quiz_id, 'quiz_special_settings', TRUE);
	}
	/**
	 * @param none
	 * @return none
	 */
	private function getCleanURL(){
		return remove_query_arg(array($this->questionQuerySlugName), $this->url);
	}

	/**
	 * @param none
	 * @return int
	 */
	public function getCourseId()
	{
				return $this->courseId;
	}

	/**
	 * -1 means test taken
	 * @param none
	 * @return none
	 */
	private function setCurrentQuestionIdFromURL(){
		if (!empty($_GET[$this->questionQuerySlugName])){
			$this->current_question_id = sanitize_textarea_field($_GET[$this->questionQuerySlugName]);
		} else {
			$this->current_question_id = get_query_var($this->questionQuerySlugName);
		}
	}
	/**
	 * @param none
	 * @return array
	 */
	private function setQuizQuestions(){
		$cookie_name = "quiz_" . $this->quiz_id . "_questions";
		if (!empty($_COOKIE[$cookie_name])){
				$cookie = json_decode(stripslashes($_COOKIE[$cookie_name]), TRUE);
		}
		if (!empty($cookie)){
			$this->quiz_questions = $cookie;
		} else {
			require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
			$object = new DbQuizQuestions();
			$this->quiz_questions = $object->getQuizQuestions($this->quiz_id);
			/// RANDOM ORDER??
			if (!empty($this->metas['ulp_quiz_display_questions_random'])){
				shuffle($this->quiz_questions);
			}
			/// store into COOKIE so we don't have to do this query every time
			$one_day = time() + 60 * 60 * 24; ///available one day should be enought
			$this->content .= ulpSetCookieViaJS($cookie_name, json_encode($this->quiz_questions), $one_day);
		}
	}
	private function getIndexOfCurrentQuestion(){
			$index = array_search($this->current_question_id, $this->quiz_questions);
			return $index + 1;
	}
	/**
	 * @param none
	 * @return string
	 */
	private function setOutput(){
		$uid = ulp_get_current_user();
		$quiz_title = $this->getTheTitle();
		/// QUIZ ACTIONS
		require_once ULP_PATH . 'classes/public/UlpQuizActions.class.php';
		$UlpQuizActions = new UlpQuizActions();
		$UlpQuizActions->setUID($uid);
		$UlpQuizActions->setQID($this->quiz_id);
		$UlpQuizActions->setQTitle($quiz_title);
		$UlpQuizActions->setMetas();
		$this->total_questions = count($this->quiz_questions);
		if (empty($this->current_question_id)){
			/// start quiz
			$this->content .= DbUlp::getPostContentByPostId($this->quiz_id);
			$grade = $UlpQuizActions->getGrade();
			if ($grade!==FALSE){
				if ($this->metas['ulp_quiz_grade_type']=='point'){
						$grade .= esc_html__(' points', 'ulp');
				} else {
						$grade .= ' %';
				}
				$quiz_passed = $UlpQuizActions->getQuizPassedOrNot();
				$this->content .= $this->quiz_result(array('grade' => $grade, 'quiz_passed' => $quiz_passed));
				if ($UlpQuizActions->canUserRunQuiz()){
					/// RETAKE
					$this->content .= $this->quiz_retake_button(array('quiz_id' => $this->quiz_id ));
				}
			} else {
				/// START QUIZ BUTTON
				$this->content .= $this->quiz_start_button( array('quiz_id' => $this->quiz_id) );
			}
		} else {


			if ($this->current_question_id>-1){
						add_filter( 'ulp_course_curriculum_show', [$this, 'stopShowingCurriculumSlider'], 1 , 1);
				/// display question
				require_once ULP_PATH . 'classes/public/UlpPrintQuestion.class.php';
				$UlpPrintQuestion = new UlpPrintQuestion($this->courseId);
				$UlpPrintQuestion->setNext(TRUE);
				$UlpPrintQuestion->setPrev(TRUE);
				$UlpPrintQuestion->setQuizWorkflow($this->metas['quiz_workflow']);
				$UlpPrintQuestion->setIsLastQuestion(0);
				$current_question_index_in_quiz = $this->getIndexOfCurrentQuestion();
				$UlpPrintQuestion->setLegend($current_question_index_in_quiz, $this->total_questions);
				$submit_quiz = FALSE;
				if ($this->is_first_question()){
					/// FIRST QUESTION
					$UlpQuizActions->startQuiz();
					$UlpPrintQuestion->setPrev(FALSE);
				}
				if ($this->is_final_question()){
					/// LAST QUESTION
					$submit_quiz = TRUE;
					$UlpPrintQuestion->setNext(FALSE);
					$UlpPrintQuestion->setIsLastQuestion(1);
				}
				$UlpPrintQuestion->setQuestionId($this->current_question_id);
				$UlpPrintQuestion->setQuizId($this->quiz_id);
				$seconds_remain = $this->calculateRemainTime();
				$this->content .= $this->quiz_countdown(array('seconds_remain' => $seconds_remain));
				$this->content .= $UlpPrintQuestion->getOutput();
				if ($submit_quiz){
					$this->content .= $this->quiz_submit_button(array('quiz_id' => $this->quiz_id));
				}
			} else {
				/// dispaly end of quiz
				$UlpQuizActions->setQuestions($this->quiz_questions);
				$grade = $UlpQuizActions->calculateGrade();
				$UlpQuizActions->writeGrade($grade);
				$quiz_passed = $UlpQuizActions->getQuizPassedOrNot();
				$UlpQuizActions->endQuiz();

				// reset quiz questions order
				$this->content .= $this->maybeResetQuestionsOrder();

				if ($this->metas['ulp_quiz_grade_type']=='point'){
						$grade .= esc_html__(' points', 'ulp');
				} else {
						$grade .= ' %';
				}
				$this->content .= DbUlp::getPostContentByPostId($this->quiz_id);
				$this->content .= $this->quiz_result(array('grade' => $grade, 'quiz_passed' => $quiz_passed));
				if ($UlpQuizActions->canUserRunQuiz()){
					$this->content .= $this->quiz_retake_button(array('quiz_id' => $this->quiz_id));
				}
			}
		}
	}

	public function stopShowingCurriculumSlider( $value=false )
	{
			return false;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function getTheContent(){
		return $this->content;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getTheTitle(){
		$title = DbUlp::getPostTitleByPostId($this->quiz_id);
		return $title;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function getRetakeMeta(){
		return $this->metas['retake_limit'];
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getRetakeAttempts(){

		$quiz_title = $this->getTheTitle();

		require_once ULP_PATH . 'classes/public/UlpQuizActions.class.php';
		$UlpQuizActions = new UlpQuizActions();
		$uid = ulp_get_current_user();
		$UlpQuizActions->setUID($uid);
		$UlpQuizActions->setQID($this->quiz_id);
		$UlpQuizActions->setQTitle($quiz_title);
		return $UlpQuizActions->getNumberOfAttempts();
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getQuizTime(){
		return $this->metas['quiz_time'];
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getQuizQuestionsCount(){
		return $this->total_questions;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getRewardPoints(){
		if(isset($this->metas['ulp_post_reward_points']))
			return $this->metas['ulp_post_reward_points'];
		return 0;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getGradeValue(){
		return $this->metas['ulp_quiz_grade_value'];
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getGradeType(){
		if ($this->metas['ulp_quiz_grade_type'] == 'percentage'){
			 return '%';
		}
		else{
			 return ' points';
		}
		return $this->metas['ulp_quiz_grade_type'];
	}
	/**
	 * @param none
	 * @return string
	 */
	private function setPagination(){
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getPagination(){
		return $this->pagination;
	}
	/**
	 * @param none
	 * @return bool
	 */
	private function is_first_question(){
		reset($this->quiz_questions);
		$first_question = key($this->quiz_questions);
		if ($this->quiz_questions[$first_question]==$this->current_question_id){
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * @param none
	 * @return bool
	 */
	private function is_final_question(){
		reset($this->quiz_questions);
		end($this->quiz_questions);
		$final_key = key($this->quiz_questions);
		if ($this->quiz_questions[$final_key]==$this->current_question_id){
			return TRUE;
		}
		return FALSE;
	}
	public function is_main_section(){
		if(empty($this->current_question_id) || $this->current_question_id < 0){
			 return TRUE;
		}
		return FALSE;
	}
	/**
	 * @param none
	 * @return int (seconds remain of quiz time)
	 */
	private function calculateRemainTime(){
		$uid = ulp_get_current_user();
		if ($uid){
			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			$quiz_start_time = $DbUserEntitiesRelations->getRelationColValue($uid, $this->quiz_id, 'start_time');
			$quiz_start_time = strtotime($quiz_start_time);
			$quiz_time = $this->metas['quiz_time'] * 60; /// in seconds
			$diff = $quiz_start_time + $quiz_time - time();
			return ($diff<0) ? 0 : $diff;
		}
		return 0;
	}
	/// SECTIONS
	/**
	 * @param array
	 * @return string
	 */
	private function quiz_start_button($data=array()){
		$data['courseId'] = $this->courseId;
		$view = new ViewUlp();
		$data ['can_run_quiz'] = true;
		/// check if have questions
		require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
		$object = new DbQuizQuestions();
		$questions = $object->getQuizQuestions($this->quiz_id);
		if (empty($questions)){
			$data ['can_run_quiz'] = false;
		}
		$template_file = ULP_PATH . 'views/templates/quiz/start_button.php';
		$basename = basename($template_file);
		$template_file = apply_filters('indeed_quiz-start_button', $template_file, $basename );
		$view->setTemplate($template_file);
		$view->setContentData($data);
		return $view->getOutput();
	}
	/**
	 * @param array
	 * @return string
	 */
	private function quiz_retake_button($data=array()){
		$data['courseId'] = $this->courseId;
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/quiz/retake_button.php';
		$basename = basename($template_file);
		$template_file = apply_filters('indeed_quiz-retake_button', $template_file, $basename );
		$view->setTemplate($template_file);
		$view->setContentData($data);
		return $view->getOutput();
	}
	/**
	 * @param array
	 * @return string
	 */
	private function quiz_submit_button($input=array()){
		$input['courseId'] = $this->courseId;
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/quiz/submit_button.php';
		$basename = basename($template_file);
		$template_file = apply_filters('indeed_quiz-submit_button', $template_file, $basename);
		$view->setTemplate($template_file);
		$view->setContentData($input);
		return $view->getOutput();
	}
	/**
	 * @param array
	 * @return string
	 */
	private function quiz_countdown($data=array()){
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/quiz/countdown.php';
		$basename = basename($template_file);
		$template_file = apply_filters('indeed_quiz-countdown', $template_file, $basename);
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
		$all_items = DbUlp::getAllItemsForCourse($this->courseId);
		$next_item = ulp_get_elem_from_array('next', $all_items, $this->quiz_id);
		$next_item = apply_filters( 'ulp_filter_navigation_items', $next_item, $this->courseId, $all_items, $this->quiz_id, $uid, 'next' );
		$prev_item = ulp_get_elem_from_array('prev', $all_items, $this->quiz_id);
		$prev_item = apply_filters( 'ulp_filter_navigation_items', $prev_item, $this->courseId, $all_items, $this->quiz_id, $uid, 'prev' );
		$next_permalink = FALSE;
		$prev_permalink = FALSE;
		$next_label = FALSE;
		$prev_label = FALSE;
		if ($next_item){
				$postType = DbUlp::getPostTypeById($next_item);
				if ($postType=='ulp_lesson'){
					$next_permalink = Ulp_Permalinks::getForLesson($next_item, $this->courseId);
				} else if ($postType=='ulp_quiz') {
						$next_permalink = Ulp_Permalinks::getForQuiz($next_item, $this->courseId);
				}
				$next_label = DbUlp::getPostTitleByPostId($next_item);
		}
		if ($prev_item){
				$postType = DbUlp::getPostTypeById($prev_item);
				if ($postType=='ulp_lesson'){
						$prev_permalink = Ulp_Permalinks::getForLesson($prev_item, $this->courseId);
				} else if ($postType=='ulp_quiz') {
						$prev_permalink = Ulp_Permalinks::getForQuiz($prev_item, $this->courseId);
				}
				$prev_label = DbUlp::getPostTitleByPostId($prev_item);
		}
		$data = array(
						'prev_url' => $prev_permalink,
						'prev_label' => $prev_label,
						'next_url' => $next_permalink,
						'next_label' => $next_label,
		);
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/quiz/navigation.php';
		$template_file = apply_filters('indeed_lesson-navigation', $template_file, basename($template_file));
		$view->setTemplate($template_file);
		$view->setContentData($data);
		return $view->getOutput();
	}
	/**
	 * @param string
	 * @return string
	 */
	private function quiz_result($input=''){
		$input['label'] = Ulp_Global_Settings::get('ulp_messages_quiz_result');
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/quiz/result.php';
		$basename = basename($template_file);
		$template_file = apply_filters('indeed_quiz-result', $template_file, $basename);
		$view->setTemplate($template_file);
		$view->setContentData($input);
		return $view->getOutput();
	}

	public function CoursePermalink(){
		if ($this->courseId==0){
			return '';
		}
		$view = new ViewUlp();
		$template_file = ULP_PATH . 'views/templates/sections/link.php';
		$template_file = apply_filters('indeed_templates_sections-link', $template_file, basename($template_file));
		$url = \Ulp_Permalinks::getForCourse($this->courseId);//get_permalink($this->courseId);
		$url = add_query_arg(['subtab' => 'curriculum'], $url);
		$data = array(
						'label' => get_the_title($this->courseId),
						'url' => $url,
						'target' => '',
						'class' => '',
						'id' => '',
		);
		$view->setTemplate($template_file);
		$view->setContentData($data);
		return $view->getOutput();
	}

	/**
	 * since version 3.6
	 * @param none
	 * @return string
	 */
	protected function maybeResetQuestionsOrder()
	{
			if ( empty( $this->quiz_id ) ){
					return '';
			}
			/// RANDOM ORDER??
			if (empty($this->metas['ulp_quiz_display_questions_random'])){
					return '';
			}
			require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
			$object = new DbQuizQuestions();
			$this->quiz_questions = $object->getQuizQuestions($this->quiz_id);
			if ( empty( $this->quiz_questions ) ){
					return '';
			}
			shuffle($this->quiz_questions);
			$one_day = time() + 60 * 60 * 24; ///available one day should be enought
			$cookie_name = "quiz_" . $this->quiz_id . "_questions";
			return ulpSetCookieViaJS($cookie_name, json_encode($this->quiz_questions), $one_day);
	}

}
