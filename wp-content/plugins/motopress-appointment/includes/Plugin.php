<?php

namespace MotoPress\Appointment;

use MotoPress\Appointment\Handlers\AjaxHandler;
use MotoPress\Appointment\Handlers\AdminMetaboxHandler;
use MotoPress\Appointment\Handlers\SecurityHandler;
use MotoPress\Appointment\Handlers\CustomerAccountActionsHandler;
use MotoPress\Appointment\Handlers\CronsHandler;
use MotoPress\Appointment\Handlers\GoogleCalendarSyncHandler;
use MotoPress\Appointment\Handlers\NotificationHandler;
use MotoPress\Appointment\PostTypes\Logs\CustomCommentsFix;
use MotoPress\Appointment\DirectLinkActions\DirectLinkActions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class Plugin {

	/**
	 * @var self
	 *
	 * @since 1.0
	 */
	protected static $instance = null;

	private $pluginData;
	private $pluginDir;
	private $pluginUrl;

	/**
	 * @var Plugin\Assets
	 *
	 * @since 1.0
	 */
	protected $assets = null;

	/**
	 * @var Plugin\I18n
	 *
	 * @since 1.0
	 */
	protected $i18n = null;

	/**
	 * @var Plugin\Settings
	 *
	 * @since 1.0
	 */
	protected $settings = null;

	/**
	 * @var Emails\EmailsDispatcher
	 *
	 * @since 1.1.0
	 */
	protected $emailsDispatcher = null;

	/**
	 * @var DirectLinkActions
	 */
	protected $directLinkActions = null;

	/**
	 * @var array
	 *
	 * @since 1.0
	 */
	protected $registries = array();

	/**
	 * @var NotificationHandler
	 */
	private $notificationHandler = null;

	/**
	 * @since 1.0
	 */
	private function setup() {

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$this->pluginData = get_plugin_data( \MotoPress\Appointment\PLUGIN_FILE, false, false );
		$this->pluginDir  = plugin_dir_path( \MotoPress\Appointment\PLUGIN_FILE );
		$this->pluginUrl  = plugin_dir_url( \MotoPress\Appointment\PLUGIN_FILE );

		// load dependencies from vendor folder
		require_once \MotoPress\Appointment\PLUGIN_DIR . 'vendor/autoload.php';

		/**
		 * Actions initialization:
		 * - Creates new tables for each new site from the multisite network.
		 * - Deletion of created tables when deleting a multisite.
		 */
		Plugin\DatabaseTables::addActionsForMultisite();

		// Setup only basics at this point and leave others for 'plugins_loaded'
		// and 'init' actions
		$this->i18n              = new Plugin\I18n();
		$this->settings          = new Plugin\Settings();
		$this->emailsDispatcher  = new Emails\EmailsDispatcher();
		$this->directLinkActions = new DirectLinkActions();

		// Setup request-specific sections
		if ( ! wp_doing_ajax() && ! wp_doing_cron() ) {
			$this->assets = new Plugin\Assets();
		}

		// Setup registries
		$this->registries['postTypes'] = new Registries\PostTypesRegistry();
		$this->registries['postTypes']->registerAll();
		$this->registries['bundles']      = new Registries\BundlesRegistry();
		$this->registries['emails']       = new Registries\EmailsRegistry();
		$this->registries['pages']        = new Registries\PagesRegistry();
		$this->registries['payments']     = new Registries\PaymentsRegistry();
		$this->registries['repositories'] = new Registries\RepositoriesRegistry();
		$this->registries['rest']         = new Registries\RestRegistry();
		$this->registries['shortcodes']   = new Registries\ShortcodesRegistry();
		$this->registries['templates']    = new Registries\TemplatesRegistry();
		$this->registries['widgets']      = new Registries\WidgetsRegistry();

		// must be after port type registration!
		new SecurityHandler();
		new CronsHandler();
		new AjaxHandler();

		new CustomerAccountActionsHandler();
		new GoogleCalendarSyncHandler();
		$this->notificationHandler = new NotificationHandler();

		// Use priority 5/15, so addons can safely load/init on default priority
		add_action( 'plugins_loaded', array( $this, 'load' ), 15 );
		add_action( 'init', array( $this, 'init' ), 15 );
		add_action( 'admin_init', array( $this, 'initAutoUpdater' ), 15 );

		add_action(
			'admin_notices',
			function() {

				// If some code wants to show admin notice it must use AdminUIHelper::addAdminNotice()
				\MotoPress\Appointment\Helpers\AdminUIHelper::echoCollectedAdminNotices();
			}
		);
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function load() {
		// Load translations
		$this->loadTextdomain();

		// Register global items (admin and frontend)
		$this->emailsDispatcher->load();

		$this->registries['shortcodes']->registerAll();
		$this->registries['widgets']->registerAll();

		// Register admin-only items
		if ( is_admin() ) {
			$this->registries['pages']->registerCustomPages();

			new AdminMetaboxHandler();
		}

		// Do other things
		new CustomCommentsFix();

		/** @since 1.0 */
		do_action( 'mpa_plugin_loaded', $this );
	}

	/**
	 * @since 1.2.1
	 */
	public function loadTextdomain() {
		load_plugin_textdomain( 'motopress-appointment', false, mpa_languages_dir() );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function init() {
		$this->initOnce();

		// Register admin-only items
		if ( is_admin() ) {
			$this->registries['pages']->registerManagePostsPages();
			$this->registries['pages']->registerEditPostPages();
		}

		new Elementor\Init();
		new Divi\Init();
		new Gutenberg\Init();

		/** @since 1.0 */
		do_action( 'mpa_plugin_inited', $this );
	}

	/**
	 * @since 1.0
	 */
	protected function initOnce() {
		if ( (bool) get_option( 'mpa_permalinks_need_update', false ) ) {
			flush_rewrite_rules();

			delete_option( 'mpa_permalinks_need_update' );
		}
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function initAutoUpdater() {

		if ( $this->settings->isLicenseEnabled() ) {

			$licenseData = $this->settings->getLicenseData();

			$apiData = array(
				'version' => $this->getVersion(),
				'license' => $licenseData['licenseKey'],
				'item_id' => $licenseData['productId'],
				'author'  => $licenseData['author'],
			);

			$storeUrl = isset( $licenseData['storeUrl'] ) ? $licenseData['storeUrl'] : '';

			new Libraries\EDDPluginUpdater( $storeUrl, \MotoPress\Appointment\PLUGIN_FILE, $apiData );
		}
	}

	/**
	 * @return Plugin\Assets
	 *
	 * @since 1.0
	 */
	public function assets() {
		return $this->assets;
	}

	/**
	 * @return Emails\EmailsDispatcher
	 *
	 * @since 1.1.0
	 */
	public function emailsDispatcher() {
		return $this->emailsDispatcher;
	}

	/**
	 * @since 1.15.0
	 *
	 * @return DirectLinkActions
	 */
	public function directLinkActions() {
		return $this->directLinkActions;
	}

	/**
	 * @return Plugin\I18n
	 *
	 * @since 1.0
	 */
	public function i18n() {
		return $this->i18n;
	}

	/**
	 * @return Plugin\Settings
	 *
	 * @since 1.0
	 */
	public function settings() {
		return $this->settings;
	}

	/**
	 * @return Registries\BundlesRegistry
	 *
	 * @since 1.0
	 */
	public function bundles() {
		return $this->registries['bundles'];
	}

	/**
	 * @return Registries\EmailsRegistry
	 *
	 * @since 1.1.0
	 */
	public function emails() {
		return $this->registries['emails'];
	}

	/**
	 * @return Registries\PagesRegistry
	 *
	 * @since 1.0
	 */
	public function pages() {
		return $this->registries['pages'];
	}

	/**
	 * @since 1.5.0
	 *
	 * @return Registries\PaymentsRegistry
	 */
	public function payments() {
		return $this->registries['payments'];
	}

	/**
	 * @return Registries\PostTypesRegistry
	 *
	 * @since 1.0
	 */
	public function postTypes() {
		return $this->registries['postTypes'];
	}

	/**
	 * @return Registries\RepositoriesRegistry
	 *
	 * @since 1.0
	 */
	public function repositories() {
		return $this->registries['repositories'];
	}

	/**
	 * @return Registries\RestRegistry
	 *
	 * @since 1.0
	 */
	public function rest() {
		return $this->registries['rest'];
	}

	/**
	 * @return Registries\ShortcodesRegistry
	 *
	 * @since 1.0
	 */
	public function shortcodes() {
		return $this->registries['shortcodes'];
	}

	/**
	 * @return Registries\TemplatesRegistry
	 *
	 * @since 1.1.0
	 */
	public function templates() {
		return $this->registries['templates'];
	}

	/**
	 * @return Registries\WidgetsRegistry
	 *
	 * @since 1.3
	 */
	public function widgets() {
		return $this->registries['widgets'];
	}

	/**
	 * @since 1.18.0
	 */
	protected function createTablesForEachSite() {
		if ( ! is_multisite() ) {
			return;
		}

		global $wpdb;

		/**
		 * @param int $limit Max number of site IDs to get.
		 */
		$limit   = apply_filters( 'mpa_multisite_limit', 100 );
		$blogIds = $wpdb->get_col( sprintf( "SELECT blog_id FROM $wpdb->blogs LIMIT %d", $limit ) );
		foreach ( $blogIds as $blogId ) {
			switch_to_blog( $blogId );

			Plugin\DatabaseTables::createTables();

			restore_current_blog();
		}
	}

	/**
	 * @access protected
	 *
	 * @param bool $networkWide
	 *
	 * @since 1.0
	 */
	public function activate( $networkWide = false ) {

		// Create database tables
		if ( $networkWide && is_multisite() ) {
			$this->createTablesForEachSite();
		} else {
			Plugin\DatabaseTables::createTables();
		}

		$uploadsDir = $this->getPluginUploadsPath();

		if ( ! file_exists( $uploadsDir ) ) {

			// Create /uploads/motopress-appointment/
			wp_mkdir_p( $uploadsDir );

			// Create /uploads/motopress-appointment/index.php
			// phpcs:ignore
			@file_put_contents(
				$uploadsDir . 'index.php',
				'<?php' . PHP_EOL
			);

			// Create /uploads/motopress-appointment/.htaccess
			// phpcs:ignore
			@file_put_contents(
				$uploadsDir . '.htaccess',
				"Options -Indexes\n" .
				"deny from all\n" .
				"<FilesMatch '\.(jpg|jpeg|png|gif|mp3|ogg)$'>\n" .
				"Order Allow,Deny\n" .
				"Allow from all\n" .
				"</FilesMatch>\n"
			);
		}

		// Flush rewrite rules on next init
		add_option( 'mpa_permalinks_need_update', true );

		CronsHandler::schedule_crons_after_plugin_activation();
	}

	/**
	 * @access protected
	 *
	 * @since 1.13.0
	 */
	public function deactivate() {

		CronsHandler::unschedule_crons_before_plugin_deactivation();
	}

	/**
	 * @return self
	 *
	 * @since 1.0
	 */
	public static function getInstance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
			static::$instance->setup();
		}

		return static::$instance;
	}

	public function getVersion(): string {
		return isset( $this->pluginData['Version'] ) ? $this->pluginData['Version'] : '';
	}

	public function getName(): string {
		return __( 'Appointment Booking', 'motopress-appointment' );
	}

	/**
	 * @param string $relativeFilePath - starts without /
	 */
	public function getPluginPath( string $relativeFilePath = '' ): string {

		return $this->pluginDir . $relativeFilePath;
	}

	/**
	 * @param string $relativeFilePath - starts without /
	 */
	public function getPluginUploadsPath( string $relativeFilePath = '' ): string {

		$uploads = wp_upload_dir();
		return trailingslashit( $uploads['basedir'] ) . 'motopress-appointment/' . $relativeFilePath;
	}

	/**
	 * @param string $relativeFileUrl - starts without /
	 */
	public function getPluginUploadsUrl( string $relativeFileUrl = '' ): string {

		$uploads = wp_upload_dir();
		return trailingslashit( $uploads['baseurl'] ) . 'motopress-appointment/' . $relativeFileUrl;
	}

	/**
	 * @param string $relativeUrl - starts without /
	 */
	public function getPluginUrl( string $relativeUrl = '' ): string {

		return $this->pluginUrl . $relativeUrl;
	}

	/**
	 * @return NotificationHandler
	 */
	public function getNotificationHandler() {
		return $this->notificationHandler;
	}
}
