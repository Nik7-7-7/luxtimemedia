<?php
$enabled = uap_is_social_share_intalled_and_active();
?>
<div class="uap-wrapper">
			<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Social Share', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/social-share/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
					<div class="uap-form-line">
						<div class="row">
						<div class="col-xs-7">
							<h2><?php esc_html_e('Activate/Hold Social Share', 'uap');?></h2>
							<p><?php esc_html_e("This Feature will work only if You have ","uap");?> '<strong><?php esc_html_e(" Social Share & Locker Pro Wordpress Plugin ", "uap");?></strong>' <?php esc_html_e(" installed and activated.","uap");?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_social_share_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_social_share_enable');" <?php echo esc_attr($checked);?> <?php echo (!$enabled) ? 'disabled' : '';?>/>
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_social_share_enable" value="<?php echo esc_attr($data['metas']['uap_social_share_enable']);?>" id="uap_social_share_enable" />
						</div>
						</div>

						<div class="row">
						<div class="col-xs-6">
							<h2><?php esc_html_e('Custom Share Message', 'uap');?></h2>
							<p><?php esc_html_e('For a better share action, you may set a custom message that will be listed besides the affiliate links.', 'uap');?></p>

							<textarea name="uap_social_share_message" class="uap-custom-css-box"><?php echo uap_correct_text($data['metas']['uap_social_share_message']);?></textarea>
						</div>
						</div>

						<div class="row">
						<div class="col-xs-6">
							<h2><?php esc_html_e('Shortcode', 'uap');?></h2>
							<p><?php esc_html_e('You can generate the social share shortcode from the "Social Share & Locker" dashboard and paste it here.', 'uap');?></p>
							<p><?php
								if ($enabled){
									echo esc_uap_content('<a href="' . $data['social_share_page'] . '" target="_blank">' . esc_html__('Click here', 'uap') . '</a>' . esc_html__(' to grab a new shortcode.', 'uap'));
								}
								?></p>
							<textarea name="uap_social_share_shortcode" class="uap-custom-css-box"><?php echo uap_correct_text($data['metas']['uap_social_share_shortcode']);?></textarea>
							</div>
						</div>
<?php
 $installed_plugins = get_plugins();
 $plugin_slug = 'indeed-social-media/indeed-social-media.php';
 if(array_key_exists( $plugin_slug, $installed_plugins ) || in_array( $plugin_slug, $installed_plugins, true )){
	 $currentVersion = indeed_get_plugin_version( WP_PLUGIN_DIR . '/indeed-social-media/indeed-social-media.php');
	 if ( version_compare( $currentVersion, 7.9, '<' ) ){
	 		$enabled = false;
	 }
 }

?>
						<div class="row">
								<div class="col-xs-7">
									<h2><?php esc_html_e('Activate/Hold Social Share on Creatives', 'uap');?></h2>
									<p><?php esc_html_e("This Feature will work only if You have ","uap");?> '<strong><?php esc_html_e(" Social Share & Locker Pro Wordpress Plugin ", "uap");?></strong>' <?php esc_html_e(" installed and activated.","uap");?></p>
									<?php if ( !$enabled ):?>
											<p><?php esc_html_e("In order for this feature to work properly You need Social Share & Locker Pro >7.9 version.", "uap");?></p>
									<?php endif;?>
									<label class="uap_label_shiwtch uap-switch-button-margin">
										<?php $checked = ($data['metas']['uap_social_share_enable_on_creatives']) ? 'checked' : '';?>
										<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_social_share_enable_on_creatives');" <?php echo esc_attr($checked);?> <?php echo (!$enabled) ? 'disabled' : '';?>/>
										<div class="switch uap-display-inline"></div>
									</label>
									<input type="hidden" name="uap_social_share_enable_on_creatives" value="<?php echo esc_attr($data['metas']['uap_social_share_enable_on_creatives']);?>" id="uap_social_share_enable_on_creatives" />
								</div>
						</div>

						<div class="row">
						<div class="col-xs-6">
							<h2><?php esc_html_e('Shortcode', 'uap');?></h2>
							<p><?php esc_html_e('You can generate the social share shortcode from the "Social Share & Locker" dashboard and paste it here.', 'uap');?></p>
							<p><?php
								if ($enabled){
									echo esc_uap_content('<a href="' . $data['social_share_page'] . '" target="_blank">' . esc_html__('Click here', 'uap') . '</a>' . esc_html__(' to grab a new shortcode.', 'uap'));
								}
								?></p>
							<textarea name="uap_social_share_shortcodeon_creatives" class="uap-custom-css-box"><?php echo uap_correct_text($data['metas']['uap_social_share_shortcodeon_creatives']);?></textarea>
							</div>
						</div>
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</div>
			</form>
</div>
