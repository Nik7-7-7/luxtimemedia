<?php
/**
 * Simply Schedule Appointments Formidable.
 *
 * @since   3.2.2
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Formidable.
 *
 * @since 3.2.2
 */
class SSA_Formidable {
	/**
	 * Parent plugin class.
	 *
	 * @since 3.2.2
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  3.2.2
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		if ( ! function_exists( 'load_formidable_forms' ) ) {
			return;
		}

		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  3.2.2
	 */
	public function hooks() {
		spl_autoload_register( array( $this, 'ssa_forms_autoloader' ) );

		add_filter( 'frm_get_field_type_class', array( $this, 'get_field_type_class' ), 10, 2 );
		add_filter( 'frm_pro_available_fields', array( $this, 'add_appointment_field' ) );

		add_action('frm_after_create_entry', array( $this, 'update_status_after_entry_created' ), 30, 2);

		add_filter( 'frm_csv_value', 'SsaFrmFieldAppointment::csv_field_value', 10, 2 );

		add_action( 'frm_enqueue_form_scripts', array( $this, 'enqueue_form_scripts' ), 10, 1 );
		
		add_shortcode( 'frm_ssa_merge_tag', array($this,'frm_ssa_merge_tag') );
	}
	
	public function frm_ssa_merge_tag( $atts, $x='', $y=null ){
		// if the shortcode does not include the enty id, we can't connect the entry
		if ( ! isset( $atts['id'] ) || ! is_numeric( $atts['id'] ) ) {
			return '';
		}
		
		$entry_id = $atts['id'];
		
		// get entry
		$entry = FrmEntry::getOne( $entry_id, true );
		
		// get form id
		$form_id = $entry->form_id;
		$fields = FrmField::get_all_for_form( $form_id );
		
		$appointment_field_ids=[];
		foreach ( $fields as $field ){
			if ( $field->type == 'ssa-appointment' ){
				$appointment_field_ids[] = $field->id;
			}
		}
		
		if( isset( $atts['field_id'] ) && is_numeric( $atts['field_id'] ) ){
			$field_id = $atts['field_id'];
		}

		if( empty( $appointment_field_ids ) ){
			// The form does not include SSA fields
			return '';
		} elseif ( count( $appointment_field_ids ) > 1 && !isset( $field_id ) ){
			// Could not decide on which SSA Appointment field to reference in the shortcode
			return '';
		}else{
			if( isset( $field_id ) && in_array( $field_id, $appointment_field_ids ) ){
				$appointment_field_id = $field_id;
			}else{
				$appointment_field_id = $appointment_field_ids[0];
			}
		}
		// clean up modifier in case we have weird html formatting / allow blank spaces for spaces in output.
		$modifier = wp_strip_all_tags( trim( $atts["merge_tag"] ) );
		
		// get the value of the field in the entry
		$appointment_id = FrmEntryMeta::get_entry_meta_by_field( $entry_id, $appointment_field_id );
		
		return ssa_evaluate_merge_tag( $appointment_id,  $modifier);
	}
	
	/**
	 * If Formidable forms is loaded / submitted via ajax, and includes an appointment field, then enqueue the scripts.
	 * 
	 * @since 4.9.5
	 *
	 * @param array $params The Formidable form parameters.
	 * @return void
	 */
	public function enqueue_form_scripts( $params ) {
		$form = FrmForm::getOne( $params['form_id'] );

		// if the form doesn't support submissions via ajax, then return.
		if ( empty( $form->options['ajax_submit'] ) && empty( $form->options['ajax_load'] ) ) {
			return;
		}

		$fields = FrmField::get_all_for_form( $params['form_id'] );

		$appointment_field = array_values(
			array_filter(
				$fields,
				function ( $field ) {
					return 'ssa-appointment' === $field->type;
				}
				)
			);

		// if form fields don't include an appointment field, then return.
		if ( empty( $appointment_field ) ) {
			return;
		}

		// enqueue the required scripts and variables.
		wp_localize_script( 'ssa-iframe-outer', 'ssa', $this->plugin->bootstrap->get_api_vars() );
		wp_enqueue_script( 'ssa-iframe-outer' );
		wp_enqueue_script( 'ssa-form-embed' );
	}

	public function ssa_forms_autoloader( $class_name ) {
		// Only load Prefix classes here
		if ( ! preg_match( '/^SsaFrm.+$/', $class_name ) ) {
			return;
		}

		$filepath = dirname( __FILE__ );
		$filepath .= '/formidable/' . $class_name . '.php';

		if ( file_exists( $filepath ) ) {
			require( $filepath );
		}
	}

	public function get_field_type_class( $class, $field_type ) {
		if ( $field_type === 'ssa-appointment' ) {
			$class = 'SsaFrmFieldAppointment';
		}

		return $class;
	}

