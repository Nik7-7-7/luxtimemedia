<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('AjaxUlp')){
	 return;
}

class AjaxUlp{

	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		/// course module
		add_action( 'wp_ajax_ulp_ajax_add_new_module_to_course', array($this, 'ulp_ajax_add_new_module_to_course'));
		add_action( 'wp_ajax_nopriv_ulp_ajax_add_new_module_to_course', array($this, 'ulp_ajax_add_new_module_to_course'));

		add_action( 'wp_ajax_ulp_ajax_get_all_course_modules', array($this, 'ulp_ajax_get_all_course_modules'));
		add_action( 'wp_ajax_nopriv_ulp_ajax_get_all_course_modules', array($this, 'ulp_ajax_get_all_course_modules'));

		add_action( 'wp_ajax_ulp_ajax_remove_module', array($this, 'ulp_ajax_remove_module'));

		/// Start Quiz
		add_action('wp_ajax_nopriv_ulp_start_quiz', array($this, 'ulp_start_quiz'));
		add_action('wp_ajax_ulp_start_quiz', array($this, 'ulp_start_quiz'));

		/// Next Question
		add_action('wp_ajax_nopriv_ulp_quiz_shift_question', array($this, 'ulp_quiz_shift_question'));
		add_action('wp_ajax_ulp_quiz_shift_question', array($this, 'ulp_quiz_shift_question'));

		/// Save Question Answer
		add_action('wp_ajax_nopriv_ulp_save_question_answer', array($this, 'ulp_save_question_answer'));
		add_action('wp_ajax_ulp_save_question_answer', array($this, 'ulp_save_question_answer'));

		/// Submit Quiz
		add_action('wp_ajax_nopriv_ulp_submit_quiz', array($this, 'ulp_submit_quiz'));
		add_action('wp_ajax_ulp_submit_quiz', array($this, 'ulp_submit_quiz'));

		/// Finish Course
		add_action('wp_ajax_nopriv_ulp_finish_course', array($this, 'ulp_finish_course'));
		add_action('wp_ajax_ulp_finish_course', array($this, 'ulp_finish_course'));

		/// Complete Lesson
		add_action('wp_ajax_nopriv_ulp_complete_lesson', array($this, 'ulp_complete_lesson'));
		add_action('wp_ajax_ulp_complete_lesson', array($this, 'ulp_complete_lesson'));

		/// Enroll Course
		add_action('wp_ajax_nopriv_ulp_do_enroll_course', array($this, 'ulp_do_enroll_course'));
		add_action('wp_ajax_ulp_do_enroll_course', array($this, 'ulp_do_enroll_course'));

		/// Retake Course
		add_action('wp_ajax_nopriv_ulp_do_retake_course', array($this, 'ulp_do_retake_course'));
		add_action('wp_ajax_ulp_do_retake_course', array($this, 'ulp_do_retake_course'));

		/// uap_check_mail_server
		add_action('wp_ajax_ulp_check_mail_server', array($this, 'ulp_check_mail_server'));

		/// ulp_get_notification_default_by_type
		add_action('wp_ajax_ulp_get_notification_default_by_type', array($this, 'ulp_get_notification_default_by_type'));

		/// ulp_question_return_correct_or_wrong
		add_action('wp_ajax_nopriv_ulp_question_return_correct_or_wrong', array($this, 'ulp_question_return_correct_or_wrong'));
		add_action('wp_ajax_ulp_question_return_correct_or_wrong', array($this, 'ulp_question_return_correct_or_wrong'));

		/// ulp_become_instructor_ajax
		add_action('wp_ajax_nopriv_ulp_become_instructor_ajax', array($this, 'ulp_become_instructor_ajax') );
		add_action('wp_ajax_ulp_become_instructor_ajax', array($this, 'ulp_become_instructor_ajax') );

		/// duplicate post
		add_action('wp_ajax_ulp_duplicate_post', array($this, 'ulp_duplicate_post'));

		/// note popup form
		add_action('wp_ajax_nopriv_ulp_get_note_popup', array($this, 'ulp_get_note_popup') );
		add_action('wp_ajax_ulp_get_note_popup', array($this, 'ulp_get_note_popup') );

		/// save note form
		add_action('wp_ajax_nopriv_ulp_save_note', array($this, 'ulp_save_note') );
		add_action('wp_ajax_ulp_save_note', array($this, 'ulp_save_note') );

		/// save note form
		add_action('wp_ajax_nopriv_ulp_delete_note', array($this, 'ulp_delete_note') );
		add_action('wp_ajax_ulp_delete_note', array($this, 'ulp_delete_note') );

		/// delete badge
		add_action('wp_ajax_nopriv_ulp_delete_badge', array($this, 'ulp_delete_badge') );
		add_action('wp_ajax_ulp_delete_badge', array($this, 'ulp_delete_badge') );

		/// add to watch list
		add_action('wp_ajax_nopriv_ulp_add_to_watch_list', array($this, 'ulp_add_to_watch_list') );
		add_action('wp_ajax_ulp_add_to_watch_list', array($this, 'ulp_add_to_watch_list') );

		/// remove from watch list
		add_action('wp_ajax_nopriv_ulp_remove_from_watch_list', array($this, 'ulp_remove_from_watch_list') );
		add_action('wp_ajax_ulp_remove_from_watch_list', array($this, 'ulp_remove_from_watch_list') );

		///buy course
		add_action('wp_ajax_nopriv_ulp_buy_course_via_standard_bttn', array($this, 'ulp_buy_course_via_standard_bttn') );
		add_action('wp_ajax_ulp_buy_course_via_standard_bttn', array($this, 'ulp_buy_course_via_standard_bttn') );

		add_action('wp_ajax_ulp_instructor_become_normal_user', array($this, 'ulp_instructor_become_normal_user') );

		add_action('wp_ajax_ulp_user_become_instructor', array($this, 'ulp_user_become_instructor') );

		add_action('wp_ajax_ulp_user_remove_course', array($this, 'ulp_user_remove_course') );

		add_action('wp_ajax_ulp_remove_instructor_from_course', array($this, 'ulp_remove_instructor_from_course') );

		add_action('wp_ajax_nopriv_ulp_get_invoice_popup', array($this, 'ulp_get_invoice_popup') );
		add_action('wp_ajax_ulp_get_invoice_popup', array($this, 'ulp_get_invoice_popup') );

		add_action('wp_ajax_ulp_admin_invoice_preview', array($this, 'ulp_admin_invoice_preview') );

		add_action('wp_ajax_ulp_autocomplete_users', [$this, 'ulp_autocomplete_users']);

		add_action('wp_ajax_nopriv_ulp_get_certificate_popup', [$this, 'ulp_get_certificate_popup']);
		add_action('wp_ajax_ulp_get_certificate_popup', [$this, 'ulp_get_certificate_popup']);

		add_action('wp_ajax_ulp_get_certificate_popup_for_admin', [$this, 'ulp_get_certificate_popup_for_admin']);

		add_action('wp_ajax_ulp_reset_points', [$this, 'ulp_reset_points']);

		add_action('wp_ajax_ulp_delete_custom_currency', array($this, 'ulp_delete_custom_currency'));

		add_action('wp_ajax_ulp_change_post_status', array($this, 'ulp_change_post_status'));

		add_action('wp_ajax_ulp_make_export_file', array($this, 'ulp_make_export_file'));

		add_action('wp_ajax_nopriv_ulp_get_stripe_payment_form', array($this, 'ulp_get_stripe_payment_form') );
		add_action('wp_ajax_ulp_get_stripe_payment_form', array($this, 'ulp_get_stripe_payment_form') );

		add_action('wp_ajax_ulp_delete_course_difficulty', array($this, 'ulp_delete_course_difficulty'));

