<?php
/**
 * Simply Schedule Appointments Gravityforms.
 *
 * @since   3.0.0
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Gravityforms.
 *
 * @since 3.0.0
 */
class SSA_Gravityforms {
	/**
	* Parent plugin class.
	*
	* @since 3.0.0
	*
	* @var   Simply_Schedule_Appointments
	*/
	protected $plugin = null;

	public static $map_payment_status_to_appointment = [
		"Processing"=>"pending_form",
		"Pending"=>"pending_form",
		"Authorized"=>"booked",
		"Paid"=>"booked",
		"Active"=>"booked",
		];
	/**
	* Constructor.
	*
	* @since  3.0.0
	*
	* @param  Simply_Schedule_Appointments $plugin Main plugin object.
	*/
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		if ( ! class_exists( 'GF_Field' ) ) {
			return;
		}

		GF_Fields::register( new SSA_Gf_Field_Appointment() );
		$this->hooks();
	}

	/**
	* Initiate our hooks.
	*
	* @since  3.0.0
	*/
	public function hooks() {
		add_action( 'gform_field_standard_settings', array( 'SSA_Gf_Field_Appointment', 'get_standard_settings' ), 10, 2 );
		add_action( 'gform_field_appearance_settings', array( 'SSA_Gf_Field_Appointment', 'get_appearance_settings' ), 10, 2 );

		add_action( 'gform_editor_js', array( $this, 'editor_script' ) );

		add_action( 'gform_entry_created', array( $this, 'book_appointment_after_form_submission' ), 10, 2 );
		add_filter( 'gform_entry_post_save', array( $this, 'update_appointment_after_form_save' ), 10, 2 );
		add_filter( 'gform_confirmation', array($this, 'custom_confirmation'), 10, 4 );

		add_action( 'gform_post_payment_action', array( $this,  'update_booking_payment_status' ), 10, 2 );

		add_filter( 'gform_zapier_field_value', array( $this, 'filter_zapier_field_value' ), 10, 4 );
		add_filter( 'gform_zapier_sample_field_value', array( $this, 'filter_zapier_sample_field_value' ), 10, 3 );

		add_filter( 'gform_webhooks_request_data', array( $this, 'filter_webhook_fields' ), 10, 4 );

		add_action( 'gform_user_registered', array( $this, 'link_appointment_to_newly_registered_user' ), 10, 3 );
		
		add_filter( 'gform_validation', array( $this, 'validate_slots_still_available' ), 10, 2 );

		add_filter( 'gform_field_settings_tabs', array( $this, 'register_booking_flow_settings_tab' ), 10, 2 );
			
		add_action( 'gform_field_settings_tab_content_ssa_booking_flow', array($this, 'register_booking_flow_settings_tab_content'), 0, 2 );
	}

	public function register_booking_flow_settings_tab ( $tabs, $form ) {
		if(ssa_should_render_booking_flow()){
			$tabs[] = array(
				// Define the unique ID for your tab.
				'id'             => 'ssa_booking_flow',
				// Define the title to be displayed on the toggle button your tab.
				'title'          =>  __('Booking Flow', 'simply-schedule-appointments'),
				// Define an array of classes to be added to the toggle button for your tab.
				// 'toggle_classes' => array( 'my_toggle_class_1', 'my_toggle_class_2' ),
				// Define an array of classes to be added to the body of your tab.
				// 'body_classes'   => array( 'my_body_class_1' ),
			);
		}
		
		return $tabs;
	}
	
	public function register_booking_flow_settings_tab_content( $form, $tab_id ) {
		$html = '';
		// select the booking flow
		$html .= $this->ssa_markup_select_booking_flow();
		// first available within duration settings
		$html .= $this->ssa_markup_first_available_within_duration_settings();
		// fallback flow
		$html .= $this->ssa_markup_select_fallback_booking_flow();
		// tabs for selecting time view, date view, and team member selection
		$html .= $this->ssa_markup_booking_flow_views();
		
		echo $html;
	}
		
	public function ssa_markup_select_booking_flow(){
		return '
		<li class=" ssa_booking_flow_setting field_setting" >
			<select name="field_ssa_booking_flow" id="field_ssa_booking_flow" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">
				<option class="ssa_booking_flow_option" value="expanded">' . __('Expanded', 'simply-schedule-appointments' ) . '</option>
				<option class="ssa_booking_flow_option" value="express">' . __('Express', 'simply-schedule-appointments' ) . '</option>
				<option class="ssa_booking_flow_option" value="first_available">' . __('First Available', 'simply-schedule-appointments' ) . '</option>
				<option class="ssa_booking_flow_option" value="appt_type_settings">' .__('Use default settings from appointment type', 'simply-schedule-appointments') . '</option>
			</select>
		</li>';
	}
	
	public function ssa_markup_first_available_within_duration_settings(){
		return '
			<li id="first-available-within-duration-settings" class=" ssa_booking_flow_setting field_setting" >
				<label for="field_ssa_first_available_within_duration_inputs field_setting" class="section_label">'.__("First Available Within", "simply-schedule-appointments").'</label>
				<div name="field_ssa_first_available_within_duration_inputs" id="field_ssa_first_available_within_duration_inputs">
					<span>
						<label for="field_ssa_first_available_within_duration" class="section_label">'.__("Duration", "simply-schedule-appointments").'</label>
						<input name="field_ssa_first_available_within_duration" id="field_ssa_first_available_within_duration" type="number" min="0" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">
					</span>
					
					<span>
						<label for="field_ssa_first_available_within_duration_unit" class="section_label">'.__("Duration Unit", "simply-schedule-appointments").'</label>
						<select name="field_ssa_first_available_within_duration_unit" id="field_ssa_first_available_within_duration_unit" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">
							<option class="first_available_within_duration" value="minutes">' . __('Minutes', 'simply-schedule-appointments' ) . '</option>
							<option class="first_available_within_duration" value="hours">' . __('Hours', 'simply-schedule-appointments' ) . '</option>
							<option class="first_available_within_duration" value="days">' . __('Days', 'simply-schedule-appointments' ) . '</option>
							<option class="first_available_within_duration" value="weeks">' . __('Weeks', 'simply-schedule-appointments' ) . '</option>
						</select>
					</span>
				</div>
			</li>';
	}
	
	public function ssa_markup_select_fallback_booking_flow(){
		return '
			<li id="fallback-booking-flow-settings" class=" ssa_booking_flow_setting field_setting" >
				<label for="field_ssa_fallback_booking_flow" class="section_label">'.__("Fallback Flow", "simply-schedule-appointments").'</label>
				<select name="field_ssa_fallback_booking_flow" id="field_ssa_fallback_booking_flow" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">
					<option class="ssa_fallback_booking_flow_option" value="expanded">' . __('Expanded', 'simply-schedule-appointments' ) . '</option>
					<option class="ssa_fallback_booking_flow_option" value="express">' . __('Express', 'simply-schedule-appointments' ) . '</option>
				</select>
			</li>';
	}
	
	public function ssa_markup_booking_flow_views(){
		return '
			<li id="booking-flow-views-settings" class=" ssa_booking_flow_setting field_setting" >
				<div role="tablist" aria-orientation="horizontal" class="ssa-tab-container">
					<button onclick="ssaBookingFlowViewsSwitchActiveTab(\'date-view-tab\')" type="button" role="tab" id="date-view-tab" aria-controls="date-view-tab-contents" class="ssa-tab ">' . __("Date View", "simply-schedule-appointments") . '</button>
					<button onclick="ssaBookingFlowViewsSwitchActiveTab(\'time-view-tab\')" type="button" role="tab" id="time-view-tab" aria-controls="time-view-tab-contents" class="ssa-tab ">' . __("Time View", "simply-schedule-appointments") . '</button>
				</div>
				
				<span id="date-view-tab-contents">
					<select aria-labelledby="date-view-tab" name="field_ssa_booking_flow_date_view" id="field_ssa_booking_flow_date_view" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">
						<option class="ssa_booking_flow_date_view_option" value="week">' . __('Weekly', 'simply-schedule-appointments' ) . '</option>
						<option class="ssa_booking_flow_date_view_option" value="month">' . __('Monthly', 'simply-schedule-appointments' ) . '</option>
						<option class="ssa_booking_flow_date_view_option" value="only_available">' . __('Only available dates', 'simply-schedule-appointments' ) . '</option>
					</select>
				</span>
				
				<span id="time-view-tab-contents">
					<select aria-labelledby="time-view-tab" name="field_ssa_booking_flow_time_view" id="field_ssa_booking_flow_time_view" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">
						<option class="ssa_booking_flow_time_view_option" value="time_of_day_columns">' . __('Time of day columns', 'simply-schedule-appointments' ) . '</option>
						<option class="ssa_booking_flow_time_view_option" value="single_column">' . __('Single column', 'simply-schedule-appointments' ) . '</option>
					</select>
				</span>
			</li>';
	}
	/**
	 * 
	 * prevent submitting a form with appointment slots that are not available anymore
	 * 
	 */
	function validate_slots_still_available( $validation_result ) {
		$form = $validation_result['form'];
			
		//finding Field with ID of 1 and marking it as failed validation
		foreach( $form['fields'] as &$field ) {
			if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
				// skip all other field types
				continue;
			}
			
			$appointment_id = (int)esc_attr( rgpost("input_{$field->id}") );
			
			// when the validation is triggered on a multi page form
			// with the appointment field not reached/selected yet
			if ( empty($appointment_id) ) {
				continue;
			}
			
			$appointment_obj = new SSA_Appointment_Object( $appointment_id );
			if( $appointment_obj->data['status']==="abandoned" && ! $this->plugin->appointment_model->is_prospective_appointment_available( $appointment_obj->get_appointment_type(), $appointment_obj->start_date_datetime ) ){
				// set the form validation to false
				$validation_result['is_valid'] = false;
				//marking the field as failed validation
				$field->failed_validation = true;
				$_POST['input_'.$field->id] = ''; // clear the field value so it doesn't try to load the same appointment again
				$field->validation_message = 'The time you picked is not available anymore, pick another time!';
			}
		}

		//Assign modified $form object back to the validation result
		$validation_result['form'] = $form;
		return $validation_result;
	}
	
	/**
	 * this function is needed to pass unique_id of entry, because it's evaluated after the entry is submitted
	 * this action fires only when an entry is submitted, it won't fire when an entry is updated by the admin in GForms dashboard
	 */
	public function update_appointment_after_form_save( $entry, $form ){
		// wrapped this in a try catch, as its not critical if it fails
		// should never fail, but just in case
		try {
			$form_id = $form['id'];
			$entry_id = $entry['id'];
			
			foreach ($form['fields'] as $key => $field) {
				if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
					// skip all other field types
					continue;
				}

				if ( empty( $entry[$field->id] ) ) {
					// No appointment id was submitted for this field
					continue;
				}
				
				$appointment_id = (int)esc_attr( $entry[$field->id] );
				$correct_hash = $this->plugin->appointment_model->get_id_token( array( 'id' => $appointment_id ) );
				$_POST["appointment_token"] = $correct_hash . $appointment_id;
				$appointment_obj = new SSA_Appointment_Object( $appointment_id );
				$customer_information = $appointment_obj->customer_information;
				
				$should_update=false;
				// extract values of fields we want to copy, should run once only
				foreach ($form['fields'] as $current_field_key => $current_field) {
					if ( apply_filters( 'ssa/forms/gravity/copy_hidden_fields_to_ssa', false, $form_id, $current_field->id, $current_field, $entry_id, $field->id  ) ) {
						
						if( $current_field->type === 'uid' ){
							$should_update=true;
							$field = RGFormsModel::get_field( $form, $current_field->id );
							$value = is_object( $field ) ? $field->get_value_export( $entry, $current_field->id, true ) : '';
							
							$label_key = $current_field->label;
							// if we have more than one field with the same label, append a number to the subsequent ones
							if ( ! empty ( $customer_information[ $label_key ] ) ) {
								for ($i=2; $i < 100; $i++) { 
									if ( empty( $customer_information[$label_key . ' ' . $i] ) ) {
										$label_key = $label_key . ' ' . $i;
										break;
									}
								}
							}
							
							$customer_information[$label_key] = $value;
						}
					}
				}
				
				if( $should_update ){
					$appointment_update_data = array(
						'customer_information' => $customer_information
					);
					$this->plugin->appointment_model->update( $appointment_obj->id, $appointment_update_data );
				}
				
			}
			return $entry;
		} catch (\Throwable $th) {
			ssa_debug_log( 'Error in update_appointment_after_form_save: ' . $th->getMessage(), 10 );
			return $entry;
		}
	}
	
	/**
	 * Updates appointment when payment status is updated
	 * @param  [type] $entry  [description]
	 */ 
	public function update_booking_payment_status( $entry, $action ) {
		// gets the entry with payment status updated
		$db_entry=GFAPI::get_entry($entry['id']);
		if( ! isset( $db_entry['payment_status'] ) ){
			return;
		}

		if ( ! isset(self::$map_payment_status_to_appointment[$db_entry['payment_status']])){
			ssa_debug_log( 'Payment status ' . $db_entry['payment_status'] . ' is not mapped to an appointment status in class-gravityforms.php', 10 );
			return;
		}

		// at this point we are sure the entry has a non-null payment status with mapping to appointment status
		$appointment_status = self::$map_payment_status_to_appointment[$db_entry['payment_status']];

		$form = GFAPI::get_form( $entry['form_id'] );
		if ( false === $form ) {
			return;
		}
		
		// iterate through all fields in the form and update ssa_appointment statuses
		foreach ($form['fields'] as $key => $field) {
			if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
				// skip all other field types
				continue;
			}

			if ( empty( $entry[$field->id] ) ) {
				// No appointment id was submitted for this field
				continue;
			}
			$appointment_id = (int)esc_attr( $entry[$field->id] );

			$appointment_obj = new SSA_Appointment_Object( $appointment_id );
			// if status already equal or if the logic will take booked->pending_form, then skip
			if ( $appointment_obj->status === $appointment_status || ( $appointment_obj->status === 'booked' && $appointment_status === 'pending_form')) {
				// no need for update
				continue;
			}

			$this->plugin->appointment_model->update( $appointment_id, array(
				'status' => $appointment_status,
			) );
		}
	}

	public function custom_confirmation( $confirmation, $form, $entry, $ajax ) {
		foreach ($form['fields'] as $key => $field) {
			if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
				// skip all other field types
				continue;
			}

			if ( empty( $entry[$field->id] ) ) {
				// No appointment id was submitted for this field
				continue;
			}
			$appointment_id = (int)esc_attr( $entry[$field->id] );
			$correct_hash = $this->plugin->appointment_model->get_id_token( array( 'id' => $appointment_id ) );
			$appointment_token = $correct_hash . $appointment_id;
			if(isset($confirmation['redirect'])){
				$confirmation['redirect'] = $confirmation['redirect'] . "?appointment_action=edit&appointment_token=" . $appointment_token;
			} else {
				$_GET['appointment_action'] = 'edit';
				$_GET['appointment_token'] = $appointment_token;
			}
		}

		return $confirmation;
	}
  
	public function link_appointment_to_newly_registered_user( $user_id, $feed, $entry ) {
		if ( empty( $entry['form_id'] ) ) {
			return;
		}

		$form = GFAPI::get_form( $entry['form_id'] );
		if ( false === $form ) {
			return;
		}

		foreach ($form['fields'] as $key => $field) {
			if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
				// skip all other field types
				continue;
			}

			if ( empty( $entry[$field->id] ) ) {
				// No appointment id was submitted for this field
				continue;
			}
			$appointment_id = (int)esc_attr( $entry[$field->id] );
			$appointment_obj = new SSA_Appointment_Object( $appointment_id );

			/* Update appointment */
			$appointment_update_data = array(
				'customer_id' => $user_id,
				'author_id' => $user_id,
			);

			$this->plugin->appointment_model->update( $appointment_obj->id, $appointment_update_data );
		}
	}

	public function filter_zapier_field_value( $field_value, $form_id, $field_id, $entry ) {
		if ( empty( $field_value ) ) {
			return $field_value;
		}
		
		$field = GFAPI::get_field( $form_id, $field_id );
		if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
			// skip all other field types
			return $field_value;
		}

		$appointment = new SSA_Appointment_Object( $field_value );
		$webhook_payload = $appointment->get_webhook_payload( 'gfzapier' );
		return $webhook_payload;
	}

	public function filter_zapier_sample_field_value(  $field_value, $form_id, $field_id ) {
		$field = GFAPI::get_field( $form_id, $field_id );
		if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
			// skip all other field types
			return $field_value;
		}

		$appointments = ssa()->appointment_model->query( array(
			'number' => 1,
		) );
		if ( empty( $appointments[0]['id'] ) ) {
			return $field_value;
		}

		$appointment = new SSA_Appointment_Object( $appointments[0]['id'] );
		$webhook_payload = $appointment->get_webhook_payload( 'gfzapier' );
		return $webhook_payload;
	}

	/**
	 * Checks if $request_data from the Gravity Forms Webhook addon contains SSA appointment fields.
	 * If it does, then replace the ID with the whole appointment webhook payload.
	 *
	 * @since 4.8.9
	 *
	 * @param array $request_data HTTP request data.
	 * @param array $feed         The current Feed object.
	 * @param array $entry        The current Entry object.
	 * @param array $form         The current Form object.
	 * @return array
	 */
	public function filter_webhook_fields( $request_data, $feed, $entry, $form ) {
		// First, let's find out which fields on the form are 'SSA_Gf_Field_Appointment' type.
		$appointment_field_ids = array();

		foreach ( $form['fields'] as $field ) {
			if ( is_a( $field, 'SSA_Gf_Field_Appointment' ) && array_key_exists( $field['id'], $request_data ) ) {
				$appointment_field_ids[] = $field['id'];
			}
		}

		// If no appointment fields are found, bail.
		if ( empty( $appointment_field_ids ) ) {
			return $request_data;
		}

		foreach ( $appointment_field_ids as $field_id ) {
			$field_value = $request_data[ $field_id ];
			$appointment = new SSA_Appointment_Object( $field_value );

			$request_data[ $field_id ] = $appointment->get_webhook_payload( 'gfwebhook' );
		}

		return $request_data;
	}	

	public function book_appointment_after_form_submission( $entry, $form ) {
		$pending_stripe_payment=false;
		$pending_paypal_payment=false;

		if ( function_exists( 'gf_stripe' ) && gf_stripe()->is_stripe_checkout_enabled() ) {
			$reflection = new ReflectionClass(gf_stripe());
			$feed = $reflection->getProperty('current_feed');
			$feed->setAccessible(true);
			if(!empty( $feed->getValue(gf_stripe()) ) ){
				// consider appointment pending_form until another hook confirms a payment
				$pending_stripe_payment=true;
			};
		}

		if ( function_exists( 'gf_ppcp' ) ) {
			$reflection = new ReflectionClass(gf_ppcp());
			$feed = $reflection->getProperty('current_feed');
			$feed->setAccessible(true);
			if(!empty( $feed->getValue(gf_ppcp()) ) ){
				// consider appointment pending_form until another hook confirms a payment
				$pending_paypal_payment=true;
			};
		}

		$form_id = $form['id'];
		$entry_id = $entry['id'];

		foreach ($form['fields'] as $key => $field) {
			if ( ! is_a( $field, 'SSA_Gf_Field_Appointment' ) ) {
				// skip all other field types
				continue;
			}

			if ( empty( $entry[$field->id] ) ) {
				// No appointment id was submitted for this field
				continue;
			}
			$appointment_id = (int)esc_attr( $entry[$field->id] );
			$correct_hash = $this->plugin->appointment_model->get_id_token( array( 'id' => $appointment_id ) );
			$_POST["appointment_token"] = $correct_hash . $appointment_id;

			$appointment_obj = new SSA_Appointment_Object( $appointment_id );
			if ( $appointment_obj->status !== 'pending_form' && $appointment_obj->status !== 'abandoned' ) {
				// Don't handle other statuses (possibly abandoned)
				continue;
			}
			
			// check if an abandoned time slot is still available on form submission
			if ( $appointment_obj->status === 'abandoned' && !$this->plugin->appointment_model->is_prospective_appointment_available( $appointment_obj->get_appointment_type(), $appointment_obj->start_date_datetime ) ) {
				// time slot already booked
				continue;
			}
			

			$customer_information = $appointment_obj->customer_information;

			/* Populate name field */
			$name_field_id = null;
			if ( ! empty( $field->map_field_name ) ) {
				$name_field_id = $field->map_field_name;
			} else {
				// Default to the first Name field if no field mapping is stored
				foreach ($form['fields'] as $current_field_key => $current_field) {
					if ( $current_field->type === 'name' ) {
						$name_field_id = $current_field->id;
						break;
					}
				}
			}
			if ( ! empty( $name_field_id ) ) {
				if ( ! empty( $entry[$name_field_id] ) ) {
					$customer_information['Name'] = $entry[$name_field_id];
				} else {
					$value_parts = array();
					foreach ($entry as $field_id_key => $value_part) {
						if ( 0 === strpos( $field_id_key, $name_field_id.'.' ) ) {
							if ( ! empty( $value_part ) ) {
								$value_parts[] = $value_part;
							}
						}
					}
					$customer_information['Name'] = implode( ' ', $value_parts );
				}
			} else {
				$customer_information['Name'] = '';
			}

			/* Populate email field */
			$email_field_id = null;
			if ( ! empty( $field->map_field_email ) ) {
				$email_field_id = $field->map_field_email;
				if ( ! empty( $entry[$email_field_id] ) && ! is_email( $entry[$email_field_id] ) ) {
					$email_field_id = null;
				}
			} else {
				// Default to the first email field if no field mapping is stored
				foreach ($form['fields'] as $current_field_key => $current_field) {
					if ( $current_field->type === 'email' ) {
						$email_field_id = $current_field->id;
						break;
					}
				}
			}
			
			if ( ! empty( $email_field_id ) ) {
				$customer_information['Email'] = $entry[$email_field_id];
			} else {
				$customer_information['Email'] = '';
			}


			/* Populate phone field */
			$phone_field_id = null;
			if ( ! empty( $field->map_field_phone ) ) {
				$phone_field_id = $field->map_field_phone;
			} else {
				// Default to the first phone field if no field mapping is stored
				foreach ($form['fields'] as $current_field_key => $current_field) {
					if ( $current_field->type === 'phone' ) {
						$phone_field_id = $current_field->id;
						break;
					}
				}
			}
			
			if ( ! empty( $phone_field_id ) ) {
				$ssa_phone_field_label = $this->plugin->customer_information->get_phone_number_field_for_appointment_type( $appointment_obj->get_appointment_type() );
				if ( false !== $ssa_phone_field_label ) {
					$customer_information[$ssa_phone_field_label] = $entry[$phone_field_id];
				} else {
					$phone_field_id = null;
				}
			}

			/* Populate sms_optin field */
			$sms_optin_field_id = null;
			if ( ! empty( $field->map_field_sms_optin ) ) {
				$sms_optin_field_id = $field->map_field_sms_optin;
			}
			
			$allow_sms = null;
			if ( ! empty( $sms_optin_field_id ) ) {
				$allow_sms = $entry[$sms_optin_field_id.'.1'];
			}

			foreach ($form['fields'] as $current_field_key => $current_field) {
				if ( $current_field->type === 'ssa_appointment' ) {
					continue;
				}

				if ( ! empty( $name_field_id ) && $name_field_id == $current_field->id ) {
					continue;
				}

				if ( ! empty( $email_field_id ) && $email_field_id == $current_field->id ) {
					continue;
				}

				if ( ! empty( $phone_field_id ) && $phone_field_id == $current_field->id ) {
					continue;
				}
				
				if ( ! empty( $sms_optin_field_id ) && $sms_optin_field_id == $current_field->id ) {
					continue;
				}
				
				if ( $current_field->type === 'password' ) {
					continue; // don't store passwords in SSA Customer Information fields
				}

				if ( ! apply_filters( 'ssa/forms/gravity/copy_hidden_fields_to_ssa', false, $form_id, $current_field->id, $current_field, $entry_id, $field->id  ) ) {
					if ( $current_field->type === 'hidden' ) {
						continue;
					}
	
					if ( $current_field->visibility === 'administrative' ) {
						continue;
					}
	
					if ( $current_field->visibility === 'hidden' ) {
						continue;
					}
				}
				
				if( ! apply_filters('ssa/forms/gravity/should_copy_field_to_ssa', true, $form_id, $field->id, $current_field->type, $current_field->id ) ){
					continue;
				}

				$field = RGFormsModel::get_field( $form, $current_field->id );
				$value = is_object( $field ) ? $field->get_value_export( $entry, $current_field->id, true ) : '';
				if ( empty( $value ) ) {
					continue; // don't synchronize empty data
				}

				$label_key = $current_field->label;
				
				// if we have more than one field with the same label, append a number to the subsequent ones
				if ( ! empty ( $customer_information[ $label_key ] ) ) {
					for ($i=2; $i < 100; $i++) { 
						if ( empty( $customer_information[$label_key . ' ' . $i] ) ) {
							$label_key = $label_key . ' ' . $i;
							break;
						}
					}
				}
				
				$customer_information[$label_key] = str_replace(array( "\r\n", "\r", "\n"), "\n", $value);
			}



			/* Update appointment meta */
			$appointment_meta = array();

			$appointment_meta['form_integration'] = 'gravity';
			$appointment_meta['gravity_form_id'] = $form_id;
			$appointment_meta['gravity_entry_id'] = $entry_id;
			$appointment_meta['gravity_entry_admin_url'] = admin_url( 'admin.php?page=gf_entries&view=entry&id='.$form_id.'&lid='.$entry_id );
			
			$customer_email_user_id = null;
			if ( ! empty ( $customer_information['Email'] ) ) {
				$customer_email_user = get_user_by( 'email', $customer_information['Email'] );
				if( !empty( $customer_email_user ) ){
					$customer_email_user_id = $customer_email_user->id;
				}
			}

			/* Update appointment */
			$appointment_data = array(
				'customer_information' => $customer_information,
				'status' => 'pending_form',
				'meta_data' => $appointment_meta
			);
			
			if( !empty( $customer_email_user_id ) ){
				$appointment_data['customer_id'] = $customer_email_user_id;
			}
			
			// if no pending payment, appointment is considered booked with no expiry date
			if( ! $pending_stripe_payment && ! $pending_paypal_payment ) {
				$appointment_data['expiration_date'] = false;
				$appointment_data['status'] = 'booked';
			}

			if( ! is_null ( $sms_optin_field_id ) ) {
				// if the opt-in field is mapped to a Gravity Forms field,
				// only allow SMS if the field was checked
				if( ! empty( $allow_sms )){
					// regardless of current SSA SMS setup, and whether it's active or not
					$appointment_data['allow_sms'] = 1;
				}
			} else if ( $this->plugin->settings_installed->is_enabled( 'sms' ) ) {
				// the opt-in field is not mapped to a Gravity Forms field
				// preserve the existing behavior, allow SMS whenever the phone field is mapped
				if ( ! empty( $phone_field_id ) ) {
					$appointment_data['allow_sms'] = 1;
				}
			}

			$appointment_update_data = $appointment_data;
			$this->plugin->appointment_model->update( $appointment_obj->id, $appointment_update_data );
		}
	}

	public function editor_script(){
			?>
			<script type='text/javascript'>
				let ssaIframeSrcDebounceTimeoutId;
				let ssaBookingFlowViewsActiveTab;
				let ssaBookingFlowSupported = <?php echo ssa_should_render_booking_flow(); ?>;
				
				let ssaTimeUnitToMinutesFactorMap = {
					'minutes' : 1,
					'hours' : 60 * 1,
					'days' : 24 * 60 * 1,
					'weeks' : 7 * 24 * 60 * 1,
				}

				let ssaFieldSettingsDefaultsMap = {
					flow: "appt_type_settings",
					fallback_flow: "expanded",
					first_available_within_minutes: 1,
					first_available_within_duration_unit: 'weeks',
					date_view: "week",
					time_view:  "time_of_day_columns"
				}
				
				// a map of inputs that correspond to one field setting
				let ssaFieldInputIdsToFieldSettingsMap = {
					// appointment types control
					"field_ssa_appointment_types_labels_filter" : "appointment_types_label",
					"field_ssa_appointment_types_filter" : "appointment_types_filter",
					
					// booking flow control
					"field_ssa_booking_flow" : "flow",
					"field_ssa_fallback_booking_flow" : "fallback_flow",
					"field_ssa_first_available_within_duration" : "first_available_within_minutes" ,
					"field_ssa_first_available_within_duration_unit" : "first_available_within_duration_unit",
					"field_ssa_booking_flow_date_view" : "date_view",
					"field_ssa_booking_flow_time_view" : "time_view",
					
					// map fields
					"field_ssa_map_field_name": "map_field_name",
					"field_ssa_map_field_email": "map_field_email",
					"field_ssa_map_field_phone": "map_field_phone",
					"field_ssa_map_field_sms_optin": "map_field_sms_optin"
				}
				
				let ssaCustomFieldPropertySettersMap = {
					// note: remember to set the property within the method, the methods should be wrappers around the default setter
					
					// this is triggered when the unit changes, the side effect here is multiplying the first_available_within_minutes value by this changed unit/factor
					"first_available_within_duration_unit" : (fieldPropertyKey, fieldPropertyValue) => {
						let factor = ssaTimeUnitToMinutesFactorMap[fieldPropertyValue]
						let durationInMinutes = (jQuery('#field_ssa_first_available_within_duration_unit').val() || 1) * factor
						SetFieldProperty("first_available_within_minutes", durationInMinutes)
						SetFieldProperty(fieldPropertyKey, fieldPropertyValue)
					},
					// the stored first_available_within_minutes value is a multiple of the input value
					"first_available_within_minutes" : (fieldPropertyKey, fieldPropertyValue) => {
						let factor = ssaTimeUnitToMinutesFactorMap[field.first_available_within_duration_unit] || ssaTimeUnitToMinutesFactorMap[ssaFieldSettingsDefaultsMap["first_available_within_duration_unit"]]
						let durationInMinutes = (fieldPropertyValue || 1) * factor
						SetFieldProperty(fieldPropertyKey, durationInMinutes)
					}
				}
				
				/**
				 * Helper functions
				 */
				
				function ssaGetCurrentIframeSrc(selectedField){
					return jQuery(`#field_${selectedField['id']} iframe`)[0].src;
				}
				
				function ssaGenerateIframeSrcFromField(selectedField){
					// the logic here would be the exact logic we pass to the ssa()->shortcodes->ssa_booking( $args );
					// we only pass the url params that are most up to date with user selection in the UI
					let {
						appointment_types_filter,
						appointment_type_id,
						appointment_types_subset,
						appointment_types_label,
						display_all_appointment_types,
						flow,
						fallback_flow,
						first_available_within_minutes,
						first_available_within_duration_unit,
						date_view,
						time_view
					} = selectedField;
					
					let currentIframeSrc = ssaGetCurrentIframeSrc(selectedField)
					let newIframeSrc = currentIframeSrc;
	
					newIframeSrc = newIframeSrc.replace(/&label([^&]*)/, "labels" === appointment_types_filter && appointment_types_label ? `&label=${appointment_types_label}` : '&label');
					
					// we stopped using 'type' inside of gravity forms integration, we always use 'types'
					newIframeSrc = newIframeSrc.replace(/&type([^&]*)/, '&type');
					
					if(display_all_appointment_types){
						newIframeSrc = newIframeSrc.replace(/&types([^&]*)/, '&types');
					} else {
						newIframeSrc = newIframeSrc.replace(/&types([^&]*)/, "types" === appointment_types_filter && appointment_types_subset ? `&types=${appointment_types_subset}` : '&types');
					}
					
					
					newIframeSrc = newIframeSrc.replace(/&flow([^&]*)/, ssaBookingFlowSupported && flow && flow !== "appt_type_settings" ? `&flow=${flow}` : '&flow');
					
					// only when flow is set to first_available
					newIframeSrc = newIframeSrc.replace(/&fallback_flow([^&]*)/, ssaBookingFlowSupported && ["first_available"].includes(flow) && fallback_flow ? `&fallback_flow=${fallback_flow}` : '&fallback_flow');
					newIframeSrc = newIframeSrc.replace(/&suggest_first_available([^&]*)/, ssaBookingFlowSupported && ["first_available"].includes(flow) ? `&suggest_first_available=1` : '&suggest_first_available');
					newIframeSrc = newIframeSrc.replace(/&suggest_first_available_within_minutes([^&]*)/, ssaBookingFlowSupported &&  ["first_available"].includes(flow) && first_available_within_minutes ? `&suggest_first_available_within_minutes=${first_available_within_minutes}` : '&suggest_first_available_within_minutes');
					
					// time_view not supported in appt_type_settings flow
					newIframeSrc = newIframeSrc.replace(/&time_view([^&]*)/, ssaBookingFlowSupported && ["expanded", "express", "first_available"].includes(flow) && time_view ? `&time_view=${time_view}` : '&time_view');
					
					// date_view only supported in expanded & first_available flows
					newIframeSrc = newIframeSrc.replace(/&date_view([^&]*)/, ssaBookingFlowSupported && ((("first_available" === flow && "express" !== fallback_flow ) || "expanded" === flow ) &&  date_view) ? `&date_view=${date_view}` : '&date_view');
					
					// avoid setting same src by including a random number
					while(currentIframeSrc === newIframeSrc){
						newIframeSrc = newIframeSrc.replace(/(&types[^&]*)/,`$1&${Math.floor(Math.random() * 100)}` )
					}
					return newIframeSrc;
				}
				
				/**
				 * Adds 'ssa-hidden' class to elements of firstSelector and removes 'ssa-hidden' class on elements of secondSelector
				 *
				 * @param {string} firstSelector - Selector to hide
				 * @param {string} secondSelector - Selector to show
				 */
				function ssaHideFirstShowSecond(firstSelector,secondSelector){
					if(firstSelector){
						jQuery(firstSelector).addClass('ssa-hidden')
					}
					if(secondSelector){
						// prevents dynamically added inline styles from ruining our UI logic
						jQuery(secondSelector).attr("role") !== "tabpanel" && jQuery(secondSelector).attr("style", "")
						jQuery(secondSelector).removeClass('ssa-hidden')
					}
				}
				
				function ssaHideCustomTabs(){
					ssaHideFirstShowSecond('#ssa_booking_flow_tab','')
					ssaHideFirstShowSecond('#ssa_booking_flow_tab_toggle','')
				}
				
				function ssaChangeHelpLink(){
					let descriptionText = <?php echo "'" . __( 'Add an Appointment field to your form', 'simply-schedule-appointments' ) . "'"; ?>;
					let linkText = <?php echo "'" . __( 'Learn how this works', 'simply-schedule-appointments' ) . "'"; ?>;
					setTimeout(() => {
						jQuery('#sidebar_field_text')[0].innerHTML= descriptionText + " <a target='_blank' href='https://simplyscheduleappointments.com/guides/gravity-forms-appointment-field/?utm_source=gf-field&utm_medium=guides&utm_campaign=support-help&utm_content=gf-learn-more'>" + linkText + "</a>";
					}, 0)
				}
				
				function ssaShowCustomTabs(){
					// show the booking flow settings tab and contents
					ssaHideFirstShowSecond('','#ssa_booking_flow_tab')
					ssaHideFirstShowSecond('','#ssa_booking_flow_tab_toggle')
					
					// Move custom tab and corresponding content before Appearance tab
					jQuery('#ssa_booking_flow_tab_toggle').insertBefore('#appearance_tab_toggle')
					jQuery('#ssa_booking_flow_tab').insertAfter('#ssa_booking_flow_tab_toggle')
				}
				
				function ssaBookingFlowViewsSwitchActiveTab(activeTab){
					ssaBookingFlowViewsActiveTab = activeTab
					let tabs = [
						'time-view-tab',
						'date-view-tab'
					];
					jQuery(`#${activeTab}`).addClass('ssa-tab-active')
					jQuery(`#${activeTab}`).attr('aria-selected', true)
					ssaHideFirstShowSecond("",`#${activeTab}`)
					ssaHideFirstShowSecond("",`#${activeTab}-contents`)
					tabs.forEach((tabID)=>{
						if(activeTab!=tabID){
							jQuery(`#${tabID}`).removeClass('ssa-tab-active')
							ssaHideFirstShowSecond(`#${tabID}-contents`,"")
						}
					})
				}
				
				/**
				 * End of stateless helper functions section
				 */
				
				//adding setting to fields of type "ssa_appointment"
				fieldSettings.ssa_appointment += ', .ssa_styles_setting';

				//adding setting to fields of type "ssa_appointment"
				fieldSettings.ssa_appointment += ', .ssa_appointment_type_setting';
				fieldSettings.ssa_appointment += ', .ssa_booking_flow_setting';
				fieldSettings.ssa_appointment += ', .ssa_map_field_name_setting';
				fieldSettings.ssa_appointment += ', .ssa_map_field_email_setting';
				fieldSettings.ssa_appointment += ', .ssa_map_field_phone_setting';
				fieldSettings.ssa_appointment += ', .ssa_map_field_sms_optin_setting';
				
				// the change handler for all appointment type checkboxes
				function ssaToggleAppointmentType(slug, checked){
					field = GetSelectedField();
					// if 'All' was clicked
					if(slug==="ssa_appointment_type_all"){
						// if 'All' was checked
						if(checked){
							SetFieldProperty("display_all_appointment_types", true);
						} else {
							SetFieldProperty("display_all_appointment_types", false);
						}
					} else {
						let displayedSlugs;
						field["appointment_types_subset"] !== "" ? displayedSlugs = new Set(field["appointment_types_subset"]?.split(",")) : displayedSlugs = new Set();
						checked? displayedSlugs.add(slug) : displayedSlugs.delete(slug);
						displayedSlugs = [...displayedSlugs].join(",");
						SetFieldProperty("appointment_types_subset",displayedSlugs);
					}
					ssaRegenerateUIFromField(field)
				}
				
				// only hides/shows different UI elements in settings
				function ssaSyncDisplayedSettingsFromFieldSettings(field){
					let {
							appointment_types_filter,
							display_all_appointment_types,
							flow,
							fallback_flow,
							first_available_within_minutes,
							first_available_within_duration_unit,
							date_view,
							time_view
						} = field;

					if ("labels" === appointment_types_filter) {
						ssaHideFirstShowSecond('.ssa_appointment_types_filter','.ssa_appointment_types_labels_filter');
					} else {
						ssaHideFirstShowSecond('.ssa_appointment_types_filter','.ssa_appointment_types_types_filter');
					}
					
					if (display_all_appointment_types) {
						ssaHideFirstShowSecond(".appointment_type","");
					} else {
						ssaHideFirstShowSecond("",".appointment_type");
					}
					
					if(ssaBookingFlowSupported){
						["expanded", "express", "appt_type_settings"].includes(flow) ? ssaHideFirstShowSecond("#first-available-within-duration-settings","") : ssaHideFirstShowSecond("", "#first-available-within-duration-settings");
						
						["expanded", "express", "appt_type_settings"].includes(flow) ? ssaHideFirstShowSecond("#fallback-booking-flow-settings","") : ssaHideFirstShowSecond("", "#fallback-booking-flow-settings");
						
						["expanded", "express", "first_available"].includes(flow) ? ssaHideFirstShowSecond("", "#booking-flow-views-settings") : ssaHideFirstShowSecond("#booking-flow-views-settings", "");
						
						if(["expanded", "first_available"].includes(flow)){
							ssaBookingFlowViewsSwitchActiveTab(ssaBookingFlowViewsActiveTab || "date-view-tab");
						}
						
						if("express" === flow || ("first_available" === flow && "express" === fallback_flow)){
							ssaHideFirstShowSecond("#date-view-tab","");
							ssaBookingFlowViewsSwitchActiveTab("time-view-tab");
						}else{
							ssaHideFirstShowSecond("", "#date-view-tab");
						}
						
						"express" === flow && ssaBookingFlowViewsSwitchActiveTab("time-view-tab");
						
						"appt_type_settings" === flow && ssaHideFirstShowSecond("#booking-flow-views-settings","");
						// if the booking flow feature is not supported, the custom tab dedicated to booking flow will not render, no need to hide any elements
					}
				}
				
				// syncs the value of an input, or whether a checkbox is checked or unchecked
				function ssaSyncInputsFromFieldSettings(field){
					let {
							appointment_types_filter,
							appointment_type_id,
							appointment_types_subset,
							appointment_types_label,
							display_all_appointment_types,
							flow,
							fallback_flow,
							first_available_within_minutes,
							first_available_within_duration_unit,
							date_view,
							time_view
						} = field;
						
					Object.keys(ssaFieldInputIdsToFieldSettingsMap)
					.forEach((inputID)=>{
						let fieldPropertyKey = ssaFieldInputIdsToFieldSettingsMap[inputID]
						let fieldPropertyValue = field[fieldPropertyKey] || ssaFieldSettingsDefaultsMap[fieldPropertyKey]
						if("first_available_within_minutes" === fieldPropertyKey){
							durationInCurrentUnits = fieldPropertyValue / ssaTimeUnitToMinutesFactorMap[field["first_available_within_duration_unit"] || ssaFieldSettingsDefaultsMap["first_available_within_duration_unit"]]
							jQuery(`#${inputID}`).val(durationInCurrentUnits)
						}else{
							jQuery(`#${inputID}`).val(fieldPropertyValue)
						}
						// when setting defaults if the field property is still empty
						// firing ssaSetFieldProperty( key, undefined ) will cause an infinite loop
						if(!field[fieldPropertyKey] && fieldPropertyValue){
							ssaSetFieldProperty(fieldPropertyKey, fieldPropertyValue)
						}
					})
					
					// 
					jQuery(".appointment_type").prop("checked", false );
					
					jQuery.each(field["appointment_types_subset"]?.split(","),(index, value)=>{
						value && jQuery(`#${value}`).prop("checked", true );
					})
					// 
					if(display_all_appointment_types){
						jQuery("#ssa_appointment_type_all").prop( "checked", true );
					} else {
						jQuery("#ssa_appointment_type_all").prop( "checked", false );
						// show all checkboxes
						ssaHideFirstShowSecond("",".appointment_type")
					}
				}
				
				function ssaRegeneratePreviewUIFromField(field){
					clearTimeout(ssaIframeSrcDebounceTimeoutId);
					ssaIframeSrcDebounceTimeoutId = setTimeout(() => {
						jQuery(`#field_${field['id']} iframe`).first().attr('src', ssaGenerateIframeSrcFromField(field));
					}, 10);
				}
				
				function ssaRegenerateSettingsUIFromField(field){
					ssaChangeHelpLink();
					ssaShowCustomTabs();
					// each of the functions below should simply set each of the corresponding settings to (the currently set value || default value)
					ssaSyncInputsFromFieldSettings(field);
					ssaSyncDisplayedSettingsFromFieldSettings(field);
				}
				
				// we call this anytime the field gets updated
				// loads the UI to be consistent with what is saved in the field
				// we never change UI manually outside of this functions and the stack of functsions it calls
				function ssaRegenerateUIFromField(field){
					ssaRegeneratePreviewUIFromField(field);
					ssaRegenerateSettingsUIFromField(field);
				}
				
				// handle backwards compatibility here
				function ssaMaybeLoadFieldDefaultValues(field){
					Object.keys(ssaFieldSettingsDefaultsMap)
					.reverse()
					.forEach((fieldPropertyKey)=>{
						!field[fieldPropertyKey] && ssaSetFieldProperty(fieldPropertyKey, field[fieldPropertyKey] || ssaFieldSettingsDefaultsMap[fieldPropertyKey])
					})
					// for backwards compatibility if appointment_type_id is still set
					if(field.appointment_type_id){
						// map appointment type id to the slug 
						let appointment_slug = jQuery("#field_ssa_appointment_types_subset").data("map")[field.appointment_type_id]
						ssaToggleAppointmentType(appointment_slug ,true)
					}
					if(!field.appointment_types_subset){
						ssaToggleAppointmentType("ssa_appointment_type_all" ,true)
					}
				}
				
				function ssaMaybeRegenerateUIFromField(field){
					if(field['type']!='ssa_appointment'){
						ssaHideCustomTabs()
						return false;
					}
					ssaMaybeLoadFieldDefaultValues(field);
					ssaRegenerateUIFromField(field);
					return true;
				}
				
				function ssaSetFieldPropertyOnInputChange(inputID, inputValue){
					let fieldPropertyKey = ssaFieldInputIdsToFieldSettingsMap[inputID]
					if(!fieldPropertyKey){
						console.error(inputID, 'not mapped to a field key')
					}
					ssaSetFieldProperty(fieldPropertyKey, inputValue)
				}
				
				function ssaSetFieldProperty(fieldPropertyKey, fieldPropertyValue){
					let selectedField = GetSelectedField();
					if(ssaCustomFieldPropertySettersMap.hasOwnProperty(fieldPropertyKey)){
						// here we handle side effects of inputs, that may affect settings other than the ones they map to
						// we don't need to handle UI changes here, because those must be generated from the state of the field
						// we use this if for example: changing time units of an input should also update the time value stored in the field
						ssaCustomFieldPropertySettersMap[fieldPropertyKey](fieldPropertyKey, fieldPropertyValue)
					} else {
						// we either use a custom setter or the default one, to avoid any value overrides
						selectedField.hasOwnProperty(fieldPropertyKey) && SetFieldProperty(fieldPropertyKey, fieldPropertyValue);
					}
					ssaRegenerateUIFromField(selectedField);
				}
				
				//binding to the load field settings event to initialize the checkbox
				jQuery(document).on('gform_load_field_settings', function(event, field, form){
					
					// if the selected field is not an ssa field, this will return false and we'll exit early exit early
					if(!ssaMaybeRegenerateUIFromField(field)){
						return;
					}
					
					var $map_field_name = jQuery('#field_ssa_map_field_name');
					$map_field_name.empty();
					$map_field_name.append('<option value="">Select GF Field</option>');
					var validIds = [];
					var autosuggestIds = [];

					form.fields.forEach(function(currentField) {
						if (currentField.type === 'name') {
							$map_field_name.append('<option value="'+currentField.id+'">GF Field #'+currentField.id+' '+currentField.label+'</option>');
							autosuggestIds.push(currentField.id);
							validIds.push(currentField.id);
						} else if (currentField.type === 'text') {
							$map_field_name.append('<option value="'+currentField.id+'">GF Field #'+currentField.id+' '+currentField.label+'</option>');
							validIds.push(currentField.id);
						}
					});

					var storedValue = parseInt(field.map_field_name, 10);
					if (storedValue && jQuery.inArray(storedValue, validIds)>-1) {
						$map_field_name.val(storedValue);
					} else if (autosuggestIds.length){
						$map_field_name.val(autosuggestIds[0]);
					} else {
						$map_field_name.val('');
					}
					SetFieldProperty('map_field_name', $map_field_name.val());




					var $map_field_email = jQuery('#field_ssa_map_field_email');
					$map_field_email.empty();
					$map_field_email.append('<option value="">Select GF Field</option>');
					var validIds = [];
					var autosuggestIds = [];

					form.fields.forEach(function(currentField) {
						if (currentField.type === 'email') {
							$map_field_email.append('<option value="'+currentField.id+'">GF Field #'+currentField.id+' '+currentField.label+'</option>');
							autosuggestIds.push(currentField.id);
							validIds.push(currentField.id);
						} else if (currentField.type === 'text') {
							$map_field_email.append('<option value="'+currentField.id+'">GF Field #'+currentField.id+' '+currentField.label+'</option>');
							validIds.push(currentField.id);
						}
					});

					var storedValue = parseInt(field.map_field_email, 10);
					if (storedValue && jQuery.inArray(storedValue, validIds)>-1) {
						$map_field_email.val(storedValue);
					} else if (autosuggestIds.length){
						$map_field_email.val(autosuggestIds[0]);
					} else {
						$map_field_email.val('');
					}
					SetFieldProperty('map_field_email', $map_field_email.val());



					var $map_field_phone = jQuery('#field_ssa_map_field_phone');
					$map_field_phone.empty();
					$map_field_phone.append('<option value="">Select GF Field</option>');
					var validIds = [];
					var autosuggestIds = [];

					form.fields.forEach(function(currentField) {
						if (currentField.type === 'phone') {
							$map_field_phone.append('<option value="'+currentField.id+'">GF Field #'+currentField.id+' '+currentField.label+'</option>');
							autosuggestIds.push(currentField.id);
							validIds.push(currentField.id);
						}
					});

					var storedValue = parseInt(field.map_field_phone, 10);
					if (storedValue && jQuery.inArray(storedValue, validIds)>-1) {
						$map_field_phone.val(storedValue);
					} else if (autosuggestIds.length){
						$map_field_phone.val(autosuggestIds[0]);
					} else {
						$map_field_phone.val('');
					}
					SetFieldProperty('map_field_phone', $map_field_phone.val());



					var $map_field_sms_optin = jQuery('#field_ssa_map_field_sms_optin');
					$map_field_sms_optin.empty();
					$map_field_sms_optin.append('<option value="">Select GF Field</option>');
					var validIds = [];

					form.fields.forEach(function(currentField) {
						if (currentField.type === 'consent') {
							$map_field_sms_optin.append('<option value="'+currentField.id+'">GF Field #'+currentField.id+' '+currentField.label+'</option>');
							validIds.push(currentField.id);
						}
					});

					var storedValue = parseInt(field.map_field_sms_optin, 10);
					if (storedValue && jQuery.inArray(storedValue, validIds)>-1) {
						$map_field_sms_optin.val(storedValue);
					} else {
						$map_field_sms_optin.val('');
					}
					SetFieldProperty('map_field_sms_optin', $map_field_sms_optin.val());

				});
			</script>
		<?php
	}

}

