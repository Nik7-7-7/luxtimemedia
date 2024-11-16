<p class="<?php echo esc_attr($data['class']);?> uap-fair-checkout-reward-wrapper">
	<?php if (self::$checkout_referrals_select_settings['uap_checkout_select_referral_label']):?>
		<label><?php echo esc_uap_content($data['require'] . self::$checkout_referrals_select_settings['uap_checkout_select_referral_label']);?></label>
	<?php endif;?>
		<?php if (self::$checkout_referrals_select_settings['uap_checkout_select_referral_s_type']==1):?>
			<select name="uap_affiliate_username" class="<?php echo esc_attr($data['select_class']);?>" <?php echo esc_attr($data['require_on_input']);?> >
				<option value="">...</option>
				<?php
				foreach ($data['affiliates'] as $id => $label):
					?>
					<option value="<?php echo esc_attr($id);?>" ><?php echo esc_html($label);?></option>
					<?php
				endforeach;
			?></select>
		<?php else :?>
			<input type="text" value="" name="uap_affiliate_username_text" class="<?php echo esc_attr($data['input_class']);?>" <?php echo esc_attr($data['require_on_input']);?> onBlur="uapAffiliateUsernameTest(this.value);" id="uap_affiliate_username_text"/>
		<?php endif;?>
</p>
