<template id="trp_ald_no_text_popup_template">
    <div id="trp_no_text_popup_wrap">
        <div id="trp_no_text_popup" class="trp_ald_no_text_popup" data-no-dynamic-translation data-no-translation>
            <?php
            $trp                   = TRP_Translate_Press::get_trp_instance();
            $trp_settings          = $trp->get_component( 'settings' );
            $settings              = $trp_settings->get_settings();
            $this->trp_languages   = $trp->get_component( 'languages' );
            $languages_to_display  = $this->settings['publish-languages'];
            $published_languages   = $this->trp_languages->get_language_names( $languages_to_display );
            $trp_language_switcher = $trp->get_component( 'language_switcher' );
            $ls_option             = $trp_settings->get_language_switcher_options();
            $shortcode_settings    = $ls_option[ $settings['shortcode-options'] ];
            $language_cookie_data  = $this->get_language_cookie_data();
            ?>

            <div id="trp_ald_not_text_popup_ls_and_button">
                <div id="trp_ald_no_text_popup_div">
                    <span id="trp_ald_no_text_popup_text"></span>
                </div>
                <div class="trp_ald_ls_container">
                    <div class="trp-language-switcher trp-language-switcher-container" id="trp_ald_no_text_select"
                         data-no-translation <?php echo ( isset( $_GET['trp-edit-translation'] ) && $_GET['trp-edit-translation'] == 'preview' ) ? 'data-trp-unpreviewable="trp-unpreviewable"' : '' ?>>
                        <?php
                        $current_language_preference = $trp_language_switcher->add_shortcode_preferences( $shortcode_settings, $settings['default-language'], $published_languages[ $settings['default-language'] ] );
                        ?>

                        <div class="trp-ls-shortcode-current-language" id="<?php echo esc_attr( $settings["default-language"] ); ?>"
                             special-selector="trp_ald_popup_current_language" data-trp-ald-selected-language="<?php echo esc_attr( $settings["default-language"] ); ?>">
                            <?php echo $current_language_preference; /* phpcs:ignore */ /* escaped inside the function that generates the output */ ?>
                        </div>
                        <div class="trp-ls-shortcode-language" id="trp_ald_no_text_popup_select_container">
                            <div class="trp-ald-popup-select" id="<?php echo esc_attr( $settings['default-language'] ) ?>"
                                 data-trp-ald-selected-language= <?php echo esc_attr( $settings['default-language'] ) ?>>
                                <?php echo $current_language_preference; /* phpcs:ignore */ /* escaped inside the function that generates the output */ ?>
                            </div>
                            <?php foreach ( $published_languages as $code => $name ) {
                                if ($code != $settings['default-language']){
                            $language_preference = $trp_language_switcher->add_shortcode_preferences( $shortcode_settings, $code, $name );
                                    ?>
                                    <div class="trp-ald-popup-select" id="<?php echo esc_attr( $code ); ?>"
                                         data-trp-ald-selected-language="<?php echo esc_attr( $code ); ?>">
                                        <?php
                                        echo $language_preference; /* phpcs:ignore */ /* escaped inside the function that generates the output */ ?>

                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <div class="trp_ald_change_language_div">
                    <a href="<?php echo esc_url( $language_cookie_data['abs_home'] ); ?>" id="trp_ald_no_text_popup_change_language"></a>
                </div>
                <div id="trp_ald_no_text_popup_x_button_and_textarea"> <a id="trp_ald_no_text_popup_x_button"></a><span id="trp_ald_no_text_popup_x_button_textarea"></span></div>
            </div>
            <div id="trp_ald_no_text_popup_x">
                <button id="trp_close"></button>
            </div>
        </div>
    </div>
</template>