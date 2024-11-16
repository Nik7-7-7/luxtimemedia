<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=manage');?>"><?php esc_html_e('Manage Discounts', 'ihc');?></a>
	<a class="ihc-subtab-menu-item ihc-subtab-selected" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=settings');?>"><?php esc_html_e('Settings', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>

<?php
ihc_save_update_metas('woo_product_custom_prices');//save update metas
$data['metas'] = ihc_return_meta_arr('woo_product_custom_prices');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form  method="post">
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('WooCommerce Products Discount', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold WooCommerce Products Custom Prices', 'ihc');?></h2>
                <p><?php esc_html_e('Provides special WooCommerce product prices for Users that have assigned a specific Membership.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_woo_product_custom_prices_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_woo_product_custom_prices_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_woo_product_custom_prices_enabled" value="<?php echo esc_attr($data['metas']['ihc_woo_product_custom_prices_enabled']);?>" id="ihc_woo_product_custom_prices_enabled" />
			</div>

			<div class="iump-form-line">
				<h2><?php esc_html_e('On multiple discount values for the same Product show the:', 'ihc');?></h2>
				<select name="ihc_woo_product_custom_prices_tiebreaker">
					<?php
						$possible = array(
											'smallest'=> esc_html__('Smallest final Price', 'ihc'),
											'biggest'=> esc_html__('Biggest final Price', 'ihc'),
						);
						foreach ($possible as $key => $value):
					?>
					<option value="<?php echo esc_attr($key);?>" <?php if ($data['metas']['ihc_woo_product_custom_prices_tiebreaker']==$key){
						 echo esc_attr('selected');
					}
					?> ><?php echo esc_html($value);?></option>
					<?php endforeach;?>
				</select>
                <p><?php esc_html_e('When a user have multiple Subscriptions assigned that will provide a different discount for Woo Products but only one price is available, this option will decide which final price will be listed.', 'ihc');?></p>
			</div>

			<div class="iump-form-line">
				<h2><?php esc_html_e('Choose Price Display', 'ihc');?></h2>
				<p><?php esc_html_e('Show the old price beside the new price', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_woo_product_custom_prices_like_discount']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_woo_product_custom_prices_like_discount');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_woo_product_custom_prices_like_discount" value="<?php echo esc_attr($data['metas']['ihc_woo_product_custom_prices_like_discount']);?>" id="ihc_woo_product_custom_prices_like_discount" />
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

</form>
