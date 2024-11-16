<?php $subtab = isset( $_GET['subtab'] ) ? sanitize_text_field($_GET['subtab']) : 'settings';?>
<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='settings' ) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=settings');?>"><?php esc_html_e('Checkout Showcase', 'ihc');?></a>
	<a class="ihc-subtab-menu-item <?php echo ( $subtab =='msg') ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url($url.'&tab='.$tab.'&subtab=msg');?>"><?php esc_html_e('Custom Messages', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

if (isset($_REQUEST['subtab'])){
   $subtab = sanitize_text_field($_REQUEST['subtab']);
}else{
	$subtab = 'settings';
}
if ($subtab=='settings'){
	if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_checkout_settings_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_checkout_settings_nonce']), 'ihc_admin_checkout_settings_nonce' ) ){
				ihc_save_update_metas('checkout-settings');
	}

	$meta_arr = ihc_return_meta_arr('checkout-settings');
	$meta_arr_payment = ihc_return_meta_arr('payment');

	$checkPage = ihcCheckCheckoutPage();

	if($meta_arr['ihc_checkout_inital'] == 0){
		$data = get_option('ihc_user_fields');

		if ($data){
			$payment_select_key = ihc_array_value_exists($data, 'payment_select', 'name');
			$dynamic_price_key = ihc_array_value_exists($data, 'ihc_dynamic_price', 'name');
			$coupon_key = ihc_array_value_exists($data, 'ihc_coupon', 'name');

			if (!empty($data[$payment_select_key]['display_public_reg'])){
					$meta_arr['ihc_checkout_payment_section'] = $data[$payment_select_key]['display_public_reg'];
		  }
			if (!empty($data[$payment_select_key]['theme'])){
				$meta_arr['ihc_checkout_payment_theme'] = $data[$payment_select_key]['theme'];
			}
			if(!empty($data[$dynamic_price_key]['display_public_reg'])){
				$meta_arr['ihc_checkout_dynamic_price'] = $data[$dynamic_price_key]['display_public_reg'];
			}
			if(!empty($data[$coupon_key]['display_public_reg'])){
				$meta_arr['ihc_checkout_coupon'] = $data[$coupon_key]['display_public_reg'];
			}
		}
	}

	wp_enqueue_script( 'wp-theme-plugin-editor' );
	wp_enqueue_style( 'wp-codemirror' );
	wp_enqueue_script( 'code-editor' );
	wp_enqueue_style( 'code-editor' );
?>
<div class="iump-page-headline"><?php esc_html_e('Checkout Page Showcase', 'ihc');?></div>
<div class="impu-shortcode-display-wrapper">
	<div class="impu-shortcode-display">
		[ihc-checkout-page]
	</div>
</div>
<form  method="post" >
  <input type="hidden" name="ihc_checkout_inital" value="1" />
	<input type="hidden" name="ihc_admin_checkout_settings_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_checkout_settings_nonce' );?>" />
  <div class="ihc-stuffbox">
    <h3><?php esc_html_e('Checkout Page Settings', 'ihc');?></h3>

		<div class="inside">

			<!--div class="iump-form-line iump-no-border">
        <h2><?php esc_html_e("Activate Checkout Page", 'ihc');?></h2>
				<p><?php esc_html_e("Checkout Page will be available only for certain payment services currently.", 'ihc');?></p>
				<div>
				 <label class="iump_label_shiwtch ihc-switch-button-margin">
					 <?php $checked = ($meta_arr['ihc_checkout_enable']) ? 'checked' : '';?>
					 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_enable');" <?php echo esc_attr($checked);?> />
					 <div class="switch ihc-display-inline"></div>
				 </label>
			 </div>
				 <input type="hidden" name="ihc_checkout_enable" value="<?php echo esc_attr($meta_arr['ihc_checkout_enable']);?>" id="ihc_checkout_enable" />
      </div -->
			<?php if($checkPage === FALSE){ ?>
					<div class="iump-form-line iump-no-border">
						<div class="ihc-warning-message">
							<strong><?php esc_html_e("Checkout Page", 'ihc');?> </strong>
							<?php esc_html_e("is missing or is not properly setup. Please create a Checkout Page and add the", 'ihc');?>
							<strong> [ihc-checkout-page] </strong>
							<?php esc_html_e("shortcode inside.", 'ihc');?>
						</div>
					</div>
			<?php } ?>

      <div class="iump-register-select-template">
      <div class="iump-form-line iump-no-border">
        <h2><?php esc_html_e("Checkout Page Template", 'ihc');?></h2>
      </div>
			 <div class="iump-form-line iump-no-border">
					<?php esc_html_e('Select Checkout Template', 'ihc');?> <select name="ihc_checkout_template" id="ihc_checkout_template">
						<?php
							$templates = array(
												'ihc_checkout_template_1'=>'(#1) '.esc_html__('Main Template', 'ihc'),
												);
							foreach($templates as $k=>$v){
								?>
									<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_checkout_template']){
										 echo esc_attr('selected');
									}
									?>
									><?php echo esc_html($v);?></option>
								<?php
							}
						?>
					</select>
				</div>
				 <!--div class="iump-form-line iump-no-border">
					<div>
						<input type="radio" name="ihc_checkout_column_structure" class="ihc-js-image-type-selector" value="1" <?php echo ($meta_arr['ihc_checkout_column_structure'] === '1') ? 'checked': ''; ?> >
						<label><?php esc_html_e('1 Column', 'ihc');?></label>
					</div>
					<div>
						<input type="radio" name="ihc_checkout_column_structure" class="ihc-js-image-type-selector" value="2" <?php echo ($meta_arr['ihc_checkout_column_structure'] === '2') ? 'checked': ''; ?> >
						<label><?php esc_html_e('2 Columns', 'ihc');?></label>
					</div>
				</div-->
    </div>

    <div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Checkout Page Additional Settings", 'ihc');?></h2>
    </div>
    <!--div class="iump-form-line iump-no-border">
      <h4><?php esc_html_e("Customer Information", 'ihc');?></h4>
       <p>- Choose and add fields from Custom Fields section</p>
       <p>- Existent added fields for Checkout page</p>
			 <div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_customer_information']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_customer_information');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_customer_information" value="<?php echo esc_attr($meta_arr['ihc_checkout_customer_information']);?>" id="ihc_checkout_customer_information" />
		 </div>
			 <p><?php echo esc_html__('To add or edit custom fields visit ', 'ihc'); ?><a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=register&subtab=custom_fields');?>" target="_blank"><?php esc_html_e( 'Registration Form Fields', 'ihc' );?></a></p>

		</div-->

    <div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Payment Method", 'ihc');?></h2>
       <p><?php esc_html_e("If Multiple Payment services are available, member may choose which one wish to use for current purchase.", 'ihc');?></p>
			 <div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_payment_section']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_payment_section');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_payment_section" value="<?php echo esc_attr($meta_arr['ihc_checkout_payment_section']);?>" id="ihc_checkout_payment_section" />
		 	</div>
    </div>

				<div class="iump-form-line iump-no-border">
					<h4><?php esc_html_e("Template", 'ihc');?></h4>
					<p><?php esc_html_e("Payment selection showcase", 'ihc');?></p>
					<select name="ihc_checkout_payment_theme"><?php
						if (empty($meta_arr['ihc_checkout_payment_theme'])) $meta_arr['ihc_checkout_payment_theme'] = 'ihc-select-payment-theme-2';
						foreach (array('ihc-select-payment-theme-1' => 'RadioBox', 'ihc-select-payment-theme-2' => 'Logos', 'ihc-select-payment-theme-3' => 'DropDown') as $k=>$v){
							?>
							<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_checkout_payment_theme']){ echo esc_attr('selected');}?> ><?php echo esc_attr($v);?></option>
							<?php
						}
					?></select>
				</div>

				<div class="iump-form-line iump-no-border">
					<select class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select " name="ihc_payment_selected">
						<?php

							$payment_arr = ihc_list_all_payments();
							if ( !isset( $meta_arr['ihc_payment_selected'] ) ){
									$meta_arr['ihc_payment_selected'] = '';
							}
							foreach($payment_arr as $k=>$v){

								$active = (ihc_check_payment_available($k)) ? esc_html__('Active', 'ihc') : esc_html__('Inactive', 'ihc');
								?>
								<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_payment_selected']){ echo esc_attr('selected');}?> >
									<?php echo esc_html($v) . ' - ' . esc_html($active);?>
								</option>
								<?php
							}
						?>
					</select>

							<p><?php echo esc_html__('Setup additional settings and conditions on: ', 'ihc'); ?><a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=level_restrict_payment');?>" target="_blank"><?php esc_html_e( 'Memberships vs Payments', 'ihc' );?></a></p>
				</div>


		<div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Membership Price details", 'ihc');?></h2>
			<p><?php esc_html_e("Full description about current package price (trial, discounts, etc).", 'ihc');?></p>
			<div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_membership_price_details']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_membership_price_details');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_membership_price_details" value="<?php echo esc_attr($meta_arr['ihc_checkout_membership_price_details']);?>" id="ihc_checkout_membership_price_details" />
		 </div>
    </div>

    <div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Dynamic Price box", 'ihc');?></h2>
       <p><?php esc_html_e("Allow members to choose how much to pay for selected Membership.", 'ihc');?></p>
			 <div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_dynamic_price']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_dynamic_price');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_dynamic_price" value="<?php echo esc_attr($meta_arr['ihc_checkout_dynamic_price']);?>" id="ihc_checkout_dynamic_price" />
		 	</div>
			 <p><?php echo esc_html__('Check the Extension module for this feature: ', 'ihc'); ?><a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=level_dynamic_price');?>" target="_blank"><?php esc_html_e( 'Membership Dynamic Price', 'ihc' );?></a></p>

		</div>

    <div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Discount Coupon box", 'ihc');?></h2>
			 <p><?php esc_html_e("If available discount codes exist, Members may use one to get a discounted price.", 'ihc');?></p>
       			 <div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_coupon']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_coupon');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_coupon" value="<?php echo esc_attr($meta_arr['ihc_checkout_coupon']);?>" id="ihc_checkout_coupon" />
		 </div>
			 <p><?php echo esc_html__('Manage your discount coupons ', 'ihc'); ?> <a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=coupons');?>" target="_blank"><?php esc_html_e( 'here', 'ihc' );?></a></p>

		</div>

    <div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Taxes display section", 'ihc');?></h2>
			 <p><?php esc_html_e("Add additional tax charges which can be based on the member location by using the Country field.", 'ihc');?></p>
			<div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_taxes_display_section']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_taxes_display_section');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_taxes_display_section" value="<?php echo esc_attr($meta_arr['ihc_checkout_taxes_display_section']);?>" id="ihc_checkout_taxes_display_section" />
		 </div>
			 <p><?php echo esc_html__('Check the Extension module for this feature: ', 'ihc'); ?> <a href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=taxes');?>" target="_blank"><?php esc_html_e( 'Taxes', 'ihc' );?></a></p>
    </div>

    <div class="iump-form-line iump-no-border">
      <h2><?php esc_html_e("Privacy Policy message", 'ihc');?></h2>
       <p><?php esc_html_e("The privacy notices below will not show up", 'ihc');?></p>
			 <div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_privacy_policy_option']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_privacy_policy_option');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_privacy_policy_option" value="<?php echo esc_attr($meta_arr['ihc_checkout_privacy_policy_option']);?>" id="ihc_checkout_privacy_policy_option" />
		 </div>

		 <div class="row">
		 		<div class="col-xs-10">
					 		<div class="iump-wp_editor">
					 		<?php wp_editor(stripslashes($meta_arr['ihc_checkout_privacy_policy_message']), 'ihc_checkout_privacy_policy_message', array('textarea_name'=>'ihc_checkout_privacy_policy_message', 'editor_height'=>200));?>
					 		</div>
				</div>
		 </div>
	</div>


			<div class="iump-form-line iump-no-border">
				<h2><?php esc_html_e("Behavior Settings", 'ihc');?></h2>
			</div>

			<div class="iump-form-line iump-no-border">
				<h2><?php esc_html_e("Avoid Checkout section for free Memberships", 'ihc');?></h2>
				<p><?php esc_html_e("During Register step avoid Checkout section for selected or predefined Memberships without a charge request", 'ihc');?></p>
			 <div>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($meta_arr['ihc_checkout_avoid_free_membership']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_avoid_free_membership');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_checkout_avoid_free_membership" value="<?php echo esc_attr($meta_arr['ihc_checkout_avoid_free_membership']);?>" id="ihc_checkout_avoid_free_membership" />
			</div>
		 </div>
		 <div class="iump-form-line iump-no-border">
			 <h2><?php esc_html_e("Payment Method position", 'ihc');?></h2>
			 <p><?php esc_html_e("Move Payment Method section after Coupon/Dynamic price fields and before SubTotal section", 'ihc');?></p>
			<div>
			 <label class="iump_label_shiwtch ihc-switch-button-margin">
				 <?php $checked = ($meta_arr['ihc_checkout_payment_method_position']) ? 'checked' : '';?>
				 <input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_checkout_payment_method_position');" <?php echo esc_attr($checked);?> />
				 <div class="switch ihc-display-inline"></div>
			 </label>
			 <input type="hidden" name="ihc_checkout_payment_method_position" value="<?php echo esc_attr($meta_arr['ihc_checkout_payment_method_position']);?>" id="ihc_checkout_payment_method_position" />
		 </div>
		</div>


      <div class="ihc-wrapp-submit-bttn">
        <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" id="ihc_submit_bttn" class="button button-primary button-large" />
      </div>
    </div>

  </div>

	<div class="ihc-stuffbox iump-custom-css-box-wrapper">
		<h3><?php esc_html_e('Custom CSS', 'ihc');?></h3>
		<div class="inside">
			<div class="iump-form-line">
				<textarea name="ihc_checkout_custom_css" id="ihc_checkout_custom_css" class="ihc-dashboard-textarea-full"><?php
				echo stripslashes($meta_arr['ihc_checkout_custom_css']);
				?></textarea>
			</div>
			<div class="ihc-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" id="ihc_submit_bttn" name="ihc_save" class="button button-primary button-large" />
			</div>
		</div>

	</div>
</form>
<?php }else{

	if ( isset($_POST['ihc_save'] ) && !empty($_POST['ihc_admin_checkout_settings_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_checkout_settings_nonce']), 'ihc_admin_checkout_settings_nonce' ) ){
				ihc_save_update_metas('checkout-messages');
	}


	$meta_arr = ihc_return_meta_arr('checkout-messages');

	?>
  <form  method="post" >
    <input type="hidden" name="ihc_admin_checkout_settings_nonce" value="<?php echo wp_create_nonce( 'ihc_admin_checkout_settings_nonce' );?>" />
    <div class="ihc-stuffbox">
      <h3><?php esc_html_e('Checkout Page Messages', 'ihc');?></h3>
      <div class="inside">
        <div class="iump-form-line iump-no-border">
          <h2><?php esc_html_e("Customize predefined Strings and Messages", 'ihc');?></h2>
				</div>
					<!--div class="iump-form-line">
						<h4><?php esc_html_e('Customer Information Section', 'ihc');?></h4>
						<div class="row">
									<div class="col-xs-4">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Title', 'ihc');?></span>
																<input name="ihc_checkout_customer_title" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_customer_title']);?>"/>
														</div>
										</div>
						</div>
					</div-->
					<div class="iump-form-line">
						<h4><?php esc_html_e('Payment Method Section', 'ihc');?></h4>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Title', 'ihc');?></span>
																<input name="ihc_checkout_payment_title" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_payment_title']);?>"/>
														</div>
										</div>
						</div>
					</div>
					<div class="iump-form-line">
						<h4><?php esc_html_e('Dynamic Price Section', 'ihc');?></h4>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Message', 'ihc');?></span>
																<input name="ihc_checkout_dynamic_field_message" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_dynamic_field_message']);?>"/>
														</div>
										</div>
						</div>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Apply Button', 'ihc');?></span>
																<input name="ihc_checkout_dynamic_field_button" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_dynamic_field_button']);?>"/>
														</div>
										</div>
						</div>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Chosen Price', 'ihc');?></span>
																<input name="ihc_checkout_dynamic_price-set" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_dynamic_price-set']);?>"/>
														</div>
										</div>
						</div>
					</div>
					<div class="iump-form-line">
						<h4><?php esc_html_e('Taxes Section', 'ihc');?></h4>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Title', 'ihc');?></span>
																<input name="ihc_checkout_taxes_title" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_taxes_title']);?>"/>
														</div>
										</div>
						</div>
					</div>
					<div class="iump-form-line">
						<h4><?php esc_html_e('Coupon Section', 'ihc');?></h4>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Coupon Message', 'ihc');?></span>
																<input name="ihc_checkout_coupon_field_message" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_coupon_field_message']);?>"/>
														</div>
										</div>
						</div>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Coupon Button', 'ihc');?></span>
																<input name="ihc_checkout_apply_button" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_apply_button']);?>"/>
														</div>
										</div>
						</div>
						<div class="row">
									<div class="col-xs-6">
														 <div class="input-group">
																<span class="input-group-addon"><?php esc_html_e('Coupon', 'ihc');?></span>
																<input name="ihc_checkout_coupon_field_used" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_coupon_field_used']);?>"/>
														</div>
										</div>
						</div>
					</div>
					<div class="iump-form-line">
							<h4><?php esc_html_e('Membership Price details:', 'ihc');?></h4>
							<div class="row">
                	<div class="col-xs-6">
                             <div class="input-group">
                                <span class="input-group-addon"><?php esc_html_e('Initial Payment', 'ihc');?></span>
                                <input name="ihc_checkout_price_initial" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_price_initial']);?>"/>
                            </div>
                    </div>
                </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('Then', 'ihc');?></span>
		                                <input name="ihc_checkout_price_then" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_price_then']);?>"/>
		                            </div>
		                    </div>
		            </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('Fee', 'ihc');?></span>
		                                <input name="ihc_checkout_price_fee" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_price_fee']);?>"/>
		                            </div>
		                    </div>
		            </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('Free', 'ihc');?></span>
		                                <input name="ihc_checkout_price_free" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_price_free']);?>"/>
		                            </div>
		                    </div>
		            </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('Discount', 'ihc');?></span>
		                                <input name="ihc_checkout_price_discount" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_price_discount']);?>"/>
		                            </div>
		                    </div>
		            </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('for', 'ihc');?></span>
		                                <input name="ihc_checkout_price_for" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_price_for']);?>"/>
		                            </div>
		                    </div>
		            </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('every', 'ihc');?></span>
		                                <input name="ihc_checkout_price_every" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_price_every']);?>"/>
		                            </div>
		                    </div>
		            </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('Subtotal', 'ihc');?></span>
		                                <input name="ihc_checkout_subtotal_title" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_subtotal_title']);?>"/>
		                            </div>
		                    </div>
		            </div>
								<div class="row">
		                	<div class="col-xs-6">
		                             <div class="input-group">
		                                <span class="input-group-addon"><?php esc_html_e('Remove', 'ihc');?></span>
		                                <input name="ihc_checkout_remove" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_remove']);?>"/>
		                            </div>
		                    </div>
		            </div>
					</div>
					<div class="iump-form-line">
							<h4><?php esc_html_e('Complete Purchase Button', 'ihc');?></h4>
							<div class="row">
										<div class="col-xs-6">
															 <div class="input-group">
																	<span class="input-group-addon"><?php esc_html_e('Complete Purchase', 'ihc');?></span>
																	<input name="ihc_checkout_purchase_button" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_purchase_button']);?>"/>
															</div>
											</div>
							</div>
							<div class="row">
										<div class="col-xs-6">
															 <div class="input-group">
																	<span class="input-group-addon"><?php esc_html_e('Free Access', 'ihc');?></span>
																	<input name="ihc_checkout_free_button" class="form-control" type="text" value="<?php echo ihc_correct_text($meta_arr['ihc_checkout_free_button']);?>"/>
															</div>
											</div>
							</div>
					</div>
				<div class="ihc-wrapp-submit-bttn">
	        <input type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" id="ihc_submit_bttn" class="button button-primary button-large" />
	      </div>
      </div>
    </div>
  </form>
<?php } ?>
