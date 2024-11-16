<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('Ulp_Buddypress_Endpoint')){
	 return;
}
class Ulp_Buddypress_Endpoint{
		private $metas = array();
		public function __construct(){
  			$this->metas = DbUlp::getOptionMetaGroup('buddypress');
  			add_action('bp_setup_nav', array($this, 'do_setup_bp_nav'), 99);
		}
		public function do_setup_bp_nav(){
			global $current_user;
			if (empty($current_user) || empty($current_user->ID)){
				return;
			}
			global $bp;
			bp_core_new_nav_item( array(
					'name' => stripslashes($this->metas['ulp_buddypress_menu_label']),
					'slug' => 'ulp',
					'position' => $this->metas['ulp_buddypress_menu_possition'],
					'show_for_displayed_user' => false,
					'screen_function' => 'ulp_bp_content_action',
					'item_css_id' => 'ulp',
					'default_subnav_slug' => 'ulp'
				)
			);
			bp_core_new_subnav_item( array(
					'name' => esc_html__('Ultimate Learning Pro', 'ulp'),
					'slug' => 'ulp',
					'show_for_displayed_user' => false,
					'parent_url' => trailingslashit( bp_displayed_user_domain() . 'ulp'),
					'parent_slug' => 'ulp',
					'position' => $this->metas['ulp_buddypress_menu_possition'],
					'screen_function' => array($this, 'ulp_bp_content_action'),
					'item_css_id' => 'ulp',
					'user_has_access' => bp_is_my_profile()
				)
			);
		}
		public function ulp_bp_content_action(){
			 add_action('bp_template_content', array($this, 'ulp_bp_do_the_content'));
			 bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
		}
		public function ulp_bp_do_the_content(){
			echo do_shortcode('[ulp-student-profile is_buddypress=1]');
		}
}
