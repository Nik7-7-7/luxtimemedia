<?php
/**
 * Automatic.css Array Config Contents PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

/**
 * Config_Contents class.
 */
class Array_Config_Contents extends Config_Contents {

	/**
	 * Constructor.
	 *
	 * @param string $filename The config file's filename, relative to the config/ directory.
	 * @param array  $contents The config file's contents.
	 */
	public function __construct( $filename, $contents ) {
		parent::__construct( $filename );
		$this->contents = apply_filters( "acss/config/{$this->filename}", $contents );
	}

	/**
	 * Load and store the config file's content
	 *
	 * @return array
	 */
	public function load() {
		return $this->contents;
	}

}
