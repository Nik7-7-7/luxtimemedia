<div class="uap-ap-wrap">
	<?php if (!empty($data['title'])):?>
		<h3><?php echo do_shortcode($data['title']);?></h3>
	<?php endif;?>
	<?php if (!empty($data['content'])):?>
		<p><?php echo do_shortcode($data['content']);?></p>
	<?php endif;?>

    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Setup PushOver Notification", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <?php esc_html_e("You can get real-time notifications directly on your smartphone via PushOver application.", 'uap');?>
                </div>
             </div>
                <div class="uap-row ">
            	<div class="uap-col-xs-8">
	<form method="post" >
		<div class="uap-form-line-register uap-form-text">
        	<div class="uap-account-title-label"><?php esc_html_e('Personal User Token', 'uap');?></div>
			<input type="text" name="uap_pushover_token" value="<?php echo esc_uap_content($data['uap_pushover_token']);?>"  class="uap-public-form-control "/>
            <div class="uap-account-notes"><?php echo esc_html__("Sign up on pushover.net for a new account in order to get your Token.", 'uap');?></div>
		</div>
		<div class="uap-submit-form">
			<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="indeed_submit" class="button button-primary button-large"  <?php echo (isset($data['preview'])) ? 'disabled' : ''; ?> />
		</div>
	</form>
    			</div>
         </div>
    </div>
    </div>
</div>
