<?php
/*
 * Upload files via Ajax
 */
if ( empty( $viaWpAjax ) ){
		require_once("../../../../wp-load.php");
}

if ( !isset( $_GET['publicn'] ) || !wp_verify_nonce( $_GET['publicn'], 'publicn' ) ) {
		die( "Not allowed" );
}


// security layer
global $indeed_db;
$uid = indeed_get_uid();
$access = true;
if ( !$uid ){
		$hash = isset( $_COOKIE['uapMedia'] ) ? $_COOKIE['uapMedia'] : '';
		if ( empty($hash) ){
				$access = false;
		}
		$hash = sanitize_textarea_field( $hash );
		$exists = $indeed_db->doesMediaHashExists( $hash );
		if ( !$exists ){
				$access = false;
		}
}
// end of security layer

if ( $access ){
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
				echo esc_html('');
			}
		}
	} else if (isset($_FILES['uap_file'])){
		//============= handle upload file
			require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$arr['id'] = media_handle_upload('uap_file',0);
		if ($arr['id']){
			$arr['url'] =  wp_get_attachment_url( $arr['id'] );
			$arr['secret'] = md5($arr['url']);
		}
		$arr['name'] = $_FILES['uap_file']['name'];
		if (in_array($_FILES['uap_file']['type'], array('image/gif','image/jpg','image/jpeg','image/png'))){
			$arr['type'] = 'image';
		} else {
			$arr['type'] = 'other';
		}
		echo json_encode($arr);
	}
	else if (isset($_FILES['img'])){
			//// upload account page banner
			$cropImage = new Indeed\Uap\CropImage();
			echo esc_uap_content( $cropImage->saveImage($_FILES)->getResponse() );
	}

	else if (isset($_POST['imgUrl'])){
			$cropImage = new Indeed\Uap\CropImage();
			$_POST['imgUrl'] = sanitize_text_field( $_POST['imgUrl'] );
			$_POST['customIdentificator'] = sanitize_text_field( $_POST['customIdentificator'] );
			if ( isset($_POST['customIdentificator']) && $_POST['customIdentificator']=='image' ){
					$cropImage->setSaveUserMeta( false );
			}
			echo esc_uap_content($cropImage->cropImage($_POST)
										 ->getResponse());
	}
}
