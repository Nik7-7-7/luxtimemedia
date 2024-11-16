<?php

namespace MotoPress\Appointment\Fields\Basic;

use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uses https://github.com/giggsey/libphonenumber-for-php
 *
 * @since 1.22.0
 */
class MultiplePhoneField extends TextField {

	const TYPE = 'multiple-phone';

	/**
	 * @param string $value
	 *
	 * @return string
	 * @throws \Exception when value is invalid
	 */
	protected function validateValue( $value ) {

		if ( empty( $value ) ) {
			return '';
		}

		$phoneUtil = PhoneNumberUtil::getInstance();

		$phoneNumbers = explode( ',', $value );

		$validPhoneNumbers = array();

		foreach ( $phoneNumbers as $phoneNumber ) {
			$parsedPhone = $phoneUtil->parse( trim( $phoneNumber ) );

			if ( ! $phoneUtil->isValidNumber( $parsedPhone ) ) {
				throw new \Exception(
					sprintf(
					// translators: %s is phone number string
						__( 'Phone number %s is invalid.', 'motopress-appointment' ),
						$phoneNumber
					)
				);
			}

			$validPhoneNumbers[] = $phoneUtil->format( $parsedPhone, PhoneNumberFormat::E164 );
		}

		return implode( ', ', $validPhoneNumbers );
	}
}