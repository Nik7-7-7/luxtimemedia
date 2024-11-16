<div class="uap-wrapper">
<form  method="post">

	<?php $check = uap_get_active_services();?>

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Source Details for Affiliates', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/source-details/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-7">
					<h2><?php esc_html_e('Activate/Hold Source Details for Affiliates', 'uap');?></h2>
					<label class="uap_label_shiwtch uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_source_details_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_source_details_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_source_details_enable" value="<?php echo esc_attr($data['metas']['uap_source_details_enable']);?>" id="uap_source_details_enable" />
				</div>
			</div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>

	<?php if (!empty($check['woo'])) :?>
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('WooCommerce Show Fields', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">

			<div class="row">
				<div class="col-xs-7">
					<?php $temp = explode(',', $data['metas']['uap_source_details_woo_fields_list']);?>
					<?php foreach ($data['fields_available'] as $k=>$v):?>
						<label class="uap-checkbox-wrapp">
						<?php $checked = (in_array($k, $temp)) ? 'checked' : '';?>
						<div><input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="uapMakeInputhString(this, this.value, '#uap_source_details_woo_fields_list');" /> <?php echo esc_attr($v);?><span class="uap-checkmark"></span></div>
						</label>
					<?php endforeach;?>
					</div>
			</div>
			<input type="hidden" name="uap_source_details_woo_fields_list" id="uap_source_details_woo_fields_list" value="<?php echo esc_attr($data['metas']['uap_source_details_woo_fields_list']);?>" />
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
	<?php endif;?>

	<?php if (!empty($check['edd'])) :?>
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Easy Download Digital Show Fields', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-7">
					<?php $temp = explode(',', $data['metas']['uap_source_details_edd_fields_list']);?>
					<?php foreach ($data['fields_available'] as $k=>$v):?>
						<label class="uap-checkbox-wrapp">
						<?php $checked = (in_array($k, $temp)) ? 'checked' : '';?>
						<div><input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="uapMakeInputhString(this, this.value, '#uap_source_details_edd_fields_list');" /> <?php echo esc_html($v);?><span class="uap-checkmark"></span></div>
						</label>
					<?php endforeach;?>
				</div>
			</div>
			<input type="hidden" name="uap_source_details_edd_fields_list" id="uap_source_details_edd_fields_list" value="<?php echo esc_attr($data['metas']['uap_source_details_edd_fields_list']);?>" />
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
	<?php endif;?>

	<?php if (!empty($check['ump'])):?>
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Ultimate Membership Pro Show Fields', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-7">
					<?php $temp = explode(',', $data['metas']['uap_source_details_ump_fields_list']);?>
					<?php foreach ($data['fields_available'] as $k=>$v):?>
							<label class="uap-checkbox-wrapp">
						<?php $checked = (in_array($k, $temp)) ? 'checked' : '';?>
						<div><input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="uapMakeInputhString(this, this.value, '#uap_source_details_ump_fields_list');" /> <?php echo esc_html($v);?><span class="uap-checkmark"></span></div>
					</label>
					<?php endforeach;?>
				</div>
			</div>
			<input type="hidden" name="uap_source_details_ump_fields_list" id="uap_source_details_ump_fields_list" value="<?php echo esc_attr($data['metas']['uap_source_details_ump_fields_list']);?>" />
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
	<?php endif;?>

	<?php if ($indeed_db->is_magic_feat_enable('sign_up_referrals')) :?>
	<?php
		unset($data['fields_available']['phone']);
		unset($data['fields_available']['cart_items']);
		unset($data['fields_available']['billing_address']);
		unset($data['fields_available']['shipping_address']);
		unset($data['fields_available']['order_amount']);
	?>
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('SignUp Show Fields', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-7">
					<?php $temp = explode(',', $data['metas']['uap_source_details_signup_fields_list']);?>
					<?php foreach ($data['fields_available'] as $k=>$v):?>
						<?php $checked = (in_array($k, $temp)) ? 'checked' : '';?>
						<div><input type="checkbox" <?php echo esc_attr($checked);?> value="<?php echo esc_attr($k);?>" onClick="uapMakeInputhString(this, this.value, '#uap_source_details_signup_fields_list');" /> <?php echo esc_attr($v);?></div>
					<?php endforeach;?>
				</div>
			</div>
			<input type="hidden" name="uap_source_details_signup_fields_list" id="uap_source_details_signup_fields_list" value="<?php echo esc_attr($data['metas']['uap_source_details_signup_fields_list']);?>" />
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
	<?php endif;?>

</form>
</div>
