<?php $disabled = (ihc_is_curl_enable()) ? 'disabled' : '';?>
<?php do_action( "ihc_admin_dashboard_after_top_menu" );?>
<?php
$responseNumber = isset($_GET['response']) ? (int)sanitize_text_field($_GET['response']) : false;
if ( !empty($_GET['token'] ) && $responseNumber == 1 ){
		$ElCheck = new \Indeed\Ihc\Services\ElCheck();
		$responseNumber = $ElCheck->responseFromGet();
}
if ( $responseNumber !== false ){
		$ElCheck = new \Indeed\Ihc\Services\ElCheck();
		$responseMessage = $ElCheck->responseCodeToMessage( $responseNumber, 'ihc-danger-box', 'ihc-success-box', 'ihc' );
}
$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
$ol_dL_ogs = new $class();
$e = !$ol_dL_ogs->FGCS();
$h = $ol_dL_ogs->GCP();
if ( $h === true ){
		$h = '';
}

?>

<div class="ump-license-management-wrapper">

	<div class="metabox-holder indeed">
		<div class="ihc-stuffbox">
			<h3>
				<label>
					<?php esc_html_e('Manage Ultimate Membership Pro License', 'ihc');?>
				</label>
			</h3>
			<form method="post" >
				<div class="inside">
					<div class="iump-form-line iump-no-border">
						<h2>Ultimate Membership Pro <?php esc_html_e('License', 'ihc');?></h2>
						<p><?php esc_html_e('To unlock the full potential of Ultimate Membership Pro and enjoy all its benefits, ensure to activate your plugin license. With an activated Ultimate Membership Pro license, you gain access to a wide range of features including all Ultimate Membership Pro Standard Modules, seamless automatic updates, and dedicated official support.', 'ihc');?></p>
					</div>
					<?php if ($disabled):?>
						<div class="iump-form-line iump-no-border"><strong><?php esc_html_e("cURL is disabled. You need to enable if for further activation request.", 'ihc');?></strong></div>
					<?php endif;?>

					<div class="iump-form-line">
					<div class="ump-form">
						<div class="ump-form-field">
							<div class="ump-field-label"><?php esc_html_e('Customer Name', 'ihc');?></div>
							<input type="text" name="cn" class="ihc-form-element ihc-form-element-text ihc-js-admin-help-section-name" value="" placeholder="">
						</div>
						<div class="ump-form-field">
							<div class="ump-field-label"><?php esc_html_e('Vendor', 'ihc');?></div>
	            <?php $currentVend = get_option('ium'.'p_l'.'nk_v');?>
							<select name="ls" class="ihc-form-element ihc-form-element-select ihc-js-admin-help-section-ls">
								<?php foreach ( IUMP_VEND as $vendName => $vendValue ):?>
                    <option value="<?php echo $vendName;?>" <?php if ( $vendName === $currentVend ){ echo 'selected'; }?> ><?php echo $vendValue;?></option>
                <?php endforeach;?>
							</select>

						</div>
						<div class="ump-form-field">
							<div class="ump-field-label"><?php esc_html_e('License Code', 'ihc');?></div>
							<span class="ump-form-right-side">
							<input name="pv2" type="password" class="ihc-form-element ihc-form-element-text" value="<?php echo esc_attr($h);?>"/>

							<a href="https://ultimatemembershippro.com/find-my-license-code/" target="_blank"><?php esc_html_e('Where can I find my License Code?', 'ihc');?></a>
						</span>
						</div>
						<div class="ump-form-field">
							<div class="ump-field-label"><?php esc_html_e('Domain Name', 'ihc');?></div>
							<span class="ump-field-subtitle"><?php echo umpSiteDomain();?></span>
						</div>
						<div class="ump-form-field">
							<div class="ump-field-label"></div>
							<span class="ump-form-right-side">
								<?php if ( (int)$ol_dL_ogs->GTCO() > 0 ): ?>
										<input type="submit" value="<?php esc_html_e('Register the License', 'ihc');?>" name="ihc_save_licensing_code" <?php echo esc_attr($disabled);?> class="iump-first-button ump-big-button ihc-text-center" />
								<?php else :?>
										<?php if ( $e ):?>
												<div class="ihc-js-revoke-license iump-first-button ump-big-button ihc-text-center"><?php esc_html_e( 'Revoke the License', 'ihc' );?></div>
										<?php else: ?>
												<input type="submit" value="<?php esc_html_e('Activate the License', 'ihc');?>" name="ihc_save_licensing_code" <?php echo esc_attr($disabled);?> class="iump-first-button ump-big-button ihc-text-center" />
										<?php endif;?>
								<?php endif;?>
							</span>
						</div>

						<div class="ump-form-field">
							<div class="ump-field-label"></div>
							<span class="ump-form-right-side ump-get-new-license-message">
								<?php esc_html_e("Don't have direct license yet?", 'ihc');?> <a href="https://ultimatemembershippro.com/pricing/" target="_blank"><?php esc_html_e('Purchase Ultimate Membership Pro License', 'ihc');?></a>
							</span>
						</div>
					</div>
					</div>

 <div class="iump-form-line ihc-license-status-wrapper">
					<?php if ( (int)$ol_dL_ogs->GTCO() > 0 ): ?>
						<div class="ihc-license-status">
								<?php esc_html_e( 'Your Ultimate Membership Pro plugin license is not activated and registered.', 'ihc' );?>
						</div>
					<?php else: ?>
							<div class="ihc-license-status">
				        	<?php
										if ( $responseNumber !== false ){
												echo esc_ump_content($responseMessage);
										} else if ( !empty( $_GET['revoke'] ) ){
												?>
												<div class="ihc-success-box"><?php esc_html_e( 'You have just deactivated your license for the Ultimate Membership Pro plugin.', 'ihc' );?></div>
												<?php
										} else if ( $ol_dL_ogs->FGCS() == 0 ){ ?>
													<div class="ihc-success-box"><?php esc_html_e( 'Your license for Ultimate Membership Pro is currently Active, ensuring you have access to all the premium features and automatic updates', 'ihc' );?></div>
				          <?php } ?>
				      </div>
					<?php endif;?>

					<div class="ihc-license-status">
						<?php
						if ( isset($_GET['extraCode']) && isset( $_GET['extraMess'] ) && $_GET['extraMess'] != '' ){
								$_GET['extraMess'] = stripslashes( $_GET['extraMess'] );
								if ( (int)$_GET['extraCode'] > 0 ){
										// success
										?>
										<div class="ihc-success-box"><?php echo urldecode( (string)sanitize_text_field($_GET['extraMess']) );?></div>
										<?php
								} else if ( (int)$_GET['extraCode'] < 0 ){
										// errors
										?>
										<div class="ihc-danger-box"><?php echo urldecode( (string)sanitize_text_field($_GET['extraMess']) );?></div>
										<?php
								} else if ( (int)$_GET['extraCode'] == 0 ){
										// warning
										?>
										<div class="ihc-warning-box"><?php echo urldecode( (string)sanitize_text_field($_GET['extraMess']) );?></div>
										<?php
								}
						}
					?>
					</div>
				</div>
					<div class="iump-form-line">
						<p><?php esc_html_e('A License code can only be used for ', 'ihc');?><strong><?php esc_html_e('ONE', 'ihc');?></strong> Ultimate Membership Pro <?php esc_html_e('for WordPress installation on', 'ihc');?> <strong><?php esc_html_e('ONE', 'ihc');?></strong> <?php esc_html_e('WordPress site at a time. If you previosly activated your purchase code on another website, then you have to get a', 'ihc');?>
							  <a href="https://ultimatemembershippro.com/pricing/" target="_blank"><?php esc_html_e('new Licence', 'ihc');?></a>.</p>
					</div>
				</div>
			</form>
		</div>
	</div>

<div class="metabox-holder indeed">

	<div class="ihc-stuffbox">
		<h3>
			<label>
		    	<?php esc_html_e('Documentation', 'ihc');?>
		    </label>
		</h3>
		<div class="inside">
			<iframe src="https://ultimatemembershippro.com/documentation/" width="100%" height="1000px" ></iframe>
		</div>
	</div>
</div>
</div>
<span class="ihc-js-help-page-data"
			data-nonce="<?php echo wp_create_nonce('ihc_license_nonce');?>"
			data-revoke_url="<?php echo admin_url('admin.php?page=ihc_manage&tab=help&revoke=true');?>"
			data-help="<?php esc_html_e( 'Error!', 'ihc' );?>"
></span>
