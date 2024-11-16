<?php
/*
 * LogOut Link [ihc-logout-link] - ihc_logout_link
 * Locker [ihc-hide-content] - ihc_hide_content_shortcode
 * Reset Password Form [ihc-pass-reset] - ihc_lost_pass_form
 * User Page [ihc-user-page] - ihc_user_page_shortcode
 * Subscription Plan [ihc-select-level] - ihc_print_level_link
 * User Data [ihc-user] - ihc_print_user_data
 * User Listing [ihc-list-users] - ihc_public_listing_users
 * View User Page [ihc-view-user-page] - ihc_public_view_user_page
 */

add_shortcode( 'ihc-login-form', 'ihc_login_form' );
add_shortcode( 'ihc-register', 'ihcRegisterForm' );
add_shortcode( 'ihc-register-lite', 'ihcRegisterFormLite' );
add_shortcode( 'ihc-logout-link', 'ihc_logout_link' );
add_shortcode( 'ihc-pass-reset', 'ihc_lost_pass_form' );
add_shortcode( 'ihc-user-page', 'ihc_user_page_shortcode' );
add_shortcode( 'ihc-select-level', 'ihc_user_select_level' );
add_shortcode( 'ihc-visitor-inside-user-page', 'ihc_public_visitor_inside_user_page' );

add_shortcode( 'ihc-thank-you-page', 'ihc_thank_you_page_shortcode' );

add_shortcode( 'ihc-list-users', 'ihc_public_listing_users' );
add_shortcode( 'ihc-hide-content', 'ihc_hide_content_shortcode' );

add_shortcode( 'ihc-user', 'ihc_print_user_data' );
add_shortcode( 'ihc-level-link', 'ihc_print_level_link' );
add_shortcode( 'ihc-purchase-link', 'ihc_print_level_link' );

add_shortcode( 'ihc-lgoin-fb', 'ihc_print_fb_login' );

add_shortcode( 'ihc-membership-card', 'ihc_membership_card' );
add_shortcode( 'ihc-list-gifts', 'ihc_do_list_gifts' );
add_shortcode( 'ihc-individual-page-link', 'ihc_link_to_individual_page' );
add_shortcode( 'ihc-list-all-access-posts', 'ihc_list_all_access_posts' );
add_shortcode( 'ihc-list-user-levels', 'ihc_list_user_levels' );
add_shortcode( 'ihc-suspend-account', 'ihc_suspend_account_bttn' );
add_shortcode( 'ihc-login-popup', 'ihc_login_popup' );
add_shortcode( 'ihc-register-popup', 'ihc_register_popup' );

add_shortcode( 'ihc-register-form-for-popup', 'ihc_register_form_for_modal' );


if (!function_exists('ihc_login_form')):
/**
	* @param array ( Shortcode attributes:  template , remember , register , lost_pass , social , captcha . )
	* @return string
	*/
function ihc_login_form( $attr=[] )
{
	$loginForm = new \Indeed\Ihc\LoginForm();
	return $loginForm->html($attr);
}
endif;

if ( !function_exists( 'ihcRegisterForm' ) ):
function ihcRegisterForm( $attr=[] )
{
		$registerForm = new \Indeed\Ihc\RegisterForm();
		return $registerForm->form( $attr );
}
endif;

if ( !function_exists( 'ihcRegisterFormLite') ):
function ihcRegisterFormLite( $attr=[] )
{
		$registerForm = new \Indeed\Ihc\RegisterForm();
		return $registerForm->liteForm( $attr );
}
endif;

if (!function_exists('ihc_logout_link')):
function ihc_logout_link($attr=array()){
	/*
	 * @param array
	 * @return string
	 */
	///////////// LOGOUT FORM
	$str = '';
	if (is_user_logged_in()){
		$meta_arr = ihc_return_meta_arr('login');
		if($meta_arr['ihc_login_custom_css']){
			wp_register_style( 'dummy-handle', false );
			wp_enqueue_style( 'dummy-handle' );
			wp_add_inline_style( 'dummy-handle', $meta_arr['ihc_login_custom_css'] );
		}
		if (!empty($attr['template'])){
			$meta_arr['ihc_login_template'] = $attr['template'];
		}
		$str .= '<div class="ihc-logout-wrap '.$meta_arr['ihc_login_template'].'">';
			$link = add_query_arg( 'ihcdologout', 'true', get_permalink() );//name was ihcaction, value was logout
			$str .= '<a href="'.$link.'">' .esc_html__('Log Out', 'ihc').'</a>';
		$str .= '</div>';
	}
	return $str;
}
endif;

