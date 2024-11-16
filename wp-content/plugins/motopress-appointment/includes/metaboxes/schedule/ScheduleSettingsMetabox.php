<?php

namespace MotoPress\Appointment\Metaboxes\Schedule;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;
use \MotoPress\Appointment\Handlers\SecurityHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ScheduleSettingsMetabox extends FieldsMetabox {

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'schedule_settings_metabox';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function theFields() {

		$manageLocationsAdminUrl = esc_url(
			admin_url(
				'edit.php?post_type=' . mpapp()->postTypes()->location()->getPostType()
			)
		);

		return array(
			'employee'      => array(
				'type'        => 'select',
				'label'       => esc_html__( 'Employee', 'motopress-appointment' ),
				'description' => esc_html__( 'To which employee this schedule applies.', 'motopress-appointment' ),
				'options'     => mpa_no_value( 0 ) + mpa_get_employees(),
				'default'     => 0,
				'disabled'    => ! SecurityHandler::isUserCanAssignEmployeeToSchedule(),
			),
			'main_location' => array(
				'type'        => 'select',
				'label'       => esc_html__( 'Main Location', 'motopress-appointment' ) . ' *',
				'description' => esc_html__( 'The location where the employee spends the major amount of time.', 'motopress-appointment' ) .
					' ' . esc_html__( 'This schedule will not work until you set the main location for it.', 'motopress-appointment' ) .
					' <a href="' . $manageLocationsAdminUrl . '">' .
					esc_html__( 'Add New Location', 'motopress-appointment' ) .
					'</a>',
				'options'     => mpa_no_value( 0 ) + mpa_get_locations(),
				'default'     => 0,
			),
		);
	}

	/**
	 * @param array $values [Postmeta name => [add, update, delete]]
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @since 1.0
	 */
	protected function saveValues( array $values, int $postId, \WP_Post $post ) {

		parent::saveValues( $values, $postId, $post );

		// Each employee may have only one schedule
		if ( isset( $values['update']['_mpa_employee'] ) ) {
			$employeeId = $values['update']['_mpa_employee'];

			$schedules = mpapp()->repositories()->schedule()->findAllByEmployee(
				$employeeId,
				array(
					'exclude' => array( $postId ),
					'fields'  => 'ids',
				)
			);

			foreach ( $schedules as $scheduleId ) {
				delete_post_meta( $scheduleId, '_mpa_employee' );
			}
		}
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel(): string {
		return esc_html__( 'Schedule Settings', 'motopress-appointment' );
	}
}
