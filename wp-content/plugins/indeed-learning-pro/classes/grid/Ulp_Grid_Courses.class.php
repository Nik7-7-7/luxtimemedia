<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Grid_Courses')){
   return;
}
class Ulp_Grid_Courses{
    private $_output = '';
    private $_query_arg_name ='ulp_courses_page_num';
    private $_shortcode_attributes = null;
    public function __construct($shortcode_attributes=[]){
        require_once ULP_PATH . 'classes/grid/Ulp_Grid_Single_Course.class.php';
        require_once ULP_PATH . 'classes/grid/Indeed_Grid_Factory.class.php';
        $this->_shortcode_attributes = $shortcode_attributes;
        $limit = $shortcode_attributes ['entries_per_page'];
        $offset = isset($_GET[$this->_query_arg_name]) ? (int)$_GET[$this->_query_arg_name] : 0;
        $total_items = $this->_count_total_items();
		if($total_items > $shortcode_attributes ['num_of_entries']){
       $total_items = $shortcode_attributes ['num_of_entries'];
    }
        if (empty($shortcode_attributes['slider_set'])){


          $limit = $shortcode_attributes['entries_per_page'];
		  if($total_items < $shortcode_attributes ['entries_per_page']){
         $limit = $total_items;
      }

		  if (empty($offset)){
            $offset = 0;
          } else {
            $offset = ( $offset - 1 ) *  $limit; //start from
          }
          if ($offset + $limit>$total_items){
            $limit = $total_items - $offset;
          }
        } else {
          $offset = 0;
          $limit = $total_items;
        }
        $items = $this->_get_items($limit, $offset);
        $single_item_factory = new Ulp_Grid_Single_Course();
        $grid_factory = new Indeed_Grid_Factory($single_item_factory, $shortcode_attributes, $this->_query_arg_name, $total_items);
        $grid_factory->set_items($items);
        $this->_output = $grid_factory->get_output();
    }
    private function _get_items($limit=0, $offset=0){
        global $wpdb;
        if (empty($this->_shortcode_attributes ['filter_by_cats'])){
            $q = "SELECT `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`, `menu_order`, `post_type`, `post_mime_type`, `comment_count`
                    FROM {$wpdb->posts}
                    WHERE
                    post_type='ulp_course'
                    AND
                    post_status='publish'
            ";
        } else {
            $q = "SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,a.comment_status,a.ping_status,a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,a.post_content_filtered,a.post_parent,a.guid,a.menu_order,a.post_type,a.post_mime_type,a.comment_count
                    FROM {$wpdb->posts} a
                    INNER JOIN {$wpdb->prefix}term_relationships b
                    ON a.ID=b.object_id
                    WHERE
                    post_type='ulp_course'
                    AND
                    b.term_taxonomy_id IN ({$this->_shortcode_attributes ['filter_by_cats']})
                    AND
                    a.post_status='publish'
            ";
        }
        if (!empty($this->_shortcode_attributes ['order_by']) && !empty($this->_shortcode_attributes ['order_type'])){
            $q .= " ORDER BY " . $this->_shortcode_attributes ['order_by'] . " " . $this->_shortcode_attributes ['order_type'];
        }
        $q .= " LIMIT $limit OFFSET $offset;";
        $data = $wpdb->get_results($q);
        if (empty($data)){
           return false;
        }
        $fields = explode(',', $this->_shortcode_attributes ['fields']);
        foreach ($data as $k=>$object){
            /// feature image
            if (in_array('feat_image', $fields)){
                $data [$k]->feat_image = DbUlp::getFeatImage($object->ID);//DbUlp::post_get_feat_image($object->ID);
            }
            /// price
            if (in_array('price', $fields)){
                $ulp_course_payment = get_post_meta($object->ID, 'ulp_course_payment', true);
                if ($ulp_course_payment==0){
                    $data [$k]->price = esc_html__('Free', 'ulp');
                } else {
                    $data [$k]->price = ulp_format_price(get_post_meta($object->ID, 'ulp_course_price', true));
                }
            }
            /// categories
            if (in_array('category', $fields)){
                $temp = DbUlp::getCategoriesForPost($object->ID, 'ulp_course_categories');
                $data [$k]->categories = implode(',', $temp);
            }
        }

        return $data;
    }
    private function _count_total_items(){
        global $wpdb;
        if (empty($this->_shortcode_attributes ['filter_by_cats'])){
            $q = "SELECT COUNT(ID)
                    FROM {$wpdb->posts}
                    WHERE
                    1=1
                    AND
                    post_type='ulp_course'
            ";
        } else {
            $q = "SELECT COUNT(a.ID)
                    FROM {$wpdb->posts} a
                    INNER JOIN {$wpdb->prefix}term_relationships b
                    ON a.ID=b.object_id
                    WHERE
                    1=1
                    AND
                    post_type='ulp_course'
                    AND
                    b.term_taxonomy_id IN ({$this->_shortcode_attributes ['filter_by_cats']})
                    AND a.post_status='publish'
            ";
        }
        return $wpdb->get_var($q);
    }
    public function get_output(){
        return $this->_output;
    }
}
