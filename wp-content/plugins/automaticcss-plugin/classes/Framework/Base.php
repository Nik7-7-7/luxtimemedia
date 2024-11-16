<?php
/**
 * Automatic.css Framework's Base file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Framework;

use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Helpers\Logger;

/**
 * Automatic.css Framework's Base class.
 */
abstract class Base {

	/**
	 * Instances of the CSS file
	 *
	 * @var array
	 */
	private $css_files = array();

	/**
	 * Add a CSS_File
	 *
	 * @param CSS_File $css_file CSS_File.
	 * @return CSS_File
	 */
	protected function add_css_file( CSS_File $css_file ) {
		$this->css_files[ $css_file->handle ] = $css_file;
		return $this->css_files[ $css_file->handle ];
	}

	/**
	 * Get a specific CSS_File
	 *
	 * @param string $handle CSS_File handle.
	 * @return CSS_File
	 */
	public function get_css_file( $handle ) {
		if ( isset( $this->css_files[ $handle ] ) ) {
			return $this->css_files[ $handle ];
		}
		return null;
	}

	/**
	 * Get all CSS_Files
	 *
	 * @return array
	 */
	public function get_css_files() {
		return $this->css_files;
	}

	/**
	 * Generate and save all registered stylesheets for the provided values.
	 *
	 * @param array $variables The values for the framework's variables.
	 * @return array The generated CSS files.
	 */
	public function generate_own_css_files( $variables ) {
		$generated_files = array();
		if ( is_array( $this->css_files ) && ! empty( $this->css_files ) ) {
			foreach ( $this->css_files as $css_file ) {
				if ( is_a( $css_file, 'Automatic_CSS\CSS_Engine\CSS_File' ) && $css_file->is_enabled() ) {
					$css_timer = new Timer();
					if ( false !== $css_file->save_file_from_variables( $variables ) ) {
						Logger::log( sprintf( '%s: generated %s in %s seconds', __METHOD__, $css_file->handle, $css_timer->get_time() ) );
						$generated_files[] = $css_file->handle;
					}
				}
			}
		}
		return $generated_files;
	}
}
