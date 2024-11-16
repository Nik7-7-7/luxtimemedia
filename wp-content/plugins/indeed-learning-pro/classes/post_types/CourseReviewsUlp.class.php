<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('CourseReviewsUlp')){
	 return;
}
class CourseReviewsUlp extends CustomPostTypeUlp{
	/*
	 * @var string
	 */
	protected $post_type_slug = 'ulp_course_review';
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
								    'name'               => esc_html__('Course Reviews', 'ulp'),
								    'singular_name'      => esc_html__('Course Review', 'ulp'),
								    'add_new'            => esc_html__('Add new Course Review', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Course Review', 'ulp'),
								    'edit_item'          => esc_html__('Edit Course Review', 'ulp'),
								    'new_item'           => esc_html__('New Course Review', 'ulp'),
								    'all_items'          => esc_html__('All Course Reviews', 'ulp'),
								    'view_item'          => esc_html__('View Course Review', 'ulp'),
								    'search_items'       => esc_html__('Search Course Review', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Course Reviews', 'ulp'),
		);
		/// taxonomy
		$this->taxonomy_labels = array(
									'name'              => esc_html__('Course Review Categories', 'ulp'),
									'singular_name'     => esc_html__('Course Review Category', 'ulp'),
									'search_items'      => esc_html__('Search Course Review Category', 'ulp'),
									'all_items'         => esc_html__('Course Reviews Categories', 'ulp'),
									'parent_item'       => '',
									'parent_item_colon' => '',
									'edit_item'         => esc_html__('Edit', 'ulp'),
									'update_item'       => esc_html__('Update', 'ulp'),
									'add_new_item'      => esc_html__('Add new Course Review category', 'ulp'),
									'new_item_name'     => esc_html__('Add new Course Review category', 'ulp'),
									'menu_name'         => esc_html__('Course Review Categories', 'ulp'),
		);
		$this->taxonomy_slug = 'ulp_course_review_categories';
		///meta boxes
		$this->metaBoxes[] = array(
												'slug' => 'ratingMetaBox',
												'title' => esc_html__('Course Rating', 'ulp'),
												'callback' => array($this, 'ratingMetaBox'),
												'context' => 'normal', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->metaBoxes[] = array(
  												'slug' => 'courseMetaBox',
  												'title' => esc_html__('Course', 'ulp'),
  												'callback' => [$this, 'selectCourseMetaBox'],
  												'context' => 'normal', // normal || side || advanced
  												'priority' => 'high', /// high || low
  		);
		$this->run(); /// from parent class
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
	public function ratingMetaBox($post=null){
		/// gettings values
		$data['post_id'] = isset($post->ID) ? $post->ID : 0;
		$data['rating'] = get_post_meta($data['post_id'], '_ulp_rating', true);
		$data['rating'] = (int)$data['rating'];
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/course_review_rating.php');
		$view->setContentData($data);
		echo esc_ulp_content($view->getOutput());
	}

	public function selectCourseMetaBox($post=null)
    {
      $data = [
          'post_id' => isset($post->ID) ? $post->ID : 0,
          'courses' => \DbUlp::getAllCourses(),
      ];
      $data['ulp_course_id'] = get_post_meta($data['post_id'], '_ulp_course_id', true);
			if (empty($data['ulp_course_id'])){
					$data['ulp_course_id'] = isset($_GET['course_id']) ? $_GET['course_id'] : 0;
			}
      $view = new \ViewUlp();
      $view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/review_select_course.php');
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

			if (!isset($_POST['submit']) && $this->post_type_slug!='ulp_course_review'){
					return;
			}
			if (!empty($_POST['ulp_course_id'])){
					//$_POST['ulp_course_id'] = sanitize_text_field( $_POST['ulp_course_id'] );
					update_post_meta($post_id, '_ulp_course_id', ulp_sanitize_array($_POST['ulp_course_id']) );
			}
			if (!empty($_POST['rating'])){
					update_post_meta($post_id, '_ulp_rating', ulp_sanitize_array($_POST['rating']) );
					$_POST['rating'] = sanitize_text_field( $_POST['rating'] );
			}
			if (!empty($_POST['post_author'])){
					update_post_meta($post_id, '_uid', sanitize_textarea_field($_POST['post_author']) );
					//$_POST['post_author'] = sanitize_text_field( $_POST['post_author'] );
					$student_fullname = DbUlp::getUserFulltName(sanitize_textarea_field($_POST['post_author']));
					update_post_meta($post_id, '_ulp_student_full_name', $student_fullname);
			}
	}

}
