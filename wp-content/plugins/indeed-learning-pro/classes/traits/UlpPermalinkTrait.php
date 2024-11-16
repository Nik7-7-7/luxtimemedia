<?php
if (!defined('ABSPATH')){
   exit();
}
if (trait_exists('UlpPermalinkTrait')){
   return;
}

trait UlpPermalinkTrait
{
    protected static $_setOptions = true;
    protected static $_optionNames = [
          '_courseQuerySlug'        => 'ulp_course_custom_query_var',
          '_lessonQuerySlug'        => 'ulp_lesson_custom_query_var',
          '_quizQuerySlug'          => 'ulp_quiz_custom_query_var',
          '_questionQuerySlug'      => 'ulp_question_custom_query_var',
          '_announcementQuerySlug'  => 'ulp_announcement_custom_query_var',
          '_qandaQuerySlug'         => 'ulp_qanda_custom_query_var',
    ];
    protected static $_courseQuerySlug = 'single-course';
    protected static $_lessonQuerySlug = 'course-lesson';
    protected static $_quizQuerySlug = 'course-quiz';
    protected static $_questionQuerySlug = 'quiz-question';
    protected static $_announcementQuerySlug = 'course-announcement';
    protected static $_qandaQuerySlug = 'course-qanda';
    protected static $_baseUrl = '';

    protected static function _setVariables()
    {
        foreach (self::$_optionNames as $key=>$varibleName){
            $temporary = get_option($varibleName);
            if ($temporary){
                self::$$key = $temporary;
            }
        }
        self::$_baseUrl = get_option('home');
        if (self::$_baseUrl && isset(self::$_baseUrl[strlen(self::$_baseUrl)-1]) && self::$_baseUrl[strlen(self::$_baseUrl)-1]!='/'){
            self::$_baseUrl .= '/';
        }
        self::$_setOptions = false;
    }
}
