<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-wrapper">
<?php
$notifications = new \Indeed\Ihc\Notifications();
$notification_arr = $notifications->getAllNotificationNames();

if (isset($_GET['edit_notification']) || isset($_GET['add_notification'])){
	//add/edit

	$notification_id = (isset($_GET['edit_notification'])) ? sanitize_text_field($_GET['edit_notification']) : FALSE;
	$meta_arr = ihc_get_notification_metas($notification_id);


$meta_arr['message'] = stripslashes( htmlspecialchars_decode( $meta_arr['message'] )  );
	?>
	<form method="post" action="<?php echo esc_url($url.'&tab=notifications');?>">

		<input type="hidden" value="<?php echo wp_create_nonce( 'ihc_admin_notifications_nonce' );?>" name="ihc_admin_notifications_nonce" />
		<?php
			if ($notification_id){
				?>
				<input type="hidden" name="notification_id" value="<?php echo esc_attr($notification_id);?>" />
				<?php
			}
		?>
		<div class="ihc-stuffbox">
			<h3><?php esc_html_e('Add new Email Notification', 'ihc');?></h3>
			<div class="inside">
				<div class="iump-form-line iump-no-border">
					<h2><?php esc_html_e('Email Notification Action', 'ihc');?></h2>
					<select name="notification_type" id="notification_type" class="ump-js-change-notification-type iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
						<?php
							foreach ($notification_arr as $k=>$v){
								//Manually set optGroups
								switch($k){
									case 'admin_user_register':
											echo esc_ump_content(' <optgroup label="' . esc_html__('-----Admininistrator Notifications-----', 'ihc') . '"> </optgroup>');
											echo esc_ump_content(' <optgroup label="' . esc_html__('Register Process', 'ihc') . '">');
										break;
									case 'ihc_new_subscription_assign_notification-admin':
													echo esc_ump_content(' <optgroup label="Subscriptions">');
													break;
									case 'ihc_order_placed_notification-admin':
													echo esc_ump_content(' <optgroup label="Payments">');
													break;
									case 'admin_user_profile_update':
													echo esc_ump_content(' <optgroup label="Customer Actions">');
													break;


									case 'register':
												  echo esc_ump_content(' <optgroup label="' . esc_html__('---------Member Notifications----------', 'ihc') . '"> </optgroup>');
													echo esc_ump_content(' <optgroup label="Register Process">');
													break;
									case 'register_lite_send_pass_to_user':
													echo esc_ump_content(' <optgroup label="Register Lite">');
													break;
									case 'email_check':
													echo esc_ump_content(' <optgroup label="Double Email Verification">');
													break;
									case 'reset_password_process':
													echo esc_ump_content(' <optgroup label="Reset Password Process">');
													break;
									case 'approve_account':
													echo esc_ump_content(' <optgroup label="Customer Account">');
													break;
									case 'user_update':
													echo esc_ump_content(' <optgroup label="Customer Actions">');
													break;
									case 'ihc_new_subscription_assign_notification':
													echo esc_ump_content(' <optgroup label="Subscriptions">');
													break;
									case 'ihc_order_placed_notification-user':
													echo esc_ump_content(' <optgroup label="Payments">');
													break;
									case 'drip_content-user':
										echo esc_ump_content(' <optgroup label="Drip Content">');
										break;
								}
								?>
								<option value="<?php echo esc_attr($k);?>" <?php echo ($meta_arr['notification_type']==$k) ? 'selected' : ''; ?>><?php echo esc_html($v);?></option>
								<?php
								switch($k){
									case 'admin_user_register':
									case 'admin_user_expire_level':
									case 'admin_before_subscription_payment_due':
									case 'ihc_delete_subscription_notification-admin':
										echo esc_ump_content(' </optgroup>');
										break;

									case 'review_request':
									case 'register_lite_send_pass_to_user':
									case 'email_check_success':
									case 'change_password':
									case 'delete_account':
									case 'ihc_delete_subscription_notification':
									case 'expire':
									case 'upcoming_card_expiry_reminder':
									case 'drip_content-user':
										echo esc_ump_content(' </optgroup>');
										break;
								}
							}
							do_action( 'ihc_admin_notification_type_select_field', $meta_arr['notification_type'] );
						?>
					</select>
					<?php
							$notificationObject = new \Indeed\Ihc\Notifications();
							$notificationPattern = $notificationObject->getNotificationTemplate( $meta_arr['notification_type'] );
							$explanation = isset( $notificationPattern['explanation'] ) ? $notificationPattern['explanation'] : '';
					?>
					<div id="ihc_notification_explanation"><?php echo esc_html($explanation);?></div>
				</div>
				<div class="iump-special-line">
					<h2><?php esc_html_e('Choose Membership Target', 'ihc');?></h2>

					<select name="level_id" class="iump-form-select ihc-form-element ihc-form-element-select ihc-form-select">
						<option value="-1" <?php echo ($meta_arr['level_id']==-1) ? 'selected' : ''; ?>><?php esc_html_e( 'All', 'ihc' );?></option>
						<?php
						$levels = \Indeed\Ihc\Db\Memberships::getAll();
						if ($levels && count($levels)){
							foreach ($levels as $k=>$v){
								?>
									<option value="<?php echo esc_attr($k);?>" <?php echo ($meta_arr['level_id']==$k) ? 'selected' : ''; ?>><?php echo esc_html($v['name']);?></option>
								<?php
							}
						}
						?>
					</select>
					<div class="ihc-notification-edit-available"><?php
						echo esc_html__('Available only for:', 'ihc')
							. ', ' . $notification_arr['register']
							. ', ' . $notification_arr['review_request']
							. ', ' . $notification_arr['before_expire']
							. ', ' . $notification_arr['expire']
							. ', ' . $notification_arr['payment']
							. ', ' . $notification_arr['bank_transfer']
							. ', ' . $notification_arr['admin_user_register']
							. ', ' . $notification_arr['admin_user_expire_level']
							. ', ' . $notification_arr['admin_before_user_expire_level']
							. ', ' . $notification_arr['admin_user_payment']
							. '.';
					;?></div>
				</div>
				<div class="iump-form-line">
					<h2 class="ihc-notification-edit-headline"><?php esc_html_e('Email Subject', 'ihc');?></h2>
					<input type="text" class="ihc-edit-notification-subject iump-form-select ihc-form-element ihc-form-element-select ihc-form-select" name="subject" value="<?php echo esc_attr($meta_arr['subject']);?>" id="notification_subject" />
				</div>
				<div class="iump-form-line iump-no-border">
					<h2 class="ihc-notification-edit-headline"><?php esc_html_e('Message to be Sent', 'ihc');?></h2>
				</div>
				<div class="iump-form-line">
					<div class="ihc-notification-edit-message">
						<?php wp_editor( $meta_arr['message'], 'ihc_message', array('textarea_name'=>'message', 'quicktags'=>TRUE) );?>
					</div>
					<div class="ihc-notification-edit-constants">
							<h4><?php esc_html_e('Template Tags', 'ihc');?></h4>
							<?php	$constants = ihcNotificationConstants( $meta_arr['notification_type'] );?>
							<div class="ump-js-list-constants">
							<?php foreach ($constants as $k=>$v):?>
									<div class="iump-tag-wrap" id="iump_notifications_template_tags" ><span class="iump-tag-code" data-target_selector="ihc_message" ><?php echo esc_html($k);?></span></div>
							<?php endforeach;?>
							</div>
					</div>
					<div class="ihc-notification-edit-constants">
							<?php
							$extra_constants = ihc_get_custom_constant_fields();
							?><h4><?php esc_html_e('Custom Fields constants', 'ihc');?></h4><?php
							foreach ($extra_constants as $k=>$v){
								?>
								<div class="iump-tag-wrap"><span class="iump-tag-code" data-target_selector="ihc_message"><?php echo esc_html($k);?></span></div>
								<?php
							}
						?>
					</div>

					<div class="ihc-clear"></div>
				</div>

				<div class="ihc-stuffbox-submit-wrap iump-submit-form">
					<input type="submit"
					value="<?php if ($notification_id){
						esc_html_e('Save Changes', 'ihc');
					} else{
						esc_html_e('Save Changes', 'ihc');
					}?>
					" name="ihc_save" id="ihc_submit_bttn" class="button button-primary button-large ihc_submit_bttn">
				</div>
			</div>
		</div>
				<!-- PUSHOVER -->
				<?php if (ihc_is_magic_feat_active('pushover')):?>
				<div class="ihc-stuffbox ihc-stuffbox-magic-feat">
					<h3><?php esc_html_e('Pushover Mobile Notification', 'ihc');?></h3>
					<div class="inside">
						<div class="iump-form-line">
							<h2><?php esc_html_e('Send Pushover Mobile Notification', 'ihc');?></h2>
							<label class="iump_label_shiwtch ihc-switch-button-margin">
								<?php $checked = (empty($meta_arr['pushover_status'])) ? '' : 'checked';?>
								<input type="checkbox" class="iump-switch" onClick="iumpCheckAndH(this, '#pushover_status');" <?php echo esc_attr($checked);?> />
								<div class="switch ihc-display-inline"></div>
							</label>
							<input type="hidden" name="pushover_status" value="<?php echo (isset($meta_arr['pushover_status'])) ? $meta_arr['pushover_status'] : '';?>" id="pushover_status" />
						</div>

						<div class="iump-form-line">
							<h2><?php esc_html_e('Pushover Message:', 'ihc');?></h2>
							<textarea name="pushover_message" class="ihc-notification-edit-pushmessage" onBlur="ihcCheckFieldLimit(1024, this);"><?php echo stripslashes((isset($meta_arr['pushover_message'])) ? esc_html( $meta_arr['pushover_message'] ) : '');?></textarea>
							<div><?php esc_html_e('Only Plain Text and up to ', 'ihc');?><span>1024</span><?php esc_html_e(' characters are available!', 'ihc');?></div>
						</div>
						<div class="ihc-stuffbox-submit-wrap iump-submit-form">
							<input type="submit"
							value="
							<?php if ($notification_id){
								esc_html_e('Save Changes', 'ihc');
							} else{
								esc_html_e('Save Changes', 'ihc');
							}
							?>
							" name="ihc_save" id="ihc_submit_bttn" class="button button-primary button-large ihc_submit_bttn">
						</div>
					</div>
				</div>
				<?php else :?>
					<input type="hidden" name="pushover_message" value=""/>
					<input type="hidden" name="pushover_status" value=""/>
				<?php endif;?>
				<!-- PUSHOVER -->


	</form>

