<div class="ulp-popup-wrapp" id="ulp_popup_box">
	<div class="ulp-the-popup ulp-notification-send-popup">
        <div class="ulp-popup-top">
        	<div class="title"><?php esc_html_e('Send a Test Email', 'ulp');?></div>
            <div class="close-bttn" onClick="ulpCloseNotifPopup();"></div>
            <div class="clear"></div>
        </div>
        <div class="ulp-popup-content ulp-notification-send-wrapper">
        	<div class="ulp-popup-content-wrapp">
              <h3><?php esc_html_e('Sent a test to', 'ulp');?></h3>
              <input type="text" value="<?php echo get_option('admin_email');?>" class="ulp-js-notification-test-email" />
							<input type="hidden" class="ulp-js-notification-test-id" value="<?php echo esc_attr(sanitize_text_field($_POST['id']));?>" />
          		<div class="ulp-send-additional-message">
								<p><?php esc_html_e('Dynamic {constants} will not be replaced with real data inside Test Email.', 'ulp');?></p>
							</div>
							<div class="ulp-notification-send-buttons">
									<div class="button button-primary button-large ulp-send-button" onClick="ulpSendNotificationTest();" ><?php esc_html_e('Sent Test', 'ulp');?></div>
									<div class="button button-primary button-large ulp-cancel-button" onClick="ulpCloseNotifPopup();"><?php esc_html_e('Cancel', 'ulp');?></div>
							</div>
        	</div>
    	</div>
    </div>
</div>
