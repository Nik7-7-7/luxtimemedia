<div class="ultp-edit-user-courses-list">

  	<h2>Ultimate Learning Pro - <?php esc_html_e('User Badges', 'ulp');?></h2>

	<div class="ulp-edit-wp-user-status">

    <table class="ulp-student-courses-table">

			<?php if (!empty($data ['user_badges'])):?>

					<?php    $i = 1;

					   foreach ($data ['user_badges'] as $badge_id => $badge_label):?>

						<tr class="<?php echo ($i%2==0) ? 'alternate' : '';?>">

              <td class="ulp-student-courses-table-check-col"> <input type="checkbox" disabled checked class="ulp-student-courses-table-disabled-check"/></td>

							<td ><img src="<?php echo esc_url($data ['badges_img'][$badge_id]);?>" class="ulp-student-courses-table-img" title="<?php echo esc_attr($badge_label);?>" /></td>

              <td width="100px">

                  <div class="ulp-small-button">

                      <span class="ulp-delete" onClick="ulpRemoveUserBadge(<?php echo esc_attr($data ['uid']);?>, <?php echo esc_attr($badge_id);?>,'<?php echo esc_attr($badge_label);?>');"><?php esc_html_e('Remove', 'ulp');?></span>

                  </div>

              </td>

            </tr>

					<?php  $i++;

					endforeach;?>

			<?php endif;?>

			<?php if (!empty($data ['badges'])):?>

					<?php   $i = 1;

					  foreach ($data ['badges'] as $badge_id => $badge_label):?>

            <tr class="<?php echo ($i%2==0) ? 'alternate' : '';?>">

						  <?php if (isset($data ['user_badges'][ $badge_id ])){
                 continue;
              }?>

						  <td class="ulp-student-courses-table-check-col"><input type="checkbox" name="ulp_badges_to_user[]" value="<?php echo esc_attr($badge_id);?>" /> </td>

              <td ><img src="<?php echo esc_url($data ['badges_img'][$badge_id]);?>" class="ulp-student-courses-table-img" title="<?php echo esc_attr($badge_label);?>" /></td>

              <td width="100px"></td>

            </tr>

					<?php  $i++;

					endforeach;?>

			<?php endif;?>

    </table>

	</div>

</div>
