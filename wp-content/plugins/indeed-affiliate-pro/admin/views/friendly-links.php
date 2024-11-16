<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Pretty Affiliate Links', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/friendly-affiliate-links/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-8">
					<h2><?php esc_html_e('Activate/Hold Pretty Affiliate Links', 'uap');?></h2>
					<p><?php esc_html_e('Affiliates will be able to use friendly links instead of the default one. They have a better looking structure and are easier to read. Ex: www.yourwebsite.com/ref/smith', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_friendly_links']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_friendly_links');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_friendly_links" value="<?php echo esc_attr($data['metas']['uap_friendly_links']);?>" id="uap_friendly_links" />
				</div>
			</div>
			<br/>
			<p><?php esc_html_e('This feature is recommended only when the referral format is based on username. Affiliates will not be able to build custom links into a friendly structure with variables inside. Ex: ?order=true', 'uap');?></p>
			<p><?php esc_html_e('Refreshing the WP permalinks may be required.', 'uap');?></p>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
</form>
</div>
