<div id="ulp_options" class="ihc-stuffbox ihc-stuffbox-magic-feat" >
    <h3 class="ihc-h3"><?php esc_html_e('Ultimate Learning Pro', 'ulp');?></h3>
    <div class="inside">
        <div class="iump-form-line">
            <label><?php esc_html_e('Target Course', 'ulp');?></label>
            <select name="ump_ulp_course">
              <option value="-1" selected><?php esc_html_e('None', 'ulp');?></option>
              <?php
              foreach ($data['courses'] as $course):?>
                <?php $selected = ($course['ID']==$data['current_value']) ? 'selected' : '';?>
              <option value="<?php echo esc_attr($course['ID']);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($course['post_title']);?></option>
              <?php
              endforeach;
            ?></select>
            <p><?php esc_html_e('Link this level to a Ultimate Learning Pro Course', 'ulp');?></p>
        </div>
        <div class="ihc-submit-form">
        		<input type="submit" value="Save Changes" name="ihc_save_level" class="button button-primary button-large">
        </div>
    </div>
</div>
