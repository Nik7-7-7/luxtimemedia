<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('MainAdminUlp')){
	 return;
}

class MainAdminUlp{
	/**
	 * @var array
	 */
	private static $tabs;
	/**
	 * @var array
	 */
	private static $extra_actions;

	private static $per_page = 20;

	private static $default_admin_page = 'dashboard';

	private static $dashboard_available = TRUE;

	private static $excluded_tabs = [];

	private static $uid = 0;

	private static $userRoles = null;

	private static $error_messages	= [];



	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}


	/**
	 * @param none
	 * @return none
	 */
	public static function run(){
		self::initActions();
		self::checkSystem();
		self::$tabs = [
													'students' => esc_html__('Students', 'ulp'),
													'instructors' => esc_html__('Instructors', 'ulp'),
													'ulp_course' => esc_html__('Courses', 'ulp'),
													'ulp_lesson' => esc_html__('Lessons', 'ulp'),
													'ulp_quiz' => esc_html__('Quizzes', 'ulp'),
													'ulp_question' => esc_html__('Questions', 'ulp'),
													'payment_services' => esc_html__('Payment Gateways', 'ulp'),
													'ulp_certificate' => esc_html__('Certificates', 'ulp'),
													'showcases' => esc_html__('Showcases', 'ulp'),
													'magic_feat' => esc_html__('Extensions', 'ulp'),
													'shortcodes' => esc_html__('Shortcodes', 'ulp'),
													'ulp_order' => esc_html__('Payment History', 'ulp'),
													'notifications' => esc_html__('Email Notifications', 'ulp'),
													'settings' => esc_html__('General Settings', 'ulp'),
		];
		//self::$tabs = apply_filters( 'ulp_admin_filter_tab_items', self::$tabs );

		if (empty(ULP_LICENSE_SET)){
				self::$excluded_tabs = ['instructors', 'ulp_quiz'];
		}

		if ( !get_option('ulp_certificates_enable') ){
				unset(self::$tabs ['ulp_certificate']);
		}

		self::$extra_actions = [
										'post_special_settings' => true,
										'notifications_add_edit' => true,
										'view_student_activity' => true,
										'import_export' => esc_html__('Export/Import', 'ulp'),
										'help' => esc_html__('Help', 'ulp'),
										//'manage_course_reviews' => true,
										'student_badges' => true,
										'ulp_certificates_magic_feat' => true,
										'grade_book' => true,
										'account_page' => true,
										'student_leaderboard' => true,
										'courses_grid' => true,
										'paypal_magic_feat' => true,
										'payment_service_bt' => true,
										'payment_service_paypal' => true,
										'payment_service_stripe' => true,
										'payment_service_2checkout' => true,
										'custom_currencies' => true,
										'pushover' => true,
										'course_difficulty' => true,
										'about_the_instructor' => true,
										'courses_tags_add_edit' => true,
										'ulp_announcement' => true,
										'ulp_qanda' => true,
										'hooks'			=> true,
		];
		self::$extra_actions = self::$extra_actions + self::magic_feature_items();
	}

	private static function getRoles()
	{
			if (self::$userRoles==null){
				self::$uid = ulp_get_current_user();
				self::$userRoles = DbUlp::get_user_roles(self::$uid);
			}
			return self::$userRoles;
	}

	/*
	 * @param none
	 * @return none
	 */
	public static function filterEntitiesByRole()
	{
			// if the current user is instructor or instructor pending, we don't want to show him all the courses, lessons, etc.
			$roles = self::getRoles();
			if (!isset($roles['administrator']) && (isset($roles['ulp_instructor']) || isset($roles['ulp_instructor_senior'])) ) {
					add_filter('ulp_admin_filter_show_entities_only_for', ['MainAdminUlp', 'showEntitiesOnlyForAuthorId'], 1, 1);
			}
	}

	public static function showEntitiesOnlyForAuthorId($userId=0)
	{
			return self::$uid;
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function initActions(){

		add_action('init', ['MainAdminUlp', 'doUpdates'], 999);
		add_action('admin_menu', array('MainAdminUlp', 'menu'), 50);
		add_action("admin_enqueue_scripts", array('MainAdminUlp', 'assets'));
		add_action('save_post', array('MainAdminUlp', 'do_edirect_after_cpt_save'), 999, 3);
		add_filter('display_post_states', array('MainAdminUlp', 'dashboard_pages_ulp_column'), 999, 2 );
		add_action('add_meta_boxes', array('MainAdminUlp', 'create_page_meta_box'), 997, 1);
		add_action('save_post', array('MainAdminUlp', 'save_meta_box_values'));
		add_action('admin_bar_menu', array('MainAdminUlp', 'wp_print_top_menu'), 990);
		add_action('user_new_form', array('MainAdminUlp', 'add_edit_wp_user_enrolled_courses'), 99);
		add_action('edit_user_profile', array('MainAdminUlp', 'add_edit_wp_user_enrolled_courses'), 98);
		add_action('edit_user_profile', array('MainAdminUlp', 'edit_wp_user_become_instructor'), 99);

		add_action('profile_update', array('MainAdminUlp', 'after_save_user_action'), 99, 2);
		add_action('user_register', array('MainAdminUlp', 'after_save_user_action'), 99, 1);
		add_action('admin_notices', array('MainAdminUlp', 'global_notice'), 999);
		add_action('init', array('MainAdminUlp', 'add_wp_editor_custom_button'));
		add_action('init', ['MainAdminUlp', 'checkAccess']);
		add_action('init', ['MainAdminUlp', 'filterEntitiesByRole']);
		add_filter('ulp_filter_custom_post_type_dashboard_action_links', ['MainAdminUlp', 'filterCustomPostTypeActionDashboardByRole']);
		add_filter('ulp_admin_special_settings_access', ['MainAdminUlp', 'filterShowSpecialSettingsPage']);
		add_action('init', ['MainAdminUlp', 'doFlushRewrite']);
		///
		require_once ULP_PATH . 'classes/admin/Ulp_Multiple_Instructors.class.php';
		$Ulp_Multiple_Instructors = new Ulp_Multiple_Instructors();

		$VideoLesson = new \Indeed\Ulp\Admin\VideoLesson();

	}

	public static function doUpdates()
	{
			/// run the updates
			UlpUpdates::run();
	}

	public static function doFlushRewrite()
	{
			$do = get_option('ulp_do_flush_rewrite_on_init');
			if ($do){
					flush_rewrite_rules();
					update_option('ulp_do_flush_rewrite_on_init', false);
			}
	}

	public static function checkAccess()
	{
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}
			if (is_user_logged_in()){
					$uid = get_current_user_id();
					$user = new WP_User( $uid );
					$url = get_home_url();
					if ($user && !empty($user->roles) && !empty($user->roles[0]) && !in_array( 'administrator', $user->roles )
							&& !in_array( 'ulp_instructor_senior', $user->roles ) && !in_array( 'ulp_instructor', $user->roles ) ){
						//&& $user->roles[0]!='administrator' && $user->roles[0]!='ulp_instructor'&& $user->roles[0]!='ulp_instructor_senior'
							$allowed_roles = DbUlp::getOptionMetaGroup('access');
							if ($allowed_roles['ulp_dashboard_allowed_roles']){
								$roles = explode(',', $allowed_roles['ulp_dashboard_allowed_roles']);
									$show = false;
									foreach ( $roles as $role ){
											if ( !empty( $role ) && !empty( $user->roles ) && in_array( $role, $user->roles ) ){
												$show = true;
											}
									}
									if ( !$show ){
										wp_safe_redirect($url);
										exit;
									}
							} else {
									wp_safe_redirect($url);
									exit;
								return;
							}
					}
			}
	}

	public static function filterCustomPostTypeActionDashboardByRole($links=[])
	{
			$roles = self::getRoles();
			if (isset($roles['ulp_instructor'])){
					$canAccess = get_option('ulp_show_special_settings_for_entry_instructors');
					if (!$canAccess){
							unset($links['settings']);
					}
			}
			return $links;
	}

	public static function filterShowSpecialSettingsPage($show=true)
	{
		$roles = self::getRoles();
		if (isset($roles['ulp_instructor'])){
				$canAccess = get_option('ulp_show_special_settings_for_entry_instructors');
				if (!$canAccess){
						return false;
				}
		}
		return $show;
	}

	public static function add_wp_editor_custom_button(){
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return;
		}
		if (is_user_logged_in()){
				$uid = get_current_user_id();
				$role = '';
				$user = new WP_User( $uid );
				if ($user && !empty($user->roles) && !empty($user->roles[0]) && $user->roles[0]!='administrator'){

					$allowed_roles = DbUlp::getOptionMetaGroup('access');
					if ($allowed_roles['ulp_dashboard_allowed_roles']){
						$roles = explode(',', $allowed_roles['ulp_dashboard_allowed_roles']);
						if ($roles && is_array($roles) && !in_array($user->roles[0], $roles)){
							/// maybe redirect here ...
							return;
						}
					} else {
						/// maybe redirect here ...
						return;
					}
				}
		    if (!current_user_can('edit_posts') || !current_user_can('edit_pages')){
		    	return;
		    }
		    if (get_user_option('rich_editing') == 'true') {
		    	/// add the buttons
		    	add_filter( 'mce_buttons', array('MainAdminUlp','register_editor_button') );
		    	add_filter( "mce_external_plugins", array('MainAdminUlp',"editor_button_js_file") );
		    }
		}
	}

	public static function register_editor_button( $arr ) {
		array_push( $arr, 'ulp_button_forms' );
		return $arr;
	}

	public static function editor_button_js_file( $arr ) {
		$arr['ulp_button_forms'] =  ULP_URL . 'assets/js/wp_editor_bttns.js';
		return $arr;
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function menu(){
		$uid = ulp_get_current_user();
		$role = DbUlp::get_user_roles($uid);
		$isInstructor = (isset($role['ulp_instructor']) || isset($role['ulp_instructor_senior'])) ? true : false;
		if ($isInstructor){
				self::$tabs = array(
								'ulp_course' => esc_html__('Courses', 'ulp'),
								'ulp_lesson' => esc_html__('Lessons', 'ulp'),
								'ulp_quiz' => esc_html__('Quizzes', 'ulp'),
								'ulp_question' => esc_html__('Questions', 'ulp'),
				);
				self::$default_admin_page = 'ulp_course';
				self::$dashboard_available = FALSE;
		}
		self::$tabs = apply_filters( 'ulp_admin_filter_tab_items', self::$tabs );
		if(is_super_admin() || $isInstructor){
			add_menu_page('Ultimate Learning Pro', '<span>Ultimate Learning Pro</span>', 'edit_posts',	'ultimate_learning_pro', array('MainAdminUlp', 'output') , 'dashicons-welcome-learn-more');
		}
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function output()
	{
			$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : self::$default_admin_page;
			if ( method_exists ( __CLASS__ , $tab ) ){
					echo self::printHeader() . self::printTopMessages() . self::$tab() . self::footer();
			} else {
					$content = apply_filters( 'ulp_print_admin_page', '', $tab );
					echo self::printHeader() . self::printTopMessages() . $content . self::footer();
			}
	}


	public static function footer(){
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/footer.php');
		$view->setContentData(array());
		return $view->getOutput();
	}


	/**
	 * @param none
	 * @return string
	 */
	private static function printHeader(){
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/header.php');
		$data = array(
									'tabs' => self::$tabs,
									'excluded_tabs' => self::$excluded_tabs,
									'current_tab' => isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard',
									'dashboard_url' => admin_url('admin.php?page=ultimate_learning_pro&tab=dashboard'),
									'base_url' => admin_url('admin.php?page=ultimate_learning_pro&tab='),
									'right_tabs' => array(
											'import_export' => esc_html__('Import/Export', 'ulp'),
											'help' => esc_html__('Help', 'ulp')
									),
		);
		if (self::$dashboard_available===FALSE){
				$data ['dashboard_url'] = '#';
		}
		$view->setContentData($data);
		return $view->getOutput();
	}

	public static function global_notice(){
			if (current_user_can('manage_options') && (!isset($_GET['page']) || $_GET['page']!='ultimate_learning_pro')){
					echo self::print_notice();
			}
	}

	public static function print_notice(){
			$data = [];
			$hide = get_option( 'ulp_hide_admin_license_notice' );
			$currentPage = isset($_GET['page']) ? $_GET['page'] : '';
			if ( $currentPage != 'ultimate_learning_pro' && $hide ){
					return '';
			}
			if (empty(ULP_LICENSE_SET)){
					$data ['notices'][] = esc_html__("This is a Trial Version of Ultimate Learning Pro plugin. Please add your purchase code into Licence section to enable the Full Ultimate Learning Pro Version. Check your ", 'ulp') . "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=help') . "'>" . esc_html__('licence section', 'ulp') . "</a>.";
			}
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/notice_section.php');
			$view->setContentData($data);
			return $view->getOutput();
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function assets(){
		global $post, $wp_version;
		$is_ulp_type_post = FALSE;
		if (isset($post->ID)){
				$type = get_post_type($post->ID);
				if (in_array($type, DbUlp::plugin_post_types())){
						$is_ulp_type_post = TRUE;
				}
		}

		wp_register_script('ulp_main_admin', ULP_URL . 'assets/js/admin.js', array('jquery'), '3.9' );

		if ( version_compare ( $wp_version , '5.7', '>=' ) ){
				wp_add_inline_script('ulp_main_admin', "var ulp_url='" . get_site_url() . "';" );
				wp_add_inline_script('ulp_main_admin', "var ulp_plugin_url='" . ULP_URL . "';" );
				wp_add_inline_script('ulp_main_admin', "var ulpKeepData=" . get_option('ulp_keep_data_after_delete', 0) . ";" );
				wp_localize_script('ulp_main_admin', 'ulp_admin_messages', self::messages_for_javascript( false ) );
		} else {
				wp_localize_script('ulp_main_admin', 'ulp_url', get_site_url());
				wp_localize_script('ulp_main_admin', 'ulp_plugin_url', ULP_URL);
				wp_localize_script('ulp_main_admin', 'ulpKeepData', get_option('ulp_keep_data_after_delete', 0) );
				wp_localize_script('ulp_main_admin', 'ulp_admin_messages', self::messages_for_javascript() );
		}

		wp_enqueue_style('ulp_font_awesome', ULP_URL . 'assets/css/font-awesome.css', [], '3.9' );
		wp_enqueue_script('ulp_main_admin');
		wp_enqueue_script('ulp_sweet_alert', ULP_URL . 'assets/js/sweetalert.js', array('jquery'), '3.9' );
		wp_enqueue_style('ulp_sweet_alert_css', ULP_URL . 'assets/css/sweetalert.css', [], '3.9' );
		if ((isset($_GET['page']) && $_GET['page']=='ultimate_learning_pro') || $is_ulp_type_post || isset($_GET['user_id'])){
				wp_enqueue_media();
				wp_enqueue_style('ulp_bootstrap', ULP_URL . 'assets/css/bootstrap.css', [], '3.9' );
				wp_enqueue_style('ulp_bootstrap_theme', ULP_URL . 'assets/css/bootstrap-theme.css', [], '3.9' );
				wp_enqueue_style('ulp_jquery_ui', ULP_URL . 'assets/css/jquery-ui.min.css', array(), null);
				wp_enqueue_style('ulp_ui_multiselect_css', ULP_URL . 'assets/css/ui.multiselect.css', [], '3.9' );

				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-core');
				wp_enqueue_script('jquery-ui-draggable');
				wp_enqueue_script('jquery-ui-droppable');
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_script('jquery-ui-widget');
				wp_enqueue_script('jquery-ui-autocomplete');
				wp_enqueue_script('jquery-ui-datepicker');

				wp_enqueue_script('ulp_jquery_flot', ULP_URL . 'assets/js/jquery.flot.js', array('jquery'), '3.9' );
				wp_register_script('ulp_ui_multiselect_js', ULP_URL . 'assets/js/ui.multiselect.js', array('jquery'), '3.9' );
				wp_enqueue_script('ulp_shiny_select', ULP_URL . 'assets/js/indeed_shiny_select.js', array('jquery'), '3.9' );
		}
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('ulp_main_admin', ULP_URL . 'assets/css/admin.css', [], '3.9' );
		wp_enqueue_style('ulp_main_public', ULP_URL . 'assets/css/public.min.css', [], '3.9' );
		if (is_rtl()){
				wp_enqueue_style('ulp_main_admin_rtl', ULP_URL . 'assets/css/admin_rtl.css', [], '3.9' );
		}
	}


	public static function messages_for_javascript( $asJson=true ){
				$data = [
						'error' => esc_html__('Error', 'ulp'),
						'limit_char' => esc_html__('is the maximum number of characters for this field!'),
						'email_works' => esc_html__('E-mail works!', 'ulp'),
						'remove_user_from_course' => esc_html__("Are you sure you want to remove this user from course ", 'ulp'),
						'remove_badge_from_user' => esc_html__("Are you sure you want to remove this badge from user?", 'ulp'),
						'remove_certificate_from_user' => esc_html__("Are you sure you want to remove this Certificate from user?", 'ulp'),
						'are_you_sure' => esc_html__("Are you sure?", 'ulp'),
						'delete_it' => esc_html__("Yes, delete it!", 'ulp'),
						'remove_instructor_from_course' => esc_html__('Are you sure you want to remove this instructor from course ', 'ulp'),
						'toggle_section' => esc_html__('Toggle Section', 'ulp'),
						'delete_post' => esc_html__("Are you sure you want to delete this post?", 'ulp'),
				];
				if ( $asJson ){
						return json_encode( $data );
				} else {
						return $data;
				}
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function dashboard(){
		$data = [
				'total_students' => DbUlp::countStudents(),
				'total_instructors' => DbUlp::countInstructors(),
				'total_earnings' => ulp_format_price( DbUlp::getTotalEarnings() ),
				'top_course' => DbUlp::top_course(),
				'student_per_course' => DbUlp::getStudentsCountPerLevel(),
				'last_five_enrolled_students' => DbUlp::getStudents(0, 5, 0),
				'last_five_orders' => DbUlp::ordersGetLast(5),
		];
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/dashboard.php');
		$view->setContentData($data);
		return self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function ulp_course(){
		/// special settings save
		if (isset($_POST['submit']) && isset($_POST['post_id']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				require_once ULP_PATH . 'classes/admin/post_settings_panel/CoursesSettingsPanel.class.php';
				$CoursesSettingsPanel = new CoursesSettingsPanel( sanitize_text_field($_POST['post_id']) );
				unset($CoursesSettingsPanel);
		}

		if ( ( isset( $_GET['action'] ) || isset( $_GET['action2'] ) ) && !empty( $_GET['ID'] ) ){
				require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
				$DbUserEntitiesRelations = new \DbUserEntitiesRelations();
				foreach ( $_GET['ID'] as $key => $postId ){
					$postId = sanitize_text_field($postId);
					$DbUserEntitiesRelations->deleteAllEntriesByEntity( $postId );
					wp_delete_post( $postId, true );
					DbUlp::deleteAllPostMeta($postId);
				}
				unset( $postId );
		}

		$data = array();
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/courses.php');
		$view->setContentData($data);

		/// Submenu
		$submenu [0] = new stdClass;
		$submenu [0]->url = admin_url('edit-tags.php?taxonomy=ulp_course_categories&post_type=ulp_course');
		$submenu [0]->label = esc_html__('Course Categories', 'ulp');

		///output
		return self::customSubmenu($submenu) . self::print_notice() . $view->getOutput();
	}


	public static function payment_services(){
		$data = array();
		$data ['paypal']['active'] = get_option('ulp_paypal_enable') ? 'paypal-active' : '';
		$data ['paypal']['settings'] = get_option('ulp_paypal_email') ? esc_html__('Completed', 'ulp') : esc_html__('Incompleted', 'ulp');
		$temp = DbUlp::getOptionMetaGroup('bt');
		$data ['bt']['active'] = $temp['ulp_bt_enable'] ? 'bt-active' : '';
		$data ['bt']['settings'] = esc_html__('Completed', 'ulp');
		$temp = DbUlp::getOptionMetaGroup('stripe_magic_feat');
		$data ['stripe']['active'] = $temp['ulp_stripe_payment_enable'] ? 'stripe-active' : '';
		$data ['stripe']['settings'] = esc_html__('Completed', 'ulp');
		$temp = DbUlp::getOptionMetaGroup('2checkout_magic_feat');
		$data ['2checkout']['active'] = $temp['ulp_2checkout_payment_enable'] ? 'twocheckout-active' : '';
		$data ['2checkout']['settings'] = esc_html__('Completed', 'ulp');
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/payment_services.php');
		$view->setContentData($data);
		return self::customSubmenu(self::payment_submenu()) . self::print_notice() . $view->getOutput();
	}

	public static function payment_submenu(){
			$submenu [0] = new stdClass;
			$submenu [0]->url = admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_bt');
			$submenu [0]->label = esc_html__('Bank Transfer', 'ulp');
			$submenu [1] = new stdClass;
			$submenu [1]->url = admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_paypal');
			$submenu [1]->label = esc_html__('PayPal', 'ulp');
			$submenu [2] = new stdClass;
			$submenu [2]->url = admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_stripe');
			$submenu [2]->label = esc_html__('Stripe', 'ulp');
			$submenu [3] = new stdClass;
			$submenu [3]->url = admin_url('admin.php?page=ultimate_learning_pro&tab=payment_service_2checkout');
			$submenu [3]->label = esc_html__('2Checkout', 'ulp');
			return $submenu;
	}

	public static function payment_service_bt(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('bt', ulp_sanitize_textarea_array($_POST));
			}
			$data = [
				'metas' => DbUlp::getOptionMetaGroup('bt'),
			];
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/payment_services_bt.php');
			$view->setContentData($data);
			return self::customSubmenu(self::payment_submenu()) . self::print_notice()  . $view->getOutput();
	}

	public static function payment_service_paypal(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('paypal', ulp_sanitize_array($_POST));
			}
			$data = [
				'metas' => DbUlp::getOptionMetaGroup('paypal'),
			];
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/payment_services_paypal.php');
			$view->setContentData($data);
			return self::customSubmenu(self::payment_submenu()) . self::print_notice() . $view->getOutput();
	}


	public static function payment_service_stripe(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('stripe', ulp_sanitize_array($_POST));
			}
			$data = [
				'metas' => DbUlp::getOptionMetaGroup('stripe'),
			];
			$data ['webhook'] = get_site_url();
			if (substr($data ['webhook'], -1) !='/')
					$data ['webhook'] = $data ['webhook'] . '/';
			$data ['webhook'] = $data ['webhook'] . '?ulp_action=stripe';
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/payment_services_stripe.php');
			$view->setContentData($data);
			return self::customSubmenu(self::payment_submenu()) . self::print_notice() . $view->getOutput();
	}

	public static function payment_service_2checkout(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('2checkout', ulp_sanitize_array($_POST));
			}
			$data = [
				'metas' => DbUlp::getOptionMetaGroup('2checkout'),
			];
			$data ['webhook'] = get_site_url();
			if (substr($data ['webhook'], -1) !='/'){
					$data ['webhook'] = $data ['webhook'] . '/';
			}
			$data ['webhook'] = $data ['webhook'] . '?ulp_action=2checkout';
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/payment_services_2checkout.php');
			$view->setContentData($data);
			return self::customSubmenu(self::payment_submenu()) . self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function ulp_certificate(){
		/// special settings save
		if (isset($_POST['submit']) && isset($_POST['post_id']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
			require_once ULP_PATH . 'classes/admin/post_settings_panel/CertificatesSettingsPanel.class.php';
			$CertificatesSettingsPanel = new CertificatesSettingsPanel( sanitize_text_field( $_POST['post_id'] ) );
			unset($CertificatesSettingsPanel);
		}

		$data = array();
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/certificates.php');
		$view->setContentData($data);

		/// Submenu
		$submenu [0] = new stdClass;
		$submenu [0]->url = admin_url('edit-tags.php?taxonomy=ulp_certificate_categories&post_type=ulp_certificate');
		$submenu [0]->label = esc_html__('Certificate Categories', 'ulp');

		///output
		return self::customSubmenu($submenu) . self::print_notice() . $view->getOutput();
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function ulp_lesson(){
		if (isset($_POST['submit']) && isset($_POST['post_id']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
			require_once ULP_PATH . 'classes/admin/post_settings_panel/LessonsSettingsPanel.class.php';
			$LessonsSettingsPanel = new LessonsSettingsPanel( sanitize_text_field($_POST['post_id']) );
			unset($LessonsSettingsPanel);
		}
		if ( ( isset( $_GET['action'] ) || isset( $_GET['action2'] ) ) && !empty( $_GET['ID'] ) ){
				foreach ( $_GET['ID'] as $key => $postId ){
					wp_delete_post( $postId, true );
					DbUlp::deleteAllPostMeta($postId);
				}
				unset( $postId );
		}
		$data = array();
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/lessons.php');
		$view->setContentData($data);

		/// Submenu
		$submenu [0] = new stdClass;
		$submenu [0]->url = admin_url('edit-tags.php?taxonomy=ulp_lesson_categories&post_type=ulp_lesson');
		$submenu [0]->label = esc_html__('Lesson Categories', 'ulp');

		///output
		return self::customSubmenu($submenu) . self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function ulp_quiz(){
		if (isset($_POST['submit']) && isset($_POST['post_id']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				require_once ULP_PATH . 'classes/admin/post_settings_panel/QuizesSettingsPanel.class.php';
				$QuizesSettingsPanel = new QuizesSettingsPanel( sanitize_text_field($_POST['post_id']) );
				unset($QuizesSettingsPanel);
		}

		if ( ( isset( $_GET['action'] ) || isset( $_GET['action2'] ) ) && !empty( $_GET['ID'] ) ){
				foreach ( $_GET['ID'] as $key => $postId ){
					wp_delete_post( sanitize_text_field($postId), true );
					\DbUlp::deleteAllPostMeta($postId);
				}
				unset( $postId );
		}

		$data = array();
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/quizes.php');
		$view->setContentData($data);
		/// Submenu
		$submenu [0] = new stdClass;
		$submenu [0]->url = admin_url('edit-tags.php?taxonomy=ulp_quiz_categories&post_type=ulp_quiz');
		$submenu [0]->label = esc_html__('Quiz Categories', 'ulp');

		///output
		return self::customSubmenu($submenu) . self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function ulp_question(){
		if (isset($_POST['submit']) && isset($_POST['post_id']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				require_once ULP_PATH . 'classes/admin/post_settings_panel/QuestionsSettingsPanel.class.php';
				$QuestionsSettingsPanel = new QuestionsSettingsPanel( sanitize_text_field($_POST['post_id']) );
				unset($QuestionsSettingsPanel);
		}

		if ( ( isset( $_GET['action'] ) || isset( $_GET['action2'] ) ) && !empty( $_GET['ID'] ) ){
				foreach ( $_GET['ID'] as $key => $postId ){
					wp_delete_post( sanitize_text_field($postId), true );
					DbUlp::deleteAllPostMeta( sanitize_text_field($postId) );
				}
				unset( $postId );
		}

		$data = array();
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/questions.php');
		$view->setContentData($data);
		/// Submenu
		$submenu [0] = new stdClass;
		$submenu [0]->url = admin_url('edit-tags.php?taxonomy=ulp_question_categories&post_type=ulp_question');
		$submenu [0]->label = esc_html__('Question Categories', 'ulp');

		///output
		return self::customSubmenu($submenu) . self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function students(){
		/// RESET DASHBOARD NOTIFICATION
		require_once ULP_PATH . 'classes/Db/Db_Ulp_Dashboard_Notifications.class.php';
		$Db_Ulp_Dashboard_Notifications = new Db_Ulp_Dashboard_Notifications();
		$Db_Ulp_Dashboard_Notifications->save('new_students', 0);

		require_once ULP_PATH . 'classes/IndeedPagination.class.php';
		require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
		require_once ULP_PATH . 'classes/Db/Db_Ulp_Student_Badges.class.php';

		$current_page = isset($_GET['ulp_page']) ? sanitize_text_field($_GET['ulp_page']) : 1;
		$course_id = isset($_GET['list_students_by_course_id']) ? sanitize_text_field($_GET['list_students_by_course_id']) : 0;

		$search_str = isset($_REQUEST['search_q']) ? sanitize_text_field($_REQUEST['search_q']) : '';

		$total_items = DbUlp::countStudents($course_id, $search_str);
		$current_url = remove_query_arg('ulp_page', ULP_CURRENT_URI);
		if ($search_str){
			$current_url = remove_query_arg('ulp_page', $current_url);
			$current_url = add_query_arg('search_q', sanitize_text_field($_REQUEST['search_q']), $current_url);
		}

		$pagination = new IndeedPagination(array(
				'base_url' => $current_url,
				'param_name' => 'ulp_page',
				'total_items' => $total_items,
				'items_per_page' => self::$per_page,
				'current_page' => $current_page,
		));
		$data['pagination'] = $pagination->output();

		$data['users_course_object'] = new UsersCoursesActionsUlp();

		$limit = self::$per_page;
		if ($current_page>1){
			$offset = ( $current_page - 1 ) * self::$per_page;
		} else {
			$offset = 0;
		}
		if ($offset + $limit>$total_items){
			$limit = $total_items - $offset;
		}
		$data['students'] = DbUlp::getStudents($course_id, $limit, $offset, $search_str);

		$data ['show_badges'] = get_option('ulp_student_badges_enable');
		if ($data['students']){
				if ( $data ['show_badges']){
						$Db_Ulp_Student_Badges = new Db_Ulp_Student_Badges();
				}
				foreach ($data['students'] as $key => $object){
						$data['students'][$key]->avatar = DbUlp::getAuthorImage($object->user_id);
						if ( $data ['show_badges']){
								$data['students'][$key]->badges = $Db_Ulp_Student_Badges->getAllForUser($object->user_id);
						}
				}
		}
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/students.php');
		$view->setContentData($data);
		return self::print_notice() . $view->getOutput();
	}


		public static function instructors(){
				require_once ULP_PATH . 'classes/IndeedPagination.class.php';
				$current_page = isset($_GET['ulp_page']) ? sanitize_text_field($_GET['ulp_page']) : 1;
				$search_str = isset($_REQUEST['search_q']) ? sanitize_text_field($_REQUEST['search_q']) : '';

				$total_items = DbUlp::countInstructors($search_str);
				$current_url = remove_query_arg('ulp_page', ULP_CURRENT_URI);
				if ($search_str){
					$current_url = remove_query_arg('search_q', $current_url);
					$current_url = add_query_arg('search_q', $_REQUEST['search_q'], $current_url);
				}

				$pagination = new IndeedPagination(array(
						'base_url' => $current_url,
						'param_name' => 'ulp_page',
						'total_items' => $total_items,
						'items_per_page' => self::$per_page,
						'current_page' => $current_page,
				));
				$data['pagination'] = $pagination->output();

				$limit = self::$per_page;
				if ($current_page>1){
					$offset = ( $current_page - 1 ) * self::$per_page;
				} else {
					$offset = 0;
				}
				if ($offset + $limit>$total_items){
					$limit = $total_items - $offset;
				}

				$data['instructors'] = DbUlp::getAllInstructors(self::$per_page, $offset, $search_str);
				if ($data['instructors']){
						foreach ($data['instructors'] as $k => $object){
								$t1 = DbUlp::get_courses_for_instructor($object->uid);
								$t2 = DbUlp::getCoursesForAdditionalInstructor($object->uid);
								if ($t1===FALSE){
									 $t1 = array();
								}
								if ($t2===FALSE){
									 $t2 = array();
								}
								$data ['instructors'][$k]->courses = array_merge($t1, $t2);
								$data ['instructors'][$k]->avatar = DbUlp::getAuthorImage($object->uid);

								$data ['instructors'][$k]->roles = DbUlp::get_user_roles($object->uid);

						}
				}
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/admin/instructors.php');
				$view->setContentData($data);
				return self::print_notice() . $view->getOutput();
		}


	/**
	 * @param none
	 * @return none
	 */
	public static function showcases(){
		$data = array();
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/showcases.php');
		$view->setContentData($data);
		return self::print_notice() . $view->getOutput();
	}


	public static function magic_feature_items(){
			$data = array(

					'woocommerce_payment' => array(
								'label' => 'Woo Payment Integration',
								'description' => 'Users can pay courses with Woocommerce',
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_woocommerce_payment_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=woocommerce_payment') : '#',
					),

					'ump_payment' => array(
								'label' => 'UMP Payment Integration',
								'description' => 'Users can pay courses with UMP',
								'disabled' => !(get_option('ulp_ump_payment_enable')),
								'link' => admin_url('admin.php?page=ultimate_learning_pro&tab=ump_payment'),
					),

					'edd_payment' => [
								'label' => 'Easy Download Digital - Payment Integration',
								'description' => '',
								'disabled' => !(get_option('ulp_edd_payment_enable')),
								'link' => admin_url('admin.php?page=ultimate_learning_pro&tab=edd_payment'),
					],

					'paypal_magic_feat' => [
								'label' => esc_html__('PayPal Payment Integration', 'ulp'),
								'description' => esc_html__('Users can pay courses with PayPal', 'ulp'),
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_paypal_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=paypal_magic_feat') : '#',
					],


					'stripe_magic_feat' => array(
								'label' => 'Stripe Payment Integration',
								'description' => 'Users can pay courses with Stripe',
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_stripe_payment_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=stripe_magic_feat') : '#',
					),

					'twocheckout_magic_feat' => array(
								'label' => '2Checkout Payment Integration',
								'description' => 'Users can pay courses with 2Checkout',
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_2checkout_payment_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=twocheckout_magic_feat') : '#',
					),

					'multiple_instructors' => array(
								'label' => esc_html__('Multiple instructors', 'ulp'),
								'description' => esc_html__('Set multiple instructors for a course', 'ulp'),
								'disabled' => !(get_option('ulp_multiple_instructors_enable')),
								'link' => admin_url('admin.php?page=ultimate_learning_pro&tab=multiple_instructors'),
					),

					'ulp_course_review' => array(  // course_reviews
								'label' => esc_html__('Course Reviews', 'ulp'),
								'description' => esc_html__('Let students write a review about your courses', 'ulp'),
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_course_reviews_enabled')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_course_review') : '#',
					),

					'lesson_drip_content' => array(
								'label' => esc_html__('Lesson Drip Content', 'ulp'),
								'description' => esc_html__('Decide when a lesson becomes available', 'ulp'),
								'disabled' => ULP_LICENSE_SET ? !(get_option('lesson_drip_content_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=lesson_drip_content') : '#',
					),

					'notes' => [
								'label' => esc_html__('Student Notes', 'ulp'),
								'description' => esc_html__('Students can take notes', 'ulp'),
								'disabled' => !(get_option('lesson_notes_enable')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=notes') : '#',
					],

					'ulp_student_badges' => [
								'label' => esc_html__('Student Badges', 'ulp'),
								'description' => esc_html__('Your students can earn badges by accomplishing certain conditions', 'ulp'),
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_student_badges_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_student_badges') : '#',
					],

					'ulp_certificates_magic_feat' => [
								'label' => esc_html__('Course Certificates', 'ulp'),
								'description' => esc_html__('Give certificates to students.', 'ulp'),
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_certificates_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_certificates_magic_feat') : '#',
					],

					'grade_book' => [
								'label' => esc_html__('Grade Book', 'ulp'),
								'description' => esc_html__('Students can see their grades', 'ulp'),
								'disabled' => !(get_option('ulp_gradebook_enable')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=grade_book') : '#',
					],

					'watch_list' => [
								'label' => esc_html__('Wish List', 'ulp'),
								'description' => esc_html__('Student can save courses in a special place to enroll later', 'ulp'),
								'disabled' => !(get_option('ulp_watch_list_enable')),
								'link' => admin_url('admin.php?page=ultimate_learning_pro&tab=watch_list'),
					],

					'mycred' => [
								'label' => esc_html__('MyCred', 'ulp'),
								'description' => esc_html__('Students can earn MyCred points', 'ulp'),
								'disabled' => !(get_option('ulp_mycred_enable')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=mycred') : '#',
					],

					'invoices' => [
								'label' => esc_html__('Invoices', 'ulp'),
								'description' => esc_html__(' Activate this feature to give your students access to their invoice(s)'),
								'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_invoices_enable')) : TRUE,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=invoices') : '#',
					],

					'buddypress' => [
								'label' => esc_html__('BuddyPress integration', 'ulp'),
								'description' => esc_html__('Add a new tab in your BuddyPress Public Profile', 'ulp'),
								'disabled' => !(get_option('ulp_buddypress_integration_enable')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=buddypress') : '#',
					],
					'custom_currencies' => [
													'label' => esc_html__('Custom currencies', 'ulp'),
													'description' => esc_html__('Add new currencies (with custom symbols) alongside the predefined list', 'ulp'),
													'disabled' => false,
													'link' => admin_url('admin.php?page=ultimate_learning_pro&tab=custom_currencies'),
					],

					'pushover' => [
													'label' => 'Pushover notifications',
													'description' => esc_html__('Users receive notifications on mobile via Pushover', 'ulp'),
													'disabled' => !(get_option('ulp_pushover_enable')),
													'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=pushover') : '#',
					],

					'course_difficulty' => [
													'label' => esc_html__('Course Difficulty', 'ulp'),
													'description' => esc_html__('Define types of course difficulty', 'ulp'),
													'disabled' => ULP_LICENSE_SET ? !(get_option('ulp_course_difficulty_enable')) : TRUE,
													'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=course_difficulty') : '#',
					],

					'course_time_period' => [
													'label' => esc_html__('Course Estimation Time', 'ulp'),
													'description' => esc_html__('Course will have a recommended Estimation Time', 'ulp'),
													'disabled' => !(get_option('ulp_course_time_period_enable')),
													'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=course_time_period') : '#',
					],

					'student_account_custom_tabs' => [
													'label' => esc_html__('Account custom tabs', 'ulp'),
													'description' => esc_html__('Create and reorder account page menu items', 'ulp'),
													'disabled' => !(get_option('ulp_student_account_custom_tabs_enabled')),
													'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=student_account_custom_tabs') : '#',
					],

					'about_the_instructor' => [
													'label' => esc_html__('About the Instructor', 'ulp'),
													'description' => esc_html__('Provide a shortcode that display current instructor details.', 'ulp'),
													'disabled' => !(get_option('ulp_about_the_instructor_mf')),
													'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=about_the_instructor') : '#',
					],

					'students_also_bought' => [
								'label' => esc_html__('Students also Bought Box', 'ulp'),
								'description' => esc_html__('Display courses that has been bought by the current Students.', 'ulp'),
								'disabled' => !(get_option('ulp_student_also_bought_enable')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=students_also_bought') : '#',
					],

					'courses_tags' => [
								'label' => esc_html__('Courses Tags', 'ulp'),
								'description' => esc_html__('Append custom tags for your courses.', 'ulp'),
								'disabled' => false,
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=courses_tags') : '#',
					],

					'more_courses_by' => [
								'label' => esc_html__('More Courses Box', 'ulp'),
								'description' => esc_html__('Display a list of courses submitted by current main Instructor.', 'ulp'),
								'disabled' => !(get_option('ulp_more_courses_by_enabled')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=more_courses_by') : '#',
					],

					'coming_soon' => [
								'label' => esc_html__('Coming Soon Course', 'ulp'),
								'description' => 'Enable Count Down for a new Course',
								'disabled' => !(get_option('ulp_coming_soon_enabled')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=coming_soon') : '#',
					],

					'announcements' => [
								'label' => esc_html__('Announcements', 'ulp'),
								'description' => 'Especially for promoting your courses and update students about new content or features',
								'disabled' => !(get_option('ulp_announcements_enabled')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=announcements') : '#',
					],
					'qanda' => [
								'label' => esc_html__('Q&A', 'ulp'),
								'description' => 'Students can submit new questions to Author/Instructor for each course or search for previous questions',
								'disabled' => !(get_option('ulp_qanda_enabled')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=qanda') : '#',
					],
					'frontendcourse' => [
								'label' => esc_html__('Front-End Course Creation', 'ulp'),
								'description' => 'Course Authors can create their courses from the front-end of your website',
								'disabled' => !(get_option('ulp_frontendcourse_enabled')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=frontendcourse') : '#',
					],
					'curriculum_slider' => [
								'label' => esc_html__('Curriculum Slider', 'ulp'),
								'description' => esc_html__( 'Curriculum slider' , 'ulp'),
								'disabled' => !(get_option('ulp_curriculum_slider_enabled')),
								'link' => ULP_LICENSE_SET ? admin_url('admin.php?page=ultimate_learning_pro&tab=curriculum_slider') : '#',
					],
			);
			$data = apply_filters( 'ulp_magic_feature_list', $data );
			return $data;
	}

	public static function buddypress(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('buddypress', ulp_sanitize_array( $_POST ) );
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('buddypress'),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/buddypress.php');
		$view->setContentData($data);
		return self::print_notice() . $view->getOutput();
	}

	public static function students_also_bought()
	{
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('students_also_bought', ulp_sanitize_array($_POST) );
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('students_also_bought'),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/students_also_bought.php');
		$view->setContentData($data);
		return self::print_notice() . $view->getOutput();
	}

	public static function courses_tags()
	{
			$DbCourseTags = new \Indeed\Ulp\Db\DbCourseTags();
			if (!empty($_POST['save_tag']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					$made = $DbCourseTags->save( ulp_sanitize_array($_POST) );
					if (!$made){
							$data['error_on_save'] = true;
					}
			}
			$data = [
					'items' => $DbCourseTags->getAll(),
			];
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/courses_tags.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function courses_tags_add_edit()
	{
			$termId = isset($_GET['term_id']) ? sanitize_text_field($_GET['term_id']) : 0;
			$DbCourseTags = new \Indeed\Ulp\Db\DbCourseTags();
			$data = [
					'object' => $DbCourseTags->getOne($termId, true)
			];
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/courses_tags_add_edit.php');
			$view->setContentData($data, true);
			return self::print_notice() . $view->getOutput();
	}

	public static function qanda()
	{
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup( 'qanda', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('qanda'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/qanda_magic_feat.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function more_courses_by()
	{
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('more_courses_by', ulp_sanitize_array($_POST ));
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('more_courses_by'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/more_courses_by.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function coming_soon()
	{
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('coming_soon', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('coming_soon'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/coming_soon.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function announcements()
	{
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('announcements', ulp_sanitize_textarea_array($_POST ));
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('announcements'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/announcements.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function ulp_announcement()
	{
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/ulp_announcement.php');
			$view->setContentData([]);
			return self::print_notice() . $view->getOutput();
	}

	public static function ulp_qanda()
	{
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/ulp_qanda.php');
			$view->setContentData([]);
			return self::print_notice() . $view->getOutput();
	}

	public static function frontendcourse()
	{
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('frontendcourse', ulp_sanitize_array($_POST ));
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('frontendcourse'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/frontendcourse.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function curriculum_slider()
	{
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('curriculum_slider', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('curriculum_slider'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/curriculum_slider.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}


	public static function bbpress(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('bbpress', ulp_sanitize_array($_POST ));
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('bbpress'),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/bbpress.php');
		$view->setContentData($data);
		return self::print_notice() .  $view->getOutput();
	}

	public static function mycred(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('mycred', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('mycred'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/mycred.php');
			$view->setContentData($data);
			return  self::print_notice() . $view->getOutput();
	}

	public static function student_account_custom_tabs(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('ulp_student_account_custom_tabs', ulp_sanitize_array($_POST) );
					if (!empty($_POST['slug'])){
							DbUlp::account_page_menu_save_custom_item( ulp_sanitize_array($_POST) );
					}
			}
			if (isset($_GET['delete'])){
					DbUlp::account_page_menu_delete_custom_item( ulp_sanitize_array($_GET['delete']) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('ulp_student_account_custom_tabs'),
					'font_awesome' => DbUlp::get_font_awesome_codes(),
					'menu_items' => DbUlp::account_page_get_tabs(),
					'standard_tabs' => DbUlp::account_page_get_tabs(TRUE),
			);

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/student_account_custom_tabs.php');
			$view->setContentData($data);
			return  self::print_notice() . $view->getOutput();
	}

	public static function about_the_instructor()
	{
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('ulp_about_instructor', ulp_sanitize_array($_POST) );
				if (!empty($_POST['slug'])){
						DbUlp::account_page_menu_save_custom_item( ulp_sanitize_array($_POST) );
				}
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('ulp_about_instructor'),
		);

		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/about_the_instructor.php');
		$view->setContentData($data);
		return  self::print_notice() . $view->getOutput();
	}

	public static function account_page(){
			if (isset($_POST['ulp_save']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('showcases_account_page', ulp_sanitize_textarea_array($_POST ));
					DbUlp::account_page_save_tabs_details( ulp_sanitize_textarea_array($_POST) );
			}
			$data = [
					'metas' => DbUlp::getOptionMetaGroup('showcases_account_page'),
					'constants' => DbUlp::user_constants(),
					'themes' => [
						'ulp-ap-top-theme-2' => esc_html__('Square Top Image Theme', 'ulp'),
						'ulp-ap-top-theme-3' => esc_html__('Rounded Big Image Theme', 'ulp'),
					],
					'available_tabs' => DbUlp::account_page_get_tabs(),
					'menu_items' => DbUlp::account_page_get_tabs(),
					'font_awesome' => DbUlp::get_font_awesome_codes(),
			];

			if (isset($data ['available_tabs']['list_certificates']) && !get_option('ulp_certificates_enable')){
					unset($data ['available_tabs']['list_certificates']);
			}

			$temp = DbUlp::account_page_get_tabs_details();
			if ($temp){
				$data ['metas'] = array_merge($data ['metas'], DbUlp::account_page_get_tabs_details());
			}

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/showcases_account_page.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}

	public static function student_leaderboard(){
			$data = array(
					'metas' => DbUlp::students_grid_default_shortcode_attributes()
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/student_leaderboard.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}

	public static function courses_grid(){
			$data = array(
					'metas' => DbUlp::courses_grid_default_shortcode_attributes(),
					'cats' => DbUlp::get_all_course_cats(),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/courses_grid.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}

	public static function paypal_magic_feat(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('paypal_magic_feat', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('paypal_magic_feat'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/paypal_magic_feat.php');
			$view->setContentData($data);
			return  self::print_notice() . $view->getOutput();
	}

	public static function invoices(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('invoices', ulp_sanitize_array($_POST) );
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('invoices'),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/magic_feat_invoices.php');
		$view->setContentData($data);
		return   $view->getOutput();
	}

	public static function grade_book(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('gradebook', ulp_sanitize_array($_POST) );
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('gradebook'),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/gradebook.php');
		$view->setContentData($data);
		return self::print_notice() .  $view->getOutput();
	}

	public static function woocommerce_payment(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('woocommerce_payment', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('woocommerce_payment'),
					'items' => DbUlp::get_woo_product_course_relations(),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/woocommerce_payment.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}

	public static function edd_payment(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('edd_payment', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('edd_payment'),
					'items' => DbUlp::get_edd_product_course_relations(),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/edd_payment.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}

	public static function ulp_student_badges(){
			$data = array();

			require_once ULP_PATH . 'classes/Db/Db_Ulp_Badges.class.php';
			$Db_Ulp_Badges = new Db_Ulp_Badges();

			if (!empty($_POST['save_badge'])){
					if (sanitize_text_field($_POST['badge_type'])=='static'){
							$arr = ['rule_type' => ulp_sanitize_array($_POST['rule_types_static']), 'rule_value' => ulp_sanitize_array($_POST['rule_value']) ];
					} else {
							$arr = ['rule_type' => ulp_sanitize_array($_POST['rule_types_tier']), 'rule_value' => ulp_sanitize_array($_POST['rule_value']) ];
					}
					$_POST ['rule'] = json_encode($arr);
					$Db_Ulp_Badges->save( ulp_sanitize_array($_POST));
			}

			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('ulp_student_badges', ulp_sanitize_array($_POST) );
			}
			$data ['metas'] = DbUlp::getOptionMetaGroup('ulp_student_badges');
			$data ['items'] = $Db_Ulp_Badges->selectAll();

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/student_badges.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}

	public static function student_badges(){
				require_once ULP_PATH . 'classes/Db/Db_Ulp_Badges.class.php';
				$Db_Ulp_Badges = new Db_Ulp_Badges();

				$data = array();
				$data ['id'] = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : 0;
				$data ['item'] = $Db_Ulp_Badges->getById($data ['id']);
				if ($data ['item']['rule']){
					$temp = json_decode($data ['item']['rule'], TRUE);
					$data ['item']['rule_type'] = $temp['rule_type'];
					$data ['item']['rule_value'] = $temp['rule_value'];
				} else {
					$data ['item']['rule_type'] = '';
					$data ['item']['rule_value'] = '';
				}

				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/admin/student_badges_add_edit.php');
				$view->setContentData($data);
				return self::print_notice() .  $view->getOutput();
	}

	public static function ulp_certificates_magic_feat(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('ulp_certificates', ulp_sanitize_textarea_array($_POST) );
				if (!empty($_POST['cid']) && $_POST['cid']>-1 && !empty( $_POST['username'] )){

						/// give certificate to this user
						$uid = DbUlp::getUidByUsername(sanitize_text_field($_POST['username']));
						if ($uid){
								require_once ULP_PATH . 'classes/Db/Db_User_Certificates.class.php';
								$Db_User_Certificates = new Db_User_Certificates();
								$certificate_id = DbUlp::getCertificateForCourse( ulp_sanitize_array($_POST['cid']) );

								if ($certificate_id){
										require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelationMetas.class.php';
										require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
										$DbUserEntitiesRelations = new DbUserEntitiesRelations();
										$user_entity_relation_id = $DbUserEntitiesRelations->getRelationId($uid, ulp_sanitize_array($_POST['cid']) );
										$DbUserEntitiesRelationMetas = new DbUserEntitiesRelationMetas();
										$grade = $DbUserEntitiesRelationMetas->getMeta($user_entity_relation_id, 'course_grade');

										$details = '';
										$Db_User_Certificates->addCertificateForUser($uid, sanitize_text_field($_POST['cid']), $certificate_id, $grade, $details);
								}
						}
				}
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('ulp_certificates'),
				'courses' => DbUlp::getAllCourses(),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/certificates_magic_feat.php');
		$view->setContentData($data);
		return self::print_notice() .  $view->getOutput();
	}


	public static function notes(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				\DbUlp::updateOptionMetaGroup('notes', ulp_sanitize_array($_POST) );
				if ( empty($_POST['lesson_notes_enable']) ){
						// deactivate note tab
						\DbUlp::deactivateApTab( 'notes' );
				} else {
						// activate note tab
						\DbUlp::activateApTab( 'notes' );
				}
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('notes'),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/notes.php');
		$view->setContentData($data);
		return self::print_notice() .  $view->getOutput();
	}

	public static function lesson_drip_content(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('lesson_drip_content', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('lesson_drip_content'),
					'items' => DbUlp::get_woo_product_course_relations(),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/lesson_drip_content_settings.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}


	public static function ulp_course_review(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('course_reviews', ulp_sanitize_array($_POST) );
			} else if ((isset($_GET['action']) && $_GET['action']=='bulk_publish') || isset($_GET['action2']) && $_GET['action2']=='bulk_publish'){
					DbUlp::updatePostStatus( sanitize_text_field($_GET['ID']) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('course_reviews'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/course_reviews.php');
			$view->setContentData($data);
			return  self::print_notice() .  $view->getOutput();
	}


	public static function multiple_instructors(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('multiple_instructors', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('multiple_instructors')
			);

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/multiple_instructors.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}


	public static function ump_payment(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('ump_payment', ulp_sanitize_array($_POST) );
		}
		$data = array(
				'metas' => DbUlp::getOptionMetaGroup('ump_payment'),
				'items' => DbUlp::get_ump_levels_course_relations(),
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/ump_payment.php');
		$view->setContentData($data);
		return self::print_notice() .  $view->getOutput();
	}


	public static function stripe_magic_feat(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('stripe_magic_feat', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('stripe_magic_feat'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/stripe_magic_feat.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function twocheckout_magic_feat(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('2checkout_magic_feat', ulp_sanitize_array($_POST) );
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('2checkout_magic_feat'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/2checkout_magic_feat.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}


		public static function watch_list(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('watch_list', ulp_sanitize_array($_POST) );
					if ( empty($_POST['ulp_watch_list_enable']) ){
							// deactivate note tab
							\DbUlp::deactivateApTab( 'wish_list' );
					} else {
							// activate note tab
							\DbUlp::activateApTab( 'wish_list' );
					}
			}
			$data = array(
					'metas' => DbUlp::getOptionMetaGroup('watch_list'),
					'items' => DbUlp::get_ump_levels_course_relations(),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/watch_list.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
		}

	/**
	 * @param none
	 * @return none
	 */
	public static function magic_feat(){
		$data = array(
				'magic_feat' => self::magic_feature_items()
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/magic_feat.php');
		$view->setContentData($data);
		return self::print_notice() .  $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function notifications(){
			require_once ULP_PATH . 'classes/Db/DbNotificationsUlp.class.php';
			$notification = new DbNotificationsUlp();

			/// save notification
			if (!empty($_POST['type']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					$data['success'] = $notification->save( ulp_sanitize_textarea_array($_POST) );
			} else if (!empty($_POST['delete_notification']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					$notification->delete( ulp_sanitize_array($_POST['delete_notification']) );
			}

			require_once ULP_PATH . 'classes/Db/DbNotificationsUlp.class.php';
			$DbNotificationsUlp = new DbNotificationsUlp();
			$data['items'] = $DbNotificationsUlp->getAll();
			$data['action_types']['admin'] = $notification->getActionTypes('admin');
			$data['action_types']['student'] = $notification->getActionTypes('student');
			$data['action_types']['announcements'] = $notification->getActionTypes('announcements');
			$data['action_types']['qanda'] = $notification->getActionTypes('qanda');
			$data['action_types']['others'] = $notification->getActionTypes('others');

			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_learning_pro&tab=notifications_add_edit');
			$data['pushover'] = get_option('ulp_pushover_enable');
			if ($data['items']){
					foreach ($data['items'] as $id => $item){
							if ($item['course_id']>-1)
									$data['items'][$id]['course_label'] = DbUlp::getPostTitleByPostId($item['course_id']);
							else
									$data['items'][$id]['course_label'] = esc_html__('All', 'ulp');
					}
			}

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/notifications.php');
			$view->setContentData($data);
			return  self::print_notice() . $view->getOutput();
	}


	public static function ulp_order(){
			wp_enqueue_script('ulp_printThis', ULP_URL . 'assets/js/printThis.js', array('jquery'), '3.9' );
			require_once ULP_PATH . 'classes/Db/Db_Ulp_Dashboard_Notifications.class.php';
			$Db_Ulp_Dashboard_Notifications = new Db_Ulp_Dashboard_Notifications();
			$Db_Ulp_Dashboard_Notifications->save('new_orders', 0);

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/orders.php');
			$view->setContentData(array());
			return self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function notifications_add_edit(){
			require_once ULP_PATH . 'classes/Db/DbNotificationsUlp.class.php';
			$notification = new DbNotificationsUlp();
			$data = array();
			$id = empty($_GET['id']) ? 0 : sanitize_text_field($_GET['id']);
			$data['action_types']['admin'] = $notification->getActionTypes('admin');
			$data['action_types']['student'] = $notification->getActionTypes('student');
			$data['action_types']['announcements'] = $notification->getActionTypes('announcements');
			$data['action_types']['qanda'] = $notification->getActionTypes('qanda');
			$data['action_types']['others'] = $notification->getActionTypes('others');
			$data['metas'] = $notification->getNotificationById($id);
			$data['courses'] = DbUlp::getAllCourses();
			$data['all_courses_list_labels'] = '';
			if ($data['courses']){
					foreach ($data['courses'] as $k=>$array){
							$all_labels[] = $array['post_title'];
					}
					$data['all_courses_list_labels'] = implode(', ', $all_labels);
			}
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/notifications_add_edit.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}


	public static function view_student_activity(){
		$data = array(
								'username' => esc_html__('Unknown user', 'ulp'),
								'userdata' => array(),
		);
		if (isset($_GET['uid'])){
				require_once ULP_PATH . 'classes/Db/DbActivityUlp.class.php';
				$DbActivityUlp = new DbActivityUlp();
				$total_items = $DbActivityUlp->getCountAll(sanitize_text_field($_GET['uid']));
				$current_page = isset($_GET['ulp_page']) ? sanitize_text_field($_GET['ulp_page']) : 1;
				$current_url = remove_query_arg('ulp_page', ULP_CURRENT_URI);

				require_once ULP_PATH . 'classes/IndeedPagination.class.php';
				$pagination = new IndeedPagination(array(
						'base_url' => $current_url,
						'param_name' => 'ulp_page',
						'total_items' => $total_items,
						'items_per_page' => self::$per_page,
						'current_page' => $current_page,
				));
				$data['pagination'] = $pagination->output();

				$limit = self::$per_page;
				if ($current_page>1){
					$offset = ( $current_page - 1 ) * self::$per_page;
				} else {
					$offset = 0;
				}
				if ($offset + $limit>$total_items){
					$limit = $total_items - $offset;
				}

				$data ['username'] = DbUlp::getUsernameByUID(sanitize_text_field($_GET['uid']));
				$data ['userdata'] = $DbActivityUlp->getAllByUid(sanitize_text_field($_GET['uid']), $limit, $offset);
				$data ['possible_actions'] = DbUlp::friendly_activity_actions();
		}
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/students_view_activity.php');
		$view->setContentData($data);
		return  self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function settings(){
		$subtab = isset($_GET['subtab']) ? sanitize_text_field($_GET['subtab']) : 'general_settings';
		return self::$subtab();
	}


	public static function general_settings(){
		if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
				DbUlp::updateOptionMetaGroup('general_settings', ulp_sanitize_array( $_POST ) );
		}
		$data['metas'] = DbUlp::getOptionMetaGroup('general_settings');
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/general_settings.php');
		$view->setContentData($data);
		return  self::general_options_subtab(). self::print_notice()  . $view->getOutput();
	}


	public static function default_pages(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('default_pages', ulp_sanitize_array($_POST) );
			}
			$data = array(
											'pages' => indeed_get_all_pages(),
											'metas' => DbUlp::getOptionMetaGroup('default_pages'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/default_pages.php');
			$view->setContentData($data);
			return  self::general_options_subtab() . self::print_notice() . $view->getOutput();
	}

	public static function redirects(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('redirects', ulp_sanitize_array($_POST) );
			}
			$data = array(
											'pages' => indeed_get_all_pages(),
											'metas' => DbUlp::getOptionMetaGroup('redirects'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/redirects.php');
			$view->setContentData($data);
			return self::general_options_subtab() . self::print_notice()  . $view->getOutput();
	}

	public static function general_options_subtab(){
			$data['subtabs'] = array(
					'general_settings' => esc_html__('General Settings', 'ulp'),
					'admin_workflow' => esc_html__('Admin Workflow', 'ulp'),
					'public_workflow' => esc_html__('Public Workflow', 'ulp'),
					'default_pages' => esc_html__('Default Pages', 'ulp'),
					'redirects' => esc_html__('Redirects', 'ulp'),
					'payment_settings' => esc_html__('Payments', 'ulp'),
					'access' => esc_html__('Access', 'ulp'),
					'notification_settings' => esc_html__('Notifications', 'ulp'),
					'messages' => esc_html__('Messages', 'ulp'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/general_options-submenu.php');
			$view->setContentData($data);
			return $view->getOutput();
	}

	public static function customSubmenu($items=array()){
			$data = new StdClass;
			$data->items = $items;
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/custom_submenu.php');
			$view->setContentData($data);
			return $view->getOutput();
	}

		public static function payment_settings(){
				if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
						DbUlp::updateOptionMetaGroup('payment_settings', ulp_sanitize_array( $_POST) );
				}
				require_once ULP_PATH . 'classes/Db/Db_Custom_Currencies.class.php';
				$Db_Custom_Currencies = new Db_Custom_Currencies();
				$data = array(
						'currencies' => DbUlp::get_currencies_list('all'),
						'metas' => DbUlp::getOptionMetaGroup('payment_settings'),
						'custom_currencies' => $Db_Custom_Currencies->getAll(),
				);
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/admin/payment_settings.php');
				$view->setContentData($data);
				return self::general_options_subtab() . self::print_notice()  . $view->getOutput();
		}

				public static function admin_workflow(){
						$data = array();
						if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
								DbUlp::updateOptionMetaGroup('admin_workflow', ulp_sanitize_array($_POST) );
						}
						$data = array(
														'metas' => DbUlp::getOptionMetaGroup('admin_workflow'),
						);
						$view = new ViewUlp();
						$view->setTemplate(ULP_PATH . 'views/admin/admin_workflow.php');
						$view->setContentData($data);
						return self::general_options_subtab() . self::print_notice() . $view->getOutput();
				}

				public static function public_workflow(){
						$data = array();
						if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
								if ($_POST['ulp_course_custom_query_var']=='ulp_course'){
										unset($_POST['ulp_course_custom_query_var']);
										$data['error'] = esc_html__("Course slug cannot be named 'ulp_course'! Please choose another name!", 'ulp');
								}
								DbUlp::updateOptionMetaGroup('public_workflow', ulp_sanitize_array($_POST) );
								update_option('ulp_do_flush_rewrite_on_init', true);
						}
						$data['metas'] =  DbUlp::getOptionMetaGroup('public_workflow');
						$view = new ViewUlp();
						$view->setTemplate(ULP_PATH . 'views/admin/public_workflow.php');
						$view->setContentData($data);
						return self::general_options_subtab() . self::print_notice() . $view->getOutput();
				}

				public static function access(){
						if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
								DbUlp::updateOptionMetaGroup('access', ulp_sanitize_array($_POST) );
						}
						$data = array(
								'roles' => get_editable_roles(),
						);
						$data['metas'] = DbUlp::getOptionMetaGroup('access');
						$data['metas']['ulp_dashboard_allowed_roles_as_array'] = (empty($data['metas']['ulp_dashboard_allowed_roles'])) ? array() : explode(',', $data['metas']['ulp_dashboard_allowed_roles']);
						$view = new ViewUlp();
						$view->setTemplate(ULP_PATH . 'views/admin/access.php');
						$view->setContentData($data);
						return self::general_options_subtab() .  self::print_notice() . $view->getOutput();
				}

	public static function notification_settings(){
						if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
								DbUlp::updateOptionMetaGroup('notification_settings', ulp_sanitize_array($_POST) );
						}
						$data['metas'] = DbUlp::getOptionMetaGroup('notification_settings');
						$view = new ViewUlp();
						$view->setTemplate(ULP_PATH . 'views/admin/general_options_notifications.php');
						$view->setContentData($data);
						return self::general_options_subtab(). self::print_notice() . $view->getOutput();
	}

	public static function messages(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('public_messages', ulp_sanitize_array($_POST) );
			}
			$data = array(
										'metas' => DbUlp::getOptionMetaGroup('public_messages'),
			);
			foreach ($data['metas'] as $key=>$message){
					$data['metas'][$key] = stripslashes($message);
			}
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/messages.php');
			$view->setContentData($data);
			return self::general_options_subtab() . self::print_notice() . $view->getOutput();
	}


	public static function import_export(){
			if (!empty($_POST['import']) && !empty($_FILES['import_file']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					////////////////// IMPORT
					$filename = ULP_PATH . 'import.xml';
					move_uploaded_file($_FILES['import_file']['tmp_name'], $filename);
					require_once ULP_PATH . 'classes/Import_Export/Ulp_Import.php';
					$import = new Ulp_Import();
					$import->setFile($filename);
					$import->run();
			}
			$data = array();
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/import_export.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}

	public static function help()
	{
			$data = array();
			$data = DbUlp::getOptionMetaGroup('licensing');
			$data ['disabled'] = (self::check_curl()) ? '' : 'disabled';
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/help.php');
			$view->setContentData($data);
			return self::print_notice() . $view->getOutput();
	}


	public static function check_curl(){
		return (function_exists('curl_version')) ? TRUE : FALSE;
	}

	public static function shortcodes(){
		require_once ULP_PATH . 'classes/public/ShortcodesUlp.class.php';
		$object = new ShortcodesUlp();
		$data = array(
									'available_shortcodes' => $object->getShortcodes()
		);
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/shortcodes.php');
		$view->setContentData($data);
		return self::print_notice() . $view->getOutput();
	}


	/**
	 * @param none
	 * @return none
	 */
    public static function do_edirect_after_cpt_save($post_ID, $post, $update){
    	if (empty($_POST)){
				 return;
			}
			$url = admin_url('admin.php?page=ultimate_learning_pro&tab=' . $post->post_type);
	    switch ($post->post_type){
				case 'ulp_course':
				case 'ulp_quiz':
				case 'ulp_question':
				case 'ulp_lesson':
					wp_redirect($url);
					exit;
					break;
	    }
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function post_special_settings(){
		switch ($_GET['post_type']){
			case 'ulp_course':
				require_once ULP_PATH . 'classes/admin/post_settings_panel/CoursesSettingsPanel.class.php';
				$object = new CoursesSettingsPanel();
				break;
			case 'ulp_lesson':
				require_once ULP_PATH . 'classes/admin/post_settings_panel/LessonsSettingsPanel.class.php';
				$object = new LessonsSettingsPanel();
				break;
			case 'ulp_quiz':
				require_once ULP_PATH . 'classes/admin/post_settings_panel/QuizesSettingsPanel.class.php';
				$object = new QuizesSettingsPanel();
				break;
			case 'ulp_question':
				require_once ULP_PATH . 'classes/admin/post_settings_panel/QuestionsSettingsPanel.class.php';
				$object = new QuestionsSettingsPanel();
				break;
			case 'ulp_certificate':
				require_once ULP_PATH . 'classes/admin/post_settings_panel/CertificatesSettingsPanel.class.php';
				$object = new CertificatesSettingsPanel();
				break;
		}
		if (isset($object)){
			$object->post_id = sanitize_text_field($_GET['id']);
			return self::print_notice() . $object->getOutput();
		}
	}


	public static function dashboard_pages_ulp_column($states, $post){
			if (isset($post->ID) ){
				$str = '';
				//////////// DEFAULT PAGES
				if (get_post_type($post->ID)=='page'){
					$pages = DbUlp::getOptionMetaGroup('default_pages');
					switch ($post->ID){
						case $pages['ulp_default_page_list_courses']:
							$print = esc_html__('Ultimate Learning Pro - List Courses Page', 'uap');
							break;
						case $pages['ulp_default_page_student_profile']:
							$print = esc_html__('Ultimate Learning Pro - Student Profile Page', 'uap');
							break;
						case $pages['ulp_default_page_become_instructor']:
							$print = esc_html__('Ultimate Learning Pro - Become Instructor', 'uap');
							break;
						case $pages['ulp_default_page_list_watch_list']:
							$print = esc_html__('Ultimate Learning Pro - Wish List', 'uap');
							break;
						case $pages['ulp_default_page_checkout']:
							$print = esc_html__('Ultimate Learning Pro - Checkout', 'uap');
							break;
						case $pages['ulp_default_page_instructor_dashboard']:
							$print = esc_html__('Ultimate Learning Pro - Instructor Dashboard', 'uap');
							break;
					}
					if (!empty($print)){
						$str .= '<div class="ulp-dashboard-list-posts-col-default-pages">' . $print . '</div>';
					}
				}
				if (!empty($str)){
					$states[] = $str;
				}
			}
			return $states;
	}


	public static function create_page_meta_box(){
		/*
		 * @param
		 * @return
		 */
		global $post;
		add_meta_box(
					'ulp_default_pages',//id
					esc_html__('Ultimate Learning Pro - Default Pages', 'uap'),
					array('MainAdminUlp', 'print_page_meta_box'),
					'page',
					'side',
					'high'
		);
	}

	public static function print_page_meta_box(){
			global $post;
			global $indeed_db;
			$data['types'] = array(
							'ulp_default_page_list_courses' => esc_html__('List Courses', 'uap'),
							'ulp_default_page_student_profile' => esc_html__('Student Profile', 'uap'),
							'post_id' => $post->ID,
			);
			$data['current_page_type'] = DbUlp::default_pages_get_current_page_type($post->ID);
			$data['unset_pages'] = DbUlp::default_pages_get_default_unset_pages();

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/page-meta_box.php');
			$view->setContentData($data);
			echo esc_ulp_content($view->getOutput());
	}

	public static function save_meta_box_values($post_id=0){
		if (!empty($_POST['uap_set_page_as_default_something'])){
			update_option( ulp_sanitize_array($_POST['uap_set_page_as_default_something']), ulp_sanitize_array($_POST['ulp_post_id']) );
		}
	}


	public static function wp_print_top_menu(){
				global $wp_admin_bar;
				if (!is_super_admin() || !is_admin_bar_showing()){
					return;
				}
				/// PARENT
				$wp_admin_bar->add_menu(array(
							'id'    => 'ulp_dashboard_menu',
							'title' => 'Ultimate Learning Pro',
							'href'  => admin_url( 'admin.php?page=ultimate_learning_pro' ),
							'meta'  => array(),
				));

				///ITEMS
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_learning_pages', 'title'=>esc_html__('Learning Pages', 'ulp'), 'href'=> "#", 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_showcases', 'title'=>esc_html__('Showcases', 'ulp'), 'href'=> '#', 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu_showcases', 'id'=>'ulp_dashboard_menu_showcases_account_page', 'title'=> esc_html__('Account Page', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=account_page'), 'meta'=>array('target'=>'_blank')));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu_showcases', 'id'=>'ulp_dashboard_menu_showcases_student_leaderboard', 'title'=> esc_html__('Student Leaderboard', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=student_leaderboard'), 'meta'=>array('target'=>'_blank')));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu_showcases', 'id'=>'ulp_dashboard_menu_showcases_courses_grid', 'title'=> esc_html__('Courses Grid', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=courses_grid'), 'meta'=>array('target'=>'_blank')));

				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_courses', 'title'=>esc_html__('Courses', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_course'), 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_lesson', 'title'=>esc_html__('Lessons', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_lesson'), 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_quizes', 'title'=>esc_html__('Quizzes', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_quiz'), 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_questions', 'title'=>esc_html__('Questions', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_question'), 'meta'=>array()));
				if (get_option('ulp_certificates_enable')){
						$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_certifications', 'title'=>esc_html__('Certificates', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_certificate'), 'meta'=>array()));
				}

				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_instructors', 'title'=>esc_html__('Instructors', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=instructors'), 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_notifications', 'title'=>esc_html__('Notifications', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=notifications'), 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_magic_feat', 'title'=>esc_html__('Extensions', 'ulp'), 'href'=>admin_url('admin.php?page=ultimate_learning_pro&tab=magic_feat'), 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_shortcodes', 'title'=>esc_html__('Shortcodes', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=shortcodes'), 'meta'=>array()));
				$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu', 'id'=>'ulp_dashboard_menu_general_settings', 'title'=>esc_html__('General Options', 'ulp'), 'href'=> admin_url('admin.php?page=ultimate_learning_pro&tab=settings'), 'meta'=>array()));

				$data = self::magic_feature_items();
				if ($data){
					foreach ($data as $key=>$item){
						$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu_magic_feat', 'id'=>'ulp_dashboard_menu_magic_feat_' . $key, 'title'=>$item['label'], 'href'=>admin_url('admin.php?page=ultimate_learning_pro&tab=' . $key), 'meta'=>array('target'=>'_blank')));
					}
				}

				$data = DbUlp::getOptionMetaGroup('default_pages');
				if ($data){
					foreach ($data as $key=>$item){
						switch($key){
							case 'ulp_default_page_list_courses':
								$label = 'List Courses';
								break;
							case 'ulp_default_page_student_profile':
								$label = 'Student Profile';
								break;
							case 'ulp_default_page_become_instructor':
								$label = 'Become Instructor';
								break;
							case 'ulp_default_page_list_watch_list':
								$label = 'Wish List';
								break;
							case 'ulp_default_page_checkout':
								$label = 'Checkout';
								break;
							case 'ulp_default_page_instructor_dashboard':
								$label = 'Instructor Dashboard';
								break;
							default:
								$label = 'Ultimate Learning Pro - Default Page';
						}
						if(isset($item) && $item !='-1' && $item !=''){
						$wp_admin_bar->add_menu(array('parent'=>'ulp_dashboard_menu_learning_pages', 'id'=>'ulp_dashboard_menu_learning_pages_' . $key, 'title'=>$label, 'href'=>get_permalink($item), 'meta'=>array('target'=>'_blank')));
						}
					}
				}

				if (get_option('ulp_dashboard_notifications')){
						require_once ULP_PATH . 'classes/Db/Db_Ulp_Dashboard_Notifications.class.php';
		        $Db_Ulp_Dashboard_Notifications = new Db_Ulp_Dashboard_Notifications();
		        $num = $Db_Ulp_Dashboard_Notifications->get('new_students');
						$wp_admin_bar->add_menu( array(
								'id'    => 'ulp_students',
								'title' => '<span class="ulp-top-bar-count">' . $num . '</span>' . esc_html__('New Students', 'ulp'),
								'href'  => admin_url('admin.php?page=ultimate_learning_pro&tab=students'),
								'meta'  => array ( 'class' => 'ulp-top-notf-admin-menu-bar' )
						));

						$num = $Db_Ulp_Dashboard_Notifications->get('new_orders');
						$wp_admin_bar->add_menu( array(
								'id'    => 'ulp_orders',
								'title' => '<span class="ulp-top-bar-count">' . $num . '</span>' . esc_html__('New Orders', 'ulp'),
								'href'  => admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_order'),
								'meta'  => array ( 'class' => 'ulp-top-notf-admin-menu-bar' )
						));
				}
	}

	public static function edit_wp_user_become_instructor($user_object=null){
			if (!empty($user_object->data) && !empty($user_object->data->user_login)){
					$data ['uid'] = $user_object->data->ID;
					$data ['already_instructor'] = DbUlp::isUserInstructor($data['uid']);//in_array('ulp_instructor', $user_object->roles) ? TRUE : FALSE;
					$view = new ViewUlp();
					$view->setTemplate(ULP_PATH . 'views/admin/edit_wp_user-become_instructor.php');
					$view->setContentData($data);
					echo esc_ulp_content($view->getOutput());
			}
	}

	public static function add_edit_wp_user_enrolled_courses($user_object=null){
			if (is_object($user_object)){
					require_once ULP_PATH . 'classes/Db/DbUserEntitiesRelations.class.php';
					require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';

					$DbUserEntitiesRelations = new DbUserEntitiesRelations();
					$temporary = $DbUserEntitiesRelations->get_user_courses($user_object->data->ID);
					$data ['uid'] = $user_object->data->ID;
					$data ['user_courses'] = array();

					$UlpRewardPoints = new UlpRewardPoints($data['uid']);
					$data ['points'] = $UlpRewardPoints->NumOfPoints();

					if ($temporary){
							foreach ($temporary as $course_id){
									$data ['user_courses'][$course_id] = DbUlp::getPostTitleByPostId($course_id);
							}
					}
			} else {
					$data ['user_courses'] = array();
			}
			$data['courses'] = DbUlp::getAllCourses();

			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/add_edit_wp_user_enrolled_courses.php');
			$view->setContentData($data);
			echo esc_ulp_content($view->getOutput());

			if(isset($data ['points'])){
				$view = new ViewUlp();
				$view->setTemplate(ULP_PATH . 'views/admin/add_edit_wp_user_edit_points.php');
				$view->setContentData($data);
				echo esc_ulp_content($view->getOutput());
			}

			if (get_option('ulp_student_badges_enable')){
				if(is_object($user_object)){
					  $data = [
							  'uid' => $user_object->data->ID,
							  'badges' => [],
							  'user_badges' => [],
					  ];
					  /// user badges
					  require_once ULP_PATH . 'classes/Db/Db_Ulp_Student_Badges.class.php';
					  $user_badge_db_object = new Db_Ulp_Student_Badges();
					  $user_badges = $user_badge_db_object->getAllForUser($user_object->data->ID);
					  if (!empty($user_badges)){
							  foreach ($user_badges as $badge_object){
									  $data ['user_badges'][$badge_object->id] = $badge_object->badge_title;
							  }
					  }
					}
					/// all badges
					require_once ULP_PATH . 'classes/Db/Db_Ulp_Badges.class.php';
					$badges_db_object = new Db_Ulp_Badges();
					$all_badges = $badges_db_object->selectAll(9999, 0);
					if (!empty($all_badges)){
							foreach ($all_badges as $badge_object){
									$data ['badges'][$badge_object->id] = $badge_object->badge_title;
									$data ['badges_img'][$badge_object->id] = $badge_object->badge_image;
							}
					}

					$view = new ViewUlp();
					$view->setTemplate(ULP_PATH . 'views/admin/add_edit_wp_user_manage_badges.php');
					$view->setContentData($data);
					echo esc_ulp_content($view->getOutput());
			}
			if (get_option('ulp_certificates_enable') && is_object($user_object)){
					$data = [
							'uid' => $user_object->data->ID,
							'user_certificates' => [],
					];

					/// user badges
					require_once ULP_PATH . 'classes/Db/Db_User_Certificates.class.php';
					$user_certificates_db_object = new Db_User_Certificates();
					$user_certificates = $user_certificates_db_object->getAllCertificatesForUser($user_object->data->ID);
					if (!empty($user_certificates)){
							foreach ($user_certificates as $certificate_object){
								$data ['user_certificates'][$certificate_object->certificate_id]['certificate_title'] = $certificate_object->certificate_title;
								$data ['user_certificates'][$certificate_object->certificate_id]['course_name'] = $certificate_object->course_name;
								$data ['user_certificates'][$certificate_object->certificate_id]['grade'] = $certificate_object->grade;
								$data ['user_certificates'][$certificate_object->certificate_id]['obtained_date'] = ulp_print_date_like_wp($certificate_object->obtained_date);
							}
					}

					$view = new ViewUlp();
					$view->setTemplate(ULP_PATH . 'views/admin/add_edit_wp_user_manage_certificates.php');
					$view->setContentData($data);
					echo esc_ulp_content($view->getOutput());
			}

	}


	public static function after_save_user_action($uid=0, $user_old_data=null){
			/// enrolled courses
			if (!empty($_POST['ulp_enroll_courses'])){
					require_once ULP_PATH . 'classes/UsersCoursesActionsUlp.class.php';
					$UsersCoursesActionsUlp = new UsersCoursesActionsUlp();
					$courses = ulp_sanitize_array( $_POST['ulp_enroll_courses'] );
					foreach ( $courses as $key => $course_id) {
							$UsersCoursesActionsUlp->AppendCourse($uid, $course_id, TRUE);
					}
			}
			//// reward points
			if (isset($_POST['ulp_reward_points'])){
					require_once ULP_PATH . 'classes/Entity/UlpRewardPoints.class.php';
					$UlpRewardPoints = new UlpRewardPoints($uid);
					$points = $UlpRewardPoints->NumOfPoints();
					if ( $points != sanitize_text_field($_POST['ulp_reward_points']) ){
							$UlpRewardPoints->update( sanitize_text_field($_POST['ulp_reward_points']) );
					}
			}

			/// badges
			if (isset($_POST ['ulp_badges_to_user'])){
					require_once ULP_PATH . 'classes/Db/Db_Ulp_Student_Badges.class.php';
					$user_badge_db_object = new Db_Ulp_Student_Badges();
					$badgesArray = ulp_sanitize_array( $_POST['ulp_badges_to_user'] );
					foreach ( $badgesArray as $badge_id){
							do_action('ulp_user_receive_badge', $uid, $badge_id);
							$user_badge_db_object->save($uid, $badge_id);
					}
			}
	}

	public static function custom_currencies(){
			require_once ULP_PATH . 'classes/Db/Db_Custom_Currencies.class.php';
			$Db_Custom_Currencies = new Db_Custom_Currencies();
			if (!empty($_POST['ulp_save'])){
					$Db_Custom_Currencies->add( ulp_sanitize_array( $_POST ) );
			}
			$data ['currencies'] = $Db_Custom_Currencies->getAll();
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/custom_currencies.php');
			$view->setContentData($data);
			return  self::print_notice() . $view->getOutput();
	}

	public static function pushover(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('pushover', ulp_sanitize_array($_POST) );
			}
			$data = array(
										'metas' => DbUlp::getOptionMetaGroup('pushover'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/pushover.php');
			$view->setContentData($data);
			return  self::print_notice() . $view->getOutput();
	}

	public static function hooks()
	{
			$object = new \Indeed\Ulp\Admin\SearchFiltersAndHooks();
			$object->setPluginName( 'indeed-learning-pro' )->setNameShouldContain( [ 'ulp' ] )->SearchFiles( ULP_PATH );
			$data = $object->getResults();
			$view = new ViewUlp();
			$view->setTemplate( ULP_PATH . 'views/admin/hooks.php' );
			$view->setContentData( $data );
			return self::print_notice() . $view->getOutput();
	}

	public static function course_time_period(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('course_time_period', ulp_sanitize_array( $_POST ) );
			}
			$data = array(
										'metas' => DbUlp::getOptionMetaGroup('course_time_period'),
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/course_time_period.php');
			$view->setContentData($data);
			return self::print_notice() .  $view->getOutput();
	}

	public static function course_difficulty(){
			if (isset($_POST['submit']) && isset( $_POST['ulp_admin_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_admin_nonce'] ), 'ulp_admin_nonce' ) ){
					DbUlp::updateOptionMetaGroup('course_difficulty', ulp_sanitize_array($_POST) );
					if (!empty($_POST['slug']) && !empty($_POST['label'])){
							$_POST['slug'] = strip_tags( $_POST['slug'] );// no html into slug
							DbUlp::save_course_difficulty_type($_POST);
					}
			}
			$data = array(
										'metas' => DbUlp::getOptionMetaGroup('course_difficulty'),
										'course_difficulty_types' => DbUlp::get_course_difficulty_types()
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/course_difficulty.php');
			$view->setContentData($data);
			return  self::print_notice() . $view->getOutput();
	}

	private function removeOldImportFiles()
	{
			$directory = UAP_PATH;
			$files = scandir( $directory );
			foreach ( $files as $file ){
					$fileFullPath = $directory . $file;
					if ( file_exists( $fileFullPath ) && filetype( $fileFullPath ) == 'file' ){
							$extension = pathinfo( $fileFullPath, PATHINFO_EXTENSION );
							if ( $extension == 'xml' && $file == 'export.xml' ){
									unlink( $fileFullPath );
							} else if ( $extension == 'csv' && ( $file == 'affiliates.csv' || $file == 'referrals.csv' || $file == 'visits.csv' ) ){
									unlink( $fileFullPath );
							}
					}
			}
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function checkSystem()
	{
		$wp_cron = ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ) ? FALSE : TRUE;
		if ( !$wp_cron ){
			self::$error_messages[] = esc_html__('Crons are disabled on your WordPress Website. Some functionality and processes may not work properly.', 'ulp');
		}

		/// curl
		if ( !function_exists('curl_version') || !curl_version() ){
				self::$error_messages[] = esc_html__('cURL is not working or is disabled on your Website environment. Please contact your Hosting provider.', 'ulp');
		}

		//
		$cropFunctions = [
											'getimagesize',
											'imagecreatefrompng',
											'imagecreatefromjpeg',
											'imagecreatefromgif',
											'imagecreatetruecolor',
											'imagecopyresampled',
											'imagerotate',
											'imagesx',
											'imagesy',
											'imagecolortransparent',
											'imagecolorallocate',
											'imagejpeg',
		];
		foreach ( $cropFunctions as $cropFunction ){
				if ( !function_exists( $cropFunction ) ){
						$functionsErrors[] = $cropFunction .'()';
				}
		}
		if ( !empty($functionsErrors) ){
				self::$error_messages[] = esc_html__('Following functions: ', 'ulp') . implode( ', ', $functionsErrors )
				. esc_html__( ' are disabled on your Website environment. Avatar feature may not work properly. Please contract your Hosting provider.', 'ulp');
		}
	}

	/**
	 * @param none
	 * @return string
	 */
	public static function printTopMessages()
	{
			if ( empty( self::$error_messages ) ){
					return '';
			}
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/admin/top_messages.php');
			$view->setContentData( self::$error_messages );
			return $view->getOutput();
	}


}
