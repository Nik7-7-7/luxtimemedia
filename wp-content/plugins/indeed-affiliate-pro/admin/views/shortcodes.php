	<div class="uap-stuffbox">
		<h3 class="uap-h3">
			<?php esc_html_e('Main ShortCodes', 'uap');?>
		</h3>
		<div class="inside">
			<div class="uap-popup-content help-shortcodes uap-text-align-center">
        	<div class="uap-showcases-wrapper">
	            <div class="uap-popup-shortcodevalue"><i class="fa-uap fa-user-plus-uap"></i><?php esc_html_e('Register Form', 'uap');?><span>[uap-register]</span></div>
	            <div class="uap-popup-shortcodevalue"><i class="fa-uap fa-sign-in-uap"></i><?php esc_html_e('Login Form', 'uap');?><span>[uap-login-form]</span></div>
	            <div class="uap-popup-shortcodevalue"><i class="fa-uap fa-sign-out-uap"></i><?php esc_html_e('Logout Button', 'uap');?><span>[uap-logout]</span></div>
	            <div class="uap-popup-shortcodevalue"><i class="fa-uap fa-unlock-uap"></i><?php esc_html_e('Password Recovery', 'uap');?><span>[uap-reset-password]</span></div>
	            <div class="uap-popup-shortcodevalue"><i class="fa-uap fa-user-uap"></i><?php esc_html_e('Affiliate Portal', 'uap');?><span>[uap-account-page]</span></div>
				<div class="uap-clear"></div>
        	</div>
    	</div>
			<div class="clear"></div>
		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3">
			<?php esc_html_e('Affiliate Data ShortCodes', 'uap');?>
		</h3>
		<div class="inside">
			<div class="uap-popup-content help-shortcodes">
				<table class="wp-list-table widefat fixed tags uap-manage-user-expire uap-shortcodes-table">
				<thead>
					<tr>
						<th><?php esc_html_e('Affiliate Field', 'uap');?></th>
						<th><?php esc_html_e('Current Logged Affiliate Shortcode', 'uap');?></th>
						<th><?php esc_html_e('Based on Affiliate Link Shortcode', 'uap');?></th>
					</tr>
				</thead>
				<tbody>
		       	<?php
				$constants = array(	"username",
									"first_name",
									"last_name",
									"user_id",
									"affiliate_id",
									"user_email",
									"account_page",
									"login_page",
									"blogname",
									"blogurl",
									"siteurl",
									'rank_id',
									'rank_name',
									'user_url,',
									'uap_avatar',
				);
		       	foreach ($constants as $k=>$v){
		       		?>
					<tr>
						<td><?php echo esc_html($v);?></td>
						<td>[uap-affiliate field="<?php echo esc_html($v);?>"]</td>
		       			<td>[uap-public-affiliate-info field="<?php echo esc_html($v);?>"]</td>
					</tr>
		       		<?php
		       	}
		       	$custom_fields = uap_get_custom_constant_fields();
		       	foreach ($custom_fields as $k=>$v){
		       		$k = str_replace('{', '', $k);
		       		$k = str_replace('}', '', $k);
		       		?>
		       			<tr>
		       				<td><?php echo esc_html($v);?></td>
		       				<td>[uap-affiliate field="<?php echo esc_html($k);?>"]</td>
		       				<td>[uap-public-affiliate-info field="<?php echo esc_html($k);?>"]</td>
		       			</tr>
		       		<?php
		       	}
		       	?>
		       	</tbody></table>
	    	</div>
			<div class="uap-clear"></div>
		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3">
			<?php esc_html_e('Additional ShortCodes', 'uap');?>
		</h3>
		<div class="inside">
			<div class="uap-popup-content help-shortcodes">
            <table class="wp-list-table widefat fixed tags uap-manage-user-expire uap-shortcodes-table">
				<thead>
					<tr>
						<th><?php esc_html_e('ShortCode', 'uap');?></th>
						<th><?php esc_html_e('What it does', 'uap');?></th>
						<th><?php esc_html_e('Arguments available', 'uap');?></th>
					</tr>
				</thead>
				<tbody>
                		<tr>
		       				<td><strong>[uap-user-become-affiliate]</strong></td>
		       				<td><?php esc_html_e('User Become Affiliate Button', 'uap');?></td>
		       				<td>-</td>
		       			</tr>
                        <tr>
		       				<td><strong>[if_affiliate]<i><?php esc_html_e('Your content here!', 'uap');?> </i>[/if_affiliate]</strong></td>
		       				<td><?php esc_html_e('Show content only for affiliate users.', 'uap');?></td>
		       				<td>-</td>
		       			</tr>
                        <tr>
		       				<td><strong>[if_not_affiliate]<i><?php esc_html_e('Your content here!', 'uap');?> </i>[/if_not_affiliate]</strong></td>
		       				<td><?php esc_html_e('Show content only for non-affiliate users.', 'uap');?></td>
		       				<td>-</td>
		       			</tr>
                         <tr>
		       				<td><strong>[visitor_referred]<i><?php esc_html_e('Your content here!', 'uap');?> </i>[/visitor_referred]</strong></td>
		       				<td><?php esc_html_e('Show content only for referred users.', 'uap');?></td>
		       				<td>-</td>
		       			</tr>
                        <tr>
		       				<td><strong>[visitor_not_referred]<i><?php esc_html_e('Your content here!', 'uap');?> </i>[/visitor_not_referred]</strong></td>
		       				<td><?php esc_html_e('Show content only for non-referred users.', 'uap');?></td>
		       				<td>-</td>
		       			</tr>
                </tbody>
            </table>

    	</div>
			<div class="clear"></div>
		</div>
	</div>