if (!function_exists('ihc_hide_content_shortcode')):
function ihc_hide_content_shortcode($meta_arr=array(), $content=''){
	/*
	 * @param array, string
	 * @return string
	 */
	///GETTING USER TYPE

	$current_user = ihc_get_user_type();
	if ($current_user=='admin'){
		return do_shortcode($content);//admin can view anything
	}

		if ((!isset($meta_arr['ihc_mb_who']) || $meta_arr['ihc_mb_who'] == '') && isset($meta_arr['membership'])){
			$meta_arr['ihc_mb_who'] = $meta_arr['membership'];
		}
		if (!isset($meta_arr['ihc_mb_type']) || $meta_arr['ihc_mb_type'] == ''){
			$meta_arr['ihc_mb_type'] = "show";
		}
		if (!isset($meta_arr['ihc_mb_template']) || $meta_arr['ihc_mb_template'] == ''){
			$meta_arr['ihc_mb_template'] = "-1";
		}


	if (isset($meta_arr['ihc_mb_who'])){
		if ($meta_arr['ihc_mb_who']!=-1 && $meta_arr['ihc_mb_who']!=''){
			$target_users = explode(',', $meta_arr['ihc_mb_who']);
		} else {
			$target_users = FALSE;
		}

	} else {
		return do_shortcode($content);
	}

	////TESTING USER
	global $post;
	$block = ihc_test_if_must_block($meta_arr['ihc_mb_type'], $current_user, $target_users, (isset($post->ID)) ? $post->ID : -1, TRUE);

	//IF NOT BLOCKING, RETURN THE CONTENT
	if (!$block){
		return do_shortcode($content);
	}

	//LOCKER HTML
	if (isset($meta_arr['ihc_mb_template'])){
		$templatePath = IHC_PATH . 'public/layouts-locker.php';
		$templatePath = apply_filters('ihc_filter_on_load_template', $templatePath, 'layouts-locker.php' );
		include_once $templatePath;
		return ihc_print_locker_template($meta_arr['ihc_mb_template']);
	}

	//IF SOMEHOW IT CAME UP HERE, RETURN CONTENT
	return do_shortcode($content);
}
endif;

if (!function_exists('ihc_lost_pass_form')):
/**
 * @param none
 * @return string
 */
function ihc_lost_pass_form()
{
	$resetPasswordObject = new \Indeed\Ihc\ResetPassword();
	return $resetPasswordObject->form();
	/*
	$str = '';
	if (!is_user_logged_in()){
		$meta_arr = ihc_return_meta_arr('login');
		$str .= ihc_print_form_password($meta_arr);

		global $ihc_reset_pass;
		if ($ihc_reset_pass){
			if ($ihc_reset_pass==1){
				//reset ok
				$message = get_option('ihc_reset_msg_pass_ok');
				$message = stripslashes( $message );
				return '<span class="ihc-reset-pass-success-msg">' . $message . '</span>';
			} else {
				//reset error
				$err_msg = get_option('ihc_reset_msg_pass_err');
				$err_msg = stripslashes( $err_msg );
				if ($err_msg){
					$str .= '<div class="ihc-wrapp-the-errors">' . $err_msg . '</div>';
				}
			}
		}
	} else {
		$user_type = ihc_get_user_type();
		if ($user_type=='admin'){
				$str .= '<div class="ihc-warning-message"><strong>' . esc_html__('Administrator Info', 'ihc') . '</strong>' . esc_html__(': Lost Password Form is not showing up once you\'re logged. You may check how it it looks for testing purpose by opening the page into a separate incognito browser window.','ihc') . '<i>' . esc_html__('this message will not be visible for other users','ihc') .'</i></div>';
		}
	}
	return $str;
	*/
}
endif;

if (!function_exists('ihc_user_page_shortcode')):
function ihc_user_page_shortcode($attr=array()){
	/*
	 * @param array
	 * @return string
	 */
	global $wp_query;
	$str = '';
	if (is_user_logged_in()){
		if (!class_exists('ihcAccountPage')){
			require_once IHC_PATH . 'classes/ihcAccountPage.class.php';
		}
		$obj = new ihcAccountPage($attr);
		$tab = isset($_GET['ihc_ap_menu']) ? sanitize_text_field($_GET['ihc_ap_menu']) : '';
		if ( $tab === '' ){
				$tab = isset( $wp_query->query_vars['iump-account-tab'] ) ? sanitize_text_field($wp_query->query_vars['iump-account-tab']) : '';
		}

		$str .= $obj->print_page($tab);
	}
	return $str;
}
endif;

