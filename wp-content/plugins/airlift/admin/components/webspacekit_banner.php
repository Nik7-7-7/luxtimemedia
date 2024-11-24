<?php
$isMalcarePresent = false;
$message = 'Speed up your website in just one click';
if (class_exists('MCAccount') &&
		class_exists('MCWPSettings') &&
		!MCAccount::isConfigured(new MCWPSettings())) {
	$isMalcarePresent = true;
	$message = 'Speed up and protect your website with one-click scans and firewall';
}
$hideDismissButton = false;
if (array_key_exists('page', $_REQUEST) && $_REQUEST['page'] == $this->bvinfo->plugname) {
	$hideDismissButton = true;
}
?>
<div class="wsk-wrapper">
	<?php if (!$hideDismissButton) : ?>
		<form method="POST">
			<?php wp_nonce_field('dismiss_wsk_banner'); ?>
			<button type="submit" class="notice-dismiss" name="dismiss_wsk_banner"></button>
		</form>
	<?php endif; ?>
	<div class="row wsk-banner">
		<div class="logo-header">
			<?php if ($isMalcarePresent) : ?>
				<img src="<?php echo esc_url(plugins_url('/../../img/wsk-airlift-malcare.png', __FILE__)); ?>" alt="WebSpaceKit" loading="lazy">
			<?php else: ?>
				<img src="<?php echo esc_url(plugins_url('/../../img/wsk-airlift.png', __FILE__)); ?>" alt="WebSpaceKit" loading="lazy">
			<?php endif; ?>
		</div>

		<div class="wsk-body">
			<div class="left-section">
				<div class="img-container">
					<img src="<?php echo esc_url(plugins_url('/../../img/speedometer.svg', __FILE__)); ?>" alt="" loading="lazy">
					<?php if ($isMalcarePresent) : ?>
						<img src="<?php echo esc_url(plugins_url('/../../img/security.svg', __FILE__)); ?>" alt="" loading="lazy">
					<?php endif; ?>
				</div>
				<h3><?php echo $message; ?></h3>
				<div class="wsk-grad-btn-wrapper">
					<form action="<?php echo esc_url($this->bvinfo->appUrl()); ?>/plugin/webspacekit_signup" onsubmit="document.getElementById('get-started').disabled = true;"  method="post" name="signup">
						<input type='hidden' name='bvsrc' value='wpplugin'/>
						<input type='hidden' name='origin' value='webspacekit'/>
						<input type='hidden' name='is_malcare_active' value='<?php echo $isMalcarePresent ? 'true' : 'false'; ?>'/>
						<input type='hidden' name='is_airlift_active' value='true'/>
						<?php echo $this->siteInfoTags(); ?>
						<input type="hidden" id="email" name="email" value="<?php echo  wp_get_current_user()->user_email; ?>">
						<input type="hidden" name="consent" value="1">
						<button class="wsk-btn" type="submit">Get Started</button>
					</form>
				</div>
			</div>
			<div class="right-section">
				<img src="<?php echo esc_url(plugins_url('/../../img/max.svg', __FILE__)); ?>" alt="">
			</div>
		</div>
	</div>
</div>