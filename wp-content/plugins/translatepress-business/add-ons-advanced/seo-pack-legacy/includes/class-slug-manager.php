<?php

class TRP_IN_SP_Slug_Manager {

    protected $settings;
    protected $human_translated_slug_meta;
    protected $automatic_translated_slug_meta;
    protected $url_converter;
    protected $translation_manager;
    protected $option_based_strings;
    public $string_translation_api_tax_slug;
    public $string_translation_api_post_type_slug;
    protected $string_translation_api_term_slug;

    public function __construct( $settings ){
        $this->settings = $settings;

        $trp = TRP_Translate_Press::get_trp_instance();
        $this->url_converter = $trp->get_component( 'url_converter' );
        $this->translation_manager = $trp->get_component( 'translation_manager' );

        $meta_based_strings   = new TRP_IN_SP_Meta_Based_Strings();
        $this->human_translated_slug_meta     = $meta_based_strings->get_human_translated_slug_meta();
        $this->automatic_translated_slug_meta = $meta_based_strings->get_automatic_translated_slug_meta();

        $this->option_based_strings = new TRP_IN_SP_Option_Based_Strings();

        $this->string_translation_api_tax_slug = new TRP_String_Translation_API_Taxonomy_Slug( $settings );
        $this->string_translation_api_post_type_slug = new TRP_String_Translation_API_Post_Type_Base_Slug( $settings );
        $this->string_translation_api_term_slug = new TRP_String_Translation_API_Term_Slug( $settings );

    }

    /**
     * Echo page slug as meta tag in preview window.
     *
     * Hooked to wp_head
     */
    public function add_slug_as_meta_tag() {
        if ( isset( $_REQUEST['trp-edit-translation'] ) && ( $_REQUEST['trp-edit-translation'] === 'preview' ) ) {
            global $post;
            $trp = TRP_Translate_Press::get_trp_instance();
            if ( ! $this->translation_manager ) {
                $this->translation_manager = $trp->get_component( 'translation_manager' );
            }
            if ( method_exists ( $this->translation_manager, 'string_groups' ) ) {
                $string_groups = $this->translation_manager->string_groups();
                if ( isset( $post->ID ) && ! empty( $post->ID ) && isset( $post->post_name ) && ! empty( $post->post_name ) && ! is_home() && ! is_front_page() && ! is_archive() && ! is_search() ) {
                    echo '<meta data-trp-post-slug=' . (int) $post->ID . ' data-trp-node-type="' . esc_attr( $string_groups['slugs'] ) . '" data-trp-node-description="' . esc_attr__( 'Post Slug', 'translatepress-multilingual' ) . '"/>' . "\n";
                }
            }
        }
    }

    /*
     * Not used in TP
     */
    public function get_translated_slug_filter( $original, $post_id, $language ){
        return $this->get_translated_slug( $post_id, $language );
    }

    /**
     * When we have the permalink structure set to postname we need an extra filter for pages with translated slugs. In this case
     * we need to change the slug of the page to the original one before the query in the get_page_by_path function. In this permalink setting
     * there is no difference between post links and page links so WP uses get_page_by_path in the parse_request function to determine if it is a page or not and if we don't
     * check the original slug it will think it is a post.
     * @param $title
     * @param $raw_title
     * @param $context
     * @return string
     */
    public function change_query_for_page_by_page_slug( $title, $raw_title, $context ){
        global $TRP_LANGUAGE;
        if( !empty($TRP_LANGUAGE) && $this->settings["default-language"] != $TRP_LANGUAGE ){
            if( !empty( $context ) && $context == 'query' ) {
                if (!empty($GLOBALS['wp_rewrite']->permalink_structure) && strpos($GLOBALS['wp_rewrite']->permalink_structure, '%postname%') !== false ) {
                    $callstack_functions = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
                    $callstack_functions_functions = array();
                    foreach( $callstack_functions as $callstack_function ){
                        $callstack_functions_functions[] = $callstack_function['function'];
                    }
                    if ( in_array( 'get_page_by_path', $callstack_functions_functions ) ) {
                        $title = $this->get_original_slug($title, 'page');
                    }
                }
            }
        }

        return $title;
    }

    /**
     * Change the query_vars inside of a query if we find a translated slug in the database
     * This is needed for the url_to_post_id() function to return the correct ID for translated slugs.
     */
    public function change_slug_var_in_query( $query ){
        $new_query_vars = $this->change_slug_var_in_request( $query->query_vars );

        //url_to_post_id on the posts page slug ( Settings > Reading ) does not work correctly (instead of not returning the id it returns the last page that was created) so we have to force a different query, fake an archive query
        if( apply_filters( 'trp_remove_url_post_id_on_the_post_page_slug', true ) ) {
            if ( isset( $new_query_vars['pagename'] ) ) {
                $page_for_posts = get_option( 'page_for_posts' );
                if ( !empty( $page_for_posts ) ) {
                    $reqpage = get_page_by_path( $new_query_vars['pagename'] );

                    if ( !empty( $reqpage ) ) {
                        $reqpage = $reqpage->ID;

                        if ( $page_for_posts == $reqpage ) {
                            $new_query_vars['post_type'] = 'post';
                            unset( $new_query_vars['pagename'] );
                        }
                    }
                }
            }
        }

        $query->query_vars = $new_query_vars;
        return $query;
    }

    /**
     * Change the query_vars if we find a translated slug in the database
     */
    public function change_slug_var_in_request( $query_vars ){
        global $TRP_LANGUAGE;
        if ( $query_vars == null ){
            return $query_vars;
        }

        if( !empty($TRP_LANGUAGE) && $this->settings["default-language"] != $TRP_LANGUAGE ){
            if (!empty($query_vars['name'])) {
                if (!empty($query_vars['post_type'])) {
                    /* we can have an hierarchical structure for post types */
                    $postnames = explode( '/', $query_vars['name'] );

                    $translated_postnames = array();
                    foreach( $postnames as $postname ){
                        /**
                         * For non-latin language the characters are urlencoded in the wp_postmeta table but in $query_vars they come as decoded
                         * so in the function "get_original_slug" we send a parameter in Hebrew and the meta value is %d7%9e%d7%95%d7%a6%d7%a8 so
                         * we cannot identify it and the page for the Hebrew slug is not found resulting in 404 error.
                         *
                         */
                        $postname = urldecode($postname);
                        $translated_postnames[] = $this->get_original_slug( $postname );
                    }
                    $query_vars['name'] = implode( '/', $translated_postnames );
                    if( !is_array( $query_vars['post_type'] ) ) {//$query_vars['post_type'] can be an array of post types
                        $query_vars[$query_vars['post_type']] = implode('/', $translated_postnames);
                    }
                    else{
                        foreach( $query_vars['post_type'] as $post_type ){
                            $query_vars[$post_type] = implode('/', $translated_postnames);
                        }
                    }
                } else {
                    $query_vars['name'] = $this->get_original_slug($query_vars['name']);
                }
            } else if (!empty($query_vars['pagename'])) {
                /* we can have an hierarchical structure for pages */
                $translated_pagenames = array();
                $pagenames = explode( '/', $query_vars['pagename'] );
                foreach ( $pagenames as $pagename ){
                    $translated_pagenames[] = $this->get_original_slug( $pagename );
                }
                $query_vars['pagename'] = implode( '/', $translated_pagenames );
                //we need to set this for pages because the default is for posts and if it is not set it won't return results
                $callstack_functions = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                $callstack_functions_functions = array();
                foreach( $callstack_functions as $callstack_function ){
                    $callstack_functions_functions[] = $callstack_function['function'];
                }
                if ( in_array( 'get_url_for_language', $callstack_functions_functions ) ) {
                    $query_vars['post_type'] = 'page';
                }
            }
        }

        return $query_vars;
    }

