<?php
/**
 * Automatic.css CSS engine to generate a CSS file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\CSS_Engine;

use Automatic_CSS\Plugin;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\ScssPhp\Compiler;
use Automatic_CSS\ScssPhp\ValueConverter;
use Automatic_CSS\Traits\Disableable;
use InvalidArgumentException;
use WP_Styles;

/**
 * Automatic.css Framework's CSS_File class.
 */
class CSS_File {

	/**
	 * Allow CSS_Files to be disabled while running.
	 */
	use Disableable;

	/**
	 * File handle
	 *
	 * @var string
	 */
	private $handle;

	/**
	 * Filename
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Source SCSS file
	 *
	 * @var string
	 */
	private $source_scss;

	/**
	 * Source SCSS imports folder
	 *
	 * @var string
	 */
	private $source_scss_imports_folder;

	/**
	 * SCSS Options
	 *
	 * @var array $scss_options = [
	 *  'source_file' => '',
	 *  'imports_folder' => '',
	 * ]
	 */
	private $scss_options;

	/**
	 * WP_Styles enqueue options
	 *
	 * @var array $enqueue_options = [
	 *  'deps' => [],
	 *  'media' => 'all',
	 *  'queue' => wp_styles(),
	 * ]
	 */
	private $enqueue_options;

	/**
	 * Is this stylesheet registered?
	 *
	 * @var bool
	 */
	private $is_registered;

	/**
	 * Is this stylesheet enqueued?
	 *
	 * @var bool
	 */
	private $is_enqueued;

	/**
	 * Constructor
	 *
	 * @param string $handle The CSS file handle.
	 * @param string $css_filename The filename used to generate the CSS file.
	 * @param array  $scss_options The SCSS options.
	 * @param array  $enqueue_options The enqueue options.
	 * @param bool   $is_enabled  Is this CSS File enabled or disabled.
	 */
	public function __construct( $handle, $css_filename, $scss_options = array(), $enqueue_options = array(), bool $is_enabled = true ) {
		$this->handle = $handle;
		$this->filename = $css_filename;
		// SCSS options.
		$scss_prefix = ACSS_ASSETS_DIR . '/scss/';
		if ( is_array( $scss_options ) ) {
			$this->scss_options = $scss_options;
			if ( isset( $this->scss_options['source_file'] ) ) {
				$no_source_prefix = isset( $scss_options['no_source_prefix'] ) && $scss_options['no_source_prefix'];
				$this->scss_options['source_file'] = $no_source_prefix ? $scss_options['source_file'] : $scss_prefix . $scss_options['source_file'];
			}
			if ( isset( $this->scss_options['imports_folder'] ) ) {
				$no_import_prefix = isset( $scss_options['no_import_prefix'] ) && $scss_options['no_import_prefix'];
				$this->scss_options['imports_folder'] = $no_import_prefix ? $scss_options['imports_folder'] : $scss_prefix . $scss_options['imports_folder'];
			}
		} else if ( is_string( $scss_options ) ) {
			// $scss_options = source file; imports_folder = $scss_prefix.
			$this->scss_options['source_file'] = $scss_prefix . $scss_options;
			$this->scss_options['imports_folder'] = $scss_prefix;
		}
		// Enqueue options.
		$this->enqueue_options = $enqueue_options;
		if ( ! isset( $this->enqueue_options['deps'] ) || ! is_array( $this->enqueue_options['deps'] ) ) {
			$this->enqueue_options['deps'] = array();
		}
		if ( ! isset( $this->enqueue_options['media'] ) ) {
			$this->enqueue_options['media'] = 'all';
		}
		if ( ! isset( $this->enqueue_options['queue'] ) || ! is_a( $this->enqueue_options['queue'], '\WP_Styles' ) ) {
			$this->enqueue_options['queue'] = null; // will be set later when wp_styles() is available.
		}
		$this->set_enabled( $is_enabled );
	}

	/**
	 * Change the stylesheet's queue, if it was not registered yet.
	 *
	 * @param \WP_Styles $queue The new queue.
	 *
	 * @return WP_Styles|null
	 */
	public function set_queue( \WP_Styles $queue ) {
		if ( ! $this->is_enabled() ) {
			return;
		}
		Logger::log( sprintf( '%s: setting new queue for stylesheet %s', __METHOD__, $this->handle ) );
		if ( $this->is_registered ) {
			Logger::log( sprintf( '%s: trying to change queue of registered stylesheet %s', __METHOD__, $this->handle ) );
			$this->deregister_stylesheet();
		}
		$this->enqueue_options['queue'] = $queue;
		return $this->enqueue_options['queue'];
	}

