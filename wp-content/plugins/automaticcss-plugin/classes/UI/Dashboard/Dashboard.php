<?php
/**
 * Automatic.css Dashboard file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS\UI\Dashboard;

use Automatic_CSS\Exceptions\Invalid_Form_Values;
use Automatic_CSS\Framework\Platforms\Breakdance;
use Automatic_CSS\Framework\Platforms\Bricks;
use Automatic_CSS\Framework\Platforms\Gutenberg;
use Automatic_CSS\Framework\Platforms\Oxygen;
use Automatic_CSS\Helpers\Flag;
use Automatic_CSS\Helpers\Locale;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Helpers\Timer;
use Automatic_CSS\Model\Config\UI;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\Plugin;

/**
 * Dashboard class.
 */
class Dashboard {

	/**
	 * The path to the dashboard scripts.
	 *
	 * @var string
	 */
	private $path;

	/**
	 * The context in which the dashboard is being loaded.
	 * Syntax: builder_name/context.
	 * Context options: frontend, builder, preview.
	 *
	 * @var string
	 */
	private $loading_context;

	/**
	 * Initialize the feature.
	 */
	public function __construct() {
		$this->path = '/UI/Dashboard/js';
		$this->loading_context = null;
		add_action( 'init', array( $this, 'enqueue_the_dashboard_assets' ) );
		add_filter( 'script_loader_tag', array( $this, 'add_type_attribute' ), 10, 3 );
		add_action( 'wp_ajax_automaticcss_save_settings_new', array( $this, 'save_settings' ) );
	}

	/**
	 * Enqueue dashboard in properly builder's context.
	 *
	 * @return void
	 */
	public function enqueue_the_dashboard_assets() {
		if ( ! current_user_can( Database_Settings::CAPABILITY ) ) {
			return;
		}
		// STEP: Get the loading context.
		$builder_context = $this->get_the_loading_context();
		$loading = $builder_context['loading'];
		$version = $builder_context['version'];
		$action = $builder_context['action'];
		$function = $builder_context['function'];
		// STEP: Enqueue the dashboard assets.
		add_action( $action, array( $this, $function ) );
		$this->loading_context = $loading;
		// STEP: Fix Breakdance loading.
		if ( 'breakdance/preview' === $loading && version_compare( $version, '2.0.0', '<' ) ) {
			// Breakdance v1 doesn't run wp_enqueue_scripts, forcing us to load in the preview context.
			$this->enqueue_builder_scripts();
		}
	}

	/**
	 * Get the loading context.
	 *
	 * @return array
	 */
	public function get_the_loading_context() {
		$builders = array(
			// NOTE: The order of the builders is important.
			'oxygen' => Oxygen::class,
			'breakdance' => Breakdance::class,
			'bricks' => Bricks::class,
			// No Gutenberg::class because Gutenberg isn't a builder and is handled differently.
		);

		$context_info = array();
		$page_builder_found = false; // If a page builder is found, we don't need to load Gutenberg.

		// STEP: Fill out the context info for each builder.
		foreach ( $builders as $builder_prefix => $builder_class ) {
			$is_active = $builder_class::is_active();
			$is_builder = $builder_class::is_builder_context();
			$is_frontend = $builder_class::is_frontend_context();
			$is_preview = $builder_class::is_preview_context();
			$version = $builder_class::get_version();
			$page_builder_found = $is_active && ( $is_builder || $is_frontend || $is_preview );
			$context_info[ $builder_prefix ] = array(
				'is_active' => $is_active,
				'is_builder' => $is_builder,
				'is_frontend' => $is_frontend,
				'is_preview' => $is_preview,
				'version' => $version,
			);
		}

		// STEP: Fill out the context info for Gutenberg.
		$context_info['gutenberg'] = array(
			'is_active' => ! $page_builder_found,
			'is_builder' => is_admin(),
			'is_frontend' => ! is_admin(),
			'is_preview' => false,
			'version' => '',
		);

		// STEP: Get the loading context.
		return ( new Loader( $context_info ) )->get_loading_context();
	}

	/**
	 * Enqueue builder context scripts.
	 *
	 * @return void
	 */
	public function enqueue_builder_scripts() {
		$this->enqueue_dashboard();
		$this->enqueue_hot_stylesheet_reloading();
	}

