<?php

if (!defined('WP_UNINSTALL_PLUGIN')){
	exit();
}

if ( !defined('UAP_PATH')){
    define( 'UAP_PATH', plugin_dir_path(__FILE__) );
}

if ( !defined('UAP_URL')){
    define( 'UAP_URL', plugin_dir_url(__FILE__) );
}

require_once UAP_PATH . 'autoload.php';
require_once UAP_PATH . 'utilities.php';

$uapElCheck = new \Indeed\Uap\ElCheck();
$uapElCheck->dorvk();


if ( get_option('uap_keep_data_after_delete') == 1 ){
		return;
}


include UAP_PATH . 'classes/UapDb.class.php';
$uap_uninstall_object = new UapDb();
$uap_uninstall_object->unistall();