if (!function_exists('ihc_user_select_level')):
function ihc_user_select_level($attr=array()){
	/*
	 * @param array
	 * @return string
	 */
	$levels = \Indeed\Ihc\Db\Memberships::getAll();
	if ($levels){
		$register_url = '';

		$levels = ihc_reorder_arr($levels);
		$levels = ihc_check_show($levels); /// SHOW/HIDE
		$levels = ihc_check_level_restricted_conditions($levels); /// MAGIC FEAT.

		$levels = apply_filters( 'ihc_public_subscription_plan_list_levels', $levels );
		// @description used in public section - subcription plan. @param list of levels to display ( array )

		if ( !empty( $attr['id'] ) ){

				if ( strpos( $attr['id'], '″' ) !== false ){
						$attr['id'] = str_replace( '″', '', $attr['id'] );
				}
				if ( strpos( $attr['id'], '“' ) !== false ){
						$attr['id'] = str_replace( '“', '', $attr['id'] );
				}
				$onlyIds = strpos( $attr['id'], ',' ) === false ? array($attr['id']) : explode( ',', $attr['id'] );

				foreach ( $levels as $id => $levelData ){
						if ( !in_array( $id, $onlyIds ) ){
								unset( $levels[$id] );
						}
				}
		}

		/// TEMPLATE
		$template = (empty($attr['template'])) ? '' : $attr['template'];
		if (!$template){
			$template = get_option('ihc_level_template');
			if (!$template){
				$template = 'ihc_level_template_1';
			}
		}

		/// CUSTOM CSS
		$custom_css = (empty($attr['css'])) ? '' : $attr['css'];

		$register_page = get_option('ihc_general_register_default_page');
		if ($register_page){
			$register_url = get_permalink($register_page);
		}

		$fields = get_option('ihc_user_fields');
		///PRINT COUPON FIELD
		$num = ihc_array_value_exists($fields, 'ihc_coupon', 'name');
		$coupon_field = ($num===FALSE || empty($fields[$num]['display_public_ap'])) ? FALSE : TRUE;
		////PRINT SELECT PAYMENT
		$key = ihc_array_value_exists($fields, 'payment_select', 'name');
		$select_payment = ($key===FALSE || empty($fields[$key]['display_public_ap'])) ? FALSE : TRUE;

		$str = '';

		$u_type = ihc_get_user_type();
		/*
		// deprecated since version 11.7
		if ($u_type!='unreg' && $u_type!='pending' && $levels && ihcCheckCheckoutSetup() == FALSE){

			global $current_user;
			$taxes = Ihc_Db::get_taxes_rate_for_user((isset($current_user->ID)) ? $current_user->ID : 0);
			$register_template = get_option('ihc_register_template');
			$default_payment = get_option('ihc_payment_selected');
			if ($select_payment && empty( $attr['is_admin_preview'] ) ){
				$payments_available = ihc_get_active_payments_services();
				$register_fields_arr = ihc_get_user_reg_fields();
				$key = ihc_array_value_exists($register_fields_arr, 'payment_select', 'name');
				if (!empty($payments_available) && count($payments_available)>1 && $key!==FALSE && !empty($register_fields_arr[$key]['display_public_ap'])){
					$payment_select_string = ihc_print_payment_select($default_payment, $register_fields_arr[$key], $payments_available, 0);
				}
			}

			$the_payment_type = ( ihc_check_payment_available($default_payment) ) ? $default_payment : '';
			$templatePath = IHC_PATH . 'public/views/account_page-subscription_page-top_content.php';
			$templatePath = apply_filters('ihc_filter_on_load_template', $templatePath, 'account_page-subscription_page-top_content.php' );
			ob_start();
			require $templatePath;//IHC_PATH . 'public/views/account_page-subscription_page-top_content.php';
			$str = ob_get_contents();
			ob_end_clean();
		}
		*/

		///bt message
		if (!empty($_GET['ihc_lid'])){
			global $current_user;
			$str = ihc_print_bank_transfer_order($current_user->ID, sanitize_text_field($_GET['ihc_lid']) );
			global $stop_printing_bt_msg;
			$stop_printing_bt_msg = TRUE;
		}
		$templatePath = IHC_PATH . 'public/layouts-subscription.php';
		$templatePath = apply_filters('ihc_filter_on_load_template', $templatePath, 'layouts-subscription.php' );
		include_once $templatePath;//IHC_PATH . 'public/layouts-subscription.php';
		$str .= ihc_print_subscription_layout($template, $levels, $register_url, $custom_css, $select_payment);
		return $str;
	}
	return '';
}
endif;

if (!function_exists('ihc_print_level_link')):
/**
 * @param array
 * @param string
 * @param bool
 * @param bool
 * @return string
 */
function ihc_print_level_link( $attr=null, $content='', $print_payments=false, $subscription_plan=false ){

	if (!empty($content)){
		$str = $content;
	} else {
		$str =  esc_html__('Sign Up', 'ihc');
	}

	$href = '';
	if (!isset($attr['class'])){
		$attr['class'] = '';
	}
	if (!isset($attr['item_class'])){
		$attr['item_class'] = '';
	}

	$purchased = FALSE;

	if (!empty($purchased)){
		return ' <div class="ihc-level-item-link ihc-purchased-level"><span class="'.$attr['class'].' " >'  .esc_html__('Purchased', 'ihc'). '</span></div> ';
	} else {
		$url = FALSE;
		$u_type = ihc_get_user_type();
		if ($u_type!='unreg' && $u_type!='pending'){
				// registered user
				if ( ihcCheckCheckoutSetup() ){
						// this contains a js function that will redirect to checkout page
						if (isset($attr['checkout_page'])){
								$url = add_query_arg( 'lid', $attr['id'], $attr['checkout_page'] );
								// since version 12.0
								$url = apply_filters( 'ihc_filter_public_subscription_link', $url, $attr['checkout_page'], $attr['id'] );
						} else {
								$page = get_option('ihc_checkout_page');
								$baseUrl = get_permalink($page);
								$url = add_query_arg( 'lid', $attr['id'], $baseUrl );
								// since version 12.0
								$url = apply_filters( 'ihc_filter_public_subscription_link', $url, $baseUrl, $attr['id'] );
						}
					return '<div onClick="ihcBuyNewLevel(\'' . $url . '\');" class="ihc-level-item-link ' . $attr['item_class'] . ' ihc-cursor-pointer">' . $str . '</div>';
				} else {
					// checkout page is not set
					return '<div class="ihc-level-item-link ' . $attr['item_class'] . ' ihc-cursor-pointer">' . $str . '</div>';
				}
		} else {
				// new user
				if (isset($attr['register_page'])){
						$url = add_query_arg( 'lid', $attr['id'], $attr['register_page'] );
						// since version 12.0
						$url = apply_filters( 'ihc_filter_public_subscription_link', $url, $attr['register_page'], $attr['id'] );
				} else {
						$page = get_option('ihc_general_register_default_page');
						$baseUrl = get_permalink($page);
						$url = add_query_arg( 'lid', $attr['id'], $baseUrl );
						// since version 12.0
						$url = apply_filters( 'ihc_filter_public_subscription_link', $url, $baseUrl, $attr['id'] );
				}

				return '<div onClick="ihcBuyNewLevel(\'' . $url . '\');" class="ihc-level-item-link ' . $attr['item_class'] . ' ihc-cursor-pointer">' . $str . '</div>';
		}
		return $str;
	}
}
endif;

