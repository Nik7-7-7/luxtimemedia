<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpRewardPointsDetails')){
	 return;
}
class UlpRewardPointsDetails{
	/**
	 * @var string
	 */
	private $table = '';
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_reward_points_details';
	}
	/**
	 * @param int (user id)
	 * @param int (number of points)
	 * @param int (post id)
	 * @param string (action name)
	 * @param string
	 * @return boolean
	 */
	public function save_details($uid=0, $points=0, $post_id=0, $action='', $description=''){
		global $wpdb;
		$now = date('Y-m-d H:i:s');
		$q = $wpdb->prepare("INSERT INTO {$this->table} VALUES(null, %d, %d, %d, %s, %s, %s);",
						$uid, $points, $post_id, $action, $description, $now
		);
		return $wpdb->query($q);
	}
	/**
	 * @param int (user id)
	 * @param int (post id)
	 * @param string (action name)
	 * @return array
	 */
	public function get_details($uid=0, $post_id=0, $action=''){
		global $wpdb;
		$q = $wpdb->prepare("SELECT `id`, `uid`, `points_num`, `post_id`, `action`, `description`, `event_time` FROM {$this->table} WHERE uid=%d AND post_id=%d AND action=%s ", $uid, $post_id, $action);
		$data = $wpdb->get_row($q);
		return indeed_convert_to_array($data);
	}
	/**
	 * @param int (user id)
	 * @param int (post id)
	 * @param string (action name)
	 * @return boolean
	 */
	public function entry_exists($uid, $post_id, $action=''){
		global $wpdb;
		$q = $wpdb->prepare("SELECT id FROM {$this->table} WHERE uid=%d AND post_id=%d ", $uid, $post_id);
		if ($action){
				$q .= $wpdb->prepare(" AND action=%s ", $action);
		}
		$data = $wpdb->get_var($q);
		if ($data===NULL){
			 $data = FALSE;
		}
		return $data;
	}
}
