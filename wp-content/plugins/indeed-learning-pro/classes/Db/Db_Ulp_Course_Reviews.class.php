<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('Db_Ulp_Course_Reviews')){
   return;
}
class Db_Ulp_Course_Reviews extends DbIndeedAbstract{
    public function getAll(){}

    public function getAllByCourse($course_id=0, $limit=10, $offset=0){
        global $wpdb;
        $course_id = sanitize_text_field($course_id);
        $q = $wpdb->prepare( "SELECT DISTINCT(a.post_id), b.post_title as title, b.post_content as content, b.post_date as created_time, c.meta_value as full_name, d.meta_value as stars, e.meta_value as user_id
                  FROM {$wpdb->postmeta} a
                  INNER JOIN {$wpdb->posts} b ON a.post_id=b.ID
                  INNER JOIN {$wpdb->postmeta} c ON b.ID=c.post_id
                  INNER JOIN {$wpdb->postmeta} d ON b.ID=d.post_id
                  INNER JOIN {$wpdb->postmeta} e ON b.ID=e.post_id
                  WHERE
                  a.meta_value=%d
                  AND a.meta_key='_ulp_course_id'
                  AND c.meta_key='_ulp_student_full_name'
                  AND d.meta_key='_ulp_rating'
				          AND e.meta_key='_uid'
                  AND b.post_status='publish'
                  ORDER BY b.ID
                  DESC
                  LIMIT %d OFFSET %d
                  ;", $course_id, $limit, $offset );
        $data = $wpdb->get_results($q);

		foreach($data as $key => $object):
			$data[$key]->authorImage = DbUlp::getAuthorImage($object->user_id);
		endforeach;

		return $data;
    }
    public function countAllByCourse($course_id=0){
        global $wpdb;
        $course_id = sanitize_text_field($course_id);
        $q = $wpdb->prepare( "SELECT COUNT(DISTINCT a.post_id) as c
                                  FROM {$wpdb->postmeta} a
                                  INNER JOIN {$wpdb->posts} b
                                  ON a.post_id=b.ID
                                  WHERE
                                  a.meta_value=%d
                                  AND a.meta_key='_ulp_course_id'
        ;", $course_id );
        $data = $wpdb->get_var($q);
        return $data;
    }
    public function getRatingAverageForCourse($course_id=0){
      global $wpdb;
      $course_id = sanitize_text_field($course_id);
      $q = $wpdb->prepare("SELECT AVG(b.meta_value) as average
                              FROM {$wpdb->postmeta} a
                              INNER JOIN {$wpdb->postmeta} b
                              ON a.post_id=b.post_id
                              INNER JOIN {$wpdb->posts} c
                              ON a.post_id=c.ID
                              WHERE
                              a.meta_value=%d
                              AND
                              a.meta_key='_ulp_course_id'
                              AND
                              b.meta_key='_ulp_rating'
                              AND
                              c.post_status='publish'
      ;", $course_id );
      $data = $wpdb->get_var($q);
      return $data;
    }
    public function addNew($course_id=0, $uid=0, $stars=0, $title='', $message='', $status='pending'){
        $post_id = wp_insert_post(array(
            'post_type' => 'ulp_course_review',
            'post_title' => sanitize_textarea_field($title),
            'post_content' => sanitize_textarea_field($message),
            'post_status' => sanitize_text_field($status)
        ));
        if ($post_id){
            update_post_meta($post_id, '_ulp_rating', sanitize_text_field($stars) );
            update_post_meta($post_id, '_ulp_course_id', sanitize_text_field($course_id) );
            update_post_meta($post_id, '_uid', sanitize_text_field($uid) );
            update_post_meta($post_id, '_ulp_student_full_name', DbUlp::getUserFulltName(sanitize_text_field($uid)) );
        }
        return $post_id;
    }
    public function review_metas($review_id=0){
        $keys = array(
            '_ulp_rating',
            '_ulp_course_id',
            '_uid',
            '_ulp_student_full_name'
        );
        $data = array();
        foreach ($keys as $key){
            $data[$key] = get_post_meta($key, sanitize_text_field($review_id), true);
        }
        return $data;
    }
    public function user_writed_course_review_for_course($uid=0, $cid=0){
        global $wpdb;
        $uid = sanitize_text_field($uid);
        $cid = sanitize_text_field($cid);
        $q = $wpdb->prepare("SELECT a.post_id FROM {$wpdb->postmeta} a
                  INNER JOIN {$wpdb->postmeta} b
                  ON a.post_id=b.post_id
                  WHERE
                  a.meta_value=%d
                  AND
                  a.meta_key='_ulp_course_id'
                  AND
                  b.meta_value=%d
                  AND
                  b.meta_key='_uid'
        ", $cid, $uid );
        return $wpdb->get_var($q);
    }

    public function getCountsOfStarPossibleValues($course_id=0)
    {
        global $wpdb;
        $array = [];
        $course_id = sanitize_text_field($course_id);
        $query = $wpdb->prepare("
            SELECT COUNT(b.meta_value) as c, b.meta_value as rating_value
                        FROM {$wpdb->postmeta} a
                        INNER JOIN {$wpdb->postmeta} b
                        ON a.post_id=b.post_id
                        INNER JOIN {$wpdb->posts} c
                        ON a.post_id=c.ID
                        WHERE
                        a.meta_value=%d
                        AND
                        a.meta_key='_ulp_course_id'
                        AND
                        b.meta_key='_ulp_rating'
                        AND
                        c.post_status='publish'
        		GROUP BY rating_value", $course_id );
        $data = $wpdb->get_results($query);
        if ($data){
            foreach ($data as $object){
                $array[$object->rating_value] = $object->c;
            }
            return $array;
        }
        return false;
    }
}
