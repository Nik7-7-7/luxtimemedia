<?php
if( !class_exists('TRP_String_Translation_API_Post_Slug') ) {
    class TRP_String_Translation_API_Post_Slug
    {
        protected $type = 'postslug';
        protected $id_column_name = 'post_id';
        protected $config;
        protected $helper;
        protected $meta_based_strings;
        protected $option_based_strings;
        protected $settings;

        public function __construct($settings)
        {
            $this->settings = $settings;
            $this->helper = new TRP_String_Translation_Helper();
            $this->meta_based_strings = new TRP_IN_SP_Meta_Based_Strings();
            $this->option_based_strings = new TRP_IN_SP_Option_Based_Strings();
        }

        public function get_strings()
        {
            $this->helper->check_ajax($this->type, 'get');
            $trp = TRP_Translate_Press::get_trp_instance();
            $trp_query = $trp->get_component('query');
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
                add_filter('posts_where', array($this, 'replace_meta_key_compare_wildcard'));
                add_filter('posts_join', array($this, 'replace_meta_key_compare_wildcard'));
                $wp_query_args = $this->meta_based_strings->get_translation_status_wp_query_args($wp_query_args, $sanitized_args, $trp_query);
            }

            // order and orderby
            if (!empty($sanitized_args['orderby'])) {
                if ($sanitized_args['orderby'] === 'original') {
                    $wp_query_args['orderby'] = 'name';
                }
                if ($sanitized_args['orderby'] === 'id') {
                    $wp_query_args['orderby'] = 'ID';
                }
                $wp_query_args['order'] = $sanitized_args['order'];
            }

            // search filter
            if (!empty($sanitized_args['s'])) {
                add_filter('posts_where', array($this, 'add_query_on_post_name'), 10, 2);
                $wp_query_args['trp_post_name_like'] = $sanitized_args['s'];
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

            $wp_query_args = apply_filters('trp_string_translation_query_args_' . $this->type, $wp_query_args, $sanitized_args);

            // query for needed strings
            $resulted_wp_query = new WP_Query($wp_query_args);
            if ($resulted_wp_query && isset($resulted_wp_query->posts) && count($resulted_wp_query->posts) > 0) {

                $found_items = $resulted_wp_query->found_posts;
                $post_ids = array();
                foreach ($resulted_wp_query->posts as $post) {
                    $post_ids[] = $post->ID;
                }

                // get all translations for all languages for the filtered strings
                $sql_results = $this->meta_based_strings->get_translations_from_meta_table($post_ids, 'postmeta', $this->id_column_name);

                // construct dictionary by original
                $translationsArrays = $this->meta_based_strings->get_translations_array_from_sql_results($post_ids, $sql_results, $trp_query, $this->id_column_name);
                foreach ($resulted_wp_query->posts as $post) {
                    // it's possible that draft posts don't have slug yet so check if post_name is empty
                    if (!empty($post->post_name)) {
                        $dictionary_by_original[] = array(
                            'original' => $post->post_name,
                            'post_type' => $post->post_type,
                            'type' => $this->type,
                            'translationsArray' => $translationsArrays[$post->ID]
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
         * Function to replace $ with % to obtain the SQL wildcard when querying for all languages
         *
         * meta_key_compare was introduced in WP 5.1 so we need to use this workaround to ensure compatibility
         *
         * @param $where
         * @return string|string[]
         */
        public function replace_meta_key_compare_wildcard($where)
        {
            $this->meta_based_strings = new TRP_IN_SP_Meta_Based_Strings();

            $where = str_replace("meta_key='" . $this->meta_based_strings->get_human_translated_slug_meta() . "$", "meta_key LIKE '" . $this->meta_based_strings->get_human_translated_slug_meta() . "%", $where);
            $where = str_replace("meta_key = '" . $this->meta_based_strings->get_human_translated_slug_meta() . "$", "meta_key LIKE '" . $this->meta_based_strings->get_human_translated_slug_meta() . "%", $where);
            $where = str_replace("meta_key='" . $this->meta_based_strings->get_automatic_translated_slug_meta() . "$", "meta_key LIKE '" . $this->meta_based_strings->get_automatic_translated_slug_meta() . "%", $where);
            $where = str_replace("meta_key = '" . $this->meta_based_strings->get_automatic_translated_slug_meta() . "$", "meta_key LIKE '" . $this->meta_based_strings->get_automatic_translated_slug_meta() . "%", $where);

            return $where;
        }

        public function add_query_on_post_name($where, $wp_query)
        {
            global $wpdb;
            if ($trp_post_name_like = $wp_query->get('trp_post_name_like')) {
                $where .= ' AND ' . $wpdb->posts . '.post_name LIKE \'%' . esc_sql($wpdb->esc_like(sanitize_title($trp_post_name_like))) . '%\'';
            }
            return $where;
        }


        public function save_strings()
        {

            // editor api should take care of this
        }
    }
}