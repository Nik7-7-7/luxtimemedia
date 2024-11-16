<?php
ihc_save_update_metas('user_sites');//save update metas
$data['metas'] = ihc_return_meta_arr('user_sites');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$levels = \Indeed\Ihc\Db\Memberships::getAll();
?>
<div>
	<form  method="post">
		<div class="ihc-stuffbox">
			<h3 class="ihc-h3"><?php esc_html_e('WP MultiSite Subscriptions', 'ihc');?></h3>
			<div class="inside">
				<div class="iump-form-line">
					<h2><?php esc_html_e('Activate/Hold WP MultiSite Subscriptions', 'ihc');?></h2>
                    <p><?php esc_html_e('Provides SingleSites based on purchased subscriptions. You can sell SingleSites via memberships. Once a user buys a specific membership he will be able to create his own SingleSite. The user will be set as administrator for that site. ', 'ihc');?></p>

					<label class="iump_label_shiwtch ihc-switch-button-margin">
						<?php $checked = ($data['metas']['ihc_user_sites_enabled']) ? 'checked' : '';?>
						<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_user_sites_enabled');" <?php echo esc_attr($checked);?> />
						<div class="switch ihc-display-inline"></div>
					</label>
					<input type="hidden" name="ihc_user_sites_enabled" value="<?php echo esc_attr($data['metas']['ihc_user_sites_enabled']);?>" id="ihc_user_sites_enabled" />
                    <p><strong><?php esc_html_e('If a user has multiple memberships that allow him to create a SingleSite, he can create one SingleSite for each membership. If a membership is about to expire, the SingleSite assigned to it will be deactivated. It will be activated again when the membership is also active.', 'ihc');?></strong></p>
				</div>
				<div class="ihc-wrapp-submit-bttn ihc-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
<?php if(!empty($levels)){ ?>
		<div class="ihc-stuffbox">
			<h3 class="ihc-h3"><?php esc_html_e('Memberships vs SingleSites', 'ihc');?></h3>
			<div class="inside">
                <div class="iump-form-line">
                <p><?php esc_html_e('Set which Memberships will provide a SingleSite to buyers.', 'ihc');?></p>
                <h2><?php esc_html_e('Enable Memberships:', 'ihc');?></p></h2>

				<?php foreach ($levels as $lid=>$level_data):?>



						<label class="iump_label_shiwtch ihc-switch-button-margin">
							<?php
								if (empty($data['metas']['ihc_user_sites_levels'][$lid])){
									$checked = '';
									$value = 0;
								} else {
									$checked = 'checked';
									$value = 1;
								}
							?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '<?php echo esc_ump_content('#ihc_lid_') . esc_attr($lid);?>');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
								<input type="hidden" name="ihc_user_sites_levels[<?php echo esc_attr($lid);?>]" value="<?php echo esc_attr($value);?>" id="<?php echo esc_attr( 'ihc_lid_' . $lid );?>" />
						</label>

						<label class="iump-labels-onbutton"><?php echo esc_html($level_data['name']);?></label><br>
				<?php endforeach;?>
				</div>
                <div class="ihc-wrapp-submit-bttn ihc-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
	<?php } ?>	
	</form>
</div>
