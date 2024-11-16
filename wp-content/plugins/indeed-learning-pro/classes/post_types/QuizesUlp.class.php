<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('QuizesUlp')):
class QuizesUlp extends CustomPostTypeUlp{
	/**
	 * @var string
	 */
	protected $post_type_slug = 'ulp_quiz';
	/**
	 * @var int
	 */
	protected $menu_position = 7;
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$this->labels = array(
								    'name'               => esc_html__('Quizes', 'ulp'),
								    'singular_name'      => esc_html__('Quizes', 'ulp'),
								    'add_new'            => esc_html__('Add new Quiz', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Quiz', 'ulp'),
								    'edit_item'          => esc_html__('Edit Quiz', 'ulp'),
								    'new_item'           => esc_html__('New Quiz', 'ulp'),
								    'all_items'          => esc_html__('All Quizes', 'ulp'),
								    'view_item'          => esc_html__('View Quiz', 'ulp'),
								    'search_items'       => esc_html__('Search Quiz', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Quizes', 'ulp'),
		);
		$this->taxonomy_labels = array(
									'name'              => esc_html__('Quiz Categories', 'ulp'),
									'singular_name'     => esc_html__('Quiz Category', 'ulp'),
									'search_items'      => esc_html__('Search Quiz Category', 'ulp'),
									'all_items'         => esc_html__('Quiz Categories', 'ulp'),
									'parent_item'       => '',
									'parent_item_colon' => '',
									'edit_item'         => esc_html__('Edit', 'ulp'),
									'update_item'       => esc_html__('Update', 'ulp'),
									'add_new_item'      => esc_html__('Add new quiz category', 'ulp'),
									'new_item_name'     => esc_html__('Add new quiz category', 'ulp'),
									'menu_name'         => esc_html__('Quiz Categories', 'ulp'),
		);
		$this->taxonomy_slug = 'ulp_quiz_categories';
		$this->metaBoxes[] = array(
												'slug' => 'questions',
												'title' => esc_html__('Quiz Questions', 'ulp'),
												'callback' => array($this, 'questionMetaBox'),
												'context' => 'normal', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->metaBoxes[] = array(
												'slug' => 'linksMetaBox',
												'title' => esc_html__('Links', 'ulp'),
												'callback' => array($this, 'quizlinksMetaBox'),
												'context' => 'quizlinksMetaBox', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->run(); /// from parent class
		add_action( 'edit_form_after_title', array($this, 'set_meta_above') );
	}
	/**
	 * @param none
	 * @return array
	 */
	protected function allMetaNames(){
		return array(
						'questions' => array(),
		);
	}
	/**
	 * @param int
	 * @return none
	 */
	public function afterSavePost($quiz_id=0){
		$postType = \DbUlp::getPostTypeById($quiz_id);
		if ($postType!=$this->post_type_slug){
				return;
		}

		if ( defined('DOING_AJAX') && DOING_AJAX ){
				return;
		}

			\DbUlp::saveQuizQuestions($quiz_id, ulp_sanitize_array($_POST));
		/*
		require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
		$DbQuizQuestions = new DbQuizQuestions();
		$old_questions = $DbQuizQuestions->getQuizQuestions($quiz_id);
		if (!empty($old_questions)){
				foreach ($old_questions as $key=>$question_id){
						if (!in_array($question_id, ulp_sanitize_array($_POST['questions_list']))){
								$DbQuizQuestions->deleteQuestionFromQuiz($question_id, $quiz_id);
						}
				}
		}
		if (isset($_POST['questions_list'])){
			$item_order = 0;
			$status = 1;
			foreach ($_POST['questions_list'] as $question_id){
				$item_order++;
				$DbQuizQuestions->saveQuizQuestion($question_id, $quiz_id, $item_order, $status);
			}
		}
		/// initiate special settings if the posts has been created
		$post_got_special_settings = DbUlp::does_post_meta_exists($quiz_id, 'retake_limit');
		if ($post_got_special_settings===FALSE){
				$defaults = DbUlp::getPostMetaGroup($quiz_id, 'quiz_special_settings', TRUE);
				DbUlp::update_post_meta_group('quiz_special_settings', $quiz_id, $defaults );
		}
		*/
	}
	/**
	 * @param object
	 * @return string
	 */
	public function questionMetaBox($post=null){
		/// gettings values
		require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
		$post_id = isset($post->ID) ? $post->ID : 0;
		$DbQuizQuestions = new DbQuizQuestions();
		$data['quiz_questions'] = $DbQuizQuestions->getQuizQuestions($post_id);
		$data['all_questions'] = DbUlp::getAllQuestions(true);
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/quiz_questions_select.php');
		$view->setContentData($data);
		echo esc_ulp_content($view->getOutput());
	}
	public function quizlinksMetaBox($post=null){
		/// gettings values
		$data['post_id'] = isset($post->ID) ? $post->ID : 0;
		$data['post_type'] = isset($post->post_type) ? $post->post_type : "";
		$data['special_settings_link'] = admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $data['post_type']  . '&id=' . $data['post_id']);
		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/quiz_abovelinks.php');
		$view->setContentData($data);
		echo esc_ulp_content($view->getOutput());
	}
	public function set_meta_above($post=null){
			do_meta_boxes( null, 'quizlinksMetaBox', $post );
	}
}
endif;
