<?php

class MailsterTranslations {

	private $endpoint = 'https://translate.mailster.co';


	public function __construct() {

		add_action( 'init', array( &$this, 'load' ), 1 );
		add_filter( 'site_transient_update_plugins', array( &$this, 'update_plugins_filter' ), 1 );
		add_action( 'delete_site_transient_update_plugins', array( &$this, 're_check' ) );
		add_action( 'wp_version_check', array( &$this, 're_check' ), 999 );
		add_action( 'add_option_WPLANG', array( &$this, 're_check' ), 999 );
		add_action( 'update_option_WPLANG', array( &$this, 're_check' ), 999 );
		add_action( 'updated_user_meta', array( &$this, 'updated_user_meta' ), 999, 4 );
	}


	/**
	 * Load the translations
	 *
	 * @return void
	 */
	public function load() {

		if ( is_dir( MAILSTER_UPLOAD_DIR . '/languages' ) ) {
			$custom = MAILSTER_UPLOAD_DIR . '/languages/' . sprintf( 'mailster-%s.mo', $this->get_locale() );
			if ( file_exists( $custom ) ) {
				load_textdomain( 'mailster', $custom );
			} else {
				load_plugin_textdomain( 'mailster' );
			}
		} else {
			load_plugin_textdomain( 'mailster' );
		}
	}


	/**
	 * Reload translations if "locale" option is changed
	 *
	 * @param object $meta_id
	 * @param object $object_id
	 * @param object $meta_key
	 * @param object $_meta_value
	 * @return void
	 */
	public function updated_user_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

