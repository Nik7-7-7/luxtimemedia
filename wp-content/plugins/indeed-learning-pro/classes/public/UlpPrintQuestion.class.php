<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpPrintQuestion')){
	 return;
}
class UlpPrintQuestion{
	/**
	 * @var int
	 */
	private $question_id = 0;
	/**
	 * @var int
	 */
	private $quiz_id = 0;
	/**
	 * @var string
	 */
	private $question_string = '';
	/**
	 * @var bool
	 */
	private $next_button = FALSE;
	/**
	 * @var bool
	 */
	private $prev_button = FALSE;
	/**
	 * @var string
	 */
 private $the_legend = '';
 /**
  * @var string
	*/
private $quiz_workflow = '';
private $is_last_question = false;
private $courseId = 0;

public $question_content = null;

	/**
	 * @param int
	 * @return none
	 */
	public function __construct($courseId=0){
			if ($courseId){
					$this->courseId = $courseId;
			}
	}
	/**
	 * @param none
	 * @return none
	 */
	public function setQuestionId($input=0){
		$this->question_id = $input;
	}
	/**
	 * @param int
	 * @return none
	 */
	public function setQuizId($input=0){
		$this->quiz_id = $input;
	}
	/**
	 * @param bool
	 * @return none
	 */
	public function setPrev($input=FALSE){
		$this->prev_button = $input;
	}
	/**
	 * @param bool
	 * @return none
	 */
	public function setNext($input=FALSE){
		$this->next_button = $input;
	}
	/**
	 * @param none
	 * @return none
	 */
	public function setQuestionString(){
		$the_answers = $this->getAnswers();
		$this->question_string = '';

		$this->question_content = DbUlp::getPostContentByPostId($this->question_id);
		$metas = DbUlp::getPostMetaGroup($this->question_id, 'answer_settings', TRUE);
		switch ($metas['answer_type']){
			case 1:
				$form_meta['type'] = 'text';
				$form_meta['classes'] = 'ulp-q-answer-text';
				if (isset($the_answers[$this->question_id])){
					$form_meta['value'] = stripslashes($the_answers[$this->question_id]);
				}
				break;
			case 7:
				$form_meta['type'] = 'fill_in_type';
				$form_meta['fullString'] = $metas['answer_value_required'];
				$form_meta['possibleAnswers'] = ulpGetPossibleValues($metas['answer_value_required']);
				break;
			case 2:
				$form_meta['type'] = 'radio';
				$form_meta['item_wrapper_class'] = 'ulp-q-answer-radio';
				$form_meta['options'] = $metas['answers_single_answer_possible_values'];
				if (isset($the_answers[$this->question_id])){
					$form_meta['value'] = $the_answers[$this->question_id];
				}
				foreach ( $form_meta['options'] as $optionName => $optionValue ){ // $optionName => &$optionValue
						//$optionValue = stripslashes( $optionValue );
						$form_meta['options'][$optionName] = stripslashes( $optionValue );
				}
				$form_meta['options'] = ulp_array_value_become_key($form_meta['options']);
				/// random order
				if ($this->random_answers()){
					  $form_meta['options'] = ulp_shuffle_assoc($form_meta['options']);
				}
				break;
			case 3:
				$form_meta['type'] = 'checkbox';
				$form_meta['item_wrapper_class'] = 'ulp-q-answer-checkbox';
				$the_values = array();
				if (!empty($metas['answers_multiple_answers_possible_values']) && is_array($metas['answers_multiple_answers_possible_values'])){
					foreach ($metas['answers_multiple_answers_possible_values'] as $temp_v){
						$temp_v = stripslashes( $temp_v );
						$the_values[$temp_v] = $temp_v;
					}
				}
				/// random order
				if ($this->random_answers()){
					  $the_values = ulp_shuffle_assoc($the_values);
				}
				$form_meta['options'] = $the_values;
				if (isset($the_answers[$this->question_id])){
					$form_meta['value'] = $the_answers[$this->question_id];
				}
				break;
			case 4:
				$form_meta['type'] = 'radio';
				$form_meta['item_wrapper_class'] = 'ulp-q-answer-radio';
				$form_meta['options'] = array( 1 => esc_html__('True', 'ulp'), 0 => esc_html__('False', 'ulp') );
				$form_meta['value'] = '';
				if (isset($the_answers[$this->question_id])){
					$form_meta['value'] = stripslashes( $the_answers[$this->question_id] );
				}
				break;
			case 5:
				$form_meta['type'] = 'textarea';
				$form_meta['classes'] = 'ulp-q-answer-textarea';
				$form_meta['value'] = '';
				break;
			case 6:
				$form_meta = [
						'type' => 'sorting',
						'class' => 'ulp-q-answer-sorting',
				];
				if (!empty($metas['answers_sorting_type']) && is_array($metas['answers_sorting_type'])){
						$form_meta ['values'] = ulp_shuffle_assoc($metas['answers_sorting_type']);
				}
				break;
			case 8:
				/// choose image - single choice
				$theOptions = [];
				if (!empty($metas['image_answers_single_answer_possible_values']) && is_array($metas['image_answers_single_answer_possible_values'])){
					foreach ($metas['image_answers_single_answer_possible_values'] as $key=>$temp_v){
						$theOptions[$key] = $temp_v;
					}
				}
				/// random order
				if ($this->random_answers()){
						$theOptions = ulp_shuffle_assoc($theOptions);//$the_values
				}
				$correctAnswer = '';
				if (isset($the_answers[$this->question_id])){
					$correctAnswer = $the_answers[$this->question_id];
				}
				$form_meta = [
						'type' => 'images-single-choice',
						'class' => '',
						'value' => $correctAnswer,
						'options' => $theOptions,
				];
				break;
			case 9:
				/// choose image - multiple choice
				$theOptions = [];
				if (!empty($metas['image_answers_multiple_answers_possible_values']) && is_array($metas['image_answers_multiple_answers_possible_values'])){
					foreach ($metas['image_answers_multiple_answers_possible_values'] as $key=>$temp_v){
						$theOptions[$key] = $temp_v;
					}
				}
				/// random order
				if ($this->random_answers()){
						$theOptions = ulp_shuffle_assoc($theOptions);// $the_values
				}
				$correctAnswer = '';
				if (isset($the_answers[$this->question_id])){
					$correctAnswer = $the_answers[$this->question_id];
				}
				$form_meta = [
						'type' => 'images-multiple-choice',
						'class' => '',
						'value' => $correctAnswer,
						'options' => $theOptions,
				];
				break;
			case 10:
				$microQuestions = empty($metas['matching_micro_questions']) ? [] : $metas['matching_micro_questions'];
				$answers = empty($metas['matching_micro_questions_answers']) ? [] : ulp_shuffle_assoc($metas['matching_micro_questions_answers']);
				/// random order
				if ($this->random_answers()){
						$theOptions = ulp_shuffle_assoc($microQuestions);
				}
				$form_meta = [
						'type' => 'matching',
						'class' => '',
						'questions' => $microQuestions,
						'answers' => $answers,
				];
				break;
		}
		if (!empty($form_meta)){
			$form_meta['name'] = $this->question_id;
			require_once ULP_PATH . 'classes/IndeedForms.class.php';
			$IndeedForms = new IndeedForms();
			$IndeedForms->attr = $form_meta;
			$this->question_string .= $IndeedForms->getOutput();
		}
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getQuestionString(){
		return $this->question_string;
	}
	/**
	 * @param none
	 * @return stirng
	 */
	public function getOutput(){
		$this->setQuestionString();
		$args = array(
					'question' => $this->getQuestionString(),
					'question_content' => $this->question_content,
					'question_id' => $this->question_id,
					'quiz_id' => $this->quiz_id,
					'print_next' => $this->next_button,
					'print_prev' => $this->prev_button,
					'legend' => $this->getLegend(),
					'print_ajax_submit_bttn' => FALSE,
					'is_last_question' => $this->is_last_question,
					'course_id' => $this->courseId,
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/templates/single-question.php');
		/// HINT
		$hint = get_post_meta($this->quiz_id, 'ulp_quiz_show_hint', TRUE);
		if ($hint){
			$args['hint'] = get_post_meta($this->question_id, 'ulp_question_hint', TRUE);
			$args['hint'] = stripslashes($args['hint']);
		}
		/// EXPLANATION
		$explanation = get_post_meta($this->quiz_id, 'ulp_quiz_show_explanation', TRUE);
		if ($explanation){
			$args['explanation'] = get_post_meta($this->question_id, 'ulp_question_explanation', TRUE);
			$args['explanation'] = stripslashes($args['explanation']);
		}
		/// Points
		$q_points = get_post_meta($this->question_id, 'ulp_question_points', TRUE);
		if ($q_points){
			$args['points'] = $q_points;
		}
		/// Prev and Next Buttons
		if ($this->quiz_workflow!='default'){
				$args['print_prev'] = FALSE;
				$args['print_next'] = FALSE;
				$args['print_ajax_submit_bttn'] = TRUE;
		} else {
			$prev_button = get_post_meta($this->quiz_id, 'enable_back_button', TRUE);
			if (!$prev_button && $args['print_prev']){
				$args['print_prev'] = FALSE;
			}
		}
		$view->setContentData( $args );
		return $view->getOutput();
	}
	/**
	 * @param none
	 * @return array
	 */
	private function getAnswers(){
		$uid = ulp_get_current_user();
		require_once ULP_PATH . 'classes/public/UlpQuestionActions.class.php';
		$UlpQuestionActions = new UlpQuestionActions();
		$UlpQuestionActions->setUID($uid);
		$UlpQuestionActions->setQuizID($this->quiz_id);
		return $UlpQuestionActions->getQuestionAnswers();
	}
	/**
	 * @param none
	 * @return boolean
	 */
	private function random_answers(){
		 return get_post_meta($this->quiz_id, 'ulp_quiz_display_answers_random', true);
	}
	public function setLegend($current_index=0, $total_questions=0){
			$this->the_legend = esc_html__('Question ', 'ulp') . $current_index . esc_html__(' of ', 'ulp') . $total_questions;
	}
	public function getLegend(){
		return $this->the_legend;
	}
	public function setQuizWorkflow($input=''){
			$this->quiz_workflow = $input;
	}
	public function setIsLastQuestion($input=false){
			$this->is_last_question = $input;
	}
}
