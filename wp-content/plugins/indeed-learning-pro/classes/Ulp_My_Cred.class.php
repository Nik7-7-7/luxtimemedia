<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_My_Cred')){
   return;
}
if (!class_exists('myCRED_Hook')){
   return;
}
class Ulp_My_Cred extends myCRED_Hook{
  	/*
  	 * @param array
  	 * @return none
  	 */
  	public function __construct($hook_prefs=array(), $type='mycred_default'){
  		$courses = DbUlp::getAllCourses();
  		if ($courses){
  			foreach ($courses as $array){
  				$defaults['finish_course_' . $array['ID']] = array(
  							'creds' => 0,
  							'log'   => '%plural% for finish course ' . $array['post_title'],
  				);
          $defaults['enroll_course_' . $array['ID']] = array(
  							'creds' => 0,
  							'log'   => '%plural% for finish course ' . $array['post_title'],
  				);
  			}
  			if (!empty($defaults)){
  				parent::__construct(array(
  					'id' => 'ulp_mycred',
  					'defaults' => $defaults,
  				), $hook_prefs, $type);
  			}
  		}
  	}
  	/*
  	 * @param none
  	 * @return none
  	 */
  	public function run(){
  		  add_action('ulp_user_complete_course', array($this, 'give_points_for_complete_course'), 1, 2);
        add_action('ulp_user_do_enroll', array($this, 'give_points_for_enroll_course'), 1, 2);
  	}
  	/*
  	 * @param int, int
  	 * @return none
  	 */
  	public function give_points_for_complete_course($course_id=0, $uid=0){
  		if ($uid && $course_id){
  			if ($this->core->exclude_user($uid)){
  				  return;
  			}
  			$key = 'finish_course_' . $course_id;
        $this->_ulp_give_points($uid, $course_id, $key);
  		}
  	}
    public function give_points_for_enroll_course($uid=0, $course_id=0){
      if ($uid && $course_id){
  			if ($this->core->exclude_user($uid)){
  				  return;
  			}
  			$key = 'enroll_course_' . $course_id;
        $this->_ulp_give_points($uid, $course_id, $key);
  		}
    }
    private function _ulp_give_points($uid, $course_id, $key){
        if ($this->prefs[$key]['creds']>0){
            $this->core->add_creds(
                $key,
                $uid,
                $this->prefs[$key]['creds'],
                $this->prefs[$key]['log'],
                $course_id,
                array('ref_type'=>'post'),
                $this->mycred_type
            );
        }
    }
  	/*
  	 * @param array
  	 * @return array
  	 */
  	public function add_references($references=array()){
  		$courses = DbUlp::getAllCourses();
  		if ($courses){
  			foreach ($courses as $temp_array){
  				$references['enroll_course_' . $temp_array['ID']] =esc_html__('Enroll Course ', 'ulp');
  				$references['finish_course_' . $temp_array['ID']] =esc_html__('Completed Course ', 'ulp');
  			}
  		}
  		return $references;
  	}
  	/*
  	 * Print settings form
  	 * @param none
  	 * @return none
  	 */
  	public function preferences(){
  		$courses = DbUlp::getAllCourses();
  		if ($courses):
  			foreach ($courses as $temp_array):
  				$key = 'finish_course_' . $temp_array['ID'];
          $enroll_key = 'enroll_course_' . $temp_array['ID'];
  				?>
          <div class="ulp-mycred-box-wrapper">
  					<h2><?php echo esc_html("Enroll Course '") .  $temp_array['post_title'] . "' ";?></h2>
  					<label class="subheader" for="<?php echo esc_ulp_content($this->field_id(array($enroll_key=>'creds')));?>"><?php echo esc_html__('Points', 'ulp');?></label>
  					<ol>
  						<li>
  							<div class="h2">
  								<input type="number" min="0" name="<?php echo esc_ulp_content($this->field_name(array($enroll_key=>'creds')));?>" id="<?php echo esc_ulp_content($this->field_id(array($enroll_key=>'creds'))); ?>" value="<?php echo esc_attr($this->core->number($this->prefs[$enroll_key]['creds']));?>" />
  							</div>
  						</li>
  					</ol>
  					<label class="subheader" for="<?php echo esc_ulp_content($this->field_id(array($enroll_key=>'log')));?>"><?php echo esc_html__('Log Template', 'ulp');?></label>
  					<ol>
  						<li>
  							<div class="h2">
  								<input type="text" name="<?php echo esc_ulp_content($this->field_name(array($enroll_key=>'log')));?>" id="<?php echo esc_ulp_content($this->field_id(array($enroll_key=>'log'))); ?>" value="<?php echo esc_attr($this->prefs[$enroll_key]['log']);?>" class="long" placeholder="%plural% for <?php echo esc_ulp_content($temp_array['post_title']); ?> purchased" />
  							</div>
  						</li>
  					</ol>
  				</div>
  				<div class="ulp-mycred-box-wrapper">
  					<h2><?php echo esc_html( "Finish Course '") .  $temp_array['post_title'] . "' ";?></h2>
  					<label class="subheader" for="<?php echo esc_ulp_content($this->field_id(array($key=>'creds')));?>"><?php echo esc_html__('Points', 'ulp');?></label>
  					<ol>
  						<li>
  							<div class="h2">
  								<input type="number" min="0" name="<?php echo esc_ulp_content($this->field_name(array($key=>'creds')));?>" id="<?php echo esc_ulp_content($this->field_id(array($key=>'creds'))); ?>" value="<?php echo esc_attr($this->core->number($this->prefs[$key]['creds']));?>" />
  							</div>
  						</li>
  					</ol>
  					<label class="subheader" for="<?php echo esc_ulp_content($this->field_id(array($key=>'log')));?>"><?php echo esc_html__('Log Template', 'ulp');?></label>
  					<ol>
  						<li>
  							<div class="h2">
  								<input type="text" name="<?php echo esc_ulp_content($this->field_name(array($key=>'log')));?>" id="<?php echo esc_ulp_content($this->field_id(array($key=>'log'))); ?>" value="<?php echo esc_attr($this->prefs[$key]['log']);?>" class="long" placeholder="%plural% for <?php echo esc_ulp_content($temp_array['post_title']); ?> purchased" />
  							</div>
  						</li>
  					</ol>
  				</div>
  				<?php
  			endforeach;
  		endif;
  	}
}
