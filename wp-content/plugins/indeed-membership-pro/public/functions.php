<?php
function ihc_test_if_must_block($block_or_show, $user_levels, $target_levels, $post_id=-1, $usedLocation='' ){
	/*
	 * return if user must be block of not
	 * @param:
	 * block_or_show = string 'block' or 'show'
	 * $user_levels = string (all current user levels seppareted by comma)
	 * target levels = array (show/hide content for users with these levels)
	 * @return :
	 * 0 - show
	 * 1 - block
	 * 2 - expired
	 */
	global $current_user;
	$block = 0; //SHOW
	if (!$target_levels || (isset($target_levels[0]) && $target_levels[0]=='')){
		$block = apply_filters('filter_on_ihc_test_if_must_block', $block, $block_or_show, $user_levels, $target_levels, $post_id, $usedLocation );
		return $block;
	}
	$registered_user = ($user_levels=='unreg') ? 'other' : 'reg';

	$arr = explode(',', $user_levels);

	if ($target_levels && is_array($target_levels)){
		switch($block_or_show){
			case 'block': {
				$block = 0;
				break;
			}
			case 'show': {
				$block = 1;
				break;
			}
		}

		foreach ($target_levels as $current_user_level){
		  switch($block_or_show){
			case 'block': {
				if(in_array($current_user_level, $arr)){

					//===LEVEL VALIDATION
					$is_expired = ihc_is_user_level_expired($current_user->ID, $current_user_level);
					// :0 - level not expired
					// :1 - level expired

					$is_ontime = ihc_is_user_level_ontime($current_user_level);
					// :0 - level OUT time
					// :1 - level ON time

					if ($is_expired == 0 && $is_ontime == 1){
						//level VALIDATED for BLOCK
						$block = 1;
					//===END LEVEL VALIDATION
					}

				}elseif( in_array('all', $target_levels) || in_array($registered_user, $target_levels) ){
						$block = 1;
				}
				 break;
			}
			case 'show': {
				if(in_array($current_user_level, $arr)){

					//===LEVEL VALIDATION
					$is_expired = ihc_is_user_level_expired($current_user->ID, $current_user_level);
					// :0 - level not expired
					// :1 - level expired

					$is_ontime = ihc_is_user_level_ontime($current_user_level);
					// :0 - level OUT time
					// :1 - level ON time

					if ($is_expired == 0 && $is_ontime == 1){
						//level VALIDATED for SHOW
						$block = 0;
					//===END LEVEL VALIDATION
				  }

				}elseif( in_array('all', $target_levels) || in_array($registered_user, $target_levels)){
						$block = 0;
				}
				if ($block == 0){
					$block = ihc_check_drip_content($current_user->ID, $current_user_level, $post_id);
					$block = apply_filters('filter_on_ihc_test_if_must_block', $block, $block_or_show, $user_levels, $target_levels, $post_id, $usedLocation );
					return $block;
				}
				break;
			}
		  }
		}
	}
	$block = apply_filters('filter_on_ihc_test_if_must_block', $block, $block_or_show, $user_levels, $target_levels, $post_id, $usedLocation );
	return $block;
}

function ihc_is_user_level_expired($u_id, $l_id, $not_started_check=TRUE, $expire_check=TRUE){
	/*
	 * test if user level is expired
	 * @param: user id, level id
	 * @return:
	 * 1 - expired/not available yet
	 * 0 - ok
	 */

	global $wpdb;

	$u_id = sanitize_text_field($u_id);
	$l_id = sanitize_text_field($l_id);

	$grace_period = \Indeed\Ihc\UserSubscriptions::getGracePeriod( $u_id, $l_id );

	$data = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription( $u_id, $l_id );
	$current_time = indeed_get_unixtimestamp_with_timezone();
	if (!empty($data['start_time']) && $not_started_check){
		$start_time = strtotime($data['start_time']);
		if ($current_time<$start_time){
			//it's not available yet
			return 1;
		}
	}
	if (!empty($data['expire_time']) && $expire_check){
		$expire_time = strtotime($data['expire_time']) + ((int)$grace_period * 24 * 60 *60);
		if ($current_time>$expire_time){
			//it's expired
			return 1;
		}
	}
	return 0;
}

