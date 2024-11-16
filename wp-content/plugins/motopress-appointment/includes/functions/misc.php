<?php

use MotoPress\Appointment\Entities\Booking;
use MotoPress\Appointment\Fields\Basic\GroupField;
use MotoPress\Appointment\Fields\AbstractField;
use MotoPress\Appointment\Fields\FieldsFactory;
use MotoPress\Appointment\Services\PaymentManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param mixed $value Optional. '' by default.
 * @param string $label Optional. '— Any —' by default.
 * @return array
 *
 * @since 1.0
 */
function mpa_any_value( $value = '', $label = '' ) {
	if ( empty( $label ) ) {
		return array( $value => esc_html__( '— Any —', 'motopress-appointment' ) );
	} else {
		return array( $value => $label );
	}
}

/**
 * @param mixed $value Optional. '' by default.
 * @param string $label Optional. '— Select —' by default.
 * @return array
 *
 * @since 1.0
 */
function mpa_no_value( $value = '', $label = '' ) {
	if ( empty( $label ) ) {
		return array( $value => esc_html__( '— Select —', 'motopress-appointment' ) );
	} else {
		return array( $value => $label );
	}
}

/**
 * @param string $countryLabel Country label like 'Germany' or 'United States (US)'.
 * @return string
 *
 * @since 1.0
 */
function mpa_country_code( $countryLabel ) {
	return mpapp()->bundles()->countries()->getCode( $countryLabel );
}

/**
 * @param string $countryCode Country code like 'US'.
 * @return string
 *
 * @since 1.0
 */
