<?php
wp_enqueue_script( 'ihc-print-this' );

$invoiceCss = get_option( 'ihc_invoices_custom_css' );
if ( $invoiceCss !== false && $invoiceCss != '' ){
	wp_register_style( 'dummy-handle', false );
	wp_enqueue_style( 'dummy-handle' );
	wp_add_inline_style( 'dummy-handle', stripslashes( $invoiceCss ) );
}

if ( isset( $_POST['save_edit_order'] ) && !empty( $_POST['ihc_admin_edit_order_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_edit_order_nonce']), 'ihc_admin_edit_order_nonce' ) ){
		$orderObject = new \Indeed\Ihc\Db\Orders();
		$orderData = indeed_sanitize_array($_POST);
		$orderObject->setData( indeed_sanitize_array($_POST) )->setId( sanitize_text_field($_POST['id']) )->save();

		$orderData = $orderObject->fetch()->get();

		$orderMeta = new \Indeed\Ihc\Db\OrderMeta();
		$paymentGateway = $orderMeta->get( sanitize_text_field($_POST['id']), 'ihc_payment_type' );

		switch ( $_POST['status'] ){
				case 'pending':
					$args = [ 'manual' => true, 'expire_time' => '0000-00-00 00:00:00', 'payment_gateway' => $paymentGateway ];
					\Indeed\Ihc\UserSubscriptions::makeComplete( $orderData->uid, $orderData->lid, true, $args );
					\Indeed\Ihc\UserSubscriptions::updateStatus( $orderData->uid, $orderData->lid, 0 );
					do_action( 'ihc_action_after_cancel_subscription', $orderData->uid, $orderData->lid );
					break;
				case 'Completed':
						$levelData = \Indeed\Ihc\Db\Memberships::getOne( $orderData->lid );
						if (isset($levelData['access_trial_time_value']) && $levelData['access_trial_time_value'] > 0 && \Indeed\Ihc\UserSubscriptions::isFirstTime($orderData['uid'], sanitize_text_field($_POST['lid']) )){
							/// CHECK FOR TRIAL
								\Indeed\Ihc\UserSubscriptions::makeComplete( $orderData->uid, $orderData->lid, true, [ 'manual' => true, 'payment_gateway' => $paymentGateway ] );
						} else {
								\Indeed\Ihc\UserSubscriptions::makeComplete( $orderData->uid, $orderData->lid, false, [ 'manual' => true, 'payment_gateway' => $paymentGateway ] );
						}
						if ( $paymentGateway === 'bank_transfer' ){
							// create a transaction_id for this entry
			        $orderMeta = new \Indeed\Ihc\Db\OrderMeta();
							$transactionId = $orderData->uid . '_' . $orderData->lid . '_' . time();
							$orderMeta->save( sanitize_text_field( $_POST['id'] ), 'transaction_id', $transactionId );
							do_action( 'ihc_payment_completed', $orderData->uid, $orderData->lid, $levelData, sanitize_text_field( $_POST['id'] ) );//
						}
					break;
				case 'error':
					\Indeed\Ihc\UserSubscriptions::updateStatus( $orderData->uid, $orderData->lid, 0 );
					do_action( 'ihc_action_after_cancel_subscription', $orderData->uid, $orderData->lid );
					break;
				case 'refund':
					$deleteLevelForUser = apply_filters( 'ihc_filter_delete_level_for_user_on_payment_refund', true, $orderData->uid, $orderData->lid );
			    do_action( 'ihc_action_payments_before_refund', $orderData->uid, $orderData->lid );
	        if ( $deleteLevelForUser ){
	            \Indeed\Ihc\UserSubscriptions::deleteOne( $orderData->uid, $orderData->lid );
	        }
	        do_action( 'ihc_action_payments_after_refund', $orderData->uid, $orderData->lid );
					break;
				case 'fail':
					\Indeed\Ihc\UserSubscriptions::deleteOne( $orderData->uid, $orderData->lid );
					break;
		}
}

