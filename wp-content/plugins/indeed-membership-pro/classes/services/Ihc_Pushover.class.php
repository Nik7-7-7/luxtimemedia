<?php
if (!class_exists('Ihc_Pushover')):

class Ihc_Pushover{
	private static $notification_templates = array();

	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){
		require_once IHC_PATH . 'classes/services/Pushover.class.php';
	}


	/*
	 * @param int, int, string, boolean
	 * @return boolean
	 */
	public function send_notification($uid=0, $lid=-1, $notification_type='', $send_to_admin=FALSE){
		if ($notification_type){
			if (empty($notification_templates[$lid])){
				self::$notification_templates[$lid] = $this->get_notification_data($notification_type, $lid);
			}
			$notification_data = self::$notification_templates[$lid];

			if ($notification_data && !empty($notification_data['pushover_status']) && !empty($notification_data['pushover_message'])){
				$meta = ihc_return_meta_arr('pushover');
				$message = stripslashes($notification_data['pushover_message']);
				$message = ihc_replace_constants($message, $uid, $lid, $lid);
				$title = $notification_data['subject'];
				$title = ihc_replace_constants($title, $uid, $lid, $lid);

				$push = new Pushover();
				$app_token = get_option('ihc_pushover_app_token');
				if ($uid && !$send_to_admin){
					$user_token = get_user_meta($uid, 'ihc_pushover_token', TRUE);	/// USER
				} else {
					$user_token = $meta['ihc_pushover_admin_token'];  /// ADMIN
				}
				$sound = get_option('ihc_pushover_sound');
				$sound = empty($meta['ihc_pushover_sound']) ? 'bike' : $meta['ihc_pushover_sound'];
				$url = empty($meta['ihc_pushover_url']) ? '' : $meta['ihc_pushover_url'];
				$url_title = empty($meta['ihc_pushover_url_title']) ? '' : $meta['ihc_pushover_url_title'];

				$push->setToken($app_token);
				$push->setUser($user_token);
				$push->setTitle($title);
				$push->setMessage($message);
				$push->setUrl($url);
				$push->setUrlTitle($url_title);
				$push->setPriority(2); /// 0 || 1 || 2
				$push->setRetry(300); /// five minutes
				$push->setExpire(3600); /// one hour
				$push->setTimestamp(time());
				$push->setDebug(FALSE);
				$push->setSound($sound);
				return $push->send();
			}
		}
		return FALSE;
	}


	/*
	 * @param string
	 * @return array
	 */
	private function get_notification_data($type='', $lid=-1){
		global $wpdb;
		$table = $wpdb->prefix . "ihc_notifications";
		$q = $wpdb->prepare("SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM $table
									WHERE
									notification_type=%s
									AND level_id=%d
									ORDER BY id DESC LIMIT 1;", $type, $lid);
		$data = $wpdb->get_row($q);
		if (empty($data)){
			$q = $wpdb->prepare("SELECT id,notification_type,level_id,subject,message,pushover_message,pushover_status,status FROM $table
										WHERE
										notification_type=%s
										AND level_id=-1
										ORDER BY id DESC LIMIT 1;", $type);
			$data = $wpdb->get_row($q);
		}
		return (array)$data;
	}

}

endif;
