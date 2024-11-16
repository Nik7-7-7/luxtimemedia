<div class="ultp-edit-user-courses-list">
  	<h2>Ultimate Learning Pro - <?php esc_html_e('User Cerificates', 'ulp');?></h2>
	<div class="ulp-edit-wp-user-status">
    <table class="ulp-student-courses-table">
			<?php if (!empty($data ['user_certificates'])):?>
					<?php    $i = 1;
					   foreach ($data ['user_certificates'] as $certificate_id => $certificate):?>
						<tr class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
              <td class="ulp-student-courses-table-check-col"></td>
			  <td ><?php echo esc_html($certificate['certificate_title']);?> <span  class="ulp-student-courses-table-special-label">( <?php esc_html_e('from', 'ulp');?> <?php echo esc_html($certificate['course_name']);?> )</span></td>
              <td  class="ulp-student-courses-table-special-label"><?php echo esc_html($certificate['grade']);?></td>
              <td  class="ulp-student-courses-table-special-label"><?php echo esc_html($certificate['obtained_date']);?></td>
              <td width="100px">
                  <div class="ulp-small-button">
                      <span class="ulp-delete" onClick="ulpRemoveUserCertificate(<?php echo esc_attr($data ['uid']);?>, <?php echo esc_attr($certificate_id);?>,'<?php echo esc_attr($certificate['certificate_title']);?>');"><?php esc_html_e('Remove', 'ulp');?></span>
                  </div>
              </td>
            </tr>
					<?php  $i++;
					endforeach;?>
			<?php endif;?>
    </table>
	</div>
</div>
