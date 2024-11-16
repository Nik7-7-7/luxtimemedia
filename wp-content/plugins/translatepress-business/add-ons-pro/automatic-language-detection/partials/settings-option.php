<div class="advanced_settings_class ald_settings">
    <div><h2><?php esc_html_e( 'Automatic User Language Detection', 'translatepress-multilingual' ); ?></h2> </div><br>
    <div class='trp_advanced_flex_box'>
        <div class='trp_advanced_option_name'><b><?php esc_html_e( 'Method of language detection', 'translatepress-multilingual' ); ?></b> </div>
        <div class='trp_advanced_settings_align'>
            <select id="trp-ald-detection-method" name="trp_ald_settings[detection-method]" class="trp-select">
                <?php
                foreach ( $detection_methods as $value => $label ) {
                    ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $ald_settings['detection-method'], $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php
                }
                ?>
            </select>
            <p class="description">
                <?php echo wp_kses_post( __( "Select how the language should be detected for first time visitors.<br>The visitor's last displayed language will be remembered through cookies." , 'translatepress-multilingual' ) ); ?>
            </p>
            <?php echo $ip_warning_message;//phpcs:ignore  ?>
        </div>
    </div>
    <div class='trp_advanced_flex_box'>
        <div class='trp_advanced_option_name'><b><?php esc_html_e( 'Pop-up to notify the user of the detected language', 'translatepress-multilingual' ); ?></b> </div>
        <div class='trp_advanced_settings_align'>
            <input type="radio" id="trp-ald-popup_option" name="trp_ald_settings[popup_option]" value="popup" <?php if($setting_option['popup_option'] == 'popup') {?> checked <?php }?>>
            <label for="trp-ald-popup_option"><?php echo $popup_options['popup']// phpcs:ignore?></label>
            </input>
            <br>
            <br>
            <label style="padding-left: 24px"><?php esc_html_e('Select the type of pop-up you wish to appear.', 'translatepress-multilingual'); ?></label><br>
            <select id="trp-ald-popup_type" name="trp_ald_settings[popup_type]" class="trp-select" style="margin-left: 24px">
                <?php
                foreach ( $popup_type as $value => $label ) {
                    ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $ald_settings['popup_type'], $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php
                }
                ?>
            </select>
            <br>
            <br>
            <label style="padding-left: 24px"><?php esc_html_e('Write the text you wish to appear in the pop-up.', 'translatepress-multilingual'); ?></label><br>
            <textarea id="trp-popup-textarea" name="trp_ald_settings[popup_textarea]" style="height: auto; width: 500px; margin-left: 24px"><?php echo $setting_option['popup_textarea'] // phpcs:ignore?></textarea>
            <p class="description" style="padding-left: 24px">
                <?php echo wp_kses_post( __( "The same text is displayed in all languages. <br>A selecting language switcher will be appended to the pop-up. The detected language is pre-selected." , 'translatepress-multilingual' ) ); ?>
            </p>
            <br>
            <label style="padding-left: 24px"><?php esc_html_e('Write the text you wish to appear on the button.', 'translatepress-multilingual'); ?></label><br>
            <textarea id="trp-popup-textarea_button" name="trp_ald_settings[popup_textarea_button]" style="height: auto; width: 500px; margin-left: 24px"><?php echo $setting_option['popup_textarea_button'] // phpcs:ignore?></textarea>
            <br><br>
            <label style="padding-left: 24px"><?php esc_html_e('Write the text you wish to appear on the close button. Leave empty for just the close button.', 'translatepress-multilingual'); ?></label><br>
            <textarea id="trp-popup-textarea_close_button" name="trp_ald_settings[popup_textarea_close_button]" style="height: auto; width: 500px; margin-left: 24px"><?php echo $setting_option['popup_textarea_close_button'] // phpcs:ignore?></textarea>
            <br><br>
            <div class="redirect_directly_option" <?php if ( isset($popup_options['no_popup']) ) { ?> >
                <input type="radio" id="trp-ald-popup_option_no_popup" name="trp_ald_settings[popup_option]" value="no_popup" <?php if($setting_option['popup_option'] == 'no_popup') {?> checked <?php }?>>
                <label for="trp-ald-popup_option_no_popup"><?php echo $popup_options['no_popup'] //phpcs:ignore?></label>
                </input>

                <br>
                <p class="description">
                    <?php echo wp_kses_post( __( "Choose if you want the user to be redirected directly.<br>* Not recommended because it may cause indexing issues for search engines." , 'translatepress-multilingual' ) ); ?>
                </p>
                <?php } ?>
            </div>
            <br>
        </div>
    </div>
</div>
</div>