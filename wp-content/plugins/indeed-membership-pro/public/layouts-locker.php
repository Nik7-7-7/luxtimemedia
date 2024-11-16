<?php
function ihc_print_locker_template( $id=false, $meta_arr=false, $preview=false ){

	$str = '';
	if( $id && $id!=-1 ){
		$meta_arr = ihc_return_meta('ihc_lockers', $id);//gettings metas for id
	}

           if ( !isset( $meta_arr['ihc_locker_template'] ) ){
               return ;
           }

	if($meta_arr){
		$login = '';
		if (isset($meta_arr['ihc_locker_login_form']) && $meta_arr['ihc_locker_login_form']){
			$meta_arr_login = ihc_return_meta_arr('login');//standard login form settings

			if ($meta_arr['ihc_locker_additional_links']){
				$meta_arr_login['ihc_login_remember_me'] = 0;
				$meta_arr_login['ihc_login_register'] = 1;
				$meta_arr_login['ihc_login_pass_lost'] = 1;
			} else {
				$meta_arr_login['ihc_login_remember_me'] = 0;
				$meta_arr_login['ihc_login_register'] = 0;
				$meta_arr_login['ihc_login_pass_lost'] = 0;
			}
			$meta_arr_login['ihc_login_template'] = 'ihc-login-template-7';//no template for login
			if (isset($meta_arr['ihc_locker_login_template']) && $meta_arr['ihc_locker_login_template']){
				$meta_arr_login['ihc_login_template'] = $meta_arr['ihc_locker_login_template'];
			}

			if ($preview){
				$meta_arr_login['preview'] = true;
			}

			if (!empty($meta_arr["ihc_locker_display_sm"])){
				$meta_arr_login["ihc_login_show_sm"] = TRUE;
			} else {
				$meta_arr_login["ihc_login_show_sm"] = FALSE;
			}

			$meta_arr_login['is_locker'] = TRUE;

			//$login = ihc_print_form_login($meta_arr_login);
			$loginForm = new \Indeed\Ihc\LoginForm();
			$login = $loginForm->html( $meta_arr_login );

		} else if (isset($meta_arr['ihc_locker_additional_links']) && $meta_arr['ihc_locker_additional_links']){
			$login = ihc_print_links_login();
		}
		$meta_arr['ihc_locker_custom_content'] = ihc_format_str_like_wp($meta_arr['ihc_locker_custom_content']);
		$meta_arr['ihc_locker_custom_content'] = stripslashes($meta_arr['ihc_locker_custom_content']);
		$meta_arr['ihc_locker_custom_content'] = htmlspecialchars_decode($meta_arr['ihc_locker_custom_content']);

		$str = ihc_locker_layout($meta_arr['ihc_locker_template'], $login, $meta_arr['ihc_locker_custom_content']);
		$str = '<div class="ihc-locker-wrap">'.$str.'</div>';


		if($meta_arr['ihc_locker_custom_css']){
			wp_register_style( 'dummy-handle', false );
			wp_enqueue_style( 'dummy-handle' );
			wp_add_inline_style( 'dummy-handle', stripslashes($meta_arr['ihc_locker_custom_css']) );
		}
	}
	return $str;//if something goes wrong return blank string
}


function ihc_locker_layout($template, $login, $lock_msg){
	$content = '';
	switch($template){
		case 1:
			//Default
			$content = "<div class='ihc_locker_1 ihc_locker_1-st'>
							<div>"
								. $lock_msg
								. "</div>"
								. $login
						. "</div>";
		break;

		case 2:
			$content = "<div  class='ihc_locker_2'>"
			. "<div class='lock_content'>"
			. $lock_msg
			. "</div>"
			. "<div class='lock_buttons'>"
			. $login
			. "</div>"
			. "</div>";
		break;

		case 3:

			$content = "<div  class='ihc_locker_3'>"
							. "<div  class='lk_wrapper'></div>"
							. "<div class='lock_content'>"
							. $lock_msg
							. "</div>"
							. "<div class='lock_buttons'>"
							. $login
							. "</div>"
						. "</div>";
		break;

		case 4:

			$content = "<div  class='ihc_locker_4'>"
							. "<div  class='lk_wrapper'></div>"
							. "<div class='lk_left_side'></div>"
							. "<div class='lock_content'>"
							. $lock_msg
							. "<div class='lock_buttons'>"
							. $login
							. "</div>"
							. "</div>"
					  . "</div>";
		break;

		case 5:

			$content = "<div  class='ihc_locker_5'>"
							. "<div class='lk_top_side'></div>"
							. "<div class='lock_content'>"
							. $lock_msg
							. "<div class='lock_buttons'>"
							. $login
							. "</div>"
							. "</div>"
						. "</div>";
		break;

		case 6:

			$content = "<div  class='ihc_locker_6'>"
							. "<div class='lk_top_side'></div>"
							. "<div class='lock_content'>"
							. $lock_msg
							. "<div class='lock_buttons'>"
							. $login
							. "</div>"
							. "</div>"
					. "</div>";
		break;

		case 7:

			$content = "<div  class='ihc_locker_7'>"
							. "<div class='lk_wrapper'></div>"
							. "<div class='lk_top_side'></div>"
							. "<div class='lock_content'>"
							. $lock_msg
							. "<div class='lock_buttons'>"
							. $login
							. "</div>"
					  		. "</div>"
					 . "</div>";
		break;

		case 8:

			$content = "<div  class='ihc_locker_8'>"
							. "<div class='lk_top_side'></div>"
							. "<div class='lk_wrapper_top'></div>"
							. "<div class='lk_wrapper_bottom'></div>"
							. "<div class='lock_content'>"
							. $lock_msg
							. "</div>"
							. "<div class='lock_buttons'>"
							. $login
							. "</div>"
						. "</div>";
		break;

		default:
			$content = '';
		break;
	}
	return $content;
}
