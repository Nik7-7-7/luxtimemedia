<div class="uap-wrapper">
		<div class="uap-stuffbox">
			<form action="<?php echo esc_url($data['url-manage']);?>" method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<h3 class="uap-h3"><?php esc_html_e('Add/Edit Referral', 'uap');?></h3>

				<div class="inside uap-referral-add">
					<div class="uap-form-line uap-referral-add-title">
						<h4><?php esc_html_e('Affiliate Details', 'uap');?></h4>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label "><?php esc_html_e('Affiliate User', 'uap');?><span class="uap-color-red">*</span></label>
						<div class="uap-referral-add-field-wrap">
						<select name="affiliate_id"><?php
							foreach ($data['affiliates'] as $k=>$v):
								$selected = ($data['metas']['affiliate_id']==$k) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v['username']);?></option>
								<?php
							endforeach;
						?></select>
						<span class="uap-sublabel"><?php esc_html_e("The affiliate's username", 'uap');?></span>
						</div>
					</div>

					<div class="uap-form-line uap-referral-add-title">
						<h4><?php esc_html_e('Referral Amount', 'uap');?></h4>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Amount', 'uap');?><span class="uap-color-red">*</span></label>
						<div class="uap-referral-add-field-wrap">
							<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' value="<?php echo esc_attr($data['metas']['amount']);?>" name="amount" />
							<span class="uap-sublabel"><?php esc_html_e('The Referral Amount earned by the Affiliate for this Referral', 'uap');?></span>
						</div>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Currency', 'uap');?><span class="uap-color-red">*</span></label>
						<div class="uap-referral-add-field-wrap">
						<select name="currency"><?php
							$currency = uap_get_currencies_list();
							foreach ($currency as $k=>$v){
								$selected = ($k==$data['metas']['currency']) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
								<?php
							}
						?></select>
						</div>
					</div>

					<div class="uap-form-line uap-referral-add-title">
						<h4><?php esc_html_e('Created Time', 'uap');?></h4>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Date', 'uap');?><span class="uap-color-red">*</span></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_attr($data['metas']['date']);?>" name="date" id="referrals_date"/>
							<span class="uap-sublabel"><?php esc_html_e('Date and Time when current Referral have been created', 'uap');?></span>
						</div>
					</div>

					<div class="uap-form-line uap-referral-add-title">
						<h4><?php esc_html_e('Referral Identificator', 'uap');?></h4>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Source', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_html($data['metas']['source']);?>" name="source" />
							<span class="uap-sublabel"><?php esc_html_e('Where this Referral came from. Source slug, predefined (ex: woo, ump, edd, ulp, bonus, User SignUp, from landing commissions)  or custom one', 'uap');?></span>
						</div>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Reference', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_attr($data['metas']['reference']);?>" name="reference" />
							<span class="uap-sublabel"><?php esc_html_e('A reference for this referral. Usually this would be the order ID of the associated purchase or User ID for User SignUp', 'uap');?></span>
						</div>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Reference Details', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<textarea name="reference_details"><?php echo esc_html($data['metas']['reference_details']);?></textarea>
							<span class="uap-sublabel"><?php esc_html_e('Additional details for current Reference', 'uap');?></span>
						</div>
					</div>

					<div class="uap-form-line uap-referral-add-title">
						<h4><?php esc_html_e('Additional Details', 'uap');?></h4>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Click ID', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_attr($data['metas']['visit_id']);?>" name="visit_id" />
							<span class="uap-sublabel"><?php esc_html_e('The ID of the Click which resulted to this Referral, if applicable. Click entry is accessible in the ', 'uap');?><a href="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro').'&tab=visits';?>" target="_blank"><?php esc_html_e('Clicks', 'uap');?></a><?php esc_html_e(' section', 'uap');?></span>
						</div>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Customer User ID', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_attr($data['metas']['refferal_wp_uid']);?>" name="refferal_wp_uid" />
							<span class="uap-sublabel"><?php esc_html_e('The ID of the Customer associated with this Referral', 'uap');?></span>
						</div>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Affiliate Campaign', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_attr($data['metas']['campaign']);?>" name="campaign" />
							<span class="uap-sublabel"><?php esc_html_e('The campaign slug that the affiliate user created using the Affiliate Portal page', 'uap');?></span>
						</div>
					</div>

					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Description', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<textarea name="description"><?php echo esc_html($data['metas']['description']);?></textarea>
							<span class="uap-sublabel"><?php esc_html_e('An expanded explanation for the present referral', 'uap');?></span>
						</div>
					</div>

					<div class="uap-display-none <?php echo (!empty($data['metas']['parent_referral_id']) || !empty($data['metas']['child_referral_id']) || $indeed_db->is_magic_feat_enable('mlm')) ? 'uap-display-block' : ''; ?> " >

						<div class="uap-form-line uap-referral-add-title">
							<h4><?php esc_html_e('MLM Linked Referrals', 'uap');?></h4>
							<p><?php esc_html_e('Exclusively designed for MLM workflows.', 'uap');?></p>
						</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Upline Referral ID', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_attr($data['metas']['parent_referral_id']);?>" name="parent_referral_id" />
							<span class="uap-sublabel"><?php esc_html_e('The Referral ID that the Upline Affiliate user has received', 'uap');?></span>
						</div>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Downline Referral ID', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
							<input type="text" value="<?php echo esc_attr($data['metas']['child_referral_id']);?>" name="child_referral_id" />
							<span class="uap-sublabel"><?php esc_html_e('The Referral ID that the Downline Affiliate user has received', 'uap');?></span>
						</div>
					</div>
				</div>

				<div class="uap-form-line uap-referral-add-title">
					<h4><?php esc_html_e('Statuses', 'uap');?></h4>
				</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Status', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
						<select name="status"><?php
							foreach ($data['status_posible'] as $k=>$v):
								$selected = ($data['metas']['status']==$k) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
								<?php
							endforeach;
						?></select>
					</div>
					</div>
					<div class="uap-form-line uap-referral-add-element">
						<label class="uap-label"><?php esc_html_e('Payment', 'uap');?></label>
						<div class="uap-referral-add-field-wrap">
						<select name="payment"><?php
							foreach ($data['payment_posible'] as $k=>$v):
								$selected = ($data['metas']['payment']==$k) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
								<?php
							endforeach;
						?></select>
						<span class="uap-sublabel"><?php esc_html_e('Choose Paid if current Referral have been withdrawn by Affiliate user', 'uap');?></span>
					</div>
					</div>

					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
					</div>
				</div>

				<input type="hidden" name="id" value="<?php echo esc_attr($data['metas']['id']);?>" />

			</form>
		</div>

</div>
