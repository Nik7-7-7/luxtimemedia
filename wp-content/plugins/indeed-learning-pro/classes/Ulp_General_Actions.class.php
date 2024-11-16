<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_General_Actions')){
   return;
}
class Ulp_General_Actions{
    public function __construct(){
        add_action('ulp_user_complete_course', array($this, 'give_user_certificate_for_finish_course'), 11, 2);
        add_action('ulp_user_do_enroll', array($this, 'user_enroll_admin_dashboard_notification'), 11, 2);
        add_action('ulp_new_order', array($this, 'new_order_admin_dashboard_notification'), 10, 3);
        add_action('ulp_make_order_complete', [$this, 'course_auto_enroll'], 1, 1 ); /// AUTO ENROLL
        add_action('ulp_remove_user_from_course', [$this, 'remove_user_from_course'], 99, 1);
        /// activity
        add_action('ulp_user_complete_course', [$this, 'save_activity_finish_course'], 12, 2);
        add_action('ulp_finish_quiz', [$this, 'save_activity_finish_quiz'], 12, 3);
        add_action('ulp_user_completes_quiz', [$this, 'save_activity_quiz_grade'], 12, 3);
        add_action('ulp_user_do_enroll', [$this, 'save_activity_user_enroll'], 12, 2);
        add_action('ulp_user_gets_points', [$this, 'save_activity_user_gets_points'], 12, 4);
        add_action('ulp_user_receive_certificate', [$this, 'save_activity_user_receive_certificate'], 12, 4);
        add_action('ulp_user_receive_badge', [$this, 'save_activity_user_receive_badge'], 12, 2);

        add_action('set_user_role', [$this, 'onSetRole'], 99, 3);
        add_action('ulp_user_has_become_instructor', ['DbUlp', 'insertCustomPostTypeInstructor'], 99, 3);

        add_action('ulp_sidebars', [$this, 'sidebars']);
        add_action('delete_post', [$this, 'doDeletePost'], 1, 1);
        add_action('wp_insert_comment', [$this, 'onInsertCommentForCPT'], 99, 2);

        add_action('ulp_before_print_single_course', [new \Indeed\Ulp\PublicSection\SingleCourseMenu(), 'doPrint'], 1, 1);
        add_action('ulp_single_course_after_overview_content', [new \Indeed\Ulp\PublicSection\SingleCoursePages(), 'announcements'], 1, 1);
        add_action('ulp_single_course_after_overview_content', [new \Indeed\Ulp\PublicSection\SingleCoursePages(), 'qAndA'], 2, 1);
        add_action('ulp_single_course_after_overview_content', [new \Indeed\Ulp\PublicSection\SingleCoursePages(), 'ulpCourseCurriculum'], 3, 1);

        add_action('transition_post_status', [$this, 'ulpChangePostStatus'], 10, 3);
    }

    public function onSetRole($uid=0, $role='', $oldRoles='')
    {
        if ($role=='ulp_instructor' || $role=='ulp_instructor_senior'){
            do_action('ulp_user_has_become_instructor', $uid, $role, $oldRoles);
        }
    }