////////////// create order manually
if (isset($_POST['save_order']) && !empty( $_POST['ihc_admin_add_new_order_nonce'] ) && wp_verify_nonce( sanitize_text_field($_POST['ihc_admin_add_new_order_nonce']), 'ihc_admin_add_new_order_nonce' ) ){
		require_once IHC_PATH . 'admin/classes/Ihc_Create_Orders_Manually.php';
		$Ihc_Create_Orders_Manually = new Ihc_Create_Orders_Manually( indeed_sanitize_array($_POST) );
		$Ihc_Create_Orders_Manually->process();
		if (!$Ihc_Create_Orders_Manually->get_status()){
				$create_order_message = '<div class="ihc-danger-box">' . esc_html($Ihc_Create_Orders_Manually->get_reason()) . '</div>';
		} else {
				$create_order_message = '<div class="ihc-success-box">' . esc_html__('Order has been created!', 'ihc') . '</div>';
		}
}

if (!empty($_POST['submit_new_payment'])){
	unset($_POST['submit_new_payment']);
	$array = indeed_sanitize_array($_POST);
	if (empty($array['txn_id'])){
		/// set txn_id
		$array['txn_id'] = sanitize_text_field($_POST['uid']) . '_' . sanitize_text_field($_POST['order_id']) . '_' . indeed_get_unixtimestamp_with_timezone();
	}
	$array['message'] = 'success';


	/// THIS PIECE OF CODE ACT AS AN IPN SERVICE.
	$level_data = ihc_get_level_by_id(sanitize_text_field($_POST['level']));
	if (isset($level_data['access_trial_time_value']) && $level_data['access_trial_time_value'] > 0 && \Indeed\Ihc\UserSubscriptions::isFirstTime( sanitize_text_field($_POST['uid']), sanitize_text_field($_POST['level']) )){
		/// CHECK FOR TRIAL
			\Indeed\Ihc\UserSubscriptions::makeComplete( sanitize_text_field($_POST['uid']), sanitize_text_field($_POST['level']), true, [ 'manual' => true ] );
	} else {
		  \Indeed\Ihc\UserSubscriptions::makeComplete( sanitize_text_field($_POST['uid']), sanitize_text_field($_POST['level']), false, [ 'manual' => true ] );
	}

	$orderId = isset( $_POST['order_id'] ) ? sanitize_text_field($_POST['order_id']) : '';
	do_action( 'ihc_payment_completed', sanitize_text_field($_POST['uid']), sanitize_text_field($_POST['level']), $level_data, $orderId );
	ihc_insert_update_transaction( sanitize_text_field($_POST['uid']), $array['txn_id'], $array);

	Ihc_User_Logs::set_user_id(sanitize_text_field($_POST['uid']));
	Ihc_User_Logs::set_level_id(sanitize_text_field($_POST['level']));
	Ihc_User_Logs::write_log( esc_html__('Complete transaction.', 'ihc'), 'payments');

	unset($array);
}
$uid = (isset($_GET['uid'])) ? sanitize_text_field($_GET['uid']) : 0;
$paramsQuerey['q'] = isset( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : false;
$paramsQuerey['levels'] = isset( $_GET['levels'] ) ? sanitize_text_field( $_GET['levels'] ) : false;
$paramsQuerey['status'] = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : false;
$paramsQuerey['payment_gateway'] = isset( $_GET['payment_gateway'] ) ? sanitize_text_field( $_GET['payment_gateway'] ) : false;
$paramsQuerey['subscription_type'] = isset( $_GET['subscription_type'] ) ? sanitize_text_field( $_GET['subscription_type'] ) : false;
$paramsQuerey['start_time'] = isset( $_GET['start_time'] ) ? sanitize_text_field( $_GET['start_time'] ) : false;
$paramsQuerey['end_time'] = isset( $_GET['end_time'] ) ? sanitize_text_field( $_GET['end_time'] ) : false;

	$data['total_items'] = Ihc_Db::get_count_orders($uid,$paramsQuerey);
	if ($data['total_items']){
		$url = admin_url('admin.php?page=ihc_manage&tab=orders');
		$limit = 25;
		$current_page = (empty($_GET['ihc_payments_list_p'])) ? 1 : sanitize_text_field($_GET['ihc_payments_list_p']);
		if ($current_page>1){
			$offset = ( $current_page - 1 ) * $limit;
		} else {
			$offset = 0;
		}
		include_once IHC_PATH . 'classes/Ihc_Pagination.class.php';
		$pagination = new Ihc_Pagination(array(
												'base_url' => $url,
												'param_name' => 'ihc_payments_list_p',
												'total_items' => $data['total_items'],
												'items_per_page' => $limit,
												'current_page' => $current_page,
		));
		if ($offset + $limit>$data['total_items']){
			$limit = $data['total_items'] - $offset;
		}
		$data['pagination'] = $pagination->output();
		$data['orders'] = Ihc_Db::get_all_order($limit, $offset, $uid, $paramsQuerey);
	}
	$data['view_transaction_base_link'] = admin_url('admin.php?page=ihc_manage&tab=payments&details_id=');
	$data['add_new_transaction_by_order_id_link'] = admin_url('admin.php?page=ihc_manage&tab=new_transaction&order_id=');

	$payment_gateways = ihc_list_all_payments();
	$payment_gateways['woocommerce'] = esc_html__( 'WooCommerce', 'ihc' );

	$show_invoices = (ihc_is_magic_feat_active('invoices')) ? TRUE : FALSE;
	$invoiceShowOnlyCompleted = get_option('ihc_invoices_only_completed_payments');
	require_once IHC_PATH . 'classes/Orders.class.php';
	$Orders = new Ump\Orders();
?>

<?php
echo ihc_inside_dashboard_error_license();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );
?>
<div class="iump-wrapper">
	<div class="iump-page-headline">
						<?php esc_html_e('Payment History', 'ihc');?>
	</div>
	<div class="imup-page-top-options">
		<a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=add_new_order');?>" class="indeed-add-new-like-wp">
					<i class="fa-ihc fa-add-ihc"></i><?php esc_html_e('Add Manual Payment', 'ihc');?></a>
	</div>

