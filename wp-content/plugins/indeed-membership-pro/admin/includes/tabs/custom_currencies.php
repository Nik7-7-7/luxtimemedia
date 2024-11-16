<?php
if (!empty($_POST['new_currency_code']) && !empty($_POST['new_currency_name']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	$data = get_option('ihc_currencies_list');
	if (empty($data[$_POST['new_currency_code']])){
		$data[sanitize_text_field($_POST['new_currency_code'])] = sanitize_text_field($_POST['new_currency_name']);
	}
	update_option('ihc_currencies_list', $data);
}
$basic_currencies = ihc_get_currencies_list('custom');
?>
<div>
<form  method="post">

		<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

		<div class="ihc-stuffbox">
			<h3><?php esc_html_e('Custom Currencies', 'ihc');?></h3>
			<div class="inside">
			<h2><?php esc_html_e('Custom Currency', 'ihc');?></h2>
		<p><?php esc_html_e('Add new currencies (with custom symbols) alongside the predefined list', 'ihc');?></p>

		<div class="row ihc-row-no-margin">
		 <div class="col-xs-5 ihc-col-no-padding">

				<div class="iump-form-line">
					<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Code:', 'ihc');?></span>
					<input type="test" value="" name="new_currency_code" class="form-control"/>
				</div>
					<p><?php esc_html_e('Insert a valid Currency Code, ex: ', 'ihc');?><span><strong><?php esc_html_e('USD, EUR, CAD.', 'ihc');?></strong></span></p>
				</div>

				<div class="iump-form-line">
					<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Name:', 'ihc');?></span>
					<input type="text" value="" name="new_currency_name" class="form-control"/>
				</div>
				</div>
			</div>
		</div>

				<div class="ihc-wrapp-submit-bttn iump-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
		<?php if ($basic_currencies!==FALSE && count($basic_currencies)>0){?>
		<div class="ihc-dashboard-form-wrap iump-rsp-table ihc-admin-user-data-list">
			<table class="wp-list-table widefat fixed tags" id="ihc-levels-table">
				<thead>
					<tr>
						<th class="manage-column">Code</th>
						<th class="manage-column">Name</th>
						<th class="manage-column" width="80px;">Delete</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($basic_currencies as $code=>$name){
					?>
					<tr id="ihc_div_<?php echo esc_attr($code);?>">
						<td><?php echo esc_html($code);?></td>
						<td><?php echo esc_html($name);?></td>
						<td><i class="fa-ihc ihc-icon-remove-e" onClick="ihcRemoveCurrency('<?php echo esc_attr($code);?>');"></i></td>
					</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</div>
	<?php }?>
</form>
</div>
