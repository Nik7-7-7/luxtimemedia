<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('ShortcodesUlp')){
	 return;
}
/**
When adding a new shortcode please add into getListShortcodes() too.
*/
class ShortcodesUlp{
	private $shortcodes = [];
	private $_notAvailable = [];

	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$this->shortcodes = array(
				'ulp-list-courses' => array(
					'what_can_do' => esc_html__('List all courses', 'ulp'),
					'function' => 'list_courses',
					'args' => '',
				),
				'ulp-enroll-course' => array(
					'what_can_do' => esc_html__('Link to enroll a course', 'ulp'),
					'args' => 'id (course id)',
					'function' => 'enroll_course',
				),
				'ulp-student-profile' => array(
					'what_can_do' => esc_html__('Student profile page', 'ulp'),
					'args' => '',
					'function' => 'student_profile',
				),
				'ulp-reward-points' => array(
					'what_can_do' => esc_html__('Return student reward points', 'ulp'),
					'args' => 'id (user id)',
					'function' => 'student_reward_points',
				),
				'ulp-become-instructor' => array(
					'what_can_do' => esc_html__('Button for become instructor', 'ulp'),
					'args' => '',
					'function' => 'become_instructor'
				),
				'ulp-gradebook' => array(
					'what_can_do' => esc_html__('Display Gradebook', 'ulp'),
					'args' => 'id(user id)',
					'function' => 'display_gradebook',
				),
				'ulp-finish-course-bttn' => [
						'what_can_do' => esc_html__('Finish course button', 'ulp'),
						'args' => 'course_id (Required)',
						'function' => 'display_finish_course_bttn',
				],
				'ulp_list_reviews' => array(
					'what_can_do' => esc_html__('Display Course Reviews', 'ulp'),
					'args' => 'course_id (Required)',
					'function' => 'list_reviews',
				),
				'ulp_review_form' => array(
					'what_can_do' => esc_html__('Display Course Review From', 'ulp'),
					'args' => 'course_id (Required)',
					'function' => 'course_review_form',
				),
				'ulp_list_notes' => array(
					'what_can_do' => esc_html__('List the notes', 'ulp'),
					'args' => 'course_id',
					'function' => 'ulp_list_notes',
				),
				'ulp_notes_form' => array(
					'what_can_do' => esc_html__('Display form Notes', 'ulp'),
					'args' => 'course_id (Required)',
					'function' => 'ulp_notes_form',
				),
				'ulp_list_badges' => [
										'what_can_do' => esc_html__('Display all badges', 'ulp'),
										'args' => '',
										'function' => 'list_all_badges',
				],
				'ulp_list_watch_list' => [
										'what_can_do' => esc_html__('List all watch list items', 'ulp'),
										'args' => '',
										'function' => 'list_watch_list',
				],
				'ulp_watch_list_bttn' => [
										'what_can_do' => esc_html__('Button for watch list items', 'ulp'),
										'args' => 'course_id (Required)',
										'function' => 'list_watch_bttn',
				],
				'ulp_buy_course' => [
										'what_can_do' => esc_html__('Button for buy course', 'ulp'),
										'args' => 'course_id (Course ID), payment_type ( type of payment : woo, ump or edd)',
										'function' => 'generate_buy_course_bttn',
				],
				'ulp_view_orders' => [
										'what_can_do' => esc_html__('List orders', 'ulp'),
										'args' => '',
										'function' => 'list_orders',
				],
				'ulp_checkout' => [
										'what_can_do' => esc_html__('Checkout for direct payment', 'ulp'),
										'args' => '',
										'function' => 'checkout',
				],
				'ulp-list-certificates' => [
										'what_can_do' => esc_html__('List all certificates', 'ulp'),
										'args' => '',
										'function' => 'list_certificates',
				],
				'ulp-grid-students' => [
										'what_can_do' => esc_html__('List students in grid', 'ulp'),
										'args' => '',
										'function' => 'grid_students',
				],
				'ulp-grid-courses' => [
										'what_can_do' => esc_html__('List courses in grid', 'ulp'),
										'args' => '',
										'function' => 'grid_courses',
				],
				'ulp_reviews_awesome_box' => [
										'what_can_do' => esc_html__('Show reviews rating average inside an awesome box', 'ulp'),
										'args' => ' course_id ',
										'function' => 'ulp_reviews_awesome_box',
				],
				'ulp_about_the_instructor' => [
					'what_can_do' => esc_html__('Show instructor details', 'ulp'),
					'args' => ' instructor_id ',
					'function' => 'ulp_about_instructor',
				],
				'ulp_students_also_bought' => [
						'what_can_do' => esc_html__('Student also bought magic feature', 'ulp'),
						'args' => 'course_id',
						'function' => 'ulp_students_also_bought',
				],
				'ulp-course-list-tags' => [
						'what_can_do' => esc_html__('List tags for course', 'ulp'),
						'args' => 'course_id',
						'function' => 'ulp_course_list_tags',
				],
				'ulp-more-courses-by' => [
						'what_can_do' => esc_html__('More courses by instructor', 'ulp'),
						'args' => 'course_id, instructor_id, limit',
						'function' => 'ulp_more_courses_by',
				],
				'ulp-list-announcements' => [
						'what_can_do' => esc_html__('List announcements', 'ulp'),
						'args' => 'course_id',
						'function' => 'ulp_list_announcements',
				],
				'ulp-list-qanda' => [
						'what_can_do' => esc_html__('List Q&A', 'ulp'),
						'args' => 'course_id',
						'function' => 'ulp_list_qanda',
				],
				'ulp-insert-qanda-form' => [
						'what_can_do' => esc_html__('Q&A Form', 'ulp'),
						'args' => 'course_id',
						'function' => 'insertQandAForm',
				],
				'ulp-qanda-searchbar' => [
						'what_can_do' => esc_html__('Q&A Search bar', 'ulp'),
						'args' => 'course_id',
						'function' => 'qandaSearchBar',
				],
				'ulp-course-curriculum' => [
						'what_can_do' => esc_html__('List curriculum for course', 'ulp'),
						'args' => 'course_id',
						'function' => 'ulpCourseCurriculum',
				],
				'ulp-instructor-dashboard' => [
						'what_can_do' => esc_html__('Public dashboard for instructor', 'ulp'),
						'args' => '',
						'function' => 'ulpInstructorDashboard',
				],
		);
		if (!get_option('ulp_course_reviews_enabled')){
				$this->_notAvailable[] = 'ulp_list_reviews';
				$this->_notAvailable[] = 'ulp_review_form';
		}
		if (!get_option('lesson_notes_enable')){
				$this->_notAvailable[] = 'ulp_list_notes';
				$this->_notAvailable[] = 'ulp_notes_form';
		}
		if (!get_option('ulp_student_badges_enable')){
				$this->_notAvailable[] = 'ulp_list_badges';
		}
		if (!get_option('ulp_gradebook_enable')){
				$this->_notAvailable[] = 'gradebook';
		}
		if (!get_option('ulp_watch_list_enable')){
				$this->_notAvailable[] = 'ulp_list_watch_list';
				$this->_notAvailable[] = 'ulp_watch_list_bttn';
		}
		if (!get_option('ulp_about_the_instructor_mf')){
				$this->_notAvailable[] = 'ulp_about_the_instructor';
		}
		if (!get_option('ulp_student_also_bought_enable')){
				$this->_notAvailable[] = 'ulp_students_also_bought';
		}
		foreach ($this->shortcodes as $shortcode=>$details){
				add_shortcode($shortcode, array($this, $details['function']));
		}
	}
		public function getShortcodes(){
				$shortcodes = $this->shortcodes;
				foreach ($shortcodes as $shortcodeName => $shortcodeData){
						if (isset($this->_notAvailable[$shortcodeName])){
								unset($shortcodes[$shortcodeName]);
						}
				}
				return $shortcodes;
		}
	/**
	 * LIST COURSES FOR VISITORS
	 * @param array
	 * @return string
	 */
	public function list_courses($params=array()){
		if (is_admin()){
			 return;
		}
		$uid = ulp_get_current_user();
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$DbUserEntitiesRelations = new DbUserEntitiesRelations();
		$data['excluded'] = array();

		$template = ULP_PATH . 'views/templates/list_courses.php';
		$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'list_courses.php' );

		if ($uid && $DbUserEntitiesRelations->is_user_student($uid) ){
			/// students
			$data['student_courses'] = $DbUserEntitiesRelations->get_user_courses($uid);
			$data['student_courses'] = DbUlp::getCoursesDetails($data['student_courses']);
		}
		require_once ULP_PATH . 'classes/IndeedPagination.class.php';
		$current_page = isset($_GET['ulp_page']) ? sanitize_text_field($_GET['ulp_page']) : 1;
		$per_page = 9;
		$total_items = DbUlp::countCourses();
		$current_url = remove_query_arg('ulp_page', ULP_CURRENT_URI);
		$pagination = new IndeedPagination(array(
				'base_url' => $current_url,
				'param_name' => 'ulp_page',
				'total_items' => $total_items,
				'items_per_page' => $per_page,
				'current_page' => $current_page,
		));
		$data['pagination'] = $pagination->output();
		$limit = $per_page;
		if ($current_page>1){
			$offset = ( $current_page - 1 ) * $per_page;
		} else {
			$offset = 0;
		}
		if ($offset + $limit>$total_items){
			$limit = $total_items - $offset;
		}
		/// all courses
		$data['courses'] = DbUlp::getAllCourses($limit, $offset);
		if (!empty($data['courses'])){
			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
		}
		return '';
	}
	/**
	 * @param array
	 * @return string
	 */
	public function enroll_course($attr=array()){
		if (is_admin()){
			 return;
		}
		if (isset($attr['id'])){
			/// check if already enrolled
			$uid = ulp_get_current_user();
			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			$data['already_enrolled'] = $DbUserEntitiesRelations->isUserEnrolledOnCourse($uid, $attr['id']) ? 1 : 0;
			if (empty($data['already_enrolled'])){
					// user is not enrolled
					require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
					$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
					$t = $UsersCoursesActionsUlp->UserCanEnrollCourse($uid, $attr['id']);
					$data['user_can_enroll'] = $t['do_it'];

					if (empty($data['user_can_enroll'])){
							/// return buy button ??

							// check maximum number of enrolled students before printing Button - extra check
							$current_students_count = $DbUserEntitiesRelations->getCountUsersForCourse($attr['id']);
							$maximumNumberOfSudents = get_post_meta( $attr['id'], 'ulp_course_max_students', true );
							if ($maximumNumberOfSudents<=$current_students_count){
									$data['user_can_enroll'] = false;
									$t['reason'] = 'ulp_messages_enroll_error_on_maximum_num_of_students';
							}

							if ($t['reason']=='ulp_enroll_error_user_didnt_pay_for_course'){
									return $this->generate_buy_course_bttn([
											'course_id' => $attr['id'],
											'payment_type' => DbUlp::getPaymentTypeForCourse($attr['id']),
									]);
							}
							$data['reason'] = get_option($t['reason']);
							if (empty($data['reason'])){
									$resons = DbUlp::getOptionMetaGroup('public_messages');
									if (isset($resons[$t['reason']])){
											$data['reason'] = $resons[$t['reason']];
									}
							}
					}
					unset($t);
			}
			///permalink to course (if enrolled)
			$data['course_permalink'] = \Ulp_Permalinks::getForCourse($attr['id']);//get_permalink($attr['id']);

			$template = ULP_PATH . 'views/templates/course/enroll.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'enroll.php' );

			$data['course_id'] = $attr['id'];
			$data['course_label'] = DbUlp::getPostTitleByPostId($attr['id']);
			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
		}
		return '';
	}
	public function student_profile($attr=array()){
		if (is_admin()){
			 return;
		}
			$uid = ulp_get_current_user();
			if ($uid){
					$data = array();
					require_once ULP_PATH . 'classes/public/Ulp_Student_Profile.class.php';
					$data ['Student_Profile'] = new Ulp_Student_Profile($uid);

					$template = ULP_PATH . 'views/templates/student_profile.php';
					$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'student_profile.php' );

	        $view = new ViewUlp();
	        $view->setTemplate($template);
	        $view->setContentData($data, true);
	        return $view->getOutput();
			}
			return '';
	}
	public function student_reward_points($attr=array()){
		if (is_admin()){
			 return;
		}
		$uid = empty($attr['id']) ? ulp_get_current_user() :
		require_once ULP_PATH . 'classes/Entity/UlpStudent.class.php';
		if ($uid){
			$object = new UlpStudent($uid);
			return $object->EarnedPoints();
		}
		return 0;
	}
	public function become_instructor($attr=array()){
		if (is_admin()){
			 return;
		}
		$data['uid'] = ulp_get_current_user();
		$data['instructor'] = DbUlp::isUserInstructor($data['uid']);

		$template = ULP_PATH . 'views/templates/become_instructor.php';
		$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'become_instructor.php' );

		$view = new ViewUlp();
		$view->setTemplate($template);
		$view->setContentData($data);
		return $view->getOutput();
	}
	public function display_gradebook($attr=array()){
		if (is_admin()){
			 return;
		}
			$uid = isset($attr['uid']) ? $attr['uid'] : ulp_get_current_user();
			$isActive = get_option('ulp_gradebook_enable', true );
			$data = new stdClass;
			if ($uid && $isActive ){
					$limit = 50;
					$offset = 0;
					$data->grades = DbUlp::get_grades($limit, $offset, $uid);

					$template = ULP_PATH . 'views/templates/gradebook.php';
					$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'gradebook.php' );

					$view = new ViewUlp();
					$view->setTemplate($template);
					$view->setContentData($data);
					return $view->getOutput();
			} else {
					return '';
			}
	}
	public function list_reviews($attr=array()){
			if (is_admin()){
   return;
}
			if (!empty($attr['course_id']) && get_option('ulp_course_reviews_enabled') ){
					$data = array();

					$template = ULP_PATH . 'views/templates/course_review_list.php';
					$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'course_review_list.php' );

					$data['single_course_review_template'] = ULP_PATH . 'views/templates/course_reviews/single_course_review.php';
					$data['single_course_review_template'] = apply_filters( 'ulp_filter_shortcodes_template', $data['single_course_review_template'], 'single_course_review.php');

					$view = new ViewUlp();

					require_once ULP_PATH . 'classes/Db/Db_Ulp_Course_Reviews.class.php';
					$Db_Ulp_Course_Reviews = new Db_Ulp_Course_Reviews();
					$data['items'] = $Db_Ulp_Course_Reviews->getAllByCourse($attr['course_id'], 10, 0);
					$data['post_slug'] = DbUlp::getPostNameById($attr['course_id']);
					$data['showMore'] = ($Db_Ulp_Course_Reviews->countAllByCourse($attr['course_id'])>10) ? true : false;
					$view->setTemplate($template);
					$view->setContentData($data);
					return $view->getOutput();
			}
			return '';
	}
	public function course_review_form($attr=array()){
			if (is_admin()){
   return;
}
			$uid = ulp_get_current_user();
			if ($uid && !empty($attr['course_id']) && get_option('ulp_course_reviews_enabled') ){
					require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
					$DbUserEntitiesRelations = new DbUserEntitiesRelations();
					$enrolled = $DbUserEntitiesRelations->isUserEnrolledOnCourse($uid, $attr['course_id']) ? 1 : 0;
					if ($enrolled){
							global $wpdb;
							$data = array();
							require_once ULP_PATH . 'classes/Db/Db_Ulp_Course_Reviews.class.php';
							$Db_Ulp_Course_Reviews = new Db_Ulp_Course_Reviews();
							if ($Db_Ulp_Course_Reviews->user_writed_course_review_for_course($uid, $attr['course_id']) && get_option('ulp_course_reviews_limit_one')){
									return '';
							}
							/// save
							if (isset($_POST['rating']) && isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' ) ){
									$_POST['message'] = str_replace( "\r\n", "<br/>", sanitize_textarea_field($_POST['message']) );
									$_POST['rating'] = sanitize_text_field( $_POST['rating'] );
									$_POST['title'] = sanitize_text_field( $_POST['title'] );
									$data['review_inserted'] = $Db_Ulp_Course_Reviews->addNew($attr['course_id'], $uid, sanitize_text_field($_POST['rating']), sanitize_textarea_field($_POST['title']), sanitize_textarea_field($_POST['message']), 'pending');
							}
							$data['course_id'] = $attr['course_id'];

							$template = ULP_PATH . 'views/templates/write_course_review.php';
							$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'write_course_review.php' );

							$view = new ViewUlp();
							$view->setTemplate($template);
							$view->setContentData($data);
							return $view->getOutput();
					}
			}
	}
	public function ulp_list_notes($attr=array()){

			/// todo pagination
			if (!get_option('lesson_notes_enable')){
					return '';
			}
			require_once ULP_PATH . 'classes/Db/Db_Ulp_Notes.class.php';
			$Db_Ulp_Notes = new Db_Ulp_Notes();
			$uid = ulp_get_current_user();
			$course_id = isset($attr['course_id']) ? $attr['course_id'] : 0;
			$data = [
					'items' => $Db_Ulp_Notes->selectAll($uid, $course_id)
			];
			$template = ULP_PATH . 'views/templates/list_notes.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'list_notes.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}
	public function ulp_notes_form($attr=array()){
			if (is_admin()){
   return;
}
			if (!get_option('lesson_notes_enable')){
					return '';
			}
			$data = [ 'course_id' => isset( $attr['course_id'] ) ? $attr['course_id'] : null ];
			$template = ULP_PATH . 'views/templates/notes_trigger.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'notes_trigger.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}
	public function display_finish_course_bttn($data = array() ){
			if (is_admin()){
   return;
}
			/// no course id set
			if (empty($data ['course_id'])){
				 return;
			}
			$uid = ulp_get_current_user();

			$check_retake = FALSE;
			/// course is completed
			require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
			$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
			if ($UsersCoursesActionsUlp->IsCourseCompleted($uid, $data ['course_id'])){
					$check_retake = TRUE;
			}

			$course_results = $UsersCoursesActionsUlp->GetCourseResult($uid, $data ['course_id']);
			if($course_results['grade'] > 0)
				$check_retake = TRUE;

			/// progress must be 100
			if (empty($data ['progress'])){
				require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
				$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
				$data ['progress'] = $UsersCoursesActionsUlp->getProgress($uid, $data ['course_id']);

				$courseCurrentResult = $UsersCoursesActionsUlp->CalculateCourseResult( $uid, $data ['course_id'] );
				$data['course_passed'] = isset( $courseCurrentResult['course_passed'] ) ? $courseCurrentResult['course_passed'] : 0;
			}


			if ( empty( $data['course_passed'] ) ){
				 return;
			}

			if($check_retake){
				$data['retake'] = $UsersCoursesActionsUlp->CourseRetakeCounts($uid, $data ['course_id']);
				if(count($data['retake']) > 0){
					$template = ULP_PATH . 'views/templates/course/retake_button.php';
					$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'retake_button.php' );

					$view = new ViewUlp();
					$view->setTemplate($template);
					$view->setContentData($data);
					return $view->getOutput();
				}else{
					return;
				}
			}
			$template = ULP_PATH . 'views/templates/course/finish_button.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'finish_button.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}
	public function list_all_badges($attr = array() ){
			if (is_admin()){
   return;
}
			$uid = ulp_get_current_user();
			require_once ULP_PATH . 'classes/Db/Db_Ulp_Student_Badges.class.php';
			$Db_Ulp_Student_Badges = new Db_Ulp_Student_Badges();
			$data ['items'] = $Db_Ulp_Student_Badges->getAllForUser($uid);
			if ($data ['items']){

					$template = ULP_PATH . 'views/templates/list_badges.php';
					$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'list_badges.php' );

					$view = new ViewUlp();
					$view->setTemplate($template);
					$view->setContentData($data);
					return $view->getOutput();
			}
	}
	public function list_watch_list(){
			if (is_admin()){
   			return;
			}
			if (!get_option('ulp_watch_list_enable')){
				 return;
			}
			$data ['uid'] = ulp_get_current_user();
			if (!$data ['uid']){
				 return;
			}
			require_once ULP_PATH . 'classes/public/Ulp_Watch_List.class.php';
			$Ulp_Watch_List = new Ulp_Watch_List();
			$data ['items'] = $Ulp_Watch_List->getAll($data ['uid'], TRUE);
			if ($data ['items']){
				foreach ($data['items'] as $course_id=>$object){
					$temp_object = new UlpCourse($course_id, FALSE);
					$data['items'][$course_id] += array (
					'number_of_students' => $temp_object->TotalStudents(),
					  'author_name' => $temp_object->AuthorName(),
					  'author_image' => $temp_object->AuthorImage(),
					  'feature_image' => $temp_object->FeatureImage(),
					  'is_featured' => $temp_object->IsFeatured(),
					  'total_modules' => $temp_object->TotalModules(),
					  'category' => $temp_object->Categories(TRUE),
					  'excerpt' => DbUlp::getPostExcerpt($course_id),
					  'create_date' => DbUlp::getPostCreateDate($course_id),
					);
				}
			}
			$template = ULP_PATH . 'views/templates/watch_list-the_list.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'watch_list-the_list.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}
	public function list_watch_bttn($data = array() ){
			if (is_admin()){
   return;
}
			if (!get_option('ulp_watch_list_enable')){
				 return;
			}
			if(!is_array($data)){
				 return;
			}

			$data['uid'] = ulp_get_current_user();
			if (!$data['uid']){
				 return;
			}

			if(isset($data['show']) && $data['show'] == 'wishpage'){
				$data ['watch_list_permalink'] = get_option('ulp_default_page_list_watch_list');
				if ($data ['watch_list_permalink']>-1){
						$data ['watch_list_permalink'] = get_permalink($data ['watch_list_permalink']);
				} else {
						$data ['watch_list_permalink'] = false;
				}
				return $data ['watch_list_permalink'];
			}

			if (empty($data['course_id'])){
				 return;
			}

			require_once ULP_PATH . 'classes/public/Ulp_Watch_List.class.php';
			$Ulp_Watch_List = new Ulp_Watch_List();
			$data ['is_on'] = $Ulp_Watch_List->user_got_course_as_fav($data ['uid'], $data['course_id']);

			$template = ULP_PATH . 'views/templates/watch_list_bttn.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'watch_list_bttn.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}
	public function generate_buy_course_bttn($data=array() ){
			if (is_admin()){
   return;
}
			if (empty($data['course_id'])){
				 return;
			}
			if (empty($data['payment_type'])){
				 $data['payment_type'] = get_option('ulp_default_payment_type');
			}
			if (empty($data['payment_type'])){
				 return;
			}

			if ($data['payment_type']=='ump' && function_exists('ihc_print_level_link')){
					$product_id = DbUlp::get_ump_product_id_by_course($data ['course_id']);

					return ihc_print_level_link(['id' => $product_id, 'item_class' => 'ulp-pay-bttn'], Ulp_Global_Settings::get('ulp_messages_buy_course_bttn'));
			}
			if ($data['payment_type']=='woo'){
				$product_id = DbUlp::get_woo_product_id_by_course($data ['course_id']);
				$data['link'] = do_shortcode('[add_to_cart_url id="'.$product_id.'"]');

				$template = ULP_PATH . 'views/templates/pay_course_bttn_woo.php';
				$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'pay_course_bttn_woo.php' );

				$view = new ViewUlp();
				$view->setTemplate($template);
				$view->setContentData($data);
				return $view->getOutput();

			}
			$template = ULP_PATH . 'views/templates/pay_course_bttn.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'pay_course_bttn.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}
	public function list_orders($attr = array() ){
			if (is_admin()){
   return;
}
			$uid = ulp_get_current_user();
			if (empty($uid)){
				 return;
			}
			$data ['orders'] = DbUlp::getOrdersByUser($uid);
			$data ['show_invoices'] = get_option('ulp_invoices_enable');
			$data ['ulp_invoices_only_completed_payments'] = get_option( 'ulp_invoices_only_completed_payments' );

			$template = ULP_PATH . 'views/templates/list_orders.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'list_orders.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}
	public function checkout(){
			if (is_admin()){
   return;
}
			$uid = ulp_get_current_user();
			if (empty($_GET['course_id'])){
				 return;
			}
			$_GET['course_id'] = sanitize_text_field( $_GET['course_id'] );
			$course_id = sanitize_text_field($_GET['course_id']);
			$payment_submit = empty($_POST['ulp_pay']) ? false : true;
			$payment_submit = apply_filters('ulp_payment_submited_filter_html', $payment_submit);
			$userNotLoggedErrorMessage = get_option('ulp_messages_checkout_user_not_logged');
			if (empty($userNotLoggedErrorMessage) && empty($uid)){
					$userNotLoggedErrorMessage = esc_html__('In order to complete this purchase you must be logged in.', 'ulp');
			}

			require_once ULP_PATH . 'classes/Payment_Services/Ulp_Payment_Services_Details.class.php';

			$template = ULP_PATH . 'views/templates/checkout.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'checkout.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData([
					'course_id' => $course_id,
					'course_label' => DbUlp::getPostTitleByPostId($course_id),
					'submited' => $payment_submit,
					'amount' => get_post_meta($course_id, 'ulp_course_price', true),
					'payment_types' => Ulp_Payment_Services_Details::get_all(),
					'uid' => $uid,
					'userNotLoggedErrorMessage' => $userNotLoggedErrorMessage
			]);
			return $view->getOutput();
	}
	public function list_certificates(){
		if (is_admin()){
   return;
}
		$uid = ulp_get_current_user();
		if (empty($uid)){
			 return;
		}
		require_once ULP_PATH . 'classes/Db/Db_User_Certificates.class.php';
		$Db_User_Certificates = new Db_User_Certificates();
		$data = [
				'items' => $Db_User_Certificates->getAllCertificatesForUser($uid)
		];
		$template = ULP_PATH . 'views/templates/list_certificates.php';
		$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'list_certificates.php' );

		$view = new ViewUlp();
		$view->setTemplate($template);
		$view->setContentData($data);
		return $view->getOutput();
	}
	public function grid_courses($attr=[]){
			if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX)){
				 return;
			}
			require_once ULP_PATH . 'classes/grid/Ulp_Grid_Courses.class.php';
			$Ulp_Grid_Courses = new Ulp_Grid_Courses($attr);
			return $Ulp_Grid_Courses->get_output();
	}
	public function grid_students($attr=[]){
			if (is_admin() && (!defined('DOING_AJAX') || !DOING_AJAX) ){
				 return;
			}
			require_once ULP_PATH . 'classes/grid/Ulp_Grid_Students.class.php';
			$Ulp_Grid_Students = new Ulp_Grid_Students($attr);
			return $Ulp_Grid_Students->get_output();
	}

	public function ulp_reviews_awesome_box($attr=[])
	{
			if (is_admin() || !get_option('ulp_course_reviews_enabled')){
   return;
}
			require_once ULP_PATH . 'classes/UlpReviewAwesomeBox.php';
			$UlpReviewAwesomeBox = new UlpReviewAwesomeBox($attr);
			return $UlpReviewAwesomeBox->output();
	}

	public function ulp_about_instructor($attr=[])
	{
			if (is_admin()){
   return;
}
			if (!get_option('ulp_about_the_instructor_mf')){
				 return;
			}
			require_once ULP_PATH . 'classes/public/Ulp_Instructor_About.php';
			$Ulp_Instructor_About = new Ulp_Instructor_About();
			return $Ulp_Instructor_About->setAttributes($attr)
							->output();
	}

	public function ulp_students_also_bought($attr=[])
	{
			if (is_admin()){
   return;
}
			if (empty($attr['course_id'])){
				 return;
			}
			if (!get_option('ulp_student_also_bought_enable')){
				 return;
			}
			$StudentsAlsoBought = new \Indeed\Ulp\PublicSection\StudentsAlsoBought($attr);
			return $StudentsAlsoBought->output();
	}

	public function ulp_course_list_tags($attr=[])
	{
			if (is_admin()){
   return;
}
			if (empty($attr['course_id'])){
				 return;
			}
			$DbCourseTags = new \Indeed\Ulp\Db\DbCourseTags();
			$data['items'] = $DbCourseTags->getAllByCourse($attr['course_id']);

			$template = ULP_PATH . 'views/templates/course/tags.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'tags.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}

	public function ulp_more_courses_by($attr=[])
	{
			if (is_admin()){
   return;
}
			if (!get_option('ulp_more_courses_by_enabled')){
				 return;
			}
			$excludePostId = 0;
			if (!empty($attr['course_id'])){
					$excludePostId = $attr['course_id'];
			}
			if (empty($attr['instructor_id']) && !empty($attr['course_id'])){
					$instructor = DbUlp::getPostAuthor($attr['course_id']);
			} else {
					$instructor = $attr['instructor_id'];
			}
			if (empty($instructor)){
				 return;
			}
			if (empty($attr['limit'])){
				 $attr['limit'] = 5;
			}
			$data['items'] = DbUlp::getAllCoursesForInstructor($instructor, $attr['limit'], $excludePostId);

			if (empty($data['items'])){
				 return;
			}
			$data['instructor_name'] = DbUlp::getUserFulltName($instructor);

			$template = ULP_PATH . 'views/templates/course/more_courses_by.php';
			$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'more_courses_by.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}

	public function ulp_list_announcements($attr=[])
	{
			if (is_admin()){
					return;
			}
			if (!get_option('ulp_announcements_enabled')){
					return;
			}
			if (empty($attr['course_id'])){
					return;
			}
			$uid = ulp_get_current_user();
			if (empty($uid)){
					return;
			}

			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			$isEnrolled = $DbUserEntitiesRelations->isUserEnrolledOnCourse($uid, $attr['course_id']);
			if (empty($isEnrolled)){
					return;
			}

			$data['limit'] = 10;
			$offset = 0;
			$object = new \Indeed\Ulp\Db\Announcements();
			$data['totalNumber'] = $object->countAllByCourse($attr['course_id'], 'publish');
			$data['announcements'] = $object->getAllForCourse($attr['course_id'], $data['limit'], $offset);
			$data['post_slug'] = DbUlp::getPostNameById($attr['course_id']);
			$data['showMore'] = $data['totalNumber']>$data['limit'] ? true : false;

			$template = ULP_PATH . 'views/templates/list_announcements.php';
			$template = apply_filters('ulp_filter_shortcodes_template', $template, 'list_announcements.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}

	public function ulp_list_qanda($attr=[])
	{
			$uid = ulp_get_current_user();
			$object = new \Indeed\Ulp\Db\QandA();
			if(!isset($attr['course_id'])){
				return;
			}
			if (!$object->doesStudentCanSeeQandaSection($uid, $attr['course_id'])){
					return;
			}

			$data['limit'] = 10;
			$offset = 0;
			$data['totalNumber'] = $object->countAllByCourse($attr['course_id'], 'publish');
			$data['items'] = $object->getAllForCourse($attr['course_id'], $data['limit'], $offset);
			$data['post_slug'] = DbUlp::getPostNameById($attr['course_id']);
			$data['showMore'] = $data['totalNumber']>$data['limit'] ? true : false;

			$template = ULP_PATH . 'views/templates/list_qanda.php';
			$template = apply_filters('ulp_filter_shortcodes_template', $template, 'list_qanda.php' );

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}

	public function insertQandAForm($attr=[])
	{
			$uid = ulp_get_current_user();
			$object = new \Indeed\Ulp\Db\QandA();
			if(!isset($attr['course_id'])){
				return;
			}
			if (!$object->doesStudentCanSeeQandaSection($uid, $attr['course_id'])){
					return;
			}

			$template = ULP_PATH . 'views/templates/course/qanda_form.php';
			$template = apply_filters('ulp_filter_shortcodes_template', $template, 'qanda_form.php' );
			$uri = ULP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$uri = remove_query_arg('subtab', $uri);
			$uri = add_query_arg('subtab', 'qanda', $uri);

			$data = [
					'course' 		=> DbUlp::getPostNameById($attr['course_id']),
					'courseId'  => $attr['course_id'],
					'uri'				=> $uri,
			];

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}

	public function qandaSearchBar($attr=[])
	{
			$uid = ulp_get_current_user();
			$object = new \Indeed\Ulp\Db\QandA();
			if(!isset($attr['course_id'])){
				return;
			}
			if (!$object->doesStudentCanSeeQandaSection($uid, $attr['course_id'])){
					return;
			}

			$template = ULP_PATH . 'views/templates/course/qanda_search_bar.php';
			$template = apply_filters('ulp_filter_shortcodes_template', $template, 'qanda_search_bar.php' );
			$data = [
					'course' 		=> DbUlp::getPostNameById($attr['course_id']),
			];

			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data);
			return $view->getOutput();
	}

	public function ulpCourseCurriculum($attr=[])
	{
			if (empty($attr['course_id'])){
					return;
			}
			$template = ULP_PATH . 'views/templates/course/curriculum.php';
			$template = apply_filters('ulp_filter_shortcodes_template', $template, 'curriculum.php' );

			$data = [
								'courseId' 	=> $attr['course_id'],
								'isTab' 		=> get_option('ulp_show_curriculum_as_tab') ? true : false
			];
			if ($data['isTab'] && empty($attr['force_print'])){
					///
					return;
			}
			$view = new ViewUlp();
			$view->setTemplate($template);
			$view->setContentData($data, true);
			return $view->getOutput();
	}

	public function ulpInstructorDashboard($attr=[])
	{
			$object = new \Indeed\Ulp\PublicSection\InstructorDashboard();
			return $object->getOutput();
	}

}
