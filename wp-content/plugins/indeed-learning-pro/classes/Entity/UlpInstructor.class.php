<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('UlpInstructor')){
   return;
}
if (!class_exists('Ulp_User_Abstract')){
   require_once ULP_PATH . 'classes/Abstracts/Ulp_User_Abstract.class.php';
}
class UlpInstructor extends Ulp_User_Abstract
{

  		public function AverageRating()
  		{
        global $wpdb;
        $query = $wpdb->prepare("
                SELECT AVG(d.meta_value) as average
                  FROM {$wpdb->posts} a
                  INNER JOIN {$wpdb->postmeta} b
                  ON a.ID=b.meta_value
                  INNER JOIN {$wpdb->posts} c
                  ON c.ID=b.post_id
                  INNER JOIN {$wpdb->postmeta} d
                  ON b.post_id=d.post_id
                  WHERE
                  a.post_author=%d
                  AND
                  a.post_status='publish'
                  AND
                  a.post_type='ulp_course'
                  AND
                  c.post_status='publish'
                  AND
                  b.meta_key='_ulp_course_id'
                  AND
                  d.meta_key='_ulp_rating'
        ", $this->uid );
        $count = $wpdb->get_var($query);
        if (empty($count)){
          return 0;
        }
        $count = number_format($count, 1);
        return $count;
  		}

  		public function NumberOfStudents()
  		{
  			global $wpdb;
  			$query = $wpdb->prepare("
  								SELECT COUNT(b.user_id)
  									FROM {$wpdb->posts} a
  									INNER JOIN {$wpdb->prefix}ulp_user_entities_relations b
  									ON a.ID=b.entity_id
  									WHERE
  									a.post_author=%d
  									AND
  									a.post_status='publish'
  									AND
  									b.entity_type='ulp_course'
  			", $this->uid);
  			$count = $wpdb->get_var($query);
  			if (empty($count)){
  				return 0;
  			}
  			return $count;
  		}

  		public function NumberOfCourses()
  		{
  				global $wpdb;
          $query = $wpdb->prepare("
                  SELECT COUNT(ID)
                      FROM {$wpdb->posts}
                      WHERE
                      post_author=%d
                      AND
                      post_status='publish'
                      AND
                      post_type='ulp_course'
          ", $this->uid );
  				$count = $wpdb->get_var($query);
  				if (empty($count)){
  					return 0;
  				}
  				return $count;
  		}

  		public function NumberOfReviews()
  		{
  				global $wpdb;
          $query = $wpdb->prepare("
                  SELECT COUNT(DISTINCT b.post_id)
                  	FROM {$wpdb->posts} a
                  	INNER JOIN {$wpdb->postmeta} b
                  	ON a.ID=b.meta_value
                    INNER JOIN {$wpdb->posts} c
                    ON c.ID=b.post_id
                  	WHERE
                  	a.post_author=%d
                  	AND
                  	a.post_status='publish'
                    AND
                    a.post_type='ulp_course'
                  	AND
                  	c.post_status='publish'
                  	AND
                  	b.meta_key='_ulp_course_id'
          ", $this->uid );
          $count = $wpdb->get_var($query);
  				if (empty($count)){
  					return 0;
  				}
  				return $count;
  		}

    public function gettingAllInstructorData($attributes=[])
    {
        $notShow = [];
        if (isset($attributes['hide'])){
            $notShow = explode(',', $attributes['hide']);
        }
        $data = [
            'avatar' => (!in_array('avatar', $notShow)) ? $this->Avatar() : '',
            'number_of_courses' => (!in_array('number_of_courses', $notShow)) ? $this->NumberOfCourses() : '',
            'number_of_students' => (!in_array('number_of_students', $notShow)) ? $this->NumberOfStudents() : '',
            'average_rating' => (!in_array('average_rating', $notShow)) ? $this->AverageRating() : '',
            'number_of_reviews' => (!in_array('number_of_reviews', $notShow)) ? $this->NumberOfReviews() : '',
            'instructorName' => (!in_array('instructorName', $notShow)) ? $this->Name() : '',
            'biography' => (!in_array('biography', $notShow)) ? $this->Biography() : '',
            'permalink' => (!in_array('permalink', $notShow)) ? Ulp_Permalinks::getForInstructor($this->uid) : '',
        ];
        return $data;
    }


    public function setPostIdForInstructor($postId=0)
    {
        $this->uid = DbUlp::getInstructorUidByPost($postId);
        return $this;
    }
	 public function getInstructorID(){
  		if (isset($this->uid)){
          return $this->uid;
      }
  		return 0;
	 }

  public function setSingleInstructorPageSettings($uid=0, $metaData=[])
  {
      if (!$uid){
          return [];
      }
      $metas = $this->getSingleInstructorPageSettings($uid, true);
      foreach ($metas as $key=>$value){
          if (isset($metaData[$key])){
              update_user_meta( $uid, $key, ulp_sanitize_array($metaData[$key]) );
          }
      }
  }

  public function getSingleInstructorPageSettings($uid=0, $defaults=false)
  {
      if (!$uid){
          return [];
      }
      $metas = [
          'ulp_instructor_show_avatar'                => 1,
          'ulp_instructor_show_average_rating'        => 1,
          'ulp_instructor_show_number_of_reviews'     => 1,
          'ulp_instructor_show_instructor_name'       => 1,
          'ulp_instructor_show_biography'             => 1,
          'ulp_instructor_show_number_of_courses'     => 1,
          'ulp_instructor_show_number_of_students'    => 1,
      ];
      if ($defaults){
          return $metas;
      }
      foreach ($metas as $key=>$value){
          $temp = get_user_meta($uid, $key, true);
          if ($temp!==null && $temp!=''){
              $metas[$key] = $temp;
          }
      }
      return $metas;
  }

  public function setInstructorNotificationSettings($uid=0, $metaData=[])
  {
      if (!$uid){
          return [];
      }
      $metas = $this->getInstructorNotificationSettings($uid, true);
      foreach ($metas as $key=>$value){
          if (isset($metaData[$key])){
              //$metaData[$key] = sanitize_text_field( $metaData[$key] );
              update_user_meta( $uid, $key, sanitize_text_field($metaData[$key]) );
          }
      }
  }

  public function getInstructorNotificationSettings($uid=0, $defaults=false)
  {
      if (!$uid){
          return [];
      }
      $metas = [
          'ulp_instructor_notifications-student_reply_on_question'              => 1,
          'ulp_instructor_notifications-on_student_ask_question'                => 1,
          'ulp_instructor_notifications-on_student_comment_on_announcement'     => 1,
          'ulp_instructor_notifications-user_enroll_course'                     => 1,
      ];
      if ($defaults){
          return $metas;
      }
      foreach ($metas as $key=>$value){
          $temp = get_user_meta($uid, $key, true);
          if ($temp!==null && $temp!=''){
              $metas[$key] = $temp;
          }
      }
      return $metas;
  }

}
