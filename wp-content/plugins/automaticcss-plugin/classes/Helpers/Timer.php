<?php
/**
 * Automatic.css Timer file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Helpers;

/**
 * Timer class.
 *
 * @see https://www.php.net/manual/en/function.microtime.php
 */
class Timer {

	/**
	 * Time when the timer was started
	 *
	 * @var float
	 */
	private $time_start;

	/**
	 * Time when the timer was stopped
	 *
	 * @var float|null
	 */
	private $time_stop;

	/**
	 * Is the timer running?
	 *
	 * @var bool
	 */
	private $is_running;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Initialize the timer
	 *
	 * @return void
	 */
	private function init() {
		$this->time_start = microtime( true );
		$this->time_stop = null;
		$this->is_running = true;
	}

	/**
	 * Start the timer
	 *
	 * @return void
	 */
	public function start() {
		$this->init();
	}

	/**
	 * Stop the timer
	 *
	 * @return void
	 */
	public function stop() {
		$this->time_stop = microtime( true );
		$this->is_running = false;
	}

	/**
	 * Get the timer
	 *
	 * @param int $precision Rounding precision.
	 * @return float
	 */
	public function get_time( $precision = 2 ) {
		if ( $this->is_running ) {
			$this->stop();
		}
		$time = $this->time_stop - $this->time_start;
		// now the integer section ($seconds) should be small enough to allow a float with 2 decimal digits.
		return round( ( $time ), $precision );
	}
}
