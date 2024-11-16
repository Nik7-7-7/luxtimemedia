<?php
defined('ABSPATH') or die();
/**
 * @package Really Simple Security
 * @subpackage RSSSL_FILE_PERMISSIONS
 */
if ( !class_exists("rsssl_file_permissions") ) {
	/**
	 *
	 * Class rsssl_file_permissions
	 * Checks permissions in the file and folder structure on a weekly basis, using cron
	 *
	 */
	class rsssl_file_permissions {
		private $directory_levels = 3;
		private $nr_of_folders_one_batch = 30;
		public $files_with_wrong_permissions = [];
		public $directories = [];
		public $files = [];
		public $files_loaded = false;
		private $extensions = [
			'.php',
			'.js',
			'.css',
		];
		private $excluded_directories = [
			'node_modules',
			'wp-content/plugins/complianz-gdpr-premium/assets/vendor/mpdf/mpdf/tmp',
			'wp-content/uploads/complianz/',
		];

		public function __construct() {
			add_action( "rsssl_weekly_cron", array($this, "run_permission_check" ) );
			add_action( "rsssl_permissions_check_cron", array($this, "run_permission_check" ) );
			add_filter( 'rsssl_notices', array($this,'get_notices_list'), 20, 1 );
			add_filter( 'rsssl_run_fix', array($this,'run_permissions_fix'), 20, 2 );
			add_action( "rsssl_after_save_field", array($this, 'maybe_start_scan'), 100, 4 );
		}
		/**
		 * If the corresponding setting has been changed, check if the scan has run yet. If not, start it.
		 *
		 * @param string $field_id
		 * @param mixed  $field_value
		 * @param mixed  $prev_value
		 * @param string $field_type
		 *
		 * @return void
		 */
		public function maybe_start_scan( string $field_id, $field_value, $prev_value, $field_type ): void {
			if ( !rsssl_user_can_manage() ) {
				return;
			}

			if ( $field_id === 'permission_detection'  ) {
				if ( $field_value && !$prev_value ) {
					//check if we need to start the permissions check.
					$this->run_permission_check();
				}
			}
		}
		/**
		 * Fixes all files and folders with insecure permissions that are stored in the option rsssl_files_with_wrong_permissions
		 * Hooked into the rsssl rest api
		 *
		 * @param array $output
		 * @param string $fix_id
		 *
		 * @return array|mixed
		 */
		public function run_permissions_fix($output, $fix_id) {
			if ( !rsssl_user_can_manage() ) {
				return $output;
			}

			if ( $fix_id === "fix_permissions" ) {
				$files = $this->files_with_wrong_permisions();
				$fixed = 0;
				$failed = 0;
				$limit = 1000;
				if ( count($files) > $limit ) {
					return [
						'request_success' => true,
						'msg' => sprintf(__("Too many files with insecure permissions to fix. Please contact your hosting provider.", "really-simple-ssl"), $fixed, $failed),
						'success' => true
					];
				}

				foreach  ($files as $file ) {

					if ( is_dir( $file ) ) {
						$success = chmod( $file, 0755 );
					} else {
						$success = chmod( $file, 0644 );
					}
					if ( $success ) {
						//remove the file from the array
						$files = array_diff($files, [$file]);
						$fixed++;
					} else {
						$failed++;
					}
				}

				//update the files status
				if ( $fixed > 0 ) {
					update_option('rsssl_files_with_wrong_permissions', $files, false );
				}

				return [
					'request_success' => true,
					'msg' => sprintf(__("Fixing of file permissions completed. Fixed: %d. Failed: %d", "really-simple-ssl"), $fixed, $failed),
					'success' => true
				];
			}
			return $output;
		}

		/**
		 * A typical WordPress setup has about 50 first and second level directories.
		 * Doing this in one week means about 10 each day, so running every hour should be enough to finish in a week.
		 *
		 * @return void
		 */
		public function run_permission_check(){
			if ( !rsssl_admin_logged_in() ) {
				return;
			}

			$dir = ABSPATH;
			$this->directories = $this->get_directories($dir);
			$directory_index = get_option("rsssl_permission_check_next_index", 0);
			$last_completed = get_option('rsssl_permission_check_completed');
			$now = time();
			$can_run_check = $last_completed === false || ($now - (int)  $last_completed) > WEEK_IN_SECONDS;
			//if the directory index was reset or never saved, AND if the last check was one week ago
			if ( !$can_run_check ) {
				wp_clear_scheduled_hook("rsssl_permissions_check_cron");
				return;
			}

			//reset the last completed time
			update_option('rsssl_permission_check_completed', false, false );

			if ( isset($this->directories[$directory_index] ) ) {
				//if we're starting over, reset the files with wrong permissions
				if ( $directory_index === 0 ) {
					update_option('rsssl_files_with_wrong_permissions', [], false );
				}
				for ($i = 0; $i < $this->nr_of_folders_one_batch; $i++) {
					$this->check_permissions($this->directories[$directory_index], true);
					$directory_index++;
					if ( $directory_index >= count($this->directories) ) {
						break;
					}
				}

				update_option("rsssl_permission_check_next_index", $directory_index, false );
				if ( $directory_index < count($this->directories) ) {
					wp_schedule_single_event(time() + 1 * MINUTE_IN_SECONDS , "rsssl_permissions_check_cron");
				} else {
					//completed
					wp_clear_scheduled_hook("rsssl_permissions_check_cron");
					update_option("rsssl_permission_check_next_index", 0, false );
					update_option("rsssl_permission_check_completed", time(), false );

					//as the check is completed, we can send an email if there are files with wrong permissions
					if ( ( count( $this->files_with_wrong_permisions() ) > 0 ) && ! get_transient( 'rsssl_permissions_mail_recently_sent' ) ) {
						$this->send_email();
						set_transient( 'rsssl_permissions_mail_recently_sent', true, DAY_IN_SECONDS );
					}
				}
			} else {
				update_option("rsssl_permission_check_next_index", 0, false );
			}

			if ( count($this->files_with_wrong_permissions) > 0 ) {
				$existing_files = get_option('rsssl_files_with_wrong_permissions', []);
				//add the new found files to this list, checkinf for duplicates
				$this->files_with_wrong_permissions = array_merge($existing_files, $this->files_with_wrong_permissions);
				update_option('rsssl_files_with_wrong_permissions', $this->files_with_wrong_permissions, false );
			}

		}

		/**
		 * Send email about the permissions issue
		 *
		 * @return void
		 */
		private function send_email(): void {
			if ( !class_exists('rsssl_mailer' )){
				require_once( rsssl_path . 'mailer/class-mail.php');
			}

			if ( class_exists('rsssl_mailer')) {
				$block = [
					'title' => __('Insecure file permissions', 'really-simple-ssl'),
					'message' => __('The recurring scan detected insecure file permissions being used for certain files or folders. Navigate to the Really Simple Security dashboard to resolve the issue.','really-simple-ssl'),
					'url' => rsssl_admin_url(),
				];

				$site_url = get_site_url();
				$url = '<a rel="noopener noreferrer" target="_blank" href="'.$site_url.'">'.$site_url.'</a>';

				$mailer          = new rsssl_mailer();
				$mailer->subject = __( 'Security warning: insecure file permissions', 'really-simple-ssl' );
				$mailer->title = __( 'Security warning', 'really-simple-ssl' );
				$mailer->message = sprintf(__( 'This is a security warning from Really Simple Security for %s.', 'really-simple-ssl' ), $url);
				$mailer->warning_blocks[] = $block;
				$mailer->send_mail();
			}
		}

		/**
		 * Get files, cached
		 *
		 * @return array
		 */
		private function files_with_wrong_permisions() {
			if ( ! rsssl_admin_logged_in() ) {
				return [];
			}
			if ( ! $this->files_loaded ) {
				$this->files        = get_option( 'rsssl_files_with_wrong_permissions', [] );
				$this->files_loaded = true;
			}

			return is_array( $this->files ) ? $this->files : [];
		}

		/**
		 * @param string $dir
		 *
		 * @return bool
		 */
		private function should_scan_recursively( string $dir ): bool {
			$root = ABSPATH;
			$dir = str_replace($root, "", $dir);
			$dirs = explode("/", $dir);

			return count( $dirs ) >= $this->directory_levels;
		}

		/**
		 * Check permissions for directories and files, and fix if required.
		 *
		 * @param string $dir
		 * @param bool   $fix
		 *
		 * @return void
		 */
		private function check_permissions( string $dir, bool $fix = false, bool $is_root = true ): void {
			if ( ! rsssl_admin_logged_in() ) {
				return;
			}

			if ( ! is_dir( $dir ) ) {
				return;
			}

			//skip directories or files starting with a .
			if ( strpos( $dir, "/." ) !== false ) {
				return;
			}

			// Check permissions for the directory itself.
			$permissions = substr( sprintf( '%o', fileperms( $dir ) ), - 4 );
			if ( octdec( $permissions ) > octdec( '0755' ) ) {
				$this->files_with_wrong_permissions[] = $dir;
				if ( $fix ) {
					chmod( $dir, 0755 );
				}
			}
			$excluded_directories = apply_filters( 'rsssl_file_scan_exclude_paths', $this->excluded_directories, $dir );
			$extensions           = apply_filters( 'rsssl_file_scan_extensions', $this->extensions );
			// Check permissions for files in the directory.
			$files = scandir( $dir );
			foreach ( $files as $file ) {
				if ( $file !== '.' && $file !== '..' ) {
					$path = trailingslashit( $dir ) . $file;

					// Check if the current path should be excluded
					$skip = false;
					foreach ( $excluded_directories as $excluded_dir ) {
						if ( strpos( $path, $excluded_dir ) !== false ) {
							$skip = true;
							break;
						}
					}

					if ( $skip ) {
						continue;
					}

					if ( is_dir( $path ) ) {
						//directories that are listed separately in the directories array should not be scanned recursively
						if ( $this->should_scan_recursively( $dir ) ) {
							$this->check_permissions( $path, $fix, false ); // Recursively check subdirectories.
						}
					} else {
						//check if this file's extension is included in the $extensions array
						$extension = strrchr( $file, '.' );
						if ( ! in_array( $extension, $extensions, true ) ) {
							continue;
						}
						$permissions = substr( sprintf( '%o', fileperms( $path ) ), - 4 );
						if ( octdec( $permissions ) > octdec( '0644' ) ) {
							$this->files_with_wrong_permissions[] = $path;
							if ( $fix ) {
								chmod( $path, 0644 );
							}
						}
					}
				}
			}
		}

		/**
		 * Get array of all directories
		 *
		 * @param string $dir
		 *
		 * @return array
		 */
		private function get_directories( string $dir, $level = 0 ){
			if ( !rsssl_admin_logged_in() ) {
				return [];
			}

			if ($level === $this->directory_levels) {
				return [];
			}
			$directories = [];

			//skip directories or files starting with a .
			if ( strpos($dir, "/.") !== false ) {
				return [];
			}
			$files = scandir($dir);

			$skip_array = apply_filters('rsssl_file_scan_exclude_paths', $this->excluded_directories, $dir );
			foreach ($files as $file) {
				if ( $file !== '.' && $file !== '..' ) {
					//skip directories where part of the directory occurs in the $skip_array
					$skip = false;
					foreach ($skip_array as $skip_dir) {
						if ( strpos($dir, $skip_dir) !== false ) {
							$skip = true;
							break;
						}
					}

					if ( $skip ) {
						continue;
					}

					//skip files starting with a .
					if ( strpos($file, ".") === 0 ) {
						continue;
					}
					$path = trailingslashit($dir) . $file;
					if ( is_dir($path) ) {
						$directories[] = $path;
						$directories = array_merge($directories, $this->get_directories($path, $level+1 ));
					}
				}
			}

			return $directories;
		}

		/**
		 * Get list of notices for the dashboard
		 *
		 * @param array $notices
		 *
		 * @return array
		 */
		public function get_notices_list( array $notices ): array {
			$completed = get_option('rsssl_permission_check_completed' ) !== false;
			if ( $completed && count($this->files_with_wrong_permisions() )>0 ) {
				$download_link = trailingslashit(rsssl_url).'pro/security/wordpress/permission-detection/download.php';
				$notices['permission_issues'] = array(
					'callback' => '_true_',
					'score' => 10,
					'output' => array(
						'true' => array(
							'title' => __("Insecure file permissions detected.", 'really-simple-ssl'),
							'msg' => sprintf(__(" Insecure file permissions detected on your server. Click “Fix” to let Really Simple Security resolve this, or %sdownload%s the affected files list, to resolve this manually.", 'really-simple-ssl'), '<a rel="noopener noreferrer"  target="_blank" href="'.$download_link.'">', '</a>'),
							'icon' => 'warning',
							'fix_id' => 'fix_permissions',
						),
					),
				);
			}

			return $notices;
		}

	}
}
$permissions = new rsssl_file_permissions();