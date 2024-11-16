<?php if ( $data['show_button'] ):?>
<div class="uap-become-affiliate-wrapp">
	<button class="uap-become-affiliate-bttn" onclick="uapBecomeAffiliatePublic();"><?php esc_html_e('Become an Affiliate', 'uap');?></button>
</div>
<?php else :?>
		<?php if ( $data['warning_message'] ):?>
				<p><?php echo esc_uap_content($data['warning_message']);?></p>
		<?php endif;?>
<?php endif;?>
