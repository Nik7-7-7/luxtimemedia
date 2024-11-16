<div class="uap-wrapper">
		<div class="uap-page-title"><?php esc_html_e('Manage Notifications', 'uap');?></div>
		<div class="uap-page-top-options">
				<a href="<?php echo esc_url($data['url-add_edit']);?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><?php esc_html_e('Activate New Notification', 'uap');?></a>
				<span class="uap-top-message"><?php esc_html_e('...create your notification Templates!', 'uap');?></span>
		<a href="javascript:void(0)" class="button button-primary button-large uap-check-email-button uap-special-button" onClick="uapCheckEmailServer();"><?php esc_html_e('Check Mail Server', 'uap');?></a>
		<a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=notification-logs' );?>" class="button button-primary button-large uap-first-button uap-notifications-logs"><?php esc_html_e('Notifications Logs', 'uap');?></a>

        <span class="uap-admin-need-help uap-admin-need-help-notifications"><i class="fa-uap fa-help-uap"></i><a href="https://ultimateaffiliate.pro/docs/how-to-send-notifications/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span>
		<div class="uap-clear"></div>
		</div>
		<div>

			<!-- Start DataTable -->
			<?php
			// 1. Datatable - define table name. used in js.
			$tableDataType = 'notifications';

			// 2. Datatable - define columns
			$columns = [
										[
													'data'				=> 'checkbox',
													'title'				=> '<input type=checkbox class=uap-js-select-all-checkboxes />',
													'orderable'		=> false,
													'sortable'		=> false,
										],
										[
													'data' 				=> 'subject',
													'title'				=> esc_html__('Subject', 'uap'),
													'orderable'   => true,
													'sortable'		=> true,
													'className'		=> 'uap-max-width-350',
									        'render'  		=> [
											                      'display'   => 'display',
											                      'sort'      => 'value',
									        ]
										],
										[
													'data' 				=> 'status',
													'title'				=> esc_html__('Status', 'uap'),
													'orderable'   => false,
													'sortable'		=> false,
													'className'		=> 'uap-max-width-100',
										],
										[
													'data' 				=> 'action',
													'title'				=> esc_html__('Action', 'uap'),
													'orderable'   => false,
													'sortable'		=> false,
									        'render'  		=> [
											                      'display'   => 'display',
											                      'sort'      => 'value',
									        ]
										],
										[
													'data' 				=> 'goes_to',
													'title'				=> esc_html__('Goes to', 'uap'),
													'orderable'   => false,
													'sortable'		=> false,
										],
										[
													'data' 				=> 'target_ranks',
													'title'				=> esc_html__('Ranks Target', 'uap'),
													'orderable'   => false,
													'sortable'		=> false,
										],
										[
													'data' 				=> 'options',
													'title'				=> esc_html__('Options', 'uap'),
													'orderable'   => false,
													'sortable'		=> false,
										],
			];
			// End of 2. Datatable - define columns


			// 3. Datatable - Js and CSS for datatable
			\Indeed\Uap\Admin\DataTable::Scripts( $columns, $tableDataType );

			?>

			<!-- 4. Datatable - Js confirm messages -->
			<div class="uap-js-messages-for-datatable"
					data-remove_one_item="<?php esc_html_e('Are You sure You want to remove this notification?', 'uap');?>"
					data-remove_many_items="<?php esc_html_e('Are You sure You want to remove selected notifications?', 'uap');?>" ></div>
			<!-- End of 4. Datatable - Js confirm messages -->

					<!-- 5. Datatable - Custom Search + Filter -->
					<div class="uap-datatable-filters-wrapper">
									<input type="text" value="" placeholder="<?php esc_html_e("Search Notifications", 'uap');?>" class="uap-js-search-phrase uap-max-width-300">
									<div class="uap-datatable-multiselect-wrapp">
											<select name="status_types[]" class="uap-js-datatable-items-status-types" multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
													<option value="1"><?php esc_html_e('Active', 'uap');?></option>
													<option value="0"><?php esc_html_e('Inactive', 'uap');?></option>
											</select>
									</div>
									<div class="uap-datatable-multiselect-wrapp">
											<select name="target[]" class="uap-js-datatable-items-target-types" multiple data-placeholder="<?php esc_html_e("Goes to", 'uap');?>">
													<option value="admin"><?php esc_html_e('Affiliate Managers', 'uap');?></option>
													<option value="affiliate"><?php esc_html_e('Affiliates', 'uap');?></option>
											</select>
									</div>
									<button class="uap-datatable-filter-bttn"><?php esc_html_e('Filter', 'uap');?></button>
					</div>
					<!-- End of 5. Datatable - Custom Search + Filter -->

					<!-- 6. Datatable - the table html -->
					<table id="uap-dashboard-table" class="display uap-dashboard-table uap-notifications-table" >
					</table>
					<!-- End of 6. Datatable - the table html -->

					<!-- 7. Datatable - Bulk actions -->
					<div class="uap-datatable-actions-wrapp-copy uap-display-none">
							<select name="uap-action" class="uap-datatable-select-field uap-js-bulk-action-select">
									<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'uap' );?></option>
									<option value="remove"><?php esc_html_e('Remove', 'uap');?></option>
							</select>
							<input type="submit" name="uap-datatable-submit" value="<?php esc_html_e('Apply', 'uap');?>" class="button button-primary button-small uap-js-items-apply-bttn" />
					</div>
					<!-- End of 7. Datatable - Bulk actions -->

					<!-- 8. Page State -->
					<?php $pageState = get_option( 'uap_datatable_state_for-notifications', false );?>
					<?php if ( $pageState !== false && !empty( $pageState )  ):?>
							<div class="uap-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>' ></div>
					<?php endif;?>
					<!-- End of 8. Page State -->

					<div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
			<!-- End DataTable -->

		</div>
