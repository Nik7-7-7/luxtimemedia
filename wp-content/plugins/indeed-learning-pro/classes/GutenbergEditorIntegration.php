<?php
namespace Indeed\Ulp;

class GutenbergEditorIntegration
{

    public function __construct()
    {
        if ( !is_admin() ){
            return;
        }
        if ( !function_exists( 'register_block_type' ) ) {
            return;
        }
        add_filter( 'block_categories_all', [$this, 'registerCategory'], 10, 2 );
        add_action( 'admin_enqueue_scripts', [$this, 'assets'] );
    }

    public function registerCategory( $categories=[], $post=null )
    {
        $categories[] = array(
                              'slug' => 'ulp-shortcodes',
                              'title' => esc_html__( 'Ultimate Learning Pro - Shortcodes', 'uap' ),
                              'icon'  => '',
        );
        return $categories;
    }

    public function assets()
    {
        global $current_screen;
        if (!isset($current_screen)) {
            $current_screen = get_current_screen();
        }
        if ( !method_exists($current_screen, 'is_block_editor') || !$current_screen->is_block_editor() ) {
            return;
        }
        wp_enqueue_script( 'ulp-gutenberg-integration', ULP_URL . 'assets/js/gutenberg_integration.js', ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'], '3.9' );
    }

}
