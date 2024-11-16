<?php
namespace Ump{
	class Orders{

		public function __construct(){}

		public function do_insert($data=array(), $automated_payment=0){
			/*
			 * @param array, int (0 - simple payment, 1 - recurring). default its 0, single payment and first interation of a recurring payment. 
			 * @return int
			 */
			$data = apply_filters('ihc_order_insert_data', $data);
			if (!empty($data['uid']) && isset($data['lid']) && isset($data['amount'])){
				if (empty($data['status'])){
					$data['status'] = 'pending';
				}
				global $wpdb;
				$table = $wpdb->prefix . 'ihc_orders';

				//// WRITE INTO USER_LOGS
				\Ihc_User_Logs::set_user_id($data['uid']);
				\Ihc_User_Logs::set_level_id($data['lid']);
				$username = \Ihc_Db::get_username_by_wpuid($data['uid']);
				$level_name = \Ihc_Db::get_level_name_by_lid($data['lid']);
				\Ihc_User_Logs::write_log($username . esc_html__(' has ordered Level ', 'ihc') . $level_name, 'user_logs');

				/// since version 8.6, before we used NOW() function in mysql
				$currentDate = indeed_get_current_time_with_timezone();

				$q = $wpdb->prepare( "INSERT INTO $table VALUES (null, %d, %d, %s, %s, %d, %s, %s );",
											$data['uid'], $data['lid'], $data['amount_type'], $data['amount'], $automated_payment, $data['status'], $currentDate );
				$wpdb->query($q);
				$id = $wpdb->insert_id;
				do_action('ump_payment_check', $id, 'insert');


				/// SAVE METAS
				if (isset($data['txn_id'])){
					\Ihc_Db::save_udate_order_meta($id, 'txn_id', $data['txn_id']);
				}
				if (isset($data['ihc_payment_type'])){
					\Ihc_Db::save_udate_order_meta($id, 'ihc_payment_type', $data['ihc_payment_type']);
				}

				/// extra fields
				if (!empty($data['extra_fields'])){
					if (!empty($data['extra_fields']['is_trial'])){
						\Ihc_Db::save_udate_order_meta($id, 'is_trial', TRUE);
					}
					if (!empty($data['extra_fields']['tax_value'])){
						\Ihc_Db::save_udate_order_meta($id, 'tax_value', $data['extra_fields']['tax_value']);
					}
					if (!empty($data['extra_fields']['discount_value'])){
						\Ihc_Db::save_udate_order_meta($id, 'discount_value', $data['extra_fields']['discount_value']);
					}
					if (!empty($data['extra_fields']['coupon_used'])){
							\Ihc_Db::save_udate_order_meta($id, 'coupon_used', $data['extra_fields']['coupon_used']);
					}
				}

				///only for authorize recurring
				if (!empty($data['extra_fields']['txn_id'])){
					/// update transactions
					$this->update_transaction_table($data['extra_fields']['txn_id'], $id);
					/// update order metas
					\Ihc_Db::save_udate_order_meta($id, 'txn_id', $data['extra_fields']['txn_id']);
				}


				// INSERT ORDER INVOICE CODE
				$prefix = get_option('ihc_order_prefix_code');
				if (empty($prefix)){
					$prefix = 'iump';
				}
				$the_code = $id;
				while (strlen($the_code)<6){
					$the_code = '0' . $the_code;
				}
				$the_code = $prefix . $the_code;
				\Ihc_Db::save_udate_order_meta($id, 'code', $the_code);

				///Wp Admin Dashboard Notification
				\Ihc_Db::increment_dashboard_notification('orders');

				do_action('ihc_action_after_order_placed', (isset($data['uid'])) ? $data['uid'] : '', (isset($data['lid'])) ? $data['lid'] : '', $id );
				// @description after order was created. @param user id (integer), level id (integer)

				return $id;
			}
		}

