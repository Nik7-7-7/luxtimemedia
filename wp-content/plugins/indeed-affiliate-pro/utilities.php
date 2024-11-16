<?php
function uap_reorder_arr($arr){
	/*
	 * @param array
	 * @return array
	 */
	if (isset($arr) && count($arr)>0 && $arr !== false){
		$new_arr = false;
		foreach ($arr as $k=>$v){
			$order = $v['order'];
			$new_arr[$order][$k] = $v;
		}
		if ($new_arr && count($new_arr)){
			ksort($new_arr);
			foreach ($new_arr as $k=>$v){
				$return_arr[key($v)] = $v[key($v)];
			}
			return $return_arr;
		}
	}
	return $arr;
}

if ( !function_exists( 'uapGeneralPrefix' ) ):
function uapGeneralPrefix()
{
    return "\Indeed\Uap\\";
}
endif;

function uap_reorder_ranks($ranks_arr=array()){
	/*
	 * reorder ranks by order attr
	 * @param array
	 * @return array
	 */
	if ($ranks_arr){
		foreach ($ranks_arr as $k=>$v){
			if (isset($v->rank_order)){
				$key = $v->rank_order;
				while (!empty($new_arr[$key])){
					$key++;
				}
				$new_arr[$key] = $v;
			}
		}
		if (!empty($new_arr)){
			ksort($new_arr);
			return $new_arr;
		}
	}
	return $ranks_arr;
}

/**
 * @param arrray
 * @param int
 * @param int
 * @return array
 */
function uap_custom_reorder_rank( $ranks_arr, $id, $new_order )
{
		$ranks_arr = uap_reorder_ranks( $ranks_arr );
		foreach ( $ranks_arr as $key => $rankArray ){
				if ( (int)$ranks_arr[$key]->id === (int)$id ){
						$ranks_arr[$key]->rank_order = $new_order;
				}
		}
		$current_order = 1;
		foreach ( $ranks_arr as $key => $rankArray ){
				if ( (int)$current_order === (int)$new_order ){
						$current_order++;
				}
				if ( (int)$ranks_arr[$key]->id !== (int)$id ){
						$ranks_arr[$key]->rank_order = $current_order;
						$current_order++;
				}
		}
		return $ranks_arr;
		/*
		foreach ($ranks_arr as $k=>$v){
			if ($v->rank_order==$new_order){
				$swap_key_1 = $k;
				break;
			}
		}
		if (isset($swap_key_1) && isset($ranks_arr[$swap_key_1]->id) && $ranks_arr[$swap_key_1]->id!=$id){
			/// if array must be reorder
			foreach ($ranks_arr as $k=>$v){
				if ($v->id==$id){
					$swap_key_2 = $k;
					$old_order = $v->rank_order;
				}
			}
			if (isset($swap_key_2) && isset($swap_key_1) && isset($old_order)){
				$ranks_arr[$swap_key_1]->rank_order = $old_order;
				$ranks_arr[$swap_key_2]->rank_order = $new_order;
			}
		}
		return $ranks_arr;
		*/
}

if ( !function_exists( 'uap_correct_text' ) ):
/**
 * @param string
 * @param bool
 * @param bool
 * @return string
 */
function uap_correct_text($str = '', $wp_editor_content=false, $escAttr=false )
{
	$str = stripcslashes(htmlspecialchars_decode($str));
	if ( $escAttr ){
			$str = esc_attr( $str );
	}
	if ($wp_editor_content){
		return uap_format_str_like_wp($str);
	}
	return $str;
}
endif;

function uap_format_str_like_wp( $str ){
	/*
	 * @param string
	 * @return string
	 */
	$str = wpautop( $str );
	return $str;
}

function uap_get_wp_roles_list(){
	/*
	 * @param none
	 * @return array with all wp roles available without administrator
	 */
	global $wp_roles;
	$roles = $wp_roles->get_names();
	if (!empty($roles)){
		unset($roles['administrator']);// remove admin role from our list
		return $roles;
	}
	return FALSE;
}

