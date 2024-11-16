<?php
/**
 * Automatic.css Invalid Form_Values exception file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Exceptions;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;

/**
 * Invalid Form_Values exception class.
 */
class Invalid_Form_Values extends \Exception {

	/**
	 * To save multiple errors at once.
	 *
	 * @var array
	 */
	private $errors;

	/**
	 * Constructor.
	 *
	 * @param string $error_message Error message.
	 * @param array  $errors Errors array.
	 */
	public function __construct( $error_message, $errors = array() ) {
		parent::__construct( $error_message );
		$this->errors = $errors;
	}

	/**
	 * Get errors
	 *
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}
}
