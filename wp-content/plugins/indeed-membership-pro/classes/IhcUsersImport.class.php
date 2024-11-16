<?php
if ( class_exists('IhcUsersImport') ){
	 return;
}


class IhcUsersImport
{
	/**
	 * @var string
	 */
	protected $file 			= '';
	/**
	 * @var int
	 */
	private $doRewrite 		= 0;
	/**
	 * @var array
	 */
	private $levels_data  = array();
	/**
	 * @var array
	 */
	private $updatedUsers = array();
	/**
	 * @var int
	 */
	private $totalUsers 	= 0;

	/**
	 * @param none
	 * @return none
	 */
	public function __construct()
	{
			$this->levels_data = \Indeed\Ihc\Db\Memberships::getAll();
	}

	/**
	 * @param none
	 * @return none
	 */
	public function getUpdatedUsers()
	{
			return count($this->updatedUsers);
	}

	/**
	 * @param none
	 * @return none
	 */
	public function getTotalUsers()
	{
			return $this->totalUsers;
	}

	/**
	 * @param string
	 * @return none
	 */
	public function setFile($filename='')
	{
			if ($filename){
					$this->file = $filename;
			}
	}


	/**
	 * @param int
	 * @return none
	 */
	public function setDoRewrite($value=0)
	{
		$this->doRewrite = $value;
	}

	/**
	 * @param none
	 * @return none
	 */
	public function run()
	{
			if ( !$this->file ){
					return;
			}
			$file_handler = fopen($this->file, 'r');
			$keys = fgetcsv($file_handler);
			if ( count( $keys ) < 2){
					return;
			}

			if ( $keys[0] !== 'email*' && $keys[0] !== 'user_email' ){
					// the top of the csv has the labels of each field
					$keys = $this->userFieldsFromLabelsToSlugs( $keys );
			}

			foreach ( $keys as $index => $value ){
					switch ( $value ){
							case 'email*':
							case 'Email*':
							case 'Email':
								$keys[$index] = 'user_email';
								break;
							case 'username':
							case 'Username':
								$keys[$index] = 'user_login';
								break;
							case 'password':
							case 'Password':
								$keys[$index] = 'user_pass';
								break;
							case 'membership_name':
							case 'Membership Slug':
								$keys[$index] = 'level_slug';
								break;
							case 'started_date':
							case 'Started Time':
								$keys[$index] = 'start_time';
								break;
							case 'expired_date':
							case 'Expired Date':
								$keys[$index] = 'expire_time';
								break;
							case 'First Name':
								$keys[$index] = 'first_name';
								break;
							case 'Last Name':
								$keys[$index] = 'last_name';
								break;
					}
			}

			while ( ($temp_array = fgetcsv($file_handler))!==FALSE ){

					$user_data = array();
					$uid = 0;

					foreach ($temp_array as $k=>$v){
						if (isset($keys[$k])){
							$user_data[$keys[$k]] = $v;
						}
					}

					if (empty($user_data['user_email']) || !is_email($user_data['user_email'])){
						continue;
					}

					// since 12.5
					if ( empty( $user_data['user_login'] ) ){
							$temporary = explode('@', $user_data['user_email'] );
							if ( validate_username($temporary[0]) && !username_exists($temporary[0]) ){
									$user_data['user_login'] = $temporary[0];
							} else {
									$user_data['user_login'] = false;
							}
					}
					// since 12.5

					/// assign user
					if ( !email_exists( $user_data['user_email'] ) && !username_exists( $user_data['user_login'] ) ){
							if (empty($user_data['user_pass'])){
								/// let's generate one
								$user_data['user_pass'] = wp_generate_password(10);
								$do_send_notification_with_pass = TRUE;
							}
							$uid = wp_insert_user(array(
													'user_email' => $user_data['user_email'],
													'user_login' => $user_data['user_login'],
													'user_pass' => $user_data['user_pass'],
							));
							if (!empty($do_send_notification_with_pass) && !empty($uid)){
									$do_send_notification_with_pass = FALSE;
									do_action( 'ihc_register_lite_action', $uid, [ '{NEW_PASSWORD}' => $user_data['user_pass'] ] );
							}
					} else {
							$uid = \Ihc_Db::get_wpuid_by_email( $user_data['user_email'] );
					}

					// no user move forward to the next line
					if ( !$uid ){
							continue;
					}

					unset($user_data['user_email']);
					if (isset($user_data['user_login'])){
						 unset($user_data['user_login']);
					}
					if (isset($user_data['user_pass'])){
						 unset($user_data['user_pass']);
					}

					$this->totalUsers++;

					/// assign user level
					if (!empty($user_data['level_slug'])){

						// since version 12.2
						if ( isset( $user_data['start_time'] ) && $user_data['start_time'] !== '' ){
								$temporaryDate = DateTime::createFromFormat(iumpExtractDateFormat( $user_data['start_time'] ), $user_data['start_time'] );
								$user_data['start_time'] = $temporaryDate->getTimestamp();
								$user_data['start_time'] = date( 'Y-m-d h:i:s', $user_data['start_time'] );
								/*
								$user_data['start_time'] = strtotime( $user_data['start_time'] );
								$user_data['start_time'] = date( 'Y-m-d h:i:s', $user_data['start_time'] );
								*/
						}
						if ( isset( $user_data['expire_time'] ) && $user_data['expire_time'] !== '' ){
								$temporaryDate = DateTime::createFromFormat(iumpExtractDateFormat( $user_data['expire_time'] ), $user_data['expire_time'] );
								$user_data['expire_time'] = $temporaryDate->getTimestamp();
								$user_data['expire_time'] = date( 'Y-m-d h:i:s', $user_data['expire_time'] );
								/*
								$user_data['expire_time'] = strtotime( $user_data['expire_time'] );
								$user_data['expire_time'] = date( 'Y-m-d h:i:s', $user_data['expire_time'] );
								*/
						}
						// end of 12.2

						$lid = Ihc_Db::get_lid_by_level_slug($user_data['level_slug']);
						if ($lid>-1 && (!\Indeed\Ihc\UserSubscriptions::userHasSubscription($uid, $lid) || $this->doRewrite==1) ){
							if (!isset($user_data['start_time']) || $user_data['start_time']=='0000-00-00 00:00:00' || $user_data['start_time']<1){
									if (isset($user_data['expire_time']) && $user_data['expire_time']!='0000-00-00 00:00:00' && $user_data['expire_time']>0 ){
										$user_data['start_time'] = date( 'Y-m-d h:i:s' );
									}else{
										$user_data['start_time'] = 0;
									}

							} else {
									$user_data['start_time'] = $user_data['start_time'];//strtotime( $user_data['start_time'] );
							}
							
							if (!isset($user_data['expire_time']) || $user_data['expire_time']=='0000-00-00 00:00:00' || $user_data['expire_time']=='0' ){
									$user_data['expire_time'] = 0;
							} else {
									$user_data['expire_time'] = $user_data['expire_time'];//strtotime( $user_data['expire_time'] );
							}
							\Indeed\Ihc\UserSubscriptions::assign( $uid, $lid, [ 'start_time' => $user_data['start_time'], 'expire_time' => $user_data['expire_time'] ] );
							if ( in_array( $uid, $this->updatedUsers ) ){
									$this->updatedUsers[] = $uid;
							}
						}
						if (isset($user_data['start_time'])){
							 unset($user_data['start_time']);
						}
						if (isset($user_data['expire_time'])){
							 unset($user_data['expire_time']);
						}
						if (isset($user_data['level_slug'])){
							 unset($user_data['level_slug']);
						}
					}

					/// assign user data
					foreach ($user_data as $meta_key => $meta_value){
							if ( !in_array($meta_key, array('level_slug','start_time','expire_time')) ){
									$temp_meta_value = Ihc_Db::does_user_meta_exists($uid, $meta_key, $meta_value);

									if ($temp_meta_value===FALSE && !empty($meta_value)){
										update_user_meta($uid, $meta_key, $meta_value);
									}
							}
					}

			} // end of while

			fclose($file_handler);
			unlink($this->file);
	}

