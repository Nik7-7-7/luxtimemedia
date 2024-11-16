<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('QuestionsUlp')):
class QuestionsUlp extends CustomPostTypeUlp{
	/*
	 * @var string
	 */
	protected $post_type_slug = 'ulp_question';
	/*
	 * @var int
	 */
	protected $menu_position = 8;
	/**
	 * @var array
	 */
	protected $supports = array('editor', 'thumbnail', 'author');
	public function __construct(){
		$this->labels = array(
								    'name'               => esc_html__('Questions', 'ulp'),
								    'singular_name'      => esc_html__('Question', 'ulp'),
								    'add_new'            => esc_html__('Add new Question', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Question', 'ulp'),
								    'edit_item'          => esc_html__('Edit Question', 'ulp'),
								    'new_item'           => esc_html__('New Question', 'ulp'),
								    'all_items'          => esc_html__('All Questions', 'ulp'),
								    'view_item'          => esc_html__('View Question', 'ulp'),
								    'search_items'       => esc_html__('Search Question', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Questions', 'ulp'),
		);
		$this->taxonomy_labels = array(
									'name'              => esc_html__('Question Categories', 'ulp'),
									'singular_name'     => esc_html__('Question Category', 'ulp'),
									'search_items'      => esc_html__('Search Question Category', 'ulp'),
									'all_items'         => esc_html__('Question Categories', 'ulp'),
									'parent_item'       => '',
									'parent_item_colon' => '',
									'edit_item'         => esc_html__('Edit', 'ulp'),
									'update_item'       => esc_html__('Update', 'ulp'),
									'add_new_item'      => esc_html__('Add new question category', 'ulp'),
									'new_item_name'     => esc_html__('Add new question category', 'ulp'),
									'menu_name'         => esc_html__('Question Categories', 'ulp'),
		);
		$this->taxonomy_slug = 'ulp_question_categories';
		$this->metaBoxes[] = array(
												'slug' => 'answer_settings',
												'title' => esc_html__('Answer Settings', 'ulp'),
												'callback' => array($this, 'answerMetaBox'),
												'context' => 'normal', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->run(); /// from parent class
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

			$meta_keys = DbUlp::getPostMetaGroup($post_id, 'answer_settings', FALSE);
			foreach ($meta_keys as $type=>$value){
				if (isset($_POST[$type])){

					if($type == 'answers_multiple_answers_correct_answers'){
						//exclude additional spaces around comma separator
						$nameStr = ulp_sanitize_array($_POST[$type]);
							$names = explode(',', $nameStr);
							foreach ( $names as $name ){ //foreach($names as &$name) {
 								 $name_arr[] = trim($name);
							}
							$_POST[$type] = implode(',', $name_arr);
					}
					if ( is_array( $_POST[$type] ) ){
							$_POST[$type] = $this->addSlashesToArray( ulp_sanitize_array($_POST[$type]) );
					}
					update_post_meta($post_id, $type, ulp_sanitize_array($_POST[$type]) );
				}
			}
			/// initiate special settings if the posts has been created
			$post_got_special_settings = DbUlp::does_post_meta_exists($post_id, 'ulp_question_points');

			if ($post_got_special_settings===FALSE){
					$defaults = DbUlp::getPostMetaGroup($post_id, 'questions_special_settings', TRUE);
					DbUlp::update_post_meta_group('questions_special_settings', $post_id, $defaults );
			}
	}

	/**
	 * @param array
	 * @return array
	 */
	public function addSlashesToArray( $array=[] )
	{
			foreach ( $array as $key => $value ){
					$array[$key] = htmlentities( $value );
			}
			return $array;
	}

	/**
	 * @param object
	 * @return string
	 */
	public function answerMetaBox($post=null){
		$post_id = isset($post->ID) ? $post->ID : 0;
		$meta_data = DbUlp::getPostMetaGroup($post_id, 'answer_settings', TRUE);
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/questions_answer.php');
		$view->setContentData($meta_data);
		echo esc_ulp_content($view->getOutput());
	}
}
endif;
