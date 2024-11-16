<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class Assets {

	const MPA_LOCALIZED_DATA_OBJECT = 'mpaData';

	/**
	 * @var array [Handle => [$file, $dependencies = [], $version = '', $inFooter = true]]
	 *
	 * @since 1.0
	 */
	protected $scripts = array();

	/**
	 * @var array
	 *
	 * @since 1.21.0
	 */
	protected $localizeData = array();

	/**
	 * @var array
	 *
	 * @since 1.21.0
	 */
	protected $localizedData = array();

	/**
	 * @var string[] Script handles that require media scripts.
	 *
	 * @since 1.2.1
	 */
	protected $mediaScripts = array();

	/**
	 * @var array [Handle => [$file, $dependencies = [], $version = '', $media = 'all']]
	 *
	 * @since 1.0
	 */
	protected $styles = array();

	/**
	 * @since 1.0
	 */
	public function __construct() {
		$this->addActions();
	}

	/**
	 * @since 1.0
	 */
	protected function addActions() {
		add_action( 'init', array( $this, 'initAssets' ), 15 );
		add_action( 'init', array( $this, 'registerAssets' ), 15 );
		add_action( 'wp_print_scripts', array( $this, 'enqueueAllLocalizeData' ) );
		add_action( 'admin_print_scripts', array( $this, 'enqueueAllLocalizeData' ) );
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function initAssets() {
		if ( is_admin() ) {
			$this->scripts = mpapp()->bundles()->assets()->getAdminScripts();
			$this->styles  = mpapp()->bundles()->assets()->getAdminStyles();
		} else {
			$this->scripts = mpapp()->bundles()->assets()->getPublicScripts();
			$this->styles  = mpapp()->bundles()->assets()->getPublicStyles();
		}

		$this->initLocalizations();
		$this->applyFlatpickrTheme();

	}

	/**
	 * @since 1.2.1
	 */
	protected function initLocalizations() {
		$currentLanguage = mpapp()->i18n()->getCurrentLanguage();

		// Add Flatpickr localization
		$this->addFlatpickrLocalization( $currentLanguage );
		$this->addIntlTelInput();
	}

	/**
	 * @param string $language
	 *
	 * @since 1.2.1
	 */
	protected function addFlatpickrLocalization( $language ) {
		/**
		 * @param string $language
		 *
		 * @since 1.2.1
		 */
		$language = apply_filters( 'mpa_flatpickr_l10n', $language );

		if ( ! mpa_is_flatpickr_l10n( $language ) ) {
			return;
		}

		// Add l10n script
		$version = \MotoPress\Appointment\FLATPICKR_VERSION;
		$l10nUrl = mpa_filter_asset( "assets/js/flatpickr-{$version}/dist/l10n/{$language}.js" );

		$this->scripts['flatpickr-l10n'] = array( $l10nUrl, array(), $version );

		// Load it with Flatpickr
		$scriptsWithFlatpickr = $this->getDependentScripts( 'flatpickr' );

		foreach ( $scriptsWithFlatpickr as $handle ) {
			$this->addJsDependency( $handle, 'flatpickr-l10n' );
		}
	}

	/**
	 * @since 1.22.0
	 */
	protected function addIntlTelInput() {
		mpa_assets()->addLocalizeData(
			'intl-tel-input',
			'urls',
			array(
				'plugin' => mpapp()->getPluginUrl(),
			)
		);
		mpa_assets()->addLocalizeData(
			'intl-tel-input',
			'settings',
			array(
				'country' => mpapp()->settings()->getCountry(),
			)
		);
	}

	/**
	 * @access protected
	 *
	 * @since 1.0
	 */
	public function registerAssets() {
		// Register scripts
		foreach ( $this->scripts as $handle => $args ) {
			list( $file, $dependencies, $version, $inFooter ) = $args + array( '', array(), '', true );

			$this->registerScript( $handle, $file, $dependencies, $version, $inFooter );
		}

		// Register styles
		foreach ( $this->styles as $handle => $args ) {
			list( $file, $dependencies, $version, $media ) = $args + array( '', array(), '', 'all' );

			$this->registerStyle( $handle, $file, $dependencies, $version, $media );
		}
	}

	/**
	 * @param string $handle
	 * @param string $file Absolute path to the file.
	 * @param array $dependencies Optional.
	 * @param string $version Optional. Current version of the plugin by default.
	 * @param bool $inFooter Optional. True by default.
	 *
	 * @return self
	 *
	 * @since 1.0
	 */
	public function registerScript( $handle, $file, $dependencies = array(), $version = '', $inFooter = true ) {
		if ( ! $version ) {
			$version = mpapp()->getVersion();
		}

		// Check "wp-media" dependency
		if ( in_array( 'wp-media', $dependencies ) ) {
			// Remove "wp-media" from dependencies
			mpa_array_remove( $dependencies, 'wp-media' );

			// Save script handle
			$this->mediaScripts[] = $handle;
		}

		wp_register_script( $handle, $file, $dependencies, $version, $inFooter );

		return $this;
	}

	/**
	 * @param string $handle
	 * @param string $file Absolute path to the file.
	 * @param array $dependencies Optional.
	 * @param string $version Optional. Current version of the plugin by default.
	 * @param string $media Optional. 'all' by default.
	 *
	 * @return self
	 *
	 * @since 1.0
	 */
	public function registerStyle( $handle, $file, $dependencies = array(), $version = '', $media = 'all' ) {
		if ( empty( $version ) ) {
			$version = mpapp()->getVersion();
		}

		wp_register_style( $handle, $file, $dependencies, $version, $media );

		return $this;
	}

	/**
	 * @param string $handle
	 * @param string|callable $dependency New dependency or callback to filter
	 *     dependencies.
	 *
	 * @since 1.0
	 */
	public function addJsDependency( $handle, $dependency ) {
		$this->addDependencyTo( $this->scripts, $handle, $dependency );
	}

	/**
	 * @param string $handle
	 * @param string|callable $dependency New dependency or callback to filter
	 *     dependencies.
	 *
	 * @since 1.0
	 */
	public function addCssDependency( $handle, $dependency ) {
		$this->addDependencyTo( $this->styles, $handle, $dependency );
	}

	/**
	 * @param array $assets
	 * @param string $handle
	 * @param string|callable $dependency
	 *
	 * @since 1.0
	 */
	protected function addDependencyTo( &$assets, $handle, $dependency ) {
		if ( ! array_key_exists( $handle, $assets ) ) {
			return;
		}

		$currentDependencies = isset( $assets[ $handle ][1] ) ? $assets[ $handle ][1] : array();

		if ( is_callable( $dependency ) ) {
			$assets[ $handle ][1] = $dependency( $currentDependencies );
		} elseif ( ! in_array( $dependency, $currentDependencies ) ) {
			$assets[ $handle ][1][] = $dependency;
		}
	}

	/**
	 * @param string $dependency
	 *
	 * @since 1.2.1
	 */
	public function getDependentScripts( $dependency ) {
		return $this->getDependentsOf( $this->scripts, $dependency );
	}

	/**
	 * @param string $dependency
	 *
	 * @since 1.2.1
	 */
	public function getDependentStyles( $dependency ) {
		return $this->getDependentsOf( $this->styles, $dependency );
	}

	/**
	 * @param array $assets
	 * @param string $dependency
	 *
	 * @since 1.2.1
	 */
	protected function getDependentsOf( &$assets, $dependency ) {
		$handles = array();

		foreach ( $assets as $handle => $args ) {
			if ( isset( $args[1] ) && in_array( $dependency, $args[1] ) ) {
				$handles[] = $handle;
			}
		}

		return $handles;
	}

	/**
	 * @param string $handle
	 *
	 * @return self
	 *
	 * @since 1.0
	 */
	public function enqueueScript( $handle ) {
		wp_enqueue_script( $handle );

		// Maybe enqueue media scripts
		$this->enqueueMedia( $handle );

		// Tell WordPress that scripts contain translations
		$this->enqueueTranslations( $handle );

		// @todo: MPI-11482
		$this->enqueueAllLocalizeData();

		return $this;
	}

	/**
	 * @param string $handle
	 *
	 * @return self
	 *
	 * @since 1.0
	 */
	public function enqueueStyle( $handle ) {
		wp_enqueue_style( $handle );

		return $this;
	}

	/**
	 * @param string $handle
	 *
	 * @since 1.2.1
	 */
	protected function enqueueMedia( $handle ) {
		$hasWPMediaDependency = in_array( $handle, $this->mediaScripts );

		if ( $hasWPMediaDependency ) {
			wp_enqueue_media();
		}
	}

	/**
	 * @param string $handle
	 *
	 * @since 1.2.1
	 */
	protected function enqueueTranslations( $handle ) {
		$dependencies = array();

		if ( array_key_exists( $handle, $this->scripts ) && ! empty( $this->scripts[ $handle ][1] ) ) {
			$dependencies = $this->scripts[ $handle ][1];
		}

		if ( in_array( 'wp-i18n', $dependencies ) ) {
			wp_set_script_translations( $handle, 'motopress-appointment', mpa_languages_dir( 'absolute' ) );
		}
	}

	/**
	 * @param string $handle
	 *
	 * @return self
	 *
	 * @since 1.0
	 */
	public function enqueueBundle( $handle ) {
		$this->enqueueScript( $handle );
		$this->enqueueStyle( $handle );

		return $this;
	}

	/**
	 * @param string $handle
	 * @param string $objectName
	 * @param array $data
	 *
	 * @return void
	 *
	 * @since 1.21.0
	 */
	public function addLocalizeData( string $handle, string $objectName, array $data ) {
		if ( ! isset( $this->localizeData[ $handle ] ) ) {
			$this->localizeData[ $handle ] = [];
		}

		if ( isset( $this->localizeData[ $handle ][ $objectName ] ) ) {
			$this->localizeData[ $handle ][ $objectName ] = array_merge( $this->localizeData[ $handle ][ $objectName ], $data );
		} else {
			$this->localizeData[ $handle ][ $objectName ] = $data;
		}
	}

	/**
	 * @param string $handle
	 *
	 * @return bool
	 *
	 * @since 1.21.0
	 */
	protected function hasLocalizeData( string $handle ): bool {
		return isset( $this->localizeData[ $handle ] ) && wp_script_is( $handle, 'enqueued' );
	}

	/**
	 * @param string $handle
	 * @param string $object_name
	 * @param $data
	 *
	 * @return bool
	 *
	 * @since 1.21.0
	 */
	protected function isNewLocalizeData( string $handle, string $object_name, $data ): bool {
		$dataHash = md5( wp_json_encode( $data ) );
		if ( ! isset( $this->localizedData[ $handle ][ $object_name ] ) || $this->localizedData[ $handle ][ $object_name ] != $dataHash ) {
			$this->localizedData[ $handle ][ $object_name ] = $dataHash;

			return false;
		}

		return true;
	}

	/**
	 * @param string $handle
	 *
	 * @return array
	 *
	 * @since 1.21.0
	 */
	protected function getLocalizeData( string $handle ): array {
		$newData = [];
		foreach ( $this->localizeData[ $handle ] as $object_name => $data ) {
			if ( ! $this->isNewLocalizeData( $handle, $object_name, $data ) ) {
				$newData[ $object_name ] = $data;
			}
		}

		return $newData;
	}

	/**
	 * @param array $localizeData
	 *
	 * @return string
	 *
	 * @since 1.21.0
	 */
	protected function createLocalizeDataScript( array $localizeData ): string {
		$js = sprintf( 'var %s = %s || {};', self::MPA_LOCALIZED_DATA_OBJECT, self::MPA_LOCALIZED_DATA_OBJECT );

		foreach ( $localizeData as $objectName => $data ) {
			$encodedData = rawurlencode( wp_json_encode( $data ) );
			$js          .= sprintf(
				" if (typeof %s.%s !== 'undefined') {
                var newData = JSON.parse(decodeURIComponent('%s'));
                %s.%s = {...%s.%s, ...newData};
            } else {
                %s.%s = JSON.parse(decodeURIComponent('%s'));
            }",
				self::MPA_LOCALIZED_DATA_OBJECT, $objectName,
				$encodedData,
				self::MPA_LOCALIZED_DATA_OBJECT, $objectName, self::MPA_LOCALIZED_DATA_OBJECT, $objectName,
				self::MPA_LOCALIZED_DATA_OBJECT, $objectName, $encodedData
			);
		}

		return $js;
	}

	/**
	 * @param string $handle
	 *
	 * @return void
	 *
	 * @since 1.21.0
	 */
	protected function enqueueLocalizeData( string $handle ) {
		if ( ! $this->hasLocalizeData( $handle ) ) {
			return;
		}

		$localizeData = $this->getLocalizeData( $handle );

		if ( empty( $localizeData ) ) {
			return;
		}

		$js = $this->createLocalizeDataScript( $localizeData );
		wp_add_inline_script( $handle, $js, 'before' );
	}

	/**
	 * @access protected
	 *
	 * @since 1.21.0
	 */
	public function enqueueAllLocalizeData() {
		foreach ( array_keys( $this->localizeData ) as $handle ) {
			$this->enqueueLocalizeData( $handle );
		}
	}

	private function applyFlatpickrTheme() {
		$theme = mpapp()->settings()->getFormCalendarTheme();

		if ( ! $theme ) {
			return;
		}

		$version = \MotoPress\Appointment\FLATPICKR_VERSION;
		$themeCSSPath = mpa_filter_asset( "assets/js/flatpickr-{$version}/dist/themes/{$theme}.css" );

		$this->styles['flatpickr'][ 0 ] = $themeCSSPath;
	}
}
