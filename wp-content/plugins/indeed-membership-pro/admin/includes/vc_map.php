<?php
//register
vc_map(
	array(
		"name" => 'Membership Pro - ' . esc_html__('Register Form', 'ihc'),
		"base" => 'ihc-register',
		"icon" => 'ihc_vc_logo',
		"description" => esc_html__('Register Form', 'ihc'),
		"class" => 'ihc-register',
		"category" => esc_html__('Content', 'js_composer'),
		"params" => array(
							array(
									"type" => "ihc_print_text_vc",
									"custom_text" => esc_html__("Register Form Shortcode", 'ihc'),
									'param_name' => 'param1',
							)
						),
		'show_settings_on_create' => false,
	)
);

//Login
vc_map(
		array(
				"name" => 'Membership Pro - ' . esc_html__('Login Form', 'ihc'),
				"base" => 'ihc-login-form',
				"icon" => 'ihc_vc_logo',
				"description" => esc_html__('Login Form', 'ihc'),
				"class" => 'ihc-login-form',
				"category" => esc_html__('Content', 'js_composer'),
				"params" => array(
									array(
											"type" => "ihc_print_text_vc",
											"custom_text" => esc_html__("Login Form Shortcode", 'ihc'),
											'param_name' => 'param1',
									)
								),
				'show_settings_on_create' => false,
		)
);

//Logout
vc_map(
		array(
				"name" => 'Membership Pro - ' . esc_html__('Logout Button', 'ihc'),
				"base" => 'ihc-logout-link',
				"icon" => 'ihc_vc_logo',
				"description" => esc_html__('Logout Button', 'ihc'),
				"class" => 'ihc-logout-link',
				"category" => esc_html__('Content', 'js_composer'),
				"params" => array(
									array(
											"type" => "ihc_print_text_vc",
											"custom_text" => esc_html__("Logout Link Shortcode", 'ihc'),
											'param_name' => 'param1',
									)
								),
				'show_settings_on_create' => false,
		)
);

//Password Recovery
vc_map(
		array(
				"name" => 'Membership Pro - ' . esc_html__('Password Recovery', 'ihc'),
				"base" => 'ihc-pass-reset',
				"icon" => 'ihc_vc_logo',
				"description" => esc_html__('Password Recovery', 'ihc'),
				"class" => 'ihc-pass-reset',
				"category" => esc_html__('Content', 'js_composer'),
				"params" => array(
									array(
											"type" => "ihc_print_text_vc",
											"custom_text" => esc_html__("Password Recovery Shortcode", 'ihc'),
											'param_name' => 'param1',
									)
								 ),
				'show_settings_on_create' => false,
		)
);

//User Page
vc_map(
		array(
				"name" => 'Membership Pro - ' . esc_html__('Account Page', 'ihc'),
				"base" => 'ihc-user-page',
				"icon" => 'ihc_vc_logo',
				"description" => esc_html__('Password Recovery', 'ihc'),
				"class" => 'ihc-user-page',
				"category" => esc_html__('Content', 'js_composer'),
				"params" => array(
									array(
											"type" => "ihc_print_text_vc",
											"custom_text" => esc_html__("User Page Shortcode", 'ihc'),
											'param_name' => 'param1',
									)
								 ),
				'show_settings_on_create' => false,
		)
);

//Subscription Plan
vc_map(
		array(
				"name" => 'Membership Pro - ' . esc_html__('Subscription Plan', 'ihc'),
				"base" => 'ihc-select-level',
				"icon" => 'ihc_vc_logo',
				"description" => esc_html__('Password Recovery', 'ihc'),
				"class" => 'ihc-select-level',
				"category" => esc_html__('Content', 'js_composer'),
				"params" => array(
									array(
											"type" => "ihc_print_text_vc",
											"custom_text" => esc_html__("Subscription Plan Shortcode", 'ihc'),
											'param_name' => 'param1',
									)
								  ),
				'show_settings_on_create' => false,
		)
);


//the locker
vc_map(
		array(
				'admin_enqueue_js' => IHC_URL . 'admin/assets/js/back_end.js',
				"name" => 'Membership Pro - ' . esc_html__('Locker', 'ihc'),
				"base" => 'ihc-hide-content',
				"icon" => 'ihc_vc_logo',
				"description" => esc_html__('Locker', 'ihc'),
				"class" => 'ihc-hide-content',
				"category" => esc_html__('Content', 'js_composer'),
				"params" => array(
									array(
											"type" => 'ihc_custom_dropdown',
											"heading" => esc_html__('Type:', 'ihc'),
											"label" => '',
											"param_name" => 'ihc_mb_type',
											"values" => array('show' => esc_html__('Show Content Only For', 'ihc'), 'block' => esc_html__('Hide Content Only For', 'ihc') ),
											'value' => '',
									),
									array(
											"type" => 'ihc_select_target_u',
											"heading" => esc_html__('Target Users:', 'ihc'),
											"label" => '',
											"param_name" => 'ihc_mb_who',
											'value' => '',
									),
									array(
											"type" => 'ihc_select_locker',
											"heading" => esc_html__('Choose Locker:', 'ihc'),
											"label" => '',
											"param_name" => 'ihc_mb_template',
											"value" => '',
									),
									array(
											"type" => "textarea_html",
											"holder" => "div",
											"class" => "",
											"heading" => esc_html__( "Content", "js_composer" ),
											"param_name" => "content", // Important: Only one textarea_html param per content element allowed and it should have "content" as a "param_name"
											"value" => esc_html__( "<p>I am test text block. Click edit button to change this text.</p>", "js_composer" ),
											"description" => esc_html__( "Enter your content.", "js_composer" )
									)
								)
		)
);


