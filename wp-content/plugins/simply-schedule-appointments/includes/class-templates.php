<?php
/**
 * Simply Schedule Appointments Templates.
 *
 * @since   2.0.3
 * @package Simply_Schedule_Appointments
 */


/**
 * Simply Schedule Appointments Templates.
 *
 * @since 2.0.3
 */
class SSA_Templates {
	/**
	 * Parent plugin class.
	 *
	 * @since 2.0.3
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  2.0.3
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		
		SSA_Utils::define( 'SSA_TEMPLATE_DEBUG_MODE', false );

		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  2.0.3
	 */
	public function hooks() {
		add_filter( 'ssa/templates/get_template_vars', array( $this, 'add_global_template_vars' ), 5, 2 );
		add_filter( 'ssa/templates/get_template_vars', array( $this, 'add_appointment_template_vars' ), 5, 2 );
		add_filter( 'ssa/templates/get_template_vars', array( $this, 'add_appointment_calendar_link_vars' ), 5, 2 );
		add_filter( 'ssa/templates/get_template_vars', array( $this, 'add_example_appointment_type_template_vars' ), 5, 2 );
		add_filter( 'ssa/templates/get_template_vars', array( $this, 'ssa_get_tec_vars' ), 10, 2 );
	
	}

	public function get_template_vars( $template, $vars = array() ) {
		$vars = apply_filters( 'ssa/templates/get_template_vars', $vars, $template );

		return $vars;
	}

	public function add_global_template_vars( $vars, $template ) {
		if ( empty( $vars['Global'] ) ) {
			$vars['Global'] = array();
		}

		$global_settings = $this->plugin->settings->get()['global'];
		$vars['Global'] = array_merge( $vars['Global'], array(
			'site_url' => site_url(),
			'home_url' => home_url(),
			'company_name' => get_bloginfo('name')
		), $global_settings );

		return $vars;
	}

	/**
	 * Add appointment template vars.
	 *
	 * @since  5.5.3
	 *
	 * @param  array  $vars Template vars.
	 * @param  string $template Template name.
	 *
	 * @return array
	 */
	public function add_appointment_calendar_link_vars( $vars, $template ) {
		if ( empty( $vars['appointment_id'] ) ) {
			return $vars;
		}

		if ( empty( $vars['Appointment'] ) ) {
			$vars['Appointment'] = array();
		}
		remove_filter( 'ssa/templates/get_template_vars', array( ssa()->templates, 'add_appointment_calendar_link_vars' ), 5, 2 );
		$appointment_obj = new SSA_Appointment_Object( (int) $vars['appointment_id'] );
		// Add to calendar link.
		$vars['add_to_calendar_link']         = array();
		$vars['add_to_calendar_link']['ics']  = $appointment_obj->get_ics_download_url( 'customer' );
		$vars['add_to_calendar_link']['gcal'] = $appointment_obj->get_gcal_add_link( 'customer' );

		// Get public token for ics download.
		$public_token = ssa()->appointment_model->get_id_token( $vars['appointment_id'] );

		$vars['add_to_calendar_link']['ics'] = add_query_arg(
			array(
				'token' => $public_token,
			),
			$vars['add_to_calendar_link']['ics']
		);

		add_filter( 'ssa/templates/get_template_vars', array( ssa()->templates, 'add_appointment_calendar_link_vars' ), 5, 2 );

		return $vars;
	}

