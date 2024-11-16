<?php
$responseNumber = isset($_GET['response']) ? $_GET['response'] : false;
if ( !empty($_GET['token'] ) && $responseNumber == 1 ){
		$ElCheck = new \Indeed\Uap\ElCheck();
		$responseNumber = $ElCheck->responseFromGet();
}
if ( $responseNumber !== false ){
		$ElCheck = new \Indeed\Uap\ElCheck();
		$responseMessage = $ElCheck->responseCodeToMessage( $responseNumber, 'uap-danger-box', 'uap-success-box', 'uap' );
}

?>
<div class="uap-wrapper uap-license-management-wrapper">
<!--div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Help', 'uap');?></span></div-->

<div class="uap-stuffbox">
	<h3 class="uap-h3">
		<?php esc_html_e('Manage Ultimate Affiliate Pro License', 'uap');?>
	</h3>

	<form method="post" >
		<div class="inside">
			<div class="uap-form-line uap-no-border">
				<h2>Ultimate Affiliate Pro <?php esc_html_e('License', 'uap');?></h2>
				<p><?php esc_html_e('To unlock the full potential of Ultimate Affiliate Pro and enjoy all its benefits, ensure to activate your plugin license. With an activated Ultimate Affiliate Pro license, you gain access to a wide range of features including all Ultimate Affiliate Pro Standard Modules, seamless automatic updates, and dedicated official support.', 'uap');?></p>
			</div>
			<?php if ($disabled):?>
				<div class="uap-form-line uap-no-border uap-color-red"><strong><?php esc_html_e("cURL is disabled. You need to enable if for further activation request.", 'uap');?></strong></div>
			<?php endif;?>
			<div class="uap-form-line">
			<div class="uap-form">
				<div class="uap-form-field">
					<div class="uap-field-label"><?php esc_html_e('Customer Name', 'uap');?></div>
					<?php $name = get_option( 'uap' . 'l_nk_' . 'n' );?>
					<input type="text" name="cn" class="uap-form-element uap-form-element-text uap-js-admin-help-section-name" value="<?php echo $name;?>" placeholder="">
				</div>
				<div class="uap-form-field">
					<div class="uap-field-label"><?php esc_html_e('Vendor', 'uap');?></div>
					<?php $currentVend = get_option('ua'.'p_l'.'nk_v');?>
					<select name="ls" class="uap-form-element uap-form-element-select uap-js-admin-help-section-ls">
						<?php foreach ( UAP_VEND as $vendName => $vendValue ):?>
								<option value="<?php echo $vendName;?>" <?php if ( $vendName === $currentVend ){ echo 'selected'; }?> ><?php echo $vendValue;?></option>
						<?php endforeach;?>
					</select>

				</div>
				<div class="uap-form-field">
					<div class="uap-field-label"><?php esc_html_e('License Key', 'uap');?></div>
					<span class="uap-form-right-side">
					<input name="cl" type="password" class="uap-form-element uap-form-element-text uap-js-admin-help-section-s" value="<?php echo $data['about_ranks'] === true ? '' : esc_attr($data['about_ranks']);?>" placeholder="">
					<a href="https://ultimateaffiliate.pro/find-my-license-code/" target="_blank"><?php esc_html_e('Where can I find my License Key?', 'uap');?></a>
				</span>
				</div>
				<div class="uap-form-field">
					<div class="uap-field-label"><?php esc_html_e('Domain Name', 'uap');?></div>
					<span class="uap-field-subtitle"><?php echo uapSiteDomain();//get_site_url();?></span>
				</div>
				<div class="uap-form-field">
					<div class="uap-field-label"></div>
					<span class="uap-form-right-side">
						<?php if ( !$data['stats']->{$data['type']}() ):?>
              	<div class="uap-first-button uap-big-button uap-text-center uap-js-revoke-license "><?php esc_html_e( 'Revoke the License', 'uap' );?></div>
              <?php else: ?>
              	<input type="submit" value="<?php esc_html_e('Activate the License', 'uap');?>" name="uap_save_licensing_code" <?php echo esc_attr($disabled);?> class="uap-first-button uap-big-button uap-text-center" />
              <?php endif;?>
					</span>
				</div>
				<div class="uap-form-field">
					<div class="uap-field-label"></div>
					<span class="uap-form-right-side uap-get-new-license-message">
						<?php esc_html_e("Don't have direct license yet?", 'uap');?> <a href="https://ultimateaffiliate.pro/pricing/" target="_blank"><?php esc_html_e('Purchase Ultimate Affiliate Pro License', 'uap');?></a>
					</span>
				</div>
			</div>
			</div>

			<div class="uap-form-line uap-license-status-wrapper">
				<div class="uap-license-status">
	        	<?php
							if ( $responseNumber !== false ){
									echo esc_uap_content( $responseMessage );
							} else if ( !empty( $_GET['revoke'] ) ){
									?>
									<div class="uap-success-box"><?php esc_html_e( 'You have just deactivated your license for the Ultimate Affiliate Pro plugin.', 'uap' );?></div>
									<?php
							} else if ( !$data['stats']->{$data['type']}() ){ ?>
										<div class="uap-success-box"><?php esc_html_e( 'Your license for Ultimate Affiliate Pro is currently Active, ensuring you have access to all the premium features and automatic updates', 'uap' );?></div>
	          <?php } ?>
	      </div>

				<div class="uap-license-status">
							<?php
					if ( isset($_GET['extraCode']) && isset( $_GET['extraMess'] ) && $_GET['extraMess'] != '' ){
							$_GET['extraMess'] = stripslashes($_GET['extraMess']);
							if ( $_GET['extraCode'] > 0 ){
									// success
									?>
									<div class="uap-success-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
									<?php
							} else if ( $_GET['extraCode'] < 0 ){
									// errors
									?>
									<div class="uap-danger-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
									<?php
							} else if ( $_GET['extraCode'] == 0 ){
									// warning
									?>
									<div class="uap-warning-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
									<?php
							}
					}
				?>
				</div>
			</div>

			<div class="uap-form-line">
				<p><?php esc_html_e('A License Key can only be used for ', 'uap');?><strong><?php esc_html_e('ONE', 'uap');?></strong> Ultimate Affiliate Pro <?php esc_html_e('for WordPress installation on', 'uap');?> <strong><?php esc_html_e('ONE', 'uap');?></strong> <?php esc_html_e('WordPress site at a time. If you previosly activated your license key on another website, then you have to get a', 'uap');?>
					  <a href="https://ultimateaffiliate.pro/pricing/" target="_blank"><?php esc_html_e('new Licence', 'uap');?></a>.</p>
			</div>

      </div>



	</form>
	</div>




	<div class="uap-stuffbox">
		<h3 class="uap-h3">
		    	<?php esc_html_e('Documentation', 'uap');?>
		</h3>
		<div class="inside">
			<iframe src="https://ultimateaffiliate.pro/docs/" width="100%" height="1000px" ></iframe>
		</div>
	</div>

</div>
<div class="uap-js-help-section-nonce" data-value="<?php echo wp_create_nonce('uap_license_nonce');?>"></div>
<div class="uap-js-help-section-revoke-url" data-value="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=help&revoke=true');?>"></div>