function uap_create_form_element($attr=array()){
	/*
	 * @param string
	 * @return string
	 */
	foreach (array('name', 'id', 'value', 'class', 'other_args', 'disabled', 'placeholder', 'multiple_values', 'user_id', 'sublabel') as $k){
		if (!isset($attr[$k])){
			$attr[$k] = '';
		}
	}
	if (empty($attr['id'])){
			$attr['id'] = 'uap_' . $attr['name'] . '_field';
	}

	$str = '';
	if (isset($attr['type']) && $attr['type']){
		switch ($attr['type']){
			case 'text':
			case 'conditional_text':
				$str = '<input type="text" name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-text '.$attr['class'].'" value="' . uap_correct_text( $attr['value'], false, true ) . '" placeholder="'.$attr['placeholder'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'number':
				$str = '<input type="number" name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-number '.$attr['class'].'" value="'.$attr['value'].'"  '.$attr['other_args'].' '.$attr['disabled'].' />';
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'textarea':
				$str = '<textarea name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-textarea uap-form-textarea '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' >' . uap_correct_text( $attr['value'], false, true ) . '</textarea>';
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'password':
				global $wp_version;
				wp_register_script('uap_passwordStrength', UAP_URL . 'assets/js/passwordStrength.js', ['jquery'], 8.3  );
				if ( version_compare ( $wp_version , '5.7', '>=' ) ){
						wp_localize_script( 'uap_passwordStrength', 'uapPasswordStrengthLabels', array(__('Very Weak', 'uap'), esc_html__('Weak', 'uap'), esc_html__('Good', 'uap'), esc_html__('Strong', 'uap')) );
				} else {
						wp_localize_script( 'uap_passwordStrength', 'uapPasswordStrengthLabels', json_encode( array(__('Very Weak', 'uap'), esc_html__('Weak', 'uap'), esc_html__('Good', 'uap'), esc_html__('Strong', 'uap')) ) );
				}
				wp_enqueue_script('uap_passwordStrength');

				$ruleOne = (int)get_option('uap_register_pass_min_length');
				$ruleTwo = (int)get_option('uap_register_pass_options');

				$str = '<input type="password" name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-password '.$attr['class'].'" value="'.$attr['value'].'" placeholder="'.$attr['placeholder'].'" '.$attr['other_args'].' data-rules="' . $ruleOne . ',' . $ruleTwo . '"  autocomplete="new-password" />';
				$str .= '<div class="uap-strength-wrapper">';
				$str .= '<ul class="uap-strength"><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li><li class="point"></li></ul>';
				$str .= '<div class="uap-strength-label"></div>';
				$str .= '</div>';
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'hidden':
				$str = '<input type="hidden" name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-hidden '.$attr['class'].'" value="'.$attr['value'].'" '.$attr['other_args'].' />';
				break;

			case 'checkbox':
				$str = '';
				if ($attr['multiple_values']){
					$id = 'uap_checkbox_parent_' . rand(1,1000);
					$str .= '<div class="uap-form-checkbox-wrapper" id="' . $id . '">';
					foreach ($attr['multiple_values'] as $v){
						if (is_array($attr['value'])){
							$checked = (in_array($v, $attr['value'])) ? 'checked' : '';
						} else {
							$checked = ($v==$attr['value']) ? 'checked' : '';
						}
						$str .= '<div class="uap-form-checkbox">';
						$str .= '<input type="checkbox" name="'.$attr['name'].'[]" id="'.$attr['id'].'" class="uap-form-element uap-form-element-checkbox '.$attr['class'].'" value="' . uap_correct_text( $v, false, true ) . '" '.$checked.' '.$attr['other_args'].' '.$attr['disabled'].'  />';
						$str .= uap_correct_text($v);
						$str .= '</div>';
					}
					$str .= '</div>';
				}
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'single_checkbox':
				$str = "";
				$str .= '<div class="uap-single-checkbox-wrap" id="' . $attr['id'] . '">'
								. '<input type="checkbox" value="1" name="' . $attr['name'] . '" class="uap-form-element uap-form-element-checkbox ' . $attr['class'] . '" />';
				if (!empty($attr['sublabel'])){
						$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				$str .= '</div>';
				break;

			case 'radio':
				$str = '';
				if ($attr['multiple_values']){
					$id = 'uap_radio_parent_' . rand(1,1000);
					$str .= '<div class="uap-form-radiobox-wrapper" id="' . $id . '">';
					foreach ($attr['multiple_values'] as $v){
						$checked = ($v==$attr['value']) ? 'checked' : '';
						$str .= '<div class="uap-form-radiobox">';
						$str .= '<input type="radio" name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-radio '.$attr['class'].'" value="' . uap_correct_text( $v, false, true ) . '" '.$checked.' '.$attr['other_args'].' '.$attr['disabled'].'  />';
						$str .= uap_correct_text($v);
						$str .= '</div>';
					}
					$str .= '</div>';
				}
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'select':
				$str = '';
				if ($attr['multiple_values']){
					$str .= '<select name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-select uap-form-select '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' >';
					if ($attr['multiple_values']){
						foreach ($attr['multiple_values'] as $k=>$v){
							$selected = ($k==$attr['value']) ? 'selected' : '';
							$str .= '<option value="'.$k.'" '.$selected.'>' . uap_correct_text( $v, false, true ) . '</option>';
						}
					}
					$str .= '</select>';
				}
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'multi_select':
				$str = '';
				if ($attr['multiple_values']){
					$str .= '<select name="'.$attr['name'].'[]" id="'.$attr['id'].'" class="uap-form-element uap-form-element-multiselect uap-form-multiselect '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' multiple>';
					foreach ($attr['multiple_values'] as $k=>$v){
						if (is_array($attr['value'])){
							$selected = (in_array($v, $attr['value'])) ? 'selected' : '';
						} else {
							$selected = ($v==$attr['value']) ? 'selected' : '';
						}
						$str .= '<option value="'.$k.'" '.$selected.'>' . uap_correct_text( $v, false, true ) . '</option>';
					}
					$str .= '</select>';
				}
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'submit':
				$str = '<input type="submit" value="' . uap_correct_text( $attr['value'], false, true ) . '" name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-submit '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'date':
				wp_enqueue_script('jquery-ui-datepicker');
				if (empty($attr['class'])){
					$attr['class'] = 'uap-date-field';
				}
				$str = '';

				global $uap_jquery_ui_min_css;
				if (empty($uap_jquery_ui_min_css)){
					$uap_jquery_ui_min_css = TRUE;
					$str .= '<link rel="stylesheet" type="text/css" href="' . UAP_URL . '/assets/css/jquery-ui.min.css"/>' ;
				}

				$str .= '
								<span class="uap-js-date-picker-data" data-selector=".'.$attr['class'].'"></span>
								';
				$str .= '<input type="text" value="'.$attr['value'].'" name="'.$attr['name'].'" id="'.$attr['id'].'" class="uap-form-element uap-form-element-date uap-form-datepicker '.$attr['class'].'" '.$attr['other_args'].' '.$attr['disabled'].' />';
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'file':
				wp_enqueue_script( 'uap-jquery_form_module' );
				wp_enqueue_script( 'uap-jquery.uploadfile' );

				global $indeed_db;

				//$ajaxURL = UAP_URL . 'public/ajax-upload.php?publicn=' . wp_create_nonce( 'publicn' );
				$ajaxURL = get_site_url() . '/wp-admin/admin-ajax.php?action=uap_ajax_public_upload&publicn=' . wp_create_nonce( 'publicn' );

				$upload_settings = $indeed_db->return_settings_from_wp_option('general-uploads');
				$max_size = $upload_settings['uap_upload_max_size'] * 1000000;
				$rand = rand(1,10000);
				$str .= '<div id="uap_fileuploader_wrapp_' . $rand . '" class="uap-wrapp-file-upload uap-wrapp-file-upload-align">';
				$str .= '<div class="uap-file-upload uap-file-upload-button">' . esc_html__( 'Upload', 'uap') . '</div>
				<span class="uap-js-upload-file-data"
				data-rand="' . $rand . '"
				data-url="' . $ajaxURL . '"
				data-max_size="' . $max_size . '"
				data-alowed_types="' . $upload_settings['uap_upload_extensions'] . '"
				data-name="' . $attr['name'] . '"
				data-alert_text="' . esc_html__( 'To add a new file please remove the previous one!', 'uap' ) . '"></span>
';
				if ($attr['value']){
					$attachment_type = uap_get_attachment_details($attr['value'], 'extension');
					$url = wp_get_attachment_url($attr['value']);
					switch ($attachment_type){
						case 'jpg':
						case 'jpeg':
						case 'png':
						case 'gif':
							//print the picture
							$str .= '<img src="' . $url . '" class="uap-member-photo" /><div class="uap-clear"></div>';
							break;
						default:
							//default file type
							$str .= '<div class="uap-icon-file-type"></div>';
							break;
					}
					$attachment_name = uap_get_attachment_details($attr['value']);
					$str .= '<div class="uap-file-name-uploaded"><a href="' . $url . '" target="_blank">' . $attachment_name . '</a></div>';
					$str .= '<div onClick="uapDeleteFileViaAjax(\'' . $attr['value'] . '\', ' . $attr['user_id'] . ', \'#uap_fileuploader_wrapp_' . $rand . '\', \'' . $attr['name'] . '\', \'#uap_upload_hidden_' . $rand . '\' );" class="uap-delete-attachment-bttn">Remove</div>';
				}
				$str .= '<input type="hidden" value="'.$attr['value'].'" name="' . $attr['name'] . '" id="uap_upload_hidden_'.$rand.'" />';
				$str .= "</div>";
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;


			case 'upload_image':
				global $indeed_db;
				$data = $attr;
				$data['rand'] = rand(1, 10000);
				$data['imageClass'] = 'uap-member-photo';
				if (empty($data['user_id'])){
						$data['user_id'] = -1;
				}
				$data['imageUrl'] = '';

				if ( !empty($data['value']) ){
						if (strpos($data['value'], "http")===0){
								$data['imageUrl'] = $data['value'];
						} else {
								$tempData = $indeed_db->getMediaBaseImage($data['value']);
								if (!empty($tempData)){
									$data['imageUrl'] = $tempData;
								}
						}
				}
				$viewObject = new \Indeed\Uap\IndeedView();
				$str = $viewObject->setTemplate( UAP_PATH . 'public/views/upload_image.php')->setContentData( $data )->getOutput();

				break;

			case 'plain_text':
				$str = uap_correct_text($attr['value'], false, false );
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				break;

			case 'uap_country':
				wp_enqueue_style( 'uap_select2_style' );
				wp_enqueue_script( 'uap-select2' );

				if (empty($attr['id'])){
					$attr['id'] = $attr['name'] . '_field';
				}
				$countries = uap_get_countries();

				$default_country = getDefaultCountry();
				if(empty($attr['value'])){
					 $attr['value'] = $default_country;
				}

				$str .= '<select name="' . $attr['name'] . '" id="' . $attr['id'] . '" class="uap-form-element uap-form-element-select">';
				foreach ($countries as $k=>$v):
					$k = strtolower($k);
					$selected = ($attr['value']==$k) ? 'selected' : '';
					$str .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
				endforeach;
				$str .= '</select>';
				if (!empty($attr['sublabel'])){
					$str .= '<label class="uap-form-sublabel">' . uap_correct_text($attr['sublabel']) . '</label>';
				}
				$str .= '<ul id="uap_countries_list_ul" class="uap-display-none">';

				$str .= '</ul>';

				$str .= '<span class="uap-js-select2-data" data-label="' . esc_html__( 'Select Your Country', 'uap' ) . '" data-selector="#' . $attr['id'] . '" ></span>';
				break;
			case 'uap_affiliate_autocomplete_field':
				ob_start();
				include UAP_PATH . 'admin/views/search-user_field_autocomplete.php';
				$str = ob_get_contents();
				ob_end_clean();
				break;
		}
	}
	return $str;

}

function uap_array_value_exists($haystack, $needle, $key){
	/*
	 * @param array, string, string
	 * @return string|int, bool
	 */
	if (is_array($haystack)){
		foreach ($haystack as $k=>$v){
			if ($v[$key]==$needle){
				return $k;
			}
		}
	}
	return FALSE;
}

function uap_value_exists_in_another_subarray($haystack=array(), $needle='', $key='', $id=0){
	/*
	 * @param array, string, string, int
	 * @return boolean
	 */
	foreach ($haystack as $k=>$v){
		if ($v[$key]==$needle && $k!=$id){
			return TRUE;
		}
	}
	return FALSE;
}


function uap_send_user_notifications($u_id=0, $notification_type='', $rank=0, $dynamic_data=array() ){
	/*
	 * main function for notification module
	 * send e-mail to user
	 * int, string, int, array
	 * @return TRUE if mail was sent, FALSE otherwise
	 */
	$sent = FALSE;

	if ($u_id && $notification_type){
		global $indeed_db;
		$send_to_admin = FALSE;
		if (empty($rank)){
			$rank = $indeed_db->get_affiliate_rank(0, $u_id);
		}
		$domain = 'uap';
		$languageCode = get_user_meta( $u_id, 'uap_locale_code', true );
		if ($rank && $rank>-1){
			$data = $indeed_db->get_notification_for_rank($rank, $notification_type);
			if ($data){
					$subject = (empty($data['subject'])) ? '' : $data['subject'];
					$message = (empty($data['message'])) ? '' : $data['message'];
					$wmplName = $notification_type . '_subject_' . $rank;
					$subject = apply_filters( 'wpml_translate_single_string', $subject, $domain, $wmplName, $languageCode );
					$wmplName = $notification_type . '_message_' . $rank;
					$message = apply_filters( 'wpml_translate_single_string', $message, $domain, $wmplName, $languageCode );
			}
		}
		if (empty($data) || $rank==-1 || !$rank){
			$data = $indeed_db->get_notification_for_rank(-1, $notification_type);
			if ($data){
					$subject = (empty($data['subject'])) ? '' : $data['subject'];
					$message = (empty($data['message'])) ? '' : $data['message'];
					$wmplName = $notification_type . '_subject_-1';
					$subject = apply_filters( 'wpml_translate_single_string', $subject, $domain, $wmplName, $languageCode );
					$wmplName = $notification_type . '_message_-1';
					$message = apply_filters( 'wpml_translate_single_string', $message, $domain, $wmplName, $languageCode );
			}
		}
		if (!empty($data)){
			$from_name = get_option('uap_notification_name');
			if (empty($from_name)){
				$from_name = get_option("blogname");
			}
			//user data
			$u_data = get_userdata($u_id);
			$user_email = isset( $u_data->data->user_email ) ? $u_data->data->user_email : '';
			//from email

			$from_email = get_option('uap_notification_email_from');
			if (empty($from_email)){
				$from_email = get_option('admin_email');
			}
			$message = uap_replace_constants($message, $u_id, $dynamic_data);
			$subject = uap_replace_constants($subject, $u_id, $dynamic_data);

			$message = stripslashes(htmlspecialchars_decode(uap_format_str_like_wp($message)));
			$message = apply_filters('uap_notification_filter', $message, $u_id, $notification_type);
			$message = "<html><head></head><body>" . $message . "</body></html>";
			if ($subject && $message && $user_email){
				if ($notification_type=='admin_user_register' || $notification_type=='admin_on_aff_change_rank' || $notification_type=='admin_affiliate_update_profile'){
					/////// ADMIN NOTIFICATION
					$user_email = get_option( 'uap_admin_notification_address', false );// >= 8.5
					if (empty($user_email)){
						$user_email = get_option('admin_email');//we change the destination
					}

					$send_to_admin = TRUE;
				}
				if (!empty($from_email) && !empty($from_name)){
					$headers[] = "From: $from_name <$from_email>";
				}
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				$sent = wp_mail($user_email, $subject, $message, $headers);

				if ( $sent ){
						// notification log ( v. >= 8.5 )
						$logContent = $message;
						if ( $notification_type === 'reset_password' ){
								$logContent = 'Reset Password - Step 2: Send Generated Password. The rest of message its hidden from notification log because its contain information only for user';
						} else if ( $notification_type === 'reset_password_process' ){
								$logContent = 'Reset Password - Step 1: Confirmation Request. The rest of message its hidden from notification log because its contain information only for user';
						}
						$log = [
						  'notification_type'       => $notification_type,
						  'email_address'           => $user_email,
							'subject'									=> $subject,
						  'message'                 => $message,
						  'uid'                     => $u_id,
						  'affiliate_id'            => $indeed_db->get_affiliate_id_by_wpuid($u_id),
						  'rank_id'                 => $rank,
						];
						\Indeed\Uap\Db\NotificationLogs::save( $log );
						// notification log ( v. >= 8.5 )
				}

			}
		}

		/// PUSHOVER
		if ($indeed_db->is_magic_feat_enable('pushover')){
			require_once UAP_PATH . 'classes/PushoverNotifications.class.php';
			$pushover_object = new PushoverNotifications();
			$pushover_object->send_notification($u_id, $rank, $notification_type, $send_to_admin);
		}
		/// PUSHOVER
	}
	return $sent;
}

if ( !function_exists( 'uapSendTestNotification' ) ):
function uapSendTestNotification( $notificationId=0, $email="" )
{
		global $wpdb;
		if ( $notificationId === 0 || $email === '' ){
				return;
		}
		$query = $wpdb->prepare( "SELECT id,type,rank_id,subject,message,pushover_message,pushover_status,status FROM {$wpdb->prefix}uap_notifications
								WHERE 1=1
								AND id=%s;", $notificationId );
		$data = $wpdb->get_row( $query );

		if ( $data ){
			$subject = (isset($data->subject)) ? $data->subject : '';
			$message = (isset($data->message)) ? $data->message : '';
		}
		$message = stripslashes( htmlspecialchars_decode( uap_format_str_like_wp( $message ) ) );

		$message = "<html><head></head><body>" . $message . "</body></html>";
		if ( $subject == '' && $message == '' ){
				return false;
		}

		$fromName = get_option( 'uap_notification_name' );
		if ( $fromName == '' ){
				$fromName = get_option( 'blogname' );
		}
		$fromEmail = get_option( 'uap_notification_email_from' );
		if ( $fromEmail == '' ){
				$fromEmail = get_option( 'admin_email' );
		}

		if ( !empty( $fromEmail ) && !empty( $fromName ) ){
			$headers[] = "From: $fromName <$fromEmail>";
		}
		$headers[] = 'Content-Type: text/html; charset=UTF-8';
		$sent = wp_mail( $email, $subject, $message, $headers );

		if ( $sent ){
				// notification log ( v. >= 8.5 )
				$log = [
					'notification_type'       => 'test_notification',
					'email_address'           => $email,
					'message'                 => $message,
					'subject'									=> $subject,
					'uid'                     => 0,
					'affiliate_id'            => 0,
					'rank_id'                 => 0,
				];
				\Indeed\Uap\Db\NotificationLogs::save( $log );
				// notification log ( v. >= 8.5 )
		}

		return $sent;
}
endif;

function uap_general_options_print_page_links($id=FALSE){
	/*
	 * used in admin section
	 * @param int
	 * @return string
	 */
	if ($id!=-1 && $id!==FALSE){
		$target_page_link = get_permalink($id);
		if ($target_page_link) {
			echo esc_uap_content('<div class="uap-general-options-link-pages">' . esc_html__('Link:', 'uap') . ' <a href="' . $target_page_link . '" target="_blank">' . $target_page_link . '</a></div>');
		}
	}
	return '';
}

function uap_get_redirect_links_as_arr_for_select(){
	/*
	 * used in admin section
	 * @param none
	 * @return array
	 */
	$return = array();
	$redirect_links = get_option("uap_custom_redirect_links_array");
	if (is_array($redirect_links) && count($redirect_links)){
		foreach ($redirect_links as $k=>$v){
			$return[$k] = esc_html__("Custom Link: ", 'uap') . $k;
		}
	}
	return $return;
}

function uap_get_all_pages(){
	/*
	 * @param none
	 * @return array
	 */
	$arr = array();
	$args = array(
					'sort_order' => 'ASC',
					'sort_column' => 'post_title',
					'hierarchical' => 1,
					'child_of' => 0,
					'parent' => -1,
					'number' => '',
					'offset' => 0,
					'post_type' => 'page',
					'post_status' => 'publish',
	);
	$pages = get_pages($args);
	if (isset($pages) && count($pages)>0){
		foreach ($pages as $page){
			if ($page->post_title==''){
				 $page->post_title = '(no title)';
			}
			$arr[$page->ID] = $page->post_title;
		}
	}
	return $arr;
}

function get_device_type(){
	/*
	 * @param none
	 * @return string
	 */
	if(!class_exists('MobileDetect'))
		require UAP_PATH . 'classes/MobileDetect.php';
	$detect = new MobileDetect();
	if( ($detect->isMobile()) || ($detect->isTablet()) ){
		 return 'mobile';
	}
	return 'web';
}

function uap_check_value_field($type='', $value='', $val2='', $register_msg=array()){
	/*
	 * @param string, string, string, array
	 * @return
	 */
	global $indeed_db, $current_user;
	if (isset($value) && $value!=''){
		switch ($type){
			case 'user_login':
				if (!validate_username($value)){
					$return = $register_msg['uap_register_error_username_msg'];
				}

				if ( (username_exists($value) && empty( $current_user->ID) ) || ( !empty( $current_user->ID ) && username_exists($value) && username_exists($value) !== $current_user->ID ) ) {
					//8.6 - extra check if a user its already registered but want to become affiliate.
					$return = $register_msg['uap_register_username_taken_msg'];
				}
				break;
			case 'user_email':
				if (!is_email($value)) {
					$return = $register_msg['uap_register_invalid_email_msg'];
				}
				if ( ( email_exists($value) && empty( $current_user->ID ) ) || ( !empty( $current_user->ID ) && email_exists($value) && email_exists($value) !== $current_user->ID ) ){
					//8.6 - extra check if a user its already registered but want to become affiliate.
					$return = $register_msg['uap_register_email_is_taken_msg'];
				}
				break;
			case 'confirm_email':
				if ($value==$val2){
					$return = 1;
				} else {
					$return = $register_msg['uap_register_emails_not_match_msg'];
				}
				break;
			case 'pass1':
				$register_metas = $indeed_db->return_settings_from_wp_option('register');
				if ($register_metas['uap_register_pass_options']==2){
					//characters and digits
					if (!preg_match('/[a-z]/', $value)){
						$return = $register_msg['uap_register_pass_letter_digits_msg'];
					}
					if (!preg_match('/[0-9]/', $value)){
						$return = $register_msg['uap_register_pass_letter_digits_msg'];
					}
				} else if ($register_metas['uap_register_pass_options']==3){
					//characters, digits and one Uppercase letter
					if (!preg_match('/[a-z]/', $value)){
						$return = $register_msg['uap_register_pass_let_dig_up_let_msg'];
					}
					if (!preg_match('/[0-9]/', $value)){
						$return = $register_msg['uap_register_pass_let_dig_up_let_msg'];
					}
					if (!preg_match('/[A-Z]/', $value)){
						$return = $register_msg['uap_register_pass_let_dig_up_let_msg'];
					}
				}
				//check the length of password
				if($register_metas['uap_register_pass_min_length']!=0){
					if (strlen($value)<$register_metas['uap_register_pass_min_length']){
						$return = str_replace( '{X}', $register_metas['uap_register_pass_min_length'], $register_msg['uap_register_pass_min_char_msg'] );
					}
				}
				break;
			case 'pass2':
				if ($value==$val2){
					$return = 1;
				} else {
					$return = $register_msg['uap_register_pass_not_match_msg'];
				}
				break;
			case 'tos':
				if ($value==1){
					$return = 1;
				} else {
					$return = $register_msg['uap_register_err_tos'];
				}
				break;

			default:
				//required conditional field
				$check = uap_required_conditional_field_test($type, $value);
				if ($check){
					$return = $check;
				} else {
					$return = 1;
				}
				break;
		}
		if (empty($return)){
			$return = 1;
		}
		return $return;
	} else {
		$check = uap_required_conditional_field_test($type, $value);//Check for required conditional field
		if ($check){
			return $check;
		} else {
			return $register_msg['uap_register_err_req_fields'];
		}
	}
}

function uap_required_conditional_field_test($name='', $match_string=''){
	/*
	 * @param string, string
	 * @return string with error if it's case, empty string if it's ok
	 */
	global $indeed_db;
	$fields_meta = $indeed_db->register_get_custom_fields();
	$key = uap_array_value_exists($fields_meta, $name, 'name');
	if ($key!==FALSE && isset($fields_meta[$key]) && $fields_meta[$key]['type']=='conditional_text' && !empty($fields_meta[$key]['conditional_text'])){
		if ($fields_meta[$key]['conditional_text']!=$match_string){
			return uap_correct_text($fields_meta[$key]['error_message']);
		}
	}
	return '';
}


function uap_get_currencies_list($type='all'){
	/*
	 * @param [string - all | custom ]
	 * @return array
	 */

	$custom = get_option('uap_currencies_list');
	if (empty($custom)){
		$custom = array();
	}
	$basic = array(
			'AUD' => 'Australian Dollar (A $)',
			'CAD' => 'Canadian Dollar (C $)',
			'EUR' => 'Euro (&#8364;)',
			'GBP' => 'British Pound (&#163;)',
			'JPY' => 'Japanese Yen (&#165;)',
			'USD' => 'U.S. Dollar ($)',
			'NZD' => 'New Zealand Dollar ($)',
			'CHF' => 'Swiss Franc',
			'HKD' => 'Hong Kong Dollar ($)',
			'SGD' => 'Singapore Dollar ($)',
			'SEK' => 'Swedish Krona',
			'DKK' => 'Danish Krone',
			'PLN' => 'Polish Zloty',
			'NOK' => 'Norwegian Krone',
			'HUF' => 'Hungarian Forint',
			'CZK' => 'Czech Koruna',
			'ILS' => 'Israeli New Shekel',
			'MXN' => 'Mexican Peso',
			'BRL' => 'Brazilian Real (only for Brazilian members)',
			'MYR' => 'Malaysian Ringgit (only for Malaysian members)',
			'PHP' => 'Philippine Peso',
			'TWD' => 'New Taiwan Dollar',
			'THB' => 'Thai Baht',
			'TRY' => 'Turkish Lira (only for Turkish members)',
			'RUB' => 'Russian Ruble',
			'ARS' => 'Argentine Peso',
			'BDT' => 'Bangladeshi Taka',
			'BTC' => 'Bitcoin',
			'BGN' => 'Bulgarian Lev',
			'CLP' => 'Chilean Peso',
			'CNY' => 'Chinese Yuan',
			'COP' => 'Colombian Peso',
			'HRK' => 'Croatia Kuna',
			'DOP' => 'Dominican Peso',
			'EGP' => 'Egyptian Pound',
			'ISK' => 'Icelandic Krona',
			'IDR' => 'Indonesia Rupiah',
			'INR' => 'Indian Rupee',
			'ILS' => 'Israeli Shekel',
			'IRR' => 'Iranian Rial',
			'KES' => 'Kenyan Shilling',
			'KZT' => 'Kazakhstani Tenge',
			'KIP' => 'Lao Kip',
			'MYR' => 'Malaysian Ringgit',
			'NPR' => 'Nepali Rupee',
			'NGN' => 'Nigerian Naira',
			'PKR' => 'Pakistani Rupee',
			'PYG' => 'Paraguayan Guaraní',
			'GBP' => 'Pounds Sterling',
			'RON' => 'Romanian Leu',
			'SAR' => 'Saudi Arabian Riyal',
			'ZAR' => 'South African Rand',
			'KRW' => 'South Korean Won',
			'TWD' => 'Taiwan New Dollar',
			'TND' => 'Tunisian Dinar',
			'AED' => 'United Arab Emirates Dirham',
			'UAH' => 'Ukrainian Hryvnia',
			'VND' => 'Vietnamese Dong'
	);
	if ($type=='custom'){
		return $custom;
	}
	return array_merge($basic, $custom);
}

function uap_get_image_size($image=''){
	/*
	 * @param string
	 * @return array
	 */
	if ($image){
		$data = @getimagesize($image);
		if (!empty($data[0]) && !empty($data[1])){
			return array('width'=>$data[0], 'height'=>$data[1]);
		}
	}
	return array();
}

/**
 * @param string
 * @param int
 * @param array
 * @return string
 */
function uap_replace_constants($str = '', $u_id = FALSE, $dynamic_data = array()){
	if ($u_id){
		global $indeed_db;
		$username = '';
		$first_name = '';
		$last_name = '';
		$user_email = '';
		$account_page = '';
		$login_page = '';
		$blogname = '';
		$blogurl = '';
		$site_url = '';
		$current_rank = '';
		$rank = '';
		$rank_name = '';

		//user data
		$u_data = get_userdata($u_id);
		$user_email = isset( $u_data->data->user_email ) ? $u_data->data->user_email : '';
		$username = isset( $u_data->data->user_login ) ? $u_data->data->user_login : '';
		$user_url = isset( $u_data->data->user_url ) ? $u_data->data->user_url : '';

		$first_name = get_user_meta($u_id, 'first_name', true);
		$last_name = get_user_meta($u_id, 'last_name', true);
		$blogname = get_option("blogname");
		$blogurl = get_option("siteurl");
		$site_url = get_option('siteurl');
		$affiliate_id = $indeed_db->get_affiliate_id_by_wpuid($u_id);

		$start_data = $indeed_db->get_affiliate_start_data($affiliate_id);
		$user_registered = isset( $start_data ) ? $start_data : '';
		$user_registered = uap_convert_date_to_us_format( $user_registered );

		///CURRENT RANK
		$rank = $indeed_db->get_affiliate_rank(0, $u_id);
		if ($rank){
			$rank_data = $indeed_db->get_rank($rank);
			$rank_name = (empty($rank_data['label'])) ? '' : $rank_data['label'];
		}

		//account page
		$account_page = get_option("uap_general_user_page");
		if ($account_page){
			$account_page = get_permalink($account_page);
		}
		//login page
		$login_page = get_option("uap_general_login_default_page");
		if ($login_page){
			$login_page = get_permalink($login_page);
		}

		/// AVATAR
		$avatar = get_user_meta($u_id, 'uap_avatar', true);
		if (strpos($avatar, "http")===0){
			$avatar_url = $avatar;
		} else {
			$avatar_url = wp_get_attachment_url($avatar);
		}
		$avatar = ($avatar_url) ? $avatar_url : UAP_URL . 'assets/images/no-avatar.png';
		$avatar = '<img src="' . $avatar . '" class="uap-public-shortcode-avatar"/>';

		$flag = get_user_meta($u_id, 'uap_country', true);
		if (empty($flag)){
			$flag = '';
		} else {
			$countries = uap_get_countries();
			$key = $flag;
			$country = $countries[strtoupper($key)];
			$title = (empty($country)) ? '' : $country;
			$flag = '<img src="' . UAP_URL . 'assets/flags/' . $flag . '.svg" class="uap-public-flag" title="' . $title . '" />';
		}

		$replace = array(
				"{username}" => $username,
				"{first_name}" => $first_name,
				"{last_name}" => $last_name,
				"{user_id}" => $u_id,
				"{current_rank}" => $current_rank,
				"{user_email}" => $user_email,
				"{account_page}" => $account_page,
				"{login_page}" => $login_page,
				"{blogname}" => $blogname,
				"{blogurl}" => $blogurl,
				"{siteurl}" => $site_url,
				'{rank_id}' => $rank,
				'{rank_name}' => $rank_name,
				'{user_url}' => $user_url,
				'{uap_avatar}' => $avatar,
				'{user_registered}' => $user_registered,
				'{flag}' => $flag,
				'{affiliate_id}' => $affiliate_id,
		);

		$custom_constant_fields = uap_get_custom_constant_fields();

		foreach ($custom_constant_fields as $k=>$v){
				if ( !$v ){
						continue;
				}
				$replace[$k] = get_user_meta($u_id, $v, TRUE);
				if ( $replace[$k] && is_array($replace[$k])){
					$replace[$k]= implode(',',$replace[$k]);
				}
		}

		//if ($dynamic_data){
			foreach ($dynamic_data as $k=>$v){
				$replace[$k] = $v;

			}
		//}

		foreach ($replace as $k=>$v){
			$str = str_replace($k, $v, $str);
		}

	}
	return $str;
}

if ( !function_exists( 'uapAdminCreateMessage' ) ):
function uapAdminCreateMessage( $code='', $successClass='', $errorClass='', $langCode='' )
{
	if ( $code === '' ){
			return '';
	}
	switch ( $code ){
			case 1:
				return "<div class='$successClass'>" . esc_html__('Your license for Ultimate Affiliate Pro is currently Active, ensuring you have access to all the premium features and automatic updates.', $langCode ) . "</div>";
				break;
			case 0:
				return "<div class='$errorClass'>" . esc_html__('B'.'ad '.'input'.' data.'.' Ple'.'ase'.' try'.' aga'.'in'.' lat'.'er'.'!', $langCode ) . "</div>";
				break;
			case -1:
				return "<div class='$errorClass'>" . esc_html__('Env'.'ato' . ' '.'API'.' '.'Ser'.'ver'.' '.'may'.' '.'be'.' '.'done'.' '.'for'.' '.'a '.'moment'
								. '. '.'Plea'.'se'.' '.'try'.' aga'.'in '.'later', $langCode ) . "</div>";
				break;
			case -2:
				return "<div class='$errorClass'>" . esc_html__('Sub'.'mitted '.'Pur'.'chase'.' '.'Co'.'de'.' '.'is'.' '.'inv'.'a'.'lid.', $langCode ) . "</div>";
				break;
			case -3:
				return "<div class='$errorClass'>" . esc_html__('Sub'.'mi'.'t'.'ted '.'Pur'.'ch'.'ase '.'C'.'o'.'de '.'do'.'es '.'n'.'ot '.'m'.'at'.'ch '.'wi'.'th '.'Cur'.'rent'.' '.'pro'.'d'.'uc'.'t.', $langCode ) . "</div>";
				break;
	}
	return '';
}
endif;

function uap_get_custom_constant_fields(){
	/*
	 * @param none
	 * @return array
	 */
	global $indeed_db;
	$data = $indeed_db->register_get_custom_fields();

	if ($data && is_array($data)){
		foreach ($data as $arr){
			$fields["{CUSTOM_FIELD_" . $arr['name'] ."}"] = $arr['name'];
		}
		$diff = array('uap_social_media', 'recaptcha', 'tos', 'pass2', 'pass1', 'user_login', 'user_email', 'confirm_email', 'first_name', 'last_name', 'uap_avatar');
		$fields = array_diff($fields, $diff);

		return $fields;
	}
	return array();
}

function uap_create_affiliate_link($url='', $ref_param='', $ref_value='', $campaign_name='', $campaign_value='', $prettify=FALSE){
	/*
	 * @param string
	 * @return string
	 */
	if (strpos($url, '?')===FALSE){
		if (substr($url, -1, 1)!='/'){
			$url .= '/';
		}
	}
	if ($prettify){
		$slash_pos = strpos($url, '?');
		if ($slash_pos){
			$url = substr($url, 0, $slash_pos - 1);
		}
		if (substr($url, -1)!='/'){
			$url .= '/';
		}
		$url .= implode('/', array($ref_param, $ref_value));
		if ($campaign_name && $campaign_value){
			$url .= '/' . implode('/', array($campaign_name, $campaign_value));
		}
		return $url;
	}
	$url = add_query_arg($ref_param, $ref_value, $url);
	if ($campaign_name && $campaign_value){
		$url = add_query_arg($campaign_name, $campaign_value, $url);
	}
	if ($prettify){
		$url = user_trailingslashit($url);
	}
	return $url;
}

function uap_return_cc_list($user, $pass){
	/*
	 * @param string, string
	 * @return array
	 */
	if (!class_exists('cc')){
		include_once UAP_PATH . 'classes/services/email_services/constantcontact/class.cc.php';
	}
	$list = array();
	$cc = new cc($user, $pass);
	$lists = $cc->get_lists('lists');
	if ($lists){
		foreach ((array) $lists as $v){
			$list[$v['id']] = array('name' => $v['Name']);
		}
	}
	return $list;
}

function uap_return_date_filter($url='', $status_arr=array(), $source_arr=array(), $search_affiliate=FALSE){
	/*
	 * @param string
	 * @return string
	 */
	wp_enqueue_script('jquery-ui-datepicker');
	ob_start();
	$start = (empty($_REQUEST['udf'])) ? '' : $_REQUEST['udf'];
	$end = (empty($_REQUEST['udu'])) ? '' : $_REQUEST['udu'];
	$status = (isset($_REQUEST['u_sts'])) ? $_REQUEST['u_sts'] : -1;
	$source = (isset($_REQUEST['u_source'])) ? $_REQUEST['u_source'] : -1;

	$start = sanitize_text_field( $start );//filter_var( $start, FILTER_SANITIZE_STRING );
	$start = preg_replace( "([^0-9-])", '', $start );
	$end = sanitize_text_field( $end );//filter_var( $end, FILTER_SANITIZE_STRING );
	$end = preg_replace( "([^0-9-])", '', $end );
	?>
	<form action="<?php echo esc_url($url);?>" method="post">
		<div class="uap-general-date-filter-wrap">
			<?php if ($search_affiliate):?>
			<input type="text" name="aff_u" value="<?php echo (isset($_REQUEST['aff_u'])) ? $_REQUEST['aff_u'] : '';?>" class="uap-data-filter-input" placeholder="<?php esc_html_e('Affiliate Username or Email Address', 'uap');?>"/>
			<?php endif;?>
			<!--label class="uap-label"><?php esc_html_e('Start:', 'uap');?></label-->
			<input type="text" name="udf" value="<?php echo esc_attr($start);?>" class="uap-general-date-filter" placeholder="From - yyyy-mm-dd"/>
			<!--label class="uap-label"><?php esc_html_e('Until:', 'uap');?></label--><span class="uap-date-line">-</span>
			<input type="text" name="udu" value="<?php echo esc_attr($end);?>" class="uap-general-date-filter" placeholder="To - yyyy-mm-dd"/>

			<?php if (!empty($status_arr)):
					$status_arr[-1] = '...';
					ksort($status_arr);
				?>
				<select name="u_sts"><?php
					foreach ($status_arr as $key=>$value):
					$selected = ($status==$key) ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr($key);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($value);?></option>
					<?php
					endforeach;
				?></select>
			<?php endif;?>
			<?php if (!empty($source_arr)):
					$source_arr[-1] = '...';
					ksort($source_arr);
				?>
				<select name="u_source"><?php
					foreach ($source_arr as $key=>$value):
					$selected = ($source==$key) ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr($key);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($value);?></option>
					<?php
					endforeach;
				?></select>
			<?php endif;?>

			<input type="submit" value="<?php esc_html_e("Apply Filter", 'uap');?>" name="apply" class="button button-primary button-large" />
		</div>

	</form>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}


function uap_is_social_share_intalled_and_active(){
	/*
	 * @param none
	 * @return boolean
	 */
	if (is_plugin_active('indeed-social-media/indeed-social-media.php')){
		if (get_option('ism_license_set')==1){
			return TRUE;
		}
	}
	return FALSE;
}

function uap_return_default_notification_content($type=''){
	/*
	 * @param string
	 * @return array
	 */
	$template = array();
	if ($type){
		switch ($type){
				case 'admin_user_register':
					$template['subject'] = '{blogname}: New Affiliate User registration';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>New Affiliate User registration on: <strong> {blogname} </strong></div>
					  <div><strong> Username:</strong> {username}</div>
					  <div><strong> Email:</strong> {user_email}</div>
					  <div>Have a nice day!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Notification to Administrators upon new affiliate registration';
					break;
				case 'register':
					$template['subject'] = '{blogname}: Welcome to {blogname}';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {username},</div>
					  <div>Thanks for registering on {blogname}. Your account is now active.</div>
					  <div>To login please fill out your credentials on:<br/>
					  {login_page}</div>
					  <div>Your Username: {username}</div>
					  <div>Have a nice day!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Empower Affiliates with timely notifications upon the registration of their accounts';
					break;
				case 'reset_password_process':
					$template['subject'] = '{blogname}: Reset Password request';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {first_name} {last_name},</div>
					  <div>You or someone else has requested to change password for your account: {username}</div>
					  <div>To change Your Password click on this URL:<br> {password_reset_link}</div>
					  <div>If you did not request for a new password, please ignore this Email notification.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'When an affiliate wants to reset his password, this notification is delivered first in order for the affiliate to approve the request. A link for requesting confirmation will be given. This will ensure that the request was made by a current affiliate and not someone else';
					break;
				case 'reset_password':
					$template['subject'] = '{blogname}: Reset Password';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {first_name} {last_name},</div>
					  <div>You or someone else has requested to change password for your account: {username}</div>
					  <div>Your new Password is: <strong>{NEW_PASSWORD}</strong></div>
					  <div>To update your Password once you are logged from your Profile Page:{account_page}</div>
					  <div>If you did not request for a new password, please ignore this Email notification.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'After the affiliate confirms his reset password request a random password will be generated for him and sent via this second Notification';
					break;
				case 'change_password':
					$template['subject'] = '{blogname}: Your Password has been changed';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {first_name} {last_name},</div>
					  <div>Your Password has been changed.</div>
					  <div>To login please fill out your credentials on:<br>{login_page}</div>
					  <div>Your Username: {username}</div>
					  <div>Have a nice day!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'After WordPress user password have been successfully changed this notification will be sent out';
					break;
				case 'user_update':
					$template['subject'] = '{blogname}: Your Account has been Updated';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {username},</div>
					  <div>Your Account has been Updated.</div>
					  <div>To visit your Profile page follow the next link:<br/>{account_page}</div>
					  <div>Have a nice day!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Notification to affiliates confirming successful profile updates';
					break;
				case 'rank_change':
					$template['subject'] = '{blogname}: You\'ve got a new Rank! ';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {username},</div>
					  <div>You receive a new Rank!</div>
					  <div>Have a nice day!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Notification to affiliates upon unlocking a new rank';
					break;
				case 'admin_on_aff_change_rank':
					$template['subject'] = '{blogname}: New Rank Achieved';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>{username} gets the following rank {rank_name}</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Alert to Administrators when a new rank is assigned to an affiliate';
					break;
				case 'admin_affiliate_update_profile':
					$template['subject'] = '{blogname}: Affiliate  Profile Updated';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>{username} has update his \ her profile.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Notification to Administrators when an affiliate updates profile details';
					break;
				case 'affiliate_account_approve':
					$template['subject'] = '{blogname}: Your Account has been approved';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>{username} has been approved.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Notification to affiliates upon approval of their account';
					break;
				case 'affiliate_profile_delete':
					$template['subject'] = '{blogname}: Your Account has been deleted';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>{username} Your account has been deleted.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Alert to affiliates when their account is deleted';
					break;
				case 'affiliate_payment_fail':
					$template['subject'] = '{blogname}: Payout has Failed';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Error on transfering {amount_to_pay} {amount_currency} to You!</div>
					  <div>Please review Your payment settings or contact the administrator!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Notifies affiliates when a payout has encountered an issue and failed to process successfully';
					break;
				case 'affiliate_payment_pending':
					$template['subject'] = '{blogname}: Payout is on Pending';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>{amount_to_pay} {amount_currency} has been sent to You. Payment status is pending.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Alerts affiliates when their payout is currently in the processing stage';
					break;
				case 'affiliate_payment_complete':
					$template['subject'] = '{blogname}: Payout Completed';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Your {amount_to_pay} {amount_currency} it\'s now available to You.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Informs affiliates that their payout has been successfully processed and completed';
					break;
				case 'email_check':
					$template['subject'] = '{blogname}: Email Verification';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {first_name} {last_name},</div>
					  <div>You must confirm/validate your Email Account before logging in.</div>
					    <div>Please click on the following link to successfully activate your account:<br/>
					  	<a href="{verify_email_address_link}">click here</a></div>
					      <div>Have a nice day!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'When Double Email Verification setting is turned on, after affiliate registration this first email is sent with a generated confirmation link inside that will allow affiliate to confirm that his email is real.';
					break;
				case 'email_check_success':
					$template['subject'] = '{blogname}: Email Verification Successfully';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {first_name} {last_name},</div>
					  <div>Your account is now verified at {blogname}.</div>
					      <div>Have a nice day!</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'When Double Email Verification setting is turned on, after affiliate confirmed his email address a successful confirmation notification is sent';
					break;
				case 'register_lite_send_pass_to_user':
					$template['subject'] = '{blogname}: Your Password';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {username},</div>
					  <div>Your password for {blogname} is {NEW_PASSWORD}</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Password notification sent to the customer after affiliate registration when the password field is not provided inside registration form';
					break;
				case 'mlm_new_assignation':
					$template['subject'] = '{blogname}: New MLM assignation ';
					$template['content'] = '<div style="max-width: 600px; padding: 20px; border-radius: 5px; margin: 40px auto; font-family: Open Sans,Helvetica,Arial; font-size: 15px; color: #5d5d5d;">
					  <div style="background:#eaeaea; text-align: left; font-weight: 600; font-size: 26px; padding: 30px 30px 30px 30px; color: #5d5d5d;"">{blogname}</div>
					<div style="background:#fff;font-size:18px; text-align: left; line-height: 40px; color: #5d5d5d; padding: 30px 25px;">
					  <div style="padding-top:30px"></div>
					  <div>Hi {username},</div>
					  <div>A new user has registered using your Affiliate link.</div>
					<div style="padding-top:30px"></div>
					</div>
					<div style="background: #545454; color: #fff; padding: 20px 30px;">
					<div>Thank you, The <a style="color: #fff;" href="{blogurl}">{blogname}</a> Team</div>
					</div>
					</div>';
					$template['description']	= 'Notification to affiliates about their assignment in MLM workflow';
					break;

			}
	}
	return $template;
}

if ( !function_exists( 'uapPrevLabel' ) ):
function uapPrevLabel()
{
    return 'Previous';
}
endif;

function uap_convert_date_to_us_format($date=''){
	/*
	 * @param string
	 * @return string
	 */
	if ($date && $date!='-' && is_string($date)){
		if(isset($date)){
			$date = strtotime($date);
		}

		$format = get_option('date_format');
		$return_date = date_i18n($format, $date);

		$time_format = get_option('time_format');
		$time = date_i18n($time_format, $date);
		if ($time){
			$time = ' ' . $time;
		}
		return $return_date . $time;
	}
	return $date;
}

function uap_service_type_code_to_title($type=''){
	/*
	 * @param string
	 * @return string
	 */
	if ($type){
		switch ($type){
			case 'ump':
				$label = get_option('uap_custom_source_name_ump');
				if ($label){
						return $label;
				}
				return 'Ultimate Membership Pro';
				break;
			case 'uap':
				return 'Ultimate Affiliate Pro';
				break;
			case 'ppc':
				return 'PayPerClick';
				break;
			case 'woo':
				$label = get_option('uap_custom_source_name_woo');
				if ($label){
						return $label;
				}
			 	return 'WooCommerce';
				break;
			case 'ulp':
				$label = get_option('uap_custom_source_name_ulp');
				if ($label){
						return $label;
				}
				return 'Ultimate Learning Pro';
				break;
			case 'edd':
				$label = get_option('uap_custom_source_name_edd');
				if ($label){
						return $label;
				}
				return 'Easy Digital Downloads';
				break;
			case 'bonus':
				$label = get_option('uap_custom_source_name_bonus');
				if ($label){
						return $label;
				}
				return 'Bonus';
				break;
			case 'mlm':
				$label = get_option('uap_custom_source_name_mlm');
				if ($label){
						return $label;
				}
				return 'MLM';
				break;
			case 'User SignUp':
				$label = get_option('uap_custom_source_name_user_signup');
				if ($label){
						return $label;
				}
				return 'User SignUp';
				break;
			case 'from landing commissions':
				$label = get_option('uap_custom_source_name_landing_commissions');
				if ($label){
						return $label;
				}
				return 'Landing commissions';
				break;
			default:
				return $type;
				break;
		}
	}
	return '';
}

if ( !function_exists( 'uap_service_label_to_service_code' ) ):
	/*
	 * @param string
	 * @return string
	 */
function uap_service_label_to_service_code($label=''){
		if ($label===''){
				return '';
		}
		switch ($label){
			case 'Ultimate Membership Pro':
				return 'ump';
				break;
			case 'Ultimate Affiliate Pro':
				return 'uap';
				break;
			case 'PayPerClick':
				return 'ppc';
				break;
			case 'WooCommerce':
				return 'woo';
				break;
			case 'Ultimate Learning Pro':
				return 'ulp';
				break;
			case 'Easy Digital Downloads':
				return 'edd';
				break;
			case 'Bonus':
				return 'bonus';
				break;
			case 'MLM':
				return 'mlm';
				break;
			case 'User SignUp':
				return 'User SignUp';
				break;
			case 'Landing commissions':
				return 'from landing commissions';
				break;
			default:
				$customLabel = get_option('uap_custom_source_name_ump');
				if ( $customLabel === $label ){
						return 'ump';
				}
				$customLabel = get_option('uap_custom_source_name_woo');
				if ( $customLabel === $label ){
						return 'woo';
				}
				$customLabel = get_option('uap_custom_source_name_edd');
				if ( $customLabel === $label ){
						return 'edd';
				}
				$customLabel = get_option('uap_custom_source_name_ulp');
				if ( $customLabel === $label ){
						return 'ulp';
				}
				$customLabel = get_option('uap_custom_source_name_bonus');
				if ( $customLabel === $label ){
						return 'bonus';
				}
				$customLabel = get_option('uap_custom_source_name_mlm');
				if ( $customLabel === $label ){
						return 'mlm';
				}
				$label = get_option('uap_custom_source_name_user_signup');
				if ( $customLabel === $label ){
						return 'User SignUp';
				}
				$label = get_option('uap_custom_source_name_landing_commissions');
				if ( $customLabel === $label ){
						return 'from landing commissions';
				}
				return $label;
				break;
		}
		return '';
}
endif;

function uap_from_simple_array_to_k_v($arr){
	/*
	 * @param array
	 * @return array
	 */
	$return_arr = array();
	foreach ($arr as $v){
		$return_arr[$v] = $v;
	}
	return $return_arr;
}

function uap_make_string_simple($str=''){
	/*
	 * @param string
	 * @return string
	 */
	if (!empty($str)){
		$str = trim($str);
		$str = str_replace(' ', '_', $str);
		$str = preg_replace("/[^A-Za-z0-9_]/", '', $str);//remove all non-alphanumeric chars
	}
	return $str;
}

if ( !function_exists( 'uapRankGeneralLabel' ) ):
function uapRankGeneralLabel()
{
    return 'Ranks';
}
endif;

function uap_random_string($length=4, $keyspace='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
	/*
	 * @param length - int, keyspace - string
	 * @return string
	 */
	$str = '';
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$str .= $keyspace[rand(0, $max)];
	}
	return $str;
}

function uap_get_active_services(){
	/*
	 * @param none
	 * @return array
	 */
	 $array = array();
	 if (!function_exists('is_plugin_active')){
	 	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	 }
	 if ( is_plugin_active('indeed-membership-pro/indeed-membership-pro.php') && \Indeed\Uap\Integrations::isUmpActive() ){
	 	$array['ump'] = 'Ultimate Membership Pro';
	 }
	 if ( is_plugin_active('woocommerce/woocommerce.php') && \Indeed\Uap\Integrations::isWooActive() ){
	 	$array['woo'] = 'WooCommerce';
	 }
	 if ( is_plugin_active('indeed-learning-pro/indeed-learning-pro.php') && \Indeed\Uap\Integrations::isUlpActive() ){
	 	$array['ulp'] = 'Ultimate Learning Pro';
	 }
	 if ( ( is_plugin_active('easy-digital-downloads/easy-digital-downloads.php') || is_plugin_active( 'easy-digital-downloads-pro/easy-digital-downloads.php') ) && \Indeed\Uap\Integrations::isEddActive() ){
	 	$array['edd'] = 'Easy Digital Downloads';
	 }
	 return $array;
}

function uap_is_ump_active(){
	/*
	 * @param none
	 * @return bool
	 */
	  if (!function_exists('is_plugin_active')){
	 	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	 }
	 if (is_plugin_active('indeed-membership-pro/indeed-membership-pro.php')){
	 	return TRUE;
	 }
	 return FALSE;
}
function uap_is_woo_active(){
	/*
	 * @param none
	 * @return bool
	 */
	  if (!function_exists('is_plugin_active')){
	 	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	 }
	 if (is_plugin_active('woocommerce/woocommerce.php')){
	 	return TRUE;
	 }
	 return FALSE;
}

function uap_get_avatar_for_uid($uid){
	/*
	 * @param int
	 * @return string
	 */
	global $indeed_db;
	$avatar_url = UAP_URL . 'assets/images/no-avatar.png';
	if (!empty($uid)){
		$avatar = get_user_meta($uid, 'uap_avatar', TRUE);
		if (!empty($avatar)){
			if (strpos($avatar, "http")===0){
				$avatar_url = $avatar;
			} else {
				$avatar_url = $indeed_db->getMediaBaseImage( $avatar );
				if ( $avatar_url && strpos($avatar_url, "http")===0 ){
						return $avatar_url;
				}
				$avatar_data = wp_get_attachment_image_src($avatar, 'full');
				if (!empty($avatar_data[0])){
					$avatar_url = $avatar_data[0];
				}
			}
		}
	}
	return $avatar_url;
}

function uap_get_possible_referral_types(){
	/*
	 * @param none
	 * @return array
	 */
	 $array = uap_get_active_services();
	 global $indeed_db;
	 if ($indeed_db->is_magic_feat_enable('mlm')){
	 	$array['mlm'] = esc_html__('Multi Level Marketing', 'uap');
	 }
	 if ($indeed_db->is_magic_feat_enable('bonus_on_rank')){
	 	$array['bonus'] = esc_html__('Bonus Rank', 'uap');
	 }
	 if ($indeed_db->is_magic_feat_enable('sign_up_referrals')){
	 	$array['User SignUp'] = esc_html__('SignUp', 'uap');
	 }
	 $referral_types = array();
	 foreach ($array as $k=>$v){
	 	$referral_types[$k]['label'] = $v;
		switch($k){
			case 'ump':
						$referral_types[$k]['sub_label'] = esc_html__('Based on Subscription purchases from Ultimate Membership system', 'uap');
						break;
			case 'woo':
						$referral_types[$k]['sub_label'] = esc_html__('Based on Product purchases from WooCommerce system', 'uap');
						break;
			case 'ulp':
						$referral_types[$k]['sub_label'] = esc_html__('Based on Product purchases from Ultimate Learning Pro system', 'uap');
						break;
			case 'edd':
						$referral_types[$k]['sub_label'] = esc_html__('Based on Product purchases from Easy Digital Downloads system', 'uap');
						break;
			case 'mlm':
						$referral_types[$k]['sub_label'] = esc_html__('Related on Rewarads provided to Affiliate from your MLM System', 'uap');
						break;
			case 'bonus':
						$referral_types[$k]['sub_label'] = esc_html__('Bonus when a new Rank is achieved', 'uap');
						break;
			case 'User SignUp':
						$referral_types[$k]['sub_label'] = esc_html__('Referrals from based on new user SignUp Rewards system', 'uap');
						break;
			default:
					$referral_types[$k]['sub_label'] = '';
		}
	 }
	return $referral_types;
}

function uap_generate_qr_code($link='', $file_unique_name=''){
	/*
	 * @param string, string
	 * @return string
	 */
	 if ($link){
	 	if (!class_exists('QRcode')){
	 		require_once UAP_PATH . 'classes/services/qrcode/qrlib.php';
	 	}
		uap_empty_qr_images();/// delete old files
		if (strpos($file_unique_name, 'home')!==FALSE){
				$file_name = 'qrcode_' . $file_unique_name . '.png';
		} else {
				$file_name = 'qrcode_' . $file_unique_name . time() . '.png';
		}

		$file_location = UAP_PATH . 'classes/services/qrcode/images/' . $file_name;
		$file_link = UAP_URL . 'classes/services/qrcode/images/' . $file_name;
		$size = get_option('uap_qr_code_size');
		if (!$size){
			$size = 5;
		}

		$ecc_level = get_option('uap_qr_code_size');
		switch ($ecc_level){
			case 'l':
				$ecc_level = QR_ECLEVEL_L;
				break;
			case 'm':
				$ecc_level = QR_ECLEVEL_M;
				break;
			case 'q':
				$ecc_level = QR_ECLEVEL_Q;
				break;
			case 'h':
			default:
				$ecc_level = QR_ECLEVEL_H;
				break;
		}
		try {
			\QRcode::png($link, $file_location, $ecc_level, $size);
			return $file_link;
		}catch(\Exception $e){}

	 }
	 return '';
}

function uap_do_opt_in($email=''){
	/*
	 * @param string
	 * @return none
	 */
	global $indeed_db;
	if (!get_option('uap_register_opt-in')){
		return;
	}
	$target_opt_in = get_option('uap_register_opt-in-type');
	if ($target_opt_in && $email){
		if (!class_exists('OptInMailServices')){
			require_once UAP_PATH . 'classes/OptInMailServices.class.php';
		}

		$uid = $indeed_db->getUidByEmail( $email );
		if ( isset( $_POST['first_name'] ) ){
				$firstName = sanitize_text_field( $_POST['first_name'] );
		} else {
				$firstName = get_user_meta( 'first_name', $uid, true );
		}
		if ( !$firstName ){
				$firstName = '';
		}
		if ( isset( $_POST['last_name'] ) ){
				$lastName = sanitize_text_field( $_POST['last_name'] );
		} else {
				$lastName = get_user_meta( 'last_name', $uid, true );
		}
		if ( !$lastName ){
				$lastName = '';
		}

		$indeed_mail = new OptInMailServices();
		$indeed_mail->dir_path = UAP_PATH . 'classes';
		switch ($target_opt_in){
			case 'aweber':
				$awListOption = get_option('uap_aweber_list');
				if ($awListOption){
					$aw_list = str_replace('awlist', '', $awListOption);
					$consumer_key = get_option( 'uap_aweber_consumer_key' );
					$consumer_secret = get_option( 'uap_aweber_consumer_secret' );
					$access_key = get_option( 'uap_aweber_acces_key' );
					$access_secret = get_option( 'uap_aweber_acces_secret' );
					if ($consumer_key && $consumer_secret && $access_key && $access_secret){
						$return = $indeed_mail->indeed_aWebberSubscribe( $consumer_key, $consumer_secret, $access_key, $access_secret, $aw_list, $email, $firstName . ' ' . $lastName );
					}
				}
				break;
			case 'email_list':
				$email_list = get_option('uap_email_list');
				$email_list .= $email . ',';
				update_option('uap_email_list', $email_list);
				break;
			case 'mailchimp':
				$mailchimp_api = get_option( 'uap_mailchimp_api' );
				$mailchimp_id_list = get_option( 'uap_mailchimp_id_list' );
				if ($mailchimp_api && $mailchimp_id_list){
					$indeed_mail->indeed_mailChimp( $mailchimp_api, $mailchimp_id_list, $email, $firstName, $lastName );
				}
				break;
			case 'get_response':
				$api_key = get_option('uap_getResponse_api_key');
				$token = get_option('uap_getResponse_token');
				if ( $api_key === '' || $token === '' ){
						return false;
				}
				$addcontacturl = 'https://api.getresponse.com/v3/contacts/';
				$getcontacturl = 'https://api.getresponse.com/v3/contacts?query[email]='.$email;
				$fullName = $firstName . ' ' . $lastName;
				$data = array (
				'name' 				=> $fullName,
				'email' 			=> $email,
				'dayOfCycle' 	=> 0,
				'campaign' 		=> array( 'campaignId' => $token ),
				'ipAddress'		=>  $_SERVER['REMOTE_ADDR'],
				);

				$dataString = json_encode($data);

				$ch = curl_init($addcontacturl);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString );
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						    'Content-Type: application/json',
						    'X-Auth-Token: api-key '.$api_key,
						)
				);

				$result = curl_exec($ch);
				break;
			case 'campaign_monitor':
				$listId = get_option('uap_cm_list_id');
				$apiID = get_option('uap_cm_api_key');
				if ($listId && $apiID){
					$indeed_mail->indeed_campaignMonitor( $listId, $apiID, $email, $firstName . ' ' . $lastName );
				}
				break;
			case 'icontact':
				$appId = get_option('uap_icontact_appid');
				$apiPass = get_option('uap_icontact_pass');
				$apiUser = get_option('uap_icontact_user');
				$listId = get_option('uap_icontact_list_id');
				if ($appId && $apiPass && $apiUser && $listId){
					$indeed_mail->indeed_iContact( $apiUser, $appId, $apiPass, $listId, $email, $firstName, $lastName );
				}
				break;
			case 'constant_contact':
				$apiUser = get_option('uap_cc_user');
				$apiPass = get_option('uap_cc_pass');
				$listId = get_option('uap_cc_list');
				if ($apiUser && $apiPass && $listId){
					$indeed_mail->indeed_constantContact( $apiUser, $apiPass, $listId, $email, $firstName, $lastName );
				}
			break;
			case 'wysija':
				$listID = get_option('uap_wysija_list_id');
				if ($listID){
					$indeed_mail->indeed_wysija_subscribe( $listID, $email, $firstName, $lastName );
				}
				break;
			case 'mymail':
				$listID = get_option('uap_mymail_list_id');
				if ($listID){
					$indeed_mail->indeed_myMailSubscribe( $listID, $email, $firstName, $lastName );
				}
				break;
			case 'madmimi':
				$username = get_option('uap_madmimi_username');
				$api_key =  get_option('uap_madmimi_apikey');
				$listName = get_option('uap_madmimi_listname');
				if ($username && $api_key && $listName){
					$indeed_mail->indeed_madMimi( $username, $api_key, $listName, $email, $firstName, $lastName );
				}
				break;
			case 'active_campaign':
				$api_url = get_option('uap_active_campaign_apiurl');
				$api_key =  get_option('uap_active_campaign_apikey');
				if ($api_url && $api_key){
					$indeed_mail->add_contanct_to_active_campaign( $api_url, $api_key, $email, $firstName, $lastName );
				}
				break;
		}
	}
}

