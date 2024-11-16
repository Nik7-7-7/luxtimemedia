<?php
/**
 * Simply Schedule Appointments Capacity.
 *
 * @since   3.7.2
 * @package Simply_Schedule_Appointments
 */

/**
 * Simply Schedule Appointments Capacity.
 *
 * @since 3.7.2
 */
class SSA_Capacity {
	/**
	 * Parent plugin class.
	 *
	 * @since 3.7.2
	 *
	 * @var   Simply_Schedule_Appointments
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 *
	 * @since  3.7.2
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
	 * @since  3.7.2
	 */
	public function hooks() {
		add_filter( 'ssa/appointment/before_insert', array( $this, 'set_group_id' ), 20, 1 );
		add_action( 'ssa/appointment/after_insert', array( $this, 'set_group_id_for_first_appointment' ), 20, 2 );
	}

	public function set_group_id( $data = array() ) {
		if ( empty( $data['appointment_type_id'] ) ) {
			return $data;
		}
		$appointment_type = new SSA_Appointment_Type_Object( $data['appointment_type_id'] );
		$capacity_type = $appointment_type->capacity_type;

		if ( empty( $capacity_type ) || $capacity_type !== 'group' ) {
			return $data;
		}

		$group_id = 0;
		// Look for existing group
		if ( ! empty( $data['staff_ids'] ) ) {
			$appointment_ids_for_staff_at_this_start_date = $this->plugin->staff_appointment_model->get_appointment_ids_at_start_date( $data['staff_ids'], $data['start_date'] );
			if ( empty( $appointment_ids_for_staff_at_this_start_date ) ) {
				return $data;
			}
			$appointment_arrays = $this->plugin->appointment_model->query( array(
				'number' => -1, 
				'id' => $appointment_ids_for_staff_at_this_start_date,
				'orderby' => 'id',
				'order' => 'ASC', 
			) );
		} else if ( ! empty( $data['selected_resources'] ) ) {
			$selected_resources = $data['selected_resources'];
			$identifiable_selected_resources = array_filter($selected_resources, function($selected_resource) {
				$resource_group = new SSA_Resource_Group_Object( $selected_resource['resource_group_id'] );
				return $resource_group->resource_type === "identifiable";
			});

			$resource_ids = array_column($identifiable_selected_resources, 'resource_id');
			$appointment_ids_for_identifiable_resources_at_this_start_date = $this->plugin->resource_appointment_model->get_appointment_ids_at_start_date( $resource_ids, $data['start_date'] );
			if ( empty( $appointment_ids_for_identifiable_resources_at_this_start_date ) ) {
				return $data;
			}
			$appointment_arrays = $this->plugin->appointment_model->query( array(
				'number' => -1, 
				'id' => $appointment_ids_for_identifiable_resources_at_this_start_date,
				'orderby' => 'id',
				'order' => 'ASC', 
			) );
		} else {
			$appointment_arrays = $appointment_type->get_appointments( null, array(
				'start_date' => $data['start_date'],
			) );
		}

		$appointment_arrays_to_update = array();
		foreach ($appointment_arrays as $appointment_array) {
			if ( ! empty( $group_id ) && $group_id == $appointment_array['group_id'] ) {
				continue;
			}

			if ( empty( $group_id ) && ! empty( $appointment_array['id'] ) ) {
				$group_id = $appointment_array['id'];
			}

			$appointment_arrays_to_update[] = $appointment_array;
		}

		if ( empty( $group_id ) ) {
			return $data;
		}

		foreach ($appointment_arrays_to_update as $appointment_array) {
			if ( $appointment_array['group_id'] == $group_id ) {
				continue;
			}

			$this->plugin->appointment_model->update( $appointment_array['id'], array(
				'group_id' => $group_id,
			) );
		}

		$data['group_id'] = $group_id;

		return $data;
	}

	public function set_group_id_for_first_appointment( $appointment_id, $data = array() ) {

		if ( ! empty( $data['group_id'] ) ) {
			return;
		}

		if ( empty( $data['appointment_type_id'] ) ) {
			return;
		}

		$appointment_type = new SSA_Appointment_Type_Object( $data['appointment_type_id'] );
		$capacity_type = $appointment_type->capacity_type;
		if ( empty( $capacity_type ) || $capacity_type !== 'group' ) {
			return;
		}

		$this->plugin->appointment_model->update( $appointment_id, array(
			'group_id' => $appointment_id,
		) );

		return $data;
	}
}
