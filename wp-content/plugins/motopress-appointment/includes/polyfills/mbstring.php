<?php

if ( ! function_exists( 'mb_convert_encoding' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 1.1.0
	 */
	function mb_convert_encoding( $s, $toEncoding, $fromEncoding = null ) {

		if ( is_array( $fromEncoding ) || strpos( $fromEncoding, ',' ) !== false ) {
			$fromEncoding = mb_detect_encoding( $s, $fromEncoding );
		} else {
			$fromEncoding = mb_validate_encoding( $fromEncoding );
		}

		$toEncoding = mb_validate_encoding( $toEncoding );

		if ( 'BASE64' == $fromEncoding ) {
			$s            = base64_decode( $s );
			$fromEncoding = $toEncoding;
		}

		if ( 'BASE64' == $toEncoding ) {
			return base64_encode( $s );
		}

		if ( 'HTML-ENTITIES' == $toEncoding || 'HTML' == $toEncoding ) {
			if ( 'HTML-ENTITIES' == $fromEncoding || 'HTML' == $fromEncoding ) {
				$fromEncoding = 'Windows-1252';
			}

			if ( 'UTF-8' != $fromEncoding ) {
				$s = iconv( $fromEncoding, 'UTF-8//IGNORE', $s );
			}

			return preg_replace_callback( '/[\x80-\xFF]+/', 'mb_convert_encoding_callback', $s );
		}

		if ( 'HTML-ENTITIES' == $fromEncoding ) {
			$s            = html_entity_decode( $s, ENT_COMPAT, 'UTF-8' );
			$fromEncoding = 'UTF-8';
		}

		return iconv( $fromEncoding, $toEncoding . '//IGNORE', $s );
	}
}

if ( ! function_exists( 'mb_convert_encoding_callback' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 1.1.0
	 */
	function mb_convert_encoding_callback( $m ) {

		$i        = 1;
		$entities = '';
		$m        = unpack( 'C*', htmlentities( $m[0], ENT_COMPAT, 'UTF-8' ) );

		while ( isset( $m[ $i ] ) ) {
			if ( $m[ $i ] < 0x80 ) {
				$entities .= chr( $m[ $i++ ] );
				continue;
			}

			if ( $m[ $i ] >= 0xF0 ) {
				$c = ( ( $m[ $i++ ] - 0xF0 ) << 18 ) + ( ( $m[ $i++ ] - 0x80 ) << 12 ) + ( ( $m[ $i++ ] - 0x80 ) << 6 ) + $m[ $i++ ] - 0x80;
			} elseif ( $m[ $i ] >= 0xE0 ) {
				$c = ( ( $m[ $i++ ] - 0xE0 ) << 12 ) + ( ( $m[ $i++ ] - 0x80 ) << 6 ) + $m[ $i++ ] - 0x80;
			} else {
				$c = ( ( $m[ $i++ ] - 0xC0 ) << 6 ) + $m[ $i++ ] - 0x80;
			}

			$entities .= '&#' . $c . ';';
		}

		return $entities;
	}
}

if ( ! function_exists( 'mb_detect_encoding' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 1.1.0
	 */
	function mb_detect_encoding( $s, $encodings = null, $strict = false ) {

		if ( is_null( $encodings ) ) {
			$encodings = array( 'ASCII', 'UTF-8' );
		} else {
			if ( ! is_array( $encodings ) ) {
				$encodings = array_map( 'trim', explode( ',', $encodings ) );
			}

			$encodings = array_map( 'strtoupper', $encodings );
		}

		foreach ( $encodings as $encoding ) {
			switch ( $encoding ) {
				case 'ASCII':
					if ( ! preg_match( '/[\x80-\xFF]/', $s ) ) {
						return $encoding;
					}
					break;

				case 'UTF8':
				case 'UTF-8':
					if ( preg_match( '//u', $s ) ) {
						return 'UTF-8';
					}
					break;

				default:
					if ( strncmp( $encoding, 'ISO-8859-', 9 ) == 0 ) {
						return $encoding;
					}
					break;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'mb_validate_encoding' ) ) {
	/**
	 * @link https://github.com/symfony/polyfill-mbstring
	 *
	 * @since 1.1.0
	 */
	function mb_validate_encoding( $encoding ) {

		if ( is_null( $encoding ) ) {
			return 'UTF-8';
		}

		$encoding = strtoupper( $encoding );

		if ( '8BIT' == $encoding || 'BINARY' == $encoding ) {
			$encoding = 'CP850';
		} elseif ( 'UTF8' == $encoding ) {
			$encoding = 'UTF-8';
		}

		return $encoding;
	}
}
