<?php if (!empty($data ['students'])):?>
      <table class="wp-list-table widefat fixed tags ulp-admin-tables striped ">
        <thead  class="thead-inverse">
          <tr>
            <th class="manage-column ulp-course-student-table-col1"><?php esc_html_e('Student', 'ulp');?></th>
            <th class="manage-column ulp-course-student-table-col2"><?php esc_html_e('Enroll Time', 'ulp');?></th>
            <th class="manage-column ulp-course-student-table-col3"><?php esc_html_e('Progress', 'ulp');?></th>
            <th class="manage-column ulp-text-aling-center ulp-table-delete-col"><?php esc_html_e('Remove', 'ulp');?></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($data ['students'] as $student_object):?>
          <?php $progress = $data['users_course_object']->getProgress($student_object->user_id, $data['post_id']);?>
             <tr>
              <td class="ulp-strong"><?php echo esc_html($student_object->full_name);?> (<?php echo esc_html($student_object->user_email);?>)</td>
                <td><?php if ( isset( $student_object->enroll_time ) ){
                   echo esc_html($student_object->enroll_time);
                }?></td>
                <td><div class="ulp-progress-line"><div class="ulp-total-progress" style= " width: <?php echo esc_attr($progress);?>%"></div></div></td>
                <td class="ulp-text-aling-center">
                  <i class="fa-ulp fa-remove-ulp" onClick="ulp_remove_course_reload_table(<?php echo esc_attr($student_object->user_id);?>, <?php echo esc_attr($data ['post_id']);?>, '<?php echo (isset($data ['post_title'])) ? esc_attr($data ['post_title']) : '';?>');"></i></td>
             </tr>
        <?php endforeach;?>
        </tbody>
     </table>
<?php endif;?>
