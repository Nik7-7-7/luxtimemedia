<?php
if (!class_exists('DripContentNotifications')):

class DripContentNotifications{
	/**
	 * @var array
	 */
	private $email_templates = array();
	/**
	 * @var object (Ihc_User_Logs)
	 */
	private $logModule;
	/**
	 * @var string
	 */
	private $logType = 'drip_content_notifications';
	/**
	 * @var string (cron || admin)
	 */
	private $startBy = 'cron';
	/**
	 * @var int
	 */
	private $countNotificationsOnSpecificDate = 0;
	/**
	 * @var int
	 */
	private $countAfterSubscriptionXTime = 0;
	/**
	 * @var int (second between sending notifications)
	 */
	private $sleepTime = 10;
	/**
	 * @var int (max execution time)
	 */
	private $executionTime = 3600;/// one hour


	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$on = get_option('ihc_drip_content_notifications_enabled');
		if (!$on){
			 return; /// MODULE INACTIVE
		}

		@set_time_limit($this->executionTime);
		$this->sleepTime = get_option('ihc_drip_content_notifications_sleep');
		if ( $this->sleepTime === false || $this->sleepTime === 0 || $this->sleepTime === null ){
				$this->sleepTime = (int)$this->sleepTime;
		} else {
				$this->sleepTime = 10; // default
		}

