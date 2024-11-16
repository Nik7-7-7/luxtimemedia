<div class="ihc-subtab-menu">
	<?php $items = ihc_list_all_payments();?>
	<?php foreach ( $items as $slug => $label ):
		//DEPRECATED starting with v.10.1
		if($slug == 'stripe'){
			continue;
		}
		//Temporary
		if($slug == 'stripe_connect'){
			//continue;
		}
		?>
			<a class="ihc-subtab-menu-item <?php echo (isset( $_GET['subtab'] ) && $_GET['subtab'] ==$slug ) ? 'ihc-subtab-selected' : '';?>" href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=' . $slug );?>"><?php echo esc_html($label);?></a>
	<?php endforeach;?>

	<a class="ihc-subtab-menu-item" href="<?php echo esc_url($url . '&tab=general&subtab=pay_settings' );?>"><?php esc_html_e('Payment Settings', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();

if (empty($_GET['subtab'])){
	//listing payment methods
	$pages = ihc_get_all_pages();//getting pages
	echo ihc_check_default_pages_set();//set default pages message
	echo ihc_check_payment_gateways();
	echo ihc_is_curl_enable();
	do_action( "ihc_admin_dashboard_after_top_menu" );
	?>
	<!--div class="iump-page-title">Ultimate Membership Pro -
		<span class="second-text">
			<?php esc_html_e('Payment Services', 'ihc');?>
		</span>
	</div-->
	<div class="iump-page-headline">
			<?php esc_html_e('Payment Gateways', 'ihc');?>
	</div>
	<div class="iump-payment-list-wrapper">
		<div class="iump-payment-box-wrap">
			 <?php $pay_stat = ihc_check_payment_status('stripe_connect'); ?>
			 <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=stripe_connect' );?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="ihc-adm-ribbon ihc-adm-ribbon-top-left"><span>PRO</span></div>
				<div class="iump-payment-box-title">Stripe Connect</div>
								<div class="iump-payment-box-type">The most Complete and fastest Payment Service with OnSite 3D Secure (3DS) Payment Form and Cards Management.</div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
			</div>
			 </a>
		</div>
		<div class="iump-payment-box-wrap">
	 <?php $pay_stat = ihc_check_payment_status('paypal_express_checkout'); ?>
	 <a href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=paypal_express_checkout' );?>">
	<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
		<div class="iump-payment-box-title">PayPal Express</div>
						<div class="iump-payment-box-type">PayPal Express Checkout - OffSite payment solution</div>
		<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
	</div>
	 </a>
</div>
		<div class="iump-payment-box-wrap">
		<?php $pay_stat = ihc_check_payment_status('paypal'); ?>
		  <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=paypal' );?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="iump-payment-box-title">PayPal Standard</div>
                <div class="iump-payment-box-type">OffSite payment solution</div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
			</div>
		 </a>
		</div>

		<div class="iump-payment-box-wrap">
			 <?php $pay_stat = ihc_check_payment_status( 'stripe_checkout_v2' ); ?>
			 <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=stripe_checkout_v2');?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="iump-payment-box-title">Stripe Checkout</div>
								<div class="iump-payment-box-type"><?php esc_html_e( 'OffSite payment solution (3d secure ready)', 'ihc' );?></div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_attr($pay_stat['settings']); ?></span></div>
			</div>
			 </a>
		</div>


		<div class="iump-payment-box-wrap">
		   <?php $pay_stat = ihc_check_payment_status('twocheckout'); ?>
		   <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=twocheckout' );?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="iump-payment-box-title">2Checkout</div>
                <div class="iump-payment-box-type">OffSite payment solution</div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
			</div>
		   </a>
		</div>
		<div class="iump-payment-box-wrap">
		   <?php $pay_stat = ihc_check_payment_status('mollie'); ?>
		   <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=mollie' );?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="iump-payment-box-title">Mollie</div>
                <div class="iump-payment-box-type">OffSite payment solution</div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
			</div>
		   </a>
		</div>
		<div class="iump-payment-box-wrap">
		   <?php $pay_stat = ihc_check_payment_status('bank_transfer'); ?>
		   <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=bank_transfer');?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="iump-payment-box-title">Bank Transfer</div>
                <div class="iump-payment-box-type">OnSite payment solution</div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
			</div>
		   </a>
		</div>
        <div class="iump-payment-box-wrap">
		    <?php $pay_stat = ihc_check_payment_status('pagseguro'); ?>
		    <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=pagseguro' );?>">
					<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
						<div class="iump-payment-box-title">Pagseguro</div>
			          <div class="iump-payment-box-type">OffSite payment solution</div>
						<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
					</div>
				</a>
		</div>
		<div class="iump-payment-box-wrap">
		   <?php $pay_stat = ihc_check_payment_status('braintree'); ?>
		   <a href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=braintree' );?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="iump-payment-box-title">Braintree</div>
                <div class="iump-payment-box-type">OnSite payment solution</div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
			</div>
		   </a>
		</div>

		<div class="iump-payment-box-wrap">
		  <?php $pay_stat = ihc_check_payment_status('authorize'); ?>
		  <a href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=authorize' );?>">
			<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
				<div class="iump-payment-box-title">Authorize.net</div>
                <div class="iump-payment-box-type">OffSite for OneTime payment & OnSite for Recurring Payment</div>
				<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
			</div>
		 </a>
		</div>
		<?php
		//DEPRECATED starting with v.10.1
		$checkModule = get_option('ihc_stripe_status');

			if(isset($checkModule) && $checkModule == 1){ ?>
					<div class="iump-payment-box-wrap">
					   <?php $pay_stat = ihc_check_payment_status('stripe'); ?>
					   <a href="<?php echo esc_url( $url . '&tab=' . $tab . '&subtab=stripe' );?>">
						<div class="iump-payment-box <?php echo esc_attr($pay_stat['active']); ?>">
							<div class="iump-payment-box-title">Stripe Standard</div>
			                <div class="iump-payment-box-type">OnSite payment solution</div>
							<div class="iump-payment-box-bottom">Settings: <span><?php echo esc_html($pay_stat['settings']); ?></span></div>
						</div>
					   </a>
					</div>
	<?php } ?>



		<?php
				do_action( 'ihc_payment_gateway_box' );
				// @description
		?>
				<div class="iump-payment-box-wrap">
					<a href="https://ultimatemembershippro.com/additional-payment-gateways/" target="_blank">
						<div class="iump-payment-box ihc-new-payment-box">
							<i class="fa-ihc fa-new-extension-ihc"></i>
							<div class="ihc-new-payment-box-title">
								<?php esc_html_e( 'Add new Payment Gateway', 'ihc' ); ?>
							</div>
						</div>
					</a>
				</div>

		<div class="ihc-clear"></div>
	</div>
	<?php
} else {
	switch ($_GET['subtab']){
		case 'paypal':
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field( $_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
					//ihc_save_update_metas('payment_paypal');//save update metas
					ihc_save_update_trimmed_metas('payment_paypal'); // save update metas without extra spaces
			}
			$meta_arr = ihc_return_meta_arr('payment_paypal');//getting metas
			$pages = ihc_get_all_pages();//getting pages
			echo ihc_check_default_pages_set();//set default pages message
			echo ihc_check_payment_gateways();
			echo ihc_is_curl_enable();
			do_action( "ihc_admin_dashboard_after_top_menu" );

			$siteUrl = site_url();
			$siteUrl = trailingslashit($siteUrl);
			?>
			<!--div class="iump-page-title">Ultimate Membership Pro -
				<span class="second-text">
					<?php esc_html_e('PayPal Standard Services', 'ihc');?>
				</span>
			</div--->
			<div class="iump-page-headline">
					<?php esc_html_e('PayPal Standard Gateway', 'ihc');?>
			</div>
			<form  method="post" class="iump-pqyments-settings-form">
					<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('PayPal Standard Setup', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
									<div class="iump-form-line">
										<h2><?php esc_html_e('Activate PayPal Standard Payment Service', 'ihc');?> </h2>

		                                <label class="iump_label_shiwtch ihc-switch-button-margin">
										<?php $checked = ($meta_arr['ihc_paypal_status']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_paypal_status');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_paypal_status']);?>" name="ihc_paypal_status" id="ihc_paypal_status" />
									<p><?php esc_html_e('Once everything is properly set up, activate the Payment Service for further use.', 'ihc');?> </p>
																	<p><?php esc_html_e("PayPal Standard redirects customers to PayPal to enter their payment information", 'ihc');?></p>
									</div>
							</div>
						</div>
							<div class="ihc-wrapp-submit-bttn iump-submit-form">
								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
							</div>
						</div>
					</div>
					<div class="ihc-stuffbox">

						<h3><?php esc_html_e('PayPal Standard Settings', 'ihc');?></h3>

						<div class="inside">
               <div class="row ihc-row-no-margin">
                  			<div class="col-xs-10">

							<div class="iump-form-line input-group">
							<label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
								<input type="radio" name="ihc_paypal_sandbox" class="" value="1" <?php if ( (int)$meta_arr['ihc_paypal_sandbox'] === 1 ) echo esc_attr('checked');?> />
								<span class="iump-checkmark-rd"></span>
							</label>
							<label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
								<input type="radio" name="ihc_paypal_sandbox" class="" value="0" <?php if ( (int)$meta_arr['ihc_paypal_sandbox'] === 0 ) echo esc_attr('checked');?> />
								<span class="iump-checkmark-rd"></span>
							</label>
							<p><?php esc_html_e("PayPal sandbox mode can be used to testing purpose. A Sandbox merchant account and additional Sandbox buyer account is required. Sign up as a ", 'ihc');?><a target="_blank" href="https://developer.paypal.com/"><?php esc_html_e("developer account", 'ihc');?></a></p>

							<!--	<input type="checkbox" onClick="checkAndH(this, '#enable_sandbox');" <?php if($meta_arr['ihc_paypal_sandbox']){ echo esc_attr('checked');}?> />
								<input type="hidden" name="ihc_paypal_sandbox" value="<?php echo esc_attr($meta_arr['ihc_paypal_sandbox']);?>" id="enable_sandbox" />
								<label class="iump-labels"><h4><?php esc_html_e(' Enable PayPal Sandbox', 'ihc');?></h4></label>-->
							</div>
							<div class="iump-form-line input-group iump-no-border">
								<span class="input-group-addon" ><?php esc_html_e('PayPal Merchant Email', 'ihc');?></span>
                                <input type="text" value="<?php echo esc_attr($meta_arr['ihc_paypal_email']);?>" name="ihc_paypal_email" class="form-control"/>
							</div>
                            <!--div class="iump-form-line">
                            <p><?php esc_html_e("Please enter your PayPal Email address. This is required in order to take payments via PayPal.", 'ihc');?></p>
													</div-->

							<div class="iump-form-line input-group iump-no-border">
								<span class="input-group-addon" ><?php esc_html_e('Merchant account ID', 'ihc');?></span>
                                <input type="text" value="<?php echo esc_attr($meta_arr['ihc_paypal_merchant_account_id']);?>" name="ihc_paypal_merchant_account_id"  class="form-control" />
							</div>

                <div class="iump-form-line input-group">
                  <p><strong>Merchant account ID</strong> <?php esc_html_e(" is necessary for Subscriptions management especially.", 'ihc');?></p>

							</div>
							<div class="iump-form-line iump-no-border">
							<h4><?php esc_html_e("How to Setup A Live Account", 'ihc');?></h4>
							<ul class="ihc-payment-capabilities-list">
								<li><?php esc_html_e("Login with your credentials and go to 'Account Settings' (top-right of page)", 'ihc');?></li>
								<li><?php esc_html_e("After that go to 'Notifications' and next Update the 'Instant payment notifications' ", 'ihc');?></li>
								<li><?php esc_html_e('Setup your IPN in order to receive Payment confirmations as: ', 'ihc');?><a target="_blank" href="<?php echo esc_url( $siteUrl . '?ihc_action=paypal' );?>"><?php echo esc_url($siteUrl . '?ihc_action=paypal');?></a></li>
									<li><?php esc_html_e("You can find 'Merchant account ID' ", 'ihc');?><?php esc_html_e("click on Account Settings -> Business information -> PayPal Merchant ID.", 'ihc');?></li>
							</ul>
						</div>
						<div class="iump-form-line iump-no-border">

														<h4><?php esc_html_e("How to Setup A Sandobx Account", 'ihc');?></h4>
														<ul class="ihc-payment-capabilities-list">
														  <li><?php esc_html_e("Login in ", 'ihc'); ?> <a target="_blank" href="https://developer.paypal.com/"><?php esc_html_e("developer account", 'ihc');?> </a> <?php esc_html_e("and go to Dashboard -> My Apps & Credentials and create an app.", 'ihc');?></li>
															<li><?php esc_html_e("In Sandbox -> Accounts, a 'Buyer' and a 'Merchant' account have been created.", 'ihc'); ?></li>
															<li><?php esc_html_e("You can find 'Merchant account ID' by loggin in to", 'ihc');?> <a target='_blank' href='https://www.sandbox.paypal.com/'>sandbox.paypal.com</a> <?php esc_html_e(" with your merchant account and click on Account Settings -> Business information -> PayPal Merchant ID.", 'ihc');?></li>
															<li><?php esc_html_e("Set ", 'ihc');?><b><?php echo esc_url($siteUrl . '?ihc_action=paypal');?></b> <?php esc_html_e("in Account Settings -> Website payments -> Instant payment notifications.",'ihc');?></li>
														</ul>
						</div>
														<div class="iump-form-line iump-no-border">
															<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Checkout Page language', 'ihc');?></span>
															<select name="ihc_paypapl_locale_code"  class="form-control">
																	<?php
																			$locale = array(
																												'en_US' => 'English - US',
																												'ar_EG' => 'Arabic - Egipt',
																												'fr_XC' => 'France - Algeria',
																									 			'en_AU' => 'English - Australia',
																									   		'de_DE' => 'German - Germany',
																									  		'nl_NL' => 'Dutch - Netherlands',
																												'fr_FR' => 'French - France',
																												'pt_BR' => 'Portuguese - Brazil',
																												'fr_CA' => 'French - Canada',
																									    	'zh_CN' => 'Chinese - China',
																									   		'da_DK' => 'Danish - Denmark',
																										    'ru_RU' => 'Russian - Russia',
																										    'en_GB' => 'English - Grand Britain',
																										    'id_ID' => 'Indonesian - Indonesia',
																									   		'he_IL' => 'Hebrew - Israel',
																									    	'it_IT' => 'Italian - Italy',
																									   		'ja_JP' => 'Japanese - Japan',
																										    'no_NO' => 'Norwegian - Norway',
																										    'pl_PL' => 'Polish - Poland',
																										    'pt_PT' => 'Portuguese - Portugal',
																									      'sv_SE' => 'Swedish - Sweden',
																									      'zh_TW' => 'Chinese - Taiwan',
																									      'th_TH' => 'Thai - Thailand',
																									      'es_ES' => 'Spanish - Spain',

																			);
																	?>
																	<?php foreach ($locale as $k=>$country):?>
																			<option value="<?php echo esc_attr($k);?>" <?php if ($k==$meta_arr['ihc_paypapl_locale_code']){ echo esc_attr('selected');}?> ><?php echo esc_html($country);?></option>
																	<?php endforeach;?>
															</select></div>
													</div>
													<div class="iump-form-line iump-no-border">
														<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Redirect Page after Payment', 'ihc');?></span>
														<select name="ihc_paypal_return_page" class="form-control">
														<option value="-1" <?php if($meta_arr['ihc_paypal_return_page']==-1){echo esc_attr('selected');}?> >...</option>
														<?php
														if($pages){
														foreach($pages as $k=>$v){
														?>
															<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_paypal_return_page']==$k){ echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
														<?php
														}
														}
														?>
														</select></div>
													</div>
													<div class="iump-form-line iump-no-border">
														<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Redirect Page after cancel Payment', 'ihc');?></span>
														<select name="ihc_paypal_return_page_on_cancel" class="form-control">
														<option value="-1" <?php if($meta_arr['ihc_paypal_return_page_on_cancel']==-1){echo esc_attr('selected');}?> >...</option>
														<?php
														if($pages){
														foreach($pages as $k=>$v){
														?>
															<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_paypal_return_page_on_cancel']==$k){ echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
														<?php
														}
														}
														?>
														</select> </div>
												</div>
							</div>
                 		 </div>




							<div class="ihc-wrapp-submit-bttn iump-submit-form">
								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
							</div>
						</div>
					</div>

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
						<div class="inside">
            <div class="row ihc-row-no-margin">
      			<div class="col-xs-6">
									<div class="iump-form-line iump-no-border input-group">
										<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
										<input type="text" name="ihc_paypal_label" value="<?php echo esc_attr($meta_arr['ihc_paypal_label']);?>"  class="form-control"/>
									</div>

									<div class="iump-form-line iump-no-border input-group">
										<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
										<input type="number" min="1" name="ihc_paypal_select_order" value="<?php echo esc_attr($meta_arr['ihc_paypal_select_order']);?>"  class="form-control"/>
									</div>
						</div>
  					</div>

												  <div class="row ihc-row-no-margin">
												<div class="col-xs-6">
												<div  class="iump-form-line iump-no-border input-group">
													 <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
														 <textarea name="ihc_paypal_short_description" class="form-control" rows="2" cols="125" placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_paypal_short_description'] ) ? stripslashes( $meta_arr['ihc_paypal_short_description'] ) : '';?></textarea>
												 </div>
											 </div>
										 </div>

												 <div class="ihc-wrapp-submit-bttn iump-submit-form">
													 <input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
												 </div>
                        </div>
					</div>

			</form>
			<?php
		break;

		case 'stripe_checkout_v2':
		if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
			ihc_save_update_trimmed_metas('payment_stripe_checkout_v2'); // save update metas without extra spaces
		}

		$meta_arr = ihc_return_meta_arr('payment_stripe_checkout_v2');//getting metas
		echo ihc_check_default_pages_set();//set default pages message
		echo ihc_check_payment_gateways();
		echo ihc_is_curl_enable();
		do_action( "ihc_admin_dashboard_after_top_menu" );
		?>
		<!--div class="iump-page-title">Ultimate Membership Pro -
			<span class="second-text">
				<?php esc_html_e('Stripe Checkout Services', 'ihc');?>
			</span>
		</div-->
		<div class="iump-page-headline">
				<?php esc_html_e('Stripe Checkout Gateway', 'ihc');?>
		</div>
		<form  method="post" class="iump-pqyments-settings-form">
			<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
		<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Stripe Checkout Setup', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
							<div class="col-xs-10">
						<div class="iump-form-line">
              <h2><?php esc_html_e('Activate Stripe Checkout Payment Service', 'ihc');?> </h2>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
							<?php $checked = ($meta_arr['ihc_stripe_checkout_v2_status']) ? 'checked' : '';?>
							<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_stripe_checkout_v2_status');" <?php echo esc_attr($checked);?> />
							<div class="switch  ihc-display-inline"></div>
						</label>
						<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_checkout_v2_status']);?>" name="ihc_stripe_checkout_v2_status" id="ihc_stripe_checkout_v2_status" />
						<p><?php esc_html_e('Once all Settings are properly done, Activate the Payment Getway for further use.', 'ihc');?> </p>

						<?php if(!$meta_arr['ihc_stripe_checkout_v2_status']){ ?>
								<div class="ihc-alert-warning"><?php echo esc_html__('We recommend ', 'ihc'); ?> <a href="<?php echo esc_url( $url . '&tab='. $tab . '&subtab=stripe_connect' );?>"><strong>Stripe Connect</strong></a> <?php echo esc_html__('Payment Service to have access on all Stripe functionalities and complete Subscriptions management.', 'ihc');?></div>
						<?php }else{?>
								<div class="ihc-alert-warning"><?php echo esc_html__('Switch to ', 'ihc'); ?> <a href="<?php echo esc_url($url . '&tab=' . $tab . '&subtab=stripe_connect');?>"><strong>Stripe Connect</strong></a> <?php echo esc_html__('Payment Service to have access on all Stripe functionalities and complete Subscriptions management. Follow ', 'ihc');?>
									<a href="https://ultimatemembershippro.com/stripe-migration-tutorial/" target="_blank">Stripe Checkout Migration</a>
									<?php echo esc_html__(' steps to accomplish this in few minutes.', 'ihc'); ?></div>
						<?php }?>

						</div>
					</div>
				</div>

						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Stripe Checkout Settings', 'ihc');?></h3>
				<div class="inside">
                <div class="row ihc-row-no-margin">
                  <div class="col-xs-10">
					<div class="iump-form-line input-group iump-no-border">
						<span class="input-group-addon" ><?php esc_html_e('Publishable Key', 'ihc');?></span>
						<input type="text" value="<?php echo esc_attr($meta_arr['ihc_stripe_checkout_v2_publishable_key']);?>" name="ihc_stripe_checkout_v2_publishable_key" class="form-control"/>
					</div>

                    <div class="iump-form-line input-group">
						<span class="input-group-addon" ><?php esc_html_e('Secret Key', 'ihc');?></span>
						<input type="text" value="<?php echo esc_attr($meta_arr['ihc_stripe_checkout_v2_secret_key']);?>" name="ihc_stripe_checkout_v2_secret_key"  class="form-control" />
					</div>


					<div class="iump-form-line">
						<?php
							$site_url = site_url();
							$site_url = trailingslashit($site_url);
							$notify_url = add_query_arg( 'ihc_action', 'stripe_checkout', $site_url );
							?>
							<h5><strong><?php esc_html_e('Important:', 'ihc');?></strong> <?php esc_html_e(" set your Webhook to: ", 'ihc');
							echo esc_ump_content('<strong>') . esc_url($notify_url) . esc_ump_content('</strong>');
						?></h5>

						<ul class="ihc-payment-capabilities-list">
							<li><?php esc_html_e('Go to', 'ihc');?> <a href="http://stripe.com" target="_blank">http://stripe.com</a> <?php esc_html_e('and login with username and password.', 'ihc');?></li>
							<li><?php esc_html_e('Complete your Account setup with all required information on ', 'ihc');?><a href="https://dashboard.stripe.com/settings/account" target="_blank">https://dashboard.stripe.com/settings/account</a></li>
							<li><?php esc_html_e('After that click on "Developers" -> "Overview" and check the current "API keys".', 'ihc');?></li>
							<li><?php esc_html_e('In"API Keys" check "Publishable Key" and "Secret Key". If have been created with an old API version, you have to delete and re-create them. If does not exist yet create them.', 'ihc');?></li>
							<li><?php esc_html_e('Verify that you are utilizing the most recent API version', 'ihc');?>. <?php esc_html_e(' Check in ' , 'ihc');?><b><a href="https://stripe.com/docs/api/versioning" target="_blank"><?php esc_html_e('API versioning', 'ihc');?></a></b><?php esc_html_e(' for more details.', 'ihc');?></li>
							<li><?php esc_html_e('Go to "Webhooks" and press "Add endpoint".', 'ihc');?></li>
							<li><?php echo esc_html__("Set your Endpoint URL to: ", 'ihc') . '<strong>' . esc_url($notify_url) . '</strong>';?> <?php esc_html_e(' and select all of the events from "Charge", "Customer", "Invoice", "Subscription Schedule". In "Checkout" event select ', 'ihc');?><code>checkout.session.completed</code>.</li>
														<li><?php echo esc_html__('If some of your cardholders required 3D Secure authentication step you may configure in ', 'ihc');?>  <strong><?php esc_html_e('Manage payments that require 3D Secure', 'ihc');?></strong> <?php esc_html_e(' from ', 'ihc');?> <a href="https://dashboard.stripe.com/account/billing/automatic" target="_blank">https://dashboard.stripe.com/account/billing/automatic</a></li>
														<li><?php echo esc_html__("Customize Stripe Checkout page and Emails on ", 'ihc') . '<a href="https://dashboard.stripe.com/account/branding" target="_blank">https://dashboard.stripe.com/account/branding</a>';?></li>
						</ul>

					</div>




					<div class="iump-form-line">
                    <h4><?php esc_html_e('Test Credentials (only on Sandbox Mode)', 'ihc');?></h4>
												<p><?php esc_html_e('For Sandbox mode use the next credentials available:', 'ihc');?></p>
												<a href="https://stripe.com/docs/testing" target="_blank">https://stripe.com/docs/testing</a>
												<div class="ihc-admin-register-margin-bottom-space"></div>
													<table class="ihc-test-crd">
													  <tr>
													    <th><?php esc_html_e('Description', 'ihc');?></th>
													    <th><?php esc_html_e('Number', 'ihc');?></th>
													  </tr>

													  <tr>
													    <td><?php esc_html_e('Credit Card:', 'ihc');?></td>
													    <td><code>4000002500003155</code></td>
													   </tr>
													   <tr>
													   <td><?php esc_html_e('Expire Time:', 'ihc');?></td>
													   <td><code>12/<?php echo substr( date("Y") + 1, - 2 );?></code></td>
													   </tr>
													</table>
											</div>

											</div>
											</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
				</div>
			</div>

<?php
$pages = ihc_get_all_pages();
?>
							<div class="ihc-stuffbox">
								<h3><?php esc_html_e('Additional Settings', 'ihc');?></h3>
								<div class="inside">

              <div class="row ihc-row-no-margin">
               <div class="col-xs-6">
									<div class="iump-form-line iump-no-border">
										<div class="input-group">
											<span class="input-group-addon"><?php esc_html_e('Stripe Checkout page Language', 'ihc');?></span>
                        <div>
										<select name="ihc_stripe_checkout_v2_locale_code" class="form-control">
												<?php
														$locales = array(
																	'zh' => 'Simplified Chinese',
																	'da' => 'Danish',
																	'nl' => 'Dutch',
																	'en' => 'English',
																	'fi' => 'Finnish',
																	'fr' => 'French',
																	'de' => 'German',
																	'it' => 'Italian',
																	'ja' => 'Japanese',
																	'no' => 'Norwegian',
																	'es' => 'Spanish',
																	'sv' => 'Swedish',
														);
												?>
												<?php foreach ($locales as $k=>$v):?>
														<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_stripe_checkout_v2_locale_code']==$k){ echo esc_attr('selected'); }?> ><?php echo esc_html($v);?></option>
												<?php endforeach;?>
										</select></div>
                                      </div>
                                 </div>

									<div class="iump-form-line  iump-no-border">
										<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Success redirect page', 'ihc');?></span>
                                        <div>
										<select name="ihc_stripe_checkout_v2_success_page" class="form-control">
												<option value="-1" <?php if($meta_arr['ihc_stripe_checkout_v2_success_page']==-1){ echo esc_attr('selected'); }?> >...</option>
												<?php
													if ($pages){
														foreach ($pages as $k=>$v){
															?>
																<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_stripe_checkout_v2_success_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
															<?php
														}
													}
												?>
										</select>
									</div>
								 </div>
                </div>

									<div class="iump-form-line">
										<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Cancel redirect page', 'ihc');?></span>
                                        <div>
										<select name="ihc_stripe_checkout_v2_cancel_page" class="form-control">
												<option value="-1" <?php if($meta_arr['ihc_stripe_checkout_v2_cancel_page']==-1){echo esc_attr('selected');}?> >...</option>
												<?php
													if ($pages){
														foreach ($pages as $k=>$v){
															?>
																<option value="<?php echo esc_attr($k);?>" <?php if($meta_arr['ihc_stripe_checkout_v2_cancel_page']==$k){echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
															<?php
														}
													}
												?>
										</select>
									</div>
								 </div>
                </div>

									<div class="iump-form-line">
											<h4><?php esc_html_e( "Stripe Checkout Fields", 'ihc' );?></h4>
											<p><?php esc_html_e( "Autocomplete Stripe Checkout Email Address with current user account", 'ihc' );?></p>
											<label class="iump_label_shiwtch ihc-switch-button-margin">
													<input type="checkbox" class="iump-switch" onclick="iumpCheckAndH(this, '#ihc_stripe_checkout_v2_use_user_email');" <?php if ( !empty( $meta_arr['ihc_stripe_checkout_v2_use_user_email'] ) ){ echo esc_attr('checked');}?> />
													<div class="switch ihc-display-inline"></div>
											</label>
											<input type="hidden" name="ihc_stripe_checkout_v2_use_user_email" id="ihc_stripe_checkout_v2_use_user_email" value="<?php echo esc_attr($meta_arr['ihc_stripe_checkout_v2_use_user_email']);?>">
									</div>
								 </div>
                </div>
									<div class="ihc-wrapp-submit-bttn iump-submit-form">
										<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
									</div>
								</div>
							</div>

			<div class="ihc-stuffbox">
				<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
				<div class="inside">
                <div class="row ihc-row-no-margin">
                  <div class="col-xs-6">
					<div class="iump-form-line iump-no-border input-group">
						<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
						<input type="text" name="ihc_stripe_checkout_v2_label" class="form-control" value="<?php echo esc_attr($meta_arr['ihc_stripe_checkout_v2_label']);?>" />
					</div>

					<div class="iump-form-line iump-no-border input-group">
						<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
						<input type="number" min="1" name="ihc_stripe_checkout_v2_select_order" class="form-control" value="<?php echo esc_attr($meta_arr['ihc_stripe_checkout_v2_select_order']);?>" />
					</div>
					</div>
            </div>

						  <div class="row ihc-row-no-margin">
						<div class="col-xs-6">
						<div class="iump-form-line iump-no-border input-group">
						   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
						     <textarea name="ihc_stripe_checkout_v2_short_description" class="form-control" rows="2" cols="125"  placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_stripe_checkout_v2_short_description'] ) ? stripslashes($meta_arr['ihc_stripe_checkout_v2_short_description']) : '';?></textarea>
						 </div>
						</div>
						</div>

						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>

				</div>
			</div>

		</form>
		<?php
			break;

		case 'authorize':
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
					//ihc_save_update_metas('payment_authorize');//save update metas
					ihc_save_update_trimmed_metas('payment_authorize'); // save update metas without extra spaces
			}
			$meta_arr = ihc_return_meta_arr('payment_authorize');//getting metas
			echo ihc_check_default_pages_set();//set default pages message
			echo ihc_check_payment_gateways();
			echo ihc_is_curl_enable();
			do_action( "ihc_admin_dashboard_after_top_menu" );
			?>

					<div class="iump-page-headline"><?php esc_html_e('Authorize.net Gateway', 'ihc');?></div>

			<form  method="post" class="iump-pqyments-settings-form">
				<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
			<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Authorize.net Setup', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
									<div class="iump-form-line">
										<h2><?php esc_html_e('Activate Authorize.net Payment Service', 'ihc');?> </h2>
										<label class="iump_label_shiwtch ihc-switch-button-margin">
												<?php $checked = ($meta_arr['ihc_authorize_status']) ? 'checked' : '';?>
												<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_authorize_status');" <?php echo esc_attr($checked);?> />
												<div class="switch ihc-display-inline"></div>
										</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_authorize_status']);?>" name="ihc_authorize_status" id="ihc_authorize_status" />
									<p><?php esc_html_e('Once all Settings are properly done, Activate the Payment Getway for further use.', 'ihc');?> </p>
									<p><?php esc_html_e('For recurring payments, the minimum time value is 7 days.', 'ihc');?></p>
								</div>
							</div>
						</div>
						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
						</div>
					</div>
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Authorize.net Settings', 'ihc');?></h3>
					<div class="inside">
							<div class="row ihc-row-no-margin">
								<div class="col-xs-10">

									<!--<input type="checkbox" onClick="checkAndH(this, '#enable_authorize_sandbox');" <?php if($meta_arr['ihc_authorize_sandbox']){ echo esc_attr('checked');}?> />
								 <input type="hidden" name="ihc_authorize_sandbox" value="<?php echo esc_attr($meta_arr['ihc_authorize_sandbox']);?>" id="enable_authorize_sandbox" />
								 <label class="iump-labels"><h4><?php esc_html_e('Enable Authorize Sandbox', 'ihc');?></h4></label>-->
								 <div class="iump-form-line input-group">
								 <label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
									 <input type="radio" name="ihc_authorize_sandbox" class="" value="1" <?php if ( (int)$meta_arr['ihc_authorize_sandbox'] === 1 ) echo esc_attr('checked');?> />
									 <span class="iump-checkmark-rd"></span>
								 </label>
								 <label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
									 <input type="radio" name="ihc_authorize_sandbox" class="" value="0" <?php if ( (int)$meta_arr['ihc_authorize_sandbox'] === 0 ) echo esc_attr('checked');?> />
									 <span class="iump-checkmark-rd"></span>
								 </label>
							 </div>
							<div class="iump-form-line input-group iump-no-border">
								<span class="input-group-addon"><?php esc_html_e('Login ID', 'ihc');?></span>
								<input type="text" value="<?php echo esc_attr($meta_arr['ihc_authorize_login_id']);?>" name="ihc_authorize_login_id" class="form-control" />
							</div>
							<div class="iump-form-line input-group">
								<span class="input-group-addon"><?php esc_html_e('Transaction Key', 'ihc');?></span>
								<input type="text" value="<?php echo esc_attr($meta_arr['ihc_authorize_transaction_key']);?>" name="ihc_authorize_transaction_key" class="form-control" />
						</div>
						<div class="iump-form-line">
							<?php
								$site_url = site_url();
								$site_url = trailingslashit($site_url);
								$notify_url = add_query_arg('ihc_action', 'authorize', $site_url);
								?>
								<h5><strong>Important:</strong> <?php esc_html_e(" set your 'Silent Post URL' to: ");
								echo esc_ump_content('<strong>' . $notify_url . '</strong>'); /// admin_url("admin-ajax.php") . "?action=ihc_twocheckout_ins"
							?></h5>
							<ul class="ihc-payment-capabilities-list">
								<li><?php esc_html_e('Go to', 'ihc');?> <a href="http://authorize.net" target="_blank">http://authorize.net</a> <?php echo esc_html__(' (or ', 'ihc');?> <a href="https://sandbox.authorize.net/" target="_blank">https://sandbox.authorize.net/</a> <?php echo esc_html__('if you want to use sandbox) and login with username and password.', 'ihc');?></li>
								<li><?php esc_html_e('After that click on "Account". ', 'ihc');?></li>
								<li><?php echo esc_html__('In "Transaction Format Settings" you will find "Silent Post URL", "Response/Receipt URLs" and "Relay Response". Set them to: ', 'ihc'). '<strong>' . $notify_url . '</strong>';?></li>
								<li><?php esc_html_e('In the "Security Settings" section you will find following link: "API Credentials & Keys", click on it.', 'ihc');?></li>
								<li><?php esc_html_e('On this page you will find the "Login ID" and "Transaction Key".', 'ihc');?></li>
							</ul>
						</div>

						<div class="iump-form-line">
							<h4><?php esc_html_e('Test Credentials (only on Sandbox Mode)', 'ihc');?></h4>
                        	<p><?php esc_html_e('For Test/Sandbox mode use the next credentials available:', 'ihc');?></p>
                        	<a href="https://developer.authorize.net/hello_world/testing_guide/" target="_blank">https://developer.authorize.net/hello_world/testing_guide/</a>

														<div class="ihc-admin-register-margin-bottom-space"></div>
														  <table class="ihc-test-crd">
														    <tr>
														      <th><?php esc_html_e('Description', 'ihc');?></th>
														      <th><?php esc_html_e('Number', 'ihc');?></th>
														    </tr>

														    <tr>
														      <td><?php esc_html_e('Credit Card:', 'ihc');?></td>
														      <td><code>370000000000002</code></td>
														     </tr>
														     <tr>
														     <td><?php esc_html_e('Expire Time:', 'ihc');?></td>
														     <td><code>12/<?php echo substr( date("Y") + 1, - 2 );?></code></td>
														     </tr>
														  </table>
						</div>
						</div>
					</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
					</div>
				</div>

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
						<div class="col-xs-6">
						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
							<input type="text" name="ihc_authorize_label" value="<?php echo esc_attr($meta_arr['ihc_authorize_label']);?>"  class="form-control" />
						</div>

						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
							<input type="number" min="1" name="ihc_authorize_select_order" value="<?php echo esc_attr($meta_arr['ihc_authorize_select_order']);?>" class="form-control" />
						</div>
					<div class="iump-form-line iump-no-border input-group">
						 <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
							 <textarea name="ihc_authorize_short_description" class="form-control" rows="2" cols="125" placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_authorize_short_description'] ) ? stripslashes($meta_arr['ihc_authorize_short_description']) : '';?></textarea>
					 </div>
					</div>
				</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
					 <input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				 </div>
				</div>
			</form>
			<?php
		break;

		case 'twocheckout':
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
					//ihc_save_update_metas('payment_twocheckout');//save update metas
					ihc_save_update_trimmed_metas('payment_twocheckout'); // save update metas without extra spaces
			}
			$meta_arr = ihc_return_meta_arr('payment_twocheckout');//getting metas
			echo ihc_check_default_pages_set();//set default pages message
			echo ihc_check_payment_gateways();
			echo ihc_is_curl_enable();
			do_action( "ihc_admin_dashboard_after_top_menu" );
			$pages = ihc_get_all_pages();
			?>
			<div class="iump-page-headline"><?php esc_html_e('2Checkout Gateway', 'ihc');?></div>
			<form  method="post" class="iump-pqyments-settings-form">
				<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('2Checkout Setup', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
						<div class="col-xs-10">
							<div class="iump-form-line">
							<h2><?php esc_html_e('Activate 2Checkout Payment Service', 'ihc');?> </h2>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_twocheckout_status']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_twocheckout_status');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_status']);?>" name="ihc_twocheckout_status" id="ihc_twocheckout_status" />
								<p><?php esc_html_e('Once all Settings are properly done, Activate the Payment Getway for further use.', 'ihc');?> </p>
							</div>
						</div>
					</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
				</div>
				</div>
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('2Checkout Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
							<div class="col-xs-10">

									<!--<input type="checkbox" onClick="checkAndH(this, '#ihc_twocheckout_sandbox');" <?php if($meta_arr['ihc_twocheckout_sandbox']){ echo esc_attr('checked');}?> />
								 <input type="hidden" name="ihc_twocheckout_sandbox" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_sandbox']);?>" id="ihc_twocheckout_sandbox" />
								 <label class="iump-labels"><h4><?php esc_html_e('Enable 2Checkout Sandbox', 'ihc');?></h4></label>-->
								 <div class="iump-form-line input-group">
								 <label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
									 <input type="radio" name="ihc_twocheckout_sandbox" class="" value="1" <?php if ( (int)$meta_arr['ihc_twocheckout_sandbox'] === 1 ) echo esc_attr('checked');?> />
									 <span class="iump-checkmark-rd"></span>
								 </label>
								 <label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
									 <input type="radio" name="ihc_twocheckout_sandbox" class="" value="0" <?php if ( (int)$meta_arr['ihc_twocheckout_sandbox'] === 0 ) echo esc_attr('checked');?> />
									 <span class="iump-checkmark-rd"></span>
								 </label>
							 </div>

							<div class="iump-form-line input-group  iump-no-border"><span class="input-group-addon"><?php esc_html_e('API Username', 'ihc');?></span>
							<input type="text" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_api_user']);?>" name="ihc_twocheckout_api_user" class="form-control" /></div>


							<div class="iump-form-line input-group iump-no-border"><span class="input-group-addon"><?php esc_html_e('API Password', 'ihc');?></span>
							<input type="text" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_api_pass']);?>" name="ihc_twocheckout_api_pass" class="form-control" /></div>


							<div class="iump-form-line input-group iump-no-border"><span class="input-group-addon"><?php esc_html_e('API Private Key', 'ihc');?></span>
							<input type="text" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_private_key']);?>" name="ihc_twocheckout_private_key" class="form-control" /></div>


							<div class="iump-form-line input-group iump-no-border"><span class="input-group-addon"><?php esc_html_e('Merchant Code (Account Number)', 'ihc');?></span>
							<input type="text" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_account_number']);?>" name="ihc_twocheckout_account_number" class="form-control" /></div>


							<div class="iump-form-line input-group"><span class="input-group-addon"><?php esc_html_e('Secret Word', 'ihc');?></span>
							<input type="text" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_secret_word']);?>" name="ihc_twocheckout_secret_word" class="form-control" /></div>

							<div class="iump-form-line">
								<?php
									$site_url = site_url();
									$site_url = trailingslashit($site_url);
									$notify_url = add_query_arg('ihc_action', 'twocheckout', $site_url);
									?>
									<h5><strong>Important:</strong> <?php esc_html_e(" set your 'Web Hook URL'(ISN) and Your 'Approved URL' to: ");
									echo esc_ump_content('<strong>' . $notify_url . '</strong>'); /// admin_url("admin-ajax.php") . "?action=ihc_twocheckout_ins"
								?></h5>

									<ul class="ihc-payment-capabilities-list">
										<li><?php esc_html_e('Go to ', 'ihc');?> <a target="_blank" href="https://www.2checkout.com/">2checkout.com</a><?php echo esc_html__(' and login with username and password.', 'ihc'); ?></li>
										<li><?php esc_html_e('Go to "Integrations" -> "Webhooks & API". You will find "Merchant Code", "Secret Key", "API Private Key", "Secret word".', 'ihc'); ?></li>
										<li><?php esc_html_e('In "Instant Notification System (INS)" section activate the service.', 'ihc'); ?></li>
										<li><?php esc_html_e('In "Redirect URL" section click "Enable return after sale" and set an approve URL.', 'ihc'); ?></li>
										<li><?php echo esc_html__('In "INS settings" add a new endpoint with', 'ihc') . " <b>" . $notify_url ."</b>"; ?></li>
										<li><?php echo esc_html__('In "IPN settings" add your IPN URL with', 'ihc') . " <b>" . $notify_url ."</b>"; ?></li>
									</ul>
							</div>
							<div class="iump-form-line">
				          <h4><?php esc_html_e('For Sandbox mode use the next credentials available:', 'ihc');?></h4>
				          <a href="https://knowledgecenter.2checkout.com/Documentation/09Test_ordering_system/01Test_payment_methods" target="_blank">https://knowledgecenter.2checkout.com/Documentation/09Test_ordering_system/01Test_payment_methods</a>

									<div class="ihc-admin-register-margin-bottom-space"></div>
									  <table class="ihc-test-crd">
									    <tr>
									      <th><?php esc_html_e('Description', 'ihc');?></th>
									      <th><?php esc_html_e('Number', 'ihc');?></th>
									    </tr>

									    <tr>
									      <td><?php esc_html_e('Credit Card:', 'ihc');?></td>
									      <td><code>4111111111111111</code></td>
									     </tr>
									     <tr>
									     <td><?php esc_html_e('Expire Time:', 'ihc');?></td>
									     <td><code>12/<?php echo substr( date("Y") + 1, - 2 );?></code></td>
										 	</tr>
											<tr>
											 <td>CVV:</td>
											 <td><code>123</code></td>
									     </tr>
											 <tr>
												 <td>Name:</td>
												 <td><code>John Doe</code></td>
											 </tr>
									  </table>
							</div>
							<div class="iump-form-line input-group"><span class="input-group-addon"><?php esc_html_e('Redirect Page after Payment', 'ihc');?></span>
							<select name="ihc_twocheckout_return_url" class="form-control">
							 <option value="-1" <?php if($meta_arr['ihc_twocheckout_return_url']==-1){echo esc_attr('selected');}?> >...</option>
							 <?php
								 if($pages){
									 foreach($pages as $k=>$v){
										 ?>
											 <option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_twocheckout_return_url']==$k){ echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
										 <?php
									 }
								 }
							 ?>
						 </select></div>
					</div>




				</div>
				<div class="ihc-wrapp-submit-bttn iump-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
				</div>
			</div>
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
							<div class="col-xs-6">
						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
							<input type="text" name="ihc_twocheckout_label" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_label']);?>" class="form-control" />
						</div>

						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
							<input type="number" min="1" name="ihc_twocheckout_select_order" value="<?php echo esc_attr($meta_arr['ihc_twocheckout_select_order']);?>" class="form-control" />
						</div>
				<div class="iump-form-line iump-no-border input-group">
				   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
				     <textarea name="ihc_twocheckout_short_description" class="form-control" rows="2" cols="125"  placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_twocheckout_short_description'] ) ? stripslashes($meta_arr['ihc_twocheckout_short_description']) : '';?></textarea>
				 </div>
				</div>
				</div>

				<div class="ihc-wrapp-submit-bttn iump-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
				</div>
				</div>

			</form>

			<?php
			break;
		case 'braintree':
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
					//ihc_save_update_metas('payment_braintree');//save update metas
					ihc_save_update_trimmed_metas('payment_braintree'); // save update metas without extra spaces
			}
			$meta_arr = ihc_return_meta_arr('payment_braintree');//getting metas
			echo ihc_check_default_pages_set();//set default pages message
			echo ihc_check_payment_gateways();
			echo ihc_is_curl_enable();
			do_action( "ihc_admin_dashboard_after_top_menu" );
			?>

		<div class="iump-page-headline"><?php esc_html_e('Braintree Gateway', 'ihc');?></div>

				<form  method="post" class="iump-pqyments-settings-form">
					<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Braintree Setup', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
									<div class="iump-form-line">
								<h2><?php esc_html_e('Activate Braintree Payment Service', 'ihc');?> </h2>
								<label class="iump_label_shiwtch ihc-switch-button-margin">
									<?php $checked = ($meta_arr['ihc_braintree_status']) ? 'checked' : '';?>
									<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_braintree_status');" <?php echo esc_attr($checked);?> />
									<div class="switch ihc-display-inline"></div>
								</label>
								<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_braintree_status']);?>" name="ihc_braintree_status" id="ihc_braintree_status" />
								<p><?php esc_html_e('Once all Settings are properly done, Activate the Payment Getway for further use.', 'ihc');?> </p>
							</div>
							</div>
						</div>
						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
						</div>
					</div>

						<div class="ihc-stuffbox">
							<h3><?php esc_html_e('Braintree Settings', 'ihc');?></h3>
							<div class="inside">
								<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
											<div class="iump-form-line input-group">
											<label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
												<input type="radio" name="ihc_braintree_sandbox" class="" value="1" <?php if ( (int)$meta_arr['ihc_braintree_sandbox'] === 1 ) echo esc_attr('checked');?> />
												<span class="iump-checkmark-rd"></span>
											</label>
											<label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
												<input type="radio" name="ihc_braintree_sandbox" class="" value="0" <?php if ( (int)$meta_arr['ihc_braintree_sandbox'] === 0 ) echo esc_attr('checked');?> />
												<span class="iump-checkmark-rd"></span>
											</label>
										</div>

											<div class="iump-form-line input-group iump-no-border"><span class="input-group-addon"><?php esc_html_e('Merchant ID', 'ihc');?></span>
											<input type="text" name="ihc_braintree_merchant_id" value="<?php echo esc_attr($meta_arr['ihc_braintree_merchant_id']);?>" class="form-control" /></div>

											<div class="iump-form-line input-group iump-no-border"><span class="input-group-addon"><?php esc_html_e('Public Key', 'ihc');?></span>
											<input type="text" name="ihc_braintree_public_key" value="<?php echo esc_attr($meta_arr['ihc_braintree_public_key']);?>" class="form-control" /></div>

											<div class="iump-form-line input-group"><span class="input-group-addon"><?php esc_html_e('Private Key', 'ihc');?></span>
											<input type="text" name="ihc_braintree_private_key" value="<?php echo esc_attr($meta_arr['ihc_braintree_private_key']);?>" class="form-control" /></div>

											<div class="iump-form-line">
												<?php
													$site_url = site_url();
													$site_url = trailingslashit($site_url);
													$notify_url = add_query_arg('ihc_action', 'braintree', $site_url);
													?>
													<h5><strong>Important:</strong><?php esc_html_e(" set your Webhook to: ");
													echo esc_ump_content('<strong>' . $notify_url . '</strong>');
												?></h5>

												<ul class="ihc-payment-capabilities-list">
													<li><?php echo esc_html__("Go to ", 'ihc');?><a href="https://www.braintreepayments.com" target="_blank">https://www.braintreepayments.com</a> <?php echo esc_html__("(or ", 'ihc');?> <a href="https://www.braintreepayments.com/sandbox" target="_blank">https://www.braintreepayments.com/sandbox</a> <?php echo esc_html__("if you want to use sandbox version) and login with username and password.", 'ihc');?></li>
													<li><?php echo esc_html__('After you login go to "Account" section and select "My User". In this page click on "View Authorizations".', 'ihc');?></li>
													<li><?php echo esc_html__('In this page you will find the "Public Key", "Private Key" and "Merchant ID".', 'ihc');?></li>
													<li><?php echo esc_html__("After You copy and paste this keys You must set the webhook, to do that go to 'Settings' section and select 'Webhook'.", 'ihc');?></li>
													<li><?php echo esc_html__('Click on "Create new Webhook" and in the next page check all subscription options and set the "Destination URL" to ', 'ihc') . '<strong>' . $notify_url . '</strong>';?></li>
												</ul>
											</div>
											<div class="iump-form-line">
												<h4><?php esc_html_e('Test Credentials (only on Sandbox Mode)','ihc');?></h4>
													<p><?php esc_html_e('For Test/Sandbox mode use the next credentials available:', 'ihc');?></p>
													<a href="https://developers.braintreepayments.com/guides/credit-cards/testing-go-live/php" target="_blank">https://developers.braintreepayments.com/guides/credit-cards/testing-go-live/php</a>

													<div class="ihc-admin-register-margin-bottom-space"></div>
														<table class="ihc-test-crd">
															<tr>
																<th><?php esc_html_e('Description', 'ihc');?></th>
																<th><?php esc_html_e('Number', 'ihc');?></th>
															</tr>

															<tr>
																<td><?php esc_html_e('Credit Card:', 'ihc');?></td>
																<td><code>4500600000000061</code></td>
															 </tr>
															 <tr>
															 <td><?php esc_html_e('Expire Time:', 'ihc');?></td>
															 <td><code>12/<?php echo substr( date("Y") + 1, - 2 );?></code></td>
															</tr>
															<tr>
															 <td>CVV:</td>
															 <td><code>123</code></td>
															 </tr>
															 <tr>
																 <td><?php esc_html_e('Name: ', 'ihc');?></td>
																 <td><code>John Doe</code></td>
															 </tr>
														</table>
													</div>
												</div>
											</div>
							<div class="ihc-wrapp-submit-bttn iump-submit-form">
								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
							</div>
							</div>
						</div>


					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
                <div class="col-xs-6">
							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
								<input type="text" name="ihc_braintree_label" value="<?php echo esc_attr($meta_arr['ihc_braintree_label']);?>" class="form-control" />
							</div>

							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
								<input type="number" min="1" name="ihc_braintree_select_order" value="<?php echo esc_attr($meta_arr['ihc_braintree_select_order']);?>" class="form-control" />
							</div>
					<div class="iump-form-line iump-no-border input-group">
					   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
					     <textarea name="ihc_braintree_short_description" class="form-control" rows="2" cols="125"  placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_braintree_short_description'] ) ? stripslashes($meta_arr['ihc_braintree_short_description']) : '';?></textarea>
					 </div>
					</div>
					</div>

					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
				</div>
			</div>
				</form>
				<?php
			break;
		case 'bank_transfer':
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
					//ihc_save_update_metas('payment_bank_transfer');//save update metas
					ihc_save_update_trimmed_metas('payment_bank_transfer'); // save update metas without extra spaces

			}
			$meta_arr = ihc_return_meta_arr('payment_bank_transfer');//getting metas
			echo ihc_check_default_pages_set();//set default pages message
			echo ihc_check_payment_gateways();
			echo ihc_is_curl_enable();
			do_action( "ihc_admin_dashboard_after_top_menu" );
			?>

		<div class="iump-page-headline"><?php esc_html_e('Bank Transfer Gateway', 'ihc');?></div>
			<form  method="post" class="iump-pqyments-settings-form">
				<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Bank Transfer Setup', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
							<div class="col-xs-10">
						<div class="iump-form-line">
							<h2><?php esc_html_e('Activate Bank Transfer Payment Service', 'ihc');?> </h2>

							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_bank_transfer_status']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_bank_transfer_status');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_bank_transfer_status']);?>" name="ihc_bank_transfer_status" id="ihc_bank_transfer_status" />
							<p><?php esc_html_e('Once all Settings are properly done, Activate the Payment Getway for further use.', 'ihc');?> </p>
							<p><?php esc_html_e('Take payments in person via bank/wire transer', 'ihc');?> </p>
						</div>
					</div>
					</div>
					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
					</div>
				</div>
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Bank Transfer Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
						 <div class="col-xs-10">
						<div class="iump-form-line">
                  	<p><?php esc_html_e('Instructions will be provided to buyer via trank you page. Use available {constants} for a dynamic and complete description', 'ihc');?></p>

							<div class="ihc-payment-bank-editor">
								<?php wp_editor( stripslashes($meta_arr['ihc_bank_transfer_message']), 'ihc_bank_transfer_message', array('textarea_name'=>'ihc_bank_transfer_message', 'quicktags'=>TRUE) );?>
							</div>
							<div class="ihc-payment-bank-editor-constants">
								<div>{siteurl}</div>
								<div>{username}</div>
								<div>{first_name}</div>
								<div>{last_name}</div>
								<div>{user_id}</div>
								<div>{level_id}</div>
								<div>{level_name}</div>
								<div>{amount}</div>
								<div>{currency}</div>
							</div>
					</div>

				</div>
			</div>
			<div class="ihc-wrapp-submit-bttn iump-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>
				</div>
				</div>

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
					<div class="inside">
                    <div class="row ihc-row-no-margin">
                  		<div class="col-xs-6">
						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon" ><?php esc_html_e('Label', 'ihc');?></span>
							<input type="text" name="ihc_bank_transfer_label" value="<?php echo esc_attr($meta_arr['ihc_bank_transfer_label']);?>"  class="form-control"/>
						</div>

						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon" ><?php esc_html_e('Position', 'ihc');?></span>
							<input type="number" min="1" name="ihc_bank_transfer_select_order" value="<?php echo esc_attr($meta_arr['ihc_bank_transfer_select_order']);?>"  class="form-control"/>
						</div>
						<div class="iump-form-line iump-no-border input-group">
						   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
						     <textarea name="ihc_bank_transfer_short_description" class="form-control" rows="2" cols="125" placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_bank_transfer_short_description'] ) ? stripslashes($meta_arr['ihc_bank_transfer_short_description']) : '';?></textarea>
						 </div>
						</div>
						</div>

								<div class="ihc-wrapp-submit-bttn iump-submit-form">
									<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
								</div>
          </div>
				</div>

			</form>

			<?php
			break;

		case 'mollie':
		if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
				//ihc_save_update_metas('payment_mollie');//save update metas
				ihc_save_update_trimmed_metas('payment_mollie'); // save update metas without extra spaces

		}
		$pages = ihc_get_all_pages();//getting pages
		$meta_arr = ihc_return_meta_arr('payment_mollie');//getting metas
		echo ihc_check_default_pages_set();//set default pages message
		echo ihc_check_payment_gateways();
		echo ihc_is_curl_enable();
		do_action( "ihc_admin_dashboard_after_top_menu" );
		?>
				<div class="iump-page-headline"><?php esc_html_e('Mollie Services', 'ihc');?></div>
			<form  method="post" class="iump-pqyments-settings-form">
				<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Mollie Payment Setup', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
						<div class="col-xs-10">
							<div class="iump-form-line">
							<h2><?php esc_html_e('Activate Mollie Payment Service', 'ihc');?> </h2>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_mollie_status']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_mollie_status');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_mollie_status']);?>" name="ihc_mollie_status" id="ihc_mollie_status" />
							<p><?php esc_html_e('Once all Settings are properly done, Activate the Payment Getway for further use.', 'ihc');?> </p>
							<ul class="ihc-payment-capabilities-list">
									<li><?php esc_html_e( ' Be sure you set into your Mollie dashboard Payment Methods at: ', 'ihc');?>  <strong>Credit Card.</strong></li>
									<li><?php esc_html_e('We recommend CreditCard as main Payment Method. In order to manage Payment Methods into your Mollie account please access: Settings -> Website Profiles -> Payment Methods.', 'ihc');?></li>
									<li><?php esc_html_e(' If no Payment Method is set into your mollie dashboard, the system will not work properly.', 'ihc');?> </li>
									<li><?php esc_html_e(' Trial option works only with "Trial Period Price" set with a minimum 0.01 value.', 'ihc');?> </li>
									<li><?php esc_html_e(' Coupons with 100% discounts are not accepted.', 'ihc');?> </li>
							</ul>

								<p><?php esc_html_e('Each payment method has a different minimum and maximum amount set by the banks. Check ', 'ihc')?> <a target="_blank" href="https://help.mollie.com/hc/en-us/articles/115000667365-What-are-the-minimum-and-maximum-amounts-per-payment-method-">minimum and maximum amounts per payment method</a>.</p>
								<p><?php esc_html_e('Check what currencies are supported by Mollie for payments in non-EUR in', 'ihc');?><a target="_blank" href="https://docs.mollie.com/payments/multicurrency"> Supported currencies</a>.</p>


					</div>
				</div>
				</div>
								 <div class="ihc-wrapp-submit-bttn iump-submit-form">
									 <input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
								 </div>
							 </div>

				</div>

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Mollie Settings', 'ihc');?></h3>
						<div class="inside">
			      <div class="row ihc-row-no-margin">
			       <div class="col-xs-10">
							<div class="iump-form-line">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('API key', 'ihc');?></span>
								<input type="text" name="ihc_mollie_api_key" value="<?php echo esc_attr($meta_arr['ihc_mollie_api_key']);?>" class="form-control"/>
							</div>
				</div>
							<div class="iump-form-line">
                <h4><?php esc_html_e('How to setup?', 'ihc'); ?></h4>
									<ul class="ihc-payment-capabilities-list">
											<li><?php esc_html_e('This payment service requires PHP > 5.6 and up-to-date OpenSSL (or other SSL/TLS toolkit). ', 'ihc');?></li>
											<li><?php esc_html_e('Register at: ', 'ihc');?> <a href="https://www.mollie.com" target="_blank">https://www.mollie.com</a></li>
                                             <li><?php esc_html_e('After you login with your username and password go to: ', 'ihc');?> <a href="https://www.mollie.com/dashboard/payments" target="_blank">https://www.mollie.com/dashboard/payments</a></li>
                                            <li> <?php esc_html_e('Go to \'Settings->Website profiles\' section and click on "Create a new website profile." ', 'ihc');?> </li>
											<li><?php esc_html_e('Complete all required details for your current website. ', 'ihc');?> </li>
                                            <li><?php esc_html_e('Go to \'Settings->Website profiles\' and click on "Payment methods" on your website section.', 'ihc');?> </li>
                                            <li><?php esc_html_e('Activate and setup at least one of accepted Payment methods for recurring charges: \'Credit Card\'. ', 'ihc');?> </li>
                                            <li><?php esc_html_e('\'Note:\' if no payment method is activated or an incompatible one payments can not be taken.', 'ihc');?> </li>
                                           <li><?php esc_html_e('Go to \'Developers->API keys\' and copy the \'Test API\' or \'Live API\' key and paste it here.', 'ihc');?> </li>

									</ul>
								</div>

								<div class="iump-form-line input-group"><span class="input-group-addon"><?php esc_html_e('Redirect Page after Payment', 'ihc');?></span>
								<select name="ihc_mollie_return_page" class="form-control">
									<option value="-1" <?php if($meta_arr['ihc_mollie_return_page']==-1){echo esc_attr('selected');}?> >...</option>
									<?php
										if($pages){
											foreach($pages as $k=>$v){
												?>
													<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_mollie_return_page']==$k){ echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
												<?php
											}
										}
									?>
								</select>
							</div>
             </div>
           </div>
						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
						</div>
					</div>

				<div class="ihc-stuffbox">
					<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
					<div class="inside">
						<div class="row ihc-row-no-margin">
            <div class="col-xs-6">
						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
							<input type="text" name="ihc_mollie_label" value="<?php echo esc_attr($meta_arr['ihc_mollie_label']);?>" class="form-control"/>
						</div>

						<div class="iump-form-line iump-no-border input-group">
							<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
							<input type="number" min="1" name="ihc_mollie_select_order" value="<?php echo esc_attr($meta_arr['ihc_mollie_select_order']);?>" class="form-control"/>
						</div>
					<div class="iump-form-line iump-no-border input-group">
					   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
					     <textarea name="ihc_mollie_short_description" class="form-control" rows="2" cols="125"  placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_mollie_short_description'] ) ? stripslashes($meta_arr['ihc_mollie_short_description'] ) : '';?></textarea>
					 </div>
					</div>
					</div>

					<div class="ihc-wrapp-submit-bttn iump-submit-form">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
				</div>
			</div>

			</form>
			<?php
			break;
		case 'paypal_express_checkout':
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
					//ihc_save_update_metas('payment_paypal_express_checkout');//save update metas
					ihc_save_update_trimmed_metas('payment_paypal_express_checkout'); // save update metas without extra spaces

			}
			$meta_arr = ihc_return_meta_arr('payment_paypal_express_checkout');//getting metas
			$pages = ihc_get_all_pages();//getting pages
			echo ihc_check_default_pages_set();//set default pages message
			echo ihc_check_payment_gateways();
			echo ihc_is_curl_enable();
			do_action( "ihc_admin_dashboard_after_top_menu" );
			$siteUrl = site_url();
			$siteUrl = trailingslashit($siteUrl);
			?>
			<!--div class="iump-page-title">Ultimate Membership Pro -
				<span class="second-text">
					<?php esc_html_e('PayPal Express Checkout Services', 'ihc');?>
				</span>
			</div-->
			<div class="iump-page-headline">
					<?php esc_html_e('PayPal Express Checkout Gateway', 'ihc');?>
			</div>
			<form  method="post" class="iump-pqyments-settings-form">
					<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('PayPal Express Checkout Setup', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-10">
							<div class="iump-form-line">
                            	<h2><?php esc_html_e('Activate PayPal Express Checkout Payment Service', 'ihc');?> </h2>

								<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_paypal_express_checkout_status']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_paypal_express_checkout_status');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_paypal_express_checkout_status']);?>" name="ihc_paypal_express_checkout_status" id="ihc_paypal_express_checkout_status" />
							<p><?php esc_html_e('Once everything is properly set up, activate the Payment Service for further use.', 'ihc');?> </p>
							</div>
						</div>
					</div>
							<div class="ihc-wrapp-submit-bttn iump-submit-form">
								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
							</div>
						</div>
					</div>
					<div class="ihc-stuffbox">

						<h3><?php esc_html_e('PayPal Express Checkout Settings', 'ihc');?></h3>

						<div class="inside">
                        <div class="row ihc-row-no-margin">
                  			<div class="col-xs-10">

							<div class="iump-form-line input-group">
								<label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
									<input type="radio" name="ihc_paypal_express_checkout_sandbox" class="" value="1" <?php if ( (int)$meta_arr['ihc_paypal_express_checkout_sandbox'] === 1 ) echo esc_attr('checked');?> />
									<span class="iump-checkmark-rd"></span>
								</label>
								<label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
									<input type="radio" name="ihc_paypal_express_checkout_sandbox" class="" value="0" <?php if ( (int)$meta_arr['ihc_paypal_express_checkout_sandbox'] === 0 ) echo esc_attr('checked');?> />
									<span class="iump-checkmark-rd"></span>
								</label>
							</div>
							<div class="iump-form-line input-group">
									<span class="input-group-addon"><?php esc_html_e('API Username', 'ihc');?></span>
                                    <input type="text" value="<?php echo esc_attr($meta_arr['ihc_paypal_express_checkout_user']);?>" name="ihc_paypal_express_checkout_user" class="form-control" />
							</div>

							<div class="iump-form-line input-group">
									<span class="input-group-addon"><?php esc_html_e('API Password', 'ihc');?></span>
                                    <input type="text" value="<?php echo esc_attr($meta_arr['ihc_paypal_express_checkout_password']);?>" name="ihc_paypal_express_checkout_password"  class="form-control"/>
							</div>

							<div class="iump-form-line input-group">
									<span class="input-group-addon"><?php esc_html_e('API Signature', 'ihc');?></span>
                                    <input type="text" value="<?php echo esc_attr($meta_arr['ihc_paypal_express_checkout_signature']);?>" name="ihc_paypal_express_checkout_signature"  class="form-control"/>
							</div>

							<div class="iump-form-line">
								<h4><?php esc_html_e( 'How to get required credentials', 'ihc' );?> </h4>
										<ul class="ihc-payment-capabilities-list">
										  <li><?php esc_html_e( 'Access the "Account Settings" section', 'ihc' );?></li>
											<li><?php esc_html_e( 'Go to PayPal "My Profile" (top-right settings icon)', 'ihc' );?></li>
											<li><?php esc_html_e( 'Find "API access" option into "Website Payments" section and click on "Update".', 'ihc' );?></li>
											<li><?php esc_html_e( 'If you do not have one, create with "Request API signature" option', 'ihc' );?></li>
											<li><?php esc_html_e( 'Copy credentials received on the next page (API Username, API Password, Signature)', 'ihc' );?></li>
											<li><?php esc_html_e( 'for sandbox', 'ihc' );?> <a target="_blank" href="https://www.sandbox.paypal.com/businessprofile/mytools/apiaccess/firstparty/signature">https://www.sandbox.paypal.com/businessprofile/mytools/apiaccess/firstparty/signature</a></li>
											<li><?php esc_html_e( 'for live environment', 'ihc' );?> <a target="_blank" href="https://www.paypal.com/businessprofile/mytools/apiaccess/firstparty/signature">https://www.paypal.com/businessprofile/mytools/apiaccess/firstparty/signature</a></li>
										</ul>
						</div>
						<div class="iump-form-line">
								<h4><?php esc_html_e( 'Setup Intructions', 'ihc' );?> </h4>

										<ul class="ihc-payment-capabilities-list">
										  <li><?php esc_html_e('In order to use PayPal Express Checkout you must set your IPN. First go to: ', 'ihc');?><a href="https://www.paypal.com/signin" target="_blank">https://www.paypal.com/signin</a><?php esc_html_e(' or: ', 'ihc');?></li>
											<li>	<a href="https://www.sandbox.paypal.com/signin" target="_blank">https://www.sandbox.paypal.com/signin</a></li>
											<li><?php esc_html_e("Login with your credentials and go to 'Account Settings' (top-right of page)", 'ihc');?></li>
											<li><?php esc_html_e("After that go to 'Notifications' and next Update the 'Instant payment notifications' ", 'ihc');?></li>
											<li><?php esc_html_e('Set your IPN at: ', 'ihc');?><a href="<?php echo esc_url($siteUrl . '?ihc_action=paypal_express_checkout_ipn');?>"><?php echo esc_url($siteUrl . '?ihc_action=paypal_express_checkout_ipn');?></a></li>
										</ul>
						</div>
						<div class="iump-form-line">
							<div class="row ihc-row-no-margin">
                <div class="col-xs-5 ihc-col-no-padding">
								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Redirect Page after Payment', 'ihc');?></span>
								<select name="ihc_paypal_express_return_page" class="form-control">
									<option value="-1" <?php if($meta_arr['ihc_paypal_express_return_page']==-1){echo esc_attr('selected');}?> >...</option>
									<?php
										if($pages){
											foreach($pages as $k=>$v){
												?>
													<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_paypal_express_return_page']==$k){ echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
												<?php
											}
										}
									?>
								</select></div>
                </div>
                </div>
							</div>
							<div class="iump-form-line">
							<div class="row  ihc-row-no-margin">
                <div class="col-xs-5 ihc-col-no-padding">
 								<div class="input-group"><span class="input-group-addon"><?php esc_html_e('Redirect Page after cancel Payment', 'ihc');?></span>
 								<select name="ihc_paypal_express_return_page_on_cancel" class="form-control">
 									<option value="-1" <?php if($meta_arr['ihc_paypal_express_return_page_on_cancel']==-1){echo esc_attr('selected');}?> >...</option>
 									<?php
 										if($pages){
 											foreach($pages as $k=>$v){
 												?>
 													<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_paypal_express_return_page_on_cancel']==$k){ echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
 												<?php
 											}
 										}
 									?>
 								</select></div>
                              </div>
                              </div>
							</div>

	                        </div>
	                        </div>
													<div class="ihc-wrapp-submit-bttn iump-submit-form">
														<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
													</div>
					</div>

						</div>


					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
						<div class="inside">
                <div class="row ihc-row-no-margin">
                	<div class="col-xs-6">
							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
								<input type="text" name="ihc_paypal_express_checkout_label" value="<?php echo esc_attr($meta_arr['ihc_paypal_express_checkout_label']);?>"  class="form-control"/>
							</div>

							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
								<input type="number" min="1" name="ihc_paypal_express_checkout_select_order" value="<?php echo esc_attr($meta_arr['ihc_paypal_express_checkout_select_order']);?>"  class="form-control"/>
							</div>
									<div class="iump-form-line input-group">
									   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
									     <textarea name="ihc_paypal_express_short_description" class="form-control" rows="2" cols="125"  placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_paypal_express_short_description'] ) ? stripslashes($meta_arr['ihc_paypal_express_short_description']) : '';?></textarea>
									 </div>
									</div>
									</div>

			 							<div class="ihc-wrapp-submit-bttn iump-submit-form">
			 								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			 							</div>
            </div>
					</div>

			</form>
			<?php
			break;
		case 'pagseguro':
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
					//ihc_save_update_metas('payment_pagseguro');//save update metas
					ihc_save_update_trimmed_metas('payment_pagseguro'); // save update metas without extra spaces

			}
			$meta_arr = ihc_return_meta_arr('payment_pagseguro');//getting metas
			$pages = ihc_get_all_pages();//getting pages
			echo ihc_check_default_pages_set();//set default pages message
			echo ihc_check_payment_gateways();
			echo ihc_is_curl_enable();
			do_action( "ihc_admin_dashboard_after_top_menu" );
			?>

				<div class="iump-page-headline"><?php esc_html_e('Pagseguro Gateway', 'ihc');?></div>
			<form  method="post" class="iump-pqyments-settings-form">
				<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Pagseguro Setup', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
							<div class="col-xs-10">
								<div class="iump-form-line">
								<h2><?php esc_html_e('Activate Pagseguro Payment Service', 'ihc');?> </h2>
								<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = ($meta_arr['ihc_pagseguro_status']) ? 'checked' : '';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_pagseguro_status');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_pagseguro_status']);?>" name="ihc_pagseguro_status" id="ihc_pagseguro_status" />
							<p><?php esc_html_e('Once everything is properly set up, activate the Payment Service for further use.', 'ihc');?> </p>
							<p><?php esc_html_e( 'Use this payment service only in Brazil and set the currency type at Real(BRL).', 'ihc' );?></p>
              <p><?php esc_html_e( 'Recurring Interval options are: WEEKLY (1 week), MONTHLY (1 month), BIMONTHLY (2 months), TRIMONTHLY (3 months), SEMIANNUALLY (6 months), YEARLY (1 year).', 'ihc' );?></p>
							</div>
							<?php
									$siteUrl = site_url();
					        $siteUrl = trailingslashit($siteUrl);
							?>
						</div>
						</div>
						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Pagseguro Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
										<div class="col-xs-10">
											<div class="iump-form-line input-group">
											<!--<input type="checkbox" onClick="checkAndH(this, '#ihc_pagseguro_sandbox');" <?php if($meta_arr['ihc_pagseguro_sandbox']){ echo esc_attr('checked');}?> />
											 <input type="hidden" name="ihc_pagseguro_sandbox" value="<?php echo esc_attr($meta_arr['ihc_pagseguro_sandbox']);?>" id="ihc_pagseguro_sandbox" />
											 <label class="iump-labels"><h4><?php esc_html_e('Enable Pagseguro Sandbox', 'ihc');?></h4></label>-->

											 <label class="iump-container-radio"><?php esc_html_e('Sandbox', 'ihc');?>
												 <input type="radio" name="ihc_pagseguro_sandbox" class="" value="1" <?php if ( (int)$meta_arr['ihc_pagseguro_sandbox'] === 1 ) echo esc_attr('checked');?> />
												 <span class="iump-checkmark-rd"></span>
											 </label>
											 <label class="iump-container-radio"><?php esc_html_e('Live', 'ihc');?>
												 <input type="radio" name="ihc_pagseguro_sandbox" class="" value="0" <?php if ( (int)$meta_arr['ihc_pagseguro_sandbox'] === 0 ) echo esc_attr('checked');?> />
												 <span class="iump-checkmark-rd"></span>
											 </label>
										 </div>
								<div class="iump-form-line input-group iump-no-border">
									<span class="input-group-addon"><?php esc_html_e('Pagseguro Account E-mail', 'ihc');?></span>
									<input type="text" value="<?php echo esc_attr($meta_arr['ihc_pagseguro_email']);?>" name="ihc_pagseguro_email" class="form-control" />
								</div>
								<div class="iump-form-line input-group">
									<span class="input-group-addon"><?php esc_html_e('Token', 'ihc');?></span>
									<input type="text" value="<?php echo esc_attr($meta_arr['ihc_pagseguro_token']);?>" name="ihc_pagseguro_token" class="form-control" />
								</div>
										<div class="iump-form-line iump-no-border">
										<ul class="ihc-payment-capabilities-list">
											<li><?php esc_html_e(' Login in to ', 'ihc'); ?><?php echo esc_ump_content('<a target="_blank" href="https://pagseguro.uol.com.br/">pagseguro.uol.com.br.</a>'); ?></li>
											<li><?php esc_html_e('In email field set the email address used to register the account.', 'ihc'); ?></li>
											<li><?php esc_html_e(' In Online Sale -> Integrations go to Use of API\'s and Generate token', 'ihc');?></li>
											<li><?php esc_html_e(' In Transaction notification set the Notification URL as: ', 'ihc'); ?><a href="<?php echo esc_url($siteUrl  . '?ihc_action=pagseguro' );?>"><?php echo esc_url($siteUrl . '?ihc_action=pagseguro');?></a></li>
										</ul>

											<p><?php esc_html_e('1. Login in to ', 'ihc'); ?> <?php echo  '<a target="_blank" href="https://sandbox.pagseguro.uol.com.br">sandbox.pagseguro.uol.com.br</a>';?></p>
											<p><?php esc_html_e('2. In Sandbox -> Test Buyer you can find the details in order to make test payments.', 'ihc'); ?></p>
										</div>
									</div>
								</div>

							<div class="ihc-wrapp-submit-bttn iump-submit-form">
								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
							</div>
						</div>
						</div>

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
                <div class="col-xs-6">
							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
								<input type="text" name="ihc_pagseguro_label" value="<?php echo esc_attr($meta_arr['ihc_pagseguro_label']);?>" class="form-control"/>
							</div>

							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
								<input type="number" min="1" name="ihc_pagseguro_select_order" value="<?php echo esc_attr($meta_arr['ihc_pagseguro_select_order']);?>" class="form-control"/>
							</div>
					<div class="iump-form-line iump-no-border input-group">
					   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
					     <textarea name="ihc_pagseguro_short_description" class="form-control" rows="2" cols="125"  placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_pagseguro_short_description'] ) ? stripslashes($meta_arr['ihc_pagseguro_short_description']) : '';?></textarea>
					 </div>
					</div>
					</div>

					 <div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
				</div>
			</div>
			</form>
			<?php
			break;

			case 'stripe_connect':
				if ( isset( $_GET['access_token'] ) && $_GET['access_token'] !== ''	&& isset( $_GET['stripe_publishable_key'] )
					&& $_GET['stripe_publishable_key'] !== '' && isset( $_GET['stripe_user_id'] ) && $_GET['stripe_user_id'] !== ''
					&& isset( $_GET['code'] ) && $_GET['code'] !== '' && wp_verify_nonce( sanitize_text_field($_GET['code']), 'ihc_stripe_connect_auth' ) ){
							// save the credentials
							if ( $_GET['sandbox'] ){
									// sandbox
									update_option( 'ihc_stripe_connect_test_client_secret', sanitize_text_field( $_GET['access_token'] ) );
									update_option( 'ihc_stripe_connect_test_publishable_key', sanitize_text_field( $_GET['stripe_publishable_key'] ) );
									update_option( 'ihc_stripe_connect_test_account_id', sanitize_text_field( $_GET['stripe_user_id'] ) );
							} else {
									// live
									update_option( 'ihc_stripe_connect_client_secret', sanitize_text_field( $_GET['access_token'] ) );
									update_option( 'ihc_stripe_connect_publishable_key', sanitize_text_field( $_GET['stripe_publishable_key'] ) );
									update_option( 'ihc_stripe_connect_account_id', sanitize_text_field( $_GET['stripe_user_id'] ) );
							}

							if ( get_option( 'ihc_stripe_connect_activation_time' ) === false ){
									update_option( 'ihc_stripe_connect_activation_time', time() );
							}
				} else if ( isset( $_GET['error_deauth'] ) && sanitize_text_field($_GET['error_deauth']) == 1 ){
						?>
								<div class="ihc-warning-box"><?php esc_html_e('Error on trying to deauth from Stripe.', 'ihc');?></div>
						<?php
				}

				if (isset($_POST['ihc_save']) && !empty($_POST['ihc-payment-settings-nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc-payment-settings-nonce']), 'ihc-payment-settings-nonce' ) ){
						ihc_save_update_trimmed_metas('payment_stripe_connect'); // save update metas without extra spaces
				}

				// 12.2.1
				if ( isset( $_POST['ihc_save'] ) && isset( $_POST['ihc_stripe_connect_status'] ) && (int)$_POST['ihc_stripe_connect_status'] === 1 ){
						$stripePaymentStatus = ihc_check_payment_status( 'stripe_connect');
						if ( isset( $stripePaymentStatus['status'] ) && $stripePaymentStatus['status'] === 1
								 && isset( $stripePaymentStatus['settings'] ) && $stripePaymentStatus['settings'] === 'Completed' ){
								// set as default payment
								update_option( 'ihc_payment_selected', 'stripe_connect' );
						}
				}
				// 12.2.1

				$meta_arr = ihc_return_meta_arr('payment_stripe_connect');//getting metas
				echo ihc_check_default_pages_set();//set default pages message
				echo ihc_check_payment_gateways();
				echo ihc_is_curl_enable();
				do_action( "ihc_admin_dashboard_after_top_menu" );

				?>
				<!--div class="iump-page-title">Ultimate Membership Pro -
					<span class="second-text">
						<?php esc_html_e('Stripe Connect Services', 'ihc');?>
					</span>
				</div-->
				<div class="iump-page-headline">
						<?php esc_html_e('Stripe Connect Gateway', 'ihc');?>
				</div>
				<form  method="post" class="iump-pqyments-settings-form">
					<input type="hidden" name="ihc-payment-settings-nonce" value="<?php echo wp_create_nonce( 'ihc-payment-settings-nonce' );?>" />
					<div class="ihc-stuffbox">
							<h3><?php esc_html_e('Stripe Connect Setup', 'ihc');?></h3>
							<div class="inside">
								<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
									<div class="iump-form-line">
		                <h2><?php esc_html_e('Activate Stripe Connect Payment Service', 'ihc');?> </h2>
										<label class="iump_label_shiwtch ihc-switch-button-margin">
											<?php $checked = ($meta_arr['ihc_stripe_connect_status']) ? 'checked' : '';?>
											<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_stripe_connect_status');" <?php echo esc_attr($checked);?> />
											<div class="switch ihc-display-inline"></div>
										</label>
										<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_status']);?>" name="ihc_stripe_connect_status" id="ihc_stripe_connect_status" />

										<p><?php esc_html_e('Once all Settings are properly done, Activate the Payment Service for further use.', 'ihc');?> </p>
										<p class="ihc-highlighted-paragrah"><strong><?php esc_html_e('The most Complete Payment Service with secure Onsite Payment Form.', 'ihc');?></strong> </p>
										<p class="ihc-highlighted-paragrah"><?php esc_html_e('Using Stripe Connect payment service Members will complete the payment process without leaving the current website. Also, they may update their Credit Cards credentials anytime from Member Portal.', 'ihc');?> </p>


									<h4><?php esc_html_e('Stripe Connect Capabilities', 'ihc');?></h4>
									<ul class="ihc-payment-capabilities-list">
										<li><?php esc_html_e('One-Time Payments', 'ihc');?></li>
										<li><?php esc_html_e('Recurring onGoing Subscriptions', 'ihc');?></li>
										<li><?php esc_html_e('Recurring Limited Subscriptions', 'ihc');?></li>
										<li><?php esc_html_e('Free Trial/Initial Payment', 'ihc');?></li>
										<li><?php esc_html_e('Instant Payment Confirmation', 'ihc');?></li>
										<li><?php esc_html_e('Use previous Saved Cards', 'ihc');?></li>
										<li><?php esc_html_e('Cancel Recurring Subscriptions', 'ihc');?></li>
										<li><?php esc_html_e('Pause Recurring Subscriptions', 'ihc');?></li>
										<li><?php esc_html_e('Resume Recurring Subscriptions', 'ihc');?></li>
										<li><?php esc_html_e('Refunds', 'ihc');?></li>
										<li><?php esc_html_e('Change Credit Card for Recurring Subscription', 'ihc');?></li>
										<li><?php esc_html_e('Credit Card Expiring Notifications Reminders', 'ihc');?></li>
										<!--li><?php esc_html_e('ApplePay, GooglePay or Microsoft Pay Integration', 'ihc');?></li-->
									</ul>
								</div>
								</div>
							</div>
							<div class="ihc-wrapp-submit-bttn iump-submit-form">
								<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
							</div>
							</div>

					</div>

					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Stripe Connect Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
								<div class="col-xs-10">
							<?php
								$site_url = site_url();
								$site_url = trailingslashit($site_url);
								$notify_url = add_query_arg('ihc_action', 'stripe_connect', $site_url);
								?>
							<div class="iump-form-line">
								<h2><?php esc_html_e('Stripe Connect Connection', 'ihc');?></h2>
								<p><?php esc_html_e('Choose how you wish to setup Stripe Connect on your website. In Test Mode you can use test credentials to see how the payment process works without transferring real money.', 'ihc');?></p>
							</div>
							<div class="iump-form-line">
									<?php
											$checked = empty( $meta_arr['ihc_stripe_connect_live_mode'] ) ? '' : 'checked';
									?>
									<div class="ihc-switch_andtype">
											<input type="checkbox" <?php echo esc_attr($checked);?> name="ihc-switch_andtype" class="ihc-switch_andtype-checkbox" id="onoff_ics_about_page_enable" onchange="iumpCheckAndH(this,'#ihc_stripe_connect_live_mode');ihcUpdateStripeConnectAuthUrl();">
											<label class="ihc-switch_andtype-label" for="onoff_ics_about_page_enable">
													<span class="ihc-switch_andtype-inner">
														<span class="ihc-switch_andtype-active"><span class="ihc-switch_andtype-switch"><?php esc_html_e('Live Mode', 'ihc');?></span></span>
														<span class="ihc-switch_andtype-inactive"><span class="ihc-switch_andtype-switch"><?php esc_html_e('Test Mode', 'ihc');?></span></span>
													</span>
											</label>
									</div>
									<input type="hidden" name="ihc_stripe_connect_live_mode" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_live_mode']);?>" id="ihc_stripe_connect_live_mode" />
							</div>
							<div class="ihc-stripe-connect-live-wrapper">
								<?php
										$stripeConnect = new \Indeed\Ihc\Gateways\StripeConnect();
										$authUrl = $stripeConnect->getAuthUrl( 1 );
										$deauthUrl = $stripeConnect->getDeauthUrl( 1, $meta_arr['ihc_stripe_connect_account_id'] );
										$extraClass = empty( $meta_arr['ihc_stripe_connect_live_mode'] ) ? 'ihc-display-none' : 'ihc-display-block';
								?>
								<div class="ihc-js-stripe-connect-live <?php echo esc_attr($extraClass);?>">
										<?php
											if ( $meta_arr['ihc_stripe_connect_publishable_key'] === '' && $meta_arr['ihc_stripe_connect_client_secret'] === ''
														&& $meta_arr['ihc_stripe_connect_account_id'] === '' ) :
										?>
										<div class="iump-form-line">

											<div class="row ihc-row-no-margin">
													<div class="col-xs-12 ihc-col-no-padding">
															<div class="ihc-stripe-connect-status ihc-danger-box"><strong><?php esc_html_e('Your Stripe Account is not Connected on Live mode', 'ihc');?></strong></div>
													</div>
											</div>
											<h4><?php esc_html_e('Connect your Stripe Account by clicking on the blue button', 'ihc');?></h4>
											<a href="<?php echo esc_url($authUrl);?>" class="ihc-stripe-connect-btn"><span>Connect with Stripe</span></a>
										</div>

										<?php else :?>
											<div class="iump-form-line">
												<div class="row ihc-row-no-margin">
														<div class="col-xs-12 ihc-col-no-padding">
																<div class="ihc-success-box ihc-stripe-connect-status"><strong><?php esc_html_e('Congratulation! Your Stripe Account is connected in Live Mode', 'ihc');?></strong></div>
														</div>
												</div>
												<p><?php esc_html_e('Deauthentificate from Stripe Connect: ', 'ihc');?></p>
												<span data-url="<?php echo esc_url($deauthUrl);?>" data-refresh_url="<?php echo admin_url('admin.php?page=ihc_manage&tab=payment_settings&subtab=stripe_connect');?>" class="ihc-stripe-connect-btn ihc-js-deauth-from-stripe-checkout-bttn"><span>Deauth from Stripe</span></span>
												<p><?php esc_html_e('or ReConnect with a new Stripe Account:', 'ihc');?></p>
													<a href="<?php echo esc_attr($authUrl);?>" class="ihc-stripe-connect-btn"><span>ReConnect with Stripe</span></a>
												<div>
													<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_publishable_key']);?>" data-type="pk_key" />
													<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_client_secret']);?>" data-type="client_key" />
													<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_account_id']);?>" data-type="acct_id" />
												</div>
											</div>
										<?php endif;?>
										<div class="iump-form-line">
											<h4><?php esc_html_e('Live Webhook', 'ihc');?></h4>
											<p><?php esc_html_e('Setup WebHook URL into your Stripe Account including specific events packages ', 'ihc'); echo esc_ump_content('<a href="https://dashboard.stripe.com/webhooks" target="_blank">'.esc_html__( 'here', 'ihc' ).'</a>'); ?></p>
											<p><strong><?php echo esc_url($notify_url); ?></strong></p>
											<p><strong><?php esc_html_e('and choose all the events from "Charge", "Customer", "Invoice", "Subscription Schedule".', 'ihc');?></strong></p>
											<?php
											$last_webhook = get_option( 'ihc_stripe_connect_last_webhook_received_live' );
											if ( ! empty( $last_webhook ) ) {
												echo esc_ump_content('<div class="ihc-stripe-connect-status ihc-success-box">' . esc_html__( 'Last Webhook Notification received at', 'ihc' ) . ': ' . esc_html( $last_webhook ) . ' GMT.</div>');
											} else {
												echo esc_ump_content('<div class="ihc-stripe-connect-status ihc-warning-box">' . esc_html__( 'No Webhooks Notifications have been received. Check your Webhook setup ', 'ihc' ) .'<a href="https://dashboard.stripe.com/webhooks" target="_blank">'.esc_html__( 'here', 'ihc' ).'</a>'. '</div>');
											}
											 ?>
										</div>
								</div>
								<?php
										$authUrl = $stripeConnect->getAuthUrl( 0 );
										$deauthUrl = $stripeConnect->getDeauthUrl( 0, $meta_arr['ihc_stripe_connect_test_account_id'] );
										$extraClass = empty( $meta_arr['ihc_stripe_connect_live_mode'] ) ? 'ihc-display-block' : 'ihc-display-none';
								?>
								<div class="ihc-js-stripe-connect-sandbox <?php echo esc_attr($extraClass);?>">
									<?php
										if ( $meta_arr['ihc_stripe_connect_test_publishable_key'] === '' && $meta_arr['ihc_stripe_connect_test_client_secret'] === ''
													&& $meta_arr['ihc_stripe_connect_test_account_id'] === '' ) :
									?>
										<div class="iump-form-line">
											<div class="row ihc-row-no-margin">
													<div class="col-xs-12 ihc-col-no-padding">
															<div class="ihc-stripe-connect-status ihc-danger-box"><strong><?php esc_html_e('Your Stripe Account is not Connected on Sandbox mode', 'ihc');?></strong></div>
													</div>
											</div>
											<h4><?php esc_html_e('Connect your Stripe Test Account by clicking on the blue button', 'ihc');?></h4>
											<a href="<?php echo esc_url($authUrl);?>" class="ihc-stripe-connect-btn" ><span>Connect with Stripe</span></a>
										</div>
									<?php else :?>
										<div class="iump-form-line">
											<div class="row ihc-row-no-margin">
													<div class="col-xs-12 ihc-col-no-padding">
															<div class="ihc-success-box ihc-stripe-connect-status"><strong><?php esc_html_e('Congratulation! Your Stripe Account is connected in Test Mode', 'ihc');?></strong></div>
													</div>
											</div>
											<p><?php esc_html_e('Deauthentificate from Test Stripe Connect: ', 'ihc');?></p>
											<span data-url="<?php echo esc_url($deauthUrl);?>"
														data-refresh_url="<?php echo admin_url('admin.php?page=ihc_manage&tab=payment_settings&subtab=stripe_connect');?>"
														class="ihc-stripe-connect-btn ihc-js-deauth-from-stripe-checkout-bttn"><span>Deauth from Stripe</span></span>
											<p><?php esc_html_e('or ReConnect with a new Test Stripe Account: ', 'ihc');?></p>
											<a href="<?php echo esc_url($authUrl);?>" class="ihc-stripe-connect-btn" ><span>ReConnect with Stripe</span></a>
											<div>
												<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_test_publishable_key']);?>" data-type="pk_key" />
												<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_test_client_secret']);?>" data-type="client_key" />
												<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_test_account_id']);?>" data-type="acct_id" />
											</div>
										</div>
									<?php endif;?>
									<div class="iump-form-line">
										<h4><?php esc_html_e('Test Webhook', 'ihc');?></h4>
										<p><?php esc_html_e('Setup WebHook URL into your Stripe Account (Test Mode) including specific events packages ', 'ihc'); echo esc_ump_content('<a href="https://dashboard.stripe.com/test/webhooks" target="_blank">'.esc_html__( 'here', 'ihc' ).'</a>'); ?></p>
										<p><strong><?php echo esc_url($notify_url); ?></strong></p>
										<p><strong><?php esc_html_e('and choose all the events from "Charge", "Customer", "Invoice", "Subscription Schedule".', 'ihc');?></strong></p>
										<?php
										$last_webhook = get_option( 'ihc_stripe_connect_last_webhook_received_test' );
										if ( ! empty( $last_webhook ) ) {
											echo esc_ump_content('<div class="ihc-stripe-connect-status ihc-success-box">' . esc_html__( 'Last Webhook Notification received at', 'ihc' ) . ': ' . esc_html( $last_webhook ) . ' GMT.</div>');
										} else {
											echo esc_ump_content('<div class="ihc-stripe-connect-status ihc-warning-box">' . esc_html__( 'No Webhooks Notifications have been received. Check your Webhook setup ', 'ihc' ) .'<a href="https://dashboard.stripe.com/test/webhooks" target="_blank">'.esc_html__( 'here', 'ihc' ).'</a>'. '</div>');
										}
										 ?>
									</div>
								</div>

								<div class="iump-form-line">
									<h4><?php esc_html_e('Setup Instructions', 'ihc');?></h4>
									<ul class="ihc-payment-capabilities-list">
										<li><?php esc_html_e('In order to use the Stripe Pro payment service extension, you must first have a Stripe Verified Account. Also cover Stripe Business Requirements.', 'ihc');?></li>
										<li><?php esc_html_e('Be sure that your website has a proper SSL certificate installed. Stripe refuses payments from websites without a proper SSL certificate. Please contact your host regarding how to get SSL on your site if needed.', 'ihc');?></li>
										<li><?php esc_html_e('Click on the Blue "Connect with Stripe" button and you will be redirected to authenticate with your Stripe account. If you are not logged into Stripe, click the "Sign in" button at the top.', 'ihc');?></li>
										<li><?php esc_html_e('Select your Stripe Account and click the "Connect my Stripe account" button.', 'ihc');?></li>
										<li><?php esc_html_e('Once Account have been connected you will be redirected back to your website.', 'ihc');?></li>
										<li><?php esc_html_e('Stripe Connect payment service includes an 1% application fee for payment processing and is used to support the Connect server.', 'ihc');?></li>
									</ul>
									<p><strong><?php esc_html_e('Check out our ', 'ihc');?> <a href="https://ultimatemembershippro.com/stripe-connect-tutorial/" target="_blank"><?php esc_html_e('Documentation', 'ihc');?></a><?php esc_html_e(' for additional details and complete Setup Steps.', 'ihc');?></strong></p>
								</div>

								<!--div class="iump-form-line">
										<h4><?php esc_html_e('Accept Payment Request for ApplePay, GooglePay or Microsoft Pay', 'ihc');?></h4>
									<label class="iump_label_shiwtch ihc-switch-button-margin">
										<?php $checked = ($meta_arr['ihc_stripe_connect_payment_request']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_stripe_connect_payment_request');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_payment_request']);?>" name="ihc_stripe_connect_payment_request" id="ihc_stripe_connect_payment_request" />

									<p><?php esc_html_e('Allow users to pay using Apple Pay, Google Pay, or Microsoft Pay depending on their browser. When enabled, your domain will automatically be registered with Apple and a domain association file will be hosted on your site', 'ihc');?></p>
									<p><a href="https://stripe.com/docs/stripe-js/elements/payment-request-button#verifying-your-domain-with-apple-pay" target="_blank"><?php esc_html_e('Read More', 'ihc');?></a></p>
								</div-->

								<div class="iump-form-line">
										<h4><?php esc_html_e('Activate Payments via Saved Cards', 'ihc');?></h4>
										<p><?php esc_html_e('Provides existent Customers to pay with previous used Cards', 'ihc');?></p>
									<label class="iump_label_shiwtch ihc-switch-button-margin">
										<?php $checked = ($meta_arr['ihc_stripe_connect_saved_cards']) ? 'checked' : '';?>
										<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_stripe_connect_saved_cards');" <?php echo esc_attr($checked);?> />
										<div class="switch ihc-display-inline"></div>
									</label>
									<input type="hidden" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_saved_cards']);?>" name="ihc_stripe_connect_saved_cards" id="ihc_stripe_connect_saved_cards" />


								</div>
								<div class="iump-form-line">
									<h4><?php esc_html_e('Statement Descriptor', 'ihc');?></h4>
									<div class="input-group">
													<span class="input-group-addon"><?php esc_html_e('Descriptor', 'ihc');?></span>
			                    <input type="text" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_descriptor']);?>" name="ihc_stripe_connect_descriptor" class="form-control">
									</div>
									<p><?php esc_html_e('Statement descriptors are limited to 22 characters, cannot use the special characters, and must not consist solely of numbers. This will appear on your customer statement', 'ihc');?></p>
								</div>
							</div>
							<div class="ihc-stripe-connect-test-wrapper ihc-js-stripe-connect-sandbox <?php echo esc_attr($extraClass);?>">
								<div class="iump-form-line">
		                         <h2><?php esc_html_e('Test Credentials', 'ihc');?></h2>
		                        	<p><?php esc_html_e('For Test/Sandbox mode use the next credentials available:', 'ihc');?></p>
		                        	<a href="https://stripe.com/docs/testing" target="_blank">https://stripe.com/docs/testing</a>

																<div class="ihc-admin-register-margin-bottom-space"></div>
																	<table class="ihc-test-crd">
																		<tr>
																			<th><?php esc_html_e('Description', 'ihc');?></th>
																			<th><?php esc_html_e('Number', 'ihc');?></th>
																		</tr>

																		<tr>
																			<td><?php esc_html_e('Credit Card:', 'ihc');?></td>
																			<td><code>4000002500003155</code></td>
																		 </tr>
																		 <tr>
																		 <td><?php esc_html_e('Expire Time:', 'ihc');?></td>

																		 <td><code>12/<?php echo substr( date("Y") + 1, - 2 );?></code></td>
																		 </tr>
																	</table>
		            </div>
							</div>
						</div>
						</div>
						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>
						</div>

					</div>
					<div class="ihc-stuffbox">
						<h3><?php esc_html_e('Extra Settings', 'ihc');?></h3>
						<div class="inside">
							<div class="row ihc-row-no-margin">
		              <div class="col-xs-6">
												<div class="iump-form-line iump-no-border input-group">
													<h4><?php esc_html_e('Stripe Connect Form Language', 'ihc');?></h4>

													<select name="ihc_stripe_connect_locale_code" class="form-control">
															<?php
																	$locales = array(
																				'auto' => 'Stripe Auto Detect',
																				'ar'	=> 'Arabic',
																				'bg'	=> 'Bulgarian',
																				'cs'	=> 'Czech',
																				'da'	=> 'Danish',
																				'de'	=> 'German',
																				'el'	=> 'Greek',
																				'en' => 'English',
																				'en-GB'	=> 'English (United Kingdom)',
																				'es'	=> 'Spanish',
																				'es-419'	=> 'Spanish (Latin America)',
																				'et'	=> 'Estonian',
																				'fi'	=> 'Finnish',
																				'fil'	=> 'Filipino',
																				'fr' => 'French',
																				'fr-CA'	=> 'French (Canada)',
																				'he'	=> 'Hebrew',
																				'hr'	=> 'Croatian',
																				'hu'	=> 'Hungarian',
																				'id'	=> 'Indonesian',
																				'it' => 'Italian',
																				'ja' => 'Japanese',
																				'ko'	=> 'Korean',
																				'lt'	=> 'Lithuanian',
																				'lv'	=> 'Latvian',
																				'ms'	=> 'Malay',
																				'mt'	=> 'Maltese',
																				'nb'	=> 'Norwegian',
																				'nl'	=> 'Dutch',
																				'pl'	=> 'Polish',
																				'pt'	=> 'Portuguese',
																				'ro'	=> 'Romanian',
																				'ru	'	=> 'Russian',
																				'sk'	=> 'Slovak',
																				'sl'	=> 'Slovenian',
																				'sv'	=> 'Swedish',
																				'th'	=> 'Thai',
																				'tr'	=> 'Turkish',
																				'vi'	=> 'Vietnamese',
																				'zh'	=> 'Chinese Simplified',
																				'zh-HK'	=> 'Chinese Traditional (Hong Kong)',
																				'zh-TW'	=> 'Chinese Traditional (Taiwan)',
																	);
															?>
															<?php foreach ($locales as $k=>$v):?>
																	<option value="<?php echo esc_attr($k);?>" <?php if ($meta_arr['ihc_stripe_connect_locale_code']==$k){ echo esc_attr('selected');}?> ><?php echo esc_html($v);?></option>
															<?php endforeach;?>
													</select>

							</div>
								</div>
							</div>
					<div class="row ihc-row-no-margin">
						<div class="col-xs-6">
							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Label', 'ihc');?></span>
								<input type="text" name="ihc_stripe_connect_label" class="form-control" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_label']);?>" />
							</div>

							<div class="iump-form-line iump-no-border input-group">
								<span class="input-group-addon"><?php esc_html_e('Position', 'ihc');?></span>
								<input type="number" min="1" name="ihc_stripe_connect_select_order" class="form-control" value="<?php echo esc_attr($meta_arr['ihc_stripe_connect_select_order']);?>" />
							</div>
								<div class="iump-form-line iump-no-border input-group">
								   <h4><?php esc_html_e('Short Description', 'ihc');?></h4>
								     <textarea name="ihc_stripe_connect_short_description" class="form-control" rows="2" cols="125"  placeholder="<?php esc_html_e('write a short description', 'ihc');?>"><?php echo isset( $meta_arr['ihc_stripe_connect_short_description'] ) ? stripslashes($meta_arr['ihc_stripe_connect_short_description']) : '';?></textarea>
								 </div>
								</div>
								</div>

						<div class="ihc-wrapp-submit-bttn iump-submit-form">
							<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
						</div>

					</div>
					</div>
				</form>
				<?php
			break;

		default:

			do_action( 'ihc_payment_gateway_page', sanitize_text_field( $_GET['subtab']) );
			// @description action on admin - dashboard , payment settings. @param type of payment

			break;
	}

}//end of switch
