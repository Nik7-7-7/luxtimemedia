<?php
namespace Indeed\Ulp\Db;
if (!defined('ABSPATH')){
   exit();
}

class DbComingSoon
{
    private static $enabled = false;

    public function __construct()
    {
        self::$enabled = get_option('ulp_coming_soon_enabled');
    }

    public function isEnabledOnCourse($courseId=0)
    {
        if (empty($courseId)){
            return false;
        }
        if (!self::$enabled){
            return false;
        }
        $enabledOnThisCourse = get_post_meta($courseId, 'ulp_course_coming_soon_enabled', true);
        if (!$enabledOnThisCourse){
            return false;
        }
        $endTime = get_post_meta($courseId, 'ulp_course_coming_soon_end_time', true);
        $endTime = strtotime($endTime);
        $currentTime = time();
        if ($endTime<$currentTime){
            return false;
        }
        return true;
    }
}
