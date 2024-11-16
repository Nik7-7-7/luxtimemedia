<tr>
    <th scope="row"> <?php esc_html_e( 'All Languages', 'translatepress-multilingual' ) ?> </th>
    <td>
        <?php
        $machine_translation_settings = $trp_settings->get_setting('trp_machine_translation_settings');
        $show_formality               = isset( $machine_translation_settings ) && $machine_translation_settings['machine-translation'] === 'yes' && ( $machine_translation_settings['translation-engine'] === 'deepl' || $machine_translation_settings['translation-engine'] === 'mtapi' );
        ?>
        <table id="trp-languages-table">
            <thead>
                <tr>
                    <th colspan="2"><?php esc_html_e( 'Language', 'translatepress-multilingual' ); ?></th>
                    <?php if( $show_formality ){ ?>
                        <th><?php esc_html_e( 'Formality', 'translatepress-multilingual' ); ?></th>
                    <?php } ?>
                    <th><?php esc_html_e( 'Code', 'translatepress-multilingual' ); ?></th>
                    <th><?php esc_html_e( 'Slug', 'translatepress-multilingual' ); ?></th>
                    <th><?php esc_html_e( 'Active*', 'translatepress-multilingual' ); ?></th>
                </tr>
            </thead>
            <tbody id="trp-sortable-languages">

            <?php
            $formality_array = array(
                    'default' => __('Default', 'translatepress-multilingual'),
                    'formal'  => __('Formal', 'translatepress-multilingual'),
                    'informal'=> __('Informal', 'translatepress-multilingual')
            );

            $data = get_option('trp_db_stored_data', array() );
            $formality_supported_languages = $show_formality && isset( $data['trp_mt_supported_languages'][ $this->settings['trp_machine_translation_settings']['translation-engine']] ) ? $data['trp_mt_supported_languages'][ $this->settings['trp_machine_translation_settings']['translation-engine']]['formality-supported-languages'] : null;
            ?>
            <?php foreach ( $this->settings['translation-languages'] as $key=>$selected_language_code ){
                $default_language            = ( $selected_language_code == $this->settings['default-language'] );
                $language_supports_formality = isset( $formality_supported_languages ) ? isset( $formality_supported_languages[$selected_language_code] ) && $formality_supported_languages[$selected_language_code]  === 'true' : null;
                $stripped_formal_language    = isset( $formality_supported_languages[ str_replace( ['_formal','_informal'],'', $selected_language_code ) ] ) ? $formality_supported_languages[ str_replace( ['_formal','_informal'],'', $selected_language_code ) ] : false; ?>
                <tr class="trp-language">
                    <td><span class="trp-sortable-handle"></span></td>
                    <td>
                        <select name="trp_settings[translation-languages][]" class="trp-select2 trp-translation-language" <?php echo ( $default_language ) ? 'disabled' : '' ?>>
	                        <?php foreach( $languages as $language_code => $language_name ){ ?>
                                <option title="<?php echo esc_attr( $language_code ); ?>" value="<?php echo esc_attr( $language_code ); ?>" <?php echo ( $language_code == $selected_language_code ) ? 'selected' : ''; ?>>
			                        <?php echo ( $default_language ) ? 'Default: ' : ''; ?>
			                        <?php echo esc_html( $language_name ); ?>
                                </option>
	                        <?php }?>
                        </select>
                    </td>
                    <?php if( $show_formality ){ ?>
                        <td>
                            <select name="trp_settings[translation-languages-formality][]" class="trp-translation-language-formality <?php if ( !$language_supports_formality && !$stripped_formal_language || $stripped_formal_language === 'false' ) echo 'trp-formality-disabled'; ?>">
                                <?php
                                foreach ( $formality_array as $value => $label ) {
                                    ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php echo ( isset($this->settings['translation-languages-formality-parameter'][$selected_language_code]) && $value == $this->settings['translation-languages-formality-parameter'][$selected_language_code] ) ? 'selected' : ''; ?>><?php echo esc_html( $label ); ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                    <?php } ?>
                    <td>
                        <input class="trp-language-code trp-code-slug" type="text" disabled value="<?php echo esc_html( $selected_language_code ); ?>">
                    </td>
                    <td>
                        <input class="trp-language-slug  trp-code-slug" name="trp_settings[url-slugs][<?php echo esc_attr( $selected_language_code ) ?>]" type="text" style="text-transform: lowercase;" value="<?php echo esc_attr( $this->url_converter->get_url_slug( $selected_language_code, false ) ); ?>">
                    </td>
                    <td align="center">
                        <input type="checkbox" class="trp-translation-published" name="trp_settings[publish-languages][]" value="<?php echo esc_attr( $selected_language_code ); ?>" <?php echo ( in_array( $selected_language_code, $this->settings['publish-languages'] ) ) ? 'checked ' : ''; echo ( $default_language ) ? 'disabled ' : ''; ?> />
                        <?php if ( $default_language ) { ?>
                                <input type="hidden" class="trp-hidden-default-language" name="trp_settings[translation-languages][]" value="<?php echo esc_attr( $selected_language_code );?>" />
                                <input type="hidden" class="trp-hidden-default-language" name="trp_settings[publish-languages][]" value="<?php echo esc_attr( $selected_language_code );?>" />
                        <?php } ?>
                    </td>
                    <td>
                        <a class="trp-remove-language" style=" <?php echo ( $default_language ) ? 'display:none' : '' ?>" data-confirm-message="<?php esc_html_e( 'Are you sure you want to remove this language?', 'translatepress-multilingual' ); ?>"><?php esc_html_e( 'Remove', 'translatepress-multilingual' ); ?></a>
                    </td>
                </tr>
            <?php }?>
            </tbody>
        </table>
        <div id="trp-new-language">
            <select id="trp-select-language" class="trp-select2 trp-translation-language" >
                <?php
                $trp = TRP_Translate_Press::get_trp_instance();
                $trp_languages = $trp->get_component('languages');
                $wp_languages = $trp_languages->get_wp_languages();
                ?>
                <option value=""><?php esc_html_e( 'Choose...', 'translatepress-multilingual' );?></option>
                <?php foreach( $languages as $language_code => $language_name ){ ?>

                <?php if(isset($wp_languages[$language_code]['is_custom_language']) && $wp_languages[$language_code]['is_custom_language'] === true){?>
                   <optgroup label="<?php echo esc_html__('Custom Languages', 'translatepress-multilingual'); ?>">
                <?php break;?>
                       <?php } ?>
                       <?php } ?>
                <?php foreach( $languages as $language_code => $language_name ){ ?>

                    <?php if(isset($wp_languages[$language_code]['is_custom_language']) && $wp_languages[$language_code]['is_custom_language'] === true){ ?>
                        <option title="<?php echo esc_attr( $language_code ); ?>" value="<?php echo esc_attr( $language_code ); ?>">
                            <?php echo esc_html( $language_name ); ?>
                        </option>

                    <?php } ?>

                <?php }?>
                   </optgroup>
                <?php foreach( $languages as $language_code => $language_name ){ ?>
                <?php if(!isset($wp_languages[$language_code]['is_custom_language']) || (isset($wp_languages[$language_code]['is_custom_language']) && $wp_languages[$language_code]['is_custom_language'] !== true)){ ?>
                    <option title="<?php echo esc_attr( $language_code ); ?>" value="<?php echo esc_attr( $language_code ); ?>">
                        <?php echo esc_html( $language_name ); ?>

                    </option>
                    <?php } ?>
                <?php }?>
            </select>
            <button type="button" id="trp-add-language" class="button-secondary"><?php esc_html_e( 'Add', 'translatepress-multilingual' );?></button>
        </div>
        <p class="trp-add-language-error-container warning" style="display: none;"></p>
        <p class="description">
            <?php echo wp_kses( __( 'Select the languages you wish to make your website available in.', 'translatepress-multilingual' ), array() ); ?>
            <?php if( $show_formality ){ echo wp_kses( sprintf(__( '<br>The Formality field is used by Automatic Translation to decide whether the translated text should lean towards formal or informal language. For now, it is supported only for a few languages and only by <a href="%s" target="_blank">DeepL</a>.', 'translatepress-multilingual' ), esc_url('https://www.deepl.com/docs-api/translating-text/') ), array( 'a' => array( 'href' => array(), 'target' =>array(), 'title' => array()), 'br' => array()) ); } ?>
            <?php echo wp_kses( __( '<br>* The inactive languages will still be visible and active for the admin. For other users they won\'t be visible in the language switchers and won\'t be accessible either.', 'translatepress-multilingual' ), array( 'br' => array() ) ); ?>
        </p>
    </td>
</tr>
