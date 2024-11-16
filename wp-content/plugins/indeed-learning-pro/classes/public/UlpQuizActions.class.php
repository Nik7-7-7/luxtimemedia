<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpQuizActions')){
	 return;
}
class UlpQuizActions{
	/**
	 * @var int (user id)
	 */
	private $uid = 0;
	/**
	 * @var int (quid id)
	 */
	private $quiz_id = 0;
	/**
	 * @var
	 */
	private $quiz_title = '';
	/**
	 * @param array
	 */
	private $quiz_metas = array();
	/**
	 * @var object
	 */
	private $entities_db = null;
	/**
	 * @var object
	 */
	private $entity_metas_db = null;
	/**
	 * @param array
	 */
	private $questions = array();
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
	public function setQID($input=0){
		$this->quiz_id = $input;
	}
	/**
	 * @param int
	 * @return none
	 */
	public function setQTitle($input=0){
		$this->quiz_title = $input;
	}
	/**
	 * @param none
	 * @return boolean
	 */
	public function canUserRunQuiz(){
		$relation_id = $this->entities_db->getRelationId($this->uid, $this->quiz_id);
		$no_of_attempts = $this->entity_metas_db->getMeta($relation_id, 'quiz_num_of_attempts');
 		return ($no_of_attempts<$this->quiz_metas['retake_limit']) ? TRUE : FALSE;
	}
	/**
	 * @param none
	 * @return none
	 */
	public function setMetas(){
		if ($this->quiz_id){
			$this->quiz_metas = DbUlp::getPostMetaGroup($this->quiz_id, 'quiz_special_settings', TRUE);
		}
	}
	/**
	 * @param array
	 * @return none
	 */
	public function setQuestions($input=array()){
		$this->questions = $input;
	}
	/**
	 * @param none
	 * @return int (timestamp) || boolean
	 */
	public function startQuiz(){
		$now = date('Y-m-d H:i:s');
		if ($this->entities_db->getRelationId($this->uid, $this->quiz_id)){
			/// update
			$start_time = $this->entities_db->getRelationColValue($this->uid, $this->quiz_id, 'start_time');
			$end_time = $this->entities_db->getRelationColValue($this->uid, $this->quiz_id, 'end_time');
			$end_time = strtotime($end_time);
			if ($end_time<0){
				/// quiz was not submited yet
				return $start_time;
			} else {
				/// submited
				if ($this->canUserRunQuiz()){
					/// quiz can be retaken
					$this->entities_db->saveRelation($this->uid, $this->quiz_id, 'quiz', $now, '0000-00-00 00:00:00', 1);
					return $now;
				} else {
					/// quiz cannot be run again
					return FALSE;
				}
			}
		} else {
			/// first time to take the quiz (do insert)
			$this->entities_db->saveRelation($this->uid, $this->quiz_id, 'quiz', $now, '', 1);
			return $now;
		}
	}
	/**
	 * @param none
	 * @return none
	 */
	public function endQuiz(){
		$relation_id = $this->entities_db->saveRelation($this->uid, $this->quiz_id, 'quiz', '', date('Y-m-d H:i:s'), 1);
		$no_of_attempts = $this->getNumberOfAttempts($relation_id);
		$no_of_attempts++;
		$this->entity_metas_db->saveMeta($relation_id, 'quiz_num_of_attempts', $no_of_attempts);
		do_action('ulp_finish_quiz', $this->uid, $this->quiz_id, $relation_id);
	}
	/**
	 * @param int
	 * @return int
	 */
	public function getNumberOfAttempts($relation_id=0){
		if($relation_id == 0){
			 $relation_id = $this->entities_db->getRelationId($this->uid, $this->quiz_id);
		}
		$no_of_attempts = $this->entity_metas_db->getMeta($relation_id, 'quiz_num_of_attempts');
		if ($no_of_attempts!=FALSE){
			return $no_of_attempts;
		}
		return 0;
	}
	/**
	 * @param none
	 * @return string
	 */
	public function getGrade(){
		$relation_id = $this->entities_db->getRelationId($this->uid, $this->quiz_id);
		if ($relation_id){
			return $this->entity_metas_db->getMeta($relation_id, 'grade');
		}
		return FALSE;
	}
	/**
	 * @param none
	 * @return int
	 */
	public function getQuizPassedOrNot(){
		$relation_id = $this->entities_db->getRelationId($this->uid, $this->quiz_id);
		if ($relation_id){
			return $this->entity_metas_db->getMeta($relation_id, 'quiz_passed');
		}
		///this was not
		return 0;
	}
	/**
	 * @param string
	 * @return none
	 */
	public function writeGrade($input=0){
		$relation_id = $this->entities_db->getRelationId($this->uid, $this->quiz_id);
		$this->entity_metas_db->saveMeta($relation_id, 'grade', $input);
		$passing_grade = get_post_meta($this->quiz_id, 'ulp_quiz_grade_value', TRUE);
		$passed = ($input>=$passing_grade) ? 1 : 0;
		$this->entity_metas_db->saveMeta($relation_id, 'quiz_passed', $passed);
		///notifications
 
		if ($passed){
				do_action('ulp_user_pass_quiz', $this->uid, $this->quiz_id, $input);
		} else {
				do_action('ulp_user_fail_quiz', $this->uid, $this->quiz_id, $input);
		}
		do_action('ulp_user_completes_quiz', $this->uid, $this->quiz_id, $input);
		/// pay reward points
		if (!empty($this->quiz_metas['ulp_post_reward_points'])){
			require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';
			$UlpRewardPoints = new UlpRewardPoints($this->uid);
			$UlpRewardPoints->add_points_to_user($this->quiz_metas['ulp_post_reward_points'], $this->quiz_id, 'quiz_passed');
		}
	}
	/**
	 * @param none
	 * @return float
	 */
	public function calculateGrade(){
		require_once ULP_PATH . 'classes/public/UlpQuestionActions.class.php';
		$UlpQuestionActions = new UlpQuestionActions();
		$UlpQuestionActions->setUID($this->uid);
		$UlpQuestionActions->setQuizID($this->quiz_id);
		$answers = $UlpQuestionActions->getQuestionAnswers();
		$correct = 0;
		$total = 0;
		$settings = DbUlp::getPostMetaGroup($this->quiz_id, 'quiz_special_settings');
		/// CHECK EACH QUESTION ANSWER
		foreach ($this->questions as $question_id){
			if (isset($answers[$question_id])){
			
				$is_correct = $UlpQuestionActions->isQuestionCorrect($question_id, $answers[$question_id]);
				if ($is_correct){
						if ($this->quiz_metas['ulp_quiz_grade_type']=='percentage'){
							$correct++;
						} else {
							$correct += (float)get_post_meta($question_id, 'ulp_question_points', TRUE);
						}
				}
			}
			$total++;
		} ///endforeach
		if ($this->quiz_metas['ulp_quiz_grade_type']=='percentage'){
			$result = $correct * 100 / $total;
			return round($result, 1);///percentage
		} else {
			return $correct;///point
		}
	}
}
