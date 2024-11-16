<?php
// 1. Datatable - define table name. used in js.
$tableDataType = 'ranks';

// 2. Datatable - define columns
$columns = [
							[
										'data'				=> 'checkbox',
										'title'				=> '<input type=checkbox class=uap-js-select-all-checkboxes />',
										'orderable'		=> false,
										'sortable'		=> false,
							],
							[
										'data' 				=> 'label',
										'title'				=> esc_html__('Name', 'uap'),
										'orderable'   => true,
										'sortable'		=> true,
						        'render'  		=> [
								                      'display'   => 'display',
								                      'sort'      => 'value',
						        ]
							],
							[
										'data' 				=> 'amount_value',
										'title'				=> esc_html__('Rank Rate', 'uap'),
										'orderable'   => true,
										'sortable'		=> true,
						        'render'  		=> [
								                      'display'   => 'display',
								                      'sort'      => 'value',
						        ]
							],
							[
										'data' 				=> 'achieve',
										'title'				=> esc_html__('Achievement', 'uap'),
										'orderable'   => false,
										'sortable'		=> false,
						        'render'  		=> [
									                      'display'   => 'display',
									                      'sort'      => 'value',
						        ]
							],

							[
										'data' 				=> 'number_of_affiliates',
										'title'				=> esc_html__('Affiliates', 'uap'),
										'orderable'   => true,
										'sortable'		=> true,
						        'render'  		=> [
									                      'display'   => 'display',
									                      'sort'      => 'value',
						        ]
							],
							[
										'data' 				=> 'status',
										'title'				=> esc_html__('Status', 'uap'),
										'orderable'   => true,
										'sortable'		=> true,
						        'render'  		=> [
									                      'display'   => 'display',
									                      'sort'      => 'value',
						        ]
							],
];
// End of 2. Datatable - define columns


// 3. Datatable - Js and CSS for datatable
\Indeed\Uap\Admin\DataTable::Scripts( $columns, $tableDataType );

?>

<!-- 4. Datatable - Js confirm messages -->
<div class="uap-js-messages-for-datatable"
		data-remove_one_rank="<?php esc_html_e('Are you sure you want to remove this Rank?', 'uap');?>"
		data-remove_many_ranks="<?php esc_html_e('Are you sure you want to remove selected Ranks?', 'uap');?>" ></div>
<!-- End of 4. Datatable - Js confirm messages -->

<div class="uap-wrapper">


		<!-- title -->
		<div class="uap-page-title"><span class="second-text"><?php esc_html_e('Manage Ranks', 'uap');?></span></div>
		<!-- end of title -->

		<!-- And new bttn -->
		<div class="uap-page-top-options">
				<a href="<?php echo esc_url($data['url-add_edit']);?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><span><?php esc_html_e('Add new Rank', 'uap');?></span></a>
				<span class="uap-top-message"><?php esc_html_e('...create your Rank with new Achievements!', 'uap');?></span>
				<div class="uap-clear"></div>
		</div>
		<!-- End of Add new bttn -->

		<!-- messages -->
		<?php if (!empty($data['alert_message'])):?>
			<div class="uap-error-message"><?php echo esc_uap_content($data['alert_message']);?></div>
		<?php endif;?>
		<!-- end of messages -->

		<!-- 5. Datatable - Custom Search + Filter -->
		<div class="uap-datatable-filters-wrapper">
						<input type="text" value="" placeholder="<?php esc_html_e("Search Ranks", 'uap');?>" class="uap-js-search-phrase uap-max-width-300">
						<div class="uap-datatable-multiselect-wrapp">
								<select name="status_types[]" class="uap-js-datatable-items-status-types" multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
										<option value="1"><?php esc_html_e('Active', 'uap');?></option>
										<option value="0"><?php esc_html_e('Inactive', 'uap');?></option>
								</select>
						</div>
						<!--button class="uap-datatable-filter-bttn"><?php esc_html_e('Filter', 'uap');?></button-->
		</div>
		<!-- End of 5. Datatable - Custom Search + Filter -->

		<!-- 6. Datatable - the table html -->
		<table id="uap-dashboard-table" class="display uap-dashboard-table" >
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
		<?php $pageState = get_option( 'uap_datatable_state_for-ranks', false );?>
		<?php if ( $pageState !== false && !empty( $pageState ) ):?>
				<div class="uap-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>' ></div>
		<?php endif;?>
		<!-- End of 8. Page State -->

</div>


<div class="uap-js-ranks-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
