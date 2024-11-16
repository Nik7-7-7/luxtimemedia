<?php
/*
 * Upload files via Ajax
 */
require_once("../../../../../wp-load.php");
if (isset($_FILES['avatar'])){
	//========== handle avatar image
	if ($_FILES['avatar']['type']=='image/png' || $_FILES['avatar']['type']=='image/gif' || $_FILES['avatar']['type']=='image/jpeg'){
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$arr['id'] = media_handle_upload('avatar',0);
		if ($arr['id']){
			$arr['url'] =  wp_get_attachment_url($arr['id']);
			$arr['secret'] = md5($arr['url']);
			echo json_encode($arr);
		} else {
			echo esc_ulp_content('');
		}
	}
} else if (isset($_FILES['ulp_file'])){
	//============= handle upload file
	//debug
	//file_put_contents( "upload_media.log", $_FILES['uap_file']['type'], FILE_APPEND | LOCK_EX );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	$arr['id'] = media_handle_upload('ulp_file',0);
	if ($arr['id']){
		$arr['url'] =  wp_get_attachment_url( $arr['id'] );
		$arr['secret'] = md5($arr['url']);
	}
	$arr['name'] = $_FILES['ulp_file']['name'];
	if (in_array($_FILES['ulp_file']['type'], array('image/gif','image/jpg','image/jpeg','image/png'))){
		$arr['type'] = 'image';
	} else {
		$arr['type'] = 'other';
	}
	echo json_encode($arr);
}
