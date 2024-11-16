<div class="ihc-popup-wrapp" id="ihc_admin_popup_box">
	<div class="ihc-the-popup">
        <div class="ihc-popup-top">
        	  <div class="title">Ultimate Membership Pro - Send Direct Email</div>
            <div class="close-bttn" id="ihc_send_email_via_admin_close_popup_bttn"></div>
            <div class="clear"></div>
        </div>
        <div class="ihc-popup-content ihc-send-email">
         <div class="ihc-inside-item">
           <div class="row">
             <div class="col-xs-6">
            	<div class="input-group">
             		 <span class="input-group-addon"><?php esc_html_e('From', 'ihc');?></span>
	           		 <input type="text" class="form-control" id="indeed_admin_send_mail_from" value="<?php echo esc_attr($fromEmail);?>"/>
        		</div>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-6">
            	<div class="input-group">
              <span class="input-group-addon"><?php esc_html_e('To', 'ihc');?></span>
	            <input type="text"  class="form-control" id="indeed_admin_send_mail_to" value="<?php echo esc_attr($toEmail);?>" disabled />
        		</div>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-8">
            	<div class="input-group">
              <span class="input-group-addon"><?php esc_html_e('Subject', 'ihc');?></span>
	            <input type="text" class="form-control" id="indeed_admin_send_mail_subject" value="<?php echo esc_ump_content($website) .esc_html__(' Notification', 'ihc');?>" />
        		</div>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-12">
              <h4><?php esc_html_e('Message:', 'ihc');?></h4>
              <textarea id="indeed_admin_send_mail_content"><?php echo esc_html__('Hi ', 'ihc') . $fullName . ", ";?></textarea>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-8">
            	<div class="input-group">
          			<div class="input-group-btn">
              			<button class="ihc-admin-popup-send-email-bttn" type="button" id="indeed_admin_send_mail_submit_bttn"><?php esc_html_e('Send Email', 'ihc');?></button>
          			</div>
        		</div>
            </div>
           </div>
         </div>
    	</div>
    </div>
</div>