	public function add_appointment_field( $fields ) {
		$fields['ssa-appointment'] = array(
			'name' => 'Appointment',
			'icon' => 'frm_icon_font frm_calendar_icon',
		);
		return $fields;
	}

	/**
	 * Update Appointment data after a form entry is created.
	 *
	 * @param string $entry_id The form entry id.
	 * @param string $form_id The form id.
	 * @return void
	 */
	public function update_status_after_entry_created( $entry_id, $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );
		if ( empty( $fields ) ) {
			return;
		}

		$entry = FrmEntry::getOne( $entry_id, true );
		if ( isset( $entry->is_draft ) && ! empty( $entry->is_draft ) ) {
			return;
		}

		$map_ssa_field_names_to_form_field_ids = array(
			'Name'  => '',
			'Email' => '',
			'Phone' => '',
		);

		$map_field_ids_to_field_objects = array();
		foreach ( $fields as $key => $field ) {
			$map_field_ids_to_field_objects[ $field->id ] = $field;

			if ( 'phone' === $field->type && empty( $map_ssa_field_names_to_form_field_ids['Phone'] ) ) {
				$map_ssa_field_names_to_form_field_ids['Phone'] = $field->id;
			}

			if ( ( 'email' === $field->type || 'Email' === $field->name ) && empty( $map_ssa_field_names_to_form_field_ids['Email'] ) ) {
				$map_ssa_field_names_to_form_field_ids['Email'] = $field->id;
			}
			
			if ( ( 'name' === $field->type || 'Name' === $field->name ) && empty( $map_ssa_field_names_to_form_field_ids['Name'] ) ) {
				$map_ssa_field_names_to_form_field_ids['Name'] = $field->id;
			}
		}

