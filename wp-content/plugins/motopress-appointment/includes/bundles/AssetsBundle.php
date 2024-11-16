<?php

namespace MotoPress\Appointment\Bundles;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 *
 * @todo Make Flatpickr part of the other bundles.
 */
class AssetsBundle {

	/**
	 * @var array
	 *
	 * @since 1.0
	 * @since 1.2 added "mpa-edit-category" script.
	 */
	protected $scripts = array(

		// Use .min.js version of the file by default if there is a minified
		// version of the script; otherwise - use .js notation
		'global' => array(
			// Handle => [$file, $dependencies = [], $version = '', $inFooter = true]
			'flatpickr'      => array( 'assets/js/flatpickr-4.6.3/dist/flatpickr.min.js', array(), \MotoPress\Appointment\FLATPICKR_VERSION ),
			'spectrum'       => array( 'assets/js/spectrum-2.0.8/dist/spectrum.min.js', array( 'jquery' ), \MotoPress\Appointment\SPECTRUM_VERSION ),
			'intl-tel-input' => array( 'assets/js/intl-tel-input-17.0.19/js/intlTelInput.min.js', array(), '17.0.19' ),
		),
		'admin' => array(
			'mpa-edit-post'           => array( 'assets/js/edit-post.min.js', array( 'jquery', 'wp-i18n', 'wp-date', 'wp-api-request', 'flatpickr', 'spectrum', 'intl-tel-input' ) ),
			'mpa-edit-category'       => array( 'assets/js/edit-category.min.js', array( 'jquery', 'wp-i18n', 'wp-date', 'wp-media' ) ),
			'mpa-manage-posts'        => array( 'assets/js/manage-posts.min.js', array( 'jquery' ) ),
			'mpa-settings-page'       => array( 'assets/js/settings-page.min.js', array( 'jquery', 'wp-i18n', 'wp-date', 'wp-api-request', 'intl-tel-input', 'wp-media' ) ),
			'mpa-gutenberg-blocks'    => array( 'assets/js/gutenberg-blocks.min.js', array( 'wp-i18n', 'wp-date', 'wp-server-side-render', 'wp-element', 'wp-blocks', 'wp-components', 'flatpickr', 'intl-tel-input' ) ),
			'wpapi'                   => array( 'assets/js/wpapi/wpapi.min.js', array() ),
			'mpa-calendar-page'       => array( 'assets/js/calendar-page.min.js', array( 'wp-i18n', 'wp-date', 'wp-element', 'wp-html-entities', 'wpapi', 'wp-components' ) ),
			'mpa-analytics-page'      => array( 'assets/js/analytics-page.min.js', array( 'wp-i18n', 'wp-date', 'wp-element', 'wp-html-entities', 'wpapi', 'wp-components' ) ),
			'mpa-widgets-manage-page' => array( 'assets/js/widgets-manage-page.min.js', array( 'jquery', 'wp-i18n', 'wp-date', ) ),
			'mpa-customers-page'      => array( 'assets/js/customers-page.min.js', array( 'jquery', 'wp-i18n', 'wp-date', 'intl-tel-input', 'wp-api-request' ) ),
		),
		'public' => array(
			'mpa-public'            => array( 'assets/js/public.min.js', array( 'jquery', 'wp-i18n', 'wp-date', 'wp-api-request', 'flatpickr', 'intl-tel-input', 'wp-hooks' ) ),
			'mpa-elementor-widgets' => array( 'assets/js/elementor-widgets.min.js', array( 'jquery', 'wp-i18n', 'wp-date', 'wp-api-request', 'flatpickr', 'intl-tel-input' ) ),
			'mpa-divi-modules'      => array( 'assets/js/divi-modules.min.js', array( 'jquery', 'wp-i18n', 'wp-date', 'customize-preview' ) ),
		),
	);

