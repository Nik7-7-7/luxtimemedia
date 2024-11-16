<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Course difficulty', 'ulp');?></h3>
		<div class="inside">

			<div class="ulp-form-line">
					<h2><?php esc_html_e('Activate Course Difficulty', 'ulp');?></h2>
				<label class="ulp_label_shiwtch ulp-switch-button-margin">
					<?php $checked = ($data['metas']['ulp_course_difficulty_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="ulp-switch" onClick="UltimateLearningPro.checkAndH(this, '#ulp_course_difficulty_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch ulp-display-inline"></div>
				</label>
				<input type="hidden" name="ulp_course_difficulty_enable" value="<?php echo esc_attr($data['metas']['ulp_course_difficulty_enable']);?>" id="ulp_course_difficulty_enable" />
			</div>

			<div class="ulp-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
			</div>

		</div>
	</div>

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Add new Difficulty', 'ulp');?></h3>
  			<div class="inside">

  				<div class="input-group ulp-input-group-max">
  					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Slug:', 'ulp');?></span>
  					<input type="text" class="form-control" value="" name="slug" />
  				</div>

  				<div class="input-group ulp-input-group-max ulp-input-group-space">
  					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label:', 'ulp');?></span>
  					<input type="text" class="form-control" value="" name="label" />
  				</div>

  				<div class="ulp-submit-form">
  					<input type="submit" value="<?php esc_html_e('Add new Dificulty', 'ulp');?>" name="submit" class="btn btn-primary pointer" />
  				</div>

  			</div>
	</div>

</form>

<?php if ($data ['course_difficulty_types']!==FALSE && count($data ['course_difficulty_types'])>0):?>
    <div class="ulp-wrapper">
      <table class="wp-list-table widefat fixed tags ulp-admin-tables striped ">
        <thead  class="thead-inverse">
          <tr>
            <th class="manage-column">Slug</th>
            <th class="manage-column">Label</th>
            <th class="manage-column ulp-text-aling-center ulp-table-delete-col">Delete</th>
          </tr>
        </thead>
        <tbody>
          <?php	foreach ($data ['course_difficulty_types'] as $slug=>$label):?>
              <tr id="<?php echo esc_attr('ulp_div_' . $slug);?>">
                <td><div class="ulp-special-label-style"><?php echo esc_html($slug);?></div></td>
                <td><?php echo esc_ulp_content($label);?></td>
                <td class="ulp-text-aling-center"><i class="fa-ulp fa-remove-ulp" onClick="ulpRemoveDifficultyType('<?php echo esc_attr($slug);?>');"></i></td>
              </tr>
          <?php endforeach;	?>
        </tbody>
      </table>
    </div>
<?php endif;?>
