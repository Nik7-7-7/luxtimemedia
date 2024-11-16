<?php
if (empty($no_load)){
	require_once '../../../wp-load.php';
}
require_once IHC_PATH . 'classes/Ihc_API.class.php';
$api = new Ihc_API();
if ($api->is_enabled() && $api->is_safe()){
	if ( empty( $no_load ) ){
			return $api->get_result();
	} else {
			$api->get_result();
			die;
	}
} else {
	echo esc_ump_content('Access denied!');
	die();
}
