<div class="">
  <div class="uap-page-title"><?php esc_html_e( 'Manage Payments', 'uap');?></div>
  <div class="uap-page-top-options">
  		<a href="<?php echo admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=new_payout' );?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><?php esc_html_e( 'Pay Affiliates', 'uap');?></a>
  		<span class="uap-top-message"><?php esc_html_e('...distribute earnings to your Affiliates', 'uap');?></span>
  </div>
  <?php if ( !empty($data['messages']) ):?>

  <?php endif;?>

  <div class="">

    <!-- Start DataTable -->
 		<?php
 		// 1. Datatable - define table name. used in js.
 		$tableDataType = 'payments';

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
 												'title'				=> esc_html__('ID', 'uap'),
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
 												'className'		=> 'uap-max-width-250',
 												'render'  		=> [
 																					'display'   => 'display',
 																					'sort'      => 'value',
 												]
 									],
 									[
 												'data' 				=> 'payment_method',
 												'title'				=> esc_html__('Payment Method', 'uap'),
 												'orderable'   => false,
 												'sortable'		=> false,
 												'className'		=> 'uap-max-width-250',
 									],
 									[
 												'data' 				=> 'amount',
 												'title'				=> esc_html__('Amount', 'uap'),
 												'orderable'   => true,
 												'sortable'		=> true,
 												'className'		=> 'uap-max-width-150',
 												'render'  		=> [
 																					'display'   => 'display',
 																					'sort'      => 'value',
 												]
 									],
 									[
 												'data' 				=> 'referrals',
 												'title'				=> esc_html__('Referrals', 'uap'),
 												'orderable'   => false,
 												'sortable'		=> false,
 												'className'		=> 'uap-max-width-100',
 									],
 									[
 												'data' 				=> 'payout',
 												'title'				=> esc_html__('Payout', 'uap'),
 												'orderable'   => false,
 												'sortable'		=> false,
 												'className'		=> 'uap-max-width-100',
 												'render'  		=> [
 																					'display'   => 'display',
 																					'sort'      => 'value',
 												]
 									],
 									[
 												'data' 				=> 'create_date',
 												'title'				=> esc_html__('Created Time', 'uap'),
 												'orderable'   => true,
 												'sortable'		=> true,
 												'className'		=> 'uap-max-width-250',
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
 												'className'		=> 'uap-max-width-150',
 									],
 									[
 												'data' 				=> 'actions',
 												'title'				=> esc_html__('Actions', 'uap'),
 												'orderable'   => false,
 												'sortable'		=> false,
 												'className'		=> 'uap-max-width-150',
 									]
 		];
 		// End of 2. Datatable - define columns


 		// 3. Datatable - Js and CSS for datatable
 		\Indeed\Uap\Admin\DataTable::Scripts( $columns, $tableDataType );

 		?>

 		<!-- 4. Datatable - Js confirm messages -->
 		<div class="uap-js-messages-for-datatable"
 				data-remove_one_item="<?php esc_html_e('Are you sure you want to remove this payment?', 'uap');?>"
 				data-remove_many_items="<?php esc_html_e('Are you sure you want to remove selected payments?', 'uap');?>" ></div>
 		<!-- End of 4. Datatable - Js confirm messages -->

 				<!-- 5. Datatable - Custom Search + Filter -->
 				<div class="uap-datatable-filters-wrapper">
 								<input type="text" value="" placeholder="<?php esc_html_e("Search Payment", 'uap');?>" class="uap-js-search-phrase uap-max-width-300">

 								<!--label class="uap-label"><?php esc_html_e('Start:', 'uap');?></label-->
 								<input type="text" name="udf" value="" class="uap-general-date-filter uap-no-margin-right" placeholder="From - yyyy-mm-dd"/>
 								<!--label class="uap-label"><?php esc_html_e('Until:', 'uap');?></label--><span class="uap-date-line">-</span>
 								<input type="text" name="udu" value="" class="uap-general-date-filter" placeholder="To - yyyy-mm-dd"/>

 								<div class="uap-datatable-multiselect-wrapp uap-filter-status-select">
 									<select name="status_in[]" class="uap-js-datatable-items-status-types " multiple data-placeholder="<?php esc_html_e("Status", 'uap');?>">
                      <option value="0"><?php esc_html_e( 'Failed', 'uap' );?></option>
 											<option value="1"><?php esc_html_e( 'Processing', 'uap' );?></option>
 											<option value="2"><?php esc_html_e( 'Paid', 'uap' );?></option>
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
 				<?php $pageState = get_option( 'uap_datatable_state_for-payments', false );?>
 				<?php if ( $pageState !== false && !empty( $pageState )  ):?>
 						<div class="uap-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>' ></div>
 				<?php endif;?>
 				<!-- End of 8. Page State -->

 				<div class="uap-js-datatable-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
        <?php if ( !empty( $_GET['payout_id'] ) ):?>
            <input type="hidden" name="payout_id" class="uap-js-payout-id" value="<?php echo sanitize_text_field( $_GET['payout_id'] );?>" />
        <?php endif;?>
  </div>

</div>