    /* change the slug in permalinks for posts and post types */
    public function translate_slug_for_posts( $permalink, $post, $leavename = null ){

        global $TRP_LANGUAGE;

        if ( $TRP_LANGUAGE === $this->settings['default-language'] )
        {
            return $permalink;
        }

        if( $post->post_parent == 0 ){
            $translated_slug = $this->get_translated_slug( $post );
            if( !empty( $translated_slug ) ){
                // we're adding a slash in case the actual permalink doesn't have one and fails the replace
                $permalink = str_replace('/'.$post->post_name.'/', '/'.$translated_slug.'/', $permalink . '/' );
                return substr( $permalink, 0, -1 );
            }
        }
        else{
            $posts_hierarchy = get_post_ancestors( $post->ID );
            $posts_hierarchy[] = $post->ID;
            foreach( $posts_hierarchy as $post_id ){
                $translated_slug = $this->get_translated_slug( $post_id );
                if( !empty( $translated_slug ) ){
                    $post_object = get_post( $post_id );
                    // we're adding a slash in case the actual permalink doesn't have one and fails the replace
                    $permalink = str_replace('/'.$post_object->post_name.'/', '/'.$translated_slug.'/', $permalink . '/' );
                    $permalink = substr( $permalink, 0, -1 );
                }
            }
        }

        return $permalink;
    }

    /* change the slug for pages in permalinks */
    public function translate_slugs_for_pages( $uri, $page ){
        global $TRP_LANGUAGE;
        if( !empty($TRP_LANGUAGE) && $this->settings["default-language"] == $TRP_LANGUAGE )
            return $uri;

        $old_uri = $uri;
        if( strpos( $uri, '/' ) === false ){//means we do not have any page ancestors in the link so proceed
            $uri = $this->get_translated_slug( $page );
        }
        else{
            $uri_parts = explode( '/', $uri );
            $page_ancestors = array_reverse( get_post_ancestors( $page->ID ) );//this returns an array of ancestors the first element in the array is the closest ancestor so we need it reversed
            $translated_uri_parts = array();
            if( !empty( $uri_parts ) && !empty( $page_ancestors ) ) {
                foreach ($uri_parts as $key => $uri_part) {
                    if( !empty( $page_ancestors[$key] ) )
                        $translated_slug = $this->get_translated_slug($page_ancestors[$key]);
                    else
                        $translated_slug = $this->get_translated_slug($page);

                    if (!empty($translated_slug))
                        $translated_uri_parts[] = $translated_slug;
                    else
                        $translated_uri_parts[] = $uri_part;
                }

                if (!empty($translated_uri_parts))
                    $uri = implode('/', $translated_uri_parts);
            }
        }
        if ( empty ( $uri ) ){
            $uri = $old_uri;
        }

        return $uri;
    }

    /**
     * Function that redirects the url to the url with the translated slug so you can't access the original url
     */
    public function redirect_to_translated_slug(){
        global $TRP_LANGUAGE;
        if( $TRP_LANGUAGE != $this->settings['default-language'] ) {
            if (is_archive() ) {//301 redirect for term slug
                global $wp_query;
                $current_archive_url = $this->url_converter->cur_page_url();

                if( !apply_filters('trp_allow_redirect_to_translated_url', true, $current_archive_url ) ){
                    return;
                }

                if ( !isset($wp_query->query['post_type']) ) {
                    global $trp_all_taxonomies;
                    if( !isset( $trp_all_taxonomies ) )
                        $trp_all_taxonomies =  get_taxonomies();

                    foreach( $wp_query->query as $taxonomy => $term_slug ) {
                        //normalize built in category and tag taxonomies which have special query vars
                        $actual_taxonomy = $this->trp_normalize_taxonomy_names( $taxonomy );

                        //check if it is actually a taxonomy we have
                        if( in_array( $actual_taxonomy, $trp_all_taxonomies ) ) {
                            $term = get_term_by('slug', $term_slug, $actual_taxonomy);
                            //try to get the translated url
                            $location = $this->translate_term_link_slugs($current_archive_url, $term, $actual_taxonomy);
                            if ($location != $current_archive_url) {
                                $status = apply_filters( 'trp_redirect_status', 301, 'redirect_to_translated_slug' );
                                wp_redirect( $location, $status );
                                exit;
                            }
                        }
                    }
                }

            } else {//301 redirect for post and pages slug
                global $post;
                if (!empty($post->ID)) {
                    $translated_slug = $this->get_translated_slug($post->ID);
                    if (!empty($translated_slug)) {
                        // treats URL's like: something.com/my-slug/
                        $location = str_replace('/' . $post->post_name . '/', '/' . $translated_slug . '/', $this->url_converter->cur_page_url());
                        if ($location != $this->url_converter->cur_page_url()) {
                            $status = apply_filters( 'trp_redirect_status', 301, 'redirect_to_translated_slug' );
                            wp_redirect( $location, $status );
                            exit;
                        }

                        // treats URL's like something.com/my-slug?param=no
                        $location = str_replace('/' . $post->post_name . '?', '/' . $translated_slug . '?', $this->url_converter->cur_page_url());
                        if ($location != $this->url_converter->cur_page_url()) {
                            $status = apply_filters( 'trp_redirect_status', 301, 'redirect_to_translated_slug' );
                            wp_redirect( $location, $status );
                            exit;
                        }

                        // treats URL's like something.com/my-slug    -   aka no trailingslash
                        $location = str_replace('/' . $post->post_name . 'TRPURLEND', '/' . $translated_slug . 'TRPURLEND', $this->url_converter->cur_page_url() . 'TRPURLEND');
                        if ($location != $this->url_converter->cur_page_url() . 'TRPURLEND') {
                            $status = apply_filters( 'trp_redirect_status', 301, 'redirect_to_translated_slug' );
                            wp_redirect( str_replace('TRPURLEND', '', $location), $status );
                            exit;
                        }

                    }

                    //handle custom permalink structure with %category% in them redirects if necessary
                    $permalink = get_option( 'permalink_structure' );
                    if ( strpos( $permalink, '%category%' ) !== false ) {
                        $term_slug = get_query_var( 'category_name' );
                        if ( $term_slug ) {
                            $term_object = get_category_by_path($term_slug);
                            //make sure we have a valid term and that we are on a link that contains the post name
                            if ( $term_object && !is_wp_error( $term_object )
                                && ( strpos( $this->url_converter->cur_page_url(), '/'.$post->post_name.'/' ) !== false
                                    || ( !empty($translated_slug) && strpos( $this->url_converter->cur_page_url(), '/'.$translated_slug.'/' ) !== false )
                                )
                            ) {
                                global $wp_query;
                                $location = get_permalink( $wp_query->get_queried_object_id() );

                                //handle get parameters
                                if( strpos( $this->url_converter->cur_page_url(), '?' ) !== false ){
                                    $url_parts = explode( '?', $this->url_converter->cur_page_url() );
                                    $location = $location.'?'.$url_parts[1];
                                }

                                if ($location != $this->url_converter->cur_page_url()) {
                                    $status = apply_filters( 'trp_redirect_status', 301, 'redirect_to_translated_slug' );
                                    wp_redirect( $location, $status );
                                    exit;
                                }
                            }
                        }
                    }

                }
            }
        }
    }

    /**
     * @param $post the post object or post id
     * @param string $language optional parameter for language. if it's not present it will grab it from the $TRP_LANGUAGE global
     * @return mixed|string an empty string or the translated slug
     */
    public function get_translated_slug( $post, $language = null ){
        if( $language == null ){
            global $TRP_LANGUAGE;
            if( !empty( $TRP_LANGUAGE ) )
                $language = $TRP_LANGUAGE;
        }

        if( is_object( $post ) )
            $post = $post->ID;

        $translated_slug = get_post_meta( $post, $this->human_translated_slug_meta.$language, true );
        if( !empty( $translated_slug ) ) {
            return $translated_slug;
        }else {
            $translated_slug = get_post_meta( $post, $this->automatic_translated_slug_meta . $language, true );
            if ( !empty( $translated_slug ) ){
                return $translated_slug;
            }
        }
        return '';
    }

    /**
     * @param $slug the translated slug
     * @return string the original slug if we can find it
     */
    protected function get_original_slug( $slug, $post_type = '' ){
        global $TRP_LANGUAGE, $wpdb;

        $slug_decoded = urldecode($slug);
        $slug_encoded = urlencode($slug_decoded);

        if( !empty( $TRP_LANGUAGE ) ){

            $translated_slug = $wpdb->get_results($wpdb->prepare(
                "
                SELECT *
                FROM $wpdb->postmeta
                WHERE ( meta_key = '%s' OR meta_key = '%s' )
                    AND (meta_value = '%s' OR meta_value = '%s')
                ", $this->human_translated_slug_meta.$TRP_LANGUAGE, $this->automatic_translated_slug_meta.$TRP_LANGUAGE, $slug_decoded, $slug_encoded
            ) );

            if( !empty( $translated_slug ) ){
                $post_id = $translated_slug[0]->post_id;
                if( empty( $post_type ) ){
                    $post = get_post( $post_id );
                    if( !empty( $post ) )
                        $slug = $post->post_name;
                }
                elseif( $post_type == 'page' ){
                    if( get_post_type( $post_id ) == 'page' ){
                        $post = get_post( $post_id );
                        if( !empty( $post ) )
                            $slug = $post->post_name;
                    }
                }
            }
        }

        return $slug;
    }


