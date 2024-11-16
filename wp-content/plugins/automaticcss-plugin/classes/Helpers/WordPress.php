<?php
/**
 * Automatic.css WordPress helper file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\Helpers;

use Automatic_CSS\CSS_Engine\CSS_File;
use Automatic_CSS\Helpers\Logger;

/**
 * Automatic.css WordPress helper class
 */
class WordPress {
	/**
	 * A helper function to enqueue a stylesheet.
	 *
	 * @param string $handle The stylesheet handle.
	 * @param array  $options The options (items: 'filename', 'url', 'dependency', optional 'filename-min', 'url-min').
	 * @return void
	 */
	public static function enqueue_stylesheet_helper( $handle, $options ) {
		// 20211115 - MG - by default enqueue the non minified version.
		// Only enqueue the minified version if SCRIPT_DEBUG is set.
		$url = $options['url'];
		$filename = realpath( $options['filename'] );
		$dependency = isset( $options['dependency'] ) ? $options['dependency'] : array();
		if ( array_key_exists( 'filename-min', $options ) && array_key_exists( 'url-min', $options ) && ( ! defined( 'SCRIPT_DEBUG' ) || true !== (bool) SCRIPT_DEBUG ) ) {
			$url = $options['url-min'];
			$filename = realpath( $options['filename-min'] );
		}
		// 20211125 - MG - enqueue the file only if it exists. Log an error otherwise.
		if ( ! file_exists( $filename ) ) {
			Logger::log(
				sprintf(
					'%s: could not enqueue the following stylesheet because the file does not exist: %s',
					__METHOD__,
					$filename
				),
				true
			);
			return;
		}
		wp_enqueue_style(
			$handle,
			$url,
			$dependency,
			strval( filemtime( $filename ) ),
			'all'
		);
	}
}