	/**
	 * Set the default queue if it was not set yet.
	 *
	 * @return WP_Styles|null
	 */
	private function maybe_set_default_queue() {
		if ( ! isset( $this->enqueue_options['queue'] ) ) {
			Logger::log( sprintf( '%s: setting default queue for stylesheet %s', __METHOD__, $this->handle ) );
			return $this->set_queue( wp_styles() );
		}
	}

	/**
	 * Register this CSS file as a stylesheet in $enqueue_options['queue']
	 *
	 * @return bool
	 * @see https://developer.wordpress.org/reference/classes/wp_dependencies/add/
	 */
	public function register_stylesheet() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->maybe_set_default_queue();
		Logger::log( sprintf( '%s: registering stylesheet %s', __METHOD__, $this->handle ) );
		$is_file_exists_check_required = apply_filters( 'acss/css_engine/file_exists_check_required', true, $this->file_path, $this->file_url );
		if ( $is_file_exists_check_required && ! $this->file_exists() ) {
			Logger::log( sprintf( '%s: CSS file %s does not exist and cannot be registered', __METHOD__, $this->file_path ), Logger::LOG_LEVEL_ERROR );
			return false;
		}
		$ret = $this->enqueue_options['queue']->add(
			$this->handle,
			$this->file_url,
			$this->enqueue_options['deps'],
			filemtime( $this->file_path ),
			$this->enqueue_options['media']
		);
		$this->is_registered = $this->enqueue_options['queue']->query( $this->handle, 'registered' );
		return $ret;
	}

	/**
	 * Deregister this CSS file from $enqueue_options['queue']
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/classes/wp_dependencies/remove/
	 */
	public function deregister_stylesheet() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->maybe_set_default_queue();
		Logger::log( sprintf( '%s: deregistering stylesheet %s', __METHOD__, $this->handle ) );
		$this->enqueue_options['queue']->remove( $this->handle );
		$this->is_registered = $this->enqueue_options['queue']->query( $this->handle, 'registered' );
	}

	/**
	 * Enqueue this CSS file
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/classes/wp_dependencies/enqueue/
	 */
	public function enqueue_stylesheet() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		if ( $this->is_file_empty() ) {
			return;
		}
		$this->maybe_set_default_queue();
		if ( ! $this->is_registered ) {
			$this->register_stylesheet();
		}
		Logger::log( sprintf( '%s: enqueuing stylesheet %s', __METHOD__, $this->handle ) );
		$this->enqueue_options['queue']->enqueue( $this->handle );
		$this->is_enqueued = $this->enqueue_options['queue']->query( $this->handle, 'enqueued' );
	}

	/**
	 * Dequeue this CSS file
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/classes/wp_dependencies/dequeue/
	 */
	public function dequeue_stylesheet() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->maybe_set_default_queue();
		Logger::log( sprintf( '%s: dequeuing stylesheet %s', __METHOD__, $this->handle ) );
		$this->enqueue_options['queue']->dequeue( $this->handle );
		$this->is_enqueued = $this->enqueue_options['queue']->query( $this->handle, 'enqueued' );
	}

	/**
	 * Process this stylesheet
	 *
	 * @return void
	 */
	public function process_stylesheet() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->maybe_set_default_queue();
		return $this->enqueue_options['queue']->do_item( $this->handle );
	}

	/**
	 * Process all stylesheets in $this->enqueue_options['queue']
	 *
	 * @return void
	 */
	public function process_stylesheets() {
		if ( ! $this->is_enabled() ) {
			return;
		}
		$this->maybe_set_default_queue();
		return $this->enqueue_options['queue']->do_items();
	}

	/**
	 * Enqueue the CSS file inline.
	 *
	 * @param string $handle The handle to use for the inline style.
	 * @return void
	 */
	public function enqueue_inline( $handle = '' ) {
		if ( ! $this->is_enabled() ) {
			return;
		}
		if ( ! file_exists( $this->file_path ) ) {
			Logger::log( sprintf( '%s: CSS file %s does not exist and cannot be enqueued inline', __METHOD__, $this->file_path ), Logger::LOG_LEVEL_ERROR );
			return;
		}
		if ( $this->is_file_empty() ) {
			return;
		}
		$this->maybe_set_default_queue();
		$handle = '' === $handle ? $this->handle : $handle;
		$this->enqueue_options['queue']->add_inline_style( $handle, file_get_contents( $this->file_path ) );
	}

	/**
	 * Generate the CSS file from the provided variables and save it to the filesystem.
	 *
	 * @param array $variables CSS variable values.
	 * @return mixed
	 * @throws \Exception If it can't save the file.
	 */
	public function save_file_from_variables( array $variables ) {
		if ( ! $this->is_enabled() ) {
			Logger::log( '%s: quitting because is_enabled = false', __METHOD__ );
			return false;
		}
		$css = $this->get_css_from_scss( $variables );
		if ( ! is_null( $css ) ) {
			$this->save_file( $css );
			return true;
		}
		return false;
	}

	/**
	 * Save the CSS file to the filesystem.
	 *
	 * @param string $css The CSS code to save to the filesystem.
	 * @return void
	 * @throws \Exception If it can't save the file.
	 */
	private function save_file( $css ) {
		// I'm not checking that the file structure exists, because that happens in the constructor.
		if ( false === file_put_contents( $this->file_path, $css ) ) {
			throw new \Exception(
				sprintf(
					'%s: could not write CSS file to %s',
					__METHOD__,
					esc_html( $this->file_path )
				)
			);
		}
	}

	/**
	 * Compile the SCSS file into the CSS code.
	 *
	 * @param array $variables CSS variable values.
	 * @return string
	 * @throws \Exception If the SCSS file does not exist or variables are not set.
	 */
	private function get_css_from_scss( array $variables ) {
		$source_scss = $this->scss_options['source_file'];
		$imports_folder = $this->scss_options['imports_folder'];
		$skip_check = isset( $this->scss_options['skip_file_exists_check'] ) && $this->scss_options['skip_file_exists_check'];
		if ( ! $skip_check && ( '' === $source_scss || ! file_exists( $source_scss ) ) ) {
			$error_message = sprintf( '%s: SCSS file %s does not exist', __METHOD__, $source_scss );
			Logger::log( $error_message, Logger::LOG_LEVEL_ERROR );
			throw new \Exception( $error_message );
		}
		if ( empty( $variables ) || is_null( $variables ) ) {
			$error_message = sprintf( '%s: SCSS variables are not set', __METHOD__ );
			Logger::log( $error_message, Logger::LOG_LEVEL_ERROR );
			throw new \Exception( $error_message );
		}
		$compiler = new Compiler();
		$compiler->setSourceMapOptions( Compiler::SOURCE_MAP_NONE );
		$scss_contents = file_get_contents( $source_scss ) ?? '';
		$import_path = ACSS_ASSETS_DIR . '/scss';
		$compiler->addImportPath( $import_path );
		if ( '' !== $imports_folder ) {
			$compiler->addImportPath( $imports_folder );
		}
		$compiler_variables = array();
		foreach ( $variables as $var_id => $var_value ) {
			try {
				$compiler_variables[ $var_id ] = ValueConverter::parseValue( $var_value );
			} catch ( InvalidArgumentException $e ) {
				$error_message = sprintf( '%s: error while parsing variable %s (value: "%s"): %s', __METHOD__, $var_id, $var_value, $e->getMessage() );
				Logger::log( $error_message, Logger::LOG_LEVEL_ERROR );
			}
		}
		// $compiler->addVariables( array_map( 'Automatic_CSS\ScssPhp\ValueConverter::parseValue', $variables ) );
		$compiler->addVariables( $compiler_variables );

		$css_compiled = $compiler->compileString( $scss_contents )->getCss();

		if ( '' == $css_compiled ) {
			return '';
		}

		$css_filename = basename( $this->file_path );
		$css = sprintf(
			"/* File: %s - Version: %s - Generated: %s */\n",
			$css_filename,
			Plugin::get_plugin_version(),
			current_time( 'mysql' )
		);
		$css .= $css_compiled;
		return $css;
	}

	/**
	 * Check if CSS file exists.
	 *
	 * @return bool
	 */
	public function file_exists() {
		return file_exists( $this->file_path );
	}

	/**
	 * Delete the CSS file.
	 *
	 * @return void
	 */
	public function delete_file() {
		@unlink( $this->file_path );
	}

	/**
	 * Check if the file is empty
	 *
	 * @return bool
	 */
	public function is_file_empty() {
		if ( ! $this->file_exists() ) {
			return true;
		}

		if ( '' == file_get_contents( $this->file_path ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Getter function
	 *
	 * @param string $key Key to search for.
	 * @return mixed
	 * @throws \Exception If provided key is not allowed or not set.
	 */
	public function __get( $key ) {
		$allowed_keys = array( 'handle', 'filename', 'file_path', 'file_url' );
		if ( in_array( $key, $allowed_keys ) ) {
			/**
			 * We used to store the file_url and file_path on instantiation, but that caused problems
			 * with plugins that alter the upload_dir (like the S3 offload plugin).
			 * So now we use a magic getter to generate the file_url and file_path on the fly.
			 *
			 * @since 2.6.0
			 */
			switch ( $key ) {
				case 'file_url':
					return Plugin::get_dynamic_css_url() . '/' . $this->filename;
				case 'file_path':
					return Plugin::get_dynamic_css_dir() . '/' . $this->filename;
				default:
					return $this->$key;
			}
		} else {
			throw new \Exception( "Trying to get a not allowed or not set key {$key} on a CSS_File instance" );
		}
	}

}
