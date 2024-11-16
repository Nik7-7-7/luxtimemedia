<?php
if (class_exists('IndeedImport') && !class_exists('Ihc_Indeed_Import')):

class Ihc_Indeed_Import extends IndeedImport{

	/*
	 * @param string ($entity_name)
	 * @param string ($entity_opt)
	 * @param object ($xml_object)
	 * @return none
	 */
	protected function do_import_custom_table($entity_name, $entity_opt, &$xml_object){
		global $wpdb;
		$table = $wpdb->prefix . $entity_name;

		if (!$xml_object->$entity_name->Count()){
			return;
		}

		switch ($entity_name){
			case 'ihc_notifications':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$insert_string = "VALUES(null,
												'" . $this->sanitize( (string)( $object->notification_type ) ) . "',
												'" . $this->sanitize( (string)( $object->level_id ) ) . "',
												'" . $this->sanitize( (string)( $object->subject ) ) . "',
												'" . $this->sanitize( (string)( $object->message ) ) . "',
												'" . $this->sanitize( (string)( $object->pushover_message ) ) . "',
												'" . $this->sanitize( (string)( $object->pushover_status ) ) . "',
												'" . $this->sanitize( (string)( $object->status ) ) . "'
						)";
						$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_user_levels':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$insert_string = "VALUES(null,
												'" . $this->sanitize( (string)( $object->user_id ) ) . "',
												'" . $this->sanitize( (string)( $object->level_id ) ) . "',
												'" . $this->sanitize( (string)( $object->start_time ) ) . "',
												'" . $this->sanitize( (string)( $object->update_time ) ) . "',
												'" . $this->sanitize( (string)( $object->expire_time ) ) . "',
												'" . $this->sanitize( (string)( $object->notification ) ) . "',
												'" . $this->sanitize( (string)( $object->status ) ) . "'
						)";
						$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_debug_payments':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$insert_string = "VALUES(null,
												'" . $this->sanitize( (string)( $object->source ) ) . "',
												'" . $this->sanitize( (string)( $object->message ) ) . "',
												'" . $this->sanitize( (string)( $object->insert_time ) ) . "'
						)";
						$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'indeed_members_payments':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$insert_string = "VALUES(null,
												'" . $this->sanitize( (string)( $object->txn_id ) ) . "',
												'" . $this->sanitize( (string)( $object->u_id ) ) . "',
												'" . $this->sanitize( (string)( $object->payment_data ) ) . "',
												'" . $this->sanitize( (string)( $object->history ) ) . "',
												'" . $this->sanitize( (string)( $object->orders ) ) . "',
												'" . $this->sanitize( (string)( $object->paydate ) ) . "'
						)";
						$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_coupons':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$insert_string = "VALUES(null,
												'" . $this->sanitize( (string)( $object->code ) ) . "',
												'" . $this->sanitize( (string)( $object->settings ) ) . "',
												'" . $this->sanitize( (string)( $object->submited_coupons_count ) ) . "',
												'" . $this->sanitize( (string)( $object->status ) ) . "'
						)";
						$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_orders':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->sanitize( (string)( $object->uid ) ) . "',
											'" . $this->sanitize( (string)( $object->lid ) ) . "',
											'" . $this->sanitize( (string)( $object->amount_type ) ) . "',
											'" . $this->sanitize( (string)( $object->amount_value ) ) . "',
											'" . $this->sanitize( (string)( $object->automated_payment ) ) . "',
											'" . $this->sanitize( (string)( $object->status ) ) . "',
											'" . $this->sanitize( (string)( $object->create_date ) ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_orders_meta':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$insert_string = "VALUES(null,
												'" . $this->sanitize( (string)( $object->order_id ) ) . "',
												'" . $this->sanitize( (string)( $object->meta_key ) ) . "',
												'" . $this->sanitize( (string)( $object->meta_value ) ) . "'
						)";
						$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_taxes':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->sanitize( (string)( $object->country_code ) ) . "',
											'" . $this->sanitize( (string)( $object->state_code ) ) . "',
											'" . $this->sanitize( (string)( $object->amount_value ) ) . "',
											'" . $this->sanitize( (string)( $object->label ) ) . "',
											'" . $this->sanitize( (string)( $object->description ) ) . "',
											'" . $this->sanitize( (string)( $object->status ) ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_dashboard_notifications':
				///
				break;
			case 'ihc_cheat_off':
				///
				break;
			case 'ihc_invitation_codes':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->sanitize( (string)( $object->code ) ) . "',
											'" . $this->sanitize( (string)( $object->settings ) ) . "',
											'" . $this->sanitize( (string)( $object->submited ) ) . "',
											'" . $this->sanitize( (string)( $object->repeat_limit ) ) . "',
											'" . $this->sanitize( (string)( $object->status ) ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_gift_templates':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
						$insert_string = "VALUES(null,
												'" . $this->sanitize( (string)( $object->lid ) ) . "',
												'" . $this->sanitize( (string)( $object->settings ) ) . "',
												'" . $this->sanitize( (string)( $object->status ) ) . "'
						)";
						$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_security_login':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->sanitize( (string)( $object->username ) ) . "',
											'" . $this->sanitize( (string)( $object->ip ) ) . "',
											'" . $this->sanitize( (string)( $object->log_time ) ) . "',
											'" . $this->sanitize( (string)( $object->attempts_count ) ) . "',
											'" . $this->sanitize( (string)( $object->locked ) ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'ihc_user_logs':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->sanitize( (string)( $object->uid ) ) . "',
											'" . $this->sanitize( (string)( $object->lid ) ) . "',
											'" . $this->sanitize( (string)( $object->log_time ) ) . "',
											'" . $this->sanitize( (string)( $object->log_content ) ) . "',
											'" . $this->sanitize( (string)( $object->create_date ) ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
		}

	}

	/*
	 * @param string (table name)
	 * @param string (insert values)
	 * @return none
	 */
	private function do_basic_insert($table='', $insert_values=''){
		global $wpdb;
		$query = "INSERT INTO $table $insert_values;";
		$wpdb->query( $query );
	}

	private function sanitize( $value='' )
	{
			global $wpdb;
			return sanitize_text_field( $value );
	}

}

endif;
