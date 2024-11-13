<?php
/**
 * Simply Schedule Appointments Availability Block.
 *
 * @since   3.6.10
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Availability Block.
 *
 * @since 3.6.10
 */
use League\Period\Period;

class SSA_Availability_Block_Factory extends SSA_Availability_Block {
	public static function create( array $data = array() ) {
		$data = array_merge( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,
			'capacity_reserved' => 0,
			'capacity_reserved_delta' => 0,

			'appointment_id' => 0,
			'appointment_type_id' => 0,
			'staff_id' => 0,

			'type' => '',
			'subtype' => '',

			'period' => new Period(
				'2116-01-01',
				'2117-01-01'
			),
		), $data );

		if ( empty( $data['period'] ) ) {
			unset( $data['period'] );
		}

		$instance = new SSA_Availability_Block();
		foreach ($data as $key => $value) {
			$instance->$key = $value;
		}

		return $instance;
	}	

	public static function create_random( array $data = array() ) {
		$start_time = time() + rand(1,1000) * 3600;

		$data = array_merge( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,
			'capacity_reserved' => 0,

			'appointment_type_id' => 0,
			'staff_id' => 0,

			'type' => '',
			'subtype' => '',

			'period' => new Period(
				gmdate( 'Y-m-d H:00:00', $start_time ),
				gmdate( 'Y-m-d H:00:00', $start_time + rand( 1, 1000 ) * 3600 )
			),
		), $data );

		$instance = new SSA_Availability_Block();
		foreach ($data as $key => $value) {
			$instance->$key = $value;
		}

		return $instance;
	}


	public static function create_from_appointment( SSA_Appointment_Object $appointment ) {
		$data = array(
			'appointment_id' => $appointment->id,
			'appointment_type_id' => $appointment->get_appointment_type()->id,
			'capacity_reserved_delta' => ( $appointment->is_unavailable() ) ? 1 : 0,
			'period' => $appointment->get_appointment_period(),
		);
		return self::create( $data );
	}

	public static function create_from_buffered_appointment( SSA_Appointment_Object $appointment ) {
		$appointment_type = $appointment->get_appointment_type();
		$data = array(
			'appointment_id' => $appointment->id,
			'appointment_type_id' => $appointment_type->id,
			'buffer_reserved_delta' => 0,
			'period' => $appointment->get_buffered_period(),
		);

		if ( $appointment_type->get_buffer_capacity_max() > 0 ) {
			if ( $appointment->is_unavailable() ) {
				$data['buffer_reserved_delta'] = 1;
			}
		}
		
		return self::create( $data );
	}

	public static function available_for_period( Period $period, $args = array() ) {
		$args = array_merge( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,

			'period' => $period,
		), $args );
		return self::create( $args );
	}

	public static function available_for_eternity() {
		return self::available_for_period( SSA_Constants::EPOCH_PERIOD() );
	}

	public static function fixture_blackout_xmas() {
		return self::create( array(
			'type' => 'global',
			'subtype' => 'blackout',

			'capacity_available' => 0,

			'period' => new Period(
				'2116-12-25',
				'2116-12-26'
			),
		) );
	}

	public static function fixture_q1() {
		return self::create( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,

			'period' => new Period(
				'2116-01-01',
				'2116-04-01'
			),
		) );
	}
	public static function fixture_q2() {
		return self::create( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,

			'period' => new Period(
				'2116-04-01',
				'2116-07-01'
			),
		) );
	}
	public static function fixture_q3() {
		return self::create( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,

			'period' => new Period(
				'2116-07-01',
				'2116-10-01'
			),
		) );
	}
	public static function fixture_q4() {
		return self::create( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,

			'period' => new Period(
				'2116-10-01',
				'2117-01-01'
			),
		) );
	}

	public static function fixture_blackout_newyear() {
		return self::create( array(
			'type' => 'global',
			'subtype' => 'blackout',

			'capacity_available' => 0,

			'period' => new Period(
				'2116-01-01',
				'2116-01-02'
			),
		) );
	}

	public static function fixture_twentytwenty() {
		return self::create( array(
			'capacity_available' => SSA_Constants::CAPACITY_MAX,

			'period' => new Period(
				'2116-01-01',
				'2117-01-01'
			),
		) );
	}

	/**
	 * Create a new block representing an unavailable time slot.
	 * 
	 * @param string $start_date The start date of the slot in 'Y-m-d H:i:s' format.
	 * @param int    $duration The duration of the slot in minutes.
	 * 
	 * @return SSA_Availability_Block The unavailable slot/block.
	 */
	public static function create_unavailable_block( $start_date, $duration ) {
		$start_time = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $start_date );
		
		$end_time = $start_time->add( new DateInterval('PT' . $duration . 'M') );
 
		$period = new Period( $start_time, $end_time );

		$unavailable_slot = new SSA_Availability_Block();
		$unavailable_slot = $unavailable_slot->set_period( $period );
		$unavailable_slot = $unavailable_slot->set_capacity_available(0);

		return $unavailable_slot;
	}


}