///vc functions

function ihc_print_text_vc_settings_field($settings, $value){
	return $settings['custom_text'];
}

function ihc_select_target_u_settings_field($settings, $value){
	$posible_values = array( 'all'=>esc_html__('All', 'ihc'), 'reg'=>esc_html__('Registered Users', 'ihc'), 'unreg'=>esc_html__('Unregistered Users', 'ihc') );

	$levels = \Indeed\Ihc\Db\Memberships::getAll();
	if ($levels){
		foreach($levels as $id=>$level){
			$posible_values[$id] = $level['name'];
		}
	}
	$str = '';
	$str .= '<select id="ihc-change-target-user-set" onChange="ihcWriteTagValue(this, \'#ihc_mb_who-hidden-vc\', \'#ihc_tags_field_vc\', \'ihc_select_tag_vc_\' );" class="ihc-change-target-user-set">';
	foreach ($posible_values as $k=>$v){
		$str .= '<option value="'.$k.'" >'.$v.'</option>';
	}
	$str .= '</select>';

	$str .= '<div id="ihc_tags_field_vc">';

	if ($value){
		if (strpos($value, ',')!==FALSE){
			$values = explode(',', $value);
		} else {
			$values[] = $value;
		}
		if (count($values)){
			foreach ($values as $val){
				if (isset($posible_values[$val])){
					$str .= '<div id="ihc_select_tag_vc_'.$val.'" class="ihc-tag-item">';
					$str .= $posible_values[$val];
					$str .= '<div class="ihc-remove-tag" onclick="ihcremoveTag(\''.$val.'\', \'#ihc_select_tag_vc_\', \'#ihc_mb_who-hidden-vc\');" title="'.esc_html__('Removing tag', 'ihc').'">x</div>';
					$str .= '</div>';
				}
	        }
	    }
		$str .= '<div class="ihc-clear"></div>';
	}
	$str .= '</div>';
	$str .= '<input type="hidden" value="'.$value.'" name="'.$settings['param_name'].'" class="wpb_vc_param_value '.$settings['param_name'].' '.$settings['type'].'_field" id="ihc_mb_who-hidden-vc" />';
	return $str;
}

function ihc_select_locker_settings_field($settings, $value){
	$str = '';
	$lockers = ihc_return_meta('ihc_lockers');
	if ($lockers){
		$str .= '<select value="'.$value.'" name="'.$settings['param_name'].'" class="wpb_vc_param_value '.$settings['param_name'].' '.$settings['type'].'_field" onChange="ihcLockerPreviewWi(this.value, 0);">';
		$str .= '<option value="-1">...</option>';
		foreach ($lockers as $k=>$v){
			$selected = ($k==$value) ? 'selected' : '';
			$str .= '<option value="'.$k.'" '.$selected.'>'.$v['ihc_locker_name'].'</option>';
		}
		$str .= '</select>';
	} else {
		$str .= esc_html__('No Lockers Available.', 'ihc');
	}
	return $str;
}

function ihc_custom_dropdown_settings_field($settings, $value){
	$str = '';
	$str .= '<select value="'.$value.'" name="'.$settings['param_name'].'" class="wpb_vc_param_value '.$settings['param_name'].' '.$settings['type'].'_field">';
	foreach ($settings['values'] as $k=>$v){
		$selected = ($k==$value) ? 'selected' : '';
		$str .= '<option value="'.$k.'" '.$selected.'>'.$v.'</option>';
	}
	$str .= '</select>';
	return $str;
}

if (defined('WPB_VC_VERSION')){
	if (version_compare(WPB_VC_VERSION, '4.4')==1){
		vc_add_shortcode_param('ihc_print_text_vc', 'ihc_print_text_vc_settings_field');
		vc_add_shortcode_param('ihc_custom_dropdown', 'ihc_custom_dropdown_settings_field');
		vc_add_shortcode_param('ihc_select_target_u', 'ihc_select_target_u_settings_field');
		vc_add_shortcode_param('ihc_select_locker', 'ihc_select_locker_settings_field');
	} else {
		add_shortcode_param('ihc_print_text_vc', 'ihc_print_text_vc_settings_field');
		add_shortcode_param('ihc_select_target_u', 'ihc_select_target_u_settings_field');
		add_shortcode_param('ihc_select_locker', 'ihc_select_locker_settings_field');
		add_shortcode_param('ihc_custom_dropdown', 'ihc_custom_dropdown_settings_field');
	}
}
