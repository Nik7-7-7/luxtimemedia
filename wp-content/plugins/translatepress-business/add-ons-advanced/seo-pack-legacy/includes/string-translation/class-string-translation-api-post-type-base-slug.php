<?php
if( !class_exists('TRP_String_Translation_API_Post_Type_Base_Slug') ) {
    class TRP_String_Translation_API_Post_Type_Base_Slug
    {
        protected $type = 'post-type-base-slug';
        protected $option_name = 'trp_post_type_base_slug_translation';
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
            $this->helper->check_ajax($this->type, 'get');

            $all_slugs = $this->option_based_strings->get_public_slugs('post_types');

            $return = $this->option_based_strings->get_strings_for_option_based_slug($this->type, $this->option_name, $all_slugs);

            echo trp_safe_json_encode($return);//phpcs:ignore
            wp_die();
        }

        public function save_strings()
        {

            $this->helper->check_ajax($this->type, 'save');

            $this->option_based_strings->save_strings_for_option_based_slug($this->type, $this->option_name);
        }

        /**
         * Get the type of the operation used for save_strings_for_option_based_slug()
         * @return string
         */
        public function get_type()
        {
            return $this->type;
        }

        /**
         * Get the option name for post type base slugs where they are translated
         * @return string
         */
        public function get_option_name()
        {
            return $this->option_name;
        }
    }
}