function ihc_is_user_level_ontime($l_id){
	/*
	 * test if user level is on time
	 * @param: level_id
	 * @return:
	 * 1 - ON time - ok
	 * 0 - OFF time
	 */
	$on_time = 1;
	$level_data = ihc_get_level_by_id($l_id);
	if (isset($level_data['special_weekdays'])){
		$day = date( "w", indeed_get_unixtimestamp_with_timezone() );
		if ($level_data['special_weekdays']=='weekdays' && ($day==6 || $day==0) ){
			//WEEK DAYS
			$on_time = 0;
		} else if ($level_data['special_weekdays']=='weekend'  && ($day!=6 && $day!=0) ){
			// WEEK-END
			$on_time = 0;
		}
	}
	return $on_time;
}

function ihc_check_drip_content($uid, $lid, $post_id){
	/*
	 * /////// DRIP CONTENT \\\\\\\\
	 * @param int, int, int
	 * @return int ( 1 : block, 0 : unblock)
	 */
	global $wpdb;
	$uid = sanitize_text_field($uid);
	$lid = sanitize_text_field($lid);
	$post_id = sanitize_text_field($post_id);
	$block = 0;
	if ($post_id>-1){
		$post_meta = ihc_post_metas($post_id);
		if (!empty($post_meta['ihc_drip_content'])){
			 /// DRIP CONTENT ACTIVE
			$current_time = indeed_get_unixtimestamp_with_timezone();

			if ($lid=='unreg'){
				/// drip content for unreg users
				if ($post_meta['ihc_drip_end_type']==3){
					$start_time = strtotime($post_meta['ihc_drip_start_certain_date']);
					$end_time = strtotime($post_meta['ihc_drip_end_certain_date']);
					if ($current_time<$start_time){//to early
						$block = 1;
						$block = apply_filters('filter_on_ihc_check_drip_content', $block, $uid, $lid, $post_id);
						return $block;
					}
					if ($current_time>$end_time){//to late
						$block = 1;
						$block = apply_filters('filter_on_ihc_check_drip_content', $block, $uid, $lid, $post_id);
						return $block;
					}
				}
			} else {
				/// registered users with level or not
				if ($lid=='reg'){
					$query = $wpdb->prepare( "SELECT user_registered FROM {$wpdb->users} WHERE ID=%d ", $uid );
					$data = $wpdb->get_row( $query );
					if (!empty($data->user_registered)){
						$subscription_start = strtotime($data->user_registered);
					}
				} else {
					$data = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription( $uid, $lid );
					if (!empty($data['start_time'])){
						$subscription_start = strtotime($data['start_time']);
					}
				}

				if (!empty($subscription_start)){
					//SET START TIME
					if ($post_meta['ihc_drip_start_type']==1){
						//initial
						$start_time = $subscription_start;
					} else if ($post_meta['ihc_drip_start_type']==2){
						//after
						if ($post_meta['ihc_drip_start_numeric_type']=='days'){
							$start_time = $subscription_start + ((int)($post_meta['ihc_drip_start_numeric_value']) * 24 * 60 * 60); // update since version 12.4
						} else if ($post_meta['ihc_drip_start_numeric_type']=='weeks'){
							$start_time = $subscription_start + ((int)($post_meta['ihc_drip_start_numeric_value']) * 7 * 24 * 60 * 60); // update since version 12.4
						} else {
							$start_time = $subscription_start + ((int)($post_meta['ihc_drip_start_numeric_value']) * 30 * 24 * 60 * 60); // update since version 12.4
						}
					} else {
						//certain date
						$start_time = strtotime($post_meta['ihc_drip_start_certain_date']);
					}
					if (empty($start_time)){
						$start_time = $subscription_start;
					}

					//SET END TIME
					if ($post_meta['ihc_drip_end_type']==1){
						//infinite
						$end_time = $start_time + 3600 * 24 * 60 * 60;// 10years should be enough
					} else if ($post_meta['ihc_drip_end_type']==2){
						//after
						if ($post_meta['ihc_drip_end_numeric_type']=='days'){
							$end_time = $start_time + $post_meta['ihc_drip_end_numeric_value'] * 24 * 60 * 60;
						} else if ($post_meta['ihc_drip_end_numeric_type']=='weeks'){
							$end_time = $start_time + $post_meta['ihc_drip_end_numeric_value'] * 7 * 24 * 60 * 60;
						} else {
							$end_time = $start_time + $post_meta['ihc_drip_end_numeric_value'] * 30 * 24 * 60 * 60;
						}
					} else {
						//certain date
						$end_time = strtotime($post_meta['ihc_drip_end_certain_date']);
					}
					if (empty($end_time)){
						$end_time = $start_time + 3600 * 24 * 60 * 60;
					}

					if ($current_time<$start_time){//to early
						$block = 1;
						$block = apply_filters('filter_on_ihc_check_drip_content', $block, $uid, $lid, $post_id);
						return $block;
					}
					if ($current_time>$end_time){//to late
						$block = 1;
						$block = apply_filters('filter_on_ihc_check_drip_content', $block, $uid, $lid, $post_id);
						return $block;
					}
				}
			}
		}
	}
	$block = apply_filters('filter_on_ihc_check_drip_content', $block, $uid, $lid, $post_id);
	return $block;
}