function uap_get_attachment_details($id = 0, $return_type='name'){
	/*
	 * @param attachment id, what to return: name or extension
	 * @return string :
	 */
	$attachment_data = wp_get_attachment_url($id);
	if (isset($attachment_data)){
		$attachment_arr = explode('/', $attachment_data);
		if (isset($attachment_arr)){
			end($attachment_arr);
			$attachment_name = $attachment_arr[key($attachment_arr)];
			if ($return_type=='name'){
				return $attachment_name;
			}
			$attachment_type = explode('.', $attachment_name);
			if (isset($attachment_type)){
				end($attachment_type);
				if (isset($attachment_type[key($attachment_type)])){
					return $attachment_type[key($attachment_type)];
				}
			}
		}
	}
	return 'Unknown';
}

if (!function_exists('uap_get_countries')):

	function uap_get_countries(){
		/*
		 * @param none
		 * @return array
		 */
		 return array(
						'AF' => esc_html__( 'Afghanistan', 'uap' ),
						'AX' => esc_html__( '&#197;land Islands', 'uap' ),
						'AL' => esc_html__( 'Albania', 'uap' ),
						'DZ' => esc_html__( 'Algeria', 'uap' ),
						'AS' => esc_html__( 'American Samoa', 'uap' ),
						'AD' => esc_html__( 'Andorra', 'uap' ),
						'AO' => esc_html__( 'Angola', 'uap' ),
						'AI' => esc_html__( 'Anguilla', 'uap' ),
						'AQ' => esc_html__( 'Antarctica', 'uap' ),
						'AG' => esc_html__( 'Antigua and Barbuda', 'uap' ),
						'AR' => esc_html__( 'Argentina', 'uap' ),
						'AM' => esc_html__( 'Armenia', 'uap' ),
						'AW' => esc_html__( 'Aruba', 'uap' ),
						'AU' => esc_html__( 'Australia', 'uap' ),
						'AT' => esc_html__( 'Austria', 'uap' ),
						'AZ' => esc_html__( 'Azerbaijan', 'uap' ),
						'BS' => esc_html__( 'Bahamas', 'uap' ),
						'BH' => esc_html__( 'Bahrain', 'uap' ),
						'BD' => esc_html__( 'Bangladesh', 'uap' ),
						'BB' => esc_html__( 'Barbados', 'uap' ),
						'BY' => esc_html__( 'Belarus', 'uap' ),
						'BE' => esc_html__( 'Belgium', 'uap' ),
						'PW' => esc_html__( 'Belau', 'uap' ),
						'BZ' => esc_html__( 'Belize', 'uap' ),
						'BJ' => esc_html__( 'Benin', 'uap' ),
						'BM' => esc_html__( 'Bermuda', 'uap' ),
						'BT' => esc_html__( 'Bhutan', 'uap' ),
						'BO' => esc_html__( 'Bolivia', 'uap' ),
						'BQ' => esc_html__( 'Bonaire, Saint Eustatius and Saba', 'uap' ),
						'BA' => esc_html__( 'Bosnia and Herzegovina', 'uap' ),
						'BW' => esc_html__( 'Botswana', 'uap' ),
						'BV' => esc_html__( 'Bouvet Island', 'uap' ),
						'BR' => esc_html__( 'Brazil', 'uap' ),
						'IO' => esc_html__( 'British Indian Ocean Territory', 'uap' ),
						'VG' => esc_html__( 'British Virgin Islands', 'uap' ),
						'BN' => esc_html__( 'Brunei', 'uap' ),
						'BG' => esc_html__( 'Bulgaria', 'uap' ),
						'BF' => esc_html__( 'Burkina Faso', 'uap' ),
						'BI' => esc_html__( 'Burundi', 'uap' ),
						'KH' => esc_html__( 'Cambodia', 'uap' ),
						'CM' => esc_html__( 'Cameroon', 'uap' ),
						'CA' => esc_html__( 'Canada', 'uap' ),
						'CV' => esc_html__( 'Cape Verde', 'uap' ),
						'KY' => esc_html__( 'Cayman Islands', 'uap' ),
						'CF' => esc_html__( 'Central African Republic', 'uap' ),
						'TD' => esc_html__( 'Chad', 'uap' ),
						'CL' => esc_html__( 'Chile', 'uap' ),
						'CN' => esc_html__( 'China', 'uap' ),
						'CX' => esc_html__( 'Christmas Island', 'uap' ),
						'CC' => esc_html__( 'Cocos (Keeling) Islands', 'uap' ),
						'CO' => esc_html__( 'Colombia', 'uap' ),
						'KM' => esc_html__( 'Comoros', 'uap' ),
						'CG' => esc_html__( 'Congo (Brazzaville)', 'uap' ),
						'CD' => esc_html__( 'Congo (Kinshasa)', 'uap' ),
						'CK' => esc_html__( 'Cook Islands', 'uap' ),
						'CR' => esc_html__( 'Costa Rica', 'uap' ),
						'HR' => esc_html__( 'Croatia', 'uap' ),
						'CU' => esc_html__( 'Cuba', 'uap' ),
						'CW' => esc_html__( 'Cura&ccedil;ao', 'uap' ),
						'CY' => esc_html__( 'Cyprus', 'uap' ),
						'CZ' => esc_html__( 'Czech Republic', 'uap' ),
						'DK' => esc_html__( 'Denmark', 'uap' ),
						'DJ' => esc_html__( 'Djibouti', 'uap' ),
						'DM' => esc_html__( 'Dominica', 'uap' ),
						'DO' => esc_html__( 'Dominican Republic', 'uap' ),
						'EC' => esc_html__( 'Ecuador', 'uap' ),
						'EG' => esc_html__( 'Egypt', 'uap' ),
						'SV' => esc_html__( 'El Salvador', 'uap' ),
						'GQ' => esc_html__( 'Equatorial Guinea', 'uap' ),
						'ER' => esc_html__( 'Eritrea', 'uap' ),
						'EE' => esc_html__( 'Estonia', 'uap' ),
						'ET' => esc_html__( 'Ethiopia', 'uap' ),
						'FK' => esc_html__( 'Falkland Islands', 'uap' ),
						'FO' => esc_html__( 'Faroe Islands', 'uap' ),
						'FJ' => esc_html__( 'Fiji', 'uap' ),
						'FI' => esc_html__( 'Finland', 'uap' ),
						'FR' => esc_html__( 'France', 'uap' ),
						'GF' => esc_html__( 'French Guiana', 'uap' ),
						'PF' => esc_html__( 'French Polynesia', 'uap' ),
						'TF' => esc_html__( 'French Southern Territories', 'uap' ),
						'GA' => esc_html__( 'Gabon', 'uap' ),
						'GM' => esc_html__( 'Gambia', 'uap' ),
						'GE' => esc_html__( 'Georgia', 'uap' ),
						'DE' => esc_html__( 'Germany', 'uap' ),
						'GH' => esc_html__( 'Ghana', 'uap' ),
						'GI' => esc_html__( 'Gibraltar', 'uap' ),
						'GR' => esc_html__( 'Greece', 'uap' ),
						'GL' => esc_html__( 'Greenland', 'uap' ),
						'GD' => esc_html__( 'Grenada', 'uap' ),
						'GP' => esc_html__( 'Guadeloupe', 'uap' ),
						'GU' => esc_html__( 'Guam', 'uap' ),
						'GT' => esc_html__( 'Guatemala', 'uap' ),
						'GG' => esc_html__( 'Guernsey', 'uap' ),
						'GN' => esc_html__( 'Guinea', 'uap' ),
						'GW' => esc_html__( 'Guinea-Bissau', 'uap' ),
						'GY' => esc_html__( 'Guyana', 'uap' ),
						'HT' => esc_html__( 'Haiti', 'uap' ),
						'HM' => esc_html__( 'Heard Island and McDonald Islands', 'uap' ),
						'HN' => esc_html__( 'Honduras', 'uap' ),
						'HK' => esc_html__( 'Hong Kong', 'uap' ),
						'HU' => esc_html__( 'Hungary', 'uap' ),
						'IS' => esc_html__( 'Iceland', 'uap' ),
						'IN' => esc_html__( 'India', 'uap' ),
						'ID' => esc_html__( 'Indonesia', 'uap' ),
						'IR' => esc_html__( 'Iran', 'uap' ),
						'IQ' => esc_html__( 'Iraq', 'uap' ),
						'IE' => esc_html__( 'Republic of Ireland', 'uap' ),
						'IM' => esc_html__( 'Isle of Man', 'uap' ),
						'IL' => esc_html__( 'Israel', 'uap' ),
						'IT' => esc_html__( 'Italy', 'uap' ),
						'CI' => esc_html__( 'Ivory Coast', 'uap' ),
						'JM' => esc_html__( 'Jamaica', 'uap' ),
						'JP' => esc_html__( 'Japan', 'uap' ),
						'JE' => esc_html__( 'Jersey', 'uap' ),
						'JO' => esc_html__( 'Jordan', 'uap' ),
						'KZ' => esc_html__( 'Kazakhstan', 'uap' ),
						'KE' => esc_html__( 'Kenya', 'uap' ),
						'KI' => esc_html__( 'Kiribati', 'uap' ),
						'KW' => esc_html__( 'Kuwait', 'uap' ),
						'KG' => esc_html__( 'Kyrgyzstan', 'uap' ),
						'LA' => esc_html__( 'Laos', 'uap' ),
						'LV' => esc_html__( 'Latvia', 'uap' ),
						'LB' => esc_html__( 'Lebanon', 'uap' ),
						'LS' => esc_html__( 'Lesotho', 'uap' ),
						'LR' => esc_html__( 'Liberia', 'uap' ),
						'LY' => esc_html__( 'Libya', 'uap' ),
						'LI' => esc_html__( 'Liechtenstein', 'uap' ),
						'LT' => esc_html__( 'Lithuania', 'uap' ),
						'LU' => esc_html__( 'Luxembourg', 'uap' ),
						'MO' => esc_html__( 'Macao S.A.R., China', 'uap' ),
						'MK' => esc_html__( 'Macedonia', 'uap' ),
						'MG' => esc_html__( 'Madagascar', 'uap' ),
						'MW' => esc_html__( 'Malawi', 'uap' ),
						'MY' => esc_html__( 'Malaysia', 'uap' ),
						'MV' => esc_html__( 'Maldives', 'uap' ),
						'ML' => esc_html__( 'Mali', 'uap' ),
						'MT' => esc_html__( 'Malta', 'uap' ),
						'MH' => esc_html__( 'Marshall Islands', 'uap' ),
						'MQ' => esc_html__( 'Martinique', 'uap' ),
						'MR' => esc_html__( 'Mauritania', 'uap' ),
						'MU' => esc_html__( 'Mauritius', 'uap' ),
						'YT' => esc_html__( 'Mayotte', 'uap' ),
						'MX' => esc_html__( 'Mexico', 'uap' ),
						'FM' => esc_html__( 'Micronesia', 'uap' ),
						'MD' => esc_html__( 'Moldova', 'uap' ),
						'MC' => esc_html__( 'Monaco', 'uap' ),
						'MN' => esc_html__( 'Mongolia', 'uap' ),
						'ME' => esc_html__( 'Montenegro', 'uap' ),
						'MS' => esc_html__( 'Montserrat', 'uap' ),
						'MA' => esc_html__( 'Morocco', 'uap' ),
						'MZ' => esc_html__( 'Mozambique', 'uap' ),
						'MM' => esc_html__( 'Myanmar', 'uap' ),
						'NA' => esc_html__( 'Namibia', 'uap' ),
						'NR' => esc_html__( 'Nauru', 'uap' ),
						'NP' => esc_html__( 'Nepal', 'uap' ),
						'NL' => esc_html__( 'Netherlands', 'uap' ),
						'AN' => esc_html__( 'Netherlands Antilles', 'uap' ),
						'NC' => esc_html__( 'New Caledonia', 'uap' ),
						'NZ' => esc_html__( 'New Zealand', 'uap' ),
						'NI' => esc_html__( 'Nicaragua', 'uap' ),
						'NE' => esc_html__( 'Niger', 'uap' ),
						'NG' => esc_html__( 'Nigeria', 'uap' ),
						'NU' => esc_html__( 'Niue', 'uap' ),
						'NF' => esc_html__( 'Norfolk Island', 'uap' ),
						'MP' => esc_html__( 'Northern Mariana Islands', 'uap' ),
						'KP' => esc_html__( 'North Korea', 'uap' ),
						'NO' => esc_html__( 'Norway', 'uap' ),
						'OM' => esc_html__( 'Oman', 'uap' ),
						'PK' => esc_html__( 'Pakistan', 'uap' ),
						'PS' => esc_html__( 'Palestinian Territory', 'uap' ),
						'PA' => esc_html__( 'Panama', 'uap' ),
						'PG' => esc_html__( 'Papua New Guinea', 'uap' ),
						'PY' => esc_html__( 'Paraguay', 'uap' ),
						'PE' => esc_html__( 'Peru', 'uap' ),
						'PH' => esc_html__( 'Philippines', 'uap' ),
						'PN' => esc_html__( 'Pitcairn', 'uap' ),
						'PL' => esc_html__( 'Poland', 'uap' ),
						'PT' => esc_html__( 'Portugal', 'uap' ),
						'PR' => esc_html__( 'Puerto Rico', 'uap' ),
						'QA' => esc_html__( 'Qatar', 'uap' ),
						'RE' => esc_html__( 'Reunion', 'uap' ),
						'RO' => esc_html__( 'Romania', 'uap' ),
						'RU' => esc_html__( 'Russia', 'uap' ),
						'RW' => esc_html__( 'Rwanda', 'uap' ),
						'BL' => esc_html__( 'Saint Barth&eacute;lemy', 'uap' ),
						'SH' => esc_html__( 'Saint Helena', 'uap' ),
						'KN' => esc_html__( 'Saint Kitts and Nevis', 'uap' ),
						'LC' => esc_html__( 'Saint Lucia', 'uap' ),
						'MF' => esc_html__( 'Saint Martin (French part)', 'uap' ),
						'SX' => esc_html__( 'Saint Martin (Dutch part)', 'uap' ),
						'PM' => esc_html__( 'Saint Pierre and Miquelon', 'uap' ),
						'VC' => esc_html__( 'Saint Vincent and the Grenadines', 'uap' ),
						'SM' => esc_html__( 'San Marino', 'uap' ),
						'ST' => esc_html__( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'uap' ),
						'SA' => esc_html__( 'Saudi Arabia', 'uap' ),
						'SN' => esc_html__( 'Senegal', 'uap' ),
						'RS' => esc_html__( 'Serbia', 'uap' ),
						'SC' => esc_html__( 'Seychelles', 'uap' ),
						'SL' => esc_html__( 'Sierra Leone', 'uap' ),
						'SG' => esc_html__( 'Singapore', 'uap' ),
						'SK' => esc_html__( 'Slovakia', 'uap' ),
						'SI' => esc_html__( 'Slovenia', 'uap' ),
						'SB' => esc_html__( 'Solomon Islands', 'uap' ),
						'SO' => esc_html__( 'Somalia', 'uap' ),
						'ZA' => esc_html__( 'South Africa', 'uap' ),
						'GS' => esc_html__( 'South Georgia/Sandwich Islands', 'uap' ),
						'KR' => esc_html__( 'South Korea', 'uap' ),
						'SS' => esc_html__( 'South Sudan', 'uap' ),
						'ES' => esc_html__( 'Spain', 'uap' ),
						'LK' => esc_html__( 'Sri Lanka', 'uap' ),
						'SD' => esc_html__( 'Sudan', 'uap' ),
						'SR' => esc_html__( 'Suriname', 'uap' ),
						'SJ' => esc_html__( 'Svalbard and Jan Mayen', 'uap' ),
						'SZ' => esc_html__( 'Swaziland', 'uap' ),
						'SE' => esc_html__( 'Sweden', 'uap' ),
						'CH' => esc_html__( 'Switzerland', 'uap' ),
						'SY' => esc_html__( 'Syria', 'uap' ),
						'TW' => esc_html__( 'Taiwan', 'uap' ),
						'TJ' => esc_html__( 'Tajikistan', 'uap' ),
						'TZ' => esc_html__( 'Tanzania', 'uap' ),
						'TH' => esc_html__( 'Thailand', 'uap' ),
						'TL' => esc_html__( 'Timor-Leste', 'uap' ),
						'TG' => esc_html__( 'Togo', 'uap' ),
						'TK' => esc_html__( 'Tokelau', 'uap' ),
						'TO' => esc_html__( 'Tonga', 'uap' ),
						'TT' => esc_html__( 'Trinidad and Tobago', 'uap' ),
						'TN' => esc_html__( 'Tunisia', 'uap' ),
						'TR' => esc_html__( 'Turkey', 'uap' ),
						'TM' => esc_html__( 'Turkmenistan', 'uap' ),
						'TC' => esc_html__( 'Turks and Caicos Islands', 'uap' ),
						'TV' => esc_html__( 'Tuvalu', 'uap' ),
						'UG' => esc_html__( 'Uganda', 'uap' ),
						'UA' => esc_html__( 'Ukraine', 'uap' ),
						'AE' => esc_html__( 'United Arab Emirates', 'uap' ),
						'GB' => esc_html__( 'United Kingdom (UK)', 'uap' ),
						'US' => esc_html__( 'United States (US)', 'uap' ),
						'UM' => esc_html__( 'United States (US) Minor Outlying Islands', 'uap' ),
						'VI' => esc_html__( 'United States (US) Virgin Islands', 'uap' ),
						'UY' => esc_html__( 'Uruguay', 'uap' ),
						'UZ' => esc_html__( 'Uzbekistan', 'uap' ),
						'VU' => esc_html__( 'Vanuatu', 'uap' ),
						'VA' => esc_html__( 'Vatican', 'uap' ),
						'VE' => esc_html__( 'Venezuela', 'uap' ),
						'VN' => esc_html__( 'Vietnam', 'uap' ),
						'WF' => esc_html__( 'Wallis and Futuna', 'uap' ),
						'EH' => esc_html__( 'Western Sahara', 'uap' ),
						'WS' => esc_html__( 'Samoa', 'uap' ),
						'YE' => esc_html__( 'Yemen', 'uap' ),
						'ZM' => esc_html__( 'Zambia', 'uap' ),
						'ZW' => esc_html__( 'Zimbabwe', 'uap' )
		);
	}

