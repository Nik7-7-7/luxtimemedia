<?php

namespace MotoPress\Appointment\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.1.0
 */
class ColorUtils {

	/**
	 * Detect which color shoud we use with the base color - dark or light.
	 *
	 * @param string $baseColor
	 * @param string $dark Optional. '000000' by default.
	 * @param string $light Optional. 'FFFFFF' by default.
	 * @return string $dark or $light.
	 *
	 * @since 1.1.0
	 */
	public static function darkOrLight( $baseColor, $dark = '#000000', $light = '#FFFFFF' ) {
		list($red, $green, $blue) = static::hexToRgb( $baseColor );

		$brightness = ( $red * 299 + $green * 587 + $blue * 114 ) / 1000;

		return $brightness > 155 ? $dark : $light;
	}

	/**
	 * @param string $color
	 * @param int $factor
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public static function hexDarker( $color, $factor ) {
		$rgb = static::hexToRgb( $color );

		foreach ( $rgb as &$component ) {
			$diff = $component / 100 * $factor;
			$diff = round( $diff );

			$component -= $diff;
		}

		unset( $component );

		return static::rgbToHex( $rgb );
	}

	/**
	 * @param string $color
	 * @param int $factor
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public static function hexLighter( $color, $factor ) {
		$rgb = static::hexToRgb( $color );

		foreach ( $rgb as &$component ) {
			$diff = ( 255 - $component ) / 100 * $factor;
			$diff = round( $diff );

			$component += $diff;
		}

		unset( $component );

		return static::rgbToHex( $rgb );
	}

	/**
	 * @param string $hex
	 * @return int[]
	 *
	 * @since 1.1.0
	 */
	public static function hexToRgb( $hex ) {
		$color = str_replace( '#', '', $hex );

		// Convert shorthand colors to full format, e.g. 'FFF' -> 'FFFFFF'
		$color = preg_replace( '/^(.)(.)(.)$/', '$1$1$2$2$3$3', $color );

		$rgb = array(
			hexdec( substr( $color, 0, 2 ) ),
			hexdec( substr( $color, 2, 2 ) ),
			hexdec( substr( $color, 4, 2 ) ),
		);

		return $rgb;
	}

	/**
	 * @param int[] $rgb
	 * @return string
	 *
	 * @since 1.1.0
	 */
	public static function rgbToHex( $rgb ) {
		$hex = '#';

		foreach ( $rgb as $component ) {
			$hexComponent = dechex( $component );

			if ( strlen( $hexComponent ) < 2 ) {
				$hexComponent = '0' . $hexComponent;
			}

			$hex .= $hexComponent;
		}

		return $hex;
	}
}
