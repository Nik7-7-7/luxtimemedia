<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('Ulp_Notifications_Triggers')){
   return;
}
class Ulp_Notifications_Triggers{
    public function __construct(){
        add_action('ulp_user_complete_course', [$this, 'on_finish_course'], 10, 2);
        add_action('ulp_user_do_enroll', [$this, 'on_user_do_enroll'], 10, 2);
        add_action('ulp_user_has_completed_lesson', [$this, 'on_lesson_completed'], 10, 2);
        add_action('ulp_user_pass_quiz', [$this, 'on_user_pass_quiz'], 10, 3);
        add_action('ulp_user_fail_quiz', [$this, 'on_user_fail_quiz'], 10, 3);
        add_action('ulp_user_completes_quiz', [$this, 'on_user_completes_quiz'], 10, 3);
        add_filter('admin_notification_user_become_instructor', [$this, 'on_user_want_become_instructor'], 10, 2);
        add_action('ulp_user_bank_transfer_order', [$this, 'on_user_bank_transfer_order'], 10, 3);
        add_action('ulp_create_new_announcement', [$this, 'on_create_new_announcement'], 10, 2);
        add_action('ulp_user_comment_on_announcement', [$this, 'on_student_comment_on_announcement'], 10, 4);
        add_action('ulp_student_ask_a_question', [$this, 'on_student_ask_question'], 10, 3);
        add_action('ulp_instructor_reply_for_student_question', [$this, 'on_instructor_reply_on_question'], 10, 4);
        add_action('ulp_student_reply_on_instructor_reponse_for_question', [$this, 'on_student_reply_on_question'], 10, 4);
        add_action('ulp_public_instructor_has_update_course', [$this, 'on_instructor_update_course'], 10, 2);
        add_action('ulp_public_instructor_has_create_course', [$this, 'on_instructor_create_course'], 10, 2);

        add_action( 'ulp_administrator_has_published_course', [$this, 'on_admin_publish_course'], 10, 1 );
    }

    public function on_finish_course($course_id=0, $uid=0){
        UlpNotification::send(
            array(
                  'course_id' => $course_id,
                  'uid' => $uid,
                  'notification_type' => 'admin_user_complete_course'
                )
        );
        UlpNotification::send(
            array(
                  'course_id' => $course_id,
                  'uid' => $uid,
                  'notification_type' => 'user_complete_course'
                )
        );
    }
    public function on_user_do_enroll($uid=0, $course_id=0){
        /// admin
        UlpNotification::send([
              'course_id' => $course_id,
              'uid' => $uid,
              'notification_type' => 'admin_user_enroll_course',
              'dynamic_data' => [ '{user_id}' => $uid ]
        ]);
        /// student
        UlpNotification::send([
              'course_id' => $course_id,
              'uid' => $uid,
              'notification_type' => 'user_enroll_course'
        ]);

        /// send one to instructors
        $sendTo[] = [];
        $instructors = DbUlp::getAllInstructorsForCourse($course_id);
        if ($instructors!=false){
            $sendTo[] = $instructors;
        }
        $courseAuthor = DbUlp::getPostAuthor($course_id);
        $sendTo[] = $courseAuthor;

        require_once ULP_PATH . 'classes/Entity/UlpInstructor.class.php';
        $UlpInstructor = new \UlpInstructor();

        foreach ($sendTo as $userId){
            if (\DbUlp::isUserInstructor($userId)){
                $instructorSettings = $UlpInstructor->getInstructorNotificationSettings($userId);
                if (!$instructorSettings['ulp_instructor_notifications-user_enroll_course']){
                    continue;
                }
            }

            UlpNotification::send([
                  'course_id'           => $course_id,
                  'uid'                 => $userId,
                  'notification_type'   => 'instructor_user_enroll_course',
                  'dynamic_data'        => [ '{user_id}' => $uid ]
            ]);
        }
    }

