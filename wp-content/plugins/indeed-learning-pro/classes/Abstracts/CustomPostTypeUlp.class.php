<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('CustomPostTypeUlp')):
abstract class CustomPostTypeUlp{
	/**
	 * @var string
	 */
	protected $post_type_slug = '';
	/**
	 * @var array
	 */
	protected $labels = array(
							    'name'               => '',
							    'singular_name'      => '',
							    'add_new'            => '',
							    'add_new_item'       => '',
							    'edit_item'          => '',
							    'new_item'           => '',
							    'all_items'          => '',
							    'view_item'          => '',
							    'search_items'       => '',
							    'not_found'          => '',
							    'not_found_in_trash' => '',
							    'parent_item_colon'  => '',
							    'menu_name'          => '',
	);
	/**
	 * @var int
	 */
	protected $menu_position = 1;
	/**
	 * @var array
	 */
	protected $supports = array('title', 'editor', 'thumbnail', 'author', 'excerpt', 'comments');
	/**
	 * @var string
	 */
	protected $menu_icon = 'dashicons-book';
	/**
	 * @var string
	 */
	protected $taxonomy_slug = '';
	/**
	 * @var array
	 */
	protected $taxonomy_labels = array(
							'name'              => '',
							'singular_name'     => '',
							'search_items'      => '',
							'all_items'         => '',
							'parent_item'       => '',
							'parent_item_colon' => '',
							'edit_item'         => '',
							'update_item'       => '',
							'add_new_item'      => '',
							'new_item_name'     => '',
							'menu_name'         => '',
	);
	/**
	 * @var array
	 */
	protected $metaBoxes = array();
	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){}
	protected function allMetaNames(){}
	/**
	 * @param none
	 * @return none
	 */
	public function run(){
		if(in_array($this->post_type_slug, array('ulp_course','ulp_lesson','ulp_quiz','ulp_question','ulp_certificate'))){
			$this->metaBoxes[] = array(
												'slug' => 'linktosettings',
												'title' => esc_html__('Ultimate Learning Pro', 'ulp'),
												'callback' => array($this, 'ulp_settings_link'),
												'context' => 'side', // normal || side || advanced
												'priority' => 'high', /// high || low
			);
		}
		add_action('init', array($this, 'registerPostType'));
		add_action('init', array($this, 'registerTaxonomy'));
		add_action('save_post', array($this, 'savePostAction'), 1, 99);
		add_action('save_post', array($this, 'afterSavePost'), 1, 1000);
		add_action('add_meta_boxes', array($this, 'registerMetaBoxes'));
	}
	/**
	 * @param none
	 * @return none
	 */
	public function registerPostType(){
			$enableGutenberg = (bool)get_option('ulp_enable_gutenberg');
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
				'show_in_rest' 			 => $enableGutenberg,
		  );
		  register_post_type($this->post_type_slug, $args);
			if ($this->firstTimeRegister()){
					flush_rewrite_rules();
			}
	}
	/**
	 * @param none
	 * @return none
	 */
	public function registerTaxonomy(){
		$args = array(
			'hierarchical'      => true,
			'labels'            => $this->taxonomy_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array('slug'=>$this->taxonomy_slug),
		);
		register_taxonomy($this->taxonomy_slug, $this->post_type_slug, $args);
	}
	/**
	 * @param int
	 * @return none
	 */
	public function savePostAction($post_id=0){
		$keys = $this->getMetaNames();
		if (empty($keys)){
			 return;
		}
		foreach ($keys as $key){
			if (isset($_POST[$key])){
				update_post_meta($post_id, $key, ulp_sanitize_array($_POST[$key]) );
			}
		}
	}
	/**
	 * @param int
	 * @return none
	 */
	abstract protected function afterSavePost($post_id=0);
	/**
	 * @param int (post id)
	 * @param array (list of meta post key to search for)
	 * @return array
	 */
	protected function getPostMetas($post_id=0, $keys=array()){
		$array = array();
		if (empty($keys)){
			 return $array;
		}
		foreach ($keys as $key){
			$array[$key] = get_post_meta($post_id, $key, TRUE);
		}
		return $array;
	}
	/**
	 * @param string
	 * @return array
	 */
	protected function getMetaNames($group=''){
		$array = $this->allMetaNames();
		if (empty($array)){
			 return array();
		}
		if ($group){
			return $array[$group];
		}
		$new_array = array();
		foreach ($array as $key=>$subarray){
			$new_array = array_merge($new_array, $subarray);
		}
		return $new_array;
	}

	/**
	 * @param none
	 * @return none
	 */
	public function registerMetaBoxes()
	{
			$count = count( $this->metaBoxes );
			if ( empty( $count ) ){
					return;
			}
			foreach ( $this->metaBoxes as $key => $meta_box ){
					if ( !empty( $meta_box ) ){
							add_meta_box($meta_box['slug'], $meta_box['title'], $meta_box['callback'], $this->post_type_slug, $meta_box['context'], $meta_box['priority']);
					}
			}
	}

	/**
	 * @param object
	 * @return string
	 */
	public function ulp_settings_link($post=null){
		/// output
		if (isset($post->ID)){
			$data = array(
							'url' => admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $post->post_type . '&id=' . $post->ID),
							'label' => esc_html__('Special Settings', 'ulp'),
							'class' => 'ulp_specialsettings_link',
							'id' => '',
							'target' => 'target="_blank"',
			);
			$view = new ViewUlp();
			$view->setTemplate(ULP_PATH . 'views/templates/sections/link.php');
			$view->setContentData($data);
			echo esc_ulp_content($view->getOutput());
		} else {
			echo esc_html__('Settings will be available after you save the post.', 'ulp');
		}
	}

	protected function firstTimeRegister()
	{
			return false;
	}

}
endif;
