<?php
/**
 * Simply Schedule Appointments Blackout Dates.
 *
 * @since   0.1.0
 * @package Simply_Schedule_Appointments
 */
use League\Period\Period;

/**
 * Simply Schedule Appointments Blackout Dates.
 *
 * @since 0.1.0
 */
class SSA_Blackout_Dates {
	/**
	 * Parent plugin class.
	 *
	 * @since 0.1.0
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  0.1.0
	 *
	 * @param  Simply_Schedule_Appointments $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {
		add_filter( 'ssa/get_blocked_periods/blocked_periods', array( $this, 'filter_blocked_periods' ), 5, 3 );
		add_filter( 'ssa/availability/troubleshoot', array( $this, 'troubleshoot_availability' ), 10, 4 );

		// scheduled cleanup
		add_action( 'admin_init', array( $this, 'schedule_blackout_date_cleanup' ) );
		add_action( 'ssa/blackout_dates/cleanup', array( $this, 'maybe_cleanup_past_blackout_dates' ), 10, 0 );
	}

	/**
	 * Scheduling the blackout dates cleanup async action
	 *
	 * @return void
	 */
	public function schedule_blackout_date_cleanup() {
		if ( false === ssa_has_scheduled_action( 'ssa/blackout_dates/cleanup' ) ) {
			ssa_schedule_recurring_action( strtotime( 'now' ), WEEK_IN_SECONDS, 'ssa/blackout_dates/cleanup' );
		}
	}

	public function maybe_cleanup_past_blackout_dates() {
		if ( ! $this->plugin->settings_installed->is_enabled( 'blackout_dates' ) ) {
			return;
		}
		$this->cleanup_business_wide_blackout_dates();
		$this->maybe_cleanup_staff_blackout_dates();
	}

	/**
	 * Cleans up the Business wide blackout dates by removing dates that are more than one month old.
	 *
	 * @return void
	 */
	public function cleanup_business_wide_blackout_dates() {

		$blackout_settings = $this->plugin->blackout_dates_settings->get();

		if ( empty( $blackout_settings['dates'] ) ) {
			return;
		}

		$today = new DateTimeImmutable();
		$one_month_ago = $today->sub(new DateInterval('P1M'))->format('Y-m-d');

		$filtered_dates = array_filter( $blackout_settings['dates'], function( $date ) use ( $one_month_ago ) {
			return $date >= $one_month_ago;
		});

		$blackout_settings['dates'] = array_values( $filtered_dates );
		$this->plugin->blackout_dates_settings->update( $blackout_settings );
	}

	public function maybe_cleanup_staff_blackout_dates() {
		if ( class_exists( 'SSA_Staff' ) ) {
			$this->plugin->staff->cleanup_staff_blackout_dates();
		}
	}

	public function troubleshoot_availability( $response, SSA_Appointment_Type_Object $appointment_type, Period $period, SSA_Appointment_Object $appointment ) {
		$args = array_merge( $response['cleared_args'], array(
			'blackout_dates' => false,
		) );
		$disabled_arg_query = new SSA_Availability_Query(
			$appointment_type,
			$period,
			$args
		);

		if ( $disabled_arg_query->is_prospective_appointment_bookable( $appointment ) ) {
			$response['reasons'][] = array(
				'reason' => 'blackout_dates',
				'reason_title' => __( 'Blocked by a global blackout date', 'simply-schedule-appointments' ),
				'reason_description' => __( 'The date you selected is set as a global blackout date in your settings', 'simply-schedule-appointments' ),
				'help_center_url' => '',
				'ssa_path' => '/ssa/settings/blackout',
			);
		}

		return $response;
	}

	public function get_blackout_dates() {
		$blackout_settings = $this->plugin->blackout_dates_settings->get();
		if ( empty( $blackout_settings['dates'] ) ) {
			$array_of_blocked_dates = array();
		} else {
			$array_of_blocked_dates = $blackout_settings['dates'];
		}

		sort( $array_of_blocked_dates );

		return $array_of_blocked_dates;
	}

	public function filter_blocked_periods( $blocked_periods, $appointment_type, $args ) {
		if ( !$this->plugin->settings_installed->is_enabled( 'blackout_dates' ) ) {
			return $blocked_periods;
		}
		
		$array_of_blocked_dates = $this->plugin->blackout_dates_settings->get();
		if ( empty( $array_of_blocked_dates['dates'] ) || !is_array( $array_of_blocked_dates['dates'] ) ) {
			return $blocked_periods;
		}

		foreach ($array_of_blocked_dates['dates'] as $key => $blocked_date_string ) {
			$local_timezone = $this->plugin->utils->get_datetimezone( $appointment_type['id'] );
			$blocked_start_local = new DateTimeImmutable( $blocked_date_string, $local_timezone );
			$blocked_period = new Period( $blocked_start_local, $blocked_start_local->add( new DateInterval( 'P1D' ) ) );
			$blocked_periods[] = $blocked_period;
		}

		return $blocked_periods;
	}

	public function get_schedule( SSA_Appointment_Type_Object $appointment_type, Period $query_period, $args ) {
		if ( !$this->plugin->settings_installed->is_enabled( 'blackout_dates' ) ) {
			return new SSA_Availability_Schedule();
		}

		$query_period = SSA_Utils::get_query_period( $query_period );
		$blackout_dates = $this->get_blackout_dates();
		
		$blackout_availability_blocks = array();

		$local_timezone = $this->plugin->utils->get_datetimezone( $appointment_type->id );

		$schedule = new SSA_Availability_Schedule();
		foreach ($blackout_dates as $blocked_date_string) {
			$start_date = new DateTimeImmutable( $blocked_date_string, $local_timezone );
			$blackout_period = new Period(
				$start_date,
				$start_date->add( new DateInterval( 'P1D' ) )
			);
			$blackout_period = SSA_Utils::get_period_in_utc( $blackout_period );
			if ( ! $query_period->overlaps( $blackout_period ) ) {
				continue;
			}

			$schedule = $schedule->pushmerge( SSA_Availability_Block_Factory::available_for_period( $blackout_period, array(
				'capacity_available' => 0,
				'buffer_available' => SSA_Constants::CAPACITY_MAX
			) ) );
		}

		return $schedule;
	}
}
