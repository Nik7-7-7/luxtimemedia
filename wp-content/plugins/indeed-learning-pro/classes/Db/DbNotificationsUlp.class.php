<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbNotificationsUlp')){
   return;
}
class DbNotificationsUlp extends DbIndeedAbstract{
	/**
	 * @var string
	 */
	protected $table = '';

	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_notifications';
	}

  public function getByTypeAndCourseId($course_id=0, $type='')
  {
      global $wpdb;
			if ($course_id==0){
         $course_id = -1;
      }
			$course_id = sanitize_text_field($course_id);
			$type = sanitize_text_field($type);
      $where = $wpdb->prepare( " type=%s AND course_id=%d AND status=1 ORDER BY id DESC LIMIT 1 ", $type, $course_id );
			$data = $this->getRow( 'subject, message', $where );
			if (empty($data)){
					$course_id = -1;
          $where = $wpdb->prepare( " type=%s AND course_id=%d AND status=1 ORDER BY id DESC LIMIT 1 ", $type, $course_id );
					$data = $this->getRow( 'subject, message', $where );
			}
			if (isset($data['message'])){
					$data['message'] = indeed_format_str_like_wp( stripslashes( $data['message'] ) );
			}
			if (isset($data['subject'])){
					$data['subject'] = strip_tags($data['subject']);
					$data['subject'] = stripslashes( $data['subject'] );
			}
			return $data;
  }

	public function getPushoverByCourseIdAndType($course_id=0, $type='')
  {
      global $wpdb;
			if ($course_id==0){
         $course_id = -1;
      }
			$course_id = sanitize_text_field($course_id);
			$type = sanitize_text_field($type);
      $where = $wpdb->prepare( " type=%s AND course_id=%d AND pushover_status=1 ORDER BY id DESC LIMIT 1 ", $type, $course_id );
			$data = $this->getRow( ' subject, pushover_message ', $where );
			if (empty($data)){
					$course_id = -1;
          $where = $wpdb->prepare( " type=%s AND course_id=%d AND pushover_status=1 ORDER BY id DESC LIMIT 1", $type, $course_id );
					$data = $this->getRow( ' subject, pushover_message ', $where );
			}
			return $data;
	}

	public function getNotificationById($id=0){
      global $wpdb;
			$id = sanitize_text_field($id);
      $where = $wpdb->prepare( " id=%d ", $id );
			$array = $this->getRow( " `id`, `type`, `course_id`, `subject`, `message`, `pushover_message`, `pushover_status`, `status` ", $where );
			if (empty($array)){
					/// DEFAULTS
					$array = array(
													'type' => '',
													'subject' => '',
													'message' => '',
													'pushover_message' => '',
													'pushover_status' => 0,
													'course_id' => 0,
													'status' => 0,
													'id' => 0,
					);
			}

			//$array['message'] = indeed_format_str_like_wp( stripslashes( $array['message'] ) );
      $array['message'] =stripslashes( htmlspecialchars_decode( $array['message'] )  );
			$array['subject'] = strip_tags($array['subject']);
			$array['subject'] = stripslashes( $array['subject'] );
			return $array;
	}

  public function save($input_data=array()){
    global $wpdb;
    $do_update = false;
		/*foreach ($input_data as $k=>$v){
				$input_data [$k] = sanitize_text_field($v);
		}*/
    if (!empty($input_data['id'])){
      $do_update = $this->getById($input_data['id']);
    }

		do_action( 'ulp_save_notification_action', $input_data );

    if ($do_update){
        $update = $wpdb->prepare( "type=%s,
															course_id=%d,
															subject=%s,
															message=%s,
															pushover_message=%s,
															pushover_status=%s,
															status=%d ",
                              $input_data['type'], $input_data['course_id'], stripslashes_deep($input_data['subject']), stripslashes_deep($input_data['message']),
                              $input_data['pushover_message'], $input_data['pushover_status'], $input_data['status']
        );
        $where = $wpdb->prepare( " id=%d ", $input_data['id'] );
        return $this->update( $update, $where );
    } else {
        $insert = $wpdb->prepare( "null,
															%s,
															%d,
															%s,
															%s,
															%s,
															%s,
															%d",
                              $input_data['type'],
                              $input_data['course_id'],
                              stripslashes_deep($input_data['subject']),
                              stripslashes_deep($input_data['message']),
                              $input_data['pushover_message'],
                              $input_data['pushover_status'],
                              $input_data['status']
        );
        return $this->insert( $insert );
    }
  }

  public function delete($id=0){
      global $wpdb;
  		$id = sanitize_text_field($id);
      $delete = $wpdb->prepare( " id=%d ", $id );
      return parent::delete( $delete );
  }

  public function getAll(){
      return $this->getResults( " `id`, `type`, `course_id`, `subject`, `message`, `pushover_message`, `pushover_status`, `status` ", '1=1');
  }

  public function getById($id=0){
      global $wpdb;
			$id = sanitize_text_field($id);
      $where = $wpdb->prepare( " id=%d ", $id );
      return $this->getRow( " `id`, `type`, `course_id`, `subject`, `message`, `pushover_message`, `pushover_status`, `status` ", $where );
  }

	public function getActionTypes($type='admin'){
		/// todo: admin_before_course_expires && admin_scheduled_lesson_user
		$data = array(
				'admin' => [
								'admin_user_become_instructor' 			=> esc_html__('User wants to be Instructor', 'ulp'),
								'admin_user_enroll_course' 					=> esc_html__('User enrolls into a course', 'ulp'),
								'admin_user_complete_course' 				=> esc_html__('User completes a course', 'ulp'),
								'admin_user_complete_lesson' 				=> esc_html__('User completes a lesson', 'ulp'),
								/// 'admin_scheduled_lesson_user' => esc_html__('A scheduled lesson is available to a user', 'ulp'),
								'admin_user_pass_quiz' 							=> esc_html__('User passes a quiz', 'ulp'),
								'admin_user_fails_quiz' 						=> esc_html__('User fails a quiz', 'ulp'),
								'admin_user_complete_quiz' 					=> esc_html__('User completes a quiz', 'ulp'),
								'admin_instructor_create_course'		=> esc_html__('Instructor create a course', 'ulp'),
								'admin_instructor_edit_course'			=> esc_html__('Instructor edit a course', 'ulp'),
								/// 'admin_before_course_expires' => esc_html__("'X' days before a course expires", 'ulp'),
					],
				'student' => [
								'user_enroll_course' 				=> esc_html__('User enrolls into a course', 'ulp'),
								'user_complete_course' 			=> esc_html__('User completes a course', 'ulp'),
								'user_complete_lesson' 			=> esc_html__('User completes a lesson', 'ulp'),
								'user_pass_quiz' 						=> esc_html__('User passes a quiz', 'ulp'),
								'user_fails_quiz' 					=> esc_html__('User fails a quiz', 'ulp'),
								'user_complete_quiz' 				=> esc_html__('User completes a quiz', 'ulp'),
								'user_bank_transfer' 				=> esc_html__('Bank Transfer Payment Details', 'ulp'),
				],
				'announcements' => [
								'admin_user_comment_on_announcement' 	=> esc_html__('Author: User commentted on Announcement', 'ulp'),
								'user_new_announcement' 							=> esc_html__('Student: New Announcement submitted', 'ulp'),
				],
				'qanda' => [
								'student_ask_question' 										=> esc_html__('Author: Student ask a new Question', 'ulp'),
								'new_reply_on_question_from_instructor' 	=> esc_html__('Student: Instructor reply to student Question', 'ulp'),
								'new_reply_on_question' 									=> esc_html__('Author: Student comment on Question', 'ulp'),
				],
				'others'	=> [
								'instructor_user_enroll_course'								=> esc_html__('Instructor: Student enrolls into a course', 'ulp'),
								'instructor_admin_has_publish_your_course'		=> esc_html__('Instructor: Your course has been published', 'ulp'),
				],
		);
		return $data[$type];
	}

	public function get_standard_by_type($type=''){
			$template = ['subject' => '', 'content' => ''];
			switch ($type){
				case 'admin_user_become_instructor':
					$template = [
						'subject' => '{blogname}: User {username} wants to become an Instructor',
						'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
              <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
            <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
              <div style="padding-top:30px"></div>
              <div>Hello,</div>
              <div>User: {username} wants to become an instructor.</div>
              <div>To make him an instructor, please go to {admin_user_url_page} and set his Wp Role to Instructor.</div>
                  <div>Have a nice day!</div>
            <div style="padding-top:30px"></div>
            </div>
            <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
            <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
            </div>
            </div>'
					];
					break;
				case 'admin_user_enroll_course':
					$template = [
								'subject' => ' {blogname}: User {username} has just enrolled in the {course_name} course ',
								'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                  <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                  <div style="padding-top:30px"></div>
                  <div>Hello,</div>
                  <div>User: {username} has just enrolled in the following course: {course_name}.</div>
                      <div>Have a nice day!</div>
                <div style="padding-top:30px"></div>
                </div>
                <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                </div>
                </div>'
					];
					break;
				case 'admin_user_complete_course':
					$template = [
							'subject' => '{blogname}: User {username} has just completed the {course_name} course',
							'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
              <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                <div style="padding-top:30px"></div>
                <div>Hello,</div>
                <div>User: {username} has just finished the {course_name} course.</div>
                    <div>Have a nice day!</div>
              <div style="padding-top:30px"></div>
              </div>
              <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
              <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
              </div>
              </div>',
					];
					break;
				case 'admin_user_complete_lesson':
					$template = [
							'subject' => '{blogname}: User {username} has just completed the {lesson_title} lesson',
							'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
              <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                <div style="padding-top:30px"></div>
                <div>Hello,</div>
                <div>User: {username} has just completed the {lesson_title} lesson from the {course_name} course.</div>
                    <div>Have a nice day!</div>
              <div style="padding-top:30px"></div>
              </div>
              <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
              <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
              </div>
              </div>',
					];
					break;
				case 'admin_scheduled_lesson_user':
					$template = [
							'subject' => '{blogname}: {lesson_title} lesson has just become available for user {username}',
							'content' => '
										<p>Hello,</p>
										<p></p>
										<p>Lesson {shortcode} has become available for user: {username}.</p>
										<p></p>
										<p>Have a nice day!</p>
							',
					];
					break;
					case 'admin_user_pass_quiz':
						$template = [
								'subject' => '{blogname}: User {username} has just passed the {quiz_title} quiz',
								'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                  <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                  <div style="padding-top:30px"></div>
                  <div>Hello,</div>
                  <div>User {username} has just passed the {quiz_title} quiz with {quiz_grade} grade.</div>
                      <div>Have a nice day!</div>
                <div style="padding-top:30px"></div>
                </div>
                <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                </div>
                </div>',
						];
						break;
						case 'admin_user_fails_quiz':
							$template = [
									'subject' => '{blogname}: User {username} has not passed the {quiz_title} quiz',
									'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                    <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                  <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                    <div style="padding-top:30px"></div>
                    <div>Hello,</div>
                    <div>User {username} scored {quiz_grade} grade on the quiz {quiz_title}, but it wasn’t enough to pass.</div>
                        <div>Have a nice day!</div>
                  <div style="padding-top:30px"></div>
                  </div>
                  <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                  <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                  </div>
                  </div>',
							];
							break;
						case 'admin_user_complete_quiz':
							$template = [
										'subject' => '{blogname}: User {username} has just completed the {quiz_title} quiz',
										'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                      <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                    <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                      <div style="padding-top:30px"></div>
                      <div>Hello,</div>
                      <div>User {username} has just finished completing the quiz {quiz_title}.</div>
                          <div>Have a nice day!</div>
                    <div style="padding-top:30px"></div>
                    </div>
                    <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                    <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                    </div>
                    </div>',
							];
							break;
						case 'admin_before_course_expires':
							$template = [
										'subject' => '{blogname}: {course_name} course will expire soon',
										'content' => '
											<p>Hello,</p>
											<p></p>
											<p>The course {course_name} will expire in {shortcode} for user {username}.<p>
											<p></p>
											<p>Have a nice day!</p>
										',
							];
							break;
						case 'admin_instructor_create_course':
							$template = [
										'subject' => '{blogname}: {username} has created a new course',
										'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                      <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                    <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                      <div style="padding-top:30px"></div>
                      <div>Hello,</div>
                      <div>{username} has created the following course: {course_name}.</div>
                          <div>Have a nice day!</div>
                    <div style="padding-top:30px"></div>
                    </div>
                    <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                    <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                    </div>
                    </div>',
							];
							break;
						case 'admin_instructor_edit_course':
							$template = [
										'subject' => '{blogname}: {username} has updated a {course_name}',
										'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                      <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                    <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                      <div style="padding-top:30px"></div>
                      <div>Hello,</div>
                      <div>{username} has updated a {course_name}.</div>
                          <div>Have a nice day!</div>
                    <div style="padding-top:30px"></div>
                    </div>
                    <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                    <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                    </div>
                    </div>',
							];
							break;

						case 'user_enroll_course':
							$template = [
										'subject' => '{blogname}: Successfully enrolled in the course',
										'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                      <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                    <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                      <div style="padding-top:30px"></div>
                      <div>Hello {username},</div>
                      <div>You have successfully enrolled in the {course_name} course.</div>
                          <div>Have a nice day!</div>
                    <div style="padding-top:30px"></div>
                    </div>
                    <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                    <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                    </div>
                    </div>',
							];
							break;
						case 'user_complete_course':
							$template = [
											'subject' => ' {blogname}: Finished course',
											'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                        <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                      <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                        <div style="padding-top:30px"></div>
                        <div>Congratulations {username},</div>
                        <div>You have just finished the {course_name} course.</div>
                            <div>Have a nice day!</div>
                      <div style="padding-top:30px"></div>
                      </div>
                      <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                      <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                      </div>
                      </div>',
							];
							break;
						case 'user_complete_lesson':
							$template = [
											'subject' => '{blogname}: Completed lesson',
											'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                        <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                      <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                        <div style="padding-top:30px"></div>
                        <div>Hello {username},</div>
                        <div>You have just finished the {lesson_title} lesson part of the {course_name} course.</div>
                            <div>Have a nice day!</div>
                      <div style="padding-top:30px"></div>
                      </div>
                      <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                      <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                      </div>
                      </div>',
							];
							break;
						case 'user_pass_quiz':
							$template = [
												'subject' => '{blogname}: Quiz results',
												'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                          <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                        <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                          <div style="padding-top:30px"></div>
                          <div>Congratulations {username},</div>
                          <div>You have passed the quiz {quiz_title} with {quiz_grade} grade.</div>
                              <div>Keep up the good work!</div>
                        <div style="padding-top:30px"></div>
                        </div>
                        <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                        <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                        </div>
                        </div>',
							];
							break;
						case 'user_fails_quiz':
							$template = [
													'subject' => ' {blogname}: Quiz results',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Hello {username},</div>
                            <div>Sadly, your {quiz_grade} grade for the {quiz_title} quiz was not enough to pass.</div>
                            <div>Study some more and give it another try.</div>
                            <div>Have a nice day</div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>',
							];
							break;
						case 'user_complete_quiz':
							$template = [
													'subject' => 'Subject: {blogname}: Quiz completed',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Hello {username},</div>
                            <div>You have just finished the {quiz_title} quiz.</div>
                            <div>Have a nice day</div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>',
							];
							break;
						case 'user_bank_transfer':
							$template = [
													'subject' => 'Subject: {blogname}: Payment Inform',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Hello {username},</div>
                            <div>Please proceed the bank transfer payment for: {currency}{amount}</div>
                            <div><strong>Payment Details:</strong> Subscription {course_name} for {username} with Identification: {user_id}_{course_id}<br/>
                            <strong>Bank Details:</strong> IBAN:xxxxxxxxxxxxxxxxxxxx Bank NAME</div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>',
							];
							break;
						case 'admin_user_comment_on_announcement':
							$template = [
													'subject' => 'Subject: {blogname} - Student commentted on your announcement',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Student <strong>{student_name}</strong> post a new comment on your announcement:</div>
                            <div><a href="{announcement_link}">{announcement_title}</a></div>
                            <div>Comment:</div>
                            <div><i>{comment_content}</i></div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>',
							];
							break;
						case 'user_new_announcement':
							$template = [
													'subject' => 'Subject: {blogname} - New announcement: {announcement_title}',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Hi {first_name} {last_name},</div>
                            <div><strong>{author_name}</strong> made a new announcement on <a href="{course_link}">{course_name}</a>.</div>
                            <div><a href="{announcement_link}"><strong>{announcement_title}</strong></a></div>
                            <div>{announcement_content}</div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>
',
							];
							break;
						case 'student_ask_question':
							$template = [
													'subject' => 'Subject: {blogname} - Student ask new question: {qanda_title}',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Hi,</div>
                            <div><strong>{student_name}</strong> ask a new question on <a href="{course_link}">{course_name}</a>.</div>
                            <div><a href="{announcement_link}"><strong>{announcement_title}</strong></a></div>
                            <div><a href="{qanda_link}"><strong>{qanda_title}</strong></a></div>
                            <div>{qanda_content}</div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>
',
							];
							break;
						case 'new_reply_on_question_from_instructor':
							$template = [
													'subject' => 'Subject: {blogname} - New reply on question: {qanda_title}',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Hi {user_full_name},</div>
                            <div>Instructor reply on your question from <a href="{course_link}">{course_name}</a>.</div>
                            <div><a href="{qanda_link}"><strong>{qanda_title}</strong></a></div>
                            <div>Reply:</div>
                            <div>{comment_content}</div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>
',
							];
							break;
						case 'new_reply_on_question':
							$template = [
													'subject' => 'Subject: {blogname} - New reply on question: {qanda_title}',
													'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                            <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                          <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                            <div style="padding-top:30px"></div>
                            <div>Hi,</div>
                            <div>Student <strong>{user_full_name}</strong> reply on his question from <a href="{course_link}">{course_name}</a>.</div>
                            <div><a href="{qanda_link}"><strong>{qanda_title}</strong></a></div>
                            <div>Reply:</div>
                            <div>{comment_content}</div>
                          <div style="padding-top:30px"></div>
                          </div>
                          <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                          <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                          </div>
                          </div>
',
							];
							break;
						case 'instructor_user_enroll_course':
							$template = [
										'subject' => ' {blogname}: User {username} has just enrolled in the {course_name} course ',
										'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                      <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                    <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                      <div style="padding-top:30px"></div>
                      <div>Hello,</div>
                      <div>User: {username} has just enrolled in the following course: {course_name}.</div>
                      <div>Have a nice day!</div>
                    <div style="padding-top:30px"></div>
                    </div>
                    <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                    <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                    </div>
                    </div>
'
							];
							break;
						case 'instructor_admin_has_publish_your_course':
							$template = [
										'subject' => ' {blogname}: User {username} your course has been published ',
										'content' => '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
                      <div style="background:#d4d5d1; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
                    <div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #606a68; padding: 30px 25px;">
                      <div style="padding-top:30px"></div>
                      <div>Hello,</div>
                      <div>{username}, your course ({course_name}) has been published.</div>
                      <div>Have a nice day!</div>
                    <div style="padding-top:30px"></div>
                    </div>
                    <div style="background: #697b9b; color: #fff; padding: 20px 30px;">
                    <div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
                    </div>
                    </div>'
							];
							break;
		}

		return $template;
	}
}
