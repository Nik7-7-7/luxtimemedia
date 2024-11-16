<?php

namespace MotoPress\Appointment\Metaboxes\Booking;

use MotoPress\Appointment\Metaboxes\FieldsMetabox;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class BookingCustomerMetabox extends FieldsMetabox {

	protected $customerId = null;

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	protected function theName(): string {
		return 'booking_customer_metabox';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function theFields() {
		return array(
			'customer_id'    => [
				'type' => 'hidden',
			],
			'customer_name'  => array(
				'type'  => 'text',
				'label' => esc_html__( 'Name', 'motopress-appointment' ),
				'size'  => 'regular',
			),
			'customer_email' => array(
				'type'  => 'text',
				'label' => esc_html__( 'Email', 'motopress-appointment' ),
				'size'  => 'regular',
			),
			'customer_phone' => array(
				'type'  => 'phone',
				'label' => esc_html__( 'Phone', 'motopress-appointment' ),
				'size'  => 'regular',
			),
			'customer_notes' => array(
				'type'         => 'textarea',
				'label'        => esc_html__( 'Booking notes', 'motopress-appointment' ),
				'rows'         => 5,
				'default'      => '',
				'translatable' => false,
				'size'         => 'large',
			),
		);
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getLabel(): string {
		return esc_html__( 'Customer Info', 'motopress-appointment' );
	}

	/**
	 * @param int $postId id or 0 for unsetted id
	 *
	 * @return int
	 * @since 1.18.0
	 */
	protected function getCustomerId( int $postId ): int {
		if ( ! is_null( $this->customerId ) ) {
			return $this->customerId;
		}

		$customerId = parent::loadValue( $postId, '_mpa_customer_id' );

		if ( is_null( $customerId ) ) {
			$this->customerId = 0;
		} else {
			$this->customerId = absint( $customerId );
		}

		return $this->customerId;
	}

	/**
	 * Loading a value from the database.
	 * If the value is in postmeta, load the value from postmeta.
	 * If the value is not in postmeta and the value must be stored in a custom database table,
	 * then we load the value from the custom table.
	 *
	 * @param int $postId
	 * @param string $metaName
	 *
	 * @return string|null
	 * @since 1.18.0
	 */
	protected function loadValue( int $postId, string $metaName ) {

		$value = parent::loadValue( $postId, $metaName );

		if ( $value ) {
			return $value;
		}

		$customerId = $this->getCustomerId( $postId );

		if ( ! $customerId ) {
			return $value;
		}

		$customer = mpapp()->repositories()->customer()->findById( $customerId );

		if ( ! $customer ) {
			return $value;
		}

		switch ( $metaName ) {
			case '_mpa_customer_name' :
				return $customer->getName();
			case '_mpa_customer_email' :
				return $customer->getEmail();
			case '_mpa_customer_phone' :
				return $customer->getPhone();
		}

		return $value;
	}

	/**
	 * For existing customers matched by email or phone number, store the values in postmeta.
	 * If the saved information about the client did not match any of the clients,
	 * then we create a new client and save the information about the client to a custom database table.
	 *
	 * @param array $values [add, update, delete]
	 * @param int $postId
	 * @param \WP_Post $post
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.18.0
	 */
	protected function saveValues( array $values, int $postId, \WP_Post $post ) {
		$customerName  = $values['update']['_mpa_customer_name'] ?? '';
		$customerEmail = $values['update']['_mpa_customer_email'] ?? '';
		$customerPhone = $values['update']['_mpa_customer_phone'] ?? '';

		if ( ! $customerEmail && ! $customerPhone ) {
			parent::saveValues( $values, $postId, $post );

			return;
		}

		$customer = mpapp()->repositories()->customer()->findByEmail( $customerEmail );

		if ( ! $customer ) {
			$customer = mpapp()->repositories()->customer()->findByPhone( $customerPhone );
		}

		if ( ! $customer ) {
			$customerData = array(
				'name'  => $customerName,
				'email' => $customerEmail,
				'phone' => $customerPhone,
			);

			$customer = mpapp()->repositories()->customer()->mapDataToEntity( $customerData );
			mpapp()->repositories()->customer()->save( $customer );
		}

		$values['update']['_mpa_customer_id'] = $customer->getId();

		if ( $customerName == $customer->getName() ) {
			$values['update']['_mpa_customer_name'] = '';
			$values['delete']['_mpa_customer_name'] = array( '' );
		}

		if (  $customerEmail == $customer->getEmail() ) {
			$values['update']['_mpa_customer_email'] = '';
			$values['delete']['_mpa_customer_email'] = array( '' );
		}

		if ( $customerPhone == $customer->getPhone() ) {
			$values['update']['_mpa_customer_phone'] = '';
			$values['delete']['_mpa_customer_phone'] = array( '' );
		}

		parent::saveValues( $values, $postId, $post );
	}
}
