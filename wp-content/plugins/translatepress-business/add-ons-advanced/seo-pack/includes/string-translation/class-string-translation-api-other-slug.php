<?php
if( !class_exists('TRP_String_Translation_API_Other_Slug') ) {
    class TRP_String_Translation_API_Other_Slug
    {
        protected $type = 'other-slug';
        protected $config;
        protected $helper;
        protected $settings;
        protected $slug_query;
        protected $editor_actions;

        public function __construct($settings)
        {
            $this->settings = $settings;
            $this->helper = new TRP_String_Translation_Helper();
            $this->slug_query = new TRP_Slug_Query();
            $this->editor_actions = new TRP_IN_SP_Editor_Actions( $this->slug_query, $settings );
        }

        public function get_strings()
        {
            $this->helper->check_ajax($this->type, 'get');
            $trp = TRP_Translate_Press::get_trp_instance();
            $string_translation = $trp->get_component('string_translation');
            $config = $string_translation->get_configuration_options();
            $sanitized_args = $this->helper->get_sanitized_query_args($this->type);
            $dictionary_by_original = [];

            $pagination_array = [
                'limit'     => $config['items_per_page'],
                'offset'    => ( $sanitized_args['page'] - 1 ) * $config['items_per_page'],
            ];

            $query_args = [
                'slug_type' => 'other'
            ];

            // translation status filter
            if ( !empty( $sanitized_args['status'] ) ) {
                $query_args['status'] = $sanitized_args['status'];
            }

            // order and orderby
            if ( !empty( $sanitized_args['orderby'] ) ) {
                if ( $sanitized_args['orderby'] === 'original' ) {
                    $query_args['order_by'] = 'original';
                }

                if ( $sanitized_args['orderby'] === 'id' ) {
                    $query_args['order_by'] = 'id';
                }

                $query_args['order'] = $sanitized_args['order'];
            }

            if ( !empty ( $sanitized_args['language'] ) ){
                $query_args['language'] = $sanitized_args['language'];
            }

            // search filter
            if ( !empty( $sanitized_args['s'] ) ) {
                $query_args['search'] = $sanitized_args['s'];
            }

            // get total items before pagination
            $found_items = $this->slug_query->get_original_slugs_count( $query_args );

            $query_args = array_merge( $query_args, $pagination_array );

            $query_args = apply_filters('trp_string_translation_query_args_' . $this->type, $query_args, $sanitized_args);

            // query for needed strings
            $resulted_wp_query = $this->slug_query->get_original_slugs( $query_args );
            $original_slugs    = [];

            foreach ( $resulted_wp_query as $translation ){
                $original_slugs[] = $translation['original'];
            }

            // Since we perform an inner join, one entry might appear multiple times - due to having translations for multiple languages
            $original_slugs = array_unique( $original_slugs );

            $translated_slugs = $this->slug_query->get_translated_slugs_from_original( $original_slugs );

            // construct dictionary by original
            $translationsArrays = new TRP_String_Translation_Array( $original_slugs, $translated_slugs, 'other' );
            $formatted_array = $translationsArrays->get_formatted_translations_array();
            $translationsArrays = $formatted_array;

            if ( $original_slugs  && count( $original_slugs ) > 0 ) {

                foreach ( $original_slugs as $slug ) {
                    $dictionary_by_original[] = array(
                        'original' => $slug,
                        'type' => $this->type,
                        'translationsArray' => $translationsArrays[$slug]
                    );
                }
            }

            echo trp_safe_json_encode(array( //phpcs:ignore
                                             'dictionary' => $dictionary_by_original,
                                             'totalItems' => $found_items
            ));
            wp_die();
        }



        public function save_strings()
        {
            $this->helper->check_ajax( $this->type, 'save' );
            $update_slugs = [];

            if ( !empty( $_POST['strings'] ) ) {
                $slugs = json_decode( stripslashes( $_POST['strings'] ) ); //phpcs:ignore

                $update_slugs = $this->editor_actions->save_slugs( $slugs, $this->type );
            }

            echo trp_safe_json_encode( $update_slugs ); //phpcs:ignore
            wp_die();
        }
    }
}