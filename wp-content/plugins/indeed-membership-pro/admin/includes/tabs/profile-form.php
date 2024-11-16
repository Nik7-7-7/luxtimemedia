<?php

echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_profile_form_settings_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_profile_form_settings_nonce']), 'ihc_admin_profile_form_settings_nonce' ) ){
    ihc_save_update_metas('profile-form-settings'); // save update metas
}

$meta_arr = ihc_return_meta_arr('profile-form-settings'); // getting metas
wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
?>
<div class="iump-page-headline"><?php esc_html_e('Profile Form Showcase', 'ihc');?></div>
  <div class="impu-shortcode-display-wrapper">
    <div class="impu-shortcode-display">
      [ihc-edit-profile-form]
    </div>
  </div>

<form  method="post" >
  <input type="hidden" name="ihc_admin_profile_form_settings_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_profile_form_settings_nonce' );?>" />
  <div class="ihc-stuffbox">
    <h3><?php esc_html_e('Profile Form Settings', 'ihc');?></h3>
    <div class="inside">
      <div>
        <div class="iump-register-select-template">
          <?php
            $templates = array(

            'ihc-register-14'=>'(#14) '.esc_html__('Ultimate Member', 'ihc'),
            'ihc-register-10'=>'(#10) '.esc_html__('BootStrap Theme', 'ihc'),
            'ihc-register-9'=>'(#9) '.esc_html__('Radius Theme', 'ihc'),
            'ihc-register-8'=>'(#8) '.esc_html__('Simple Border Theme', 'ihc'),
            'ihc-register-13'=>'(#13) '.esc_html__('Double BootStrap Theme', 'ihc'),
            'ihc-register-12'=>'(#12) '.esc_html__('Dobule Radius Theme', 'ihc'),
            'ihc-register-11'=>'(#11) '.esc_html__('Double Simple Border Theme', 'ihc'),
            'ihc-register-7'=>'(#7) '.esc_html__('BackBox Theme', 'ihc'),
            'ihc-register-6'=>'(#6) '.esc_html__('Double Strong Theme', 'ihc'),
            'ihc-register-5'=>'(#5) '.esc_html__('Strong Theme', 'ihc'),
            'ihc-register-4'=>'(#4) '.esc_html__('PlaceHolder Theme', 'ihc'),
            'ihc-register-3'=>'(#3) '.esc_html__('Blue Box Theme', 'ihc'),
            'ihc-register-2'=>'(#2) '.esc_html__('Basic Theme', 'ihc'),
            'ihc-register-1'=>'(#1) '.esc_html__('Standard Theme', 'ihc')
            );
          ?>
          <?php esc_html_e('Profile Form Template', 'ihc');?>
          <select name="ihc_profile_form_template" id="ihc_profile_form_template" onChange="" class="ihc_profile_form_template-st">
          <?php

            foreach ($templates as $k=>$v){
            ?>
              <option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_profile_form_template']) echo esc_attr('selected');?> >
                <?php echo esc_html($v);?>
              </option>
            <?php
            }
          ?>
          </select>
        </div>
      </div>

      <div class="inside">
        <p><?php esc_html_e('In order to decide what type of fields will be available in Form Profile, you may access ', 'ihc');?><a target='_blank' href='<?php echo esc_url( $url ."&tab=register&subtab=custom_fields" );?>'><?php esc_html_e('Custom Fields', 'ihc'); ?></a></p>
      </div>

      <div class="ihc-wrapp-submit-bttn">
        <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
      </div>
     </div>
  </div>

  <!-- custom css -->
  <div class="ihc-stuffbox iump-custom-css-box-wrapper">
    <h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
    <div class="inside">
      <div class="iump-form-line">
        <textarea name="ihc_profile_form_custom_css" id="ihc_register_custom_css" class="ihc-dashboard-textarea ihc-dashboard-textarea-full" ><?php
        echo stripslashes($meta_arr['ihc_profile_form_custom_css']);
        ?></textarea>
      </div>
      <div class="ihc-wrapp-submit-bttn">
        <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large ihc_submit_bttn" />
      </div>
    </div>

  </div>

</form>
