<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('DbUserEntitiesRelations')){
	 require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
}
if (class_exists('UsersCoursesActionsUlp')){
	 return;
}
class UsersCoursesActionsUlp extends DbUserEntitiesRelations{
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @param boolean (use this param only when insert from admin section)
	 * @return int (relation id)
	 */
	public function AppendCourse($uid=0, $course_id=0, $force_it=FALSE){
		if ($uid && $course_id){
			$reponse['do_it'] = TRUE;
			$settings = DbUlp::getPostMetaGroup($course_id, 'course_special_settings');
			$reponse = $this->UserCanEnrollCourse($uid, $course_id, $settings);
			$reponse['do_it'] = apply_filters('ulp_do_append_course_to_user', $reponse['do_it'], $uid, $course_id);
			if ($reponse['do_it'] || $force_it){
				$now = time();
				/// start time
				$start_time = date('Y-m-d H:i:s', $now);
				///end
				if (empty($settings['ulp_course_duration'])){
					/// 48 weeks
					$settings['ulp_course_duration'] = 48;
					$settings['ulp_course_duration_type'] = 'w';
				}
				$time_diff = ulp_get_seconds_by_time_value_and_type($settings['ulp_course_duration'], $settings['ulp_course_duration_type']);
				$end_time = $now + $time_diff;
				$end_time = date('Y-m-d H:i:s', $end_time);
				/// make order used
				require_once ULP_PATH . 'classes/Entity/UlpOrder.class.php';
				$UlpOrder = new UlpOrder();
				$order_id = $UlpOrder->user_got_order_unused_for_course($uid, $course_id);
				if ($order_id){
						$UlpOrder->make_order_used($order_id);
				}
				do_action('ulp_user_do_enroll', $uid, $course_id);
				return $this->do_Insert($uid, $course_id, 'ulp_course', $start_time, $end_time, 1); /// this must be every time insert (re-rake course etc)
			}
		}
		return 0;
	}
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @param array (metas)
	 * @return
	 */
	public function UserCanEnrollCourse($uid=0, $course_id=0, $settings=array()){
			if (empty($settings)){
					$settings = DbUlp::getPostMetaGroup($course_id, 'course_special_settings');
			}
			$response = array('do_it' => TRUE,
			 									'reason' => '');
			if (empty($uid)){
				$response = array('do_it' => FALSE,
				 									'reason' => 'ulp_messages_enroll_error_user_not_logged',
				);
			}
			/// user already is enrolled
			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			if ($DbUserEntitiesRelations->isUserEnrolledOnCourse($uid, $course_id)){
				/// Re-Take Course
				$past_enroll = $DbUserEntitiesRelations->how_many_times_user_enroll_course($uid, $course_id);
				if (!empty($past_enroll) && !empty($settings['ulp_course_retake_course']) && $past_enroll>=$settings['ulp_course_retake_course']){
					$response = array(
														'do_it' => FALSE,
														'reason' => 'ulp_messages_enroll_error_retake_course_limit',
					);
				}
			}
			///does user has pay for this course
			$course_payment = get_post_meta($course_id, 'ulp_course_payment', TRUE);
			if ($course_payment==1 && get_post_meta($course_id, 'ulp_course_price', TRUE)){
					///
					require_once ULP_PATH . 'classes/Entity/UlpOrder.class.php';
					$UlpOrder = new UlpOrder();
					$got_order = $UlpOrder->user_got_order_unused_for_course($uid, $course_id);
					if (!$got_order){
								$response = array('do_it' => FALSE,
																	'reason' => 'ulp_enroll_error_user_didnt_pay_for_course', /// message exists
								);
					}
			}

			/// Maximum number of students
			if (!empty($response['do_it']) && !empty($settings['ulp_course_max_students'])){
				$current_students_count = $DbUserEntitiesRelations->getCountUsersForCourse($course_id);
				if ($settings['ulp_course_max_students']<=$current_students_count){
						$response = array('do_it' => FALSE,
						 									'reason' => 'ulp_messages_enroll_error_on_maximum_num_of_students',
						);
				}
			}
			/// PREREQUEST COURSES
			if (!empty($response['do_it']) && !empty($settings['ulp_course_prerequest_courses'])){
					$courses_requested = explode(',', $settings['ulp_course_prerequest_courses']);
					if ($courses_requested){
						$student_courses = $DbUserEntitiesRelations->get_user_courses($uid);
						foreach ($courses_requested as $course_id){
							if (!in_array($course_id, $student_courses)){
								$response = array('do_it' => FALSE,
								 									'reason' => 'ulp_messages_course_prerequest_courses',
								);
								break;
							}
						}
					}
			}
			/// PREREQUEST Reward POINTS
			if (!empty($response['do_it']) && !empty($settings['ulp_course_prerequest_reward_points']) ){
				require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';
				$UlpRewardPoints = new UlpRewardPoints($uid);
				$points = $UlpRewardPoints->NumOfPoints();
				if ($points<$settings['ulp_course_prerequest_reward_points']){
					$response = array(
														'do_it' => FALSE,
														'reason' => 'ulp_messages_course_prerequest_reward_points',
					);
				}
			}
			/// Re-Take Course
			$past_enroll = $DbUserEntitiesRelations->how_many_times_user_enroll_course($uid, $course_id);

			if (!empty($past_enroll) && !empty($settings['ulp_course_retake_course']) && $past_enroll>=$settings['ulp_course_retake_course']){
					$response = array(
														'do_it' => FALSE,
														'reason' => 'ulp_messages_enroll_error_retake_course_limit',
					);
			}
			return $response;
	}
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @return boolean
	 */
	public function RemoveCourse($uid=0, $course_id=0){
		if ($uid && $course_id){
			return $this->deleteRelation($uid, $course_id);
		}
	}
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @param string (end time)
	 * @return boolean
	 */
	public function UpdateEndTime($uid=0, $course_id=0, $end_time=''){
		$q = "end_time='$end_time'";
		$this->update($q, "user_id=$uid AND entity_id=$course_id");
	}
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @return none
	 */
	public function WriteCourseResult($uid=0, $course_id=0){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
		$result_arr = $this->CalculateCourseResult($uid, $course_id);
		$DbUserEntitiesRelations = new DbUserEntitiesRelations();
		$relation_id = $DbUserEntitiesRelations->getRelationColValue($uid, $course_id, 'id');
		$DbUserEntitiesRelationMetas = new DbUserEntitiesRelationMetas();
		$DbUserEntitiesRelationMetas->saveMeta($relation_id, 'course_grade', $result_arr['grade']); /// SAVE THE GRADE
		$DbUserEntitiesRelationMetas->saveMeta($relation_id, 'course_passed', $result_arr['course_passed']);/// SAVE THE COURSE IS PASSED OR NOT
		if ($result_arr['course_passed']){
			$points = get_post_meta($course_id, 'ulp_post_reward_points', TRUE);
			if ($points){
				require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';
				$UlpRewardPoints = new UlpRewardPoints($uid);
				$UlpRewardPoints->add_points_to_user($points, $course_id, 'course_passed');
			}
		}
	}
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @return array
	 */
	public function GetCourseResult($uid=0, $course_id=0, $relation_id=0){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$DbUserEntitiesRelations = new DbUserEntitiesRelations();
		if (empty($relation_id)){
				$relation_id = $DbUserEntitiesRelations->getRelationColValue($uid, $course_id, 'id');
		}
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
		$DbUserEntitiesRelationMetas = new DbUserEntitiesRelationMetas();
		$array['grade'] = $DbUserEntitiesRelationMetas->getMeta($relation_id, 'course_grade');
		$array['course_passed'] = $DbUserEntitiesRelationMetas->getMeta($relation_id, 'course_passed');
		return $array;
	}
	public function IsCourseCompleted($uid=0, $course_id=0){
			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			$relation_id = $DbUserEntitiesRelations->getRelationColValue($uid, $course_id, 'id');
			$DbUserEntitiesRelationMetas = new DbUserEntitiesRelationMetas();
			return $DbUserEntitiesRelationMetas->getMeta($relation_id, 'course_passed');
	}

