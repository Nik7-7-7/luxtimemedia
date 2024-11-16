<?php

function uap_print_form_login($meta_arr){
	/*
	 * @param array
	 * @return string
	 */
	$str = '';
	if($meta_arr['uap_login_custom_css']){
		wp_register_style( 'dummy-handle', false );
		wp_enqueue_style( 'dummy-handle' );
		wp_add_inline_style( 'dummy-handle', stripslashes($meta_arr['uap_login_custom_css']) );
	}

	$user_field_id = 'uap_login_username';
	$password_field_id = 'uap_login_password';

	$sm_string = '';
	$captcha = '';
	if (!empty($meta_arr['uap_login_show_recaptcha'])){
		$captchaType = get_option( 'uap_recaptcha_version' );
		if ( $captchaType !== false && $captchaType == 'v3' ){
				$key = get_option('uap_recaptcha_public_v3');
		} else {
				$key = get_option('uap_recaptcha_public');
		}

		if ( !empty( $key ) ){
				$view = new \Indeed\Uap\IndeedView();
				$captchaData = array(
						'class' 		=> '',
						'key'				=> $key,
						'langCode'	=> indeed_get_current_language_code(),
						'type'			=> $captchaType,
				);
				/*
				if ( $captchaType !== false && $captchaType == 'v3' ){

				}else{
					wp_enqueue_script( 'uap-recaptcha-v2', 'https://www.google.com/recaptcha/api.js?hl='.indeed_get_current_language_code() );
				}
				*/


				$captcha .= $view->setTemplate( UAP_PATH . 'public/views/register-captcha.php' )->setContentData( $captchaData, true )->getOutput();
		}

	}

	$str .= '<div class="uap-login-form-wrap '.$meta_arr['uap_login_template'].'">'
			.'<form method="post" id="uap_login_form">'
			. '<input type="hidden" name="uapaction" value="login" />';

	switch ($meta_arr['uap_login_template']){

	case 'uap-login-template-2':
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-username">'.esc_html__('Username', 'uap').':</span>'
				. '<input type="text" value="" name="log" id="' . $user_field_id . '" />'
				. '</div>'
				. '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-pass">'.esc_html__('Password', 'uap').':</span>'
				. '<input type="password" value="" name="pwd" id="' . $password_field_id . '" />'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .= '<div class="uap-form-line-fr uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me', 'uap').'</span> </div>';
		}
		//>>>>

		//<<<< ADDITIONAL LINKS
		if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
		$str .= '<div  class="uap-form-line-fr uap-form-links">';
			if($meta_arr['uap_login_register']){
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			}
			if($meta_arr['uap_login_pass_lost']){
				$pag_id = get_option('uap_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
						 $lost_pass_page = get_home_url();
					}
					$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="uap-form-line-fr uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
	break;

	case 'uap-login-template-3':
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">'
				. '<input type="text" value="" id="' . $user_field_id . '" name="log" placeholder="'.esc_html__('Username', 'uap').'"/>'
				. '</div>'
				. '<div class="uap-form-line-fr">'
				. '<input type="password" value="" name="pwd" id="' . $password_field_id . '" placeholder="'.esc_html__('Password', 'uap').'"/>'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}

		$str .= $captcha;

		$str .=    '<div class="uap-form-line-fr uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.'/>'
				 . '</div>';

		$str .=    '<div class="uap-temp3-bottom">';
		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-remember">'.esc_html__('Remember Me', 'uap').'</span> </div>';
		}
		//>>>>

		//<<<< ADDITIONAL LINKS
		if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
		$str .= '<div  class="uap-form-links">';
			if ($meta_arr['uap_login_register']){
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
$register_page = get_home_url();
}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			}
			if ($meta_arr['uap_login_pass_lost']){
				$pag_id = get_option('uap_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
				if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
					$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>
		$str .= '<div class="uap-clear"></div>';
		$str .= '</div>';

		break;

	case 'uap-login-template-4':
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">'
				. '<i class="fa-uap fa-username-uap"></i><input type="text" id="' . $user_field_id . '" value="" name="log" placeholder="'.esc_html__('Username', 'uap').'"/>'
				. '</div>'
				. '<div class="uap-form-line-fr">'
				. '<i class="fa-uap fa-pass-uap"></i><input type="password" id="' . $password_field_id . '" value="" name="pwd" placeholder="'.esc_html__('Password', 'uap').'"/>'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-remember">'.esc_html__('Remember Me', 'uap').'</span> </div>';
		}
		//>>>>

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="uap-form-line-fr uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.' />'
				 . '</div>';



		//<<<< ADDITIONAL LINKS
		if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
		$str .= '<div  class="uap-form-links">';
			if($meta_arr['uap_login_register']){
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
$register_page = get_home_url();
}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			}
			if($meta_arr['uap_login_pass_lost']){
				$pag_id = get_option('uap_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
				if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
					$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>

		break;
	case 'uap-login-template-5':
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-username">'.esc_html__('Username', 'uap').':</span>'
				. '<input id="' . $user_field_id . '" type="text" value="" name="log" />'
				. '</div>'
				. '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-pass">'.esc_html__('Password', 'uap').':</span>'
				. '<input type="password" id="' . $password_field_id . '" value="" name="pwd" />'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		$str .=    '<div class="uap-temp5-row">';
		$str .=    '<div class="uap-temp5-row-left">';
		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me', 'uap').'</span> </div>';
		}
		//>>>>
		//<<<< ADDITIONAL LINKS
		if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
		$str .= '<div  class="uap-form-line-fr uap-form-links">';
			if($meta_arr['uap_login_register']){
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
$register_page = get_home_url();
}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			}
			if($meta_arr['uap_login_pass_lost']){
				$pag_id = get_option('uap_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
				if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
					$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>
		$str .= '</div>';

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="uap-form-line-fr uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
		$str .= '<div class="uap-clear"></div>';
		$str .= '</div>';

		break;
		case 'uap-login-template-6':
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-username"><b>'.esc_html__('Username', 'uap').':</b></span>'
				. '<input type="text" id="' . $user_field_id . '" value="" name="log" />'
				. '</div>'
				. '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-pass"><b>'.esc_html__('Password', 'uap').':</b></span>'
				. '<input type="password" id="' . $password_field_id . '" value="" name="pwd" />'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//<<<< ADDITIONAL LINKS
		if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
		$str .= '<div  class="uap-form-links">';
			if($meta_arr['uap_login_register']){
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
$register_page = get_home_url();
}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			}
			if($meta_arr['uap_login_pass_lost']){
				$pag_id = get_option('uap_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
				if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
					$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>
		$str .=    '<div class="uap-temp6-row">';
		$str .=    '<div class="uap-temp6-row-left">';
		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me', 'uap').'</span> </div>';
		}
		//>>>>

		$str .= '</div>';

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="uap-form-line-fr uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
		$str .= '<div class="uap-clear"></div>';
		$str .= '</div>';

		break;

		case 'uap-login-template-7':
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-username">'.esc_html__('Username', 'uap').':</span>'
				. '<input type="text" value="" id="' . $user_field_id . '" name="log" />'
				. '</div>'
				. '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-pass">'.esc_html__('Password', 'uap').':</span>'
				. '<input type="password" id="' . $password_field_id . '" value="" name="pwd" />'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		$str .=    '<div class="uap-temp5-row">';
		$str .=    '<div class="uap-temp5-row-left">';
		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me', 'uap').'</span> </div>';
		}
		//>>>>
		//<<<< ADDITIONAL LINKS
		if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
		$str .= '<div  class="uap-form-links">';
			if($meta_arr['uap_login_register']){
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
$register_page = get_home_url();
}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			}
			if($meta_arr['uap_login_pass_lost']){
				$pag_id = get_option('uap_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
				if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
					$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>
		$str .= '</div>';

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		//>>>>
		$str .= '<div class="uap-clear"></div>';
		$str .= '</div>';

		break;

		case 'uap-login-template-8':
			//<<<< FIELDS
			$str .= '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-username-uap"></i><input type="text" id="' . $user_field_id . '" value="" name="log" placeholder="' . esc_html__('Username', 'uap') . '" />'
					. '</div>'
					. '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-pass-uap"></i><input type="password" id="' . $password_field_id . '" value="" placeholder="'.esc_html__('Password', 'uap').'" name="pwd" />'
					. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
					. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
					. '</div>';
			//>>>>
			$str .= $sm_string;
			//<<<< REMEMBER ME
			if($meta_arr['uap_login_remember_me']){
				$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me').'</span> </div>';
			}
			//>>>>


			$str .= $captcha;

			//SUBMIT BUTTON
			$disabled = '';
			if(isset($meta_arr['preview']) && $meta_arr['preview']){
				$disabled = 'disabled';
			}
			$str .=    '<div class="uap-form-line-fr uap-form-submit">'
						. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.' />'
					 . '</div>';
			//>>>>

			//<<<< ADDITIONAL LINKS
			if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
				$str .= '<div  class="uap-form-line-fr uap-form-links">';
					if($meta_arr['uap_login_register']){
						$pag_id = get_option('uap_general_register_default_page');
						if($pag_id!==FALSE){
							$register_page = get_permalink( $pag_id );
							if (!$register_page){
$register_page = get_home_url();
}
							$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
						}
					}
					if($meta_arr['uap_login_pass_lost']){
						$pag_id = get_option('uap_general_lost_pass_page');
						if($pag_id!==FALSE){
							$lost_pass_page = get_permalink( $pag_id );
						if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
							$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
						}
					}
				$str .= '</div>';
			}
			//>>>>

			break;

		case 'uap-login-template-9':
			//<<<< FIELDS
			$str .= '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-username-uap"></i><input type="text" id="' . $user_field_id . '" value="" name="log" placeholder="' . esc_html__('Username', 'uap') . '" />'
					. '</div>'
					. '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-pass-uap"></i><input type="password" id="' . $password_field_id . '" value="" placeholder="'.esc_html__('Password', 'uap').'" name="pwd" />'
					. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
					. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
					. '</div>';
			//>>>>
			$str .= $sm_string;
			//<<<< REMEMBER ME
			if($meta_arr['uap_login_remember_me']){
				$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me').'</span> </div>';
			}
			//>>>>

			//<<<< ADDITIONAL LINKS
			if($meta_arr['uap_login_pass_lost']){
			$str .= '<div  class="uap-form-links">';
				if($meta_arr['uap_login_pass_lost']){
					$pag_id = get_option('uap_general_lost_pass_page');
					if($pag_id!==FALSE){
						$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
						$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
					}
				}
			$str .= '</div>';
			$str .= '<div class="uap-clear"></div>';
			}
			//>>>>

			$str .= $captcha;

			//SUBMIT BUTTON
			$disabled = '';
			if(isset($meta_arr['preview']) && $meta_arr['preview']){
				$disabled = 'disabled';
			}
			$str .=    '<div class="uap-form-line-fr uap-form-submit">'
						. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.' />'
					 . '</div>';
			//>>>>
				if($meta_arr['uap_login_register']){
					$pag_id = get_option('uap_general_register_default_page');
					if($pag_id!==FALSE){
						$register_page = get_permalink( $pag_id );
						if (!$register_page){
$register_page = get_home_url();
}
						$str .= '<div  class="uap-form-links">';
						$str .= '<div class="uap-form-links-reg">' . esc_html__("Don't have an account?", 'uap') . '<a href="'.$register_page.'">'.esc_html__('Sign Up', 'uap').'</a></div>';
						$str .= '</div>';
						$str .= '<div class="uap-clear"></div>';
					}
				}
			break;

		case 'uap-login-template-10':
			//<<<< FIELDS
			$str .= '<div class="uap-form-line-fr">'
				. '<i class="fa-uap fa-username-uap"></i><input type="text" id="' . $user_field_id . '" value=""  name="log" placeholder="'.esc_html__('Username', 'uap').'"/>'
				. '</div>'
				. '<div class="uap-form-line-fr">'
				. '<i class="fa-uap fa-pass-uap"></i><input type="password" id="' . $password_field_id . '" value=""  name="pwd" placeholder="'.esc_html__('Password', 'uap').'"/>'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
			//>>>>
			$str .= $sm_string;
			//<<<< REMEMBER ME
			if($meta_arr['uap_login_remember_me']){
				$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me').'</span> </div>';
			}
			//>>>>

			//<<<< ADDITIONAL LINKS
			if($meta_arr['uap_login_pass_lost']){
			$str .= '<div  class="uap-form-links">';
				if($meta_arr['uap_login_pass_lost']){
					$pag_id = get_option('uap_general_lost_pass_page');
					if($pag_id!==FALSE){
						$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
						$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
					}
				}
			$str .= '</div>';
			$str .= '<div class="uap-clear"></div>';
			}
			//>>>>

			$str .= $captcha;

			//SUBMIT BUTTON
			$disabled = '';
			if(isset($meta_arr['preview']) && $meta_arr['preview']){
				$disabled = 'disabled';
			}
			$str .=    '<div class="uap-form-line-fr uap-form-submit">'
						. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.' />'
					 . '</div>';
			//>>>>
				if($meta_arr['uap_login_register']){
					$pag_id = get_option('uap_general_register_default_page');
					if($pag_id!==FALSE){
						$register_page = get_permalink( $pag_id );
						if (!$register_page){
$register_page = get_home_url();
}
						$str .= '<div  class="uap-form-links">';
						$str .= '<div class="uap-form-links-reg">' . esc_html__("Don't have an account?", 'uap') . '<a href="'.$register_page.'">'.esc_html__('Sign Up', 'uap').'</a></div>';
						$str .= '</div>';
						$str .= '<div class="uap-clear"></div>';
					}
				}
			break;

	case 'uap-login-template-11':
			//<<<< FIELDS
			$str .= '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-username-uap"></i><input type="text" id="' . $user_field_id . '" value="" name="log" placeholder="' . esc_html__('Username', 'uap') . '" />'
					. '</div>'
					. '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-pass-uap"></i><input type="password" id="' . $password_field_id . '" value="" placeholder="'.esc_html__('Password', 'uap').'" name="pwd" />'
					. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
					. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
					. '</div>';
			//>>>>
			$str .= $sm_string;
			//<<<< REMEMBER ME
			if($meta_arr['uap_login_remember_me']){
				$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me').'</span> </div>';
			}
			//>>>>

			//<<<< ADDITIONAL LINKS
			if($meta_arr['uap_login_pass_lost']){
			$str .= '<div  class="uap-form-links">';
				if($meta_arr['uap_login_pass_lost']){
					$pag_id = get_option('uap_general_lost_pass_page');
					if($pag_id!==FALSE){
						$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
						$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
					}
				}
			$str .= '</div>';
			$str .= '<div class="uap-clear"></div>';
			}
			//>>>>

			$str .= $captcha;

			//SUBMIT BUTTON
			$disabled = '';
			if(isset($meta_arr['preview']) && $meta_arr['preview']){
				$disabled = 'disabled';
			}
			$str .=    '<div class="uap-form-line-fr uap-form-submit">'
						. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.' />'
					 . '</div>';
			//>>>>
				if($meta_arr['uap_login_register']){
					$pag_id = get_option('uap_general_register_default_page');
					if($pag_id!==FALSE){
						$register_page = get_permalink( $pag_id );
						if (!$register_page){
$register_page = get_home_url();
}
						$str .= '<div  class="uap-form-links">';
						$str .= '<div class="uap-form-links-reg">' . esc_html__("Don't have an account?", 'uap') . '<a href="'.$register_page.'">'.esc_html__('Sign Up', 'uap').'</a></div>';
						$str .= '</div>';
						$str .= '<div class="uap-clear"></div>';
					}
				}
			break;

	case 'uap-login-template-12':
			//<<<< FIELDS
			$str .= '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-username-uap"></i><input type="text" id="' . $user_field_id . '" value="" name="log" placeholder="' . esc_html__('Username', 'uap') . '" />'
					. '</div>'
					. '<div class="uap-form-line-fr">'
					. '<i class="fa-uap fa-pass-uap"></i><input type="password" id="' . $password_field_id . '" value="" placeholder="'.esc_html__('Password', 'uap').'" name="pwd" />'
					. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
					. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
					. '</div>';
			//>>>>
			$str .= $sm_string;
			//<<<< REMEMBER ME
			if($meta_arr['uap_login_remember_me']){
				$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me').'</span> </div>';
			}
			//>>>>

			//<<<< ADDITIONAL LINKS
			if($meta_arr['uap_login_pass_lost']){
			$str .= '<div  class="uap-form-links">';
				if($meta_arr['uap_login_pass_lost']){
					$pag_id = get_option('uap_general_lost_pass_page');
					if($pag_id!==FALSE){
						$lost_pass_page = get_permalink( $pag_id );
					if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
						$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
					}
				}
			$str .= '</div>';
			$str .= '<div class="uap-clear"></div>';
			}
			//>>>>

			$str .= $captcha;

			//SUBMIT BUTTON
			$disabled = '';
			if(isset($meta_arr['preview']) && $meta_arr['preview']){
				$disabled = 'disabled';
			}
			$str .=    '<div class="uap-form-line-fr uap-form-submit">'
						. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.' />'
					 . '</div>';
			//>>>>
				if($meta_arr['uap_login_register']){
					$pag_id = get_option('uap_general_register_default_page');
					if($pag_id!==FALSE){
						$register_page = get_permalink( $pag_id );
						if (!$register_page){
$register_page = get_home_url();
}
						$str .= '<div  class="uap-form-links">';
						$str .= '<div class="uap-form-links-reg">' . esc_html__("Don't have an account?", 'uap') . '<a href="'.$register_page.'">'.esc_html__('Sign Up', 'uap').'</a></div>';
						$str .= '</div>';
						$str .= '<div class="uap-clear"></div>';
					}
				}
			break;

	case 'uap-login-template-13':
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-username">'.esc_html__('Username', 'uap').':</span>'
				. '<input id="' . $user_field_id . '" type="text" value="" name="log" />'
				. '</div>'
				. '<div class="uap-form-line-fr uap-form-line-fr-margin">' . '<span class="uap-form-label-fr uap-form-label-pass">'.esc_html__('Password', 'uap').':</span>'
				. '<input type="password" id="' . $password_field_id . '" value="" name="pwd" />'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>


		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .=    '<div class="uap-temp5-row">';
			$str .= '<div class="uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me', 'uap').'</span> </div>';
			$str .= '</div>';
		}
		//>>>>
		$str .=    '<div class="uap-temp5-row">';

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .= '<div class="uap-temp5-row-left">';
		$str .=    '<div class="uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.'/>'
				 . '</div>';
		$str .= '</div>';
		//>>>>
		if($meta_arr['uap_login_register']){
			$str .= '<div class="uap-temp5-row-right">';
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
$register_page = get_home_url();
}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			$str .= '</div>';
			}
		$str .= '<div class="uap-clear"></div>';
		$str .= '</div>';

		//<<<< ADDITIONAL LINKS

		if($meta_arr['uap_login_pass_lost']){
			$str .= '<div class="uap-temp5-row">';
			$pag_id = get_option('uap_general_lost_pass_page');
			if($pag_id!==FALSE){
				$lost_pass_page = get_permalink( $pag_id );
			if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
				$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
			}
			$str .= '</div>';
		}



		$str .= $captcha;
		$str .= $sm_string;


		break;

	default:
		//<<<< FIELDS
		$str .= '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-username">'.esc_html__('Username', 'uap').':</span>'
				. '<input type="text" value="" name="log" id="' . $user_field_id . '" />'
				. '</div>'
				. '<div class="uap-form-line-fr">' . '<span class="uap-form-label-fr uap-form-label-pass">'.esc_html__('Password', 'uap').':</span>'
				. '<input type="password" value="" name="pwd" id="' . $password_field_id . '" />'
				. '<span class="uap-hide-pw hide-if-no-js" data-toggle="0" aria-label="'.esc_html__('Show password', 'uap').'">'
				. '<span class="dashicons dashicons-visibility" aria-hidden="true"></span>'
				. '</div>';
		//>>>>
		$str .= $sm_string;
		//<<<< REMEMBER ME
		if($meta_arr['uap_login_remember_me']){
			$str .= '<div class="uap-form-line-fr uap-remember-wrapper"><input type="checkbox" value="forever" name="rememberme" class="uap-form-input-remember" /><span class="uap-form-label-fr uap-form-label-remember">'.esc_html__('Remember Me').'</span> </div>';
		}
		//>>>>

		//<<<< ADDITIONAL LINKS
		if($meta_arr['uap_login_register'] || $meta_arr['uap_login_pass_lost']){
		$str .= '<div  class="uap-form-line-fr uap-form-links">';
			if($meta_arr['uap_login_register']){
				$pag_id = get_option('uap_general_register_default_page');
				if($pag_id!==FALSE){
					$register_page = get_permalink( $pag_id );
					if (!$register_page){
						 $register_page = get_home_url();
					}
					$str .= '<div class="uap-form-links-reg"><a href="'.$register_page.'">'.esc_html__('Register', 'uap').'</a></div>';
				}
			}
			if($meta_arr['uap_login_pass_lost']){
				$pag_id = get_option('uap_general_lost_pass_page');
				if($pag_id!==FALSE){
					$lost_pass_page = get_permalink( $pag_id );
				if (!$lost_pass_page){
$lost_pass_page = get_home_url();
}
					$str .= '<div class="uap-form-links-pass"><a href="'.$lost_pass_page.'">'.esc_html__('Lost your password?', 'uap').'</a></div>';
				}
			}
		$str .= '</div>';
		}
		//>>>>

		$str .= $captcha;

		//SUBMIT BUTTON
		$disabled = '';
		if(isset($meta_arr['preview']) && $meta_arr['preview']){
			$disabled = 'disabled';
		}
		$str .=    '<div class="uap-form-line-fr uap-form-submit">'
					. '<input type="submit" value="'.esc_html__('Log In', 'uap').'" name="Submit" '.$disabled.' class="button button-primary button-large"/>'
				 . '</div>';
		//>>>>
		break;



	}

	$nonce = wp_create_nonce( 'uap_login_nonce' );
	$str .= "<input type='hidden' value='$nonce' name='uap_login_nonce' />";

	$err_msg = esc_html__('Please complete all require fields!', 'uap');
	$custom_err_msg = get_option('uap_login_error_ajax');
	if ($custom_err_msg){
		$err_msg = $custom_err_msg;
	}

	$str .=   '</form>
	<span class="uap-js-login-form-details"
	data-username_selector="#' . $user_field_id . '"
	data-password_selector="#'.$password_field_id.'"
	data-error_message="' . $err_msg . '"></span>
	</div>';



	return $str;
}
