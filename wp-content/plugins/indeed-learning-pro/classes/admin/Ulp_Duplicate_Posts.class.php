<?php
/*
$object = new Ulp_Duplicate_Posts( $post_id );
$object->Run();
*/
if (!class_exists('Indeed_Duplicate_Posts')){
   require_once ULP_PATH . 'classes/Abstracts/Indeed_Duplicate_Posts.class.php';
}
if (class_exists('Ulp_Duplicate_Posts')){
   return;
}
class Ulp_Duplicate_Posts extends Indeed_Duplicate_Posts{
    protected $_copy_post_id = 0;
    protected $_created_post_id = 0;
    protected $_error = '';
    protected $_post_type = '';
    protected function _duplicate_custom_data(){
        global $wpdb;
        switch ($this->_post_type){
            case 'ulp_course':
              /// course modules
              $q = "SELECT module_name, module_order, status, module_id FROM {$wpdb->prefix}ulp_courses_modules WHERE course_id={$this->_copy_post_id};";
              $data = $wpdb->get_results($q);
              if ($data){
              	foreach ($data as $object){
              		$q = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ulp_courses_modules VALUES (NULL, %s, %s, %s, %s );",
                                          $object->module_name, $this->_created_post_id, $object->module_order, $object->module_status
                  );
              		$wpdb->query($q);
              		$new_module_id = $wpdb->insert_id;
              		/// modules items
              		$q = "SELECT `id`, `module_id`, `course_id`, `item_id`, `item_type`, `item_order`, `status` FROM {$wpdb->prefix}ulp_course_modules_items WHERE module_id={$object->module_id};";
              		$inside_data = $wpdb->get_results($q);
              		if ($inside_data){
              			foreach ($inside_data as $second_object){
                      $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ulp_course_modules_items
                                                    VALUES(null, %d, %d, %d, %s, %d, %s );",
                                                    $new_module_id, $this->_created_post_id, $second_object->item_id, $second_object->item_type, $second_object->item_order, $second_object->status
                      );
              				$wpdb->query( $query );
              			}
              		}
              	}
              }
              $data_from_db = DbUlp::getPostMetaGroup($this->_copy_post_id, 'course_special_settings');
              break;
            case 'ulp_certificate':
              $data_from_db = array();
              break;
            case 'ulp_question':
              $post_meta = DbUlp::getPostMetaGroup($this->_copy_post_id, 'answer_settings');
              $data_from_db = DbUlp::getPostMetaGroup($this->_copy_post_id, 'questions_special_settings');
              $data_from_db = $data_from_db + $post_meta;
              break;
            case 'ulp_quiz':
              $query = $wpdb->prepare( "SELECT question_id, item_order, status FROM {$wpdb->prefix}ulp_quizes_questions WHERE quiz_id=%d ;", $this->_copy_post_id );
              $data = $wpdb->get_results( $query );
              if ($data){
                  foreach ($data as $object){
                      $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ulp_quizes_questions VALUES(null, %s, %s, %s, %s );",
                                                    $this->_created_post_id, $object->question_id, $object->item_order, $object->status
                      );
                      $wpdb->query( $query );
                  }
              }
              $data_from_db = DbUlp::getPostMetaGroup($this->_copy_post_id, 'quiz_special_settings');
              break;
            case 'ulp_lesson':
              $data_from_db = DbUlp::getPostMetaGroup($this->_copy_post_id, 'lesson_special_settings');
              break;
        }
        /// SPECIAL SETTINGS
        if ($data_from_db){
            foreach ($data_from_db as $k=>$v){
                update_post_meta($this->_created_post_id, $k, $v);
            }
        }
        ///
    }
}
