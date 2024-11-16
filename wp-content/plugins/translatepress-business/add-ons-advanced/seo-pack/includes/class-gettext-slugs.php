<?php

/**
 * Translate WooCommerce Slugs. Can be extended for other similarly defined gettext slugs
 */
class TRP_IN_SP_Gettext_Slugs {

    protected $loader;
    protected $slug_query;
    protected $slug_manager;
    protected $settings;
    protected $gettext_slugs;
    protected $migration_completed;

    public function __construct( $settings, $slug_manager ) {
        $this->settings   = $settings;
        $this->slug_query = new TRP_Slug_Query();
        $this->slug_manager = $slug_manager;

        $was_data_migration_completed = get_option( 'trp_migrate_old_slug_to_new_parent_and_translate_slug_table_term_meta_284', 'not_set' );
        $this->migration_completed = ( $was_data_migration_completed == 'not_set' || $was_data_migration_completed == 'yes' );
    }

    /**
     * It's cached. It returns translations too
     *
     * @param $break_cache
     * @return mixed|null
     */
    public function get_gettext_slugs( $break_cache = false ) {
        if ( !$this->gettext_slugs || $break_cache ) {
            $this->gettext_slugs = array();
            if ( class_exists( 'WooCommerce' ) ) {
                $this->gettext_slugs['woocommerce'] = array(
                    'context' => 'slug',
                    'slugs'   => array( 'product-category', 'product-tag', 'product' )
                );
            }
            $this->gettext_slugs = apply_filters( 'trp_seo_pack_pre_get_gettext_slugs', $this->gettext_slugs );


            foreach ( $this->gettext_slugs as $slugs_domain => $slugs_details ) {
                $default_language_slugs = array();
                foreach ( $slugs_details['slugs'] as $slug ) {
                    $trp_x                           = trp_x( $slug, $slugs_details['context'], $slugs_domain, $this->settings['default-language'] );
                    $default_language_slugs[ $slug ] = ( $trp_x ) ? $trp_x : $slug;
                }
                foreach ( $this->settings['translation-languages'] as $language ) {
                    if ( $language == $this->settings['default-language'] ) {
                        continue;
                    }

                    // function takes care of getting translations from old places if migration was not successful
                    $this->gettext_slugs[ $slugs_domain ]['translations'][ $language ] = $this->slug_manager->get_slugs_pairs_based_on_language( $default_language_slugs, $this->settings['default-language'], $language );
                    $this->gettext_slugs[ $slugs_domain ]['default-language-slugs']    = $default_language_slugs;
                }
            }

            $this->gettext_slugs = apply_filters( 'trp_seo_pack_post_get_gettext_slugs', $this->gettext_slugs );
        }
        return $this->gettext_slugs;
    }

    /**
     * Force slugs in default language regardless of current language.
     *
     * If new po/mo translations are detected for strings that don't already have a translation,
     * add a flag in trp_in_sp_add_gettext_slugs option so that the translations will
     * be added in a different hook. New translations are saved in the array but only used for setting flag.
     *
     * @param $translation
     * @param $text
     * @param $context
     * @param $domain
     * @return mixed
     */
    public function keep_default_slugs( $translation, $text, $context, $domain ) {
        global $TRP_LANGUAGE;
        if ( $TRP_LANGUAGE !== $this->settings['default-language'] ) {
            $slugs = $this->get_gettext_slugs();
            foreach ( $slugs as $slugs_domain => $slugs_details ) {
                if ( $context === $slugs_details['context'] && $domain === $slugs_domain && in_array( $text, $slugs_details['slugs'] ) ) {
                    if ( $this->migration_completed && empty( $slugs_details['translations'][ $TRP_LANGUAGE ][ $slugs_details['default-language-slugs'][ $text ] ] ) && $translation != $text ) {

                        $this->gettext_slugs[ $domain ]['translations'][ $TRP_LANGUAGE ][ $slugs_details['default-language-slugs'][ $text ] ] = $translation;
                        $add_gettext_slugs = get_option( 'trp_in_sp_add_gettext_slugs', 'not_set' );
                        if ( $add_gettext_slugs !== 'todo' ) {
                            update_option( 'trp_in_sp_add_gettext_slugs', 'todo' );
                        }
                    }
                    return $slugs_details['default-language-slugs'][ $text ];
                }
            }
        }
        return $translation;
    }

    /**
     * On new installs, insert existing translations for secondary languages in TP slug tables. On existing installs,
     * only do this if migration was successful. Only adds translations from po/mo files if translations don't already
     * exist in DB.
     *
     * These translations may not be fully used, depending on the Woo Permalinks settings i.e. if they have
     * /shop/%product_cat%/ then translations for 'product' will not be used, but it will be there in the DB
     *
     *
     * @return void
     */
    public function add_slug_translation_in_db() {
        $add_gettext_slugs = get_option( 'trp_in_sp_add_gettext_slugs', 'not_set' );

        if ( $add_gettext_slugs == 'todo' && $this->migration_completed ) {
            update_option( 'trp_in_sp_add_gettext_slugs', 'done' );
            $gettext_slugs = $this->get_gettext_slugs( true );

            foreach ( $gettext_slugs as $slugs_domain => $slugs_details ) {

                foreach ( $this->settings['translation-languages'] as $language ) {

                    $insert_slugs = array();
                    if ( $language == $this->settings['default-language'] ) {
                        continue;
                    }
                    foreach ( $slugs_details['slugs'] as $slug ) {
                        if ( !empty( $slugs_details['translations'][ $language ][ $slugs_details['default-language-slugs'] [ $slug ] ] ) ) {
                            continue;
                        }

                        $item['original']   = $slugs_details['default-language-slugs'][ $slug ];
                        $item['status'] = '2';

                        // Search for translation in language files. If not found, search for translation in gettext
                        // tables where automatic/manual translation will be stored
                        $item['translated'] = trp_x( $slug, $slugs_details['context'], $slugs_domain, $language );
                        if ( empty( $item['translated'] ) || $item['translated'] === $slug ) {
                            $trp                 = TRP_Translate_Press::get_trp_instance();
                            $trp_query           = $trp->get_component( 'query' );
                            $translated_gettexts = $trp_query->get_gettext_string_rows_by_original( array( $slug ), $language );
                            if ( !empty( $translated_gettexts) ) {
                                foreach($translated_gettexts as $gettext) {
                                    if ( $gettext['original'] === $slug &&
                                        $gettext['domain'] === $slugs_domain &&
                                        $gettext['context'] === $slugs_details['context'] &&
                                        !empty( $gettext['translated'] )
                                    ) {
                                        $item['translated'] = $gettext['translated'];
                                        $item['status'] = $gettext['status'];
                                    }
                                }
                            }
                            if ( empty( $item['translated'] ) ) {
                                continue;
                            }
                        }


                        // 'type' is not used for anything other than debugging
                        if ( $slugs_domain == 'woocommerce' ) {
                            if ( $slug == 'product-category' || $slug == 'product-tag' ) {
                                $item['type'] = 'taxonomy';
                            } elseif ( $slug == 'product' ) {
                                $item['type'] = 'post-type-base';
                            }
                        }
                        $item['type']   = apply_filters( 'trp_seo_pack_set_type_slug_for_gettext_slug', $item['type'], $slugs_domain, $slug, $slugs_details );
                        $insert_slugs[] = $item;
                    }

                    $this->slug_query->insert_slugs( $insert_slugs, $language );
                }
            }


            // call again in order to get translated ones too. In case keep_default_slugs gets called after this function
            $this->get_gettext_slugs( true );
        }
    }
}
