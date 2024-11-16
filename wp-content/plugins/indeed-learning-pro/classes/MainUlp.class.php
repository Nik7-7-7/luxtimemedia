<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('MainUlp')){
	 return;
}

class MainUlp{
	private static $instance = FALSE;

	public function __construct(){}

	/**
	 * @param none
	 * @return none
	 */
	public static function run(){
		if (self::$instance){
			return;
		}
		self::$instance = TRUE;

		self::loadDependencies();

		self::MagicFeatures();

	  register_activation_hook(ULP_PATH . 'indeed-learning-pro.php', array('MainUlp', 'activatePluginJobs'));
		self::includePostTypes();

		self::doInitJobs();

		if (is_admin() && !defined('DOING_AJAX')){
			/// ADMIN
			require_once ULP_PATH . 'classes/MainAdminUlp.class.php';
			MainAdminUlp::run();
		} else {
			/// PUBLIC
			require_once ULP_PATH . 'classes/MainPublicUlp.class.php';
			MainPublicUlp::run();
		}
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function activatePluginJobs(){
		/// create db tables
		DbUlp::createTables();

		/// create default pages
		DbUlp::create_default_pages();

		/// save settings default values to db
		DbUlp::save_settings_default_values();

		/// save settings default values to db
		DbUlp::create_default_course_difficulties();

		/// save settings default values to db
		DbUlp::create_default_notifications();

		/// add custom wp roles
		self::add_new_role();

		/// query variables
		$Ulp_Query_Vars = new Ulp_Query_Vars();
		$Ulp_Query_Vars->doRegister(true);

		/// insert demo content
		require_once ULP_PATH . 'classes/Ulp_Demo_Content.class.php';
		$Ulp_Demo_Content = new Ulp_Demo_Content();

		/// do flush rewrite
		update_option('ulp_do_flush_rewrite', true);
	}


	public static function add_new_role(){
			DbUlp::create_plugin_custom_roles();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function loadDependencies(){
		require_once ULP_PATH . 'utilities.php';
		require_once ULP_PATH . 'html_functions.php';
		require_once ULP_PATH . 'classes/IndeedForms.class.php';
		require_once ULP_PATH . 'classes/AjaxUlp.class.php';
		require_once ULP_PATH . 'classes/Abstracts/CustomPostTypeUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/CoursesUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/LessonsUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/QuizesUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/QuestionsUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/CertificatesUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/OrderUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/CourseReviewsUlp.class.php';
		require_once ULP_PATH . 'classes/post_types/InstructorsUlp.php';
		require_once ULP_PATH . 'classes/ViewUlp.class.php';
		require_once ULP_PATH . 'classes/Db/DbUlp.class.php';
		require_once ULP_PATH . 'classes/IndeedPagination.class.php';
		require_once ULP_PATH . 'classes/public/ShortcodesUlp.class.php';
		require_once ULP_PATH . 'classes/Entity/UlpCourse.class.php';
		require_once ULP_PATH . 'classes/Entity/UlpLesson.class.php';
		require_once ULP_PATH . 'classes/Entity/UlpQuiz.class.php';
		require_once ULP_PATH . 'classes/Entity/UlpQuestion.class.php';
		require_once ULP_PATH . 'classes/Entity/UlpModuleItems.class.php';
		require_once ULP_PATH . 'classes/UlpCronJobs.class.php';
		require_once ULP_PATH . 'classes/UlpNotification.class.php';
		require_once ULP_PATH . 'classes/Ulp_Notifications_Triggers.class.php';
		require_once ULP_PATH . 'classes/Entity/UlpOrder.class.php';
		require_once ULP_PATH . 'classes/Ulp_General_Actions.class.php';
		require_once ULP_PATH . 'classes/Ulp_Query_Vars.php';
		require_once ULP_PATH . 'classes/Ulp_Permalinks.php';
		require_once ULP_PATH . 'classes/UlpUpdates.php';
		require_once ULP_PATH . 'classes/public/Ulp_Global_Settings.class.php';
		require_once ULP_PATH . 'classes/RegisterElementorWidgets.php';
	}


	public static function MagicFeatures(){
			/// WOOCOMMERCE PAYMENT
			if (get_option('ulp_woocommerce_payment_enable')){
				require_once ULP_PATH . 'classes/Ulp_WooCommerce_Payment.class.php';
				$Ulp_WooCommerce_Payment = new Ulp_WooCommerce_Payment();
			}

			/// UMP Payment
			if (get_option('ulp_ump_payment_enable')){
				require_once ULP_PATH . 'classes/Ulp_UMP_Payment.class.php';
				$Ulp_WooCommerce_Payment = new Ulp_UMP_Payment();
			}

			///edd payment
			if (get_option('ulp_edd_payment_enable')){
					require_once ULP_PATH . 'classes/Ulp_Edd_Payment.class.php';
					$Ulp_Edd_Payment = new Ulp_Edd_Payment();
			}

			if (get_option('lesson_drip_content_enable')){
					require_once ULP_PATH . 'classes/Ulp_Drip_Content.class.php';
					$Ulp_Drip_Content = new Ulp_Drip_Content();
			}

			if (get_option('ulp_student_badges_enable')){
					require_once ULP_PATH . 'classes/Ulp_Give_Badges.class.php';
					$Ulp_Give_Badges = new Ulp_Give_Badges();
			}

			if (get_option('ulp_mycred_enable')){
					require_once ULP_PATH . 'classes/Ulp_My_Cred_Hooks.class.php';
					$Ulp_My_Creed_Hooks = new Ulp_My_Cred_Hooks();
			}

			if (get_option('ulp_buddypress_integration_enable')){
					require_once ULP_PATH . 'classes/Ulp_Buddypress_Endpoint.class.php';
					$Ulp_Buddypress_Endpoint = new Ulp_Buddypress_Endpoint();
			}

			$CourseCurriculumSlider = new \Indeed\Ulp\CourseCurriculumSlider();
	}


	/**
	 * @param none
	 * @return none
	 */
	public static function doInitJobs(){
		$AjaxUlp = new AjaxUlp();
		$cronJobs = new UlpCronJobs();
		$ShortcodesUlp = new ShortcodesUlp();
		$Ulp_Notifications_Triggers = new Ulp_Notifications_Triggers();
		$Ulp_General_Actions = new Ulp_General_Actions();
		$Ulp_Query_Vars = new Ulp_Query_Vars();
		$Ulp_Query_Vars->doRegister();
		$WPMLActions = new \Indeed\Ulp\WPMLActions();

		$Filters = new \Indeed\Ulp\Filters();
		$RewriteDefaultWpAvatar = new \Indeed\Ulp\RewriteDefaultWpAvatar();

		$GutenbergEditorIntegration = new \Indeed\Ulp\GutenbergEditorIntegration();

		add_action('init', array('MainUlp', 'ulp_add_endpoint'));
		/// make Yoast SEO custom columns available into our custom post type custom tables
		add_filter('wpseo_always_register_metaboxes_on_admin', array('MainUlp', 'set_yoast_seo_cols_active'), 99, 1);
		add_filter('query_vars', array('MainUlp', 'registerQueryStrings'), 99, 1);

		add_action('init', ['MainUlp', 'direct_payment_processing']);
		add_action('init', ['MainUlp', 'gate']);

		$elCheck  = new \Indeed\Ulp\ElCheck();

		//nonce
		add_action( 'admin_head', 'MainUlp::adminNonce' );
		add_action( 'wp_head', 'MainUlp::publicNonce' );

	}


	/**
	 * @param none
	 * @return none
	 */
	public static function includePostTypes(){
		$CoursesUlp = new CoursesUlp();
		$LessonsUlp = new LessonsUlp();
		$QuizesUlp = new QuizesUlp();
		$QuestionsUlp = new QuestionsUlp();
		$CertificatesUlp = new CertificatesUlp();
		$OrderUlp = new OrderUlp();
		$CourseReviewsUlp = new CourseReviewsUlp();
		$InstructorsUlp = new InstructorsUlp();
		$Announcement = new \Indeed\Ulp\PostType\AnnouncementsUlp();
		$qanda = new \Indeed\Ulp\PostType\QandaUlp();
	}


	/**
	 * @param bool
	 * @return bool
	 */
	public static function set_yoast_seo_cols_active($bool=FALSE){
		return TRUE;
	}


	/**
	 * @param array
	 * @return array
	 */
	public static function registerQueryStrings($vars=array()){
		/// todo check this

 		return $vars;
	}


	/**
	 * @param array
	 * @return array
	 */
	public static function ulp_add_endpoint(){

	}

	public static function direct_payment_processing(){
			$doIt = apply_filters('ulp_do_direct_payment_processing', true);
			if (empty($doIt)){
				 return;
			}

			if (!empty($_POST['ulp_pay']) && !empty($_POST['course_id']) && !empty($_POST['payment_type'])
					&& isset( $_POST['ulp_public_t'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ulp_public_t'] ), 'ulp_public_t' )
			){
					require_once ULP_PATH . 'classes/Ulp_Direct_Payment.class.php';
					$uid = ulp_get_current_user();
					$payment_data = [
							'amount' => get_post_meta( sanitize_text_field( $_POST['course_id'] ), 'ulp_course_price', true),
							'currency' => get_option('ulp_currency'),
					];

					$_POST['course_id'] = sanitize_text_field( $_POST['course_id'] );
					$_POST['payment_type'] = sanitize_text_field( $_POST['payment_type'] );

					$Ulp_Direct_Payment = new Ulp_Direct_Payment();
					$Ulp_Direct_Payment->setUid($uid)
														 ->setCourseId( sanitize_text_field( $_POST['course_id'] ) )
														 ->setPaymentType( sanitize_text_field( $_POST['payment_type'] ) )
														 ->setPaymentDetails($payment_data)
														 ->do_payment();


					return;
			}
	}

	public static function gate(){
			if (empty($_GET['ulp_action'])){
				 return;
			}
			switch ($_GET['ulp_action']){
					case 'paypal':
						require_once ULP_PATH . 'classes/Payment_Services/Ulp_PayPal.class.php';
						$Ulp_PayPal = new Ulp_PayPal();
						$Ulp_PayPal->ipn();
						break;
					case 'stripe':
						require_once ULP_PATH . 'classes/Payment_Services/Ulp_Stripe.class.php';
						$Ulp_Stripe = new Ulp_Stripe();
						$Ulp_Stripe->ipn();
						break;
					case '2checkout':
						$TwoCheckout = new Indeed\Ulp\PaymentService\TwoCheckout();
						$TwoCheckout->ipn();
						break;
			}
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function adminNonce()
	{
			$nonce = wp_create_nonce( 'ulpAdminNonce' );
			echo esc_ulp_content("<meta name='ulp-admin-token' content='$nonce'>");
	}

	/**
	 * @param none
	 * @return none
	 */
	public static function publicNonce()
	{
			$nonce = wp_create_nonce( 'ulpPublicNonce' );
	    echo esc_ulp_content("<meta name='ulp-token' content='$nonce'>");
	}

}
