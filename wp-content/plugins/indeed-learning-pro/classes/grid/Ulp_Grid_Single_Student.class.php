<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Grid_Single_Student')){
   return;
}
if (!interface_exists('Indeed_Grid_Single_Item')){
   require_once ULP_PATH . 'classes/interfaces/Indeed_Grid_Single_Item.php';
}
class Ulp_Grid_Single_Student implements Indeed_Grid_Single_Item{
    private $_shortcode_attributes = [];
    private $_item = null;
    private $_output = '';
    public function set_shortcode_attributes($input_data=array()){
        $this->_shortcode_attributes = $input_data;
    }
    public function build($item){      
      $view = new ViewUlp();
      $view->setTemplate(ULP_PATH . 'views/grid/themes/students/' . $this->_shortcode_attributes['theme'] . '/index.php');
      $view->setContentData([
          'student' => $item,
          'shortcode_attributes' => $this->_shortcode_attributes,
      ], TRUE);
      $this->_output = $view->getOutput();
    }
    public function get_output(){
        return $this->_output;
    }
    public function reset(){
      $this->_item = null;
      $this->_output = '';
      $this->_shortcode_attributes = [];
    }
}
