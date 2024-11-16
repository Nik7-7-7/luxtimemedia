<div class="ultp-edit-user-courses-list">
	<h2>Ultimate Learning Pro - <?php esc_html_e('Enrolled Courses', 'ulp');?></h2>
	<div class="ulp-edit-wp-user-status">
    <table class="ulp-student-courses-table">
			<?php if (!empty($data ['user_courses'])):?>
					<?php    $i = 1;
					   foreach ($data ['user_courses'] as $course_id => $course_label):?>
						<tr class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
                            <td class="ulp-student-courses-table-check-col"> <input type="checkbox" checked disabled class="ulp-student-courses-table-disabled-check"/></td>
							<td ><?php echo esc_html($course_label);?></td>
                            <td width="100px"><div  class="ulp-small-button"><span class="ulp-delete" onClick="ulpRemoveCourse(<?php echo esc_attr($data ['uid']);?>, <?php echo esc_attr($course_id);?>,'<?php echo esc_attr($course_label);?>');"><?php esc_html_e('Remove', 'ulp');?></span></div>
                            </td>
                        </tr>
					<?php  $i++;
					endforeach;?>
			<?php endif;?>
			<?php if ($data ['courses']):?>
					<?php   $i = 1;
					  foreach ($data ['courses'] as $course):?>
                      <tr class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
						<?php if (isset($data ['user_courses'][ $course['ID'] ])){
							 continue;
						}?>
						<td class="ulp-student-courses-table-check-col"><input type="checkbox" name="ulp_enroll_courses[]" value="<?php echo esc_attr($course ['ID']);?>" /> </td>
                        <td ><?php echo esc_html($course ['post_title']);?></td>
                        <td width="100px"></td>
                      </tr>
					<?php  $i++;
					endforeach;?>
			<?php endif;?>
    </table>
	</div>
</div>