    /**
     * Add slug into the array to run through process_strings and maybe obtain an existing translation or a machine translation
     *
     * It is later retrieved for saving in db in function save_machine_translated_slug
     *
     * Hooked to trp_translateable_strings
     *
     * @param $translateable_information
     * @param $html
     * @param $no_translate_attribute
     * @param $TRP_LANGUAGE
     * @param $language_code
     * @param $translation_render
     *
     * @return array
     */
    public function include_slug_for_machine_translation( $translateable_information, $html, $no_translate_attribute, $TRP_LANGUAGE, $language_code, $translation_render ) {
        if ( !apply_filters( 'trp_machine_translate_slug', false ) ) {
            return $translateable_information;
        }

        global $post;
        if ( isset( $post->ID ) && !empty( $post->ID ) && isset( $post->post_name ) && !empty( $post->post_name ) && !is_home() && !is_front_page() && !is_archive() && !is_search() ) {
            if ( !get_post_meta( $post->ID, $this->human_translated_slug_meta . $language_code, true ) &&
                !get_post_meta( $post->ID, $this->automatic_translated_slug_meta . $language_code, true ) ) {
                $post->post_name = str_replace('-', ' ', $post->post_name);
                $translateable_information['translateable_strings'][] = $post->post_name;
                $translateable_information['nodes'][]                 = array( 'type' => 'post_slug', 'post_id' => $post->ID );
            }
        }
        // add support for automatic translation of tax base slugs, cpt base slugs and term slugs
        global $wp_query;
        if ( is_archive() ) {
            if ( !isset( $wp_query->query['post_type'] ) ) {
                global $trp_all_taxonomies;
                if ( !isset( $trp_all_taxonomies ) )
                    $trp_all_taxonomies = get_taxonomies();

                foreach ( $wp_query->query as $taxonomy => $term_slug ) {
                    //normalize built in category and tag taxonomies which have special query vars
                    $actual_taxonomy = $this->trp_normalize_taxonomy_names( $taxonomy );

                    //check if it is actually a taxonomy we have
                    if ( in_array( $actual_taxonomy, $trp_all_taxonomies ) ) {

                        $term_object = get_term_by( 'slug', $term_slug, $actual_taxonomy );

                        if ( isset( $term_object->term_id ) ) {
                            $translated_term_slug = get_term_meta( $term_object->term_id, $this->human_translated_slug_meta . $language_code, true );
                            if ( empty( $translated_term_slug ) )//if no human translated slug try to find an automatic translated slug
                                $translated_term_slug = get_term_meta( $term_object->term_id, $this->automatic_translated_slug_meta . $language_code, true );
                            $translated_term_slug = trim( $translated_term_slug, '/\\' );

                            if ( empty( $translated_term_slug ) ) {
                                $term_slug = str_replace('-', ' ', $term_slug);
                                $translateable_information['translateable_strings'][] = $term_slug;
                                $translateable_information['nodes'][]                 = array( 'type' => 'term_slug', 'term_id' => $term_object->term_id );
                            }
                        }
                        $translated_base_slug = $this->get_translated_rewrite_base_slug( $actual_taxonomy, $language_code, true );

                        if ( !$translated_base_slug ) {
                            $tax_object         = get_taxonomy( $actual_taxonomy );
                            $original_base_slug = $this->get_rewrite_base_slug( $tax_object, $actual_taxonomy );
                            if ( $original_base_slug && strpos( trim( $original_base_slug, '/\\' ), '/' ) === false && strpos( $original_base_slug, '%' ) === false ) {
                                $original_base_slug_without_hyphens = str_replace('-', ' ', $original_base_slug);
                                $translateable_information['translateable_strings'][] = $original_base_slug_without_hyphens;
                                $translateable_information['nodes'][]                 = array( 'type' => 'tax_base_slug', 'tax_original_base_slug' => $original_base_slug );
                            }
                        }
                    }
                }
            }
        }

        if ( isset( $wp_query->query['post_type'] ) ) {
            //$wp_query->query['post_type'] can be a string or an array. Next lines we are making sure to treat both cases
            $post_types = $wp_query->query['post_type'];
            if ( !empty( $post_types ) && !is_object( $post_types ) ) {
                if ( is_string( $post_types ) ) {
                    $post_types = array( $post_types );
                }
                if ( is_array( $post_types ) ) {
                    foreach ( $post_types as $post_type_string ) {
                        if ( !is_string( $post_type_string ) ) {
                            continue;
                        }

                        if ( apply_filters( 'trp_filter_post_type_base_slugs_from_automatic_translation', true, $post_type_string) ) {


                            $translated_base_slug = $this->get_translated_rewrite_base_slug( $post_type_string, $language_code, false );
                            if ( !$translated_base_slug ) {
                                $post_type_object   = get_post_type_object( $post_type_string );
                                $original_base_slug = $this->get_rewrite_base_slug( $post_type_object, $post_type_string );
                                if ( $original_base_slug && strpos( trim( $original_base_slug, '/\\' ), '/' ) === false && strpos( $original_base_slug, '%' ) === false ) {
                                    $original_base_slug_without_hyphens = str_replace('-', ' ', $original_base_slug);
                                    $translateable_information['translateable_strings'][] = $original_base_slug_without_hyphens;
                                    $translateable_information['nodes'][]                 = array( 'type' => 'post_type_base_slug', 'post_type_original_base_slug' => $original_base_slug );

                                }
                            }


                        }
                    }
                }
            }
        }

        return $translateable_information;
    }

    /**
     * Function hooked on trp_translateable_information to save the slug translation.
     *
     * Runs on every translated page. It's used to save the page slug from google translate into the proper slug meta
     *
     * Works together with function include_slug_for_machine_translation
     */
    public function save_machine_translated_slug($translateable_information, $translated_strings, $language_code){

        if( !apply_filters('trp_machine_translate_slug', false)){
            return;
        }

        foreach($translateable_information['nodes'] as $key => $value){
            if ($value['type'] === 'post_slug'){ //post slugs
                $post_id = $value['post_id'];

                if( !empty( $translated_strings[$key] ) && !empty($post_id) && is_numeric($post_id)){
                    $post = get_post($post_id);

                    if( is_object($post)){
                        $sanitized_slug = sanitize_title( $translated_strings[$key] );
                        $sanitized_slug = urldecode($sanitized_slug);
                        if ( !empty( trim( $sanitized_slug, ' -' ) ) ) {
                            $unique_slug = $this->get_unique_post_slug( $sanitized_slug, $post, $language_code );
                            update_post_meta( $post_id, $this->automatic_translated_slug_meta . $language_code, $unique_slug );
                        }
                        return;
                    }
                }

            }
            elseif ( $value['type'] === 'term_slug' ){//term slugs
                if(isset($value['term_id'])) {
                $term_id = $value['term_id'];

                    if ( !empty( $translated_strings[ $key ] ) && !empty( $term_id ) && is_numeric( $term_id ) ) {
                        $sanitized_slug = sanitize_title( $translated_strings[ $key ] );
                        $sanitized_slug = urldecode($sanitized_slug);
                        if ( !empty( trim( $sanitized_slug, ' -' ) ) ) {
                            $unique_slug = $this->string_translation_api_term_slug->get_unique_term_slug( $sanitized_slug, $term_id, $language_code );
                            update_term_meta( $term_id, $this->automatic_translated_slug_meta . $language_code, $unique_slug );
                        }
                    }
                }
            }
            elseif ( $value['type'] === 'tax_base_slug' || $value['type'] === 'post_type_base_slug' ){
                if( $value['type'] === 'tax_base_slug' )
                    $original_base_slug = $value['tax_original_base_slug'];
                else
                    $original_base_slug = $value['post_type_original_base_slug'];

                if( !empty( $translated_strings[$key] ) && !empty($original_base_slug) ) {

                    $sanitized_slug = sanitize_title($translated_strings[$key]);
                    $sanitized_slug = urldecode($sanitized_slug);
                    if ( !empty( trim( $sanitized_slug, ' -' ) ) ) {
                        $trp       = TRP_Translate_Press::get_trp_instance();
                        $trp_query = $trp->get_component( 'query' );

                        $sanitized_slug_array = array( $language_code => array( array( "editedTranslation" => $sanitized_slug, "translated" => $sanitized_slug, "status" => $trp_query->get_constant_machine_translated(), "id" => $original_base_slug, "original" => $original_base_slug ) ) );

                        if ( $value['type'] === 'tax_base_slug' )
                            $this->option_based_strings->save_strings_for_option_based_slug( $this->string_translation_api_tax_slug->get_type(), $this->string_translation_api_tax_slug->get_option_name(), $sanitized_slug_array );
                        else
                            $this->option_based_strings->save_strings_for_option_based_slug( $this->string_translation_api_post_type_slug->get_type(), $this->string_translation_api_post_type_slug->get_option_name(), $sanitized_slug_array );
                    }

                }
            }
        }

    }

