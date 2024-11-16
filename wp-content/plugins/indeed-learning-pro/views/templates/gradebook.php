<div class="ulp-gradebook-wrapp">
    <?php if ($data->grades):?>
      <table class="ulp-table-general ulp-list-orders">
          <thead>
              <tr>
                  <th><?php esc_html_e('Course', 'ulp');?></th>
                  <th><?php esc_html_e('Quiz Title', 'ulp');?></th>
                  <th><?php esc_html_e('Grade', 'ulp');?></th>
                  <th><?php esc_html_e('Passed', 'ulp');?></th>
              </tr>
          </thead>
          <?php foreach ($data->grades as $grade_object):?>
              <tr>
                  <td class="ulp-special-column"><a href="<?php echo Ulp_Permalinks::getForCourse($grade_object->course_id);?>" target="_blank"><?php echo esc_ulp_content($grade_object->course_title);?></a></td>
                  <td><?php echo esc_html($grade_object->quiz_title);?></td>
                  <td><?php echo esc_html($grade_object->grade);?></td>
                  <td>
                    <?php if ($grade_object->course_passed){
                     echo esc_ulp_content('<span class="ulp-quiz-status-passed">'.esc_html__('Passed', 'ulp').'</span>');
                  }else{
                     echo esc_ulp_content(' <span class="ulp-quiz-status-failed">'.esc_html__('Failed', 'ulp').'</span>');
                  }?>
                </td>
              </tr>
          <?php endforeach;?>
      </table>
    <?php else:?>
      <div class="ulp-additional-message"><?php esc_html_e('You have not received any Grades yet!', 'ulp');?></div>
    <?php endif;?>
</div>
