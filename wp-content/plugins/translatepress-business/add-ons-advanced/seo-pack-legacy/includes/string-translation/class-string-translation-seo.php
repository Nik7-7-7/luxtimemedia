<?php


class TRP_IN_SP_String_Translation_SEO {

    public function add_string_translation_types( $string_types_config, $trp_string_translation ) {
        $option_based_strings = new TRP_IN_SP_Option_Based_Strings();
        $slugs_string_type    = array(
            'slugs' =>
                array(
                    'name'           => __( 'URL Slugs Translation', 'translatepress-multilingual' ),
                    'tab_name'       => __( 'Slugs', 'translatepress-multilingual' ),
                    'category_based' => true,
                    'categories'     => array(
                        'taxonomy-slug'       => array(
                            'name'                   => __( 'Taxonomy Slugs', 'translatepress-multilingual' ),
                            'search_name'            => __( 'Search Taxonomy Slugs', 'translatepress-multilingual' ),
                            'class_name_suffix'      => 'Taxonomy_Slug',
                            'plugin_path'            => TRP_IN_SP_PLUGIN_DIR,
                            'nonces'                 => $trp_string_translation->get_nonces_for_type( 'taxonomy-slug' ),
                            'save_nonce'             => wp_create_nonce( 'string_translation_save_strings_taxonomy-slug' ),
                            'table_columns'          => array(
                                'original'   => __( 'Taxonomy Slug', 'translatepress-multilingual' ),
                                'translated' => __( 'Translation', 'translatepress-multilingual' )
                            ),
                            'show_original_language' => false,
                            'filters'                => array()
                        ),
                        'term-slug'           => array(
                            'name'                   => __( 'Term Slugs', 'translatepress-multilingual' ),
                            'search_name'            => __( 'Search Term Slugs', 'translatepress-multilingual' ),
                            'class_name_suffix'      => 'Term_Slug',
                            'plugin_path'            => TRP_IN_SP_PLUGIN_DIR,
                            'nonces'                 => $trp_string_translation->get_nonces_for_type( 'term-slug' ),
                            'table_columns'          => array(
                                'original'   => __( 'Term Slug', 'translatepress-multilingual' ),
                                'translated' => __( 'Translation', 'translatepress-multilingual' ),
                                'taxonomy'   => __( 'Taxonomy', 'translatepress-multilingual' )
                            ),
                            'show_original_language' => false,
                            'filters'                => array(
                                'taxonomy' => array_merge(
                                    array( 'trp_default' => __( 'Filter by Taxonomy', 'translatepress-multilingual' ) ),
                                    $option_based_strings->get_public_slugs( 'taxonomies', true, array(), false )
                                )
                            )
                        ),
                        'postslug'            => array(
                            'name'                   => __( 'Post Slugs', 'translatepress-multilingual' ),
                            'search_name'            => __( 'Search Post Slugs', 'translatepress-multilingual' ),
                            'class_name_suffix'      => 'Post_Slug',
                            'plugin_path'            => TRP_IN_SP_PLUGIN_DIR,
                            'nonces'                 => $trp_string_translation->get_nonces_for_type( 'postslug' ),
                            'table_columns'          => array(
                                'id'         => __( 'Post ID', 'translatepress-multilingual' ),
                                'original'   => __( 'Post Slug', 'translatepress-multilingual' ),
                                'translated' => __( 'Translation', 'translatepress-multilingual' ),
                                'post_type'  => __( 'Post Type', 'translatepress-multilingual' )
                            ),
                            'show_original_language' => false,
                            'filters'                => array(
                                'post-type'   => array_merge(
                                    array( 'trp_default' => __( 'Filter by Post Type', 'translatepress-multilingual' ) ),
                                    $option_based_strings->get_public_slugs( 'post_types', true, array(), false )
                                ),
                                'post-status' => array_merge(
                                    array( 'publish' => __( 'Published', 'translatepress-multilingual' ) ),
                                    array( 'trp_any' => __( 'Any Post Status', 'translatepress-multilingual' ) ),
                                    get_post_statuses()
                                )
                            )
                        ),
                        'post-type-base-slug' => array(
                            'name'                   => __( 'Post Type Base Slugs', 'translatepress-multilingual' ),
                            'table_columns'          => array(
                                'original'   => __( 'Post Type Base Slug', 'translatepress-multilingual' ),
                                'translated' => __( 'Translation', 'translatepress-multilingual' )
                            ),
                            'show_original_language' => false,
                            'search_name'            => __( 'Search Post Type Base Slugs', 'translatepress-multilingual' ),
                            'class_name_suffix'      => 'Post_Type_Base_Slug',
                            'plugin_path'            => TRP_IN_SP_PLUGIN_DIR,
                            'nonces'                 => $trp_string_translation->get_nonces_for_type( 'post-type-base-slug' ),
                            'filters'                => array()
                        )
                    )
                )
        );

        if ( class_exists( 'WooCommerce' ) ) {
            $slugs_string_type['slugs']['categories']['woocommerce-slug'] = array(
                'name'                   => __( 'WooCommerce Slugs', 'translatepress-multilingual' ),
                'table_columns'          => array(
                    'original'   => __( 'WooCommerce Slug', 'translatepress-multilingual' ),
                    'translated' => __( 'Translation', 'translatepress-multilingual' )
                ),
                'show_original_language' => false,
                'search_name'            => __( 'Search WooCommerce Slugs', 'translatepress-multilingual' ),
                'class_name_suffix'      => 'WooCommerce_Slug',
                'plugin_path'            => TRP_IN_SP_PLUGIN_DIR,
                'nonces'                 => $trp_string_translation->get_nonces_for_type( 'woocommerce-slug' ),
                'filters'                => array()
            );

        }
        return $slugs_string_type + $string_types_config;
    }

    /**
     * Enable navigation tabs
     * Hooked to trp_editors_navigation
     *
     * @param $editors_navigation
     * @return array
     */
    public function enable_editors_navigation( $editors_navigation ){
        $editors_navigation['show'] = true;
        return $editors_navigation;
    }
}