    public function give_user_certificate_for_finish_course($course_id=0, $uid=0){
        require_once ULP_PATH . 'classes/Db/Db_User_Certificates.class.php';
        $Db_User_Certificates = new Db_User_Certificates();
        $certificate_id = DbUlp::getCertificateForCourse($course_id);
        if ($certificate_id){
            /// getting grade
            require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
            require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
            $DbUserEntitiesRelations = new DbUserEntitiesRelations();
            $user_entity_relation_id = $DbUserEntitiesRelations->getRelationId($uid, $course_id);
            $DbUserEntitiesRelationMetas = new DbUserEntitiesRelationMetas();
            $grade = $DbUserEntitiesRelationMetas->getMeta($user_entity_relation_id, 'course_grade');
            $details = '';
            $Db_User_Certificates->addCertificateForUser($uid, $course_id, $certificate_id, $grade, $details);
        }
    }
    public function user_enroll_admin_dashboard_notification($uid=0, $course_id=0){
        require_once ULP_PATH . 'classes/Db/Db_Ulp_Dashboard_Notifications.class.php';
        $Db_Ulp_Dashboard_Notifications = new Db_Ulp_Dashboard_Notifications();
        $num = $Db_Ulp_Dashboard_Notifications->get('new_students');
        $num++;
        $Db_Ulp_Dashboard_Notifications->save('new_students', $num);
    }
    public function new_order_admin_dashboard_notification($order_id=0, $uid=0, $course_id=0){
        require_once ULP_PATH . 'classes/Db/Db_Ulp_Dashboard_Notifications.class.php';
        $Db_Ulp_Dashboard_Notifications = new Db_Ulp_Dashboard_Notifications();
        $num = $Db_Ulp_Dashboard_Notifications->get('new_orders');
        $num++;
        $Db_Ulp_Dashboard_Notifications->save('new_orders', $num);
    }
    public function course_auto_enroll($order_id=0){
        require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
        $DbUlpOrdersMeta = new DbUlpOrdersMeta();
        $uid = $DbUlpOrdersMeta->getVar('meta_value', "meta_key='user_id' AND order_id=$order_id");
        $course_id = $DbUlpOrdersMeta->getVar('meta_value', "meta_key='course_id' AND  order_id=$order_id");
        $do_auto_entroll = get_option('ulp_course_auto_enroll');
        if ($course_id && $uid && $do_auto_entroll){
            require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
            $UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
            $UsersCoursesActionsUlp->AppendCourse($uid, $course_id);
        }
    }
    public function remove_user_from_course($order_id=0){
            require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
            $DbUlpOrdersMeta = new DbUlpOrdersMeta();
            $uid = $DbUlpOrdersMeta->getVar('meta_value', "meta_key='user_id' AND order_id=$order_id");
            $course_id = $DbUlpOrdersMeta->getVar('meta_value', "meta_key='course_id' AND  order_id=$order_id");
            if ($course_id && $uid){
                require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
                $UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
                $UsersCoursesActionsUlp->RemoveCourse($uid, $course_id);
            }
    }
    public function save_activity_finish_course($course_id=0, $uid=0){
        require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
  			$DbActivityUlp = new DbActivityUlp();
  			$time = date('Y-m-d H:i:s', time() );
  			$DbActivityUlp->saveItem($uid, $course_id, 'ulp_course', 'finish_course', '', $time, 1);
    }
    public function save_activity_finish_quiz($uid=0, $quiz_id=0, $relation_id=0){
        require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
        $DbActivityUlp = new DbActivityUlp();
        $time = date('Y-m-d H:i:s', time() );
        $DbActivityUlp->saveItem($uid, $quiz_id, 'ulp_quiz', 'finish_quiz', '', $time, 1);
    }
    public function save_activity_quiz_grade($uid=0, $quiz_id=0, $grade=0){
        require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
        $DbActivityUlp = new DbActivityUlp();
        $time = date('Y-m-d H:i:s', time() );
        $details = 'Student finish quiz: ' . DbUlp::getPostTitleByPostId($quiz_id) . ' and obtained grade: ' . $grade;
        $DbActivityUlp->saveItem($uid, $quiz_id, 'ulp_quiz', 'quiz_grade', $details, $time, 1);
    }
    public function save_activity_user_enroll($uid=0, $course_id=0){
        require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
        $DbActivityUlp = new DbActivityUlp();
        $time = date('Y-m-d H:i:s', time() );
        $DbActivityUlp->saveItem($uid, $course_id, 'ulp_course', 'user_enroll', '', $time, 1);
    }
    public function save_activity_user_gets_points($uid=0, $post_id=0, $action_type='', $points=0){
        require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
        $DbActivityUlp = new DbActivityUlp();
        $time = date('Y-m-d H:i:s', time() );
        $action_types = DbUlp::friendly_reward_points_action_type();
        $action_readable = isset($action_types[$action_type]) ? $action_types[$action_type] : $action_type;
        $details = 'Student win ' . $points . ' point/s for ' . $action_readable . '.';
        $post_type = DbUlp::getPostTypeById($post_id);
        $DbActivityUlp->saveItem($uid, $post_id, $post_type, 'user_gets_points', $details, $time, 1);
    }
    public function save_activity_user_receive_certificate($uid=0, $course_id=0, $certificate=0, $grade=0){
      require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
      $DbActivityUlp = new DbActivityUlp();
      $time = date('Y-m-d H:i:s', time() );
      $details = 'Student receive certificate for finish course ' . DbUlp::getPostTitleByPostId($course_id) . '.';
      $DbActivityUlp->saveItem($uid, $certificate, 'ulp_certificate', 'user_receive_certificate', $details, $time, 1);
    }
    public function save_activity_user_receive_badge($uid=0, $badge_id=0){
        require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
        $DbActivityUlp = new DbActivityUlp();
        $time = date('Y-m-d H:i:s', time() );
        $DbActivityUlp->saveItem($uid, $badge_id, 'ulp_badge', 'user_receive_badge', '', $time, 1);
    }