<?php if (!empty($create_order_message)):?>
    <div><?php echo esc_ump_content($create_order_message);?></div>
<?php endif;?>
<?php


$taxesOn = ihc_is_magic_feat_active( 'taxes' );

$tableDataType = 'orders';
$columns = [
	[
			'data'        => 'select_item',
			'orderable'   => false,
			'sortable'		=> false,
	],
  [
      'data'        => 'id',
  ],
  [
      'data'        => 'code',
  ],
  [
      'data'      => 'user',
  ],
  [
      'data'      => 'membership',
      'orderable'   => false,
      'sortable'		=> false,
  ],
  [
      'data'      => 'net_amount',
      'orderable' => false
  ],
  [
      'data'      => 'taxes',
      'orderable' => false
  ],
  [
      'data'        => 'amount_value',
  ],
  [
      'data'        => 'payment_method',
      'orderable'   => false,
      'sortable'		=> false,
  ],
  [
      'data'        => 'create_date'
  ],
  [
      'data'        => 'coupon',
      'orderable'   => false,
      'sortable'		=> false,
  ],
  [
      'data'        => 'transaction',
      'orderable'   => false,
      'sortable'		=> false,
  ],
  [
      'data'        => 'invoices',
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
  ]
];

if ( !$taxesOn ){
		unset( $columns[5] );
		unset( $columns[6] );
		$columns = array_values( $columns );
}
if ( !$show_invoices ){
		foreach ( $columns as $columnKey => $columnData){
				if ( $columnData['data'] === 'invoices' ){
						unset( $columns[$columnKey] );
				}
		}
		$columns = array_values( $columns );
}


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
    wp_add_inline_script( 'ihc-table', "var iump_orders_uid='$uid';" );
} else {
    wp_localize_script( 'ihc-table', 'iump_datatable_cols', json_encode( $columns ) );
    wp_localize_script( 'ihc-table', 'iump_datatable_labels', json_encode( $labels ) );
    wp_localize_script( 'ihc-table', "var iump_datatable_type='$tableDataType';" );
    wp_localize_script( 'ihc-table', "var iump_orders_uid='$uid';" );
}
wp_enqueue_script( 'ihc-table' );

