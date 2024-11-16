<?php
if (!class_exists('Ihc_User_Logs')):

class Ihc_User_Logs{
	private static $user_id = '';
	private static $lid = '';
	private static $module_enabled = array('payments'=>FALSE, 'general'=>FALSE);
	private static $update_uid_later_arr = array();
	private static $update_lid_later_arr = array();
	private static $bannerName = [];

	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){
		self::$module_enabled['payments'] = get_option('ihc_payment_logs_on');
		self::$module_enabled['user_logs'] = get_option('ihc_user_reports_enabled');
		self::$module_enabled['drip_content_notifications'] = get_option('ihc_drip_content_notifications_logs_enabled');
		self::generateImagNames();
	}


	/*
	 * @param int($uid)
	 * @return none
	 */
	public static function set_user_id($uid=0){
		if ($uid){
			self::$user_id = $uid;

			/// do update previous entries
			if (!empty(self::$update_uid_later_arr)){
				foreach (self::$update_uid_later_arr as $id){
					self::do_update_row($id, 'uid', $uid);
				}
			}
		}
	}


	/*
	 * @param int($lid)
	 * @return none
	 */
	public static function set_level_id($lid=FALSE){
		if ($lid!==FALSE){
			self::$lid = $lid;

			/// do update previous entries
			if (!empty(self::$update_lid_later_arr)){
				foreach (self::$update_uid_later_arr as $id){
					self::do_update_row($id, 'lid', $lid);
				}
			}
		}
	}


	/*
	 * @param string ($message)
	 * @param string ($type can be 'payments' 'user_logs' )
	 * @return none
	 */
	public static function write_log($message='', $type=''){
		global $wpdb;
		if (!self::$module_enabled[$type]){
			return;
		}
		if (empty(self::$user_id)){
			self::$user_id = get_current_user_id();
		}

		$table = $wpdb->prefix . 'ihc_user_logs';
		if ($message){
			$uid = self::$user_id;
			$lid = self::$lid;
			$now = indeed_get_unixtimestamp_with_timezone();
			$q = $wpdb->prepare("INSERT INTO $table VALUES(null, %d, %d, %s, %s, %s);", $uid, $lid, $type, $message, $now);
			$wpdb->query($q);
			$inserted = $wpdb->insert_id;

			if (empty($uid)){
				self::$update_uid_later_arr[] = $inserted;
			}
			if (empty($lid)){
				self::$update_lid_later_arr[] = $inserted;
			}
		}
	}


	/*
	 * @param string ($type can be 'payments' 'general' )
	 * @param int ($older_then timestamp)
	 * @return none
	 */
	public static function delete_logs($type='', $older_then=''){
		global $wpdb;
		if ($type && $older_then){
			$table = $wpdb->prefix . 'ihc_user_logs';
			$q = $wpdb->prepare("DELETE FROM $table WHERE log_type=%s AND create_date<%d ", $type, $older_then);
			$wpdb->query($q);
		}
	}


	/*
	 * @param string ($type can be 'payments' 'general')
	 * @param int ($uid, 0 means all)
	 * @param int ($offset)
	 * @param int ($limit)
	 * @return array
	 */
	 public static function get_logs($type='', $uid=0, $offset=0, $limit=0){
	 	 global $wpdb;
		 $table = $wpdb->prefix . 'ihc_user_logs';
		 $array = array();
		 $type = sanitize_text_field($type);
		 $uid = sanitize_text_field($uid);
		 $offset = sanitize_text_field($offset);
		 $limit = sanitize_text_field($limit);
		 if ($type){
		 	$q = "SELECT id,uid, lid, log_content, create_date FROM {$wpdb->prefix}ihc_user_logs ";
			$q .= $wpdb->prepare( " WHERE log_type=%s ", $type );
			if ($uid){
				$q .= $wpdb->prepare( " AND uid=%d ", $uid );
			}
			$q .= "ORDER BY create_date DESC"; // id DESC
			if ($limit){
				$q .= $wpdb->prepare(" LIMIT %d ", $limit );
			}
			if ($offset){
				$q .= $wpdb->prepare( " OFFSET %d ", $offset );
			}
		 	$data = $wpdb->get_results($q);
			if ($data){
				foreach ($data as $object){
					$array[] = (array)$object;
				}
			}
		 }
		 return $array;
	 }


	/**
	 * @param string ($type can be 'payments' 'general')
	 * @param int ($uid, 0 means all)
	 * @return int
	 */
	 public static function get_count_logs($type='', $uid=0){
	 	 global $wpdb;
		 $type = sanitize_text_field($type);
		 $uid = sanitize_text_field($uid);
		 $table = $wpdb->prefix . 'ihc_user_logs';
		 $q = "SELECT COUNT(id) as c FROM {$wpdb->prefix}ihc_user_logs ";
		 $q .= $wpdb->prepare( " WHERE log_type=%s ", $type );
		 if ($uid){
		 	$q .= $wpdb->prepare( " AND uid=%d ", $uid );
		 }
		 $data = $wpdb->get_row($q);
		 if ($data && isset($data->c)){
		 	return $data->c;
		 }
	 	 return 0;
	 }


	 /**
	  * Update log with lid or uid
	  * @param int
		* @param string
		* @param int
	  * @return none
	  */
	  private static function do_update_row($id=0, $column='', $new_value=0)
		{
				 global $wpdb;
		  	 if ( !$id ){
					 	return;
		  	 }
				 $column = sanitize_text_field( $column );
				 $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_user_logs SET $column=%s WHERE id=%d ;", $new_value, $id );
				 $wpdb->query( $query );
	  }

		public static function generateImagNames()
		{
				$class = "\Indeed\Ihc\\" . 'UmpBanner' . 'LinksAndNames';
				self::$bannerName = new $class();
		}
}

endif;
