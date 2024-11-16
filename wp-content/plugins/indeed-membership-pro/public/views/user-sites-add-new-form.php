<?php if (!empty($data['error'])):?>
  <div class="ihc-wrapp-the-errors"><?php echo esc_html($data['error']);?></div>
<?php endif;?>
<?php if (!empty($data['success'])):?>
  <div class="ihc-success-box"><?php esc_html_e( 'Saved', 'ihc' );?></div>
<?php endif;?>

<?php global $current_site;?>


<?php if ( empty($data['success']) ):?>
<form method="post"  >
  <input type="hidden" name="ihc_multi_site_add_edit_nonce" value="<?php echo wp_create_nonce( 'ihc_multi_site_add_edit_nonce' );?>" />
  <div class="ihc-form-line-register ihc-form-text">
    <label class="ihc-labels-register ihc-content-bold"><?php esc_html_e('Site Address', 'ihc');?></label>
    <?php if ( isset( $current_site->domain ) && isset( $current_site->path ) ):?>
        <?php echo esc_url($current_site->domain . $current_site->path );?>
    <?php endif;?>
    <input type="text" name="domain" value=""/>
  </div>
  <div class="ihc-form-line-register ihc-form-text">
    <label class="ihc-labels-register ihc-content-bold"><?php esc_html_e('Site Title', 'ihc');?></label>
    <input type="text" name="title" value=""/>
  </div>
  <input type="hidden" name="lid" value="<?php echo esc_attr($data['lid']);?>" />
  <div class="ihc-submit-form ihc-content-pushover-button">
    <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="add_new_site" class="ihc-submit-bttn-fe" />
  </div>
</form>
<?php endif;?>
