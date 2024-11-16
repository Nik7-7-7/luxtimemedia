<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('woo_payment');//save update metas
}

$data['metas'] = ihc_return_meta_arr('woo_payment');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$data['items'] = Ihc_Db::get_woo_product_level_relations();
?>
<div>
<form method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('WooCommerce Payment Integration', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
					<h2><?php esc_html_e('Activate/Hold WooCommerce Payment Integration', 'ihc');?></h2>
					<p><?php esc_html_e('Link a WooCommerce product with a membership from "Product Edit Page". Once an order with that product is created into Woo, a new Ultimate Membership Pro order is created and membership is managed based on the order status.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_woo_payment_on']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_woo_payment_on');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_woo_payment_on" value="<?php echo esc_attr($data['metas']['ihc_woo_payment_on']);?>" id="ihc_woo_payment_on" />
				<p><strong><?php esc_html_e('The user will have an active membership only when the WooCommerce order will be set as completed (manually or automatically).', 'ihc');?></strong></p>
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input type="submit" id="ihc_submit_bttn" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

		<?php if (!empty($data['items'])):?>
			<div class="ihc-stuffbox">
				<table class="wp-list-table widefat fixed tags ihc-no-margin">
					<thead>
						<tr>
							<th><?php esc_html_e('Ultimate Membership Pro Membership', 'ihc');?></th>
							<th><?php esc_html_e('WooCommerce Product', 'ihc');?></th>
						</tr>
					</thead>
					<tbody class="ihc-alternate">
					<?php foreach ($data['items'] as $array):?>
					<tr>
						<td><span  class="ihc-list-affiliates-name-label"><a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=levels&edit_level=' . $array['level_id']);?>" target="_blank"><?php echo esc_html($array['level_label']);?></a></span></td>
						<td><a href="<?php echo admin_url('post.php?post=' . $array['product_id'] . '&action=edit');?>" target="_blank"><?php echo esc_html($array['product_label']);?></a></td>
					</tr>
					<?php endforeach;?>
					</tbody>
				</table>
			</div>
		<?php endif;?>

</form>
</div>
