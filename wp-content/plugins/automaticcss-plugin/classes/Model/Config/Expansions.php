<?php
/**
 * Automatic.css Expansions config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

use Automatic_CSS\Exceptions\Missing_ExpandsTo_Key;
use Automatic_CSS\Helpers\Logger;

/**
 * Automatic.css Expansions config class.
 */
final class Expansions {

	/**
	 * Stores the config file
	 *
	 * @var mixed
	 */
	private $config;

	/**
	 * Cache the expansions for repeat calls
	 *
	 * @var array
	 */
	private $expansions;

	/**
	 * Constructor.
	 *
	 * @param Config_Contents $config_dir_or_contents The config directory or the config contents.
	 */
	public function __construct( Config_Contents $config_dir_or_contents = null ) {
		$this->config = $config_dir_or_contents ?? new Config_Contents( 'utility-expansions/all-expansions.json' );
	}

	/**
	 * Load the 'tabs' item from the ui.json file.
	 *
	 * @return array
	 * @throws \Exception If it can't load the file or it doesn't have the right structure.
	 * @throws \Automatic_CSS\Exceptions\NoExpansionsDefined If the expansions file is empty.
	 * @throws \Automatic_CSS\Exceptions\Missing_ExpandsTo_Key If the expansion file is missing the 'expandTo' key.
	 */
	public function load() {
		// STEP: load the file and check if it has the right structure.
		$contents = $this->config->load(); // contents stored in $contents.
		if ( ! is_array( $contents['expansions'] ) || empty( $contents['expansions'] ) ) {
			throw new \Automatic_CSS\Exceptions\NoExpansionsDefined( 'The Expansions config file has an empty or non-array "expansions" key.' );
		}
		// STEP: iterate the expansions and load their content.
		Logger::log( sprintf( '%s: loading expansions', __METHOD__ ), Logger::LOG_LEVEL_NOTICE );
		$contents['content'] = array();
		foreach ( $contents['expansions'] as $expansion ) {
			Logger::log( sprintf( '%s: loading expansion %s', __METHOD__, $expansion ), Logger::LOG_LEVEL_NOTICE );
			$expansion_json = ( new Expansion( $expansion ) )->load();
			if ( ! is_array( $expansion_json ) || empty( $expansion_json ) ) {
				throw new \Exception( sprintf( 'The Expansion config file "%s" has an empty or non-array "content" key.', $expansion ) );
			}
			if ( ! isset( $expansion_json['expansions'] ) ) {
				throw new \Exception( sprintf( 'The Expansion config file "%s" is missing the "expansions" key.', $expansion ) );
			}
			foreach ( $expansion_json['expansions'] as $expansion_name => $expansion_data ) {
				if ( ! is_array( $expansion_data ) || empty( $expansion_data ) ) {
					throw new Missing_ExpandsTo_Key( sprintf( 'The Expansion config file "%s" has an empty or non-array "expansions" key.', $expansion_name ) );
				}
				if ( ! isset( $expansion_data['expandTo'] ) ) {
					throw new \Exception( sprintf( 'The Expansion config file "%s" is missing the "expandTo" key.', $expansion_name ) );
				}
				$contents['content'][ $expansion_name ] = $expansion_data['expandTo'];
			}
		}
		Logger::log( sprintf( '%s: expansion contents: %s', __METHOD__, print_r( $contents, true ) ), Logger::LOG_LEVEL_INFO );
		$contents = apply_filters( "acss/config/{$this->config->get_filename()}/after_load", $contents );
		return $contents['content'];
	}

	/**
	 * Get all the expansions.
	 *
	 * @return array
	 */
	public function get_all_expansions() {
		if ( ! isset( $this->expansions ) ) {
			$this->expansions = $this->load();
		}
		return $this->expansions ?? array();
	}

}
