<?php
/**
 * Automatic.css UI_Screen config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Plugin;

/**
 * Automatic.css UI_Screen config class.
 */
class UI_Screen extends Base {

	/**
	 * Constructor.
	 *
	 * @param string $screen The name of the screen to load (which corresponds to a .json file with the same name).
	 */
	public function __construct( $screen ) {
		parent::__construct( 'ui/' . $screen . '.json' );
	}

}
