<?php

namespace MotoPress\Appointment\Payments\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.5.0
 */
abstract class AbstractInstructionPaymentGateway extends AbstractPaymentGateway {


	public function getInstructions(): string {
		return $this->getOption( 'instructions', $this->getDefaultInstructions() );
	}

	protected function getDefaultInstructions(): string {
		return '';
	}

	/**
	 * @since 1.5.0
	 */
	public function printBillingFields() {

		$instructions = $this->getInstructions();

		if ( ! empty( $instructions ) ) {
			echo '<p>' . wp_kses_post( $instructions ) . '</p>';
		} else {
			echo '';
		}
	}

	/**
	 * @since 1.5.0
	 *
	 * @return array Raw field args.
	 */
	public function getFields() {

		$fields = parent::getFields();

		// Add instructions
		$fields[ $this->getOptionNameRaw( 'instructions' ) ] = array(
			'type'         => 'textarea',
			'label'        => __( 'Instructions', 'motopress-appointment' ),
			'description'  => __( 'Instructions for a customer on how to complete the payment.', 'motopress-appointment' ),
			'rows'         => 3,
			'size'         => 'large',
			'translatable' => true,
			'value'        => $this->getInstructions(),
		);

		return $fields;
	}

	protected function isTranslatableOption( string $unprefixedOptionName ): bool {
		return parent::isTranslatableOption( $unprefixedOptionName ) || 'instructions' === $unprefixedOptionName;
	}
}
