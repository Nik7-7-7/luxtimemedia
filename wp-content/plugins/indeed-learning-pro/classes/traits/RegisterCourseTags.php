<?php
if (!defined('ABSPATH')){
   exit();
}
if (trait_exists('RegisterCourseTags')){
   return;
}

trait RegisterCourseTags
{
  	public function registerTags()
  	{
  			$args = array(
  				'hierarchical'      => true,
  				'labels'            => [
  							'name'              => esc_html__('Courses Tags', 'ulp'),
  							'singular_name'     => esc_html__('Course Tag', 'ulp'),
  							'search_items'      => esc_html__('Search Course Tag', 'ulp'),
  							'all_items'         => esc_html__('Courses Tags', 'ulp'),
  							'parent_item'       => '',
  							'parent_item_colon' => '',
  							'edit_item'         => esc_html__('Edit', 'ulp'),
  							'update_item'       => esc_html__('Update', 'ulp'),
  							'add_new_item'      => esc_html__('Add new course tag', 'ulp'),
  							'new_item_name'     => esc_html__('Add new course tag', 'ulp'),
  							'menu_name'         => esc_html__('Courses Tags', 'ulp'),
  				],
  				'show_ui'           => true,
  				'show_admin_column' => true,
  				'query_var'         => true,
  				'rewrite'           => ['slug' => 'course_tags'],
  			);
  			register_taxonomy('course_tags', 'ulp_course', $args);
  	}
}