if ( class_exists( 'GF_Field' ) ) {
	class SSA_Gf_Field_Appointment extends GF_Field {
		public $type = 'ssa_appointment';
		// can be types or labels
		public $appointment_types_filter = 'types';
		public $appointment_type_id = '';
		public $appointment_types_subset = '';
		public $appointment_types_label = 'Default';
		public $display_all_appointment_types = false;
		public $flow = 'appt_type_settings';
		public $fallback_flow = 'expanded';
		public $first_available_within_minutes = 1;
		public $first_available_within_duration_unit = 'minutes';
		public $date_view='';
		public $time_view='';
		
		public $map_field_name = '';
		public $map_field_email = '';
		public $map_field_phone = '';
		public $map_field_sms_optin = '';

		public function get_form_editor_field_title() {
			return esc_attr__( 'Appointment', 'simply-schedule-appointments' );
		}

		public function get_form_editor_button() {
			return array(
				'group' => 'advanced_fields',
				'text'  => $this->get_form_editor_field_title()
			);
		}

		public function get_form_editor_field_settings() {
			return array(
				'conditional_logic_field_setting',
				'error_message_setting',
				'label_setting',
				'label_placement_setting',
				'admin_label_setting',
				'size_setting',
				'rules_setting',
				'visibility_setting',
				'placeholder_setting',
				'description_setting',
				'css_class_setting',
			);
		} 

		public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

			if ( empty( $value ) ) {
				return $value;
			}


			try {
				$appointment_obj = new SSA_Appointment_Object( $value );
				$start_date = $appointment_obj->start_date;
			} catch ( Exception $e ) {
				return '';
			}

			$ssa = ssa();
			$local_start_date = $ssa->utils->get_datetime_as_local_datetime( $start_date );

			$format = SSA_Utils::get_localized_date_format_from_settings();
			$format = SSA_Utils::localize_default_date_strings( $format ) . ' (T)';
			$value = $local_start_date->format( $format );
			$value = SSA_Utils::translate_formatted_date( $value );

			$should_display_end_date = apply_filters( 'ssa/forms/should_display_end_date', false );
			if ( $should_display_end_date ) {
				$end_date = $appointment_obj->end_date;
				$local_end_date = $ssa->utils->get_datetime_as_local_datetime( $end_date );

				$value = $local_start_date->format( 'F d, Y g:ia' );
				$value = SSA_Utils::translate_formatted_date( $value );
				$value .= ' - ' . $local_end_date->format( 'g:ia (T)' );

			}

			if ( $format === 'html' && $media !== 'email' && is_admin() ) {
				$admin_edit_url = $appointment_obj->get_admin_edit_url();
				$value .= ' <a href="' . $admin_edit_url .'">['. __( 'View in SSA', 'simply-schedule-appointments' ) .']</a>';
			}

			return $value;
		}

		public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {
			if ( empty( $value ) ) {
				return $value;
			}


			try {
				$appointment_obj = new SSA_Appointment_Object( $value );
				$start_date = $appointment_obj->start_date;
			} catch ( Exception $e ) {
				return '';
			}

			$ssa = ssa();
			$local_start_date = $ssa->utils->get_datetime_as_local_datetime( $start_date );

			$format = SSA_Utils::get_localized_date_format_from_settings();
			$format = SSA_Utils::localize_default_date_strings( $format ) . ' (T)';
			$value = $local_start_date->format( $format );
			$value = SSA_Utils::translate_formatted_date( $value );
			if ( is_admin() ) {
				$admin_edit_url = $appointment_obj->get_admin_edit_url();
				$value .= ' <a href="' . $admin_edit_url .'">['. __( 'View in SSA', 'simply-schedule-appointments' ) .']</a>';
			}

			return $value;
		}

		/**
		 * Format the entry value for the Appointment field.
		 *
		 * @since  Unknown
		 *
		 * @param string|array $value      The field value. Depending on the location the merge tag is being used the following functions may have already been applied to the value: esc_html, nl2br, and urlencode.
		 * @param string       $input_id   The field or input ID from the merge tag currently being processed.
		 * @param array        $entry      The Entry Object currently being processed.
		 * @param array        $form       The Form Object currently being processed.
		 * @param string       $modifier   The merge tag modifier. e.g. value
		 * @param string|array $raw_value  The raw field value from before any formatting was applied to $value.
		 * @param bool         $url_encode Indicates if the urlencode function may have been applied to the $value.
		 * @param bool         $esc_html   Indicates if the esc_html function may have been applied to the $value.
		 * @param string       $format     The format requested for the location the merge is being used. Possible values: html, text or url.
		 * @param bool         $nl2br      Indicates if the nl2br function may have been applied to the $value.
		 *
		 * @return string
		 */
		public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br ) {
			if ( empty( $input_id ) ) {
				$input_id = $this->id;
			}

			// clean up modifier in case we have weird html formatting or blank spaces.
			$modifier = wp_strip_all_tags( trim( $modifier ) );

			$appointment_id = rgar( $entry, $input_id );
			return ssa_evaluate_merge_tag( $appointment_id,  $modifier);
		}

		/**
		 * Format the entry value before it is used in entry exports and by framework add-ons using GFAddOn::get_field_value().
		 *
		 * For CSV export return a string or array.
		 *
		 * @param array      $entry    The entry currently being processed.
		 * @param string     $input_id The field or input ID.
		 * @param bool|false $use_text When processing choice based fields should the choice text be returned instead of the value.
		 * @param bool|false $is_csv   Is the value going to be used in the .csv entries export?
		 *
		 * @return string|array
		 */
		public function get_value_export( $entry, $input_id = '', $use_text = false, $is_csv = false ) {
			if ( empty( $input_id ) ) {
				$input_id = $this->id;
			}

			if ( empty( $entry['form_id'] ) ) {
				return rgar( $entry, $input_id );
			}

			$appointment_id = rgar( $entry, $input_id );

			try {
				$appointment_obj = new SSA_Appointment_Object( $appointment_id );
				$start_date = $appointment_obj->start_date;
			} catch ( Exception $e ) {
				return '';
			}

			$ssa = ssa();
			$local_start_date = $ssa->utils->get_datetime_as_local_datetime( $start_date );
			$format = SSA_Utils::localize_default_date_strings( 'F j, Y g:i a' ) . ' (T)';
			$formatted_value = $local_start_date->format( $format );
			$formatted_value = SSA_Utils::translate_formatted_date( $formatted_value );
			return $formatted_value;
		}

		public function is_conditional_logic_supported() {
			return true;
		}

		public static function get_appearance_settings($placement, $form_id) {
			if( $placement == 0 ) {
				$html = '<li class="ssa_styles_setting field_setting">
				You can easily adjust the colors of your booking form by editing your 
				<a href="' . ssa()->wp_admin->url( '/ssa/settings/styles' ) . '">SSA Style Settings</a>
				</li>';
				echo $html;
			}
		}

		public static function get_standard_settings($placement, $form_id) {
			if( $placement == 0 ) {
				$html = '';
				$appointment_types = ssa()->appointment_type_model->query(  array(
					'status' => 'publish',
					) );
				$appointment_type_labels = ssa()->appointment_type_label_model->query(  array(
					) );
				
				$appointment_types_active_labels = array();
				$appointment_types_ids_to_slugs = array();
				
				foreach ($appointment_types as $key => $appointment_type) {
					$appointment_types_ids_to_slugs[$appointment_type['id']]=$appointment_type['slug'];
					$appointment_types_active_labels[$appointment_type['label_id']] = true;
				}
				
				/* appointment_types_filter by types or labels */
				$html .= '
					<li class=" ssa_appointment_type_setting field_setting">
						<label for="field_ssa_appointment_types_filter" class="section_label">'.
							__("Filter By", "simply-schedule-appointments").'
							<button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_field_label" aria-label="<strong>Appointment type</strong>Note: any payment options or custom fields defined in SSA settings will be skipped since the appointment type will be embedded in this form.">
								<i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
							</button>
					  </label>
						<select name="field_ssa_appointment_types_filter" id="field_ssa_appointment_types_filter" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">
							<option class="ssa_appointment_types_filter_option" value="types">' . __('Appointment Types', 'simply-schedule-appointments' ) . '</option>
							<option class="ssa_appointment_types_filter_option" value="labels">' . __('Labels', 'simply-schedule-appointments' ) . '</option>
						</select>
					</li>';
					
				/* appointment_types filter by types */
				$html .= '
					<li class="ssa_appointment_types_filter ssa_appointment_types_types_filter field_setting">
						<ul data-map="' . htmlspecialchars(json_encode($appointment_types_ids_to_slugs ), ENT_QUOTES, "UTF-8") .'" class="md-theme-default" name="field_ssa_appointment_types_subset" id="field_ssa_appointment_types_subset">
							<li class=" ssa_appointment_type_all">
								<input  type="checkbox" onchange="ssaToggleAppointmentType(this.id,this.checked)" name="ssa_appointment_type_all" id="ssa_appointment_type_all">
								<label for="ssa_appointment_type_all" class="md-checkbox-label">'.__('All', 'simply-schedule-appointments').'</label>
							</li>';
							
							foreach ($appointment_types as $key => $appointment_type) {
								$appointment_types_ids_to_slugs[$appointment_type['id']]=$appointment_type['slug'];
								$html .= '
									<li class=" appointment_type">
										<input type="checkbox" onchange="ssaToggleAppointmentType(this.id,this.checked)" class="appointment_type" name="appointment_type" id="'.$appointment_type['slug'].'">
										<label for="'.$appointment_type['slug'].'" class="md-checkbox-label">'.$appointment_type['title'].'</label>
									</li>';
							}
				$html .= '
						</ul>
					</li>';
				
				/* appointment_types filter by labels */
				$html .= '
					<li class="ssa_appointment_types_filter ssa_appointment_types_labels_filter field_setting">
					<label for="field_ssa_appointment_types_labels_filter" style="position: absolute; left: -9999px; width: 1px; height: 1px; overflow: hidden;">'.__('Select Label', 'simply-schedule-appointments').'</label>
					<select name="field_ssa_appointment_types_labels_filter" id="field_ssa_appointment_types_labels_filter" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">';
						foreach( $appointment_type_labels as $index => $label ){
							if( isset( $appointment_types_active_labels[$label['id']] ) && true == $appointment_types_active_labels[$label['id']] ){
								$html .= '
									<option class="ssa_appointment_type_label_option" value="'.$label['name'] .'">'.$label['name'].'</option>
								';
							}
						}
				$html .= '
					</select>
					</li>';
				
				/* START map_field_name */
				$html .= '<li class="ssa_map_field_name_setting field_setting">
					<label for="field_ssa_map_field_name" class="section_label">'.__('Name Form Field', 'simply-schedule-appointments').'
						<button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_field_label" aria-label="<strong>Name Form Field</strong>Select the Gravity Form field that SSA should use for the customer\'s name.">
						<i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
						</button>
					</label>

					<select name="field_ssa_map_field_name" id="field_ssa_map_field_name" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">

						<option value="">Select...</option>
						';
						$html .= '
					</select>
				</li>';
				/* END map_field_name */

				/* START map_field_email */
				$html .= '<li class="ssa_map_field_email_setting field_setting">
					<label for="field_ssa_map_field_email" class="section_label">'.__('Email Form Field', 'simply-schedule-appointments').'
						<button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_field_label" aria-label="<strong>Email Form Field</strong>Select the Gravity Form field that SSA should use for the customer\'s email address.">
						<i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
						</button>
					</label>

					<select name="field_ssa_map_field_email" id="field_ssa_map_field_email" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">

						<option value="">Select...</option>
						';
						$html .= '
					</select>
				</li>';
				/* END map_field_email */

				/* START map_field_phone */
				$html .= '<li class="ssa_map_field_phone_setting field_setting">
					<label for="field_ssa_map_field_phone" class="section_label">'.__('Phone Form Field', 'simply-schedule-appointments').'
						<button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_field_label" aria-label="<strong>Phone Form Field</strong>Select the Gravity Form field that SSA should use for the customer\'s phone number">
						<i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
						</button>
					</label>

					<select name="field_ssa_map_field_phone" id="field_ssa_map_field_phone" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">

						<option value="">Select...</option>
						';
						$html .= '
					</select>
				</li>';
				/* END map_field_phone */

				/* START map_field_sms_optin */
				$html .= '<li class="ssa_map_field_sms_optin_setting field_setting">
					<label for="field_ssa_map_field_sms_optin" class="section_label">'.__('Opt-In Form Field', 'simply-schedule-appointments').'
						<button onclick="return false;" onkeypress="return false;" class="gf_tooltip tooltip tooltip_form_field_label" aria-label="<strong>sms_optin Form Field</strong>Select the Gravity Form field that SSA should use for the customer\'s sms_optin number">
						<i class="gform-icon gform-icon--question-mark" aria-hidden="true"></i>
						</button>
					</label>

					<select name="field_ssa_map_field_sms_optin" id="field_ssa_map_field_sms_optin" onchange="ssaSetFieldPropertyOnInputChange(this.id, this.value);">

						<option value="">Select...</option>
						';
						$html .= '
					</select>
				</li>';
				/* END map_field_sms_optin */


				echo $html;
			}
		}

		public function get_field_input( $form, $value = '', $entry = null ) {
			$form_id         = $form['id'];
			$is_entry_detail = $this->is_entry_detail();
			$is_form_editor  = $this->is_form_editor();
			$value			 = esc_attr( $value );

			$id              = (int) $this->id;
			$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

			$class_suffix       = $is_entry_detail ? '_admin' : '';
			$class              = '' . $class_suffix;
			$tabindex              = $this->get_tabindex();
			$placeholder_attribute = $this->get_field_placeholder_attribute();
			$required_attribute    = $this->isRequired ? 'aria-required="true"' : '';
			$invalid_attribute     = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';
			$aria_describedby      = $this->get_aria_describedby();

			if ( $is_entry_detail ) {
				$input = "<input type='hidden' id='input_{$id}' name='input_{$id}' value='{$value}' />";

				return $input . '<br/>' . esc_html__( 'Appointment fields are not editable. Please manage the appointment directly.', 'simply-schedule-appointments' );
			}


			$disabled_text         = $is_form_editor ? 'disabled="disabled"' : '';

			$args = array(
				'integration' => 'form',
				// when types is not empty, it overrides and value in type
				'type' => '',
				'types' => '',
				'edit' => $value ? $value : '',
				'token' => '',
				'view' => '',
				'suggest_first_available' => '',
				'suggest_first_available_within_minutes' => '',
				'flow' => '',
				'fallback_flow' => '',
				'time_view' => '',
				'date_view' => '',
			);
			
			// based on the active filter, append into $args
			// this is needed because we're not clearing the field settings when user switched modes
			if( 'labels' === $this->appointment_types_filter ){
				$args['label'] = $this->appointment_types_label;
			} else {
				$args['type'] = $this->appointment_type_id;
				$args['types'] = $this->display_all_appointment_types? '': $this->appointment_types_subset;
			}
			
			// based on whether the booking flow feature is supported or not
			// this allows going back and forth between activated versions of booking app with no problems
			if(ssa_should_render_booking_flow()){
				$args['flow'] = $this->flow === "appt_type_settings" ? "" : $this->flow;
				if("first_available" === $this->flow){
					$args['fallback_flow'] = $this->fallback_flow;
					$args['suggest_first_available'] = '1';
					$args['suggest_first_available_within_minutes'] = $this->first_available_within_minutes;
				}
				if(in_array($this->flow, array("expanded", "express", "first_available"))){
					$args['time_view'] = $this->time_view;
				}
				if(("first_available" === $this->flow &&  "express" !== $this->fallback_flow) || "expanded" === $this->flow ){
					$args['date_view'] =$this->date_view;
				}
			}
			
			if ( $value ) {
				$token = ssa()->appointment_model->get_id_token( $value );

				if ( $token ) {
					$args['token'] = $token . $value;
				}
			}
			// gets the proper id to apply the conditional logic rules on
			$fields = GFCommon::json_encode( $this->conditionalLogicFields );
			$iframe_embed = ssa()->shortcodes->ssa_booking( $args );

			$interactivity = $is_form_editor ? 'pointer-events: none;' : "";
			$input = 
			"<div style='{$interactivity}' class='ginput_container ginput_container_ssa_appointment_field ssa_appointment_form_field_container' id='gf_ssa_appointment_container_{$form_id}_{$id}'>"
			.
				$iframe_embed
			.
				"<input type='hidden' data-form-id='{$form_id}' data-fields='{$fields}' name='input_{$id}' id='{$field_id}' value='{$value}' class='ssa_appointment_form_field_appointment_id {$class}' {$tabindex} {$placeholder_attribute} {$disabled_text} {$required_attribute} {$invalid_attribute} {$aria_describedby} />"
			.
			"</div>";

			return $input;
		}
	}
}
