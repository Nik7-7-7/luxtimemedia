<?php
global $indeed_db;
wp_enqueue_script( 'indeed_csv_export', UAP_URL . 'assets/js/csv_export.js', ['jquery'] );

$available_systems = $indeed_db->getPossibleSources();
$source_arr = [];
if ( $available_systems ){
		foreach($available_systems as $k=>$v){
				$label = uap_service_type_code_to_title($v['source']);
				if ( $label === '' ){
						continue;
				}
				$source_arr[$v['source']] = $label;
		}
}
$affiliateId = isset( $_GET['affiliate_id'] ) ? sanitize_text_field( $_GET['affiliate_id'] ) : false;
if ( $affiliateId !== false ){
		$searchAffiliate = $indeed_db->get_email_by_affiliate_id( $affiliateId );
}
?>
<div class="uap-wrapper">
	<div class="uap-page-title"><?php esc_html_e('Manage Referrals (rewards)', 'uap');?></div>
	<div class="uap-page-top-options">
		<a href="<?php echo esc_url($data['url-add_edit']);?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><?php esc_html_e('Add New Referral', 'uap');?></a>
		<span class="uap-top-message"><?php esc_html_e('...add manual Referral (Reward) for a specific Affiliate', 'uap');?></span>
	</div>
		<?php if (!empty($data['error'])):?>
			<div class="uap-wrapp-the-errors">
				<?php echo esc_html($data['error']);?>
			</div>
		<?php endif;?>
		<!-- messages -->
		<?php if (!empty($data['alert_message'])):?>
			<div class="uap-error-message"><?php echo esc_uap_content($data['alert_message']);?></div>
		<?php endif;?>
		<!-- end of messages -->

		<?php if (!empty($data['subtitle'])):?>
			<h4><?php echo esc_html($data['subtitle']);?></h4>
		<?php endif;?>

		<div class="uap-special-buttons-users">
			<div class="uap-special-button js-uap-export-csv" data-filters="" data-export_type="referrals" >
					<i class="fa-uap fa-export-csv"></i><?php esc_html_e( 'Export CSV', 'uap' );?>
			</div>
		</div>

		<!-- Start DataTable -->
		<?php
		// 1. Datatable - define table name. used in js.
		$tableDataType = 'referrals';

		// 2. Datatable - define columns
		$columns = [
									[
												'data'				=> 'checkbox',
												'title'				=> '<input type=checkbox class=uap-js-select-all-checkboxes />',
												'orderable'		=> false,
												'sortable'		=> false,
									],
									[
												'data' 				=> 'id',
												'title'				=> esc_html__('Referral ID', 'uap'),
												'orderable'   => true,
												'sortable'		=> true,
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
												'data' 				=> 'affiliate_username',
												'title'				=> esc_html__('Affiliate', 'uap'),
												'orderable'   => true,
												'sortable'		=> true,
												'className'		=> 'uap-max-width-250',
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'source',
												'title'				=> esc_html__('Source', 'uap'),
												'orderable'   => false,
												'sortable'		=> false,
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'reference',
												'title'				=> esc_html__('Reference', 'uap'),
												'orderable'   => false,
												'sortable'		=> false,
												'className'		=> 'uap-max-width-100',
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'description',
												'title'				=> esc_html__('Description', 'uap'),
												'orderable'   => false,
												'sortable'		=> false,
												'className'		=> 'uap-max-width-300 uap-referral-description',
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'amount',
												'title'				=> esc_html__('Amount', 'uap'),
												'orderable'   => false,
												'sortable'		=> false,
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'customer_id',
												'title'				=> esc_html__('Customer ID', 'uap'),
												'orderable'   => false,
												'sortable'		=> false,
												'visible'	  	=> false,
												'className'		=> 'uap-max-width-100',
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'click_id',
												'title'				=> esc_html__('Click ID', 'uap'),
												'orderable'   => false,
												'sortable'		=> false,
												'visible'	  	=> false,
												'className'		=> 'uap-max-width-100',
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'created_time',
												'title'				=> esc_html__('Created Time', 'uap'),
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
												'orderable'   => false,
												'sortable'		=> false,
												'render'  		=> [
																					'display'   => 'display',
																					'sort'      => 'value',
												]
									],
									[
												'data' 				=> 'payment_status',
												'title'				=> esc_html__('Payout', 'uap'),
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
								<input type="text" value="<?php if ( isset( $searchAffiliate ) && $searchAffiliate !== '' ){ echo $searchAffiliate;}?>" placeholder="<?php esc_html_e("Search Referrals", 'uap');?>" class="uap-js-search-phrase uap-max-width-300">

								<!--label class="uap-label"><?php esc_html_e('Start:', 'uap');?></label-->
								<input type="text" name="udf" value="" class="uap-general-date-filter uap-no-margin-right" placeholder="From - yyyy-mm-dd"/>
								<!--label class="uap-label"><?php esc_html_e('Until:', 'uap');?></label--><span class="uap-date-line">-</span>
								<input type="text" name="udu" value="" class="uap-general-date-filter" placeholder="To - yyyy-mm-dd"/>

								<div class="uap-datatable-multiselect-wrapp uap-filter-status-select">
									<select name="status_in[]" class="uap-js-datatable-items-status-types " multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
											<option value="1"><?php esc_html_e( 'Pending', 'uap' );?></option>
											<option value="2"><?php esc_html_e( 'Approved', 'uap' );?></option>
											<option value="0"><?php esc_html_e( 'Rejected', 'uap' );?></option>
									</select>
								</div>

								<?php if ( $source_arr ):?>
								<div class="uap-datatable-multiselect-wrapp">
									<select name="source_in[]" class="uap-js-datatable-items-source-types-referrals" multiple data-placeholder="<?php esc_html_e("Source", 'uap');?>">
												<?php foreach ( $source_arr as $sourceSlug => $sourceLabel ):?>
													<option value="<?php echo $sourceSlug;?>" ><?php echo $sourceLabel;?></option>
												<?php endforeach;?>
									</select>
								</div>
								<?php endif;?>

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
				<?php $pageState = get_option( 'uap_datatable_state_for-referrals', false );?>
				<?php if ( $pageState !== false && !empty( $pageState )  ):?>
						<div class="uap-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>' ></div>
				<?php endif;?>
				<!-- End of 8. Page State -->

				<div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
		<!-- End DataTable -->

</div>
