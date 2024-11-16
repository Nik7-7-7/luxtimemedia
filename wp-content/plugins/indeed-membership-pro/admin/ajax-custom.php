<?php
if ( empty( $viaWpAjax ) ){
		require_once '../../../../wp-load.php';
		require_once '../utilities.php';
}

if ( !isset( $_GET['ihcAdminAjaxNonce'] ) || !wp_verify_nonce( sanitize_text_field($_GET['ihcAdminAjaxNonce']), 'ihcAdminAjaxNonce' ) ) {
		die( "Not allowed" );
}

if (!empty($_GET['term'])){
	if (isset($_GET['woo_type']) && sanitize_text_field($_GET['woo_type']) === 'category'){
		$data = Ihc_Db::search_woo_product_cats( sanitize_text_field( $_GET['term'] ) );
	} else {
		$data = Ihc_Db::search_woo_products( sanitize_text_field($_GET['term']) );
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
