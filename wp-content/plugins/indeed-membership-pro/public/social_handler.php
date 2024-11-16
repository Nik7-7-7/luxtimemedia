<?php

//first of all remove ihcaction, to prevent instatiate register class
/*
Updated since UMP version 8.4.
linkedin use the new version of HybridAuth  ( v3.8.1, the file from hybrid-auth/src/Provider/LinkedIn.php has been updated, the rest of hybridAuth is still the old version )
*/
if (isset($_POST['ihcaction'])){
	unset($_POST['ihcaction']);
}
if ( empty( $ihcLoadWp ) ){
	require_once '../../../../wp-load.php';
	require_once IHC_PATH . 'utilities.php';
}
require_once IHC_PATH . 'classes/services/hybrid-auth/vendor/autoload.php';

if(session_id() == '') {
	session_start();
}

if (!empty($_POST['sm_register'])){
	//===============================REGISTER
	if (!empty($_POST['sm_type'])){
		$_SESSION['sm_type'] = sanitize_text_field($_POST['sm_type']);
		unset($_POST['sm_type']);
	}
	//previous url
	if (isset($_POST['ihc_current_url'])){
		$_SESSION['ihc_current_url'] = $_POST['ihc_current_url'];
		unset($_POST['ihc_current_url']);
		$url_str = parse_url( $_SESSION['ihc_current_url'] );
		if (isset($url_str['query']['lid'])){
			$_SESSION['lid'] = $url_str['query']['lid'];
		}
	}
	//remove Submit
	if (isset($_POST['Submit'])){
		unset($_POST['Submit']);
	}
	unset($_POST['sm_register']);
	foreach ($_POST as $k=>$v){
		$_SESSION['ihc_register'][$k] = $v;
	}
	$_SESSION['sm_action'] = 'register';
} else if (!empty($_GET['sm_login'])){
	//================================LOGIN
	$_SESSION['sm_type'] = sanitize_text_field($_GET['sm_login']);
	$_SESSION['ihc_current_url'] = esc_url($_GET['ihc_current_url']);
	$_SESSION['sm_action'] = 'login';
	if (!empty($_GET['is_locker'])){
		$_SESSION['is_locker'] = 1;
	}
} else if (!empty($_GET["reg_ext_usr"])){
	//=================================REGISTER SOCIAL FOR EXISTING USER
	global $current_user;
	$_SESSION['sm_action'] = 'update_user';
	$_SESSION['ihc_uid'] = $current_user->ID;
	$_SESSION['sm_type'] = sanitize_text_field($_GET['reg_ext_usr']);
	$_SESSION['ihc_current_url'] = $_GET['ihc_current_url'];
}

//========================= CONFIG FOR HYBRIDAUTH
$config = array(
		'callback' => site_url() . '?ihc_action=social_login',//\Hybridauth\HttpClient\Util::getCurrentUrl(),
		"providers" => array (
				"OpenID" => array(
						"enabled" => true
				),
		)
);
//getting settings for current provider
$sm_options = ihc_return_meta_arr($_SESSION['sm_type']);


switch ($_SESSION['sm_type']){
	case 'fb':
		$provider = "Facebook";
		$config = [
				'callback' => \Hybridauth\HttpClient\Util::getCurrentUrl(),
				'providers' => [
						'Facebook' => [
								'enabled' => true,
								'keys' => [
													"id" 					=> $sm_options['ihc_fb_app_id'],
													"secret" 			=> $sm_options['ihc_fb_app_secret'],
								],
						],
				],
		];
		break;
	case 'tw':
		$provider = "Twitter";
		$config = [
				'callback' => \Hybridauth\HttpClient\Util::getCurrentUrl(),
				'providers' => [
						'Twitter' => [
								'enabled' => true,
								'keys' => [
													"id" 					=> $sm_options['ihc_tw_app_key'],
													"secret" 			=> $sm_options['ihc_tw_app_secret'],
								],
						],
				],
		];
		break;
	case 'in':
		$provider = "LinkedIn";
		$config = [
				'callback' => \Hybridauth\HttpClient\Util::getCurrentUrl(),
		    'providers' => [
		        'LinkedIn' => [
		            'enabled' => true,
		            'keys' => [
													"id" 					=> $sm_options['ihc_in_app_key'],
													"secret" 			=> $sm_options['ihc_in_app_secret'],
		            ],
		        ],
		    ],
		];
		break;
	case 'tbr':
		$provider = "Tumblr";
		$config = [
					'callback' => site_url() . '?ihc_action=social_login',
					'providers' => [
							'Tumblr' => [
									'enabled' => true,
									'keys' => [
														"id" 					=> $sm_options['ihc_tbr_app_key'],
														"secret" 			=> $sm_options['ihc_tbr_app_secret'],
									],
							],
					],
		];
		break;
	case 'ig':
		$provider = "Instagram";
		$config = [
				'callback' => \Hybridauth\HttpClient\Util::getCurrentUrl(),
				'providers' => [
						'Instagram' => [
								'enabled' => true,
								'keys' => [
													"id" 					=> $sm_options['ihc_ig_app_id'],
													"secret" 			=> $sm_options['ihc_ig_app_secret'],
								],
						],
				],
		];
		break;
	case 'vk':
		$provider = "Vkontakte";
		$config = [
				'callback' => \Hybridauth\HttpClient\Util::getCurrentUrl(),
				'providers' => [
						'Vkontakte' => [
								'enabled' => true,
								'keys' => [
													"id" 					=> $sm_options['ihc_vk_app_id'],
													"secret" 			=> $sm_options['ihc_vk_app_secret'],
								],
						],
				],
		];
		break;
	case 'goo':
		$provider = "Google";
		$config = [
				'callback' => site_url() . '?ihc_action=social_login',
				'providers' => [
						'Google' => [
								'enabled' => true,
								'keys' => [
													"id" 					=> $sm_options['ihc_goo_app_id'],
													"secret" 			=> $sm_options['ihc_goo_app_secret'],
								],
						],
				],
		];
		break;
}


