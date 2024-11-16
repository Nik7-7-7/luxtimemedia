<?php

use GeoIp2\Database\Reader;


class MailsterGeo {

	private $zip = 'https://static.mailster.co/geo/GeoLite2-City.zip';

	private $reader;

	public function __construct() {

		add_action( 'mailster_location_update', array( &$this, 'maybe_update' ) );
		add_action( 'mailster_cron', array( &$this, 'maybe_set_cron' ) );
	}


	/**
	 *
	 *
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function maybe_update( $force = false ) {

		if ( mailster_option( 'track_location' ) ) {
			return $this->update( $force );
		} else {
			$this->clear_cron();
		}
		return false;
	}


	public function maybe_set_cron() {

		if ( mailster_option( 'track_location' ) ) {
			if ( mailster_option( 'track_location_update' ) ) {
				$this->set_cron( 'weekly' );
			}
		} else {
			$this->clear_cron();
		}
	}

	public function get_record( $ip ) {

		if ( ! $this->reader ) {
			$file = $this->get_file_path();
			if ( ! file_exists( $file ) ) {
				return false;
			}

			$this->reader = new Reader( $file );
		}
		try {

			$record = $this->reader->city( $ip );

		} catch ( Exception $e ) {
			return false;
		}

		return $record;
	}


	public function get_country_for_ip( $ip, $local = null ) {

		$record = $this->get_record( $ip );

		if ( ! $record ) {
			return 'unknown';
		}

		if ( ! empty( $local ) ) {
			$local = substr( $local, 0, 2 );

			if ( isset( $record->country->names[ $local ] ) ) {
				return $record->country->names[ $local ];
			}
		}

		return $record->country->name;
	}

	public function get_country_code_for_ip( $ip ) {

		$record = $this->get_record( $ip );

		if ( ! $record ) {
			return 'unknown';
		}

		return $record->country->isoCode;
	}



	public function get_city_for_ip( $ip ) {

		$record = $this->get_record( $ip );

		if ( ! $record ) {
			return 'unknown';
		}

		return $record->city->name;
	}


	public function set_cron( $type = 'single' ) {

		if ( wp_next_scheduled( 'mailster_location_update' ) ) {
			return;
		}
		switch ( $type ) {
			case 'single':
				wp_schedule_single_event( time(), 'mailster_location_update' );
				break;
			case 'weekly':
				wp_schedule_event( time(), 'weekly', 'mailster_location_update' );
				break;
		}
	}


	public function clear_cron() {
		if ( wp_next_scheduled( 'mailster_location_update' ) ) {
			wp_clear_scheduled_hook( 'mailster_location_update' );
		}
	}



	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_file_path() {

		$folder = apply_filters( 'mailster_location_db_folder', MAILSTER_UPLOAD_DIR );

		return apply_filters( 'mailster_location_db_file_city', trailingslashit( $folder ) . 'geo/GeoLite2-City.mmdb' );
	}


	/**
	 *
	 *
	 * @param unknown $force (optional)
	 * @return unknown
	 */
	public function update( $force = false ) {

		$file = $this->get_file_path();

		$filemtime = file_exists( $file ) ? filemtime( $file ) : 0;

		if ( ! $filemtime || $force ) {
			$do_renew = true;
		} else {
			$response = wp_remote_get( $this->zip, array( 'method' => 'HEAD' ) );
			$headers  = wp_remote_retrieve_headers( $response );

			// check header
			if ( ! isset( $headers['content-type'] ) || $headers['content-type'] != 'application/zip' ) {
				return new WP_Error( 'wrong_filetype', 'wrong file type' );
			}
			$lastmodified = strtotime( $headers['last-modified'] );
			$do_renew     = $lastmodified - $filemtime > 0;
		}

		if ( $do_renew ) {

			$wp_filesystem = mailster_require_filesystem();
			set_time_limit( 120 );

			if ( ! function_exists( 'download_url' ) ) {
				include ABSPATH . 'wp-admin/includes/file.php';
			}

			// download
			$tempfile = download_url( $this->zip );

			if ( is_wp_error( $tempfile ) ) {
				return $tempfile;
			}

			// create directory
			mailster( 'helper' )->mkdir( dirname( $file ), true );

			// unzip package
			if ( is_wp_error( unzip_file( $tempfile, dirname( $file ) ) ) ) {
				$wp_filesystem->delete( $tempfile, true );
				return new WP_Error( 'unzip', esc_html__( 'Unable to unzip template', 'mailster' ) );
			}

			// Delete the temporary file.
			$wp_filesystem->delete( $tempfile, true );

		}

		return file_exists( $file );
	}


	/**
	 *
	 *
	 * @param unknown $code
	 * @return unknown
	 */
	public function code2Country( $code ) {

		// indicator for continent
		if ( 0 === strpos( $code, '_' ) ) {
			$continents = $this->get_continents( true );
			return isset( $continents[ $code ] ) ? $continents[ $code ] : 'unknown';
		}

		$countries = $this->get_countries();
		if ( isset( $countries[ $code ] ) ) {
			return $countries[ $code ];
		}

		return 'unknown';
	}


