<?php

/** Functions useful for cpt slugs and taxonomy slugs */
class TRP_IN_SP_Option_Based_Strings {

    public function get_public_slugs( $type, $include_labels = false, $include_items = array(), $only_with_slugs = true ) {
        $exclude_array = apply_filters( 'trp_exclude_' . $type . '_from_translation', array() );
        $slugs         = call_user_func( 'get_' . $type, array(), 'objects' );
        $return        = array();
        foreach ( $slugs as $item ) {
            if ( ( count( $include_items ) == 0 || in_array( $item->name, $include_items ) ) &&
                $item->public === true &&
                ( !$only_with_slugs || ( $item->publicly_queryable === true && $item->rewrite !== false && isset( $item->rewrite['slug'] ) && !in_array( $item->rewrite['slug'], $exclude_array ) ) )
            ) {
                if ( $include_labels ) {
                    $return[ $item->name ] = trim( $item->label, '\\/');
                } else {
                    $return[] = ( $only_with_slugs ) ? trim( $item->rewrite['slug'], '\\/') : trim( $item->name, '\\/');
                }
            }
        }
        return apply_filters( 'trp_to_translate_' . $type . '_slugs_array', $return, $type, $include_labels );
    }

    /**
     * Slugs for product-category, product-tag and product might have been edited in the Permalinks section of admin and we want to return their new values.
     * @return array
     */
    public function get_woocommerce_actual_slugs(){
        $woo_product_slugs  = $this->get_public_slugs('post_types', false, apply_filters('trp_get_woocommerce_cpt', array('product')));
        $woo_tax_slugs      = $this->get_public_slugs('taxonomies', false, apply_filters('trp_get_woocommerce_taxonomies', array('product_cat', 'product_tag')));
        $slugs              = array_merge( $woo_product_slugs, $woo_tax_slugs );
        $return             = array();
        foreach ( $slugs as $slug ){
            $return[] = trim($slug, '/\\');
        }
        return $return;
    }

    public function get_strings_for_option_based_slug( $type, $option_name, $all_slugs ) {
        $trp                = TRP_Translate_Press::get_trp_instance();
        $string_translation = $trp->get_component( 'string_translation' );
        $trp_query          = $trp->get_component( 'query' );
        $config             = $string_translation->get_configuration_options();
        $trp_settings       = $trp->get_component( 'settings' );
        $settings           = $trp_settings->get_settings();
        $helper             = new TRP_String_Translation_Helper();

        $dictionary_by_original = get_option( $option_name, array() );
        $found_inactive_slug    = false;

        // convert string array to associative array in the format array['slug'] = 'slug'
        $associative_all_slugs = array();
        foreach ( $all_slugs as $slug ) {
            $associative_all_slugs[ $slug ] = $slug;
        }
        $all_slugs = $associative_all_slugs;

        foreach ( $dictionary_by_original as $key => $entry ) {
            if ( isset( $all_slugs[ $entry['original'] ] ) ) {
                // found slug, don't add it again in the next foreach
                unset( $all_slugs[ $entry['original'] ] );

                // make sure all languages have arrays, even those added later
                foreach( $settings['translation-languages'] as $language ){
                    if ( $settings['default-language'] === $language ) {
                        continue;
                    }

                    if ( !isset( $dictionary_by_original[$key]['translationsArray'][$language] ) ){
                        $dictionary_by_original[$key]['translationsArray'][ $language ] = array(
                            'editedTranslation' => '',
                            'translated'        => '',
                            'status'            => $trp_query->get_constant_not_translated(),
                            'id'                => $dictionary_by_original[$key]['original'],
                        );
                    }
                }
            } else {
                // found a previously detected slug that no longer exists
                $found_inactive_slug = true;
                unset( $dictionary_by_original[ $key ] );
            }

        }

        if ( $found_inactive_slug ) {
            $dictionary_by_original = array_values( $dictionary_by_original );
        }

        if ( !empty ( $all_slugs ) ) {
            // add to dictionary all the newly found all_slugs
            foreach ( $all_slugs as $slug ) {
                $translationsArray = array();
                foreach ( $settings['translation-languages'] as $language ) {
                    if ( $settings['default-language'] === $language ) {
                        continue;
                    }
                    $translationsArray[ $language ] = array(
                        'editedTranslation' => '',
                        'translated'        => '',
                        'status'            => $trp_query->get_constant_not_translated(),
                        'id'                => $slug,
                    );
                }

                $dictionary_by_original[ $slug ] = array(
                    'original'          => $slug,
                    'type'              => $type,
                    'translationsArray' => $translationsArray
                );
            }
        }


        $sanitized_args      = $helper->get_sanitized_query_args( $type );
        $returned_dictionary = array();

        // order and orderby
        if ( !empty( $sanitized_args['order'] ) && !empty( $sanitized_args['orderby'] ) ) {
            $orderby_array    = array_column( $dictionary_by_original, $sanitized_args['orderby'] );
            $sort_asc_or_desc = ( $sanitized_args['order'] === 'desc' ) ? SORT_DESC : SORT_ASC;
            array_multisort( $orderby_array, $sort_asc_or_desc, $dictionary_by_original );
        }

        $items_found = 0;
        $upper_limit = $sanitized_args['page'] * $config['items_per_page'];
        $lower_limit = ( $sanitized_args['page'] - 1 ) * $config['items_per_page'];

        // filter the dictionary according to the requested params
        foreach ( $dictionary_by_original as $key => $entry ) {

            // if the search key is not set, consider every item
            if ( empty( $sanitized_args['s'] ) || strpos( $entry['original'], $sanitized_args['s'] ) !== false ) {
                foreach ( $entry['translationsArray'] as $language => $item ) {

                    // if the language is not selected, look for the status in all the languages
                    if ( empty( $sanitized_args['language'] ) || $language === $sanitized_args['language'] ) {
                        if ( empty( $sanitized_args['status'] ) || in_array( $item['status'], $sanitized_args['status'] ) ) {

                            // only keep the entries according to the page number requested
                            if ( $lower_limit <= $items_found && $items_found < $upper_limit ) {
                                $returned_dictionary[] = $entry;
                            }

                            // only add entry once
                            $items_found++;
                            break;
                        }
                    }
                }
            }
        }

        return array(
            'dictionary' => $returned_dictionary,
            'totalItems' => $items_found
        );
    }