</div>






<?php
return;?>

<?php if (!empty($data['listing_items'])) : ?>

	<form method="post" id="form_notification" class="uap-notifications-list">

			<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

			<table class="wp-list-table widefat fixed tags uap-admin-tables">
				<thead>
					<tr>
						<th><?php esc_html_e('Subject', 'uap');?></th>
						<th><?php esc_html_e('Action', 'uap');?></th>
						<th><?php esc_html_e('Goes to', 'uap');?></th>
						<th><?php esc_html_e('Ranks Target', 'uap');?></th>
						<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
						<th class="manage-column uap-text-center"><?php esc_html_e('Mobile Notifications', 'uap');?></th>
						<?php endif;?>
						<th><?php esc_html_e('Options', 'uap');?></th>
					</tr>
				</thead>

				<tbody class="ui-sortable uap-alternate">
					<?php
						$admin_notifications = array(
													'admin_user_register',
													'admin_on_aff_change_rank',
													'admin_affiliate_update_profile',
						);
					?>
					<?php foreach ($data['listing_items'] as $arr) : ?>
						<?php
							if (empty($data['email_verification']) && ($arr->type=='email_check' || $arr->type=='email_check_success')){
								continue;
							}
						?>
						<tr onmouseover="uapDhSelector('#notification_<?php echo esc_attr($arr->id);?>', 1);" onmouseout="uapDhSelector('#notification_<?php echo esc_attr($arr->id);?>', 0);">
							<td>
								<?php echo esc_html($arr->subject);?>
								<div id="notification_<?php echo esc_attr($arr->id);?>" class="uap-visibility-hidden">
									<a href="<?php echo esc_url($data['url-add_edit'] . '&id=' . $arr->id);?>"><?php esc_html_e('Edit', 'uap');?></a>
									|
									<a onclick="uapDeleteFromTable(<?php echo esc_attr($arr->id);?>, 'Notification', '#delete_notification_id', '#form_notification');" href="javascript:return false;" class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>
								</div>
							</td>
							<td><div class="uap-list-affiliates-name-label"><?php if (!empty($data['actions_available'][$arr->type])){
								echo esc_html($data['actions_available'][$arr->type]);
							}?></div></td>
							<td><?php
								if (in_array($arr->type, $admin_notifications)){
									echo esc_html__('Affiliate Managers', 'uap');
								} else {
									echo esc_html__('Affiliate', 'uap');
								}
							?></td>
							<td><?php
								if ($arr->rank_id==-1){
									 esc_html_e("All", 'uap');
								}elseif (!empty($data['ranks'][$arr->rank_id])){
									 echo esc_html($data['ranks'][$arr->rank_id]);
								}?>
							</td>
							<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
								<td class="uap-text-center">
									<?php if (!empty($arr->pushover_status)):?>
										<i class="fa-uap fa-pushover-on-uap"></i>
									<?php endif;?>
								</td>
							<?php endif;?>
							<td>
									<div class="uap-js-notifications-fire-notification-test uap-notifications-list-send uap-special-button"
												data-notification_id="<?php echo esc_attr($arr->id);?>"
												data-email="<?php echo get_option( 'admin_email' );?>"
									><?php esc_html_e('Send Test Email', 'uap');?></div>
							</td>
						</tr>
					<?php endforeach;?>
				</tbody>
				<tfoot>
					<tr>
						<th><?php esc_html_e('Subject', 'uap');?></th>
						<th><?php esc_html_e('Action', 'uap');?></th>
						<th><?php esc_html_e('Goes to', 'uap');?></th>
						<th><?php esc_html_e('Ranks Target', 'uap');?></th>
						<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
						<th class="manage-column uap-text-center"><?php esc_html_e('Mobile Notifications', 'uap');?></th>
						<?php endif;?>
						<th><?php esc_html_e('Options', 'uap');?></th>
					</tr>
				</tfoot>
			</table>

		<input type="hidden" name="delete_notification" value="" id="delete_notification_id" />
	</form>

<?php else :?>

	<h5><?php esc_html_e('No Notification Available!', 'uap');?></h5>

<?php endif;?>
