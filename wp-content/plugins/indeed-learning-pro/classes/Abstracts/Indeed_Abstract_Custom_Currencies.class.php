<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('Indeed_Abstract_Custom_Currencies')){
   return;
}
abstract class Indeed_Abstract_Custom_Currencies{
  protected $_option_name = '';
  protected $_post_key_name = 'new_currency_code';
  protected $_post_value_name = 'new_currency_name';
  public function __construct(){}
  public function add($input_data=array()){
      $data = $this->getAll();
      if (empty($data [$input_data[$this->_post_key_name]])){
          $data [$input_data[$this->_post_key_name]] = $input_data [$this->_post_value_name];
      }
      update_option($this->_option_name, $data);
  }
  public function delete($key=''){
      $data = $this->getAll();
      if (!empty($data [$key])){
          unset($data [$key]);
      }
      update_option($this->_option_name, $data);
  }
  public function getAll(){
      return get_option($this->_option_name);
  }
  public function getValueByKey($key=''){
      $data = $this->getAll();
      if (!empty($data [$key])){
          return $data [$key];
      }
      return FALSE;
  }
}