	public function IsCourseRetaken($uid=0, $course_id=0){

			if (empty($settings)){
					$settings = DbUlp::getPostMetaGroup($course_id, 'course_special_settings');
			}

			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			$past_enroll = $DbUserEntitiesRelations->how_many_times_user_enroll_course($uid, $course_id);
			if (!empty($past_enroll) && !empty($settings['ulp_course_retake_course']) && $past_enroll>=$settings['ulp_course_retake_course']){
				return FALSE;
			}

			return TRUE;
	}

	public function CourseRetakeCounts($uid=0, $course_id=0){

			if (empty($settings)){
					$settings = DbUlp::getPostMetaGroup($course_id, 'course_special_settings');
			}

			require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			$DbUserEntitiesRelations = new DbUserEntitiesRelations();
			$past_enroll = $DbUserEntitiesRelations->how_many_times_user_enroll_course($uid, $course_id);
			if (!empty($past_enroll) && !empty($settings['ulp_course_retake_course']) && $past_enroll<$settings['ulp_course_retake_course'] && $past_enroll > 0){
				return array(
					'retaken' => $past_enroll,
					'limit' => $settings['ulp_course_retake_course']
				);
			}

			return array();
	}
	/**
	 * @param int (user id)
	 * @param int (course id)
	 * @return array
	 */
	public function CalculateCourseResult($uid=0, $course_id=0){
		$result = array(
						'grade' => 0,
						'course_passed' => 0,
		);
		$type = get_post_meta($course_id, 'ulp_course_assessments', TRUE);
		$passing_value = get_post_meta($course_id, 'ulp_course_assessments_passing_value', TRUE);
		switch ($type){
			case 'lessons':
				///get all lessons for this course
				$all_lessons = DbUlp::getAllCourseItems($course_id, 'ulp_lesson');
				if ($all_lessons){
					$result['course_passed'] = 1;
					$passed = 0;
					$total = 0;
					foreach ($all_lessons as $lesson){
						$lesson_passed = DbUlp::isLessonCompletedForUID($uid, $lesson['item_id']);
						if ($lesson_passed){
							$passed++;
						}
						$total++;
					}
					$result['grade'] = $passed * 100 / $total;
					$result['percentage'] = $result['grade'];
				}
				break;
			case 'quizes':
				$every_quiz_min_value = get_post_meta($course_id, 'ulp_course_quizes_min_grade', TRUE);
				$all_quizes = DbUlp::getAllCourseItems($course_id, 'ulp_quiz');
				$sum = 0;
				if ($all_quizes){
					$result['course_passed'] = 1;
					foreach ($all_quizes as $quiz){
						$current_grate = DbUlp::userGetQuizGrade($uid, $quiz['item_id']);
						$grades[] = $current_grate;
						$sum += $current_grate;
						if ($current_grate<$every_quiz_min_value){
							$result['course_passed'] = 0;
						}
					}
					$result['grade'] = $sum / (count($grades));
				}
				$result['grade'] = round( $result['grade'], 2 );
				$passing_value = (float) $passing_value;

				// update since version 3.4
				if ($result['grade']<$passing_value){
					$result['course_passed'] = 0;
				}
				return $result;
				// end of update since version 3.4

				break;
			case 'final_quiz':
				$result['course_passed'] = 1;
				$last_quiz = DbUlp::courseGetLastQuiz($course_id);
				$result['grade'] = DbUlp::userGetQuizGrade($uid, $last_quiz);
				$result['percentage'] = 100;
				break;
		}
		$result['grade'] = round( $result['grade'], 2 );
		$passing_value = (float) $passing_value;

		if ($result['grade']<$passing_value){
			$result['course_passed'] = 0;
		} else if ( $result['grade'] >= $passing_value ){
				$result['course_passed'] = 1;
		}

		return $result;
	}
	public function getProgress($uid=0, $course_id=0){
			$type = get_option('ulp_course_progress_type');
			if ($type==FALSE) {
				$type = 'completed_lessons_and_quizes';
			}
			$all_lessons = DbUlp::getAllCourseItems($course_id, 'ulp_lesson');
			$all_quizes = DbUlp::getAllCourseItems($course_id, 'ulp_quiz');
			if ($type=='completed_lessons_and_quizes'){
					/// based on total number of completed lessons and passed courses
					if ( is_array( $all_lessons ) ){
							$countAllLessons = count($all_lessons);
					} else {
							$countAllLessons = 0;
					}
					if ( is_array( $all_quizes ) ){
							$countAllQuizes = count($all_quizes);
					} else {
							$countAllQuizes = 0;
					}
					$total_items = $countAllLessons + $countAllQuizes;
					$passed_items = 0;
					if ($all_lessons){
							foreach ($all_lessons as $lesson){
									$lesson_passed = DbUlp::isLessonCompletedForUID($uid, $lesson['item_id']);
									if ($lesson_passed){
											$passed_items++;
									}
							}
					}
					if ($all_quizes){
							$every_quiz_min_value = get_post_meta($course_id, 'ulp_course_quizes_min_grade', TRUE);
							foreach ($all_quizes as $quiz){
									$current_grade = DbUlp::userGetQuizGrade($uid, $quiz['item_id']);
									if ($current_grade && $current_grade>=$every_quiz_min_value){
											$passed_items++;
									}
							}
					}
					if ($total_items>0){
							$result = $passed_items * 100 / $total_items;
							$result = round($result, 2);
							return $result;
					}
			} else {
				  /// based on reawrd points
					require_once ULP_PATH . 'classes/Entity/UlpRewardPointsDetails.class.php';
					$UlpRewardPointsDetails = new UlpRewardPointsDetails();
					$total_points_possible = 0;
					$win_points = 0;
					if ($all_lessons){
							foreach ($all_lessons as $lesson){
									$temp_points = get_post_meta($lesson['item_id'], 'ulp_post_reward_points', true);
									$total_points_possible += $temp_points;
									$user_got_points = $UlpRewardPointsDetails->entry_exists($uid, $lesson['item_id']);
									if ($user_got_points){
											$win_points += $temp_points;
									}
							}
					}
					if ($all_quizes){
							$every_quiz_min_value = get_post_meta($course_id, 'ulp_course_quizes_min_grade', TRUE);
							foreach ($all_quizes as $quiz){
								$temp_points = get_post_meta($quiz['item_id'],  'ulp_post_reward_points', true);
								$total_points_possible += $temp_points;
								$user_got_points = $UlpRewardPointsDetails->entry_exists($uid, $quiz['item_id']);
								if ($user_got_points){
										$win_points += $temp_points;
								}
							}
					}
					if ($total_points_possible>0){
						$result = $win_points * 100 / $total_points_possible;
						$result = round( $result, 2 );
						return $result;
					}
			}
			return 0;
	}
}
