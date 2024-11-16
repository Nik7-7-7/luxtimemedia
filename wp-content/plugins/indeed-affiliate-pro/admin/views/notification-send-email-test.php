<div class="uap-popup-wrapp" id="popup_box">
	<div class="uap-the-popup uap-notification-send-popup">
        <div class="uap-popup-top">
        	<div class="title"><?php esc_html_e('Send a Test Email', 'uap');?></div>
            <div class="close-bttn" onClick="uapClosePopup();"></div>
            <div class="clear"></div>
        </div>
        <div class="uap-popup-content uap-notification-send-wrapper">
        	<div class="uap-popup-content-wrapp">

              <h3><?php esc_html_e('Sent a test to', 'uap');?></h3>
              <input type="text" value="<?php echo get_option('admin_email');?>" class="uap-js-notification-test-email" />
							<input type="hidden" class="uap-js-notification-test-id" value="<?php echo sanitize_text_field($_POST['id']);?>" />
          		<div class="uap-send-additional-message">
								<p><?php esc_html_e('Dynamic {constants} will not be replaced with real data inside Test Email.', 'uap');?></p>
							</div>
              <div class="uap-notification-send-buttons">
              		<div class="button button-primary button-large uap-send-button" onClick="uapSendNotificationTest();" ><?php esc_html_e('Send Email', 'uap');?></div>
									<div class="button button-primary button-large uap-cancel-button" onClick="uapClosePopup();"><?php esc_html_e('Cancel', 'uap');?></div>
							</div>
        	</div>
    	</div>
    </div>
</div>
