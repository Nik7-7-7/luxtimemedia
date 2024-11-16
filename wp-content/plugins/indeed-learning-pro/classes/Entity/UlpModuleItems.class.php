<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpModuleItems')){
	 return;
}
class UlpModuleItems {
	/**
	 * @var int
	 */
	private $course_id;
	/**
	 * @var array
	 */
	private $modules = array();
	/**
	 * @var array
	 */
	private $module_items_meta = array();
	/**
	 * @var int
	 */
	private $last_module_key = 0;
	/**
	 * @var int
	 */
	private $current_module_index = 0;
	/**
	 * @var array
	 */
	private $current_module = array();
	/**
	 * @var array
	 */
	private $children = array();
	/**
	 * @var int
	 */
	private $first_children_key = 0;
	/**
	 * @var int
	 */
	private $last_children_key = 0;
	/**
	 * @var int
	 */
	private $current_children_index = 0;
	/**
	 * @var int
	 */
	private $children_id = 0;
	/**
	 * @var string
	 */
	private $current_base_url = '';
	/**
	 * @var int
	 */
	private $limit = 0;
	/**
	 * @var int
	 */
	private $offset = 0;
	/**
	 * @var string
	 */
	private $pagination = '';
	/**
	 * @var int
	 */
	private $current_page = 1;
	/**
	 * @var int
	 */
	private $total_items = 0;
	/**
	 * @var int
	 */
	private $items_per_page = 5;
	/**
	 * @var int
	 */
	private $uid = 0;
	/**
	 * @var string
	 */
	private $current_url = null;
	
