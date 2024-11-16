<?php
if ( !class_exists( 'TRP_String_Translation_API_WooCommerce_Slug' ) ) {
    class TRP_String_Translation_API_WooCommerce_Slug {
        protected $config;
        protected $helper;
        protected $option_based_strings;
        protected $settings;
        protected $slug_query;
        protected $editor_actions;

        public function __construct( $settings ) {
            $this->settings       = $settings;
            $this->helper         = new TRP_String_Translation_Helper();
            $this->slug_query     = new TRP_Slug_Query();
            $this->editor_actions = new TRP_IN_SP_Editor_Actions( $this->slug_query, $settings );
        }

        public function get_strings() {
            $this->helper->check_ajax( 'woocommerce-slug', 'get' );

            $woo_tax_slugs = array();

            $woocommerce_permalink_options = get_option( 'woocommerce_permalinks', false );
            $woo_cpt_slug                  = $woocommerce_permalink_options['product_base'];
            $cpt_slugs_components          = explode( '/', $woo_cpt_slug );
            foreach ( $cpt_slugs_components as $key => $component ) {
                if ( empty( $component ) || $component == '%product_cat%' ) {
                    unset( $cpt_slugs_components[ $key ] );
                }
            }

            $translated_slugs = $this->slug_query->get_translated_slugs_from_original( array_values( $cpt_slugs_components ) );

            $translationsArray = new TRP_String_Translation_Array( array_values( $cpt_slugs_components ), $translated_slugs, 'post-type-base' );
            $translationsArray = $translationsArray->get_formatted_translations_array();

            foreach ( $translationsArray as $key => $array ) {
                $dictionary_by_original[ $key ] = [
                    'original'          => $key,
                    'type'              => 'post-type-base',
                    'translationsArray' => $array
                ];
            }

            $woo_tax_slugs[]  = $woocommerce_permalink_options['category_base'];
            $woo_tax_slugs[]  = $woocommerce_permalink_options['tag_base'];
            $translated_slugs = $this->slug_query->get_translated_slugs_from_original( $woo_tax_slugs );

            $translationsArray = new TRP_String_Translation_Array( array_values( $woo_tax_slugs ), $translated_slugs, 'taxonomy' );
            $translationsArray = $translationsArray->get_formatted_translations_array();

            foreach ( $translationsArray as $key => $array ) {
                $dictionary_by_original[ $key ] = [
                    'original'          => $key,
                    'type'              => 'taxonomy',
                    'translationsArray' => $array
                ];
            }

            $return = array(
                'dictionary' => $dictionary_by_original,
                'totalItems' => count( $dictionary_by_original )
            );

            echo trp_safe_json_encode( $return );//phpcs:ignore
            wp_die();
        }

        // saving will go through cpt and taxonomies save functions
        public function save_strings() {

        }
    }
}
