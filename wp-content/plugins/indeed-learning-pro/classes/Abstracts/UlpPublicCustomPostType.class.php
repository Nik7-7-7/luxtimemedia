<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpPublicCustomPostType')){
	 return;
}
abstract class UlpPublicCustomPostType{
	/**
	 * @var string
	 */
	protected $url = '';
	/**
	 * @var string
	 */
	protected $clean_url = '';
	/**
	 * @var int
	 */
	protected $post_id = 0;
	/**
	 * @var array
	 */
	protected $metas = array();
	/**
	 * @var string
	 */
	protected $content = '';
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}
	/**
	 * @param none
	 * @return int
	 */
	protected function setPostId(){
		global $post;
		$this->post_id = isset($post->ID) ? $post->ID : 0;
	}
	/**
	 * @param none
	 * @return string
	 */
	protected function setURL(){
		$this->url = ULP_CURRENT_URI;
	}
}