	public function add_example_appointment_type_template_vars( $vars, $template ) {
		if ( empty( $vars['example_appointment_type_id'] ) ) {
			return $vars;
		}

		if ( empty( $vars['Appointment'] ) ) {
			$vars['Appointment'] = array();
		}

		$settings = $this->plugin->settings->get();
		$appointment_type_object = new SSA_Appointment_Type_Object( (int)$vars['example_appointment_type_id'] );
		
		$vars['Appointment']['AppointmentType'] = $appointment_type_object->data;
		if ( isset( $vars['Appointment']['AppointmentType']['availability'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['availability'] );
		}
		if ( isset( $vars['Appointment']['AppointmentType']['notifications'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['notifications'] );
		}
		if ( isset( $vars['Appointment']['AppointmentType']['google_calendars_availability'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['google_calendars_availability'] );
		}
		if ( !empty( $vars['Appointment']['AppointmentType']['custom_customer_information'] ) ) {
			$vars['Appointment']['AppointmentType']['customer_information'] = $vars['Appointment']['AppointmentType']['custom_customer_information'];
		}
		if ( isset( $vars['Appointment']['AppointmentType']['custom_customer_information'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['custom_customer_information'] );
		}
		if ( isset( $vars['Author'] ) ) {
			$vars['Customer'] = $vars['Author'];
			unset( $vars['Author'] );
		}

		$vars['Appointment']['customer_information'] = array();
		$vars['Appointment']['customer_information_strings'] = array();
		foreach ($vars['Appointment']['AppointmentType']['customer_information'] as $key => $value) {
			$vars['Appointment']['customer_information'][$value['field']] = __( '[customer info will go here...]', 'simply-schedule-appointments' );
			$vars['Appointment']['customer_information_strings'][$value['field']] = __( '[customer info will go here...]', 'simply-schedule-appointments' );
		}
		$vars['Appointment']['start_date'] = gmdate( 'Y-m-d H:i:s' );
		$vars['Appointment']['end_date'] = gmdate( 'Y-m-d H:i:s' );
		$vars['Appointment']['status'] = 'booked';
		$vars['Appointment']['customer_id'] = 0;
		$vars['Appointment']['customer_timezone'] = 'UTC';
		$vars['Appointment']['appointment_type_id'] = $vars['example_appointment_type_id'];

		$vars['admin_email'] = $settings['global']['admin_email'];
		$vars['customer_email'] = $vars['Appointment']['customer_information']['Email'];
		$vars['customer_name'] = $vars['Appointment']['customer_information']['Name'];
		$vars['attendees_list'] = __( '[List of attendees will go here...]', 'simply-schedule-appointments' );
		$vars['instructions'] = $vars['Appointment']['AppointmentType']['instructions'];
		$vars['location'] = __( '[Event location info will go here...]', 'simply-schedule-appointments' );

		// Refund policy
		$vars['refund_policy'] = __('[Refund Policy will go here...]', 'simply-schedule-appointments');

		// Add to calendar link.
		$vars['add_to_calendar_link'] = array(
			'ics'  => __( '[Link to save .ics file will go here...]', 'simply-schedule-appointments' ),
			'gcal' => __( '[Link to add to calendar will go here...]', 'simply-schedule-appointments' ),
		);

		return $vars;
	}

	public function add_appointment_template_vars( $vars, $template ) {
		if ( empty( $vars['appointment_id'] ) ) {
			return $vars;
		}

		if ( empty( $vars['Appointment'] ) ) {
			$vars['Appointment'] = array();
		}

		$settings = $this->plugin->settings->get();
		$appointment_obj = new SSA_Appointment_Object( (int)$vars['appointment_id'] );

		$eol = "\r\n";

		$vars['Appointment'] = array_merge( $vars['Appointment'], $appointment_obj->get_data( 1 ) );
		$vars['Appointment']['customer_information_strings'] = array();
		foreach ( $vars['Appointment']['customer_information'] as $key => $value ) {
			if (is_array($value)) {
				$value = implode(', ', $value);
			}

			$value = htmlspecialchars($value);
			$vars['Appointment']['customer_information'][$key] = $value;
			$vars['Appointment']['customer_information_strings'][$key] = $value;
		}

		if ( isset( $vars['Appointment']['AppointmentType']['availability'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['availability'] );
		}
		if ( isset( $vars['Appointment']['AppointmentType']['notifications'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['notifications'] );
		}
		if ( isset( $vars['Appointment']['AppointmentType']['google_calendars_availability'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['google_calendars_availability'] );
		}
		if ( !empty( $vars['Appointment']['AppointmentType']['custom_customer_information'] ) ) {
			$vars['Appointment']['AppointmentType']['customer_information'] = $vars['Appointment']['AppointmentType']['custom_customer_information'];
		}
		if ( isset( $vars['Appointment']['AppointmentType']['custom_customer_information'] ) ) {
			unset( $vars['Appointment']['AppointmentType']['custom_customer_information'] );
		}

		if ( isset( $vars['Author'] ) ) {
			$vars['Customer'] = $vars['Author'];
			unset( $vars['Author'] );
		}

		$vars['admin_email'] = $settings['global']['admin_email'];
		$vars['customer_email'] = isset ( $vars['Appointment']['customer_information']['Email'] ) ? $vars['Appointment']['customer_information']['Email'] : '';
		$vars['customer_name'] = isset ( $vars['Appointment']['customer_information']['Name'] ) ? $vars['Appointment']['customer_information']['Name'] : '';

		
		// =================================================================================================
		// important: get meta data early on
		// =================================================================================================
		$meta = $this->plugin->appointment_model->get_metas( (int)$vars['appointment_id'], array( 'booking_url', 'booking_post_id', 'booking_title', 'cancelation_note', 'canceled_by_user_id', 'rescheduling_note', 'rescheduled_by_user_id', 'rescheduled_from_start_dates' ) );

		
		/* BEGIN customer_start_date */
		if ( ! empty( $vars['Appointment']['customer_locale'] ) ) {
			$this->plugin->translation->set_programmatic_locale( esc_attr( $vars['Appointment']['customer_locale'] ) );
		}
		$format = SSA_Utils::localize_default_date_strings( 'F j, Y g:i a' ) . ' (T)';

		if ( empty( $vars['Appointment']['customer_timezone'] ) ) {
			$customer_timezone = $appointment_obj->get_date_timezone();
			$vars['Appointment']['customer_timezone'] = $customer_timezone->getName();
		} else if ( false !== strpos( $vars['Appointment']['customer_timezone'], 'Etc/' ) ) {
			$customer_timezone = $appointment_obj->get_date_timezone();
			$vars['Appointment']['customer_timezone'] = $customer_timezone->getName();
		} else {
			$customer_timezone = new DateTimeZone( $vars['Appointment']['customer_timezone'] );
		}

		$vars['Appointment']['customer_start_date'] = ssa_datetime( $vars['Appointment']['start_date'] )->setTimezone( $customer_timezone )->format( $format );
		$vars['Appointment']['customer_start_date'] = SSA_Utils::translate_formatted_date( $vars['Appointment']['customer_start_date'] );
		/* END customer_start_date */
		
		/* Start customer_prev_start_dates - rescheduling history */
		if( ! empty( $meta['rescheduled_from_start_dates'] ) ) {
			$customer_prev_start_dates = array_map( function( $date ) use ( $format, $vars, $customer_timezone ) {
				$value = ssa_datetime( $date )->setTimezone( $customer_timezone )->format( $format );
				$value = SSA_Utils::translate_formatted_date( $value );
				return $value;
			}, array_reverse( $meta['rescheduled_from_start_dates'] ) );
			// in business timezone
			$vars['Appointment']['customer_prev_start_dates'] = implode(",\n", $customer_prev_start_dates );
			$vars['Appointment']['customer_prev_start_date'] = array_shift( $customer_prev_start_dates );
		} else {
			$vars['Appointment']['customer_prev_start_dates'] = null;
			$vars['Appointment']['customer_prev_start_date'] = null;
		}
		/* End customer_prev_start_dates - rescheduling history */

		// =================================================================================================
		// important: reset the locale to the default locale before proceeding to the business timezone
		// =================================================================================================
		$this->plugin->translation->set_programmatic_locale( null );
		
		/* BEGIN business_start_date */
		$format = SSA_Utils::localize_default_date_strings( 'F j, Y g:i a' ) . ' (T)';
		$vars['Appointment']['business_start_date'] = ssa_datetime( $vars['Appointment']['start_date'] );

		$vars['Appointment']['business_start_date'] = $this->plugin->utils->get_datetime_as_local_datetime( $vars['Appointment']['business_start_date'], $vars['Appointment']['appointment_type_id'] )->format( $format );
		$vars['Appointment']['business_start_date'] = SSA_Utils::translate_formatted_date( $vars['Appointment']['business_start_date'] );
		/* END business_start_date */
		
		/* Start business_prev_start_dates - rescheduling history */
		if( ! empty( $meta['rescheduled_from_start_dates'] ) ) {
			$business_prev_start_dates = array_map( function( $date ) use ( $format, $vars ) {
				$value = ssa_datetime( $date );
				$value = $this->plugin->utils->get_datetime_as_local_datetime( $value, $vars['Appointment']['appointment_type_id'] )->format( $format );
				$value = SSA_Utils::translate_formatted_date( $value );
				return $value;
			}, array_reverse( $meta['rescheduled_from_start_dates'] ) );
			// in business timezone
			$vars['Appointment']['business_prev_start_dates'] = implode(",\n", $business_prev_start_dates );
			$vars['Appointment']['business_prev_start_date'] = array_shift( $business_prev_start_dates );
		} else {
			$vars['Appointment']['business_prev_start_dates'] = null;
			$vars['Appointment']['business_prev_start_date'] = null;
		}
		/* End business_prev_start_dates - rescheduling history */

		if ( empty( $vars['Appointment']['customer_timezone'] ) || false !== strpos( $vars['Appointment']['customer_timezone'], 'Etc/' ) ) {
			$vars['Appointment']['customer_timezone'] = $vars['Appointment']['date_timezone'];
		}

		// If Appointment date_timezone is a DateTimeZone object, get the name.
		if ( is_object( $vars['Appointment']['date_timezone'] ) ) {
			$vars['Appointment']['date_timezone'] = $vars['Appointment']['date_timezone']->getName();
		}
		
		$vars['booking_url'] = isset( $meta['booking_url'] ) ? $meta['booking_url'] : null;
		$vars['booking_post_id'] = isset( $meta['booking_post_id'] ) ? $meta['booking_post_id'] : null;
		$vars['booking_title'] = isset( $meta['booking_title'] ) ? $meta['booking_title'] : null;
		$vars['cancelation_note'] = isset( $meta['cancelation_note'] ) ? $meta['cancelation_note'] : null;
		$vars['canceled_by_name'] = isset( $meta['canceled_by_user_id'] ) ? $this->get_user_name_from_user_id( $meta['canceled_by_user_id'] ) :  null;
		$vars['rescheduling_note'] = isset( $meta['rescheduling_note'] ) ? $meta['rescheduling_note'] : null;
		$vars['rescheduled_by_name'] = isset( $meta['rescheduled_by_user_id'] ) ? $this->get_user_name_from_user_id( $meta['rescheduled_by_user_id'] ) :  null;
		$vars['location'] = isset($vars['Appointment']['web_meeting_url'] ) ? $vars['Appointment']['web_meeting_url'] : '';

		// List of attendees
		$vars['attendees_list'] = '';
		$appointments = $appointment_obj->query_group_appointments();
		if( $appointments ) {
			$appointments_count = count( $appointments );
			foreach ($appointments as $index => $appointment) {
				$separator = $index < $appointments_count - 1 ? ', ' : '';
				if ( ! $appointment->is_booked() ) {
					continue;
				}
	
				$vars['attendees_list'] .= $appointment->get_customer_name() . $separator . $eol;
			}		
		}

		if ( empty( $vars['instructions'] ) ) {
			$vars['instructions'] = '';
		}
		$vars['instructions'] .= $vars['Appointment']['AppointmentType']['instructions'];

		// Refund policy
		$vars['refund_policy'] = isset($vars['Appointment']['AppointmentType']['payments']) && isset($vars['Appointment']['AppointmentType']['payments']['refund_policy']) ? $vars['Appointment']['AppointmentType']['payments']['refund_policy'] : '';

		// adding empty values for {{team_member_name}}, {{team_member_email}}, {{team_member_phone}} in case they're used without the business edition
		$team_member_keys = array( 'name', 'email', 'phone' );
		foreach ($team_member_keys as $key) {
			$vars['team_member_'.$key] = '';
		}

		return $vars;
	}
	
	
	public function render_template_string( $template_string, $vars ) {
		
		$loader = new Twig\Loader\ArrayLoader(
			array(
				'template'   => $template_string,
				'autoescape' => false,
			)
		);

		$twig = new Twig\Environment( $loader );
		
		// enable sandbox mode
		$twig->addExtension( $this->getCustomTwigSandboxExtension() );
		
		$twig->addExtension( new SSA_Twig_Extension() );
		$twig->getExtension( \Twig\Extension\CoreExtension::class )->setTimezone( 'UTC' );
		try {
			$rendered_template = $twig->render( 'template', $vars );
		} catch ( Exception $e ) {
			return $e;
		}
		
		// need to unescape the html so PHP can process it in wp_kses_post
		// was escaped in the frontend
		$rendered_template = html_entity_decode($rendered_template);
		
		return wp_kses_post($rendered_template);
	}
	
	
	/**
	 * Initialize sandbox extension with custom policy to prevent template-injections
	 *
	 * @return \Twig\Extension\SandboxExtension
	 */
	public function getCustomTwigSandboxExtension()
	{
		$tags = array('if', 'else', 'elseif', 'endif', 'include', 'import', 'block', 'set', 'for');
		$filters = array( 'internationalize', 'trim', 'join', 'number_format', 'date', 'escape', 'trans', 'split', 'length', 'slice', 'lower', 'raw', 'filter', 'date', 'upper', 'link', 'nl2br', 'replace');
		$methods = array();
		$properties = array();
		$functions = array('include', 'path', 'absolute_url', 'asset', 'is_granted', 'attribute');

		$policy = new \Twig\Sandbox\SecurityPolicy($tags, $filters, $methods, $properties, $functions);
		return new \Twig\Extension\SandboxExtension( $policy, true );
	}



	/**
	 * Get template part.
	 *
	 * SSA_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
	 *
	 * @access public
	 * @param mixed  $slug Template slug.
	 * @param string $name Template name (default: '').
	 * @param boolean $echo  Should echo $output to screen. (default: false).
	 * 
	 * @return string
	 */
	function get_template_part( $slug, $name = '', $echo = false ) {
		ob_start();
		$template = '';

		// Look in yourtheme/slug-name.php and yourtheme/ssa/slug-name.php.
		if ( $name && ! SSA_TEMPLATE_DEBUG_MODE ) {
			$template = locate_template( array( "{$slug}-{$name}.php", $this->plugin->template_subdirectory() . "{$slug}-{$name}.php" ) );
		}

		// Get default slug-name.php.
		if ( ! $template && $name && file_exists( $this->plugin->dir() . "templates/{$slug}-{$name}.php" ) ) {
			$template = $this->plugin->dir() . "templates/{$slug}-{$name}.php";
		}

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/ssa/slug.php.
		if ( ! $template && ! SSA_TEMPLATE_DEBUG_MODE ) {
			$template = locate_template( array( "{$slug}.php", $this->plugin->template_subdirectory() . "{$slug}.php" ) );
		}

		// Allow 3rd party plugins to filter template file from their plugin.
		$template = apply_filters( 'ssa/templates/get_template_part', $template, $slug, $name );

		if ( $template ) {
			load_template( $template, false );
		}

		$output = ob_get_clean();
		if ( !empty( $echo ) ) {
			echo $output;
		}

		return $output;
	}

	/**
	 * Get other templates passing attributes and including the file.
	 *
	 * @access public
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 * @param boolean $echo  Should echo $output to screen. (default: false).
	 * 
	 * @return string
	 */
	function get_template( $template_name, $args = array(), $template_path = '', $default_path = '', $echo = false ) {
		ob_start();

		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args ); // @codingStandardsIgnoreLine
		}

		$located = $this->locate_template( $template_name, $template_path, $default_path );

		if ( ! file_exists( $located ) ) {
			return;
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$located = apply_filters( 'ssa/templates/get_template', $located, $template_name, $args, $template_path, $default_path );

		do_action( 'ssa_before_template_part', $template_name, $template_path, $located, $args );

		include $located;

		do_action( 'ssa_after_template_part', $template_name, $template_path, $located, $args );

		$output = ob_get_clean();
		if ( !empty( $echo ) ) {
			echo $output;
		}

		return $output;
	}

	public function get_template_rendered( $template, $template_vars = array() ) {
		$template_string = $this->get_template( $template );
		$template_vars = $this->get_template_vars( $template, $template_vars );
		if ( empty( $template_string ) ) {
			return false;
		}

		$rendered_template_string = $this->render_template_string( $template_string, $template_vars );

		return $rendered_template_string;
	}


	/**
	 * Like get_template, but returns the HTML instead of outputting.
	 *
	 * @see get_template_html
	 * @since 2.5.0
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 *
	 * @return string
	 */
	function get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		ob_start();
		$this->get_template( $template_name, $args, $template_path, $default_path );
		return ob_get_clean();
	}
	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 * yourtheme/$template_path/$template_name
	 * yourtheme/$template_name
	 * $default_path/$template_name
	 *
	 * @access public
	 * @param string $template_name Template name.
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 * @return string
	 */
	function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = $this->plugin->template_subdirectory();
		}

		if ( ! $default_path ) {
			$default_path = $this->plugin->dir() . 'templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			)
		);

		// Get default template/.
		if ( ! $template || SSA_TEMPLATE_DEBUG_MODE ) {
			$template = $default_path . $template_name;
		}

		// Return what we found.
		return apply_filters( 'ssa/templates/locate_template', $template, $template_name, $template_path );
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 * yourtheme/$template_path/$template_name
	 * yourtheme/$template_name
	 * $default_path/$template_name
	 *
	 * @access public
	 * @param string $template_name Template name.
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 * @return string
	 */
	function locate_template_url( $template_name, $template_path = '', $default_path = '' ) {
		$template = $this->locate_template( $template_name, $template_path, $default_path );

		if ( empty( $template ) ) {
			return;
		}

		if ( $themes_pos = strpos( $template, 'themes/' ) ) {
			return content_url( substr( $template, $themes_pos ) );
		}

		if ( $plugins_pos = strpos( $template, 'plugins/' ) ) {
			return content_url( substr( $template, $plugins_pos ) );
		}

		return false;
	}


	public function cleanup_variables_in_string( $string ) {
		$string =  $this->strip_mustache_content($string);		

		$string = str_replace(
			array( '{{', '{{  ', '}}', '  }}', '{%', '{%  ', '%}', '  %}', "\n }}", "\n}}" ),
			array( '{{ ', '{{ ', ' }}', ' }}', '{% ', '{% ', ' %}', ' %}', ' }}', '}}' ),
			$string
		);	

		return $string;
	}

	/**
	 * Cleans HTML tags within mustache and template tags in the provided text.
	 * 
	 * This function processes the input text to remove any HTML entities or elements
	 * inside mustache tags ({{ }}) and template tags ({% %}). It ensures that the content
	 * within these tags is stripped of any HTML, leaving only the plain text or function calls.
	 *
	 * @param string $text The input text containing mustache and template tags.
	 * @return string The cleaned text with HTML tags removed from mustache and template tags.
	 */
	public function strip_mustache_content($text) {
		$mustachePattern = '/{{\s*([^}]+)\s*}}/';
				$mustacheCallback = function ($matches) {
				return '{{' . strip_tags(trim($matches[1])) . '}}';
		};
		$text = preg_replace_callback($mustachePattern, $mustacheCallback, $text);
		
		$templatePattern = '/{%\s*([^}]+)\s*%}/';
		$templateCallback = function ($matches) {
				return '{%' . strip_tags(trim($matches[1])) . '%}';
		};
		$text = preg_replace_callback($templatePattern, $templateCallback, $text);
		
		return $text;
	}

	/**
	 * Having the user_id check if it's a staff member and return its display name
	 * Otherwise use get_userdata wp function to get the user name from the users table
	 *
	 * @param string $user_id
	 * @return string
	 */
	public function get_user_name_from_user_id( $user_id = null ){
		if( empty( $user_id ) ) {
			return __( 'A logged out user', 'simply-schedule-appointments' );
		}

		// Check if who canceled is a staff member
		if( class_exists( 'SSA_Staff_Model' ) ) {
			$staff_id = $this->plugin->staff_model->get_staff_id_for_user_id( $user_id );

			if( ! empty( $staff_id ) ) {
				$staff = new SSA_Staff_Object( $staff_id );
				return $staff->display_name;
			}
		}

		// Try get the display name from the wp users table or the user_login(username)
		$userdata = get_userdata( $user_id );

		if( ! empty( $userdata ) &&  ! empty( $userdata->display_name ) ) {
			return $userdata->display_name;
		} 
		
		if( ! empty( $userdata ) && ! empty( $userdata->user_login )  ) {
			return $userdata->user_login;
		} 

		return __( 'A logged out user', 'simply-schedule-appointments' ); // Just in case but we shouldn't really reach this far
		
	}


function ssa_get_tec_vars( $vars, $template ) {

	if ( empty( $vars['booking_post_id'] ) ) {
		return $vars;
	}

	if ( ! function_exists( 'tribe_get_venue_object' ) ) {
		return $vars;

	}

	if ( ! function_exists( 'tribe_get_organizer_object' ) ) {
		return $vars;
	
	}
	
	$event_id = $vars['booking_post_id'];

	// Add Venue Information

	$venue_object = tribe_get_venue_object($event_id);
	$venue_id = get_post_meta($event_id, '_EventVenueID', true);

	if (!empty($venue_id)) {
		$venue_post = get_post($venue_id);
        $venue_information = array(
            'name'    => !empty( $venue_post->post_title ) ? $venue_post->post_title : null,
            'address' => !empty( $venue_object->address ) ? $venue_object->address : null,
            'city'    => !empty( $venue_object->city ) ? $venue_object->city : null,
            'state'   => !empty( $venue_object->state ) ? $venue_object->state : null,
            'province'   => !empty( $venue_object->province ) ? $venue_object->province : null,
            'zip'     => !empty( $venue_object->zip ) ? $venue_object->zip : null,
        );

		$vars['TEC']['venue'] = $venue_information;

	}
	
	// Add Organizer Information

	$organizer_object = tribe_get_organizer_object($event_id);
	$organizer_id = get_post_meta($event_id, '_EventOrganizerID', true);

	if (!empty($organizer_id)) {

		$organizer_post = get_post($organizer_id);
        $organizer_information = array(
            'name'    => !empty( $organizer_post->post_title ) ? $organizer_post->post_title : null,
            'phone'   => !empty( $organizer_object->phone ) ? $organizer_object->phone : null,
            'email'   => !empty( $organizer_object->email ) ? $organizer_object->email : null,
            'website' => !empty( $organizer_object->website ) ? $organizer_object->website : null,
        );
		
		$vars['TEC']['organizer'] = $organizer_information;
		
		}

	// Add Categories for the event

	$event_categories = get_the_terms( $event_id, 'tribe_events_cat' ); 

    if ( ! empty( $event_categories ) && ! is_wp_error( $event_categories ) ) {
        $tec_categories= [];
        foreach ( $event_categories as $index => $category ) {
            $tec_categories[$index] = $category->name;
        }
        $vars['TEC']['categories'] = $tec_categories;
    }

	return $vars;

}
}
