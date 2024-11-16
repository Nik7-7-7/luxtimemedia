<?php
if (!defined('ABSPATH')){
	 exit();
}
if (!class_exists('CertificatesUlp')):
class CertificatesUlp extends CustomPostTypeUlp{
	/*
	 * @var string
	 */
	protected $post_type_slug = 'ulp_certificate';
	/*
	 * @var int
	 */
	protected $menu_position = 6;
	public function __construct(){
		$this->labels = array(
								    'name'               => esc_html__('Certificates', 'ulp'),
								    'singular_name'      => esc_html__('Certificate', 'ulp'),
								    'add_new'            => esc_html__('Add new Certificate', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Certificate', 'ulp'),
								    'edit_item'          => esc_html__('Edit Certificate', 'ulp'),
								    'new_item'           => esc_html__('New Certificate', 'ulp'),
								    'all_items'          => esc_html__('All Certificates', 'ulp'),
								    'view_item'          => esc_html__('View Certificate', 'ulp'),
								    'search_items'       => esc_html__('Search Certificate', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Certificates', 'ulp'),
		);
		$this->taxonomy_labels = array(
						'name'              => esc_html__('Certificate Categories', 'ulp'),
						'singular_name'     => esc_html__('Certificate Category', 'ulp'),
						'search_items'      => esc_html__('Search Certificate Category', 'ulp'),
						'all_items'         => esc_html__('Certificate Categories', 'ulp'),
						'parent_item'       => '',
						'parent_item_colon' => '',
						'edit_item'         => esc_html__('Edit', 'ulp'),
						'update_item'       => esc_html__('Update', 'ulp'),
						'add_new_item'      => esc_html__('Add new certificate category', 'ulp'),
						'new_item_name'     => esc_html__('Add new certificate category', 'ulp'),
						'menu_name'         => esc_html__('Certificate Categories', 'ulp'),
		);
		$this->taxonomy_slug = 'ulp_certificate_categories';
		$this->metaBoxes[] = array(
												'slug' => 'constantsBox',
												'title' => esc_html__('Ultimate Learning Pro - Constants', 'ulp'),
												'callback' => array($this, 'constantsBox'),
												'context' => 'normal', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		$this->run(); /// from parent class
	}
	/*
	 * @param none
	 * @return array
	 */
	protected function allMetaNames(){
		return array();
	}
	/**
	 * @param int
	 * @return none
	 */
	public function afterSavePost($post_id=0){}
		public function constantsBox(){
				$data = DbUlp::user_constants();
				$data = $data + [
						"{grade}" => '',
						'{course_name}' => '',
						'{obtained_date}' => '',
				];
				echo esc_ulp_content('
					<div class="ulp-inside-item">
  					  <div class="row">
   						<div class="col-xs-6">
						<p>'. esc_html__('Use the following constants to built your Certificate Template:', 'ulp').'</p>
				');
				foreach ($data as $key => $value){
					echo esc_html($key) .'</br>';
				}
				echo esc_ulp_content('
				  	   </div>
					  </div>
					 </div>
				  ');
		}
}
endif;
