<?php

namespace MotoPress\Appointment\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class I18n {

	/**
	 * @var string The language before first call of switchLanguage().
	 *
	 * @since 1.0
	 */
	protected $switchedLanguage = null;

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getDefaultLanguage() {
		/** @since 1.0 */
		return apply_filters( 'wpml_default_language', $this->getWordPressLanguage() );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getCurrentLanguage() {
		/** @since 1.0 */
		return apply_filters( 'wpml_current_language', $this->getWordPressLanguage() );
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getWordPressLanguage() {
		$locale = determine_locale();
		return substr( $locale, 0, 2 );
	}

	/**
	 * @param string|null $language Optional. Default language by default.
	 *
	 * @since 1.0
	 */
	public function switchLanguage( $language = null ) {
		if ( is_null( $language ) ) {
			$language = $this->getDefaultLanguage();
		}

		if ( is_null( $this->switchedLanguage ) ) {
			$this->switchedLanguage = $this->getCurrentLanguage();
		}

		/** @since 1.0 */
		do_action( 'wpml_switch_language', $language );
	}

	/**
	 * @since 1.0
	 */
	public function restoreLanguage() {
		$this->switchLanguage( $this->switchedLanguage );
		$this->switchedLanguage = null;
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isDefaultLanguage() {
		return $this->getCurrentLanguage() === $this->getDefaultLanguage();
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isTranslationPage() {
		return $this->isTranslationPluginActive() && ! $this->isDefaultLanguage();
	}

	/**
	 * @param string $postType
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isTranslatablePostType( $postType ) {
		/** @since 1.0 */
		return (bool) apply_filters( 'wpml_is_translated_post_type', null, $postType );
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isTranslationPluginActive() {
		return $this->isWpmlActive();
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	protected function isWpmlActive() {
		return defined( 'ICL_SITEPRESS_VERSION' );
	}
}
