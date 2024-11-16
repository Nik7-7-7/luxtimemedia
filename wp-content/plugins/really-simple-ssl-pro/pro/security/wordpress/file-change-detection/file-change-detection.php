<?php
defined('ABSPATH') or die();
/**
 * @package Really Simple Security
 * @subpackage RSSSL_FILE_PERMISSIONS
 */
if ( !class_exists("rsssl_file_change_detection") ) {
	/**
	 *
	 * Class rsssl_file_change_detection
	 * Checks permissions in the file and folder structure on a weekly basis, using cron
	 *
	 */
	class rsssl_file_change_detection {

		private $directory_levels = 3;
		private $nr_of_folders_one_batch = 30;
		public $changed_files = [];
		public $directories = [];
		public $files_loaded = false;
		public $default_skip_array = [];
		public $skip_array = [];
		private $extensions = [
			'.php',
			'.js',
		];
		private $file_list = [];
		private $table_exists = [];
		private $wp_plugin_dir = '';
		private $wp_theme_dir = '';

		public function __construct() {
			$this->wp_plugin_dir = trailingslashit( WP_PLUGIN_DIR );
			$this->wp_theme_dir = trailingslashit( get_theme_root() );
			$this->default_skip_array = [
				'node_modules',
			];
			//hash creation is triggered by plugin activation or update, or scheduled by itself
			add_action( "rsssl_hash_creation_cron", array($this, "run_hash_creation" ) );
			add_action( "rsssl_hash_creation_cron_start", array($this, "start_hash_creation_from_cron" ) );
			add_action( 'upgrader_process_complete', array( $this, 'upgrader_process_complete'), 10, 2);
			add_action( 'activated_plugin', array( $this, 'activated_plugin'), 10, 2);

			//change detection runs only on weekly cron, and if scheduled by itself.
			add_action( "rsssl_daily_cron", array($this, "start_change_detection_full" ) );
			add_action( "rsssl_file_change_detection_cron", array($this, "run_change_detection" ) );

			add_filter( 'rsssl_notices', array($this, 'get_notices_list'), 20, 1 );
			add_action( "rsssl_after_save_field", array($this, 'maybe_start_hash_creation'), 100, 4 );
			add_action( "rsssl_install_tables", array($this, 'upgrade_table'), 100, 4 );
			add_filter( 'rsssl_file_scan_exclude_paths', array($this, 'add_custom_excluded_directories'), 10, 1);
			add_filter( 'rsssl_do_action', array( $this, 'handle_rest_call' ), 10, 3 );
		}

		/**
		 * On reset of changed files from react, a cron is scheduled to start it.
		 *
		 * @return void
		 */
		public function start_hash_creation_from_cron(): void {
			$this->run_hash_creation(true);
		}

		/**]
		 * Handle the rest call
		 * @param array  $response
		 * @param string $action
		 * @param        $data
		 *
		 * @return array
		 */
		public function handle_rest_call( array $response, string $action, $data ): array {
			if ( !rsssl_admin_logged_in() ) {
				return $response;
			}
			if ( 'reset_changed_files' === $action ) {
				wp_clear_scheduled_hook("rsssl_hash_creation_cron");
				wp_clear_scheduled_hook("rsssl_file_change_detection_cron");
				$this->reset_files_changed();
				$this->clear_directory_table();
				wp_schedule_single_event(time() + 30 , "rsssl_hash_creation_cron_start");
				$response['data'] = [];
			}
			if ( 'get_changed_files' === $action ) {
				$response['data'] = $this->get_changed_files();
			}
			if ( 'exclude_from_changed_files' === $action ) {
				global $wpdb;
				$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
				$exclude_ids = $data['ids'];
				$exclude_ids = array_map('intval', $exclude_ids);

				//get list of filenames to exclude.
				$exclude_files = [];
				if ( $this->table_exists('rsssl_file_hashes') ) {
					$exclude_files = $wpdb->get_results( "SELECT file_path FROM $table_name WHERE id IN (" . implode( ",", $exclude_ids ) . ")" );
				}
				if ( is_array( $exclude_files ) && count($exclude_files)>0 ) {
					$exclude_files = wp_list_pluck($exclude_files, 'file_path');
					$excluded_directories = rsssl_get_option('file_change_exclusions');
					foreach ( $exclude_files as $exclude_file ) {
						//remove ABSPATH from the file path
						$exclude_file = str_replace(ABSPATH, '', $exclude_file);
						//check if file is already included
						if ( strpos($excluded_directories, $exclude_file ) === false ) {
							$excluded_directories .= "\n$exclude_file";
						}
					}
					rsssl_update_option('file_change_exclusions', $excluded_directories );
				}
				$this->delete_changed_files( $exclude_ids );
				$response['data'] = $this->get_changed_files();
			}
			if ( 'delete_changed_files' ===  $action ) {
				$this->delete_changed_files( $data['ids'] );
				$response['data'] = $this->get_changed_files();;
			}
			return $response;
		}

		/**
		 * Delete changed files
		 * @param array $ids
		 *
		 * @return void
		 */
		private function delete_changed_files( array $ids ): void {
			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
			$delete_ids = array_map('intval', $ids);
			foreach ( $delete_ids as $id ) {
				$wpdb->delete($table_name, ['id' => $id]);
			}
		}

		/**
		 * Get an array of changed files
		 *
		 * @return array
		 */
		private function get_changed_files(): array {
			global $wpdb;
			$files = [];
			if ( $this->table_exists('rsssl_file_hashes') ) {
				$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
				$files = $wpdb->get_results("SELECT id, file_path, changed FROM $table_name WHERE changed IS NOT NULL AND changed <> 0");
			}

			$changed_files = [];
			foreach ( $files as $file ) {
				if ( !file_exists($file->file_path) ) {
					continue;
				}
				$changed_files[] = [
					'id' => $file->id,
					'file'  => str_replace(ABSPATH, '', $file->file_path),
					'changed' => date( get_option('date_format'), $file->changed),
				];
			}

			return $changed_files;
		}

		/**
		 * Set some editable defaults
		 * @return void
		 */
		private function set_default_excludes(): void {
			$excluded_directories = rsssl_get_option('file_change_exclusions');
			$defaults = [
				'advanced-headers.php',
				'advanced-headers-test.php',
				'advanced-cache.php',
				'wp-content/cache',
				'wp-content/backup',
				'wp-content/uploads',
				'wp-config.php',
			];

			//check if file is already included
			foreach ( $defaults as $default ) {
				if ( strpos( $excluded_directories, $default ) === false ) {
					if ( ! empty( $excluded_directories ) ) {
						$excluded_directories .= "\n";
					}
					$excluded_directories .= $default;
				}
			}

			rsssl_update_option('file_change_exclusions', $excluded_directories );
		}

		/**
		 * @param array  $skip_array
		 *
		 * @return array
		 */
		public function add_custom_excluded_directories( array $skip_array ): array {
			$excluded_directories = rsssl_get_option('file_change_exclusions');
			//allow comma separated as well:
			$excluded_directories = str_replace(",", "\n", $excluded_directories);

			$excluded_directories = explode("\n", $excluded_directories);
			//strip off all ../ in the array
			$excluded_directories = array_map( static function($dir){
				return str_replace('../', '', $dir);
			}, $excluded_directories);

			foreach ( $excluded_directories as $excluded_directory ) {
				$excluded_directory = trim($excluded_directory);
				if ( !empty($excluded_directory) ) {
					$skip_array[] = $excluded_directory;
				}
			}
			return array_unique($skip_array);
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
		public function maybe_start_hash_creation( string $field_id, $field_value, $prev_value, $field_type ): void {
			if ( !rsssl_user_can_manage() ) {
				return;
			}

			if ( $field_id === 'file_change_detection'  ) {
				if ( $field_value && !$prev_value ) {
					$this->set_default_excludes();
					$this->upgrade_table();
					$this->clear_directory_table();
					$this->reset_files_changed();

					if ( !wp_next_scheduled( "rsssl_hash_creation_cron_start" ) ) {
						wp_schedule_single_event(time() + 30 , "rsssl_hash_creation_cron_start");
					}
				}

				if ( !$field_value ) {
					wp_clear_scheduled_hook("rsssl_hash_creation_cron");
					wp_clear_scheduled_hook("rsssl_file_change_detection_cron");
					wp_clear_scheduled_hook("rsssl_hash_creation_cron_start");
					$this->delete_tables();
				}
			}
		}

		/**
		 * Handle plugin activation
		 * @param string $plugin
		 * @param bool   $network_wide
		 *
		 * @return void
		 */
		public function activated_plugin( string $plugin, bool $network_wide ): void {
			if ( ! rsssl_admin_logged_in() ) {
				return;
			}

			$slug          = trim( $plugin );
			$slug          = str_replace( '\\', '/', $slug );
			$folder = dirname($slug);
			$this->create_hashes_for_dir( ABSPATH . 'wp-content/plugins/' . $folder );
		}

		/**
		 * Run after an upgrade has completed
		 *
		 * @param $upgrader
		 * @param $upgrader_data
		 *
		 * @return void
		 */
		public function upgrader_process_complete( $upgrader, $upgrader_data ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}
			$directories = [];
			$type = $upgrader_data['type'];
			$installed = isset( $upgrader_data['action']) && $upgrader_data['action'] === 'install';
			if ( $installed ) {
				$dir = $upgrader->result['destination'] ?? '';
				$directories = [ $dir ];
			} else if ( 'core' === $type ) {
				$directories = [ABSPATH];
				add_filter( 'rsssl_file_scan_exclude_paths', function( $skip_array ){
					$skip_array[] = '/wp-content/';
					return $skip_array;
				}, 10, 2);
			} else if ( 'plugin' === $type ) {
				$plugin_slugs = $upgrader_data['plugins'] ?? [];
				foreach ( $plugin_slugs as $slug ) {
					$slug = trim($slug);
					$slug = str_replace('\\', '/', $slug);
					$folder = dirname($slug);
					$directories[] = $this->wp_plugin_dir . $folder;
				}
			} else if ( 'theme' === $type ) {
				$theme_slugs = $upgrader_data['themes'] ?? [];
				foreach ( $theme_slugs as $slug ) {
					$slug = trim($slug);
					$slug = str_replace('\\', '/', $slug);
					$folder = dirname($slug);
					$directories[] = $this->wp_theme_dir . $folder;
				}
			} else if ( 'translation' === $type ) {
				//only scan the languages folder
				$directories = [ABSPATH . 'wp-content/languages'];
			}

			foreach ( $directories as $dir ) {
				$this->create_hashes_for_dir( $dir );
			}
		}

		/**
		 * Create hashes
		 * Can run from a cron hook, or when it is started for the first time
		 *
		 * @return void
		 */
		public function run_hash_creation( $full = false ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}
			//set transient to prevent file change detection from starting before hash creation has finished.
			//there are also checks for the directory index, and the scheduled cron, but this is an extra protection.
			set_site_transient('rsssl_hash_creation_active', true, 60);

			//if upgrader data is passed, it's triggered by an update, so we do only the folder of these plugins/themes
			if ( $full ) {
				$directories = [ABSPATH];
			} else {
				//from cron, check incomplete hash indexes.
				$directories = $this->get_incomplete_directories('hash');
			}

			foreach ( $directories as $dir ) {
				$this->create_hashes_for_dir( $dir );
			}
			delete_site_transient('rsssl_hash_creation_active');
		}

		/**
		 * Create hashes fo each file in a directory and its subdirectories
		 *
		 * @param string $dir
		 *
		 * @return void
		 */
		private function create_hashes_for_dir( string $dir ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}

			$this->directories = $this->get_directories( $dir );
			$directory_count = count($this->directories);
			$this->update_directory_count( $dir, $directory_count );
			$directory_index = $this->get_directory_index( $dir, 'hash' );
			if ( isset($this->directories[$directory_index] ) ) {
				for ( $i = 0; $i < $this->nr_of_folders_one_batch; $i++ ) {
					$this->create_hash( $this->directories[$directory_index] );
					$directory_index++;
					if ( $directory_index >= $directory_count ) {
						break;
					}
				}
				$this->update_directory_index($dir, $directory_index, 'hash' );
				$incomplete_directories = $this->get_incomplete_directories('hash');
				if ( $directory_index < $directory_count || count($incomplete_directories)>0 ) {
					if ( !wp_next_scheduled( "rsssl_hash_creation_cron" ) ) {
						wp_schedule_single_event(time() + 30 , "rsssl_hash_creation_cron");
					}
				} else {
					//completed
					wp_clear_scheduled_hook("rsssl_hash_creation_cron");
					wp_clear_scheduled_hook("rsssl_hash_creation_cron_start");
					$this->clear_directory_index( $dir, 'hash' );
				}
			} else {
				$this->delete_directory_index( $dir );
			}
		}

		/**
		 * Get all  files in a directory, if it has one of the extensions in the $extensions array
		 * @param string $dir
		 * @param bool   $is_root
		 *
		 * @return void
		 */
		private function get_files_for_dir( string $dir, bool $is_root ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}

			if ( !is_dir($dir) ) {
				return;
			}

			//skip directories or files starting with a .
			if ( strpos($dir, "/.") !== false ) {
				return;
			}

			if ( $is_root ) {
				$this->file_list = [];
			}
			$extensions = apply_filters('rsssl_file_scan_extensions', $this->extensions);
			// Check permissions for files in the directory.
			$files = scandir($dir);
			foreach ( $files as $file ) {
				if ( $this->should_skip( $dir."/".$file ) ) {
					continue;
				}

				if ( $file !== '.' && $file !== '..' ) {
					$path = trailingslashit($dir) . $file;
					if ( is_dir($path) ) {
						//directories that are listed separately in the directories array should not be scannned recursively
						if ( $this->should_scan_recursively($dir) ) {
							$this->get_files_for_dir($path, false ); // Recursively check subdirectories.
						}
					} else {
						//check if this file's extension is included in the $extensions array
						$extension = strrchr($file, '.');
						if ( ! in_array( $extension, $extensions, true ) ) {
							continue;
						}

						if ( !in_array($path, $this->file_list) ) {
							$this->file_list[] = $path;
						}
					}
				}
			}
			$this->file_list = array_unique($this->file_list);
			//ensure array keys are sequential
			$this->file_list = array_values($this->file_list);
		}

		/**
		 * Check if hash creation has completed
		 *
		 * @return bool
		 */
		private function hash_creation_complete(): bool {
			$incomplete_directories = $this->get_incomplete_directories('hash');
			if ( count($incomplete_directories) > 0 ) {
				return false;
			}
			return true;
		}

		/**
		 * Check if hash creation has completed
		 *
		 * @return bool
		 */
		private function change_detection_complete(): bool {
			$incomplete_directories = $this->get_incomplete_directories('file_change' );
			return count( $incomplete_directories ) <= 0;
		}

		public function start_change_detection_full(): void {
			//check if rsssl_file_change_detection_cron is already scheduled. If so, we don't need to start it.
			if ( wp_next_scheduled( "rsssl_file_change_detection_cron" ) ) {
				return;
			}
			$this->clear_directory_index( ABSPATH, 'file_change' );
			$this->run_change_detection();
		}

		/**
		 * A typical WordPress setup has about 50 first and second level directories.
		 * Doing this in one week means about 10 each day, so running every hour should be enough to finish in a week.
		 *
		 * @return void
		 */
		public function run_change_detection(): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}

			//if there is still a hash creation cron scheduled, we should wait for that to finish
			if ( wp_next_scheduled( "rsssl_hash_creation_cron" ) || wp_next_scheduled( "rsssl_hash_creation_cron_start" ) ) {
				return;
			}

			//check if the hash creation has finished:
			if ( !$this->hash_creation_complete() ) {
				//check if the cron is still running
				if ( !wp_next_scheduled( "rsssl_hash_creation_cron" ) ) {
					wp_schedule_single_event(time() + 30 , "rsssl_hash_creation_cron");
				}
				return;
			}

			if ( get_site_transient('rsssl_hash_creation_active') ) {
				return;
			}

			$dir = ABSPATH;
			$this->directories = $this->get_directories( $dir );
			$directory_count = count($this->directories);
			$directory_index = $this->get_directory_index( $dir, 'file_change' );
			if ( isset($this->directories[$directory_index] ) ) {
				for ( $i = 0; $i < $this->nr_of_folders_one_batch; $i++ ) {
					$this->check_hash( $this->directories[$directory_index] );
					$directory_index++;
					if ( $directory_index >= $directory_count ) {
						break;
					}
				}
				$this->update_directory_index($dir, $directory_index, 'file_change' );
				if ( $directory_index < $directory_count ) {
					wp_schedule_single_event(time() + 30 , "rsssl_file_change_detection_cron");
				} else {
					//completed
					$this->clear_directory_index( $dir, 'file_change' );
					wp_clear_scheduled_hook("rsssl_file_change_detection_cron");
					//should be no hash creation active, so we can always delete now.
					$this->delete_directory_index( $dir );

					//as the check is completed, we can send an email if there are files with wrong permissions
					if ( ! get_transient( 'rsssl_filechange_mail_recently_sent' ) && ( count( $this->changed_files() ) > 0 ) ) {
						$this->send_email();
						set_transient( 'rsssl_filechange_mail_recently_sent', true, WEEK_IN_SECONDS );
					}
				}
			} else {
				$this->delete_directory_index( $dir );
			}

			if ( count($this->changed_files) > 0 ) {
				$this->changed_files = array_unique($this->changed_files);
				foreach ($this->changed_files as $changed_file ) {
					$this->set_file_changed( $changed_file );
				}
			}
		}

		/**
		 * Create a hash for a file
		 *
		 * @param string $dir
		 *
		 * @return void
		 */
		private function create_hash( string $dir ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}

			if ( !is_dir( $dir ) ) {
				return;
			}

			if ( !$this->table_exists('rsssl_file_hashes') ) {
				return;
			}

			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
			$this->get_files_for_dir( $dir, true );
			foreach ( $this->file_list as $file ){
				$hash = $this->get_hash($file);
				if ( empty( $hash ) ) {
					return;
				}

				$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE file_path = %s", $file));
				if ( $id ) {
					$wpdb->update($table_name, ["hash" => $hash, 'changed' => 0], ['id' => $id ]);
				} else {
					$wpdb->insert($table_name, ['file_path' => $file, "hash" => $hash, 'changed' => 0]);
				}
			}
		}

		/**
		 * Compare the hash of a file with the stored hash
		 * @param string $dir
		 *
		 * @return void
		 */
		private function check_hash( string $dir ): void {
			$this->get_files_for_dir( $dir, true );
			foreach ( $this->file_list as $file ) {
				$hash        = $this->get_hash( $file );
				$stored_hash = $this->get_stored_hash( $file );
				if ( $hash !== $stored_hash ) {
					$this->changed_files[] = $file;
				}
			}
		}

		/**
		 * Check if a table exists
		 * @param string $table
		 *
		 * @return bool
		 */
		public function table_exists( string $table ): bool {
			if ( in_array($table, $this->table_exists, true) && $this->table_exists[$table] ) {
				return true;
			}

			global $wpdb;
			$table_name = $wpdb->base_prefix . $table;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$tables = $wpdb->get_col( 'SHOW TABLES', 0 );
			if ( in_array( $table_name, $tables, true ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Get the contents of the file $file, and get the hash
		 *
		 * @param string $file
		 *
		 * @return string
		 */
		private function get_hash( string $file ): string {
			$hash = '';
			if ( is_file($file) ) {
				$hash = md5_file($file);
			}
			return $hash;
		}

		/**
		 * Get the hash for a file from the database
		 *
		 * @param string $file
		 *
		 * @return string
		 */
		private function get_stored_hash( string $file ): string {
			if ( !rsssl_admin_logged_in() ) {
				return '';
			}
			global $wpdb;
			if ( !$this->table_exists('rsssl_file_hashes') ) {
				return '';
			}
			$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
			$row = $wpdb->get_row($wpdb->prepare("SELECT hash FROM $table_name WHERE file_path = %s", $file));
			if ( $row ) {
				return $row->hash;
			}

			//file didn't exist yet, create hash, add to database, and return empty string
			//as the file didn't exist yet, we add the file as changed, although the process will set it to changed anyway
			$hash = $this->get_hash($file);
			$wpdb->insert($table_name, [ 'file_path' => $file, "hash" => $hash, 'changed' => time() ]);
			return '';
		}

		/**
		 * Send email about the permissions issue
		 *
		 * @return void
		 */
		private function send_email(): void {
			if ( !class_exists('rsssl_mailer' ) ){
				require_once( rsssl_path . 'mailer/class-mail.php');
			}

			$changed_files = $this->changed_files();
			//get first 10 files
			$cutoff = false;
			if ( count($changed_files) > 10 ) {
				$changed_files = array_slice($changed_files, 0, 10);
				$cutoff =true;
			}

			$list = implode( "<br>", $changed_files );
			$list = "<br>".$list."<br><br>";
			if ($cutoff) {
				$list .= __(" Download the full list via the Really Simple Security dashboard.", "really-simple-ssl")."<br>";
			}

			if ( class_exists('rsssl_mailer')) {
				$block = [
					'title' => __('Changed files', 'really-simple-ssl'),
					'message' => __('The recurring scan detected files that were changed outside plugin, theme or WordPress updates:','really-simple-ssl')
					.$list,
					'url' => rsssl_admin_url([], '#settings/hardening-file-change'),
				];
				$site_url = get_site_url();
				$url = '<a rel="noopener noreferrer" target="_blank" href="'.$site_url.'">'.$site_url.'</a>';

				$mailer          = new rsssl_mailer();
				$mailer->subject = __( 'Security warning: changed files', 'really-simple-ssl' );
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
		public function changed_files(): array {
			if ( !rsssl_admin_logged_in() ) {
				return [];
			}

			if ( !$this->table_exists('rsssl_file_hashes') ) {
				return [];
			}

			if ( !$this->files_loaded ) {
				global $wpdb;
				$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
				$files = $wpdb->get_results("SELECT file_path FROM $table_name WHERE changed IS NOT NULL AND changed <> 0");
				$changed_files = [];
				foreach ( $files as $file ) {
					if ( !file_exists($file->file_path) ) {
						continue;
					}
					$changed_files[] = str_replace(ABSPATH, '', $file->file_path);
				}
				//add to existing array, in case we've done some hash checking during this run.
				$this->changed_files = array_unique( $this->changed_files + $changed_files );
				$this->files_loaded = true;
			}

			return $this->changed_files;
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
		 * Check if file or path should be skipped because it is in an exclude list
		 *
		 * @param string $path //full path to file or directory
		 *
		 * @return bool
		 */
		private function should_skip( string $path ): bool {
			if ( empty($this->skip_array) ) {
				$this->skip_array = apply_filters('rsssl_file_scan_exclude_paths', $this->default_skip_array );
			}
			$skip_array = $this->skip_array;
			//remove empty values from $skip_array
			$skip_array = array_filter($skip_array);

			foreach ( $skip_array as $skip_dir ) {
				if ( strpos( $path, $skip_dir ) !== false ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Get array of all directories
		 *
		 * @param string $dir
		 *
		 * @return array
		 */
		private function get_directories( string $dir, $level = 0 ): array {
			if ( !rsssl_admin_logged_in() ) {
				return [];
			}

			if ( empty($dir) ) {
				return [];
			}

			if ($level === $this->directory_levels) {
				return [];
			}

			//ensure that root directory is included.
			$directories = [ $dir ];

			//skip directories or files starting with a .
			if ( strpos($dir, "/.") !== false ) {
				return [];
			}

			$files = scandir($dir);
			foreach ($files as $file) {
				if ( $file !== '.' && $file !== '..' ) {
					//skip files starting with a .
					if ( strpos($file, ".") === 0 ) {
						continue;
					}

					$path = trailingslashit($dir) . $file;
					//skip directories where part of the directory occurs in the $skip_array
					if ( $this->should_skip($path) ) {
						continue;
					}

					if ( is_dir($path) ) {
						if ( !in_array($path, $directories) ) {
							$directories[] = $path;
						}
						$directories = array_merge($directories, $this->get_directories($path, $level+1 ));
					}
				}
			}

			if ( $level === 0 ) {
				$directories = array_unique($directories);
				//ensure array keys are sequential
				$directories = array_values($directories);
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
			if ( !$this->change_detection_complete() ) {
				return $notices;
			}

			if ( count($this->changed_files() )>0 ) {
				$download_link = trailingslashit(rsssl_url).'pro/security/wordpress/file-change-detection/download.php';
				$notices['changed_files'] = array(
					'callback' => '_true_',
					'score' => 10,
					'show_with_options' => array( 'file_change_detection' ),
					'output' => array(
						'true' => array(
							'highlight_field_id' => 'changed-files-overview',
							'title' => __("Changed files detected.", 'really-simple-ssl'),
							'msg' => sprintf(__("Some files on your server have been changed outside the normal update procedure. You can %sdownload%s the affected files list to verify the files.", 'really-simple-ssl'), '<a rel="noopener noreferrer"  target="_blank" href="'.$download_link.'">', '</a>'),
							'icon' => 'warning',
						),
					),
				);
			}

			return $notices;
		}

		/**
		 * Get current index of a directory, to keep track of current progress
		 *
		 * @param string $dir
		 * @param string $type
		 *
		 * @return int
		 */
		private function get_directory_index( string $dir, string $type ): int {
			if ( !rsssl_admin_logged_in() ) {
				return 0;
			}
			if ( !$this->table_exists('rsssl_file_change_detection_directory_indexes') ) {
				return 0;
			}

			global $wpdb;
			$type = $this->sanitize_type( $type );
			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";
			$index = $wpdb->get_var($wpdb->prepare("SELECT {$type}_index FROM $table_name WHERE directory = %s", $dir));
			if ( $index ) {
				if ( $index < 0 ) {
					$index = 0;
				}
				return $index;
			}
			return 0;
		}

		/**
		 * @param string $dir
		 * @param int    $index
		 * @param string $type
		 *
		 * @return void
		 */
		private function update_directory_index( string $dir, int $index, string $type ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}
			if ( !$this->table_exists('rsssl_file_change_detection_directory_indexes') ) {
				return;
			}
			global $wpdb;
			$type = $this->sanitize_type( $type );
			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";
			$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE directory = %s", $dir));
			if ( $id ) {
				$wpdb->update($table_name, ["{$type}_index" => $index], ['id' => $id]);
			} else {
				$wpdb->insert($table_name, ['directory' => $dir, "{$type}_index" => $index]);
			}
		}

		/**
		 * Set a directory index as completed. -1 means completed. Delete if it doesn't exist
		 *
		 * @param string $dir
		 * @param string $type
		 *
		 * @return void
		 */
		private function clear_directory_index( string $dir, string $type ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}
			global $wpdb;
			$type = $this->sanitize_type( $type );
			$inverse_type_index = $type === 'hash' ? 'file_change_index' : 'hash_index';
			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";

			//check if the directory exists at all
			if ( !is_dir($dir) ) {
				$this->delete_directory_index( $dir );
				return;
			}

			$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE directory = %s", $dir));
			//if the other type is already -1, we can also delete it. Otherwise, set it to -1
			if ( $row && $row->{$inverse_type_index} === -1 ) {
				$wpdb->delete( $table_name, ['directory' => $dir]);
			} else {
				$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE directory = %s", $dir));
				if ( $id ) {
					$wpdb->update($table_name, ["{$type}_index" => -1], ['id' => $id]);
				} else {
					$wpdb->insert($table_name, ['directory' => $dir, "{$type}_index" => -1]);
				}
			}
		}

		/**
		 * Remove a directory from the database indexes table.
		 *
		 * @param string $dir
		 *
		 * @return void
		 */
		private function delete_directory_index( string $dir ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}
			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";
			$wpdb->delete($table_name, ['directory' => $dir]);
		}

		/**
		 * Get list of directories that have not been fully hashed yet
		 *
		 * @param string $type
		 *
		 * @return array
		 */
		private function get_incomplete_directories( string $type ): array {
			if ( !rsssl_admin_logged_in() ) {
				return [];
			}
			global $wpdb;
			if ( !$this->table_exists('rsssl_file_change_detection_directory_indexes') ) {
				return [];
			}
			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";
			$type = $this->sanitize_type( $type );
			$directories = $wpdb->get_results("SELECT * FROM $table_name WHERE {$type}_index > -1");
			$dirs = [];
			$index = $type.'_index';
			foreach ( $directories as $directory ) {
				//check if the directory exists at all
				if ( !is_dir($directory->directory) ) {
					$this->delete_directory_index( $directory->directory );
					continue;
				}

				if ( $directory->count > $directory->{$index} && $directory->{$index} >-1 ) {
					$dirs[] = $directory->directory;
				}
			}
			return $dirs;
		}

		/**
		 * Update the directory count.
		 *
		 * @param string $dir
		 * @param int    $count
		 *
		 * @return void
		 */
		private function update_directory_count( string $dir, int $count ): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}
			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";
			$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE directory = %s", $dir));
			if ( $id ) {
				$wpdb->update($table_name, ['count' => $count], ['id' => $id]);
			} else {
				$wpdb->insert($table_name, ['directory' => $dir, 'count' => $count]);
			}
		}

		/**
		 * Clear the directory table
		 *
		 * @return void
		 */
		private function clear_directory_table(): void {
			if ( !rsssl_admin_logged_in() ) {
				return;
			}
			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";
			$wpdb->query( "TRUNCATE TABLE $table_name" );
		}

		/**
		 * Sanitize the type, to be hash or file_change
		 *
		 * @param string $type
		 *
		 * @return string
		 */
		private function sanitize_type( string $type ): string {
			$possible_types = ['hash', 'file_change'];
			if ( !in_array($type, $possible_types) ) {
				$type = 'file_change';
			}
			return $type;
		}

		/**
		 * Set file as having been changed
		 * @param string $file
		 *
		 * @return void
		 */
		private function set_file_changed( string $file ): void {
			if ( !$this->table_exists('rsssl_file_hashes') ) {
				return;
			}
			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_hashes";

			//cleanup
			if ( !file_exists($file) ) {
				$wpdb->delete( $table_name, ['file_path' => $file]);
				return;
			}

			$wpdb->update($table_name, [ 'changed' => time() ], ['file_path' => $file]);
		}

		/**
		 * Set all changed files to unchanged
		 *
		 * @return void
		 */
		private function reset_files_changed(): void {
			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
			$wpdb->query("UPDATE $table_name SET changed = 0");
		}

		/**
		 * On deactivation, drop tables
		 * @return void
		 */
		private function delete_tables(): void {

			global $wpdb;
			$table_names = array(
				$wpdb->base_prefix . 'rsssl_file_change_detection_directory_indexes',
				$wpdb->base_prefix . 'rsssl_file_hashes',
			);

			foreach($table_names as $table_name){
				$sql = "DROP TABLE IF EXISTS $table_name";
				$wpdb->query($sql);
			}
		}

		/**
		 * Check if db should be updated
		 */
		public function upgrade_table()
		{
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			global $wpdb;
			$table_name = $wpdb->base_prefix . "rsssl_file_hashes";
			$charset_collate = $wpdb->get_charset_collate();
			$sql = /** @lang text */
				"CREATE TABLE $table_name (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              changed int(11) NOT NULL,
              file_path text  NOT NULL,
              hash text  NOT NULL,
              PRIMARY KEY  (id)
            ) $charset_collate";

			dbDelta($sql);

			$table_name = $wpdb->base_prefix . "rsssl_file_change_detection_directory_indexes";
			$charset_collate = $wpdb->get_charset_collate();
			$sql = /** @lang text */
				"CREATE TABLE $table_name (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              directory text  NOT NULL,
              count mediumint(9)  NOT NULL,
              hash_index mediumint(9)  NOT NULL,
              file_change_index mediumint(9)  NOT NULL,
              PRIMARY KEY  (id)
          ) $charset_collate";

			dbDelta($sql);

		}

	}
}
$rsssl_file_change_detection = new rsssl_file_change_detection();