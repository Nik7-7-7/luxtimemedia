<?php
/**
 * Automatic.css Expansion config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

/**
 * Automatic.css Expansion config class.
 */
class Expansion extends Base {

	/**
	 * Constructor.
	 *
	 * @param string $expansion The name of the expansion to load (which corresponds to a .json file with the same name).
	 */
	public function __construct( $expansion ) {
		parent::__construct( 'utility-expansions/' . $expansion . '.json' );
	}

}
