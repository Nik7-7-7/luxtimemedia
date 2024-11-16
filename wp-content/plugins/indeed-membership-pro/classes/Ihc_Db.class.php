<?php
if (!class_exists('Ihc_Db')):

class Ihc_Db{

	public function __construct(){}

		public static function create_tables(){
			/*
			 * @param none
			 * @return none
			 */
			global $wpdb;
			$prefixes = self::get_all_prefixes();
			$engine = self::tableEngine();

			foreach ($prefixes as $the_table_prefix):

			$table_name = $the_table_prefix . "ihc_user_levels";
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
															id int(11) NOT NULL AUTO_INCREMENT,
															user_id int(11) NOT NULL,
															level_id int(11) NOT NULL,
															start_time datetime,
															update_time datetime,
															expire_time datetime,
															notification tinyint(1) DEFAULT 0,
															status int(3) NOT NULL,
															PRIMARY KEY (`id`),
															INDEX idx_ihc_user_levels_user_id (`user_id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;
				";
				dbDelta ( $sql );
			}
			//ihc_debug_payments
			$table_name = $the_table_prefix . "ihc_debug_payments";
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							source VARCHAR(200),
							message TEXT,
							insert_time datetime,
							PRIMARY KEY (`id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta ( $sql );
			}
			////////// indeed_members_payments
			$table_name = $the_table_prefix . 'indeed_members_payments';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$sql = "CREATE TABLE " . $table_name . " (
							id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
							txn_id VARCHAR(100) DEFAULT NULL,
							u_id int(9) DEFAULT NULL,
							payment_data text DEFAULT NULL,
							history TEXT,
							orders TEXT DEFAULT NULL,
							paydate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
							INDEX idx_indeed_members_payments_uid (`u_id`)
					)
					ENGINE=$engine
					CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			//ihc_notifications
			$table_name = $the_table_prefix . "ihc_notifications";
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$sql = "CREATE TABLE " . $table_name . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							notification_type VARCHAR(200),
							level_id VARCHAR(200),
							subject TEXT,
							message TEXT,
							pushover_message TEXT,
							pushover_status TINYINT(1) NOT NULL DEFAULT 0,
							status TINYINT(1),
							PRIMARY KEY (`id`)
						)
						ENGINE=$engine
						CHARACTER SET utf8 COLLATE utf8_general_ci;
				";
				dbDelta($sql);
			}

			//ihc_coupons
			$table_name = $the_table_prefix . "ihc_coupons";
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							code varchar(200),
							settings text,
							submited_coupons_count int(11),
							status tinyint(1),
							PRIMARY KEY (`id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta ( $sql );
			}

			//ihc_orders
			$table = $the_table_prefix . 'ihc_orders';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table'";
			if ($wpdb->get_var( $query )!=$table){
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$sql = "CREATE TABLE $table(
											id INT(11) NOT NULL AUTO_INCREMENT,
											uid INT(11),
											lid INT(11),
											amount_type VARCHAR(200),
											amount_value DECIMAL(12, 2) DEFAULT 0,
											automated_payment TINYINT(1) DEFAULT NULL,
											status VARCHAR(100),
											create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
											PRIMARY KEY (`id`),
											INDEX idx_ihc_orders_uid (`uid`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			///ihc_orders_meta
			$table = $the_table_prefix . 'ihc_orders_meta';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table'";
			if ($wpdb->get_var( $query )!=$table){
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$sql = "CREATE TABLE $table(
											id INT(11) NOT NULL AUTO_INCREMENT,
											order_id INT(11),
											meta_key VARCHAR(200),
											meta_value TEXT,
											PRIMARY KEY (`id`),
											INDEX idx_ihc_orders_meta_order_id (`order_id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			//ihc_taxes
			$table = $the_table_prefix . 'ihc_taxes';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table'";
			if ($wpdb->get_var( $query )!=$table){
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$sql = "CREATE TABLE $table(
											id INT(11) NOT NULL AUTO_INCREMENT,
											country_code VARCHAR(20),
											state_code VARCHAR(50) DEFAULT '',
											amount_value DECIMAL(12, 2) DEFAULT 0,
											label VARCHAR(200),
											description TEXT,
											status TINYINT(1),
											PRIMARY KEY (`id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			/// IHC_DASHBOARD_NOTIFICATIONS
			$table_name = $the_table_prefix . 'ihc_dashboard_notifications';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query )!=$table_name){
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$sql = "CREATE TABLE $table_name (
							type VARCHAR(40) NOT NULL,
							value INT(11) DEFAULT 0
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);

				/// THIS TABLE WILL CONTAIN ONLY THIS TWO ENTRIES
				$query = $wpdb->prepare( "INSERT INTO $table_name VALUES( %s, %d);", 'users', 0 );
				$wpdb->query( $query );
				$query = $wpdb->prepare( "INSERT INTO $table_name VALUES( %s, %d );", 'orders', 0 );
				$wpdb->query( $query );
			}

			/// ihc_cheat_off
			$table_name = $the_table_prefix . 'ihc_cheat_off';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query )!=$table_name){
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				$sql = "CREATE TABLE $table_name (
							uid INT(11) NOT NULL,
							hash VARCHAR(40) NOT NULL
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			//ihc_invitation_codes
			$table_name = $the_table_prefix . "ihc_invitation_codes";
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
							id int(11) NOT NULL AUTO_INCREMENT,
							code varchar(200),
							settings text,
							submited int(11),
							repeat_limit int(11),
							status tinyint(1),
							PRIMARY KEY (`id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta ( $sql );
			}

			$table_name = $the_table_prefix . 'ihc_gift_templates';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
							id INT(11) NOT NULL AUTO_INCREMENT,
							lid INT(11),
							settings TEXT,
							status TINYINT(2),
							PRIMARY KEY (`id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta ( $sql );
			}

			$table = $the_table_prefix . 'ihc_security_login';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table' ";
			if ($wpdb->get_var( $query )!=$table){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE $table (
												id INT(11) NOT NULL AUTO_INCREMENT,
												username VARCHAR(200),
												ip VARCHAR(30),
												log_time INT(11),
												attempts_count INT(3),
												locked TINYINT(1),
												PRIMARY KEY (`id`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			$table = $the_table_prefix . 'ihc_user_logs';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table' ";
			if ($wpdb->get_var( $query )!=$table){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE $table (
												id INT(11) NOT NULL AUTO_INCREMENT,
												uid INT(9) NOT NULL DEFAULT 0,
												lid INT(3),
												log_type VARCHAR(100),
												log_content TEXT,
												create_date INT(11),
												PRIMARY KEY (`id`),
												INDEX idx_ihc_user_logs_uid (`uid`)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			/// ihc_woo_products
			$table_name = $wpdb->base_prefix . 'ihc_woo_products';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
											id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
											slug VARCHAR(200) NOT NULL,
											discount_type VARCHAR(20),
											discount_value DECIMAL(12, 2),
											start_date DATETIME,
											end_date DATETIME,
											settings TEXT,
											status TINYINT(1) DEFAULT 0
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			/// ihc_woo_product_level_relations
			$table_name = $wpdb->base_prefix . 'ihc_woo_product_level_relations';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
											id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
											ihc_woo_product_id INT(11),
											lid INT(11),
											woo_item INT(11),
											woo_item_type VARCHAR(200)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}


			///ihc_user_sites
			$table_name = $wpdb->base_prefix . 'ihc_user_sites';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name'";
			if ($wpdb->get_var( $query ) != $table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
											id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
											site_id INT(11),
											uid INT(11),
											lid INT(11)
				)
				ENGINE=$engine
				CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}


			/// ihc_download_monitor_limit
			$table_name = $the_table_prefix . 'ihc_download_monitor_limit';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name';";
			if ($wpdb->get_var( $query )!=$table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE " . $table_name . " (
											uid INT(11) NOT NULL,
											lid INT(11) NOT NULL,
											download_limit INT(11) NOT NULL
							)
							ENGINE=$engine
							CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			$table_name = $the_table_prefix . 'ihc_reason_for_cancel_delete_levels';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name';";
			if ($wpdb->get_var( $query )!=$table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE $table_name (
											id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
											uid INT(11) NOT NULL,
											lid INT(11) NOT NULL,
											reason VARCHAR(400),
											action_type VARCHAR(30),
											action_date INT(10)
							)
							ENGINE=$engine
							CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			$table_name = $the_table_prefix . 'ihc_notifications_logs';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "show tables like '$table_name';";
			if ($wpdb->get_var( $query )!=$table_name){
				require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
				$sql = "CREATE TABLE $table_name (
											id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
											notification_type VARCHAR( 100 ),
											email_address VARCHAR( 300 ),
											subject VARCHAR( 300 ),
											message TEXT,
											uid INT(11) NOT NULL,
											lid INT(11) NOT NULL,
											create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
							)
							ENGINE=$engine
							CHARACTER SET utf8 COLLATE utf8_general_ci;";
				dbDelta($sql);
			}

			/// memberships
			\Indeed\Ihc\Db\Memberships::setTablePrefix( $the_table_prefix );
			\Indeed\Ihc\Db\Memberships::createTables( $engine );

			/// ihc_user_subscription_meta
			\Indeed\Ihc\Db\UserSubscriptionsMeta::setTablePrefix( $the_table_prefix );
			\Indeed\Ihc\Db\UserSubscriptionsMeta::createTable( $engine );

			endforeach;
		}

	public static function update_tables_structure(){
		/*
		 * @param none
		 * @return none
		 */
		global $wpdb;
		$table = $wpdb->prefix . 'indeed_members_payments';
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SHOW COLUMNS FROM " . $table . " LIKE 'txn_id';";
		$data = $wpdb->get_row( $query );
		if (!$data){
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$q = 'ALTER TABLE ' . $wpdb->prefix . 'indeed_members_payments ADD history TEXT AFTER payment_data';
			$wpdb->query($q);
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$q = 'ALTER TABLE ' . $wpdb->prefix . 'indeed_members_payments ADD txn_id VARCHAR(100) DEFAULT NULL AFTER id';
			$wpdb->query($q);
		}

		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SHOW COLUMNS FROM " . $table . " LIKE 'orders';";
		$data = $wpdb->get_row( $query );
		if (!$data){
			$q = "ALTER TABLE $table ADD orders TEXT AFTER history";
			$wpdb->query($q);
		}

		$table = $wpdb->prefix . 'ihc_user_levels';
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SHOW COLUMNS FROM " . $table . " LIKE 'notification';";
		$data = $wpdb->get_row( $query );
		if (!$data){
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$q = 'ALTER TABLE ' . $wpdb->prefix . 'ihc_user_levels ADD notification tinyint(1) DEFAULT 0 AFTER expire_time;';
			$wpdb->query($q);
		}

		/// alter ihc_taxes if its case
		$table = $wpdb->prefix . 'ihc_taxes';
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SHOW COLUMNS FROM " . $table . " LIKE 'state_code';";
		$data = $wpdb->get_row( $query );
		if (!$data){
			$q = "ALTER TABLE $table ADD state_code VARCHAR(50) DEFAULT '' AFTER country_code;";
			$wpdb->query($q);
		}

		/// alter ihc_notifications if its case
		$table = $wpdb->prefix . 'ihc_notifications';
		$query = "SHOW COLUMNS FROM " . $table . " LIKE 'pushover_message';";
		$data = $wpdb->get_row( $query );
		if (!$data){
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$q = "ALTER TABLE $table ADD pushover_message TEXT AFTER message;";
			$wpdb->query($q);
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$q = "ALTER TABLE $table ADD pushover_status TINYINT(1) NOT NULL DEFAULT 0 AFTER pushover_message;";
			$wpdb->query($q);
		}
	}

		/**
		 * @param none
		 * @return array
		 */
		public static function get_all_prefixes()
		{
				global $wpdb;
				$data[] = $wpdb->base_prefix;
				if (is_multisite() ){
						if ( is_network_admin() ){
								// activate on entire network
								$ids = self::get_all_blog_ids();
								if ($ids){
									foreach ($ids as $object){
											if ( $object->blog_id == 1 ){
												 continue;
											}
											$data[] = $wpdb->base_prefix . $object->blog_id . '_';
									}
								}
						} else {
							// activate on single site on network
							$currentSite = get_current_blog_id();
							$mainSite = 1;
							if ( $currentSite == $mainSite ){
									return [ $wpdb->base_prefix ];
							}
							return [ $wpdb->base_prefix . $currentSite . '_' ];
						}
				}
				return $data;
		}

	public static function do_uninstall(){
		/*
		 * @param none
		 * @return none
		 */
		$values = self::default_settings_groups();

		if (is_multisite()){
				// multisite
				$blogs = self::get_all_blog_ids();
				foreach ( $blogs as $blogObject ){
						switch_to_blog( $blogObject->blog_id );
						// do delete
						foreach ($values as $value){
								$data = ihc_return_meta_arr($value, true);
								foreach ($data as $k=>$v){
									delete_option($k);
								}
						}
						delete_option('ihc_levels');//delete the levels
						delete_option('ihc_lockers');//delete the lockers
						delete_option('ihc_dashboard_allowed_roles');
						delete_option('ihc_custom_redirect_links_array');
						delete_option( 'ihc_plugin_current_version' );
				}
		} else {
				// single site
				foreach ($values as $value){
					$data = ihc_return_meta_arr($value, true);
					foreach ($data as $k=>$v){
						delete_option($k);
					}
				}
				delete_option('ihc_levels');//delete the levels
				delete_option('ihc_lockers');//delete the lockers
				delete_option('ihc_dashboard_allowed_roles');
				delete_option('ihc_custom_redirect_links_array');
				delete_option( 'ihc_plugin_current_version' );
		}

		//delete table indeed_members_payments
		global $wpdb;
		$tables = array(
						 "indeed_members_payments",
						 "ihc_user_levels",
						 "ihc_debug_payments",
						 "ihc_notifications",
						 "ihc_coupons",
						 'ihc_orders',
						 'ihc_orders_meta',
						 'ihc_taxes',
						 'ihc_dashboard_notifications',
						 'ihc_cheat_off',
						 'ihc_invitation_codes',
						 'ihc_gift_templates',
						 'ihc_security_login',
						 'ihc_user_logs',
						 'ihc_woo_products',
						 'ihc_woo_product_level_relations',
						 'ihc_user_sites',
						 'ihc_download_monitor_limit',
						 'ihc_reason_for_cancel_delete_levels',
						 'ihc_memberships',
						 'ihc_memberships_meta',
						 'ihc_notifications_logs',
						 'ihc_user_subscriptions_meta',
		);
		$prefixes = self::get_all_prefixes_for_unistall();
		foreach ($prefixes as $the_table_prefix){
			foreach ($tables as $table){
				$table_name = $the_table_prefix . $table;
				//No query parameters required, Safe query. prepare() method without parameters can not be called
				$query = "DROP TABLE IF EXISTS $table_name;";
				$wpdb->query( $query );
			}
		}

		//delete user levels
		$users_obj = new WP_User_Query(array(
				'meta_query' => array(
						'relation' => 'OR',
						array(
								'key' => $wpdb->get_blog_prefix() . 'capabilities',
								'value' => 'subscriber',
								'compare' => 'like'
						),
						array(
								'key' => $wpdb->get_blog_prefix() . 'capabilities',
								'value' => 'pending_user',
								'compare' => 'like'
						)
				)
		));
		$users = $users_obj->results;
		if (!empty($users)){
			foreach ($users as $user){
				delete_user_meta($user->data->ID, 'ihc_user_levels');
			}
		}

		//since version 12.1 -  wizard options
		delete_option( 'iump_wizard_lid' );
		delete_option( 'iump_wizard_complete' );
		delete_option( 'ihc_wizard_current_page' );

		delete_option('ihc_admin_announcement_initial_time');
		delete_option( 'ihc_admin_announcement_step' );

	}

	/**
	 * @param none
	 * @return array
	 */
	public static function get_all_prefixes_for_unistall()
	{
			global $wpdb;
			$data[] = $wpdb->base_prefix;
			if (is_multisite() ){
					$ids = self::get_all_blog_ids();
					if ( $ids ){
							foreach ($ids as $object){
										$data[] = $wpdb->base_prefix . $object->blog_id . '_';
							}
					}
			}
			return $data;
	}

	/**
	 * @param none
	 * @return object
	 */
	public static function get_all_blog_ids(){
		global $wpdb;
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SELECT blog_id FROM {$wpdb->blogs};";
		$data = $wpdb->get_results( $query );
		return $data;
	}

	public static function create_notifications(){
		/*
		 * @param none
		 * @return none
		 */
		global $wpdb;
		$keys = array(
						'email_check',
						'email_check_success',
						'reset_password',
						'admin_user_register',
						'reset_password_process',
						'change_password',
						'register',
						'review_request',
						'approve_account',
						'bank_transfer',
						'register_lite_send_pass_to_user',

		);
		$table = $wpdb->prefix . "ihc_notifications";

		$notificationObject = new \Indeed\Ihc\Notifications();
		foreach ($keys as $key){
			$q = $wpdb->prepare("SELECT id FROM $table WHERE notification_type=%s;", $key);
			$check = $wpdb->get_row($q);
			if (empty($check)){
				$notf_data = $notificationObject->getNotificationTemplate( $key );
				$notf_data['message'] = (isset($notf_data['content'])) ? $notf_data['content'] : '';
				$notf_data['notification_type'] = $key;
				$notf_data['level_id'] = -1;
				$notf_data['pushover_message'] = '';
				$notf_data['pushover_status'] = '';
				$notificationObject->save($notf_data);
				unset($notf_data);
			}
		}
	}

	public static function create_default_pages(){
			/*
			 * @param none
			 * @return none
			 */
		$insert_array = array(
						'ihc_general_user_page' => array(
											'title' => esc_html__('My Account', 'ihc'),
											'content' => '[ihc-user-page]',
						),
						'ihc_general_login_default_page' => array(
											'title' => esc_html__('Member Login', 'ihc'),
											'content' => '[ihc-login-form]',
						),
						'ihc_general_logout_page' => array(
											'title' => esc_html__('Member LogOut', 'ihc'),
											'content' => '[ihc-logout-link]',
						),
						'ihc_general_register_default_page' => array(
											'title' => esc_html__('Register', 'ihc'),
											'content' => '[ihc-register]',
						),
						'ihc_general_lost_pass_page' => array(
											'title' => esc_html__('Lost Password', 'ihc'),
											'content' => '[ihc-pass-reset]',
						),
						'ihc_subscription_plan_page' => array(
											'title' => esc_html__('Subscription Plan', 'ihc'),
											'content' => '[ihc-select-level]',
						),
						'ihc_checkout_page' => array(
											'title' => esc_html__('Checkout Page', 'ihc'),
											'content' => '[ihc-checkout-page]',
						),
						'ihc_thank_you_page' => array(
											'title' => esc_html__('Thank You Page', 'ihc'),
											'content' => '[ihc-thank-you-page]',
						),
						'ihc_general_tos_page' => array(
											'title' => esc_html__('Member TOS Page', 'ihc'),
											'content' => 'Terms of Services',
						),
						'ihc_general_register_view_user' => array(
											'title' => esc_html__('Public Individual Page', 'ihc'),
											'content' => '[ihc-visitor-inside-user-page]',
						),
		);

		foreach ($insert_array as $key=>$inside_arr){
			$exists = get_option($key);
			if (!$exists){
				$arr = array(
							'post_content' => $inside_arr['content'],
							'post_title' => $inside_arr['title'],
							'post_type' => 'page',
							'post_status' => 'publish',
				);
				$post_id = wp_insert_post($arr);
				update_option($key, $post_id);
			}
		}
	}

	public static function create_default_redirects(){
		/*
		 * @param none
		 * @return none
		 */
		///DEFAULT REDIRECT
		$exists = get_option('ihc_general_redirect_default_page');
		if (!$exists){
			$arr = array(
							'post_content' => 'Redirected',
							'post_title' => 'Default Redirect Page',
							'post_type' => 'page',
							'post_status' => 'publish',
			);
			$post_id = wp_insert_post($arr);
			update_option('ihc_general_redirect_default_page', $post_id);
		}

		///AFTER LOGIN
		$exists = get_option('ihc_general_logout_redirect');
		if (!$exists){
			$login = get_option('ihc_general_login_default_page');
			update_option('ihc_general_logout_redirect', $login);
		}

		///AFTER REGISTER
		$exists = get_option('ihc_general_register_redirect');
		if ($exists){
			$account_page = get_option('ihc_general_user_page');
			update_option('ihc_general_register_redirect', $account_page);
		}

		///AFTER LOGIN
		$exists = get_option('ihc_general_login_redirect');
		if (!$exists){
			$account_page = get_option('ihc_general_user_page');
			update_option('ihc_general_login_redirect', $account_page);
		}
	}

	public static function getUmpDefaultPages()
	{
			return array(
					'ihc_general_login_default_page'					=> get_option( 'ihc_general_login_default_page' ),
					'ihc_general_user_page'										=> get_option( 'ihc_general_user_page' ),
					'ihc_general_register_default_page'				=> get_option( 'ihc_general_register_default_page' ),
					'ihc_general_logout_page'									=> get_option( 'ihc_general_logout_page' ),
					'ihc_general_lost_pass_page'							=> get_option( 'ihc_general_lost_pass_page' ),
					'ihc_subscription_plan_page'							=> get_option( 'ihc_subscription_plan_page' ),
					'ihc_checkout_page'												=> get_option( 'ihc_checkout_page' ),
					'ihc_thank_you_page'											=> get_option( 'ihc_thank_you_page' ),
					'ihc_general_register_view_user'					=> get_option( 'ihc_general_register_view_user' ),
					'ihc_general_tos_page'										=> get_option( 'ihc_general_tos_page' ),
					'ihc_general_redirect_default_page'				=> get_option( 'ihc_general_redirect_default_page' ),
			);
	}

	public static function create_extra_redirects(){
		/*
		 * @param none
		 * @return none
		 */
		 $login = get_option('ihc_general_login_default_page');
		 $account_page = get_option('ihc_general_user_page');
		 $register = get_option('ihc_general_register_default_page');
		 $logout = get_option('ihc_general_logout_page');
		 $lost_password = get_option('ihc_general_lost_pass_page');
		 $checkoutPage = get_option( 'ihc_checkout_page' );
		 if ($login){
		 	/// LOGIN
		 	update_post_meta($login, 'ihc_mb_type', 'show');
		 	update_post_meta($login, 'ihc_mb_who', 'unreg');
			update_post_meta($login, 'ihc_mb_block_type', 'redirect');
			update_post_meta($login, 'ihc_mb_redirect_to', $account_page);
		 }
		 if ($account_page){
		 	/// ACCOUNT PAGE
		 	update_post_meta($account_page, 'ihc_mb_type', 'show');
		 	update_post_meta($account_page, 'ihc_mb_who', 'reg');
			update_post_meta($account_page, 'ihc_mb_block_type', 'redirect');
			update_post_meta($account_page, 'ihc_mb_redirect_to', $login);
		 }
		 if ($register){
		 	/// REGISTER PAGE
		 	update_post_meta($register, 'ihc_mb_type', 'show');
		 	update_post_meta($register, 'ihc_mb_who', 'unreg');
			update_post_meta($register, 'ihc_mb_block_type', 'redirect');
			update_post_meta($register, 'ihc_mb_redirect_to', $account_page);
		 }
		 if ($logout){
		 	///LOGOUT
		 	update_post_meta($logout, 'ihc_mb_type', 'show');
		 	update_post_meta($logout, 'ihc_mb_who', 'reg');
			update_post_meta($logout, 'ihc_mb_block_type', 'redirect');
			update_post_meta($logout, 'ihc_mb_redirect_to', $login);
		 }
		 if ($lost_password){
		 	///LOGOUT
		 	update_post_meta($lost_password, 'ihc_mb_type', 'show');
		 	update_post_meta($lost_password, 'ihc_mb_who', 'unreg');
			update_post_meta($lost_password, 'ihc_mb_block_type', 'redirect');
			update_post_meta($lost_password, 'ihc_mb_redirect_to', $account_page);
		 }
		 if ( $checkoutPage ){
			 	// checkout page
				update_post_meta($checkoutPage, 'ihc_mb_type', 'show');
				update_post_meta($checkoutPage, 'ihc_mb_who', 'reg');
				update_post_meta($checkoutPage, 'ihc_mb_block_type', 'redirect');
				update_post_meta($checkoutPage, 'ihc_mb_redirect_to', $register );
		 }
	}

	public static function create_default_lockers(){
		/*
		 * @param none
		 * @return none
		 */
		 $data = get_option('ihc_lockers');
		 if ($data){
		 	return;
		 }
		 $array = array(
		 				'ihc_locker_name' => 'Loker with Form',
		 				'ihc_locker_custom_content' => '<h2>This content is locked</h2>Login To Unlock The Content!',
		 				'ihc_locker_custom_css' => '',
		 				'ihc_locker_template' => 3,
		 				'ihc_locker_login_template' => 'ihc-login-template-7',
		 				'ihc_locker_login_form' => 1,
		 				'ihc_locker_additional_links' => 1,
		 				'ihc_locker_display_sm' => 0,
		 );
		 self::save_update_locker_template($array);
		 $array = array(
		 				'ihc_locker_name' => 'Empty Showcase (only hide)',
		 				'ihc_locker_custom_content' => '',
		 				'ihc_locker_custom_css' => '.ihc-locker-wrap{}',
		 				'ihc_locker_template' => 1,
		 				'ihc_locker_login_template' => '',
		 				'ihc_locker_login_form' => 0,
		 				'ihc_locker_additional_links' => 0,
		 				'ihc_locker_display_sm' => 0,
		 );
		 self::save_update_locker_template($array);
	}

	public static function create_demo_levels(){
		/*
		 * @param none
		 * @return none
		 */
		if (!function_exists('ihc_save_level')){
			include_once IHC_PATH . 'admin/includes/functions/levels.php';
		}
		$array = array(
							'name'=>'free_demo',
							'payment_type'=>'free',
							'price'=>'',
						    'label'=>'Free',
							'description'=>'<strong>Free</strong> level allowing limited access to most of our content.',
							'price_text' => 'Sign up Now!',
							'order' => '',
							'access_type' => 'unlimited',
							'access_limited_time_type' => 'D',
							'access_limited_time_value' => '',
							'access_interval_start' => '',
							'access_interval_end' => '',
							'access_regular_time_type' => 'D',
							'access_regular_time_value' => '',
							'billing_type' => '',
							'billing_limit_num' => '2',
							'show_on' => '1',
							'afterexpire_action' => 0,
							'afterexpire_level' => -1,
							'aftercancel_action' => 0,
							'aftercancel_level' => -1,
							'grace_period' => '',
							'custom_role_level' => '-1',
							'start_date_content' => '0',
							'special_weekdays' => '',
							//trial
							'access_trial_time_value' => '',
							'access_trial_time_type' => 'D',
							'access_trial_price' => '',
							'access_trial_couple_cycles' => '',
							'access_trial_type' => 1,
		);
		ihc_save_level($array, TRUE);
		$array = array(
							'name'=>'onetime_demo',
							'payment_type'=>'payment',
							'price'=>10,
						    'label'=>'One Time Plan',
							'description'=>'<h4><strong>Premium Content!</strong></h4>
It is a <strong>one time</strong> payment of a small fee. Just have a test.',
							'price_text' => 'only $10',
							'order' => '',
							'access_type' => 'unlimited',
							'access_limited_time_type' => 'D',
							'access_limited_time_value' => '',
							'access_interval_start' => '',
							'access_interval_end' => '',
							'access_regular_time_type' => 'D',
							'access_regular_time_value' => '',
							'billing_type' => '',
							'billing_limit_num' => '2',
							'show_on' => '1',
							'afterexpire_action' => 0,
							'afterexpire_level' => -1,
							'aftercancel_action' => 0,
							'aftercancel_level' => -1,
							'grace_period' => '',
							'custom_role_level' => '-1',
							'start_date_content' => '0',
							'special_weekdays' => '',
							//trial
							'access_trial_time_value' => '',
							'access_trial_time_type' => 'D',
							'access_trial_price' => '',
							'access_trial_couple_cycles' => '',
							'access_trial_type' => 1,
		);
		ihc_save_level($array, TRUE);
		$array = array(
							'name'=>'recurring_demo',
							'payment_type'=>'payment',
							'price'=>1,
						    'label'=>'Recurring Plan',
							'description'=>'Is a <strong>Recurring</strong> Payment (monthly) on a small fee for testing purpose.
<h4>New Updates will be available!</h4>',
							'price_text' => 'only $1',
							'order' => '',
							'access_type' => 'regular_period',
							'access_limited_time_type' => 'D',
							'access_limited_time_value' => '',
							'access_interval_start' => '',
							'access_interval_end' => '',
							'access_regular_time_type' => 'M',
							'access_regular_time_value' => 1,
							'billing_type' => 'bl_ongoing',
							'billing_limit_num' => '2',
							'show_on' => '1',
							'afterexpire_action' => 0,
							'afterexpire_level' => -1,
							'aftercancel_action' => 0,
							'aftercancel_level' => -1,
							'grace_period' => '',
							'custom_role_level' => '-1',
							'start_date_content' => '0',
							'special_weekdays' => '',
							//trial
							'access_trial_time_value' => '',
							'access_trial_time_type' => 'D',
							'access_trial_price' => '',
							'access_trial_couple_cycles' => '',
							'access_trial_type' => 1,
		);
		ihc_save_level($array, TRUE);
	}

	public static function add_new_role(){
		/*
		 * @param none
		 * @return none
		 */
			add_role( 'pending_user', 'Pending', array( 'read' => false, 'level_0' => true ) );
			if (is_multisite()){
				global $wpdb;
				$table = $wpdb->base_prefix . 'blogs';
				//No query parameters required, Safe query. prepare() method without parameters can not be called
				$query = "SELECT blog_id FROM $table;";
				$data = $wpdb->get_results( $query );
				if ($data){
					foreach ($data as $object){
						if (!empty($object->blog_id) && $object->blog_id>1){
							$prefix = $wpdb->base_prefix . $object->blog_id . '_' ;
							$table = $prefix . 'options';
							$option = $prefix . 'user_roles';
							$query = $wpdb->prepare( "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name=%s ;", $option );
							$temp_data = $wpdb->get_row( $query );
							if ($temp_data && !empty($temp_data->option_value)){
								$array_unserialize = maybe_unserialize($temp_data->option_value);
								if (empty($array_unserialize['pending_user'])){
									$array_unserialize['pending_user'] = array(
																				'name' => 'Pending',
																				'capabilities' => array(
																											'read' => FALSE,
																											'level_0' => 1,
																				)
									);
									$array_serialize = serialize($array_unserialize);
									$query = $wpdb->prepare( "UPDATE {$wpdb->prefix}options SET option_value=%s WHERE option_name=%s ;", $array_serialize, $option );
									$wpdb->query( $query );
								}
							}
						}
					}
				}
			}
			add_role( 'suspended', 'Suspended', array( 'read' => false, 'level_0' => false ) );
			if (is_multisite()){
				global $wpdb;
				$table = $wpdb->base_prefix . 'blogs';
				//No query parameters required, Safe query. prepare() method without parameters can not be called
				$query = "SELECT blog_id FROM $table;";
				$data = $wpdb->get_results( $query );
				if ($data){
					foreach ($data as $object){
						if (!empty($object->blog_id) && $object->blog_id>1){
							$prefix = $wpdb->base_prefix . $object->blog_id . '_' ;
							$table = $prefix . 'options';
							$option = $prefix . 'user_roles';
							$query = $wpdb->prepare( "SELECT option_value FROM {$wpdb->prefix}options WHERE option_name=%s ;", $option );
							$temp_data = $wpdb->get_row( $query );
							if ($temp_data && !empty($temp_data->option_value)){
								$array_unserialize = maybe_unserialize($temp_data->option_value);
								if (empty($array_unserialize['suspended'])){
									$array_unserialize['suspended'] = array(
																				'name' => 'Suspended',
																				'capabilities' => array(
																											'read' => FALSE,
																											'level_0' => 0,
																				)
									);
									$array_serialize = serialize($array_unserialize);
									$query = $wpdb->prepare( "UPDATE {$wpdb->prefix}options SET option_value=%s WHERE option_name=%s ;", $array_serialize, $option );
									$wpdb->query( $query );
								}
							}
						}
					}
				}
			}
	}

	public static function default_settings_groups(){
		/*
		 * @param none
		 * @return array
		 */
		return array(
								 'payment',
						 		 'payment_paypal',
						 		 'payment_stripe',
						 		 'payment_authorize',
						 		 'payment_twocheckout',
						 		 'payment_bank_transfer',
						 		 'payment_braintree',
						 		 'payment_mollie',
						 		 'payment_paypal_express_checkout',
						 		 'payment_pagseguro',
						 		 'payment_stripe_checkout_v2',
								 'payment_stripe_connect',
						 		 'login',
						 		 'login-messages',
						 		 'general-defaults',
						 		 'general-captcha',
						 		 'general-subscription',
						 		 'general-msg',
						 		 'register',
						 		 'register-msg',
						 		 'register-custom-fields',
						 		 'opt_in',
						 		 'notifications',
						 		 'extra_settings',
						 		 'account_page',
						 		 'fb',
						 		 'tw',
						 		 'in',
						 		 'tbr',
						 		 'ig',
						 		 'vk',
						 		 'goo',
						 		 'social_media',
						 		 'double_email_verification',
						 		 'licensing',
						 		 'listing_users',
						 		 'listing_users_inside_page',
						 		 'affiliate_options',
						 		 'ihc_taxes_settings',
						 		 'admin_workflow',
						 		 'public_workflow',
						 		 'ihc_woo',
						 		 'ihc_bp',
						 		 'ihc_membership_card',
						 		 'ihc_cheat_off',
						 		 'ihc_invitation_code',
						 		 'download_monitor_integration',
						 		 'register_lite',
						 		 'individual_page',
						 		 'level_restrict_payment',
						 		 'level_subscription_plan_settings',
						 		 'gifts',
						 		 'login_level_redirect',
						 		 'register_redirects_by_level',
						 		 'wp_social_login',
						 		 'list_access_posts',
						 		 'invoices',
						 		 'woo_payment',
						 		 'badges',
						 		 'login_security',
						 		 'workflow_restrictions',
						 		 'subscription_delay',
						 		 'level_dynamic_price',
						 		 'user_reports',
						 		 'pushover',
						 		 'account_page_menu',
						 		 'mycred',
						 		 'api',
						 		 'woo_product_custom_prices',
						 		 'drip_content_notifications',
						 		 'user_sites',
						 		 'zapier',
						 		 'infusionSoft',
						 		 'kissmetrics',
						 		 'direct_login',
						 		 'reason_for_cancel',
								 'security',
		);
	}


	public static function save_settings_into_db(){
		/*
		 * @param none
		 * @return none
		 */
		//save the metas to db
		$values = self::default_settings_groups();
		foreach ($values as $value){
			ihc_return_meta_arr($value);
		}
	}

	public static function save_udate_order_meta($order_id=0, $meta_key='', $meta_value=''){
		/*
		 * @param int, string, string
		 * @return boolean
		 */
		 if ($order_id && $meta_key){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders_meta';
			 $q = $wpdb->prepare("SELECT id FROM $table WHERE order_id=%d AND meta_key=%s ", $order_id, $meta_key);
			 $exists = $wpdb->get_row($q);
			 if ($exists && !empty($exists->id)){
			 	/// update
				$q = $wpdb->prepare("UPDATE $table SET meta_value=%s WHERE order_id=%d AND meta_key=%s ", $meta_value, $order_id, $meta_key);
			 	$wpdb->query($q);
			 } else {
			 	/// insert
				$q = $wpdb->prepare("INSERT INTO $table VALUES(null, %d, %s, %s);", $order_id, $meta_key, $meta_value);
			 	$wpdb->query($q);
			 }
			 return TRUE;
		 }
		 return FALSE;
	}

	public static function delete_order($order_id=0){
		/*
		 * @param int
		 * @return none
		 */
		 if ($order_id){
			 do_action('ihc_action_before_delete_order', $order_id);
			 // @description run before an order will be deleted. @param order id (integer)

		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders';
			 $q = $wpdb->prepare("DELETE FROM $table WHERE id=%d ", $order_id);
			 $wpdb->query($q);
			 $table = $wpdb->prefix . 'ihc_orders_meta';
			 $q = $wpdb->prepare("DELETE FROM $table WHERE order_id=%d ", $order_id);
			 $wpdb->query($q);
		 }
	}

	public static function delete_order_meta($order_id=0, $meta_key=''){
		/*
		 * @param int, string
		 * @return none
		 */
		 if ($order_id && $meta_key){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders_meta';
			 $q = $wpdb->prepare("DELETE FROM $table WHERE order_id=%d AND meta_key=%s ", $order_id, $meta_key);
			 $wpdb->query($q);
		 }
	}

	public static function get_order_meta($order_id=0, $meta_key=''){
		/*
		 * @param int, string
		 * @return string
		 */
		 if ($order_id && $meta_key){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders_meta';
			 $q = $wpdb->prepare("SELECT meta_value FROM $table WHERE order_id=%d AND meta_key=%s ", $order_id, $meta_key);
			 $data = $wpdb->get_row($q);
			 if ($data && isset($data->meta_value)){
			 	return $data->meta_value;
			 }
		 }
		 return '';
	}

	public static function get_order_id_by_meta_value_and_meta_type($meta_key='', $meta_value=''){
		/*
		 * @param string, string
		 * @return int
		 */
		 if ($meta_key && $meta_value){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders_meta';
			 $q = $wpdb->prepare("SELECT order_id FROM $table WHERE meta_key=%s AND meta_value=%s ", $meta_key, $meta_value);
			 $data = $wpdb->get_row($q);
			 if ($data && isset($data->order_id)){
			 	return $data->order_id;
			 }
		 }
		 return 0;
	}

	public static function get_all_order_metas($order_id=0){
		/*
		 * @param int
		 * @return array
		 */
		 $array = array();
		 if ($order_id){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders_meta';
			 $q = $wpdb->prepare("SELECT meta_key, meta_value FROM $table WHERE order_id=%d", $order_id);
			 $data = $wpdb->get_results($q);
			 if ($data){
			 	foreach ($data as $object){
			 		$array[$object->meta_key] = $object->meta_value;
				}
			 }
		 }
		 return $array;
	}

	/**
	 * @param int
 	 * @param int
 	 * @param int
 	 * @param array
	 * @return array
	 */
	public static function get_all_order($limit=30, $offset=0, $uid=0, $params=[])
	{
		 global $wpdb;
		 $array = array();
		 $table = $wpdb->prefix . 'ihc_orders';
		 $q = "SELECT o.id,o.uid,o.lid,o.amount_type,o.amount_value,o.automated_payment,o.status,o.create_date FROM $table AS o ";

		 if ( !empty( $params['q'] ) ){
			 $q .= " INNER JOIN {$wpdb->users} u ";
		 }

		 $q .= " WHERE 1=1";
		 if ($uid){
		 	$q .= " AND o.uid=%d";
			$q = $wpdb->prepare($q, $uid);
		 }

		 // status
		 if ( !empty( $params['status'] ) ){
			 $q .= $wpdb->prepare( " AND o.status=%s ", $params['status'] );
		 }

		 // search keyword
		 if ( !empty( $params['q'] ) ){
			 	$q .= $wpdb->prepare( " AND ( u.user_login LIKE %s OR u.user_nicename LIKE %s OR u.user_email LIKE %s OR o.amount_value=%s ) ",
								'%' . $params['q']. '%',
								'%' . $params['q']. '%',
								'%' . $params['q'] . '%',
								'%' . $params['q']. '%' );
		 }

		 // start time
		 if ( !empty( $params['start_time'] ) ){
			 $q .= $wpdb->prepare( " AND o.create_date>%s ", $params['start_time'] );
		 }

		 // end time
		 if ( !empty( $params['end_time'] ) ){
			 $q .= $wpdb->prepare( " AND o.create_date<%s ", $params['end_time'] );
		 }


		 $q .= " ORDER BY o.id DESC LIMIT %d OFFSET %d;";
		 $q = $wpdb->prepare($q, $limit, $offset);
		 $data = $wpdb->get_results($q);
		 if ($data){
		 	foreach ($data as $object){
		 		$temp = (array)$object;
				$temp['metas'] = self::get_all_order_metas($temp['id']);
				$temp['user'] = self::get_username_by_wpuid($temp['uid']);
				$temp['transaction_id'] = (empty($temp['metas']) || empty($data['metas']['transaction_id'])) ? self::get_transaction_id_by_order_id($temp['id']) : $temp['metas']['transaction_id'];
				if (empty($temp['user'])){
					$temp['user'] = '-';
				}
				///payment type
				if (empty($temp['metas']['ihc_payment_type'])){
					$temp['metas']['ihc_payment_type'] = self::get_payment_type_by_transaction_id($temp['transaction_id']);
				}
				$temp['level'] = self::get_level_name_by_lid($temp['lid']);
		 		$array[] = $temp;
		 	}
		 }
		 return $array;
	}

	public static function get_payment_type_by_transaction_id($id=0){
		/*
		 * @param int
		 * @return string
		 */
		 if ($id){
		 	 global $wpdb;
		 	 $table = $wpdb->prefix . 'indeed_members_payments';
			 $q = $wpdb->prepare("SELECT id,txn_id,u_id,payment_data,history,orders,paydate FROM $table WHERE id=%s", $id);
			 $data = $wpdb->get_row($q);
			 if ($data && !empty($data->payment_data)){
				$temp = json_decode($data->payment_data, TRUE);
				return (empty($temp['ihc_payment_type'])) ? '' : $temp['ihc_payment_type'];
			 }
		 }
		 return '';
	}

	/**
	 * @param none
 	 * @param array
	 * @return int
	 */
	public static function get_count_orders($uid=0, $params=[])
	{
		 global $wpdb;
		 $table = $wpdb->prefix . 'ihc_orders';
		 $q = "SELECT COUNT(id) as num FROM $table";
		 $q .= " WHERE 1=1";
		 if ($uid){
		 	$q .= " AND uid=%d ";
			$q = $wpdb->prepare($q, $uid);
		 }
		 $data = $wpdb->get_row($q);
		 return (empty($data->num)) ? 0 : $data->num;
	}

	public static function get_username_by_wpuid($wpuid=0){
		/*
		 * @param int
		 * @return string
		 */
		if ($wpuid){
			global $wpdb;
			$table = $wpdb->base_prefix . 'users';
			$q = $wpdb->prepare("SELECT user_login FROM $table WHERE ID=%d ", $wpuid);
			$data = $wpdb->get_row($q);
			if (!empty($data->user_login)){
				return $data->user_login;
			}
		}
		return '';
	}

	/*
	 * @param string
	 * @return int
	 */
	public static function get_wpuid_by_email($email=''){
		global $wpdb;
		if ($email){
			$table = $wpdb->base_prefix . 'users';
			$q = $wpdb->prepare("SELECT ID FROM $table WHERE user_email=%s ", $email);
			$data = $wpdb->get_row($q);
			if ($data && !empty($data->ID)){
				return $data->ID;
			}
		}
		return 0;
	}
	/*
	 * @param string
	 * @return int
	 */
	public static function get_wpuid_by_username($username=''){
		global $wpdb;
		if ($username){
			$table = $wpdb->base_prefix . 'users';
			$q = $wpdb->prepare("SELECT ID FROM $table WHERE user_login=%s ", $username);
			$data = $wpdb->get_row($q);
			if ($data && !empty($data->ID)){
				return $data->ID;
			}
		}
		return 0;
	}

	public static function get_level_name_by_lid($lid=0){
		/*
		 * @param int
		 * @return string
		 */
		if ( !$lid ){
				return '';
		}
		return \Indeed\Ihc\Db\Memberships::getMembershipLabel( $lid );
	}


	/*
	 * @param string (level slug)
	 * @return int
	 */
	public static function get_lid_by_level_slug($slug=''){
		if ($slug){
			$level = \Indeed\Ihc\Db\Memberships::getOneByName( $slug );
			if ( isset( $level['id'] ) ){
					return $level['id'];
			}
		}
		return false;
	}

	public static function getLevelsDetails()
	{
			$levels = \Indeed\Ihc\Db\Memberships::getAll();
			if ( !$levels ){
					return array();
			}
			$array = array();
			foreach ($levels as $lid=>$data){
					$array[ $lid ] = array(
																		'slug'		=> $data['name'],
																		'label'		=> $data['label'],
					);
			}
			return $array;
	}


	/*
	 * @param int
	 * @return boolean
	 */
	public static function does_level_exists($lid=-1){
		if ($lid>-1){
			$data = \Indeed\Ihc\Db\Memberships::getOne( $lid );
			if ( $data ){
					return true;
			}
		}
		return FALSE;
	}

	public static function get_transaction_id_by_order_id($order_id=0){
		/*
		 * @param int
		 * @return int
		 */
		if ($order_id){
			global $wpdb;
			$p = $wpdb->prefix . 'indeed_members_payments';
			$o = $wpdb->prefix . 'ihc_orders';
			$q = $wpdb->prepare("SELECT p.orders as orders, p.id as id FROM $p p INNER JOIN $o o ON p.u_id=o.uid WHERE o.id=%d ", $order_id);
			$data = $wpdb->get_results($q);
			if ($data){
				foreach ($data as $object){
					if (isset($object->orders)){
						$temp_data = maybe_unserialize($object->orders);
						if ($temp_data && in_array($order_id, $temp_data)){
							return $object->id;
						}
					}
				}
			}
		}
		return 0;
	}

	public static function get_order_data_by_id($order_id=0){
		/*
		 * @param none
		 * @return array
		 */
		 $array = array();
		 if ($order_id){
			 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders';
			 $q = $wpdb->prepare("SELECT id,uid,lid,amount_type,amount_value,automated_payment,status,create_date FROM $table WHERE id=%d", $order_id);
			 $data = $wpdb->get_row($q);
			 if ($data){
			 	$array = (array)$data;
				$array['metas'] = self::get_all_order_metas($array['id']);
				$array['user'] = self::get_username_by_wpuid($array['uid']);
				$array['transaction_id'] = (empty($array['metas']) || empty($array['metas']['transaction_id'])) ? self::get_transaction_id_by_order_id($array['id']) : $array['metas']['transaction_id'];
				if (empty($array['user'])){
					$array['user'] = '-';
				}
				$array['level'] = self::get_level_name_by_lid($array['lid']);
			 }
		 }
		 return $array;
	}


	/// TAXES
	public static function save_tax($post_data=array()){
		/*
		 * @param array
		 * @return boolean
		 */
		 if ($post_data){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_taxes';
			 if (empty($post_data['id'])){
			 	//insert
				$q = $wpdb->prepare("SELECT id,country_code,state_code,amount_value,label,description,status FROM $table
																WHERE
																country_code=%s
																AND label=%s
																AND state_code=%s ", $post_data['country_code'], $post_data['label'], $post_data['state_code']);
			 	$data = $wpdb->get_row($q);
			 	if (empty($data)){
					$q = $wpdb->prepare("INSERT INTO $table
				 						VALUES(null,
				 								%s,
				 								%s,
				 								%s,
				 								%s,
				 								%s,
				 								%s );",
												$post_data['country_code'],
												$post_data['state_code'],
												$post_data['amount_value'],
												$post_data['label'],
												$post_data['description'],
												$post_data['status']
					);
				 	$wpdb->query($q);
					return TRUE;
			 	} else {
			 		return FALSE;
			 	}
			 } else {
			 	//update
				$q = $wpdb->prepare("SELECT id FROM $table WHERE country_code=%s AND label=%s ", $post_data['country_code'], $post_data['label']);
			 	$data = $wpdb->get_row($q);
			 	if (isset($data) && isset($data->id) && $data->id!=$post_data['id']){
			 		return FALSE;
			 	}
				$q = $wpdb->prepare("UPDATE $table SET
				 								country_code=%s,
				 								state_code=%s,
				 								amount_value=%s,
				 								label=%s,
				 								description=%s,
				 								status=%s
				 						WHERE id=%d
				", $post_data['country_code'], $post_data['state_code'], $post_data['amount_value'], $post_data['label'],
				$post_data['description'], $post_data['status'], $post_data['id']);
				$wpdb->query($q);
				return TRUE;
			 }
		 }
		 return FALSE;
	}

	public static function get_tax($id=0){
		/*
		 * @param int
		 * @return array
		 */
		 if (empty($id)){
		 	return array(
							'id' => 0,
							'country_code' => '',
							'state_code' => '',
							'amount_value' => '',
							'label' => '',
							'description' => '',
							'status' => 1,
			);
		 } else {
		 	global $wpdb;
			$table = $wpdb->prefix . 'ihc_taxes';
			$q = $wpdb->prepare("SELECT id,country_code,state_code,amount_value,label,description,status FROM $table WHERE id=%d;", $id);
			$data = $wpdb->get_row($q);
			if ($data){
					$domain = 'ihc';
					$languageCode = indeed_get_current_language_code();
					$wmplName = $data->id . '_label';
					$data->label = apply_filters( 'wpml_translate_single_string', $data->label, $domain, $wmplName, $languageCode );
					$wmplName = $data->id . '_description';
					$data->description = apply_filters( 'wpml_translate_single_string', $data->description, $domain, $wmplName, $languageCode );
					return (array)$data;
			}
		 }
	}

	public static function get_all_taxes(){
		/*
		 * @param none
		 * @return array
		 */
		$array = array();
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_taxes';
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SELECT id,country_code,state_code,amount_value,label,description,status FROM $table;";
		$data = $wpdb->get_results( $query );
		if ($data){
			foreach ($data as $object){
					$domain = 'ihc';
					$languageCode = indeed_get_current_language_code();
					$wmplName = $object->id . '_label';
					$object->label = apply_filters( 'wpml_translate_single_string', $object->label, $domain, $wmplName, $languageCode );
					$wmplName = $object->id . '_description';
					$object->description = apply_filters( 'wpml_translate_single_string', $object->description, $domain, $wmplName, $languageCode );
					$array[] = (array)$object;
			}
		}
		return $array;
	}

	public static function delete_tax($id=0){
		/*
		 * @param int
		 * @return none
		 */
		 if ($id){
			global $wpdb;
			$table = $wpdb->prefix . 'ihc_taxes';
			$q = $wpdb->prepare("DELETE FROM $table WHERE id=%d;", $id);
			$wpdb->query($q);
		 }
	}

	public static function get_taxes_by_country($country='', $state=''){
		/*
		 * @param string, string
		 * @return array
		 */
		$array = array();
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_taxes';
		$q = $wpdb->prepare("SELECT id,country_code,state_code,amount_value,label,description,status FROM $table WHERE country_code=%s", $country);
		if ($state){
			$q .= " AND state_code=%s ";
			$q = $wpdb->prepare($q, $state);
			$data = $wpdb->get_results($q);
			if (empty($data)){
				$q = $wpdb->prepare("SELECT id,country_code,state_code,amount_value,label,description,status FROM $table WHERE country_code=%s AND state_code='' ", $country);
				$data = $wpdb->get_results($q);
			}
		} else {
			$q .= " AND state_code='' ";
			$data = $wpdb->get_results($q);
		}

		if ($data){
			foreach ($data as $object){
					$domain = 'ihc';
					$languageCode = indeed_get_current_language_code();
					$wmplName = $object->id . '_label';
					$object->label = apply_filters( 'wpml_translate_single_string', $object->label, $domain, $wmplName, $languageCode );
					$wmplName = $object->id . '_description';
					$object->description = apply_filters( 'wpml_translate_single_string', $object->description, $domain, $wmplName, $languageCode );
					$array[] = (array)$object;
			}
		}
		return $array;
	}

	public static function get_taxes_rate_for_user($uid=0){
		/*
		 * @param int (user id)
		 * @return array
		 */
		 if (ihc_is_magic_feat_active('taxes') && $uid){
		 	 global $wpdb;
			 $country = get_user_meta($uid, 'ihc_country', TRUE);
 			 $state = get_user_meta($uid, 'ihc_state', TRUE);
			 $taxes = self::get_taxes_by_country($country, $state);
			 $return = array();
			 if ($taxes){
			 	/// taxes by country & state
			 	foreach ($taxes as $array){
			 		$return[$array['label']] = $array['amount_value'] . '%';
			 	}
			 } else {
			 	/// default taxes
			 	$taxes_settings = ihc_return_meta_arr('ihc_taxes_settings');
				if (!empty($taxes_settings['ihc_default_tax_label']) && !empty($taxes_settings['ihc_default_tax_value'])){
					$return[$taxes_settings['ihc_default_tax_label']] = $taxes_settings['ihc_default_tax_value'] . '%';
				}
			 }
			 return $return;
		 }
		 return array();
	}

	public static function increment_dashboard_notification($type=''){
		/*
		 * @param string ( affiliates || referrals )
		 * @return none
	 	 */
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_dashboard_notifications';
		$q = $wpdb->prepare("UPDATE $table SET value=value+1 WHERE type=%s;", $type);
		$wpdb->query($q);
		do_action('ihc_dashboard_notification_action', $type);
		// @description run when a new dashboard notification was been added. @param type of notification (string)
	}

	public static function reset_dashboard_notification($type=''){
		/*
		 * @param string ( affiliates || referrals )
		 * @return none
		 */
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_dashboard_notifications';
		$q = $wpdb->prepare("UPDATE $table SET value=0 WHERE type=%s;", $type);
		$wpdb->query($q);
	}

	public static function get_dashboard_notification_value($type=''){
		/*
		 * @param string ( affiliates || referrals )
		 * @return none
		 */
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_dashboard_notifications';
		$q = $wpdb->prepare("SELECT value FROM $table WHERE type=%s;", $type);
		$data = $wpdb->get_row($q);
		return (empty($data->value)) ? 0 : $data->value;
	}

	public static function save_update_locker_template($post_data=array()){
		/*
		 * @param array
		 * @return none
		 */
		$option_name = 'ihc_lockers';
		$meta_keys = ihc_locker_meta_keys();
		foreach ($meta_keys as $k=>$v){
			if (isset($post_data[$k])){
				$data[$k] = $post_data[$k];
			}
		}
		$data_db = get_option($option_name);
		if ($data_db!==FALSE){
			if (isset($post_data['template_id'])){
				$data_db[$post_data['template_id']] = $data;
			} else {
				end($data_db);
				$key = key($data_db);
				$key++;
				$data_db[$key] = $data;
			}
			update_option($option_name, $data_db);
		} else {
			if ( !is_array( $data_db ) && $data_db == false ){
					$data_db = [];
			}
			$data_db[1] = $data;
			add_option($option_name, $data_db);
		}
	}

	public static function cheat_off_get_hash($uid=0){
		/*
		 * @param int
		 * @return string
		 */
		 if ($uid){
			 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_cheat_off';
			 $q = $wpdb->prepare("SELECT hash FROM $table WHERE uid=%d;", $uid);
		 	 $data = $wpdb->get_row($q);
			 if (!empty($data) && !empty($data->hash)){
			 	return $data->hash;
			 }
		 }
		 return '';
	}

	public static function cheat_off_set_hash($uid=0, $hash=''){
		/*
		 * @param int, string
		 * @return boolean
		 */
		 if ($uid && $hash){
			 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_cheat_off';
			 $q = $wpdb->prepare("SELECT hash FROM $table WHERE uid=%d;", $uid);
		 	 $data = $wpdb->get_row($q);
			 if (!empty($data) && !empty($data->hash)){
			 	/// update
				$q = $wpdb->prepare("UPDATE $table SET hash=%s WHERE uid=%d;", $hash, $uid);
			 	return $wpdb->query($q);
			 } else {
			 	/// insert
				$q = $wpdb->prepare("INSERT INTO $table VALUES(%d, %s);", $uid, $hash);
			 	return $wpdb->query($q);
			 }
		 }
		 return FALSE;
	}

	public static function invitation_code_add_new($data=array()){
		/*
		 * @param array
		 * @return boolean
		 */
		 if ($data){
		 	global $wpdb;
			$table = $wpdb->prefix . 'ihc_invitation_codes';
			if (empty($data['repeat'])){
				$data['repeat'] = 1;
			}
			if (empty($data['how_many_codes'])){
				///single
				if (!empty($data['code'])){
					$data['code'] = ihc_make_string_simple($data['code']);
					$q = $wpdb->prepare("SELECT id,code,settings,submited,repeat_limit,status FROM $table WHERE code=%s;", $data['code']);
				 	$check = $wpdb->get_row($q);
				 	if ($check && !empty($check->id)){
				 		return FALSE; ///already exists
				 	}
					$q = $wpdb->prepare("INSERT INTO $table VALUES(null, %s, '', 0, %s, 1);", $data['code'], $data['repeat']);
					$wpdb->query($q);
					return TRUE;
				}
			} else {
				/// multiple
				$prefix = $data['code_prefix'];
				$length = $data['code_length'] - strlen($data['code_prefix']);
				$limit = $data['how_many_codes'];
				while ($limit){
					$code = ihc_random_str($length);
					$code = $prefix . $code;
					$code = str_replace(' ', '', $code);
					$code = ihc_make_string_simple($code);
					$q = $wpdb->prepare("SELECT id,code,settings,submited,repeat_limit,status FROM $table WHERE code=%s ", $code);
					$check = $wpdb->get_row($q);
					if ($check){
						continue;
					}
					$q = $wpdb->prepare("INSERT INTO $table VALUES(null, %s, '', 0, %s, 1);", $code, $data['repeat']);
					$wpdb->query($q);
					$limit--;
				}
				return TRUE;
			}
		 }
		 return FALSE;
	}

	public static function invitation_code_delete($id=0){
		/*
		 * @param int
		 * @return boolean
		 */
		 if (!empty($id)){
		 	global $wpdb;
			$table = $wpdb->prefix . 'ihc_invitation_codes';
			$q = $wpdb->prepare("DELETE FROM $table WHERE id=%d ", $id);
			$wpdb->query($q);
			return TRUE;
		 }
		 return FALSE;
	}

	public static function invitation_code_check($code=''){
		/*
		 * @param string
		 * @return boolean
		 */
		 if (!empty($code)){
		 	global $wpdb;
			$table = $wpdb->prefix . 'ihc_invitation_codes';
			$q = $wpdb->prepare("SELECT id,code,settings,submited,repeat_limit,status FROM $table WHERE code=%s;", $code);
			$check = $wpdb->get_row($q);
			if ($check && isset($check->submited) && isset($check->repeat_limit)){
				if ($check->submited<$check->repeat_limit){
					return TRUE;
				}
			}
		 }
		 return FALSE;
	}

	public static function invitation_code_increment_submited_value($code=''){
		/*
		 * @param string
		 * @return boolean
		 */
		 if ($code){
		 	global $wpdb;
			$table = $wpdb->prefix . 'ihc_invitation_codes';
			$q = $wpdb->prepare("SELECT submited, repeat_limit FROM $table WHERE code=%s;", $code);
			$check = $wpdb->get_row($q);
			if ($check && isset($check->submited)){
				$increment_value = $check->submited + 1;
				if ($increment_value<=$check->repeat_limit){
					$q = $wpdb->prepare("UPDATE $table SET submited=%d WHERE code=%s;", $increment_value, $code);
					$wpdb->query($q);
					return TRUE;
				}
			}
		 }
		 return FALSE;
	}

	public static function invitation_code_get_all(){
		/*
		 * @param none
		 * @return array
		 */
		$array = array();
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_invitation_codes';
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SELECT id,code,settings,submited,repeat_limit,status FROM $table;";
		$data = $wpdb->get_results( $query );
		if ($data){
			foreach ($data as $object){
				$array[] = (array)$object;
			}
		}
		return $array;
	}

	public static function invitation_code_does_exist_codes(){
		/*
		 * @param none
		 * @return boolean
		 */
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_invitation_codes';
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "SELECT COUNT( id ) as c FROM $table;";
		$data = $wpdb->get_row( $query );
		if ($data && isset($data->c) && $data->c>0){
			return TRUE;
		}
		return FALSE;
	}

	public static function download_monitor_get_count_for_user($uid=0, $type='files'){
		/*
		 * @param int, string. uid set as -1 means all registered users
		 * @return int
		 */
		 global $wpdb;
		 $table = $wpdb->base_prefix . 'download_log';

		 if ($type=='files'){
		 	$q = "SELECT COUNT(DISTINCT download_id) as c FROM $table WHERE";
		 } else {
		 	$q = "SELECT COUNT(ID) as c FROM $table WHERE";
		 }
		 if ($uid==-1){
		 	/// all registered users
		 	$q .= " user_id<>0;";
		 } else {
		 	$q .= " user_id=%d;";
			$q = $wpdb->prepare($q, $uid);
		 }
		 $data = $wpdb->get_row($q);
		 if ($data && !empty($data->c)){
		 	return (int)$data->c;
		 }
		 return 0;
	}

	public static function get_payment_tyoe_by_userId_levelId($uid=0, $lid=0){
		/*
		 * @param int, int
		 * @return string
		 */
		 $payment_type = '';
		 if ($uid && $lid){
		 	global $wpdb;
		 	$table = $wpdb->prefix . 'indeed_members_payments';
			$q = $wpdb->prepare("SELECT payment_data FROM $table WHERE u_id=%d ORDER BY paydate DESC;", $uid);
			$data = $wpdb->get_results($q);
			if ($data){
				foreach ($data as $object){
					$array = json_decode($object->payment_data, TRUE);


					if (empty($array['level']) && !empty($array['custom'])){
						$temp_paypal_data = json_decode(stripslashes($array['custom']), TRUE);
						$array['level'] = (isset($temp_paypal_data['level_id'])) ? $temp_paypal_data['level_id'] : '';
					}

					if (isset($array['level']) && $array['level']!='' && isset($array['ihc_payment_type'])){
						if ($lid==$array['level']){
							$payment_type = $array['ihc_payment_type'];
							break;
						}
					} else if (isset($array['custom'])){
						$custom = json_decode($array['custom'], TRUE);
						if ( isset( $custom['level_id'] ) && $lid==$custom['level_id']){
							$payment_type = 'paypal';
							break;
						}
					}
					if ( isset( $array['lid'] ) && $array['lid'] == $lid && isset($array['ihc_payment_type']) ){
							$payment_type = $array['ihc_payment_type'];
							break;
					}
				}
			}
		 }
		 return $payment_type;
	}

	public static function get_page_slug($post_id=0){
		/*
		 * @param int
		 * @return string
		 */
		 if ($post_id){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'posts';
			 $q = $wpdb->prepare("SELECT post_name FROM $table WHERE ID=%d;", $post_id);
			 $data = $wpdb->get_row($q);
			 if ($data && !empty($data->post_name)){
			 	return $data->post_name;
			 }
		 }
		 return '';
	}

	public static function get_users_with_no_individual_page(){
	    /*
	     * @param none
	     * @return array
	     */
	     $array = array();
	     global $wpdb;
	     $table = $wpdb->base_prefix . 'usermeta';
			 //No query parameters required, Safe query. prepare() method without parameters can not be called
			 $query = "SELECT DISTINCT user_id, meta_value FROM $table WHERE meta_key='ihc_individual_page' GROUP BY user_id;";
	     $data = $wpdb->get_results( $query );
	     $not_in_string = '';
	     if ($data){
	         foreach ($data as $object){
	         	 if (self::post_does_exists($object->meta_value)){
	             	$not_in[] = $object->user_id;
	         	 }
	         }
	        if ($not_in){
	            $not_in_string = implode(',', $not_in);
	        }
	     }
	     $table = $wpdb->base_prefix . 'users';
	     $q = "SELECT ID FROM $table WHERE 1=1";
	     if (!empty($not_in_string)){
	         $q .= " AND ID NOT IN ($not_in_string) ";
	     }
	     $our_target = $wpdb->get_results($q);
	     if ($our_target){
	         foreach ($our_target as $u){
	             $array[] = $u->ID;
	         }
	     }
	     return $array;
	}

	public static function post_does_exists($post_id=0){
		/*
		 * @param int
		 * @return boolean
		 */
		 if ($post_id){
		 	 global $wpdb;
			 $table = $wpdb->base_prefix . 'posts';
			 $q = $wpdb->prepare("SELECT post_title FROM $table WHERE ID=%d LIMIT 1", $post_id);
			 $data = $wpdb->get_row($q);
			 if ($data && isset($data->post_title)){
			 	return TRUE;
			 }
		 }
		 return FALSE;
	}

	public static function get_excluded_payment_types_for_level_id($level_id=-1){
		/*
		 * @param int
		 * @return string
		 */
		 if ($level_id>-1){
			 if ( !get_option( 'ihc_level_restrict_payment_enabled' ) ){
				 	return;
			 }
		 	 $data = get_option('ihc_level_restrict_payment_values');
			 if ($data && !empty($data[$level_id])){
			 	return $data[$level_id];
			 }
		 }
		 return '';
	}

	public static function get_default_payment_gateway_for_level($lid=-1, $default_payment=''){
		/*
		 * @param int, string
		 * @return string
		 */
		 if ($lid>-1){
		 	 $data = get_option('ihc_levels_default_payments');
			 $levelsVsMemberships = get_option( 'ihc_level_restrict_payment_enabled', 0 );
			 if ( $levelsVsMemberships && $data && !empty($data[$lid]) && $data[$lid]!=-1){
			 	if (!function_exists('ihc_check_payment_status')){
					require_once IHC_PATH . 'admin/includes/functions.php';
				}
				$check = ihc_check_payment_status($data[$lid]);
				if ($check['status'] && $check['settings']=='Completed'){
					return $data[$lid];
				}
			 }
		 }
		 return $default_payment;
	}

	public static function does_this_user_bought_something($uid=0){
		/*
		 * @param int
		 * @return boolean
		 */
		 $bool = FALSE;
		 if ($uid){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'indeed_members_payments';
			 $q = $wpdb->prepare("SELECT payment_data FROM $table WHERE u_id=%d", $uid);
			 $data = $wpdb->get_results($q);
			 if ($data){
			 	foreach ($data as $object){
			 		$temp = json_decode($object->payment_data, TRUE);
					if (!empty($temp['amount'])){
						$bool = TRUE;
						break;
					}
			 	}
			 }
		 }
		 return $bool;
	}

	public static function gift_templates_get_metas($id=0){
		/*
		 * @param int
		 * @return array
		 */
		 if (empty($id)){
		 	$array = array(
							'id' => 0,
							"discount_type" => "price",
							"discount_value" => '',
							'target_level' => -1,
							"reccuring" => '',
			);
		 } else {
		 	global $wpdb;
			$table = $wpdb->prefix . 'ihc_gift_templates';
			$q = $wpdb->prepare("SELECT lid, settings FROM $table WHERE id=%d", $id);
			$data = $wpdb->get_row($q);
			if ($data && isset($data->lid) && isset($data->settings)){
				$array = maybe_unserialize($data->settings);
				$array['lid'] = $data->lid;
			}
		 }
		 return $array;
	}

	public static function gifts_do_save($data=array()){
		/*
		 * @param array
		 * @return boolean
		 */
		 if ($data){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_gift_templates';
			 if (empty($data['id'])){
			 	///insert
			 	$settings = array(
									'discount_type' => $data['discount_type'],
									"discount_value" => $data['discount_value'],
									'target_level' => $data['target_level'],
									"reccuring" => $data['reccuring'],
				);
				$settings = serialize($settings);
				$q = $wpdb->prepare("INSERT INTO $table VALUES(null, %d, %s, 1);", $data['lid'], $settings);
			 	$wpdb->query($q);
			 } else {
			 	///update
			 	$settings = array(
									'discount_type' => $data['discount_type'],
									"discount_value" => $data['discount_value'],
									'target_level' => $data['target_level'],
									"reccuring" => $data['reccuring'],
				);
				$settings = serialize($settings);
				$q = $wpdb->prepare("UPDATE $table SET lid=%d, settings=%s WHERE id=%d;", $data['lid'], $settings, $data['id']);
			 	$wpdb->query($q);
			 }

		 }
		 return FALSE;
	}

	public static function gift_get_all_items($a_lid=''){
		/*
		 * @param int (aworded level id)
		 * @return array
		 */
		 global $wpdb;
		 $array = array();
		 $table = $wpdb->prefix . 'ihc_gift_templates';
		 $q = "SELECT id,lid,settings,status FROM $table";
		 if ($a_lid!=''){
				$q = $wpdb->prepare($q . " WHERE lid=%d OR lid=-1;", $a_lid);
		 }
		 $data = $wpdb->get_results($q);
		 if ($data){
		 	foreach ($data as $object){
		 		$temp = maybe_unserialize($object->settings);
				$item = $temp;
				$item['lid'] = $object->lid;
				$array[$object->id] = $item;
		 	}
		 }
		 return $array;
	}

	public static function gifts_do_delete($id=0){
		/*
		 * @param int
		 * @return none
		 */
		 if ($id){
			 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_gift_templates';
			 $q = $wpdb->prepare("DELETE FROM $table WHERE id=%d", $id);
			 $wpdb->query($q);
		 }
	}

	public static function get_gifts_by_uid($uid=0){
		/*
		 * @param int (user id)
		 * @return array
		 */
		 $array = array();
		 if ($uid){
		 	 $gifts = get_user_meta($uid, 'ihc_gifts', TRUE);
			 if ($gifts){
				 	 foreach ($gifts as $arr){
						 	if ( !$arr || !isset( $arr['code'] ) ){
									continue;
							}
							$temp = ihc_get_coupon_by_code($arr['code']);
							$temp['is_active'] = self::is_gift_stil_active($arr['code']);
							$array[] = $temp;
				 	 }
			 }
		 }
		 return $array;
	}

	public static function is_gift_stil_active($code=''){
		/*
		 * @param string
		 * @return bool
		 */
		 if ($code){
			 $coupon_data = ihc_get_coupon_by_code($code);
			 if ($coupon_data){
			 	if ($coupon_data['submited_coupons_count']<1){
			 		return TRUE;
			 	}
			 }
		 }
		 return FALSE;
	}

	public static function get_all_gift_codes($limit=30, $offset=0){
		/*
		 * @param int
		 * @return array
		 */
		 $array = array();
		 global $wpdb;
		 $table = $wpdb->prefix . 'ihc_coupons';
		 $q = $wpdb->prepare("SELECT id,code,settings,submited_coupons_count,status FROM $table WHERE status=2 ORDER BY id DESC LIMIT %d OFFSET %d ", $limit, $offset);
		 $data = $wpdb->get_results($q);
		 if ($data){
		 	foreach ($data as $object){
		 		$temp = maybe_unserialize($object->settings);
		 		$temp['username'] = self::get_username_by_wpuid((isset($temp['uid'])) ? $temp['uid'] : '');
				$temp['code'] = $object->code;
				$temp['is_active'] = self::is_gift_stil_active($object->code);
				$array[$object->id] = $temp;
		 	}
		 }
		 return $array;
	}

	public static function get_count_all_gift_codes(){
		/*
		 * @param none
		 * @return int
		 */
		 global $wpdb;
		 $table = $wpdb->prefix . 'ihc_coupons';
		 //No query parameters required, Safe query. prepare() method without parameters can not be called
		 $query = "SELECT COUNT( id ) as c FROM $table WHERE status=2";
		 $data = $wpdb->get_row( $query );
		 if ($data && isset($data->c)){
		 	return $data->c;
		 }
		 return 0;
	}

	public static function do_delete_generated_gift_code($coupon_id=0){
		/*
		 * @param int
		 * @return none
		 */
		 if ($coupon_id){
		 	 $metas = ihc_get_coupon_by_id($coupon_id);
			 if (isset($metas['uid']) && isset($metas['code'])){
			 	 $code = $metas['code'];
			 	 $meta_user = get_user_meta($metas['uid'], 'ihc_gifts', TRUE);
				 $key = ihc_array_value_exists($meta_user, $code, 'code');
				 if ($key!==FALSE){
				 	 unset($meta_user[$key]);
					 update_user_meta($metas['uid'], 'ihc_gifts', $meta_user);
				 }
			 }
		 	 ihc_delete_coupon($coupon_id);
		 }
	}

	public static function is_order_id_for_uid($uid=0, $order_id=0){
		/*
		 * check if a order belong to a user
		 * @param int, int
		 * @return boolean
		 */
		 if ($uid && $order_id){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders';
			 $q = $wpdb->prepare("SELECT id,uid,lid,amount_type,amount_value,automated_payment,status,create_date FROM $table WHERE uid=%d AND id=%d;", $uid, $order_id);
			 $check = $wpdb->get_row($q);
			 if ($check && !empty($check->id)){
			 	return TRUE;
			 }
		 }
		 return FALSE;
	}

	public static function get_uid_by_order_id($order_id=0){
		/*
		 * @param int
		 * @return int
		 */
		 if ($order_id){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders';
			 $q = $wpdb->prepare("SELECT uid FROM $table WHERE id=%d;", $order_id);
			 $check = $wpdb->get_row($q);
			 if ($check && !empty($check->uid)){
			 	return $check->uid;
			 }
		 }
		 return 0;
	}

	public static function transactions_get_total_for_user($uid=0){
		/*
		 * @param int
		 * @return int
		 */
		 if ($uid){
		 	 global $wpdb;
			 $table = $wpdb->prefix . "indeed_members_payments";
			 $q = $wpdb->prepare("SELECT COUNT( id ) as c FROM $table WHERE u_id=%d", $uid);
			 $data = $wpdb->get_row($q);
			 if ($data && !empty($data->c)){
			 	return $data->c;
			 }
		 }
		 return 0;
	}

	public static function transaction_get_items_for_user($limit=999, $offset=0, $uid=0){
		/*
		 * @param int, int, int
		 * @return array
		 */
		 if ($uid){
		 	 global $wpdb;
		 	 $table = $wpdb->prefix . "indeed_members_payments";
			 $q = "SELECT id,txn_id,u_id,payment_data,history,orders,paydate FROM $table";
			 $q .= " WHERE 1=1";
			 $q .= " AND u_id=%d";
			 $q .= " ORDER BY paydate DESC LIMIT %d OFFSET %d;";
			 $q = $wpdb->prepare($q, $uid, $limit, $offset);
			 $data = $wpdb->get_results($q);
			 if (!empty($data)){
				 return $data;
			 }
		 }
		 return array();
	}

	public static function user_get_register_date($uid=0){
		/*
		 * @param int
		 * @return string
		 */
		 if ($uid){
		 	 global $wpdb;
			 $table = $wpdb->base_prefix . 'users';
			 $q = $wpdb->prepare("SELECT user_registered FROM $table WHERE ID=%d", $uid);
			 $data = $wpdb->get_row($q);
			 if ($data && !empty($data->user_registered)){
			 	return $data->user_registered;
			 }
		 }
		 return '';
	}

	public static function user_get_email($uid=0){
		/*
		 * @param int
		 * @return string
		 */
		 if ($uid){
		 	 global $wpdb;
			 $table = $wpdb->base_prefix . 'users';
			 $q = $wpdb->prepare("SELECT user_email FROM $table WHERE ID=%d;", $uid);
			 $data = $wpdb->get_row($q);
			 if ($data && !empty($data->user_email)){
			 	return $data->user_email;
			 }
		 }
		 return '';
	}

	public static function update_order_status($order_id=0, $new_status=''){
		/*
		 * @param int, string
		 * @return boolean
		 */
		 if ($order_id){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_orders';
			 $q = $wpdb->prepare("SELECT id,uid,lid,amount_type,amount_value,automated_payment,status,create_date FROM $table WHERE id=%d;", $order_id);
			 $check = $wpdb->get_row($q);
			 if ($check && !empty($check->id)){
				  $q = $wpdb->prepare("UPDATE $table SET status=%s WHERE id=%d", $new_status, $order_id);
			 		return $wpdb->query($q);
			 }
		 }
	}

	public static function update_transaction_status($txn_id='', $new_status=''){
		/*
		 * @param int, string
		 * @return boolean
		 */
		 if ($txn_id){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'indeed_members_payments';
			 $q = $wpdb->prepare("SELECT payment_data FROM $table WHERE txn_id=%s;", $txn_id);
			 $check = $wpdb->get_row($q);
			 if ($check && !empty($check->payment_data)){
			 	$data = json_decode($check->payment_data, TRUE);
				$data['message'] = $new_status;
				$json = json_encode($data);
				$q = $wpdb->prepare("UPDATE $table SET payment_data=%s WHERE txn_id=%s ", $json, $txn_id);
			 	return $wpdb->query($q);
			 }
		 }
	}

	public static function get_woo_product_id_for_lid($lid=0){
		/*
		 * @param int
		 * @return int
		 */
		 if ($lid!==FALSE){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'postmeta';
			 $q = $wpdb->prepare("SELECT post_id FROM $table WHERE meta_key='iump_woo_product_level_relation' AND meta_value=%s", $lid);
			 $data = $wpdb->get_row($q);
			 if ($data && isset($data->post_id)){
			 	return $data->post_id;
			 }
		 }
		 return 0;
	}

	public static function get_woo_product_level_relations(){
		/*
		 * @param none
		 * @return array
		 */
		 $array = array();
		 global $wpdb;
		 $table = $wpdb->prefix . 'postmeta';
		 //No query parameters required, Safe query. prepare() method without parameters can not be called
		 $query = "SELECT meta_value, post_id FROM $table WHERE meta_key='iump_woo_product_level_relation' AND meta_value!='' AND meta_value!='-1';";
		 $data = $wpdb->get_results( $query );
		 if ($data){
		 	foreach ($data as $object){
		 		$temp['level_label'] = self::get_level_name_by_lid($object->meta_value);
				$temp['product_label'] = get_the_title($object->post_id);
				$temp['level_id'] = $object->meta_value;
				$temp['product_id'] = $object->post_id;
				$array[] = $temp;
		 	}
		 }
		 return $array;
	}

	public static function search_woo_products($search=''){
		/*
		 * @param string
		 * @return array
		 */
		$arr = array();
		if ($search){
			global $wpdb;
			$table = $wpdb->prefix . 'posts';
			$search = sanitize_text_field($search);
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "SELECT post_title, ID
											FROM $table
											WHERE
											post_title LIKE '%$search%'
											AND post_type='product'
											AND post_status='publish'
			";
			$data = $wpdb->get_results( $query );
			if ($data){
				foreach ($data as $object){
					$arr[$object->ID] = $object->post_title;
				}
			}
		}
		return $arr;
	}


	/*
	 * @param int (product id)
	 * @return string (list of terms id separated by comma)
	 */
	public static function woo_get_product_terms_as_string($product_id=0){
		if ($product_id){
			$cats_arr = wp_get_post_terms($product_id, 'product_cat');
			if ($cats_arr){
				foreach ($cats_arr as $cat_object){
					$arr[] = $cat_object->term_id;
				}
				return implode(',', $arr);
			}
		}
		return '';
	}


	/*
	 * @param string
	 * @return array
	 */
	public static function search_woo_product_cats($search=''){
		$array = array();
		if ($search){
			$args = array(
			    'hide_empty' => TRUE,
			    'name__like' => $search,
			);
			$product_categories = get_terms( 'product_cat', $args );
			if ($product_categories){
				foreach ($product_categories as $object){
					$array[$object->term_id] = $object->name;
				}
			}
		}
		return $array;
	}


	/*
	 * @param int (id of cat)
	 * @return string
	 */
	public static function get_category_name($id=0){
		global $wpdb;
		if ($id){
			$table = $wpdb->base_prefix . 'terms';
			$q = $wpdb->prepare("SELECT name FROM $table WHERE term_id=%d;", $id);
			$data = $wpdb->get_row($q);
			if ($data){
				return $data->name;
			}
		}
		return '';
	}


	public static function unsign_woo_product_level_relation($lid=-1){
		/*
		 * @param int
		 * @return none
		 */
		 if ($lid>-1){
		 	 $product_id = self::get_woo_product_id_for_lid($lid);
			 if ($product_id){
			 	 update_post_meta($product_id, 'iump_woo_product_level_relation', '');
			 }
		 }
	}

	public static function user_get_website($uid=0){
		/*
		 * @param int
		 * @return string
		 */
		 if ($uid){
		 	 global $wpdb;
			 $table = $wpdb->base_prefix . 'users';
			 $q = $wpdb->prepare("SELECT user_url FROM $table WHERE ID=%d;", $uid);
			 $data = $wpdb->get_row($q);
			 if ($data && isset($data->user_url)){
			 	 return $data->user_url;
			 }
		 }
		 return '';
	}

	public static function user_get_inserted_posts_count($uid=0, $since=''){
		/*
		 * @param int, string (timestamp),
		 * @return int
		 */
		 if ($uid){
		 	 global $wpdb;
		 	 $table = $wpdb->prefix . 'posts';
			 $q = "SELECT COUNT(ID) as c FROM $table WHERE post_author=%d AND post_status!='auto-draft' AND post_status!='trash' and post_type!='revision' ";
			 $q = $wpdb->prepare($q, $uid);
			 if ($since){
			 	$since = indeed_timestamp_to_date_without_timezone( $since );
			 	$q .= $wpdb->prepare(" AND post_date>%s ", $since );
			 }
		 	 $data = $wpdb->get_row($q);
			 if ($data && isset($data->c)){
			 	return $data->c;
			 }
		 }
		 return 0;
	}

	public static function getPostCreateTime( $postId=0 )
	{
			global $wpdb;
			if ( !$postId ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT post_date FROM {$wpdb->posts} WHERE ID=%d;", $postId );
			return $wpdb->get_var( $query );
	}

	public static function getDefaultUmpPages()
	{
			$keys = [
								'ihc_general_login_default_page' 				=> '',
								'ihc_general_register_default_page'			=> '',
								'ihc_general_lost_pass_page' 						=> '',
								'ihc_general_logout_page' 							=> '',
								'ihc_general_user_page' 								=> '',
								'ihc_general_tos_page' 									=> '',
								'ihc_subscription_plan_page' 						=> '',
								'ihc_checkout_page' 										=> '',
								'ihc_thank_you_page' 										=> '',
								'ihc_general_register_view_user' 				=> '',
			];
			$array = [];
			foreach ( $keys as $key => $value ){
					$array[ $key ] = get_option( $key );
			}
			return $array;
	}

	public static function user_get_inserted_comments_count($uid=0, $since=''){
		/*
		 * @param int, string (timestamp)
		 * @return int
		 */
		 if ($uid){
		 	 global $wpdb;
		 	 $table = $wpdb->prefix . 'comments';
			 $q = $wpdb->prepare("SELECT COUNT(comment_ID) as c FROM $table WHERE user_id=%d ", $uid);
			 if ($since){
			 	$since = indeed_timestamp_to_date_without_timezone( $since );
			 	$q .= $wpdb->prepare( " AND comment_date>%s ", $since );
			 }
		 	 $data = $wpdb->get_row($q);
			 if ($data && isset($data->c)){
			 	return $data->c;
			 }
		 }
		 return 0;
	}

	public static function do_delete_comment($comment_id=0){
		/*
		 * @param int
		 * @return none
		 */
		if ($comment_id){
			global $wpdb;
			$comments = $wpdb->prefix . 'comments';
			$commentmeta = $wpdb->prefix . 'commentmeta';
			$q = $wpdb->prepare("DELETE FROM $comments WHERE comment_ID=%d ", $comment_id);
			$wpdb->query($q);
			$q = $wpdb->prepare("DELETE FROM $commentmeta WHERE comment_id=%d ", $comment_id);
			$wpdb->query($q);
		}
	}

	public static function do_delete_post($post_ID=0){
		/*
		 * @param int
		 * @return none
		 */
		 if ($post_ID){
			 global $wpdb;
			 $posts = $wpdb->prefix . 'posts';
			 $postmeta = $wpdb->prefix . 'postmeta';
			 $q = $wpdb->prepare("DELETE FROM $posts WHERE ID=%d ", $post_ID);
			 $wpdb->query($q);
			 $q = $wpdb->prepare("DELETE FROM $postmeta WHERE post_id=%d ", $post_ID);
			 $wpdb->query($q);
		 }
	}

	public static function level_get_delay_time($lid=-1){
		/*
		 * @param int
		 * @return int || boolean
		 */
		 if ($lid>-1){
		 	 $time_value = get_option('ihc_subscription_delay_time');
		 	 $time_type = get_option('ihc_subscription_delay_type');
			 if ($time_value && $time_type){
			 	 if (isset($time_value[$lid]) && isset($time_type[$lid]) && $time_value[$lid] != '' ){
			 	 	 if ($time_type[$lid]=='h'){
			 	 	 	 ///hours
			 	 	 	 return $time_value[$lid] * 3600;
			 	 	 } else {
			 	 	 	 /// days
			 	 	 	 return $time_value[$lid] * 24 * 3600;
			 	 	 }
			 	 }
			 }
		 }
		 return FALSE;
	}


	/**
	 * @param array
	 * @return bool
	 */
	public static function account_page_menu_save_custom_item( $array=array() )
	{
			 $data = get_option('ihc_account_page_custom_menu_items');
			 $slug = $array['ihc_account_page_menu_add_new-the_slug'];

			 $exists = get_option( 'ihc_ap_' . $slug . '_menu_label' );
			 if ( $exists ){
				 	return;
			 }

			 $exclude = [
									 'overview',
									 'profile',
									 'subscription',
									 'social',
									 'orders',
									 'transactions',
									 'membeship_gifts',
									 'membership_cards',
									 'pushover_notifications',
									 'user_sites',
									 'help',
									 'affiliate',
									 'logout',
			 ];
			 if ( in_array( $slug, $exclude ) ){
				 	return false;
			 }

			 if ($data && isset($data[$slug])){
				 	return false; /// slug already exists
			 } else {
				 	$data[$slug] = array(
											'label' 		=> $array[ 'ihc_account_page_menu_add_new-the_label' ],
											'url'  			=> isset( $array[ 'ihc_account_page_menu_add_new-url' ] ) ? $array[ 'ihc_account_page_menu_add_new-url' ] : '',
					);
					$tempkey = 'ihc_ap_' . $slug . '_icon_code';
					update_option( $tempkey, $array['ihc_account_page_menu_add_new-the_icon_code'] );
					$tempkey = 'ihc_ap_' . $slug . '_icon_class';
					update_option( $tempkey, $array['ihc_account_page_menu_add_new-the_icon_class'] );
			 }
			 return update_option('ihc_account_page_custom_menu_items', $data);
	}


	/*
	 * @param none
	 * @return array
	 */
	 public static function account_page_menu_get_custom_items()
	 {
				$data = get_option('ihc_account_page_custom_menu_items');
				if ($data){
					foreach ($data as $slug => $array){
						$tempkey = 'ihc_ap_' . $slug . '_icon_code';
						$data[$slug]['icon'] = get_option($tempkey);
						$tempkey = 'ihc_ap_' . $slug . '_icon_class';
						$data[$slug]['class'] = get_option($tempkey);
					}
				}
				$data = apply_filters( 'ihc_filter_custom_menu_items', $data );
				return $data;
	 }


	 /*
	  * @param int
	  * @return noen
	  */
	  public static function account_page_menu_delete_custom_item($slug=''){
	  		if ($slug){
	  			$data = get_option('ihc_account_page_custom_menu_items');
				if (isset($data[$slug])){
					unset($data[$slug]);
				}
				update_option('ihc_account_page_custom_menu_items', $data);
	  		}
	  }


	  /*
	   * @param none
	   * @return array
	   */
	   public static function account_page_get_menu($only_standard=FALSE){
			$available_tabs = array(
									'overview'=> array('label' => esc_html__('Dashboard', 'ihc'), 'icon' => 'f015', 'icon_class' => ''),
									'profile'=> array('label' => esc_html__('Profile', 'ihc'), 'icon' => 'f007', 'icon_class' => ''),
									'subscription'=> array('label' => esc_html__('Subscriptions', 'ihc'), 'icon' => 'f0a1', 'icon_class' => ''),
									'social' => array('label' => esc_html__('Social Plus', 'ihc'), 'icon' => 'f0e6', 'icon_class' => ''),
									'orders' => array('label' => esc_html__('Orders', 'ihc'), 'icon' => 'f0d6', 'icon_class' => ''),
									//'transactions'=> array('label' => esc_html__('Transactions', 'ihc'), 'icon' => 'f155', 'icon_class' => ''),
									'membeship_gifts' => array('label' => esc_html__('Membership Gifts', 'ihc'), 'icon' => 'f06b', 'icon_class' => '', 'check_magic_feat' => 'gifts'),
									'membership_cards' => array('label' => esc_html__('Membership Cards', 'ihc'), 'icon' => 'f022', 'icon_class' => '', 'check_magic_feat' => 'pushover'),
									'pushover_notifications' => array('label' => esc_html__('Pushover Notifications', 'ihc'), 'icon' => 'f0f3', 'icon_class' => '', 'check_magic_feat' => 'user_sites'),
									'user_sites' => array('label' => esc_html__('Your Sites', 'ihc'), 'icon' => 'f084', 'icon_class' => '', 'check_magic_feat' => TRUE),
									'help' => array('label' => esc_html__('Help', 'ihc'), 'icon' => 'f059', 'icon_class' => ''),
									'affiliate' => array('label' => esc_html__('Affiliate', 'ihc'), 'icon' => 'f0e8', 'icon_class' => ''),
									'logout' => array('label' => 'LogOut', 'icon' => 'f08b', 'icon_class' => ''),
			);
			foreach ($available_tabs as $slug=>$array_data){

				$tempkey = 'ihc_ap_' . $slug . '_icon_code';
				$temp_data = get_option($tempkey);
				if ($temp_data){
					$available_tabs[$slug]['icon'] = $temp_data;
				}
			}
			if ($only_standard){
				return $available_tabs;
			}
			$custom_available_tabs = Ihc_Db::account_page_menu_get_custom_items();
			if (!empty($custom_available_tabs)){
				$available_tabs = array_merge($available_tabs, $custom_available_tabs);
			}
			$available_tabs = ihc_reorder_menu_items(get_option('ihc_account_page_menu_order'), $available_tabs);
			return $available_tabs;
	   }


	/*
	 * used in account_page.php
	 * @param array
	 * @return none
	 */
	public static function account_page_save_tabs_details($array=array()){
		$keys = self::account_page_get_menu();
		foreach ($keys as $key => $extra){
			$tempkey = 'ihc_ap_' . $key . '_menu_label';
			if (isset($array[$tempkey])){
				update_option($tempkey, $array[$tempkey]);
			}
			$tempkey = 'ihc_ap_' . $key . '_title';
			if (isset($array[$tempkey])){
				update_option($tempkey, $array[$tempkey]);
			}
			$tempkey = 'ihc_ap_' . $key . '_msg';
			if (isset($array[$tempkey])){
				update_option($tempkey, $array[$tempkey]);
			}
			$tempkey = 'ihc_ap_' . $key . '_icon_code';
			if (isset($array[$tempkey])){
				update_option($tempkey, $array[$tempkey]);
			}
			$tempkey = 'ihc_ap_' . $key . '_icon_class';
			if (isset($array[$tempkey])){
				update_option($tempkey, $array[$tempkey]);
			}
		}
	}


	/*
	 * @param none
	 * @return array
	 */
	 public static function account_page_get_tabs_details(){
	 	$keys = self::account_page_get_menu();
		$return = array();
		foreach ($keys as $key => $extra){
			$tempkey = 'ihc_ap_' . $key . '_menu_label';
			$return[$tempkey] = get_option($tempkey);
			$tempkey = 'ihc_ap_' . $key . '_title';
			$return[$tempkey] = get_option($tempkey);
			$tempkey = 'ihc_ap_' . $key . '_msg';
			$return[$tempkey] = get_option($tempkey);
			$tempkey = 'ihc_ap_' . $key . '_icon_code';
			$return[$tempkey] = get_option($tempkey);
			$tempkey = 'ihc_ap_' . $key . '_icon_class';
			$return[$tempkey] = get_option($tempkey);
		}
		return $return;
	 }


	 /*
	  * @param int
	  * @return array
	  */
	  public static function user_get_all_data($uid=0){
	  		global $wpdb;
			$array = array();
			if ($uid){
				$table_users = $wpdb->base_prefix . 'users';
				$q = $wpdb->prepare( "SELECT ID,user_login,user_pass,user_nicename,user_email,user_url,user_registered,user_activation_key,user_status,display_name
								FROM $table_users WHERE ID=%d ;", $uid );
				$data = $wpdb->get_row($q);
				if ($data){
					$array = (array)$data;
				}
				$table_user_meta = $wpdb->base_prefix . 'usermeta';
				$q = $wpdb->prepare( "SELECT meta_key, meta_value FROM $table_user_meta WHERE user_id=%d ;", $uid );
				$data = $wpdb->get_results($q);
				if ($data){
					foreach ($data as $object){
						$array[$object->meta_key] = $object->meta_value;
					}
				}
			}
			return $array;
	  }


	 /*
	  * @param none
	  * @return array
	  */
	  public static function get_font_awesome_codes(){
			return array(
							 "fa-ihc-glass" => "f000",
							 "fa-ihc-music" => "f001",
							 "fa-ihc-search" => "f002",
							 "fa-ihc-envelope-o" => "f003",
							 "fa-ihc-heart" => "f004",
							 "fa-ihc-star" => "f005",
							 "fa-ihc-star-o" => "f006",
							 "fa-ihc-user" => "f007",
							 "fa-ihc-film" => "f008",
							 "fa-ihc-th-large" => "f009",
							 "fa-ihc-th" => "f00a",
							 "fa-ihc-th-list" => "f00b",
							 "fa-ihc-check" => "f00c",
							 "fa-ihc-times" => "f00d",
							 "fa-ihc-search-plus" => "f00e",
							 "fa-ihc-search-minus" => "f010",
							 "fa-ihc-power-off" => "f011",
							 "fa-ihc-signal" => "f012",
							 "fa-ihc-cog" => "f013",
							 "fa-ihc-trash-o" => "f014",
							 "fa-ihc-home" => "f015",
							 "fa-ihc-file-o" => "f016",
							 "fa-ihc-clock-o" => "f017",
							 "fa-ihc-road" => "f018",
							 "fa-ihc-download" => "f019",
							 "fa-ihc-arrow-circle-o-down" => "f01a",
							 "fa-ihc-arrow-circle-o-up" => "f01b",
							 "fa-ihc-inbox" => "f01c",
							 "fa-ihc-play-circle-o" => "f01d",
							 "fa-ihc-repeat" => "f01e",
							 "fa-ihc-refresh" => "f021",
							 "fa-ihc-list-alt" => "f022",
							 "fa-ihc-lock" => "f023",
							 "fa-ihc-flag" => "f024",
							 "fa-ihc-headphones" => "f025",
							 "fa-ihc-volume-off" => "f026",
							 "fa-ihc-volume-down" => "f027",
							 "fa-ihc-volume-up" => "f028",
							 "fa-ihc-qrcode" => "f029",
							 "fa-ihc-barcode" => "f02a",
							 "fa-ihc-tag" => "f02b",
							 "fa-ihc-tags" => "f02c",
							 "fa-ihc-book" => "f02d",
							 "fa-ihc-bookmark" => "f02e",
							 "fa-ihc-print" => "f02f",
							 "fa-ihc-camera" => "f030",
							 "fa-ihc-font" => "f031",
							 "fa-ihc-bold" => "f032",
							 "fa-ihc-italic" => "f033",
							 "fa-ihc-text-height" => "f034",
							 "fa-ihc-text-width" => "f035",
							 "fa-ihc-align-left" => "f036",
							 "fa-ihc-align-center" => "f037",
							 "fa-ihc-align-right" => "f038",
							 "fa-ihc-align-justify" => "f039",
							 "fa-ihc-list" => "f03a",
							 "fa-ihc-outdent" => "f03b",
							 "fa-ihc-indent" => "f03c",
							 "fa-ihc-video-camera" => "f03d",
							 "fa-ihc-picture-o" => "f03e",
							 "fa-ihc-pencil" => "f040",
							 "fa-ihc-map-marker" => "f041",
							 "fa-ihc-adjust" => "f042",
							 "fa-ihc-tint" => "f043",
							 "fa-ihc-pencil-square-o" => "f044",
							 "fa-ihc-share-square-o" => "f045",
							 "fa-ihc-check-square-o" => "f046",
							 "fa-ihc-arrows" => "f047",
							 "fa-ihc-step-backward" => "f048",
							 "fa-ihc-fast-backward" => "f049",
							 "fa-ihc-backward" => "f04a",
							 "fa-ihc-play" => "f04b",
							 "fa-ihc-pause" => "f04c",
							 "fa-ihc-stop" => "f04d",
							 "fa-ihc-forward" => "f04e",
							 "fa-ihc-fast-forward" => "f050",
							 "fa-ihc-step-forward" => "f051",
							 "fa-ihc-eject" => "f052",
							 "fa-ihc-chevron-left" => "f053",
							 "fa-ihc-chevron-right" => "f054",
							 "fa-ihc-plus-circle" => "f055",
							 "fa-ihc-minus-circle" => "f056",
							 "fa-ihc-times-circle" => "f057",
							 "fa-ihc-check-circle" => "f058",
							 "fa-ihc-question-circle" => "f059",
							 "fa-ihc-info-circle" => "f05a",
							 "fa-ihc-crosshairs" => "f05b",
							 "fa-ihc-times-circle-o" => "f05c",
							 "fa-ihc-check-circle-o" => "f05d",
							 "fa-ihc-ban" => "f05e",
							 "fa-ihc-arrow-left" => "f060",
							 "fa-ihc-arrow-right" => "f061",
							 "fa-ihc-arrow-up" => "f062",
							 "fa-ihc-arrow-down" => "f063",
							 "fa-ihc-share" => "f064",
							 "fa-ihc-expand" => "f065",
							 "fa-ihc-compress" => "f066",
							 "fa-ihc-plus" => "f067",
							 "fa-ihc-minus" => "f068",
							 "fa-ihc-asterisk" => "f069",
							 "fa-ihc-exclamation-circle" => "f06a",
							 "fa-ihc-gift" => "f06b",
							 "fa-ihc-leaf" => "f06c",
							 "fa-ihc-fire" => "f06d",
							 "fa-ihc-eye" => "f06e",
							 "fa-ihc-eye-slash" => "f070",
							 "fa-ihc-exclamation-triangle" => "f071",
							 "fa-ihc-plane" => "f072",
							 "fa-ihc-calendar" => "f073",
							 "fa-ihc-random" => "f074",
							 "fa-ihc-comment" => "f075",
							 "fa-ihc-magnet" => "f076",
							 "fa-ihc-chevron-up" => "f077",
							 "fa-ihc-chevron-down" => "f078",
							 "fa-ihc-retweet" => "f079",
							 "fa-ihc-shopping-cart" => "f07a",
							 "fa-ihc-folder" => "f07b",
							 "fa-ihc-folder-open" => "f07c",
							 "fa-ihc-arrows-v" => "f07d",
							 "fa-ihc-arrows-h" => "f07e",
							 "fa-ihc-bar-chart" => "f080",
							 "fa-ihc-twitter-square" => "f081",
							 "fa-ihc-facebook-square" => "f082",
							 "fa-ihc-camera-retro" => "f083",
							 "fa-ihc-key" => "f084",
							 "fa-ihc-cogs" => "f085",
							 "fa-ihc-comments" => "f086",
							 "fa-ihc-thumbs-o-up" => "f087",
							 "fa-ihc-thumbs-o-down" => "f088",
							 "fa-ihc-star-half" => "f089",
							 "fa-ihc-heart-o" => "f08a",
							 "fa-ihc-sign-out" => "f08b",
							 "fa-ihc-linkedin-square" => "f08c",
							 "fa-ihc-thumb-tack" => "f08d",
							 "fa-ihc-external-link" => "f08e",
							 "fa-ihc-sign-in" => "f090",
							 "fa-ihc-trophy" => "f091",
							 "fa-ihc-github-square" => "f092",
							 "fa-ihc-upload" => "f093",
							 "fa-ihc-lemon-o" => "f094",
							 "fa-ihc-phone" => "f095",
							 "fa-ihc-square-o" => "f096",
							 "fa-ihc-bookmark-o" => "f097",
							 "fa-ihc-phone-square" => "f098",
							 "fa-ihc-twitter" => "f099",
							 "fa-ihc-facebook" => "f09a",
							 "fa-ihc-github" => "f09b",
							 "fa-ihc-unlock" => "f09c",
							 "fa-ihc-credit-card" => "f09d",
							 "fa-ihc-rss" => "f09e",
							 "fa-ihc-hdd-o" => "f0a0",
							 "fa-ihc-bullhorn" => "f0a1",
							 "fa-ihc-bell" => "f0f3",
							 "fa-ihc-certificate" => "f0a3",
							 "fa-ihc-hand-o-right" => "f0a4",
							 "fa-ihc-hand-o-left" => "f0a5",
							 "fa-ihc-hand-o-up" => "f0a6",
							 "fa-ihc-hand-o-down" => "f0a7",
							 "fa-ihc-arrow-circle-left" => "f0a8",
							 "fa-ihc-arrow-circle-right" => "f0a9",
							 "fa-ihc-arrow-circle-up" => "f0aa",
							 "fa-ihc-arrow-circle-down" => "f0ab",
							 "fa-ihc-globe" => "f0ac",
							 "fa-ihc-wrench" => "f0ad",
							 "fa-ihc-tasks" => "f0ae",
							 "fa-ihc-filter" => "f0b0",
							 "fa-ihc-briefcase" => "f0b1",
							 "fa-ihc-arrows-alt" => "f0b2",
							 "fa-ihc-users" => "f0c0",
							 "fa-ihc-link" => "f0c1",
							 "fa-ihc-cloud" => "f0c2",
							 "fa-ihc-flask" => "f0c3",
							 "fa-ihc-scissors" => "f0c4",
							 "fa-ihc-files-o" => "f0c5",
							 "fa-ihc-paperclip" => "f0c6",
							 "fa-ihc-floppy-o" => "f0c7",
							 "fa-ihc-square" => "f0c8",
							 "fa-ihc-bars" => "f0c9",
							 "fa-ihc-list-ul" => "f0ca",
							 "fa-ihc-list-ol" => "f0cb",
							 "fa-ihc-strikethrough" => "f0cc",
							 "fa-ihc-underline" => "f0cd",
							 "fa-ihc-table" => "f0ce",
							 "fa-ihc-magic" => "f0d0",
							 "fa-ihc-truck" => "f0d1",
							 "fa-ihc-pinterest" => "f0d2",
							 "fa-ihc-pinterest-square" => "f0d3",
							 "fa-ihc-google-plus-square" => "f0d4",
							 "fa-ihc-google-plus" => "f0d5",
							 "fa-ihc-money" => "f0d6",
							 "fa-ihc-caret-down" => "f0d7",
							 "fa-ihc-caret-up" => "f0d8",
							 "fa-ihc-caret-left" => "f0d9",
							 "fa-ihc-caret-right" => "f0da",
							 "fa-ihc-columns" => "f0db",
							 "fa-ihc-sort" => "f0dc",
							 "fa-ihc-sort-desc" => "f0dd",
							 "fa-ihc-sort-asc" => "f0de",
							 "fa-ihc-envelope" => "f0e0",
							 "fa-ihc-linkedin" => "f0e1",
							 "fa-ihc-undo" => "f0e2",
							 "fa-ihc-gavel" => "f0e3",
							 "fa-ihc-tachometer" => "f0e4",
							 "fa-ihc-comment-o" => "f0e5",
							 "fa-ihc-comments-o" => "f0e6",
							 "fa-ihc-bolt" => "f0e7",
							 "fa-ihc-sitemap" => "f0e8",
							 "fa-ihc-umbrella" => "f0e9",
							 "fa-ihc-clipboard" => "f0ea",
							 "fa-ihc-lightbulb-o" => "f0eb",
							 "fa-ihc-exchange" => "f0ec",
							 "fa-ihc-cloud-download" => "f0ed",
							 "fa-ihc-cloud-upload" => "f0ee",
							 "fa-ihc-user-md" => "f0f0",
							 "fa-ihc-stethoscope" => "f0f1",
							 "fa-ihc-suitcase" => "f0f2",
							 "fa-ihc-bell-o" => "f0a2",
							 "fa-ihc-coffee" => "f0f4",
							 "fa-ihc-cutlery" => "f0f5",
							 "fa-ihc-file-text-o" => "f0f6",
							 "fa-ihc-building-o" => "f0f7",
							 "fa-ihc-hospital-o" => "f0f8",
							 "fa-ihc-ambulance" => "f0f9",
							 "fa-ihc-medkit" => "f0fa",
							 "fa-ihc-fighter-jet" => "f0fb",
							 "fa-ihc-beer" => "f0fc",
							 "fa-ihc-h-square" => "f0fd",
							 "fa-ihc-plus-square" => "f0fe",
							 "fa-ihc-angle-double-left" => "f100",
							 "fa-ihc-angle-double-right" => "f101",
							 "fa-ihc-angle-double-up" => "f102",
							 "fa-ihc-angle-double-down" => "f103",
							 "fa-ihc-angle-left" => "f104",
							 "fa-ihc-angle-right" => "f105",
							 "fa-ihc-angle-up" => "f106",
							 "fa-ihc-angle-down" => "f107",
							 "fa-ihc-desktop" => "f108",
							 "fa-ihc-laptop" => "f109",
							 "fa-ihc-tablet" => "f10a",
							 "fa-ihc-mobile" => "f10b",
							 "fa-ihc-circle-o" => "f10c",
							 "fa-ihc-quote-left" => "f10d",
							 "fa-ihc-quote-right" => "f10e",
							 "fa-ihc-spinner" => "f110",
							 "fa-ihc-circle" => "f111",
							 "fa-ihc-reply" => "f112",
							 "fa-ihc-github-alt" => "f113",
							 "fa-ihc-folder-o" => "f114",
							 "fa-ihc-folder-open-o" => "f115",
							 "fa-ihc-smile-o" => "f118",
							 "fa-ihc-frown-o" => "f119",
							 "fa-ihc-meh-o" => "f11a",
							 "fa-ihc-gamepad" => "f11b",
							 "fa-ihc-keyboard-o" => "f11c",
							 "fa-ihc-flag-o" => "f11d",
							 "fa-ihc-flag-checkered" => "f11e",
							 "fa-ihc-terminal" => "f120",
							 "fa-ihc-code" => "f121",
							 "fa-ihc-reply-all" => "f122",
							 "fa-ihc-star-half-o" => "f123",
							 "fa-ihc-location-arrow" => "f124",
							 "fa-ihc-crop" => "f125",
							 "fa-ihc-code-fork" => "f126",
							 "fa-ihc-chain-broken" => "f127",
							 "fa-ihc-question" => "f128",
							 "fa-ihc-info" => "f129",
							 "fa-ihc-exclamation" => "f12a",
							 "fa-ihc-superscript" => "f12b",
							 "fa-ihc-subscript" => "f12c",
							 "fa-ihc-eraser" => "f12d",
							 "fa-ihc-puzzle-piece" => "f12e",
							 "fa-ihc-microphone" => "f130",
							 "fa-ihc-microphone-slash" => "f131",
							 "fa-ihc-shield" => "f132",
							 "fa-ihc-calendar-o" => "f133",
							 "fa-ihc-fire-extinguisher" => "f134",
							 "fa-ihc-rocket" => "f135",
							 "fa-ihc-maxcdn" => "f136",
							 "fa-ihc-chevron-circle-left" => "f137",
							 "fa-ihc-chevron-circle-right" => "f138",
							 "fa-ihc-chevron-circle-up" => "f139",
							 "fa-ihc-chevron-circle-down" => "f13a",
							 "fa-ihc-html5" => "f13b",
							 "fa-ihc-css3" => "f13c",
							 "fa-ihc-anchor" => "f13d",
							 "fa-ihc-unlock-alt" => "f13e",
							 "fa-ihc-bullseye" => "f140",
							 "fa-ihc-ellipsis-h" => "f141",
							 "fa-ihc-ellipsis-v" => "f142",
							 "fa-ihc-rss-square" => "f143",
							 "fa-ihc-play-circle" => "f144",
							 "fa-ihc-ticket" => "f145",
							 "fa-ihc-minus-square" => "f146",
							 "fa-ihc-minus-square-o" => "f147",
							 "fa-ihc-level-up" => "f148",
							 "fa-ihc-level-down" => "f149",
							 "fa-ihc-check-square" => "f14a",
							 "fa-ihc-pencil-square" => "f14b",
							 "fa-ihc-external-link-square" => "f14c",
							 "fa-ihc-share-square" => "f14d",
							 "fa-ihc-compass" => "f14e",
							 "fa-ihc-caret-square-o-down" => "f150",
							 "fa-ihc-caret-square-o-up" => "f151",
							 "fa-ihc-caret-square-o-right" => "f152",
							 "fa-ihc-eur" => "f153",
							 "fa-ihc-gbp" => "f154",
							 "fa-ihc-usd" => "f155",
							 "fa-ihc-inr" => "f156",
							 "fa-ihc-jpy" => "f157",
							 "fa-ihc-rub" => "f158",
							 "fa-ihc-krw" => "f159",
							 "fa-ihc-btc" => "f15a",
							 "fa-ihc-file" => "f15b",
							 "fa-ihc-file-text" => "f15c",
							 "fa-ihc-sort-alpha-asc" => "f15d",
							 "fa-ihc-sort-alpha-desc" => "f15e",
							 "fa-ihc-sort-amount-asc" => "f160",
							 "fa-ihc-sort-amount-desc" => "f161",
							 "fa-ihc-sort-numeric-asc" => "f162",
							 "fa-ihc-sort-numeric-desc" => "f163",
							 "fa-ihc-thumbs-up" => "f164",
							 "fa-ihc-thumbs-down" => "f165",
							 "fa-ihc-youtube-square" => "f166",
							 "fa-ihc-youtube" => "f167",
							 "fa-ihc-xing" => "f168",
							 "fa-ihc-xing-square" => "f169",
							 "fa-ihc-youtube-play" => "f16a",
							 "fa-ihc-dropbox" => "f16b",
							 "fa-ihc-stack-overflow" => "f16c",
							 "fa-ihc-instagram" => "f16d",
							 "fa-ihc-flickr" => "f16e",
							 "fa-ihc-adn" => "f170",
							 "fa-ihc-bitbucket" => "f171",
							 "fa-ihc-bitbucket-square" => "f172",
							 "fa-ihc-tumblr" => "f173",
							 "fa-ihc-tumblr-square" => "f174",
							 "fa-ihc-long-arrow-down" => "f175",
							 "fa-ihc-long-arrow-up" => "f176",
							 "fa-ihc-long-arrow-left" => "f177",
							 "fa-ihc-long-arrow-right" => "f178",
							 "fa-ihc-apple" => "f179",
							 "fa-ihc-windows" => "f17a",
							 "fa-ihc-android" => "f17b",
							 "fa-ihc-linux" => "f17c",
							 "fa-ihc-dribbble" => "f17d",
							 "fa-ihc-skype" => "f17e",
							 "fa-ihc-foursquare" => "f180",
							 "fa-ihc-trello" => "f181",
							 "fa-ihc-female" => "f182",
							 "fa-ihc-male" => "f183",
							 "fa-ihc-gittip" => "f184",
							 "fa-ihc-sun-o" => "f185",
							 "fa-ihc-moon-o" => "f186",
							 "fa-ihc-archive" => "f187",
							 "fa-ihc-bug" => "f188",
							 "fa-ihc-vk" => "f189",
							 "fa-ihc-weibo" => "f18a",
							 "fa-ihc-renren" => "f18b",
							 "fa-ihc-pagelines" => "f18c",
							 "fa-ihc-stack-exchange" => "f18d",
							 "fa-ihc-arrow-circle-o-right" => "f18e",
							 "fa-ihc-arrow-circle-o-left" => "f190",
							 "fa-ihc-caret-square-o-left" => "f191",
							 "fa-ihc-dot-circle-o" => "f192",
							 "fa-ihc-wheelchair" => "f193",
							 "fa-ihc-vimeo-square" => "f194",
							 "fa-ihc-try" => "f195",
							 "fa-ihc-plus-square-o" => "f196",
							 "fa-ihc-space-shuttle" => "f197",
							 "fa-ihc-slack" => "f198",
							 "fa-ihc-envelope-square" => "f199",
							 "fa-ihc-wordpress" => "f19a",
							 "fa-ihc-openid" => "f19b",
							 "fa-ihc-university" => "f19c",
							 "fa-ihc-graduation-cap" => "f19d",
							 "fa-ihc-yahoo" => "f19e",
							 "fa-ihc-google" => "f1a0",
							 "fa-ihc-reddit" => "f1a1",
							 "fa-ihc-reddit-square" => "f1a2",
							 "fa-ihc-stumbleupon-circle" => "f1a3",
							 "fa-ihc-stumbleupon" => "f1a4",
							 "fa-ihc-delicious" => "f1a5",
							 "fa-ihc-digg" => "f1a6",
							 "fa-ihc-pied-piper" => "f1a7",
							 "fa-ihc-pied-piper-alt" => "f1a8",
							 "fa-ihc-drupal" => "f1a9",
							 "fa-ihc-joomla" => "f1aa",
							 "fa-ihc-language" => "f1ab",
							 "fa-ihc-fax" => "f1ac",
							 "fa-ihc-building" => "f1ad",
							 "fa-ihc-child" => "f1ae",
							 "fa-ihc-paw" => "f1b0",
							 "fa-ihc-spoon" => "f1b1",
							 "fa-ihc-cube" => "f1b2",
							 "fa-ihc-cubes" => "f1b3",
							 "fa-ihc-behance" => "f1b4",
							 "fa-ihc-behance-square" => "f1b5",
							 "fa-ihc-steam" => "f1b6",
							 "fa-ihc-steam-square" => "f1b7",
							 "fa-ihc-recycle" => "f1b8",
							 "fa-ihc-car" => "f1b9",
							 "fa-ihc-taxi" => "f1ba",
							 "fa-ihc-tree" => "f1bb",
							 "fa-ihc-spotify" => "f1bc",
							 "fa-ihc-deviantart" => "f1bd",
							 "fa-ihc-soundcloud" => "f1be",
							 "fa-ihc-database" => "f1c0",
							 "fa-ihc-file-pdf-o" => "f1c1",
							 "fa-ihc-file-word-o" => "f1c2",
							 "fa-ihc-file-excel-o" => "f1c3",
							 "fa-ihc-file-powerpoint-o" => "f1c4",
							 "fa-ihc-file-image-o" => "f1c5",
							 "fa-ihc-file-archive-o" => "f1c6",
							 "fa-ihc-file-audio-o" => "f1c7",
							 "fa-ihc-file-video-o" => "f1c8",
							 "fa-ihc-file-code-o" => "f1c9",
							 "fa-ihc-vine" => "f1ca",
							 "fa-ihc-codepen" => "f1cb",
							 "fa-ihc-jsfiddle" => "f1cc",
							 "fa-ihc-life-ring" => "f1cd",
							 "fa-ihc-circle-o-notch" => "f1ce",
							 "fa-ihc-rebel" => "f1d0",
							 "fa-ihc-empire" => "f1d1",
							 "fa-ihc-git-square" => "f1d2",
							 "fa-ihc-git" => "f1d3",
							 "fa-ihc-hacker-news" => "f1d4",
							 "fa-ihc-tencent-weibo" => "f1d5",
							 "fa-ihc-qq" => "f1d6",
							 "fa-ihc-weixin" => "f1d7",
							 "fa-ihc-paper-plane" => "f1d8",
							 "fa-ihc-paper-plane-o" => "f1d9",
							 "fa-ihc-history" => "f1da",
							 "fa-ihc-circle-thin" => "f1db",
							 "fa-ihc-header" => "f1dc",
							 "fa-ihc-paragraph" => "f1dd",
							 "fa-ihc-sliders" => "f1de",
							 "fa-ihc-share-alt" => "f1e0",
							 "fa-ihc-share-alt-square" => "f1e1",
							 "fa-ihc-bomb" => "f1e2",
							 "fa-ihc-futbol-o" => "f1e3",
							 "fa-ihc-tty" => "f1e4",
							 "fa-ihc-binoculars" => "f1e5",
							 "fa-ihc-plug" => "f1e6",
							 "fa-ihc-slideshare" => "f1e7",
							 "fa-ihc-twitch" => "f1e8",
							 "fa-ihc-yelp" => "f1e9",
							 "fa-ihc-newspaper-o" => "f1ea",
							 "fa-ihc-wifi" => "f1eb",
							 "fa-ihc-calculator" => "f1ec",
							 "fa-ihc-paypal" => "f1ed",
							 "fa-ihc-google-wallet" => "f1ee",
							 "fa-ihc-cc-visa" => "f1f0",
							 "fa-ihc-cc-mastercard" => "f1f1",
							 "fa-ihc-cc-discover" => "f1f2",
							 "fa-ihc-cc-amex" => "f1f3",
							 "fa-ihc-cc-paypal" => "f1f4",
							 "fa-ihc-cc-stripe" => "f1f5",
							 "fa-ihc-bell-slash" => "f1f6",
							 "fa-ihc-bell-slash-o" => "f1f7",
							 "fa-ihc-trash" => "f1f8",
							 "fa-ihc-copyright" => "f1f9",
							 "fa-ihc-at" => "f1fa",
							 "fa-ihc-eyedropper" => "f1fb",
							 "fa-ihc-paint-brush" => "f1fc",
							 "fa-ihc-birthday-cake" => "f1fd",
							 "fa-ihc-area-chart" => "f1fe",
							 "fa-ihc-pie-chart" => "f200",
							 "fa-ihc-line-chart" => "f201",
							 "fa-ihc-lastfm" => "f202",
							 "fa-ihc-lastfm-square" => "f203",
							 "fa-ihc-toggle-off" => "f204",
							 "fa-ihc-toggle-on" => "f205",
							 "fa-ihc-bicycle" => "f206",
							 "fa-ihc-bus" => "f207",
							 "fa-ihc-ioxhost" => "f208",
							 "fa-ihc-angellist" => "f209",
							 "fa-ihc-cc" => "f20a",
							 "fa-ihc-ils" => "f20b",
							 "fa-ihc-meanpath" => "f20c",
			);
	  }


	 public static function getUserIdFromUserMetaValue( $value='' )
	 {
		 	global $wpdb;
		 	if ( empty( $value ) ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_value=%s; ", $value );
			return $wpdb->get_var( $query );
	 }


	 /*
	  * @param string, string
	  * @return array
	  */
	 public static function search_user_by_term_name_term_value($term_name='', $term_value=''){
	 	 global $wpdb;
	 	 if ($term_name){
	 	 	 $search_into_users = array(
										'display_name',
										'user_registered',
										'user_nicename',
										'user_email',
										'user_login',
			 );
			 $term_name = sanitize_text_field($term_name);
			 $term_value = sanitize_text_field($term_value);
			 if (in_array($term_name, $search_into_users)){
			 	 $table = $wpdb->base_prefix . 'users';
				 $q = "SELECT ID FROM $table WHERE $term_name LIKE '%$term_value%';";
			 } else {
	 	 	 	 $table = $wpdb->base_prefix . 'usermeta';
		 	 	 $q = "SELECT user_id FROM $table WHERE meta_key LIKE '%$term_name%' AND meta_value LIKE '%$term_value%';";
			 }
			 $data = $wpdb->get_results($q);
			 if ($data){
			 	return (array)$data;
			 }
	 	 }
	 	 return array();
	 }


	/*
	 * @param int ($post_id)
	 * @param string ($meta_key)
	 * @return bool
	 */
	 public static function does_post_meta_exists($post_id=0, $meta_key=''){
	 	 global $wpdb;
		 $table = $wpdb->prefix . 'postmeta';
		 $q = $wpdb->prepare("SELECT meta_id FROM $table WHERE post_id=%d AND meta_key=%s;", $post_id, $meta_key);
		 $data = $wpdb->get_row($q);
		 if (isset($data->meta_id)){
		 	return TRUE;
		 }
		 return FALSE;
	 }


	 /*
	  * Get all post meta values and post id for a post meta key
	  * @param string ($post_meta_key)
	  * @return array
	  */
	 public static function get_all_post_meta_data_for_meta_key($meta_key=''){
	 	 global $wpdb;
		 $array = array();
		 $table = $wpdb->prefix . 'postmeta';
		 $q = $wpdb->prepare("SELECT post_id, meta_value FROM $table WHERE meta_key=%s ", $meta_key);
		 $data = $wpdb->get_results($q);
		 if ($data){
		 	foreach ($data as $object){
		 		$array[] = (array)$object;
		 	}
		 }
		 return $array;
	 }


	 /*
	  * @param none
	  * @return array
	  */
	 public static function get_post_meta_keys_used_in_ump(){
	 	return array(
						'ihc_mb_type',
						'ihc_mb_who',
						'ihc_mb_block_type',
						'ihc_mb_redirect_to',
						'ihc_replace_content',
						'ihc_drip_content',
						'ihc_drip_start_type',
						'ihc_drip_end_type',
						'ihc_drip_start_numeric_type',
						'ihc_drip_start_numeric_value',
						'ihc_drip_end_numeric_type',
						'ihc_drip_end_numeric_value',
						'ihc_drip_start_certain_date',
						'ihc_drip_end_certain_date',
		);
	 }


	 /*
	  * Return all settings with values
	  * @param none
	  * @return array
	  */
	 public static function get_all_ump_wp_options($except=array('general-defaults')){
	 	 $search_groups = array(
		 							'payment',
		 							'payment_paypal',
		 							'payment_stripe',
		 							'payment_authorize',
		 							'payment_twocheckout',
		 							'payment_bank_transfer',
		 							'payment_braintree',
		 							'login',
		 							'login-messages',
		 							'general-captcha',
		 							'general-subscription',
		 							'general-msg',
		 							'general-defaults',
		 							'register',
		 							'register-msg',
		 							'register-custom-fields',
		 							'opt_in',
		 							'notifications',
		 							'extra_settings',
		 							'account_page',
		 							'fb',
		 							'tw',
		 							'in',
		 							'tbr',
		 							'ig',
		 							'vk',
		 							'goo',
		 							'social_media',
		 							'double_email_verification',
		 							'licensing',
		 							'listing_users',
		 							'listing_users_inside_page',
		 							'affiliate_options',
		 							'ihc_taxes_settings',
		 							'admin_workflow',
		 							'public_workflow',
		 							'ihc_woo',
		 							'ihc_bp',
		 							'ihc_membership_card',
		 							'ihc_cheat_off',
		 							'ihc_invitation_code',
		 							'download_monitor_integration',
		 							'register_lite',
		 							'individual_page',
		 							'level_restrict_payment',
		 							'level_subscription_plan_settings',
		 							'gifts',
		 							'login_level_redirect',
		 							'wp_social_login',
		 							'list_access_posts',
		 							'invoices',
		 							'woo_payment',
		 							'badges',
		 							'login_security',
		 							'workflow_restrictions',
		 							'subscription_delay',
		 							'level_dynamic_price',
		 							'user_reports',
		 							'pushover',
		 							'account_page_menu',
		 							'mycred',
		 							'api',
		 );
		 if ($except){
			 foreach ($except as $value){
			 	$key = array_search($value, $search_groups);
				if ($key!==FALSE){
				 	unset($search_groups[$key]);
				}
			 }
		 }
		 $array = array();
		 foreach ($search_groups as $key_group){
		 	 $temp = ihc_return_meta_arr($key_group);
			 $array = array_merge($array, $temp);
		 }
		 return $array;
	 }


	/*
	 * @param int (user id)
	 * @param string (meta name)
	 * @return bool
	 */
	public static function does_usermeta_exists($uid=0, $key_meta=''){
	 	 global $wpdb;
		 if ( $key_meta == ''){
				 return FALSE;
		 }
		 $table = $wpdb->usermeta;
		 $q = $wpdb->prepare("SELECT umeta_id FROM $table WHERE user_id=%d AND meta_key=%s ", $uid, $key_meta);
		 $data = $wpdb->get_row($q);
		 if (isset($data->umeta_id)){
		 	return TRUE;
		 }
		 return FALSE;
	}


	/*
	 * @param array
	 * @return bool
	 */
	public static function custom_insert_user_with_ID($userdata=array()){
		global $wpdb;
		$table = $wpdb->users;
		foreach ($userdata as $key=>$check_data){
			if (empty($userdata[$key]) || is_object($userdata[$key])){
				$userdata[$key] = '';
			} else {
				$userdata[$key] = sanitize_text_field($userdata[$key]);
			}
		}
		$query = $wpdb->prepare( "INSERT INTO $table VALUES(
																	%d,
																	%s,
																	%s,
																	%s,
																	%s,
																	%s,
																	%s,
																	%s,
																	%s,
																	%s
		);",
						$userdata['ID'],
						$userdata['user_login'],
						$userdata['user_pass'],
						$userdata['user_nicename'],
						$userdata['user_email'],
						$userdata['user_url'],
						$userdata['user_registered'],
						$userdata['user_activation_key'],
						$userdata['user_status'],
						$userdata['display_name']
		);
		return $wpdb->query( $query );
	}


	/*
	 * @param array
	 * @return bool
	 */
	public static function custom_insert_usermeta($uid=0, $key_meta='', $meta_value=''){
		global $wpdb;
		$table = $wpdb->usermeta;
		$q = $wpdb->prepare("INSERT INTO $table VALUES(
														null,
														%d,
														%s,
														%s
		)", $uid, $key_meta, $meta_value);
		return $wpdb->query($q);
	}


	/*
	 * @param int (user id)
	 * @param string (selected row)
	 * @return string
	 */
	public static function get_user_col_value($uid=0, $col_name=''){
		if ($uid && $col_name){
			global $wpdb;
			$table = $wpdb->base_prefix . 'users';
			$col_name = sanitize_text_field($col_name);
			$q = $wpdb->prepare("SELECT $col_name FROM $table WHERE ID=%d;", $uid);
			$data = $wpdb->get_row($q);
			if (!empty($data->$col_name)){
				return $data->$col_name;
			}
		}
	}


	/*
	 * @param array
	 * @return int
	 */
	public static function ihc_woo_product_custom_price_save_item($post_data=array()){
		global $wpdb;
		if (!empty($post_data)){
			$table = $wpdb->base_prefix . 'ihc_woo_products';
			if (isset($post_data['settings'])){
				$post_data['settings'] = serialize($post_data['settings']);
			}

			if (!empty($post_data['id'])){
				/// do update
				$q = $wpdb->prepare("UPDATE $table SET slug=%s,
										discount_type=%s,
										discount_value=%s,
										start_date=%s,
										end_date=%s,
										settings=%s,
										status=%s
					WHERE id=%d;
				", $post_data['slug'], $post_data['discount_type'], $post_data['discount_value'],
					$post_data['start_date'], $post_data['end_date'], $post_data['settings'], $post_data['status'],
					$post_data['id']
				);
				$wpdb->query($q);
				return $post_data['id'];
			} else {
				/// do insert
				$q = $wpdb->prepare("INSERT INTO $table VALUES(
												null,
												%s,
												%s,
												%s,
												%s,
												%s,
												%s,
												%s
					)", $post_data['slug'], $post_data['discount_type'], $post_data['discount_value'],
				$post_data['start_date'], $post_data['end_date'], $post_data['settings'], $post_data['status']);
				$wpdb->query($q);
				return $wpdb->insert_id;
			}
		}
		return 0;
	}


	/*
	 * @param array
	 * @return boolean
	 */
	public static function ihc_woo_product_custom_price_delete_item($item_id=0){
		global $wpdb;
		if (!empty($item_id)){
			$table = $wpdb->base_prefix . 'ihc_woo_products';
			$q = $wpdb->prepare("DELETE FROM $table WHERE id=%d ", $item_id);
			return $wpdb->query($q);
		}
		return FALSE;
	}


	/*
	 * @param array
	 * @return array
	 */
	public static function ihc_woo_product_custom_price_get_item($item_id=0){
		global $wpdb;
		if (!empty($item_id)){
			$table = $wpdb->base_prefix . 'ihc_woo_products';
			$q = $wpdb->prepare("SELECT
										id, slug, discount_type, discount_value, start_date, end_date, status, settings
										FROM $table
										WHERE id=%d
			", $item_id);
			$temp = $wpdb->get_row($q);
			if ($temp){
				$temp->settings = maybe_unserialize($temp->settings);
				return (array)$temp;
			}
		}
		return array(
						'id' => 0,
						'status' => 0,
						'slug' => '',
						'discount_type' => '%',
						'discount_value' => '',
						'start_date' => '',
						'end_date' => '',
						'settings' => array(),
		);
	}


	/*
	 * @param int (item_id)
	 * @param int (level id)
	 * @param int (product id or category id)
	 * @param string (type of woo item: product or category)
	 * @return boolean
	 */
	public static function ihc_woo_product_custom_price_lid_product_save($item_id=0, $lid=0, $product_or_cat_id=0, $type_of_woo_item=''){
		global $wpdb;
		if (!empty($item_id) && !empty($product_or_cat_id)){
			$table = $wpdb->base_prefix . 'ihc_woo_product_level_relations';
			$q = $wpdb->prepare("INSERT INTO $table VALUES( null, %d, %d, %d, %s);", $item_id, $lid, $product_or_cat_id, $type_of_woo_item);
			$wpdb->query($q);
		}
	}


	/*
	 * @param int (item_id)
	 * @param string (meta_key)
	 * @return boolean
	 */
	public static function ihc_woo_product_custom_price_lid_product_delete($item_id=0){
		global $wpdb;
		if (!empty($item_id)){
			$table = $wpdb->base_prefix . 'ihc_woo_product_level_relations';
			$q = $wpdb->prepare("DELETE FROM $table WHERE ihc_woo_product_id=%d", $item_id);
			$wpdb->query($q);
		}
	}


	/*
	 * @param int
	 * @return array
	 */
	public static function ihc_woo_product_custom_price_lid_product_get_lid_list($item_id=0){
		global $wpdb;
		$array = array();
		if (!empty($item_id)){
			$table = $wpdb->base_prefix . 'ihc_woo_product_level_relations';
			$q = $wpdb->prepare("SELECT DISTINCT lid FROM $table WHERE ihc_woo_product_id=%d", $item_id);
			$data = $wpdb->get_results($q);
			if ($data){
				foreach ($data as $object){
					$array[] = $object->lid;
				}
			}
		}
		return $array;
	}


	/*
	 * @param int
	 * @return array
	 */
	public static function ihc_woo_product_custom_price_lid_product_get_products_list($item_id=0){
		global $wpdb;
		$array = array();
		if (!empty($item_id)){
			$table = $wpdb->base_prefix . 'ihc_woo_product_level_relations';
			$q = $wpdb->prepare("SELECT DISTINCT woo_item FROM $table WHERE ihc_woo_product_id=%d", $item_id);
			$data = $wpdb->get_results($q);
			if ($data){
				foreach ($data as $object){
					$array[] = $object->woo_item;
				}
			}
		}
		return $array;
	}


	/*
	 * @param none
	 * @return array
	 */
	public static function ihc_woo_products_custom_price_get_all(){
		global $wpdb;
		$array = array();
		$table = $wpdb->base_prefix . 'ihc_woo_products';
		//No query parameters required, Safe query. prepare() method without parameters can not be called
		$query = "
							SELECT id, slug, discount_type, discount_value, start_date, end_date, status, settings
								FROM $table
		";
		$data = $wpdb->get_results( $query );
		if ($data){
			foreach ($data as $object){
				$temp = (array)$object;
				$temp['settings'] = maybe_unserialize($temp['settings']);

				$temp['levels'] = self::ihc_woo_product_custom_price_lid_product_get_lid_list($temp['id']);
				$temp['products'] = self::ihc_woo_product_custom_price_lid_product_get_products_list($temp['id']);
				$array[$temp['id']] = $temp;
			}
		}
		return $array;
	}


	/*
	 * @param int (product id)
	 * @param int (level id)
	 * @param array (category ids)
	 * @return array
	 */
	public static function ihc_woo_products_get_discount_by_lid_prodid($product_id=0, $lid=0, $cat_ids=0){
		global $wpdb;
		$array = array();
		$table_a = $wpdb->base_prefix . 'ihc_woo_products';
		$table_b = $wpdb->base_prefix . 'ihc_woo_product_level_relations';
		$product_id = sanitize_text_field($product_id);
		$lid = sanitize_text_field($lid);
		$q = $wpdb->prepare("
			SELECT a.discount_type as discount_type, a.discount_value as discount_value , UNIX_TIMESTAMP(a.start_date) as c
				FROM $table_a a
				INNER JOIN $table_b b
				ON a.id = b.ihc_woo_product_id
				WHERE
				1=1
				AND
				(b.lid=-1 OR b.lid=%d)
				AND
				(
					(b.woo_item=%d AND b.woo_item_type='product')
						OR
					(b.woo_item=-1 AND b.woo_item_type='all')
		", $lid, $product_id );

		if (!empty($cat_ids)){
			$q .= "
						OR
					(
						b.woo_item_type='category'
							AND
							(
			";
			foreach ($cat_ids as $cat_id){
				if (!empty($put_or)){
					$q .= " OR ";
				}
				$q .= $wpdb->prepare(" b.woo_item=%d ", $cat_id );
				$put_or = TRUE;
			}
					$q .= " )";
			$q .= " )";
		}
		$q .= "
				)
				AND
				( UNIX_TIMESTAMP(a.start_date)<UNIX_TIMESTAMP(NOW()) OR a.start_date='0000-00-00 00:00:00' )
				AND
				( UNIX_TIMESTAMP(a.end_date)>UNIX_TIMESTAMP(NOW()) OR a.end_date='0000-00-00 00:00:00' )
				AND
				a.status=1
		";

		$data = $wpdb->get_results($q);
		if (!empty($data)){
			foreach ($data as $object){
				$array[] = array(
									'discount_type' => $object->discount_type,
									'discount_value' => $object->discount_value,
				);
			}
		}
		return $array;
	}


	/*
	 * @param string (type of log)
	 * @param int (older than timestamp)
	 * @return none
	 */
	public static function delete_logs($type='', $older_then=0){
		global $wpdb;
		if ($type && $older_then){
			$table = $wpdb->prefix . 'ihc_user_logs';
			$q = $wpdb->prepare("DELETE FROM $table WHERE log_type=%s AND create_date<%d;", $type, $older_then);
			$wpdb->query($q);
		}
	}


	/*
	 * @param int (user id)
	 * @param int (level id)
	 * @param int (site id)
	 * @return boolean
	 */
	public static function user_site_save_uid_lid_relation($uid=0, $lid=0, $site_id=0){
		global $wpdb;
		$table = $wpdb->base_prefix . 'ihc_user_sites';
		if (self::get_user_site_for_uid_lid($uid, $lid)){
			/// update
			$q = $wpdb->prepare("UPDATE $table SET site_id=%d WHERE uid=%d AND lid=%d ", $site_id, $uid, $lid);
		} else {
			/// insert
			$q = $wpdb->prepare("INSERT INTO $table VALUES(null, %d, %d, %d);", $site_id, $uid, $lid);
		}
		return $wpdb->query($q);
	}


	/*
	 * @param int (user id)
	 * @param int (level id)
	 * @return int (site id)
	 */
	public static function get_user_site_for_uid_lid($uid=0, $lid=0){
		global $wpdb;
		$table = $wpdb->base_prefix . 'ihc_user_sites';
		$q = $wpdb->prepare("SELECT site_id FROM $table WHERE uid=%d AND lid=%d ", $uid, $lid);
		$exists = $wpdb->get_row($q);
		if ($exists && isset($exists->site_id)){
			return $exists->site_id;
		}
		return 0;
	}


	/*
	 * @param int
	 * @return array
	 */
	public static function get_sites_by_uid($uid=0){
		global $wpdb;
		$array = array();
		$table = $wpdb->base_prefix . 'ihc_user_sites';
		$q = $wpdb->prepare("SELECT site_id FROM $table WHERE uid=%d ", $uid);
		$data = $wpdb->get_results($q);
		if ($data){
			foreach ($data as $object){
				$array[] = $object->site_id;
			}
		}
		return $array;
	}


	/*
	 * @param int
	 * @return array
	 */
	public static function get_sites_by_lid($lid=0){
		global $wpdb;
		$array = array();
		$table = $wpdb->base_prefix . 'ihc_user_sites';
		$q = $wpdb->prepare("SELECT site_id FROM $table WHERE lid=%d ", $lid);
		$data = $wpdb->get_results($q);
		if ($data){
			foreach ($data as $object){
				$array[] = $object->site_id;
			}
		}
		return $array;
	}


	/*
	 * @param int (blog id)
	 * @return bool
	 */
	public static function delete_user_site_item_by_blog_id($blog_id=0){
		global $wpdb;
		$table = $wpdb->base_prefix . 'ihc_user_sites';
		$q = $wpdb->prepare("SELECT id FROM $table WHERE site_id=%d", $blog_id);
		$exists = $wpdb->get_row($q);
		if ($exists && !empty($exists->id)){
			$q = $wpdb->prepare("DELETE FROM $table WHERE id=%d ", $exists->id);
			return $wpdb->query($q);
		}
		return FALSE;
	}


	/*
	 * @param int (user id)
	 * @param string (meta key)
	 * @return bool
	 */
	public static function does_user_meta_exists($uid=0, $meta_key='', $meta_value =''){
		global $wpdb;
		if ($uid && $meta_key){
			$table = $wpdb->usermeta;
			$q = $wpdb->prepare("SELECT umeta_id FROM $table WHERE meta_key=%s AND user_id=%d AND meta_value=%s ", $meta_key, $uid, $meta_value);
			$data = $wpdb->get_row($q);
			if ($data && !empty($data->umeta_id)){
				return TRUE;
			}
		}
		return FALSE;
	}


	/*
	 * @param bool
	 * @return int
	 */
	public static function user_get_count($exclude_admin=FALSE){
		global $wpdb;
		$table = $wpdb->users;
		$q = "SELECT COUNT(ID) as num FROM $table WHERE 1=1 ";
		if ($exclude_admin){
			$data = ihc_get_admin_ids_list();
			$not_in = implode(',', $data);
			$q .= " AND ID NOT IN ($not_in) ";
		}
		$data = $wpdb->get_row($q);
		return (isset($data->num)) ? $data->num : 0;
	}


	/*
	 * @param int (blog id)
	 * @return bool
	 */
	public static function is_blog_available($blog_id=0){
		global $wpdb;
		if ($blog_id){
			$table = $wpdb->base_prefix . 'blogs';
			$q = $wpdb->prepare("SELECT public, deleted FROM $table WHERE blog_id=%d ", $blog_id);
			$data = $wpdb->get_row($q);
			if ($data){
				if ($data->public && !$data->deleted){
					return TRUE;
				}
			}
		}
		return FALSE;
	}


	/**
	 * @param int
	 * @param int
	 * @return int
	 */
	public static function ihc_download_monitor_get_user_limit($uid=0, $lid=0){
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_download_monitor_limit';
		$q = $wpdb->prepare("SELECT download_limit FROM $table WHERE uid=%d AND lid=%d ", $uid, $lid);
		$c = $wpdb->get_var($q);
		return $c;
	}


	/**
	 * @param int
	 * @param int
	 * @return bool
	 */
	public static function ihc_download_monitor_update_user_limit($uid=0, $lid=0){
		global $wpdb;
		$table = $wpdb->prefix . 'ihc_download_monitor_limit';
		$q = $wpdb->prepare("SELECT download_limit FROM $table WHERE uid=%d AND lid=%d ", $uid, $lid);
		$c = $wpdb->get_var($q);
		if ($c==null){
			$c = 0;
			$do_insert = TRUE;
		}
		$increment_data =  get_option('ihc_download_monitor_values');
		$increment = empty($increment_data['level_' . $lid]) ? 0 : $increment_data['level_' . $lid];
		$c = $c + $increment;
		if (!empty($do_insert)){
			$q = $wpdb->prepare("INSERT INTO $table VALUES(%d, %d, %d);", $uid, $lid, $c);
			return $wpdb->query($q);
		} else {
			$q = $wpdb->prepare("UPDATE $table SET download_limit=%d WHERE uid=%d AND lid=%d;", $c, $uid, $lid);
			return $wpdb->query($q);
		}
	}


	/**
	 * @param int (user id)
	 * @param string (transation id)
	 * @return bool
	 */
	public static function ihc_paypal_transaction_id_exists($uid=0, $txn_id=''){
		if ($uid && $txn_id){
			global $wpdb;
			$q = $wpdb->prepare("SELECT id FROM {$wpdb->prefix}indeed_members_payments WHERE u_id=%d AND txn_id=%s ", $uid, $txn_id);
			$data = $wpdb->get_var($q);
			if ($data){
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * @param int (order id)
	 * @return float (amount value)
	 */
	public static function getOrderAmount($order_id=0){
		global $wpdb;
		if ($order_id){
			$table = $wpdb->prefix . 'ihc_orders';
			$q = $wpdb->prepare("SELECT amount_value FROM $table WHERE id=%d;", $order_id);
			$data = $wpdb->get_var($q);
			if ($data!=NULL){
				return $data;
			}
		}
		return 0;
	}

	public static function decrement_coupon($coupon_code=''){
			if ($coupon_code){
					global $wpdb;
					$q = $wpdb->prepare("SELECT submited_coupons_count FROM {$wpdb->prefix}ihc_coupons WHERE code=%s ", $coupon_code);
					$current = $wpdb->get_var($q);
					if ($current){
							$current--;
							$q = $wpdb->prepare("UPDATE {$wpdb->prefix}ihc_coupons SET submited_coupons_count=%d WHERE code=%s ", $current, $coupon_code);
							return $wpdb->query($q);
					}
			}
			return FALSE;
	}

	public static function getUserFulltName($uid=0){
			if (empty($uid)){
				 return '';
			}
			$uid = sanitize_text_field($uid);
			$first = get_user_meta($uid, 'first_name', TRUE);
			$last = get_user_meta($uid, 'last_name', TRUE);
			if($first != '' || $last != '')
				return $first . ' ' . $last;

			$nickname = get_user_meta($uid, 'nickname', TRUE);
			return $nickname;
	}

	public static function updateTransactionAddOrderId($txnId='', $orderId=0)
	{
			global $wpdb;
			if (empty($txnId) || empty($orderId)){
					return false;
			}
			$oldValues = $wpdb->get_var($wpdb->prepare("SELECT orders FROM {$wpdb->prefix}indeed_members_payments WHERE txn_id=%s", $txnId));
			$data = arraY();
			if (!empty($oldValues)){
					$data = maybe_unserialize($oldValues);
			}
			if (empty($data) || !in_array($orderId, $data)){
					$data[] = sanitize_text_field( $orderId );
					$serializedArray = serialize($data);
					$query = $wpdb->prepare("UPDATE {$wpdb->prefix}indeed_members_payments SET orders=%s WHERE txn_id=%s", $serializedArray, $txnId);
					return $wpdb->query($query);
			}
			return false;
	}

	public static function mollieGetSubscriptionDataByTransaction($transactionId='')
	{
			global $wpdb;
			if (empty($transactionId)){
					return false;
			}
			$query = $wpdb->prepare("SELECT history FROM {$wpdb->prefix}indeed_members_payments WHERE txn_id=%s", $transactionId);
			$data = $wpdb->get_var($query);
			if (empty($data)){
					return false;
			}
			$dataDecoded = maybe_unserialize($data);
			if (empty($dataDecoded)){
					return false;
			}
			foreach ($dataDecoded as $array){
					if (isset($array['subscriptionId']) && isset($array['customerId'])){
							return array(
									'subscriptionId'			=> $array['subscriptionId'],
									'customerId'					=> $array['customerId'],
							);
					}
			}
			return false;
	}

	public static function PayPalExpressCheckoutGetPaymentDataByToken($token='')
	{
			global $wpdb;
			if (empty($token)){
					return false;
			}
			$query = $wpdb->prepare("SELECT payment_data FROM {$wpdb->prefix}indeed_members_payments WHERE txn_id=%s; ", $token);
			$data = $wpdb->get_var($query);
			if (empty($data)){
					return false;
			}
			$decodedData = json_decode($data, true);
			return $decodedData;
	}

	public static function getUidLidByTxnId($txnId='')
	{
			global $wpdb;
			if (empty($txnId)){
					return false;
			}
			$query = $wpdb->prepare("SELECT payment_data FROM {$wpdb->prefix}indeed_members_payments WHERE txn_id=%s;", $txnId);
			$data = $wpdb->get_var($query);
			if (!$data){
					return false;
			}
			$decoded = json_decode( $data, true );
			return array(
					'uid'		=> $decoded['uid'],
					'lid'		=> isset($decoded['lid']) ? $decoded['lid'] : $decoded['level']
			);
	}

	public static function changeTxnId($old='', $new='')
	{
			global $wpdb;
			if (!$old || !$new){
					return false;
			}
			$query = $wpdb->prepare("UPDATE {$wpdb->prefix}indeed_members_payments SET txn_id=%s WHERE txn_id=%s;", $new, $old);
			return $wpdb->query($query);
	}

	public static function getLastOrderIdForTransaction( $txnId='' )
	{
			global $wpdb;
			if ( !$txnId ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT orders FROM {$wpdb->prefix}indeed_members_payments WHERE txn_id=%s;", $txnId );
			$data = $wpdb->get_var( $query );
			if ( !$data ){
					return false;
			}
			$orders = maybe_unserialize( $data );
			if ( !$orders ){
					return false;
			}
			return end( $orders );
	}

	public static function getLastOrderByTxnId( $txnId='' )
	{
			global $wpdb;
			if ( !$txnId ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT a.id FROM
																			{$wpdb->prefix}ihc_orders a
																			INNER JOIN {$wpdb->prefix}ihc_orders_meta b
																			ON a.id=b.order_id
																			WHERE
																			b.meta_key='txn_id'
																			AND
																			b.meta_value=%d
																			ORDER BY a.id DESC
			", $txnId );
			return $wpdb->get_var( $query );
	}

	public static function getLastOrderIdByUserAndLevel( $uid=0, $lid=0 )
	{
			global $wpdb;
			if ( !$uid || !$lid ){
					return false;
			}
			$data = $wpdb->get_var( $wpdb->prepare( "SELECT id
																									FROM {$wpdb->prefix}ihc_orders WHERE uid=%d AND lid=%d ORDER BY id DESC LIMIT 1;", $uid, $lid ) );
			return $data;
	}

	public static function getLastOrderDataByUserAndLevel( $uid=0, $lid=0 )
	{
			global $wpdb;
			if ( !$uid || !$lid ){
					return false;
			}
			$data = $wpdb->get_row( $wpdb->prepare( "SELECT id,amount_type,amount_value,automated_payment,status,create_date
																									FROM {$wpdb->prefix}ihc_orders WHERE uid=%d AND lid=%d ORDER BY id DESC LIMIT 1;", $uid, $lid ) );
			return (array)$data;
	}

	public static function getTxnIdByOrder( $orderId=0 )
	{
			global $wpdb;
			if ( !$orderId ){
					return false;
			}
			$temporaryData = $wpdb->get_row( $wpdb->prepare( "SELECT uid, lid FROM {$wpdb->prefix}ihc_orders WHERE id=%d; ", $orderId ) );
			if ( !$temporaryData ){
					return false;
			}
			$orderId = sanitize_text_field($orderId);
			$query = $wpdb->prepare( "SELECT txn_id, orders FROM {$wpdb->prefix}indeed_members_payments WHERE u_id=%d AND orders LIKE '%{$orderId}%' ", $temporaryData->uid );
			$data = $wpdb->get_results( $query );
			if ( !$data ){
					return false;
			}
			foreach ( $data as $object ){
					$orders = maybe_unserialize($object->orders);
					if ( in_array( $orderId, $orders ) ){
							return $object->txn_id;
					}
			}
			return false;
	}

	public static function directLoginGetUserByToken( $token='' )
	{
			global $wpdb;
			if ( !$token ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_value=%s AND meta_key='direct_link_token';", $token );
			return $wpdb->get_var( $query );
	}

	public static function directLoginIsTokenActive( $token='' )
	{
			global $wpdb;
			if ( !$token ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT b.meta_value FROM
																		{$wpdb->usermeta} a
																		INNER JOIN {$wpdb->usermeta} b
																		ON a.user_id=b.user_id
				  													WHERE a.meta_value=%s
																		AND a.meta_key='direct_link_token'
																		AND b.meta_key='direct_link_token_timeout';", $token );
			$expireTime = $wpdb->get_var( $query );
			if ( !$expireTime ){
				 return false;
			}
			$currentTime = indeed_get_unixtimestamp_with_timezone();
			if ( $expireTime > $currentTime ){
					return true;
			}
			return false;
	}

	public static function directLoginGettAllItems()
	{
			global $wpdb;
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "SELECT a.ID, a.user_login, b.meta_value as token, c.meta_value as timeout
										FROM {$wpdb->users} a
										INNER JOIN {$wpdb->usermeta} b
										ON a.ID=b.user_id
										INNER JOIN {$wpdb->usermeta} c
										ON a.ID=c.user_id
										WHERE
										b.meta_key='direct_link_token'
										AND
										c.meta_key='direct_link_token_timeout'
			";
			return $wpdb->get_results( $query );
	}

	public static function getOrderStatus( $orderId=0 )
	{
			global $wpdb;
			if ( !$orderId ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT status FROM {$wpdb->prefix}ihc_orders WHERE id=%d;", $orderId );
			return $wpdb->get_var($query);
	}

	public static function updateOrderStatus( $orderId=0, $newStatus='' )
	{
			global $wpdb;
			if ( !$orderId || !$newStatus ){
					return false;
			}
			if ( self::getOrderStatus( $orderId )===false ){
					return false;
			}
			$query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_orders SET status=%s WHERE id=%d;", $newStatus, $orderId );
			$result = $wpdb->query( $query );
			do_action( 'ump_payment_check', $orderId, 'update' );
			return $result;
	}

	public static function isSubscriptionTrial( $txnId='', $uid=0, $lid=0 )
	{
			if ( empty($txnId) || empty($uid) || empty($lid) ){
					return false;
			}
			if ( !self::level_has_trial_period( $lid ) ){
					return;
			}

	}

	public static function level_has_trial_period( $lid=0 )
	{
			if ( !$lid ){
					return false;
			}
			$levelData = ihc_get_level_by_id( $lid );
			if ( !$levelData ){
					return false;
			}
			if ( !isset($levelData['access_trial_type']) ){
					return false;
			}
			if ( $levelData['access_trial_type']==1 && $levelData['access_trial_time_value']!='' ){
					return true;
			} else if ( $levelData['access_trial_type']==2 && $levelData['access_trial_couple_cycles']!='' ){
					return true;
			}
			return false;
	}

	public static function modifyGuid( $postId=0, $newValue='' )
	{
			global $wpdb;
			if ( !$postId ){
					return;
			}
			$query = $wpdb->prepare( "UPDATE {$wpdb->posts} SET guid=%s WHERE ID=%d ;", $newValue, $postId );
			return $wpdb->query( $query );
	}

	public static function updateAttachmentMetadataFileUrl( $postId=0, $fileUrl='' )
	{
			if ( !$postId || !$fileUrl ){
					return false;
			}
			$data = get_post_meta( $postId, '_wp_attachment_metadata', true);
			if ( !$data ){
					return false;
			}
			$data['file'] = $fileUrl;
			return update_post_meta( $postId, '_wp_attachment_metadata', $data);
	}

	public static function getMediaBaseImage( $mediaId=0 )
	{
			global $wpdb;
			if ( !$mediaId ){
					return false;
			}
			$data = get_post_meta( $mediaId, '_wp_attachment_metadata', true);
			if ( !$data || empty($data['file']) ){
					return false;
			}
			if ( strpos( $data['file'], 'http' ) === false ){
					$link = wp_get_attachment_url( $mediaId, 'medium', true );
			}
			if ( !empty( $link ) ){
					return $link;
			}
			return $data['file'];
	}


	public static function doesMediaHashExists( $hash='' )
	{
			$data = get_option( 'ihc_media_hash_data', true );
			if ( !is_array($data) || in_array( $hash, $data ) == false ){
					return false;
			}
			return true;
	}

	public static function saveMediaHash( $hash='' )
	{
			if ( !$hash ){
					return false;
			}
			$data = get_option( 'ihc_media_hash_data', true );
			if ( !is_array($data) ){
					$data = array();
			}
			$data[] = $hash;
			return update_option( 'ihc_media_hash_data', $data );
	}

	public static function deleteMediaHash( $hash='' )
	{
			if ( !$hash ){
					return false;
			}
			$data = get_option( 'ihc_media_hash_data', true );
			if ( !$data ){
					return false;
			}
			$key = array_search( $hash, $data );
			if ( $key === false ){
					return false;
			}
			unset( $data[$key] );
			return update_option( 'ihc_media_hash_data', $data );
	}

	public static function isListingUserAcceptEnabled()
	{
			$registerFields = get_option( 'ihc_user_fields' );
			if ( !$registerFields ){
					return false;
			}
			$accept = ihc_array_value_exists( $registerFields, 'ihc_memberlist_accept', 'name' );
			if ( !$accept ){
					return false;
			}
			if ( empty( $registerFields[$accept] ) || empty( $registerFields[$accept]['display_public_reg'] ) ){
					return false;
			}
			return true;
	}

	/**
	 * @param int
	 * @return int
	 */
	public static function getLidByOrder( $id=0 )
	{
			global $wpdb;
			if ( !$id ){
					return 0;
			}
			$queryString = $wpdb->prepare( "SELECT lid FROM {$wpdb->prefix}ihc_orders WHERE id=%d;", $id );
			return $wpdb->get_var( $queryString );
	}

	/**
	 * @param int
	 * @return bool
	 */
	public static function deleteLocker( $id=0 )
	{
			if ( !$id ){
					return false;
			}
			$data = get_option( 'ihc_lockers' );
			if ( $data===false || !isset( $data[$id] ) ){
					return false;
			}
			unset($data[$id]);
			return update_option( 'ihc_lockers', $data );
	}

	/**
	 * @param string
	 * @return bool
	 */
	public static function deactivateApTab( $slug='' )
	{
			if ( !$slug ){
					return false;
			}
			$data = get_option( 'ihc_ap_tabs' );
			if ( !$data ){
					return false;
			}
			$array = explode( ',', $data );
			if ( !$array ){
					return false;
			}
			foreach ( $array as $key=>$value ){
					if ( $value == $slug ){
							unset($array[$key]);
							break;
					}
			}
			$data = implode( ',', $array );
			return update_option( 'ihc_ap_tabs', $data );
	}

	/**
	 * @param string
	 * @return bool
	 */
	public static function activateApTab( $slug='' )
	{
			if ( !$slug ){
					return false;
			}
			$data = get_option( 'ihc_ap_tabs' );
			if ( !$data ){
					return false;
			}
			$array = explode( ',', $data );
			if ( !$array ){
					return false;
			}
			if ( in_array( $slug, $array ) ){
					return false;
			}
			$array[] = $slug;
			$data = implode( ',', $array );
			return update_option( 'ihc_ap_tabs', $data );
	}

	public static function does_user_exists( $uid=0 )
	{
			global $wpdb;
			if ( !$uid ){
					return false;
			}
			$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->users} WHERE ID=%d", $uid );
			return $wpdb->get_var( $query );
	}

	/**
	 * @param string
	 * @param string
	 * @param int
	 * @return int
	 */
	public static function doesUserMetaValueExists( $metaKey='', $metaValue='', $excludeUid=0 )
	{
			global $wpdb;
			if ( $metaKey == '' || $metaValue == '' ){
					return 0;
			}
			$query = $wpdb->prepare( "SELECT umeta_id FROM {$wpdb->usermeta} WHERE meta_value=%s AND meta_key=%s ", $metaValue, $metaKey );
			if ( !empty( $excludeUid ) ){
					$query .= $wpdb->prepare( " AND user_id != %d;", $excludeUid );
			}
			return $wpdb->get_var( $query );
	}

	public static function deletePostMetaRestrictionsForMembership( $id=0 )
	{
			global $wpdb;
			$table = $wpdb->prefix . 'postmeta';
			//No query parameters required, Safe query. prepare() method without parameters can not be called
			$query = "SELECT post_id, meta_value FROM $table WHERE meta_key='ihc_mb_who';";
			$data = $wpdb->get_results( $query );
			if ( !$data ){
					return;
			}
			foreach ($data as $object){
				if ( !$object->meta_value ){
						continue;
				}
				$post_levels = explode(',', $object->meta_value);
				if ( !$post_levels ){
						continue;
				}
				foreach ($post_levels as $k=>$u_lid){
					if ($u_lid==$id){
						unset($post_levels[$k]);
						$level_str = implode(',', $post_levels);
						$q = $wpdb->prepare("UPDATE $table SET meta_value=%s WHERE post_id=%d AND meta_key='ihc_mb_who';", $level_str, $object->post_id);
						$wpdb->query($q);
						break;
					}
				}
			}
	}

	public static function getUserRole( $uid=0 )
	{
			global $wpdb;
			if ( !$uid ){
					return '';
			}
			$capability = $wpdb->prefix . 'capabilities';
			$data = get_user_meta( $uid, $capability, true );
			return $data;
	}

	/**
	 * @param int
	 * @param int
	 * @param int
	 * @return string
	 */
	public static function getTransactionIdForUserSubscription( $uid=0, $lid=0, $orderId=0 )
	{
			global $wpdb;
			if ( !$uid || !$lid ){
					return false;
			}

			// search into last order meta
			if ( $orderId ){
					$orderMetaObject = new \Indeed\Ihc\Db\OrderMeta();
					$transactionId = $orderMetaObject->get( $orderId, 'transaction_id' );
					if ( $transactionId !== false || $transactionId !== '' ){
							return $transactionId;
					}
			}

			// serach into first order meta
			$query = $wpdb->prepare( "SELECT a.meta_value as transaction_id, b.create_date
											FROM {$wpdb->prefix}ihc_orders_meta a
											INNER JOIN {$wpdb->prefix}ihc_orders b ON a.order_id=b.id
											WHERE
											a.meta_key='transaction_id'
											AND
											b.uid=%d
											AND
											b.lid=%d
											ORDER BY b.create_date
											DESC
											LIMIT 1
											", $uid, $lid );
			$transactionId = $wpdb->get_var( $query );
			if ( $transactionId !== null || $transactionId !== false ){
					return $transactionId;
			}

			// search into indeed_members_payments table
			$query = $wpdb->prepare( "SELECT txn_id, payment_data FROM {$wpdb->prefix}indeed_members_payments WHERE u_id=%d ORDER BY paydate DESC;", $uid );
			$results = $wpdb->get_results( $query );
			if ( !$results ){
					return false;
			}
			foreach ( $results as $result ){
					$resultData = json_decode( $result->payment_data, true );
					if ( isset( $resultData['lid'] ) && $resultData['lid'] == $lid ){
						return $result->txn_id;
					}
			}
	}

	/**
	 * @param int
	 * @param int
	 * @return object
	 */
	public static function stripeConnectGetCardsThatWillExpire( $month=0, $year=0 )
	{
			global $wpdb;
			$query = $wpdb->prepare( "SELECT user_id as uid, level_id as lid
										FROM {$wpdb->prefix}ihc_user_levels a
										INNER JOIN
										{$wpdb->prefix}ihc_user_subscriptions_meta b
										ON a.id=b.subscription_id
										INNER JOIN
										{$wpdb->prefix}ihc_user_subscriptions_meta c
										ON a.id=c.subscription_id
										WHERE
										b.meta_key='payment_method_exp_month'
										AND
										b.meta_value=%d
										AND
										c.meta_key='payment_method_exp_year'
										AND
										c.meta_value=%d
			", $month, $year );
			return $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Deprecated, use instead : \Indeed\Ihc\UserSubscriptions::getSubscriptionsUsersList( $lid=-1, $only_active=FALSE )
	 * @param int
	 * @param bool
	 * @return array
	 */
	public static function get_level_users_list($lid=-1, $only_active=FALSE)
	{
	 global $wpdb;
	 $data = array();
	 if ($lid>-1){
		 $table = $wpdb->prefix . 'ihc_user_levels';
		 $q = $wpdb->prepare("SELECT user_id FROM $table WHERE level_id=%d", $lid);
		 $data = $wpdb->get_results($q);
		 if ($data){
			 foreach ($data as $object){
				 $do_it = TRUE;
				 if ($only_active){
					 /// only active users
					 if (!self::is_user_level_active($object->user_id, $lid)){
						 $do_it = FALSE;
					 }
				 }
				 if ($do_it){
					 $array['username'] = self::get_username_by_wpuid($object->user_id);
					 $array['user_id'] = $object->user_id;
					 $data[] = array(
									 'username' => self::get_username_by_wpuid($object->user_id),
									 'user_id' => $object->user_id,
					 );
				 }
			 }
		 }
	 }
	 return $data;
	}

	/**
	 * Deprecated, use instead :
	 * \Indeed\Ihc\UserSubscriptions::isActive($uid=0, $lid=0);
	 */
	public static function is_user_level_active($uid=0, $lid=0)
	{
		global $wpdb;
		$grace_period = \Indeed\Ihc\UserSubscriptions::getGracePeriod( $uid, $lid );

		$q = $wpdb->prepare("SELECT expire_time, start_time FROM {$wpdb->prefix}ihc_user_levels WHERE user_id=%d AND level_id=%d;", $uid, $lid);
		$data = $wpdb->get_row($q);
		$current_time = indeed_get_unixtimestamp_with_timezone();

		if (!empty($data->start_time)){
			$start_time = strtotime($data->start_time);
			if ($current_time<$start_time){
				//it's not available yet
				return FALSE;
			}
		}
		if (!empty($data->expire_time)){
			$expire_time = strtotime($data->expire_time) + ((int)$grace_period * 24 * 60 *60);
			if ($current_time>$expire_time){
				//it's expired
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * @param int
	 * @param bool
	 * @return array
	 */
	public static function get_user_levels( $uid=0, $check_expire=false )
	{
		 $array = array();
		 if ($uid){
			 global $wpdb;
			 $levels = \Indeed\Ihc\Db\Memberships::getAll();
			 $table = $wpdb->prefix . "ihc_user_levels";
			 $q = $wpdb->prepare("SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status FROM $table WHERE user_id=%d", $uid);
			 $data = $wpdb->get_results($q);
			 if ($data){
				foreach ($data as $object){
					$temp = (array)$object;
					if (isset($levels[$object->level_id]['label'])){
						$temp['label'] = $levels[$object->level_id]['label'];
					} else {
						continue;
					}
					$temp['level_slug'] = $levels[$object->level_id]['name'];
					if (!empty($levels[$object->level_id]['badge_image_url'])){
						$temp['badge_image_url'] = $levels[$object->level_id]['badge_image_url'];
					} else {
						$temp['badge_image_url'] = '';
					}
					if (self::is_user_level_active($uid, $object->level_id)){
						$temp['is_expired'] = FALSE;
					} else {
						$temp['is_expired'] = TRUE;
						if ($check_expire){
							continue;
						}
					}
					$array[$object->level_id] = $temp;
				}
			 }
		 }

		 // @since version 10.8
		 $extraLevels = apply_filters( 'ihc_public_get_user_levels', '', $uid );
		 if ( $extraLevels !== '' ){
				 $temporary = explode( ',', $extraLevels );
				 if ( is_array( $temporary ) && count( $temporary ) > 0 ){
						 foreach ( $temporary as $lid ){
								 if ( !isset( $array[$lid] ) ){
										 $array[$lid] = [
																			'label'						=> isset( $levels[$lid]['label'] ) ? $levels[$lid]['label'] : '',
																			'level_slug'			=> isset( $levels[$lid]['level_slug'] ) ? $levels[$lid]['level_slug'] : '',
																			'badge_image_url'	=> isset( $levels[$lid]['badge_image_url'] ) ? $levels[$lid]['badge_image_url'] : '',
																			'is_expired'			=> false,
										 ];
								 }
						 }
				 }
		 }
		 // @since version 10.8

		 return $array;
	}

	/**
	 * @param int
	 * @param int
	 * @return boolean
	 */
	public static function user_has_level($uid=0, $lid=0)
	{
		 if ($uid && $lid!==FALSE){
		 	 global $wpdb;
			 $table = $wpdb->prefix . 'ihc_user_levels';
			 $q = $wpdb->prepare("SELECT id,user_id,level_id,start_time,update_time,expire_time,notification,status FROM $table WHERE user_id=%d AND level_id=%d;", $uid, $lid);
			 $data = $wpdb->get_row($q);
			 if ($data && isset($data->start_time)){
			 	return TRUE;
			 }
		 }
		 return FALSE;
	}


		/**
		 * @param string
		 * @param string
		 * @return bool
		 */
		public static function userMetaAndValueExists( $metaKey='', $metaValue='' )
		{
				global $wpdb;
				if ( $metaKey === false || $metaValue === false ){
						return false;
				}
				$table = $wpdb->base_prefix . 'usermeta';
				$query = $wpdb->prepare( "SELECT umeta_id FROM $table WHERE meta_value=%s AND meta_key=%s;", $metaValue, $metaKey );
				$data = $wpdb->get_var( $query );
				if ( $data === null || $data === false ){
						return false;
				}
				return true;
		}

		/**
		 * @param string
		 * @param string
		 * @return bool
		 */
		public static function getUserIdForMetaAndValue( $metaKey='', $metaValue='' )
		{
				global $wpdb;
				if ( $metaKey === false || $metaValue === false ){
						return false;
				}
				$table = $wpdb->base_prefix . 'usermeta';
				$query = $wpdb->prepare( "SELECT user_id FROM $table WHERE meta_value=%s AND meta_key=%s;", $metaValue, $metaKey );
				$uid = $wpdb->get_var( $query );
				if ( $uid === null || $uid === false ){
						return 0;
				}
				return $uid;
		}

		/**
		 * @param array
		 * @return array
		 */
		public static function couponsGetMany( $params=[] )
		{
				global $wpdb;
				$data = [];
				//No query parameters required, Safe query. prepare() method without parameters can not be called
				$query = "SELECT id, code, settings, submited_coupons_count, status FROM {$wpdb->prefix}ihc_coupons WHERE status=1 ";
				if ( isset( $params['search_phrase'] ) && $params['search_phrase'] !== '' ){
						$query .= $wpdb->prepare( " AND code LIKE %s ", '%' . $params['search_phrase'] . '%' );
				}
				if ( isset( $params['order_by'] ) && $params['order_by'] !== '' && isset( $params['order_type'] ) && $params['order_type'] !== '' ){
						$query .= $wpdb->prepare( " ORDER BY %1s %1s ", $params['order_by'], $params['order_type'] );
				}
				if ( isset( $params['limit'] ) && $params['limit'] !== '' && isset( $params['offset'] ) && $params['offset'] !== '' ){
						$query .= $wpdb->prepare( " LIMIT %d OFFSET %d ", $params['limit'], $params['offset'] );
				}
				$coupons = $wpdb->get_results( $query );
				if ( !$coupons ){
						return $data;
				}
				foreach ( $coupons as $coupon ){
						$data[$coupon->id]['id'] = $coupon->id;
						$data[$coupon->id]['code'] = $coupon->code;
						$data[$coupon->id]['settings'] = maybe_unserialize( $coupon->settings );
						$data[$coupon->id]['submited_coupons_count'] = $coupon->submited_coupons_count;
				}
				return $data;
		}

		public static function couponsCountWithFilter()
		{
				global $wpdb;
				$data = [];
				//No query parameters required, Safe query. prepare() method without parameters can not be called
				$query = "SELECT COUNT(id) FROM {$wpdb->prefix}ihc_coupons WHERE status=1 ";
				if ( isset( $params['search_phrase'] ) && $params['search_phrase'] !== '' ){
						$query .= $wpdb->prepare( " AND code LIKE %s ", '%' . $params['search_phrase'] . '%' );
				}
				return $wpdb->get_var( $query );
		}

		public static function tableEngine()
		{
		    global $wpdb;
		    $engineType = 'MyISAM';
		    $table_name = $wpdb->base_prefix . 'ihc_download_monitor_limit';
		    //No query parameters required, Safe query. prepare() method without parameters can not be called
		    $query = "show tables like '$table_name';";
		    if ($wpdb->get_var( $query )!=$table_name){
		        require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		        $sql = "CREATE TABLE " . $table_name . " (
		                      uid INT(11) NOT NULL,
		                      lid INT(11) NOT NULL,
		                      download_limit INT(11) NOT NULL
		              )
		              ENGINE=MyISAM
		              CHARACTER SET utf8 COLLATE utf8_general_ci;";
		        $results = dbDelta ( $sql );
		       	$error = $wpdb->last_error;
						// since version 12.5
		        if ( $error !== "" && $error !== false && $error !== null ){
		            $engineType = 'InnoDB';
		        }
		    }
		    return $engineType;
		}

		/**
		 * @param none
		 * @return bool
		 */
		public static function membershipTableExists()
		{
				global $wpdb;
				$table_name = $wpdb->base_prefix . "ihc_user_levels";
				$query = "show tables like '$table_name'";
				if ( $wpdb->get_var( $query ) != $table_name ){
						return false;
				}
				return true;
		}



}

endif;
