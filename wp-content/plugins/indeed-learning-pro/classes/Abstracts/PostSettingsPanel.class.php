<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('PostSettingsPanel')){
	 return;
}
abstract class PostSettingsPanel{
	/**
	 * @var string
	 */
	protected $post_type;
	/**
	 * @var int
	 */
	protected $post_id;
	/**
	 * @var array
	 */
	protected $options;
	/**
	 * @var string
	 */
	protected $view_file = '';
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}
	/**
	 * @param mixed
	 * @return mixed
	 */
	public function __get($variable=null){
		return $this->$variable;
	}
	/**
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function __set($variable=null, $value=null){
		$this->$variable = $value;
	}
	/**
	 * @param array
	 * @return none
	 */
	public function doSave($save_data=array()){
		$do = true;
		$do = apply_filters('ulp_admin_special_settings_access', $do);
		if (!$do){
			 return;
		}
		if ($this->options){
			$postType = DbUlp::getPostTypeById($this->post_id);
			/// DOUBLE CHECK THIS
			if ($postType!=$this->post_type){
					return;
			}
			/// DOUBLE CHECK THIS
			foreach ($this->options as $key => $default_value){
					if ( isset($save_data[$key]) ){
							update_post_meta($this->post_id, $key, $save_data[$key]);						
					}

			}
		}
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getOutput(){
		$show = true;
		$show = apply_filters('ulp_admin_special_settings_access', $show);
		if (!$show){
			 return '';
		}

		$view = new ViewUlp();
		$view->setTemplate($this->view_file);
		$data = $this->options;
		$data['post_title'] = DbUlp::getPostTitleByPostId($this->post_id);
		$view->setContentData($data);
		return $view->getOutput();
	}
}