endif;

if (!function_exists('indeed_debug_var')):
function indeed_debug_var($variable){
	/*
	 * print the array into '<pre>' tags
	 * @param array, string, int ... anything
	 * @return none (echo)
	 */
	 if (is_array($variable) || is_object($variable)){
		 echo esc_uap_content('<pre>');
		 print_r($variable);
		 echo esc_uap_content('</pre>');
	 } else {
	 	var_dump($variable);
	 }
}
endif;


if (!function_exists('uap_reorder_menu_items')):
function uap_reorder_menu_items($order=array(), $array=array()){
	/*
	 * @param array, array
	 * @return array
	 */
	 if (!empty($order) && is_array($order)){
		 $return_array = array();
		 foreach ($order as $key=>$value){
		 	 if (isset($array[$key])){
		 	 	 $return_array[$key] = $array[$key];
				 unset($array[$key]);
		 	 }
		 }
		 if (!empty($array)){
		 	$return_array = array_merge($return_array, $array);
		 }
		 return $return_array;
	 }
	 return $array;
}
endif;

if (!function_exists('uap_format_price_and_currency')):
function uap_format_price_and_currency($currency='', $price_value=''){
	/*
	 * @param string, string
	 * @return string
	 */
	 if ( $price_value === '' || $price_value === null || $price_value === false ){
		 	return $price_value;
	 }
	 $output = '';
	 $possition = get_option( 'uap_currency_position' , 'left' );
	 if ( empty( $possition ) ){
	 		$possition = 'left';
	 }

	 $settings = [
		 							'uap_num_of_decimals'			=> get_option( 'uap_num_of_decimals', 2 ),
							 		'uap_thousands_separator'	=> get_option( 'uap_thousands_separator', ',' ),
									'uap_decimals_separator'	=> get_option( 'uap_decimals_separator', '.' ),
	 ];
	 $settings['uap_num_of_decimals'] = (int)$settings['uap_num_of_decimals'];
	 // force separator if its case
	 if ( $settings['uap_thousands_separator'] === '' ){
		  $settings['uap_thousands_separator'] = ',';
	 }
	 if ( $settings['uap_decimals_separator'] === '' ){
		  $settings['uap_decimals_separator'] = '.';
	 }

   $price_value = number_format( $price_value, $settings['uap_num_of_decimals'], $settings['uap_decimals_separator'], $settings['uap_thousands_separator'] );



	 switch ( $possition ){
		 case 'left':
		 		$output = $currency . $price_value;
		 		break;
			case 'left_space':
				$output = $currency . ' ' . $price_value;
				break;
			case 'right':
				$output = $price_value . $currency;
				break;
			case 'right_space':
				$output = $price_value . ' ' . $currency;
				break;
	 }
	 return $output;
}
endif;

