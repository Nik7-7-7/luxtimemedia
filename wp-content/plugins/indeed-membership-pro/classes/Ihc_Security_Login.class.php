<?php
if (!class_exists('Ihc_Security_Login')):

class Ihc_Security_Login{
	private $metas = array();
	private $ip = '';
	private $username = '';
	private $password = '';
	private $current_ip = array();
	private $error_on_login = FALSE;

	public function __construct($username='', $password=''){
		/*
		 * @param string, string
		 * @return none
		 */
		 $this->ip = $this->set_ip();
		 $this->username = $username;
		 $this->password = $password;
		 $this->metas = ihc_return_meta_arr('login_security');
		 $this->set_ip_data_from_db();
	}

	private function set_ip(){
		/*
		 * @param none
		 * @return string
		 */
		$ip = '';
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){
		    $ip = sanitize_text_field( $_SERVER['HTTP_CLIENT_IP'] );
		} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
		    $ip = sanitize_text_field( $_SERVER['HTTP_X_FORWARDED_FOR'] );
		} else {
		    $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		}
		return $ip;
	}

	public function login(){
		/*
		 * @param
		 * @return boolean
		 */
		 if ($this->metas['ihc_login_security_black_list'] && $this->is_ip_on_black_list()){
		 	 return FALSE;
		 } else {
			 if ($this->ip_exists_in_db()){
			 	 /// IP EXISTS IN DB
			 	 if ($this->ip_is_extended_locked()){
			 	 	/// EXTENDED LOCKED
			 	 	return FALSE;
			 	 } else if ($this->ip_is_locked()){
			 	 	/// LOCKED FOR N MINUTES
					 return FALSE;
			 	 } else {
			 	 	 /// DO LOGIN
			 	 	 if ($this->do_login()){
			 	 	 	return TRUE;
			 	 	 } else {
			 	 	 	$this->increment_attempts();
						$this->error_on_login = TRUE;
						return FALSE;
			 	 	 }
			 	 }
			 } else {
			 	 /// NO IP IN DB, DO LOGIN
				 if ($this->do_login()){
				 	return TRUE;
				 } else {
					$this->error_on_login = TRUE;
				 	$this->insert_attempt();
					return FALSE;
				 }
			 }
		 }
	}

	public function show_login_form(){
		/*
		 * @param none
		 * @return boolean
		 */
		 if ($this->metas['ihc_login_security_black_list'] && $this->is_ip_on_black_list()){
		 	 return FALSE;
		 }
		 if ($this->ip_exists_in_db()){
		 	if ($this->ip_is_extended_locked()){
		 		return FALSE;
			}
		 }
		 return TRUE;
	}

	public function get_error_attempt_message(){
		/*
		 * @param none
		 * @return string
		 */
		if (!empty($this->current_ip['attempts_count'])){
			$remaining = $this->metas['ihc_login_security_allowed_retries'] - $this->current_ip['attempts_count'];
			if ($remaining<=0){
				return ihc_correct_text($this->metas['ihc_login_security_lockout_message']);
			} else {
				 $message = $this->metas['ihc_login_security_lockout_attempt_message'];
				 $message = str_replace('{number}', $remaining, $message);
				 return ihc_correct_text($message);
			}
		}
		return '';
	}

	public function get_locked_message(){
		/*
		 * @param none
		 * @return string
		 */
		 if ($this->ip_is_extended_locked()){
		     return ihc_correct_text($this->metas['ihc_login_security_extended_lockout_message']);
		 }
		 return '';
	}

	public function is_ip_on_black_list(){
		/*
		 * @param none
		 * @return boolean
		 */
		$ip_array = explode(',', $this->metas['ihc_login_security_black_list']);
		if (in_array($this->ip, $ip_array)){
			return TRUE;
		}
		return FALSE;
	}

	public function is_error_on_login(){
		/*
		 * @param none
		 * @return boolean
		 */
		 return $this->error_on_login;
	}

	private function ip_exists_in_db(){
		/*
		 * @param none
		 * @return boolean
		 */
		if (empty($this->current_ip)){
			return FALSE;
		}
		return TRUE;
	}

	private function set_ip_data_from_db(){
		/*
		 * @param none
		 * @return none
		 */
		global $wpdb;
		$query = $wpdb->prepare( "SELECT attempts_count, locked, log_time, username FROM {$wpdb->prefix}ihc_security_login
																		WHERE ip=%s ;", $this->ip );
		$data = $wpdb->get_row( $query );
		if ($data && !empty($data->log_time)){
		 	$this->current_ip['attempts_count'] = $data->attempts_count;
			$this->current_ip['locked'] = $data->locked;
			$this->current_ip['log_time'] = $data->log_time;
			$this->current_ip['username'] = $data->username;
		}
	}

	/**
	 * @param none
	 * @return boolean
	 */
	private function ip_is_extended_locked()
	{
		 if (!empty($this->current_ip['locked']) && $this->current_ip['locked']>=$this->metas['ihc_login_security_max_lockouts']){
		 	 //check time
		 	 $lock_time = $this->current_ip['log_time'] + $this->metas['ihc_login_security_extended_lockout_time'] * 60 * 60;
			 if ($lock_time>indeed_get_unixtimestamp_with_timezone()){
			 	 return true;
			 } else {
			 	 $this->reset_locked();
				 $this->reset_attempts();
			 }
		 }
		 return false;
	}

	/**
	 * @param none
	 * @return none
	 */
	private function ip_is_locked()
	{
		 if (!empty($this->current_ip['locked']) && $this->metas['ihc_login_security_allowed_retries']<=$this->current_ip['attempts_count']){
		 	 /// check time
		 	 $end_lock_time = $this->current_ip['log_time'] + $this->metas['ihc_login_security_lockout_time'] * 60;
			 if ($end_lock_time>indeed_get_unixtimestamp_with_timezone()){
			 	 return TRUE;
			 } else {
			 	$this->reset_attempts();
			 }
		 }
		 return FALSE;
	}

	/**
	 * @param none
	 * @return boolean
	 */
	private function do_login()
	{
		$array['user_login'] = $this->username;
		$array['user_password'] = $this->password;
		$array['remember'] = FALSE;
		$user = wp_signon($array, TRUE);
		if (is_wp_error($user)){
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * @param none
	 * @return none
	 */
	private function reset_attempts()
	{
		 global $wpdb;
		 $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_security_login SET attempts_count=0 WHERE ip=%s ;", $this->ip );
		 $wpdb->query( $query );
	}

	/**
	 * @param none
	 * @return none
	 */
	private function reset_locked()
	{
		 global $wpdb;
		 $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_security_login SET locked=0 WHERE ip=%s;", $this->ip );
		 $wpdb->query( $query );
	}

	/**
	 * @param none
	 * @return none
	 */
	private function increment_attempts()
	{
		 global $wpdb;
		 $time = indeed_get_unixtimestamp_with_timezone();
		 $this->current_ip['attempts_count']++;
		 if ($this->current_ip['attempts_count'] && $this->current_ip['attempts_count']>=$this->metas['ihc_login_security_allowed_retries']){
		 	$end_lock_time = $this->current_ip['log_time'] + $this->metas['ihc_login_security_lockout_time'] * 60;
			if ($end_lock_time>$time){
			 	$this->current_ip['locked']++;
				if ($this->current_ip['locked']==$this->metas['ihc_login_security_notify_admin']){
					$this->ihc_send_security_notification_to_admin();
				}
			} else {
				$this->current_ip['attempts_count'] = 1;
			}
		 }
		 $table = $wpdb->prefix . 'ihc_security_login';
		 $time = indeed_get_unixtimestamp_with_timezone();
		 $q = $wpdb->prepare("UPDATE {$wpdb->prefix}ihc_security_login
			 												SET attempts_count=%s,
		 						 									locked=%s,
		 						 									username=%s,
		 						 									log_time=%s
		 						 							WHERE ip=%s;",
													$this->current_ip['attempts_count'], $this->current_ip['locked'], $this->username, $time, $this->ip );
		 $wpdb->query($q);
	}

	/**
	 * @param none
	 * @return none
	 */
	private function insert_attempt()
	{
		 global $wpdb;
		 $time = indeed_get_unixtimestamp_with_timezone();
		 $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_security_login
			 															VALUES(null, %s, %s, %s, 1, 0);", $this->username, $this->ip, $time );
		 $wpdb->query( $query );
	}

	/**
	 * @param none
	 * @return boolean
	 */
	private function ihc_send_security_notification_to_admin()
	{
		$from_email = get_option('ihc_notification_email_from');
		if (!$from_email){
			$from_email = get_option('admin_email');
		}
		$from_name = get_option('ihc_notification_name');
		if (!$from_name){
			$from_name = get_option("blogname");
		}
		$to = get_option('ihc_notification_email_addresses');
		if (!$to){
			$to = get_option('admin_email');
		}
		$title = esc_html__('Security alert on ', 'ihc');
		$message = esc_html__('Someone with following IP address: {IP}, has try multiple times to login into Your website.', 'ihc');
		$message = str_replace('{IP}', $this->ip, $message);

		if (!empty($from_email) && !empty($from_name)){
			$headers[] = "From: $from_name <$from_email>";
		}
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$sent = wp_mail($to, $title, $message, $headers);
	    return $sent;
	}


}

endif;
