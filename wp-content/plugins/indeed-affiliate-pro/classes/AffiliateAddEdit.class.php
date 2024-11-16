<?php
if(!class_exists('AffiliateAddEdit')){
	class AffiliateAddEdit{
		private $is_public = true;
		private $user_id = '';
		private $type = 'create';//create or edit
		private $action = '';// form action (url)
		private $user_data = array();
		private $tos = false;
		private $captcha = false;
		private $register_metas = array();
		private $errors = array();
		private $register_fields = '';
		private $disabled_submit_form = '';
		private $register_template = 'uap-register-1';
		private $display_type = 'display_admin';
		private $required_fields = array();
		private $current_rank = 0;
		private $global_css = '';
		private $global_js = '';
		private $exception_fields = array();
		private static $print_errors = array();
		private static $uap_error_register;
		private $set_username_automaticly = FALSE;
		private $set_password_automaticly = FALSE;
		private $send_password_via_mail = FALSE;
		private $shortcodes_attr = array();
		private $fields 				= [];

		public function __construct($args=array()){
			/*
			 * @param array - optional
			 * @return none
			 */
			global $indeed_db;
			$this->register_metas = array_merge($indeed_db->return_settings_from_wp_option('register', FALSE, FALSE), $indeed_db->return_settings_from_wp_option('register-msg', FALSE, FALSE), $indeed_db->return_settings_from_wp_option('register-custom-fields', FALSE, FALSE));
			$this->register_template = (empty($args['register_template'])) ? $this->register_metas['uap_register_template'] : $args['register_template'];

			/// INPUT VARIABLES
			if (!empty($args)){
				foreach($args as $k=>$v){
					$this->$k = $v;
				}
			}
			//8.6

			if ($this->is_public){
				if ($this->type=='create'){
					$this->display_type = 'display_public_reg';
				} else {
					$this->display_type = 'display_public_ap';
				}
			} else {
				$this->display_type = 'display_admin';
			}

			/// SET REGISTER FIELDS
			$this->register_fields = $indeed_db->register_get_custom_fields();//register fields
			ksort($this->register_fields);
			if ($this->type=='edit'){
				$key = uap_array_value_exists($this->register_fields, 'user_login', 'name');
				if ($key!==FALSE && isset($this->register_fields[$key])){
					unset($this->register_fields[$key]);
				}
			}

			// >= 8.6
			if ( $this->type === 'create' && !empty( $this->user_id) ){
					$key = uap_array_value_exists($this->register_fields, 'pass1', 'name');
					if ($key!==FALSE && isset($this->register_fields[$key])){
						unset($this->register_fields[$key]);
					}
					$key = uap_array_value_exists($this->register_fields, 'pass2', 'name');
					if ($key!==FALSE && isset($this->register_fields[$key])){
						unset($this->register_fields[$key]);
					}
					foreach ( $this->register_fields as $fieldId => $registerFieldArray ){
							if ( $registerFieldArray['native_wp'] ){
									$this->register_fields[$fieldId]['disabled'] = 1;
							}
					}
			}
			// end of >= 8.6

			/// SET CURRENT RANK
			$this->current_rank = get_option('uap_register_new_user_rank');
			if ($this->user_id && $indeed_db->get_affiliate_id_by_wpuid( $this->user_id ) ){
				// affiliate already has a rank
				$this->current_rank = $indeed_db->get_affiliate_rank(0, $this->user_id);
			}
		}

		/////////
		public function form(){
			/*
			 * @param none
			 * @return string
			 */

			/*extra fields that must be transalted:*/
			   esc_html__("Confirm Password", 'uap');
			   esc_html__("Last Name", 'uap');
			   esc_html__("First Name", 'uap');
			 /**/

			$this->userdata();

			$i = 0;

			$template_with_cols= array('uap-register-6', 'uap-register-11', 'uap-register-12', 'uap-register-13');
			if (in_array($this->register_template, $template_with_cols)){
				$return_data['count_register_fields'] = $this->count_register_fields();
			}

			$this->global_js = '';

			foreach ($this->register_fields as $v){
				$str = '';
				if ($v[$this->display_type]>0){
					$i++;
					switch ($v['name']){
						case 'tos':
							if ($this->tos){
								$disp_tos = $this->print_tos($v);
								if ($disp_tos != ''){
							 		$str .= $disp_tos;
							 		$this->required_fields[] = array('name' => 'tos', 'type' => 'checkbox');
							 		if (!empty(self::$print_errors['tos'])){
							 			$str .= '<div class="uap-register-notice">' . self::$print_errors['tos'] . '</div>';
							 		}
								}
							}
							break;
						case 'recaptcha':
							if ($this->captcha){
								$disp_captcha = $this->print_captcha($v);
								if ($disp_captcha != ''){
							 		$str .= $disp_captcha;
									if (!empty(self::$print_errors['captcha'])){
										$str .= '<div class="uap-register-notice">' . self::$print_errors['captcha'] . '</div>';
									}
								}
							}
							break;
						default:
							if ($this->is_public) {

								//========== PUBLIC
								$str .= $this->print_fields($v);
							} else {
								//========== ADMIN

								$notAvailableForAdminSection = [ 'uap_social_media', 'payment_select', 'confirm_email', 'pass2' ];
								if( in_array( $v['name'], $notAvailableForAdminSection ) ){
										break;//continue;
								}

								$disabled = '';
								if ( $this->type=='edit' && $v['name']=='user_login'){
									$disabled = 'disabled';
								}

								//FORM FIELD
								$parent_id = 'uap_reg_' . $v['type'] . '_' . rand(1,10000);
								$temp_type_class = 'uap-form-' . $v['type'];
								$str .= '<div class="uap-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
								$str .= '<label class="uap-labels-register">';

								if (isset($v['native_wp']) && $v['native_wp']){
									$str .= esc_html__($v['label'], 'uap');
								} else {
									$str .= uap_correct_text($v['label']);
								}
								if ($v['req']){
									$str .= '<span class="uap-color-red">*</span>';
								}
								$str .= '</label>';

								$val = '';
								if (isset($this->user_data[$v['name']])){
									$val = $this->user_data[$v['name']];
								}
								if (empty($val) && $v['type']=='plain_text'){ //maybe it's plain text
									$val = $v['plain_text_value'];
								}

								$multiple_values = FALSE;
								if (isset($v['values']) && $v['values']){
									//is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
									$multiple_values = uap_from_simple_array_to_k_v($v['values']);
								}

								if (empty($v['sublabel'])){
									$v['sublabel'] = '';
								}

								if (empty($v['class'])){
									$v['class'] = '';
								}

								$str .= uap_create_form_element(array( 'type' => $v['type'], 'name' => $v['name'], 'value' => $val,
										'disabled' => $disabled, 'multiple_values' => $multiple_values,
										'user_id' => $this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'] ));
								$str .= '</div>';
							}
							break;
					}//end of switch
				}
				$return_data['form_fields'][] = $str;
			}

			$return_data['hiddens'][] = $this->printNonce();

			if ($this->is_public){
				/*******************************PUBLIC****************************/

				//ACTIONS
				if ($this->type=='edit'){
					$return_data['hiddens'][] = uap_create_form_element(array('type'=>'hidden', 'name'=>'uapaction', 'value' => 'update' ));
				} else {
					$return_data['hiddens'][] = uap_create_form_element(array('type'=>'hidden', 'name'=>'uapaction', 'value' => 'register' ));
				}
			} else {
				/******************************** ADMIN ****************************/

				//Add Extra options for Affiliate edit page
				global $indeed_db;
				$return_data['form_fields'][] = $this->print_section_title();//select wp role
				$return_data['form_fields'][] = $this->print_wp_role();//select wp role
				$return_data['form_fields'][] = $this->select_rank();//select ranks
				$return_data['form_fields'][] = $this->print_overview_post_select();

				if ($indeed_db->is_magic_feat_enable('custom_affiliate_slug')){
					$return_data['form_fields'][] = $this->the_slug();// edit the slug
				}

				if ($this->user_id && $this->type=='edit'){//hide user id into the form for edit, only in admin
					$return_data['hiddens'][] = uap_create_form_element(array('type'=>'hidden', 'name'=>'user_id', 'value' => $this->user_id ));
				}
			}

			if ($this->type=='create'){
				$return_data['submit_button']= uap_create_form_element(array('type'=>'submit', 'name'=>'Submit', 'value' => esc_html__('Register', 'uap'),
						'class' => '', 'id'=>'uap_submit_bttn', 'disabled'=>$this->disabled_submit_form )); /// class button button-primary button-large
			} else {
				$return_data['submit_button']= uap_create_form_element(array('type'=>'submit', 'name'=>'Update', 'value' => esc_html__('Save Changes', 'uap'),
						 'class' => '', 'id'=>'uap_submit_bttn', 'disabled'=>$this->disabled_submit_form ));
			}

			if (count($this->exception_fields)>0){
				$return_data['hiddens'][] = '<input type="hidden" name="uap_exceptionsfields" id="uap_exceptionsfields" value="' . implode(',', $this->exception_fields) . '" />';
			}

			global $indeed_db;
			//Allow to change MLM Parent
			//&& ($this->type=='create' || !$indeed_db->mlm_get_parent($indeed_db->get_affiliate_id_by_wpuid($this->user_id)) )
			if (!$this->is_public && $indeed_db->is_magic_feat_enable('mlm') ){ //  && ($this->type=='create' || !$indeed_db->mlm_get_parent($indeed_db->get_affiliate_id_by_wpuid($this->user_id)) )
				$mlmParent = $indeed_db->mlm_get_parent($indeed_db->get_affiliate_id_by_wpuid($this->user_id));
				$return_data['form_fields'][] = uap_create_form_element(
																			array(
																				'type'						=>'uap_affiliate_autocomplete_field',
																				'label' 					=> esc_html__('Select a Parent for this Affiliate', 'uap'),
																				'field_style' 		=> '',
																				'title' 					=> esc_html__('Multi-Level Marketing (MLM) Setup', 'uap'),
																				'hidden_name' 		=> 'uap_affiliate_mlm_parent',
																				'exclude_user_id' => $this->user_id,
																				'value'						=> $mlmParent,
																				'text_name'				=> 'uap_affiliate_mlm_parent_slug'
																			)
				);
			}

			//wrapp it all in a form
			if ($this->type=='edit'){
				$return_data['form_name'] = "uap_edituser";
				$return_data['form_id'] = "uap_edituser";
			} else {
				$return_data['form_name'] = "uap_createuser";
				$return_data['form_id'] = "uap_createuser";
			}

			$return_data['css'] = $this->global_css;

			//AJAX CHECK FIELDS VALUES (ONLY FOR PUBLIC REGISTER)
			$return_data['js'] = '';
			if ($this->is_public && $this->type=='create'){
				$return_data['js'] .= 'var req_fields_arr = [];';
				$return_data['js'] .= 'jQuery(window).on( "load",function(){';
				foreach ($this->required_fields as $req_field){
					if (in_array($req_field['type'], array('text', 'textarea', 'number', 'password', 'date', 'conditional_text'))){
						$return_data['js'] .= 'jQuery(".uap-form-create-edit [name='.$req_field['name'].']").on("blur", function(){
							uapRegisterCheckViaAjax("'.$req_field['name'].'");
						});';
					}

					$return_data['js'] .= 'req_fields_arr.push("' . $req_field['name'] . '");
					';
				}
				$return_data['js'] .= 'jQuery(".uap-form-create-edit").on("submit", function() { /// live
							if (window.must_submit==1){
								return true;
							} else {
								uapRegisterCheckViaAjaxRec(req_fields_arr);
								return false;
							}
						});';
				$return_data['js'] .= '});';
			}
			$return_data['js'] .= $this->global_js;

			//return $str;
			return $return_data;
		}

		/**
		 * @param none
		 * @return string
		 */
		protected function printNonce()
		{
				if ( $this->is_public ){
						$nonce = wp_create_nonce( 'uap_user_add_edit_nonce' );
				} else {
						$nonce = wp_create_nonce( 'uap_admin_user_add_edit_nonce' );
				}
				return "<input type='hidden' name='uap_user_add_edit_nonce' value='$nonce' />";
		}


		/////////
		public function userdata(){
			/*
			 * @param none
			 * @return none
			 */
			$user_fields = $this->register_fields;
			if ($this->user_id){
				//getting user meta for id
				$data = get_userdata($this->user_id);
				if ($data){
					foreach ($user_fields as $user_field){
						$name = $user_field['name'];
						if ($user_field['native_wp']==1){
							//native wp field, get value from get_userdata ( $data object )
							if (isset($data->$name) && $data->$name){
								$this->user_data[ $name ] = $data->$name;
							}
						} else {
							//custom field, get value from get_user_meta()
							$this->user_data[ $name ] = get_user_meta($this->user_id, $name, true);
						}
					}
				}
				//user wp role
				if (isset($data->roles[0])){
					$this->user_data['role'] = $data->roles[0];
				}
			} else {
				//empty arr
					foreach ($user_fields as $user_field){
						$name = $user_field['name'];
						$this->user_data[$name] = '';

						if ($this->is_public && isset($_REQUEST[$name])){
							$this->user_data[$name] = uap_sanitize_array($_REQUEST[$name]);
						}
					}
				$this->user_data['role'] = '';
			}
		}
		private function print_section_title(){
			$str = '';
			$str .= '<div class="uap-additional-section-title">';
			$str .= '<h2>'.esc_html__('Additional Settings', 'uap').'</h2>';
			$str .= '<p>'.esc_html__('Extra settings available for Administration purpose', 'uap').'</p>';
			$str .= '</div>';
			return $str;
		}
		private function print_wp_role(){
			/*
			 * @param none
			 * @return string
			 */
			$str = '';
			$str .= '<div class="uap-form-line-register uap-form-select-role">';
			$str .= '<h4>'.esc_html__('WordPress Role', 'uap').'</h4>';
			$str .= '<p>'.esc_html__('If required, select a specific WordPress Role for the current Affiliate user', 'uap').'</p>';
			$str .= uap_create_form_element(
													array(  'type' => 'select',
															'name' => 'role',
															'value' => isset($this->user_data['role']) ? $this->user_data['role'] : '',
															'multiple_values' => uap_get_wp_roles_list(),
															'class' => '' )
													);
			$str .= '</div>';
			return $str;
		}

		////////
		private function select_rank(){
			/*
			 * RUN THIS ONLY ON ADMIN
			 * @param none
			 * @return string
			 */
			$output = '';
			if (!$this->is_public){
				global $indeed_db;
				$ranks = $indeed_db->get_rank_list();
				$ranks = array( 0 => esc_html__('None...', 'uap')) + $ranks;
				$output .= '<div class="uap-form-line-register uap-form-select-ranks">';
				$output .= '<h4>'.esc_html__("Affiliate's Rank", 'uap').'</h4>';
				$output .= '<p>'.esc_html__("Manually assign a particular Rank based on the affiliate's evolution or accomplishments", 'uap').'</p>';
				$output .= uap_create_form_element(
														array(  'type' => 'select',
																'name' => 'rank_id',
																'value' => $this->current_rank,
																'multiple_values' => $ranks,
																'class' => '' )
														);
				$output .= '</div>';
			}
			return $output;
		}

		private function the_slug(){
			/*
			 * @param none
			 * @return string
			 */
			$output = '';
			if (!$this->is_public){
				global $indeed_db;
				$value = $indeed_db->get_custom_slug_for_uid($this->user_id);
				$output .= '<div class="uap-form-line-register uap-form-select-slug">';
				$output .= '<h4>' . esc_html__('Affiliate Custom Slug', 'uap') . '</h4>';
				$output .= '<p>' . esc_html__('Create or change the custom slug available for current affiliate user', 'uap') . '</p>';
				$fieldSettings = 														array(  'type' => 'text',
																				'name' => 'uap_affiliate_custom_slug',
																				'value' => $value,
																				'class' => ''
																			);
				if ( $this->user_id === false || $this->user_id === '' || $this->user_id === 0 ){
						$fieldSettings['disabled'] = 'disabled';
				}
				$output .= uap_create_form_element( $fieldSettings );
				$output .= '</div>';
			}
			return $output;
		}

		private function edit_ap_check_conditional_logic($field_data=array()){
			$value = get_user_meta($this->user_id, $field_data['conditional_logic_corresp_field'], TRUE);

			if ($field_data['conditional_logic_cond_type']=='has'){
				//has value
				if ($field_data['conditional_logic_corresp_field_value']==$value){
					return 1;
				}
			} else {
				//contain value
				if (strpos($value, $field_data['conditional_logic_corresp_field_value'])!==FALSE){
					return 1;
				}
			}

			return 0;
		}

		private function check_for_conditional_logic($field_arr, $field_id){
			/*
			 * @param string, string
			 * @return none
			 */
			if (!$this->is_public){
				return;
			}
			if (!empty($field_arr['conditional_logic_corresp_field']) && $field_arr['conditional_logic_corresp_field']!=-1){
				//so this field is correlated with another

				////Js ACTION
				$key = uap_array_value_exists($this->register_fields, $field_arr['conditional_logic_corresp_field'], 'name');
				if ($key!==FALSE && !empty($this->register_fields[$key]['type'])){
					$show = (strcmp($field_arr['conditional_logic_show'], 'yes')) ? 0 : 1;

					if ($this->type=='edit'){
						if ($show){
							/// 'yes'
							$no_on_edit = $this->edit_ap_check_conditional_logic($field_arr);
						} else {
							/// 'no'
							$no_on_edit = !$this->edit_ap_check_conditional_logic($field_arr);
						}

					}

					switch ($this->register_fields[$key]['type']){
						case 'text':
						case 'textarea':
						case 'number':
						case 'password':
						case 'date':
						case 'conditional_text':
							$js_function = 'uapAjaxCheckFieldConditionOnblurOnclick("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", ' . $show . ');';
							$this->global_js .= '
								jQuery(".uap-form-create-edit [name='.$field_arr['conditional_logic_corresp_field'].']").on("blur", function(){
									' . $js_function . '
								});
							';
							break;
						case 'checkbox':
							$js_function = 'uapAjaxCheckOnClickFieldCondition("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", "checkbox", ' . $show . ');';
							$this->global_js .= '
								jQuery(".uap-form-create-edit [name=\''.$field_arr['conditional_logic_corresp_field'].'[]\'], .uap-form-create-edit [name='.$field_arr['conditional_logic_corresp_field'].']").on("click", function(){
									' . $js_function . '
								});
							';
							break;
						case 'radio':
							$js_function = 'uapAjaxCheckOnClickFieldCondition("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", "radio", ' . $show . ');';
							$this->global_js .= '
								jQuery(".uap-form-create-edit [name='.$field_arr['conditional_logic_corresp_field'].']").on("click", function(){
									' . $js_function . '
								});
							';
							break;
						case 'select':
							$js_function = 'uapAjaxCheckFieldConditionOnblurOnclick("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", ' . $show . ');';
							$this->global_js .= '
								jQuery(".uap-form-create-edit [name='.$field_arr['conditional_logic_corresp_field'].']").on("change", function(){
									' . $js_function . '
								});
							';
							break;
						case 'multi_select':
							$js_function = 'uapAjaxCheckCnChangeMultiselectFieldCondition("' . $field_arr['conditional_logic_corresp_field'] . '", "#' . $field_id . '", "' . $field_arr['name'] . '", ' . $show . ');';
							$this->global_js .= '
								jQuery(".uap-form-create-edit [name=\''.$field_arr['conditional_logic_corresp_field'].'[]\']").on("change", function(){
									' . $js_function . '
								});
							';
							break;
					}
					if (!empty($js_function)){
						$this->global_js .= 'window.addEventListener( "load", function(){' . $js_function . '});';
					}
				}


				//conditional logic & required => add new exception
				if ($field_arr['req']){
					$this->exception_fields[] = $field_arr['name'];
				}

				if (empty($show) || !empty($no_on_edit)){
					//we must hide this field and show only when correlated field it's completed with desired value
					$this->global_css .= "#$field_id{display: none;}";
				}
			}
		}

		private function print_fields($v=array()){
			/*
			 * @param array
			 * @return string
			 */

			$str = '';
			$disabled = '';
			$placeholder = '';
			 if ( $this->type=='edit' && $v['name']=='user_login'){
			 	$disabled = 'disabled';
			 } else if ( isset( $v['disabled'] ) && $v['disabled'] ){
					 $disabled = 'disabled';
			 }
			 $parent_id = 'uap_reg_' . $v['type'] . '_' . rand(1,10000);

			 $this->check_for_conditional_logic($v, $parent_id);

			 if (!empty($v['req']) || $v['type']=='conditional_text'){
			 	$this->required_fields[] = array('name' => $v['name'], 'type'=>$v['type']);
			 }

			 switch ($this->register_template){
			 	 case 'uap-register-8':
				 case 'uap-register-9':
				 case 'uap-register-3':
				  //////// FORM FIELD
				  	 $temp_type_class = 'uap-form-' . $v['type'];
					 $str .= '<div class="uap-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
					 if ($v['type'] == 'text' || $v['type'] == 'password'){

						if (isset($v['native_wp']) && $v['native_wp']){
							$placeholder .= esc_html__($v['label'], 'uap');
						 } else {
							$placeholder .= uap_correct_text($v['label']);
						 }
						 if ($v['req']){
 							 $placeholder .= '*';
 						 }
					 } else {
						 $str .= '<label class="uap-labels-register">';

						 if (isset($v['native_wp']) && $v['native_wp']){
							$str .= esc_html__($v['label'], 'uap');
						 } else {
						 	$str .= uap_correct_text($v['label']);
						 }
						 if ($v['req']){
							 $str .= '<span class="uap-color-red">*</span>';
						 }
						 $str .= '</label>';
					 }
					 $val = '';
					 if (isset($this->user_data[$v['name']])){
					 	$val = $this->user_data[$v['name']];
					 }
			 		 if (empty($val) && $v['type']=='plain_text'){ //maybe it's plain text
					 	$val = $v['plain_text_value'];
					 }

					 $multiple_values = FALSE;
					 if (isset($v['values']) && $v['values']){
					 	//is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
					 	$multiple_values = uap_from_simple_array_to_k_v($v['values']);
					 }

					 if (empty($v['sublabel'])){
					 	$v['sublabel'] = '';
					 }
					 if (empty($v['class'])){
					 	$v['class'] = '';
					 }

					 $str .= uap_create_form_element(array(	'type'=>$v['type'], 'name'=>$v['name'], 'value' => $val,
					 											'disabled' => $disabled, 'placeholder' => $placeholder, 'multiple_values'=>$multiple_values,
					 											'user_id'=>$this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'] ));
			 		 if (!empty(self::$print_errors[$v['name']])){
					 	$str .= '<div class="uap-register-notice">' . self::$print_errors[$v['name']] . '</div>';
					 }
					 $str .= '</div>';
				 break;
				 case 'uap-register-4':
				  //////// FORM FIELD
				  $add_class = '';
					if ($v['type'] == 'select' || $v['type'] == 'multi_select' || $v['type'] == 'file' || $v['type'] == 'upload_image' || $v['type'] == 'date'){
						$add_class ='uap-no-backs';
					}
					$temp_type_class = 'uap-form-' . $v['type'];
					 $str .= '<div class="uap-form-line-register '.$add_class.' ' . $temp_type_class . '" id="' . $parent_id . '">';
					 if ($v['type'] == 'text' || $v['type'] == 'password'){

						if (isset($v['native_wp']) && $v['native_wp']){
							$placeholder .= esc_html__($v['label'], 'uap');
						 } else {
							$placeholder .= uap_correct_text($v['label']);
						 }
						 if ($v['req']){
								$placeholder .= '*';
							}
					 } else {
							 $str .= '<label class="uap-labels-register">';

							 if (isset($v['native_wp']) && $v['native_wp']){
								$str .= esc_html__($v['label'], 'uap');
							 } else {
								$str .= uap_correct_text($v['label']);
							 }
							 if ($v['req']){
								$str .= '<span class="uap-color-red">*</span>';
							}
							 $str .= '</label>';
					 }
					 $val = '';
					 if (isset($this->user_data[$v['name']])){
					 	$val = $this->user_data[$v['name']];
					 }
			 		 if (empty($val) && $v['type']=='plain_text'){ //maybe it's plain text
					 	$val = $v['plain_text_value'];
					 }

			 		 $multiple_values = FALSE;
					 if (isset($v['values']) && $v['values']){
					 	//is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
					 	$multiple_values = uap_from_simple_array_to_k_v($v['values']);
					 }

					 if (empty($v['sublabel'])){
					 	$v['sublabel'] = '';
					 }
					 if (empty($v['class'])){
					 	$v['class'] = '';
					 }

					 $str .= uap_create_form_element(array( 'type'=>$v['type'], 'name'=>$v['name'], 'value' => $val,
					 										   'disabled' => $disabled, 'placeholder' => $placeholder, 'multiple_values'=>$multiple_values,
					 											'user_id'=>$this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'] ));
			 		 if (!empty(self::$print_errors[$v['name']])){
					 	$str .= '<div class="uap-register-notice">' . self::$print_errors[$v['name']] . '</div>';
					 }
					 $str .= '</div>';
				 break;

				  case 'uap-register-6':
				  	 //////// FORM FIELD
				  	 $temp_type_class = 'uap-form-' . $v['type'];
					 $str .= '<div class="uap-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
					 $str .= '<label class="uap-labels-register">';

					 if (isset($v['native_wp']) && $v['native_wp']){
						$str .= esc_html__($v['label'], 'uap');
					 } else {
						$str .= uap_correct_text($v['label']);
					 }
					 if ($v['req']){
						 $str .= '<span class="uap-color-red">*</span>';
					 }
					 $str .= '</label>';

					 $val = '';
					 if (isset($this->user_data[$v['name']])){
					 	$val = $this->user_data[$v['name']];
					 }
			 		 if (empty($val) && $v['type']=='plain_text'){ //maybe it's plain text
					 	$val = $v['plain_text_value'];
					 }

					 $multiple_values = FALSE;
					 if (isset($v['values']) && $v['values']){
					 	//is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
					 	$multiple_values = uap_from_simple_array_to_k_v($v['values']);
					 }

					 if (empty($v['sublabel'])){
					 	$v['sublabel'] = '';
					 }

					 if (empty($v['class'])){
					 	$v['class'] = '';
					 }

					 $str .= uap_create_form_element(array( 'type'=>$v['type'], 'name'=>$v['name'], 'value' => $val,
					 										   'disabled' => $disabled, 'multiple_values'=>$multiple_values,
					 											'user_id'=>$this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'] ));
					 if (!empty(self::$print_errors[$v['name']])){
					 	$str .= '<div class="uap-register-notice">' . self::$print_errors[$v['name']] . '</div>';
					 }
					 $str .= '</div>';
				 break;
				 default:
					 //////// FORM FIELD
					 $temp_type_class = 'uap-form-' . $v['type'];
					 $str .= '<div class="uap-form-line-register ' . $temp_type_class . '" id="' . $parent_id . '">';
					 $str .= '<label class="uap-labels-register">';

					 if (isset($v['native_wp']) && $v['native_wp']){
						$str .= esc_html__($v['label'], 'uap');
					 } else {
					 	$str .= uap_correct_text($v['label']);
					 }
					 if ($v['req']){
						 $str .= '<span class="uap-color-red">*</span>';
					 }
					 $str .= '</label>';

					 $val = '';
					 if (isset($this->user_data[$v['name']])){
					 	$val = $this->user_data[$v['name']];
					 }
					 if (empty($val) && $v['type']=='plain_text'){ //maybe it's plain text
					 	$val = $v['plain_text_value'];
					 }

					 $multiple_values = FALSE;
					 if (isset($v['values']) && $v['values']){
					 	//is checkbox, select or radio input field, so we have to include multiple+_values into indeed_create_form_elelemt
					 	$multiple_values = uap_from_simple_array_to_k_v($v['values']);
					 }

					 if (empty($v['sublabel'])){
					 	$v['sublabel'] = '';
					 }

					 if (empty($v['class'])){
					 	$v['class'] = '';
					 }

					 $str .= uap_create_form_element(array( 'type'=>$v['type'], 'name'=>$v['name'], 'value' => $val,
					 										   'disabled' => $disabled, 'multiple_values'=>$multiple_values,
					 										   'user_id'=>$this->user_id, 'sublabel' => $v['sublabel'], 'class' => $v['class'] ));
					 if (!empty(self::$print_errors[$v['name']])){
					 	$str .= '<div class="uap-register-notice">' . self::$print_errors[$v['name']] . '</div>';
					 }
					 $str .= '</div>';
				 break;
			 }
			return $str;
		}
		///////
		private function print_tos($v=array()){
			/*
			 * @param array
			 * @return string
			 */
			$str = '';
			$tos_msg = get_option('uap_register_terms_c');//getting tos message
			$tos_page_id = get_option('uap_general_tos_page');
			$tos_link = get_permalink($tos_page_id);

			if ($tos_msg && $tos_page_id){
				$class = (empty($v['class'])) ? '' : $v['class'];
				$id = 'uap_tos_field_parent_' . rand(1,1000);
				$str .= '<div class="uap-tos-wrap" id="' . $id . '">';
				$str .= '<input type="checkbox" value="1" name="tos" class="' . $class . '" />';
				$str .= '<a href="'.$tos_link.'" target="_blank">' . $tos_msg . '</a>';
				$str .= '</div>';
			}
			return $str;
		}



		//////
		private function print_captcha($v=array())
		{
				$type = get_option( 'uap_recaptcha_version' );
				if ( $type !== false && $type == 'v3' ){
						$key = get_option('uap_recaptcha_public_v3');
				} else {
						$key = get_option('uap_recaptcha_public');
				}

				if ( !$key ){
						return '';
				}

				$view = new \Indeed\Uap\IndeedView();
				$data = array(
						'class' 		=> (empty($v['class'])) ? '' : $v['class'],
						'key'				=> $key,
						'langCode'	=> indeed_get_current_language_code(),
						'type'			=> $type,
				);
				/*
				if ( $type !== false && $type == 'v3' ){
					wp_enqueue_script( 'uap-recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render='.$key );
			    wp_enqueue_script( 'uap-register-captcha', UAP_URL . 'assets/js/register-captcha.js', ['jquery'], 8.3 );
				}else{
					wp_enqueue_script( 'uap-recaptcha-v2', 'https://www.google.com/recaptcha/api.js?hl='.indeed_get_current_language_code() );
				}
				*/
				return $view->setTemplate( UAP_PATH . 'public/views/register-captcha.php' )->setContentData( $data, true )->getOutput();
		}


		private function print_overview_post_select(){
			/*
			 * dropdown with all post
			 * @param none
			 * @return string
			 */
			$str = '';
			global $indeed_db;
			$default_pages_arr = $indeed_db->return_settings_from_wp_option('general-redirects');
			$default_pages_arr = array_diff_key($default_pages_arr, array(	'uap_general_logout_redirect'=>'',
																			'uap_general_register_redirect'=>'',
																			'uap_general_login_redirect'=>'' ));//let's exclude the redirect pages
			$args = array(
					'posts_per_page'   => 100,/// 1000
					'offset'           => 0,
					'orderby'          => 'date',
					'order'            => 'DESC',
					'post_type'        => array( 'post', 'page' ),
					'post_status'      => 'publish',
					'post__not_in'	   => $default_pages_arr,
			);

			$posts_array = get_posts( $args );
			$arr['-1'] = '...';
			foreach ($posts_array as $k=>$v){
				$arr[$v->ID] = $v->post_title;
			}
			$str .= '<div class="uap-form-line-register uap-form-select-overview-post">';
			$str .= '<h4>' . esc_html__('Custom Dashboard message', 'uap') . ' </h4>';
			$str .= '<p>' . esc_html__('The Dashboard section from the Account Page can display general content or specific content for each member. You may choose a specific post content for this member or leave the default setup', 'uap') . ' </p>';
			$args['type'] = 'select';
			$args['multiple_values'] = $arr;
			$value = get_user_meta($this->user_id, 'uap_overview_post', true);
			$args['value'] = ($value!==FALSE) ? $value : '';
			$args['name'] = 'uap_overview_post';
			$str .= uap_create_form_element($args);
			$str .= '</div>';
			return $str;
		}

		///////
		public function save_update_user(){
			/*
			 * @param none
			 * @return none
			 */

			 if ( !$this->checkNonce() ){
					 return false;
			 }

			$this->userdata();//set the user data, in case of new user the array will contain only keys
			if ( (empty( $this->user_id ) && $this->type === 'create') || $this->type === 'edit' ){
					$this->check_username();
					$this->check_password();
					$this->check_email();
			}

			$this->check_tos();
			$this->check_captcha();
			$this->set_roles();
			$this->set_the_slug();
			$this->set_automatically_fields();

			//ADD exceptions
			if (!empty($_REQUEST['uap_exceptionsfields'])){
				$this->exception_fields = explode(',', uap_sanitize_textarea_array( $_REQUEST['uap_exceptionsfields'] ) );
			}

			$custom_meta_user = array();
			if (!$this->is_public){
				///////// UPDATE THIS
				$custom_meta_user['uap_overview_post'] = uap_sanitize_array($_REQUEST['uap_overview_post']);
			}
			foreach ($this->register_fields as $fieldId => $value){
				$name = $value['name'];
				if (isset($_REQUEST[$name])){
					if ( $this->register_fields[$fieldId]['type'] == 'date' ){
							$_REQUEST[$name] = preg_replace( "([^0-9-])", '', uap_sanitize_textarea_array($_REQUEST[$name]) );
					}
					if ($this->is_public && !empty($value['req']) && $_REQUEST[$name]=='' && !in_array($name, $this->exception_fields)){
						$this->errors[$name] = $this->register_metas['uap_register_err_req_fields'];//add the error message
					}
					if (!empty($value['native_wp']) ||  $name=='user_login'){
						 //wp standard info
						 $this->fields[$name] = uap_sanitize_textarea_array($_REQUEST[$name]);//filter_var( uap_sanitize_textarea_array($_REQUEST[$name]), FILTER_SANITIZE_STRING );
					} else {
						 //custom field
						 if ( is_array( $_REQUEST[$name] ) ){
							 	$custom_meta_user[$name] = indeedFilterVarArrayElements( uap_sanitize_textarea_array($_REQUEST[$name]) );
						 } else {
							 	$custom_meta_user[$name] = uap_sanitize_textarea_array($_REQUEST[$name]);//filter_var( uap_sanitize_textarea_array($_REQUEST[$name]), FILTER_SANITIZE_STRING );
						 }
						 $this->uap_is_req_conditional_field($value);//conditional required field
					}
				}
			}

			$this->errors = apply_filters( 'uap_register_process_filter_errors', $this->errors );

			if ($this->errors){
				 //print the error and exit
				 $this->return_errors();
				 return FALSE;
			}

			//=========================== SAVE / UPDATE
			//wp native user
			if ($this->type=='create' ){
				//add new user
				$this->fields = apply_filters('uap_before_register_new_user', $this->fields);
				define('UAP_USER_REGISTER_PROCESS', TRUE); /// USED IN all_new_users_become_affiliates() to skip wp filter

				// 8.6
				if( empty( $this->user_id ) ){ // || !isset($this->user_id)
						$this->user_id = wp_insert_user($this->fields);
				} else {
						// user its already registered but wants to become and affiliate
						$this->fields['ID'] = $this->user_id;
						if ( isset( $this->fields['user_pass'] ) && $this->fields['user_pass'] === '' ){
								unset( $this->fields['user_pass'] );
						}
						wp_update_user($this->fields);
				}
				// end of 8.6

				global $indeed_db;
				if ( $this->is_public ){
							$saveAsAffiliate = apply_filters( 'uap_save_as_affiliate_filter', true );
				}else{
						  $saveAsAffiliate = true;
				}
				if ( $saveAsAffiliate ){
						$indeed_db->save_affiliate($this->user_id);
				} else {
						if ( $indeed_db->is_magic_feat_enable('mlm') && $indeed_db->is_magic_feat_enable('pay_to_become_affiliate') ){
								/// save mlm parent into a user option if 'mlm' and 'apy to become affiliate' is activated
								$this->saveMlmParentForUser();
						}
				}
				do_action('uap_on_register_action', $this->user_id);
			} else {
				//update user
				$this->fields['ID'] = $this->user_id;
				wp_update_user($this->fields);
				do_action('uap_on_update_action', $this->user_id);
			}

			$this->do_opt_in();
			$this->double_email_verification();

			//custom user meta
			if ($custom_meta_user){
				foreach ($custom_meta_user as $k=>$v){
						do_action( 'uap_before_user_save_custom_field', $this->user_id, $k, $v );
						update_user_meta($this->user_id, $k, $v);
				}
			}

			//auto login
			if ($this->is_public && $this->type=='create' &&
					!empty($this->register_metas['uap_register_auto_login']) && !empty($this->register_metas['uap_register_new_user_role'])
					&& $this->register_metas['uap_register_new_user_role']!='pending_user'){
				wp_set_auth_cookie($this->user_id);
			}

			$this->set_rank();//USER RANKS
			$this->set_mlm_parent();

			if ($this->is_public){
				/// NOTIFICATIONS
				if ($this->type=='create'){
					if ($this->send_password_via_mail){
						/// send generated password to user
						uap_send_user_notifications($this->user_id, 'register_lite_send_pass_to_user', FALSE, array('{NEW_PASSWORD}' => $this->fields['user_pass']));
					}
					uap_send_user_notifications($this->user_id, 'register', $this->current_rank);//notify the affiliate
					uap_send_user_notifications($this->user_id, 'admin_user_register', $this->current_rank);//notify the admin
				} else {
					uap_send_user_notifications($this->user_id, 'user_update', $this->current_rank);//notify the affiliate
					uap_send_user_notifications($this->user_id, 'admin_affiliate_update_profile');/// USER HAS UPDATE PROFILE, SEND EMAIL TO ADMIN ABOUT THAT
				}
				$this->succes_message();//this will redirect
			}

		}

		/**
		 * @param none
		 * @return bool
		 */
		public function checkNonce()
		{
			if ( $this->is_public ){
				if ( empty( $_POST['uap_user_add_edit_nonce'] ) || !wp_verify_nonce( sanitize_text_field($_POST['uap_user_add_edit_nonce']), 'uap_user_add_edit_nonce' ) ){
					return false;
				}
			} else {
				if ( empty( $_POST['uap_user_add_edit_nonce'] ) || !wp_verify_nonce( sanitize_text_field($_POST['uap_user_add_edit_nonce']), 'uap_admin_user_add_edit_nonce' ) ){
					return false;
				}
			}
			return true;
		}

		private function saveMlmParentForUser()
		{
				$parentAffiliateId = empty( $_POST['uap_affiliate_mlm_parent'] ) ? 0 : sanitize_text_field($_POST['uap_affiliate_mlm_parent']);
				if ( isset( $_COOKIE ) && !empty( $_COOKIE['uap_referral'] ) && empty( $parentAffiliateId ) ){
						$cookieData = json_decode( stripslashes( $_COOKIE['uap_referral'] ), true );//since version 9.1
						$parentAffiliateId = empty( $cookieData['affiliate_id'] ) ? 0 : $cookieData['affiliate_id'];
				}
				if ( empty( $parentAffiliateId ) ){
						global $indeed_db;
						return;
				}
				update_user_meta( $this->user_id, 'uap_mlm_parrent_id_pending', $parentAffiliateId );
		}


		///handle password
		private function check_password(){
			if(($this->type=='edit' && !empty($_REQUEST['pass1'])) || $this->type=='create' ){
				///// only for create new user or in case that current user has selected a new password (edit)

				if ($this->type=='create'){
					$key = uap_array_value_exists($this->register_fields, 'pass1', 'name');
					if (isset($this->register_fields[$key])){
						if ($this->is_public){
							$check = 'display_public_reg';
						} else {
							$check = 'display_admin';
						}
						if (empty($this->register_fields[$key][$check])){
							$this->set_password_automaticly = TRUE;
							return;
						}
					}
				}

				//check the strength
				if ($this->register_metas['uap_register_pass_options']==2){
					//characters and digits
					if (!preg_match('/[a-z]/', sanitize_text_field( $_REQUEST['pass1'] ) ) ){
						$this->errors['pass1'] = $this->register_metas['uap_register_pass_letter_digits_msg'];
					}
					if ( !preg_match('/[0-9]/', sanitize_text_field($_REQUEST['pass1']) ) ){
						$this->errors['pass1'] = $this->register_metas['uap_register_pass_letter_digits_msg'];
					}
				} elseif ($this->register_metas['uap_register_pass_options']==3){
					//characters, digits and one Uppercase letter
					if ( !preg_match('/[a-z]/', sanitize_text_field($_REQUEST['pass1']) ) ){
						$this->errors['pass1'] = $this->register_metas['uap_register_pass_let_dig_up_let_msg'];
					}
					if ( !preg_match('/[0-9]/', sanitize_text_field($_REQUEST['pass1']) ) ){
						$this->errors['pass1'] = $this->register_metas['uap_register_pass_let_dig_up_let_msg'];
					}
					if ( !preg_match('/[A-Z]/', sanitize_text_field($_REQUEST['pass1']) ) ){
						$this->errors['pass1'] = $this->register_metas['uap_register_pass_let_dig_up_let_msg'];
					}
				}

				//check the length of password
				if($this->register_metas['uap_register_pass_min_length']!=0){
					if( strlen( sanitize_text_field($_REQUEST['pass1']) )<$this->register_metas['uap_register_pass_min_length']){
						$this->errors['pass1'] = str_replace( '{X}', $this->register_metas['uap_register_pass_min_length'], $this->register_metas['uap_register_pass_min_char_msg'] );
					}
				}
				if(isset($_REQUEST['pass2'])){
					if ( sanitize_text_field($_REQUEST['pass1']) != sanitize_text_field($_REQUEST['pass2']) ){
						$this->errors['pass2'] = $this->register_metas['uap_register_pass_not_match_msg'];
					}
				}
				//PASSWORD
				$this->fields['user_pass'] = sanitize_text_field($_REQUEST['pass1']);
			}
			$pass1 = uap_array_value_exists($this->register_fields, 'pass1', 'name');
			if ($pass1!==FALSE && isset($this->register_fields[$pass1])){
				unset($this->register_fields[$pass1]);
			}
			$pass2 = uap_array_value_exists($this->register_fields, 'pass2', 'name');
			if ($pass2!==FALSE && isset($this->register_fields[$pass2])){
				unset($this->register_fields[$pass2]);
			}
		}

		///check email
		private function check_email(){
			if ( !is_email( sanitize_text_field( $_REQUEST['user_email'] ) ) ) {
				$this->errors['user_email'] = $this->register_metas['uap_register_invalid_email_msg'];
			}
			if (isset($_REQUEST['confirm_email']) && $this->is_public ){
				if ( sanitize_text_field($_REQUEST['confirm_email']) != sanitize_text_field($_REQUEST['user_email']) ){
					$this->errors['user_email'] = $this->register_metas['uap_register_emails_not_match_msg'];
				}
			}
			if ( email_exists( sanitize_text_field($_REQUEST['user_email']) ) ){
				if ($this->type=='create' || ($this->type=='edit' && email_exists( sanitize_text_field($_REQUEST['user_email']) )!=$this->user_id  ) ){

					$this->errors['user_email'] = $this->register_metas['uap_register_email_is_taken_msg'];
				}
			}
		}

		//check username
		private function check_username(){
			//only for create

			if ($this->type=='create'){

				///NO USERNAME FIELd
				$key = uap_array_value_exists($this->register_fields, 'user_login', 'name');
				if (isset($this->register_fields[$key])){
					if ($this->is_public){
						$check = 'display_public_reg';
					} else {
						$check = 'display_admin';
					}
					if (empty($this->register_fields[$key][$check])){
						$this->set_username_automaticly = TRUE;
						return;
					}
				}

				if ( isset($_REQUEST['user_login']) && !validate_username( sanitize_text_field($_REQUEST['user_login']) ) ) {
					$this->errors['user_login'] = $this->register_metas['uap_register_error_username_msg'];
				}
				if ( isset($_REQUEST['user_login']) && username_exists( sanitize_text_field($_REQUEST['user_login']) ) ) {
					$this->errors['user_login'] = $this->register_metas['uap_register_username_taken_msg'];
				}
			}

		}

		///////// TERMS AND CONDITIONS CHECKBOX CHECK
		private function check_tos(){
			//check if tos was printed
			$tos_page_id = get_option('uap_general_tos_page');
			$tos_msg = get_option('uap_register_terms_c');//getting tos message
			if (!$tos_page_id || !$tos_msg){
				$tos = uap_array_value_exists($this->register_fields, 'tos', 'name');
				if ($tos!==FALSE && isset($this->register_fields[$tos])){
					unset($this->register_fields[$tos]);
				}
				return;
			}

			if ($this->tos && $this->type=='create'){
				$tos = uap_array_value_exists($this->register_fields, 'tos', 'name');
				if ($tos!==FALSE && $this->register_fields[$tos][$this->display_type]){
					unset($this->register_fields[$tos]);
					if (!isset($_REQUEST['tos']) || sanitize_text_field($_REQUEST['tos'])!=1){
						$this->errors['tos'] = get_option('uap_register_err_tos');
					}
				}
			} else {
				$tos = uap_array_value_exists($this->register_fields, 'tos', 'name');
				if ($tos!==FALSE && isset($this->register_fields[$tos])){
					unset($this->register_fields[$tos]);
				}
			}
		}

		//////////// CAPTCHA
		private function check_captcha(){
			if ($this->type=='create' && $this->captcha){
				//check if capcha key is set

				$captchaType = get_option( 'uap_recaptcha_version' );
				if ( $captchaType !== false && $captchaType == 'v3'){
						$captcha_key = get_option('uap_recaptcha_private_v3');
				} else {
						$captcha_key = get_option('uap_recaptcha_private');
				}

				if (!$captcha_key){
					$captcha = uap_array_value_exists($this->register_fields, 'recaptcha', 'name');
					if ($captcha!==FALSE){
						unset($this->register_fields[$captcha]);
					}
					return;
				}

				$captcha = uap_array_value_exists($this->register_fields, 'recaptcha', 'name');
				if ($captcha!==FALSE && $this->register_fields[$captcha][$this->display_type]){
					$captha_err = get_option('uap_register_err_recaptcha');
					unset($this->register_fields[$captcha]);
					if (isset($_REQUEST['g-recaptcha-response'])){
						//$secret = get_option('uap_recaptcha_private');
						if ($captcha_key){
							require_once UAP_PATH . 'classes/services/recaptcha/autoload.php';
							$recaptcha = new \ReCaptcha\ReCaptcha( $captcha_key, new \ReCaptcha\RequestMethod\CurlPost() );
							$resp = $recaptcha->verify( $_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
							if (!$resp->isSuccess()){
								$this->errors['captcha'] = $captha_err;
							}
						} else {
							$this->errors['captcha'] = $captha_err;
						}
					} else {
						$this->errors['captcha'] = $captha_err;
					}
				}
			}

		}

		private function uap_is_req_conditional_field($field_meta=array()){
			/*
			 * @param array
			 * @return none
			 */
			if (!empty($field_meta['type']) && $field_meta['type']=='conditional_text' && $this->is_public){
				$field_name = $field_meta['name'];
				if ($field_meta['conditional_text']!=sanitize_text_field($_REQUEST[$field_name])){
					if (!empty($field_meta['error_message'])){
						$this->errors[$field_name] = uap_correct_text($field_meta['error_message']);
					} else {
						$this->errors[$field_name] = esc_html__("Error");
					}
				}
			}
		}

		////WP ROLE
		private function set_roles(){
			//role
			if ($this->is_public && $this->type=='create'){
				if (isset($this->register_metas['uap_register_new_user_role'])){
					$this->fields['role'] = $this->register_metas['uap_register_new_user_role'];
				} else {
					$this->fields['role'] = get_option('default_role');
					if (empty($this->fields['role'])){
							$this->fields['role'] = 'subscriber';
					}
				}
			} else if (!$this->is_public){
				if (isset($_REQUEST['role'])){
					$this->fields['role'] = uap_sanitize_array($_REQUEST['role']);
				}
			}
		}

		private function set_the_slug(){
			/*
			 * @param none
			 * @return none
			 */
			 if (!$this->is_public){
			 	/// only admin
			 	if (isset($_POST['uap_affiliate_custom_slug']) && sanitize_text_field($_POST['uap_affiliate_custom_slug'])!=''){
			 		global $indeed_db;
					$saved = $indeed_db->save_custom_slug_for_uid($this->user_id, sanitize_text_field($_POST['uap_affiliate_custom_slug']), TRUE);
					if (empty($saved)){
						$this->errors['Custom Slug'] = esc_html__('Error on trying to save the Affiliate Custom Slug', 'uap');
					}
					unset($_POST['uap_affiliate_custom_slug']);
			 	}
			 }
		}

		private function set_automatically_fields(){
			/*
			 * @param none
			 * @return none
			 */
			 if (!empty($this->set_username_automaticly)){
			 	$this->fields['user_login'] = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
			 }
			 if (!empty($this->set_password_automaticly)){
				$this->fields['user_pass'] = wp_generate_password(10);
				$this->send_password_via_mail = TRUE;
			 }
		}

		///RANKS
		private function set_rank(){
			/*
			 * set RANK on public create, admin create, admin update.
			 * @param none
			 * @return none
			 */
			global $indeed_db;
			if ($this->type=='create' && $this->is_public){
				/// PUBLIC CREATE
				$indeed_db->update_affiliate_rank_by_uid($this->user_id, $this->current_rank);
			} else if (!$this->is_public) {
				/// ADMIN
				$rank = (isset($_REQUEST['rank_id'])) ? sanitize_text_field($_REQUEST['rank_id']) : $this->current_rank;
				$indeed_db->update_affiliate_rank_by_uid($this->user_id, $rank);
			}

		}

		private function set_mlm_parent(){
			/*
			 * @param none
			 * @return none
			 */
			global $indeed_db;
			$affiliate_id = $indeed_db->affiliate_get_id_by_uid($this->user_id);
			if ($this->type=='create' && $this->is_public && $indeed_db->is_magic_feat_enable('mlm') ){
				/// SET MLM PARENT IN PUBLIC SECTION

				// IF FAIR CHECKOUT REWARD MODULE IS ENABLED
				if($indeed_db->is_magic_feat_enable('checkout_select_referral')){
					if(!empty($_REQUEST['uap_affiliate_username'])){
						$parentAffiliateId = sanitize_text_field($_REQUEST['uap_affiliate_username']);
					}else if (!empty($_REQUEST['uap_affiliate_username_text'])){
						$sanitized_username = sanitize_text_field( $_REQUEST['uap_affiliate_username_text'] );
				 		$temp = $indeed_db->get_affiliate_id_by_username($sanitized_username);
						if ($temp){
							$parentAffiliateId = $temp;
						}
					}
				}

				if(isset($parentAffiliateId)){
						$indeed_db->set_mlm_relation_on_new_affiliate($affiliate_id, $parentAffiliateId);
				}else{
						$indeed_db->set_mlm_relation_on_new_affiliate($affiliate_id);
				}
				//Allow to change MLM Parent
			} else if (!$this->is_public && $indeed_db->is_magic_feat_enable('mlm') ){
				// Edit
				if ( ( isset( $_POST['uap_affiliate_mlm_parent'] ) && sanitize_text_field( $_POST['uap_affiliate_mlm_parent']) === '0' )
					|| ( isset( $_POST['uap_affiliate_mlm_parent_slug'] ) && sanitize_text_field( $_POST['uap_affiliate_mlm_parent_slug'] ) === '' ) ){
						$indeed_db->delete_mlm_relation( $affiliate_id );
						unset($_POST['uap_affiliate_mlm_parent']);
						unset($_POST['uap_affiliate_mlm_parent_slug']);
				} else if ( !empty($_POST['uap_affiliate_mlm_parent']) ){
						/// SET MLM PARENT IN ADMIN SECTION
						$indeed_db->set_mlm_relation_on_new_affiliate($affiliate_id, sanitize_text_field($_POST['uap_affiliate_mlm_parent']) );
						unset($_POST['uap_affiliate_mlm_parent']);
				}
			}
		}

		///// RETURN ERROR
		private function return_errors(){
			/*
			 * set the global variable with the error string
			 */
			if (!empty($this->errors)){
				global $uap_error_register;
				$uap_error_register = $this->errors;
				self::$uap_error_register = $this->errors;
			}
		}

		private function count_register_fields(){
			$count = 0;
			foreach ($this->register_fields as $v){
				if ($v[$this->display_type] > 0){
					$count++;
				}
			}
			return $count;
		}

		private function succes_message(){
			/*
			 * REDIRECT...
			 * @param none
			 * @return none
			 */

			if ($this->type=='create'){
				$q_arg = 'create_message';
			} else {
				$q_arg = 'update_message';
			}

			$redirect = get_option('uap_general_register_redirect');
			if ($redirect && $redirect!=-1 && $this->type=='create'){
				$url = get_permalink($redirect);
			}
			if (empty($url)){
				$url = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			}

			$url = add_query_arg(array('uap_register'=>$q_arg), $url);

			wp_redirect($url);
			exit();
		}


		private function do_opt_in(){
				/*
				 * @param none
				 * @return none
				 */
				$double_email_verification = get_option('uap_register_double_email_verification');
				if ($this->type=='create' && empty($double_email_verification)){

						$optinAccept = uap_array_value_exists( $this->register_fields, 'uap_optin_accept', 'name' );
						if ( $optinAccept && !empty($this->register_fields[$optinAccept]['display_public_reg']) && ( !isset( $_POST['uap_optin_accept']) || sanitize_text_field($_POST['uap_optin_accept']) != 1 ) ){
								return;
						}
						if ( isset( $_POST['user_email'] ) ){
								uap_do_opt_in( sanitize_email($_POST['user_email']) );
						}
				}
		}

		private function double_email_verification(){
			/*
			 * @param none
			 * @return none
			 */
			$double_email_verification = get_option('uap_register_double_email_verification');
			if ($this->is_public && $this->type=='create' && !empty($double_email_verification) ){
				$hash = uap_random_string(10);
				update_user_meta($this->user_id, 'uap_activation_code', $hash);//put the hash into user option
				update_user_meta($this->user_id, 'uap_verification_status', -1);//set uap_verification_status @ -1

				$activation_url_w_hash = site_url();
				$activation_url_w_hash = trailingslashit($activation_url_w_hash);
				$activation_url_w_hash = add_query_arg('uap_act', 'email_verification', $activation_url_w_hash);
				$activation_url_w_hash = add_query_arg('uid', $this->user_id, $activation_url_w_hash);
				$activation_url_w_hash = add_query_arg('do_uap_code', $hash, $activation_url_w_hash);
				//send a nice notification
				uap_send_user_notifications($this->user_id, 'email_check', 0, array('{verify_email_address_link}'=>$activation_url_w_hash));
			}
		}


	}//end of class AffiliateAddEdit
}
