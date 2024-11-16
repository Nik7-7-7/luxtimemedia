<?php
/**
 * Marcel Santing, Really Simple Plugins
 *
 * This PHP file contains the implementation of the [Class Name] class.
 *
 * @author Marcel Santing
 * @company Really Simple Plugins
 * @email marcel@really-simple-plugins.com
 * @package RSSSL\Pro\Security\WordPress\limitlogin
 */

namespace RSSSL\Pro\Security\WordPress\Limitlogin;
require_once rsssl_path . '/lib/admin/class-helper.php';
use RSSSL\lib\admin\Helper;
require_once __DIR__ . '/../../../security/wordpress/limitlogin/class-rsssl-country-detection.php';
use Exception;
use GeoIp2\Database\Reader;
use InvalidArgumentException;
use MaxMind\Db\Reader\InvalidDatabaseException;
use PharData;

use RuntimeException;


/**
 * Rsssl_Geo_Location Class
 *
 * This class provides functionalities related to geolocation.
 * It utilizes the geoip2/geoip2 library for GeoIP functionalities.
 *
 * # geoip2/geoip2 library is provided by MaxMind.
 * # License: Apache-2.0
 * # Details & Documentation: [https://github.com/maxmind/GeoIP2-php](https://github.com/maxmind/GeoIP2-php)
 * # Author: Gregory J. Oschwald
 * Email: goschwald@maxmind.com
 * Homepage: [https://www.maxmind.com/](https://www.maxmind.com/)
 *
 * @since 7.0.1
 * @package RSSSLPRO\Security\WordPress\LimitLogin
 */
class Rsssl_Geo_Location {
	use Helper;
	/**
	 * The URL of the GeoIP database file.
	 *
	 * @var string The URL of the GeoIP database file.
	 */
	private $geo_ip_database_file_url = 'https://downloads.really-simple-security.com/maxmind/GeoLite2-Country.md5';

	/**
	 * The country detector that utilizes MaxMind.
	 *
	 * @var Rsssl_Country_Detection $country_detector The country detector that utilizes MaxMind.
	 */
	public $country_detector;


	/**
	 * Rsssl_Geo_Location constructor.
	 */
	public function __construct() {
		$this->init();
		add_filter( 'rsssl_notices', array( $this, 'check_maxmind_database' ) );
		// Initialize the Rsssl_Country_Detection with the path to the GeoIP database.
		if ( ! $this->country_detector && $this->validate_geo_ip_database()  ) {
			$this->country_detector = new Rsssl_Country_Detection( get_site_option( 'rsssl_geo_ip_database_file' ) );
		}
	}

	/**
	 * Check if the MaxMind GeoIP database is installed.
	 *
	 * @param  array $notices  The notices array to which the error message will be added.
	 *
	 * @return array The updated notices array.
	 */
	public function check_maxmind_database( array $notices ): array {
        $last_check = get_site_option( 'rsssl_geo_ip_database_error_lock', false );

		if ( ! $this->validate_geo_ip_database() && ( $last_check && $last_check > strtotime( '-240 seconds' ))) {
			$notice                                     = $this->create_geo_notice(
				__( 'MaxMind GeoIP database not installed', 'really-simple-ssl' ),
				sprintf(
					__( "You have enabled GEO IP, but the GEO IP database hasn't been downloaded automatically. If you continue to see this message, download the file from %1\$sReally Simple Security CDN%2\$s, unzip it, and put it in the %3\$s folder in your WordPress uploads directory", 'really-simple-ssl' ),
					'<a href="https://downloads.really-simple-security.com/maxmind/GeoLite2-Country.tar.gz">',
					'</a>',
					'/uploads/really-simple-ssl/geo-ip'
				),
				'warning',
				'warning'
			);
			$notices['max_mind_database_not_available'] = $notice;
		}

      return $notices;
	}

	/**
	 * Creates a captcha notice array.
	 *
	 * This method creates and returns an array representing a captcha notice.
	 *
	 * @param  string $title  The title of the notice.
	 * @param  string $msg  The message of the notice.
	 * @param  string $icon  The icon class for the notice.
	 * @param  string $type  The type of the notice.
	 *
	 * @return array The captcha notice array.
	 */
	private function create_geo_notice( string $title, string $msg, string $icon, string $type ): array {
		return array(
			'callback'          => '_true_',
			'score'             => 1,
			'show_with_options' => array( 'enable_firewall', 'enable_limited_login_attempts' ),
			'output'            => array(
				'true' => array(
					'title'              => $title,
					'msg'                => $msg,
					'icon'               => $icon,
					'type'               => $type,
					'dismissible'        => false,
					'admin_notice'       => false,
					'plusone'            => true,
					'highlight_field_id' => 'enable_limited_login_attempts',
				),
			),
		);
	}

