<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbQuizQuestions')){
   return;
}
class DbQuizQuestions extends DbIndeedAbstract{
	/**
	 * @var string
	 */
	protected $table = '';
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		global $wpdb;
		$this->table = $wpdb->prefix . 'ulp_quizes_questions';
	}
	/**
	 * Return all questions for a quiz
	 * @param int (quiz id)
	 * @return array
	 */
	public function getQuizQuestions($quiz_id=0, $asc_or_desc='ASC'){
		global $wpdb;
		$array = array();
		$quiz_id = sanitize_text_field($quiz_id);
		$asc_or_desc = sanitize_text_field($asc_or_desc);
    $where = $wpdb->prepare( " quiz_id=%d AND status=1 ORDER BY item_order $asc_or_desc ", $quiz_id );
		$data = $this->getResults( 'question_id', $where ); /// extra cols  item_order, status
		if ($data){
			foreach ($data as $subarray){
				$array[] = $subarray['question_id'];
			}
		}
		return $array;
	}
	/**
	 * @param int (question id)
	 * @param int (quiz id)
	 * @return bool
	 */
	public function deleteQuestionFromQuiz($question_id=0, $quiz_id=0){
		global $wpdb;
		$question_id = sanitize_text_field($question_id);
		$quiz_id = sanitize_text_field($quiz_id);
    $query = $wpdb->prepare( "DELETE FROM {$this->table} WHERE question_id=%d AND quiz_id=%d ;", $question_id, $quiz_id );
		return $wpdb->query( $query );
	}
	/**
	 * @param int (question id)
	 * @param int (quiz id)
	 * @return int
	 */
	public function saveQuizQuestion($question_id=0, $quiz_id=0, $item_order=0, $status=0){
		global $wpdb;
		$quiz_id = sanitize_text_field($quiz_id);
		$question_id = sanitize_text_field($question_id);
		$item_order = sanitize_text_field($item_order);
		$status = sanitize_text_field($status);
    $where = $wpdb->prepare( " quiz_id=%d AND question_id=%d ", $quiz_id, $question_id );
		$id = $this->getVar( 'id', $where );
		if ($id){
      $update = $wpdb->prepare( " item_order=%d, status=%d ", $item_order, $status );
      $where = $wpdb->prepare( " id=%d ", $id );
			$this->update( $update, $where );
			return $id;
		} else {
      $insert = $wpdb->prepare( "null, %d, %d, %d, %d", $quiz_id, $question_id, $item_order, $status );
			return $this->insert( $insert );
		}
	}
}
