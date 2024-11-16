<?php

/**
 * Class Rsssl_Country_Detection
 *
 * The Rsssl_Country_Detection class is responsible for detecting the country of a given IP address using a GeoIP database.
 *
 * @package Really_Simple_SSL_PRO
 * @subpackage Security\WordPress\Limitlogin
 * @since 7.3.0
 * @category Class
 * @author Really Simple Security
 */

namespace RSSSL\Pro\Security\WordPress\Limitlogin;

if ( ! defined( 'GEOIP_DETECT_VERSION' ) ) {
	require_once __DIR__ . '/../../../assets/vendor/autoload.php';
}
use GeoIp2\Database\Reader;
use Exception;
use MaxMind\Db\Reader\InvalidDatabaseException;

/**
 * Class Rsssl_Country_Detection
 *
 * The Rsssl_Country_Detection class is responsible for detecting the country of a given IP address using a GeoIP database.
 */
class Rsssl_Country_Detection {
	/**
	 * The GeoIP database reader.
	 *
	 * @var Reader
	 */
	private $reader;

	/**
	 * Constructs a new instance of the class.
	 *
	 * @param  string $database_path  The path to the GeoIP database file.
	 *
	 */
	public function __construct( string $database_path ) {
		try {
			$this->reader = new Reader( $database_path );
		} catch ( Exception $e ) {
			//retry attempt
			$geo_location = new Rsssl_Geo_Location();
			$geo_location->get_geo_ip_database_file(true);
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Really Simple Security: Failed to initialize the GeoIP database reader.' );
			}
		}
	}


	/**
	 * Retrieves the ISO country code for the specified IP address.
	 *
	 * @param  string $ip  The IP address for which to retrieve the country code.
	 *
	 * @return string The ISO country code if found, or 'N/A' if not found or an error occurred.
	 *
	 */
	public function get_country_by_ip( string $ip ): string {
		// If there is an cidr notation, we need to remove it.
		$ip = explode( '/', $ip )[0];

		$sanitized_ip = filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6 );

		if ( false === $sanitized_ip ) {
			return '';
		}

		if ( !$this->reader ) {
			return '';
		}

		try {
			$record = $this->reader->country( $sanitized_ip );
			return $record->country->isoCode ?? 'N/A';
		} catch ( Exception $e ) {
			// Returning an empty string or 'N/A' is a better approach than throwing an exception.
			return 'N/A';
		}
	}

	/**
	 * Get the country code by IP address using HTTP headers.
	 *
	 * @param  string $file  The geoip2 database file.
	 * @param  string $ip  The IP address to retrieve the country code of.
	 *
	 * @return string The ISO code of the country associated with the IP address. If the code cannot be fetched, 'N/A' is returned.
	 */
	public static function get_country_by_ip_headers( string $file, string $ip ): string {
		// Sanitize the IP.
		$ip = filter_var( $ip, FILTER_VALIDATE_IP );

		if ( false === $ip ) {
			return '';
		}
		return ( new self( $file ) )->get_country_by_ip( $ip );
	}
}
