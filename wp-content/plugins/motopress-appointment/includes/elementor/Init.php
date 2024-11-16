<?php

namespace MotoPress\Appointment\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
class Init {

	/**
	 * @since 1.15.2
	 */
	const WIDGET_CATEGORY_NAME = 'mpa-elementor-widgets';

	public function __construct() {

		// Check if the Elementor is active
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}

		add_filter(
			'elementor/elements/categories_registered',
			function ( \Elementor\Elements_Manager $elementsManager ) {

				$elementsManager->add_category(
					self::WIDGET_CATEGORY_NAME,
					array(
						'title' => mpapp()->getName(),
						'icon'  => 'font',
					)
				);
			},
			10,
			1
		);

		add_filter(
			'elementor/widgets/register',
			function ( \Elementor\Widgets_Manager $widgetsManager ) {

				foreach ( $this->widgets() as $widget ) {

					$widgetsManager->register( $widget );
				}
			},
			10,
			1
		);
	}

	/**
	 * @since 1.15.2
	 */
	protected function widgets(): array {

		return apply_filters(
			'mpa_elementor_widgets',
			array(
				new Widgets\AppointmentFormWidget(),
				new Widgets\EmployeesListWidget(),
				new Widgets\LocationsListWidget(),
				new Widgets\ServiceCategoriesWidget(),
				new Widgets\ServicesListWidget(),
				new Widgets\EmployeeImageWidget(),
				new Widgets\EmployeeTitleWidget(),
				new Widgets\EmployeeServicesListWidget(),
				new Widgets\EmployeeScheduleWidget(),
				new Widgets\EmployeeContentWidget(),
				new Widgets\EmployeeContactsWidget(),
				new Widgets\EmployeeContactsWidget(),
				new Widgets\EmployeeSocialNetworksWidget(),
				new Widgets\EmployeeAdditionalInfoWidget(),
			)
		);
	}
}
