<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('Ulp_Finish_Course')){
   return;
}
class Ulp_Finish_Course{
    private $course_id = 0;
    private $uid = 0;
    public function __construct($course_id=0, $uid=0){
        $this->course_id = $course_id;
        $this->uid = $uid;
    }
    public function run(){
      if ($this->course_id && $this->uid){
    			/// save to db
    			require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
    			$object = new UsersCoursesActionsUlp();
    			$object->WriteCourseResult($this->uid, $this->course_id);
          do_action('ulp_user_complete_course', $this->course_id, $this->uid);
  		}
    }
    public function getLandingPage(){
        return '';
    }
}
