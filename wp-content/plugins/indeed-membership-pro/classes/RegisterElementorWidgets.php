<?php
namespace Indeed\Ihc;

class RegisterElementorWidgets
{

	private static $_instance = null;

	public static function instance()
  {
		  if ( is_null( self::$_instance ) ) {
			   self::$_instance = new self();
		  }
		  return self::$_instance;
	}

	private function include_widgets_files()
  {
		  require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Account_Page_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Register_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Login_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Checkout_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Thank_You_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Logout_Link_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Password_Reset_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Select_Level_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Visitor_Inside_User_Page_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Login_Popup_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Register_Popup_Widget.php';
      require_once IHC_PATH . 'classes/services/Elementor/Elementor_Ihc_Locker_Widget.php';
	}

	public function register_widgets()
  {
		  $this->include_widgets_files();
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Account_Page_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Register_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Login_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Checkout_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Thank_You_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Logout_Link_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Password_Reset_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Select_Level_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Visitor_Inside_User_Page_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Login_Popup_Widget() );
      \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Register_Popup_Widget() );
      	  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ihc_Locker_Widget() );
	}

	public function __construct()
  {
		  // Register widgets
		  add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
	}

}

\Indeed\Ihc\RegisterElementorWidgets::instance();