	/**
	 * Initializes the Rsssl_Geo_Location class.
	 *
	 * @return void Initializes the Rsssl_Geo_Location class.
	 */
	public function init(): void {
		add_action( 'rsssl_uninstall_country_table', array( $this, 'delete_old_country_datatable' ), 10, 0 );
		add_action( 'rsssl_month_cron', array( $this, 'get_geo_ip_database_file' ) );

		if ( is_admin() && rsssl_user_can_manage() ) {
			// Schedule a single event to run in 1 hour if the database file does not exist.
			if ( ! $this->validate_geo_ip_database() && ! wp_next_scheduled( 'rsssl_geo_ip_database_file' ) ) {
				wp_schedule_single_event( time() + 3600, 'rsssl_geo_ip_database_file' );
			}
		}
	}

	/**
	 * Remove the geo ip database file if it exists.
	 */
	public static function remove_geoip_database_file(): void {
		global $wp_filesystem;
		require_once ABSPATH . '/wp-admin/includes/file.php';
		WP_Filesystem();

		if ( rsssl_get_option( 'enable_limited_login_attempts' ) || rsssl_get_option( 'enable_firewall' ) ) {
			return;
		}

		$filename = get_site_option( 'rsssl_geo_ip_database_file' );

		if ( $filename && $wp_filesystem->exists( $filename ) ) {
			$wp_filesystem->delete( $filename );
		}

		delete_site_option( 'rsssl_geo_ip_database_file' );
	}

	/**
	 * Delete the old country datatable.
	 *
	 * @return void
	 */
	public function delete_old_country_datatable(): void {
		global $wpdb;
		$table_name = $wpdb->prefix . 'rsssl_country';
		// Executes a query to drop the table if it exists.
		// phpcs:ignore
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
	}


	/**
	 * Removes all dependencies.
	 *
	 * @return void
	 */
	public static function down(): void {
		self::remove_geoip_database_file();
	}

	/**
	 * Get the county by IP address.
	 *
	 * @param  string $ip  The IP address to get the county for.
	 *
	 * @return string  The county corresponding to the IP address, or 'N/A' if the IP address is not valid.
	 */
	public static function get_county_by_ip( string $ip ): string {
		$self = new self;
		if ( $self->country_detector === null ) {
			return 'N/A';
		}
		try {
			return $self->country_detector->get_country_by_ip( $ip );
		} catch ( InvalidArgumentException $e ) {
			// Log error if the IP address provided was not valid.
			return 'N/A';
		}
	}