		if ( $meta_key == 'locale' ) {
			$this->re_check();
		}
	}


	/**
	 * Filter the update_plugins transient and adds our translations
	 *
	 * @param object $value
	 * @return object
	 */
	public function update_plugins_filter( $value ) {
		// no translation support
		if ( ! isset( $value->translations ) ) {
			return $value;
		}

		$translation_data = $this->get_updates();

		if ( ! empty( $translation_data ) ) {
			$value->translations = array_merge( $value->translations, array_values( $translation_data ) );
		}

		return $value;
	}


	/**
	 * Check if a translation is installed
	 *
	 * @return boolean
	 */
	public function translation_installed() {

		return is_textdomain_loaded( 'mailster' );
	}

	/**
	 * Check if a translation is available
	 *
	 * @param  boolean $force (optional)
	 * @return boolean|object
	 */
	public function translation_available( $force = false ) {

		if ( $force ) {
			$this->get_translation_data();
		}

		$translation_data = $this->get_updates();
		if ( empty( $translation_data ) ) {
			return false;
		}
		$locale = $this->get_locale();
		if ( ! isset( $translation_data[ $locale ] ) ) {
			return false;
		}

		return $translation_data[ $locale ];
	}


	/**
	 *
	 *
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function get_translation_set( $force = false ) {

		if ( $force ) {
			$this->get_translation_data();
		}

		$locale = $this->get_locale();

		return get_transient( '_mailster_translation_set_' . $locale );
	}


	/**
	 * Get the locale of the site.
	 */
	public function get_locale() {

		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'mailster' );

		return $locale;
	}


	/**
	 * Get the updates from the database
	 *
	 * @return array
	 */
	private function get_updates() {

		return get_option( 'mailster_translation', array() );
	}


	/**
	 *
	 * Collect all translations we need to download
	 *
	 * @param boolean $force (optional)
	 * @return array
	 */
	public function get_translation_data( $force = false ) {

		global $wpdb;

		// site langguage
		$site_language = get_locale();

		// get all used translations on this site
		$translations = $wpdb->get_col( "SELECT DISTINCT meta_value FROM $wpdb->usermeta WHERE meta_key = 'locale' AND meta_value != ''" );

		// get the site language from the options
		if ( $wplang = get_option( 'WPLANG' ) ) {
			$translations[] = $wplang;
		}

		// merge with site language
		$translations = array_unique( array_merge( array( $site_language ), $translations ) );

		$cache_time = DAY_IN_SECONDS;

		// get all available sets (via cache or API call)
		if ( $force || false === ( $translation_sets = get_transient( 'mailster_translation_data' ) ) ) {
			$url      = $this->endpoint . '/api/projects/mailster';
			$response = wp_remote_get( $url );
			$code     = wp_remote_retrieve_response_code( $response );
			if ( $code != 200 ) {
				return array();
			}
			$body             = wp_remote_retrieve_body( $response );
			$body             = json_decode( $body );
			$translation_sets = $body->translation_sets;
			set_transient( 'mailster_translation_data', $translation_sets, $cache_time );
		}

		$updates = array();

		foreach ( $translations as $locale ) {

			$file            = 'mailster-' . $locale;
			$translation_set = null;
			$lastmodified    = 0;
			$location        = WP_LANG_DIR . '/plugins';
			$package         = $this->endpoint . '/api/get/mailster/' . $locale;
			$mo_file         = trailingslashit( $location ) . $file . '.mo';
			$filemtime       = file_exists( $mo_file ) ? filemtime( $mo_file ) : 0;
			$base_locale     = preg_replace( '/([a-z]+)_([A-Z]+)_(.*)/', '$1_$2', $locale );
			$root_locale     = preg_replace( '/([a-z]+)_([A-Z]+)/', '$1', $base_locale );

			// get all the sets we need
			foreach ( $translation_sets as $set ) {

				if ( ! isset( $set->wp_locale ) ) {
					$set->wp_locale = $set->locale;
				}
				// as a fallback we use the root locale
				if ( $set->locale == $root_locale ) {
					$translation_set = $set;
					$lastmodified    = strtotime( $set->last_modified );
				}
				// another fallback
				if ( $set->wp_locale == $base_locale ) {
					$translation_set = $set;
					$lastmodified    = strtotime( $set->last_modified );
				}
				// this is what we really need
				if ( $set->wp_locale == $locale ) {
					$translation_set = $set;
					$lastmodified    = strtotime( $set->last_modified );
					break;
				}
			}

			// no translateion set found
			if ( ! $translation_set ) {
				continue;
			}

			if ( ! function_exists( 'wp_get_available_translations' ) ) {
				require ABSPATH . '/wp-admin/includes/translation-install.php';
			}
			$translations                 = wp_get_available_translations();
			$translation_set->native_name = isset( $translations[ $translation_set->wp_locale ] ) ? $translations[ $translation_set->wp_locale ]['native_name'] : $translation_set->name;

			set_transient( '_mailster_translation_set_' . $locale, $translation_set, $cache_time );

			// only add if we have a newer version
			if ( $lastmodified - $filemtime > 0 ) {
				$updates[ $locale ] = array(
					'type'       => 'plugin',
					'slug'       => 'mailster',
					'language'   => $locale,
					'version'    => MAILSTER_VERSION,
					'updated'    => date( 'Y-m-d H:i:s', $lastmodified ),
					'current'    => $filemtime ? date( 'Y-m-d H:i:s', $filemtime ) : false,
					'package'    => $package,
					'autoupdate' => true,
				);
			}
		}

		update_option( 'mailster_translation', $updates );

		return $updates;
	}


	/**
	 *
	 *
	 * @param unknown $new
	 */
	public function on_activate( $new ) {

		if ( ! $new ) {
			return;
		}

		try {
			$this->re_check();
			$this->download_language();
			mailster( 'settings' )->define_texts( true );

			// convert some settings with text
			$default_settings = mailster( 'settings' )->get_defaults();
			foreach ( array( 'slugs', 'tags' ) as $key ) {
				if ( isset( $default_settings[ $key ] ) ) {
					mailster_update_option( $key, $default_settings[ $key ] );
				}
			}
		} catch ( Exception $e ) {
		}
	}


	public function re_check() {
		$this->get_translation_data();
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function download_language() {

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		$upgrader = new Language_Pack_Upgrader( new Automatic_Upgrader_Skin() );

		// add filters to only load Mailster translations
		add_filter( 'site_transient_update_plugins', array( &$this, 'site_transient_update_plugins' ) );
		add_filter( 'site_transient_update_themes', array( &$this, 'site_transient_update_themes' ) );
		$result = $upgrader->bulk_upgrade();
		remove_filter( 'site_transient_update_plugins', array( &$this, 'site_transient_update_plugins' ) );
		remove_filter( 'site_transient_update_themes', array( &$this, 'site_transient_update_themes' ) );

		if ( ! empty( $result[0] ) ) {

			$this->load();
			return true;

		}

		return false;
	}


	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function site_transient_update_plugins( $value ) {

		// clear so we only load Mailster translations
		if ( isset( $value->translations ) ) {
			$value->translations = array();
		}

		return $this->update_plugins_filter( $value );
	}

	/**
	 *
	 *
	 * @param unknown $value
	 * @return unknown
	 */
	public function site_transient_update_themes( $value ) {

		// clear so we only load Mailster translations
		if ( isset( $value->translations ) ) {
			$value->translations = array();
		}
		return $value;
	}
}
