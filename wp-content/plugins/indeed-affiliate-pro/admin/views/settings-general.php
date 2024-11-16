<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

<div class="uap-stuffbox">
	<h3 class="uap-h3"><?php esc_html_e('General Settings', 'uap');?></h3>
	<div class="inside">
		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-12">
					<h2><?php esc_html_e('Which Rate should be used for Referral calculation?', 'uap');?></h2>
					<p><?php esc_html_e('If there are multiple Rate set for the same action, like Ranks&Product Rates or multiple Product Rates decide which one will be taken in consideration', 'uap');?></p>
				</div>
				<div class="col-xs-6">
							<select name="uap_referral_offer_type" class="form-control m-bot15"><?php
							$types = array('lowest'=>esc_html__('Lowest Amount', 'uap'), 'biggest'=>esc_html__('Biggest Amount', 'uap'));
							foreach ($types as $key=>$value){
								$selected = ($key==$data['metas']['uap_referral_offer_type']) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($key)?>" <?php echo esc_attr($selected);?>><?php echo esc_html($value);?></option>
								<?php
							}
						?></select>
					</div>
				</div>
		</div>
		<div class="uap-form-line">
					<h2><?php esc_html_e('Redirect', 'uap');?></h2>
					<p><?php esc_html_e('Redirect Same Page Without URL parameters. This feature enhances the overall affiliate experience, providing a seamless and personalized approach to URL presentation.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_redirect_without_param']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_redirect_without_param');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_redirect_without_param" value="<?php echo esc_attr($data['metas']['uap_redirect_without_param']);?>" id="uap_redirect_without_param" />
		</div>

		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
				<h2><?php esc_html_e('Affiliate Link Settings', 'uap');?></h2>
				<br/>
				<p><?php esc_html_e('Set the Affiliate Link Variable name', 'uap');?></p>
					<div class="form-group">
						<input type="text" class="form-control" value="<?php echo esc_attr($data['metas']['uap_referral_variable']);?>" name="uap_referral_variable" />
					</div>
				</div>
				<div class="col-xs-12">
				<p><?php echo esc_html__('The URL variable for affiliate URLs. For example: ', 'uap')."<strong>".get_site_url().'/?'.esc_attr($data['metas']['uap_referral_variable']).'=1</strong>';?></p>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-6">
				<h4><?php esc_html_e('Base Affiliate Link', 'uap');?></h4>
				<br/>
					<div class="form-group">
						<?php if (empty($data['metas']['uap_referral_custom_base_link'])){
							 $data['metas']['uap_referral_custom_base_link'] = get_home_url();
						}?>
						<input type="text" class="form-control" onBlur="uapCheckBaseReferralLink(this.value, '<?php echo get_site_url();?>');" value="<?php echo esc_attr($data['metas']['uap_referral_custom_base_link']);?>" name="uap_referral_custom_base_link" />
					</div>
				</div>
				<div class="col-xs-12">
					<p id="base_referral_link_alert"><?php esc_html_e('Please insert a link from the website on which this plugin is installed.
Do not enter a link from a different website.', 'uap');?></p>
				</div>
			</div>

		</div>
		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
				<p class="uap-labels-special"><?php esc_html_e('Affiliate Link Format:', 'uap');?></p>
				<select name="uap_default_ref_format" class="form-control m-bot15"><?php
				$referral_format = array('id' => 'Affiliate ID', 'username'=>'Username');
				foreach ($referral_format as $k=>$v){
					$selected = ($data['metas']['uap_default_ref_format']==$k) ? 'selected' : '';
					?>
					<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
					<?php
				}
				?></select>
			</div>
			<div class="col-xs-12">
				<p><?php echo esc_html__('Display affiliate URLs to affiliates with either their unique affiliate ID or Username appended. For Example: ', 'uap')."<strong>".get_site_url().'/?'.esc_attr($data['metas']['uap_referral_variable']).'=1</strong>'.' or '."<strong>".get_site_url().'/?'.esc_attr($data['metas']['uap_referral_variable']).'=admin</strong>';?></p>
				</div>
			</div>
		</div>

		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
						<p class="uap-labels-special"><?php esc_html_e('Search into URL for both affiliate link format:', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_search_into_url_for_affid_or_username']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_search_into_url_for_affid_or_username');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_search_into_url_for_affid_or_username" value="<?php echo esc_attr($data['metas']['uap_search_into_url_for_affid_or_username']);?>" id="uap_search_into_url_for_affid_or_username" />
				</div>
			</div>
		</div>


	<div class="uap-form-line">
		<div class="row">
			<div class="col-xs-12">
			<h4><?php esc_html_e('Affiliate Link URL Blacklist', 'uap');?></h4>
			<br/>
			<p><?php esc_html_e('If you wish to block specific affiliates’ websites from generating referrals for any reason, you can do it by placing here their website urls. Place one URL per line.', 'uap');?></p>
			</div>
			<div class="col-xs-6">
				<div class="form-group">
					<textarea class="form-control"  name="uap_blocked_referers"><?php echo esc_html($data['metas']['uap_blocked_referers']);?></textarea>
				</div>
			</div>
		</div>
	</div>

		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
				<h4><?php esc_html_e('Campaign Settings', 'uap');?></h4>
				<br/>
				<p><?php esc_html_e('Set the Campaign Variable name', 'uap');?></p>
					<div class="form-group">
						<input type="text" class="form-control" value="<?php echo esc_attr($data['metas']['uap_campaign_variable']);?>" name="uap_campaign_variable"  />
					</div>
				</div>
			</div>
		</div>

			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-6">
					<h2><?php esc_html_e('Cookie Settings', 'uap');?></h2>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Cookie Expiration:', 'uap');?></span>
						<input type="number" min="1" class="form-control" value="<?php echo esc_attr($data['metas']['uap_cookie_expire']);?>" name="uap_cookie_expire"/>
						<div class="input-group-addon"> <?php esc_html_e("Days", 'uap');?></div>
					</div>
					<p><?php esc_html_e('Enter how many days the affiliate tracking cookie should be valid for.', 'uap');?></p>

					</div>
				</div>
			</div>

			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-12">
						<h4><?php esc_html_e('Cookie Sharing', 'uap');?></h4>
						<p><?php esc_html_e('Enable the sharing of tracking cookies across sub-domains within a multisite installation. When activated, cookies generated on domain.com will also be accessible on sub.domain.com. Please note that this functionality is specific to WordPress Multisite installations.', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_cookie_sharing']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_cookie_sharing');" <?php echo esc_attr($checked);?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_cookie_sharing" value="<?php echo esc_attr($data['metas']['uap_cookie_sharing']);?>" id="uap_cookie_sharing" />
					</div>
				</div>
			</div>


		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('Currency Settings', 'uap');?></h2>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Currency:', 'uap');?></span>
						<select name="uap_currency" class="form-control m-bot15"><?php
							$currency = uap_get_currencies_list();
							foreach ($currency as $k=>$v){
								$selected = ($k==$data['metas']['uap_currency']) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?></select>
						<p><?php esc_html_e('Choose your currency. Note that some payment gateways have currency restrictions.', 'uap');?></p>
					</div>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Currency Symbol position:', 'uap');?></span>
						<select name="uap_currency_position" class="form-control m-bot15"><?php
							$positions = [
															'left'        => esc_html__('Before - $10', 'uap'),
															'right'       => esc_html__('After - 10$', 'uap'),
															'left_space'  => esc_html__('Before with space - $ 10', 'uap'),
															'right_space' => esc_html__('After with space - 10 $', 'uap'),
							];
							foreach ($positions as $k=>$v){
								$selected = ($k==$data['metas']['uap_currency_position']) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?></select>
						<p><?php esc_html_e('Choose the location of the currency symbol.', 'uap');?></p>
					</div>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Thousands Separator', 'uap');?></span>
						<input type="text" value="<?php echo esc_attr($data['metas']['uap_thousands_separator']);?>" name="uap_thousands_separator" class="form-control" />
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Decimals Separator', 'uap');?></span>
						<input type="text" value="<?php echo esc_attr($data['metas']['uap_decimals_separator']);?>" name="uap_decimals_separator" class="form-control" />
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Number of Decimals', 'uap');?></span>
						<input type="number" min="0" value="<?php echo esc_attr($data['metas']['uap_num_of_decimals']);?>" name="uap_num_of_decimals" class="form-control" />
					</div>

					</div>
				</div>
			</div>

			<div class="uap-form-line">
				<div class="row">
					<div class="col-xs-8">
							<h2><?php esc_html_e('Referral Calculation Settings', 'uap');?></h2>
						</div>

				<div class="col-xs-12">
						<h4><?php esc_html_e('Exclude Shipping', 'uap');?></h4>
						<p><?php esc_html_e('Exclude shipping costs from referral calculations to ensure accurate commission attribution. This option allows you to customize how referrals are calculated by excluding the shipping expenses from the total sale value.', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_exclude_shipping']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_exclude_shipping');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_exclude_shipping" value="<?php echo esc_attr($data['metas']['uap_exclude_shipping']);?>" id="uap_exclude_shipping" />

		 </div>
		</div>
<div class="row">
	<div class="col-xs-12">
						<h4><?php esc_html_e('Exclude Tax', 'uap');?></h4>
						<p><?php esc_html_e('Exclude taxes from referral calculations. When enabled, the plugin ensures that taxes are not factored into the referral calculation, allowing you to tailor your commission rules more precisely.', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_exclude_tax']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_exclude_tax');" <?php echo esc_attr($checked);?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_exclude_tax" value="<?php echo esc_attr($data['metas']['uap_exclude_tax']);?>" id="uap_exclude_tax" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12">
					<h4><?php esc_html_e('Save Zero Value Referrals', 'uap');?></h4>
					<p><?php esc_html_e('Allows you to control whether the plugin should store or skip referrals without actual sales or conversions. This can be valuable for tracking the overall impact of your affiliates, providing a comprehensive view of their efforts, regardless of immediate results.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_empty_referrals_enable']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_empty_referrals_enable');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_empty_referrals_enable" value="<?php echo esc_attr($data['metas']['uap_empty_referrals_enable']);?>" id="uap_empty_referrals_enable" />
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h4><?php esc_html_e('Reject Commissions on Refund', 'uap');?></h4>
			<p><?php esc_html_e('Auto reject Unpaid Referrals when the original purchase is refunded or revoked.', 'uap');?></p>
			<label class="uap_label_shiwtch uap-switch-button-margin">
				<?php $checked = ($data['metas']['uap_reject_refund_referrals']) ? 'checked' : '';?>
				<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_reject_refund_referrals');" <?php echo esc_attr($checked);?> />
				<div class="switch uap-display-inline"></div>
			</label>
			<input type="hidden" name="uap_reject_refund_referrals" value="<?php echo esc_attr($data['metas']['uap_reject_refund_referrals']);?>" id="uap_reject_refund_referrals" />
		</div>
	</div>
	<div class="row">
		<div class="col-xs-12">
			<h4><?php esc_html_e('New Customer Commissions', 'uap');?></h4>
			<p><?php esc_html_e("This setting ensures that affiliates receive commissions only for referring new customers, individuals making their initial purchase on your online store. When activated, referrals won't be generated for returning customers. However, subscriptions will consistently generate renewal referrals if you have configured subscription renewal referrals.", 'uap');?></p>
			<label class="uap_label_shiwtch uap-switch-button-margin">
				<?php $checked = ($data['metas']['uap_new_customer_commissions']) ? 'checked' : '';?>
				<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_new_customer_commissions');" <?php echo esc_attr($checked);?> />
				<div class="switch uap-display-inline"></div>
			</label>
			<input type="hidden" name="uap_new_customer_commissions" value="<?php echo esc_attr($data['metas']['uap_new_customer_commissions']);?>" id="uap_new_customer_commissions" />
		</div>
	</div>
		</div>


		<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-12">
					<h2><?php esc_html_e('Automatically Affiliate', 'uap');?></h2>
					<p><?php esc_html_e('Every new user who signs up on your website is instantly enrolled as an affiliate eliminating the need for manual assignments. By automating the affiliate onboarding process, you save time and effortlessly expand your affiliate network.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_all_new_users_become_affiliates']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_all_new_users_become_affiliates');" <?php echo esc_attr($checked);?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_all_new_users_become_affiliates" value="<?php echo esc_attr($data['metas']['uap_all_new_users_become_affiliates']);?>" id="uap_all_new_users_become_affiliates" />
				</div>
			</div>
		</div>

				<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-8">
							<h2><?php esc_html_e('Default Country', 'uap');?></h2>
							<p><?php esc_html_e('Choose a default country for Affiliates submission form. If none is chosen default WordPress Locale will be used instead', 'uap');?></p>
									<select name="uap_defaultcountry" class="form-control m-bot15">
									<option value="" >....</option>
									<?php
									$types = uap_get_countries();
									foreach ($types as $key=>$value){
										$key = strtolower($key);
										$selected = ($key==$data['metas']['uap_defaultcountry']) ? 'selected' : '';
										?>
										<option value="<?php echo esc_attr($key);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($value);?></option>
										<?php
									}
								?></select>
					</div>
				</div>
			</div>

		<div id="uap_save_changes" class="uap-submit-form">
			<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
		</div>
	</div>

</div>
</form>
</div>