// maybe submit
if ( isset( $_POST['iump-datatable-submit'] ) && isset( $_POST['order_id'] ) ){
		$target = indeed_sanitize_array( $_POST['order_id'] );
		if ( count($target) && $_POST['iump-action'] === 'remove' ){
				foreach ( $target as $orderId ){
						// delete each order
						\Ihc_Db::delete_order( $orderId );
				}
		} else if ( count($target) && $_POST['iump-action'] === 'make_completed' ){
				// make completed
				foreach ( $target as $orderId ){
						$orderObject = new \Indeed\Ihc\Db\Orders();
		        $orderData = $orderObject->setId( $orderId )->fetch()->get();
						if ( isset( $orderData->status ) && $orderData->status === 'Completed' ){
								continue;
						}
						$orderObject->setId( $orderId )->update( 'status', 'Completed' );
						$orderData = $orderObject->fetch()->get();
						if ( !$orderData ){
								continue;
						}
						$orderMeta = new \Indeed\Ihc\Db\OrderMeta();
						$paymentGateway = $orderMeta->get( $orderId, 'ihc_payment_type' );
						$levelData = \Indeed\Ihc\Db\Memberships::getOne( $orderData->lid );
						if (isset($levelData['access_trial_time_value']) && $levelData['access_trial_time_value'] > 0 && \Indeed\Ihc\UserSubscriptions::isFirstTime( $orderData->uid, $orderData->lid )){
							/// CHECK FOR TRIAL
								\Indeed\Ihc\UserSubscriptions::makeComplete( $orderData->uid, $orderData->lid, true, [ 'manual' => true, 'payment_gateway' => $paymentGateway ] );
						} else {
								\Indeed\Ihc\UserSubscriptions::makeComplete( $orderData->uid, $orderData->lid, false, [ 'manual' => true, 'payment_gateway' => $paymentGateway ] );
						}
						if ( $paymentGateway === 'bank_transfer' ){
							// create a transaction_id for this entry
							$transactionId = $orderData->uid . '_' . $orderData->lid . '_' . time();
							$orderMeta->save( $orderId, 'transaction_id', $transactionId );
							do_action( 'ihc_payment_completed', $orderData->uid, $orderData->lid, $levelData, $orderId );// modified since version 12.5, adeed level data and order id
						}
				}
		}
}
$paymentGateways = ihc_get_active_payments_services( false );
$possibleStatus = [
										'Completed' => esc_html__('Completed', 'ihc'),
										'pending'		=> esc_html__('Pending', 'ihc'),
										'fail'			=> esc_html__('Failed', 'ihc'),
										'error'			=> esc_html__('Error', 'ihc'),
];
$subscriptionTypes = [
		'unlimited' 			=> 'LifeTime',
		'limited' 				=> 'Limited Time',
		'date_interval' 	=> 'Date Range',
		'regular_period' 	=> 'Recurring Subscription',
];
$taxesOn = ihc_is_magic_feat_active( 'taxes' );
?>

<!-- Page State -->
<?php $pageState = get_option( 'ihc_datatable_state_for-orders', false );?>
<?php if ( $pageState !== false ):?>
	<div class="iump-js-datatable-state" data-value='<?php echo stripslashes( $pageState );?>'></div>
<?php endif;?>
<!-- End of Page State -->