if (!function_exists('ihc_print_user_data')):
function ihc_print_user_data($attr){
	/*
	 * @param array
	 * @return string
	 */
	$str = '';
	if (!empty($attr['field'])){
		global $current_user;
		if (!empty($current_user->ID)){
			$search = "{" . $attr['field'] . "}";
			$return = ihc_replace_constants($search, $current_user->ID);
			if ($search!=$return){
				$str = $return;
			}
		}
	}
	return $str;
}
endif;

if (!function_exists('ihc_public_listing_users')):
function ihc_public_listing_users($input=array()){
	/*
	 * @param array
	 * @return string
	 */
	$input['current_page'] = (empty($_REQUEST['ihcUserList_p'])) ? 1 : sanitize_text_field($_REQUEST['ihcUserList_p']);
	if (!class_exists('ListingUsers')){
		require_once IHC_PATH . 'classes/ListingUsers.class.php';
	}
	$obj = new ListingUsers($input);
	$output = $obj->run();
	return $output;
}
endif;

if (!function_exists('ihc_public_visitor_inside_user_page')):
function ihc_public_visitor_inside_user_page(){
	/*
	 * @param
	 * @return string
	 */
	if (!empty($_GET['ihc_name'])){
		$name = sanitize_text_field($_GET['ihc_name']);
	} else {
		$name = get_query_var('ihc_name');
	}

	if (!empty($name)){
		$name = urldecode($name);
		$uid = ihc_get_user_id_by_user_login($name);
		if ($uid>0){
			$output = '';
			$content = '';
			$css = '';

			$shortcode_attr = ihc_return_meta_arr('listing_users_inside_page');

			///AVATAR
			$data['avatar_url'] = ihc_get_avatar_for_uid($uid);

			///SOCIAL MEDIA ICONS WITH LINKS
			$data['sm_links'] = ihc_return_user_sm_profile_visit($uid);

			///CUSTOM CSS
			if (!empty($shortcode_attr['ihc_listing_users_inside_page_custom_css'])){
				$shortcode_attr['ihc_listing_users_inside_page_custom_css'] = stripslashes($shortcode_attr['ihc_listing_users_inside_page_custom_css']);
				$css = '';
				wp_register_style( 'dummy-handle', false );
				wp_enqueue_style( 'dummy-handle' );
				wp_add_inline_style( 'dummy-handle', $shortcode_attr['ihc_listing_users_inside_page_custom_css'] );
			}

			if ($shortcode_attr['ihc_listing_users_inside_page_type']=='custom'){
				/// getting user data

				/// FLAG
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_flag'])){
					$data['flag'] = ihc_user_get_flag($uid);
				}
				/// AVATAR
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_avatar'])){
					$data['avatar'] = $data['avatar_url'];
				}
				/// SINCE
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_since'])){
					$data['since'] = ihc_convert_date_to_us_format(Ihc_Db::user_get_register_date($uid));
				}
				/// WEBSITE
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_website'])){
					$data['website'] = Ihc_Db::user_get_website($uid);
				}
				/// NAME
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_name'])){
					$first_name = get_user_meta($uid, 'first_name', TRUE);
					$last_name = get_user_meta($uid, 'last_name', TRUE);
					$data['name'] = $first_name . ' ' . $last_name;
				}
				/// USERNAME
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_username'])){
					$data['username'] = $name;
				}
				/// EMAIL
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_email'])){
					$data['email'] = Ihc_Db::user_get_email($uid);
				}
				/// LEVELS
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_level'])){
					$data['levels'] = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid );
				}
				/// CUSTOM FIELDS
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_custom_fields'])){
					$temp_fields = explode(',', $shortcode_attr['ihc_listing_users_inside_page_show_custom_fields']);
					$temp_fields = ihc_order_like_register( $temp_fields );
				 	foreach ($temp_fields as $field){
				 		$label = ihc_get_custom_field_label($field);
						$type = ihc_register_field_get_type_by_slug($field);
						switch($type){
						 case 'date':
						 			$data['custom_fields'][$label] = ihc_convert_date_to_us_format(get_user_meta($uid, $field, TRUE));
						 			break;
						case 'checkbox':
						case 'multi_select':
										$values = get_user_meta($uid, $field, TRUE);
										if(!empty($values)){
											$data['custom_fields'][$label] = '';
											foreach($values as $key => $value){
												$data['custom_fields'][$label] .= $value;
												if($key < count($values)-1){
													$data['custom_fields'][$label] .= ', ';
												}

											}
										}
									break;
						default:
									$data['custom_fields'][$label] = get_user_meta($uid, $field, TRUE);
									if ($type=='text'){
										if (strpos($data['custom_fields'][$label], 'http')!==FALSE || strpos($data['custom_fields'][$label], 'www.')!==FALSE){
											$data['custom_fields'][$label] = '<a href="' . $data['custom_fields'][$label] . '" target="_blank">' . $data['custom_fields'][$label] . '</a>';
										}
									} else if ( $type === 'file' ){
											$data['custom_fields'][$label] = '<a href="' . wp_get_attachment_url( $data['custom_fields'][$label] ) . '" target="_blank">' . esc_html__( 'Download', 'ihc' ) . '</a>';
									}
							break;
						}
				 	}
				}
				/// the content
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_extra_custom_content'])){
					$data['content'] = stripslashes($shortcode_attr['ihc_listing_users_inside_page_extra_custom_content']);
				}
				/// COLOR SCHEME
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_color_scheme'])){
					$data['color_scheme_class'] = $shortcode_attr['ihc_listing_users_inside_page_color_scheme'];
				} else {
					$data['color_scheme_class'] = '';
				}

				if (!empty($shortcode_attr['ihc_listing_users_inside_page_show_banner'])){
					if ( !empty( $shortcode_attr['ihc_listing_users_inside_page_banner_href'] ) ){
							$data['banner'] = $shortcode_attr['ihc_listing_users_inside_page_banner_href'];
					} else {
							$data['banner'] = 'default';
					}
				} else {
					$data['banner'] = '';
				}

				$customBanner = get_user_meta($uid, 'ihc_user_custom_banner_src', true);
				if (!empty($customBanner)){
						$data['banner'] = $customBanner;
				}
				$data['ihc_badges_on'] = get_option( 'ihc_badges_on' );
				$data['ihc_badge_custom_css'] = get_option('ihc_badge_custom_css');

				/// output
				if (!empty($shortcode_attr['ihc_listing_users_inside_page_template'])){
					switch ($shortcode_attr['ihc_listing_users_inside_page_template']){
						case 'template-2':
							$templatePath = IHC_PATH . 'public/views/view_user/template_2.php';
							$templatePath = apply_filters('ihc_filter_on_load_template', $templatePath, 'view_user/template_2.php' );
							ob_start();
							require $templatePath;
							$content = ob_get_contents();
							ob_end_clean();
							break;
						case 'template-1':
						default:
							$templatePath = IHC_PATH . 'public/views/view_user/template_1.php';
							$templatePath = apply_filters('ihc_filter_on_load_template', $templatePath, 'view_user/template_1.php' );
							ob_start();
							require $templatePath;
							$content = ob_get_contents();
							ob_end_clean();
							break;
					}
				}
			} else {
				$data['content'] = get_option('ihc_listing_users_inside_page_content');
				$data['content'] = stripslashes( $data['content'] );
				$content = ihc_replace_constants($data['content'], $uid, FALSE, FALSE, array( '{AVATAR_HREF}' => $data['avatar_url'], '{IHC_SOCIAL_MEDIA_LINKS}' => $data['sm_links'] ));
				$content = '<div class="ihc-public-wrapp-visitor-user">' . $content . '</div>';
			}

			$output = $css . $content;
			return $output;
		}
	}
	return '';
}
endif;

