<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Print_Certificate')){
   return;
}
class Ulp_Print_Certificate{
    private $_certificate_template_id = 0;
    private $_certificate_user_id = 0;
    private $_uid = 0;
	private $certificate_title = '';
    public function __construct($certificate_template_id=0, $certificate_user_id=0, $uid=0){
        $this->_certificate_template_id = $certificate_template_id;
        $this->_certificate_user_id = $certificate_user_id;
        $this->_uid = $uid;
    }
    public function getOutput(){
        $string = '';
        if ($this->_certificate_template_id){
            /// admin empty
            $meta ['grade'] = 8;
            $meta ['details'] = '';
            $meta ['obtained_date'] = date("Y-m-d h:i:s", time());
            $meta ['title'] = DbUlp::getPostTitleByPostId($this->_certificate_template_id);
            $meta ['content'] = DbUlp::getPostContentByPostId($this->_certificate_template_id);
            $meta ['uid'] = 0;
            $meta ['course_id'] = 0;
        } else {
            /// get from db
            require_once ULP_PATH . 'classes/Db/Db_User_Certificates.class.php';
            $Db_User_Certificates = new Db_User_Certificates();
            $meta = $Db_User_Certificates->getCertificateById($this->_certificate_user_id);
            $meta ['title'] = DbUlp::getPostTitleByPostId($meta['certificate_id']);
            $meta ['content'] = DbUlp::getPostContentByPostId($meta['certificate_id']);
      	
            $meta ['image'] = get_the_post_thumbnail_url( $meta['certificate_id'], 'large' );
        }
        $pass_arg = ['{grade}' => $meta ['grade'], '{obtained_date}' => ulp_print_date_like_wp($meta ['obtained_date'], FALSE)];
        $meta ['title'] = ulp_replace_constants($meta ['title'], $meta ['uid'], $meta ['course_id'], $pass_arg );
        $meta ['content'] = ulp_replace_constants($meta ['content'], $meta ['uid'], $meta ['course_id'], $pass_arg );
        $meta ['wrapp_id'] = 'ulp_certificate_' . $meta ['certificate_id'];

		$this->certificate_title =  $meta ['title'];

        $view = new ViewUlp();
      	$view->setTemplate(ULP_PATH . 'views/templates/certificate.php');
      	$string = $view->setContentData($meta);
      	return $this->_wrapp_into_popup($view->getOutput($string));
    }
    private function _wrapp_into_popup($input=''){
    	  $data ['content'] = $input;
      	$data ['title'] = $this->certificate_title;
        $view = new ViewUlp();
      	$view->setTemplate(ULP_PATH . 'views/templates/popup.php');
      	$view->setContentData($data);
      	return $view->getOutput();
    }
}
