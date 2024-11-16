<div class="ulp-stuffbox">

  <h3 class="ulp-h3"><?php esc_html_e('Student badges', 'ulp');?></h3>

  <div class="inside">



<form  method="post" role = "form">
  <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-inside-item">

              <div class="row">

               <div class="col-xs-6">

				<div class="ulp-form-line">

					<h2><?php esc_html_e('Activate Student Badges', 'ulp');?></h2>

					<label class="ulp_label_shiwtch ulp-switch-button-margin">

						<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_student_badges_enable');" <?php echo ($data['metas']['ulp_student_badges_enable']) ? 'checked' : '';?> />

						<div class="switch ulp-display-inline"></div>

					</label>

                    <input type="hidden" name="ulp_student_badges_enable" id="ulp_student_badges_enable" value="<?php echo esc_attr($data['metas']['ulp_student_badges_enable']);?>" />



				</div>

      <div class="form-group row">

          <div class="col-4">
            <div class="ulp-submit-form">
              <input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
            </div>
          </div>

      </div>

     </div>

    </div>

</div>

</form>



  </div>

</div>



  <div class="row">

     <div class="col-xs-6">

		<div class="wrap">

   		 <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=student_badges&action=new');?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e('Add new Badge', 'ulp');?></a>

		</div>

	</div>

 </div>





<div class="ulp-wrapper">

<div class="ulp-input-group-space">



	<?php if ($data ['items']):?>

	  <table class="wp-list-table widefat fixed tags ulp-admin-tables striped ulp-student-badges-table ">

	      <thead class="thead-inverse">

	        <tr>

	          <th class="ulp-student-badges-table-col1"><?php esc_html_e('ID', 'ulp');?></th>

	          <th class="ulp-student-badges-table-col2"><?php esc_html_e('Badge Image', 'ulp');?></th>

	          <th class="ulp-student-badges-table-col3"><?php esc_html_e('Title', 'ulp');?></th>

	          <th class="ulp-student-badges-table-col4"><?php esc_html_e('Description', 'ulp');?></th>

	          <th class="ulp-student-badges-table-col5"><?php esc_html_e('Type', 'ulp');?></th>

	          <th class="ulp-student-badges-table-col6"><?php esc_html_e('Achievement', 'ulp');?></th>

	        </tr>

	      </thead>

	      <tbody>

	          <?php foreach ($data['items'] as $object):?>

	              <tr id="<?php echo esc_attr('table_tr_' . $object->id);?>" onMouseOver="ulpDhSelector('<?php echo esc_attr('#hidden' . $object->id);?>', 1);" onMouseOut="ulpDhSelector('<?php echo esc_attr('#hidden' . $object->id);?>', 0);">

	                <td scope="row">

	                    <?php echo esc_html($object->id);?>

	                </td>

	                <td class="ulp-text-aling-center"><img src="<?php echo esc_url($object->badge_image);?>" class="uap-admin-badge-image ulp-admin-badge-image" /></td>

	                <td >

	                    <div class="ulp-special-label-style"><?php echo esc_html($object->badge_title);?></div>

	                    <div id="<?php echo esc_attr('hidden' . esc_attr($object->id));?>" class="ulp-visibility-hidden">

	                        <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=student_badges&action=edit&id=' .esc_attr($object->id));?>"><?php esc_html_e('Edit', 'ulp');?></a>

	                        <span class="ulp-delete" onClick="ulpBadgesDoDelete(<?php echo esc_attr($object->id);?>);" ><?php esc_html_e('Delete', 'ulp');?></span>

	                    </div>



	                </td>

	                <td><?php echo esc_html($object->badge_content);?></td>

	                <td><div class="ulp-property"><?php echo ucfirst($object->badge_type);?></div></td>

	                <td class="ulp-student-badges-role">

					<?php

							if ($object->rule){

								$rules = json_decode($object->rule, true);
                if ( isset( $rules['rule_type'] ) ){
								switch($rules['rule_type']){

								case 'finish_course':

									echo esc_html__('Finish the Course with ','ulp').$rules['rule_value'];

									break;

								case 'finish_quiz':

									echo esc_html__('Finish the Quiz with ','ulp').$rules['rule_value'];

									break;

								case 'reward_points':

									echo esc_html__('Received ','ulp') . $rules['rule_value'] . esc_html__(' points','ulp');

									break;

								default:

									echo esc_ulp_content('<div><span><strong>'. esc_html__('Reason: ', 'ulp').'</strong></span>' . $rules['rule_type'].'</div>');

									echo esc_ulp_content('<div><span><strong>'. esc_html__('With: ', 'ulp').'</strong></span>' . $rules['rule_value'].'</div>');

								};
              }

							} else {

									echo esc_html('');

							}

									?></td>

	              </tr>

	            <?php endforeach;?>

	      </tbody>

	  </table>

	  <div class="ulp-clear"></div>



	<?php else :?>

	  <div><?php esc_html_e('No items yet!', 'ulp');?></div>

	<?php endif;?>





   </div>

 </div>
