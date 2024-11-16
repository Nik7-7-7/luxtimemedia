<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=gifts');?>"><?php esc_html_e('Settings', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=generated-gift-code');?>"><?php esc_html_e('Generated Membership Gift Codes', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$data['id'] = (isset($_GET['id'])) ? sanitize_text_field($_GET['id']) : 0;
$data['metas'] = Ihc_Db::gift_templates_get_metas($data['id']);
$levels = ihc_get_levels_with_payment();
?>

<div class="iump-page-title"><?php  esc_html_e("Gifts", 'ihc');?></div>
	<form method="post" action="<?php echo admin_url('admin.php?page=ihc_manage&tab=gifts');?>">
		<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

		<div class="ihc-stuffbox">
			<?php if (!empty( $_GET['id'] ) ){?>
			<h3><?php esc_html_e("Edit", 'ihc');?></h3>
			<?php } else { ?>
			<h3><?php esc_html_e("Add New", 'ihc');?></h3>
			<?php } ?>
			<input type="hidden" name="id" value="<?php echo esc_attr($data['id']);?>" />
			<div class="inside">

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e("Purchased Membership", 'ihc');?></label>
					<select name="lid"><?php
						if ($levels && count($levels)){
							$levels_arr[-1] = esc_html__("All", 'ihc');
							foreach ($levels as $k=>$v){
								$levels_arr[$k] = $v['name'];
							}
						}
						foreach ($levels_arr as $k=>$v){
							$selected = (isset($data['metas']['lid']) && $data['metas']['lid']==$k) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v);?></option>
						<?php
							}
						?>
					</select>
					<p><?php esc_html_e('(this is the membership which has the gift assigned to it)', 'ihc');?></p>
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e("Type of discount", 'ihc');?></label>
					<select name="discount_type" onChange="ihcDiscountType(this.value);"><?php
						$arr = array(
										'price' => esc_html__('Price', 'ihc'),
										'percentage' => esc_html__('Percentage (%)', 'ihc'),
						);
						foreach ($arr as $k=>$v){
							$selected = ($data['metas']['discount_type']==$k) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
							<?php
						}
					?></select>
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e("Discount Value", 'ihc');?></label>
					<input type="number" step="0.01" value="<?php echo esc_attr($data['metas']['discount_value']);?>" name="discount_value"/>
					<span id="discount_currency" class="<?php if ($data['metas']['discount_type']=='price'){
						 echo esc_attr('ihc-display-inline');
					}else{
						 echo esc_attr('ihc-display-none');
					}
					?>"><?php echo get_option('ihc_currency');?></span>
					<span id="discount_percentage" class="<?php if ($data['metas']['discount_type']=='percentage'){
						 echo esc_attr('ihc-display-inline');
					}else{
						 echo esc_attr('ihc-display-none');
					}
					?>">%</span>
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e("Gifted Membership", 'ihc');?></label>
					<select name="target_level"><?php
						if ($levels && count($levels)){
							$levels_arr[-1] = esc_html__("All", 'ihc');
							foreach ($levels as $k=>$v){
								$levels_arr[$k] = $v['name'];
							}
						}
						foreach ($levels_arr as $k=>$v){
							$selected = ($data['metas']['target_level']==$k) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
						<?php
							}
						?>
					</select>
					<p><?php esc_html_e('(the discount set above will apply to this membership)', 'ihc');?></p>
				</div>

				<div class="iump-form-line">
					<label class="iump-labels-special"><?php esc_html_e("On Subscriptions with Billing Recurrence apply the Discount:", 'ihc');?></label>
					<select name="reccuring"><?php
						$arr = array( 0 => esc_html__("Just Once", 'ihc'),
									  1 => esc_html__("Forever", 'ihc'),
						);
						foreach ($arr as $k=>$v){
							$selected = ($data['metas']['reccuring']==$k) ? 'selected' : '';
							?>
								<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
							<?php
						}
					?></select>
				</div>

				<div class="ihc-wrapp-submit-bttn">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save_gift" class="button button-primary button-large" />
				</div>

			</div>
		</div>
	</form>
