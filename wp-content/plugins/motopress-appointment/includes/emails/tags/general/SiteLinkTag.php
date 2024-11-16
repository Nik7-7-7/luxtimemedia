<?php

namespace MotoPress\Appointment\Emails\Tags\General;

use MotoPress\Appointment\Emails\Tags\AbstractTag;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class SiteLinkTag extends AbstractTag {

	public function getName(): string {
		return 'site_link';
	}

	protected function description(): string {
		return esc_html__( 'Site address (URL)', 'motopress-appointment' );
	}

	public function getTagContent(): string {
		$title = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		return mpa_tmpl_link( home_url(), $title );
	}
}
