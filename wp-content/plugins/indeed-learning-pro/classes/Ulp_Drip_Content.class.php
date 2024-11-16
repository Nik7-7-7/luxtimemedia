<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('Ulp_Drip_Content')){
   return;
}
class Ulp_Drip_Content{
    public function __construct(){
        add_filter('ulp_list_course_children', array($this, 'drip_content_to_course_list_items'), 10, 3);
        add_filter( 'ulp_filter_navigation_items', [ $this, 'navigation' ], 1, 6 );
    }
    public function drip_content_to_course_list_items($items=array(), $uid=0, $course_id=0){
        if ($uid && $items){
            require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
            $DbUserEntitiesRelations = new DbUserEntitiesRelations();
            $enroll_date = $DbUserEntitiesRelations->getRelationColValue($uid, $course_id, 'start_time');
            if ( $enroll_date !== null && $enroll_date !== false && $enroll_date !== '' ){
                $enroll_date = strtotime($enroll_date);
            }

            foreach ($items as $key=>$post_id){
                $do_block = $this->_check_block_with_drip_content($post_id, $uid, $course_id);
                if ($do_block){
                      unset($items[$key]);
                }
            }
        }
        return $items;
    }
    private function _check_block_with_drip_content($post_id=0, $uid=0, $course_id=0){
      $block = 0;
      $post_meta = DbUlp::getPostMetaGroup($post_id, 'drip_content');
      $now = time();
      if ($post_meta && !empty($post_meta['ulp_drip_content'])){
          /// 1 - after certain date
          /// 2 - specific date
          /// START ulp_drip_start_type
          require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
          $DbUserEntitiesRelations = new DbUserEntitiesRelations();
          $enroll_date = $DbUserEntitiesRelations->get_enroll_date($uid, $course_id);
          
          if ( $enroll_date !== null && $enroll_date !== '' && $enroll_date !== false ){
              $enroll_date = strtotime( $enroll_date );
          }

          if ($post_meta['ulp_drip_start_type']==1){
              if ($post_meta['ulp_drip_start_numeric_type']=='days'){
                  $start_time = $enroll_date + $post_meta['ulp_drip_start_numeric_value'] * 24 * 60 * 60;
              } else if ($post_meta['ulp_drip_start_numeric_type']=='weeks'){
                  $start_time = $enroll_date + $post_meta['ulp_drip_start_numeric_value'] * 7 * 24 * 60 * 60;
              } else {
                  $start_time = $enroll_date + $post_meta['ulp_drip_start_numeric_value'] * 30 * 24 * 60 * 60;
              }
              if ($start_time>$now){
                  /// to early
                  $block = 1;
              }
          } else {
              $start = strtotime($post_meta['ulp_drip_start_certain_date']);
              if ($start>$now){
                  $block = 1;
              }
          }
      }
      return $block;
  }

  /**
   * @param int
   * @param int
   * @param int
   * @param int
   * @param int
   * @return int
   */
  public function navigation( $nextPost=0, $courseId=0, $allItems=[], $currentPost=0, $uid=0, $direction='' )
  {
      if ( !$nextPost ){
          return $nextPost;
      }
      if ( !$this->_check_block_with_drip_content( $nextPost, $uid, $courseId ) ){
          return $nextPost;
      }
      while ( $nextPost !== false && $this->_check_block_with_drip_content( $nextPost, $uid, $courseId ) ){
          $nextPost = ulp_get_elem_from_array( $direction, $allItems, $nextPost );
      }
      return $nextPost;
  }

}
