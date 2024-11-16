<?php
/**
 * Automatic.css Singleton class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Traits;

use Automatic_CSS\Helpers\Logger;

trait Disableable {

	/**
	 * Is this thing enabled or disabled
	 *
	 * @var bool
	 */
	private $is_enabled;

	/**
	 * Enable this thing
	 *
	 * @return void
	 */
	public function enable() {
		$this->is_enabled = true;
	}

	/**
	 * Disable this thing
	 *
	 * @return void
	 */
	public function disable() {
		$this->is_enabled = false;
	}

	/**
	 * Enable or disable this thing based on the provided parameter
	 *
	 * @param boolean $is_enabled Enable or disable.
	 * @return void
	 */
	public function set_enabled( bool $is_enabled ) {
		$this->is_enabled = $is_enabled;
	}

	/**
	 * Get the enabled status
	 *
	 * @return boolean
	 */
	public function is_enabled() {
		return $this->is_enabled;
	}

}
