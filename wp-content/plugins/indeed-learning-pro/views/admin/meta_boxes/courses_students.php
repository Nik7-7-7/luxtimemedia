
<div class="ulp-course-enrolled-students-wrapper">
<h3>Current enrolled students on this course</h3>
 <div class="ulp-wrapper">

   <div id="ulp_list_course_students">

    </div>

     <div class="input-group">
  					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Student Username', 'ulp');?></span>
  					<input type="text" class="form-control" value="" name="ulp_add_new_student" id="ulp_new_student" />
  	</div>
    <div class="ulp-clear"></div>
    <input name="save" type="button" class="button button-primary button-large" id="publish" value="<?php esc_html_e('Add New Student', 'ulp');?>" onClick="ulpAddUserToCourse(<?php echo esc_attr($data ['post_id']);?>);" />
</div>
</div>
<span class="ulp-js-courses-students-meta-box" data-post_id="<?php echo esc_attr($data ['post_id']);?>" ></span>