	/**
	 *
	 *
	 * @param unknown $sorted
	 * @param unknown $european_union
	 * @return unknown
	 */
	public function get_countries( $sorted = false, $european_union = false ) {

		include MAILSTER_DIR . 'includes/countries.php';

		if ( ! $sorted ) {
			return $countries;
		}
		asort( $countries );
		$continents = $this->get_continents( $european_union );

		$sorted = array();

		foreach ( $continents as $continent_code => $name ) {
			$sorted[ $name ] = array_intersect_key( $countries, array_flip( $this->get_continent_members( $continent_code ) ) );
		}

		return $sorted;
	}


	/**
	 *
	 *
	 * @param unknown $european_union
	 * @return unknown
	 */
	public function get_continents( $european_union = false ) {

		$continents = array(
			'_EU' => esc_html__( 'Europe', 'mailster' ),
			'_AS' => esc_html__( 'Asia/Pacific Region', 'mailster' ),
			'_NA' => esc_html__( 'North America', 'mailster' ),
			'_SA' => esc_html__( 'South America', 'mailster' ),
			'_AF' => esc_html__( 'Africa', 'mailster' ),
			'_OC' => esc_html__( 'Oceania/Australia', 'mailster' ),
		);

		if ( $european_union ) {
			$continents['_EN'] = esc_html__( 'European Union', 'mailster' );
		}

		asort( $continents );

		return $continents;
	}


	/**
	 *
	 *
	 * @param unknown $continent
	 * @return unknown
	 */
	public function get_continent_members( $continent ) {

		switch ( $continent ) {
			case '_EN':
				return array( 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK' );
			case '_EU':
				return array( 'AL', 'AD', 'AT', 'BY', 'BE', 'BA', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FO', 'FI', 'FR', 'DE', 'GI', 'GR', 'HU', 'IS', 'IE', 'IM', 'IT', 'RS', 'LV', 'LI', 'LT', 'LU', 'MK', 'MT', 'MD', 'MC', 'ME', 'NL', 'NO', 'PL', 'PT', 'RO', 'RU', 'SM', 'RS', 'SK', 'SI', 'ES', 'SE', 'CH', 'UA', 'GB', 'VA', 'RS' );
			case '_AS':
				return array( 'AF', 'AM', 'AZ', 'BH', 'BD', 'BT', 'BN', 'KH', 'CN', 'CX', 'CC', 'IO', 'GE', 'HK', 'IN', 'ID', 'IR', 'IQ', 'IL', 'JP', 'JO', 'KZ', 'KW', 'KG', 'LA', 'LB', 'MO', 'MY', 'MV', 'MN', 'MM', 'NP', 'KP', 'OM', 'PK', 'PS', 'PH', 'QA', 'SA', 'SG', 'KR', 'LK', 'SY', 'TW', 'TJ', 'TH', 'TR', 'TM', 'AE', 'UZ', 'VN', 'YE' );
			case '_NA':
				return array( 'AI', 'AG', 'AW', 'BS', 'BB', 'BZ', 'BM', 'BQ', 'VG', 'CA', 'KY', 'CR', 'CU', 'CW', 'DM', 'DO', 'SV', 'GL', 'GD', 'GP', 'GT', 'HT', 'HN', 'JM', 'MQ', 'MX', 'PM', 'MS', 'CW', 'KN', 'NI', 'PA', 'PR', 'BQ', 'BQ', 'SX', 'KN', 'LC', 'PM', 'VC', 'TT', 'TC', 'US', 'VI' );
			case '_SA':
				return array( 'AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'GY', 'PY', 'PE', 'SR', 'UY', 'VE' );
			case '_AF':
				return array( 'DZ', 'AO', 'SH', 'BJ', 'BW', 'BF', 'BI', 'CM', 'CV', 'CF', 'TD', 'KM', 'CG', 'CD', 'DJ', 'EG', 'GQ', 'ER', 'SZ', 'ET', 'GA', 'GM', 'GH', 'GN', 'GW', 'CI', 'KE', 'LS', 'LR', 'LY', 'MG', 'MW', 'ML', 'MR', 'MU', 'YT', 'MA', 'MZ', 'NA', 'NE', 'NG', 'ST', 'RE', 'RW', 'ST', 'SN', 'SC', 'SL', 'SO', 'ZA', 'SS', 'SH', 'SD', 'SZ', 'TZ', 'TG', 'TN', 'UG', 'CD', 'ZM', 'TZ', 'ZW' );
			case '_OC':
				return array( 'AS', 'AU', 'NZ', 'CK', 'TL', 'FM', 'FJ', 'PF', 'GU', 'KI', 'MP', 'MH', 'UM', 'NR', 'NC', 'NZ', 'NU', 'NF', 'PW', 'PG', 'MP', 'WS', 'SB', 'TK', 'TO', 'TV', 'VU', 'UM', 'WF' );
		}

		return array();
	}
}
