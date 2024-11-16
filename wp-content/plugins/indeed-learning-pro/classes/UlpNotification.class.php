<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('UlpNotification')){
   return;
}
class UlpNotification{

    private static $notification_data = array();
    private static $from_email = '';
    private static $from_name = '';
    private static $error = '';
    private static $admin_case = [
                                    'admin_user_become_instructor',
                                    'admin_user_enroll_course',
                                    'admin_user_complete_course',
                                    'admin_user_complete_lesson',
                                    'admin_scheduled_lesson_user',
                                    'admin_user_pass_quiz',
                                    'admin_user_fails_quiz',
                                    'admin_user_complete_quiz',
                                    'admin_before_course_expires',
                                    'admin_new_student_on_course',
                                    'admin_instructor_edit_course',
                                    'admin_instructor_create_course',
    ];

    // @param array( 'from' => '', 'course_id' => 0, 'uid' => 0, 'notification_type' => '', 'dynamic_data' => array )
    public static function send( $attr=array() ){
        require_once ULP_PATH . 'classes/Db/DbNotificationsUlp.class.php';
        if (empty($attr['dynamic_data'])){
            $attr['dynamic_data'] = array();
        }
        /// FROM email
        if (!empty($attr['from'])){
            $from_email = $attr['from'];
        } else {
            if (empty(self::$from_email)){
                self::$from_email = get_option('ulp_notifications_from_email_addr');
            }
            if (empty(self::$from_email)){
                self::$from_email = get_option('admin_email');
            }
            $from_email = self::$from_email;
        }
        /// from name
        if (!empty($attr['from'])){
            $from_name = $attr['from'];
        } else {
            if (empty(self::$from_name)){
                self::$from_name = get_option('ulp_notifications_from_name');
                self::$from_name = stripslashes(self::$from_name);
                if (!self::$from_name){
                    self::$from_name = get_option("blogname");
                }
            }
            $from_name = self::$from_name;
        }
        ///set user email
        if (empty($attr['uid'])){
            self::$error = 'Send to user id not set.';
            return FALSE;
        } else {
            $user_email = DbUlp::get_user_col_value($attr['uid'], 'user_email');
            if (empty($user_email)){
                self::$error = 'User has no email address.';
                return FALSE;
            }
        }
        /// NOTIFICATION TYPE
        if (empty($attr['notification_type'])){
            self::$error = 'Notification type not set.';
            return FALSE;
        } else {
            $notification_type = $attr['notification_type'];
        }
        /// COURSE ID can be 0
        $course_id = isset($attr['course_id']) ? $attr['course_id'] : 0;
        /// getting message content and title
        if (isset(self::$notification_data[$notification_type][$course_id])){
            $message_data = self::$notification_data[$notification_type][$course_id]; /// no need for another query...
        } else {
            $notification_content_object = new DbNotificationsUlp();
            $message_data = $notification_content_object->getByTypeAndCourseId($course_id, $notification_type);
            if ($message_data!=null){
                self::$notification_data[$notification_type][$course_id] = $message_data;
            } else {
                self::$error = 'No message and title for course_id=' . $course_id . ' and notification type: '. $notification_type . '.';
                return FALSE;
            }
        }

        /// wpml here
        $message_data = self::wpmlFilterData( $attr['uid'], $course_id, $notification_type, $message_data );

        /// replace constants - ulp_replace_constants( $string='', $uid=0, $course_id=0, $dynamic_data=array() )
        $message_data['message'] = ulp_replace_constants($message_data['message'], $attr['uid'], $course_id, $attr['dynamic_data']);
        $message_data['message'] = stripslashes(htmlspecialchars_decode(indeed_format_str_like_wp($message_data['message'])));
        $message_data['message'] = apply_filters('ulp_send_notification_filter_message', $message_data['message'], $attr['uid'], $attr['course_id'], $attr['notification_type']);
        $message_data['message'] = "<html><head></head><body>" . $message_data['message'] . "</body></html>";
        $message_data['subject'] = ulp_replace_constants($message_data['subject'], $attr['uid'], $course_id, $attr['dynamic_data']);
        $message_data['subject'] = stripslashes(htmlspecialchars_decode($message_data['subject']));
        $sent = FALSE;
        if ($message_data['message'] && $user_email){
          if (in_array($notification_type, self::$admin_case)){
            /// SEND NOTIFICATION TO ADMIN, (we change the destination)
            $admin_email = get_option('ulp_notifications_admin_email');
            if (empty($admin_email)){
              $user_email = get_option('admin_email');
            } else {
              $user_email = $admin_email;
            }
          }
          $headers = array();
          if (!empty($from_email) && !empty($from_name)){
            $headers[] = "From: $from_name <$from_email>";
          }
          $headers[] = 'Content-Type: text/html; charset=UTF-8';
          $sent = wp_mail($user_email, $message_data['subject'], $message_data['message'], $headers);
        }
        self::pushover($attr);
        return $sent;
    }

    public static function getError(){
        return self::$error;
    }

    public static function pushover($attr=array()){
        $send_to_admin = in_array($attr['notification_type'], self::$admin_case) ? true : false;
        require_once ULP_PATH . 'classes/Ulp_Pushover.class.php';
        $pushover_object = new Ulp_Pushover();
        $pushover_object->send_notification($attr['uid'], $attr['course_id'], $attr['notification_type'], $send_to_admin);
    }

    private static function wpmlFilterData( $uid=0, $courseId=0, $notificationType='', $messageData=array() )
    {
        $languageCode = get_user_meta( $uid, 'ulp_locale_code', true );
        if ( !$languageCode ){
            return $messageData;
        }
        $domain = 'ulp';
        $wmplName = $notificationType . '_subject_' . $courseId;
        $messageData['subject'] = apply_filters( 'wpml_translate_single_string', $messageData['subject'], $domain, $wmplName, $languageCode );
        $wmplName = $notificationType . '_message_' . $courseId;
        $messageData['message'] = apply_filters( 'wpml_translate_single_string', $messageData['message'], $domain, $wmplName, $languageCode );
        $wmplName = $notificationType . '_pushover_message_' . $courseId;
        $messageData['pushover_message'] = apply_filters( 'wpml_translate_single_string', $messageData['pushover_message'], $domain, $wmplName, $languageCode );
        return $messageData;
    }

}
