<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Give_Badges')){
   return;
}
class Ulp_Give_Badges{
    private $user_badge_db_object = null;
    private $badges_db_object = null;
    public function __construct(){
        require_once ULP_PATH . 'classes/Db/Db_Ulp_Student_Badges.class.php';
        require_once ULP_PATH . 'classes/Db/Db_Ulp_Badges.class.php';
        $this->badges_db_object = new Db_Ulp_Badges();
        $this->user_badge_db_object = new Db_Ulp_Student_Badges();
        /// give badges on events
        add_action('ulp_user_complete_course', array($this, 'on_finish_course'), 20, 2);
        add_action('ulp_finish_quiz', array($this, 'on_finish_quiz'), 20, 3);
        add_action('ulp_general_cron', array($this, 'on_cron'), 10, 0);
    }
    /// STATIC
    public function on_finish_course($course_id=0, $uid=0){
        $badge_id = $this->badges_db_object->getByRuleAndType('{"rule_type":"finish_course","rule_value":"' . $course_id . '"}', 'static');
        $this->_give_badge($uid, $badge_id);
    }
    public function on_finish_quiz($uid=0, $quiz_id=0, $relation_id=0){
        $badge_id = $this->badges_db_object->getByRuleAndType('{"rule_type":"finish_quiz","rule_value":"' . $quiz_id . '"}', 'static');
        $this->_give_badge($uid, $badge_id);
    }
    private function _give_badge($uid=0, $badge_id=0){
      if ($badge_id){
          do_action('ulp_user_receive_badge', $uid, $badge_id);
          $this->user_badge_db_object->save($uid, $badge_id);
      }
    }
    /// TIER
    public function on_cron(){
        $this->_give_for_minimum_points();
    }
    public function _give_for_minimum_points(){
      $badges = $this->badges_db_object->getAllByRuleAndType('reward_points', 'tier');
      if ($badges){
          foreach ($badges as $object){
              $temp = json_decode($object->rule, TRUE);
              $badges_arr [$temp['rule_value']] = $object->id;
          }
          if ($badges_arr){
              krsort($badges_arr);
              $excluded_badges = array();
              foreach ($badges_arr as $min_points => $badge_id){
                  $excluded_badges [] = $badge_id;
                  $users = $this->badges_db_object->getUsersMinPointsExcludedBadges($min_points, $excluded_badges);
				  if ($users){
                      foreach ($users as $user_data){
                          $this->user_badge_db_object->save($user_data->uid, $badge_id);
                      }
                  }
              }
          }
      }
    }
}