	/**
	 * @since 1.0
	 * @since 1.2 added "mpa-edit-category" style.
	 * @since 1.2.1 added "mpa-admin" style.
	 */
	protected $styles = array(

		// Use .min.css version of the file by default if there is a minified
		// version of the stylesheet; otherwise - use .css notation
		'global' => array(
			// Handle => [$file, $dependencies = [], $version = '', $media = 'all']
			'flatpickr'      => array( 'assets/js/flatpickr-4.6.3/dist/flatpickr.min.css', array(), \MotoPress\Appointment\FLATPICKR_VERSION ),
			'spectrum'       => array( 'assets/js/spectrum-2.0.8/dist/spectrum.min.css', array(), \MotoPress\Appointment\SPECTRUM_VERSION ),
			'intl-tel-input' => array( 'assets/js/intl-tel-input-17.0.19/css/intlTelInput.min.css', array(), '17.0.19' ),
		),
		'admin'  => array(
			'mpa-admin'                  => array( 'assets/css/admin.min.css', array( 'intl-tel-input' ) ),
			'mpa-wp-admin-color-schemes' => array( 'assets/css/wp-admin-color-schemes.min.css' ),
			'mpa-edit-post'              => array( 'assets/css/edit-post.min.css', array( 'flatpickr', 'spectrum', 'intl-tel-input' ) ),
			'mpa-edit-category'          => array( 'assets/css/edit-category.min.css' ),
			'mpa-manage-posts'           => array( 'assets/css/manage-posts.min.css' ),
			'mpa-settings-page'          => array( 'assets/css/settings-page.min.css', array( 'intl-tel-input' ) ),
			'mpa-gutenberg-blocks'       => array( 'assets/css/public.min.css', array( 'flatpickr' ) ),
			'mpa-calendar-page'          => array( 'assets/css/calendar-page.min.css', array( 'wp-components', 'mpa-wp-admin-color-schemes' ) ),
			'mpa-analytics-page'         => array( 'assets/css/analytics-page.min.css', array( 'wp-components', 'mpa-wp-admin-color-schemes' ) ),
			'mpa-widgets-manage-page'    => array( 'assets/css/widgets-manage-page.min.css' ),
		),
		'public' => array(
			'mpa-public' => array( 'assets/css/public.min.css', array( 'flatpickr', 'intl-tel-input' ) ),
		),
	);

	/**
	 * @return array Array of [Handle => [$file, $dependencies = [],
	 *     $version = '', $inFooter = true]].
	 *
	 * @since 1.0
	 */
	public function getAdminScripts() {

		$scripts = array_merge( $this->scripts['global'], $this->scripts['admin'] );
		/** @since 1.0 */
		$scripts = apply_filters( 'mpa_admin_scripts', $this->filterAssets( $scripts ) );

		return $scripts;
	}

	/**
	 * @return array Array of [Handle => [$file, $dependencies = [],
	 *     $version = '', $inFooter = true]].
	 *
	 * @since 1.0
	 */
	public function getPublicScripts() {

		$scripts = array_merge( $this->scripts['global'], $this->scripts['public'] );
		/** @since 1.0 */
		$scripts = apply_filters( 'mpa_public_scripts', $this->filterAssets( $scripts ) );

		return $scripts;
	}

	/**
	 * @return array Array of [Handle => [$file, $dependencies = [],
	 *     $version = '', $media = 'all']].
	 *
	 * @since 1.0
	 */
	public function getAdminStyles() {

		$styles = array_merge( $this->styles['global'], $this->styles['admin'] );
		/** @since 1.0 */
		$styles = apply_filters( 'mpa_admin_styles', $this->filterAssets( $styles ) );

		return $styles;
	}

	/**
	 * @return array Array of [Handle => [$file, $dependencies = [],
	 *     $version = '', $media = 'all']].
	 *
	 * @since 1.0
	 */
	public function getPublicStyles() {

		$styles = array_merge( $this->styles['global'], $this->styles['public'] );
		/** @since 1.0 */
		$styles = apply_filters( 'mpa_public_styles', $this->filterAssets( $styles ) );

		return $styles;
	}

	/**
	 * @param array $assets
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function filterAssets( $assets ) {

		foreach ( $assets as &$args ) {
			$args[0] = mpa_filter_asset( $args[0] );
		}

		unset( $args );

		return $assets;
	}
}
