<?php
/**
 * Class: AbstractAppointmentWidget
 * Name: Appointment Form
 * Slug: appointment-form
 */

namespace MotoPress\Appointment\Elementor\Widgets;

use \Elementor\Widget_Base;
use MotoPress\Appointment\Elementor\Init;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractAppointmentWidget extends Widget_Base {

	public function get_style_depends() {
		return array( 'mpa-public' );
	}

	public function get_keywords() {
		return array( 'appointment' );
	}

	public function get_categories() {
		return array( Init::WIDGET_CATEGORY_NAME );
	}
}