if (!function_exists('ihc_membership_card')):
function ihc_membership_card($attr=array()){
	/*
	 * @param none
	 * @return string
	 */
	 global $current_user;
	 if (empty($current_user->ID)){
	 	return '';
	 }
	 $output = '';
	 $data['metas'] = ihc_return_meta_arr('ihc_membership_card');

	 if (!empty($attr['template'])){
			$data['metas']['ihc_membership_card_template'] = $attr['template'];
		}
	if (isset($attr['size'])){
			$data['metas']['ihc_membership_card_size'] = $attr['size'];
		}
	if (isset($attr['exclude_levels'])){
			$data['metas']['ihc_membership_card_exclude_levels'] = $attr['exclude_levels'];
		}
	if (isset($attr['ihc_membership_member_show_extrafields'])){
			$data['metas']['ihc_membership_member_show_extrafields'] = $attr['ihc_membership_member_show_extrafields'];
		}

	 if ($data['metas']['ihc_membership_card_enable']){
	 	 $data['levels'] = \Indeed\Ihc\UserSubscriptions::getAllForUser( $current_user->ID, true );
		 $exclude_levels = explode(',', (isset($data['metas']['ihc_membership_card_exclude_levels'])) ? $data['metas']['ihc_membership_card_exclude_levels'] : '');
		 $data['full_name'] = '';
		 $user_data = get_userdata($current_user->ID);
		 if (!empty($user_data->first_name) && !empty($user_data->last_name)){
			 $data['full_name'] = $user_data->first_name . ' ' . $user_data->last_name;
		 }
		 if (!empty($user_data->data) && !empty($user_data->data->user_registered)){
		 	$data['member_since'] = ihc_convert_date_to_us_format($user_data->data->user_registered);
		 }

		 /// CUSTOM FIELDS

		 if (!empty($data['metas']['ihc_membership_member_show_extrafields'])){
			 $temp_fields = $data['metas']['ihc_membership_member_show_extrafields'];
			 $temp_fields = ihc_order_like_register( $temp_fields );
			 foreach ($temp_fields as $field){
				 $label = ihc_get_custom_field_label($field);
				 $type = ihc_register_field_get_type_by_slug($field);
				 switch($type){
					case 'date':
							 $data['custom_fields'][$label] = ihc_convert_date_to_us_format(get_user_meta($current_user->ID, $field, TRUE));
							 break;
				 case 'checkbox':
				 case 'multi_select':
								 $values = get_user_meta($current_user->ID, $field, TRUE);
								 if(!empty($values)){
									 $data['custom_fields'][$label] = '';
									 foreach($values as $key => $value){
										 $data['custom_fields'][$label] .= $value;
										 if($key < count($values)-1){
											 $data['custom_fields'][$label] .= ', ';
										 }
									 }
								 }
							 break;

				 default:
							 $data['custom_fields'][$label] = get_user_meta($current_user->ID, $field, TRUE);
							 if ($field == 'user_login'){
								 $data['custom_fields'][$label] = $user_data->user_login;
							 }
							 if ($field == 'user_email'){
								 $data['custom_fields'][$label] = $user_data->user_email;
							 }
					 break;
				 }
			 }
		 }
		 if(isset($data['custom_fields']) && is_array($data['custom_fields'])){
				 foreach ($data['custom_fields'] as $key => $value) {
				 	if (empty($data['custom_fields'][$key])){
						unset($data['custom_fields'][$key]);
					}
				 }
	 	}

		 if (!empty($data['levels'])){

			$fullPath = IHC_PATH . 'public/views/membership_card.php';
			$searchFilename = 'membership_card.php';
			$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

			wp_enqueue_script( 'ihc-print-this' );

			if (!empty($data['metas']['ihc_membership_card_custom_css'])){
				wp_register_style( 'dummy-handle', false );
				wp_enqueue_style( 'dummy-handle' );
				wp_add_inline_style( 'dummy-handle', stripslashes($data['metas']['ihc_membership_card_custom_css']) );
			}

		 	foreach ($data['levels'] as $lid => $level_data){
		 		if (in_array($lid, $exclude_levels)){
		 			continue;
		 		}
		 		ob_start();
				include $template;
				$output .= ob_get_contents();
				ob_end_clean();
		 	}
		 }else{
			$output = '<div class="ihc-additional-message">'. esc_html__("No Membership Cards available based on your Subscriptions. SignUp on new Subscriptions or renew the existent one.", 'ihc').'</div>';
		 }
	 }
	 return $output;
}
endif;

