<?php
if( !class_exists('TRP_String_Translation_API_Post_Slug') ) {
    class TRP_String_Translation_API_Post_Slug
    {
        protected $type = 'postslug';
        protected $config;
        protected $helper;
        protected $meta_based_strings;
        protected $option_based_strings;
        protected $settings;
        protected $slug_query;

        public function __construct($settings)
        {
            $this->settings = $settings;
            $this->helper = new TRP_String_Translation_Helper();
            $this->meta_based_strings = new TRP_IN_SP_Meta_Based_Strings();
            $this->option_based_strings = new TRP_IN_SP_Option_Based_Strings();
            $this->slug_query = new TRP_Slug_Query();
        }

        public function get_strings()
        {
            $this->helper->check_ajax($this->type, 'get');
            $trp = TRP_Translate_Press::get_trp_instance();
            $string_translation = $trp->get_component('string_translation');
            $config = $string_translation->get_configuration_options();
            $sanitized_args = $this->helper->get_sanitized_query_args($this->type);
            $dictionary_by_original = array();
            $found_items = 0;

            // pagination
            $wp_query_args = array(
                'posts_per_page' => $config['items_per_page'],
                'offset' => ($sanitized_args['page'] - 1) * $config['items_per_page']
            );

            // translation status filter
            if (!empty($sanitized_args['status'])) {
                $wp_query_args['status'] = $sanitized_args['status'];
            }

            // order and orderby
            if (!empty($sanitized_args['orderby'])) {
                if ($sanitized_args['orderby'] === 'original') {
                    $wp_query_args['orderby'] = 'post_name';
                }
                if ($sanitized_args['orderby'] === 'id') {
                    $wp_query_args['orderby'] = 'ID';
                }
                $wp_query_args['order'] = $sanitized_args['order'];
            }

            // search filter
            if (!empty($sanitized_args['s'])) {
                $wp_query_args['search'] = $sanitized_args['s'];
            }

            // post status filter
            if ($sanitized_args['post-status'] != 'trp_any') {
                $wp_query_args['post_status'] = (empty($sanitized_args['post-status'])) ? 'publish' : $sanitized_args['post-status'];
            }

            // post type filter
            if (empty($sanitized_args['post-type'])) {
                $wp_query_args['post_type'] = $this->option_based_strings->get_public_slugs('post_types', false, array(), false);
            } else {
                $wp_query_args['post_type'] = $sanitized_args['post-type'];
            }

            if ( !empty( $sanitized_args['language'] ) && !empty( $sanitized_args['status'] ) ){
                $wp_query_args['language'] = $sanitized_args['language'];
            }

            // query for needed strings
            $posts = $this->get_posts( $wp_query_args );

            $resulted_wp_query = $posts['query_results'];

            if ( $resulted_wp_query && count( $resulted_wp_query ) > 0 ) {

                $found_items = $posts['total_posts'];
                $post_slugs = array();

                foreach ( $resulted_wp_query as $post ) {
                    $post_slugs[] = $post->post_name;
                }

                $translated_posts = $this->slug_query->get_translated_slugs_from_original( $post_slugs );

                // construct dictionary by original
                $translationsArrays = new TRP_String_Translation_Array( $post_slugs, $translated_posts, 'post' );

                $translationsArrays = $translationsArrays->get_formatted_translations_array();

                foreach ($resulted_wp_query as $post) {
                    // it's possible that draft posts don't have slug yet so check if post_name is empty
                    if (!empty($post->post_name)) {
                        $curr_post_name = $post->post_name;

                        $dictionary_by_original[] = array(
                            'original' => urldecode( $curr_post_name ),
                            'post_type' => $post->post_type,
                            'type' => $this->type,
                            'post_id' => $post->ID,
                            'translationsArray' => $translationsArrays[$curr_post_name]
                        );
                    }
                }
            }

            echo trp_safe_json_encode(array( //phpcs:ignore
                'dictionary' => $dictionary_by_original,
                'totalItems' => $found_items
            ));
            wp_die();
        }

        /**
         * Retrieves posts based on various filtering criteria including post type, status, search terms, and translation status.
         *
         * Constructs an SQL query to fetch posts from the WordPress database while joining translation tables to
         * filter by translation status and language. It supports advanced filtering such as searching by post name,
         * filtering by translation status (translated, not translated), and pagination.
         *
         * @param array $args Associative array of arguments to filter and retrieve posts. Supported keys are:
         *                    - 'post_type': (array|string) Post types to include in the query.
         *                    - 'post_status': (string) Specific post status to filter on. If omitted, defaults to excluding 'auto-draft' and 'inherit'.
         *                    - 'search': (string) Optional. Search term to match against post names.
         *                    - 'status': (array) Optional. Translation status to filter on, can include special flags to indicate not translated.
         *                    - 'language': (string) Optional. Language code to filter translations on.
         *                    - 'orderby': (string) Optional. Column to order by.
         *                    - 'order': (string) Optional. Order direction ('ASC' or 'DESC').
         *                    - 'posts_per_page': (int) Optional. Number of posts to limit the query to.
         *                    - 'offset': (int) Optional. Number of posts to skip.
         *
         * @return array Associative array containing:
         *               - 'query_results': Array of objects representing each post that matches the criteria.
         *               - 'total_posts': Integer representing the total number of posts that match the criteria before pagination.
         */
        private function get_posts( $args ){
            global $wpdb;

            $excluded_post_statuses = apply_filters( 'trp_editor_get_posts_excluded_statuses', [ "'auto-draft'", "'inherit'" ] ); // If "Any status" is selected, exclude these statuses from being shown

            $slug_translation_table = $this->slug_query->get_translation_table_name();
            $slug_original_table    = $this->slug_query->get_original_table_name();

            $post_types = (array) $args['post_type'];

            $default_post_status = "NOT IN (" . implode( ',', $excluded_post_statuses ) . ")";
            $post_status         = isset( $args['post_status'] ) ? "='%s'" : $default_post_status;

            $post_types_placeholders        = array_fill( 0, count( $post_types ), "%s" );
            $post_types_placeholders_string = implode( ',', $post_types_placeholders );

            $sql = "SELECT p.post_name, p.ID, p.post_type FROM $wpdb->posts as p 
                        LEFT JOIN $slug_original_table as so ON p.post_name = so.original
                        LEFT JOIN $slug_translation_table as st ON st.original_id = so.id
                    WHERE p.post_type IN ( $post_types_placeholders_string )
                        AND p.post_status $post_status";

            $conditions = [];
            $sql_params = $post_types;

            if ( isset( $args['post_status'] ) ){
                $sql_params[] = $args['post_status'];
            }

            if ( !empty( $args['search'] ) ) {
                $conditions[] = "p.post_name LIKE %s";
                $sql_params[] = "%" . urlencode( $args['search'] ) . "%";
            }

            if ( !empty( $args['status'] ) ) {
                $status  = (array) $args['status'];
                $filtered_status  = array_filter( $status, function( $value ) { return $value !== 0; } ); // Remove status 0

                if ( !empty( $args['language'] ) ){
                    $has_language_arg = true;

                    $status_sub_query = $wpdb->prepare( "SELECT p.post_name FROM $wpdb->posts as p 
                                                                  INNER JOIN $slug_original_table as so on p.post_name = so.original 
                                                                  INNER JOIN $slug_translation_table as st on so.id = st.original_id 
                                                               WHERE st.language = %s ", $args['language'] );
                }

                // Not translated only
                if ( $status === [0] ) {
                    /** When looking for non translated strings in a certain language, we search for status NULL or entries that are not found in the sub-query  */
                    $status_condition = isset( $has_language_arg ) ?
                                        "(st.status IS NULL OR p.post_name NOT IN ($status_sub_query))"
                                        : "st.status IS NULL";

                    $conditions[] = $status_condition;
                }

                // Not translated and another status
                elseif ( in_array( 0, $status ) && count( $status ) > 1 ) {
                    $the_other_status = absint( $filtered_status[0] );

                    $status_condition = isset( $has_language_arg ) ?
                                        $wpdb->prepare( "( ( st.status IS NULL OR p.post_name NOT IN ($status_sub_query) ) OR ( st.status = '$the_other_status' AND st.language = '%s' ) )", $args['language'])
                                        : "st.status IS NULL OR st.status = '$the_other_status'";

                    $conditions[] = $status_condition;
                }

                // Automatically translated and manually translated
                else {
                    $status_placeholders = implode( ',', array_fill( 0, count( $status ), '%d' ) );

                    $conditions[] = isset( $has_language_arg) ? "st.status IN ($status_placeholders) AND st.language = %s" : "st.status IN ($status_placeholders)";

                    $sql_params = array_merge( $sql_params, $status );

                    if ( isset( $has_language_arg ) ) $sql_params[] = $args['language'];
                }
            }

            if ( !empty( $conditions ) ) {
                $sql .= " AND " . implode( ' AND ', $conditions );
            }

            $sql .= " GROUP BY p.post_name"; // Remove slug duplicates

            $total_nr_posts = count( $wpdb->get_results( $wpdb->prepare( $sql, $sql_params ) ) ); // Get total nr of slugs before pagination

            // Append the ORDER BY clause
            if ( !empty( $args['orderby'] ) ) {
                 $order_direction = strtoupper( $args['order'] );
                 $sql .= " ORDER BY p.{$args['orderby']} {$order_direction}";
            }

            // Pagination
            if ( isset( $args['posts_per_page'] ) ) {
                $sql .= " LIMIT %d";
                $sql_params[] = $args['posts_per_page'];

                if ( !empty( $args['offset'] ) ) {
                    $sql .= " OFFSET %d";
                    $sql_params[] = $args['offset'];
                }
            }

            $prepared_query = $wpdb->prepare( $sql, $sql_params );

            $query_results = $wpdb->get_results( $prepared_query );

            foreach ( $query_results as $result ) {
                $result->post_name = $result->post_name;
            }

            return [
                'query_results' => $query_results,
                'total_posts'   => $total_nr_posts
            ];
        }

        public function save_strings()
        {

            // editor api should take care of this
        }
    }
}