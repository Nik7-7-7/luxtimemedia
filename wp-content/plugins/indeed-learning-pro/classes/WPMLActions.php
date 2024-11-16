<?php
namespace Indeed\Ulp;

class WPMLActions
{
    public function __construct()
    {
        if ( !indeed_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ){
            return false;
        }
        /// register notifications
        add_action( 'ulp_save_notification_action', array( $this, 'registerNotifications'), 99, 1 );

        /// courses modules
        add_action( 'ulp_course_modules_save', array( $this, 'saveCourseModules' ), 999, 1 );
        add_action( 'ulp_course_modules_update', array( $this, 'saveCourseModules' ), 999, 1 );

        /// save user language
        add_action( 'ulp_user_do_enroll', array( $this, 'saveUserLanguage' ), 999, 2 );
        add_action( 'ulp_set_user_new_role', array( $this, 'saveInstructorLanguage' ), 999, 2 );

        /// filter courses total number
        add_filter( 'ulp_filter_count_all_courses', array( $this, 'filterCountCourses'), 999, 1 );

        /// filter listing courses
        add_filter( 'ulp_filter_select_courses', array( $this, 'filterSelectCourses'), 999, 3 );

    }

    /// use ihc_save_notification_action just for trigger, we'll ignore the param
    public function registerNotifications( $notificationData=null )
    {
        global $wpdb;
        $query = "SELECT type, course_id, subject, message, pushover_message FROM {$wpdb->prefix}ulp_notifications;";
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return;
        }
        $domain = 'ulp';
        foreach ( $data as $object ){
                $name = $object->type . '_subject_' . $object->course_id;
            do_action( 'wpml_register_single_string', $domain, $name, $object->subject );
                $name = $object->type . '_message_' . $object->course_id;
            do_action( 'wpml_register_single_string', $domain, $name, $object->message );
                $name = $object->type . '_pushover_message_' . $object->course_id;
            do_action( 'wpml_register_single_string', $domain, $name, $object->pushover_message );
        }
    }

    public function saveCourseModules( $moduleId=0 )
    {
        global $wpdb;
        $query = "SELECT module_id, module_name FROM {$wpdb->prefix}ulp_courses_modules;";
        $data = $wpdb->get_results( $query );
        if ( !$data ){
            return;
        }
        $domain = 'ulp';
        foreach ( $data as $object ){
            $name = 'module_name_' . $object->module_id;
            do_action( 'wpml_register_single_string', $domain, $name, $object->module_name );
        }
    }


    public function saveUserLanguage( $uid=0, $courseId=0 )
    {
        if ( !$uid ){
            return false;
        }
        $currentLanguage = get_user_meta( $uid, 'uap_locale_code', true );
        if ( $currentLanguage ){
            return false;
        }
        $language = indeed_get_current_language_code();
        return update_user_meta( $uid, 'uap_locale_code', $language );
    }

    public function saveInstructorLanguage( $uid=0, $newRole='' )
    {
        if ( !$uid ){
            return false;
        }
        if ( !$newRole || ($newRole!='ulp_instructor-pending' && $newRole!='ulp_instructor') ){
            return false;
        }
        return $this->saveUserLanguage( $uid );
    }

    public function filterSelectCourses( $courses=array(), $limit=0, $offset=0 )
    {
        $lang = indeed_get_current_language_code();
        if ( !$lang ){
            return $courses;
        }
        $coursesForLanguage = \DbUlp::selectCoursesByLanguage( $lang, $limit, $offset );
        return $coursesForLanguage;
    }

    public function filterCountCourses( $number=0 )
    {
        $lang = indeed_get_current_language_code();
        if ( !$lang ){
            return $number;
        }
        $total = \DbUlp::countCoursesByLanguage( $lang );
        return $total;
    }

}