function ihc_block_url($url, $current_user, $post_id){
	/*
	 * @param string, string, int
	 * @return none
	 */
	if (!$current_user){
		$current_user = ihc_get_user_type();
	}
	if ($current_user=='admin'){
		//admin can view anything
		return;
	}

	if (strpos($url, 'indeed-membership-pro')!==FALSE){
		/// links inside plugin must work everytime!
		return;
	}

	$redirect_link = false;
	$data = get_option('ihc_block_url_entire');
	if ($data){
		//////////////////////// BLOCK URL
		$key = ihc_array_value_exists($data, $url, 'url');
		if ($key!==FALSE){
			if ($data[$key]['target_users']!='' && $data[$key]['target_users']!=-1){
				$target_users = explode(',', $data[$key]['target_users']);
			} else {
				$target_users = FALSE;
			}

			$block_or_show = (isset($data[$key]['block_or_show'])) ? $data[$key]['block_or_show'] : 'block';

			/// used to $block = ihc_test_if_must_block('block', $current_user, $target_users, $post_id); older version
			$block = ihc_test_if_must_block($block_or_show, $current_user, $target_users, $post_id);	//test if user must be block

			if ($block){
				if ($data[$key]['redirect'] && $data[$key]['redirect']!=-1){
					$redirect_link = get_permalink($data[$key]['redirect']);
					if (!$redirect_link){
						$redirect_link = ihc_get_redirect_link_by_label($data[$key]['redirect']);
					}
				} else {
					//if not exists go to homepage
					$redirect_link = get_home_url();
				}
			}
		}
	}
	$data = get_option('ihc_block_url_word');
	if ($data){
		///////////////// BLOCK IF URL CONTAINS A SPECIFIED WORD
		foreach($data as $k=>$arr){
			if (strpos($url, $arr['url'])!==FALSE) {
				if ($arr['target_users']!='' && $arr['target_users']!=-1){
					$target_users = explode(',', $arr['target_users']);
				} else {
					$target_users = FALSE;
				}

				$block_or_show = (isset($arr['block_or_show'])) ? $arr['block_or_show'] : 'block';

				$block = ihc_test_if_must_block($block_or_show, $current_user, $target_users, $post_id);

				if ($block){
					if ($arr['redirect'] && $arr['redirect']!=-1){
						$redirect_link = get_permalink($arr['redirect']);
						if (!$redirect_link){
							$redirect_link = ihc_get_redirect_link_by_label($arr['redirect']);
						}
					} else {
						//if not exists go to homepage
						$redirect_link = get_home_url();
					}
					break;
				}
			}
		}
	}
	if ($redirect_link){
		$redirect_link = apply_filters( 'ump_filter_block_url_redirect_link', $redirect_link, $post_id );
		wp_redirect($redirect_link);
		exit();
	}
}

