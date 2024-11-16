<?php

use MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return \MotoPress\Appointment\Plugin
 *
 * @since 1.0
 */
function mpapp() {
	return Plugin::getInstance();
}

/**
 * @return \MotoPress\Appointment\Plugin\Assets
 *
 * @since 1.0
 */
function mpa_assets() {
	return mpapp()->assets();
}

/**
 * @param string $file Relative path to the plugin file.
 * @param string $pluginDir Optional. Appointment Booking by default.
 * @return string Absolute path.
 *
 * @since 1.0
 * @since 1.2.1 added the <code>pluginDir</code> argument.
 * @deprecated use mpapp()->getPluginPath()
 */
function mpa_path_to( $file, $pluginDir = MotoPress\Appointment\PLUGIN_DIR ) {
	return trailingslashit( $pluginDir ) . $file;
}

/**
 * @param string $file Relative path to the plugin file.
 * @param string $pluginUrl Optional. Appointment Booking by default.
 * @return string
 *
 * @since 1.0
 * @since 1.2.1 added the <code>pluginUrl</code> argument.
 * @deprecated use mpapp()->getPluginUrl()
 */
function mpa_url_to( $file, $pluginUrl = MotoPress\Appointment\PLUGIN_URL ) {
	return trailingslashit( $pluginUrl ) . $file;
}

/**
 * @param 'relative'|'absolute' $path Optional. 'relative' by default (that
 *     suits well textdomain functions, like load_plugin_textdomain()).
 * @return string Path to languages/ directory (with trailing slash).
 *
 * @since 1.2.1
 * @deprecated use mpapp()->getPluginPath()
 */
function mpa_languages_dir( $path = 'relative' ) {

	if ( 'absolute' == $path ) {
		$pluginDir = MotoPress\Appointment\PLUGIN_DIR;
	} else {
		// "motopress-appointment/" or renamed directory
		$pluginDir = trailingslashit( plugin_basename( MotoPress\Appointment\PLUGIN_DIR ) );
	}

	return $pluginDir . 'languages/';
}

/**
 * @return string
 *
 * @since 1.0
 * @deprecated use mpapp()->getPluginUploadsPath()
 */
function mpa_uploads_dir() {
	return \MotoPress\Appointment\UPLOADS_DIR;
}

/**
 * @return string
 *
 * @since 1.0
 * @deprecated use mpapp()->getVersion()
 */
function mpa_version() {
	return \MotoPress\Appointment\VERSION;
}

/**
 * @return string
 *
 * @since 1.0
 */
function mpa_get_plugin_data() {
	return get_plugin_data( \MotoPress\Appointment\PLUGIN_FILE );
}

/**
 * @return string
 *
 * @since 1.1.0
 * @deprecated use mpapp()->getName()
 */
function mpa_name() {
	return esc_html__( 'Appointment Booking', 'motopress-appointment' );
}

/**
 * @return MotoPress\Appointment\Registries\ShortcodesRegistry
 *
 * @since 1.2
 */
function mpa_shortcodes() {
	return mpapp()->shortcodes();
}

/**
 * @since 1.5.0
 *
 * @return string
 * @deprecated use mpapp()->getPluginUrl()
 */
function mpa_plugin_uri() {
	return MotoPress\Appointment\PLUGIN_URI;
}

/**
 * @since 1.5.0
 *
 * @return string
 * @deprecated
 */
function mpa_plugin_author() {
	return MotoPress\Appointment\PLUGIN_AUTHOR;
}

/**
 * @since 1.14.0
 */
function mpa_is_hotel_booking_active(): bool {
	return function_exists( 'MPHB' );
}
