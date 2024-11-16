<?php
/**
 * Really Simple Security
 * Download the file with the files with wrong permissions
 *
 * @package really-simple-ssl
 * @version 1.0.0
 */
# No need for the template engine
if ( ! defined( 'WP_USE_THEMES' ) ) {
	define( 'WP_USE_THEMES', false ); // phpcs:ignore
}

#find the base path
if ( ! defined( 'BASE_PATH' ) ) {
	define( 'BASE_PATH', rsssl_find_wordpress_base_path() . '/' );
}

# Load WordPress Core
if ( ! file_exists( BASE_PATH . 'wp-load.php' ) ) {
	die( 'WordPress not installed here' );
}

require_once BASE_PATH . 'wp-load.php';
require_once ABSPATH . 'wp-includes/class-phpass.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

if ( rsssl_user_can_manage() ) {
	function rsssl_get_files_output() {
		$output = '';
		global $wpdb;
		$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
		$files = $wpdb->get_results("SELECT file_path FROM $table_name WHERE changed > 0");
		$changed_files = [];
		foreach ( $files as $file ) {
			if ( !file_exists($file->file_path) ) {
				continue;
			}
			$changed_files[] = str_replace(ABSPATH, '', $file->file_path);
		}
		//add to existing array, in case we've done some hash checking during this run.
		if ( count($changed_files)=== 0) {
			$output .= __("No changed files found", "really-simple-ssl") . "\n";
		} else {
			$output .= __("Changed files:", "really-simple-ssl") . "\n";
			foreach ($changed_files as $file) {
				$output .= $file . "\n";
			}
			$output .= __("These files were changed outside a normal plugin, theme or WordPress update. If you have changed them through FTP, or manually, you can ignore this. If you're not sure, please check with your hosting provider for help.", "really-simple-ssl") . "\n";
		}
		return $output;
	}

	$rsssl_content   = rsssl_get_files_output();
	$rsssl_fsize     = function_exists( 'mb_strlen' ) ? mb_strlen( $rsssl_content, '8bit' ) : strlen( $rsssl_content );
	$rsssl_file_name = 'really-simple-ssl-changed-files.txt';

	//direct download
	header( 'Content-type: application/octet-stream' );
	header( 'Content-Disposition: attachment; filename="' . $rsssl_file_name . '"' );
	//open in browser
	//header("Content-Disposition: inline; filename=\"".$file_name."\"");
	header( "Content-length: $rsssl_fsize" );
	header( 'Cache-Control: private', false ); // required for certain browsers
	header( 'Pragma: public' ); // required
	header( 'Expires: 0' );
	header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
	header( 'Content-Transfer-Encoding: binary' );
	echo $rsssl_content;
}

function rsssl_find_wordpress_base_path() {
	$path = __DIR__;

	do {
		if ( file_exists( $path . '/wp-config.php' ) ) {
			//check if the wp-load.php file exists here. If not, we assume it's in a subdir.
			if ( file_exists( $path . '/wp-load.php' ) ) {
				return $path;
			} else {
				//wp not in this directory. Look in each folder to see if it's there.
				if ( file_exists( $path ) && $handle = opendir( $path ) ) { //phpcs:ignore
					while ( false !== ( $file = readdir( $handle ) ) ) {//phpcs:ignore
						if ( '.' !== $file && '..' !== $file ) {
							$file = $path . '/' . $file;
							if ( is_dir( $file ) && file_exists( $file . '/wp-load.php' ) ) {
								$path = $file;
								break;
							}
						}
					}
					closedir( $handle );
				}
			}

			return $path;
		}
	} while ( $path = "$path/.." );

	return false;
}
