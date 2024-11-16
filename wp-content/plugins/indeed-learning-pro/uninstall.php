<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ){
   exit();
}
if (!defined('ULP_PATH')){
	define('ULP_PATH', plugin_dir_path(__FILE__));
}
if (!defined('ULP_URL')){
	define('ULP_URL', plugin_dir_url(__FILE__));
}
require_once ULP_PATH . 'utilities.php';
require_once ULP_PATH . 'autoload.php';

// revoke
$ulpElCheck = new \Indeed\Ulp\ElCheck();
$ulpElCheck->doRevoke();

if ( get_option('ulp_keep_data_after_delete') == 1 ){
		return;
}
require_once plugin_dir_path(__FILE__) . 'classes/Db/DbUlp.class.php';

\DbUlp::do_uninstall();
