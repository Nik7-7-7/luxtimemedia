<?php
$levels = \Indeed\Ihc\Db\Memberships::getAll();
//ihc_save_update_metas('level_restrict_payment');//save update metas
if (!empty($_POST['ihc_save'])){
	update_option('ihc_level_subscription_plan_settings_enabled', sanitize_text_field($_POST['ihc_level_subscription_plan_settings_enabled']) );
	//update_option('ihc_show_renew_link', sanitize_text_field($_POST['ihc_show_renew_link']) );
	//update_option('ihc_show_delete_link', sanitize_text_field($_POST['ihc_show_delete_link']) );
	/// RESTRICT LEVELs
	$restrict_arr = array();
	$conditions = array();
	if (isset($levels) && is_array($levels) && count($levels)>0 && $levels !== false){
			foreach ($levels as $id=>$level){
				$restrict_arr[$id] = (isset($_POST['ihc_level_subscription_plan_settings_restr_levels'][$id])) ? sanitize_text_field($_POST['ihc_level_subscription_plan_settings_restr_levels'][$id]) : '';
				$conditions[$id] = (isset($_POST['ihc_level_subscription_plan_settings_condt'][$id])) ? sanitize_text_field($_POST['ihc_level_subscription_plan_settings_condt'][$id]) : '';
			}
}
	update_option('ihc_level_subscription_plan_settings_restr_levels', $restrict_arr);
	update_option('ihc_level_subscription_plan_settings_condt', $conditions);
}
$data['metas'] = ihc_return_meta_arr('level_subscription_plan_settings');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form method="post">
	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Memberships Plus', 'ihc');?></h3>
		<div class="inside">

			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Memberships Plus', 'ihc');?></h2>
				<p><?php esc_html_e('Restrict the purchase of a membership if the user has not made any purchases yet, or has already purchased certain memberships.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_level_subscription_plan_settings_enabled']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_level_subscription_plan_settings_enabled');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<p><?php esc_html_e('Ex: We have memberships A,B,C. A user cannot purchase membership C if they already
have membership A and B. Another example would be: a user cannot purchase membership B if they never purchased a membership before.
				', 'ihc');?></p>
				<input type="hidden" name="ihc_level_subscription_plan_settings_enabled" value="<?php echo esc_attr($data['metas']['ihc_level_subscription_plan_settings_enabled']);?>" id="ihc_level_subscription_plan_settings_enabled" />
				<p><strong><?php esc_html_e('Important: In order to manage a membership with this filter, the Show up in Subscription Plan setting must be turned on. To turn this on for a membership that has already been created, go to ', 'ihc');?> <a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=levels');?>" target="_blank"><?php esc_html_e(' Memberships ', 'ihc');?></a><?php esc_html_e(' and check if the option is enabled.', 'ihc');?></strong></p>

			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

	<?php if ($levels):?>
		<?php foreach ($levels as $id=>$level):?>
			<div class="ihc-stuffbox">

					<h3 class="ihc-h3"><?php echo esc_html__('Membership: ', 'ihc') . $level['label'];?></h3>
				<div class="inside">
					<div class="iump-form-line">
						<label><?php esc_html_e('Activate Restriction for this membership', 'ihc');?></label>
						<div>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = (empty($data['metas']['ihc_level_subscription_plan_settings_restr_levels'][$id])) ? '' : 'checked';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '<?php echo esc_attr('#ihc_level_subscription_plan_settings_restr_levels'.$id);?>');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<?php $hidden_value = (empty($data['metas']['ihc_level_subscription_plan_settings_restr_levels'][$id])) ? 0 : $data['metas']['ihc_level_subscription_plan_settings_restr_levels'][$id];?>
							<input type="hidden" name="ihc_level_subscription_plan_settings_restr_levels[<?php echo esc_attr($id);?>]" value="<?php echo esc_attr($hidden_value);?>" id="<?php echo esc_attr('ihc_level_subscription_plan_settings_restr_levels'.$id);?>" />
						</div>
					</div>

					<?php
						$hidden_value = (empty($data['metas']['ihc_level_subscription_plan_settings_condt'][$id])) ? '' : $data['metas']['ihc_level_subscription_plan_settings_condt'][$id];
						$hidden_arr = array();
						if (!empty($hidden_value)){
							$hidden_arr = explode(',', $hidden_value);
						}
					?>
					<div class="iump-form-line">
						<h4><?php esc_html_e('User never bought:', 'ihc');?></h4>
						<div>
							<?php $checked = (in_array('unreg', $hidden_arr)) ? 'checked' : '';?>
							<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, 'unreg', '<?php echo esc_attr('#level' . $id . 'cond');?>');" /><span> <?php esc_html_e('UnRegistered Users', 'ihc');?></span>
						</div>
						<div>
							<?php $checked = (in_array('no_pay', $hidden_arr)) ? 'checked' : '';?>
							<input type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, 'no_pay', '<?php echo esc_attr('#level' . $id . 'cond');?>');" /><span> <?php esc_html_e('Registered Users with no payment made', 'ihc');?></span>
						</div>
						<h4><?php esc_html_e('User already bought: ', 'ihc');?></h4>
						<?php foreach ($levels as $lid=>$larr):?>
							<?php $spanclass = ($lid==$id) ? 'ihc-magic-feat-bold-span' : '';?>
							<div class="ihc-list-access-posts-memberships">
								<?php $checked = (in_array($lid, $hidden_arr)) ? 'checked' : '';?>
								<input class="form-check-input" type="checkbox" <?php echo esc_attr($checked);?> onClick="ihcMakeInputhString(this, '<?php echo esc_attr($lid);?>', '<?php echo esc_attr('#level' . $id . 'cond');?>');" /> <span  class="form-check-label <?php echo esc_attr($spanclass);?>"><?php echo esc_attr($larr['label']);?></span>
							</div>
						<?php endforeach;?>
						<input type="hidden" name="ihc_level_subscription_plan_settings_condt[<?php echo esc_attr($id);?>]" id="<?php echo esc_attr('level' . $id . 'cond');?>" value="<?php echo esc_attr($hidden_value);?>" />
					</div>

					<div class="ihc-wrapp-submit-bttn ihc-submit-form">
						<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
					</div>
				</div>
			</div>
		<?php endforeach;?>
	<?php endif;?>

</form>
