<?php

namespace MotoPress\Appointment\Emails\Tags\General;

use MotoPress\Appointment\Emails\Tags\AbstractTag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class SiteTitleTag extends AbstractTag {

	public function getName(): string {
		return 'site_title';
	}

	public function description(): string {
		return esc_html__( 'Site title (set in Settings > General)', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}
}
