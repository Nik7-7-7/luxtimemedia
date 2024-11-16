<?php
/**
 * Automatic.css Color helper file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Helpers;

/**
 * Automatic.css Color helper class.
 */
class Color {

	/**
	 * The rgb color
	 *
	 * @var \Automatic_CSS\PHPColors\Color
	 */
	private $phpcolors;

	/**
	 * Undocumented function
	 *
	 * @param string $color The rgb color.
	 */
	public function __construct( $color ) {
		$this->phpcolors = new \Automatic_CSS\PHPColors\Color( $color );
	}

	/**
	 * Getter function
	 *
	 * @param string $key The key.
	 * @return mixed
	 */
	public function __get( $key ) {
		$allowed_keys = array( 'h', 's', 'l', 's_perc', 'l_perc', 'hex', 'r', 'g', 'b' );
		if ( ! in_array( $key, $allowed_keys ) ) {
			return null;
		}
		$hex = $this->phpcolors->getHex();
		$hsl = $this->phpcolors->getHsl();
		$rgb = $this->phpcolors->getRgb();
		switch ( $key ) {
			case 'hex':
				return '#' . $hex;
			case 'h':
				return round( $hsl['H'] );
			case 's':
				return round( $hsl['S'] * 100 );
			case 's_perc':
				return round( $hsl['S'] * 100 ) . '%';
			case 'l':
				return round( $hsl['L'] * 100 );
			case 'l_perc':
				return round( $hsl['L'] * 100 ) . '%';
			case 'r':
				return round( $rgb['R'] );
			case 'g':
				return round( $rgb['G'] );
			case 'b':
				return round( $rgb['B'] );
		}
	}

}
