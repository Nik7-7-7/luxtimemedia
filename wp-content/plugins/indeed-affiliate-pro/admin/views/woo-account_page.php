<div class="uap-wrapper">
	<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('WooCommerce Account Page', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/woocommerce-buddypress-account-page-integration/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-10">
					<h2><?php esc_html_e('Activate/Hold Affiliate Section', 'uap');?></h2>
					<p><?php esc_html_e('Fully integrate a user "Affiliate Account" in their "WooCommerce MyAccount". Once activated, a new tab in their " Woo MyAccount" menu will show up.', 'uap');?></p>
					<label class="uap_woo_account_page_enable uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_woo_account_page_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_woo_account_page_enable');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_woo_account_page_enable" value="<?php echo esc_attr($data['metas']['uap_woo_account_page_enable']);?>" id="uap_woo_account_page_enable" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-5">
					<div class="input-group input-group-extra-margin">
						<span class="input-group-addon" ><?php esc_html_e('Menu Label', 'uap');?></span>
						<input type="text" class="form-control" name="uap_woo_account_page_name" value="<?php echo esc_attr($data['metas']['uap_woo_account_page_name']);?>" />
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-5">
					<div class="input-group">
						<span class="input-group-addon" ><?php esc_html_e('Menu Position', 'uap');?></span>
						<input type="number" class="form-control" name="uap_woo_account_page_menu_position" value="<?php echo esc_attr($data['metas']['uap_woo_account_page_menu_position']);?>" min=1 />
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-7">
					<h2><?php esc_html_e('Non-Affiliate Users', 'uap');?></h2>
					<p><?php esc_html_e('Even the non-affiliate users will see the new option but instead of "Affiliate Account Page", custom content will show up.', 'uap');?></p>
					<label class="woo_account_page_enable uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_woo_account_page_show_to_everyone']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_woo_account_page_show_to_everyone');" <?php echo esc_attr($checked);?> />
					<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_woo_account_page_show_to_everyone" value="<?php echo esc_attr($data['metas']['uap_woo_account_page_show_to_everyone']);?>" id="uap_woo_account_page_show_to_everyone" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12">
					<h4><?php esc_html_e('Custom Content for Non-Affiliate Users:', 'uap');?></h4>
					<div class="uap-wp_editor uap-wp-editor-module">
					<?php wp_editor(stripslashes($data['metas']['uap_woo_account_page_non_affiliate_content']), 'uap_woo_account_page_non_affiliate_content', array('textarea_name'=>'uap_woo_account_page_non_affiliate_content', 'editor_height'=>200));?>
					</div>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
</form>
</div>
