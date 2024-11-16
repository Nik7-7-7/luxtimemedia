<?php
require_once("../../../wp-load.php");



/// feature image - custom post type
if (!empty($_GET['do_upload_image'])){
  $uploadImage = new \Indeed\Ulp\UploadImage();
  if (isset($_FILES['img'])){
      $response = $uploadImage->saveImage($_FILES)
                              ->getResponse();
      echo esc_ulp_content($response);
      die;
  }	else if (isset($_POST['imgUrl'])){
      $response = $uploadImage->cropImage($_POST)
                              ->getResponse();
      echo esc_ulp_content($response);
      die;
  }
}

/// banner image
if (isset($_FILES['img'])){
    //// upload account page banner
    $cropImage = new Indeed\Ulp\CropImage();
    $response = $cropImage->saveImage($_FILES)
                          ->getResponse();
    echo esc_ulp_content($response);
    die;
}	else if (isset($_POST['imgUrl'])){
    $cropImage = new Indeed\Ulp\CropImage();
    if ( isset($_POST['customIdentificator']) && $_POST['customIdentificator']=='image' ){
        $cropImage->setSaveUserMeta( false );
    }
    $response = $cropImage->cropImage($_POST)
                          ->getResponse();
    echo esc_ulp_content($response);
    die;
}