    /**
     * Return a unique post slug.
     *
     * It should be unique against WP posts and slugs saved in the same language.
     * Numeral suffixes will be added if there is a conflict.
     * (ex. my-post-2 )
     *
     * @param $sanitized_slug
     * @param $post
     * @param $language
     * @return string|void
     */
    public function get_unique_post_slug( $sanitized_slug, $post, $language ){
        if ( ! in_array( $language, $this->settings['translation-languages'] ) ) {
            return;
        }
        $wp_unique_slug = wp_unique_post_slug($sanitized_slug, $post->ID, $post->post_status, $post->post_type, $post->post_parent);

        global $wpdb;
        $suffix = 2;
        $slug_base = $wp_unique_slug;

        $wp_unique_slug_decoded = urldecode($wp_unique_slug);
        $wp_unique_slug_encoded = urlencode($wp_unique_slug_decoded);
        do {
            $meta_value = $wpdb->get_var( "SELECT meta_value FROM " . $wpdb->postmeta . " WHERE (meta_value='" .
                sanitize_text_field( $wp_unique_slug_decoded ) . "'OR'" . sanitize_text_field($wp_unique_slug_encoded) . "') AND ( meta_key='" . sanitize_text_field( $this->human_translated_slug_meta . $language ) .
                "' OR meta_key='" . sanitize_text_field( $this->automatic_translated_slug_meta . $language ) . "')" );

            $slug_already_exists = !empty( $meta_value ) && $meta_value == $wp_unique_slug;
            if ( $slug_already_exists ){
                $wp_unique_slug = $slug_base . '-' . $suffix;
                $suffix++;
            }
        }while( $slug_already_exists );

        return $wp_unique_slug;
    }

    /**
     * Function that filters the rewrite arg when registering taxonomies and CPTs on other languages if we have a translation for that slug
     * @param $args
     * @param $name
     * @return mixed
     */
    public function filter_registration_args_for_slug( $args, $name ){
        global $TRP_LANGUAGE;

	    // we're testing if this is an admin page or this is a rest request, we don't do anything
	    // Rest API with translated slugs don't work for example en/wp-json/wp/v2/my-translated-cpt-slug/
	    if( is_admin() || $TRP_LANGUAGE === $this->settings['default-language'] || (method_exists('TRP_Translation_Manager', 'is_rest_api_request') && $this->translation_manager::is_rest_api_request() ))
		    return $args;

        global $wp_current_filter;

        // There is no need to duplicate the function
        $is_tax = ( in_array( 'register_taxonomy_args', $wp_current_filter ) );

        // Get original rewrite slug
        $rewrite_slug = $this->get_rewrite_base_slug( $args, $name );

        if( $rewrite_slug ) {
            // Get the translated rewrite slug
            $translated_rewrite_slug = $this->get_translated_rewrite_base_slug($rewrite_slug, $TRP_LANGUAGE, $is_tax);

            // Filter only if rewrite slug is set
            if ($translated_rewrite_slug) {
                $args['rewrite'] = (isset($args['rewrite']) && is_array($args['rewrite'])) ? $args['rewrite'] : array();
                $args['rewrite']['slug'] = $translated_rewrite_slug;

                // Filter archive base (only for post types)
                if (!$is_tax) {
                    if(isset($args['has_archive'])) {
                        if ( $args['has_archive'] && is_string( $args['has_archive'] ) ) {
                            //add compatibility with the product post type archive which takes the slug from the page that is set up as a Shop page in woocommerce settings
                            if ( class_exists( 'WooCommerce' ) ) {
                                $shop_page_id = wc_get_page_id( 'shop' );
                                $shop_archive = $shop_page_id && get_post( $shop_page_id ) ? urldecode( get_page_uri( $shop_page_id ) ) : 'shop';
                                if ( $args['has_archive'] === $shop_archive ) {
                                    $args['has_archive'] = $shop_archive; //basically we don't change anything. If it is a page slug it will already be translated from get_page_uri() that passes through the filter 'get_page_uri'
                                } else
                                    $args['has_archive'] = $translated_rewrite_slug;
                            } else
                                $args['has_archive'] = $translated_rewrite_slug;
                        }
                    }
                }

                //301 redirect for taxonomy and cpt base slug
                $current_archive_url = $this->url_converter->cur_page_url();

                if( !apply_filters('trp_allow_redirect_to_translated_url', true, $current_archive_url ) ){
                    return $args;
                }

                $location = str_replace( '/'.$rewrite_slug.'/', '/'.$translated_rewrite_slug.'/', $current_archive_url  );
                if ($location != $current_archive_url) {
                    $status = apply_filters( 'trp_redirect_status', 301, 'redirect_to_translated_slug' );
                    wp_redirect( $location, $status );
                    exit;
                }
            }
        }

        return $args;
    }

    /**
     * Function to get the original rewrite slug from registering args
     * @param $args
     * @param $name
     * @return bool|mixed
     */
    public function get_rewrite_base_slug( $args, $name ){
        if(is_object($args))//this way we can pass a taxonomy object, not just an arguments array
            $args = (array) $args;

        if ( is_string( $name ) ) {
            //for woocommerce we have a special case, we need the original hardcoded slug and not the one passed through the translation functions (_x)
            if (class_exists('WooCommerce')) {

                if (!class_exists('TRP_Translation_Manager'))
                    require_once TRP_PLUGIN_DIR . 'includes/class-translation-manager.php';

                if (class_exists('TRP_Translation_Manager')) {
                    if ($name === 'product' || $name === 'product_cat' || $name === 'product_tag') {
                        if ($name === 'product' && trim($args['rewrite']['slug'], '\\/') === TRP_Translation_Manager::strip_gettext_tags(_x('product', 'slug', 'woocommerce'))) //phpcs:ignore
                            $name = trim($name, '/\\');
                        elseif ($name === 'product_cat' && trim($args['rewrite']['slug'], '\\/') === TRP_Translation_Manager::strip_gettext_tags(_x('product-category', 'slug', 'woocommerce'))) //phpcs:ignore
                            $name = 'product-category';
                        elseif ($name === 'product_tag' && trim($args['rewrite']['slug'], '\\/') === TRP_Translation_Manager::strip_gettext_tags(_x('product-tag', 'slug', 'woocommerce'))) //phpcs:ignore
                            $name = 'product-tag';

                        if ($name === 'product' || $name === 'product-category' || $name === 'product-tag') {
                            global $trp_wc_permalinks;//this should be defined in woocommerce_filter_permalink_option() function
                            if (isset($trp_wc_permalinks)) {
                                if ($name == 'product-category') {
                                    $option_index = 'category_base';
                                } elseif ($name == 'product-tag') {
                                    $option_index = 'tag_base';
                                } elseif ($name == 'product') {
                                    $option_index = 'product_base';
                                }

                                if (!empty($trp_wc_permalinks) && !empty($option_index) && isset($trp_wc_permalinks[$option_index]) && $name != $trp_wc_permalinks[$option_index]) {
                                    $name = trim($trp_wc_permalinks[$option_index], '\\/');
                                }
                            }

                            return $name;
                        }

                    }
                }
            }

            if (isset($args['rewrite'])) {
                if (is_array($args['rewrite']) && isset($args['rewrite']['slug']))
                    return trim($args['rewrite']['slug'], '\\/');
                else if ( $args['rewrite'] === true || ( ( false !== $args['rewrite'] ) && ( is_admin() || get_option( 'permalink_structure' ) ) ) )
                    return trim($name, '\\/');
                else
                    return false;
            } else
                return trim($name, '\\/');
        }

        return false;
    }