    public function save_strings_for_option_based_slug( $type, $option_name, $strings = array() ) {
        $trp          = TRP_Translate_Press::get_trp_instance();
        $trp_query    = $trp->get_component( 'query' );
        $trp_settings = $trp->get_component( 'settings' );
        $settings     = $trp_settings->get_settings();

        //initially made to work through ajax, added an optional parameter that enables the function to be used without
        if( empty( $strings ) )
            $all_strings  = json_decode( stripslashes( $_POST['strings'] ), true ); //phpcs:ignore
        else
            $all_strings = $strings;

        $translations = $original_translations =  get_option( $option_name, array() );

        foreach ($translations as $key=>$translation){
            $new_key = $key;
            $key = trim( $key, '\\/');
            $translations[ $key ] = $translation;
            if( $key !== $new_key ) {
                unset( $translations[ $new_key ] );
            }
        }

        $update_slugs = array();
        foreach ( $all_strings as $language => $strings_in_language ) {
            if ( in_array( $language, $settings['translation-languages'] ) && $language != $settings['default-language'] ) {
                foreach ( $strings_in_language as $string ) {
              
                    if ( isset( $string['id'] ) && isset( $string['translated'] ) ) {
                        $string['id'] = trim( $string['id'], '\\/' );
                        if ( $string['translated'] !== '' ) {
                            $string['translated'] = urldecode($string['translated']);
                            $string['translated']   = $this->get_unique_string_for_language( $string['translated'], $string['id'], $translations, $language );
                            $string['translated']   = trim( $string['translated'], '\\/');

                        }
                        // used to return the sanitized values through the ajax request
                        $update_slugs [$language][] = array(
                            'id'         => $string['id'],
                            'translated' => $string['translated'],
                            'status'     => (int) $string['status']
                        );
                        // prepare entry in db
                        $string['editedTranslation'] = $string['translated'];
                        $string['status']            = (int)$string['status'];


                        if ( isset( $translations[ $string['id'] ] ) && isset( $translations[ $string['id'] ]['translationsArray'] ) ) {
                            // dictionary already has an entry for this slug
                            $translations[ $string['id'] ]['translationsArray'][ $language ] = $string;
                        } else {
                            // dictionary from db does not contain this string. Add a correct entry for all languages
                            $translationsArray = array();

                            foreach ( $settings['translation-languages'] as $translationLanguage ) {
                                if ( $settings['default-language'] === $translationLanguage ) {
                                    continue;
                                }
                                if ( $translationLanguage === $language ) {
                                    $translationsArray[ $translationLanguage ] = $string;
                                } else {
                                    $translationsArray[ $translationLanguage ] = array(
                                        'editedTranslation' => '',
                                        'translated'        => '',
                                        'status'            => $trp_query->get_constant_not_translated(),
                                        'id'                => $string['id'],
                                    );
                                };
                            }

                            $string ['original'] = trim($string['id'], '\\/');
                            $translations[ $string['id'] ] = array(
                                'original'          => $string['id'],
                                'type'              => $type,
                                'translationsArray' => $translationsArray
                            );
                        }
                    }
                }
            }
        }
        do_action( 'trp_before_based_slug_save', $option_name, $type, $translations, $all_strings, $original_translations );
        update_option( $option_name, $translations );

        //initially made to work through ajax, added an optional parameter that enables the function to be used without
        if( empty( $strings ) ) {
            echo trp_safe_json_encode( $update_slugs );//phpcs:ignore
            wp_die();
        }
    }

    public function get_unique_string_for_language( $sanitized_string, $id, $translations, $language ) {

        //allow same translation as original
        if( $id === $sanitized_string )
            return $sanitized_string;

        $suffix      = 2;
        $string_base = $sanitized_string;

        do {
            $translation_already_exists = false;
            foreach ( $translations as $key => $entry ) {
                if ( ( $key !== $id ) && ( isset($entry['translationsArray'][ $language ]['translated']) && $entry['translationsArray'][ $language ]['translated'] === $sanitized_string ) || taxonomy_exists( $sanitized_string ) || post_type_exists( $sanitized_string ) ) {
                    $translation_already_exists = true;
                    break;
                }
            }

            if ( $translation_already_exists ) {
                $sanitized_string = $string_base . '-' . $suffix;
                $suffix++;
            }
        } while ( $translation_already_exists );

        return $sanitized_string;
    }

}