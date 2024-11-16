<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('UlpStudent')){
	 return;
}
if (!class_exists('Ulp_User_Abstract')){
	 require_once ULP_PATH . 'classes/Abstracts/Ulp_User_Abstract.class.php';
}
class UlpStudent extends Ulp_User_Abstract{
	/**
	 * @var string
	 */
	  protected $uid = 0;
    public $student_courses = array();
    public $reward_points = 0;
    public function __construct($input=0){
        if ($input){
          $this->uid = $input;
        }
    }
    public function EarnedPoints(){
        require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';
        $UlpRewardPoints = new UlpRewardPoints($this->uid);
        $this->reward_points = $UlpRewardPoints->NumOfPoints();
        return $this->reward_points;
    }
    public function StudentHasCourse(){
      require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
			require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
      $DbUserEntitiesRelations = new DbUserEntitiesRelations();
      $data = $DbUserEntitiesRelations->get_user_courses($this->uid);
      if ($data){
        foreach ($data as $course_id){
						if ( !DbUlp::postDoesReallyExists($course_id) ){
							 continue;
						}
						$temp_object = new UlpCourse($course_id, FALSE);
						$courses_actions = new UsersCoursesActionsUlp();
            $this->student_courses[$course_id] = array(
                  'permalink' => \Ulp_Permalinks::getForCourse($course_id),//geT_permalink($course_id),
                  'title' => DbUlp::getPostTitleByPostId($course_id),
                  'start_time' => $DbUserEntitiesRelations->getRelationColValue($this->uid, $course_id, 'start_time'),
                  'end_time' => $DbUserEntitiesRelations->getRelationColValue($this->uid, $course_id, 'end_time'),
								  'number_of_students' => $temp_object->TotalStudents(),
								  'author_name' => $temp_object->AuthorName(),
								  'author_image' => $temp_object->AuthorImage(),
								  'feature_image' => $temp_object->FeatureImage(),
								  'is_featured' => $temp_object->IsFeatured(),
								  'total_modules' => $temp_object->TotalModules(),
								  'category' => $temp_object->Categories(TRUE),
								  'excerpt' => DbUlp::getPostExcerpt($course_id),
								  'create_date' => DbUlp::getPostCreateDate($course_id),
								  'progress' => $courses_actions->getProgress($this->uid, $course_id) . '%',
								  'status' => esc_html__('In progress', 'ulp'),
            );
						if ($this->student_courses[$course_id]['progress']==100){
								$this->student_courses[$course_id]['status'] = esc_html__('Completed', 'ulp');
						} else if (strtotime($this->student_courses[$course_id]['end_time'])<time()){
								$this->student_courses[$course_id]['status'] = esc_html__('Expired', 'ulp');
						}
        }
        return true;
      }
    }
}
