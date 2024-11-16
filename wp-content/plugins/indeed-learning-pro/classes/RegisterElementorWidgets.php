<?php
namespace Indeed\Ulp;

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
		  require_once ULP_PATH . 'classes/services/Elementor/Elementor_Ulp_Student_Profile_Widget.php';
      require_once ULP_PATH . 'classes/services/Elementor/Elementor_Ulp_List_Courses_Widget.php';
      require_once ULP_PATH . 'classes/services/Elementor/Elementor_Ulp_Become_Instructor_Widget.php';
      require_once ULP_PATH . 'classes/services/Elementor/Elementor_Ulp_List_Watchlist_Widget.php';
      require_once ULP_PATH . 'classes/services/Elementor/Elementor_Ulp_Checkout_Widget.php';
	}

	public function register_widgets()
  {
		  $this->include_widgets_files();
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ulp_Student_Profile_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ulp_List_Courses_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ulp_Become_Instructor_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ulp_List_Watchlist_Widget() );
		  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Ulp_Checkout_Widget() );
	}

  public function __construct()
  {
      // Register widgets
      add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
  }

}
\Indeed\Ulp\RegisterElementorWidgets::instance();