    /**
     * Function to get the translated rewrite slug based on the original rewrite slug
     * @param $name
     * @param $langauge
     * @param $is_tax
     * @return bool
     */
    public function get_translated_rewrite_base_slug( $name, $langauge, $is_tax ){
        global  $trp_taxonomy_slug_translation;

        //rebase $name for woocommerce. ex: default site language de_de then default slug for product cpt will be 'produkt' and that is how we save in the db. But in de_at (austrian) as there is no translation in the woocommerce mo $name will come as 'product' and we won't find the translation in the db
        global $trp_wc_permalinks;//this should be defined in woocommerce_filter_permalink_option() function
        if( isset($trp_wc_permalinks) ){
            if ($name == 'product-category') {
                $option_index = 'category_base';
            } elseif ($name == 'product-tag') {
                $option_index = 'tag_base';
            } elseif ($name == 'product') {
                $option_index = 'product_base';
            }

            if( !empty( $trp_wc_permalinks ) && !empty( $option_index ) && isset( $trp_wc_permalinks[$option_index] ) && $name != $trp_wc_permalinks[$option_index] ){
                $name = trim( $trp_wc_permalinks[ $option_index ], '\\/' );
            }
        }

        /* get the options from the database and store them in a global so we don't query the db on every call */
        if( $is_tax ){
            if( !isset($trp_taxonomy_slug_translation) )
                $trp_taxonomy_slug_translation = get_option( $this->string_translation_api_tax_slug->get_option_name(), '' );
        }
        else{
            $trp_cpt_slug_translation = get_option( $this->string_translation_api_post_type_slug->get_option_name(), '' );
        }


        if( $is_tax ){
            $slug_translations = $trp_taxonomy_slug_translation;
        }
        else {
            $slug_translations = $trp_cpt_slug_translation;
        }

        if (!empty($slug_translations)) {
            //remove any slashes from keys from saved translations in the database
            $trimmed_slug_translations = array();
            foreach($slug_translations as $key => $value ) {
                $trimmed_slug_translations[ trim( $key, '/\\' ) ] = $value;
            }

            $slug_translations = $trimmed_slug_translations;

            if( !empty( $slug_translations[$name] ) && !empty( $slug_translations[$name]['translationsArray'] ) && !empty( $slug_translations[$name]['translationsArray'][$langauge] ) && !empty( $slug_translations[$name]['translationsArray'][$langauge]['translated'] ) ){
                return remove_accents( trim( $slug_translations[$name]['translationsArray'][$langauge]['translated'], '/\\' ) );
            }

        }

        return false;

    }

    /**
     * Filter saved permalinks from db so we don't get 404 on translated slugs. This is for tax and cpt base slugs
     * @param $rewrite_rules
     * @return array
     */
    function filter_permalinks_on_other_languages($rewrite_rules){
        global $TRP_LANGUAGE;
        if( $TRP_LANGUAGE != $this->settings['default-language'] && is_array( $rewrite_rules ) ) {

            $tax_translated_slug_pairs = $this->get_taxonomy_translated_slugs_pairs_for_languages( $this->settings['default-language'], $TRP_LANGUAGE );
            $cpt_translated_slug_pairs = $this->get_cpt_translated_slugs_pairs_for_languages( $this->settings['default-language'], $TRP_LANGUAGE );

            $object_translated_slug_pairs = $tax_translated_slug_pairs + $cpt_translated_slug_pairs;

            //add compatibility with the product post type archive which takes the slug from the page that is set up as a Shop page in woocommerce settings
            if( class_exists( 'WooCommerce' ) ){
                $shop_page_from_slug = $this->get_woocommerce_shop_slug_in_language( $this->settings['default-language'] );
                $shop_page_to_slug = $this->get_woocommerce_shop_slug_in_language( $TRP_LANGUAGE);
                if ( !is_null($shop_page_from_slug) && !is_null($shop_page_to_slug) && $shop_page_from_slug != $shop_page_to_slug ) {//we actually have a translation
                    foreach ($rewrite_rules as $match => $rewrite_rule) {
                        unset( $rewrite_rules[$match] );
                        //check only in the rewrite rules for the archive page for product
                        if ( $match === $shop_page_from_slug . '/?$' || $match === $shop_page_from_slug . '/feed/(feed|rdf|rss|rss2|atom)/?$' || $match === $shop_page_from_slug . '/(feed|rdf|rss|rss2|atom)/?$' || $match === $shop_page_from_slug . '/page/([0-9]{1,})/?$' ) {
                            $rewrite_rules[str_replace( $shop_page_from_slug.'/', $shop_page_to_slug.'/', $match )] = $rewrite_rule;
                        }
                        else{
                            $rewrite_rules[$match] = $rewrite_rule;
                        }
                    }
                }

                //handle translations for /%product_cat% slugs for products
                foreach ( $rewrite_rules as $match => $rewrite_rule ) {
                    foreach( $object_translated_slug_pairs as $original_slug => $translated_slug ) {
                        if ( preg_match( '`(.+)(/%product_cat%)`', $original_slug, $matches ) && preg_match( '`(.+)(/%product_cat%)`', $translated_slug, $translated_matches ) ) {
                            unset($rewrite_rules[$match]);
                            if ( preg_match( '`^' . preg_quote( $matches[1], '`' ) . '/\(`', $match ) && preg_match( '/^(index\.php\?product_cat)(.*product)/', $rewrite_rule ) ) {
                                $rewrite_rules[str_replace($matches[1] . '/', $translated_matches[1] . '/', $match)] = $rewrite_rule;
                            } else {
                                $rewrite_rules[$match] = $rewrite_rule;
                            }
                        }
                    }
                }

            }

            $new_rewrite_rules = array();

            foreach ($rewrite_rules as $match => $rewrite_rule) {
                $translated = false;

                foreach( $object_translated_slug_pairs as $original_slug => $translated_slug ) {
                    if ( strpos($match, $original_slug.'/') === 0 || strpos($match, '/'. $original_slug .'/') !== false ) {
                        $new_rewrite_rules[str_replace( $original_slug.'/', $translated_slug.'/', $match)] = $rewrite_rule;
                        $translated = true;
                        break;
                    }
                }

                if( !$translated )
                    $new_rewrite_rules[$match] = $rewrite_rule;
            }

            return $new_rewrite_rules;
        }

        return $rewrite_rules;
    }

    /**
     * Function to get pairs of translation slugs for taxonomies in an array (key => value pairs). If it is missing in a certain language it will return the original slug
     * @param $from_language
     * @param $to_langauge
     * @return array $from_slug will be the key and $to_slug the value
     */
    public function get_taxonomy_translated_slugs_pairs_for_languages( $from_language, $to_langauge ){
        global $trp_taxonomy_slug_translation;

        /* get the options from the database and store them in a global so we don't query the db on every call */
        if (!isset($trp_taxonomy_slug_translation))
            $trp_taxonomy_slug_translation = get_option( $this->string_translation_api_tax_slug->get_option_name(), '');

        $translation_pairs = $this->get_object_translated_slugs_pairs_for_languages( $trp_taxonomy_slug_translation, $from_language, $to_langauge );

        return $translation_pairs;
    }

    /**
     * Function to get pairs of translation base slugs for CPT in an array (key => value pairs). If it is missing in a certain language it will return the original slug
     * @param $from_language
     * @param $to_langauge
     * @return array $from_slug will be the key and $to_slug the value
     */
    public function get_cpt_translated_slugs_pairs_for_languages( $from_language, $to_langauge )    {
        $trp_cpt_slug_translation = get_option( $this->string_translation_api_post_type_slug->get_option_name(), '');

        $translation_pairs = $this->get_object_translated_slugs_pairs_for_languages( $trp_cpt_slug_translation, $from_language, $to_langauge );

        return $translation_pairs;

    }

