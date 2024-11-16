<?php
/*
Plugin Name: Indeed Ultimate Learning Pro
Plugin URI: https://www.wpindeed.com/
Description: A Premium WordPress plugin ready to manage a full e-learning platform.
Version: 3.9
Author: WPIndeed Development
Author URI: https://www.wpindeed.com
Text Domain: ulp
Domain Path: /languages

@package        Indeed Ultimate Learning Pro
@author           WPIndeed Development
*/
update_option('ulp_license_set', 1);
/// require > PHP 5.4
if (!defined('ULP_PATH')){
	define('ULP_PATH', plugin_dir_path(__FILE__));
}
if (!defined('ULP_URL')){
	define('ULP_URL', plugin_dir_url(__FILE__));
}
if (!defined('ULP_PROTOCOL')){
	if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
		|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&  $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
				define('ULP_PROTOCOL', 'https://');
	} else {
				define('ULP_PROTOCOL', 'http://');
	}
}
/// language
add_action('init', 'ulpLoadLanguage');
function ulpLoadLanguage(){
	load_plugin_textdomain( 'ulp', false, dirname(plugin_basename(__FILE__)).'/languages/' );
}

require_once ULP_PATH . 'utilities.php';
require_once ULP_PATH . 'autoload.php';

if (!defined('ULP_PLUGIN_VER')){
	define('ULP_PLUGIN_VER', indeed_get_plugin_version(ULP_PATH . 'indeed-learning-pro.php') );
}
if (!defined('ULP_LICENSE_SET')){
	define('ULP_LICENSE_SET', get_option('ulp_license_set'));
}

if (!defined('ULP_CURRENT_URI')){
		define('ULP_CURRENT_URI', ULP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
}

require_once ULP_PATH . 'classes/MainUlp.class.php';
MainUlp::run();

add_action('activated_plugin','save_error');
function save_error(){
    update_option('plugin_error',  ob_get_contents());
}

add_filter( 'et_grab_image_setting', 'ulpDiviGrabImage', 999, 1 );
if ( !function_exists( 'ulpDiviGrabImage' ) ):
function ulpDiviGrabImage( $bool=true )
{
		return false;
}
endif;
