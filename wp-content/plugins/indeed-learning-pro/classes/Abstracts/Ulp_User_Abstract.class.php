<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('Ulp_User_Abstract')){
	 return;
}
abstract class Ulp_User_Abstract{
  /**
	 * @var int
	 */
	  protected $uid = 0;

    public function Username(){
        return DbUlp::getUsernameByUID($this->uid);
    }

    public function Email(){
        return DbUlp::getUserColByUid($this->uid, 'user_email');
    }

    public function Avatar(){
				return DbUlp::getAuthorImage($this->uid);
		}

    public function setUid($input=0){
        $this->uid = $input;
				return $this;
    }

    public function Name(){
        return DbUlp::getUserFulltName($this->uid);
    }

		public function Biography()
		{
				return get_user_meta($this->uid, 'description', true);
		}


}
