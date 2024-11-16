<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Query_Vars')){
   return;
}
require_once ULP_PATH . 'classes/traits/UlpPermalinkTrait.php';

class Ulp_Query_Vars
{
    use UlpPermalinkTrait;

    public function __construct()
    {
        self::_setVariables();
    }

    public function doRegister($withRewrite=false)
    {
        add_action('init', [$this, 'customRewriteRules']);
        add_action('init', [$this, 'customRewriteTags']);
        if ($withRewrite){
            flush_rewrite_rules();
        }
    }

    public function customRewriteTags()
    {
        add_rewrite_tag('%' . self::$_courseQuerySlug . '%', '([^&]+)');
        add_rewrite_tag('%' . self::$_questionQuerySlug . '%', '([^&]+)');
    }

    public function customRewriteRules()
    {
        /// for lessons
        add_rewrite_rule(
            '^' . self::$_courseQuerySlug . '/([^/]*)/' . self::$_lessonQuerySlug . '/([^/]*)/?',
            'index.php?ulp_lesson=$matches[2]&' . self::$_courseQuerySlug . '=$matches[1]',
            'top'
        );
        /// for question
        add_rewrite_rule(
            '^' . self::$_courseQuerySlug . '/([^/]*)/' . self::$_quizQuerySlug . '/([^/]*)/' . self::$_questionQuerySlug . '/([^/]*)/?' ,
            'index.php?ulp_quiz=$matches[2]&' . self::$_courseQuerySlug . '=$matches[1]&' . self::$_questionQuerySlug . '=$matches[3]',
            'top'
        );
        /// for quizes
        add_rewrite_rule(
            '^' . self::$_courseQuerySlug . '/([^/]*)/' . self::$_quizQuerySlug . '/([^/]*)/?',
            'index.php?ulp_quiz=$matches[2]&' . self::$_courseQuerySlug . '=$matches[1]',
            'top'//'bottom'
        );

        /// announcement
        add_rewrite_rule(
            '^' . self::$_courseQuerySlug . '/([^/]*)/' . self::$_announcementQuerySlug . '/([^/]*)/?',
            'index.php?ulp_announcement=$matches[2]&' . self::$_courseQuerySlug . '=$matches[1]',
            'top'
        );

        /// Q&A
        add_rewrite_rule(
            '^' . self::$_courseQuerySlug . '/([^/]*)/' . self::$_qandaQuerySlug . '/([^/]*)/?',
            'index.php?ulp_qanda=$matches[2]&' . self::$_courseQuerySlug . '=$matches[1]',
            'top'
        );

        /// for courses
        add_rewrite_rule(
            '^' . self::$_courseQuerySlug . '/([^/]*)/?',
            'index.php?ulp_course=$matches[1]',
            'top'
        );
    }
}