function ihc_check_block_rules($url='', $current_user='', $post_id=0){
	/*
	 * @param string, string, int
	 * @return none
	 */
	if (!$current_user){
		$current_user = ihc_get_user_type();
	}
	if ($current_user=='admin'){
		//admin can view anything
		return;
	}
	if (strpos($url, 'indeed-membership-pro')!==FALSE){
		/// links inside plugin must work everytime!
		return;
	}

	/// CHECK BLOCK ALL POST TYPES
	$block_posts = get_option('ihc_block_posts_by_type');

	if (!empty($block_posts)){
		$post_type = get_post_type($post_id);

		foreach ($block_posts as $key=>$array){
			if ($post_type==$array['post_type']){
				$except_arr = array();
				if (!empty($array['except'])){
					$except_arr = explode(',', $array['except']);
				} else {
					$except_arr = array();
				}
				if (!empty($except_arr) && in_array($post_id, $except_arr)){
					continue; /// SKIP THIS RULE
				}
				/// TARGET USERS
				$target_users = FALSE;
				if (!empty($array['target_users']) && $array['target_users']!=-1){
					$target_users = explode(',', $array['target_users']);
				}
				$block_or_show = (isset($array['block_or_show'])) ? $array['block_or_show'] : 'block';
				$block = ihc_test_if_must_block($block_or_show, $current_user, $target_users, $post_id);//test if user must be block

				if ($block){
					if (!empty($array['redirect'])){
						$redirect = $array['redirect'];
					}
					if (!empty($redirect)){
						$redirect_link = get_permalink($redirect);
					}
					if (empty($redirect_link)){
						$redirect_link = get_home_url();
					}
					break;
				}
			}
		}
	}

	/// BLOCK CATS
	$block_terms_data = get_option('ihc_block_cats_by_name');
	if (!empty($block_terms_data)){
		$post_terms = get_terms_for_post_id($post_id);
		if (!empty($post_terms)){
			foreach ($block_terms_data as $key=>$array){
				if (in_array($array['cat_id'], $post_terms)){
					$except_arr = array();
					if (!empty($array['except'])){
						$except_arr = explode(',', $array['except']);
						if ( in_array( $post_id, $except_arr ) ){
								continue;
						}
					}

					/// TARGET USERS
					$target_users = FALSE;
					if (!empty($array['target_users']) && $array['target_users']!=-1){
						$target_users = explode(',', $array['target_users']);
					}

					$block_or_show = (isset($array['block_or_show'])) ? $array['block_or_show'] : 'block';
					$block = ihc_test_if_must_block($block_or_show, $current_user, $target_users, $post_id);//test if user must be block

					if ($block){
						if (!empty($array['redirect'])){
							$redirect = $array['redirect'];
						}
						if (!empty($redirect)){
							$redirect_link = get_permalink($redirect);
						}
						if (empty($redirect_link)){
							$redirect_link = get_home_url();
						}
						break;
					}
				}
			}
		}
	}

	if (empty($redirect_link)){
		$redirect_link = '';
	}
	$redirect_link = apply_filters('filter_on_ihc_block_url', $redirect_link, $url, $current_user, $post_id);

	/// REDIRECT IF IT's CASE
	if (!empty($redirect_link)){
		wp_redirect($redirect_link);
		exit();
	}

}

