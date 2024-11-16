<?php

class TRP_IN_Seo_Pack {

    protected $loader;
    protected $slug_manager;
    protected $settings;
    protected $url_converter;
    protected $render;
    /* @var TRP_Editor_Api_Slugs */
    protected $editor_api_post_slug;
    /* @var TRP_IN_SP_String_Translation_SEO */
    protected $string_translation;

    /**
     * Timezone.
     *
     * @var Timezone
     */
    public $timezone;

    public function __construct() {

        // This is needed in the TP core version to show message if Seo Pack needs update
        define( 'TRP_IN_SP_PLUGIN_VERSION', '1.4.0' );

        define( 'TRP_IN_SP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'TRP_IN_SP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/class-slug-manager.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/class-editor-api-post-slug.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-string-translation-seo.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-meta-based-strings.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-option-based-strings.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-string-translation-api-taxonomy-slug.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-string-translation-api-post-type-base-slug.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-string-translation-api-term-slug.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-string-translation-api-postslug.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'includes/string-translation/class-string-translation-api-woocommerce-slug.php';
        require_once TRP_IN_SP_PLUGIN_DIR . 'tp-seo-pack-activator.php';

        $trp                 = TRP_Translate_Press::get_trp_instance();
        $this->loader        = $trp->get_component( 'loader' );
        $this->url_converter = $trp->get_component( 'url_converter' );
        $trp_settings        = $trp->get_component( 'settings' );
        $this->settings      = $trp_settings->get_settings();
        $this->render        = $trp->get_component( 'translation_render' );;

        include_once('includes/class-timezone.php');
        $this->timezone = new TranslatePress\IN_Seo\Timezone;


        $this->slug_manager         = new TRP_IN_SP_Slug_Manager( $this->settings );
        $this->editor_api_post_slug = new TRP_IN_Editor_Api_Post_Slug( $this->settings, $this->slug_manager );
        $this->string_translation   = new TRP_IN_SP_String_Translation_SEO();

        $this->loader->add_filter( 'trp_node_accessors', $this, 'add_seo_node_accessor_details', 10, 1 );

	    $this->loader->add_filter( 'trp_st_string_types_config', $this->string_translation, 'add_string_translation_types', 10, 2 );
	    $this->loader->add_filter( 'trp_editors_navigation', $this->string_translation, 'enable_editors_navigation', 10, 1 );

        // not used in TP
        $this->loader->add_filter( 'trp_translate_slug', $this->slug_manager, 'get_translated_slug_filter', 10, 3 );

        $this->loader->add_action( 'wp_head', $this->slug_manager, 'add_slug_as_meta_tag', 1 );
        $this->loader->add_filter( 'request', $this->slug_manager, 'change_slug_var_in_request' );
        $this->loader->add_filter( 'pre_get_posts', $this->slug_manager, 'change_slug_var_in_query' );

        $this->loader->add_filter( 'sanitize_title', $this->slug_manager, 'change_query_for_page_by_page_slug', 10, 3 );
        $this->loader->add_filter( 'post_link', $this->slug_manager, 'translate_slug_for_posts', 99, 3 );
        $this->loader->add_filter( 'post_type_link', $this->slug_manager, 'translate_slug_for_posts', 99, 3 );
        $this->loader->add_filter( 'get_page_uri', $this->slug_manager, 'translate_slugs_for_pages', 10, 2 );
        $this->loader->add_filter( 'trp_translateable_strings', $this->slug_manager, 'include_slug_for_machine_translation', 10, 6 );
        $this->loader->add_action( 'trp_translateable_information', $this->slug_manager, 'save_machine_translated_slug', 10, 3 );

        $this->loader->add_action( 'wp_ajax_trp_get_translations_postslug', $this->editor_api_post_slug, 'postslug_get_translations' );
        $this->loader->add_action( 'wp_ajax_trp_save_translations_postslug', $this->editor_api_post_slug, 'postslug_save_translations' );

        $this->loader->add_filter( 'template_redirect', $this->slug_manager, 'redirect_to_translated_slug', 100, 2 );


        // Yoast SEO Sitemap Support
        if ( !apply_filters('trp_disable_languages_sitemap', false)){
            $this->loader->add_action( 'pre_get_posts', $this, 'wpseo_init_sitemap', 1 );
            $this->loader->add_action( 'wpseo_sitemap_url', $this, 'sitemap_add_language_urls', 10, 2 );
            // clear sitemap when saving TP settings.
            $this->loader->add_filter( 'trp_extra_sanitize_settings', $this, 'wpseo_clear_sitemap', 10 );
        }

        // RankMath Sitemap Support
        if ( !apply_filters('trp_disable_languages_sitemap', false)){
            $this->loader->add_action( 'parse_query', $this, 'rankmath_init_sitemap', 0 );
            $this->loader->add_action( 'rank_math/sitemap/url', $this, 'sitemap_add_language_urls', 10, 2 );
        }

        //SeoPress Sitemap Support
        if ( !apply_filters('trp_disable_languages_sitemap', false)){
            $this->loader->add_action( 'seopress_sitemaps_url', $this, 'sitemap_add_language_urls', 10, 2 );
            $this->loader->add_action( 'seopress_sitemaps_urlset', $this, 'sitemap_add_xhtml_to_urlset', 10, 1 );
        }

        // All In One SEO Support
        if ( !apply_filters('trp_disable_languages_sitemap', false)){
            $this->loader->add_action( 'aiosp_sitemap_data', $this, 'aiosp_sitemap_data', 1, 4 );

            $this->loader->add_action( 'aioseo_sitemap_posts', $this, 'aiosp_sitemap_add_language_urls', 1, 1 );
            $this->loader->add_action( 'aioseo_sitemap_terms', $this, 'aiosp_sitemap_add_language_urls', 1, 1 );

            // we're not implementing the xhtml alternate yet. Maybe in a future update.
            // Also, we can't add the xhtml alternate tag to each url because there are no filters there.
            /* @to-do
             * create pull request for All In One SEO so we can add <xhtml:link rel='alternate' /> in a future version.
             **/
            //$this->loader->add_action( 'aiosp_sitemap_xml_namespace', $this, 'aiosp_sitemap_xml_namespace', 10, 1 );
        }


        //Taxonomy and CPT base slug translation, we filter the $args['rewrite']['slug']
        $this->loader->add_filter( 'register_taxonomy_args', $this->slug_manager, 'filter_registration_args_for_slug', 100, 2 );
        $this->loader->add_filter( 'register_post_type_args', $this->slug_manager, 'filter_registration_args_for_slug', 100, 2 );
        // Filter saved permalinks from db so we don't get 404 on translated slugs. This is for tax and cpt base slugs
        $this->loader->add_filter( "option_rewrite_rules", $this->slug_manager, 'filter_permalinks_on_other_languages', 10 );
        //Filter our on language switcher links, hopefully that's all it does :)
        $this->loader->add_filter( 'trp_get_url_for_language', $this->slug_manager, 'filter_language_switcher_link', 10, 3 );

        //filter slug translations for taxonomies and cpts so only the current active ones remain
        $this->loader->add_filter( 'option_'.$this->slug_manager->string_translation_api_tax_slug->get_option_name(), $this->slug_manager, 'filter_taxonomy_translation_slugs_option', 10 );

        // Commented out due to issue of post type slug translations sometimes disappearing. See iss#85zt5c6cq
        // $this->loader->add_filter( 'option_'.$this->slug_manager->string_translation_api_post_type_slug->get_option_name(), $this->slug_manager, 'filter_cpt_translation_slugs_option', 10 );

        //Term translation
        $this->loader->add_filter( 'term_link', $this->slug_manager, 'translate_term_link_slugs', 100, 3 );//filter term links
        $this->loader->add_filter( 'request', $this->slug_manager, 'change_term_slug_var_in_request' );//so we don't get 404
        $this->loader->add_filter( 'pre_get_posts', $this->slug_manager, 'change_term_slug_var_in_query' );//so we have propper results in queries

        //Handle %category% in custom permalinks structures
        $this->loader->add_filter( 'post_link_category', $this->slug_manager, 'filter_term_slugs_in_custom_permalink_structure', 10, 3 );
        $this->loader->add_filter( 'get_term', $this->slug_manager, 'filter_parent_term_slugs_in_custom_permalink_structure', 10 );

        //WooCommerce translation
        if( class_exists( 'WooCommerce' ) ) {
            $this->loader->add_filter('trp_x', $this->slug_manager, 'translate_woocommerce_main_slugs', 10, 5);//filters product, product-category and product-tag
            $this->loader->add_filter('trp_before_based_slug_save', $this->slug_manager, 'reset_woocommerce_transients', 10, 5);//filters product, product-category and product-tag

            $this->loader->add_filter('wc_product_post_type_link_product_cat', $this->slug_manager, 'woocommerce_product_cat_in_permalinks', 10, 3);//filters %product_cat% in custom product permalinks
        }

        //schema.org support
        $this->loader->add_filter( 'trp_before_translate_content', $this, 'append_schema_data', 10 );//append in translation editor the nodes we want to translate to the html so we have access in the String Dropdown
        $this->loader->add_filter( 'trp_process_other_text_nodes', $this, 'translate_schema_data', 10 );//translate the nodes inside the schema json

        //add compatibility with the Buisness Directory Plugin
        $this->loader->add_filter('wpbdp_get_option_permalinks-category-slug', $this->slug_manager, 'business_directory_plugin_compatibility', 10 );//filter the wpbdp_category option that is used by the plugin directly to create links

        $this->loader->add_action( 'plugins_loaded', $this, 'check_for_necessary_updates', 10 );
    }

    public function wpseo_init_sitemap() {
        global $wp_query;
        if ( !empty( $wp_query ) ) {
            $type = get_query_var( 'sitemap', '' );
            add_filter( "wpseo_sitemap_{$type}_urlset",  array( $this, 'sitemap_add_xhtml_to_urlset' ) );
        }
    }

    public function rankmath_init_sitemap(){
        global $wp_query;
        if( !empty($wp_query) ){
            $type = get_query_var( 'sitemap', '' );
            add_filter( "rank_math/sitemap/{$type}_urlset",  array( $this, 'sitemap_add_xhtml_to_urlset' ) );
        }
    }

    public function sitemap_add_xhtml_to_urlset( $urlset ){
        $urlset = str_replace(  '<urlset', '<urlset xmlns:xhtml="http://www.w3.org/1999/xhtml" ', $urlset);
        return $urlset;
    }

    public function sitemap_add_language_urls( $output, $url ){

        if (empty($url['loc'])) {
            return $output;
        }

        $date = null;

        $url = apply_filters( 'trp_filter_url_sitemap_before_output', $url );

        if ( ! empty( $url['mod'] ) ) {
            $date = $this->timezone->format_date( $url['mod'] );
        }

        $trp           = TRP_Translate_Press::get_trp_instance();
        $url_converter = $trp->get_component( 'url_converter' );
        $settings      = $this->settings;
        $languages     = $settings['publish-languages'];

        $alternate       = '';
        $other_lang_urls = array();

        /* The original sitemaps urls are generated in a translation language instead of a default language if
         * the "Use subdirectory for default language" is on and the first language is not the default one.
         * Thus the urls come with the language slug of an translated language and all the other ones
         * except $original_language need to be generated.
        */
        $original_language = ( isset( $this->settings['add-subdirectory-to-default-language'] ) && $this->settings['add-subdirectory-to-default-language'] == 'yes' && isset( $this->settings['publish-languages'][0] ) ) ? $settings['publish-languages'][0] : $settings['default-language'];

        $region_independent_languages           = array();
        $hreflang_duplicates                    = array();
        $hreflang_duplicates_region_independent = array();

        foreach ( $languages as $language ) {
            $add_language = apply_filters( 'trp_add_language_url_to_sitemap', true, $language, $url, $output );

            if ( ! $add_language ){
                continue;
            }
            // hreflang should have - instead of _ . For example: en-EN, not en_EN like the locale

            if ( apply_filters( 'trp_add_country_hreflang_tags', true ) ) {
                $hreflang              = $url_converter->strip_formality_from_language_code( $language ); // returns the language without formality
                // hreflang should have - instead of _ . For example: en-EN, not en_EN like the locale
                $hreflang              = str_replace( '_', '-', $hreflang );
                $hreflang              = apply_filters( 'trp_hreflang', $hreflang, $language );
                $hreflang_duplicates[] = $hreflang;
                $alternate .= '<xhtml:link rel="alternate" hreflang="' . esc_attr( $hreflang ) . '" href="' . esc_url( $url_converter->get_url_for_language( $language, $url["loc"] ) ) . '"/>' . "\n";
            }

            if ( apply_filters( 'trp_add_region_independent_hreflang_tags', true ) ) {
                $language_independent_hreflang = strtok( $language, '_' );
                $language_independent_hreflang = apply_filters( 'trp_hreflang', $language_independent_hreflang, $language );
                if ( !empty( $language_independent_hreflang ) && !in_array( $language_independent_hreflang, $region_independent_languages ) ) {
                    $region_independent_languages[]                      = $language_independent_hreflang;
                    $hreflang_duplicates_region_independent[ $language ] = '<xhtml:link rel="alternate" hreflang="' . esc_attr( $language_independent_hreflang ) . '" href="' . esc_url( $url_converter->get_url_for_language( $language, $url["loc"] ) ) . '"/>' . "\n";

                }
            }

            if ( $language != $original_language ) {
                $lastmod = '';
                if (!empty( $date )){
                    $lastmod = "<lastmod>" . htmlspecialchars($date) . "</lastmod>\n";
                }

                // add images if it's set
                $images = '';
                if( isset($url['images']) && is_array($url['images']) ){
                    foreach ($url['images'] as $image) {
                        $images .= "<image:image><image:loc>{$image['src']}</image:loc></image:image>\n";
                    }
                }

                // add news tags if it's set. SEOPress uses them.
                $news = '';
                if( isset($url['news']) && is_array($url['news']) ){
                    $news .= '<news:news>';
                    $news .= "\n";
                    $news .= '<news:publication>';
                    $news .= "\n";
                    $news .= '<news:name>'.$url['news']['name'].'</news:name>';
                    $news .= "\n";
                    $news .= '<news:language>'. $hreflang .'</news:language>';
                    $news .= "\n";
                    $news .= '</news:publication>';
                    $news .= "\n";
                    $news .= '<news:publication_date>';
                    $news .= $url['news']['publication_date'];
                    $news .= '</news:publication_date>';
                    $news .= "\n";
                    $news .= '<news:title>';
                    $news .= $url['news']['title'];
                    $news .= '</news:title>';
                    $news .= "\n";
                    $news .= '</news:news>';
                    $news .= "\n";
                }

                $other_lang_urls[] = "\n<url>\n<loc>" . esc_url($url_converter->get_url_for_language($language, $url["loc"]) ) . "</loc>\n" . $lastmod . $images . $news ;
            }
        }

        foreach ( $languages as $language ) {
            $language_hreflang = strtok( $language, '_' );
            $language_hreflang = apply_filters( 'trp_hreflang', $language_hreflang, $language );
            if ( !in_array( $language_hreflang, $hreflang_duplicates ) ) {
                if ( isset( $hreflang_duplicates_region_independent[ $language ] ) ) {
                    $alternate .= $hreflang_duplicates_region_independent[ $language ]; /* phpcs:ignore */ /* escaped inside the array */
                }
            }
        }

        // add support for x-default hreflang.
        if ( !empty( $this->settings['trp_advanced_settings']['enable_hreflang_xdefault'] ) && $this->settings['trp_advanced_settings']['enable_hreflang_xdefault'] != 'disabled' && in_array( $this->settings['trp_advanced_settings']['enable_hreflang_xdefault'], $this->settings['translation-languages'] ) ) {
            $default_lang = $this->settings['trp_advanced_settings']['enable_hreflang_xdefault'];
            $alternate .= "<xhtml:link rel='alternate' hreflang='x-default' href='" . esc_url( $url_converter->get_url_for_language( $default_lang, $url["loc"] ) ) . "' />\n";
        }

        foreach ( $other_lang_urls as &$value){
            $value .= $alternate . "</url>\n";
        }
        $all_lang_urls = implode( '', $other_lang_urls );

        $new_output = str_replace("</url>", $alternate . "</url>" . $all_lang_urls , $output);

        /* Add the language slug to URL's in the case it is not present and
         * Use a subdirectory for the default language is set to Yes
         */
	    if(isset($settings["add-subdirectory-to-default-language"]) && $settings["add-subdirectory-to-default-language"] ==='yes' && $url_converter->get_lang_from_url_string($url['loc']) === null ) {
		    $new_output = str_replace( '<loc>' . $url['loc'] . '</loc>', '<loc>' . $url['loc'] .$url_converter->get_url_slug($original_language, false)."/"."</loc>", $new_output );
	    }

	    /* Clean the final output for any leftover #TRPLINKPROCESSED strings as they are not needed after
         * An alternative to doing that here would be in the class-url-converter inside get_url_for_language function
        */
	    $new_output = str_replace("#TRPLINKPROCESSED", '', $new_output);
        return apply_filters( 'trp_xml_sitemap_output_for_url', $new_output, $output, $settings, $alternate, $all_lang_urls, $url );
    }

    static function wpseo_clear_sitemap($settings){
        trp_in_sp_wpseo_clear_sitemap();
        return $settings;
    }

    public function aiosp_sitemap_xml_namespace($namespace){
        $namespace['xhtml'] = 'http://www.w3.org/1999/xhtml';
        return $namespace;
    }

    public function aiosp_sitemap_data($sitemap_data, $sitemap_type, $page_number, $aioseop_options){

        if( $sitemap_type == 'root' )
            return $sitemap_data;

        return $this->aiosp_sitemap_add_language_urls( $sitemap_data );

    }

    public function aiosp_sitemap_add_language_urls( $entries ){

        if( empty( $entries ) )
            return $entries;

        $trp_sitemap_data = [];

        foreach( $entries as $url ){
            $trp                = TRP_Translate_Press::get_trp_instance();
            $url_converter      = $trp->get_component( 'url_converter' );
            $settings           = $this->settings;
            $languages          = $settings['publish-languages'];
            $trp_render         = $trp->get_component('translation_render');

            if ( $trp_render->is_first_language_not_default_language() ) {
                $url['loc'] = $url_converter->get_url_for_language( $settings['default-language'], $url["loc"], null );
            }

            $trp_sitemap_data[] = $url;
            foreach( $languages as $language ){
                $add_language = apply_filters( 'trp_add_language_url_to_sitemap', true, $language, $url, '' );

                if ( ! $add_language )
                    continue;

                $url_backup = $url;
                if( $language != $settings['default-language'] ){
                    $url['loc'] = $url_converter->get_url_for_language($language, $url["loc"], null) ;

                    $trp_sitemap_data[] = $url;
                    $url['loc'] = $url_backup['loc'];
                }
            }
        }

        return $trp_sitemap_data;

    }

    public function add_seo_node_accessor_details( $node_accessor_array ){
	    $node_accessor_array['image_alt'] = array(
		    'selector' => 'img[alt]',
		    'accessor' => 'alt',
		    'attribute' => true
	    );


	    $node_accessor_array['meta_desc'] = array(
		    'selector' => 'meta[name="description"],meta[property="og:title"],meta[property="og:description"],meta[property="og:site_name"],meta[property="og:image:alt"],meta[name="twitter:title"],meta[name="twitter:description"],meta[name="twitter:image:alt"],meta[name="DC.Title"],meta[name="DC.Description"],meta[property="article:section"],meta[property="article:tag"]',
		    'accessor' => 'content',
		    'attribute' => true
	    );

	    $node_accessor_array['page_title'] = array(
		    'selector' => 'title',
		    'accessor' => 'innertext',
		    'attribute' => false
	    );

	    $node_accessor_array['meta_desc_img'] = array(
		    'selector' => 'meta[property="og:image"],meta[property="og:image:secure_url"],meta[name="twitter:image"]',
		    'accessor' => 'content',
		    'attribute' => true
	    );

	    return $node_accessor_array;
    }

    /**
     * Function that appends the nodes from schema.org json to the html when we are in translation editor so those strings are detected and can be translated
     * @param string $output the html string from translate_page function before it gets processed
     * @return string returns the html string with the schema strings attached or the original one if no schema detected
     */
    public function append_schema_data( $output ){
        //check to see if we are in the editor
        $preview_mode = isset($_REQUEST['trp-edit-translation']) && $_REQUEST['trp-edit-translation'] == 'preview';
        if ($preview_mode) {//only do this in the editor

            $json_array = json_decode($output);
            if (!$json_array) {

                //try to create html object with the dom parser
                $html = TranslatePress\str_get_html( $output, true, true, TRP_DEFAULT_TARGET_CHARSET, false, TRP_DEFAULT_BR_TEXT, TRP_DEFAULT_SPAN_TEXT );
                if ( $html ) {

                    foreach ( $html->find( 'script[type="application/ld+json"]' ) as $schema ) {//get all the schema
                        $schema_content = $schema->innertext;

                        if ( $schema_content ) {
                            global $json_schema_remaining_array;
                            $json_schema_remaining_array = array();
                            $this->process_schema_json( $schema_content, 'get_schema_nodes' );

                            if ( !empty( $json_schema_remaining_array ) ) {//if we have text from the schema append it to the end of the body tag
                                $body = $html->find( 'body', 0 );
                                if ( $body ) {
                                    $append_schema_info = '';
                                    foreach ( $json_schema_remaining_array as $schema_value ) {
                                        $append_schema_info .= '<div style="display:none">' . $schema_value . '</div>';//don't show it to the user
                                    }
                                    $body->innertext .= $append_schema_info;
                                }
                            }
                        }
                    }

                    $output = $html->save();
                }
            }
        }

        return $output;
    }


    /**
     * Function that translates some of the leaves of a json schema and replaces them in the dom node
     * @param $row a node from html DOM parser
     * @return mixed the node with some of the leaves translated
     */
    function translate_schema_data( $row ){

        $outertext = $row->outertext;
        $parent = $row->parent();
        $trimmed_string = trp_full_trim( $outertext );

        if( $parent->tag === "script" && isset( $parent->attr['type'] ) && $parent->attr['type'] === "application/ld+json"){//this is the type of the script that contains the json
            $json_schema_array = $this->process_schema_json($trimmed_string, 'translate_schema'); //translate here
            if ($json_schema_array !== false) {
                $row->outertext = trp_safe_json_encode( $json_schema_array ); //reencode the JSON
            }
        }

        return $row;
    }

    /**
     * Function that aplies a callback to a valid json object
     * @param $json_text the json in text form
     * @param $action_type
     * @return array|false|mixed
     */
    function process_schema_json( $json_text, $action_type ){
        $json_schema_array = json_decode( $json_text, true );
        if( $json_schema_array && $json_schema_array != $json_text ) { //if we successfully decoded the json
            if ( is_array( $json_schema_array ) ) {
                array_walk_recursive($json_schema_array, array( $this, $action_type ) );//apply the callback
                return $json_schema_array;
            }

        }

        return false;
    }

    /**
     * Funciton that returns the keys of the schema that we allow translation
     * @return mixed|void
     */
    function get_schema_node_keys(){
        return apply_filters('trp_schema_node_keys', array( 'name', 'description', 'text' ) );
    }

    /**
     * Callback function that passes through the schema json and populates a global array with the desired text in certain keys
     * @param $value
     * @param $key
     */
    function get_schema_nodes( $value, $key ){
        global $json_schema_remaining_array;
        $schema_node_keys = $this->get_schema_node_keys();
        if( in_array( $key, $schema_node_keys ) ){
            if( !in_array( $value,  $json_schema_remaining_array ) )//don't duplicate strings
                $json_schema_remaining_array[] = $value;
        }
    }

    /**
     * Callback function that translates some of the keys in the json
     * @param $value
     * @param $key
     */
    function translate_schema( &$value, $key ){
        $schema_node_keys = $this->get_schema_node_keys();
        if( in_array( $key, $schema_node_keys ) ) {
            $value = $this->render->translate_page($value);
        }
    }

    /**
     * When changing seo pack version, call certain database upgrade functions.
     *
     */
    public function check_for_necessary_updates(){
        // Updates that can be done right way. They should take very little time.
        $stored_database_version = get_option('trp_seopack_version');

        if( empty($stored_database_version) ){
            // if empty, it's either a fresh install, or it had version 1.3.9 or lower prior to updating
            $this->remove_incorrectly_translated_post_based_slugs_exterior_slashes_from_slugs_in_db();
            $this->remove_incorrectly_translated_taxonomy_slugs_from_db();
        }else{


            /**
             * Write an upgrading function above this comment to be executed only once: while updating plugin to a higher version.
             * Use example condition: version_compare( $stored_database_version, '2.9.9', '<=')
             * where 2.9.9 is the current version, and 3.0.0 will be the updated version where this code will be launched.
             */
        }

        // don't update the db version unless they are different. Otherwise the query is run on every page load.
        if( version_compare( TRP_IN_SP_PLUGIN_VERSION, $stored_database_version, '!=' ) ){
            update_option( 'trp_seopack_version', TRP_IN_SP_PLUGIN_VERSION );
        }
    }


    public function remove_incorrectly_translated_post_based_slugs_exterior_slashes_from_slugs_in_db(){
        $data = get_option( 'trp_post_type_base_slug_translation', array() );

        foreach ($data as $key => $values_array) {
            $key_holder = $key;
            $key = trim($key, '\\/');
            $data[ $key ] = $values_array;
            if ($key !== $key_holder) {
                unset( $data[ $key_holder ] );
            }
            unset($data[null]);
            foreach ( $data[ $key ] as $item => $value_array ) {
                if ( $item === 'original' ) {
                    $data[ $key ]['original'] = trim($value_array, '\\/');
                }
                if ( $item === 'translationsArray' ) {
                    foreach ( $value_array as $lang => $value ) {
                        if ( $value['translated'] == "-2"  || $value['translated'] == "-" ) {
                            $data[$key][$item][$lang]['translated']         = "";
                            $data[$key][$item][$lang]['editedTranslation']  = "";
                            $data[$key][$item][$lang]['status']             = "0";
                        }
                        $data[ $key ]['translationsArray'][ $lang ]['translated']        = trim( $data[$key][$item][$lang]['translated'] , '\\/' );
                        $data[ $key ]['translationsArray'][ $lang ]['editedTranslation'] = trim( $data[$key][$item][$lang]['editedTranslation'], '\\/' );
                        $data[ $key ]['translationsArray'][ $lang ]['id']                = trim( $data[$key][$item][$lang]['id'], '\\/' );
                        if (isset($value['original'])){
                            $data[ $key ]['translationsArray'][ $lang ]['original']          = trim( $data[$key][$item][$lang]['original'], '\\/' );
                        }
                    }
                }
            }
        }
        update_option('trp_post_type_base_slug_translation', $data);
    }

    public function remove_incorrectly_translated_taxonomy_slugs_from_db(){
        $trp = TRP_Translate_Press::get_trp_instance();
        $trp_delete_woocommerce_transients = $trp->get_component( 'url_converter' );

        $data = get_option('trp_taxonomy_slug_translation', array());

        foreach ($data as $key => $values_array) {
            foreach ( $values_array as $item => $value_array ) {
                if ( $item === 'translationsArray' ) {
                    foreach ( $value_array as $lang => $value ) {
                        if ($key === "-"){
                            unset($data[$key]);
                        }

                        if ( $value["translated"] == "-" || $value["translated"] == "-2") {
                            $data[$key]['translationsArray'][$lang]['translated']         = "";
                            $data[$key]['translationsArray'][$lang]['editedTranslation']  = "";
                            $data[$key]['translationsArray'][$lang]['status']             = "0";
                        }
                    }
                }
            }

        }

        update_option('trp_taxonomy_slug_translation', $data);
        $trp_delete_woocommerce_transients->delete_woocommerce_transient_permalink(false);
    }

}
