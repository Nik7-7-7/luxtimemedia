<div>
	<h2>Ultimate Affiliate Pro</h2>
	<p><?php esc_html_e('This section enables administrators to manage affiliate-related settings', 'uap');?></p>
	<label class="uap-edit-wp-user-label"><?php esc_html_e('Affiliate Actions', 'uap');?></label>
	<div class="uap-edit-wp-user-status">
		<?php if ($data['is_affiliate']): ?>

			<div>
				<button type="button" class="button button-secondary" onclick="uapMakeAffiliateSimpleUser(<?php echo esc_attr($data['id']);?>);"><?php esc_html_e('Revert from Affiliate Program', 'uap');?></button>
			</div>
		<?php else:?>
			<button type="button" class="button button-secondary" onclick="uapMakeUserAffiliate(<?php echo esc_attr($data['id']);?>);"><?php esc_html_e('Convert to Affiliate', 'uap');?></button>
		<?php endif?>
	</div>
</div>
