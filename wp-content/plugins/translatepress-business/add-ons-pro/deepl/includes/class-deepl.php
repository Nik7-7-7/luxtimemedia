<?php

class TRP_IN_DeepL {

    protected $loader;

    public function __construct() {
        $trp                 = TRP_Translate_Press::get_trp_instance();
        $this->loader        = $trp->get_component( 'loader' );

        $this->loader->add_action( 'trp_machine_translation_engines', $this, 'add_engine', 10, 1 );
        $this->loader->add_action( 'trp_machine_translation_extra_settings_middle', $this, 'add_settings', 10, 1 );
        $this->loader->add_action( 'trp_machine_translation_sanitize_settings', $this, 'sanitize_settings', 10, 1 );
        $this->loader->add_action( 'admin_enqueue_scripts', $this, 'add_scripts', 99, 1 );
        $this->loader->add_filter( 'trp_deepl_target_language', $this, 'configure_api_target_language', 10, 3 );
        $this->loader->add_filter( 'trp_deepl_source_language', $this, 'configure_api_source_language', 10, 3 );
        $this->loader->add_filter( 'trp_deepl_supported_languages', $this, 'add_missing_supported_languages', 10, 1 );

        require_once TRP_IN_DL_PLUGIN_DIR . 'includes/class-deepl-machine-translator.php';
    }

    public function add_scripts( $hook ){
        if( $hook == 'admin_page_trp_machine_translation' )
            wp_enqueue_script( 'trp-deepl-settings', TRP_IN_DL_PLUGIN_URL . 'assets/js/trp-deepl-back-end.js', [ 'jquery' ], TRP_IN_DL_PLUGIN_VERSION );
    }

    public function add_engine( $engines ){
        $engines[] = [ 'value' => 'deepl', 'label' => __( 'DeepL', 'translatepress-multilingual' ) ];

        return $engines;
    }

    /**
     * Returns an appropriate error/success message for the DeepL API acess.
     *
     * @param int $code The code returned by DeepL API access.
     *
     * @return array [ (string) $message, (bool) $is_error ].
     */
    public static function deepl_response_codes( $code ) {
        $is_error       = false;
        $code           = intval( $code );
        $return_message = '';

        /**
         * Determine if we have a 4xx or 5xx error.
         *
         * @see https://www.deepl.com/docs-api/accessing-the-api/
         */
        if ( preg_match( '/4\d\d/', $code ) || preg_match( '/5\d\d/', $code ) ) {
            $is_error = true;
        }
        
        if ( true === $is_error ) {
            switch ( $code ) {
                case 400:
                    $return_message = esc_html__( 'Bad request. There was an error accessing the DeepL API.', 'translatepress-multilingual' );
                    break;
                case 403:
                    $return_message = esc_html__( 'The API key entered is invalid.', 'translatepress-multilingual' );
                    break;
                case 404:
                    $return_message = esc_html__( 'The API resource could not be found.', 'translatepress-multilingual' );
                    break;
                case 413:
                    $return_message = esc_html__( 'The request size is too large.', 'translatepress-multilingual' );
                    break;
                case 414:
                    $return_message = esc_html__( 'The request is too long.', 'translatepress-multilingual' );
                    break;
                case 429:
                    $return_message = esc_html__( 'Too many requests. Please try again later.', 'translatepress-multilingual' );
                    break;
                case 456:
                    $return_message = esc_html__( 'Your translation quota has been reached.', 'translatepress-multilingual' );
                    break;
                case 503:
                    $return_message = esc_html__( 'We could not process your request. Please try again later.', 'translatepress-multilingual' );
                    break;
                default:
                    $return_message = esc_html__( 'There is an error on the DeepL service and your request could not be processed.', 'translatepress-multilingual' );
                    break;
            }
        }
        return array(
            'message' => $return_message,
            'error'   => $is_error,
        );
    }

