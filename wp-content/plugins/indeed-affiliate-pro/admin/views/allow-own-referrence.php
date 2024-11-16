<div class="uap-wrapper">
			<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Allow Self Referrals', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/allow-own-reference/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
						<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-7">
							<h2><?php esc_html_e('Activate/Hold Allow Self Referrals', 'uap');?></h2>
							<p><?php esc_html_e('Affiliates will be able to earn a commission on their own purchases via their own referral links.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_allow_own_referrence_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_allow_own_referrence_enable');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_allow_own_referrence_enable" value="<?php echo esc_attr($data['metas']['uap_allow_own_referrence_enable']);?>" id="uap_allow_own_referrence_enable" />
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