if (!function_exists('ihc_link_to_individual_page')):
function ihc_link_to_individual_page(){
	/*
	 * @param none
	 * @return string
	 */
	 $output = '';
	 global $current_user;
	 if (!empty($current_user->ID)){
	 	 $individual_page = get_user_meta($current_user->ID, 'ihc_individual_page', TRUE);
		 if ($individual_page){
		 	 $permalink = get_permalink($individual_page);
			 if ($permalink){
			 	$output = '<a href="' . $permalink . '" class="ihc-individual-page-link">' . esc_html__('Individual Page', 'ihc') . '</a>';
			 }
		 }
	 }
	 return $output;
}
endif;

if (!function_exists('ihc_do_list_gifts')):
function ihc_do_list_gifts(){
	/*
	 * @param none
	 * @retunr string
	 */
	$output = '';
	global $current_user;
	if (!empty($current_user) && !empty($current_user->ID)){
		$gifts = Ihc_Db::get_gifts_by_uid($current_user->ID);
		$levels = \Indeed\Ihc\Db\Memberships::getAll();
		$levels[-1]['label'] = esc_html__('All', 'ihc');
		$currency = get_option('ihc_currency');
		if ($gifts){
			$fullPath = IHC_PATH . 'public/views/listing_gifts.php';
			$searchFilename = 'listing_gifts.php';
			$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

			ob_start();
			include $template;
			$output .= ob_get_contents();
			ob_end_clean();
		}else{
			$output = '<div class="ihc-additional-message">'. esc_html__("No Membership Gifts have been received yet", 'ihc').'</div>';
		}
	}
	return $output;
}
endif;

if (!function_exists('ihc_list_all_access_posts')):
function ihc_list_all_access_posts($attr=array()){
	/*
	 * @param array
	 * @return string
	 */
	global $current_user;
	$uid = (empty($current_user->ID)) ? 0 : $current_user->ID;
	if ($uid && ihc_is_magic_feat_active('list_access_posts')){
		 require_once IHC_PATH . 'classes/ListOfAccessPosts.class.php';
		 $levels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, true );
		 $levels = array_keys($levels);
		 $metas = ihc_return_meta_arr('list_access_posts');
		 if (!empty($attr['limit'])){
		 	$metas['ihc_list_access_posts_order_limit'] = $attr['limit'];
		 }
		 if (!empty($attr['template'])){
		 	$metas['ihc_list_access_posts_template'] = $attr['template'];
		 }
		 if (!empty($attr['order_by'])){
		 	$metas['ihc_list_access_posts_order_by'] = $attr['order_by'];
		 }
		 if (!empty($attr['order'])){
		 	$metas['ihc_list_access_posts_order_type'] = $attr['order'];
		 }
		 if (!empty($attr['post_types'])){
		 	$metas['ihc_list_access_posts_order_post_type'] = $attr['post_types'];
		 }
		 if (!empty($attr['levels_in'])){
		 	$metas['ihc_list_access_posts_order_exclude_levels'] = $attr['levels_in'];
		 }
		 if (!empty($metas['ihc_list_access_posts_order_exclude_levels'])){
			 $exclude = explode(',', $metas['ihc_list_access_posts_order_exclude_levels']);
			 if ($exclude){
				 $levels = array_diff($levels, $exclude);
			 }
		 }
		 if ($levels){
			 $object = new ListOfAccessPosts($levels, $metas);
			 return $object->output();
		 }
	}
	return '';
}
endif;

