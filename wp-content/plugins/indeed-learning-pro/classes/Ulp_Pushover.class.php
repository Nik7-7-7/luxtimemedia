<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Pushover')){
   return;
}
class Ulp_Pushover{
  private static $notification_templates = array();
  private static $meta = array();
	public function __construct(){
		require_once ULP_PATH . 'classes/services/Pushover.php';
	}
	public function send_notification($uid=0, $course_id=-1, $notification_type='', $send_to_admin=FALSE){
      if (empty(self::$meta)){
          self::$meta = DbUlp::getOptionMetaGroup('pushover');
      }
      if (empty(self::$meta['ulp_pushover_enable'])){
          return FALSE;
      }
  		if ($notification_type){
  			if (empty($notification_templates[$course_id])){
  				self::$notification_templates[$course_id] = $this->get_notification_data($notification_type, $course_id);
  			}
  			$notification_data = self::$notification_templates[$course_id];
  			if ($notification_data && !empty($notification_data['pushover_status']) && !empty($notification_data['pushover_message'])){
  				$message = stripslashes($notification_data['pushover_message']);
  				$message = ulp_replace_constants($message, $uid, $course_id);
  				$title = $notification_data['subject'];
  				$title = ulp_replace_constants($title, $uid, $course_id);
  				$app_token = get_option('ulp_pushover_app_token');
  				if ($uid && !$send_to_admin){
  					$user_token = get_user_meta($uid, 'ulp_pushover_token', TRUE);	/// USER
  				} else {
  					$user_token = self::$meta['ulp_pushover_admin_token'];  /// ADMIN
  				}
  				$sound = get_option('ulp_pushover_sound');
  				$sound = empty(self::$meta['ulp_pushover_sound']) ? 'bike' : self::$meta['ulp_pushover_sound'];
  				$url = empty(self::$meta['ulp_pushover_url']) ? '' : stripslashes(self::$meta['ulp_pushover_url']);
  				$url_title = empty(self::$meta['ulp_pushover_url_title']) ? '' : stripslashes(self::$meta['ulp_pushover_url_title']);
          $push = new Pushover();
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
	private function get_notification_data($type='', $course_id=-1){
		global $wpdb;
		$table = $wpdb->prefix . "ulp_notifications";
		$q = $wpdb->prepare("SELECT `id`, `type`, `course_id`, `subject`, `message`, `pushover_message`, `pushover_status`, `status` FROM $table
									WHERE
									type=%s
									AND course_id=%d
									ORDER BY id DESC LIMIT 1;", $type, $course_id);
		$data = $wpdb->get_row($q);
		if (empty($data)){
			$q = $wpdb->prepare("SELECT `id`, `type`, `course_id`, `subject`, `message`, `pushover_message`, `pushover_status`, `status` FROM $table
										WHERE
										type=%s
										AND course_id=-1
										ORDER BY id DESC LIMIT 1;", $type);
			$data = $wpdb->get_row($q);
		}
		return (array)$data;
	}
}
