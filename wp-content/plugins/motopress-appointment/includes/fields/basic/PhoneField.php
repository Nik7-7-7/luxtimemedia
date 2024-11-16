<?php

namespace MotoPress\Appointment\Fields\Basic;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uses https://github.com/jackocnr/intl-tel-input
 */
class PhoneField extends TextField {

	const TYPE = 'phone';

	/**
	 * Validation uses: https://github.com/giggsey/libphonenumber-for-php
	 *
	 * @param string $value
	 *
	 * @return string
	 * @throws \Exception when value is invalid
	 */
	protected function validateValue( $value ) {

		$phoneNumber = trim( $value );

		if ( empty( $value ) ) {

			if ( empty( $this->default ) ) {
				return '';
			}

			$phoneNumber = trim( $this->default );
		}

		$phoneUtil = PhoneNumberUtil::getInstance();

		$parsedPhone = $phoneUtil->parse( $phoneNumber );

		if ( ! $phoneUtil->isValidNumber( $parsedPhone ) ) {
			throw new \Exception(
				sprintf(
				// translators: %s is phone number string
					__( 'Phone number %s is invalid.', 'motopress-appointment' ),
					$parsedPhone
				)
			);
		}

		return $phoneUtil->format( $parsedPhone, PhoneNumberFormat::E164 );
	}

	/**
	 * @return array
	 *
	 * @since 1.1.0
	 */
	protected function inputAtts() {
		return array_merge(
			parent::inputAtts(),
			array(
				'type' => 'tel',
			)
		);
	}
}
