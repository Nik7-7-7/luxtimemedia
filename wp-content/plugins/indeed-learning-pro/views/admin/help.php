<?php
$responseNumber = isset($_GET['response']) ? $_GET['response'] : false;
if ( !empty($_GET['token'] ) && $responseNumber == 1 ){
		$ElCheck = new \Indeed\Ulp\ElCheck();
		$responseNumber = $ElCheck->responseFromGet();
}
if ( $responseNumber !== false ){
		$ElCheck = new \Indeed\Ulp\ElCheck();
		$responseMessage = $ElCheck->responseCodeToMessage( $responseNumber, 'ulp-danger-box', 'ulp-success-box', 'ulp' );
}
$license = get_option( 'ulp_license_set' );
$envato_code = get_option( 'ulp_envato_code' );
?>

<div class="ulp-page-title">Ultimate Learning Pro - <span class="second-text"><?php esc_html_e('Help', 'ulp');?></span></div>

<div class="ulp-stuffbox">
	<h3 class="ulp-h3">
		<?php esc_html_e('Activate Ultimate Learning Pro', 'ulp');?>
	</h3>

	<form method="post" >
		<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
		
		<div class="inside">
			<?php if ($data ['disabled']):?>
				<div class="ulp-form-line ulp-no-border ulp-admin-help-warning"><?php esc_html_e("cURL is disabled. You need to enable if for further activation request.")?></div>
			<?php endif;?>

			<div class="ulp-form-line ulp-no-border ulp-admin-help-label">
				<label for="tag-name" class="ulp-labels"><?php esc_html_e('Purchase Code', 'ulp');?></label>
			</div>

			<div class="ulp-form-line ulp-no-border ulp-admin-help-input-wrapper">
				<input name="ulp_licensing_code" type="text" value="<?php echo esc_attr($data['ulp_envato_code']);?>" class="ulp-admin-help-input"/>
			</div>

			<div class="ulp-stuffbox-submit-wrap ulp-admin-help-submit">
				<?php if ( $license ):?>
						<div class="ulp-revoke-license ulp-js-revoke-license"><?php esc_html_e( 'Revoke License', 'ulp' );?></div>
				<?php else: ?>
						<input type="submit" value="<?php esc_html_e('Activate License', 'ulp');?>" name="ulp_save_licensing_code" <?php echo esc_attr($data ['disabled']);?> class="button button-primary button-large" />
				<?php endif;?>
			</div>

			<div class="ulp-clear"></div>

			<div class="ulp-license-status">
				<?php
					if ( $responseNumber !== false ){
							echo esc_ulp_content($responseMessage);
					} else if ( !empty( $_GET['revoke'] ) ){
							?>
							<div class="ulp-success-box"><?php esc_html_e( 'You have just revoke your License for Ultimate Learning Pro plugin.', 'ulp' );?></div>
							<?php
					} else if ( $license ){ ?>
								<div class="ulp-success-box"><?php esc_html_e( 'Your License for Ultimate Learning Pro is currently Active.', 'ulp' );?></div>
				<?php } ?>
      </div>

			<div class="ulp-license-status">
				<?php
						if ( isset($_GET['extraCode']) && isset( $_GET['extraMess'] ) && $_GET['extraMess'] != '' ){
								$_GET['extraMess'] = stripslashes($_GET['extraMess']);
								if ( $_GET['extraCode'] > 0 ){
										// success
										?>
										<div class="ulp-success-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
										<?php
								} else if ( $_GET['extraCode'] < 0 ){
										// errors
										?>
										<div class="ulp-danger-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
										<?php
								} else if ( $_GET['extraCode'] == 0 ){
										// warning
										?>
										<div class="ulp-warning-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
										<?php
								}
						}
				?>
			</div>


				<div class="ulp-admin-help-desc">
					<p><?php esc_html_e('A valid purchase code Activate the Full Version of', 'ulp');?><strong> Ultimate Learning Pro</strong> <?php esc_html_e('plugin and provides access on support system. A purchase code can only be used for ', 'ulp');?><strong><?php esc_html_e('ONE', 'ulp');?></strong> Ultimate Learning Pro <?php esc_html_e('for WordPress installation on', 'ulp');?> <strong><?php esc_html_e('ONE', 'ulp');?></strong> <?php esc_html_e('WordPress site at a time. If you previosly activated your purchase code on another website, then you have to get a', 'ulp');?> <a href="https://codecanyon.net/user/azzaroco/portfolio?ref=azzaroco" target="_blank"><?php esc_html_e('new Licence', 'ulp');?></a>.</p>
					<h4><?php esc_html_e('Where can I find my Purchase Code?', 'ulp');?></h4>
					<a href="https://codecanyon.net/user/azzaroco/portfolio?ref=azzaroco" target="_blank">
							<img src="<?php echo ULP_URL;?>assets/images/purchase_code.jpg" class="ulp-admin-help-img"/>
					</a>
				</div>
			</div>
	</form>
	<div class="ulp-clear"></div>
</div>


  <div class="ulp-stuffbox">

		<h3 class="ulp-h3">
			<label>
				<?php esc_html_e('Contact Support', 'ulp');?>
			</label>
		</h3>

		<div class="inside">
			<div class="submit ulp-admin-help-support-text">
				<?php esc_html_e('In order to contact Indeed support team you need to create a ticket providing all the necessary details via our support system:', 'ulp');?> support.wpindeed.com
			</div>
			<div class="submit ulp-admin-help-support-link">
				<a href="http://support.wpindeed.com/open.php?topicId=20" target="_blank" class="button button-primary button-large"> <?php esc_html_e('Submit Ticket', 'ulp');?></a>
			</div>
			<div class="ulp-clear"></div>
		</div>

	</div>

  <div class="ulp-stuffbox">
		<h3 class="ulp-h3">
			<label>
				<?php esc_html_e('PHP Classes Descriptions', 'ulp');?>
			</label>
		</h3>
		<div class="inside">
        <?php require ULP_PATH . 'views/admin/view_description_for_php_classes.php';?>
		</div>
	</div>

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3">
			<label>
		    	<?php esc_html_e('Documentation', 'ulp');?>
		    </label>
		</h3>
		<div class="inside">
			<iframe src="https://learning.wpindeed.com/documentation/" width="100%" height="1000px" ></iframe>
		</div>
	</div>
<span class="ulp-js-help"
			data-nonce="<?php echo wp_create_nonce( 'ulp_license_nonce' );?>"
			data-location_reload="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=help&revoke=true');?>"
></span>

<?php