	/**
	 * Output builder scripts.
	 * Because Breakdance doesn't support wp_enqueue_scripts, we need to output the script tag directly.
	 *
	 * @return void
	 */
	public function output_builder_scripts() {
		$enqueue_settings = $this->get_dashboard_enqueue_settings();
		$all_settings = $enqueue_settings['dashboard_settings'];
		$file_url = $enqueue_settings['file_url'];
		$file_version = $enqueue_settings['file_version'];
		$defer = Flag::is_on( 'ACSS_FLAG_DEFER_DASHBOARD_SCRIPTS' ) ? ' defer' : '';
		$module_and_crossorigin =
			Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_AS_MODULE' ) || Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_FROM_VITE' ) ?
			' type="module" crossorigin' :
			'';
		?>
		<script>
			window.automatic_css_settings = <?php echo wp_json_encode( $all_settings ); ?>;
		</script>
		<script
			<?php echo $module_and_crossorigin; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			src="<?php echo esc_url( $file_url ); ?>?ver=<?php echo esc_attr( $file_version ); ?>"
			<?php echo esc_attr( $defer ); ?>>
		</script> <?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
		<?php
	}

	/**
	 * Enqueue preview context scripts.
	 *
	 * @return void
	 */
	public function enqueue_preview_scripts() {
		$this->enqueue_hot_stylesheet_reloading();
	}

	/**
	 * Enqueue frontend context scripts.
	 *
	 * @return void
	 */
	public function enqueue_frontend_scripts() {
		$this->enqueue_dashboard();
		$this->enqueue_preview_scripts();
	}

	/**
	 * Enqueue dashboard scripts.
	 *
	 * @return void
	 */
	public function enqueue_dashboard() {
		$load_in_footer = Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_IN_FOOTER' ) ?? false;
		$enqueue_settings = $this->get_dashboard_enqueue_settings();
		wp_enqueue_script(
			$enqueue_settings['handle'],
			$enqueue_settings['file_url'],
			array(),
			$enqueue_settings['file_version'],
			$load_in_footer
		);
		wp_localize_script(
			$enqueue_settings['handle'],
			$enqueue_settings['dashboard_object_name'],
			$enqueue_settings['dashboard_settings']
		);
	}

	/**
	 * Get all the settings to enqueue the dashboard script.
	 *
	 * @return array
	 */
	private function get_dashboard_enqueue_settings() {
		$filename = 'dashboard.js';
		$database_settings = ( Database_Settings::get_instance() )->get_vars();
		$ui_settings = ( new UI() )->load();
		$loading_context_parts = null !== $this->loading_context ?
			explode( '/', $this->loading_context ) :
			array( '', '' );
		return array(
			'handle' => 'acss-dashboard',
			'file_url' => Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_FROM_VITE' ) ? 'http://localhost:5173/features/Dashboard/main.js' : ACSS_CLASSES_URL . "{$this->path}/{$filename}",
			'file_version' => filemtime( ACSS_CLASSES_DIR . "{$this->path}/{$filename}" ),
			'dashboard_object_name' => 'automatic_css_settings',
			'dashboard_settings' => array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'automatic_css_save_settings' ),
				'database_settings' => ( Database_Settings::get_instance() )->get_vars(),
				'ui_settings' => ( new UI() )->load(),
				'version' => Plugin::get_plugin_version(),
				'loading_context' => array(
					'is_frontend' => 'frontend' === $loading_context_parts[1],
					'is_preview' => 'preview' === $loading_context_parts[1],
					'is_builder' => 'builder' === $loading_context_parts[1],
					'builder' => $loading_context_parts[0],
					'active_plugins' => $this->get_active_plugins(),
				),
			)
		);
	}

	/**
	 * Get active plugins.
	 *
	 * @return array
	 */
	private function get_active_plugins() {
		$active_plugins = wp_get_active_and_valid_plugins();
		$plugin_filenames = array_map(
			function( $path ) {
				$filename = basename( $path );
				switch ( $filename ) {
					case 'frames-plugin.php':
						$filename = 'frames';
						break;
				}
				$pos = strrpos( $filename, '.' );
				return ( false === $pos ) ? $filename : substr( $filename, 0, $pos );
			},
			$active_plugins
		);
		return $plugin_filenames;
	}

	/**
	 * Enqueue hot stylesheet reloading script.
	 *
	 * @return void
	 */
	public function enqueue_hot_stylesheet_reloading() {
		$load_in_footer = Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_IN_FOOTER' ) ?? false;
		$filename = 'acss-hot-reload.js';
		$file_url = ACSS_CLASSES_URL . "{$this->path}/{$filename}";
		wp_enqueue_script(
			'acss-hot-reload',
			$file_url,
			array(),
			filemtime( ACSS_CLASSES_DIR . "{$this->path}/{$filename}" ),
			$load_in_footer
		);
	}

	/**
	 * Adds 'type="module"' to the script tag
	 *
	 * @param string $tag The original script tag.
	 * @param string $handle The script handle.
	 * @param string $src The script source.
	 * @return string
	 */
	public static function add_type_attribute( $tag, $handle, $src ) {
		// if not correct script, do nothing and return original $tag.
		$defer = Flag::is_on( 'ACSS_FLAG_DEFER_DASHBOARD_SCRIPTS' ) ? ' defer' : '';
		$module_and_crossorigin =
			Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_SCRIPTS_AS_MODULE' ) || Flag::is_on( 'ACSS_FLAG_LOAD_DASHBOARD_FROM_VITE' ) ?
			' type="module" crossorigin' :
			'';
		$scripts_to_change = array( 'acss-dashboard', 'acss-hot-reload' );
		if ( in_array( $handle, $scripts_to_change, true ) ) {
			$tag = sprintf( '<script%s src="%s"%s></script>', $module_and_crossorigin, esc_url( $src ), $defer ); // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
		}
		// change the script tag by adding type="module" and return it.
		return $tag;
	}

	/**
	 * Save the plugin's settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		$timer = new Timer();
		$this->ensure_permissions_to_save( __METHOD__ );
		$form_settings = $this->sanitize_input_data();
		// Save settings.
		try {
			$this->save( $form_settings, $timer );
		} catch ( \Exception $e ) {
			$error_message = $e->getMessage();
			Logger::log( sprintf( '%s: caught this error: %s', __METHOD__, $error_message ), Logger::LOG_LEVEL_ERROR );
			Logger::log( debug_backtrace(), Logger::LOG_LEVEL_ERROR );
			Locale::restore_locale();
			wp_send_json_error( $error_message, 500 );
		}
	}

	/**
	 * Ensure the user has the permissions to save the settings.
	 *
	 * @param string $method_name The name of the method that is trying to save the settings.
	 * @return void
	 */
	private function ensure_permissions_to_save( $method_name ) {
		Logger::log( sprintf( '%s: starting', $method_name ) );
		if ( ! check_ajax_referer( 'automatic_css_save_settings', 'nonce', false ) ) {
			Logger::log( sprintf( '%s: failed nonce check - quitting early', $method_name ), Logger::LOG_LEVEL_ERROR );
			wp_send_json_error( 'Failed nonce check.', 400 );
		}
		if ( ! current_user_can( Database_Settings::CAPABILITY ) ) {
			Logger::log( sprintf( '%s: capability check failed - quitting early', $method_name ), Logger::LOG_LEVEL_ERROR );
			wp_send_json_error( 'You cannot save these settings.', 403 );
		}
	}

	/**
	 * Sanitize the input data.
	 *
	 * @return array
	 */
	private function sanitize_input_data() {
		// Sanitize and validate input data.
		$form_settings = json_decode( filter_input( INPUT_POST, 'database_settings' ), true );
		ksort( $form_settings ); // to make debugging easier.
		if ( ! is_array( $form_settings ) ) {
			Logger::log( sprintf( '%s: did not receive form settings in the expected format - quitting early', __METHOD__ ), Logger::LOG_LEVEL_ERROR );
			wp_send_json_error( 'Received empty settings or in an unexpected format.', 400 );
		}
		Logger::log( sprintf( "%s: received these form settings:\n%s", __METHOD__, print_r( $form_settings, true ) ), Logger::LOG_LEVEL_NOTICE );
		return $form_settings;
	}

	/**
	 * Save the settings.
	 *
	 * @param array $form_settings The form settings.
	 * @param Timer $timer The timer.
	 * @return void
	 */
	private function save( $form_settings, $timer ) {
		try {
			Locale::fix_locale();
			$model = Database_Settings::get_instance();
			$all_settings = array_merge( $model->get_vars(), $form_settings );
			$all_settings['timestamp'] = time(); // Force update_option to always save the settings.
			ksort( $all_settings ); // to make debugging easier.
			$save_info = $model->save_settings( $all_settings );
			if ( true === $save_info['has_changed'] ) {
				$time = $timer->get_time();
				$generated_files = $save_info['generated_files_number'];
				// Settings were saved and CSS regenerated.
				Logger::log( sprintf( '%s: settings saved and %d CSS files regenerated - done in %s seconds', __METHOD__, $generated_files, $time ) );
				Locale::restore_locale();
				wp_send_json_success( sprintf( 'Settings updated and %d CSS file(s) generated correctly in %s seconds.', $generated_files, $time ) );
			} else {
				$time = $timer->get_time();
				// Settings were not saved because no changed was detected.
				// Please note: this is not an error! Those are thrown through exceptions.
				Logger::log( sprintf( '%s: no changes detected, did not save or regenerate CSS files - done in %s seconds', __METHOD__, $time ) );
				Locale::restore_locale();
				wp_send_json_success( 'No changes detected. Change a setting and click save to force stylesheet regeneration.' );
			}
		} catch ( Invalid_Form_Values $e ) {
			$error_message = $e->getMessage();
			$errors = $e->get_errors();
			Locale::restore_locale();
			wp_send_json_error(
				array(
					'message' => $error_message,
					'errors' => $errors
				),
				422 // Unprocessable Entity.
			);
		}
	}

}
