<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpPostAbstract')){
	 return;
}
abstract class UlpPostAbstract{
	/**
	 * @var string
	 */
	protected $post_type = 'post';
	/**
	 * @var int
	 */
	protected $post_id = 0;
	/**
	 * @var object
	 */
	protected $post_main_data = null;
	/**
	 * @var object
	 */
	protected $post_metas = null;
	/**
	 * @var object
	 */
	protected $additional_infos = null;
	abstract protected function run_queries();
	/**
	 * @param int
	 * @param bool
	 * @return none
	 */
	public function __construct($input=0, $run_queries=TRUE){
		$this->post_id = $input;
		$this->uid = ulp_get_current_user();
		if ($run_queries){
			$this->run_queries();
		}
	}
	/**
	 * @param int
	 * @return none
	 */
	public function setUID($input=0){
		$this->uid = $input;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function Title(){
		return $this->post_main_data->post_title;
	}
	/**
	 * @param none
	 * @return int or string
	 */
	public function Author($id=TRUE){
		if ($id){
			 return $this->post_main_data->post_author;
		}else{
			 return DbUlp::getUsernameByUID($this->post_main_data->post_author);
		}
	}
	/**
	 * @param none
	 * @return string
	 */
	public function CreateDate($print_time=TRUE){
		return ulp_print_date_like_wp($this->post_main_data->post_date, $print_time);
	}
	/**
	 * @param none
	 * @return string
	 */
	public function Content(){
		return $this->post_main_data->post_content;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function Excerpt(){
		return $this->post_main_data->post_excerpt;
	}
	/**
	 * @param none
	 * @return none
	 */
	public function Reset(){
        foreach ($this as $k=>$v){
            unset($this->$k);
        }
    }
	/**
	 * @param none
	 * @return int
	 */
	public function GetParent(){
		global $wpdb;
		if ($this->post_type!='ulp_course'){
			$query = $wpdb->prepare( "SELECT ucm.course_id FROM
									{$wpdb->prefix}ulp_courses_modules ucm
									INNER JOIN {$wpdb->prefix}ulp_course_modules_items ucmi
									ON ucm.module_id=ucmi.module_id
									WHERE ucmi.item_id=%d ", $this->post_id );
			return $wpdb->get_var( $query );
		}
	}
	/**
	 * @param string
	 * @return mixed (false if nothing found)
	 */
	public function __get($name){
		if (isset($this->post_main_data->$name)){
			return $this->post_main_data->$name;
		} else if (isset($this->post_metas->$name)){
			return $this->post_metas->$name;
		} else if (isset($this->additional_infos->$name)){
			return $this->additional_infos->$name;
		}
		return FALSE;
	}
	/**
	 * @param none
	 * @return mixed
	 */
	public function Categories($as_string=FALSE){
		$temp = DbUlp::getCategoriesForPost($this->post_id, 'ulp_course_categories');
		if ($as_string){
			if ($temp){
				$temp = implode(',', $temp);
			} else {
				$temp = '-';
			}
		}
		return $temp;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function Permalink(){
			switch ($this->post_type){
					case 'ulp_quiz':
						return Ulp_Permalinks::getForQuiz($this->post_id, $this->courseId);
						break;
					case 'ulp_lesson':
						return Ulp_Permalinks::getForLesson($this->post_id, $this->courseId);
						break;
					case 'ulp_question':
						if(isset($this->quizId)){
							return Ulp_Permalinks::getForQuestion($this->post_id, $this->quizId, $this->courseId);
						}
						break;
					default:
						return get_permalink($this->post_id);
						break;
			}
	}
}
