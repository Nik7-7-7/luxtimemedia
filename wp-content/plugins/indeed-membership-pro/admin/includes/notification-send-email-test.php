<div class="ihc-popup-wrapp" id="popup_box">
	<div class="ihc-the-popup ihc-notification-send-popup">
        <div class="ihc-popup-top">
        	<div class="title"><?php esc_html_e('Send a Test Email', 'ihc');?></div>
            <div class="close-bttn" onClick="ihcClosePopup();"></div>
            <div class="clear"></div>
        </div>
        <div class="ihc-popup-content ihc-notification-send-wrapper">
        	<div class="ihc-popup-content-wrapp">

              <h3><?php esc_html_e('Sent a test to', 'ihc');?></h3>
              <input type="text" value="<?php echo get_option('admin_email');?>" class="ihc-js-notification-test-email" />
							<input type="hidden" class="ihc-js-notification-test-id" value="<?php echo sanitize_text_field($_POST['id']);?>" />
          		<div class="ihc-send-additional-message">
								<p><?php esc_html_e('Dynamic {constants} will not be replaced with real data inside Test Email.', 'ihc');?></p>
							</div>
              <div class="ihc-notification-send-buttons">
              		<div class="button button-primary button-large ihc-send-button" onClick="ihcSendNotificationTest();" ><?php esc_html_e('Sent Test', 'ihc');?></div>
									<div class="button button-primary button-large ihc-cancel-button" onClick="ihcClosePopup();"><?php esc_html_e('Cancel', 'ihc');?></div>
							</div>
        	</div>
    	</div>
    </div>
</div>
