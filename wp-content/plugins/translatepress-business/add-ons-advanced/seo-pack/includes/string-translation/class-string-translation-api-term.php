<?php
if( !class_exists('TRP_String_Translation_API_Term_Slug') ) {
    class TRP_String_Translation_API_Term_Slug {
        protected $type = 'term';
        protected $settings;
        protected $config;
        protected $helper;
        protected $meta_based_strings;
        protected $option_based_strings;
        protected $slug_query;
        protected $editor_actions;

        public function __construct( $settings ) {
            $this->settings             = $settings;
            $this->helper               = new TRP_String_Translation_Helper();
            $this->meta_based_strings   = new TRP_IN_SP_Meta_Based_Strings();
            $this->option_based_strings = new TRP_IN_SP_Option_Based_Strings();
            $this->slug_query           = new TRP_Slug_Query();
            $this->editor_actions       = new TRP_IN_SP_Editor_Actions( $this->slug_query, $settings );
        }

        public function get_strings() {
            $this->helper->check_ajax( $this->type, 'get' );
            $trp                    = TRP_Translate_Press::get_trp_instance();
            $string_translation     = $trp->get_component( 'string_translation' );
            $config                 = $string_translation->get_configuration_options();
            $sanitized_args         = $this->helper->get_sanitized_query_args( $this->type );
            $dictionary_by_original = array();
            $found_items            = 0;
            $wp_query_args          = array( 'hide_empty' => false );


            // translation status filter
            if ( !empty( $sanitized_args['status'] ) ) {
                $wp_query_args['status'] = $sanitized_args['status'];
            }

            // order and orderby
            if ( !empty( $sanitized_args['orderby'] ) ) {
                if ( $sanitized_args['orderby'] === 'original' ) {
                    $wp_query_args['orderby'] = 'slug';
                }
                $wp_query_args['order'] = $sanitized_args['order'];
            }

            // search filter
            if ( !empty( $sanitized_args['s'] ) ) {
                $wp_query_args['search'] = $sanitized_args['s'];
            }

            if ( !empty( $sanitized_args['language'] ) ) {
                $wp_query_args['language'] = $sanitized_args['language'];
            }

            // taxonomy filter
            if ( empty( $sanitized_args['taxonomy'] ) ) {
                $wp_query_args['taxonomy'] = $this->option_based_strings->get_public_slugs( 'taxonomies', false, array(), false );
            } else {
                $wp_query_args['taxonomy'] = $sanitized_args['taxonomy'];
            }

            // pagination
            $wp_query_args['posts_per_page'] = $config['items_per_page'];
            $wp_query_args['offset'] = ( $sanitized_args['page'] - 1 ) * $config['items_per_page'];

            // query for needed strings
            $terms = $this->get_terms( $wp_query_args );

            $resulted_wp_query = $terms['query_results'];

            $found_items = $terms['total_terms'];

            if ( $resulted_wp_query && count( $resulted_wp_query ) > 0 ) {
                $wp_term_names = [];

                foreach ( $resulted_wp_query as $term ) {
                    $wp_term_names[] = $term->slug;
                }

                $translated_terms = $this->slug_query->get_translated_slugs_from_original( $wp_term_names );

                // construct dictionary by original
                $translationsArrays = new TRP_String_Translation_Array( $wp_term_names, $translated_terms, $this->type );
                $translationsArrays = $translationsArrays->get_formatted_translations_array();

                foreach ( $resulted_wp_query as $term ) {
                    // it's possible that draft posts don't have slug yet so check if post_name is empty
                    if ( !empty( $term->slug ) ) {
                        $dictionary_by_original[] = array(
                            'original'          => $term->slug,
                            'type'              => $this->type,
                            'taxonomy'          => $term->taxonomy,
                            'translationsArray' => $translationsArrays[ $term->slug ]
                        );
                    }
                }
            }

            echo trp_safe_json_encode( array( //phpcs:ignore
                                              'dictionary' => $dictionary_by_original,
                                              'totalItems' => $found_items
            ) );
            wp_die();
        }

        /**
         * Save translations of term slugs
         *
         * Hooked to wp_ajax_trp_save_translations_term
         */
        public function save_strings() {
            $this->helper->check_ajax( $this->type, 'save' );
            $update_slugs = [];
            $slugs        = json_decode( stripslashes( $_POST['strings'] ) );//phpcs:ignore

            if ( !empty( $slugs ) ) {
                $update_slugs = $this->editor_actions->save_slugs( $slugs, $this->type );
            }

            echo trp_safe_json_encode( $update_slugs ); //phpcs:ignore
            wp_die();
        }

        /**
         * Retrieves taxonomy terms based on specified criteria, handling translation status and providing support for pagination.
         *
         * This function constructs a SQL query to fetch taxonomy terms from the WordPress database. It joins custom translation tables to allow
         * filtering by translation status. It supports various filters such as taxonomy types, search terms, translation status, and language.
         * It also provides pagination capabilities and ordering of the results.
         *
         * @param array $args Associative array of arguments to filter and retrieve terms. Supported keys are:
         *                    - 'taxonomy': (array|string) Taxonomies to include in the query.
         *                    - 'search': (string) Optional. Search term to match against term slugs.
         *                    - 'status': (array) Optional. Translation status to filter on, can include special flags to indicate not translated.
         *                    - 'language': (string) Optional. Language code to filter translations on, handled in the front-end.
         *                    - 'orderby': (string) Optional. Column to order by.
         *                    - 'order': (string) Optional. Order direction ('ASC' or 'DESC').
         *                    - 'posts_per_page': (int) Optional. Number of terms to limit the query to.
         *                    - 'offset': (int) Optional. Number of terms to skip for pagination.
         *
         * @return array Associative array containing:
         *               - 'query_results': Array of objects representing each term that matches the criteria.
         *               - 'total_terms': Integer representing the total number of terms that match the criteria before pagination.
         */
        private function get_terms( $args ) {
            global $wpdb;

            $slug_translation_table = $this->slug_query->get_translation_table_name();
            $slug_original_table    = $this->slug_query->get_original_table_name();

            $taxonomies = (array)$args['taxonomy'];

            $taxonomies_placeholders        = array_fill( 0, count( $taxonomies ), "%s" );
            $taxonomies_placeholders_string = implode( ',', $taxonomies_placeholders );

            $sql = "SELECT t.slug, tt.taxonomy FROM $wpdb->terms as t 
                        INNER JOIN $wpdb->term_taxonomy as tt ON t.term_id = tt.term_id 
                        LEFT JOIN $slug_original_table as so ON t.slug = so.original
                        LEFT JOIN $slug_translation_table as st ON st.original_id = so.id
                    WHERE tt.taxonomy IN ( $taxonomies_placeholders_string )";

            $conditions = [];
            $sql_params = $taxonomies;

            if ( !empty( $args['search'] ) ) {
                $conditions[] = "t.slug LIKE %s";
                $sql_params[] = "%" . $args['search'] . "%";
            }

            // Handle translation status
            if ( !empty( $args['status'] ) ) {
                $status          = (array)$args['status'];
                $filtered_status = array_filter( $status, function ( $value ) { return $value !== 0; } ); // Remove status 0

                if ( !empty( $args['language'] ) ) {
                    $has_language_arg = true;

                    $status_sub_query = $wpdb->prepare( "SELECT t.slug FROM $wpdb->terms as t
                                                                  INNER JOIN $wpdb->term_taxonomy as tt ON t.term_id = tt.term_id 
                                                                  LEFT JOIN $slug_original_table as so ON t.slug = so.original
                                                                  LEFT JOIN $slug_translation_table as st ON st.original_id = so.id
                                                               WHERE st.language = %s ", $args['language'] );
                }

                // Not translated only
                if ( $status === [ 0 ] ) {
                    /** When looking for non translated strings in a certain language, we search for status NULL or entries that are not found in the sub-query  */
                    $status_condition = isset( $has_language_arg ) ?
                        "(st.status IS NULL OR t.slug NOT IN ($status_sub_query))"
                        : "st.status IS NULL";

                    $conditions[] = $status_condition;
                } // Not translated and another status
                elseif ( in_array( 0, $status ) && count( $status ) > 1 ) {
                    $the_other_status = absint( $filtered_status[0] );

                    $status_condition = isset( $has_language_arg ) ?
                        $wpdb->prepare( "( ( st.status IS NULL OR t.slug NOT IN ($status_sub_query) ) OR ( st.status = '$the_other_status' AND st.language = '%s' ) )", $args['language'] )
                        : "st.status IS NULL OR st.status = '$the_other_status'";

                    $conditions[] = $status_condition;
                } // Automatically translated and manually translated
                else {
                    $status_placeholders = implode( ',', array_fill( 0, count( $status ), '%d' ) );

                    $conditions[] = isset( $has_language_arg ) ? "st.status IN ($status_placeholders) AND st.language = %s" : "st.status IN ($status_placeholders)";

                    $sql_params = array_merge( $sql_params, $status );

                    if ( isset( $has_language_arg ) ) $sql_params[] = $args['language'];
                }
            }

            if ( !empty( $conditions ) ) {
                $sql .= " AND " . implode( ' AND ', $conditions );
            }

            /**
             * We use GROUP in order to eliminate slug duplicates.
             * Since slugs are replaced arbitrarily in the URL, regardless of the post, it doesn't matter which post ID is shown in the translation interface.
             */

            $sql .= " GROUP BY t.slug";

            $total_nr_terms = count( $wpdb->get_results( $wpdb->prepare( $sql, $sql_params ) ) ); // Get total nr of slugs before pagination

            // Append the ORDER BY clause
            if ( !empty( $args['orderby'] ) ) {
                $order_direction = strtoupper( $args['order'] );
                $sql             .= " ORDER BY t.{$args['orderby']} {$order_direction}";
            }

            // Pagination
            if ( isset( $args['posts_per_page'] ) ) {
                $sql          .= " LIMIT %d";
                $sql_params[] = $args['posts_per_page'];

                if ( !empty( $args['offset'] ) ) {
                    $sql          .= " OFFSET %d";
                    $sql_params[] = $args['offset'];
                }
            }

            $prepared_query = $wpdb->prepare( $sql, $sql_params );

            $query_results = $wpdb->get_results( $prepared_query );

            foreach ( $query_results as $result ) {
                $result->slug = urldecode( $result->slug );
            }

            return [
                'query_results' => $query_results,
                'total_terms'   => $total_nr_terms
            ];
        }

    }
}