<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=gifts');?>"><?php esc_html_e('Settings', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=generated-gift-code');?>"><?php esc_html_e('Generated Membership Gift Codes', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
if ( !empty( $_POST['ihc_save'] ) && isset( $_POST['ihc_gifts_enabled'] ) ){
		if ( empty( $_POST['ihc_gifts_enabled'] ) ){
				\Ihc_Db::deactivateApTab( 'membeship_gifts' );
		} else {
				\Ihc_Db::activateApTab( 'membeship_gifts' );
		}
}
if ( isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
		ihc_save_update_metas('gifts');//save update metas
}

$data = ihc_return_meta_arr('gifts');
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-wrapper">
	<form  method="post">

		<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

		<div class="ihc-stuffbox">
			<h3 class="ihc-h3"><?php esc_html_e('Membership Gifts', 'ihc');?></h3>
			<div class="inside">

				<div class="iump-form-line">
					<h2><?php esc_html_e('Activate/Hold Membership Gifts', 'ihc');?></h2>
					<p><?php esc_html_e('Allow your customers to buy Memberships as gifts which can be then sent to other users or used by themselves.', 'ihc');?></p>
					<label class="iump_label_shiwtch ihc-switch-button-margin">
						<?php $checked = empty($data['ihc_gifts_enabled']) ? '' : 'checked';?>
						<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_gifts_enabled');" <?php echo esc_attr($checked);?> />
						<div class="switch ihc-display-inline"></div>
					</label>
					<input type="hidden" name="ihc_gifts_enabled" value="<?php echo esc_attr($data['ihc_gifts_enabled']);?>" id="ihc_gifts_enabled" />
				</div>

				<div class="iump-form-line">
					<h2><?php esc_html_e('Additional Settings', 'ihc');?></h2>
					<br/>
					<h5><?php esc_html_e('Give User Gift on every recurring membership payment', 'ihc');?></h5>
					<label class="iump_label_shiwtch ihc-switch-button-margin">
						<?php $checked = empty($data['ihc_gifts_user_get_multiple_on_recurring']) ? '' : 'checked';?>
						<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_gifts_user_get_multiple_on_recurring');" <?php echo esc_attr($checked);?> />
						<div class="switch ihc-display-inline"></div>
					</label>
					<input type="hidden" name="ihc_gifts_user_get_multiple_on_recurring" value="<?php echo esc_attr($data['ihc_gifts_user_get_multiple_on_recurring']);?>" id="ihc_gifts_user_get_multiple_on_recurring" />
				</div>

				<div class="ihc-wrapp-submit-bttn ihc-submit-form">
					<input id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>

			</div>
		</div>
	</form>
<?php
if ( !empty($_POST['ihc_save_gift']) && isset($_POST['ihc_admin_nonce_extension'] )
	&& wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' )
	&& isset( $_POST['discount_value'] ) && $_POST['discount_value'] !== '' ){
	Ihc_Db::gifts_do_save( indeed_sanitize_array($_POST) );
} else if (isset($_GET['do_delete'])){
	Ihc_Db::gifts_do_delete( sanitize_text_field($_GET['do_delete']) );
}
$data = Ihc_Db::gift_get_all_items();
$levels = \Indeed\Ihc\Db\Memberships::getAll();
$levels[-1]['label'] = esc_html__('All', 'ihc');
$currency = get_option('ihc_currency');
?>
<div  class="iump-form-line">
	<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=add_new_gift');?>" class="indeed-add-new-like-wp"><i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add new Gift Offer', 'ihc');?></a>
</div>

<?php if (!empty($data)):?>
	<div>
		<table class="wp-list-table widefat fixed tags ihc-admin-tables">
			<thead>
				<tr>
					<th class="manage-column"><?php esc_html_e('Awarded Membership', 'ihc');?></th>
					<th class="manage-column"><?php esc_html_e('Discount Value', 'ihc');?></th>
					<th class="manage-column"><?php esc_html_e('Target Membership', 'ihc');?></th>
					<th class="manage-column"><?php esc_html_e('Action', 'ihc');?></th>
				</tr>
			</thead>
			<?php  $i = 1;
				foreach ($data as $id => $array):?>
				<tr class="<?php if($i%2==0){
					 echo esc_attr('alternate');
				}
				?>">
					<td><strong><?php
						$l = $array['lid'];
						if (isset($levels[$l]) && isset($levels[$l]['label'])){
							echo esc_html($levels[$l]['label']);
						}
					?></strong></td>
					<td>
						<?php
							if ($array['discount_type']=='price'){
								echo esc_html(ihc_format_price_and_currency($currency, $array['discount_value']));
							} else {
								echo esc_html($array['discount_value']) . '%';
							}
						?>
					</td>
					<td>
						<div class="level-type-list ">
						<?php
						$l = $array['target_level'];
						if (isset($levels[$l]) && isset($levels[$l]['label'])){
							echo esc_html($levels[$l]['label']);
						}
						?>
						</div>
					</td>
					<td>
						<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=add_new_gift&id=' . $id);?>"><?php esc_html_e('Edit', 'ihc');?></a> |
						<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=gifts&do_delete=' . $id);?>"><?php esc_html_e('Delete', 'ihc');?></a>
					</td>
				</tr>
			<?php
			$i++;
			endforeach;?>
		</table>
	</div>
<?php endif;?>
<div class="ihc-clear"></div>

</div>

<?php
