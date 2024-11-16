<?php
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('invoices');//save update metas
}

$data['metas'] = ihc_return_meta_arr('invoices');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$invoiceCss = get_option( 'ihc_invoices_custom_css' );
if ( $invoiceCss !== false && $invoiceCss != '' ){
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes( $invoiceCss ) );
}

wp_enqueue_script( 'wp-theme-plugin-editor' );
wp_enqueue_style( 'wp-codemirror' );
wp_enqueue_script( 'code-editor' );
wp_enqueue_style( 'code-editor' );
wp_enqueue_script( 'ihc-print-this', IHC_URL . 'assets/js/printThis.js', ['jquery'], '12.7' );

?>
<form method="post" id="invoice_form">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Order Invoices', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Order Invoices', 'ihc');?></h2>
				<p><?php esc_html_e('Provides printable invoices for each order in the account page or system dashboard.', 'ihc'); ?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_invoices_on']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_invoices_on');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_invoices_on" value="<?php echo esc_attr($data['metas']['ihc_invoices_on']);?>" id="ihc_invoices_on" />
			</div>


			<div class="iump-form-line">
				<h4><?php esc_html_e('Show only for \'Completed\' Payment', 'ihc'); ?></h4>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_invoices_only_completed_payments']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_invoices_only_completed_payments');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_invoices_only_completed_payments" value="<?php echo esc_attr($data['metas']['ihc_invoices_only_completed_payments']);?>" id="ihc_invoices_only_completed_payments" />
			</div>

			<div class="iump-register-select-template" >
				<div class="iump-form-line">
					<h4><?php esc_html_e('Invoice Template', 'ihc');?></h4>
					<select name="ihc_invoices_template" onChange="iumpAdminPreviewInvoice();"><?php
						foreach (array('iump-invoice-template-1'=>esc_html__('Template 1', 'ihc'), 'iump-invoice-template-2'=>esc_html__('Template 2', 'ihc')) as $k=>$v){
							$selected = ($data['metas']['ihc_invoices_template']==$k) ? 'selected' : '';
							?>
							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v);?></option>
							<?php
						}
					?></select>
				</div>

				<div class="iump-form-line">
					<h4><?php esc_html_e('Invoice Logo', 'ihc');?></h4>
					<input type="text" onblur="iumpAdminPreviewInvoice();" width="400px"  name="ihc_invoices_logo" value="<?php echo esc_attr($data['metas']['ihc_invoices_logo']);?>" onClick="openMediaUp(this);" />	<i class="fa-ihc ihc-icon-remove-e iump-pointer ihc-js-admin-invoices-delete" ></i>
				</div>

				<div class="iump-form-line">
					<h4><?php esc_html_e('Invoice main Title', 'ihc');?></h4>
					<input type="text" onblur="iumpAdminPreviewInvoice();" name="ihc_invoices_title" value="<?php echo esc_attr($data['metas']['ihc_invoices_title']);?>"/>
				</div>
			</div>

			<div class="iump-form-line">
			<h2><?php esc_html_e('Additional Invoice Details', 'ihc');?></h2>
			</div>
			<div class="iump-form-line">
			<div class="row">
				<div class="col-xs-5">
					<h4><?php esc_html_e('Company Field', 'ihc');?></h4>
					<div class="ihc-invoice-settings-company-col">
						<?php wp_editor( stripslashes($data['metas']['ihc_invoices_company_field']), 'ihc_invoices_company_field', array('textarea_name'=>'ihc_invoices_company_field', 'quicktags'=>TRUE) );?>
					</div>
				</div>
				<div class="col-xs-7">
					<h4><?php esc_html_e('Bill to', 'ihc');?></h4>
					<div class="ihc-invoice-settings-bill-col">
						<?php wp_editor( stripslashes($data['metas']['ihc_invoices_bill_to']), 'ihc_invoices_bill_to', array('textarea_name'=>'ihc_invoices_bill_to', 'quicktags'=>TRUE) );?>
					</div>
					<div class="ihc-invoice-settings-constantsone-col" >
                                            <h4><?php echo esc_html__('Standard Fields constants', 'ihc');?></h4>
							<?php
								$constants = array(
													'{username}'=>'',
													'{user_email}'=>'',
													'{user_id}'		=> '',
													'{first_name}'=>'',
													'{last_name}'=>'',
													'{account_page}'=>'',
													'{login_page}'=>'',
													'{level_list}'=>'',
													'{blogname}'=>'',
													'{blogurl}'=>'',
													'{currency}'=>'',
													'{amount}'=>'',
													'{level_name}'=>'',
													'{current_date}' => '',
								);
								$extra_constants = ihc_get_custom_constant_fields();
								foreach ($constants as $k=>$v){
									?>
									<div><?php echo esc_html($k);?></div>
									<?php
								}
								?>
						</div>
						<div class="ihc-invoice-settings-constantstwo-col" >
                                                    <h4><?php echo esc_html__('Custom Fields constants', 'ihc');?></h4>
								<?php
								foreach ($extra_constants as $k=>$v){
									?>
									<div><?php echo esc_html($k);?></div>
									<?php
								}
							?>
					</div>
				</div>
				<div class="ihc-clear"></div>
			</div>
		</div>
			<div class="iump-form-line">
				<h2><?php esc_html_e('Footer Invoice Info', 'ihc');?></h2>
			</div>
			<div class="ihc-invoice-settings-footer-col">
				<?php wp_editor( stripslashes($data['metas']['ihc_invoices_footer']), 'ihc_invoices_footer', array('textarea_name'=>'ihc_invoices_footer', 'quicktags'=>TRUE) );?>
			</div>

			<div class="iump-form-line iump-custom-css-box-wrapper">
				<h2><?php esc_html_e('Custom CSS', 'ihc');?></h2>
				<textarea name="ihc_invoices_custom_css" id="ihc_invoices_custom_css" onblur="iumpAdminPreviewInvoice();" class="ihc-custom-css-box"><?php echo stripslashes($data['metas']['ihc_invoices_custom_css']);?></textarea>
			</div>


			<div class="ihc-wrapp-submit-bttn iump-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large">
			</div>

		</div>
	</div>
</form>

<div class="ihc-stuffbox">
	<h3 class="ihc-h3"><?php esc_html_e('Preview', 'ihc');?></h3>
	<div class="inside" id="preview_container">
	</div>
</div>
