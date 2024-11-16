<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('CoursesUlp')){
	 return;
}
if (!trait_exists('RegisterCourseTags')){
	require_once ULP_PATH . 'classes/traits/RegisterCourseTags.php';
}

class CoursesUlp extends CustomPostTypeUlp{

	use RegisterCourseTags;
	/*
	 * @var string
	 */
	protected $post_type_slug = 'ulp_course';
	/*
	 * @var int
	 */
	protected $menu_position = 5;
	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){
		/// post type
		$this->labels = array(
								    'name'               => esc_html__('Courses', 'ulp'),
								    'singular_name'      => esc_html__('Course', 'ulp'),
								    'add_new'            => esc_html__('Add new Course', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Course', 'ulp'),
								    'edit_item'          => esc_html__('Edit Course', 'ulp'),
								    'new_item'           => esc_html__('New Course', 'ulp'),
								    'all_items'          => esc_html__('All Courses', 'ulp'),
								    'view_item'          => esc_html__('View Course', 'ulp'),
								    'search_items'       => esc_html__('Search Course', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Courses', 'ulp'),
		);
		/// taxonomy
		$this->taxonomy_labels = array(
									'name'              => esc_html__('Courses Categories', 'ulp'),
									'singular_name'     => esc_html__('Course Category', 'ulp'),
									'search_items'      => esc_html__('Search Course Category', 'ulp'),
									'all_items'         => esc_html__('Courses Categories', 'ulp'),
									'parent_item'       => '',
									'parent_item_colon' => '',
									'edit_item'         => esc_html__('Edit', 'ulp'),
									'update_item'       => esc_html__('Update', 'ulp'),
									'add_new_item'      => esc_html__('Add new course category', 'ulp'),
									'new_item_name'     => esc_html__('Add new course category', 'ulp'),
									'menu_name'         => esc_html__('Courses Categories', 'ulp'),
		);
		$this->taxonomy_slug = 'ulp_course_categories';
		///meta boxes
		$this->metaBoxes[] = array(
												'slug' => 'modulesMetaBox',
												'title' => esc_html__('Course Sections', 'ulp'),
												'callback' => array($this, 'modulesMetaBox'),
												'context' => 'normal', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->metaBoxes[] = array(
												'slug' => 'linksMetaBox',
												'title' => esc_html__('Links', 'ulp'),
												'callback' => array($this, 'courselinksMetaBox'),
												'context' => 'courselinksMetaBox', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->metaBoxes[] = array(
												'slug' => 'studentsMetaBox',
												'title' => esc_html__('Enrolled Students', 'ulp'),
												'callback' => array($this, 'studentsMetaBox'),
												'context' => 'normal', // normal || side || advanced
												'priority' => 'default', /// high || low
		);
		$this->run(); /// from parent class
		add_action( 'edit_form_after_title', array($this, 'set_meta_above') );
		add_filter('wp_dropdown_users', array($this, 'filter_the_dropdown_author'));

		add_action('init', [$this, 'registerTags'], 999);
	}
	/*
	 * @param none
	 * @return array
	 */
	protected function allMetaNames(){
		return array(
						'modules' => array(
											'modules_i_num',
						),
 		);
	}
	/// META BOXES
	/**
	 * @param object
	 * @return string
	 */
	public function modulesMetaBox($post=null){
		/// gettings values
		$data['post_id'] = isset($post->ID) ? $post->ID : 0;
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/courses_modules.php');
		$view->setContentData($data);
		echo esc_ulp_content($view->getOutput());
	}

	public function courselinksMetaBox($post=null){
		/// gettings values
		$data['post_id'] = isset($post->ID) ? $post->ID : 0;
		$data['post_type'] = isset($post->post_type) ? $post->post_type : "";
		$data['special_settings_link'] = admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $data['post_type']  . '&id=' . $data['post_id']);
		if (get_option('ulp_course_reviews_enabled')){
			$data['reviews_link'] = admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_course_review&list_by_course_id=' . $data['post_id']);
		}
		$data['permalink'] = get_post_permalink($data['post_id']);
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/courses_abovelinks.php');
		$view->setContentData($data);
		echo esc_ulp_content($view->getOutput());
	}

	public function studentsMetaBox($post=null){
		/// gettings values
		$data['post_id'] = isset($post->ID) ? $post->ID : 0;
		$data['post_title'] = isset($post->post_title) ? $post->post_title : '';
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/courses_students.php');
		$view->setContentData($data);
		echo esc_ulp_content($view->getOutput());
	}

	/**
	 * @param int
	 * @return none
	 */
	public function afterSavePost($post_id=0){
		$postType = \DbUlp::getPostTypeById($post_id);
		if ($postType!=$this->post_type_slug){
				return;
		}
		
		if ( defined('DOING_AJAX') && DOING_AJAX ){
				return;
		}

		\DbUlp::saveCoursesModules( $post_id, ulp_sanitize_array($_POST) );
		/// initiate special settings if the posts has been created
		$post_got_special_settings = DbUlp::does_post_meta_exists($post_id, 'ulp_course_assessments');
		if ($post_got_special_settings===FALSE){
				$defaults = DbUlp::getPostMetaGroup($post_id, 'course_special_settings', TRUE);
				DbUlp::update_post_meta_group('course_special_settings', $post_id, $defaults );
		}
	}

	public function filter_the_dropdown_author($html=''){
			global $post;
			if (isset($post->post_type) && $post->post_type=='ulp_course'){
					$data ['users'] = array_merge( DbUlp::get_list_of_admins(), DbUlp::getAllInstructors(999, 0) );
					$data ['the_value'] = $post->post_author;
					$view = new ViewUlp();
					$view->setTemplate(ULP_PATH . 'views/admin/custom_select_author_for_course.php');
					$view->setContentData($data);
					return $view->getOutput();
			}
			return $html;
	}

	public function set_meta_above($post=null){
			do_meta_boxes( null, 'courselinksMetaBox', $post );
	}

	protected function firstTimeRegister()
	{
		$doRewrite = get_option('ulp_do_flush_rewrite');
		if ($doRewrite){
			delete_option('ulp_do_flush_rewrite');
			return true;
		}
		return false;
	}

}