    public function on_lesson_completed($uid=0, $lesson_title=''){
        UlpNotification::send([
                  'course_id' => -1,
                  'uid' => $uid,
                  'notification_type' => 'admin_user_complete_lesson',
                  'dynamic_data' => [ '{lesson_title}' => $lesson_title ]
        ]);
        UlpNotification::send([
                  'course_id' => -1,
                  'uid' => $uid,
                  'notification_type' => 'user_complete_lesson',
                  'dynamic_data' => [ '{lesson_title}' => $lesson_title ]
        ]);
    }
    public function on_user_pass_quiz($uid=0, $quiz_id=0, $grade=0){
        UlpNotification::send([
                  'course_id' => -1,
                  'uid' => $uid,
                  'notification_type' => 'admin_user_pass_quiz',
                  'dynamic_data' => ['{quiz_title}' => DbUlp::getPostTitleByPostId($quiz_id), '{quiz_grade}' => $grade]
        ]);
        UlpNotification::send([
                  'course_id' => -1,
                  'uid' => $uid,
                  'notification_type' => 'user_pass_quiz',
                  'dynamic_data' => ['{quiz_title}' => DbUlp::getPostTitleByPostId($quiz_id), '{quiz_grade}' => $grade]
        ]);
    }
    public function on_user_fail_quiz($uid=0, $quiz_id=0, $grade=0){
        UlpNotification::send([
            'course_id' => -1,
            'uid' => $uid,
            'notification_type' => 'admin_user_fails_quiz',
            'dynamic_data' => ['{quiz_title}' => DbUlp::getPostTitleByPostId($quiz_id), '{grade}' => $grade]
        ]);
        UlpNotification::send([
            'course_id' => -1,
            'uid' => $uid,
            'notification_type' => 'user_fails_quiz',
            'dynamic_data' => ['{quiz_title}' => DbUlp::getPostTitleByPostId($quiz_id), '{grade}' => $grade]
        ]);
    }
    public function on_user_completes_quiz($uid=0, $quiz_id=0, $grade=0){
        UlpNotification::send([
            'course_id' => -1,
            'uid' => $uid,
            'notification_type' => 'admin_user_complete_quiz',
            'dynamic_data' => ['{quiz_title}' => DbUlp::getPostTitleByPostId($quiz_id), '{grade}' => $grade]
        ]);
        UlpNotification::send([
            'course_id' => -1,
            'uid' => $uid,
            'notification_type' => 'user_complete_quiz',
            'dynamic_data' => ['{quiz_title}' => DbUlp::getPostTitleByPostId($quiz_id), '{grade}' => $grade]
        ]);
    }
    public function on_user_want_become_instructor($return=FALSE, $uid=0){
        return UlpNotification::send([
        					      'course_id' => -1,
        					      'uid' => $uid,
        					      'notification_type' => 'admin_user_become_instructor',
        								'dynamic_data' => ['{admin_user_url_page}' => admin_url('user-edit.php?user_id=' . $uid)]
        ]);
    }

	public function on_user_bank_transfer_order($uid=0, $course_id=0, $amount = 0){
        return UlpNotification::send([
        					      'course_id' => $course_id,
        					      'uid' => $uid,
        					      'notification_type' => 'user_bank_transfer',
        						     'dynamic_data' => ['{amount}' => $amount ]
        ]);
    }

    public function on_create_new_announcement($announcementId=0, $courseId=0)
    {
        /// send to all students that are enrolled to this course
        if (empty($announcementId) || empty($courseId)){
            return;
        }
        $students = DbUlp::getStudentIdsByCourse($courseId);
        if (empty($students)){
            return;
        }
        $authorId = DbUlp::getPostAuthor($announcementId);
        foreach ($students as $student){
            UlpNotification::send([
                            'course_id' => $courseId,
                            'uid' => $student->ID,
                            'notification_type' => 'user_new_announcement',
                            'dynamic_data' => [
                                '{first_name}'            => get_user_meta($student->ID, 'first_name', TRUE),
                                '{last_name}'             => get_user_meta($student->ID, 'last_name', TRUE),
                                '{course_name}'           => DbUlp::getPostTitleByPostId($courseId),
                                '{course_link}'           => Ulp_Permalinks::getForCourse($courseId),
                                '{announcement_content}'  => DbUlp::getPostContentByPostId($courseId),
                                '{announcement_title}'    => DbUlp::getPostTitleByPostId($courseId),
                                '{announcement_link}'     => Ulp_Permalinks::getForAnnouncement($announcementId),
                                '{author_name}'           => DbUlp::getUserFulltName($authorId),
                            ],
            ]);
        }
    }

