<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('UlpPostAbstract')){
	 require_once ULP_PATH . 'classes/Abstracts/UlpPostAbstract.class.php';
}
if (class_exists('UlpCourse')){
	 return;
}
class UlpCertificate extends UlpPostAbstract{
  /**
	 * @var string
	 */
	protected $post_type = 'ulp_course';
	/**
	 * @var int
	 */
	protected $post_id = 0;
	/**
	 * @param int
	 */
	protected $uid = 0;
  public function __construct(){}
}
