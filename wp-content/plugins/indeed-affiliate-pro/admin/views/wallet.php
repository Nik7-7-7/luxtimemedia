<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Wallet', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/wallet/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-8">
					<h2><?php esc_html_e('Activate/Hold Wallet', 'uap');?></h2>
					<p><?php esc_html_e('Affiliates will have the option to spend their earnings directly in the website by using generated coupons with a specific flat discount.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_wallet_enable']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_wallet_enable');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_wallet_enable" value="<?php echo esc_attr($data['metas']['uap_wallet_enable']);?>" id="uap_wallet_enable" />
				</div>
			</div>
			<div class="row">
				<div class="col-xs-8">
					<p><?php esc_html_e('Establish a minimum amount required for an affiliate to be able to move his earnings from his account into his wallet. Only referrals that are verified but not yet paid can be available for converting into coupons in an affiliate "Wallet".', 'uap');?></p>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<div class="input-group">
						<label class="input-group-addon"><?php esc_html_e('Minimum Amount', 'uap');?></label>
						<input type="number" class="form-control" step='<?php echo uapInputNumerStep();?>' name="uap_wallet_minimum_amount" value="<?php echo esc_attr($data['metas']['uap_wallet_minimum_amount']);?>" />

					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-4">
					<div class="input-group">
						<label class="iump-labels-special"><?php esc_html_e('Excluded sources:', 'uap');?></label>
						<div>
								<?php
									if ($data['metas']['uap_wallet_exclude_sources']!='')
											$temp = explode(',', $data['metas']['uap_wallet_exclude_sources']);
									else
											$temp = array();
									$types = uap_get_active_services();
									foreach ($types as $key=>$value):?>
									<div>
										<label class="uap-checkbox-wrapp">
										<input type="checkbox" <?php echo (in_array($key, $temp)) ? 'checked' : '';?> onClick="uapMakeInputhString(this, '<?php echo esc_attr($key);?>', '#uap_wallet_exclude_sources');" /> <?php echo esc_attr($value);?>
										<span class="uap-checkmark"></span>
									</label>
									</div>
								<?php endforeach;?>
						</div>
						<input type="hidden" name="uap_wallet_exclude_sources" value="<?php echo esc_attr($data['metas']['uap_wallet_exclude_sources']);?>" id="uap_wallet_exclude_sources"/>
					</div>
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
