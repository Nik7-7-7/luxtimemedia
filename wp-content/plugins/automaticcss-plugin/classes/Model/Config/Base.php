<?php
/**
 * Automatic.css config PHP file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Model\Config;

/**
 * Automatic.css config class.
 */
abstract class Base {

	/**
	 * Stores the config file's filename
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 * Stores the config file's contents
	 *
	 * @var mixed
	 */
	protected $contents;

	/**
	 * Stores the config directory
	 *
	 * @var string
	 */
	protected $config_dir;

	/**
	 * Constructor.
	 *
	 * @param string $filename The config file's filename, relative to the config/ directory.
	 * @param string $config_dir The config directory.
	 */
	public function __construct( $filename, $config_dir = ACSS_CONFIG_DIR ) {
		$this->filename = $filename;
		$this->config_dir = $config_dir;
	}

	/**
	 * Load and store the config file's content
	 *
	 * @return array
	 * @throws \Exception If the file does not exist.
	 */
	public function load() {
		if ( ! isset( $this->contents ) ) {
			$file = $this->config_dir . '/' . $this->filename;
			if ( ! file_exists( $file ) ) {
				throw new \Exception(
					sprintf(
						'%s: could not find file %s',
						__METHOD__,
						$file
					)
				);
			}
			$json = json_decode( file_get_contents( $file ), true );
			// @since 2.3.0 - allow programmatically adding config items.
			$this->contents = apply_filters( "acss/config/{$this->filename}", $json );
		}
		return $this->contents;
	}

}
