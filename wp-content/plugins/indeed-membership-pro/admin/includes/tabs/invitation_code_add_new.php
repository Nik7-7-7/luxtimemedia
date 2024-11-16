<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=invitation_code-add_new');?>"><?php esc_html_e('Add Single Invitation Code', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=invitation_code-add_new&multiple=true');?>"><?php esc_html_e('Add Bulk Invitation Codes', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=invitation_code');?>"><?php esc_html_e('Manage Invitation Codes', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-wrapper">
		<div class="iump-page-title">Ultimate Membership Pro -
				<span class="second-text">
					<?php  esc_html_e("Invitation Codes", 'ihc');?>
				</span>
		</div>
			<form method="post" action="<?php echo admin_url('admin.php?page=ihc_manage&tab=invitation_code');?>">
				<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e("Add New", 'ihc');?></h3>
					<div class="inside">
						<?php
							if (!empty($_GET['multiple'])){
								//////////////// MULTIPLE COUPONS ////////////
								?>
								<div class="iump-form-line">
									<label class="iump-labels-special"><?php esc_html_e("Code prefix", 'ihc');?></label>
									<input type="text" value="" name="code_prefix" />
								</div>
								<div class="iump-form-line">
									<label class="iump-labels-special"><?php esc_html_e("Length", 'ihc');?></label>
									<input type="number" min="2" value="10" name="code_length" />
								</div>
								<div class="iump-form-line">
									<label class="iump-labels-special"><?php esc_html_e("Number of Codes", 'ihc');?></label>
									<input type="number" min="2" value="2" max="100" name="how_many_codes" />
								</div>
								<?php
							} else {
								/////////////// ONE /////////////
								?>
								<div class="iump-form-line">
									<label class="iump-labels-special"><?php esc_html_e("Code", 'ihc');?></label>
									<input type="text" value="" name="code" id="ihc_the_coupon_code" /> <span class="ihc-generate-gift-code-button" onClick="ihcGenerateCode('#ihc_the_coupon_code', 10);"><?php esc_html_e("Generate Code", "ihc");?></span>
								</div>
								<?php
							}
						?>

						<div class="iump-form-line">
							<label class="iump-labels-special"><?php esc_html_e("Repeat", 'ihc');?></label>
							<input type="number"  min="1" value="1" name="repeat"/>
						</div>

						<div class="ihc-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="add_new" class="button button-primary button-large" />
						</div>

					</div>
				</div>
			</form>
</div>
