<?php
/*************** PUBLIC SECTION ***************/
require_once IHC_PATH . 'public/functions.php';

//SHORTCODES
require IHC_PATH . 'public/shortcodes.php';

//INIT ACTION (login, register, logout, reset_pass)
require IHC_PATH . 'public/init.php';
add_action('init', 'ihc_init', 50, 0);

//FILTERS
require IHC_PATH . 'public/filters.php';

//STYLE AND SCRIPTS
add_action('wp_enqueue_scripts', 'ihc_public_head');
function ihc_public_head(){
	global $wp_version;

	wp_enqueue_style( 'ihc_front_end_style', IHC_URL . 'assets/css/style.min.css', [], '12.8' );
	//wp_enqueue_style( 'ihc_front_end_style', IHC_URL . 'assets/css/style.css', [], '12.8' );

	wp_enqueue_style( 'ihc_templates_style', IHC_URL . 'assets/css/templates.min.css', [], '12.8' );
	//wp_enqueue_style( 'ihc_templates_style', IHC_URL . 'assets/css/templates.css', [], '12.8' );

	wp_enqueue_script( 'jquery' );
	//wp_enqueue_script( 'ihc-jquery-ui', IHC_URL . 'assets/js/jquery-ui.min.js', ['jquery'], '12.8' );

	wp_enqueue_script( 'ihc-front_end_js', IHC_URL . 'assets/js/functions.min.js', ['jquery'], '12.8' );

	if ( version_compare ( $wp_version , '5.7', '>=' ) ){
			wp_add_inline_script( 'ihc-front_end_js', "var ihc_site_url='" . get_site_url() . "';" );
			wp_add_inline_script( 'ihc-front_end_js', "var ihc_plugin_url='" . IHC_URL . "';" );
			wp_add_inline_script( 'ihc-front_end_js', "var ihc_ajax_url='" . get_site_url() . '/wp-admin/admin-ajax.php' . "';" );
			wp_localize_script( 'ihc-front_end_js', 'ihc_translated_labels',  ihcJavascriptLabels() );
			wp_add_inline_script( 'ihc-front_end_js', "var ihcStripeMultiply='" . ihcStripeMultiplyForCurrency( get_option( 'ihc_currency' ) ) . "';" );
	} else {
			wp_localize_script( 'ihc-front_end_js', 'ihc_site_url', get_site_url() );
			wp_localize_script( 'ihc-front_end_js', 'ihc_plugin_url', IHC_URL );
			wp_localize_script( 'ihc-front_end_js', 'ihc_ajax_url', get_site_url() . '/wp-admin/admin-ajax.php' );
			wp_localize_script( 'ihc-front_end_js', 'ihc_translated_labels', json_encode( ihcJavascriptLabels() ) );
			wp_localize_script( 'ihc-front_end_js', 'ihcStripeMultiply', "" . ihcStripeMultiplyForCurrency( get_option( 'ihc_currency' ) ) . "" );
	}

	wp_register_style( 'ihc_select2_style', IHC_URL . 'assets/css/select2.min.css', [], '12.8' );
	wp_register_script( 'ihc-select2', IHC_URL . 'assets/js/select2.min.js', ['jquery'], '12.8' );
	wp_register_style( 'ihc_iziModal', IHC_URL . 'assets/css/iziModal.min.css', [], '12.8' );
	wp_register_script( 'ihc_iziModal_js', IHC_URL . 'assets/js/iziModal.min.js', ['jquery'], '12.8' );
	wp_register_script( 'ihc-jquery_upload_file', IHC_URL . 'assets/js/jquery.uploadfile.min.js', ['jquery'], '12.8' );
	wp_register_script( 'ihc-print-this', IHC_URL . 'assets/js/printThis.js', ['jquery'], '12.8' );

	wp_register_script( 'ihc-jquery_form_module', IHC_URL . 'assets/js/jquery.form.js', ['jquery'], '12.8' );

	if ( !isset( $GLOBALS['wp_scripts']->registered['ihc-public-dynamic'] ) ){
			wp_register_script( 'ihc-public-dynamic', IHC_URL . 'assets/js/public.js', ['jquery'], '12.8' );
	}
}

function ihcJavascriptLabels()
{
		return array(
				'delete_level'					=> esc_html__('Are you sure you want to delete this membership?', 'ihc'),
				'cancel_level'					=> esc_html__('Are you sure you want to cancel this membership?', 'ihc'),
		);
}

add_filter('wp_authenticate_user', 'ihc_authenticate_filter', 9998, 3);
function ihc_authenticate_filter($user_data=null, $username='', $password=''){
		if ($user_data==null){
			 return $user_data;
		}
		if (is_object($user_data) && !empty($user_data->roles) && in_array('pending_user', $user_data->roles)){
		$errors = new WP_Error();
        $errors->add('title_error', 'Pending User');
        return $errors;
		}
		return $user_data;
}

/// CHEAT OFF MODULE
include_once IHC_PATH . 'classes/Cheat_Off.class.php';
$cheat_off = new Cheat_Off();