if ( !function_exists( 'uapCurrency' ) ):
function uapCurrency( $currency='' )
{
		if ( $currency === '' ){
				$currency = get_option( 'uap_currency' );
		}
		switch ( $currency ){
			 case 'USD':
				 $currency = '&#36;';
				 break;
			 case 'EUR':
				 $currency = '&#8364;';
				 break;
			 case 'GBP':
				 $currency = '&#163;';
				 break;
		}
		return $currency;
}
endif;

if (!function_exists('uap_get_array_key_for_subarray_element')):
function uap_get_array_key_for_subarray_element($haystack=array(), $needle='', $value=''){
	/*
	 * @param array (where to search)
	 * @param string (search key)
	 * @param string (search value)
	 * @return int (-1 if not found)
	 */
	foreach ($haystack as $key=>$array){
		if ($array[$needle]==$value){
			return $key;
		}
	}
	return -1;
}
endif;

/**
 * Convert array of objects into array of array
 * @param mixed (array or object)
 * @return array
 */
if (!function_exists('indeed_convert_to_array')):
function indeed_convert_to_array($input=null){
	if ($input){
		foreach ($input as $object){
			$array[] = (array)$object;
		}
		return $array;
	}
	return $input;
}
endif;

if (!function_exists('uap_empty_qr_images')):
function uap_empty_qr_images(){
		$path = UAP_PATH . 'classes/services/qrcode/images/';
		if ($handle = opendir($path)) {
				while (false !== ($file = readdir($handle))){
						$filetime = filectime($path.'/'.$file)+3600;
						$time = time();
				    if ($filetime && $time-$filetime >= 0) {
				      if (preg_match('/\.png$/i', $file)) {
				        unlink($path.'/'.$file);
				      }
				    }
				}
		}
}
endif;