function mpa_country_label( $countryCode ) {
	return mpapp()->bundles()->countries()->getLabel( $countryCode );
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 *
 * @author MrHus
 * @link http://stackoverflow.com/a/834355/3918377
 *
 * @since 1.0
 */
function mpa_str_starts_with( $haystack, $needle ) {
	$length = strlen( $needle );
	return substr( $haystack, 0, $length ) === $needle;
}

/**
 * @param string $polyfill Polyfill name, like 'mbstring'.
 *
 * @since 1.1.0
 */
function mpa_load_polyfill( $polyfill ) {
	require mpa_path_to( "includes/polyfills/{$polyfill}.php" );
}

/**
 * @return MotoPress\Appointment\Emails\MailerInterface
 *
 * @since 1.1.0
 */
function mpa_mailer() {
	$mailer = new MotoPress\Appointment\Emails\Mailer();
	$mailer = apply_filters( 'mpa_mailer', $mailer );

	return $mailer;
}

/**
 * Usage:
 * <ul>
 *     <li>1 argument (only fields, default usage): <code>mpa_create_fields($fields);</code>
 *         (get values from wp_options table)</li>
 *     <li>2 arguments - fields and prefix:
 *         <code>mpa_create_fields($fields, 'metabox'); // $type = 'postmeta', $id = get_the_ID()</code></li>
 *     <li>3 arguments - use default type, but custom ID:
 *         <code>mpa_create_fields($fields, 'metabox', $postId); // $type = 'postmeta', $id = $postId</code></li>
 *     <li>3 arguments:
 *         <code>mpa_create_fields($fields, $prefix, $type); // $id = get_the_ID()</code></li>
 *     <li>4 arguments: <code>mpa_create_fields($fields, $prefix, $type, $id);</code></li>
 * </ul>
 *
 * @param array $fields Array of args.
 * @param string $prefix Optional. See allowed prefixes in mpa_prefix().
 *     'public' by default.
 * @param 'option'|'postmeta'|'widget' $type Optional. The type of the source.
 *     'option' by default.
 * @param int $id Optional. ID of the post. Only for metaboxes (postmetas).
 *     Current post ID by default.
 * @return AbstractField[]
 *
 * @since 1.1.0
 * @since 1.3 added the <code>prefix</code> argument.
 * @since 1.3 added the <code>type</code> argument.
 * @since 1.3 added the <code>id</code> argument.
 */
function mpa_create_fields( $fields, $prefix = 'public', $type = 'option', $id = 0 ) {
	// Set proper values to arguments
	switch ( func_num_args() ) {
		// Use default values for 1
		case 3: // $fields + $prefix + $id or custom $type
			if ( ! is_numeric( $type ) ) {
				break; // Custom type set

			} else {
				// Third argument is ID
				$id   = mpa_posint( $type );
				$type = 'option'; // Reset the type

				// Continue to "case 2:" and set proper pair of $prefix/$type
			}

		case 2: // $fields + $prefix
			if ( 'metabox' == $prefix ) {
				$type = 'postmeta';
			} elseif ( 'widget' == $prefix ) {
				$type = 'widget';
			}
			break;
	}

	if ( ! $id ) {
		$id = (int) get_the_ID(); // False on Add New post page
	}

	// Create fields
	$instances = array();

	foreach ( $fields as $name => $args ) {
		$inputName = mpa_prefix( $name, $prefix );

		// Get value
		$value = null; // Default starting value in FieldsFactory (no value)

		switch ( $type ) {
			case 'option':
				$value = get_option( $inputName, $value );
				break;

			case 'postmeta':
				if ( ! $id ) {
					break;
				}

				$metaValues = get_post_meta( $id, $inputName ); // Yes, not single

				if ( 1 == count( $metaValues ) ) {
					$value = reset( $metaValues );
				} elseif ( count( $metaValues ) > 1 ) {
					$value = $metaValues;
				}

				break;

			case 'widget':
				// Do nothing, widgets will pass values throught the args. See
				// AbstractWidget::form() for details
				break;
		}

		// Create field
		$field = FieldsFactory::createField( $inputName, $args, $value );

		$instances[ $inputName ] = $field;
	}

	return $instances;
}

/**
 * @param array $fields [Field name => args or AbstractField]
 * @return array [Group name => Group fields]
 *
 * @since 1.1.0
 */
function mpa_group_fields( $fields ) {
	if ( empty( $fields ) ) {
		return array();
	}

	$groups = array();

	$currentGroup = array();
	$groupName    = 'basic_group';

	foreach ( $fields as $name => $field ) {

		if ( is_array( $field ) ) {
			$isNewGroup = 'field' == $field['type'];
		} else {
			$isNewGroup = $field instanceof GroupField;
		}

		// Save previous group if new group found
		if ( $isNewGroup ) {
			if ( ! empty( $currentGroup ) ) {
				$groups[ $groupName ] = $currentGroup;
			}

			$currentGroup = array();
			$groupName    = mpa_unprefix( $name );
		}

		$currentGroup[ $name ] = $field;
	}

	// Save last group
	if ( ! empty( $currentGroup ) ) {
		$groups[ $groupName ] = $currentGroup;
	}

	return $groups;
}

/**
 * Works properly only when all values (with indexes 0-6) are present in the
 * array.
 *
 * @param array $daysArray
 * @param int $firstDay Optional. -1 by default (use settings value).
 * @return array
 *
 * @since 1.2
 */
function mpa_shift_days_array( $daysArray, $firstDay = -1 ) {

	if ( -1 == $firstDay ) {
		$firstDay = mpapp()->settings()->getFirstDayOfWeek();
	}

	if ( $firstDay > 0 ) {
		$startPart = array_slice( $daysArray, $firstDay, 7 - $firstDay, true );
		$endPart   = array_slice( $daysArray, 0, $firstDay, true );

		return array_replace( $startPart, $endPart );

	} else {
		return $daysArray;
	}
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 *
 * @since 1.2
 */
function mpa_str_ends_with( $haystack, $needle ) {

	$length = strlen( $needle );

	if ( 0 == $length ) {
		return true; // Notice: the length of $needle is 0, not the length of $haystack
	} else {
		$ending = substr( $haystack, -$length );
		return ( $ending === $needle );
	}
}

/**
 * @param callable $function
 * @param mixed $carry Value of the last argument.
 * @return Function without the last argument.
 *
 * @since 1.2.1
 */
function mpa_carry( $function, $carry ) {
	return function ( ...$args ) use ( $function, $carry ) {
		$args[] = $carry; // Cannot use positional argument after argument unpacking
		return $function( ...$args );
	};
}

/**
 * @param callable $function
 * @param mixed $carry Value of the first argument.
 * @return Function without the first argument.
 *
 * @since 1.2.1
 */
function mpa_carry_shift( $function, $carry ) {
	return function ( ...$args ) use ( $function, $carry ) {
		return $function( $carry, ...$args );
	};
}

/**
 * @since 1.4.0
 *
 * @param int $limit The maximum execution time, in seconds. If set to zero, no
 *      time limit is imposed.
 */
function mpa_set_time_limit( $limit ) {
	if ( function_exists( 'set_time_limit' )
		&& ! mpa_is_function_disabled( 'set_time_limit' )
		&& ! ini_get( 'safe_mode' )
	) {
		@set_time_limit( $limit );
	}
}

/**
 * Source: http://php.net/manual/ru/function.uniqid.php#94959
 *
 * @since 1.5.0
 *
 * @return string Unique identifier like '550e8400-e29b-41d4-a716-446655440000'.
 */
function mpa_generate_uuid4() {
	$uuid4 = sprintf(
		'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0x0fff ) | 0x4000,
		mt_rand( 0, 0x3fff ) | 0x8000,
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff ),
		mt_rand( 0, 0xffff )
	);

	return $uuid4;
}

/**
 * Adds UID meta field only if it does not exist.
 *
 * @since 1.5.0
 *
 * @param int $postId
 * @param string $uid Optional. New UUID v4 by default.
 * @return int|false Meta ID on success, false on failure.
 */
function mpa_add_post_uid( $postId, $uid = '' ) {
	if ( ! $uid ) {
		$uid = mpa_generate_uuid4();
	}

	return add_post_meta( $postId, '_mpa_uid', $uid, true );
}

/**
 * @since 1.5.0
 *
 * @return PaymentManager
 */
function mpa_payment_manager() {
	return new PaymentManager();
}

/**
 * @since 1.5.0
 *
 * @param Booking|int $booking Booking entity or ID.
 * @return string
 */
function mpa_generate_product_name( $booking ) {
	$bookingId = is_object( $booking ) ? $booking->getId() : $booking;

	if ( $bookingId > 0 ) {
		// Translators: %d: Booking ID.
		$productName = sprintf( esc_html_x( 'Booking #%d', 'Product name for payment gateway', 'motopress-appointment' ), $bookingId );
	} else {
		$productName = esc_html_x( 'Service booking', 'Product name for payment gateway (no booking ID)', 'motopress-appointment' );
	}

	/**
	 * @since 1.5.0
	 *
	 * @param string $productName
	 * @param Booking|int $booking
	 */
	$productName = apply_filters( 'mpa_generate_product_name', $productName, $booking );

	return $productName;
}


/**
 * @param array $selectors Associative array. Keys - CSS selectors. Values - array of pairs CSS prop => variable name.
 * @param array $variables Associative array. Keys - variable names. Values - CSS prop values.
 * @return string Valid CSS string.
 */
function mpa_generate_css( $selectors, $variables ) {

	$css = '';

	foreach ( $selectors as $class => $properties ) {

		$filledProperties = array();
		foreach ( $properties as $property => $variable ) {
			if ( ! empty( $variables[ $variable ] ) ) {
				$filledProperties[] = $property . ': ' . $variables[ $variable ];
			}
		}

		if ( $filledProperties ) {
			$css .= " {$class}{" . implode( '; ', $filledProperties ) . "}";
		}

	}

	return $css;
}