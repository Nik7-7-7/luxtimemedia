<?php if (!empty($data ['notices'])):?>

		<div class="ulp-error-global-dashboard-message"">
				<div class="ulp-close-notice ulp-js-close-admin-dashboard-notice">x</div>
				<?php foreach ($data ['notices'] as $notice):?>

						<div><?php echo esc_ulp_content($notice);?></div>

				<?php endforeach;?>

		</div>

<?php endif;?>
