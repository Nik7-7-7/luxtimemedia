<?php

declare(strict_types=1);

namespace MotoPress\Appointment\AdminPages\Edit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.11.0
 */
class EditCouponPage extends EditPostPage {

	protected function addActions() {

		parent::addActions();

		add_filter( 'enter_title_here', array( $this, 'filterPostTitlePlaceholder' ) );
	}

	/**
	 * @access protected
	 */
	public function filterPostTitlePlaceholder( string $title ): string {

		$title = esc_html__( 'Coupon code', 'motopress-appointment' );

		return $title;
	}
}
