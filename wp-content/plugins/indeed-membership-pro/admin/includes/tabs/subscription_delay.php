<?php
$levels = \Indeed\Ihc\Db\Memberships::getAll();
//ihc_save_update_metas('level_restrict_payment');//save update metas
if (!empty($_POST['ihc_save']) && isset($_POST['ihc_admin_nonce_extension'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ihc_admin_nonce_extension']), 'ihc_admin_nonce_extension' ) ){
	update_option('ihc_subscription_delay_on', sanitize_text_field($_POST['ihc_subscription_delay_on']));

	if (isset($_POST['ihc_subscription_delay_time'])){
		$ihc_subscription_delay_time = array();
		foreach ($levels as $id=>$leveldata){
			$ihc_subscription_delay_time[$id] = (isset($_POST['ihc_subscription_delay_time'][$id])) ? sanitize_text_field($_POST['ihc_subscription_delay_time'][$id]) : '';
		}
		update_option('ihc_subscription_delay_time', $ihc_subscription_delay_time);
	}

	if (isset($_POST['ihc_subscription_delay_type'])){
		$ihc_subscription_delay_type = array();
		foreach ($levels as $id=>$leveldata){
			$ihc_subscription_delay_type[$id] = (isset($_POST['ihc_subscription_delay_type'][$id])) ? sanitize_text_field($_POST['ihc_subscription_delay_type'][$id]) : '';
		}
		update_option('ihc_subscription_delay_type', $ihc_subscription_delay_type);
	}

}
$data['metas'] = ihc_return_meta_arr('subscription_delay');//getting metas
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<form  method="post">

	<input type="hidden" name="ihc_admin_nonce_extension" value="<?php echo wp_create_nonce( 'ihc_admin_nonce_extension' );?>" />

	<div class="ihc-stuffbox">
		<h3 class="ihc-h3"><?php esc_html_e('Subscription Delay', 'ihc');?></h3>

		<div class="inside">
			<div class="iump-form-line">
				<h2><?php esc_html_e('Activate/Hold Subscription Delay', 'ihc');?></h2>
				<p><?php esc_html_e('Each membership will become active after a custom delay time instead of when it was assigned. This option is available only when the membership is assigned for the first time and not when user renews the subscription.', 'ihc');?></p>
				<label class="iump_label_shiwtch ihc-switch-button-margin">
					<?php $checked = ($data['metas']['ihc_subscription_delay_on']) ? 'checked' : '';?>
					<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#ihc_subscription_delay_on');" <?php echo esc_attr($checked);?> />
					<div class="switch ihc-display-inline"></div>
				</label>
				<input type="hidden" name="ihc_subscription_delay_on" value="<?php echo esc_attr($data['metas']['ihc_subscription_delay_on']);?>" id="ihc_subscription_delay_on" />
			</div>

			<div class="ihc-wrapp-submit-bttn ihc-submit-form">
				<input  id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
			</div>

		</div>
	</div>

	<?php if ($levels):?>
		<div class="ihc-stuffbox">
			<h3 class="ihc-h3"><?php esc_html_e('Delay Time', 'ihc');?></h3>
			<div class="inside">
				<h4><?php esc_html_e('Memberships', 'ihc');?></h4>
				<div class="iump-form-line">
					<?php foreach ($levels as $id=>$level):?>
						<?php $value = (isset($data['metas']['ihc_subscription_delay_time'][$id])) ? $data['metas']['ihc_subscription_delay_time'][$id] : '';?>
						<div class="row">
						<div class="col-xs-4">
						   <div class="input-group">
							<span class="input-group-addon" id="basic-addon1"><?php echo esc_html($level['label']);?></span>
							<input type="number" min="0" class="form-control" value="<?php echo esc_attr($value);?>" name="ihc_subscription_delay_time[<?php echo esc_attr($id);?>]" />
						   </div>
						</div>
						<div class="col-xs-3 ihc-col-no-padding">
						</div>
						<div class="col-xs-4 ihc-col-no-padding">
							<select name="ihc_subscription_delay_type[<?php echo esc_attr($id);?>]" class="form-control"><?php
								$possible_values = array('h' =>esc_html__('Hours', 'ihc'), 'd' =>esc_html__('Days', 'ihc'));
								foreach ($possible_values as $type=>$label):
									$value = (isset($data['metas']['ihc_subscription_delay_type'][$id])) ? $data['metas']['ihc_subscription_delay_type'][$id] : '';
									$checked = ($value==$type) ? 'selected' : '';
									?>
								<option value="<?php echo esc_attr($type);?>" <?php echo esc_attr($checked);?> ><?php echo esc_html($label);?></option>
									<?php
								endforeach;
							?></select>
						</div>
					</div>
				  <?php endforeach;?>
				</div>
				<div class="ihc-wrapp-submit-bttn ihc-submit-form">
					<input  id="ihc_submit_bttn" type="submit" value="<?php esc_html_e('Save Changes', 'ihc');?>" name="ihc_save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
	<?php endif;?>
</form>