		add_action('wp_ajax_ulp_ajax_do_shortcode', array($this, 'ulp_ajax_do_shortcode'));

		add_action('wp_ajax_ulp_ajax_edit_course_return_all_students_in_table', array($this, 'ulp_ajax_edit_course_return_all_students_in_table'));

		add_action('wp_ajax_ulp_ajax_edit_course_add_new_student', array($this, 'ulp_ajax_edit_course_add_new_student'));

		add_action('wp_ajax_ulp_ajax_admin_popup_shortcodes', array($this, 'ulp_ajax_admin_popup_shortcodes'));

		add_action('wp_ajax_ulp_get_font_awesome_popup', array($this, 'ulp_get_font_awesome_popup'));

		add_action('wp_ajax_ulp_user_remove_badge', [$this, 'ulp_user_remove_badge'] );

		add_action('wp_ajax_ulp_user_remove_certificate', [$this, 'ulp_user_remove_certificate'] );

		add_action('wp_ajax_nopriv_ulp_delete_attachment_ajax_action', array($this, 'ulp_delete_attachment_ajax_action'));
		add_action('wp_ajax_ulp_delete_attachment_ajax_action', array($this, 'ulp_delete_attachment_ajax_action'));

		add_action('wp_ajax_nopriv_ulp_get_more_course_reviews', array($this, 'ulp_get_more_course_reviews'));
		add_action('wp_ajax_ulp_get_more_course_reviews', array($this, 'ulp_get_more_course_reviews'));

		add_action('wp_ajax_ulp_delete_tag', [$this, 'ulp_delete_tag'] );

		add_action('wp_ajax_nopriv_ulp_ap_reset_custom_banner', [$this, 'ulp_ap_reset_custom_banner']);
		add_action('wp_ajax_ulp_ap_reset_custom_banner', [$this, 'ulp_ap_reset_custom_banner']);

		add_action('wp_ajax_nopriv_ulp_get_more_announcements', [$this, 'ulp_get_more_announcements']);
		add_action('wp_ajax_ulp_get_more_announcements', [$this, 'ulp_get_more_announcements']);

		add_action('wp_ajax_nopriv_ulp_get_more_qanda_items', [$this, 'ulp_get_more_qanda_items']);
		add_action('wp_ajax_ulp_get_more_qanda_items', [$this, 'ulp_get_more_qanda_items']);

		add_action('wp_ajax_nopriv_ulp_save_qanda_question', [$this, 'ulp_save_qanda_question']);
		add_action('wp_ajax_ulp_save_qanda_question', [$this, 'ulp_save_qanda_question']);

		add_action('wp_ajax_nopriv_ulp_search_qanda_question', [$this, 'ulp_search_qanda_question']);
		add_action('wp_ajax_ulp_search_qanda_question', [$this, 'ulp_search_qanda_question']);


		add_action('wp_ajax_ulp_admin_send_email_popup', [$this, 'ulp_admin_send_email_popup'] );
		add_action('wp_ajax_ulp_admin_do_send_email', [$this, 'ulp_admin_do_send_email'] );
		add_action( 'wp_ajax_ulp_ajax_do_delete_post', [$this, 'ulp_ajax_do_delete_post'] );

		add_action('wp_ajax_nopriv_ulp_instructor_delete_post', [$this, 'ulp_instructor_delete_post']);
		add_action('wp_ajax_ulp_instructor_delete_post', [$this, 'ulp_instructor_delete_post']);

		add_action('wp_ajax_nopriv_ulp_save_comment', [$this, 'ulp_save_comment']);
		add_action('wp_ajax_ulp_save_comment', [$this, 'ulp_save_comment']);

		add_action('wp_ajax_nopriv_ulp_delete_comment', [$this, 'ulp_delete_comment']);
		add_action('wp_ajax_ulp_delete_comment', [$this, 'ulp_delete_comment']);

		add_action('wp_ajax_nopriv_ulp_load_more_comments', [$this, 'ulp_load_more_comments']);
		add_action('wp_ajax_ulp_load_more_comments', [$this, 'ulp_load_more_comments']);

		add_action( 'wp_ajax_ulp_close_admin_notice', [ $this, 'ulp_close_admin_notice'] );