	/**
	 * @param int (course id)
	 * @return none
	 */
	public function __construct($input=0){
		$this->uid = ulp_get_current_user();
		$this->course_id = $input;
		$this->setBaseUrl();
		/// post meta for courses
		$this->module_items_meta = DbUlp::getPostMetaGroup($this->course_id, 'course_special_settings');

		/// get modules
		$this->modules = $this->get_modules_from_db();
		$this->setPagination();
	}
	public function setBaseUrl(){
			$this->current_url = ULP_CURRENT_URI;
			$this->current_base_url = remove_query_arg('ulp_page', $this->current_url);
	}
	/**
	 * @param none
	 * @return array
	 */
	private function get_modules_from_db(){
		require_once ULP_PATH . 'classes/Db/DbCoursesModulesUlp.class.php';
		$DbCoursesModulesUlp = new DbCoursesModulesUlp();
		$this->limit = $this->module_items_meta['ulp_modules_per_page'];
		$this->items_per_page = $this->module_items_meta['ulp_modules_per_page'];
		$this->current_page = (empty($_GET['ulp_page'])) ? 1 : sanitize_text_field($_GET['ulp_page']);
		$this->total_items = $DbCoursesModulesUlp->countModules($this->course_id);
		if ($this->current_page>1){
			$this->offset = ( $this->current_page - 1 ) * $this->limit;
		} else {
			$this->offset = 0;
		}
		if ($this->offset + $this->limit>$this->total_items){
			$this->limit = $this->total_items - $this->offset;
		}
		return $DbCoursesModulesUlp->getAllModulesForCourse($this->course_id, $this->limit, $this->offset); ///getting the modules
	}
	private function setPagination(){
		require_once ULP_PATH . 'classes/IndeedPagination.class.php';
		$pagination = new IndeedPagination(array(
				'base_url' => $this->current_base_url,
				'param_name' => 'ulp_page',
				'total_items' => $this->total_items,
				'items_per_page' => $this->items_per_page,
				'current_page' => $this->current_page,
		));
		$this->pagination = $pagination->output();
	}
	public function hasPagination(){
			return ($this->pagination=='') ? FALSE : TRUE;
	}
	public function Pagination(){
			return $this->pagination;
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function has_modules(){
		end($this->modules);
		$this->last_module_key = key($this->modules);
		reset($this->modules);
		return count($this->modules);
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function have_modules(){
		if ($this->current_module_index==-2){
			return FALSE;
		} else {
			$key = key($this->modules);
			next($this->modules);
			if ($this->last_module_key==$key){
				/// last one
				$this->current_module_index = -2;
			} else {
				$this->current_module_index = $key;
			}
			$this->current_module = $this->modules[$key];
			return TRUE;
		}
	}
	/**
	 * @param none
	 * @return string
	 */
	public function Name(){
		if (isset($this->current_module['module_name'])){
				$domain = 'uap';
				$languageCode = indeed_get_current_language_code();
				$wmplName = 'module_name_' . $this->current_module['module_id'];
				$this->current_module['module_name'] = apply_filters( 'wpml_translate_single_string', $this->current_module['module_name'], $domain, $wmplName, $languageCode );
				return stripslashes($this->current_module['module_name']);
		}
		return '';
	}
	/**
	 * @param none
	 * @return array
	 */
	public function select_children_from_db(){
		if (isset($this->current_module['module_id'])){
			require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
			$DbModuleItems = new DbModuleItems();
			$this->children = $DbModuleItems->getAllModuleItemsByModuleId($this->current_module['module_id'], '', true); /// add '', true since v 1.0.6
			$this->children = ulp_array_value_of_child_become_value($this->children, 'item_id');
			/// ReOrder
			$this->children = apply_filters('ulp_list_course_children', $this->children, $this->uid, $this->course_id); /// will pass and array of elements (0=>$post_id, 1=>$post_id, ...)
			$this->children = DbUlp::course_reorder_items($this->children, $this->module_items_meta['ulp_modules_order_items_type'], $this->module_items_meta['ulp_modules_order_items_by']);
 		  /// SET FIRST CHILDREN INDEX
			$this->set_first_children_index();
			/// SET LAST CHILDREN INDEX
			$this->set_last_children_index();
			/// SET CURRENT CHILDREN INDEX
			$this->current_children_index = $this->first_children_key;
		} else {
			$this->children = array();
		}
	}

	/**
	 * @param none
	 * @return boolean
	 */
	public function has_children(){
		$this->select_children_from_db();
		return count($this->children);
	}
	public function countChildren(){
				return count($this->children);
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function have_children(){
		if ($this->current_children_index==-2){
			return FALSE;
		} else {
			$key = key($this->children);
			next($this->children);
			if ($this->last_children_key==$key){
				/// last one
				$this->current_children_index = -2;
			} else {
				$this->current_children_index = $key;
			}
			$this->children_id = $this->children[$key];
			return TRUE;
		}
	}
	/**
	 * @param none
	 * @return int
	 */
	public function ChildId(){
		return $this->children_id;
	}
	/**
	 * @param none
	 * @return int
	 */
	public function countLessons(){
		require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
		$DbModuleItems = new DbModuleItems();

		$lessons = $DbModuleItems->getCountModuleItems($this->current_module['module_id'], 'ulp_lesson');
		return $lessons;
	}
	/**
	 * @param none
	 * @return int
	 */
	public function countQuizes(){
		require_once ULP_PATH . 'classes/Db/DbModuleItems.class.php';
		$DbModuleItems = new DbModuleItems();

		$quizes = $DbModuleItems->getCountModuleItems($this->current_module['module_id'], 'ulp_quiz');
		return $quizes;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function ChildType(){
		return DbUlp::getPostTypeById($this->children_id);
	}
	/**
	 * @param none
	 * @return int
	 */
	public function FirstChildId(){
		if (isset($this->children[$this->first_children_key])){
			return $this->children[$this->first_children_key];
		}
	}
	/**
	 * @param none
	 * @return none
	 */
	private function set_first_children_index(){
		reset($this->children);
		$this->first_children_key = key($this->children);
	}
	/**
	 * @param none
	 * @return none
	 */
	private function set_last_children_index(){
		end($this->children);
		$this->last_children_key = key($this->children);
		reset($this->children);
	}
}
