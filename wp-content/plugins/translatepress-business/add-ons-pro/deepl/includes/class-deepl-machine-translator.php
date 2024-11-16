<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class TRP_IN_Deepl_Machine_Translator extends TRP_Machine_Translator {
    /**
     * Send request to Google Translation API
     *
     * @param string $source_language       Translate from language
     * @param string $language_code         Translate to language
     * @param array $strings_array          Array of string to translate
     *
     * @return array|WP_Error               Response
     */
    public function send_request( $source_language, $language_code, $strings_array, $formality = "default" ){
        /* build our translation request */

        $translation_request          = 'auth_key=' . $this->get_api_key();
        $translation_request          .= '&source_lang=' . $source_language;
        $translation_request          .= '&target_lang=' . $language_code;
        $translation_request          .= '&split_sentences=1';
        if($formality != "default") {
            $translation_request .= '&formality=' . $formality;
        }
        foreach ( $strings_array as $new_string ) {
            $translation_request .= '&text=' . rawurlencode( html_entity_decode( $new_string, ENT_QUOTES ) );
        }
        $referer = $this->get_referer();

        /* Due to url length restrictions we need so send a POST request faked as a GET request and send the strings in the body of the request and not in the URL */
        $response = wp_remote_post( "{$this->get_api_url()}/translate", array(
                'method'    => 'POST',
                'timeout'   => 45,
                'headers'   => [
                    'Referer'                => $referer,
                ],
                'body'      => $translation_request,
            )
        );

        return $response;
    }

    /**
     * Returns an array with the API provided translations of the $new_strings array.
     *
     * @param array $new_strings            array with the strings that need translation. The keys are the node number in the DOM so we need to preserve the m
     * @param string $target_language_code  wp language code of the language that we will be translating to. Not equal to the google language code
     * @param string $source_language_code  wp language code of the language that we will be translating from. Not equal to the google language code
     * @return array                        array with the translation strings and the preserved keys or an empty array if something went wrong
     */
    public function translate_array( $new_strings, $target_language_code, $source_language_code = null ){

        if ( $source_language_code == null )
            $source_language_code = $this->settings['default-language'];

        if( empty( $new_strings ) || !$this->verify_request_parameters( $target_language_code, $source_language_code ) )
            return [];

        $translated_strings = [];

        $source_language = apply_filters( 'trp_deepl_source_language', $this->machine_translation_codes[$source_language_code], $source_language_code, $target_language_code );
        $target_language = apply_filters( 'trp_deepl_target_language', $this->machine_translation_codes[$target_language_code], $source_language_code, $target_language_code );

        $formality = $this->get_request_formality_for_language($target_language_code);

        // split the strings array in 50 string parts;
        $new_strings_chunks = array_chunk( $new_strings, 50, true );

        foreach( $new_strings_chunks as $new_strings_chunk ){

            $response = $this->send_request( $source_language, $target_language, $new_strings_chunk, $formality );

            // this runs only if "Log machine translation queries." is set to Yes.
            $this->machine_translator_logger->log([
                'strings'     => serialize( $new_strings_chunk),
                'response'    => serialize( $response ),
                'lang_source' => $source_language,
                'lang_target' => $target_language,
            ]);

            if ( is_array( $response ) && ! is_wp_error( $response ) && isset( $response['response'] ) &&
                isset( $response['response']['code']) && $response['response']['code'] == 200 ) {

                $this->machine_translator_logger->count_towards_quota( $new_strings_chunk );

                $translation_response = json_decode( $response['body'] );

                /* if we have strings build the translation strings array and make sure we keep the original keys from $new_string */
                $translations = ( empty( $translation_response->translations ) )? array() : $translation_response->translations;
                $i            = 0;

                foreach( $new_strings_chunk as $key => $old_string ){

                    if( isset( $translations[$i] ) && ! empty( $translations[$i]->text ) ) {
                        $translated_strings[ $key ] = $translations[ $i ]->text;
                    }else{
                        /*  In some cases when API doesn't have a translation for a particular string,
                        translation is returned empty instead of same string. Setting original string as translation
                        prevents TP from keep trying to submit same string for translation endlessly.  */
                        $translated_strings[ $key ] = $old_string;
                    }

                    $i++;

                }

                if( $this->machine_translator_logger->quota_exceeded() )
                    break;

            }

        }

        return $translated_strings;
    }

    public function get_formality_setting_for_language($target_language_code){

        $formality = "default";

        if(isset($this->settings["translation-languages-formality-parameter"][ $target_language_code ])) {
            if ( $this->settings["translation-languages-formality-parameter"][ $target_language_code ] == 'informal'){
                $formality = "less";
            }else{
                if($this->settings["translation-languages-formality-parameter"][ $target_language_code ] == 'formal'){
                    $formality = "more";
                }
            }
        }

        return $formality;
    }

    public function get_languages_that_support_formality(){

        $formality_supported_languages = array();

        $data = get_option('trp_db_stored_data', array() );

        if (isset($data['trp_mt_supported_languages'][$this->settings['trp_machine_translation_settings']['translation-engine']]['formality-supported-languages'])){
            foreach ($this->settings['translation-languages'] as $language){
                if(array_key_exists($language, $data['trp_mt_supported_languages'][$this->settings['trp_machine_translation_settings']['translation-engine']]['formality-supported-languages'])){
                    $formality_supported_languages[$language] = $data['trp_mt_supported_languages'][$this->settings['trp_machine_translation_settings']['translation-engine']]['formality-supported-languages'][$language];
                }else{
                    $this->check_languages_availability($this->settings['translation-languages'], true);
                    $data = get_option('trp_db_stored_data', array());
                    $formality_supported_languages = $data['trp_mt_supported_languages'][$this->settings['trp_machine_translation_settings']['translation-engine']]['formality-supported-languages'];
                    break;
                }
            }

        }
        return $formality_supported_languages;
    }

    public function get_request_formality_for_language($target_language_code){

        $formality = $this->get_formality_setting_for_language($target_language_code);
        $formality_supported_languages = $this->get_languages_that_support_formality();

        if(isset($formality_supported_languages[$target_language_code]) && $formality_supported_languages[$target_language_code] == "true"){
            return $formality;
        }else{
            return 'default';
        }
    }

    public function check_formality() {

        $formality_supported_languages = [];
        $language_iso_codes = [];

        $request_url = "{$this->get_api_url()}/languages?type=target";

        $request_headers = [
            'Referer' => $this->get_referer(),
            'Authorization' => "DeepL-Auth-Key {$this->get_api_key()}"
        ];

        $deepl_response = wp_remote_post( $request_url, array(
                'method'  => 'POST',
                'timeout' => 45,
                'headers' => $request_headers,
            )
        );

        // Check for WP_Error
        if (is_wp_error($deepl_response)) {
            error_log('DeepL API request failed: ' . $deepl_response->get_error_message());
            return;
        }

        // Ensure response is an array and contains the response code
        if (!is_array($deepl_response) || !isset($deepl_response['response']['code'])) {
            error_log('Invalid response from DeepL API');
            return;
        }

        $deepl_response_code = $deepl_response['response']['code'];

        if ($deepl_response_code !== 200) {
            error_log('DeepL API request returned unexpected status code: ' . $deepl_response_code);
            return;
        }

        $all_languages         = $this->trp_languages->get_wp_languages();
        $supported_languages   = json_decode( wp_remote_retrieve_body( $deepl_response ) );
        $portuguese_variations = [
            (object) [
                'language'           => 'pt',
                'name'               => 'Portuguese',
                'supports_formality' =>  true
            ]
        ];

        $supported_languages = array_merge( $supported_languages, $portuguese_variations );

        foreach ( $all_languages as $language ){
            $language_iso_codes[ $language['language'] ] = reset( $language['iso'] );
        }

        // In some cases, the ISO codes provided by DeepL do not accurately match with our list - therefore, we match them manually
        $exceptions_map = [
            'EN-GB' => 'en_GB',
            'EN-US' => 'en_US'
        ];

        foreach ( $supported_languages as $supported_language ){
            if ( array_key_exists( $supported_language->language, $exceptions_map ) ){
                $formality_supported_languages[ $exceptions_map[$supported_language->language] ] = $supported_language->supports_formality ? 'true' : 'false';                continue;
            }

            $matched_languages = array_keys( $language_iso_codes, strtolower( $supported_language->language ) );

            if ($matched_languages) {
                foreach ($matched_languages as $matched_language) {
                    $formality_supported_languages[$matched_language] = $supported_language->supports_formality ? 'true' : 'false';
                }
            }
        }

        return apply_filters( 'trp_deepl_formality_languages', $formality_supported_languages );
    }

    /**
     * Send a test request to verify if the functionality is working
     */
    public function test_request(){

        return $this->send_request( 'en', 'es', [ 'Where are you from ?' ], 'less' );

    }

    public function get_api_key(){

        return isset( $this->settings['trp_machine_translation_settings'], $this->settings['trp_machine_translation_settings']['deepl-api-key'] ) ? $this->settings['trp_machine_translation_settings']['deepl-api-key'] : false;

    }


    public function get_supported_languages(){
        if ( $this->get_api_key() ) {

            $response = wp_remote_post( "{$this->get_api_url()}/languages", array(
                    'method'  => 'POST',
                    'timeout' => 45,
                    'headers' => [
                        'Referer' => $this->get_referer(),
                    ],
                    'body'    => 'auth_key=' . $this->get_api_key(),
                )
            );

            if ( is_array( $response ) && !is_wp_error( $response ) && isset( $response['response'] ) &&
                isset( $response['response']['code'] ) && $response['response']['code'] == 200 ) {
                $data                = json_decode( $response['body'] );
                $supported_languages = array();
                foreach ( $data as $data_entry ) {
                    $supported_languages[] = strtolower( $data_entry->language );
                }
                return apply_filters( 'trp_deepl_supported_languages', $supported_languages );
            }
        }

        return array();
    }

    public function get_engine_specific_language_codes($languages){

        $iso_translation_codes = $this->trp_languages->get_iso_codes($languages);
        $engine_specific_languages = array();
        foreach( $languages as $language ) {
            /* All combinations of source and target languages are supported.
            Target language code can be country specific. Source language code is not. So the source language code is used here.
            */
            $engine_specific_languages[] = apply_filters( 'trp_deepl_source_language', $iso_translation_codes[ $language ], $language, null );
        }
        return $engine_specific_languages;
    }

    public function get_api_url(){
       if( isset( $this->settings['trp_machine_translation_settings']['deepl-api-type'] ) && $this->settings['trp_machine_translation_settings']['deepl-api-type'] == 'free' )
           return 'https://api-free.deepl.com/v2';

       return 'https://api.deepl.com/v2';
    }

    public function check_api_key_validity() {

        $machine_translator = $this;
        $translation_engine = $this->settings['trp_machine_translation_settings']['translation-engine'];
        $api_key            = $machine_translator->get_api_key();

        $is_error       = false;
        $return_message = '';

        if ( 'deepl' === $translation_engine && $this->settings['trp_machine_translation_settings']['machine-translation'] === 'yes') {

            if ( isset( $this->correct_api_key ) && $this->correct_api_key != null ) {
                return $this->correct_api_key;
            }

            if ( empty( $api_key ) ) {
                $is_error       = true;
                $return_message = __( 'Please enter your DeepL API key.', 'translatepress-multilingual' );
            } else {
                // Perform test.
                $is_error = false;
                $response = $machine_translator->test_request();
                $code     = wp_remote_retrieve_response_code( $response );
                if ( 200 !== $code ) {

                    $translate_response = TRP_IN_DeepL::deepl_response_codes( $code );

                    $is_error       = true;
                    $return_message = $translate_response['message'];
                }
            }
            $this->correct_api_key = array(
                'message' => $return_message,
                'error'   => $is_error,
            );
        }

        return array(
            'message' => $return_message,
            'error'   => $is_error,
        );
    }

}