		foreach ( $fields as $key => $field ) {
			if ( 'ssa-appointment' !== $field->type ) {
				// skip all other field types.
				continue;
			}

			if ( empty( $entry->metas[ $field->id ] ) ) {
				// No appointment id was submitted for this field.
				continue;
			}
			
			/* Populate name field */
			$name_field_id = null;
			if ( ! empty( $field->field_options['map_field_name'] ) && ! is_null( $entry->metas[ $field->field_options['map_field_name'] ] ) ) {
				$name_field_id = $field->field_options['map_field_name'];
			} else if ( ! empty( $map_ssa_field_names_to_form_field_ids['Name'] ) ) {
				// Default to the first Name field if no field mapping is stored
				$name_field_id = $map_ssa_field_names_to_form_field_ids['Name'];
			}
			
			// same for phone
			$phone_field_id = null;
			if ( ! empty( $field->field_options['map_field_phone'] ) && ! is_null( $entry->metas[ $field->field_options['map_field_phone'] ] ) ) {
				$phone_field_id = $field->field_options['map_field_phone'];
			} else if ( ! empty( $map_ssa_field_names_to_form_field_ids['Phone'] ) ) {
				// Default to the first Phone field if no field mapping is stored
				$phone_field_id = $map_ssa_field_names_to_form_field_ids['Phone'];
			}

			// same for email
			$email_field_id = null;
			if ( ! empty( $field->field_options['map_field_email'] ) && ! is_null( $entry->metas[ $field->field_options['map_field_email'] ] ) ) {
				$email_field_id = $field->field_options['map_field_email'];
			} else if ( ! empty( $map_ssa_field_names_to_form_field_ids['Email'] ) ) {
				// Default to the first Email field if no field mapping is stored
				$email_field_id = $map_ssa_field_names_to_form_field_ids['Email'];
			}
      
			// same for SMS opt-in
			$sms_optin_field_id = null;
			if ( ! empty( $field->field_options['map_field_sms_optin'] ) && ! is_null( $entry->metas[ $field->field_options['map_field_sms_optin'] ] ) ) {
				$sms_optin_field_id = $field->field_options['map_field_sms_optin'];
			}
      
			$appointment_id  = (int) esc_attr( $entry->metas[ $field->id ] );
			$appointment_obj = new SSA_Appointment_Object( $appointment_id );
			if ( 'pending_form' !== $appointment_obj->status && $appointment_obj->status !== 'abandoned' ) {
				// Don't handle other statuses (possibly abandoned).
				continue;
			}
			// check if an abandoned time slot is still available on form submission
			if ( $appointment_obj->status === 'abandoned' && !$this->plugin->appointment_model->is_prospective_appointment_available( $appointment_obj->get_appointment_type(), $appointment_obj->start_date_datetime ) ) {
				// time slot already booked
				continue;
			}

			$customer_information = $appointment_obj->customer_information;

			$customer_information['Name']  = '';
			$customer_information['Email'] = '';

			// map email
			if ( ! empty( $email_field_id ) ) {
				$customer_information['Email'] = is_null( $entry->metas[ $email_field_id ] ) ? '' : $entry->metas[ $email_field_id ];
			}
			
			// map phone
			if ( ! empty( $phone_field_id ) ) {
				$customer_information['Phone'] = is_null( $entry->metas[ $phone_field_id ] ) ? '' : $entry->metas[ $phone_field_id ];
			}
			
			// map name
			if ( ! empty( $name_field_id ) ) {
				$name_field_value = is_null( $entry->metas[ $name_field_id ] ) ? '' : $entry->metas[ $name_field_id ];
				// Name could be an array with first and last name. If so, then format into a string before saving it to the database.
				if ( is_array( $name_field_value ) ) {
					$name_field_value = implode( ' ', $name_field_value );
				}
				$customer_information['Name'] = is_null( $name_field_value ) ? '' : $name_field_value;
			}
			
			// map SMS opt-in
			$allow_sms = false;
			if ( ! empty( $sms_optin_field_id ) && ! empty ( $map_field_ids_to_field_objects[ $sms_optin_field_id ] ) ) {
				$toggled_on_value = $map_field_ids_to_field_objects[ $sms_optin_field_id ]->field_options['toggle_on'];
				if ( ! empty( $entry->metas[ $sms_optin_field_id ] ) ) {
					$allow_sms = $entry->metas[ $sms_optin_field_id ] === $toggled_on_value ? true : false;
				}
			}
			
			foreach ( $entry->metas as $field_id => $submitted_value ) {
				if ( 'ssa-appointment' === $map_field_ids_to_field_objects[ $field_id ]->type ) {
					continue;
				}
				
				if ( in_array( (string) $field_id, array( $email_field_id, $phone_field_id, $name_field_id, $sms_optin_field_id ), true ) ) {
					continue; // we already used this field ID for mapping to a specific SSA field, we don't need to append it again.
				}
				
				if ( 'name' === $map_field_ids_to_field_objects[ $field_id ]->type ) {
					// Name could be an array with first and last name. If so, then format into a string before saving it to the database.
					if ( is_array( $submitted_value ) ) {
						$submitted_value = implode( ' ', $submitted_value );
					}
				} else if ( 'file' === $map_field_ids_to_field_objects[ $field_id ]->type ) {
					if ( is_int( $submitted_value ) || is_string( $submitted_value ) ) {
						$submitted_value = wp_get_attachment_url( $submitted_value, 'full' );
					} else if ( is_array( $submitted_value ) ) {
						$submitted_value = array_map( function( $attachment_id ) {
							return wp_get_attachment_url( $attachment_id, 'full' );
						}, $submitted_value );
						$submitted_value = implode( "\r\n", $submitted_value );
					}
				}
				
				// needed for forms that may have multiple fields with the same name
				$label_key = $map_field_ids_to_field_objects[ $field_id ]->name;
				
				// if we have more than one field with the same label, append a number to the subsequent ones
				if ( ! empty ( $customer_information[ $label_key ] ) ) {
					for ($i=2; $i < 100; $i++) { 
						if ( empty( $customer_information[ $label_key . ' ' . $i ] ) ) {
							$label_key = $label_key . ' ' . $i;
							break;
						}
					}
				}

				$customer_information[ $label_key ] = is_null( $submitted_value ) ? '' : $submitted_value;
			}

			$appointment_meta = array();

			$appointment_meta['form_integration']           = 'formidable';
			$appointment_meta['formidable_form_id']         = $form_id;
			$appointment_meta['formidable_entry_id']        = $entry_id;
			$appointment_meta['formidable_entry_admin_url'] = admin_url( 'admin.php?page=formidable-entries&frm_action=show&id=' . $entry_id . '&frm-full=1' );

			/* Update appointment */
			$appointment_update_data = array(
				'customer_information' => $customer_information,
				'status'               => 'booked',
				'meta_data' => $appointment_meta
			);

			if( ! is_null ( $sms_optin_field_id ) ) {
				// if the opt-in field is mapped to a Gravity Forms field,
				// only allow SMS if the field was checked
				if( ! empty( $allow_sms ) ) {
					// regardless of current SSA SMS setup, and whether it's active or not
					$appointment_update_data['allow_sms'] = 1;
				}
			} else if ( $this->plugin->settings_installed->is_enabled( 'sms' ) ) {
				// the opt-in field is not mapped to a Gravity Forms field
				// preserve the existing behavior, allow SMS whenever the phone field is mapped
				if ( ! empty( $phone_field_id ) ) {
					$appointment_update_data['allow_sms'] = 1;
				}
			}
			$this->plugin->appointment_model->update( $appointment_obj->id, $appointment_update_data );
		}
	}

}
