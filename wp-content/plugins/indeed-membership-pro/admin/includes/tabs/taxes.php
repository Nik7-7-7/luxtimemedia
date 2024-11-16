<?php
///save
if (isset($_POST['ihc_save']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	/// SAVE TAX
	Ihc_Db::save_tax( indeed_sanitize_array($_POST) );
} else if (!empty($_POST['save_settings']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	///update settings
	ihc_save_metas_group('ihc_taxes_settings', indeed_sanitize_array($_POST) );//save update metas
} else if (!empty($_GET['delete'])){
	/// DELETE TAX
	Ihc_Db::delete_tax( sanitize_text_field($_GET['delete']) );
}
$data['items'] = Ihc_Db::get_all_taxes();
$data['countries'] = ihc_get_countries();
$data['metas'] = ihc_return_meta_arr('ihc_taxes_settings');
?>
<div>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3><?php esc_html_e('Taxes', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Taxes', 'ihc');?></h2>
				<p><?php esc_html_e('You can activate this option to take in place in your membership system.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_enable_taxes']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_enable_taxes');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_enable_taxes" value="<?php echo esc_attr($data['metas']['ihc_enable_taxes']);?>" id="ihc_enable_taxes" />
			</div>
			<div class="iump-form-line">
				<h2><?php esc_html_e('Show Tax details', 'ihc');?></h2>
				<p><?php esc_html_e('Display tax details and amount in the register process.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_show_taxes']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_show_taxes');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_show_taxes" value="<?php echo esc_attr($data['metas']['ihc_show_taxes']);?>" id="ihc_show_taxes" />
				<br/>
				<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Label:', 'ihc');?></span>
						<input type="text" class="form-control" name="ihc_default_tax_label" value="<?php echo esc_attr($data['metas']['ihc_default_tax_label']);?>" />
					</div>
				</div>
				</div>
			 </div>
			 <div class="iump-form-line">
				<h2><?php esc_html_e('General Tax Value', 'ihc');?></h2>
				<p><?php esc_html_e("Set a default tax value that will be used if there isn't a special tax for a specific Country.", 'ihc');?></p>
				<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Default Tax Value:', 'ihc');?></span>
						<input type="number" class="form-control" min="0" step="0.01" name="ihc_default_tax_value" value="<?php echo esc_attr($data['metas']['ihc_default_tax_value']);?>" />
						<div class="input-group-addon">%</div>
					</div>
				</div>
				</div>
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input type="submit" id="ihc_submit_bttn" value="Save Changes" name="save_settings" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>

<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=add_edit_taxes');?>" class="indeed-add-new-like-wp"><i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Tax', 'ihc');?></a>

<?php
if ($data['items']):
	?>
	<div>
		<table class="wp-list-table widefat fixed tags ihc-admin-tables ihc-taxes-table">
			<thead>
				<tr>
					<th class="manage-column"><?php esc_html_e('Country', 'ihc');?></th>
					<th class="manage-column"><?php esc_html_e('State', 'ihc');?></th>
					<th class="manage-column"><?php esc_html_e('Label', 'ihc');?></th>
					<th class="manage-column"><?php esc_html_e('Tax Value (%)', 'ihc');?></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$i = 1;
					foreach ($data['items'] as $item):?>
					<tr  class="<?php if($i%2==0){
						 echo esc_attr('alternate');
					}
					?>" onMouseOver="ihcDhSelector('#tax_tr_<?php echo esc_attr($item['id']);?>', 1);" onMouseOut="ihcDhSelector('#tax_tr_<?php echo esc_attr($item['id']);?>', 0);">
						<td><?php echo esc_html($data['countries'][$item['country_code']]);?>
							<div class="ihc-visibility-hidden" id="tax_tr_<?php echo esc_attr($item['id']);?>">
								<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=add_edit_taxes&edit=') . esc_attr($item['id']);?>"><?php esc_html_e('Edit', 'ihc');?></a>
								|
								<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=taxes&delete=') . esc_attr($item['id']);?>" class="ihc-red"><?php esc_html_e('Delete', 'ihc');?></a>
							</div>
						</td>
						<td><?php
							if (!empty($item['state_code'])){
								echo esc_html($item['state_code']);
							} else {
								echo esc_html('-');
							}
						?></td>
						<td><?php echo esc_html($item['label']);?></td>
						<td><?php echo esc_html($item['amount_value']);?></td>
					</tr>
				<?php $i++;
				endforeach;?>
			</tbody>
		</table>
	</div>
	<?php
else :
	?>
	<div><?php esc_html_e('No Taxes yet!', 'ihc');?></div>
	<?php
endif;
?>
</div>
<?php
