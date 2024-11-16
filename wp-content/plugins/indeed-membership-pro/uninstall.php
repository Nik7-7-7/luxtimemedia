<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ){
  exit();
}
if ( !defined('IHC_PATH')){
    define( 'IHC_PATH', plugin_dir_path(__FILE__) );
}
require_once IHC_PATH . 'utilities.php';
require_once IHC_PATH . 'autoload.php';

// revoke
$ihcElCheck = new \Indeed\Ihc\Services\ElCheck();
$ihcElCheck->doUnLnk();

if ( get_option('ihc_keep_data_after_delete') == 1 ){
  return;
}
// remove data
require_once plugin_dir_path(__FILE__) . 'classes/Ihc_Db.class.php';
Ihc_Db::do_uninstall();
require_once plugin_dir_path(__FILE__) . 'classes/Old'.'Logs.php';
$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
$ol_dL_ogs = new $class();
$ol_dL_ogs->ECP();
$ol_dL_ogs->WECP();
