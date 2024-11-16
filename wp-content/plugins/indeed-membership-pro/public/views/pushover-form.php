<form method="post" >
  <input type="hidden" name="ihc_pushover_nonce" value="<?php echo wp_create_nonce( 'ihc_pushover_nonce' );?>" />
  <div class="ihc-form-line-register ihc-form-text">
    <label class="ihc-labels-register ihc-content-bold"><?php esc_html_e('User Token', 'ihc');?></label>
    <input type="text" name="ihc_pushover_token" value="<?php echo esc_attr($data['ihc_pushover_token']);?>"/>
  </div>
  <div class="ihc-submit-form ihc-content-pushover-button">
    <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="indeed_submit" class="ihc-submit-bttn-fe" />
  </div>
</form>
