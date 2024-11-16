<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('InstructorsUlp')){
	 return;
}
class InstructorsUlp extends CustomPostTypeUlp
{
	protected $post_type_slug = 'ulp-instructor';

  protected $supports = ['title', 'author'];

  public function __construct(){
		/// post type
		$this->labels = array(
								    'name'               => esc_html__('Instructors', 'ulp'),
								    'singular_name'      => esc_html__('Instructors', 'ulp'),
								    'add_new'            => esc_html__('Add new Instructor', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Instructor', 'ulp'),
								    'edit_item'          => esc_html__('Edit Instructor', 'ulp'),
								    'new_item'           => esc_html__('New Instructor', 'ulp'),
								    'all_items'          => esc_html__('All Instructors', 'ulp'),
								    'view_item'          => esc_html__('View Instructor', 'ulp'),
								    'search_items'       => esc_html__('Search Instructor', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Instructor', 'ulp'),
		);
		$this->run();
	}

  public function run(){
      add_action('init', array($this, 'registerPostType'));
  }

	public function registerPostType(){
		  $args = array(
		    'labels'             => $this->labels,
		    'public'             => true,
		    'publicly_queryable' => true,
		    'show_ui'            => true,
		    'show_in_menu'       => false,
		    'query_var'          => $this->post_type_slug,
				'rewrite'            => array( 'slug' => $this->post_type_slug ),
		    'capability_type'    => 'post',
		    'has_archive'        => true,
		    'hierarchical'       => false,
		    'menu_position'      => $this->menu_position,
		    'menu_icon'          => $this->menu_icon,
		    'supports'           => $this->supports,
		  );
		  register_post_type($this->post_type_slug, $args);
			if ($this->firstTimeRegister()){
					flush_rewrite_rules();
			}
	}

	protected function afterSavePost($post_id=0)
	{

	}


}
