<div class="ulp-inside-item">
  <div class="row">
   <div class="col-xs-6">
      <h4><?php esc_html_e('Multiple Instructors', 'ulp');?></h4>
      <p><?php esc_html_e('Choose multiple intructors for current course', 'ulp');?></p>
        <?php if ($data['instructors']):?>
            <?php foreach ($data['instructors'] as $instructor_object):?>
                <div>
                    <input type="checkbox" <?php echo (in_array($instructor_object->uid, $data['value_as_array'])) ? 'checked' : '';?> onClick="ulpSecondMakeInputhString(this, <?php echo esc_attr($instructor_object->uid);?>, '#ulp_additional_instructors');" value="<?php echo esc_attr($instructor_object->uid);?>"  /> <?php echo esc_html($instructor_object->user_login);?>
                </div>
            <?php endforeach;?>
            <input type="hidden" name="ulp_additional_instructors" id="ulp_additional_instructors" value="<?php echo esc_attr($data['ulp_additional_instructors']);?>" />
        <?php endif;?>
     </div>
    </div>
</div>