    /**
     * Function to parse an array of either taxonomy or cpt slug translations and return pairs of slug translations
     * @param $trp_object_slug_translations
     * @param $from_language
     * @param $to_langauge
     * @return array
     */
    public function get_object_translated_slugs_pairs_for_languages( $trp_object_slug_translations, $from_language, $to_langauge ){
        $translation_pairs = array();
        if( !empty($trp_object_slug_translations) ){
            foreach( $trp_object_slug_translations as $original_slug => $transaltions ){
                $from_slug = $this->get_slug_from_translation_array($transaltions, $original_slug, $from_language);
                $to_slug =  $this->get_slug_from_translation_array($transaltions, $original_slug, $to_langauge);

                $translation_pairs[$from_slug] = $to_slug;
            }
        }
        return $translation_pairs;
    }

    /**
     * Function to get a speciffic value from the translations array of slugs. If it is for the default language or the value is not translated it will default to $original_slug
     * @param $transaltions
     * @param $original_slug
     * @param $language
     * @return mixed
     */
    public function get_slug_from_translation_array( $transaltions, $original_slug, $language ){
        if( $language === $this->settings['default-language'] )
            $slug = $original_slug;
        else if (!empty($transaltions['translationsArray'][$language]) && !empty($transaltions['translationsArray'][$language]['translated']))
            $slug = $transaltions['translationsArray'][$language]['translated'];
        else
            $slug = $original_slug;//default to original_slug so we always have a value

        return trim( $slug, '/\\' );
    }


    /**
     * Filter the links for the language switcher so it changes base slugs for taxonomies and post types. I think this can use improvements ?
     * @param $new_url
     * @param $url
     * @param $language
     * @return string|string[]
     */
    function filter_language_switcher_link( $new_url, $url, $language ){
        global $TRP_LANGUAGE;

        $new_url = urldecode($new_url);

        $tax_translated_slug_pairs = $this->get_taxonomy_translated_slugs_pairs_for_languages( $TRP_LANGUAGE, $language );
        $cpt_translated_slug_pairs = $this->get_cpt_translated_slugs_pairs_for_languages( $TRP_LANGUAGE, $language );
        $object_translated_slug_pairs = $tax_translated_slug_pairs + $cpt_translated_slug_pairs;

        //add compatibility with the product post type archive which takes the slug from the page that is set up as a Shop page in woocommerce settings
        if( class_exists( 'WooCommerce' ) ){

            $shop_page_from_slug = $this->get_woocommerce_shop_slug_in_language( $TRP_LANGUAGE );
            $shop_page_to_slug = $this->get_woocommerce_shop_slug_in_language( $language);

            if ( !is_null($shop_page_from_slug) && !is_null($shop_page_to_slug) && $shop_page_from_slug != $shop_page_to_slug ) {//we actually have a translation
                $url_parts = explode( '/' .$shop_page_from_slug . '/', $new_url );
                if( count( $url_parts ) > 1 ){//it is part of the url
                    //check that we are actually on the archive page for products (there should not be any / in the last parts )
                    if( strpos( end( $url_parts ), '/' ) === false ){
                        return $new_url = str_replace( '/' .$shop_page_from_slug . '/', '/' .$shop_page_to_slug . '/', $new_url );
                    }
                }
            }
        }

        foreach( $object_translated_slug_pairs as $from_slug => $to_slug ) {

            //handle translations for /%product_cat% slugs for products
            if( class_exists( 'WooCommerce' ) ) {
                if (preg_match('`(.+)(/%product_cat%)`', $from_slug, $matches) && preg_match('`(.+)(/%product_cat%)`', $to_slug, $to_matches)) {
                    $new_url = str_replace('/' . $matches[1] . '/', '/' . $to_matches[1] . '/', $new_url);
                }
            }

            $position = strpos($new_url, '/' . $from_slug . '/');
            if ($position !== false) {
                $new_url = substr_replace($new_url, '/' . $to_slug . '/', $position, strlen('/' . $from_slug . '/')); // replace just the first occurrence in the url, so we don't replace identical term slugs that can be positioned later in the url
            }

        }

        return $new_url;
    }


    /**
     * Function that filters links for terms, so we have translation of slugs
     * @param $termlink
     * @param $term object The WP_Term object
     * @param $taxonomy
     * @return string|string[]
     */
    function translate_term_link_slugs( $termlink, $term, $taxonomy ){
        global $TRP_LANGUAGE;

        if( $TRP_LANGUAGE != $this->settings['default-language'] ) {
            //term slug can have the same slug as the taxonomy so we should change only the last occurrence
            $termlink = $this->replace_last_occurrence_of_term_slug_in_link( $termlink, $term, $TRP_LANGUAGE );

            //handle hierarchical terms
            if ( isset( $term->term_id ) ) {
                $parents_ids = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );
                if ( !empty( $parents_ids ) ) {
                    foreach ( $parents_ids as $parent_id ) {
                        $parent_term = get_term_by( 'id', $parent_id, $taxonomy );
                        //term slug can have the same slug as the taxonomy so we should change only the last occurrence
                        $termlink = $this->replace_last_occurrence_of_term_slug_in_link( $termlink, $parent_term, $TRP_LANGUAGE );
                    }
                }
            }
        }

