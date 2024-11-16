<?php
namespace Indeed\Uap;
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
		  require_once UAP_PATH . 'classes/services/Elementor_Integration/Elementor_Uap_Account_Page_Widget.php';
      require_once UAP_PATH . 'classes/services/Elementor_Integration/Elementor_Uap_Register_Widget.php';
      require_once UAP_PATH . 'classes/services/Elementor_Integration/Elementor_Uap_Login_Form_Widget.php';
      require_once UAP_PATH . 'classes/services/Elementor_Integration/Elementor_Uap_Logout_Widget.php';
      require_once UAP_PATH . 'classes/services/Elementor_Integration/Elementor_Uap_Reset_Password_Widget.php';
	}

	public function register_widgets()
  {
		  $this->include_widgets_files();
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Uap_Account_Page_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Uap_Register_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Uap_Login_Form_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Uap_Logout_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Uap_Reset_Password_Widget() );
	}

	public function __construct()
  {
		  // Register widgets
		  add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
	}

}

\Indeed\Uap\RegisterElementorWidgets::instance();