if (!function_exists('indeed_get_uid')):
function indeed_get_uid(){
		global $current_user;
		// 8.6
		if ( current_user_can('administrator') && isset( $_GET['aid'] ) ){
				global $indeed_db;
				$uid = $indeed_db->get_uid_by_affiliate_id( sanitize_text_field( $_GET['aid'] ) );
				return (int)$uid;
		}
		// end of 8.6
		if (isset($current_user->ID)){
				return $current_user->ID;
		}
		return 0;
}
endif;

if (!function_exists('uap_get_uid')):
function uap_get_uid(){
		global $current_user;
		// 8.6
		if ( current_user_can('administrator') && isset( $_GET['aid'] ) ){
				global $indeed_db;
				$uid = $indeed_db->get_uid_by_affiliate_id( sanitize_text_field( $_GET['aid'] ) );
				return (int)$uid;
		}
		// end of 8.6
		if (isset($current_user->ID)){
				return $current_user->ID;
		}
		return 0;
}
endif;

if (!function_exists('dd')):
function dd($variable)
{
		indeed_debug_var($variable);
		die;
}
endif;

if ( !function_exists('indeed_get_current_language_code') ):
function indeed_get_current_language_code()
{
		$languageCode = get_locale();
		if ( !$languageCode ){
				return false;
		}
		$language = explode( '_', $languageCode );
		if ( isset($language[0]) ){
				return $language[0];
		}
		return $languageCode;
}
endif;

