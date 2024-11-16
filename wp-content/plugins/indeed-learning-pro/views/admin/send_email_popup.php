<div class="ulp-popup-wrapp" id="ulp_admin_popup_box">
	<div class="ulp-the-popup">
        <div class="ulp-popup-top">
        	  <div class="title">Ultimate Learning Pro - Send Direct Email</div>
            <div class="close-bttn" id="ulp_send_email_via_admin_close_popup_bttn"></div>
            <div class="clear"></div>
        </div>
        <div class="ulp-popup-content ulp-send-email">
         <div class="ulp-inside-item">
           <div class="row">
             <div class="col-xs-6">
            	<div class="input-group">
             		 <span class="input-group-addon"><?php esc_html_e('From', 'ulp');?></span>
	           		 <input type="text" class="form-control" id="indeed_admin_send_mail_from" value="<?php echo esc_attr($fromEmail);?>"/>
        		</div>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-6">
            	<div class="input-group">
              <span class="input-group-addon"><?php esc_html_e('To', 'ulp');?></span>
	            <input type="text"  class="form-control" id="indeed_admin_send_mail_to" value="<?php echo esc_attr($toEmail);?>" disabled />
        		</div>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-8">
            	<div class="input-group">
              <span class="input-group-addon"><?php esc_html_e('Subject', 'ulp');?></span>
	            <input type="text" class="form-control" id="indeed_admin_send_mail_subject" value="<?php echo esc_attr($website) . esc_html__(' Notification', 'ulp');?>" />
        		</div>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-12">
              <h4><?php esc_html_e('Message:', 'ulp');?></h4>
              <textarea id="indeed_admin_send_mail_content"><?php echo esc_html__('Hi ', 'ulp') . esc_html($fullName) . ", ";?></textarea>
            </div>
           </div>
           <div class="row">
             <div class="col-xs-8">
            	<div class="input-group">
          			<div class="input-group-btn">
              			<button class="btn btn-primary pointer" type="button" id="indeed_admin_send_mail_submit_bttn"><?php esc_html_e('Send Email', 'ulp');?></button>
          			</div>
        		</div>
            </div>
           </div>
         </div>
    	</div>
    </div>
</div>
