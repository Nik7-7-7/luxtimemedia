<div class="uap-wrapper">
	<div class="uap-page-title"><?php esc_html_e('Manage Creatives', 'uap');?></div>
	<div class="uap-page-top-options">
		<a href="<?php echo esc_url($data['url-add_edit']);?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><?php esc_html_e('Add New Creative', 'uap');?></a>
		<span class="uap-top-message"><?php esc_html_e('...create Creatives for your Affiliates', 'uap');?></span>
	</div>
	<!-- Start DataTable -->
<?php
// 1. Datatable - define table name. used in js.
$tableDataType = 'banners';

// 2. Datatable - define columns
$columns = [
							[
										'data'				=> 'checkbox',
										'title'				=> '<input type=checkbox class=uap-js-select-all-checkboxes />',
										'orderable'		=> false,
										'sortable'		=> false,
							],
							[
										'data' 				=> 'name',
										'title'				=> esc_html__('Name', 'uap'),
										'orderable'   => true,
										'sortable'		=> true,
						        'render'  		=> [
								                      'display'   => 'display',
								                      'sort'      => 'value',
						        ]
							],
							[
										'data' 				=> 'content',
										'title'				=> esc_html__('Content', 'uap'),
										'orderable'		=> false,
										'sortable'		=> false,
							],
							[
										'data' 				=> 'type',
										'title'				=> esc_html__('Type', 'uap'),
										'orderable'		=> false,
										'sortable'		=> false,
							],
							[
										'data' 				=> 'url',
										'title'				=> esc_html__('URL', 'uap'),
										'orderable'		=> false,
										'sortable'		=> false,
							],
							[
										'data' 				=> 'status',
										'title'				=> esc_html__('Status', 'uap'),
										'orderable'		=> false,
										'sortable'		=> false,
							],
							[
										'data' 				=> 'created_date',
										'title'				=> esc_html__('Created Time', 'uap'),
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
		data-remove_one_item="<?php esc_html_e('Are You sure You want to remove this Creative?', 'uap');?>"
		data-remove_many_items="<?php esc_html_e('Are You sure You want to remove selected Creatives?', 'uap');?>" ></div>
<!-- End of 4. Datatable - Js confirm messages -->

		<!-- 5. Datatable - Custom Search + Filter -->
		<div class="uap-datatable-filters-wrapper">
						<input type="text" value="" placeholder="<?php esc_html_e("Search Creatives", 'uap');?>" class="uap-js-search-phrase uap-max-width-300">
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
		<?php $pageState = get_option( 'uap_datatable_state_for-banners', false );?>
		<?php if ( $pageState !== false && !empty( $pageState )  ):?>
				<div class="uap-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>' ></div>
		<?php endif;?>
		<!-- End of 8. Page State -->

		<div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
<!-- End DataTable -->

</div>
