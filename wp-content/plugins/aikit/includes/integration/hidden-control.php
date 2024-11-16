<?php

use Elementor\Control_Hidden;

defined( 'ABSPATH' ) || die();

class AIKit_Elementor_Editor_Control extends Control_Hidden {

    private array $jsConfigs;

    public function __construct(array $jsConfigs)
    {
        parent::__construct();

        $this->jsConfigs = $jsConfigs;
    }

    public function get_type() {
        return 'aikit_hidden_control';
    }
    public function enqueue() {

        wp_register_script( 'aikit_elementor_editor', plugins_url('../../includes/js/elementor-classic.js', __FILE__ ), [
            'jquery',
        ] );

        wp_register_script( 'aikit_color', plugins_url('../../includes/js/color.js', __FILE__ ), [
                'aikit_elementor_editor'
        ] );

        wp_register_style( 'aikit_elementor_editor_css', plugins_url('../../includes/css/elementor.css', __FILE__ ) );

        wp_enqueue_script( 'aikit_elementor_editor' );
        wp_enqueue_script( 'aikit_color' );
        wp_enqueue_style( 'aikit_elementor_editor_css' );

        // languages

        $currentLanguage = get_locale();

        if (strlen($currentLanguage) > 2) {
            $currentLanguage = explode('_', $currentLanguage)[0];
        }

        if (in_array($currentLanguage, ['de', 'es', 'fr'])) {
            // include the js file for translation
            wp_register_script( 'aikit_elementor_editor_lang', plugins_url('../../includes/js/langs/' . $currentLanguage . '.js', __FILE__ ), [
                'aikit_elementor_editor'
            ] );

            wp_enqueue_script( 'aikit_elementor_editor_lang' );
        }
    }

    public function content_template() {
        ?>
            <input id="aikit-js-configs" type="hidden" value="<?php echo htmlentities(json_encode($this->jsConfigs)); ?>"/>
            <input type="hidden" class="aikit-editor-is-there">
        <?php
    }
}
