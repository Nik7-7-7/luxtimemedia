<?php
/**
 * Automatic.css Plugin class file.
 *
 * @package Automatic_CSS
 */

namespace Automatic_CSS;

use Automatic_CSS\UI\Settings_Page\Import_Export;
use Automatic_CSS\UI\Settings_Page\Plugin_Updater;
use Automatic_CSS\CSS_Engine\CSS_Engine;
use Automatic_CSS\Helpers\Logger;
use Automatic_CSS\Traits\Singleton;
use Automatic_CSS\Model\Database_Settings;
use Automatic_CSS\UI\Settings_Page\Settings_Page;
use Automatic_CSS\Exceptions\Insufficient_Permissions;
use Automatic_CSS\Exceptions\Invalid_Form_Values;
use Automatic_CSS\UI\Dashboard\Dashboard;

/**
 * Plugin class.
 */
class Plugin {

	use Singleton;

	/**
	 * All of the instances.
	 *
	 * @var array
	 */
	private $components = array();

	/**
	 * Option name for locking the plugin during the database upgrade process
	 *
	 * @var string
	 */
	public const ACSS_DATABASE_UPGRADE_LOCK_OPTION = 'automaticcss_database_upgrade_lock';

	/**
	 * Option name for locking the plugin during the plugin deletion process.
	 *
	 * @var string
	 */
	public const ACSS_DATABASE_DELETE_LOCK_OPTION = 'automaticcss_database_delete_lock';

	/**
	 * Option name for the plugin's database version.
	 *
	 * @var string
	 */
	public const ACSS_DB_VERSION = 'automatic_css_db_version';

	/**
	 * Method for getting the instances of other plugin's objects.
	 *
	 * @see https://www.php.net/manual/en/language.oop5.overloading.php#object.get
	 * @param string $key Key.
	 * @return mixed
	 * @throws \Exception If provided key is not allowed or not set.
	 */
	public function __get( $key ) {
		$allowed_keys = array( 'framework', 'settings_page', 'platforms' );
		if ( in_array( $key, $allowed_keys ) && isset( $this->$key ) ) {
			return $this->$key;
		} else {
			throw new \Exception( "Trying to get a not allowed or not set key {$key} on the Plugin instance" );
		}
	}

