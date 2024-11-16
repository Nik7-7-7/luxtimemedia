<?php
		global $indeed_db;
		wp_enqueue_script( 'indeed_csv_export', UAP_URL . 'assets/js/csv_export.js', ['jquery'] );
 		wp_enqueue_script('jquery-ui-datepicker');
		$affiliateId = isset( $_GET['affiliate_id'] ) ? sanitize_text_field( $_GET['affiliate_id'] ) : false;
		if ( $affiliateId !== false ){
				$searchAffiliate = $indeed_db->get_email_by_affiliate_id( $affiliateId );
		}
?>
<div class="uap-wrapper">
	<div class="uap-page-title"><?php esc_html_e('Manage Clicks', 'uap');?></div>
	<div class="uap-special-buttons-users">
		<div class="uap-special-button js-uap-export-csv" data-export_type="visits"><i class="fa-uap fa-export-csv"></i><?php esc_html_e( 'Export CSV', 'uap' );?></div>
	</div>

	<!-- Start DataTable -->
	<?php
	// 1. Datatable - define table name. used in js.
	$tableDataType = 'visits';

	// 2. Datatable - define columns
	$columns = [
								[
											'data'				=> 'checkbox',
											'title'				=> '<input type=checkbox class=uap-js-select-all-checkboxes />',
											'orderable'		=> false,
											'sortable'		=> false,
								],
								[
											'data' 				=> 'ip',
											'title'				=> esc_html__('IP', 'uap'),
											'orderable'   => false,
											'sortable'		=> false,
											'className'		=> 'uap-max-width-100',
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data' 				=> 'affiliate_id',
											'title'				=> esc_html__('Affiliate ID', 'uap'),
											'orderable'   => true,
											'sortable'		=> true,
											'className'		=> 'uap-max-width-100',
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data' 				=> 'affiliate',
											'title'				=> esc_html__('Affiliate', 'uap'),
											'orderable'   => false,
											'sortable'		=> false,
											'className'		=> 'uap-max-width-300',
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data' 				=> 'referral_id',
											'title'				=> esc_html__('Referral ID', 'uap'),
											'orderable'   => true,
											'sortable'		=> true,
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data' 				=> 'landing_page',
											'title'				=> esc_html__('Landing Page', 'uap'),
											'orderable'   => false,
											'sortable'		=> false,
											'className'		=> 'uap-max-width-200',
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data' 				=> 'referring_url',
											'title'				=> esc_html__('Referring URL', 'uap'),
											'orderable'   => false,
											'sortable'		=> false,
											'className'		=> 'uap-max-width-200',
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data'				=> 'browser',
											'title'				=> esc_html__('Browser', 'uap'),
											'orderable'   => false,
											'sortable'		=> false,
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data'				=> 'device',
											'title'				=> esc_html__('Device', 'uap'),
											'orderable'   => false,
											'sortable'		=> false,
											'visible'	  	=> false,
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data'				=> 'created_time',
											'title'				=> esc_html__('Created Time', 'uap'),
											'orderable'   => true,
											'sortable'		=> true,
											'render'  		=> [
																				'display'   => 'display',
																				'sort'      => 'value',
											]
								],
								[
											'data'				=> 'status',
											'title'				=> esc_html__('Status', 'uap'),
											'orderable'   => false,
											'sortable'		=> false,
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
			data-remove_one_item="<?php esc_html_e('Are You sure You want to remove this click?', 'uap');?>"
			data-remove_many_items="<?php esc_html_e('Are You sure You want to remove selected clicks?', 'uap');?>" ></div>
	<!-- End of 4. Datatable - Js confirm messages -->

			<!-- 5. Datatable - Custom Search + Filter -->
			<div class="uap-datatable-filters-wrapper">
							<input type="text" value="<?php if ( isset( $searchAffiliate ) && $searchAffiliate !== '' ){ echo $searchAffiliate;}?>" placeholder="<?php esc_html_e("Search Clicks", 'uap');?>" class="uap-js-search-phrase uap-max-width-300">

							<!--label class="uap-label"><?php esc_html_e('Start:', 'uap');?></label-->
							<input type="text" name="udf" value="" class="uap-general-date-filter uap-no-margin-right" placeholder="From - yyyy-mm-dd"/>
							<!--label class="uap-label"><?php esc_html_e('Until:', 'uap');?></label--><span class="uap-date-line">-</span>
							<input type="text" name="udu" value="" class="uap-general-date-filter" placeholder="To -yyyy-mm-dd"/>

							<div class="uap-datatable-multiselect-wrapp">
								<select name="status_in[]" class="uap-js-datatable-items-status-types" multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
										<option value="0"><?php esc_html_e( 'Just Visit', 'uap' );?></option>
										<option value="1"><?php esc_html_e( 'Converted', 'uap' );?></option>
								</select>
							</div>
							<button class="uap-datatable-filter-bttn"><?php esc_html_e('Filter', 'uap');?></button>
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
			<?php $pageState = get_option( 'uap_datatable_state_for-visits', false );?>
			<?php if ( $pageState !== false && !empty( $pageState )  ):?>
					<div class="uap-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>' ></div>
			<?php endif;?>
			<!-- End of 8. Page State -->

			<div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
	<!-- End DataTable -->

</div>
