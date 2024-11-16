<div class="trp_model_container" id="trp_ald_modal_container" style="display: none" data-no-dynamic-translation data-no-translation>
    <?php
    $trp                = TRP_Translate_Press::get_trp_instance();
    $trp_settings       = $trp->get_component( 'settings' );
    $settings           = $trp_settings->get_settings();
    $this->trp_languages = $trp->get_component('languages');
    $languages_to_display = $this->settings['publish-languages'];
    $published_languages = $this->trp_languages->get_language_names( $languages_to_display );
    $trp_language_switcher = $trp->get_component('language_switcher');
    $ls_option = $trp_settings->get_language_switcher_options();
    $shortcode_settings = $ls_option[$settings['shortcode-options']];
    $language_cookie_data = $this->get_language_cookie_data();
    ?>
    <div class="trp_ald_modal" id="trp_ald_modal_popup">
            <div id="trp_ald_popup_text"></div>

        <div class="trp_ald_select_and_button">
            <div class="trp_ald_ls_container">
            <div class="trp-language-switcher trp-language-switcher-container"  id="trp_ald_popup_select_container" data-no-translation <?php echo ( isset( $_GET['trp-edit-translation'] ) && $_GET['trp-edit-translation'] == 'preview' ) ? 'data-trp-unpreviewable="trp-unpreviewable"' : '' ?>>
                <?php
                $current_language_preference = $trp_language_switcher->add_shortcode_preferences($shortcode_settings, $settings['default-language'], $published_languages[$settings['default-language']]);
                ?>

                <div class="trp-ls-shortcode-current-language" id="<?php echo esc_attr($settings["default-language"]); ?>" special-selector="trp_ald_popup_current_language" data-trp-ald-selected-language= "<?php echo esc_attr($settings["default-language"]); ?>">
                    <?php echo $current_language_preference; /* phpcs:ignore */ /* escaped inside the function that generates the output */ ?>
                </div>
                <div class="trp-ls-shortcode-language">
                    <div class="trp-ald-popup-select" id="<?php echo esc_attr($settings["default-language"]); ?>" data-trp-ald-selected-language = "<?php echo esc_attr($settings['default-language']);?>">
                        <?php echo $current_language_preference; /* phpcs:ignore */ /* escaped inside the function that generates the output */ ?>
                    </div>
                    <?php foreach ( $published_languages as $code => $name ){
                        if ($code != $settings['default-language']){
                            $language_preference = $trp_language_switcher->add_shortcode_preferences($shortcode_settings, $code, $name);
                            ?>
                            <div class="trp-ald-popup-select" id="<?php echo esc_attr($code); ?>" data-trp-ald-selected-language = "<?php echo esc_attr($code); ?>">
                                <?php echo $language_preference; /* phpcs:ignore */ /* escaped inside the function that generates the output */ ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            </div>


            <div class="trp_ald_button">
            <a href="<?php echo esc_url( $language_cookie_data['abs_home'] ); ?>" id="trp_ald_popup_change_language"></a>
            </div>
         </div>
        <a id="trp_ald_x_button_and_textarea" href="#"> <span id="trp_ald_x_button"></span><span id="trp_ald_x_button_textarea"></span></a>
    </div>
</div>