    public function add_settings( $settings ){
        $trp                = TRP_Translate_Press::get_trp_instance();
        $machine_translator = $trp->get_component( 'machine_translator' );

        // Error messages.
        $show_errors   = false;
        $error_message = '';

        $translation_engine = isset( $settings['translation-engine'] ) ? $settings['translation-engine'] : '';

        // Check for API errors.
        if ( 'deepl' === $translation_engine ) {
            $trp = TRP_Translate_Press::get_trp_instance();
            $machine_translator = $trp->get_component( 'machine_translator' );
            $api_check = $machine_translator->check_api_key_validity();
        }

        if ( isset($api_check) && true === $api_check['error'] ) {
            $error_message = $api_check['message'];
            $show_errors    = true;
        }

        $text_input_classes = array(
            'trp-text-input',
        );
        if ( $show_errors && 'deepl' === $translation_engine ) {
            $text_input_classes[] = 'trp-text-input-error';
        }

        if( !isset( $settings['deepl-api-type'] ) )
            $settings['deepl-api-type'] = 'pro';
        ?>

        <tr class="trp-engine" id="deepl">
            <th scope="row"><?php esc_html_e( 'DeepL API Type', 'translatepress-multilingual' ); ?> </th>
            <td>
                <label for="trp-deepl-api-type-pro" style="margin-right:10px;">
                    <input type="radio" class="trp-deepl-api-type trp-radio" id="trp-deepl-api-type-pro" name="trp_machine_translation_settings[deepl-api-type]" value="pro" <?php checked( $settings['deepl-api-type'], 'pro' ); ?>>
                    <?php esc_html_e( 'Pro', 'translatepress-multilingual' ); ?>
                </label>

                <label for="trp-deepl-api-type-free" style="margin-right:10px;">
                    <input type="radio" class="trp-deepl-api-type trp-radio" id="trp-deepl-api-type-free" name="trp_machine_translation_settings[deepl-api-type]" value="free" <?php checked( $settings['deepl-api-type'], 'free' ); ?>>
                    <?php esc_html_e( 'Free', 'translatepress-multilingual' ); ?>
                </label>

                <p class="description">
                    <?php esc_html_e( 'Select the type of DeepL API you want to use.', 'translatepress-multilingual' ); ?>
                </p>
                <h4><?php esc_html_e( 'DeepL API Key', 'translatepress-multilingual' ); ?></h4>
                <?php
                // Display an error message above the input.
                if ( $show_errors && 'deepl' === $translation_engine ) {
                    ?>
                    <p class="trp-error-inline">
                        <?php echo wp_kses_post( $error_message ); ?>
                    </p>
                    <?php
                }
                ?>
                <input type="text" id="trp-deepl-key" class="<?php echo esc_html( implode( ' ', $text_input_classes ) ); ?>" name="trp_machine_translation_settings[deepl-api-key]" value="<?php if( !empty( $settings['deepl-api-key'] ) ) echo esc_attr( $settings['deepl-api-key']);?>"/>
                <?php
                // Show error or success SVG.
                if ( method_exists( $machine_translator, 'automatic_translation_svg_output' ) && 'deepl' === $translation_engine ) {
                    $machine_translator->automatic_translation_svg_output( $show_errors );
                }
                ?>
                <p class="description">
                    <?= wp_kses( sprintf( __( 'Visit <a href="%s" target="_blank">this link</a> to see how you can set up an API key and control API costs.', 'translatepress-multilingual' ), 'https://translatepress.com/docs/addons/deepl-automatic-translation/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=TRP&utm_content=deepl-api-key#generate-key' ), [ 'a' => [ 'href' => [], 'target'=> [] ] ] ) ?>
                </p>
            </td>
        </tr>
        <?php
    }

    public function sanitize_settings( $settings ){
        if( !empty( $settings['deepl-api-key'] ) )
            $settings['deepl-api-key'] = sanitize_text_field( $settings['deepl-api-key'] );

        return $settings;
    }

    /**
     * Particularities for source language in DeepL API.
     *
     * PT_BR is not treated in the same way as for the target language
     *
     * @param $source_language
     * @param $source_language_code
     * @param $target_language_code
     * @return string
     */
    public function configure_api_source_language($source_language, $source_language_code, $target_language_code ){
        $exceptions_source_mapping_codes = array(
            'zh_HK' => 'zh',
            'zh_TW' => 'zh',
            'zh_CN' => 'zh',
            'de_DE_formal' => 'de',
            'nb_NO' => 'nb'
        );
        if ( isset( $exceptions_source_mapping_codes[$source_language_code] ) ){
            $source_language = $exceptions_source_mapping_codes[$source_language_code];
        }

        return $source_language;
    }

    /**
     * Particularities for target language in DeepL API
     *
     * @param $target_language
     * @param $source_language_code
     * @param $target_language_code
     * @return string
     */
    public function configure_api_target_language($target_language, $source_language_code, $target_language_code ){
        $exceptions_target_mapping_codes = array(
                'zh_HK' => 'zh-hant',
                'zh_TW' => 'zh-hant',
                'zh_CN' => 'zh-hans',
                'pt_BR' => 'pt-br',
                'pt_PT' => 'pt-pt',
                'pt_AO' => 'pt-pt',
                'pt_PT_ao90' => 'pt-pt',
                'de_DE_formal' => 'de',
                'en_GB' => 'en-gb',
                'en_US' => 'en-us',
                'en_CA' => 'en-us',
                'en_ZA' => 'en-gb',
                'en_NZ' => 'en-gb',
                'en_AU' => 'en-gb',
                'nb_NO' => 'nb'
        );
        if ( isset( $exceptions_target_mapping_codes[$target_language_code] ) ){
            $target_language = $exceptions_target_mapping_codes[$target_language_code];
        }

        return $target_language;
    }

    /**
     * DeepL does support Arabic but it doesn't show up in /languages endpoint because they don't have support for
     * document translation yet, only text. Adding these languages manually here.
     */
    public function add_missing_supported_languages( $languages ) {
        if ( !in_array( 'ar', $languages ) ) {
            $languages[] = 'ar';
        }
        return $languages;
    }
}