    public function on_student_comment_on_announcement($uid=0, $commentId=0, $announcementId=0, $courseId=0)
    {
        /// send notification to announcement author
        if (empty($uid) || empty($courseId) || empty($commentId) || empty($announcementId)){
            return false;
        }
        $authorId = DbUlp::getPostAuthor($announcementId);

        require_once ULP_PATH . 'classes/Entity/UlpInstructor.class.php';
        $UlpInstructor = new \UlpInstructor();
        $instructorSettings = $UlpInstructor->getInstructorNotificationSettings($authorId);
        if (!$instructorSettings['ulp_instructor_notifications-on_student_comment_on_announcement']){
            return false;
        }

        return UlpNotification::send([
                                'course_id' => $courseId,
                                'uid' => $authorId,
                                'notification_type' => 'admin_user_comment_on_announcement',
                                'dynamic_data' => [
                                      '{course_name}'           => DbUlp::getPostTitleByPostId($courseId),
                                      '{course_link}'           => Ulp_Permalinks::getForCourse($courseId),
                                      '{announcement_content}'  => DbUlp::getPostContentByPostId($courseId),
                                      '{announcement_title}'    => DbUlp::getPostTitleByPostId($courseId),
                                      '{announcement_link}'     => Ulp_Permalinks::getForAnnouncement($announcementId),
                                      '{comment_content}'       => DbUlp::getCommentContent($commentId),
                                      '{student_name}'          => DbUlp::getUserFulltName($uid),
                                      '{user_id}'               => $uid,
                                ],
        ]);
    }

    public function on_student_ask_question($uid=0, $courseId=0, $qandaQuestion=0)
    {
        if (empty($uid) || empty($courseId) || empty($qandaQuestion)){
            return false;
        }
        /// send notification to instructor
        $courseAuthor = DbUlp::getPostAuthor($courseId);

        require_once ULP_PATH . 'classes/Entity/UlpInstructor.class.php';
        $UlpInstructor = new \UlpInstructor();
		    $instructorSettings = $UlpInstructor->getInstructorNotificationSettings($courseAuthor);
        if (!$instructorSettings['ulp_instructor_notifications-on_student_ask_question']){
            return false;
        }

        $made = UlpNotification::send([
                            'course_id' => $courseId,
                            'uid' => $courseAuthor,
                            'notification_type' => 'student_ask_question',
                            'dynamic_data' => [
                                '{course_name}'           => DbUlp::getPostTitleByPostId($courseId),
                                '{course_link}'           => Ulp_Permalinks::getForCourse($courseId),
                                '{student_name}'          => DbUlp::getUserFulltName($uid),
                                '{qanda_content}'         => DbUlp::getPostContentByPostId($qandaQuestion),
                                '{qanda_title}'           => DbUlp::getPostTitleByPostId($qandaQuestion),
                                '{qanda_link}'            => Ulp_Permalinks::getForQanda($qandaQuestion),
                                '{user_id}'               => $uid,
                            ],
        ]);
        return $made;
    }

