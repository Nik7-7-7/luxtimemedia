<?php
if( !class_exists('TRP_String_Translation_API_WooCommerce_Slug') ) {
    class TRP_String_Translation_API_WooCommerce_Slug
    {
        protected $config;
        protected $helper;
        protected $option_based_strings;
        protected $settings;

        public function __construct($settings)
        {
            $this->settings = $settings;
            $this->helper = new TRP_String_Translation_Helper();
            $this->option_based_strings = new TRP_IN_SP_Option_Based_Strings();
        }

        public function get_strings()
        {
            $this->helper->check_ajax('woocommerce-slug', 'get');

            $woo_cpt_slugs = $this->option_based_strings->get_public_slugs('post_types', false, apply_filters('trp_get_woocommerce_cpt', array('product')));
            $cpt_strings = $this->option_based_strings->get_strings_for_option_based_slug('post-type-base-slug', 'trp_post_type_base_slug_translation', $woo_cpt_slugs);

            $woo_tax_slugs = $this->option_based_strings->get_public_slugs('taxonomies', false, apply_filters('trp_get_woocommerce_taxonomies', array('product_cat', 'product_tag')));
            $taxonomy_strings = $this->option_based_strings->get_strings_for_option_based_slug('taxonomy-slug', 'trp_taxonomy_slug_translation', $woo_tax_slugs);

            $return = array(
                'dictionary' => array_merge($cpt_strings['dictionary'], $taxonomy_strings['dictionary']),
                'totalItems' => $cpt_strings['totalItems'] + $taxonomy_strings['totalItems']
            );

            echo trp_safe_json_encode($return);//phpcs:ignore
            wp_die();
        }

        // saving will go through cpt and taxonomies save functions
        public function save_strings()
        {

        }
    }
}
