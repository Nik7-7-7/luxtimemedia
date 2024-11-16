<?php
if( !class_exists('TRP_String_Translation_API_Term_Slug') ) {
    class TRP_String_Translation_API_Term_Slug
    {
        protected $type = 'term-slug';
        protected $id_column_name = 'term_id';
        protected $settings;
        protected $config;
        protected $helper;
        protected $meta_based_strings;
        protected $option_based_strings;

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
            $wp_query_args = array('hide_empty' => false);


            // translation status filter
            if (!empty($sanitized_args['status'])) {
                add_filter('terms_clauses', array($this, 'replace_meta_key_compare_wildcard'), 10, 3);
                $wp_query_args = $this->meta_based_strings->get_translation_status_wp_query_args($wp_query_args, $sanitized_args, $trp_query);
            }

            // order and orderby
            if (!empty($sanitized_args['orderby'])) {
                if ($sanitized_args['orderby'] === 'original') {
                    $wp_query_args['orderby'] = 'slug';
                }
                $wp_query_args['order'] = $sanitized_args['order'];
            }

            // search filter
            if (!empty($sanitized_args['s'])) {
                add_filter('terms_clauses', array($this, 'add_query_on_slug_name'), 20, 3);
                $wp_query_args['trp_slug_name_like'] = $sanitized_args['s'];
            }

            // taxonomy filter
            if (empty($sanitized_args['taxonomy'])) {
                $wp_query_args['taxonomy'] = $this->option_based_strings->get_public_slugs('taxonomies', false, array(), false);
            } else {
                $wp_query_args['taxonomy'] = $sanitized_args['taxonomy'];
            }

            // counting the matched terms
            $wp_query_args['fields'] = 'ids';
            $wp_query_args = apply_filters('trp_string_translation_query_args_count_' . $this->type, $wp_query_args, $sanitized_args);
            $resulted_wp_query = new WP_Term_Query($wp_query_args);
            if ($resulted_wp_query && isset($resulted_wp_query->terms)) {
                $found_items = count($resulted_wp_query->terms);
            }

            unset($wp_query_args['fields']);

            // pagination
            $wp_query_args['number'] = $config['items_per_page'];
            $wp_query_args['offset'] = ($sanitized_args['page'] - 1) * $config['items_per_page'];

            $wp_query_args = apply_filters('trp_string_translation_query_args_' . $this->type, $wp_query_args, $sanitized_args);

            // query for needed strings
            $resulted_wp_query = new WP_Term_Query($wp_query_args);
            if ($resulted_wp_query && isset($resulted_wp_query->terms) && count($resulted_wp_query->terms) > 0) {
                $term_ids = array();
                foreach ($resulted_wp_query->terms as $term) {
                    $term_ids[] = $term->term_id;
                }

                // get all translations for all languages for the filtered strings
                $sql_results = $this->meta_based_strings->get_translations_from_meta_table($term_ids, 'termmeta', $this->id_column_name);

                // construct dictionary by original
                $translationsArrays = $this->meta_based_strings->get_translations_array_from_sql_results($term_ids, $sql_results, $trp_query, $this->id_column_name);
                foreach ($resulted_wp_query->terms as $term) {
                    // it's possible that draft posts don't have slug yet so check if post_name is empty
                    if (!empty($term->slug)) {
                        $dictionary_by_original[] = array(
                            'original' => $term->slug,
                            'type' => $this->type,
                            'taxonomy' => $term->taxonomy,
                            'translationsArray' => $translationsArrays[$term->term_id]
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
        public function replace_meta_key_compare_wildcard($clauses, $taxonomies, $args)
        {
            $meta_based_strings = new TRP_IN_SP_Meta_Based_Strings();

            $clauses['where'] = str_replace("meta_key='" . $meta_based_strings->get_human_translated_slug_meta() . "$", "meta_key LIKE '" . $meta_based_strings->get_human_translated_slug_meta() . "%", $clauses['where']);
            $clauses['where'] = str_replace("meta_key = '" . $meta_based_strings->get_human_translated_slug_meta() . "$", "meta_key LIKE '" . $meta_based_strings->get_human_translated_slug_meta() . "%", $clauses['where']);
            $clauses['where'] = str_replace("meta_key='" . $meta_based_strings->get_automatic_translated_slug_meta() . "$", "meta_key LIKE '" . $meta_based_strings->get_automatic_translated_slug_meta() . "%", $clauses['where']);
            $clauses['where'] = str_replace("meta_key = '" . $meta_based_strings->get_automatic_translated_slug_meta() . "$", "meta_key LIKE '" . $meta_based_strings->get_automatic_translated_slug_meta() . "%", $clauses['where']);
            return $clauses;
        }

        public function add_query_on_slug_name($clauses, $taxonomies, $args)
        {
            global $wpdb;
            if ($trp_slug_name_like = $args['trp_slug_name_like']) {
                $clauses['where'] .= ' AND t.slug LIKE \'%' . esc_sql($wpdb->esc_like(sanitize_title($trp_slug_name_like))) . '%\'';
            }
            return $clauses;
        }


        /**
         * Save translations of term slugs
         *
         * Hooked to wp_ajax_trp_save_translations_term-slug
         */
        public function save_strings()
        {
            $this->helper->check_ajax($this->type, 'save');
            if (!empty($_POST['strings'])) {
                $slugs = json_decode(stripslashes($_POST['strings']));//phpcs:ignore
                $update_slugs = array();
                foreach ($slugs as $language => $language_slugs) {
                    if (in_array($language, $this->settings['translation-languages']) && $language != $this->settings['default-language']) {
                        $update_slugs[$language] = array();
                        foreach ($language_slugs as $slug) {
                            if (isset($slug->id) && is_numeric($slug->id)) {
                                $update_slugs[$language][] = array(
                                    'id' => (int)$slug->id,
                                    'translated' => $slug->translated,
                                    'status' => (int)$slug->status
                                );

                            }
                        }
                    }
                }

                $meta_based_strings = new TRP_IN_SP_Meta_Based_Strings();
                $translated_slug_meta = array(
                    1 => $meta_based_strings->get_automatic_translated_slug_meta(),
                    2 => $meta_based_strings->get_human_translated_slug_meta()
                );
                foreach ($update_slugs as $language => $update_slugs_array) {
                    foreach ($update_slugs_array as $slug_key => $slug) {
                        if (!empty($slug['id'])) {
                            if ($slug['status'] == 0) {
                                delete_term_meta($slug['id'], $meta_based_strings->get_human_translated_slug_meta() . $language);
                                delete_term_meta($slug['id'], $meta_based_strings->get_automatic_translated_slug_meta() . $language);
                            } else {
                                $sanitized_slug = sanitize_title($slug['translated']);
                                $sanitized_slug = urldecode($sanitized_slug);
                                $update_slugs[$language][$slug_key] = $slug;
                                $unique_slug = $this->get_unique_term_slug($sanitized_slug, $slug['id'], $language);
                                $update_slugs[$language][$slug_key]['translated'] = $unique_slug;
                                update_term_meta($slug['id'], $translated_slug_meta[$slug['status']] . $language, $unique_slug);
                                if ($translated_slug_meta[$slug['status']] === $meta_based_strings->get_human_translated_slug_meta()) {
                                    delete_term_meta($slug['id'], $meta_based_strings->get_automatic_translated_slug_meta() . $language);
                                }
                            }
                        }
                    }
                }
            }
            echo trp_safe_json_encode( $update_slugs ); //phpcs:ignore
            wp_die();
        }

        public function get_unique_term_slug($sanitized_slug, $term_id, $language)
        {
            if (!in_array($language, $this->settings['translation-languages'])) {
                return;
            }
            $meta_based_strings = new TRP_IN_SP_Meta_Based_Strings();
            global $wpdb;

            // get term taxonomy
            $term = get_term($term_id);
            if (is_wp_error($term)) {
                // terms that have a taxonomy which is no longer registered will return wp_error
                $term_taxonomy = $wpdb->get_var("SELECT tt.taxonomy FROM `" . $wpdb->termmeta . "` as t INNER JOIN `" . $wpdb->term_taxonomy . "` AS tt ON t.term_id = tt.term_id WHERE t.term_id = " . (int)$term_id);
            } else {
                $term_taxonomy = $term->taxonomy;
            }

            $suffix = 2;
            $slug_base = $sanitized_slug;
            $sanitized_slug_decoded = urldecode($sanitized_slug);
            $sanitized_slug_encoded = urlencode($sanitized_slug_decoded);
            do {
                // search if already have this slug in the same language and in the same taxonomy
                $meta_value = $wpdb->get_var("SELECT t.meta_value FROM `" . $wpdb->termmeta . "` AS t INNER JOIN `" . $wpdb->term_taxonomy . "` AS tt ON t.term_id = tt.term_id" .
                    " WHERE (t.meta_value='" . sanitize_text_field($sanitized_slug_decoded) . "'OR'" . sanitize_text_field($sanitized_slug_encoded) . "') AND tt.taxonomy='" . $term_taxonomy . "' AND " .
                    "( t.meta_key='" . sanitize_text_field($meta_based_strings->get_human_translated_slug_meta() . $language) .
                    "' OR t.meta_key='" . sanitize_text_field($meta_based_strings->get_automatic_translated_slug_meta() . $language) . "')");

                $slug_already_exists = !empty($meta_value) && $meta_value == $sanitized_slug;
                if ($slug_already_exists) {
                    $sanitized_slug = $slug_base . '-' . $suffix;
                    $suffix++;
                }
            } while ($slug_already_exists);

            return $sanitized_slug;
        }
    }
}