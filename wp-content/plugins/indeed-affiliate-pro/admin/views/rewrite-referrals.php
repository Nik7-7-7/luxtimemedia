<div class="uap-wrapper">
			<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Credit Last Referrer', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/reassign-referral/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
					<div class="inside">
					<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-10">
							<h2><?php esc_html_e('Activate/Hold Credit Last Referrer', 'uap');?></h2>
							<p><?php esc_html_e("Decides if a new customer is re-assigned to the first or last linked affiliate. If the same customer is referred to a different affiliate than the first one, you can decide if the reference will be changed or not.
Example: John is a customer that is linked to Smith (affiliate). John enters the website but does not buy anything. Later, John enters the website through Bob's link (affiliate) and makes a purchase. You can decide if John will be linked to the first affiliate (Smith), or the last one (Bob), therefore deciding which affiliate will receive referral.", 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_rewrite_referrals_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_rewrite_referrals_enable');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_rewrite_referrals_enable" value="<?php echo esc_attr($data['metas']['uap_rewrite_referrals_enable']);?>" id="uap_rewrite_referrals_enable" />
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