		$this->startLogModule();
		$this->runOnSpecificDate();
		$this->runOnAfterSubscriptionXTime();
		$content = esc_html__('Process end! Total number of notifications sent: ', 'ihc') . ($this->countNotificationsOnSpecificDate + $this->countAfterSubscriptionXTime) . '.';
		$this->logModule->write_log($content, $this->logType);
	}


	/**
	 * @param string
	 * @return none
	 */
	public function setStartBy($type='cron'){
		$this->startBy = $type;
	}


	/**
	 * @param none
	 * @return none
	 */
	private function runOnSpecificDate(){
		global $wpdb;

		$content = esc_html__('Start sending notifications for posts that are available on current date.', 'ihc');
		$this->logModule->write_log($content, $this->logType);

		$table = $wpdb->prefix . 'postmeta';
		$current = date('d-m-Y');
		$q = $wpdb->prepare("
			SELECT DISTINCT d.post_id as post_id, d.meta_value as target_levels
				FROM {$wpdb->prefix}postmeta a
				INNER JOIN {$wpdb->prefix}postmeta b
				ON a.post_id=b.post_id
				INNER JOIN {$wpdb->prefix}postmeta c
				ON c.post_id=a.post_id
				INNER JOIN {$wpdb->prefix}postmeta d
				ON d.post_id=a.post_id
				WHERE
				(a.meta_key='ihc_drip_content' AND a.meta_value=1)
				AND
				(b.meta_key='ihc_drip_start_type' AND b.meta_value=3)
				AND
				(c.meta_key='ihc_drip_start_certain_date' AND c.meta_value=%s )
				AND
				d.meta_key='ihc_mb_who'
		", $current );

		$post_data = $wpdb->get_results($q);

		if ($post_data){
			foreach ($post_data as $post_object){
				$this->posts_links[$post_object->post_id] = get_permalink($post_object->post_id);
				$dynamic_data = array('{POST_LINK}' => $this->posts_links[$post_object->post_id]);
				$users = \Indeed\Ihc\UserSubscriptions::searchMembersForDripContent($post_object->target_levels);
				if (!empty($users)){
					foreach ($users as $temp_array){
						$lid = isset($temp_array->lid) ? $temp_array->lid : -1;
						if (!isset($this->email_templates[$lid])){
							$this->email_templates[$lid] = $this->getNotification($lid);
						}
						if (!empty($this->email_templates[$lid]['message'])){

						  $notification = new \Indeed\Ihc\Notifications();
							$sent = $notification->setUid( $temp_array->uid )
							                     ->setLid( $lid )
							                     ->setType( 'drip_content-user' )
							                     ->setMessageVariables( $dynamic_data )
																	 ->setSubject( (isset($this->email_templates[$lid]['subject'])) ? $this->email_templates[$lid]['subject'] : '' )
																	 ->setMessage( (isset($this->email_templates[$lid]['message'])) ? $this->email_templates[$lid]['message'] : '' )
							                     ->send();

							$this->countNotificationsOnSpecificDate++;
							sleep($this->sleepTime);
						}
					}
				}
			}
		}
		$content = esc_html__('End sending notifications for posts that are available on current date. Total number : ', 'ihc') . $this->countAfterSubscriptionXTime;
		$this->logModule->write_log($content, $this->logType);
	}


	/**
	 * @param none
	 * @return none
	 */
	private function runOnAfterSubscriptionXTime(){
		global $wpdb;

		$content = esc_html__('Start sending notifications for posts that are available after a specified subscription time.', 'ihc');
		$this->logModule->write_log($content, $this->logType);

		$table = $wpdb->prefix . 'postmeta';
		$current = date('d-m-Y');
		$q = "
			SELECT DISTINCT d.post_id as post_id, d.meta_value as target_levels, e.meta_value as interval_type, f.meta_value as interval_value
				FROM $table a
				INNER JOIN $table b
				ON a.post_id=b.post_id
				INNER JOIN $table c
				ON c.post_id=a.post_id
				INNER JOIN $table d
				ON d.post_id=a.post_id
				INNER JOIN $table e
				ON e.post_id=a.post_id
				INNER JOIN $table f
				ON f.post_id=a.post_id
				WHERE
				(a.meta_key='ihc_drip_content' AND a.meta_value=1)
				AND
				(b.meta_key='ihc_drip_start_type' AND b.meta_value=2)
				AND
				d.meta_key='ihc_mb_who'
				AND
				e.meta_key='ihc_drip_start_numeric_type'
				AND
				f.meta_key='ihc_drip_start_numeric_value'
		";
		$post_data = $wpdb->get_results($q);
		if ($post_data){
			foreach ($post_data as $post_object){
				$this->posts_links[$post_object->post_id] = get_permalink($post_object->post_id);
				$dynamic_data = array('{POST_LINK}' => $this->posts_links[$post_object->post_id]);
				switch ($post_object->interval_type){
					case 'days':
						$after_time = $post_object->interval_value;
						break;
					case 'weeks':
						$after_time = $post_object->interval_value * 7;
						break;
					case 'months':
						$after_time = $post_object->interval_value * 30;
						break;
				}
				$users = \Indeed\Ihc\UserSubscriptions::searchMembersForDripContent($post_object->target_levels, $after_time);

				if (!empty($users)){
					foreach ($users as $temp_array){
						$lid = isset($temp_array->lid) ? $temp_array->lid : -1;
						if (!isset($this->email_templates[$lid])){
							$this->email_templates[$lid] = $this->getNotification($lid);
						}
						if (!empty($this->email_templates[$lid]['message'])){

							$notification = new \Indeed\Ihc\Notifications();
							$sent = $notification->setUid( $temp_array->uid )
							                     ->setLid( $lid )
							                     ->setType( 'drip_content-user' )
							                     ->setMessageVariables( $dynamic_data )
																	 ->setSubject( (isset($this->email_templates[$lid]['subject'])) ? $this->email_templates[$lid]['subject'] : '' )
																	 ->setMessage( (isset($this->email_templates[$lid]['message'])) ? $this->email_templates[$lid]['message'] : '' )
							                     ->send();

							$this->countAfterSubscriptionXTime++;
							sleep($this->sleepTime);
						}
					}
				}
			}
		}
		$content = esc_html__('End sending notifications for posts that are available after a specified subscription time. Total number : ', 'ihc') . $this->countAfterSubscriptionXTime;
		$this->logModule->write_log($content, $this->logType);
	}

	/**
	 * @param int (level_id) , -1 means registered with no level
	 * @return string (the notification text)
	 */
	private function getNotification($lid=-1){
		global $wpdb;
		$data = array();
		$table = $wpdb->prefix . 'ihc_notifications';
		$standard_q = "
			SELECT subject, message
				FROM $table
				WHERE
				notification_type='drip_content-user'
				AND
				level_id=%d
				AND
				status=1
				ORDER BY id DESC LIMIT 1;
		";
		if ($lid>-1){
			$q = $wpdb->prepare($standard_q, $lid);
			$data = $wpdb->get_row($q);
		}
		if (!empty($data)){
			$data = (array)$data;
		} else {
			$q = $wpdb->prepare($standard_q, -1);
			$data = $wpdb->get_row($q);
			$data = (array)$data;
		}
		return $data;
	}


	/**
	 * @param none
	 * @return none
	 */
	private function startLogModule(){
		require_once IHC_PATH . 'classes/Ihc_User_Logs.class.php';
		$this->logModule = new Ihc_User_Logs();
		$this->logModule->set_user_id(-1);/// no user, action made by wp ajax
		$this->logModule->set_level_id(-1);/// no level
		$content = esc_html__('Process start by: ', 'ihc') . $this->startBy . '.';
		$this->logModule->write_log($content, $this->logType);
	}

}

endif;
