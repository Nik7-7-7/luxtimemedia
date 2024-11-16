<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpQuestionActions')){
	 return;
}
class UlpQuestionActions{
	/**
	 * @var int (user id)
	 */
	private $uid = 0;
	/**
	 * @param int
	 */
	private $quiz_id = 0;
	/**
	 * @var int (quid id)
	 */
	private $question_id = 0;
	/**
	 * @var int ( user-quiz relation id )
	 */
	private $relation_id = 0;
	/**
	 * @var object
	 */
	private $entities_db = null;
	/**
	 * @var object
	 */
	private $entity_metas_db = null;
	/**
	* @var array
	*/
	private $question_postmeta = array();
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$this->setEntitiesDb();
		$this->setEntityMetasDb();
	}


	/**
	 * @param none
	 * @return none
	 */
	private function setEntitiesDb(){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
		$this->entities_db = new DbUserEntitiesRelations();
	}
	/**
	 * @param none
	 * @return none
	 */
	private function setEntityMetasDb(){
		require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
		$this->entity_metas_db = new DbUserEntitiesRelationMetas();
	}
	/**
	 * @param int
	 * @return none
	 */
	public function setUID($input=0){
		$this->uid = $input;
	}
	/**
	 * @param int
	 * @return none
	 */
	public function setQuizID($input=0){
		$this->quiz_id = $input;
	}
	/**
	 * @param int
	 * @return none
	 */
	public function setQuestionID($input=0){
		$this->question_id = $input;
	}
	/**
	 * @param int
	 * @return none
	 */
	public function setRelationId(){
		$this->relation_id = $this->entities_db->getRelationId($this->uid, $this->quiz_id);
	}
	/**
	 * @param string
	 * @return none
	 */
	public function saveAnswer($input=''){
		$input = maybe_unserialize( $input );
		$data = $this->getQuestionAnswers();
		if ( $data === null || $data === '' ){
				$data = [];
		}
		$data[$this->question_id] = $input;
		$data = serialize($data);
		$this->entity_metas_db->saveMeta($this->relation_id, 'quiz_questions_answers', $data);
	}
	/**
	 * @param none
	 * @return array
	 */
	public function getQuestionAnswers(){
		$this->setRelationId();
		if ($this->relation_id){
			$data = $this->entity_metas_db->getMeta($this->relation_id, 'quiz_questions_answers');
			if ( $data !== null ){
					$data = stripslashes( $data );
			}
			$data = maybe_unserialize( $data );
			return $data;
		}
		return array();
	}
	/**
	 * @param int
	 * @return mixed
	 */
	public function getQuestionCorrectAnswer($question_id=0){
		$correct_answer = FALSE;
		if ($question_id){
			$this->setQuestionPostMetas($question_id);
			$meta = $this->question_postmeta[$question_id];
			switch ($meta['answer_type']){
				case 1:
					/// fill in
					$correct_answer = stripslashes($meta['answer_value']);
					break;
				case 7:
					$correct_answer = stripslashes($meta['answer_value_required']);
					break;
				case 2:
					/// single answer (radio)
					$correct_answer = $meta['answers_single_answer_correct_value'];
					break;
				case 3:
					/// multiple values (checkboxes)
					$correct_answer = $meta['answers_multiple_answers_correct_answers'];
					//if (is_array($correct_answer) && count($correct_answer)){
					//	$correct_answer = serialize($correct_answer);
					//}
					break;
				case 4:
					/// true or false
					$correct_answer = $meta['answer_value_for_bool'];
					if ($correct_answer){
						$correct_answer = 1;
					} else {
						$correct_answer = 0;
					}
					break;
				case 5:
					/// essay
					$keywords_string = stripslashes($meta['answer_value_for_essay']);
					$correct_answer = explode(',', $keywords_string);
					break;
				case 6:
					$correct_answer = $meta['answers_sorting_type'];
					break;
				case 8:
					/// image single choice
					$correct_answer = stripslashes($meta['image_answers_single_answer_correct_value']);
					break;
				case 9:
					$correct_answer = stripslashes($meta['image_answers_multiple_answers_correct_answers']);
					break;
				case 10:
					$correct_answer = [];
					$questions = isset($meta['matching_micro_questions']) ? $meta['matching_micro_questions'] : [];
					$answers = isset($meta['matching_micro_questions_answers']) ? $meta['matching_micro_questions_answers'] : [];
					if (!$questions || !$answers){
							return $correct_answer;
					}
					foreach ($questions as $key=>$question){
							$correct_answer[$question] = isset($answers[$key]) ? $answers[$key] : '';
					}
					return $correct_answer;
					break;
			}
		}
		return $correct_answer;
	}
	/**
	 * @param int
	 * @param string
	 * @return boolean
	 */
	public function isQuestionCorrect($question_id=0, $the_answer=''){
			if ( is_string( $the_answer ) ){
					$the_answer = stripslashes( $the_answer );
			}
			if (empty($this->question_postmeta)){
					$this->setQuestionPostMetas($question_id);
			}
			$correct_answer = $this->getQuestionCorrectAnswer($question_id);

			switch ($this->question_postmeta[$question_id]['answer_type']){
					case 1:
						/// free choice
						$correct_answer = strtolower($correct_answer);
						$correct_answer = trim($correct_answer);
						$answer_from_student = strtolower($the_answer);
						$answer_from_student = trim($answer_from_student);
					  $answer_from_student = stripslashes($answer_from_student); /// check this , added @version 1.7

						/// multiple answers from student
						if (strpos($answer_from_student, ',')!==FALSE){
								$student_answers = indeed_explode_with_trim(',', $answer_from_student);
						} elseif (strpos($answer_from_student, ';')!==FALSE){
								$student_answers = indeed_explode_with_trim(';', $answer_from_student);
						}

						/// multiple possible answers
						if (strpos($correct_answer, ',')!==FALSE){
								$possible_answers = indeed_explode_with_trim(',', $correct_answer);
						}

						if (!empty($possible_answers)){
								/// multiple possible answers
								if (!empty($student_answers)){
									foreach ($possible_answers as $posible_answer){
											if (in_array($posible_answer, $student_answers)){
													return TRUE;
											}
									}
								} else {
										foreach ($possible_answers as $posible_answer){
												if ($answer_from_student==$posible_answer){
														return TRUE;
												}
										}
								}
						} else {
								/// just one correct answer
								if (!empty($student_answers)){
										if (in_array($correct_answer, $student_answers)){
												return TRUE;
										}
								} else {
										if ($correct_answer==$answer_from_student){
												return TRUE;
										}
								}
						}
						return FALSE;
						break;
					case 7:
						/// fill in blank
						$correct_answer = strtolower($correct_answer);
						$correct_answer = trim($correct_answer);
						$correct_answer = ulpGetPossibleValues($correct_answer);
						if ( is_string( $the_answer ) ){
							$answer_from_student = strtolower($the_answer);
							$answer_from_student = trim($answer_from_student);
						} else {
								$answer_from_student = $the_answer;
						}
						$answer_from_student = maybe_unserialize($answer_from_student);

						if (empty($correct_answer)){
								return true;
						}
						foreach ($correct_answer as $key => $value){
							if (!isset($answer_from_student[$key])){
								return false;
							}
							if (strpos($value, '|')!==FALSE){
								$chunks = indeed_explode_with_trim('|', $value);
								if (!in_array($answer_from_student[$key], $chunks)){
										return false;
								}
							} else {
								if ($value!=$answer_from_student[$key]){
									return false;
								}
							}
						}
						return true;
						break;
					case 2: /// radio
					case 4: /// boolean
					case 8: /// image - single choice
						if ($correct_answer==$the_answer){
							return true;
						}
						break;
					case 9: /// images - multiple answers
					case 3: /// multiple answers possible (checkboxes)
						$the_answer = maybe_unserialize($the_answer);
						if ( is_array($the_answer) && count($the_answer)==1){
								$the_answer = $the_answer[0];
						}
						if (strpos($correct_answer, ',')!==FALSE){
								$correct_answer = explode(',', $correct_answer);
						}
						if (is_array($the_answer)){
								if (is_array($correct_answer)){
										/// both arrays
										if (count($correct_answer)!=count($the_answer)){
												return false;
										}
										foreach ($correct_answer as $substr){
												if (!in_array($substr, $the_answer)){
														return false;
												}
										}
								} else {
										/// user has completed multiple answers but only one is correct
										return FALSE;
								}
						} else {
								if (is_array($correct_answer)){
										/// user has checked only one answer but there are more
										return false;
								} else {
										/// one answer from user one correct answer possible
										if ($the_answer!=$correct_answer){
												return false;
										}
								}
						}
						return true;
						break;
					case 5: ///essay
						if ($correct_answer){
							foreach ($correct_answer as $substr){
								if (strpos($the_answer, $substr)===FALSE){
										return false;
								}
							}
							return true;
						}
						break;
					case 6:
						/// we verify the order
						$student_answers = maybe_unserialize( $the_answer );
						if ($correct_answer){
							foreach ($correct_answer as $key=>$value){
									$student_answers[$key] = ulpPrintStringIntoField( $student_answers[$key] );
									$value = ulpPrintStringIntoField( $value );
									if ( $student_answers[$key] != $value ){
											return false;
									}
							}
							return true;
						}
						break;
					case 10:
						if ( is_string( $the_answer ) ){
								$the_answer = stripslashes( $the_answer );
						}
						$student_answers = maybe_unserialize( $the_answer );
						if ( !is_array( $student_answers ) ){
								return false;
						}
						$result = array_diff_assoc($student_answers, $correct_answer);
						if (empty($result)){
								return true;
						}
						return false;
						break;
			}
			return false;
	}
	private function setQuestionPostMetas($question_id=0){
			$this->question_postmeta[$question_id] = DbUlp::getPostMetaGroup($question_id, 'answer_settings', TRUE);
	}
}