function ihc_if_register_url($url){
	/*
	 * test if current page is register page
	 * if is register page and lid(level id) is not set redirect to subscription plan (if its set and available one)
	 */

	$reg_page = get_option('ihc_general_register_default_page');

	if ($reg_page && $reg_page!=-1){

		$reg_page_url = get_permalink($reg_page);

		if (strpos($url,$reg_page_url) !== FALSE){
			//current page is register page

			$subscription_type = get_option('ihc_subscription_type');
			if ( $subscription_type=='predifined_level' && !isset( $_GET['lid'] ) ){
				 	return;
			}

			// special condition for elementor - edit page
			if ( current_user_can( 'administrator' ) && isset( $_GET['elementor-preview'] ) && $_GET['elementor-preview'] !== '' ){
					if (!function_exists('is_plugin_active')){
						include_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					if ( is_plugin_active( 'elementor/elementor.php' ) ){
							return;
					}
			}
			// end of special condition for elementor - edit page

			$lid = isset( $_GET['lid'] ) ? sanitize_text_field($_GET['lid']) : -1;
			$levels = \Indeed\Ihc\Db\Memberships::getAll();
			if ( $lid > -1 && \Indeed\Ihc\Db\Memberships::getOne( $lid ) !== false ){
					$checkResult = ihc_check_level_restricted_conditions( [ $lid => [] ] );
					if ( isset( $checkResult[ $lid ] ) ){
							// user can ask for this level
							return;
					}
			}

			$doRedirect = apply_filters( 'ihc_public_filter_register_form_no_lid_redirect', true, $url );

			if ( !$doRedirect ){
					return;
			}

			// no membership, redirect to subscription page.
			$subscription_pid = get_option('ihc_subscription_plan_page');
			if ($subscription_pid && $subscription_pid!=-1){
				$subscription_link = get_permalink($subscription_pid);
				if ($subscription_link){
					wp_redirect($subscription_link);
					exit();
				}
			}
		}
	}
}

function ihc_block_page_content($postid, $url){
	/*
	 * test if current post, page content must to blocked
	 */
	$meta_arr = ihc_post_metas($postid);
	if(isset($meta_arr['ihc_mb_block_type']) && $meta_arr['ihc_mb_block_type']){
		if($meta_arr['ihc_mb_block_type']=='redirect'){
			/////////////////////// REDIRECT
			if(isset($meta_arr['ihc_mb_who'])){

				//getting current user type and target user types
				$current_user = ihc_get_user_type();
				if($meta_arr['ihc_mb_who']!=-1 && $meta_arr['ihc_mb_who']!=''){
					$target_users = explode(',', $meta_arr['ihc_mb_who']);
				} else {
					$target_users = FALSE;
				}
				//test if current user must be redirect
				if($current_user=='admin'){
					 return;//show always for admin
				}

				$redirect = ihc_test_if_must_block($meta_arr['ihc_mb_type'], $current_user, $target_users, $postid);

				if($redirect){
					//getting default redirect id
					$default_redirect_id = get_option('ihc_general_redirect_default_page');

					//PREVENT INFINITE REDIRECT LOOP - if current page is default redirect page return
					if($default_redirect_id==$postid){
						 return;
					}

					if (isset($meta_arr['ihc_mb_redirect_to']) && $meta_arr['ihc_mb_redirect_to']!=-1){
						$redirect_id = $meta_arr['ihc_mb_redirect_to'];//redirect to value that was selected in meta box
						//test if redirect page exists

						if(get_post_status($redirect_id)){
							$redirect_link = get_permalink($redirect_id);
						} else {
							//custom redirect link
							if (!empty($redirect_id)){
								$redirect_link = ihc_get_redirect_link_by_label($redirect_id);
							}

							if (empty($redirect_link)){
								//if not exists go to homepage
								$redirect_link = home_url();
							}
						}
					} else {
						if ($default_redirect_id && $default_redirect_id!=-1){
							if (get_post_status($default_redirect_id)){
								$redirect_link = get_permalink($default_redirect_id); //default redirect page, selected in general settings
							} else {
								//custom redirect link
								if (!empty($redirect_id)){
									$redirect_link = ihc_get_redirect_link_by_label($redirect_id);
								}
								if (empty($redirect_link)){
									//if not exists go to homepage
									$redirect_link = home_url();
								}
							}
						} else {
							$redirect_link = home_url();//if default redirect page is not set, redirect to home
						}
					}


					if ($url==$redirect_link){
						//PREVENT INFINITE REDIRECT LOOP
						return;
					}

					$redirect_link = apply_filters('filter_on_ihc_link_to_redirect', $redirect_link, $current_user, $target_users, $postid);
					wp_redirect($redirect_link);
					exit();
				}
			}
		}else{
			////////////////////// REPLACE CONTENT, adding filter to block, show only the content
			add_filter('the_content', 'ihc_filter_content');
		}
	}
}

function ihc_init_form_action($url){
	/*
	 * form actions :
	 * REGISTER
	 * LOGIN
	 * UPDATE
	 * RESET PASS
	 * DELETE LEVEL FROM ACCOUNT PAGE
	 * CANCEL LEVEL FROM ACCOUNT PAGE
	 * RENEW LEVEL
	 */
	switch (sanitize_text_field($_POST['ihcaction'])){
		case 'suspend':
			global $current_user;
			if (!empty($current_user->ID)){
				if (ihc_suspend_account($current_user->ID)){
					////// do logout
					///write log
					Ihc_User_Logs::set_user_id($current_user->ID);
					$username = Ihc_Db::get_username_by_wpuid($current_user->ID);
					Ihc_User_Logs::write_log(__('User ', 'ihc') . $username .esc_html__(' suspend his profile.', 'ihc'), 'user_logs');
					require_once IHC_PATH . 'public/logout.php';
					ihc_do_logout($url);
				}
			}
			break;

		case 'login':
			//login
			$ihcLoginForm = new \Indeed\Ihc\LoginForm();
			$ihcLoginForm->doLogin( sanitize_text_field( $_POST['ihcaction'] ), indeed_sanitize_array( $_POST ) );
			break;
		case 'update':
			/////////////////////// UPDATE
			if (is_user_logged_in()){

				// Profile Form - new implementation starting with 11.0
				$profileEdit = new \Indeed\Ihc\ProfileForm();
				$result = $profileEdit->setUid()
				                      ->setFields()
				                      ->doUpdate( indeed_sanitize_array($_POST) );
				// end of Profile Form

			}
		break;
		case 'reset_pass':
			//check nonce
			if ( empty( $_POST['ihc_lost_password_nonce'] ) || !wp_verify_nonce( sanitize_text_field($_POST['ihc_lost_password_nonce']), 'ihc_lost_password_nonce' ) ){
					return;
			}
			$reset_password = new \Indeed\Ihc\ResetPassword();
			$reset_password->send_mail_with_link( sanitize_text_field( $_REQUEST['email_or_userlogin'] ) );
		break;
		case 'renew_cancel_delete_level_ap':
			global $current_user;

			// ----------------------------- DELETE USER - LEVEL RELATION ---------------------
			if (isset($_POST['ihc_delete_level']) && $_POST['ihc_delete_level']!=''){
				//delete level
				if (isset($current_user->ID)){
					/// user logs
					Ihc_User_Logs::set_user_id($current_user->ID);
					Ihc_User_Logs::set_level_id( sanitize_text_field($_POST['ihc_delete_level']) );
					$username = Ihc_Db::get_username_by_wpuid($current_user->ID);
					$level_name = Ihc_Db::get_level_name_by_lid( sanitize_text_field($_POST['ihc_delete_level']) );
					Ihc_User_Logs::write_log(__('The Member has ', 'ihc') . $username .esc_html__(' deleted Membership ', 'ihc') . $level_name, 'user_logs', sanitize_text_field($_POST['ihc_delete_level']));
					\Indeed\Ihc\UserSubscriptions::deleteOne( $current_user->ID, sanitize_text_field($_POST['ihc_delete_level']) );
				}
			}
			// ----------------------------- END OF DELETE USER - LEVEL RELATION ---------------------

			// ------------------------ CANCEL LEVEL --------------------------------
			if (isset($_POST['ihc_cancel_level']) && $_POST['ihc_cancel_level']!=''){
				//////////////cancel level
				/// user logs
				Ihc_User_Logs::set_user_id($current_user->ID);
				Ihc_User_Logs::set_level_id( sanitize_text_field($_POST['ihc_cancel_level']) );
				$username = Ihc_Db::get_username_by_wpuid($current_user->ID);
				$level_name = Ihc_Db::get_level_name_by_lid( sanitize_text_field($_POST['ihc_cancel_level']) );
				Ihc_User_Logs::write_log(__('The Member has ', 'ihc') . $username .esc_html__(' canceled Membership ', 'ihc') . $level_name, 'user_logs', sanitize_text_field($_POST['ihc_cancel_level']) );

				$cancel = new \Indeed\Ihc\Payments\CancelSubscription();
				$cancel->setUid( $current_user->ID )
							 ->setLid( sanitize_text_field( $_POST['ihc_cancel_level'] ) )
							 ->proceed();


			}
			// ----------------------------- END OF CANCEL LEVEL ----------------------------

			// ----------------------- FINISH PAYMENT ------------------------------
			if ( isset( $_POST['ihc_finish_payment_level'] ) && $_POST['ihc_finish_payment_level'] ){
					// getting payment type
					$orderId = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : false;
					$orderMeta = new \Indeed\Ihc\Db\OrderMeta();
					$paymentType = $orderMeta->get( $orderId, 'ihc_payment_type' );
					if ( $paymentType == false ){
							$paymentType = isset( $_POST['ihc_payment_gateway'] ) ? sanitize_text_field( $_POST['ihc_payment_gateway'] ) : '';
					}
					
					$newWay = [
											'stripe_checkout_v2',
											'pagseguro',
											'paypal_express_checkout',
											'mollie',
											'paypal',
											'bank_transfer',
											'twocheckout',
					];
					if ( in_array( $paymentType, $newWay )){
							// new way
							$finishPayment = new \Indeed\Ihc\Payments\FinishUnpaidPayments();
							$finishPayment->setInput([
																					'payment_type'			=> $paymentType,
																					'uid'								=> $current_user->ID,
																					'lid'								=> sanitize_text_field( $_POST['ihc_finish_payment_level'] ),
																					'order_id'					=> $orderId,
														])
														->doIt();
					} else {
							// redirect to checkout page
							ihcRedirectToCheckout( [ 'lid' => sanitize_text_field( $_POST['ihc_finish_payment_level'] ), 'oid'	=> $orderId, 'ihc-finish-payment' => '1' ] );
					}
			}
			// ---------------------- END OF FINISH PAYMENT ------------------------------

			// ----------------------- RENEW LEVEL
			if ( isset( $_POST['ihc_renew_level'] ) && $_POST['ihc_renew_level'] ){
					// redirect to checkout page
					ihcRedirectToCheckout( [ 'lid' => sanitize_text_field( $_POST['ihc_renew_level'] ) ] );
			}
			// ---------------------- END OF RENEW LEVEL

		break;

		default:
			do_action( 'ihc_action_public_post', sanitize_text_field($_POST['ihcaction']), indeed_sanitize_array($_POST) );
			break;

	}
}//end of ihc_init_form_action()

function ihc_check_individual_page_block($post_id=0){
	/*
	 * TRUE if must block
	 * @param int
	 * @return boolean
	 */
	 global $current_user;
	 $user_type = ihc_get_user_type();
	 if ($user_type!='admin'){
		 $uid = (isset($current_user->ID)) ? $current_user->ID : 0;
		 if ($post_id){
		 	 $individual_page = get_post_meta($post_id, 'ihc_individual_page', TRUE);
			 if ($individual_page && $individual_page!=$uid){
			 	return TRUE;
			 }
		 }
	 }
	 return FALSE;
}

function ihc_do_block_if_individual_page($post_id=0){
	/*
	 * Do REDIRECT IF IT's CASE
	 * @param int
	 * @return none
	 */
	if ($post_id && ihc_is_magic_feat_active('individual_page')){
		$is_individual_page = ihc_check_individual_page_block($post_id);
		if ($is_individual_page){
			$default_redirect_id = get_option('ihc_general_redirect_default_page');
			if ($default_redirect_id){
				$redirect_link = get_permalink($default_redirect_id);
			}
			if (empty($redirect_link)){
				$redirect_link = home_url();
			}
			wp_redirect($redirect_link);
			exit();
		}
	}
}

if ( !function_exists( 'ump_process_content' ) ):
function ump_process_content( $content='' )
{
		$restrictDoShortcode = get_option( 'ihc_prevent_use_of_do_shortcode_on_content', 0 );
		$restrictDoShortcode = (int)$restrictDoShortcode;
		if ( $restrictDoShortcode === 1 ){
				return $content;
		} else {
				return do_shortcode( $content );
		}

}
endif;
