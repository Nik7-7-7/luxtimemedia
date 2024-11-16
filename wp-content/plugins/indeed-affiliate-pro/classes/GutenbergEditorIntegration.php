<?php
namespace Indeed\Uap;
class GutenbergEditorIntegration
{
  public function __construct(){
      if ( !is_admin() ){
        return;
      }
      if ( !function_exists( 'register_block_type' ) ) {
          return;
      }
      add_filter( 'block_categories_all', array( $this, 'registerCategory'), 10, 2 );
      add_action( 'admin_enqueue_scripts', array($this, 'assets') );
  }

  public function registerCategory( $categories=[], $post=null ){
      if ( !current_user_can( 'manage_options' ) ){
        return $categories;
      }
      $categories[] = array(
        'slug' => 'uap-shortcodes',
        'title' => esc_html__( 'Ultimate Affiliate Pro - Shortcodes', 'uap' ),
        'icon'  => '',
      );
      return $categories;
  }

  public function assets(){
    global $current_screen;
    if ( !current_user_can( 'manage_options' ) ){
        return;
    }
    if (!isset($current_screen)) {
        $current_screen = get_current_screen();
    }
    if ( !method_exists($current_screen, 'is_block_editor') || !$current_screen->is_block_editor() ) {
        return;
    }
    wp_enqueue_script( 'uap-gutenberg-integration', UAP_URL . 'assets/js/gutenberg_integration.js', array('jquery', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'), 8.3 );
  }
}