if ( !function_exists( 'uapIsRegisterPage' ) ):
function uapIsRegisterPage( $url )
{
		$registerPage = get_option('uap_general_register_default_page');
		if ( !$registerPage || $registerPage==-1 ){
				return false;
		}
		$permalink = get_permalink($registerPage);
		if ( empty( $permalink ) || $permalink == '' ){
				return false;
		}
		if ( strpos( $url, $permalink) !== false ){
				return true;
		}
		return false;
}
endif;

if ( !function_exists( 'uap_generate_color_hex' ) ):
/**
 * @param none
 * @return string
 */
function uap_generate_color_hex()
{
	$colors = [
			'#0a9fd8',
			'#38cbcb',
			'#27bebe',
			'#0bb586',
			'#94c523',
			'#6a3da3',
			'#f1505b',
			'#ee3733',
			'#f36510',
			'#f8ba01',
	];
	return $colors[rand(0, (count($colors)-1) )];
}
endif;

if ( !function_exists( 'indeedIsAdmin' ) ):
function indeedIsAdmin()
{
		global $current_user;
		if ( empty( $current_user->ID ) ){
				return false;
		}
		if ( is_super_admin( $current_user->ID ) ){
				return true;
		}
		$userData = get_userdata( $current_user->ID );
		if ( !$userData || empty( $userData->roles ) ){
				return false;
		}
		if ( !in_array( 'administrator', $userData->roles ) ){
				return false;
		}
		return true;
}
endif;

if ( !function_exists( 'uapAdminVerifyNonce' ) ):
function uapAdminVerifyNonce()
{
		$nonce = isset( $_SERVER['HTTP_X_CSRF_UAP_ADMIN_TOKEN'] ) ? $_SERVER['HTTP_X_CSRF_UAP_ADMIN_TOKEN']	: '';
		if ( wp_verify_nonce( $nonce, 'uapAdminNonce' ) ) {
				return true;
		}
		return false;
}
endif;

if ( !function_exists( 'uapPublicVerifyNonce' ) ):
function uapPublicVerifyNonce()
{
		$nonce = isset( $_SERVER['HTTP_X_CSRF_UAP_TOKEN'] ) ? $_SERVER['HTTP_X_CSRF_UAP_TOKEN']	: '';
		if ( wp_verify_nonce( $nonce, 'uapPublicNonce' ) ) {
				return true;
		}
		return false;
}
endif;