	/**
	 * @since version 12.2
	 * @param array
	 * @return array
	 */
	public function userFieldsFromLabelsToSlugs( $keys=[] )
	{
			// getting register fields
			$exclude = [ 'pass2', 'tos', 'recaptcha', 'ihc_dynamic_price', 'ihc_social_media' ];// 'pass1',
			$register_fields = ihc_get_user_reg_fields();
			$columnNames = [
								'ID' 											=> esc_html__('User ID', 'ihc'),
								'user_login' 							=> esc_html__('Username', 'ihc'),
								'user_email' 							=> esc_html__('Email', 'ihc'),
								'user_pass'								=> esc_html__('Password', 'ihc'),
								'first_name' 							=> esc_html__('First Name', 'ihc'),
								'last_name' 							=> esc_html__('Last Name', 'ihc'),
								'membership_name' 				=> esc_html__('Membership Slug', 'ihc'),
								'membership_label'  			=> esc_html__('Membership Label', 'ihc'),
								'start_time' 							=> esc_html__('Started Time', 'ihc'),
								'expire_time' 						=> esc_html__('Expired Time', 'ihc'),
								'roles'										=> esc_html__('WP Role', 'ihc'),
								'user_registered' 			  => esc_html__('Join Date', 'ihc')
			];
			foreach ($register_fields as $k=>$v){
					if ( in_array( $v['name'], $exclude ) ){
							unset($register_fields[$k]);
					} else if( isset( $columnNames[ $v['name'] ] ) ){
							continue;
					} else {
							if (isset($v['native_wp']) && $v['native_wp']){
									$columnNames[ $v['name'] ] = esc_html__($v['label'], 'ihc');
							} else {
									$columnNames[ $v['name'] ] = $v['label'];
							}
					}
			}

			foreach ( $keys as $index => $value ){
					if ( in_array( $value, $columnNames ) ){
							$theKey = array_search ( $value, $columnNames );
							$keys[$index] = $theKey;
					}
			}
			return $keys;
	}

}
