<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpRewardPoints')){
	 return;
}
class UlpRewardPoints{
	/**
	 * @var string
	 */
	private $table = '';
	/**
	 * @var int
	 */
	private $uid = 0;
	/**
	 * @var int
	 */
	private $points = NULL;
	/**
	 * @var
	 */
	private $add_points_for_same_action = FALSE; /// make this dynamic
	/**
	 * @param none
	 * @reutrn none
	 */
	public function __construct($uid=0){
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_reward_points';
		$this->uid = $uid;
		if (!class_exists('UlpRewardPointsDetails')){
			 require_once ULP_PATH . 'classes/Entity/UlpRewardPointsDetails.class.php';
		}
	}
	/**
	 * @param int (number of points)
	 * @param int (post id)
	 * @param string (action type)
	 * @param string (description)
	 * @return boolean
	 */
	public function add_points_to_user($points=0, $post_id=0, $action_type='', $description=''){
		global $wpdb;
		$object = new UlpRewardPointsDetails();
		if (!$this->add_points_for_same_action && $object->entry_exists($this->uid, $post_id, $action_type)){
			return FALSE;
		}
		if ($points){
			if ($this->has_points()){
				// update
				$old_number = $this->NumOfPoints();
				if ($old_number===NULL){
					$old_number = 0;
				}
				$this->points = $old_number + $points;
				$q = $wpdb->prepare("UPDATE {$this->table} SET points=%d WHERE uid=%d;", $this->points, $this->uid);
				$wpdb->query($q);
			} else {
				/// insert
				$this->points = $points;
				$q = $wpdb->prepare("INSERT INTO {$this->table} VALUES(null, %d, %d)", $this->uid, $this->points);
				$wpdb->query($q);
			}
		}
		do_action('ulp_user_gets_points', $this->uid, $post_id, $action_type, $points);
		$object->save_details($this->uid, $points, $post_id, $action_type, $description);
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function has_points(){
		global $wpdb;
		$q = $wpdb->prepare("SELECT id from {$this->table} WHERE uid=%d ", $this->uid);
		return $wpdb->get_var($q);
	}
	/**
	 * @param int (num of points)
	 * @return boolean
	 */
	public function delete_points($points=0){
		global $wpdb;
		$updated_value = $this->NumOfPoints();
		if ($updated_value===NULL){
			$updated_value = 0;
		}
		$this->points = $updated_value - $points;
		$q = $wpdb->prepare("UPDATE {$this->table} SET points=%d WHERE uid=%d ", $this->points, $this->uid);
		return $wpdb->query($q);
	}
	public function update($new_value=0){
			global $wpdb;
			if ($this->NumOfPoints()==null){
					$q = $wpdb->prepare("INSERT INTO {$this->table} VALUES(null, %d, %d)", $this->uid, $new_value);
					return $wpdb->query($q);
			}
			$q = $wpdb->prepare("UPDATE {$this->table} SET points=%d WHERE uid=%d ", $new_value, $this->uid);
			return $wpdb->query($q);
	}
	public function reset(){
			global $wpdb;
			$q = $wpdb->prepare("DELETE FROM {$this->table} WHERE uid=%d ", $this->uid);
			$wpdb->query($q);
			$q = $wpdb->prepare("DELETE FROM {$wpdb->prefix}ulp_reward_points_details WHERE uid=%d ", $this->uid);
			$wpdb->query($q);
	}
	/**
	 * @param none
	 * @return int
	 */
	public function NumOfPoints(){
		global $wpdb;
		if ($this->points===NULL){
			$q = $wpdb->prepare("SELECT points FROM {$this->table} WHERE uid=%d ", $this->uid);
			$this->points = $wpdb->get_var($q);
			if ($this->points===null){
				$this->points = 0;
			}
		}
		return $this->points;
	}
}
