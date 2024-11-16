<?php
echo ihc_inside_dashboard_error_license();
echo iump_is_wizard_uncompleted_but_not_skiped();
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$payment_gateways = ihc_list_all_payments();

global $wpdb;
$table_name = $wpdb->prefix . 'indeed_members_payments';

if (isset($_REQUEST['details_id'])){
	?>
	<div class="ihc-stuffbox">
	<h3><?php esc_html_e('Payment Details', 'ihc');?></h3>
	<div class="inside">
	<?php
	$q = $wpdb->prepare("SELECT id,txn_id,u_id,payment_data,history,orders,paydate FROM $table_name WHERE id=%d ", sanitize_text_field($_REQUEST['details_id']) );
	$data = $wpdb->get_row($q);

	if (!empty($data->history)){

		$dat = $data->history;

		$dat = maybe_unserialize($dat);
		if (isset($dat) && is_array($dat)){
			foreach ($dat as $k=>$transaction_history_arr){
				if (is_string($transaction_history_arr)){
					//is json
					$json = stripslashes($transaction_history_arr);
					if ($k){
						echo esc_ump_content('<h4>' . date('Y-m-d H:i:s', $k) .'</h4>');
					}
					$arr = (array)json_decode($json, true);
					foreach ($arr as $key=>$value){
						echo esc_html($key) . ': ' . esc_html( $value ) . '<br/>';
					}
				} else {
					//is an array
					if ($k>0){
						echo esc_ump_content('<h4>' . date('Y-m-d H:i:s', $k) .'</h4>');
					}
					foreach ($transaction_history_arr as $key=>$value){
							if (is_string($value)){
									echo esc_html($key) . ' : ' . esc_html( $value ) . '<br/>';
							} else if (is_array($value)){
									echo esc_html($key) .' : ';
									foreach ($value as $insideValue){
											if ( is_array( $insideValue ) ){
													ihc_print_array_in_depth( $insideValue );
											} else {
												echo esc_html( $insideValue ) . '<br/>';
											}
									}
									echo esc_ump_content('<br/>');
							}
					}
				}
			}
		}
	} else if(!empty($data->payment_data)) {
		//insert history
		$arr = json_decode($data->payment_data, true);
		unset($arr['custom']);
		unset($arr['transaction_subject']);
		if (!empty($arr->paydate)){
			$arr_key = strtotime($arr->paydate);
		} else {
			$arr_key = 0;
		}
		$history[$arr_key] = json_encode($arr);
		$history_str = serialize($history);
		$history_str = addcslashes($history_str, "'");
		$q = $wpdb->prepare("UPDATE $table_name SET history=%s WHERE id=%d ", $history_str, sanitize_text_field($_REQUEST['details_id']));
		$wpdb->query($q);
	}
	?>
	</div>
	</div>
<?php
} else {
	///list all payments
?>
<div class="iump-wrapper">
<div class="iump-page-title">Ultimate Membership Pro -
							<span class="second-text">
								<?php esc_html_e('Transactions List', 'ihc');?>
							</span>
						</div>
<?php
//No query parameters required, Safe query. prepare() method without parameters can not be called
$query = "SELECT COUNT(id) as c FROM $table_name;";
$count_total_items = $wpdb->get_row( $query );
$total_items = (empty($count_total_items->c)) ? 0 : $count_total_items->c;
$url = admin_url('admin.php?page=ihc_manage&tab=payments');
$limit = 25;
$current_page = (empty($_GET['ihc_payments_list_p'])) ? 1 : sanitize_text_field($_GET['ihc_payments_list_p']);
if ($current_page>1){
	$offset = ( $current_page - 1 ) * $limit;
} else {
	$offset = 0;
}
if ($offset + $limit>$total_items){
	$limit = $total_items - $offset;
}
$limit = 25;
include_once IHC_PATH . 'classes/Ihc_Pagination.class.php';
$pagination = new Ihc_Pagination(array(
										'base_url' 						=> $url,
										'param_name' 					=> 'ihc_payments_list_p',
										'total_items' 				=> $total_items,
										'items_per_page' 			=> $limit,
										'current_page' 				=> $current_page,
));
$pagination_str = $pagination->output();

$q = $wpdb->prepare("SELECT id,txn_id,u_id,payment_data,history,orders,paydate FROM $table_name ORDER BY paydate DESC LIMIT %d OFFSET %d", $limit, $offset);
$data_db = $wpdb->get_results($q);



	if ($data_db && count($data_db)){
		?>
    <div class="iump-rsp-table">	<?php echo esc_ump_content($pagination_str);?></div>
							<table class="wp-list-table widefat fixed tags ihc-admin-tables">
								  <thead>
									<tr>
										  <th class="manage-column">
											  <span>
												<?php esc_html_e('Username', 'ihc');?>
											  </span>
										  </th>

										  <th>
											  <span>
												<?php esc_html_e('Orders', 'ihc');?>
											  </span>
										  </th>
										  <th>
											  <span>
												<?php esc_html_e('Amount', 'ihc');?>
											  </span>
										  </th>
										  <th>
											  <span>
												<?php esc_html_e('Payment Type', 'ihc');?>
											  </span>
										  </th>
										  <th>
										  	  <span>
										  		<?php esc_html_e('Status', 'ihc');?>
										  	  </span>
										  </th>
										  <th class="manage-column">
											  <span>
												<?php esc_html_e('Details', 'ihc');?>
											  </span>
										  </th>
										  <th class="manage-column">
											  <span>
												<?php esc_html_e('Date', 'ihc');?>
											  </span>
										  </th>
										  <th class="manage-column">
											  <span>
												<?php esc_html_e('Remove', 'ihc');?>
											  </span>
										  </th>
								    </tr>
								  </thead>

								  <tfoot>
									<tr>
										  <th class="manage-column">
											  <span>
													<?php esc_html_e('Username', 'ihc');?>
											  </span>
										  </th>

										  <th>
											  <span>
													<?php esc_html_e('Orders', 'ihc');?>
											  </span>
										  </th>
										  <th>
											  <span>
													<?php esc_html_e('Amount', 'ihc');?>
											  </span>
										  </th>
										  <th>
											  <span>
												<?php esc_html_e('Payment Type', 'ihc');?>
											  </span>
										  </th>
										  <th>
										  		<span>
										  			<?php esc_html_e('Status', 'ihc');?>
										  		</span>
										  </th>
										  <th class="manage-column">
											  <span>
													<?php esc_html_e('Details', 'ihc');?>
											  </span>
										  </th>
										  <th class="manage-column">
											  <span>
													<?php esc_html_e('Date', 'ihc');?>
											  </span>
										  </th>
										  <th class="manage-column">
											  <span>
													<?php esc_html_e('Remove', 'ihc');?>
											  </span>
										  </th>
								    </tr>
								  </tfoot>
								  <tbody>
										<?php
										foreach ($data_db as $arr){
											if ( !empty($arr->payment_data)){
													$data = json_decode(stripslashes($arr->payment_data));
											}

											$user_info = get_userdata($arr->u_id);
											?>
												<tr>
													  <td class="manage-column">
													  	<span>
													  	<?php
													  		if (isset( $user_info->data->user_login) &&  $user_info->data->user_login){
													  			echo esc_html($user_info->data->user_login);
													  		}
													  	?>
														</span>
													  </td>
													  	<?php
													  		if (isset($user_info->data->user_email) && $user_info->data->user_email){
													  			echo esc_html($user_info->data->user_email);
													  		}
													  	?>
													  </td-->
													  <td class="manage-column">
													  	<?php

															$tx_orders = array();
															$tx_orders = maybe_unserialize($arr->orders);
															if(!empty($tx_orders) && is_array($tx_orders) && count($tx_orders)>0){
																foreach($tx_orders as $order){
																	$order_code = '-';
																	$order_code = Ihc_Db::get_order_meta($order, 'code');

																	echo esc_ump_content('<div class="level-type-list  ihc-expired-level">' . $order_code . '</div>');
																}
															} else {
																/// No order
																echo esc_ump_content('<div class="level-type-list  ihc-expired-level">-</div>');
															}
													  	?>

													  </td>
													  <td class="manage-column">
													  	<span class="level-payment-list">
														<?php
															if (isset($data->mc_gross) && isset($data->mc_currency)){
																echo esc_html( $data->mc_gross ) . ' ' . esc_html( $data->mc_currency );
															} else if (isset($data->x_amount)){
																echo esc_html($data->x_amount);
																if(isset($data->x_currency_code)){
																	echo esc_html(' ') . esc_html($data->x_currency_code);
																}
															} else if (isset($data->amount) && isset($data->currency)){
																echo esc_html($data->amount) . ' ' . esc_html($data->currency);
															} else if(isset($data->total)){
																echo esc_html($data->total) . ' ' . esc_html($data->currency_code);
															} else {
																echo esc_html('-');
															}
														?>
													  	</span>
													  </td>
													  <td><?php
													  		if (!empty($data->ihc_payment_type)){
													  			$gateway_key = $data->ihc_payment_type;
													  			echo esc_html($payment_gateways[$gateway_key]);
													  		}
													  ?></td>
													  <td class="manage-column">
													  	<?php
													  		if (!empty($data->payment_status)){
													  			$pay_sts = $data->payment_status;
													  		} else if (isset($data->x_response_code) && ($data->x_response_code == 1)){
															  	$pay_sts = esc_html__('Confirmed', 'ihc');
															} else if (isset($data->code) && ($data->code == 2)){
															  	$pay_sts = esc_html__('Confirmed', 'ihc');
															} else if(isset($data->message) && $data->message=='success'){
																$pay_sts = esc_html__('Confirmed', 'ihc');
															} else if (isset($data->ap_status) && ($data->ap_status=='Success' || $data->ap_status=='Subscription-Payment-Success')){
																$pay_sts = esc_html__('Confirmed', 'ihc');
															} else {
																$pay_sts = '-';
															}
															if ($pay_sts=='pending'){
																$pay_sts = esc_html__('Pending', 'ihc');
															}
															echo esc_html($pay_sts);
													  	?>
													  </td>
													  <td class="manage-column">
														  <span>
															<a href="<?php echo esc_url( $url . '&tab=payments&details_id=' . $arr->id );?>"><?php esc_html_e('View Details', 'ihc');?></a>
														  </span>
													  </td>
													  <td class="manage-column">
														  <span>
															<?php echo esc_html(ihc_convert_date_time_to_us_format($arr->paydate));?>
														  </span>
													  </td>
												      <td class="column">
																	<span class="ihc-pointer ihc-js-delete-payment-transaction" data-id="<?php echo esc_attr($arr->id);?>" >
																			<i class="fa-ihc ihc-icon-remove-e"></i>
																	</span>
												      </td>
												</tr>
											<?php
										}
										?>
								  </tbody>
						</table>
		<?php
	} else {
		?>
		<div class="ihc-warning-message"> <?php esc_html_e('No Payments Available to show up!', 'ihc');?></div>
		<?php
	}
} // end of list all payments
?>

<?php