		public function do_insert_update($txn_id=0){
			/*
			 * @param int
			 * @return none
			 */

			if ($txn_id){
				require_once IHC_PATH . 'classes/Transactions.class.php';
				$object = new Transactions($txn_id);
				$data = $object->get_data();

				global $wpdb;
				$table = $wpdb->prefix . 'ihc_orders';

				/// SEARCH BY AMOUNT LEVEL AND UID
				$q = $wpdb->prepare("SELECT id,uid,lid,amount_type,amount_value,automated_payment,status,create_date FROM $table
											WHERE
											uid=%d
											AND lid=%d
											AND amount_value=%s
											AND status='pending'
											ORDER BY create_date DESC
											LIMIT 1
				", $data['uid'], $data['lid'], $data['amount']);
				$query_result = $wpdb->get_row($q);
				if (!empty($query_result) && !empty($query_result->id)){
					$order_id = (isset($query_result->id)) ? $query_result->id : '';
				}

				if (empty($order_id)){
					/****************** INSERT **************/
					$automated_payment = ($this->is_recuring_payment($data)) ? 1 : 0;/// CHECK if it's recurring payment
					$the_id = $this->do_insert($data, $automated_payment);
				} else {
					/***************** SIMPLE UPDATE **************/
					if (!empty($data['status'])){
						$the_id = $order_id;
						$q = $wpdb->prepare("UPDATE $table SET status=%s WHERE id=%d", $data['status'], $the_id);
						$wpdb->query($q);
						do_action('ump_payment_check', $the_id, 'update');
					}
				}

				if (!empty($the_id)){
					/// update transactions
					$this->update_transaction_table($txn_id, $the_id);
					/// update order metas
					\Ihc_Db::save_udate_order_meta($the_id, 'txn_id', $txn_id);
				}
			}
		}

		public function get_data($order_id=0){
			/*
			 * @param none
			 * @return array
			 */
			if ($order_id){
				global $wpdb;
				$table = $wpdb->prefix . 'ihc_orders';
				$q = $wpdb->prepare("SELECT id,uid,lid,amount_type,amount_value,automated_payment,status,create_date FROM $table WHERE id=%d;", $order_id);
				$data = $wpdb->get_row($q);
				if (!empty($data)){
					return (array)$data;
				} else {
					return array();
				}
			}
		}

		private function update_transaction_table($txn_id='', $id=0){
			/*
			 * @param string, int
			 * @return none
			 */
			if ($txn_id && $id){
				global $wpdb;
				$table = $wpdb->prefix . 'indeed_members_payments';
				$txn_id = sanitize_text_field($txn_id);
				$query = $wpdb->prepare( "SELECT orders FROM $table WHERE txn_id=%s ;", $txn_id );
				$data = $wpdb->get_row( $query );
				if ($data && !empty($data->orders)){
					$ids = (isset($data->orders)) ? maybe_unserialize($data->orders) : '';
				}
				if ( isset( $ids ) && in_array( $id, $ids ) ){
						return;
				}
				$id = sanitize_text_field($id);
				$ids[] = (int)$id;
				$ids = serialize($ids);
				$query = $wpdb->prepare( "UPDATE $table SET orders=%s WHERE txn_id=%s ;", $ids, $txn_id );
				$made = $wpdb->query( $query );
			}
		}

		private function is_recuring_payment($data=array()){
			/*
			 * @param array
			 * @return boolean
			 */
			global $wpdb;
			$table = $wpdb->prefix . 'ihc_orders';
			$q = $wpdb->prepare("SELECT id FROM $table
									WHERE
									uid=%d
									AND lid=%d
									AND automated_payment=1
									AND status='Completed'
									", $data['uid'], $data['lid']
			);
			$query_result = $wpdb->get_row($q);
			if (isset($query_result->id)){
				return TRUE;
			}
			return FALSE;
		}


		public function get_metas($order_id=0){
			/*
			 * @param int
			 * @return array
			 */
			if ($order_id){
				global $wpdb;
				$table = $wpdb->prefix . 'ihc_orders_meta';
				$q = $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE order_id=%d ", $order_id);
				$data = $wpdb->get_results($q);
				if (!empty($data)){
					$array = array();
					foreach ($data as $object){
						$array[$object->meta_key] = $object->meta_value;
					}
					return $array;
				} else {
					return array();
				}
			}
		}

		public function get_meta_by_order_and_name($order_id=0, $meta_key=''){
				global $wpdb;
				$table = $wpdb->prefix . 'ihc_orders_meta';
				$q = $wpdb->prepare("SELECT meta_value FROM $table WHERE order_id=%d AND meta_key=%s ", $order_id, $meta_key);
				return $wpdb->get_var($q);
		}

	}
}
