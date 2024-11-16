<?php
/**
 * @package MotoPress\Appointment\Rest
 * @since 1.21.0
 */

namespace MotoPress\Appointment\Rest\Controllers\V1;

use MotoPress\Appointment\Rest\Controllers\AbstractRestObjectController;
use MotoPress\Appointment\Rest\Data\PaymentData;
use WP_REST_Request;

class PaymentsController extends AbstractRestObjectController {


	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'mpa/v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'payments';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'mpa_payment';

	/**
	 * Prepare links for the request.
	 *
	 * @param PaymentData $paymentData Payment data object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $paymentData, $request ) {
		$links = parent::prepare_links( $paymentData, $request );

		$bookingId = $paymentData->entity->getBookingId();
		if ( $bookingId ) {
			$links['booking'] = array(
				'href'       => rest_url( sprintf( '/%s/%s/%d', $this->namespace, 'bookings', $bookingId ) ),
				'embeddable' => true,
			);
		}

		return $links;
	}
}