		//notification send test popup for admin
		add_action( 'wp_ajax_ulp_ajax_notification_send_test_email_popup', [ $this, 'ulp_ajax_notification_send_test_email_popup'] );
		// send test notification
		add_action( 'wp_ajax_ulp_ajax_do_send_notification_test', [ $this, 'ulp_ajax_do_send_notification_test'] );
	}


	/**
	 * @param none
	 * @return none
	 */
	public function ulp_ajax_add_new_module_to_course()
	{
			if ( isset( $_SERVER['HTTP_X_CSRF_ULP_TOKEN'] ) && ( indeedIsAdmin() || current_user_can( 'ulp_instructor_senior' ) || current_user_can( 'ulp_instructor' ) ) ){
					// public
					if ( !ulpPublicVerifyNonce() ){
						die;
					}
			} else if ( isset( $_SERVER['HTTP_X_CSRF_ULP_ADMIN_TOKEN'] ) && ( indeedIsAdmin() || current_user_can( 'ulp_instructor_senior' ) || current_user_can( 'ulp_instructor' ) ) ){
					// admin
					if ( !ulpAdminVerifyNonce() ){
						die;
					}
			} else {
					die;
			}
			require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
			$DbModuleItems = new DbModuleItems();
			$data['id'] = isset($_POST['last_id']) ? sanitize_text_field($_POST['last_id']) : 0;
			$data['id']++;
			$data['module_order'] = isset($_POST['last_order']) ? sanitize_text_field($_POST['last_order']) : 0;
			$data['module_order']++;
			$data['items'] = DbUlp::getAllQuizesAndLessons(true);
			$data['lessons_in'] = array();
			$data['quizes_in'] = array();
			$data['module_name'] = '';
			$data['new'] = 1;
			require_once ULP_PATH . 'views/admin/course_add_new_module.php';
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_ajax_get_all_course_modules()
	{
			if ( isset( $_SERVER['HTTP_X_CSRF_ULP_TOKEN'] ) && ( indeedIsAdmin() || current_user_can( 'ulp_instructor_senior' ) || current_user_can( 'ulp_instructor' ) ) ){
					// public
					if ( !ulpPublicVerifyNonce() ){
						die;
					}
			} else if ( isset( $_SERVER['HTTP_X_CSRF_ULP_ADMIN_TOKEN'] ) && ( indeedIsAdmin() || current_user_can( 'ulp_instructor_senior' ) || current_user_can( 'ulp_instructor' ) ) ){
					// admin
					if ( !ulpAdminVerifyNonce() ){
						die;
					}
			} else {
					die;
			}
			$output = '';
			if (isset($_POST['post_id'])){
				require_once ULP_PATH . 'classes/Db/DbCoursesModulesUlp.class.php';
				require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
				$DbCoursesModulesUlp = new DbCoursesModulesUlp();
				$DbModuleItems = new DbModuleItems();
				$data['modules'] = $DbCoursesModulesUlp->getAllModulesForCourse(sanitize_text_field($_POST['post_id']));
				$data['items'] = DbUlp::getAllQuizesAndLessons(true);

				if ($data['modules']){
					foreach ($data['modules'] as $array){
						$data['module_name'] = stripslashes($array['module_name']);
						$data['id'] = $array['module_id'];
						$data['module_order'] = $array['module_order'];
						$temp = $DbModuleItems->getAllModuleItemsByModuleId($array['module_id']);
						if ($temp){
								foreach ($temp as $temparray){
										$data['items_in'][$array['module_id']][] = $temparray['item_id'];
								}
						}

						$view = new ViewUlp();
						$view->setTemplate(ULP_PATH . 'views/admin/course_add_new_module.php');
						$view->setContentData($data);
						$output .= $view->getOutput();
						unset($data['lessons_in']);
						unset($data['quizes_in']);
					}
				}
			}
			echo esc_ulp_content($output);
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_start_quiz()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			$quiz_id = isset($_POST['qid']) ? sanitize_text_field($_POST['qid']) : 0; /// for best practice
			$courseId = isset($_POST['course_id']) ? sanitize_text_field($_POST['course_id']) : 0;
			if (!empty($quiz_id)){
				if (!empty($_COOKIE["quiz_" . $quiz_id. "_questions"])){
					$data = json_decode(stripslashes($_COOKIE["quiz_" . $quiz_id . "_questions"]), TRUE);
				}
				if (!empty($data)){
						$key = key($data);
						$questionId = $data[$key];
						$url = Ulp_Permalinks::getForQuestion($questionId, $quiz_id, $courseId);
						echo esc_url($url);
				} else {
						echo esc_html('');
				}
			}
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_quiz_shift_question()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			$quiz_id = isset($_POST['qid']) ? sanitize_text_field($_POST['qid']) : 0;
			$question_id = isset($_POST['question_id']) ? sanitize_text_field($_POST['question_id']) : 0;
			$courseId = isset($_POST['course_id']) ? sanitize_text_field($_POST['course_id']) : 0;

			if ( !empty($question_id) && !empty($quiz_id) && !empty($courseId)){
				//// GETTING QUESTIONS
				if (!empty($_COOKIE["quiz_" . $quiz_id . "_questions"])){
					$data = json_decode(stripslashes($_COOKIE["quiz_" . $quiz_id . "_questions"]), TRUE);
				}
				$current_key = array_search($question_id, $data);
				ulp_array_set_pointer($data, $current_key);
				if (sanitize_text_field($_POST['direction'])=='forward'){
					next($data); /// move forward
				} else {
					prev($data); /// go back one question
				}
				$key = key($data);
				$targetQuestionId = $data[$key];
				$url = Ulp_Permalinks::getForQuestion($targetQuestionId, $quiz_id, $courseId);

				echo esc_url($url);
			}
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_save_question_answer()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			$question_id = isset($_POST['question_id']) ? sanitize_text_field($_POST['question_id']) : 0;
			$quiz_id = isset($_POST['qid']) ? sanitize_text_field($_POST['qid']) : 0;
			$do_decode = isset($_POST['decode']) ? ulp_sanitize_array($_POST['decode']) : 0;
			$the_value = isset($_POST['the_value']) ? ulp_sanitize_array($_POST['the_value']) : '';

			if ( !empty($question_id) && !empty($quiz_id) ){
				$uid = ulp_get_current_user();
				if (!empty($do_decode)){
					$value = stripslashes($the_value);
					$value = json_decode($value, TRUE);
					$value = serialize($value);
				} else {
					$value = $the_value;
				}

				/// is matching type ?
				if (isset($_POST['the_questions'])){
						$dataToDb = [];
						$value = unserialize($value);
						$_POST['the_questions'] = ulp_sanitize_array( $_POST['the_questions'] );
						foreach ($_POST['the_questions'] as $key=>$question){
								$dataToDb[ ulpPrintStringIntoField( stripslashes ( $question ) ) ] = ulpPrintStringIntoField( stripslashes ( $value[$key] ) ) ; //
						}
						$value = $dataToDb;
						$value = serialize($value);
				}

				if ($uid){
					require_once ULP_PATH . 'classes/public/UlpQuestionActions.class.php';
					$UlpQuestionActions = new UlpQuestionActions();
					$UlpQuestionActions->setUID($uid);
					$UlpQuestionActions->setQuizID($quiz_id);
					$UlpQuestionActions->setQuestionID($question_id);
					$UlpQuestionActions->saveAnswer($value);
					echo 1;
				}
			}
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_submit_quiz()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			$quiz_id = isset($_POST['qid']) ? sanitize_text_field($_POST['qid']) : 0;
			$courseId = isset($_POST['course_id']) ? sanitize_text_field($_POST['course_id']) : 0;
			if (!empty($quiz_id) || empty($courseId)){
				$url = Ulp_Permalinks::getForQuestion(-1, $quiz_id, $courseId);
				echo esc_url($url);
			}
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_finish_course()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			require_once ULP_PATH . 'classes/Ulp_Finish_Course.class.php';
			$uid = ulp_get_current_user();
			$course_id = isset($_POST['course_id']) ? sanitize_text_field($_POST['course_id']) : 0;
			$object = new Ulp_Finish_Course($course_id, $uid);
			$object->run();
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_complete_lesson()
	{
			if ( !ulpPublicVerifyNonce() ){
				die;
			}
			$uid = ulp_get_current_user();
			$lesson_id = isset($_POST['lesson_id']) ? sanitize_text_field($_POST['lesson_id']) : 0;

			if ($uid && isset($lesson_id)){
				require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
				$DbActivityUlp = new DbActivityUlp();

				/// check time
				$lesson_title =	DbUlp::getPostTitleByPostId($lesson_id);
				$settings = DbUlp::getPostMetaGroup($lesson_id, 'lesson_special_settings');
				$difference = ulp_get_seconds_by_time_value_and_type($settings['ulp_lesson_duration'], $settings['ulp_lesson_duration_type']);
				$lesson_start_time = strtotime($DbActivityUlp->getItemTime($uid, $lesson_id, 'view_lesson'));
				$now = time();

				if ($lesson_start_time+$difference<$now){
					/// save activity
					$time = date('Y-m-d H:i:s', $now );
					$DbActivityUlp->saveItem($uid, $lesson_id, 'ulp_lesson', 'complete_lesson', '', $time, 1);

					/// pay reward points
					if (!empty($settings['ulp_post_reward_points'])){
						require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';
						$UlpRewardPoints = new UlpRewardPoints($uid);
						$UlpRewardPoints->add_points_to_user($settings['ulp_post_reward_points'], $lesson_id, 'complete_lesson');
					}

					do_action('ulp_user_has_completed_lesson', $uid, $lesson_title);

					echo 1;
					die;
				}
			}
			echo 0;
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function ulp_do_enroll_course()
	{
			if ( !ulpPublicVerifyNonce() ){
				die;
			}
			$uid = ulp_get_current_user();
			$course_id = isset($_POST['course_id']) ? sanitize_text_field($_POST['course_id']) : 0;
			if (empty($uid)){
					echo esc_ulp_content('<div class="ulp-course-enroll-message-danger">' . esc_html__('In order to enroll for this course, you must be logged in!', 'ulp') . '</div>');
					die;
			}
			if (empty($course_id)){
					echo esc_ulp_content('<div class="ulp-course-enroll-message-danger">' . esc_html__('Something went wrong, please try again later!', 'ulp') . '</div>');
					die;
			}
			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			if (!$DbUserEntitiesRelations->isUserEnrolledOnCourse($uid, $course_id)) { /// if user has not enrolled on this course yet
					require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
					$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
					$UsersCoursesActionsUlp->AppendCourse($uid, $course_id);
					echo esc_ulp_content('<div class="ulp-course-enroll-message-success">' . esc_html__("You're now Enrolled on this course!", 'ulp') . '</div>');
					die;
			}
			echo esc_ulp_content('<div class="ulp-course-enroll-message-danger">' . esc_html__('Something went wrong, please try again later!', 'ulp') . '</div>');
			die;
	}

		/**
	 * @param none
	 * @return string
	 */
	public function ulp_do_retake_course()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			$uid = ulp_get_current_user();
			$course_id = isset($_POST['course_id']) ? sanitize_text_field($_POST['course_id']) : 0;
			if (empty($uid)){
					echo esc_ulp_content('<div class="ulp-course-enroll-message-danger">' . esc_html__('In order to retake for this course, you must be logged in!', 'ulp') . '</div>');
					die;
			}
			if (empty($course_id)){
					echo esc_ulp_content('<div class="ulp-course-enroll-message-danger">' . esc_html__('Something went wrong, please try again later!', 'ulp') . '</div>');
					die;
			}
			require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
			$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
			$trytoappend = $UsersCoursesActionsUlp->AppendCourse($uid, $course_id);

			if ($trytoappend == 0) {
					echo esc_ulp_content('<div class="ulp-course-enroll-message-danger">' . esc_html__('Something went wrong, please try again later!', 'ulp') . '</div>');
					die;
			}else{
				$all_quizes = DbUlp::getAllCourseItems($course_id, 'ulp_quiz');

				if($all_quizes){
					require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
					$DbUserEntitiesRelations = new DbUserEntitiesRelations();
					foreach ($all_quizes as $quiz){
						$DbUserEntitiesRelations->deleteRelation($uid, $quiz['item_id']);
					}
				}

				require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
				$DbUserEntitiesRelations = new DbUserEntitiesRelations();
				$relation_id = $DbUserEntitiesRelations->getRelationColValue($uid, $course_id, 'id');
				$DbUserEntitiesRelationMetas = new DbUserEntitiesRelationMetas();
				$DbUserEntitiesRelationMetas->saveMeta($relation_id, 'course_grade', 0 ); /// reset the grade
				$DbUserEntitiesRelationMetas->saveMeta($relation_id, 'course_passed', 0);/// save the course as not passed
				die;
			}
			echo esc_ulp_content('<div class="ulp-course-enroll-message-success">' . esc_html__("You're now Enrolled on this course!", 'ulp') . '</div>');
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function ulp_check_mail_server()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !ulpAdminVerifyNonce() ){
					die;
			}
			$from_email = '';
			$from_name = '';
			$from_email = get_option('ulp_notification_email_from');
			if (empty($from_email)){
			 $from_email = get_option('admin_email');
			}
			$from_name = get_option('ulp_notification_name');
			if (empty($from_name)){
			 $from_name = get_option("blogname");
			}
			$headers[] = "From: $from_name <$from_email>";
			$headers[] = 'Content-Type: text/html; charset=UTF-8';

			$to = get_option('admin_email');
			$subject = get_option('blogname') . ': ' . esc_html__('Testing Your E-mail Server', 'ulp');
			$content = esc_html__('Just a simple message to test if Your E-mail Server is working', 'ulp');
			wp_mail($to, $subject, $content, $headers);
			echo 1;
			die;
	}


	/**
	 * @param none
	 * @return string
	 */
	 public function ulp_get_notification_default_by_type()
	 {
			  if ( !indeedIsAdmin() ){
					  die;
			  }
			  if ( !ulpAdminVerifyNonce() ){
					  die;
			  }
			 	if (!empty($_POST['type'])){
						require_once ULP_PATH . 'classes/Db/DbNotificationsUlp.class.php';
						$DbNotificationsUlp = new DbNotificationsUlp();
						$template = $DbNotificationsUlp->get_standard_by_type(sanitize_text_field($_POST['type']));

				 		if ($template){
					 		echo json_encode($template);
				 		}
			  }
			  die;
	 }

	 public function ulp_question_return_correct_or_wrong()
	 {
			  if ( !ulpPublicVerifyNonce() ){
				  	die;
				}
			  $question_id = isset($_POST['question_id']) ? sanitize_text_field($_POST['question_id']) : 0;
				$quiz_id = isset($_POST['qid']) ? sanitize_text_field($_POST['qid']) : 0;

			 	if ($question_id && $quiz_id){
					$uid = ulp_get_current_user();
					if ($uid){
						require_once ULP_PATH . 'classes/public/UlpQuestionActions.class.php';
						$UlpQuestionActions = new UlpQuestionActions();
						$UlpQuestionActions->setUID($uid);
						$UlpQuestionActions->setQuizID($quiz_id);
						$answers = $UlpQuestionActions->getQuestionAnswers();
						$is_correct = $UlpQuestionActions->isQuestionCorrect($question_id, $answers[$question_id]);
						if ($is_correct){
							echo 1;
							die;
						}
					}
				}
				echo 0;
				die;
	 }


	 public function ulp_become_instructor_ajax()
	 {
		 		if ( !ulpPublicVerifyNonce() ){
						die;
				}
			 	$uid = ulp_get_current_user();
				if ($uid){
						$sent = FALSE;
						$sent = apply_filters('admin_notification_user_become_instructor', $sent, $uid);	/// send notification to admin, ussing filter just to keep the things clean
								/// put pending
								DbUlp::set_role_for_user($uid, 'ulp_instructor-pending');
								echo 1;
								die;
				}
				echo 0;
				die;
	 }

	 public function ulp_duplicate_post()
	 {
		 		if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
			 	if (!empty($_POST['post_id'])){
					require_once ULP_PATH . 'classes/admin/Ulp_Duplicate_Posts.class.php';
					$object = new Ulp_Duplicate_Posts( sanitize_text_field($_POST['post_id']) );
					$object->Run();
				}
				echo 1;
				die;
	 }

	 public function ulp_get_note_popup()
	 {
			 if ( !ulpPublicVerifyNonce() ){
				 	die;
			 }
			 $data = array();
			 $location = locate_template('ultimate-learning-pro/notes_form_popup.php');
			 $template = empty($location) ? ULP_PATH . 'views/templates/notes_form_popup.php' : $location;
			 $view = new ViewUlp();
			 $view->setTemplate($template);
			 $view->setContentData($data);
			 echo esc_ulp_content($view->getOutput());
			 die;
	 }

	 public function ulp_save_note()
	 {
				if ( !ulpPublicVerifyNonce() ){
						die;
				}
			 	if (!empty($_POST['course_id']) && !empty($_POST['title']) && !empty($_POST['content'])){
						$_POST['course_id'] = sanitize_text_field($_POST['course_id']);
						$_POST['title'] = sanitize_textarea_field($_POST['title']);
						$_POST['content'] = sanitize_textarea_field($_POST['content']);
						require_once ULP_PATH . 'classes/Db/Db_Ulp_Notes.class.php';
						$uid = ulp_get_current_user();
						if ($uid){
								$Db_Ulp_Notes = new Db_Ulp_Notes();
								$Db_Ulp_Notes->save($uid, sanitize_text_field( $_POST['course_id'] ), sanitize_textarea_field( $_POST['title'] ), sanitize_textarea_field($_POST['content']) );
						}
				}
				die;
	 }

	 public function ulp_delete_note()
	 {
			  if ( !ulpPublicVerifyNonce() ){
					  die;
			  }
			 	if (empty($_POST['id'])){
					 return;
				}
				require_once ULP_PATH . 'classes/Db/Db_Ulp_Notes.class.php';
				$Db_Ulp_Notes = new Db_Ulp_Notes();
				$Db_Ulp_Notes->delete(sanitize_text_field($_POST['id']));
				die;
 	 }

	 public function ulp_delete_badge()
	 {
		 		if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
			 	if (!empty($_POST['id'])){
						require_once ULP_PATH . 'classes/Db/Db_Ulp_Badges.class.php';
						$Db_Ulp_Badges = new Db_Ulp_Badges();
						$Db_Ulp_Badges->delete( sanitize_text_field($_POST['id']) );
				}
				die;
	 }

	 public function ulp_add_to_watch_list()
	 {
				if ( !ulpPublicVerifyNonce() ){
						die;
			  }
			 	$uid = ulp_get_current_user();
				if ($uid){
						require_once ULP_PATH . 'classes/public/Ulp_Watch_List.class.php';
						$Ulp_Watch_List = new Ulp_Watch_List();
						$success = $Ulp_Watch_List->save($uid, sanitize_text_field($_POST['course_id']));
						if ($success){
								echo 1;
								die;
						}
				}
				echo 0;
				die;
	 }

	 public function ulp_remove_from_watch_list()
	 {
			  if ( !ulpPublicVerifyNonce() ){
					  die;
			  }
			  $uid = ulp_get_current_user();
			  if ($uid){
					  require_once ULP_PATH . 'classes/public/Ulp_Watch_List.class.php';
					  $Ulp_Watch_List = new Ulp_Watch_List();
					  $success = $Ulp_Watch_List->delete($uid, sanitize_text_field($_POST['course_id']));
					  if ($success){
							  echo 1;
							  die;
					  }
			  }
			  echo 0;
			  die;
	 }


	 public function ulp_buy_course_via_standard_bttn()
	 {
			  if ( !ulpPublicVerifyNonce() ){
					  die;
			  }
			 	if (empty($_POST['course_id']) && empty($_POST['payment_type'])){
						echo 1;
						die;
				}
				if ( empty(ulp_get_current_user()) ){
						// unregistered user ... do redirect
						$redirectTo = get_option( 'ulp_unregistered_user_try_to_buy_redirect' );
						$redirectLink = get_permalink( $redirectTo );
						if ( $redirectLink ){
								echo esc_url($redirectLink);
								die;
						}
				}
				switch ($_POST['payment_type']){
						case 'woo':
							$product_id = DbUlp::get_woo_product_id_by_course(sanitize_text_field($_POST['course_id']));
							if ($product_id){
									WC_Cart::add_to_cart($product_id, 1);
									$url = WC_Cart::get_checkout_url();
									if ($url){
											echo esc_url($url);
											die;
									}
							}
							break;
						case 'edd':
							$product_id = DbUlp::get_edd_product_id_by_course(sanitize_text_field($_POST['course_id']));
							if ($product_id){
									EDD()->cart->add( $product_id, array() );
									$url = edd_get_checkout_uri();
									if ($url){
											echo esc_url($url);
											die;
									}
							}
							break;
						case 'checkout': /// paypal, bt
							$checkout_page = get_option('ulp_default_page_checkout');
							if ($checkout_page>0){
									$url = get_permalink($checkout_page);
									$url = add_query_arg('course_id', sanitize_text_field($_POST['course_id']), $url);
									echo esc_url($url);
									die;
							}
							break;
				}
				echo 0;
				die;
	 }

	 public function ulp_instructor_become_normal_user()
	 {
				if ( !indeedIsAdmin() ){
					  die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
			 	if (empty($_POST['user_id'])){
					 return;
				}
	 		  $_POST['user_id'] = sanitize_text_field( $_POST['user_id'] );
				DbUlp::set_role_for_user( $_POST['user_id'], 'subscriber');
				die;
	 }

	 public function ulp_user_become_instructor()
	 {
			  if ( !indeedIsAdmin() ){
					  die;
			  }
			  if ( !ulpAdminVerifyNonce() ){
					  die;
			  }
			  if (empty($_POST['user_id'])){
					 return;
				}
			  $_POST['user_id'] = sanitize_text_field( $_POST['user_id'] );
			  DbUlp::set_role_for_user( $_POST['user_id'], 'ulp_instructor');
			  die;
	 }

	 public function ulp_user_remove_course()
	 {
			  if ( !indeedIsAdmin() ){
					  die;
			  }
			  if ( !ulpAdminVerifyNonce() ){
					  die;
			  }
			 	if (empty($_POST['user_id']) || empty($_POST['course_id'])){
					 return;
				}
				require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
				$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
				$UsersCoursesActionsUlp->RemoveCourse( sanitize_text_field($_POST['user_id']), sanitize_text_field($_POST['course_id']) );
				die;
	 }


	 public function ulp_remove_instructor_from_course()
	 {
			  if ( !indeedIsAdmin() ){
					  die;
			  }
			  if ( !ulpAdminVerifyNonce() ){
					  die;
			  }
		 		if (!empty($_POST['user_id']) && !empty($_POST['course_id'])){
						if (empty($_POST['is_additional_instructor'])){
								DbUlp::remove_instructor_from_instructor( sanitize_text_field($_POST['user_id']), sanitize_text_field($_POST['course_id']) );
						} else {
								DbUlp::remove_additional_instructor_from_course( sanitize_text_field($_POST['user_id']), sanitize_text_field($_POST['course_id']) );
						}
				}
				die;
	 }


	 public function ulp_get_invoice_popup()
	 {
			  if ( indeedIsAdmin() ){
						// admin check
						if ( !ulpAdminVerifyNonce() ){
						  	die;
						}
				} else {
						// public check
						if ( !ulpPublicVerifyNonce() ){
						 		die;
						}
				}
			 	if (empty($_POST['order_id'])){
					 return 0;
				}
				if (isset($_POST['uid'])){
						$uid = sanitize_text_field($_POST['uid']);
				} else {
						$uid = ulp_get_current_user();
						if (!DbUlp::user_got_this_order($uid, sanitize_text_field($_POST['order_id']))){
							 return 0;
						}
				}
				require_once ULP_PATH . 'classes/Ulp_Invoices.php';
				$Ulp_Invoices = new Ulp_Invoices($uid, sanitize_text_field($_POST['order_id']));
				echo esc_ulp_content($Ulp_Invoices->output());
				die;
	 }

	 	public function ulp_admin_invoice_preview()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				require_once ULP_PATH . 'classes/Ulp_Invoices.php';
				$Ulp_Invoices = new Ulp_Invoices(0, 0,  ulp_sanitize_array( $_POST['metas'] )  );
				echo esc_ulp_content($Ulp_Invoices->output());
				die;
	 	}

		public function ulp_autocomplete_users()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !isset($_GET['n']) || !wp_verify_nonce( $_GET['n'], 'ulpAdminNonce' ) ) {
						die;
				}

				// todo
				$return = array();
				$data = DbUlp::search_for_users(sanitize_text_field($_GET['term']));
				if ($data){
						foreach ($data as $key => $object){
								$return[$key]['label'] = $object->user_login;
								$return[$key]['id'] = $object->ID;
						}
				}
				echo json_encode($return);
				die;
		}

		public function ulp_get_certificate_popup()
		{
				if ( indeedIsAdmin() ){
						// admin check
						if ( !ulpAdminVerifyNonce() ){
								die;
						}
				} else {
						// public check
						if ( !ulpPublicVerifyNonce() ){
								die;
						}
				}
				$uid = ulp_get_current_user();
				if (empty($uid)){
					 return;
				}
				if (!empty($_POST['user_certificate_id'])){
						require_once ULP_PATH . 'classes/Ulp_Print_Certificate.class.php';
						$Ulp_Print_Certificate = new Ulp_Print_Certificate(0, sanitize_text_field($_POST['user_certificate_id']), $uid);
						echo esc_ulp_content($Ulp_Print_Certificate->getOutput());
						die;
				}
				die;
		}

		public function ulp_get_certificate_popup_for_admin()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (!empty($_POST['certificate_id'])){
						require_once ULP_PATH . 'classes/Ulp_Print_Certificate.class.php';
						$Ulp_Print_Certificate = new Ulp_Print_Certificate(sanitize_text_field($_POST['certificate_id']), 0, 0);
						echo esc_ulp_content($Ulp_Print_Certificate->getOutput());
						die;
				}
				die;
 		}

		public function ulp_reset_points()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (isset($_POST['user_id'])){
						require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';
						$UlpRewardPoints = new UlpRewardPoints(sanitize_text_field($_POST['user_id']));
						$UlpRewardPoints->reset();
				}
				die;
		}

		public function ulp_delete_custom_currency()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (isset($_POST['code'])){
						require_once ULP_PATH . 'classes/Db/Db_Custom_Currencies.class.php';
						$Db_Custom_Currencies = new Db_Custom_Currencies();
						$Db_Custom_Currencies->delete(sanitize_text_field($_POST['code']));
						echo 1;
				}
				die;
		}

		public function ulp_change_post_status()
		{
					if ( !indeedIsAdmin() ){
							die;
					}
					if ( !ulpAdminVerifyNonce() ){
							die;
					}
					if (isset($_POST['post_id']) && isset($_POST['post_status'])){
							DbUlp::change_post_status(sanitize_text_field($_POST['post_id']), sanitize_text_field($_POST['post_status']));
					}
					die;
		}


		public function ulp_make_export_file()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}

				global $wpdb;
				require_once ULP_PATH . 'classes/Import_Export/Indeed_Export.php';
				$export = new Indeed_Export();
				$export->setFile(ULP_PATH . 'export.xml');

				if (!empty($_POST['import_settings'])){
						///////// SETTINGS
						$values = DbUlp::get_all_ulp_db_options();
						$export->setEntity( array('full_table_name' => $wpdb->base_prefix . 'options', 'table_name' => 'options', 'values' => $values) );

						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_notifications', 'table_name' => 'ulp_notifications') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_dashboard_notifications', 'table_name' => 'ulp_dashboard_notifications') );

						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_badges', 'table_name' => 'ulp_badges') );
				}
				if (!empty($_POST['import_custom_post_types'])){
						$export->setCustomPostTypesEntity(TRUE);

						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_courses_modules', 'table_name' => 'ulp_courses_modules') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_courses_modules_metas', 'table_name' => 'ulp_courses_modules_metas') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_course_modules_items', 'table_name' => 'ulp_course_modules_items') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_quizes_questions', 'table_name' => 'ulp_quizes_questions') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_order_meta', 'table_name' => 'ulp_order_meta') );

				}
				if (!empty($_POST['import_students'])){
						$export->setStudentsEntity(TRUE);

						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_user_entities_relations', 'table_name' => 'ulp_user_entities_relations') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_user_entities_relations_metas', 'table_name' => 'ulp_user_entities_relations_metas') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_activity', 'table_name' => 'ulp_activity') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_reward_points', 'table_name' => 'ulp_reward_points') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_reward_points_details', 'table_name' => 'ulp_reward_points_details') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_student_badges', 'table_name' => 'ulp_student_badges') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_student_certificate', 'table_name' => 'ulp_student_certificate') );
						$export->setEntity( array('full_table_name' => $wpdb->prefix . 'ulp_notes', 'table_name' => 'ulp_notes') );
				}
				if (!empty($_POST['import_instructors'])){
						$export->setInstructorsEntity(TRUE);
				}
				if ($export->run()){
						echo ULP_URL . 'export.xml';
				} else {
						echo 0;
				}
				die;
		}


		public function ulp_get_stripe_payment_form()
		{
				if ( !ulpPublicVerifyNonce() ){
						die;
				}
				require_once ULP_PATH . 'classes/Payment_Services/Ulp_Stripe.class.php';
				$Ulp_Stripe = new Ulp_Stripe();
				echo esc_ulp_content($Ulp_Stripe->generate_payment_form());
				die;
		}

		public function ulp_delete_course_difficulty()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}

				if (!empty($_POST['slug'])){
						DbUlp::delete_course_difficulty_type( sanitize_textarea_field( $_POST['slug'] ) );
						echo 1;
				}
				die;
		}

		public function ulp_ajax_do_shortcode()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (isset($_POST['shortcode'])){
						$shortcode = stripslashes( sanitize_text_field( $_POST['shortcode'] ) );
						echo do_shortcode($shortcode);
				}
				die;
		}

		public function ulp_ajax_edit_course_return_all_students_in_table()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (!empty($_POST['post_id'])){
						require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
						$data['post_id'] = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : 0;
						$data['students'] = DbUlp::getStudents($data['post_id'], 99999, 0);
						$data['users_course_object'] = new UsersCoursesActionsUlp();
						$view = new ViewUlp();
						$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/edit_courses-list_students_table.php');
						$view->setContentData($data);
						echo esc_ulp_content($view->getOutput());
				}
				die;
		}

		public function ulp_ajax_edit_course_add_new_student()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (!empty($_POST['post_id']) && !empty($_POST['username'])){
					$uid = DbUlp::getUidByUsername( sanitize_text_field( $_POST['username'] ) );
					if ($uid){
							$post_id = sanitize_text_field( $_POST['post_id'] );
							require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
							$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
							$result = $UsersCoursesActionsUlp->AppendCourse($uid, $post_id, TRUE);
							if ($result){
								 echo esc_html($post_id);
							}
					}
				}
				die;
		}

		public function ulp_ajax_admin_popup_shortcodes()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/admin/popup-shortcodes.php');
				$view->setContentData([]);
				echo esc_ulp_content($view->getOutput());
				die;
		}

		public function ulp_get_font_awesome_popup()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/admin/popup-font_awesome.php');
				$view->setContentData([]);
				echo esc_ulp_content($view->getOutput());
				die;
		}

		public function ulp_user_remove_badge()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (!empty($_POST['user_id']) && !empty($_POST['badge_id'])){
						require_once ULP_PATH . 'classes/Db/Db_Ulp_Student_Badges.class.php';
						$user_badge_db_object = new Db_Ulp_Student_Badges();
						$user_badge_db_object->delete( sanitize_text_field($_POST['user_id']), sanitize_text_field($_POST['badge_id']) );
				}
		}

		public function ulp_user_remove_certificate()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !ulpAdminVerifyNonce() ){
						die;
				}
				if (!empty($_POST['user_id']) && !empty($_POST['certificate_id'])){
						require_once ULP_PATH . 'classes/Db/Db_User_Certificates.class.php';
						$user_certificate_db_object = new Db_User_Certificates();
						$user_certificate_db_object->delete( sanitize_text_field($_POST['user_id']), sanitize_text_field($_POST['certificate_id']) );
				}
		}

		/**
		 * @param none
		 * @return string
		 */
		public function ulp_delete_attachment_ajax_action()
		{
				if ( indeedIsAdmin() ){
					// admin check
					if ( !ulpAdminVerifyNonce() ){
						die;
					}
				} else {
					// public check
					if ( !ulpPublicVerifyNonce() ){
						die;
					}
				}

			 $uid = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : 0;
			 $field_name = isset($_POST['field_name']) ? sanitize_text_field($_POST['field_name']) : '';
			 $attachment_id = isset($_POST['attachemnt_id']) ? sanitize_text_field($_POST['attachemnt_id']) : 0;

			 if (function_exists('is_user_logged_in') && is_user_logged_in()){
					 $current_user = wp_get_current_user();
					 if ( !empty($uid) && $uid == $current_user->ID ){
							 /// registered users
							 if (!empty($attachment_id)){
									 $verify_attachment_id  = get_user_meta($uid, $field_name, TRUE);
									 if ($verify_attachment_id==$attachment_id){
											 wp_delete_attachment($attachment_id, TRUE);
											 update_user_meta($uid, $field_name, '');
											 echo 0;
											 die();
									 }
							 }
					 } else if (current_user_can('administrator')){
							/// ADMIN, no extra checks
							wp_delete_attachment($attachment_id, TRUE);
							update_user_meta($uid, $field_name, '');
					 }
			 } else if ($uid==-1){
					 /// unregistered user
					 $hash_from_user = isset($_POST['h']) ? sanitize_text_field($_POST['h']) : '';
					 $attachment_url = wp_get_attachment_url($attachment_id);
					 $attachment_hash = md5($attachment_url);
					 if (empty($hash_from_user) || empty($attachment_hash) || $hash_from_user!==$attachment_hash){
							 echo 1;die;
					 } else {
							 wp_delete_attachment($attachment_id, TRUE);
							 echo 0;die;
					 }
			 }

			 echo 1;
			 die();
	}

	public function ulp_get_more_course_reviews()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			if (empty($_POST['course']) || empty($_POST['hash'])){
					die;
			}
			if (md5($_POST['course'] . 'ulp_secret')!==sanitize_text_field( $_POST['hash'] ) ){
					die;
			}
			$courseId = DbUlp::getPostIdByName( sanitize_text_field( $_POST['course'] ) );
			if (empty($courseId)){
					die;
			}
			$offset = sanitize_text_field($_POST['offset']);
			$location = locate_template('ultimate-learning-pro/single_course_review.php');
			$template = empty($location) ? ULP_PATH . 'views/templates/course_reviews/single_course_review.php' : $location;
			$view = new ViewUlp();

			require_once ULP_PATH . 'classes/Db/Db_Ulp_Course_Reviews.class.php';
			$Db_Ulp_Course_Reviews = new Db_Ulp_Course_Reviews();
			$data['items'] = $Db_Ulp_Course_Reviews->getAllByCourse($courseId, 10, $offset);
			if (empty($data['items'])){
					die;
			}
			$output = '';
			foreach ($data['items'] as $object){
					$data = [
							'post_slug' => DbUlp::getPostNameById($attr['course_id']),
							'fullName' => $object->full_name,
							'authorImage' => $object->authorImage,
							'stars' => $object->stars,
							'createdTime' => $object->created_time,
							'title' => $object->title,
							'content' => $object->content,
					];
					$view->setTemplate($template);
					$view->setContentData($data, true);
					$output .= $view->getOutput();
			}
			echo esc_ulp_content($output);
			die;
	}

	public function ulp_delete_tag()
	{
			if (empty($_POST['termId'])){
				 return;
			}
			$DbCourseTags = new \Indeed\Ulp\Db\DbCourseTags();
			$DbCourseTags->delete(sanitize_text_field($_POST['termId']));
			echo 1;
			die;
	}

	public function ulp_ap_reset_custom_banner()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			global $current_user;
			$uid = isset($current_user->ID) ? $current_user->ID : 0;
			if (empty($uid)){
					die;
			}
			$banner = isset($_POST['oldBanner']) ? sanitize_text_field($_POST['oldBanner']) : '';
			if (empty($banner)){
					die;
			}
			update_user_meta($uid, 'ulp_account_page_personal_header', $banner);
			die;
	}

	public function ulp_get_more_announcements()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			if (empty($_POST['course']) || empty($_POST['hash'])){
					die;
			}
			if ( md5( sanitize_text_field( $_POST['course'] ) . 'ulp_secret')!==sanitize_text_field($_POST['hash']) ){
					die;
			}
			$courseId = DbUlp::getPostIdByName( sanitize_text_field($_POST['course']) );
			if (empty($courseId)){
					die;
			}

			$offset = sanitize_text_field($_POST['offset']);
			$limit = sanitize_text_field($_POST['limit']);
			$location = locate_template('ultimate-learning-pro/course/miniatures-single_announcement.php');
			$template = empty($location) ? ULP_PATH . 'views/templates/course/miniatures-single_announcement.php' : $location;

			$object = new \Indeed\Ulp\Db\Announcements();
			$data['announcements'] = $object->getAllForCourse($courseId, $limit, $offset);
			$view = new ViewUlp();
			$output = '';
			if (empty($data['announcements'])){
					die;
			}

			foreach ($data['announcements'] as $object){
					$view->setTemplate($template);
					$view->setContentData(['object' => $object], true);
					$output .= $view->getOutput();
			}

			echo esc_ulp_content($output);
			die;
	}

	public function ulp_get_more_qanda_items()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			if (empty($_POST['course']) || empty($_POST['hash'])){
					die;
			}
			if (md5( sanitize_text_field( $_POST['course'] ) . 'ulp_secret') !== sanitize_text_field($_POST['hash']) ){
					die;
			}
			$courseId = DbUlp::getPostIdByName( sanitize_text_field( $_POST['course'] ) );
			if (empty($courseId)){
					die;
			}
			$offset = sanitize_text_field($_POST['offset']);
			$offset = (int)$offset;
			$substring = empty($_POST['substring']) ? '' : sanitize_text_field($_POST['substring']);
			$limit = 10;
			$location = locate_template('ultimate-learning-pro/miniatures-single_qanda.php');
			$template = empty($location) ? ULP_PATH . 'views/templates/course/miniatures-single_qanda.php' : $location;
			$view = new ViewUlp();

			$object = new \Indeed\Ulp\Db\QandA();
			$questions = $object->getAllForCourse($courseId, $limit, $offset, $substring);
			if (empty($questions)){
					die;
			}
			$questionHtml = '';
			foreach ($questions as $object){
					$location = locate_template('ultimate-learning-pro/course/miniatures-single_qanda.php');
					$template = empty($location) ? ULP_PATH . 'views/templates/course/miniatures-single_qanda.php' : $location;
					$view = new ViewUlp();
					$view->setTemplate($template);
					$view->setContentData(['object' => $object], true);
					$questionHtml .= $view->getOutput();
			}
			echo esc_ulp_content($questionHtml);
			die;
	}

	public function ulp_save_qanda_question()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			global $current_user;
			$somethingWentWrong = json_encode([
						'status' => 'error',
						'message' => esc_html__('Something went wrong, please try again!', 'ulp')
			]);
			$uid = isset($current_user->ID) ? $current_user->ID : 0;

			$courseId = isset($_POST['courseId']) ? sanitize_text_field($_POST['courseId']) : 0;
			if (empty($courseId)){
					echo esc_ulp_content($somethingWentWrong);
					die;
			}
			if (empty($_POST['title']) || empty($_POST['content'])){
					echo json_encode([
								'status' => 'error',
								'message' => esc_html__('Please complete question title and description!', 'ulp')
					]);
					die;
			}

			///
			$courseId = DbUlp::getPostIdByName(sanitize_text_field($_POST['course']));
			///
			$object = new \Indeed\Ulp\Db\QandA();
			$_POST['content'] = str_replace( "\r\n", "<br/>", sanitize_textarea_field($_POST['content']) );
			$postId = $object->saveQuestion($uid, $courseId, sanitize_textarea_field($_POST['title']), sanitize_textarea_field($_POST['content']));

			if ( $postId ){
					do_action('ulp_student_ask_a_question', $uid, $courseId, $postId);
			}

			echo 1;die;
	}

	public function ulp_search_qanda_question()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			$nothingToReturn = json_encode([
					'status' 	=> 1,
					'html' 		=> esc_html__('No results', 'ulp'),
			]);
			if (empty($_POST['course']) || empty($_POST['hash'])){
					echo esc_ulp_content($nothingToReturn);
					die;
			}
			if ( md5( sanitize_text_field( $_POST['course']) . 'ulp_secret') !== sanitize_text_field( $_POST['hash'] ) ){
					echo esc_ulp_content($nothingToReturn);
					die;
			}
			$limit = 10;
			$offset = 0;
			$substring = empty($_POST['substring']) ? '' : sanitize_textarea_field($_POST['substring']);
			$courseId = DbUlp::getPostIdByName(sanitize_text_field($_POST['course']));
			$object = new \Indeed\Ulp\Db\QandA();
			$questions = $object->getAllForCourse($courseId, $limit, $offset, $substring);
			if (empty($questions)){
					echo esc_ulp_content($nothingToReturn);
					die;
			}
			$questionHtml = '';
			foreach ($questions as $object){
					$location = locate_template('ultimate-learning-pro/course/miniatures-single_qanda.php');
					$template = empty($location) ? ULP_PATH . 'views/templates/course/miniatures-single_qanda.php' : $location;
					$view = new ViewUlp();
					$view->setTemplate($template);
					$view->setContentData(['object' => $object], true);
					$questionHtml .= $view->getOutput();
			}
			echo json_encode([
					'status' 		=> 1,
					'html' 			=> $questionHtml,
			]);
			die;
	}

	public function ulp_admin_send_email_popup()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !ulpAdminVerifyNonce() ){
					die;
			}

			$uid = empty($_POST['uid']) ? 0 : sanitize_text_field( $_POST['uid'] );
			if (empty($uid)){
					die;
			}
			$toEmail = DbUlp::get_user_col_value($uid, 'user_email');
			if (empty($toEmail)){
					die;
			}
			$fromEmail = '';
			$fromEmail = get_option('ulp_notifications_from_email_addr');
			if (empty($fromEmail)){
		  		$fromEmail = get_option('admin_email');
			}
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/send_email_popup.php');
			$view->setContentData([
															'toEmail' 		=> $toEmail,
															'fromEmail' 	=> $fromEmail,
															'fullName'		=> DbUlp::getUserFulltName($uid),
															'website'			=> get_option('blogname')
			], true);
			echo esc_ulp_content($view->getOutput());
			die;
	}

	public function ulp_admin_do_send_email()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !ulpAdminVerifyNonce() ){
					die;
			}

			$to = empty($_POST['to']) ? '' : sanitize_text_field($_POST['to']);
			$from = empty($_POST['from']) ? '' : sanitize_text_field($_POST['from']);
			$subject = empty($_POST['subject']) ? '' : sanitize_textarea_field($_POST['subject']);
			$message = empty($_POST['message']) ? '' : stripslashes(htmlspecialchars_decode(ulp_format_str_like_wp( sanitize_textarea_field($_POST['message']) )));
			$headers = [];

			if (empty($to) || empty($from) || empty($subject) || empty($message)){
					die;
			}

			$from_name = get_option('ulp_notifications_from_name');
			$from_name = stripslashes($from_name);
			if (!empty($from) && !empty($from_name)){
				$headers[] = "From: $from_name <$from>";
			}
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$sent = wp_mail($to, $subject, $message, $headers);
			echo esc_ulp_content($sent);
			die;
	}

	public function ulp_ajax_do_delete_post()
	{
			if ( !indeedIsAdmin() && !current_user_can( 'ulp_instructor_senior' ) && current_user_can( 'ulp_instructor' )  ){
					die;
			}
			if ( !ulpAdminVerifyNonce() ){
					die;
			}

			$postId = isset($_POST['postId']) ? sanitize_text_field($_POST['postId']) : 0;
			if (empty($postId)){
					echo 0;
					die;
			}
			$postType = DbUlp::getPostTypeById($postId);
			if ($postType=='ulp_course'){
					require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
					$DbUserEntitiesRelations = new DbUserEntitiesRelations();
					$DbUserEntitiesRelations->deleteAllEntriesByEntity( $postId );
			}
			wp_delete_post( $postId, true );
			DbUlp::deleteAllPostMeta($postId);
			echo 1;
			die;
	}

	public function ulp_instructor_delete_post()
	{
			if ( indeedIsAdmin() ){
					// admin check
					if ( !ulpAdminVerifyNonce() ){
							die;
					}
			} else {
					// public check
					if ( !ulpPublicVerifyNonce() ){
							die;
					}
			}
			$postId =  isset($_POST['post']) ? sanitize_text_field($_POST['post']) : '';
			if (empty($postId)){
					echo 0;
					die;
			}
			$uid = ulp_get_current_user();
			if (empty($uid)){
					echo 0;
					die;
			}
			if (!\DbUlp::isUserAuhtorForPost($uid, $postId)){
					echo 0;
					die;
			}


			wp_delete_post($postId, true);
			\DbUlp::deleteAllPostMeta($postId);
			echo 1;
			die;
	}

	public function ulp_save_comment()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			if (empty($_POST['postId']) || empty($_POST['hash'])){
					die;
			}
			if ( md5( sanitize_text_field($_POST['postId']) . 'ulp_secret') !== sanitize_text_field($_POST['hash']) ){
					die;
			}
			$uid = ulp_get_current_user();
			if (!$uid){
					die;
			}
			if (empty($_POST['content'])){
					die;
			}
			$content = sanitize_textarea_field($_POST['content']);
			$postId = sanitize_text_field($_POST['postId']);

			$data = [
							'comment_author' 				=> \DbUlp::getUsernameByUID($uid),
							'comment_author_email' 	=> \DbUlp::get_user_col_value($uid, 'user_email'),
							'comment_post_ID' 			=> $postId,
							'comment_content' 			=> $content,
							'comment_parent' 				=> 0,
							'user_id' 							=> $uid,
			];
			wp_insert_comment($data);
			echo 1;
			die;
	}

	public function ulp_delete_comment()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			$uid = ulp_get_current_user();
			if (empty($uid)){
					echo 0;
					die;
			}
			if (!\DbUlp::isUserInstructor($uid)){
					echo 0;
					die;
			}
			$commentId = isset($_POST['comment']) ? sanitize_textarea_field($_POST['comment']) : 0;
			wp_delete_comment($commentId, true);
			echo 1;
			die;
	}

	public function ulp_load_more_comments()
	{
			if ( !ulpPublicVerifyNonce() ){
					die;
			}
			if (empty($_POST['postId']) || empty($_POST['hash'])){
					die;
			}
			if ( md5( sanitize_text_field($_POST['postId']) . 'ulp_secret') !== sanitize_text_field($_POST['hash']) ){
					die;
			}
			$postId = sanitize_text_field($_POST['postId']);
			$offset = sanitize_text_field($_POST['offset']);
			$limit = sanitize_text_field($_POST['limit']);

			$commentsObject = new \Indeed\Ulp\Db\Comments();
			$comments = $commentsObject->getForPost($postId, $limit, $offset);

			if (empty($comments)){
					die;
			}

			$location = locate_template('ultimate-learning-pro/instructor_dashboard/miniatures-single_comment.php');
			$template = empty($location) ? ULP_PATH . 'views/templates/instructor_dashboard/miniatures-single_comment.php' : $location;

			$html = '';
			foreach ($comments as $comment){
					$view = new ViewUlp();
					$view->setTemplate($template);
					$view->setContentData(['comment' => $comment], true);
					$html .= $view->getOutput();
			}
			echo esc_ulp_content($html);
			die;
	}

	public function ulp_close_admin_notice()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !ulpAdminVerifyNonce() ){
					die;
			}

			update_option( 'ulp_hide_admin_license_notice', 1 );
			echo 1;
			die;
	}

	public function ulp_ajax_notification_send_test_email_popup()
	{

		if ( !indeedIsAdmin() ){

				die;
		}
		if ( !ulpAdminVerifyNonce() ){

				die;
		}

		require_once ULP_PATH . 'views/admin/notification-email-send-test.php';
		die;

	}

	public function ulp_ajax_do_send_notification_test()
	{
		if ( !indeedIsAdmin() ){
				echo 0;
				die;
		}
		if ( !ulpAdminVerifyNonce() ){
			 echo 0;
			 die;
		}
		$notificationId = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : 0;
		$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
		ulpSendTestNotification( $notificationId, $email );
		echo 1;
		die;
	}


}
