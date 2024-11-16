<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $functionName
 * @return bool
 *
 * @since 1.0
 */
function mpa_is_function_disabled( $functionName ) {
	$disabledFunctions = explode( ',', ini_get( 'disable_functions' ) );

	return in_array( $functionName, $disabledFunctions );
}

/**
 * @param mixed $value
 * @return bool
 *
 * @since 1.0
 */
function mpa_is_operator( $value ) {
	return is_string( $value ) && in_array(
		strtoupper( $value ),
		array(
			'=',
			'!=',
			'>',
			'>=',
			'<',
			'<=',
			'LIKE',
			'NOT LIKE',
			'IN',
			'NOT IN',
			'BETWEEN',
			'NOT BETWEEN',
			'REGEXP',
			'NOT REGEXP',
			'RLIKE',
			'EXISTS',
			'NOT EXISTS',
		)
	);
}

/**
 * @param int|\WP_Post $post
 * @return bool
 *
 * @since 1.0
 */
function mpa_is_post_autosave( $post ) {
	return defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_autosave( $post ) );
}

/**
 * @param int|\WP_Post $post
 * @return bool
 *
 * @since 1.0
 */
function mpa_is_post_revision( $post ) {
	return is_int( wp_is_post_revision( $post ) );
}

/**
 * @param string $language Language like "en", "uk", "ru" etc.
 * @return bool
 *
 * @since 1.2.1
 */
function mpa_is_flatpickr_l10n( $language ) {
	// "en" skipped, since it's localization by default
	return in_array(
		$language,
		array(
			'ar',
			'at',
			'az',
			'be',
			'bg',
			'bn',
			'bs',
			'cat',
			'cs',
			'cy',
			'da',
			'de',
			'eo',
			'es',
			'et',
			'fa',
			'fi',
			'fo',
			'fr',
			'ga',
			'gr',
			'he',
			'hi',
			'hr',
			'hu',
			'id',
			'is',
			'it',
			'ja',
			'ka',
			'km',
			'ko',
			'kz',
			'lt',
			'lv',
			'mk',
			'mn',
			'ms',
			'my',
			'nl',
			'no',
			'pa',
			'pl',
			'pt',
			'ro',
			'ru',
			'si',
			'sk',
			'sl',
			'sq',
			'sr',
			'sr-cyr',
			'sv',
			'th',
			'tr',
			'uk',
			'vn',
			'zh',
			'zh-tw',
		)
	);
}
