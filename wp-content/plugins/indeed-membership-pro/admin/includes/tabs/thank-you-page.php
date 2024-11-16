<?php

echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_thank_you_settings_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_thank_you_settings_nonce']), 'ihc_admin_thank_you_settings_nonce' ) ){
    ihc_save_update_metas('thank-you-page-settings'); // save update metas
}

$meta_arr = ihc_return_meta_arr('thank-you-page-settings'); // getting metas
wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
?>
<div class="iump-page-headline"><?php esc_html_e('Thank You Showcase', 'ihc');?></div>
  <div class="impu-shortcode-display-wrapper">
    <div class="impu-shortcode-display">
      [ihc-thank-you-page]
    </div>
  </div>

<form  method="post" >
  <input type="hidden" name="ihc_admin_thank_you_settings_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_thank_you_settings_nonce' );?>" />
  <div class="ihc-stuffbox">
    <h3><?php esc_html_e('Thank You Page Settings', 'ihc');?></h3>
    <div class="inside">
      <div class="iump-form-line iump-no-border">
        <div class="row">
   		 		<div class="col-xs-8">
   					 		<div class="iump-wp_editor">
   					 		<?php wp_editor(stripslashes($meta_arr['ihc_thank_you_message']), 'ihc_thank_you_message', array('textarea_name'=>'ihc_thank_you_message', 'editor_height'=>200));?>
   					 		</div>
   				</div>
          <div class="col-xs-3">
            <h4><?php esc_html_e('Template Tags', 'ihc');?></h4>
            <div class="ump-js-list-constants">
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{customer_id}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{customer_email}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{customer_name}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{membership_id}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{membership_name}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{amount}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{currency}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{order_code}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{order_date}</span></div>
                <div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_thank_you_message" >{order_payment_method}</span></div>
            </div>
          </div>
   		 </div>
       <div>
         </div>
         <p><?php esc_html_e('Customize the Message available on Thank You page where Buyer will be redirected after each Payment. ', 'ihc');?></p>
       </div>
       <div class="iump-form-line">
						<h4><?php esc_html_e('Missing Data response', 'ihc');?></h4>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Message', 'ihc');?></span>
																<input name="ihc_thank_you_error_message" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_thank_you_error_message']);?>">
														</div>
										</div>
						</div>
					</div>




      <div class="ihc-wrapp-submit-bttn ihc-wrapp-submit-bttn">
        <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" id="ihc_submit_bttn" class="button button-primary button-large" />
      </div>
     </div>
  </div>

  <!-- custom css -->
  <div class="ihc-stuffbox  iump-custom-css-box-wrapper">
    <h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
    <div class="inside">
      <div class="iump-form-line">
        <textarea name="ihc_thank_you_custom_css" id="ihc_thank_you_custom_css" class="ihc-dashboard-textarea-full" ><?php
        echo stripslashes($meta_arr['ihc_thank_you_custom_css']);
        ?></textarea>
      </div>
      <div class="ihc-wrapp-submit-bttn">
        <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" id="ihc_submit_bttn" class="button button-primary button-large" />
      </div>
    </div>

  </div>

</form>
