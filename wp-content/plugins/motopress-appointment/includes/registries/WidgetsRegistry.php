<?php

namespace MotoPress\Appointment\Registries;

use MotoPress\Appointment\Widgets;
use MotoPress\Appointment\Views\WidgetsView;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.3
 */
class WidgetsRegistry {

	/**
	 * @var array
	 *
	 * @since 1.3
	 */
	protected $widgets = array();

	/**
	 * @since 1.19.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'maybeEnqueueScripts' ) );
	}

	/**
	 * @return Widgets\AppointmentFormWidget
	 *
	 * @since 1.3
	 */
	public function appointmentForm() {
		if ( ! isset( $this->widgets['appointmentForm'] ) ) {
			$this->widgets['appointmentForm'] = new Widgets\AppointmentFormWidget();

			WidgetsView::getInstance()->addAppointmentFormActions();
		}

		return $this->widgets['appointmentForm'];
	}

	/**
	 * @since 1.19.0
	 */
	function maybeEnqueueScripts( $hook ) {
		if ( 'widgets.php' != $hook ) {
			return;
		}

		wp_enqueue_script( 'mpa-widgets-manage-page' );
		wp_enqueue_style( 'mpa-widgets-manage-page' );
	}

	/**
	 * @since 1.3
	 */
	public function registerAll() {
		$this->appointmentForm();
	}
}