if (!function_exists('ihc_list_user_levels')):
function ihc_list_user_levels($attr=array()){
	/*
	 * @param array.
	 * Available Shortcode params:
	 * 		- exclude_expire (display expired levels - TRUE || FALSE),, default FALSE
	 * 		- badges (display levels as badges - TRUE || FALSE), default FALSE
	 * @return string
	 */
	 $output = '';
	 global $current_user;
	 if ($current_user){
	 	$uid = isset($current_user->ID) ? $current_user->ID : 0;
	 	if ($uid){
	 		$data['custom_css'] = '';
			if (empty($attr['exclude_expire'])){
				$attr['exclude_expire'] = FALSE;
			}
			if (empty($attr['badges'])){
				$attr['badges'] = FALSE;
			} else {
				$data['badges_metas'] = ihc_return_meta_arr('badges');
				if (empty($data['badges_metas']['ihc_badges_on'])){
					$data['badges'] = FALSE;
				} else if (!empty($data['badges_metas']['ihc_badge_custom_css'])){
					$data['custom_css'] = $data['badges_metas']['ihc_badge_custom_css'];
				}
			}
	 		$data['levels'] = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, $attr['exclude_expire'] );

			$fullPath = IHC_PATH . 'public/views/listing_levels.php';
			$searchFilename = 'listing_levels.php';
			$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

	 		ob_start();
			include $template;
			$output .= ob_get_contents();
			ob_end_clean();
	 	}
	 }
     return $output;
}
endif;

if ( !function_exists('ihc_register_form_for_modal') ):
function ihc_register_form_for_modal($attr=array()){
		/*
		 * @param array
		 * @return string
		 */
		$str = '';

		$user_type = ihc_get_user_type();
		if ($user_type=='unreg'){
			///////ONLY UNREGISTERED CAN SEE THE REGISTER FORM

			if (isset($_GET['ihc_register'])){
				 return;
			}
				/// TEMPLATE
				if (!empty($attr['template'])){
					$template = $attr['template'];
				} else {
					$template = get_option('ihc_register_template');
				}

				// since version 10.3 - new workflow
				$extraParams = '';
				// Template
				$extraParams .= " template='$template'";
				/// Double E-mail Verification
				$extraParams .= (isset($attr['double_email'])) ? " double_email='{$attr['double_email']}'" : '';
				/// Rule
				$extraParams .= (isset($attr['role'])) ? " role='{$attr['role']}'" : '';
				/// Autologin
				$extraParams .= (isset($attr['autologin'])) ? " autologin='{$attr['autologin']}'" : '';
				/// Predefined Membership
				$extraParams .= (isset($attr['level'])) ? " level='{$attr['level']}'" : '';

				$str .= do_shortcode( "[ihc-register is_modal=1 $extraParams ]" );
				// end of new workflow ( 10.3 )


		} else {
			//already logged in
			if ($user_type=='admin'){
				$str .= '<div class="ihc-warning-message"><strong>' . esc_html__('Administrator Info', 'ihc') . '</strong>' . esc_html__(': Register Form is not showing up once you\'re logged. You may check how it it looks for testing purpose by opening the page into a separate incognito browser window. ', 'ihc') . '<i>' . esc_html__(' This message will not be visible for other users ', 'ihc') . '</i></div>';
			}
		}
		return $str;
}
endif;

if (!function_exists('ihc_suspend_account_bttn')):
function ihc_suspend_account_bttn($attr=array()){
	/*
	 * @param array
	 * @return string
	 */
	 global $current_user;
	 $output = '';
	 if (!empty($current_user->ID)){
		 $fullPath = IHC_PATH . 'public/views/suspend_account.php';
		 $searchFilename = 'suspend_account.php';
		 $template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

	 	ob_start();
		include $template;
		$output .= ob_get_contents();
		ob_end_clean();
	 }
	 return $output;
}
endif;

if ( !function_exists('ihc_login_popup') ):
function ihc_login_popup( $attr=array(), $content='' )
{
		// don't show on register page
		global $post, $current_user;
		$isLoginPage = false;
		$loginPostId = get_option('ihc_general_login_default_page');
		if ( isset( $post->ID ) && $loginPostId == $post->ID ){
				$isLoginPage = true;
		}
		if ( !$isLoginPage && isset( $post->ID ) ){
				$pageContent = get_post_field('post_content', $post->ID );
				if ( strpos( $pageContent, '[ihc-login-form]' ) !== false ){
						$isLoginPage = true;
				}
		}

		$fullPath = IHC_PATH . 'public/views/login_popup.php';
		$searchFilename = 'login_popup.php';
		$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );
		$data = array(
				'content'							=> $content,
				'trigger'							=> empty($attr['trigger']) ? false : $attr['trigger'],
				'isLoginPage'					=> $isLoginPage,
				'uid'								  => isset( $current_user->ID ) ? $current_user->ID : 0,
		);

		$view = new \Indeed\Ihc\IndeedView();
		return $view->setTemplate( $template )->setContentData( $data, true )->getOutput();
}
endif;