    public function sidebars($postId=0)
    {
        if (!$postId){
           get_sidebar();
        }

        /// generatepress theme
        $generatePress = get_post_meta($postId, '_generate-sidebar-layout-meta', true);
        if ($generatePress && $generatePress=='no-sidebar'){
            return '';
        }
        get_sidebar();
    }

    /// delete questions, lessons, quizes from courses
    public function doDeletePost($postId=0)
    {
        if (empty($postId)){
      		  return;
      	}
      	global $wpdb;
        $type = \DbUlp::getPostTypeById( $postId );
        if ( $type === 'ulp_question' ){
            // its question on some quiz - added since versin 3.6
            $quizesData = \DbUlp::getQuizesForQuestionId( $postId );
            if ( $quizesData ){
                require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
                $DbQuizQuestions = new \DbQuizQuestions();
                foreach ( $quizesData as $quizData ){
                    // remove questions from quizes
                    $DbQuizQuestions->deleteQuestionFromQuiz( $postId, $quizData['quiz_id'] );
                }
            }
        }

      	$query = "SELECT module_id FROM {$wpdb->prefix}ulp_course_modules_items WHERE item_id=$postId";
      	$moduleObject = $wpdb->get_results($query);
      	if (empty($moduleObject)){
            // if its not a questions, lesson or a quiz or the entity its not added to any module. out
      		  return;
      	}
      	$query = "DELETE FROM {$wpdb->prefix}ulp_course_modules_items WHERE item_id=$postId";
      	$wpdb->query($query);
      	foreach ($moduleObject as $module){
            $query = "SELECT COUNT(id) FROM {$wpdb->prefix}ulp_course_modules_items WHERE module_id={$module->module_id} ";
        		$count = $wpdb->get_var($query);
        		if ( !empty( $count ) ){
                // this mnodule has some extra items, so we dont want to delete it
                continue;
        		}
            // remove module if the module have no items
            $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ulp_courses_modules WHERE module_id=%d;", $module->module_id );
            $wpdb->query( $query );
            $query = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ulp_courses_modules_metas WHERE module_id=%d;", $module->module_id );
            $wpdb->query( $query );
      	}
    }

    public function onInsertCommentForCPT($commentId=0, $commentObject=null)
    {
        if (empty($commentId) || empty($commentObject) || empty($commentObject->comment_post_ID)){
            return false;
        }
        $postType = DbUlp::getPostTypeByComment($commentId);
        if ($postType=='ulp_announcement'){
            $object = new \Indeed\Ulp\Db\Announcements();
            $courseId = $object->getCourseIdByAnnouncement($commentObject->comment_post_ID);
            do_action('ulp_user_comment_on_announcement', $commentObject->user_id, $commentId, $commentObject->comment_post_ID, $courseId);
        } else if ($postType=='ulp_qanda'){
            $object = new \Indeed\Ulp\Db\QandA();
            $courseId = $object->getCourseIdByQanda($commentObject->comment_post_ID);

            if ( is_admin() || (DbUlp::isUserInstructor($commentObject->user_id) && DbUlp::isInstructorForCourse($commentObject->user_id, $courseId)) ){
                do_action('ulp_instructor_reply_for_student_question', $commentObject->user_id, $commentId, $commentObject->comment_post_ID, $courseId);
            } else {
                do_action('ulp_student_reply_on_instructor_reponse_for_question', $commentObject->user_id, $commentId, $commentObject->comment_post_ID, $courseId );
            }

        }
    }

    public function ulpChangePostStatus($newStatus='', $oldStatus='', $post=null)
    {
        if ($newStatus!='publish' || !$oldStatus || !$post){
            return;
        }
        if ($post->post_type!='ulp_course'){
            return;
        }
        do_action('ulp_administrator_has_published_course', $post);
    }

}