	/**
	 * Checks if the GeoIP database file exists.
	 *
	 * @return bool
	 */
	public function validate_geo_ip_database(): bool {
		//retry once a day if not existing
		if ( !file_exists( get_site_option( 'rsssl_geo_ip_database_file' ) ) ) {
			$this->get_geo_ip_database_file( true );
		}

		if ( file_exists( get_site_option( 'rsssl_geo_ip_database_file' ) ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if the provided IP address is in the provided IP range.
	 *
	 * @return string
	 */
	public static function get_country_by_iso2(): string {
		return $country->country_name ?? '';
	}

	/**
	 * Get the Geo IP database file.
	 *
	 * @param  bool $retry  Whether to renew the database file. Default true.
	 *
	 */
	public function get_geo_ip_database_file( bool $retry = false ): void {
		//if retry is true, do one attempt to download it, and store in an option to prevent duplicate attempts
		if ( $retry ) {
			$last_attempt = get_site_option( 'rsssl_geo_ip_database_last_attempt', false );
			update_site_option( 'rsssl_geo_ip_database_last_attempt', time() );
			if ( $last_attempt && $last_attempt > strtotime( '-1 day' ) ) {
				return;
			}
		}

		//if the file exists, delete it first.
		//this way, the file can get updated to the latest version, and if corrupt, will get replaced with a fresh one.
		if ( file_exists( get_site_option( 'rsssl_geo_ip_database_file', false ) ) ) {
			unlink( get_site_option( 'rsssl_geo_ip_database_file' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$upload_dir       = $this->set_upload_dir( 'geo_ip' );
		$zip_file_name    = $upload_dir . 'GeoLite2-Country.tar.gz';
		$hash_file_name    = $upload_dir . 'GeoLite2-Country.md5';

		$tar_file_name    = str_replace( '.gz', '', $zip_file_name );
		$result_file_name = str_replace( '.tar.gz', '.mmdb', 'GeoLite2-Country.tar.gz' );
		$unzipped         = $upload_dir . $result_file_name;

		$response = wp_remote_get( $this->geo_ip_database_file_url, array( 'timeout' => 250 ) );


        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$error_message = is_wp_error( $response ) ? $response->get_error_message() : 'Failed to download file.';
			$this->log("Failed to download file: $error_message");
		}
		WP_Filesystem();
		global $wp_filesystem;

        if ($this->checkLockFile($upload_dir)) {
            $error_message = 'A file extraction operation is already in progress.';
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($error_message);
            }
            return;
        }
        $this->createLockFile($upload_dir);
		try {
			$body = wp_remote_retrieve_body( $response );

			$wp_filesystem->put_contents( $hash_file_name, $body );

            $expected_hash = $this->get_checksum($hash_file_name);

            //rename the file to the correct name
            $wp_filesystem->move($hash_file_name, $zip_file_name);
            $file_contents = file_get_contents($zip_file_name);
            // Search for the hash part in the file and replace it with ''
            $file_contents_cleaned = str_replace('#rsssl-hash#'.$expected_hash, '', $file_contents);

            $wp_filesystem->put_contents($zip_file_name, $file_contents_cleaned);

            // Get the actual hash of the downloaded file
            $actual_hash = hash_file('md5',$zip_file_name);

            // Compare hashes to verify integrity
            if ($actual_hash !== $expected_hash) {
                $error_message = 'File integrity check failed. Downloaded file is corrupted.';
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log($error_message);
                }
                // Stop further processing
                return;
            }

			$this->extract_tar_gz_file( $zip_file_name, $tar_file_name, $upload_dir );
			$this->copy_unzipped_file( $upload_dir, $result_file_name );

            // Checking if the file can be accessed by MaxMind.
            if (!self::check_database_maxmind( $unzipped )) {
                $this->removeLockFile($upload_dir);
                //Stop processing if the file is invalid
                return;
            }

			$wp_filesystem->delete( $zip_file_name );
			$wp_filesystem->delete( $tar_file_name );
			if ( $wp_filesystem->exists( $unzipped ) ) {
				update_site_option( 'rsssl_geo_ip_database_file', $unzipped );
				delete_site_option('rsssl_geo_ip_database_last_attempt');
			}
		} catch ( Exception $e ) {
			$this->log($e->getMessage());
            $this->removeLockFile($upload_dir);
            // Add an option with a current time stamp.
            update_option('rsssl_geo_ip_database_error_lock', time(), false);
			if ( defined('WP_DEBUG') && WP_DEBUG ) {
				error_log($e->getMessage());
			}
		}

        $this->removeLockFile($upload_dir);
	}

    /**
     * Get the path of the lock file.
     *
     * @return string The path of the lock file.
     */
    private function getLockFilePath(string $upload_dir): string
    {
        return $upload_dir . 'file.lock';
    }

    /**
     * Create a lock file.
     *
     * @return void Whether the lock file was successfully created or not.
     */
    private function createLockFile(string $upload_dir): void
    {
        // making sure if there is one, it is removed first
        $this->removeLockFile($upload_dir);
        touch($this->getLockFilePath($upload_dir));
    }

    /**
     * Remove the lock file.
     *
     * @return void  True if the lock file is successfully removed, false otherwise.
     */
    private function removeLockFile(string $upload_dir): void
    {
        if (file_exists($this->getLockFilePath($upload_dir))) {
            unlink($this->getLockFilePath($upload_dir));
        }
    }

    /**
     * Check if the lock file exists and its age is less than 60 seconds.
     *
     * @return bool  True if the lock file exists and its age is less than 60 seconds, false otherwise.
     */
    private function checkLockFile(string $upload_dir ): bool
    {
        $lockFile = $this->getLockFilePath($upload_dir);
        if (file_exists($lockFile)) {
            $fileAge = time() - filemtime($lockFile);
            return $fileAge < 60;
        }
        return false;
    }

	/**
	 * Extract a tar.gz file.
	 *
	 * @param  string $zip_file_name  The tar.gz file to extract.
	 * @param  string $tar_file_name  The tar file name.
	 * @param  string $upload_dir  The upload directory.
	 *
	 */
	public function extract_tar_gz_file( string $zip_file_name, string $tar_file_name, string $upload_dir ): void {
		global $wp_filesystem;
		// Check if the PharData class and the required extension are available.
		if ( ! class_exists( 'PharData' ) || ! extension_loaded( 'phar' ) ) {
			return;
		}

		try {
			$phar = new PharData( $zip_file_name );
			$wp_filesystem->delete( $tar_file_name );
			// phpcs:ignore
			@$phar->decompress(); // We ignore the warning, since it never gives an issue but can cause invalid headers.

			$phar = new PharData( $tar_file_name );
			$phar->extractTo( $upload_dir, null, true );
		} catch ( Exception $e ) {
			$this->log($e->getMessage() );
		}

		// Mark the operation as completed successfully.
		update_option( 'rsssl_phar_decompress_status', 'completed', false );
	}

	/**
	 * Copy the unzipped file.
	 *
	 * @param  string $upload_dir  The upload directory.
	 * @param  string $result_file_name  The result file name.
	 */
	private function copy_unzipped_file( string $upload_dir, string $result_file_name ): void {
		global $wp_filesystem;

		foreach ( glob( $upload_dir . '*' ) as $file ) {
			if ( is_dir( $file ) ) {
				copy( trailingslashit( $file ) . $result_file_name, $upload_dir . $result_file_name );
				// We delete all the files in the directory.
				$wp_filesystem->delete( $file, true );
			}
		}
	}

	/**
	 * Sets an upload path for the GeoIP database file.
	 *
	 * @param  string $path  The path to set.
	 *
	 * @return string
	 */
	public function set_upload_dir( string $path ): string {
		global $wp_filesystem;
		WP_Filesystem();

		$wp_upload_dir = wp_upload_dir();
		$upload_dir    = $wp_upload_dir['basedir'] . '/really-simple-ssl/';
		// If the directory does not exist, we create it.
		if ( ! $wp_filesystem->exists( $upload_dir ) ) {
			$wp_filesystem->mkdir( $upload_dir, 0755 );
		}

		$upload_dir .= $path;

		if ( ! $wp_filesystem->exists( $upload_dir ) ) {
			$wp_filesystem->mkdir( $upload_dir, 0755 );
		}

		return trailingslashit( $upload_dir );
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
		try {
			// Instantiate the 'Rsssl_Country_Detection' class.
			// Use the 'get_country_by_ip' method from the 'Rsssl_Country_Detection' class.
			return ( new Rsssl_Country_Detection( $file ) )->get_country_by_ip( $ip );
		} catch ( Exception $e ) {
			// If any error occurs, return 'N/A'.
			return 'N/A';
		}
	}

    /**
     * Get the checksum value of a file.
     *
     * @param string $zip_file_name The name of the file to calculate the checksum for.
     *
     * @return string  The checksum value.
     *
     */
    private function get_checksum(string $hash_file_name): string
    {
        $contents = file_get_contents($hash_file_name);

        //The strpos function is used to find the position of the first occurrence of '#rsssl-hash#' in the file
        $start = strpos($contents, '#rsssl-hash#');

        if($start === false) {
            // '#rsssl-hash#' not found in file
            return '';
        }

        return substr($contents, $start + 12);
    }

    /**
     * Checks the validity of the GeoIP database file.
     *
     * @param string $dbfile The path to the GeoIP database file.
     *
     * @return bool
     */
    public static function check_database_maxmind(string $dbfile ): bool
    {
        if ( ! defined( 'GEOIP_DETECT_VERSION' ) ) {
            require_once __DIR__ . '/../../../assets/vendor/autoload.php';
        }
        global $wp_filesystem;
        try {
            // This creates the Reader object, which should point to your GeoLite2 database (e.g., GeoLite2-City.mmdb)
            $reader = new Reader($dbfile);
            // Use the reader on some IP address
            $record = $reader->country('128.101.101.101');

            // If there is no exception thrown at this point, meaning the database is in working order
            // Process the $record or do something else
        } catch (InvalidDatabaseException $e) {
            // If an exception is thrown, the database is invalid
            // delete the file, as it is invalid
            $wp_filesystem->delete($dbfile);
            // Log the error message
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($e->getMessage());
            }
            return false;
        } catch (Exception $e) {
            // delete the file, as it is invalid
            $wp_filesystem->delete($dbfile);
            // Log the error message
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log($e->getMessage());
            }
            return false;
        }
        return true;
    }
}