if ( !function_exists('ihc_register_popup') ):
function ihc_register_popup( $attr=array(), $content='' )
{
		// don't show on register page
		global $post, $current_user;
		$isRegisterPage = false;
		$registerPostId = get_option('ihc_general_register_default_page');
		if ( isset( $post->ID ) && $registerPostId == $post->ID ){
				$isRegisterPage = true;
		}
		if ( !$isRegisterPage && isset( $post->ID ) ){
				$pageContent = get_post_field('post_content', $post->ID );
				if ( strpos( $pageContent, '[ihc-register]' ) !== false || strpos( $pageContent, '[ihc-register-lite]' ) !== false ){
						$isRegisterPage = true;
				}
		}

		$fullPath = IHC_PATH . 'public/views/register_popup.php';
		$searchFilename = 'register_popup.php';
		$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );
		$data = array(
				'content'							=> $content,
				'trigger'							=> empty($attr['trigger']) ? false : $attr['trigger'],
				'isRegisterPage'			=> $isRegisterPage,
				'uid'									=> isset( $current_user->ID ) ? $current_user->ID : 0,
		);

		$view = new \Indeed\Ihc\IndeedView();
		return $view->setTemplate( $template )->setContentData( $data, true )->getOutput();
}
endif;

if (!function_exists('ihc_thank_you_page_shortcode')):
function ihc_thank_you_page_shortcode($attr=array()){
	/*
	 * @param array
	 * @return string
	 */
	 global $stop_printing_bt_msg;
	 $meta_arr = ihc_return_meta_arr('thank-you-page-settings');
	$str = '';

	if( isset( $_COOKIE['ihc_payment']) && $_COOKIE['ihc_payment'] !== '' ){
		wp_enqueue_script( 'ihc-cookie', IHC_URL . 'assets/js/cookies.js', [ 'jquery' ], '12.8' );
		$paymentKey = sanitize_text_field($_COOKIE['ihc_payment']);

		$orderMetaObject = new \Indeed\Ihc\Db\OrderMeta();
		$orderID = $orderMetaObject->getIdFromMetaNameMetaValue( 'key', $paymentKey );
		$orderID = (int)$orderID;
		if(!empty($orderID)){
			$orderObject = new \Indeed\Ihc\Db\Orders();
			$orderData = $orderObject->setId( $orderID )
			                         ->fetch()
			                         ->get();
			$orderMeta =	$orderMetaObject->getAllByOrderId( $orderID );

			$payment_gateways = ihc_list_all_payments();
			$payment_gateways['woocommerce'] = esc_html__( 'WooCommerce', 'ihc' );

			$outputData = [
					'customer_id'						=> isset( $orderData->uid ) ? $orderData->uid : 0,
					'customer_email'				=> isset( $orderMeta['customer_email'] ) ? $orderMeta['customer_email'] : '',
					'customer_name'					=> isset( $orderMeta['customer_name'] ) ? $orderMeta['customer_name'] : '',

					'membership_id'					=> isset( $orderData->lid ) ? $orderData->lid : 0,
					'membership_name'				=> isset( $orderMeta['level_label'] ) ? $orderMeta['level_label'] : '',

					'amount'								=> isset( $orderData->amount_value ) ? $orderData->amount_value : 0,
					'currency'							=> isset( $orderData->amount_type ) ? $orderData->amount_type : '',

					'order_code'						=> isset( $orderMeta['code'] ) ? $orderMeta['code'] : 0,
					'order_date'						=> isset( $orderData->create_date ) ? ihc_convert_date_time_to_us_format($orderData->create_date) : '',

					'order_payment_method'	=> isset( $orderMeta['ihc_payment_type'] ) ? $orderMeta['ihc_payment_type'] : '-'

			];

			if(isset($outputData['order_payment_method']) && $outputData['order_payment_method'] != '-' && isset($payment_gateways) && is_array($payment_gateways) && count($payment_gateways) > 0){
				$outputData['order_payment_method'] = isset( $payment_gateways[$outputData['order_payment_method']] ) ? $payment_gateways[$outputData['order_payment_method']] : '-';
			}

			$meta_arr['ihc_thank_you_message'] = ihc_format_str_like_wp($meta_arr['ihc_thank_you_message']);
			$meta_arr['ihc_thank_you_message'] = htmlspecialchars_decode($meta_arr['ihc_thank_you_message']);
			$meta_arr['ihc_thank_you_message'] = stripslashes($meta_arr['ihc_thank_you_message']);

			$str = ihc_replace_constants($meta_arr['ihc_thank_you_message'], $outputData['customer_id'], $outputData['membership_id'] , $outputData['membership_id'], $outputData );

		}else{
			$str = $meta_arr['ihc_thank_you_error_message'];
		}


	}else{
		$str = $meta_arr['ihc_thank_you_error_message'];
	}
	return $str;
}
endif;