try {

	$hybridauth = new \Hybridauth\Hybridauth($config);
	$adapter = $hybridauth->authenticate( $provider );
	$user_profile = $adapter->getUserProfile();

	if ($_SESSION['sm_type']=='tbr'){
		$user_profile->identifier = md5($user_profile->identifier);//identifier for tumblr is the profile url, so we made the md5 of it
	}

	if ($_SESSION['sm_action']=='register'){

		//==================================== REGISTER
		//SET COOKIE
		if (!empty($_SESSION['ihc_register'])){
			$data_to_return = indeed_sanitize_array($_SESSION['ihc_register']);
		}
		//username

		if (empty($data_to_return['user_login'])){
			if (!empty($user_profile->username)){
				$data_to_return['user_login'] = $user_profile->username;
			} else if (!empty($user_profile->email)){
				$data_to_return['user_login'] = $user_profile->email;
			} else if (!empty($user_profile->displayName)){
				$data_to_return['user_login'] = $user_profile->displayName;
			}
		}
		//first name
		if (!empty($user_profile->firstName)){
			$data_to_return['first_name'] = $user_profile->firstName;
		}
		//last name
		if (!empty($user_profile->lastName)){
			$data_to_return['last_name'] = $user_profile->lastName;
		}
		//email
		if (!empty($user_profile->email)){
			$data_to_return['user_email'] = $user_profile->email;
			$data_to_return['confirm_email'] = $user_profile->email;
		}
		//avatar
		if (!empty($user_profile->photoURL)){
			$data_to_return['ihc_avatar'] = $user_profile->photoURL;
		}
		//city
		if (!empty($user_profile->city)){
			$data_to_return['city'] = $user_profile->city;
		}
		//county
		if (!empty($user_profile->county)){
			$data_to_return['county'] = $user_profile->county;
		}
		//phone
		if (!empty($user_profile->phone)){
			$data_to_return['phone'] = $user_profile->phone;
		}
		//age
		if (!empty($user_profile->age)){
			$data_to_return['age'] = $user_profile->age;
		}
		setcookie('ihc_register', json_encode($data_to_return), time()+3600, COOKIEPATH, COOKIE_DOMAIN, false);

		//REDIRECT
		$url = urldecode($_SESSION['ihc_current_url']);
		// social media type = social media id (ex. fb=*************)
		$url = add_query_arg(array("ihc_" . $_SESSION['sm_type'] => $user_profile->identifier), $url);
		//level id
		if (!empty($_SESSION['lid'])){
			$url = add_query_arg(array('lid'=>$_SESSION['lid']), $url);
		}
		wp_redirect($url);
		exit;
	} else if ($_SESSION['sm_action']=='login'){
		//=========================================== LOGIN
		//if (!function_exists('ihc_login_social')){
		//	require_once IHC_PATH . 'public/login.php';
		//}
		$arr['sm_type'] = sanitize_text_field($_SESSION['sm_type']);
		$arr['sm_uid'] = $user_profile->identifier;
		$arr['url'] = esc_url($_SESSION['ihc_current_url']);
		if (!empty($_SESSION['is_locker'])){
			$arr['is_locker'] = 1;
		}
		//ihc_login_social($arr);// deprecated since version 11.7
		$ihcLoginForm = new \Indeed\Ihc\LoginForm();
		$ihcLoginForm->doLoginWithSocial( $arr );

	} else if ($_SESSION['sm_action']=='update_user'){
		//=========================================== UPDATE USER
		if (isset($_SESSION['ihc_uid']) && !empty($_SESSION['sm_type']) && !empty($user_profile->identifier)){
			update_user_meta(sanitize_text_field($_SESSION['ihc_uid']), 'ihc_' . sanitize_text_field($_SESSION['sm_type']), $user_profile->identifier);
		}
		if (!empty($_SESSION['ihc_current_url'])){
			$url = urldecode( sanitize_text_field($_SESSION['ihc_current_url']) );
		} else {
			$url = home_url();
		}
		wp_redirect($url);
		exit;
	}
} catch ( Exception $e ){

}
///if nothing happens until here, redirect to home
if (!empty($_SESSION['ihc_current_url'])){
	$url = urldecode($_SESSION['ihc_current_url']);
} else {
	$url = home_url();
}
wp_redirect($url);
exit;
