<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Permalinks')){
   return;
}
require_once ULP_PATH . 'classes/traits/UlpPermalinkTrait.php';

class Ulp_Permalinks
{
  use UlpPermalinkTrait;

  protected static $_permalinkStructure = null;

  public static function getForCourse($courseId=0)
  {
      if (self::$_setOptions){
         self::_setVariables();
      }
      $permalink = get_permalink($courseId);
      //return $permalink;
      
      if (!self::_usePrettyPermalink()){
         return $permalink;
      }

      $courseSlug = DbUlp::getPostNameById($courseId);
      if ( !empty($courseSlug) ){
          $permalink = self::$_baseUrl
                        . self::$_courseQuerySlug . '/' . $courseSlug . '/';
      }
      return $permalink;
  }

  /// it returns : www.exemple.com/ulp-course/{CourseSlug}/ulp-lesson/{LessonSlug}
  public static function getForLesson($lessonId=0, $courseId=0)
  {
      if (self::$_setOptions){
         self::_setVariables();
      }
      $permalink = get_permalink($lessonId);
      if (!self::_usePrettyPermalink()){
         return $permalink;
      }

      $lessonSlug = DbUlp::getPostNameById($lessonId);
      $courseSlug = DbUlp::getPostNameById($courseId);
      if (!empty($courseSlug) && !empty($lessonSlug)){
          $permalink = self::$_baseUrl
                        . self::$_courseQuerySlug . '/' . $courseSlug . '/'
                        . self::$_lessonQuerySlug . '/' . $lessonSlug . '/';
      }
      return $permalink;
  }

  /// it returns : www.exemple.com/ulp-course/{CourseSlug}/ulp-quiz/{QuizSlug}
  public static function getForQuiz($quizId=0, $courseId=0)
  {
      if (self::$_setOptions){
         self::_setVariables();
      }
      $permalink = get_permalink($quizId);
      if (!self::_usePrettyPermalink()){
         return $permalink;
      }

      $quizSlug = DbUlp::getPostNameById($quizId);
      $courseSlug = DbUlp::getPostNameById($courseId);
      if (!empty($courseSlug) && !empty($quizSlug)){
          $permalink = self::$_baseUrl
                        . self::$_courseQuerySlug . '/' . $courseSlug . '/'
                        . self::$_quizQuerySlug . '/' . $quizSlug . '/';
      }
      return $permalink;
  }

  /// it returns : www.exemple.com/ulp-course/{CourseSlug}/ulp-quiz/{QuizSlug}/ulp-question/{QuestionSlug}
  public static function getForQuestion($questionId=0, $quizId=0, $courseId=0)
  {
      if (self::$_setOptions){
         self::_setVariables();
      }
      $permalink = get_permalink($questionId);
      if (!self::_usePrettyPermalink()){
          $permalink = get_permalink($quizId);
          $url = add_query_arg(self::$_questionQuerySlug, $questionId, $permalink);
          return $url;
      }

      $questionSlug = $questionId; /// we don't use question slug because questions dont have slugs (slugs are plain id)
      $quizSlug = DbUlp::getPostNameById($quizId);
      $courseSlug = DbUlp::getPostNameById($courseId);
      if (!empty($quizSlug) && !empty($courseSlug) && !empty($questionSlug)){
          $permalink = self::$_baseUrl
                        . self::$_courseQuerySlug . '/' . $courseSlug . '/'
                        . self::$_quizQuerySlug . '/' . $quizSlug . '/'
                        . self::$_questionQuerySlug . '/' . $questionSlug . '/';
      }
      return $permalink;
  }

  public static function getForInstructor($uid=0)
  {
      $cptId = DbUlp::getPostIdForInstructor($uid);
      if (!$cptId){
          return '';
      }
      $permalink = get_permalink($cptId);
      return $permalink;
  }

  public static function getForAnnouncement($postId=0)
  {
      if (self::$_setOptions){
         self::_setVariables();
      }
      $permalink = get_permalink($postId);
      if (!self::_usePrettyPermalink()){
          return $permalink;
      }

      $announcementSlug = DbUlp::getPostNameById($postId);
      $object = new \Indeed\Ulp\Db\Announcements();
      $courseId = $object->getCourseIdByAnnouncement($postId);
      $courseSlug = DbUlp::getPostNameById($courseId);
      if (!empty($courseSlug) && !empty($announcementSlug)){
          $permalink = self::$_baseUrl
                        . self::$_courseQuerySlug . '/' . $courseSlug . '/'
                        . self::$_announcementQuerySlug . '/' . $announcementSlug . '/';
      }
      return $permalink;
  }

  public static function getForQanda($postId=0)
  {
      if (self::$_setOptions){
         self::_setVariables();
      }
      $permalink = get_permalink($postId);
      if (!self::_usePrettyPermalink()){
          return $permalink;
      }

      $qandaSlug = DbUlp::getPostNameById($postId);
      $object = new \Indeed\Ulp\Db\QandA();
      $courseId = $object->getCourseIdByQanda($postId);
      $courseSlug = DbUlp::getPostNameById($courseId);
      if (!empty($courseSlug) && !empty($qandaSlug)){
          $permalink = self::$_baseUrl
                        . self::$_courseQuerySlug . '/' . $courseSlug . '/'
                        . self::$_qandaQuerySlug . '/' . $qandaSlug . '/';
      }
      return $permalink;
  }

  protected static function _usePrettyPermalink()
  {
      if (self::$_permalinkStructure===null){
          self::$_permalinkStructure = get_option('permalink_structure');
      }
      return self::$_permalinkStructure;
  }



}
