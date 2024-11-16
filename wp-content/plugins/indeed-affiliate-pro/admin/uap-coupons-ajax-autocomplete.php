<?php
if ( empty( $viaWpAjax ) ){
		require_once '../../../../wp-load.php';
		require_once '../utilities.php';
}

if ( !isset( $_GET['uapAdminAjaxNonce'] ) || !wp_verify_nonce( $_GET['uapAdminAjaxNonce'], 'uapAdminAjaxNonce' ) ) {
		die( "Not allowed" );
}

if (!empty($_GET['term'])){
	global $indeed_db;

	if (!empty($_GET['source'])){
		/// SEARCH FOR PRODUCTS
		$data = $indeed_db->search_coupon_code_by_source_and_term($_GET['source'], $_GET['term']);
	} else if (!empty($_GET['users'])){
		/// SEARCH FOR USERS
		$data = $indeed_db->search_affiliates_by_char($_GET['term']);
	}

	if (!empty($data)){
		$i = 0;
		foreach ($data as $k=>$v){
			$return[$i]['id'] = $k;
			$return[$i]['label'] = $v;
			$i++;
		}
		echo json_encode($return);
	}
}

die();