    public function on_instructor_reply_on_question($uid=0, $commentId=0, $qandaId=0, $courseId=0)
    {
        if (empty($uid) || empty($courseId) || empty($commentId) || empty($qandaId)){
            return false;
        }
        /// send notification to student that created the question
        $qandaAuthor = DbUlp::getPostAuthor($qandaId);
        if (!$qandaAuthor){
            return;
        }
        return UlpNotification::send([
                            'course_id' => $courseId,
                            'uid' => $qandaAuthor,
                            'notification_type' => 'new_reply_on_question_from_instructor',
                            'dynamic_data' => [
                                '{course_name}'           => DbUlp::getPostTitleByPostId($courseId),
                                '{course_link}'           => Ulp_Permalinks::getForCourse($courseId),
                                '{user_full_name}'        => DbUlp::getUserFulltName($uid),
                                '{qanda_content}'         => DbUlp::getPostContentByPostId($qandaId),
                                '{qanda_title}'           => DbUlp::getPostTitleByPostId($qandaId),
                                '{qanda_link}'            => Ulp_Permalinks::getForQanda($qandaId),
                                '{comment_content}'       => DbUlp::getCommentContent($commentId),
                                '{user_id}'               => $uid,
                            ]
        ]);
    }

    public function on_student_reply_on_question($uid=0, $commentId=0, $qandaId=0, $courseId=0)
    {
        if (empty($uid) || empty($qandaId) || empty($commentId) || empty($courseId)){
            return false;
        }
        /// send to instructor and maybe to question author if the user that made the comment is not the author
        $qandaAuthor = DbUlp::getPostAuthor($qandaId);
        $sendTo[] = [];
        if ($qandaAuthor!=$uid){
            $sendTo[] = $qandaAuthor;
        }
        $instructors = DbUlp::getAllInstructorsForCourse($courseId);
        if ($instructors!=false){
            $sendTo[] = $instructors;
        }
        $courseAuthor = DbUlp::getPostAuthor($courseId);
        $sendTo[] = $courseAuthor;

        require_once ULP_PATH . 'classes/Entity/UlpInstructor.class.php';
        $UlpInstructor = new \UlpInstructor();

        foreach ($sendTo as $userId){

            if (\DbUlp::isUserInstructor($userId)){
                $instructorSettings = $UlpInstructor->getInstructorNotificationSettings($userId);
                if (!$instructorSettings['ulp_instructor_notifications-student_reply_on_question']){
                    continue;
                }
            }

            \UlpNotification::send([
                                'course_id' => $courseId,
                                'uid' => $userId,
                                'notification_type' => 'new_reply_on_question',
                                'dynamic_data' => [
                                    '{course_name}'           => DbUlp::getPostTitleByPostId($courseId),
                                    '{course_link}'           => Ulp_Permalinks::getForCourse($courseId),
                                    '{user_full_name}'        => DbUlp::getUserFulltName($uid),
                                    '{qanda_content}'         => DbUlp::getPostContentByPostId($qandaId),
                                    '{qanda_title}'           => DbUlp::getPostTitleByPostId($qandaId),
                                    '{qanda_link}'            => Ulp_Permalinks::getForQanda($qandaId),
                                    '{comment_content}'       => DbUlp::getCommentContent($commentId),
                                    '{user_id}'               => $uid,
                                ],
            ]);
        }

    }

    public function on_instructor_update_course($uid=0, $postId=0)
    {
        if (!$uid || !$postId){
            return false;
        }
        return \UlpNotification::send([
                        'course_id' => $postId,
                        'uid' => $uid,
                        'notification_type' => 'admin_instructor_edit_course',
        ]);
    }

    public function on_instructor_create_course($uid=0, $postId=0)
    {
        if (!$uid || !$postId){
            return false;
        }
        return \UlpNotification::send([
                        'course_id' => $postId,
                        'uid' => $uid,
                        'notification_type' => 'admin_instructor_create_course',
        ]);
    }

    public function on_admin_publish_course($courseObject=null)
    {
        if (!$courseObject){
            return false;
        }
        if (\DbUlp::isUserAdmin($courseObject->post_author)){
            return false;
        }
        return \UlpNotification::send([
                        'course_id' => $courseObject->ID,
                        'uid' => $courseObject->post_author,
                        'notification_type' => 'instructor_admin_has_publish_your_course',
        ]);
    }

}
