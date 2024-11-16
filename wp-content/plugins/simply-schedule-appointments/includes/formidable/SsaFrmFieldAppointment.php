<?php

class SsaFrmFieldAppointment extends FrmFieldType {

	/**
	 * @var string
	 */
	protected $type = 'ssa-appointment';

	/**
	 * Set to false if a normal input field should not be displayed.
	 * @var bool
	 */
	protected $has_input = true;

	/**
	 * Which Formidable settings should be hidden or displayed?
	 */
	protected function field_settings_for_type() {
		$settings            = parent::field_settings_for_type();
		$settings['default'] = true;
		$settings['logic'] = true;
		$settings['visibility'] = true;

		return $settings;
	}

	/**
	 * Need custom options too? Add them here or remove this function.
	 */
	protected function extra_field_opts() {
		return array(
			// name => default,
			'appointment_type_id' => '',
			'map_field_phone' => '',
			'map_field_name' => '',
			'map_field_email' => '',
			'map_field_sms_optin' => '',
		);
	}

	protected function include_form_builder_file() {
		return dirname( __FILE__ ) . '/builder-field.php';
	}

	/**
	 * Get the type of field being displayed. This is required to add a settings
	 * section just for this field. show_extra_field_choices will not be triggered
	 * without it.
	 *
	 * @return array
	 */
	public function displayed_field_type( $field ) {
		return array(
			$this->type => true,
		);
	}

	/**
	 * Add settings in the builder here.
	 */
	public function show_extra_field_choices( $args ) {
		$field = $args['field'];
		include( dirname( __FILE__ ) . '/builder-settings.php' );
	}

	protected function html5_input_type() {
		return 'hidden';
	}

	/**
	 * Customize the way the value is displayed in emails and views.
	 *
	 * @param string $value The field value.
	 * @param array  $atts An array containing the form and fields data.
	 * @return string
	 */
	protected function prepare_display_value( $value, $atts ) {
		if ( empty( $value ) ) {
			return $value;
		}

		$ssa = ssa();
		try {
			$appointment_obj = new SSA_Appointment_Object( $value );
			$start_date = $appointment_obj->start_date;
		} catch ( Exception $e ) {
			return '';
		}

		$local_start_date = $ssa->utils->get_datetime_as_local_datetime( $appointment_obj->start_date );

		$format = SSA_Utils::get_localized_date_format_from_settings();
		$format = SSA_Utils::localize_default_date_strings( $format ) . ' (T)';
		$value  = $local_start_date->format( $format );
		$value  = SSA_Utils::translate_formatted_date( $value );
		if ( empty( $atts['truncate'] ) && current_user_can( 'ssa_manage_appointments' ) ) {
			$admin_edit_url = $appointment_obj->get_admin_edit_url();
			$value         .= ' <a href="' . $admin_edit_url . '">[' . __( 'View in SSA', 'simply-schedule-appointments' ) . ']</a>';
		}

		return $value;
	}

	public static function csv_field_value( $field_value, $atts ) {
		if ( $atts['field']->type != 'ssa-appointment' ) {
			return $field_value;
		}

		try {
			$appointment_obj = new SSA_Appointment_Object( $field_value );
			$start_date = $appointment_obj->start_date;
		} catch ( Exception $e ) {
			return $field_value;
		}

		$ssa = ssa();
		$local_start_date = $ssa->utils->get_datetime_as_local_datetime( $start_date );

		$format = SSA_Utils::get_localized_date_format_from_settings();
		$format = SSA_Utils::localize_default_date_strings( $format ) . ' (T)';
		$formatted_value = $local_start_date->format( $format );
		$formatted_value = SSA_Utils::translate_formatted_date( $formatted_value );
		
		return $formatted_value;
	}

	/**
	 * @return string Whatever shows in the front end goes here.
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$input_html = parent::front_field_input( $args, $shortcode_atts );
		$field = $this->field;

		ob_start();
		include( dirname( __FILE__ ) . '/front-end-field.php' );
		$input_html .= ob_get_contents();
		ob_end_clean();

		return $input_html;
	}

	protected function get_input_class() {
		return ' ssa_appointment_form_field_appointment_id';
	}

	public function get_container_class() {
		return ' ssa_appointment_form_field_container';
	}
}
