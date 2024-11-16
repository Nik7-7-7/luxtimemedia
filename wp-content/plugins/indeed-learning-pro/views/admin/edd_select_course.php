<div id="ulp_options"  class="ulp-select-course-wrapper">
  <h3>Ultimate Learning Pro</h3>
  <p><?php esc_html_e('Link this product to a Ultimate Learning Pro Course', 'ulp');?></p>
  <p class="form-field">
  <label><?php esc_html_e('Target Course', 'ulp');?></label>
  <select name="ulp_edd_product_course_relation">
    <option value="-1" selected><?php esc_html_e('None', 'ulp');?></option>
    <?php
    foreach ($data['courses'] as $course):?>
      <?php $selected = ($course['ID']==$data['current_value']) ? 'selected' : '';?>
    <option value="<?php echo esc_attr($course['ID']);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($course['post_title']);?></option>
    <?php
    endforeach;
  ?></select>
  </p>
</div>