        return $termlink;
    }

    /**
     * Function that replaces a term slug with its translation if it exists. It replaces only the last occurrence in the link to avoid replacing the taxonomy slug
     * @param $link
     * @param $term
     * @param $language
     * @return string|string[]
     */
    public function replace_last_occurrence_of_term_slug_in_link( $link, $term, $language ){
        if( is_object($term) ) {
            if(isset($term->term_id)) {
                $translated_slug = get_term_meta( $term->term_id, $this->human_translated_slug_meta . $language, true );
                if ( empty( $translated_slug ) )//if no human translated slug try to find an automatic translated slug
                    $translated_slug = get_term_meta( $term->term_id, $this->automatic_translated_slug_meta . $language, true );
                $translated_slug = trim( $translated_slug, '/\\' );
            }
            $original_slug = trim($term->slug, '/\\');

            if (!empty($translated_slug)) {
                $position = strrpos($link, '/' . urldecode( $original_slug ) . '/');
                if ($position !== false) {
                    $link = substr_replace($link, '/' . urldecode($translated_slug ) . '/', $position, strlen('/' . urldecode( $original_slug ) . '/')); // replace just the last occurrence in the url
                }
            }
        }

        return $link;
    }

    /**
     * Function that changes translated term slugs with original term slugs inside query vars. It also sets some globals if it is used on the request filter.
     * @param $query_vars
     * @return mixed
     */
	public function change_term_slug_var_in_request( $query_vars ){

		global $TRP_LANGUAGE, $wp_current_filter;
		if ( $query_vars == null ){
			return $query_vars;
		}

		if( !empty($TRP_LANGUAGE) ) {

			if( did_action('wp_loaded') ) {//only use the global after init, when probably all taxonomies were registered. we had a case in Bridge theme where this function ran before init and the global was set with just a part of the taxonomies
				global $trp_all_taxonomies;
				if (!isset($trp_all_taxonomies))
					$trp_all_taxonomies = get_taxonomies();
			}
			else
				$trp_all_taxonomies = get_taxonomies();

			// a request to display a term page seems to only have one query_var or 2 if they are paged (in JetEngine we have 2 or 3 query_vars, see comment bellow)
			// there are two formats that come in $wp_query
			if( ( in_array( 'request', $wp_current_filter ) && ( count( $query_vars ) === 1 || count( $query_vars ) === 2 || count( $query_vars ) === 3 ) ) || in_array( 'pre_get_posts', $wp_current_filter ) ) {
                foreach ($query_vars as $query_var => $query_var_value) {

                    //normalize built in category and tag taxonomies which have special query vars
                    $actual_taxonomy = $this->trp_normalize_taxonomy_names($query_var);

                    /**
                     * when we register a custom taxonomy with some plugins for instance JetEngine in query_vars we have
                     * query_vars['taxonomy'] = 'custom-taxonomy'
                     * query_vars['term'] = 'custom-term'
                     * instead of query_vars['custom-taxonomy'] = 'custom-term'
                     */
                    if( $query_var === 'term' && isset( $query_vars['taxonomy'] ) && !empty( $query_vars['taxonomy'] ) )
                        $actual_taxonomy = $this->trp_normalize_taxonomy_names($query_vars['taxonomy']);

                    //check if it is actually a taxonomy we have
                    if (in_array($actual_taxonomy, $trp_all_taxonomies) && is_string($query_var_value) && !empty($query_var_value)) {
                        $terms = explode( '/', $query_var_value ); //we could have this situation $query_vars['category_name'] = 'caty/caty-copil/caty-caty-copil'; hierarchic in category

                        $translated_slugs = array();
                        foreach ( $terms as $translated_term_slugs ) {

                            if ( strpos( $translated_term_slugs, ',' ) ) {
                                $translated_term_slugs = explode( ',', $translated_term_slugs );
                            }

                            if ( is_array( $translated_term_slugs ) ) {
                                $translated_slugs_to_implode = array();
                                foreach ( $translated_term_slugs as $translated_term_slug ) {

                                    //in some cases the slug is encoded in some decoded...we need all to be decoded if it has special characters in it
                                    $translated_term_slug = urlencode( urldecode( $translated_term_slug ) );

                                    if ( $this->settings["default-language"] != $TRP_LANGUAGE ) {
                                        //find the original slug from the translated slug for a term in a taxonomy
                                        $original_slug = $this->get_original_term_slug( $translated_term_slug, $actual_taxonomy, $TRP_LANGUAGE );

                                    } else {
                                        $original_slug = $translated_term_slug;
                                    }

                                    $translated_slugs_to_implode[] = $original_slug;

                                }
                                $translated_slugs_implode = implode( ',', $translated_slugs_to_implode );

                                $translated_slugs[] = $translated_slugs_implode;
                            }
                            else {
                                $translated_term_slugs = urlencode( urldecode( $translated_term_slugs ) );

                                if ( $this->settings["default-language"] != $TRP_LANGUAGE ) {
                                    //find the original slug from the translated slug for a term in a taxonomy
                                    $original_slug = $this->get_original_term_slug( $translated_term_slugs, $actual_taxonomy, $TRP_LANGUAGE );

                                } else {
                                    $original_slug = $translated_term_slugs;
                                }

                                $translated_slugs[] = $original_slug;

                                /* set here some globals that we can use inside get_url_for_language() function so we can have proper language switcher links */
                                /* it's ok that it is overwritten because we only need the last one in the hierarchy */
                                if ( in_array( 'request', $wp_current_filter ) || in_array( 'wpseo_sitemap_url', $wp_current_filter ) || in_array( 'rank_math/sitemap/url', $wp_current_filter ) || in_array( 'seopress_sitemaps_url', $wp_current_filter ) ) {//this is how we identify later that we are on a term page
                                    global $trp_current_url_term_slug, $trp_current_url_taxonomy;
                                    $trp_current_url_term_slug = $original_slug;
                                    $trp_current_url_taxonomy  = $actual_taxonomy;
                                }
                            }
                        }


                        $translated_slugs = implode('/', $translated_slugs);

                        $query_vars[$query_var] = $translated_slugs;
                    }

                }
            }

		}

		return $query_vars;
	}

    /**
     * Function used so we have the original query vars inside queries
     * @param $query
     * @return mixed
     */
    function change_term_slug_var_in_query( $query ){
        $new_query_vars = $this->change_term_slug_var_in_request( $query->query_vars );
        $query->query_vars = $new_query_vars;
        return $query;
    }

    /**
     * When we have a custom permalink structure with %category% tag in it, we need to change the slug in the term object
     * @param $cat_object
     * @param $cats
     * @param $post
     * @return mixed
     */
    function filter_term_slugs_in_custom_permalink_structure( $cat_object, $cats, $post ){
        global $TRP_LANGUAGE;
        $permalink_structure = get_option( 'permalink_structure' );
        if( $this->settings["default-language"] != $TRP_LANGUAGE && strpos( $permalink_structure, '%category%' ) !== false ) {
            if(isset($cat_object->term_id)) {
                $translated_slug = get_term_meta( $cat_object->term_id, $this->human_translated_slug_meta . $TRP_LANGUAGE, true );
                if ( empty( $translated_slug ) )//if no human translated slug try to find an automatic translated slug
                    $translated_slug = get_term_meta( $cat_object->term_id, $this->automatic_translated_slug_meta . $TRP_LANGUAGE, true );
                $translated_slug = trim( $translated_slug, '/\\' );
            }
            if (!empty($translated_slug)) {
                $cat_object->slug = $translated_slug;
            }
        }

        return $cat_object;
    }

    /**
     * Handle hierarchical categories in custom permalinks with %category% in them and %product_cat% for WooCommerce
     * @param $term
     * @return mixed
     */
    function filter_parent_term_slugs_in_custom_permalink_structure( $term ){
        global $TRP_LANGUAGE;
        $permalink_structure = get_option( 'permalink_structure' );
        if( $this->settings["default-language"] != $TRP_LANGUAGE && strpos( $permalink_structure, '%category%' ) !== false ) {
            $callstack_functions = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
            if (!empty($callstack_functions)) {
                $function_names = array();
                foreach ($callstack_functions as $function) {
                    $function_names[] = $function['function'];
                }
                //this seems to be the way functions are called when constructing the link for parents, so make modifications only in this case so we don't break something else
                if (in_array('get_term_parents_list', $function_names) && in_array('get_category_parents', $function_names)) {
                    if(isset($term->term_id)) {
                        $translated_slug = get_term_meta( $term->term_id, $this->human_translated_slug_meta . $TRP_LANGUAGE, true );
                        if ( empty( $translated_slug ) )//if no human translated slug try to find an automatic translated slug
                            $translated_slug = get_term_meta( $term->term_id, $this->automatic_translated_slug_meta . $TRP_LANGUAGE, true );
                        $translated_slug = trim( $translated_slug, '/\\' );
                    }
                    if (!empty($translated_slug)) {
                        $term->slug = $translated_slug;
                    }
                }
            }
        }


        //add support for hierarchical terms in WooCommerce for product singles
        if( class_exists('WooCommerce') ){
            if( $this->settings["default-language"] != $TRP_LANGUAGE ){
                $wc_permalinks = get_option('woocommerce_permalinks');
                if (!empty($wc_permalinks['product_base']) && strpos($wc_permalinks['product_base'], '%product_cat%') !== false) {
                    $callstack_functions = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
                    if (!empty($callstack_functions)) {
                        $function_names = array();
                        foreach ($callstack_functions as $function) {
                            $function_names[] = $function['function'];
                        }
                        //this seems to be the way functions are called when constructing the link for parents, so make modifications only in this case so we don't break something else
                        if (in_array('wc_product_post_type_link', $function_names) ) {
                            if(isset($term->term_id)) {
                                $translated_slug = get_term_meta( $term->term_id, $this->human_translated_slug_meta . $TRP_LANGUAGE, true );
                                if ( empty( $translated_slug ) )//if no human translated slug try to find an automatic translated slug
                                    $translated_slug = get_term_meta( $term->term_id, $this->automatic_translated_slug_meta . $TRP_LANGUAGE, true );
                                $translated_slug = trim( $translated_slug, '/\\' );
                            }
                            if (!empty($translated_slug)) {
                                $term->slug = $translated_slug;
                            }
                        }
                    }
                }
            }
        }

        return $term;
    }

    /**
     * Function that returns the original term slug based on a translation. it looks for terms in a certain taxonomy only
     * @param $slug
     * @param $taxonomy
     * @param $language
     * @return string
     */
    protected function get_original_term_slug( $slug, $taxonomy, $language ){
        global $wpdb;

        $all_possible_terms_ids = array();

        $term_args = apply_filters( 'trp_get_term_args', array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ), $slug, $taxonomy, $language );

        $all_terms_in_taxonomy = get_terms( $term_args );

        $slug_decoded = urldecode($slug);
        $slug_encoded = urlencode($slug_decoded);
        if( !empty($all_terms_in_taxonomy) ) {

            foreach ( $all_terms_in_taxonomy as $term ){
                $all_possible_terms_ids[] = $wpdb->prepare( "%d", $term->term_id );
            }

            $translated_slug = $wpdb->get_results($wpdb->prepare(
                "
            SELECT *
            FROM $wpdb->termmeta
            WHERE ( meta_key = '%s' OR meta_key = '%s' )
                AND (meta_value = '%s' OR meta_value = '%s')
                AND term_id IN (".implode( ',', $all_possible_terms_ids ).")
            ", $this->human_translated_slug_meta . $language, $this->automatic_translated_slug_meta . $language, $slug_decoded, $slug_encoded
            ));

            if (!empty($translated_slug)) {
                $term_id = $translated_slug[0]->term_id;
                foreach ( $all_terms_in_taxonomy as $term ){
                    if( $term->term_id == $term_id ){
                        $slug = $term->slug;
                    }
                }

            }
        }

        return trim( $slug, '/\\' );
    }

    /**
     * Function that returns the translation from the db instead of the po file  so we can change them from the interface
     * @param $text
     * @param $original_text
     * @param $context
     * @param $domain
     * @param $language
     * @return string
     */
    function translate_woocommerce_main_slugs($text, $original_text, $context, $domain, $language ){
        if( $domain === 'woocommerce' && $context === 'slug' ) {
            if( $original_text === 'product' || $original_text === 'product-category' || $original_text === 'product-tag' ) {
                if ($original_text != 'product')
                    $is_tax = true;
                else
                    $is_tax = false;

                $translated_slug = $this->get_translated_rewrite_base_slug( $original_text, $language, $is_tax );
                if( $translated_slug )
                    $text = $translated_slug;
            }
        }

        return $text;
    }

    /**
     * Function that deletes the transients for woocommerce when a different translation is saved
     * @param $option_name
     * @param $type
     * @param $translations
     * @param $all_strings
     * @param $original_translations
     */
    function reset_woocommerce_transients($option_name, $type, $translations, $all_strings, $original_translations ){
        if( $type === 'post-type-base-slug' || $type === 'taxonomy-slug' ){
            if( !empty($all_strings) ){
                foreach( $all_strings as $language => $new_translations ){
                    if( !empty( $new_translations ) ){
                        $delete_transients = false;
                        foreach( $new_translations as $translation ){
                            //remove any slashes before comparison
                            $translation['id'] = trim($translation['id'], '/\\');
                            //consider woocommerce slugs as they are written in the Permalinks section of admin
                            $slugs = $this->option_based_strings->get_woocommerce_actual_slugs();
                            $all_slugs = array_unique( array_merge( $slugs, array( 'product', 'product-category', 'product-tag' ) ) );
                            if( in_array( $translation['id'], $all_slugs ) ){
                                $delete_transients = true;
                                break;
                            }
                        }

                        if( $delete_transients ){
                            delete_transient('tp_default_language_wc_permalink_structure_' . $this->settings['default-language']);
                            delete_transient('tp_current_language_wc_permalink_structure_' . $language);
                            delete_transient('tp_'.$translation['id'].'_'. $language);
                        }
                    }
                }
            }
        }
    }

    /**
     * Function that retrieves the lug of the shop page of woocomemerce in a language
     * @param $language
     * @return mixed|string|null
     */
    public function get_woocommerce_shop_slug_in_language( $language ){
        if( class_exists( 'WooCommerce' ) ){
            $shop_page_id = wc_get_page_id('shop');
            if( $shop_page_id ) {
                $shop_page_object = get_post($shop_page_id);
                if( $shop_page_object ) {
                    $shop_page_slug_in_language = $this->get_translated_slug($shop_page_object, $language);
                    if ($language === $this->settings['default-language'] || empty($shop_page_slug_in_language))
                        $shop_page_slug_in_language = $shop_page_object->post_name;

                    return $shop_page_slug_in_language;
                }
            }
        }
        return null;
    }

    /**
     * Function to allow product_cat translation in the permalinks for products.
     */
    function woocommerce_product_cat_in_permalinks( $category_object, $terms, $post ){
        global $TRP_LANGUAGE;

        if( isset( $TRP_LANGUAGE ) && $TRP_LANGUAGE != $this->settings['default-language'] ) {
            if (is_object($category_object)) {
                if(isset($category_object->term_id)) {
                    $translated_slug = get_term_meta( $category_object->term_id, $this->human_translated_slug_meta . $TRP_LANGUAGE, true );
                    if ( empty( $translated_slug ) )//if no human translated slug try to find an automatic translated slug
                        $translated_slug = get_term_meta( $category_object->term_id, $this->automatic_translated_slug_meta . $TRP_LANGUAGE, true );
                    $translated_slug = trim( $translated_slug, '/\\' );
                }
                if( !empty($translated_slug) )
                    $category_object->slug = $translated_slug;
            }
        }

        return $category_object;
    }

    /**
     * function that normalizez built in category and tag taxonomies which have special query vars
     * @param $taxonomy
     * @return string
     */
    public function trp_normalize_taxonomy_names( $taxonomy ){
        if( $taxonomy === 'category_name' )
            $actual_taxonomy = 'category';
        else if( $taxonomy === 'tag' )
            $actual_taxonomy = 'post_tag';
        else
            $actual_taxonomy = $taxonomy;

        return $actual_taxonomy;
    }

    /**
     * Function that filters calls to get the option for taxonomy translation slugs so only the current active taxonomies remain
     * @param $trp_taxonomy_slug_translation
     * @return mixed
     */
    public function filter_taxonomy_translation_slugs_option( $trp_taxonomy_slug_translation ){
        global $TRP_LANGUAGE, $trp_taxonomy_slug_translation_transient;

        if( $TRP_LANGUAGE === $this->settings['default-language'] ){
            if( !$trp_taxonomy_slug_translation_transient ) {//don't do this more than once on a page load
                set_transient('trp_active_taxonomies_slugs', $this->option_based_strings->get_public_slugs('taxonomies'));
                $trp_taxonomy_slug_translation_transient = true;
            }
        }

        $current_taxonomies_slugs = get_transient( 'trp_active_taxonomies_slugs' );
        if( !empty( $current_taxonomies_slugs ) && is_array($current_taxonomies_slugs) ){

            if( !empty($trp_taxonomy_slug_translation) && is_array($trp_taxonomy_slug_translation) ) {
                foreach ($trp_taxonomy_slug_translation as $tax_slug => $tax_translations) {
                    if (!in_array( $tax_slug, $current_taxonomies_slugs)) {
                        unset($trp_taxonomy_slug_translation[$tax_slug]);
                    }
                }
            }
        }

        return $trp_taxonomy_slug_translation;
    }

    /**
     * Function that filters calls to get the option for cpt translation slugs so only the current active taxonomies remain
     * @param $trp_taxonomy_slug_translation
     * @return mixed
     */
    public function filter_cpt_translation_slugs_option( $trp_cpt_slug_translation ){
        global  $TRP_LANGUAGE, $trp_cpt_slug_translation_transient;

        //set a transient only on the default language
        if( $TRP_LANGUAGE === $this->settings['default-language'] ){
            if( !$trp_cpt_slug_translation_transient ) {//don't do this more than once on a page load
                set_transient('trp_active_cpt_slugs', $this->option_based_strings->get_public_slugs('post_types'));
                $trp_cpt_slug_translation_transient = true;
            }
        }

        //eliminate all translation pairs from db that are no longer registered (the post types don't exist) so they don't cause problems
        $current_cpt_slugs = get_transient( 'trp_active_cpt_slugs' );
        if( !empty( $current_cpt_slugs ) && is_array($current_cpt_slugs) ){

            if( !empty( $trp_cpt_slug_translation ) && is_array($trp_cpt_slug_translation) ) {
                foreach ($trp_cpt_slug_translation as $cpt_slug => $cpt_translations) {
                    if (!in_array( $cpt_slug, $current_cpt_slugs)) {
                        unset($trp_cpt_slug_translation[$cpt_slug]);
                    }
                }
            }
        }

        return $trp_cpt_slug_translation;
    }

    /**
     * with the Buisness Directory Plugin
     * filter the wpbdp_category option that is used by the plugin directly to create links
     **/
    public function business_directory_plugin_compatibility( $value ){
        global $TRP_LANGUAGE;

        if( is_admin() || $TRP_LANGUAGE === $this->settings['default-language'] )
            return $value;

        $translated = $this->get_translated_rewrite_base_slug( $value, $TRP_LANGUAGE, true );
        if($translated)
            return $translated;

        return $value;
    }

}
