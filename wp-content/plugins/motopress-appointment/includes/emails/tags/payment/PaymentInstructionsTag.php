<?php

namespace MotoPress\Appointment\Emails\Tags\Payment;

use \MotoPress\Appointment\Entities\Payment;
use \MotoPress\Appointment\Payments\Gateways\AbstractInstructionPaymentGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.15.2
 */
class PaymentInstructionsTag extends AbstractPaymentEntityTag {

	public function getName(): string {
		return 'payment_instructions';
	}

	protected function description(): string {
		return esc_html__( 'Payment gateway instructions', 'motopress-appointment' );
	}

	public function getTagContent(): string {

		$tagContent = '';

		$payment = $this->getEntity();

		if ( $payment instanceof Payment ) {

			$gateway = $payment->getGateway();

			if ( null !== $gateway &&
				$gateway instanceof AbstractInstructionPaymentGateway
			) {

				$tagContent = $gateway->getInstructions();
			}
		}

		return $tagContent;
	}
}