	/**
	 * Initialize the Plugin.
	 *
	 * @return void
	 */
	public function init() {
		// (de)activation hooks.
		register_activation_hook( ACSS_PLUGIN_FILE, array( $this, 'activate_plugin' ) );
		register_deactivation_hook( ACSS_PLUGIN_FILE, array( $this, 'deactivate_plugin' ) );
		// admin hooks.
		if ( is_admin() ) {
			// @since 1.1.1.1 - MG - plugins_loaded would be more suitable than admin_init, but also more dangerous.
			add_action( 'admin_init', array( $this, 'maybe_update_plugin' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_assets' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( ACSS_PLUGIN_FILE ), array( $this, 'add_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		}
		// @since 2.7.1 - MG - trigger the stylesheet regeneration after WP's auto updates too.
		// add_action( 'automatic_updates_complete', array( $this, 'maybe_autoupdate_plugin' ) );
		// Initialize components.
		$this->components['model'] = Database_Settings::get_instance()->init();
		$debug_enabled = $this->components['model']->get_var( 'debug-enabled' ) === 'on' ? true : false;
		$this->components['logger'] = new Logger( $debug_enabled );
		Logger::log( sprintf( "[%s]\n%s - Plugin version %s - requested by %s", gmdate( 'd-M-Y H:i:s' ), __METHOD__, self::get_plugin_version(), Logger::get_redacted_uri() ) );
		$this->components['css_engine'] = CSS_Engine::get_instance()->init();
		$this->components['settings_page'] = Settings_Page::get_instance()->init();
		$this->components['dashboard'] = new Dashboard();
		$this->components['automatic_updater'] = Plugin_Updater::get_instance()->init();
		$this->components['import_export'] = Import_Export::get_instance()->init();
		$this->components['feature_manager'] = Feature_Manager::get_instance()->init();
	}

	/**
	 * Handle the plugin's activation
	 *
	 * @return void
	 */
	public function activate_plugin() {
		try {
			do_action( 'automaticcss_activate_plugin_start' );
			// possibly other stuff...
			do_action( 'automaticcss_activate_plugin_end' );
		} catch ( Insufficient_Permissions | Invalid_Form_Values $e ) {
			Logger::log( sprintf( '%s: error while activating the plugin: %s', __METHOD__, $e->getMessage() ) );
			// Show the error.
			add_action(
				'admin_notices',
				function() use ( $e ) {
					Logger::log( 'admin_notices action' );
					$class = 'notice notice-error';
					$message = '[Automatic.css] An issue occurred while activating the plugin: ' . $e->getMessage();
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				}
			);
		}
	}

	/**
	 * Handle plugin's deactivation by (maybe) cleaning up after ourselves
	 *
	 * @return void
	 */
	public function deactivate_plugin() {
		do_action( 'automaticcss_deactivate_plugin_start' );
		$vars = $this->components['model']->get_vars();
		$delete = is_array( $vars ) && array_key_exists( 'delete-on-deactivation', $vars ) ? strtolower( trim( $vars['delete-on-deactivation'] ) ) : 'no';
		if ( 'yes' === $delete ) {
			$this->delete_plugin_data();
		}
		do_action( 'automaticcss_deactivate_plugin_end' );
	}

	/**
	 * Handle the plugin's update, if current version and last db saved version don't match.
	 *
	 * All the hooks in the upgrader_* family are not suitable because they will run the code
	 * from before the update was carried over while the files and directories have been updated.
	 * That means if your upgrader_* hook calls a function/method/namespace that is no longer
	 * present in the new code, that's going to cause a fatal error.
	 *
	 * @return void
	 */
	public function maybe_update_plugin() {
		// STEP: Check if the plugin is locked during the database upgrade process.
		$lock = get_option( self::ACSS_DATABASE_UPGRADE_LOCK_OPTION, false );
		Logger::log( sprintf( '%s: starting with lock = %b', __METHOD__, $lock ) );
		if ( $lock ) {
			// We're already running the upgrade process.
			Logger::log( sprintf( '%s: upgrade process already running, skipping', __METHOD__ ) );
			return;
		}
		// STEP: set the lock.
		update_option( self::ACSS_DATABASE_UPGRADE_LOCK_OPTION, true );
		// STEP: run the updates.
		$plugin_version = $this->get_plugin_version();
		$db_version = get_option( self::ACSS_DB_VERSION );
		if ( false === $db_version ) {
			// This is a new installation or someone deleted the option.
			Logger::log( sprintf( '%s: new installation or option deleted, skipping updates.', __METHOD__ ) );
		} else if ( $plugin_version !== $db_version ) {
			Logger::log(
				sprintf(
					'%s: db_version (%s) differs from plugin_version (%s) => running updates.',
					__METHOD__,
					$db_version,
					$plugin_version
				)
			);
			try {
				// run updates.
				do_action( 'automaticcss_update_plugin_start', $plugin_version, $db_version );
				// possibly other stuff...
				do_action( 'automaticcss_update_plugin_end', $plugin_version, $db_version );
				Logger::log( sprintf( '%s: plugin update done', __METHOD__ ) );
			} catch ( Insufficient_Permissions | Invalid_Form_Values $e ) {
				Logger::log( sprintf( '%s: error while running updates: %s', __METHOD__, $e->getMessage() ) );
				// Show the error.
				add_action(
					'admin_notices',
					function() use ( $e ) {
						Logger::log( 'admin_notices action' );
						$class = 'notice notice-error';
						$message = '[Automatic.css] An issue occurred while updating the plugin: ' . $e->getMessage();
						printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
					}
				);
				// STEP: remove the lock.
				update_option( self::ACSS_DATABASE_UPGRADE_LOCK_OPTION, false );
				return;
			}
		}
		// STEP: update the db_version.
		update_option( self::ACSS_DB_VERSION, $plugin_version );
		Logger::log( sprintf( '%s: db version set to %s', __METHOD__, $plugin_version ) );
		// STEP: remove the lock.
		update_option( self::ACSS_DATABASE_UPGRADE_LOCK_OPTION, false );
		Logger::log( sprintf( '%s: lock removed', __METHOD__ ) );
		Logger::log( sprintf( '%s: done', __METHOD__ ) );
	}

	/**
	 * Handle the plugin's update through WP's auto updates.
	 *
	 * @param array $results The results of all attempted updates.
	 * @return void
	 */
	public function maybe_autoupdate_plugin( $results ) {
		foreach ( $results['plugin'] as $plugin ) {
			if ( ! empty( $plugin->item->slug ) && 'automaticcss-plugin' === $plugin->item->slug ) {
				Logger::log( sprintf( '%s: the plugin was updated through automatic_updates_complete, triggering maybe_update_plugin', __METHOD__ ) );
				$this->maybe_update_plugin();
				return;
			}
		}
	}

	/**
	 * Delete plugin's data.
	 *
	 * @return void
	 */
	public function delete_plugin_data() {
		// STEP: check the lock.
		$lock = get_option( self::ACSS_DATABASE_DELETE_LOCK_OPTION, false );
		Logger::log( sprintf( '%s: starting with lock = %b', __METHOD__, $lock ) );
		if ( $lock ) {
			// We're already running the upgrade process.
			Logger::log( sprintf( '%s: upgrade process already running, skipping', __METHOD__ ) );
			return;
		}
		// STEP: set the lock.
		update_option( self::ACSS_DATABASE_DELETE_LOCK_OPTION, true );
		// STEP: delete the data.
		do_action( 'automaticcss_delete_plugin_data_start' );
		// possibly other stuff...
		do_action( 'automaticcss_delete_plugin_data_end' );
		delete_option( self::ACSS_DB_VERSION );
		delete_option( self::ACSS_DATABASE_UPGRADE_LOCK_OPTION );
		// STEP: remove the lock.
		delete_option( self::ACSS_DATABASE_DELETE_LOCK_OPTION );
		Logger::log( sprintf( '%s: done', __METHOD__ ) );
	}

	/**
	 * Enqueue admin scripts & styles.
	 *
	 * @param string $hook The current admin page.
	 * @return void
	 */
	public function admin_enqueue_assets( $hook ) {
		$stylesheets = apply_filters( 'automaticcss_admin_stylesheets', array() );
		foreach ( $stylesheets as $stylesheet => $options ) {
			if (
				! array_key_exists( 'hook', $options )
				|| ( is_string( $options['hook'] ) && $hook === $options['hook'] )
				|| ( is_array( $options['hook'] ) && in_array( $hook, $options['hook'] ) )
			) {
				$file = isset( $options['filename'] ) ? ACSS_ASSETS_URL . $options['filename'] : $options['url'];
				$version = isset( $options['filename'] ) ? strval( filemtime( ACSS_ASSETS_DIR . $options['filename'] ) ) : $options['version'];
				$dependency = isset( $options['dependency'] ) ? $options['dependency'] : array();
				wp_enqueue_style(
					$stylesheet,
					$file,
					$dependency,
					$version,
					'all'
				);
			}
		}
		$scripts = apply_filters( 'automaticcss_admin_scripts', array() );
		foreach ( $scripts as $script => $options ) {
			if (
				! array_key_exists( 'hook', $options )
				|| ( is_string( $options['hook'] ) && $hook === $options['hook'] )
				|| ( is_array( $options['hook'] ) && in_array( $hook, $options['hook'] ) )
			) {
				$file = isset( $options['filename'] ) ? ACSS_ASSETS_URL . $options['filename'] : $options['url'];
				$version = isset( $options['filename'] ) ? strval( filemtime( ACSS_ASSETS_DIR . $options['filename'] ) ) : $options['version'];
				$dependency = isset( $options['dependency'] ) ? $options['dependency'] : array();
				wp_enqueue_script(
					$script,
					$file,
					$dependency,
					$version,
					true
				);
				if ( ! empty( $options['localize'] ) && ! empty( $options['localize']['name'] ) && ! empty( $options['localize']['options'] ) ) {
					wp_localize_script( $script, $options['localize']['name'], $options['localize']['options'] );
				}
			}
		}
	}

	/**
	 * Add action links to the plugin's row in the plugins list.
	 *
	 * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	 * @param array $actions The current links.
	 * @return array The links with the new ones added.
	 */
	public function add_action_links( $actions ) {
		$links = array(
			'<a href="' . admin_url( 'admin.php?page=automatic-css' ) . '">Settings</a>',
			'<a href="' . admin_url( 'admin.php?page=automatic-css&tab=license' ) . '">License</a>'
		);
		$actions = array_merge( $links, $actions );
		return $actions;
	}

	/**
	 * Add a link to the plugin's row in the plugins list.
	 *
	 * @param array  $plugin_meta The current links.
	 * @param string $plugin_file The plugin file.
	 * @return array The links with the new ones added.
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		$acss_plugin_file = plugin_basename( ACSS_PLUGIN_FILE );
		if ( $plugin_file === $acss_plugin_file ) {
			$links = array(
				'guide' => '<a href="https://automaticcss.com/docs/" target="_blank">User Guide</a>',
				'support' => '<a href="https://community.automaticcss.com/" target="_blank">Support</a>',
				'faq'   => '<a href="https://community.automaticcss.com/c/faqs/" target="_blank">FAQs</a>',
				'cheatsheet' => '<a href="https://automaticcss.com/cheat-sheet/" target="_blank">Cheat Sheet</a>'
			);
			$plugin_meta = array_merge( $plugin_meta, $links );
		}
		return $plugin_meta;
	}

	/**
	 * Get the plugin's Version
	 *
	 * @return string
	 */
	public static function get_plugin_version() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( ACSS_PLUGIN_FILE );
		$version = $plugin_data['Version'];
		return $version;
	}

	/**
	 * Get the plugin's Author
	 *
	 * @return string
	 */
	public static function get_plugin_author() {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_data = get_plugin_data( ACSS_PLUGIN_FILE );
		$author = $plugin_data['Author'];
		return $author;
	}

	/**
	 * Get the directory where we store the dynamic CSS files.
	 * If it doesn't exist, create it.
	 *
	 * This was added to support plugins like S3 Offload that alter the uploads_dir.
	 *
	 * @since 2.6.0
	 * @return string
	 */
	public static function get_dynamic_css_dir() {
		$wp_upload_dir = wp_upload_dir();
		$acss_uploads_dir = trailingslashit( $wp_upload_dir['basedir'] ) . 'automatic-css';
		if ( ! file_exists( $acss_uploads_dir ) ) {
			wp_mkdir_p( $acss_uploads_dir );
		}
		return $acss_uploads_dir;
	}

	/**
	 * Get the URL where we store the dynamic CSS files.
	 *
	 * This was added to support plugins like S3 Offload that alter the uploads_dir.
	 *
	 * @since 2.6.0
	 * @return string
	 */
	public static function get_dynamic_css_url() {
		$wp_upload_dir = wp_upload_dir();
		return trailingslashit( set_url_scheme( $wp_upload_dir['baseurl'] ) ) . 'automatic-css';
	}
}
