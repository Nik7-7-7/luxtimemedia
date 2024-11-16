<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Grid_Students')){
   return;
}
class Ulp_Grid_Students{
    private $_output = '';
    private $_query_arg_name = 'ulp_students_page_num';
    private $_shortcode_attributes = null;
    public function __construct($shortcode_attributes=[]){
        require_once ULP_PATH . 'classes/grid/Ulp_Grid_Single_Student.class.php';
        require_once ULP_PATH . 'classes/grid/Indeed_Grid_Factory.class.php';
        $this->_shortcode_attributes = $shortcode_attributes;
        
       
        $total_items = $this->_count_total_items();
		if($total_items > $shortcode_attributes ['num_of_entries']){
       $total_items = $shortcode_attributes ['num_of_entries'];
    }
		
		$limit = $shortcode_attributes ['entries_per_page'];
		if($total_items < $shortcode_attributes ['entries_per_page']){
       $limit = $total_items;
    }
		
		$offset = isset($_GET[$this->_query_arg_name]) ? ((int)$_GET[$this->_query_arg_name]-1)*$limit : 0;
		
        $items = $this->_get_items($limit, $offset);
        $single_item_factory = new Ulp_Grid_Single_Student();
        $grid_factory = new Indeed_Grid_Factory($single_item_factory, $shortcode_attributes, $this->_query_arg_name, $total_items);
        $grid_factory->set_items($items);
        $this->_output = $grid_factory->get_output();
    }
    private function _get_items($limit=0, $offset=0){
        global $wpdb;
        if ($this->_shortcode_attributes ['order_by']=='reward_points'){
          $q = "
                SELECT DISTINCT(u.ID) as ID, u.user_email as user_email, u.user_registered as user_registered, p.points as points
                    FROM {$wpdb->users} u
                    INNER JOIN {$wpdb->prefix}ulp_user_entities_relations ulp
                    ON u.ID=ulp.user_id
                    INNER JOIN {$wpdb->prefix}ulp_reward_points p
                    ON ulp.user_id=p.uid
                    ORDER BY p.points {$this->_shortcode_attributes ['order_type']}
          ";
        } else {
          $q = "
                SELECT DISTINCT(u.ID) as ID, u.user_email as user_email, u.user_registered as user_registered, p.points as points
                    FROM {$wpdb->users} u
                    INNER JOIN {$wpdb->prefix}ulp_user_entities_relations ulp
                    ON u.ID=ulp.user_id
					LEFT JOIN {$wpdb->prefix}ulp_reward_points p
                    ON ulp.user_id=p.uid
                    ORDER BY {$this->_shortcode_attributes ['order_by']} {$this->_shortcode_attributes ['order_type']}
          ";
        }
        $q .= " LIMIT $limit OFFSET $offset;";
	    $data = $wpdb->get_results($q);
        if ($data){
          $fields = explode(',', $this->_shortcode_attributes ['fields']);
          foreach ($data as $key=>$object){
              if (in_array('feat_image', $fields))
                  $data [$key]->feat_image = DbUlp::getAuthorImage($object->ID);
              if (in_array('full_name', $fields))
                  $data [$key]->full_name = DbUlp::get_full_name($object->ID);
              if (!in_array('user_email', $fields))
                  unset($data [$key]->user_email);
			  if (!in_array('user_registered', $fields))
                  unset($data [$key]->user_registered);	
				  if (!in_array('points', $fields))
                  unset($data [$key]->points);  
          }
        }
        return $data;
    }
    private function _count_total_items(){
        global $wpdb;
		 if ($this->_shortcode_attributes ['order_by']=='reward_points'){
			  $q = "
                SELECT COUNT(DISTINCT(u.ID))
                    FROM {$wpdb->users} u
                    INNER JOIN {$wpdb->prefix}ulp_user_entities_relations ulp
                    ON u.ID=ulp.user_id
                    INNER JOIN {$wpdb->prefix}ulp_reward_points p
                    ON ulp.user_id=p.uid
          ";
		 }else{
        $q = "SELECT COUNT(DISTINCT(u.ID))
                FROM {$wpdb->users} u
                INNER JOIN {$wpdb->prefix}ulp_user_entities_relations ulp
                ON u.ID=ulp.user_id
				LEFT JOIN {$wpdb->prefix}ulp_reward_points p
                ON ulp.user_id=p.uid
        ";
		 }
        return $wpdb->get_var($q);
    }
    public function get_output(){
        return $this->_output;
    }
}
