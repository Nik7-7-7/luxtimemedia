<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Watch_List')){
   return;
}
/*
Courses are hold in usermeta  'ulp_watch_list' as array
[23, 47, ...]
*/
class Ulp_Watch_List{
  private $user_meta_name = 'ulp_watch_list';
	public function __construct(){}
	public function save($uid=0, $course_id=0){
      $data = $this->getAll($uid);
      if (empty($data)){
         $data = array();
      }
      if (!in_array($course_id, $data)){
          $data[] = $course_id;
      }
      update_user_meta($uid, $this->user_meta_name, $data);
  }
	public function delete($uid=0, $course_id=0){
      $data = $this->getAll($uid);
      $key = array_search($course_id, $data);
      if ($key!==FALSE){
          unset($data[$key]);
      }
      update_user_meta($uid, $this->user_meta_name, $data);
  }
	public function user_got_course_as_fav($uid=0, $course_id=0){
      $data = $this->getAll($uid);
      if ($data && in_array($course_id, $data)){
          return TRUE;
      }
      return FALSE;
  }
	public function getAll($uid=0, $width_details=FALSE){
      $data = get_user_meta($uid, $this->user_meta_name, TRUE);
      if ($data && $width_details){
          $array = array();
          foreach ($data as $course_id){
              $array[$course_id]['title'] = DbUlp::getPostTitleByPostId($course_id);
              $array[$course_id]['permalink'] = get_permalink($course_id);
          }
          return $array;
      }
      return $data;
  }
}
