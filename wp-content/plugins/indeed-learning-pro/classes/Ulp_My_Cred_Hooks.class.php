<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_My_Cred_Hooks')){
   return;
}
class Ulp_My_Cred_Hooks{
    public function __construct(){
        add_filter('mycred_setup_hooks', array($this, 'ulp_my_cred_setup_hooks'));
        add_action('mycred_load_hooks', array($this, 'ulp_mycredpro_load_custom_hook'));
    }
    public function ulp_my_cred_setup_hooks($installed){
        require_once ULP_PATH . 'classes/Ulp_My_Cred.class.php';
        $installed['ulp_mycred'] = array(
      		'title'       => esc_html__('Ultimate Learning Pro', 'ulp'),
      		'description' => esc_html__('Ultimate Learning Pro', 'ulp'),
      		'callback'    => array('Ulp_My_Cred')
      	);
      	return $installed;
    }
    public function ulp_mycredpro_load_custom_hook(){
        require_once ULP_PATH . 'classes/Ulp_My_Cred.class.php';
    }
}