<?php
} else {
	//listing
			$notificationObject = new \Indeed\Ihc\Notifications();
			if (isset($_POST['ihc_save']) && !empty($_POST['ihc_admin_notifications_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_notifications_nonce']), 'ihc_admin_notifications_nonce' ) ){
				$notificationObject->save(indeed_sanitize_textarea_array($_POST));
			} else if (isset($_POST['delete_notification_by_id']) && !empty($_POST['ihc_admin_notifications_nonce']) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_notifications_nonce']), 'ihc_admin_notifications_nonce' ) ){
				$notificationObject->deleteOne( sanitize_text_field($_POST['delete_notification_by_id']) );
			}

			$tableDataType = 'notifications';
			$columns = [
				[
						'data'        => 'subject',
						'orderable'   => false,
						'sortable'		=> false,
				],
				[
						'data'        => 'status',
						'orderable'   => false,
						'sortable'		=> false,
				],
				[
						'data'        => 'action',
						'orderable'   => false,
						'sortable'		=> false,
				],
				[
						'data'      => 'goes_to',
						'orderable'   => false,
						'sortable'		=> false,
				],
				[
						'data'      => 'runtime',
						'orderable'   => false,
						'sortable'		=> false,
				],
				[
						'data'      => 'membership_target',
						'orderable' => false
				],
				[
						'data'      => 'pushover',
						'orderable' => false
				],
				[
						'data'        => 'options_act',
						'orderable'   => false,
						'sortable'		=> false,
				]
			];

			$pushoverOn = ihc_is_magic_feat_active('pushover');
			if ( !$pushoverOn ){
					unset( $columns[6] );
			}
			$columns = array_values( $columns );

			$labels = [
					'search'				=> esc_html__( "Search&nbsp;:", 'ihc'),
					'lengthMenu'		=> esc_html__( "Show _MENU_ entries", 'ihc'),
					'info'					=> esc_html__( "Showing _START_ to _END_ of _TOTAL_ entries", 'ihc'),
					'infoEmpty'			=> esc_html__( "No results available", 'ihc'),
					'infoFiltered'	=> esc_html__( "", 'ihc'),
					'loadingRecords'=> esc_html__( "Loading", 'ihc'),
					'zeroRecords'		=> esc_html__( "No results available", 'ihc'),
					'emptyTable'		=> esc_html__( "No results available", 'ihc'),
					'paginate'			=> [
								'first'					=> esc_html__( "First", 'ihc'),
								'previous'			=> esc_html__( "Previous", 'ihc'),
								'next'					=> esc_html__( "Next", 'ihc'),
								'last'					=> esc_html__( "Last", 'ihc'),
					],
					'aria'					=> [
								'sortAscending'		=> esc_html__( "Ascending", 'ihc'),
								'sortDescending'	=> esc_html__( "Descending", 'ihc'),
					],
					'searchPlaceholder'			=> esc_html__( "Search", 'ihc'),
					'show_hide_cols_label'	=> esc_html__( "Show / Hide columns", 'ihc'),
			];

			wp_enqueue_style( 'ihcmultiselect', IHC_URL . 'admin/assets/css/jquery.multiselect.css');
			wp_enqueue_script( 'ihcmultiselectfunctions', IHC_URL . 'admin/assets/js/jquery.multiselect.js', ['jquery'], '12.7' );

			// css
			wp_enqueue_style( 'ihcdatabse', IHC_URL . 'admin/assets/css/datatables/datatables.min.css');
			wp_enqueue_style( 'ihcdatabse-buttons', IHC_URL . 'admin/assets/css/datatables/buttons.dataTables.min.css');

			// js
			wp_enqueue_script( 'ihcdatabse', IHC_URL . 'admin/assets/js/datatables/datatables.min.js', ['jquery'], '12.7' );
			wp_enqueue_script( 'ihcdatabse-buttons', IHC_URL . 'admin/assets/js/datatables/dataTables.buttons.min.js', ['jquery'], '12.7' );
			wp_enqueue_script( 'ihcdatabse-colvis', IHC_URL . 'admin/assets/js/datatables/buttons.colVis.min.js', ['jquery'], '12.7' );
			// iump datatable functions
			wp_register_script( 'ihc-table', IHC_URL . 'admin/assets/js/table.js', ['jquery'], '12.7' );
			// setting up the variables
			global $wp_version;
			if ( version_compare ( $wp_version , '5.7', '>=' ) ){
					wp_add_inline_script( 'ihc-table', "var iump_datatable_cols='" . json_encode( $columns ) . "';" );
					wp_add_inline_script( 'ihc-table', "var iump_datatable_labels='" . json_encode( $labels ) . "';" );
					wp_add_inline_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
			} else {
					wp_localize_script( 'ihc-table', 'iump_datatable_cols', json_encode( $columns ) );
					wp_localize_script( 'ihc-table', 'iump_datatable_labels', json_encode( $labels ) );
					wp_add_inline_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
			}
			wp_enqueue_script( 'ihc-table' );


		?>
		<!-- Page State -->
		<?php $pageState = get_option( 'ihc_datatable_state_for-notifications', false );?>
		<?php if ( $pageState !== false ):?>
			<div class="iump-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>'></div>
		<?php endif;?>
		<!-- End of Page State -->

		<div id="col-right" class="ihc-notification-list-wrapper">
		<div class="iump-page-headline"><?php esc_html_e('Manage Email Notifications', 'ihc');?></div>
		<div class="imup-page-top-options">
			<a href="<?php echo esc_url( $url .'&tab=notifications&add_notification=true');?>" class="indeed-add-new-like-wp"><i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add New Notification', 'ihc');?></a>
			<span class="ihc-top-message"><?php esc_html_e('...create your notification Templates!', 'ihc');?></span>
			<a href="javascript:void(0)" title="<?php esc_html_e('Let you know if your website is able to send emails independently of UMP settings. A test email should be received on Admin email address.', 'ihc');?>" class="button button-primary button-large ihc-remove-group-button ihc-notification-list-check" onClick="ihcCheckEmailServer();"><?php esc_html_e('Check SMTP Mail Server', 'ihc');?></a>
			<a class="button button-primary button-large ihc-notification-list-check ihc-notification-list-logs iump-first-button" href="<?php echo admin_url( 'admin.php?page=ihc_manage&tab=notification-logs' );?>" target="_blank"><?php esc_html_e( 'Notifications Logs', 'ihc' );?></a>
			<div class="ihc-clear"></div>
		</div>
				<form id="delete_notification" method="post" >
						<input type="hidden" value="<?php echo wp_create_nonce( 'ihc_admin_notifications_nonce' );?>" name="ihc_admin_notifications_nonce" />
						<input type="hidden" value="" id="delete_notification_by_id" name="delete_notification_by_id"/>
				</form>
				<div class="iump-rsp-table">
					<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-notifications-table"  data-delete_message="<?php esc_html_e( 'Are You sure You wish to delete this notification?', 'ihc' );?>" >
					        <thead>
					            <tr>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Subject', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Status', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Action', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Goes to', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('RunTime', 'ihc');?></th>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Memberships Target', 'ihc');?></th>
													<?php if ( $pushoverOn ):?>
															<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Mobile Notification', 'ihc');?></th>
													<?php endif;?>
					                <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Options', 'ihc');?></th>
					            </tr>
					        </thead>
					    </table>


			</div>

		</div>

<?php
}
?>
</div>
<?php
