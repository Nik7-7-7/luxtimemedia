<?php
namespace Indeed\Ulp;
if (!defined('ABSPATH')){
   exit();
}
class CropImage
{
    private $response = array(
                "status"  => 'error',
                "message" => 'Error'
    );
    private $userMetaName = 'ulp_account_page_personal_header';
    private $userId = 0;
    private $saveUserMeta = true;

    public function __construct()
    {
        global $current_user;
        $this->userId = empty($current_user->ID) ? 0 : $current_user->ID;
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }

    public function setSaveUserMeta( $inputValue=true )
    {
        $this->saveUserMeta = $inputValue;
        return $this;
    }

    public function saveImage($filesData=array())
    {
        $inputName = 'img'; /// ulp_upload_image_top_banner

      	$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
      	$temp = explode(".", $filesData[$inputName]["name"]);
      	$extension = end($temp);

        if ($filesData[$inputName]["error"] > 0){
          $this->response = array(
            "status"    => 'error',
            "message"   => 'ERROR Return Code: ' . $filesData[$inputName]["error"]
          );
          return $this;
        }
      	if (!in_array($extension, $allowedExts)){
          $this->response = array(
            "status"    => 'error',
            "message"   => 'Something went wrong. Try again!',
          );
          return $this;
      	}
        $filename = $filesData[$inputName]["tmp_name"];
        list($width, $height) = getimagesize( $filename );
        $uploadId = media_handle_upload($inputName, 0);
        if (!$uploadId){
          $this->response = array(
            "status"    => 'error',
            "message"   => 'Error - on save file'
          );
          return $this;
        }

    		$fileUrl = wp_get_attachment_url( $uploadId );
        list($widthNew, $heightNew) = getimagesize( $fileUrl  );

        $this->response = array(
          "status"  => 'success',
          "url"     => $fileUrl,
          "width"     => $widthNew,
          "height"    => $heightNew,
          "uploadId"  => $uploadId,
        );
        return $this;
    }

    public function cropImage($postData=array())
    {
        $imgUrl = $postData['imgUrl'];
        // original sizes
        $imgInitW = $postData['imgInitW'];
        $imgInitH = $postData['imgInitH'];
        // resized sizes
        $imgW = $postData['imgW'];
        $imgH = $postData['imgH'];
        // offsets
        $imgY1 = $postData['imgY1'];
        $imgX1 = $postData['imgX1'];
        // crop box
        $cropW = $postData['cropW'];
        $cropH = $postData['cropH'];
        // rotation angle
        $angle = $postData['rotation'];


        // uncomment line below to save the cropped image in the same location as the original image.
        $uploadDirData = wp_upload_dir(date('Y/m'));
        $fileName = basename($imgUrl);
        $fileNameWithoutType = explode('.', $fileName);
        $fileNameWithoutTypeStr = isset($fileNameWithoutType[0]) ? $fileNameWithoutType[0] : $fileName;
        $output_filename = $uploadDirData['path'] . '/' . $fileNameWithoutTypeStr;
        $urlPath = $uploadDirData['url'];

        $what = getimagesize($imgUrl);

        switch(strtolower($what['mime'])){
            case 'image/png':
                $img_r = imagecreatefrompng($imgUrl);
            		$source_image = imagecreatefrompng($imgUrl);
            		$type = '.png';
                break;
            case 'image/jpeg':
                $img_r = imagecreatefromjpeg($imgUrl);
            		$source_image = imagecreatefromjpeg($imgUrl);
            		$type = '.jpeg';

                                /// check if really is jpeg
                $pieces = explode('.', $imgUrl);
                end($pieces);
                if ( current($pieces)=='jpg' ){
                    $type = '.jpg';
                }
                break;
            case 'image/gif':
                $img_r = imagecreatefromgif($imgUrl);
            		$source_image = imagecreatefromgif($imgUrl);
            		$type = '.gif';
                break;
            default:
              $this->response = array(
                          "status"  => 'error',
                          "message" => 'Image type not supported'
              );
              return $this;
        }


            // resize the original image to size of editor
            $resizedImage = imagecreatetruecolor($imgW, $imgH);
        	imagecopyresampled($resizedImage, $source_image, 0, 0, 0, 0, $imgW, $imgH, $imgInitW, $imgInitH);
            // rotate the rezized image
            $rotated_image = imagerotate($resizedImage, -$angle, 0);
            // find new width & height of rotated image
            $rotated_width = imagesx($rotated_image);
            $rotated_height = imagesy($rotated_image);
            // diff between rotated & original sizes
            $dx = $rotated_width - $imgW;
            $dy = $rotated_height - $imgH;
            // crop rotated image to fit into original rezized rectangle
        	$cropped_rotated_image = imagecreatetruecolor($imgW, $imgH);
        	imagecolortransparent($cropped_rotated_image, imagecolorallocate($cropped_rotated_image, 0, 0, 0));
        	imagecopyresampled($cropped_rotated_image, $rotated_image, 0, 0, $dx / 2, $dy / 2, $imgW, $imgH, $imgW, $imgH);
        	// crop image into selected area
        	$final_image = imagecreatetruecolor($cropW, $cropH);
        	imagecolortransparent($final_image, imagecolorallocate($final_image, 0, 0, 0));
        	imagecopyresampled($final_image, $cropped_rotated_image, 0, 0, $imgX1, $imgY1, $cropW, $cropH, $cropW, $cropH);
        	// finally output png image
        	//imagepng($final_image, $output_filename.$type, $png_quality);
          $rand = rand(1, 10000);
          $temporaryFileName = $output_filename . $rand . $type;
        	imagejpeg( $final_image, $temporaryFileName, 100 );

          $fileUrl = $urlPath . '/' . $fileNameWithoutTypeStr . $rand . $type;
          $oldFileUrl = $urlPath . '/' . $fileNameWithoutTypeStr . $type;
          \DbUlp::modifyGuid( $postData['uploadId'], $fileUrl );
          \DbUlp::updateAttachmentMetadataFileUrl( $postData['uploadId'], $fileUrl);

          if ( $this->saveUserMeta ){
              update_user_meta($this->userId, $this->userMetaName, $fileUrl);
          }

        	$this->response = array(
        	    "status" => 'success',
        	    "url" => $fileUrl,
          );

        return $this;
    }

    public function getResponse($asJson=true)
    {
        if ($asJson){
            return json_encode($this->response);
        } else {
            return $this->response;
        }
    }

}
