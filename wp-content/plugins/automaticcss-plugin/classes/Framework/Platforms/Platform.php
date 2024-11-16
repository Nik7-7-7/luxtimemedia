<?php
/**
 * Automatic.css page builder interface file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework\Platforms;

/**
 * Automatic.css page builder interface.
 */
interface Platform {

	/**
	 * Check if the page builder is active.
	 *
	 * @return boolean
	 */
	public static function is_active();

}
