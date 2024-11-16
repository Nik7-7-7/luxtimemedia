<?php

namespace MotoPress\Appointment\Gutenberg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
class Init {


	public function __construct() {

		if ( mpa_wordpress_at_least( '5.8' ) ) {
			add_filter( 'block_categories_all', array( $this, 'registerGutenbergCategories' ) );
		} else {
			add_filter( 'block_categories', array( $this, 'registerGutenbergCategories' ) );
		}

		$this->includeGutenbergBlocks();

		// Fires after block assets have been enqueued for the editing interface.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueueGutenbergAssets' ) );
	}

	public function registerGutenbergCategories( $categories ) {

		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'mpa-gutenberg-blocks',
					'title' => mpapp()->getName(),
				),
			)
		);
	}

	public function includeGutenbergBlocks() {

		new Blocks\AppointmentFormBlock();
		new Blocks\EmployeesListBlock();
		new Blocks\LocationsListBlock();
		new Blocks\ServiceCategoriesBlock();
		new Blocks\ServiceListBlock();
		new Blocks\EmployeeImageBlock();
		new Blocks\EmployeeTitleBlock();
		new Blocks\EmployeeServicesListBlock();
		new Blocks\EmployeeScheduleBlock();
		new Blocks\EmployeeContentBlock();
		new Blocks\EmployeeContactsBlock();
		new Blocks\EmployeeSocialNetworksBlock();
		new Blocks\EmployeeAdditionalInfoBlock();
	}

	/**
	 * Enqueue editor-only js and css (Enqueue scripts (only on Edit Post Page))
	 */
	public function enqueueGutenbergAssets() {

		$version      = mpapp()->getVersion();
		$unselectedId = array( 0 => esc_html__( '— Unselected —', 'motopress-appointment' ) );

		// Enqueue the bundled block JS file
		wp_enqueue_script( 'mpa-gutenberg-blocks' );

		wp_localize_script(
			'mpa-gutenberg-blocks',
			'MotoPress_Appointment',
			apply_filters(
				'mpa_gutenberg_blocks_localize_data',
				array(
					'get' => array(
						'service_categories' => $unselectedId + mpa_get_service_categories(),
						'services'           => $unselectedId + mpa_get_services(),
						'locations'          => $unselectedId + mpa_get_locations(),
						'employees'          => $unselectedId + mpa_get_employees(),
					),
				)
			)
		);

		wp_enqueue_style( 'mpa-gutenberg-blocks' );
	}

}
