<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('Db_Ulp_Badges')){
   return;
}
class Db_Ulp_Badges extends DbIndeedAbstract{
/*
id BIGINT(20) NOT NULL,
badge_title VARCHAR(255),
badge_content LONGTEXT,
badge_image VARCHAR(400),
badge_type VARCHAR(200),
rule TEXT,
create_date TIMESTAMP NOT NULL DEFAULT 0
*/
  	/**
  	 * @var string
  	 */
  	protected $table = '';
  	/**
  	 * @param none
  	 * @return none
  	 */
  	public function __construct(){
  		global $wpdb;
  		$this->table = $wpdb->prefix . 'ulp_badges';
  	}
    public function save($data=array()){
        global $wpdb;

        foreach ($data as $k=>$v){
          $data [$k] = ulp_sanitize_array($v);
        }
        if (!empty($data['id'])){
            /// update
            $update = $wpdb->prepare( "
              badge_title=%s,
              badge_content=%s,
              badge_image=%s,
              badge_type=%s,
              rule=%s ", $data['badge_title'], $data['badge_content'], $data['badge_image'], $data['badge_type'], $data['rule'] );
            $where = $wpdb->prepare( " id=%d ", $data['id'] );
            return parent::update( $update, $where );
        } else {
            /// insert
            $insert_date = date('Y-m-d H:i:s', time() );
            $insertData = $wpdb->prepare( " NULL, %s, %s, %s, %s, %s, %s ",
                                          $data['badge_title'], $data['badge_content'], $data['badge_image'], $data['badge_type'], $data['rule'], $insert_date );
            return parent::insert( $insertData );
        }
    }
    public function delete($id=0){
        global $wpdb;
        $id = sanitize_text_field($id);
        $delete = $wpdb->prepare( " id=%d ", $id );
        return parent::delete( $delete );
    }
    public function getById($id=0){
        global $wpdb;
        if ($id){
            $id = sanitize_text_field($id);
            $where = $wpdb->prepare( " id=%d ", $id );
            $data = parent::getRow("`id`, `badge_title`, `badge_content`, `badge_image`, `badge_type`, `rule`, `create_date`", $where );
        } else {
            $data = [
                'id'            => 0,
                'badge_title'   => '',
                'badge_content' => '',
                'badge_image'   => '',
                'badge_type'    => 'static',
                'rule'          => '',
            ];
        }
        return $data;
    }
    public function selectByType($type='', $limit=0, $offset=0){
        global $wpdb;
        $q = $wpdb->prepare("SELECT `id`,`badge_title`,`badge_content`,`badge_image`,`badge_type`,`rule`,`create_date` FROM {$this->table} WHERE badge_type=%s ", $type);
        if ($limit){
            $q .= $wpdb->prepare(" LIMIT %d OFFSET %d ", $limit, $offset);
        }
        $data = $wpdb->get_results($q);
        if ($data==null){
            $data = false;
        }
        return $data;
    }
    public function selectAll($limit=0, $offset=0){
        global $wpdb;
        $q = "SELECT `id`,`badge_title`,`badge_content`,`badge_image`,`badge_type`,`rule`,`create_date` FROM {$this->table} WHERE 1=1 ";
        if ($limit){
            $q .= $wpdb->prepare(" LIMIT %d OFFSET %d ", $limit, $offset);
        }
        $data = $wpdb->get_results($q);
        if ($data==null){
            $data = false;
        }
        return $data;
    }
    public function getByRuleAndType($search_rule='', $type=''){
        global $wpdb;
        $search_rule = sanitize_text_field($search_rule);
        $type = sanitize_text_field($type);
        $q = "SELECT id FROM {$this->table} WHERE rule LIKE '%$search_rule%' ";
        $q .= $wpdb->prepare( " AND badge_type=%s ", $type );
        return $wpdb->get_var( $q );
    }
    public function getAllByRuleAndType($search_rule='', $type=''){
        global $wpdb;
        $search_rule = sanitize_text_field($search_rule);
        $type = sanitize_text_field($type);
        $q = "SELECT id, rule FROM {$this->table} WHERE rule LIKE '%$search_rule%'  ";
        $q .= $wpdb->prepare( " AND badge_type=%s ", $type );
        return $wpdb->get_results($q);
    }
    public function getUsersMinPointsExcludedBadges($min_points=0, $excluded_badges=array() ){
        global $wpdb;
        $excluded_badges_str = implode(',', $excluded_badges);
        $min_points = sanitize_text_field($min_points);
        $excluded_badges_str = sanitize_text_field($excluded_badges_str);

			$q = "SELECT a.uid
              	FROM {$wpdb->prefix}ulp_student_badges a
              	WHERE a.badge_id IN ($excluded_badges_str);";
		$users_no_badge_arr = $wpdb->get_results( $q );

		$q = $wpdb->prepare( "SELECT a.uid
                          	FROM {$wpdb->prefix}ulp_reward_points a
                          	WHERE
                          	1=1
                            AND a.points>=%d ", $min_points );

		if ($users_no_badge_arr && count($users_no_badge_arr)){
			foreach ($users_no_badge_arr as $obj){
				$users_no_badge[] = $obj->uid;
			}
			$users_no_badge = implode(',', $users_no_badge);

          $q .= " AND a.uid NOT IN ($users_no_badge);";
		}
        return $wpdb->get_results($q);
    }
}
