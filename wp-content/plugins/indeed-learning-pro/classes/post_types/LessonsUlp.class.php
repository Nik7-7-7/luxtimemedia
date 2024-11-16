<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('LessonsUlp')):
class LessonsUlp extends CustomPostTypeUlp{
	/*
	 * @var string
	 */
	protected $post_type_slug = 'ulp_lesson';
	/*
	 * @var int
	 */
	protected $menu_position = 6;
	public function __construct(){
		$this->labels = array(
								    'name'               => esc_html__('Lessons', 'ulp'),
								    'singular_name'      => esc_html__('Lesson', 'ulp'),
								    'add_new'            => esc_html__('Add new Lesson', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Lesson', 'ulp'),
								    'edit_item'          => esc_html__('Edit Lesson', 'ulp'),
								    'new_item'           => esc_html__('New Lesson', 'ulp'),
								    'all_items'          => esc_html__('All Lessons', 'ulp'),
								    'view_item'          => esc_html__('View Lesson', 'ulp'),
								    'search_items'       => esc_html__('Search Lesson', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Lessons', 'ulp'),
		);
		$this->taxonomy_labels = array(
						'name'              => esc_html__('Lesson Categories', 'ulp'),
						'singular_name'     => esc_html__('Lesson Category', 'ulp'),
						'search_items'      => esc_html__('Search Lesson Category', 'ulp'),
						'all_items'         => esc_html__('Lesson Categories', 'ulp'),
						'parent_item'       => '',
						'parent_item_colon' => '',
						'edit_item'         => esc_html__('Edit', 'ulp'),
						'update_item'       => esc_html__('Update', 'ulp'),
						'add_new_item'      => esc_html__('Add new lesson category', 'ulp'),
						'new_item_name'     => esc_html__('Add new lesson category', 'ulp'),
						'menu_name'         => esc_html__('Lesson Categories', 'ulp'),
		);
		$this->taxonomy_slug = 'ulp_lesson_categories';
		if (get_option('lesson_drip_content_enable')){
			$this->metaBoxes[] = array(
													'slug' => 'ulp_drip_content',
													'title' => esc_html__('Indeed Learning Pro - Drip Content', 'ulp'),
													'callback' => array($this, 'ulp_drip_content'),
													'context' => 'normal', // normal || side || advanced
													'priority' => 'high', /// high || low
			);
		}
		$this->metaBoxes[] = array(
												'slug' => 'linksMetaBox',
												'title' => esc_html__('Links', 'ulp'),
												'callback' => array($this, 'lessonlinksMetaBox'),
												'context' => 'lessonlinksMetaBox', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->run(); /// from parent class
		add_action( 'edit_form_after_title', array($this, 'set_meta_above') );
	}
	/*
	 * @param none
	 * @return array
	 */
	protected function allMetaNames(){
		return array();
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
			
			if (get_option('lesson_drip_content_enable')){
					DbUlp::update_post_meta_group('drip_content', $post_id, ulp_sanitize_array( $_POST) );
			}
			/// initiate special settings if the posts has been created
			$post_got_special_settings = DbUlp::does_post_meta_exists($post_id, 'ulp_lesson_duration');
			if ($post_got_special_settings===FALSE){
					$defaults = DbUlp::getPostMetaGroup($post_id, 'lesson_special_settings', TRUE);
					DbUlp::update_post_meta_group('lesson_special_settings', $post_id, $defaults );
			}
	}
	public function ulp_drip_content($post=null){
			/// gettings values
			$data['post_id'] = isset($post->ID) ? $post->ID : 0;
			$data['metas'] = DbUlp::getPostMetaGroup($post->ID, 'drip_content');
			/// output
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/lesson_drip_content.php');
			$view->setContentData($data);
			echo esc_ulp_content($view->getOutput());
	}
	public function lessonlinksMetaBox($post=null){
		/// gettings values
		$data['post_id'] = isset($post->ID) ? $post->ID : 0;
		$data['post_type'] = isset($post->post_type) ? $post->post_type : "";
		$data['special_settings_link'] = admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $data['post_type']  . '&id=' . $data['post_id']);
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/lessons_abovelinks.php');
		$view->setContentData($data);
		echo esc_ulp_content($view->getOutput());
	}
	public function set_meta_above($post=null){
			do_meta_boxes( null, 'lessonlinksMetaBox', $post );
	}
}
endif;
