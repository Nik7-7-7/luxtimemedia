<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param string $postType Optional.
 * @return bool
 *
 * @since 1.0
 */
function mpa_is_translation_page( $postType = '' ) {
	$i18n = mpapp()->i18n();

	if ( ! $i18n->isTranslationPluginActive() ) {
		return false;
	}

	// If the language is defult, then it's not a translation page
	if ( $i18n->isDefaultLanguage() ) {
		return false;
	}

	if ( ! empty( $postType ) && ! $i18n->isTranslatablePostType( $postType ) ) {
		return false;
	}

	return true;
}

/**
 * @param string $string
 * @param array $args Optional.
 * @return string
 *
 * @since 1.1.0
 */
function mpa_translate_string( $string, ...$args ) {
	/** @since 1.1.0 */
	return apply_filters( 'mpa_translate_string', $string, ...$args );
}

/**
 * @since 1.5.0
 *
 * @param int $pageId
 * @return int
 */
function mpa_translate_page_id( $pageId ) {
	/**
	 * @since 1.5.0
	 *
	 * @param int $pageId
	 */
	return apply_filters( 'mpa_translate_page_id', $pageId );
}

/**
 * @param string|null $language Optional. Default language by default.
 *
 * @since 1.0
 */
function mpa_switch_language( $language = null ) {
	mpapp()->i18n()->switchLanguage( $language );
}

/**
 * @param int $dayNumber 0-6 (Sunday-Saturday).
 * @return string Day of the week (translated): 'Sun', 'Mon' etc.
 *
 * @since 1.2
 */
function mpa_weekday( $dayNumber ) {
	global $weekday;

	return $weekday[ $dayNumber ];
}

/**
 * @param int $dayNumber 0-6 (Sunday-Saturday).
 * @return string Day of the week (abbreviation, translated): 'Sun', 'Mon' etc.
 *
 * @since 1.2
 */
function mpa_weekday_abbr( $dayNumber ) {
	global $weekday_abbrev;

	$day = mpa_weekday( $dayNumber );

	return $weekday_abbrev[ $day ];
}

/**
 * @param int $monthNumber 1-12 (January-February).
 * @return string Month name (translated).
 *
 * @since 1.2
 */
function mpa_month( $monthNumber ) {
	global $month;

	return $month[ $monthNumber ];
}