if ( !function_exists('uapInputNumerStep') ):
function uapInputNumerStep()
{
		$number = get_option( 'uap_num_of_decimals' );
		if ( $number === FALSE ){
				return 0.01;
		}
		if ( $number == 0 ){
			return 1;
		}
		$j = 1;
		for ( $i=0; $i<$number; $i++ ){
				$j = $j / 10;
		}
		return $j;
}
endif;

if ( !function_exists('getDefaultCountry') ):
function getDefaultCountry()
{
		$locale = 'US';

		$defaultcountry = get_option( 'uap_defaultcountry' );
		if(empty($defaultcountry)){
				$locale = get_locale();
				$locale_arr = explode( '_' , $locale);

				if(is_array($locale_arr) && isset($locale_arr[1]))
					$locale = $locale_arr[1];
				elseif(isset($locale_arr[0]))
					$locale = $locale_arr[0];
		}else{
			$locale = $defaultcountry;
		}
		return strtolower ($locale);
}
endif;

/**
 * @param array
 * @return array
 */
if ( !function_exists( 'indeedFilterVarArrayElements' ) ):
function indeedFilterVarArrayElements( $data=[] )
{
		if ( !is_array( $data ) || count( $data ) == 0 ){
				return $data;
		}
		foreach ( $data as $key => $value ){
				if ( is_array( $value ) ){
						$data[$key] = indeedFilterVarArrayElements( $value );
				} else {
						$data[$key] = ( isset( $data[$key] ) ) ? sanitize_text_field( $data[$key] ) : false;
				}
				//$data[$key] = filter_var( $value, FILTER_SANITIZE_STRING );
		}
		return $data;
}
endif;

if ( !function_exists('indeed_get_unixtimestamp_with_timezone') ):
/**
 * Return unixtimestamp with the timezone set in Wp Admin dashboard.
 * @param int ( timestamp )
 * @return int
 */
function indeed_get_unixtimestamp_with_timezone( $time='' )
{
		if ( '' == $time ){
				$time = time();
		}
		$date = new \DateTime();
		$date->setTimestamp( $time );
		$date->setTimezone( new \DateTimeZone('UTC') );
		$time = $date->format('Y-m-d H:i:s');
		$time = get_date_from_gmt( $time );
		return strtotime( $time );
}
endif;

if ( !function_exists('uap_is_ihc_active') ):
function uap_is_ihc_active(){
	/*
	 * @param none
	 * @return boolean
	 */
	 if (!function_exists('is_plugin_active')){
	 	include_once ABSPATH . 'wp-admin/includes/plugin.php';
	 }
	 if (file_exists(WP_CONTENT_DIR . '/plugins/indeed-membership-pro/indeed-membership-pro.php') && is_plugin_active('indeed-membership-pro/indeed-membership-pro.php')){
		if (get_option('ihc_license_set')==1){
			return TRUE;
		}
	}
	return FALSE;
}
endif;

if ( !function_exists( 'uapSearchForWooAndExtension' ) ):
/**
 * @param none
 * @return array
 */
function uapSearchForWooAndExtension()
{
    // search for woo
    if ( !defined( 'WC_PLUGIN_FILE' ) ){
        // woo is not installed
        return;
    }

    // search for our add-on
    $exists = file_exists( WP_CONTENT_DIR . '/plugins/uap-woo-discounts/uap-woo-discounts.php');
		$disabled = get_option( 'uap_disable_woo_mk_message', false );
    if ( $exists === false && ( $disabled === false || (int)$disabled + ( 15 * 24 * 60 * 60 ) < time() ) ){
				$view = new \Indeed\Uap\IndeedView();
				$html = $view->setTemplate( UAP_PATH . 'admin/views/woo-message.php' )
										 ->setContentData( [], true )
										 ->getOutput();
				return [
									'status'      => 1,
									'message'     => $html,
				];
    }
    return [
              'status'        => 0,
              'message'       => ''
    ];
}
endif;

if ( !function_exists( 'uapSearchForMyCredAndExtension' ) ):
/**
 * @param none
 * @return array
 */
function uapSearchForMyCredAndExtension()
{
    // search for mycred
    if ( !defined( 'myCRED_VERSION' ) ){
        // mycred is not installed
        return;
    }
    // search for our add-on
    $exists = file_exists( WP_CONTENT_DIR . '/plugins/uap-my-cred/uap-my-cred.php');
		$disabled = get_option( 'uap_disable_mycred_mk_message', false );
    if ( $exists === false && ( $disabled === false || (int)$disabled + ( 15 * 24 * 60 * 60 ) < time() ) ){
				$view = new \Indeed\Uap\IndeedView();
				$html = $view->setTemplate( UAP_PATH . 'admin/views/my-cred-message.php' )
										 ->setContentData( [], true )
										 ->getOutput();
				return [
									'status'      => 1,
									'message'     => $html,
				];
    }
    return [
              'status'        => 0,
              'message'       => ''
    ];
}
endif;

if (!function_exists('indeed_get_plugin_version')):
function indeed_get_plugin_version( $base_file_path='' ){
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugin_data = get_plugin_data( $base_file_path, false, false);
		return $plugin_data['Version'];
}
endif;

if ( !function_exists('esc_uap_content') ):
/*
 * This function is used to filter the html output.
 */
function esc_uap_content( $string='' )
{
		return $string;
}
endif;

if ( !function_exists( 'uap_sanitize_array' ) ):
function uap_sanitize_array( $input=[] )
{
		$output = [];
		if ( !is_array( $input ) ){
				return sanitize_text_field($input);
		}
		foreach ( $input as $key => $val ) {
				if ( is_array( $val ) ){
						$output[ $key ] = uap_sanitize_array( $val );
				} else {
						$output[ $key ] = ( isset( $input[ $key ] ) ) ? sanitize_text_field( $val ) : false;
				}
		}
		return $output;
}
endif;

if ( !function_exists( 'uap_sanitize_textarea_array' ) ):
function uap_sanitize_textarea_array( $input=[] )
{
		$output = [];
		if ( !is_array( $input ) ){
				return wp_kses_post($input);
		}
		foreach ( $input as $key => $val ) {
				if ( is_array( $val ) ){
						$output[ $key ] = uap_sanitize_textarea_array( $val );
				} else {
						$output[ $key ] = ( isset( $input[ $key ] ) ) ? wp_kses_post( $val ) : false;
				}
		}
		return $output;
}
endif;

if ( !function_exists( 'indeed_sanitize_array' ) ):
function indeed_sanitize_array( $input=[] )
{
    $output = [];
    if ( !is_array( $input ) ){
        return sanitize_text_field($input);
    }
    foreach ( $input as $key => $val ) {
        if ( is_array( $val ) ){
            $output[ $key ] = indeed_sanitize_array( $val );
        } else {
            $output[ $key ] = ( isset( $input[ $key ] ) ) ? sanitize_text_field( $val ) : false;
        }
    }
    return $output;
}
endif;

if ( !function_exists( 'indeedEscapeArrayForQuery' ) ):
function indeedEscapeArrayForQuery( $array=[] ){
    global $wpdb;
    $prepered = [];
    foreach ( $array as $key => $value ){
        if ( is_numeric( $value ) ){
            	$prepered[] = $wpdb->prepare( '%d', $value );
        } else {
							$prepered[] = $wpdb->prepare( '%s', $value );
				}
    }
    return implode(',', $prepered );
}
endif;

/**
 * @param int
 * @return string
 */
if ( !function_exists( 'uap_flag_for_affiliate' ) ):
function uap_flag_for_affiliate($user_id=0)
{
	 if ($user_id){
		 	return '';
	 }
	 $flag_src = get_user_meta( $user_id, 'uap_country', true );
	 if ($flag_src){
		$countries = uap_get_countries();
		$country = $countries[strtoupper($flag_src)];
		$title = (empty($country)) ? '' : $country;
		return '<span class="uap-affiliate-flag-wrapp"><img src="' . UAP_URL . 'assets/flags/' . $flag_src . '.svg" title="' . $title . '" class="uap-affiliate-admin-flag" /></span>';
	 }
	 return '';
}
endif;

/*
$dayname = date('w', time() ); // will return 0-6
*/
if ( !function_exists( 'uapDaysOfWeek' ) ):
function uapDaysOfWeek()
{
    return [
              1 => esc_html__( 'Monday', 'uap' ),
              2 => esc_html__( 'Tuesday', 'uap' ),
              3 => esc_html__( 'Wednesday', 'uap' ),
              4 => esc_html__( 'Thursday', 'uap' ),
              5 => esc_html__( 'Friday', 'uap' ),
              6 => esc_html__( 'Saturday', 'uap' ),
              0 => esc_html__( 'Sunday', 'uap' ),
    ];
}
endif;

if ( !function_exists( 'uapUnixtimestampForNextDayNumber' ) ):
function uapUnixtimestampForNextDayNumber( $dayNumber=0 )
{
    $currentTime = indeed_get_unixtimestamp_with_timezone();
    $days = [
              1 => 'Monday',
              2 => 'Tuesday',
              3 => 'Wednesday',
              4 => 'Thursday',
              5 => 'Friday',
              6 => 'Saturday',
              0 => 'Sunday',
    ];
    $currentDayNumber = date('w', $currentTime );
    if ( $dayNumber === (int)$currentDayNumber ){
        // return current day timestamp
        return $currentTime;
    } else {

      $target = strtotime("next ". $days[$dayNumber] );
      return indeed_get_unixtimestamp_with_timezone( $target );
    }
}
endif;

if ( !function_exists( 'uapGetArrayDate' ) ):
function uapGetArrayDate( $start, $end, $step )
{
    $dates = [];
    $current = $start;
    switch ( $step ){
				case 'hour':
          $end = strtotime( $end );
          $current = strtotime( $current );
          if ( $end === $current ){
              $end = $end + ( 24 * 60 * 60 );
          }
          while ( $current <= $end ){
              $dates[ date('Y-m-d H:i:s', $current ) ] = 0;
              $current = strtotime( "+1 hour", $current );
          }
          return $dates;
					break;
        case 'day':
            while ( $current <= $end ){
                $dates[date('Y-m-d', strtotime( $current ) )] = 0;
                $current = date('Y-m-d', strtotime("+1 day", strtotime($current) ) );
            }
            return $dates;
            break;
        case 'week':
            while ( $current <= $end ){
                $dates[date('Y-m-d', strtotime( $current ) )] = 0;
                $current = date('Y-m-d', strtotime("+1 week", strtotime($current) ) );
            }
            return $dates;
            break;
        case 'month':
            while ( $current <= $end ){
                $dates[date('Y-m', strtotime( $current ) )] = 0;
                $current = date('Y-m', strtotime("+1 month", strtotime($current) ) );
            }
            return $dates;
            break;
    }
}
endif;

if ( !function_exists('uapArrayIntersectKeepKeys' ) ):
function uapArrayIntersectKeepKeys( $arrayA, $arrayB )
{
	foreach ( $arrayA as $dataKey => $dataValue ){
			if (isset( $arrayB[$dataKey])){
					$arrayA[$dataKey] = $arrayB[$dataKey];
			}
	}
	return $arrayA;
}
endif;

if ( !function_exists( 'uapSiteDomain' ) ):
function uapSiteDomain()
{
		$domain = get_option( 'siteurl' );
		$domain = str_replace('http://', '', $domain);
		$domain = str_replace('https://', '', $domain);
		$domain = str_replace('www.', '', $domain);
		return $domain;
}
endif;

if ( !function_exists( 'indeedImplodeKeys') ):
function indeedImplodeKeys( $input=[], $delimiter=',' )
{
		if ( !is_array( $input ) || count( $input ) < 1 ){
				return '';
		}
		$inputKeys = array_keys( $input );
		return implode( $delimiter, $inputKeys );
}
endif;

if ( !function_exists( 'uap_is_ulp_active' ) ):
function uap_is_ulp_active()
{
	if ( !function_exists( 'is_plugin_active' ) ){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if (is_plugin_active('indeed-learning-pro/indeed-learning-pro.php')){
			return true;
	}
	return false;
}
endif;

if ( !function_exists( 'uap_is_edd_active' ) ):
function uap_is_edd_active()
{
	if ( !function_exists( 'is_plugin_active' ) ){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' )  || is_plugin_active( 'easy-digital-downloads-pro/easy-digital-downloads.php') ){
			return true;
	}
	return false;
}
endif;

if ( !function_exists( 'uap_is_cf7_active' ) ):
function uap_is_cf7_active()
{
	if ( !function_exists( 'is_plugin_active' ) ){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ){
			return true;
	}
	return false;
}
endif;

if ( !function_exists( 'uap_is_wpforms_active' ) ):
function uap_is_wpforms_active()
{
	if ( !function_exists( 'is_plugin_active' ) ){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'wpforms/wpforms.php' ) ){
			return true;
	}
	return false;
}
endif;

if ( !function_exists( 'uap_is_ninjaforms_active' ) ):
function uap_is_ninjaforms_active()
{
	if ( !function_exists( 'is_plugin_active' ) ){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'ninja/ninja.php' ) ){
			return true;
	}
	return false;
}
endif;

if ( !function_exists( 'uap_is_ninjaformsdemo_active' ) ):
function uap_is_ninjaformsdemo_active()
{
	if ( !function_exists( 'is_plugin_active' ) ){
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'ninja-demo/ninja-demo.php' ) ){
			return true;
	}
	return false;
}
endif;
