<?php
/**
 * Automatic.css variables config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

/**
 * Automatic.css variables config class.
 */
class Variables extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct( 'variables.json' );
	}

	/**
	 * Get the plugin's config/variables.json content and store it / return it
	 *
	 * @return array
	 * @throws \Exception If it can't load the file or it doesn't have the right structure.
	 */
	public function load() {
		parent::load(); // contents stored in $this->contents.
		if ( ! array_key_exists( 'variables', $this->contents ) ) {
			throw new \Exception(
				sprintf(
					'%s: there is no "variables" item in the configuration file',
					__METHOD__
				)
			);
		}
		return $this->contents['variables'];
	}

	/**
	 * Get the default VARS values from the variables.json config file.
	 *
	 * @return array
	 */
	public function load_defaults() {
		$vars = $this->load();
		$values = array();
		foreach ( $vars as $var => $options ) {
			if ( is_array( $options ) && array_key_exists( 'default', $options ) ) {
				$values[ $var ] = $options['default'];
			}
		}
		return $values;
	}

	/**
	 * Get the config options for the specified variable.
	 *
	 * @param string $var_id Variable ID.
	 * @return array|null
	 */
	public function get_variable_options( $var_id ) {
		$vars = $this->load();
		if ( ! array_key_exists( $var_id, $vars ) ) {
			// TODO: error message?
			return null;
		}
		return $vars[ $var_id ];
	}
}