<div class="iump-rsp-table">

		<form action="" method="post" class="ihc-orders-lists-wrapper" data-complete_many_orders="<?php esc_html_e( 'Are You sure You wish to make complete the selected orders?', 'ihc' );?>" data-delete_many_orders="<?php esc_html_e( 'Are You sure You wish to remove the selected orders?', 'ihc' );?>" >

			<div class="iump-datatable-filters-wrapper">
					<br/>
							<input type="text" value="" placeholder="<?php esc_html_e('Search', 'ihc');?>" class="iump-js-search-phrase">
							<?php $roles = ihc_get_wp_roles_list();?>
							<div class="iump-datatable-multiselect-wrapp">
									<select name="payment_gateway[]" class="iump-datatable-filter-orders-payment-gateway" multiple data-placeholder="<?php esc_html_e("Payment Gateway", 'ihc');?>" >
											<?php foreach ( $paymentGateways as $paymentType => $paymentTypeLabel ):?>
													<option value="<?php echo $paymentType;?>" ><?php echo $paymentTypeLabel;?></option>
											<?php endforeach;?>
									</select>
							</div>
							<div class="iump-datatable-multiselect-wrapp">
									<select name="subscription_type[]" class="iump-datatable-filter-orders-subscription-type" multiple data-placeholder="<?php esc_html_e("Subscription Type", 'ihc');?>" >
										<?php foreach ( $subscriptionTypes as $subscriptionType => $subscriptionTypeLabel ):?>
												<option value="<?php echo $subscriptionType;?>" ><?php echo $subscriptionTypeLabel;?></option>
										<?php endforeach;?>
									</select>
							</div>
							<div class="iump-datatable-multiselect-wrapp">
									<select name="" class="iump-datatable-filter-orders-status" multiple data-placeholder="<?php esc_html_e("Status", 'ihc');?>" >
										<?php foreach ( $possibleStatus as $statusType => $statusLabel ):?>
												<option value="<?php echo $statusType;?>" ><?php echo $statusLabel;?></option>
										<?php endforeach;?>
									</select>
							</div>
							<div>
									<br/>
									<div class="iump-datatable-date-input-wrapp">
											<input type="text" name="" class="iump-js-orders-start-date" placeholder="<?php esc_html_e( 'Start Date', 'ihc');?>"/>
									</div>
									<div class="iump-datatable-date-input-wrapp">
											<input type="text" name="" class="iump-js-orders-end-date" placeholder="<?php esc_html_e( 'End Date', 'ihc');?>" />
									</div>
									<div class="iump-datatable-date-input-wrapp">
											<button type="button" class="button button-primary button-small iump-js-admin-orders-submit-filters-bttn" ><?php esc_html_e('Filter', 'ihc');?></button>
									</div>
							</div>
							<!--button class="iump-datatable-filter-bttn"><?php esc_html_e('Apply', 'ihc');?></button-->

			</div>

	  		<table id="iump-dashboard-table" class="display iump-dashboard-table ihc-display-none iump-js-orders-table"  >
	          <thead>
	              <tr>
										<th class=""><input type="checkbox" class="iump-js-select-all-checkboxes" data-target="iump-dashboard-table"/></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Id', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Code', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Customer', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Membership', 'ihc');?></th>
										<?php if ($taxesOn):?>
	                  		<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Net amount', 'ihc');?></th>
	                  		<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Taxes', 'ihc');?></th>
										<?php endif;?>
										<th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Total amount', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Payment method', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Date', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Coupon', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Transaction', 'ihc');?></th>
										<?php if ( $show_invoices ):?>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Invoice', 'ihc');?></th>
										<?php endif;?>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Status', 'ihc');?></th>
	                  <th class="iump-dashboard-table-head-col iump-dashboard-table-col-sorting"><?php esc_html_e('Action', 'ihc');?></th>
	          </thead>
	      </table>

				<div class="iump-datatable-actions-wrapp-copy ihc-display-none">
						<select name="iump-action" class="iump-datatable-select-field iump-js-bulk-action-select">
								<option value="" disabled selected ><?php esc_html_e( 'Bulk Actions', 'ihc' );?></option>
								<option value="make_completed"><?php esc_html_e('Change Status to Completed', 'ihc');?></option>
								<option value="remove"><?php esc_html_e('Remove', 'ihc');?></option>
						</select>
						<input type="submit" name="iump-datatable-submit" value="<?php esc_html_e('Apply', 'ihc');?>" class="button button-primary button-small iump-js-orders-apply-bttn" />
				</div>

	  </form>

</div>
<?php
$class = 'Indeed\Ihc\\' . 'Ol'.'dL'.'ogs';
$ol_dL_ogs = new $class();
if ( (int)$ol_dL_ogs->FGCS() === 2){
		echo '<div class="ihc'.'-err' .'or-'.'glo'.'bal'.'-dash'.'board'.'-message">This'.' tri'.'al '.'ver'.'sion'.' of'.' the'.' plu'.'gin'.' all'.'ows'.' ac'.'ces'.'s '.'to'.' on'.'ly'. "<strong>" . ' 1'.'0'.' pay'.'men'.'t'.'s'. "</strong>" . '.'.' T'.'o'.' un'.'lo'.'ck '.'fu'.'ll'.' function'.'ality'.' and'.' en'.'joy'.' un'.'limited'.' access'.', '.'cons'.'ider'.' activ'.'ating '.'the ' . '<a href="' . admin_url( 'admin.php?page=ihc_manage&tab=help' ). '">' . 'lice'.'nse' . '</a>' . '</div>';
}
?>
</div>
