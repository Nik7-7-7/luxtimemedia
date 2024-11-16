<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item ihc-subtab-selected" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=manage');?>"><?php esc_html_e('Manage Discounts', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=settings');?>"><?php esc_html_e('Settings', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
if (!empty($_POST['ihc_save'])){
	if (!empty($_POST['levels']) && !empty($_POST['products'])){
		$lids = explode(',', $_POST['levels']);
		$products = explode(',', $_POST['products']);
	}

	if (!empty($lids) && !empty($products)){
		$rule_id = Ihc_Db::ihc_woo_product_custom_price_save_item($_POST);
		if (!empty($_POST['id'])){
			Ihc_Db::ihc_woo_product_custom_price_lid_product_delete($_POST['id']);
		}
		foreach ($lids as $lid){
			foreach ($products as $product_id){
				Ihc_Db::ihc_woo_product_custom_price_lid_product_save($rule_id, $lid, $product_id, $_POST['settings']['woo_item_type']);
			}
		}
	}
}
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-page-title">Ultimate Membership Pro -
	<span class="second-text">
		<?php esc_html_e('WooCommerce Products Discount', 'ihc');?>
	</span>
</div>
<div class="clear"></div>
<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=add_edit');?>" class="indeed-add-new-like-wp">
	<i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Discount', 'ihc');?>
</a>
<div class="clear"></div>

<?php
$data = Ihc_Db::ihc_woo_products_custom_price_get_all();
if ($data){
    $the_currency =	get_option('ihc_currency');
	foreach ($data as $id => $item_data){
		$edit_url = admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=add_edit&id=' . $id);
		?>
		<div class="ihc-coupon-admin-box-wrap" >
				<div class="ihc-coupon-box-wrap <?php echo (empty($item_data['status'])) ? 'ihc-disabled-box' : '';?> ihc-box-background-<?php echo substr($item_data['settings']['color'],1);?>" >
					<div class="ihc-coupon-box-main">
						<div class="ihc-coupon-box-title"><?php echo esc_html($item_data['slug']);?></div>
						<div class="ihc-coupon-box-content">
							<div class="ihc-coupon-box-levels"><?php
								esc_html_e("Target Memberships: ", "ihc");
								if (!empty($item_data['levels'])){
									foreach ($item_data['levels'] as $templid){
										if ($templid==-1){
											$temp_levels[] = esc_html__('All', 'ihc');
										} else {
											$temp_levels[] = Ihc_Db::get_level_name_by_lid($templid);
										}
									}
									echo esc_ump_content(implode(', ', $temp_levels));
								} else {
									echo esc_html('-');
								}
							?>
							</div>
							<div class="ihc-coupon-box-levels"><?php
								if (!empty($item_data['products'])){
									foreach ($item_data['products'] as $tempproduct){
										if ($item_data['settings']['woo_item_type']=='category'){
											$temp_products[] = Ihc_Db::get_category_name($tempproduct);
										} else {
											if ($tempproduct==-1){
												$all_products = TRUE;
												break;
											} else {
												$temp_products[] = get_the_title($tempproduct);
											}
										}
									}
									if (!empty($all_products)){
										esc_html_e('Target Products: All', 'ihc');
									} else {
										if ($item_data['settings']['woo_item_type']=='category'){
											esc_html_e('Target Category: ', 'ihc');
										} else {
											esc_html_e('Target Products: ', 'ihc');
										}
										if (!empty($temp_products)){
											echo implode(', ', $temp_products);
										}
									}
								}
								if (!empty($temp_levels)){
									unset($temp_levels);
								}
								if (!empty($temp_products)){
									unset($temp_products);
								}
								if (!empty($all_products)){
									unset($all_products);
								}
							?>
							</div>
						</div>
						<div class="ihc-coupon-box-links-wrap ihcwoo-no-top-margin">
							<div class="ihc-coupon-box-links">
								<a href="<?php echo esc_url($edit_url);?>" class="ihc-coupon-box-link"><?php esc_html_e('Edit', 'ihc');?></a>
								<div class="ihc-coupon-box-link" onClick="ihcDoDeleteWooIhcRelation(<?php echo esc_attr($id);?>, '<?php echo admin_url('admin.php?page=ihc_manage&tab=woo_product_custom_prices&subtab=manage');?>');"><?php esc_html_e('Delete', 'ihc');?></div>
							</div>
						</div>
					</div>
					<div class="ihc-coupon-box-bottom">
						<div class="ihc-coupon-box-bottom-disccount"><?php
								echo esc_html($item_data['discount_value']);
								if ($item_data['discount_type']=='%'){
									echo esc_html('%');
								} else {
									echo esc_html(' ') . esc_html($the_currency);
								}
							?></div>
						<div class="ihc-coupon-box-bottom-submitted"></div>

						<div class="ihc-coupon-box-bottom-date"><?php
							if (!empty($item_data['start_date']) && $item_data['start_date']!='0000-00-00 00:00:00'){
								echo esc_html__("From: ", "ihc") . '<span>'. esc_html( $item_data['start_date'] ) . "</span><br/> ";
							}
							if (!empty($item_data['end_date']) && $item_data['end_date']!='0000-00-00 00:00:00'){
								echo esc_html__("Until: ", "ihc") . ' <span>' . esc_html( $item_data['end_date'] ) . '</span>';
							}
						?>
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		<?php
	}
} else {
	?>
	<div class="ihc-warning-message"><?php esc_html_e(" No Rule available! Please create your first Rule.", "ihc");?></div>
	<?php
}
