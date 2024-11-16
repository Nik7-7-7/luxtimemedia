<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('OrderUlp')){
	 return;
}
class OrderUlp extends CustomPostTypeUlp{
	/**
	 * @var string
	 */
	protected $post_type_slug = 'ulp_order';
	/**
	 * @var int
	 */
	protected $menu_position = 6;
	/**
	 * @var array
	 */
	protected $supports = ['title', 'author'];
	public function __construct(){
		$this->labels = array(
								    'name'               => esc_html__('Orders', 'ulp'),
								    'singular_name'      => esc_html__('Order', 'ulp'),
								    'add_new'            => esc_html__('Add new Order', 'ulp'),
								    'add_new_item'       => esc_html__('Add new Order', 'ulp'),
								    'edit_item'          => esc_html__('Edit Order', 'ulp'),
								    'new_item'           => esc_html__('New Order', 'ulp'),
								    'all_items'          => esc_html__('All Orders', 'ulp'),
								    'view_item'          => esc_html__('View Order', 'ulp'),
								    'search_items'       => esc_html__('Search Order', 'ulp'),
								    'not_found'          => '',
								    'not_found_in_trash' => '',
								    'parent_item_colon'  => '',
								    'menu_name'          => esc_html__('Orders', 'ulp'),
		);
		$this->taxonomy_labels = array(
									'name'              => esc_html__('Categories Orders', 'ulp'),
									'singular_name'     => '',
									'search_items'      => '',
									'all_items'         => esc_html__('Categories Orders', 'ulp'),
									'parent_item'       => '',
									'parent_item_colon' => '',
									'edit_item'         => '',
									'update_item'       => '',
									'add_new_item'      => '',
									'new_item_name'     => '',
									'menu_name'         => esc_html__('Categories Orders', 'ulp'),
		);
		$this->taxonomy_slug = 'ulp_order_categories';
		$this->metaBoxes[] = array(
												'slug' => 'ulp_metas',
												'title' => esc_html__('Order Details', 'ulp'),
												'callback' => array($this, 'ulp_metas_box'),
												'context' => 'normal', // normal || side || advanced
												'priority' => 'high', /// high || low
		);
		add_action( 'add_meta_boxes', array($this, 'remove_meta_boxes' ), 10 );
		$this->run();
		add_filter('wp_insert_post_data', array($this, 'custom_save_order'));
		///
		add_action('init', array($this, 'register_order_status'));
	}
	public function custom_save_order($data=array()){
			if (isset($data ['post_type']) && $data ['post_type']==$this->post_type_slug && is_admin()){
					if (isset($_POST['ulp_status'])){
							$_POST['ulp_status'] = sanitize_text_field( $_POST['ulp_status'] );
							$data ['post_status'] = $_POST['ulp_status'];
					}
			}
			return $data;
	}
	public function register_order_status(){
		register_post_status('ulp_pending', [
			 'label' => esc_html__('Pending', 'ulp'),
			 'public'                    => false,
			 'exclude_from_search'       => false,
			 'show_in_admin_all_list'    => true,
			 'show_in_admin_status_list' => true,
		]);
		register_post_status('ulp_complete', [
			 'label' => esc_html__('Completed', 'ulp'),
			 'public'                    => false,
			 'exclude_from_search'       => false,
			 'show_in_admin_all_list'    => true,
			 'show_in_admin_status_list' => true,
		]);
		register_post_status('ulp_fail', [
			 'label' => esc_html__('Fail', 'ulp'),
			 'public'                    => false,
			 'exclude_from_search'       => false,
			 'show_in_admin_all_list'    => true,
			 'show_in_admin_status_list' => true,
		]);
	}
	/*
	 * @param none
	 * @return array
	 */
	protected function allMetaNames(){
		return array();
	}
	public function remove_meta_boxes(){
		remove_meta_box( 'submitdiv', $this->post_type_slug, 'side' );
		remove_meta_box( 'titlediv', $this->post_type_slug, 'normal' );
		remove_meta_box( 'slugdiv', $this->post_type_slug, 'normal' );
		remove_meta_box( 'authordiv', $this->post_type_slug, 'normal' );
		remove_meta_box( 'ulp_order_categoriesdiv', $this->post_type_slug, 'normal' );
	}
	/**
	 * @param int
	 * @return none
	 */
	public function afterSavePost($post_id=0){
		$postType = \DbUlp::getPostTypeById($post_id);
		if ($postType!=$this->post_type_slug){
				return;
		}
		
		if ($post_id){
			require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
			$DbUlpOrdersMeta = new DbUlpOrdersMeta();
			$userId = isset($_POST['username']) ? \DbUlp::getUidByUsername( sanitize_text_field($_POST['username']) ) : 0;
			if ( $userId ){
					$DbUlpOrdersMeta->save($post_id, 'user_id', $userId );
			}
			$orderCode = $this->_generate_order_code( $post_id );
			if ( $userId ){
					$DbUlpOrdersMeta->save( $post_id, 'code', $orderCode );
			}

			$metas = [
					'username',
					'course_id',
					'unique_identificator',
					'amount',
			];
			foreach ($metas as $meta_key){
					if (isset($_POST[$meta_key]))
							$DbUlpOrdersMeta->save($post_id, $meta_key, ulp_sanitize_array($_POST[$meta_key]) );
			}
			if (isset($_POST['original_post_status']) && isset($_POST['ulp_status']) && sanitize_text_field($_POST['original_post_status'])!=sanitize_text_field($_POST['ulp_status']) ){
					$order = new UlpOrder();
					$order->modify_status( sanitize_text_field($_POST['unique_identificator']), sanitize_text_field($_POST['ulp_status']), TRUE);
			}

			if ( !empty( $_POST['save'] ) ){
					wp_safe_redirect( admin_url( 'admin.php?page=ultimate_learning_pro&tab=ulp_order' ) );
					exit;
			}

		}
	}
	public function ulp_metas_box(){
		global $post;
		require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
		$DbUlpOrdersMeta = new DbUlpOrdersMeta();
		$metas = [
				'user_id',
				'course_id',
				'unique_identificator',
				'amount',
		];
		$post_id = isset($post->ID) ? $post->ID : 0;
		foreach ($metas as $meta_key){
				$meta_data ['metas'][$meta_key] = $DbUlpOrdersMeta->get($post_id, $meta_key);
		}

		if (isset($meta_data ['metas']['user_id'])){
			$meta_data ['metas']['avatar'] = DbUlp::getAuthorImage($meta_data ['metas']['user_id']);
			$meta_data ['metas']['username'] = DbUlp::getUsernameByUID($meta_data ['metas']['user_id']);
		}
		$source = $DbUlpOrdersMeta->getSource($post_id);
		if ($source){
              $meta_data ['metas']['source'] = ucfirst($source);
          }
		$meta_data ['courses'] = DbUlp::getAllCourses();
		$meta_data ['post_status'] = isset($post->post_status) ? $post->post_status : 'ulp_pending';

		$payment_settings = DbUlp::getOptionMetaGroup('payment_settings');
		$meta_data ['currency'] = $payment_settings['ulp_currency'];


		if ( isset( $meta_data['metas']['amount'] ) && $meta_data['metas']['amount'] !== null ){
				$meta_data['metas']['amount'] = number_format($meta_data['metas']['amount'], $payment_settings['ulp_num_of_decimals'], $payment_settings['ulp_decimals_separator'], $payment_settings['ulp_thousands_separator']);
		}

		/// output
		$view = new ViewUlp();
		$view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/order_metas.php');
		$view->setContentData($meta_data);
		echo esc_ulp_content($view->getOutput());
	}

	private function _generate_order_code($order_id=0)
	{
			$prefix = get_option('ulp_order_prefix_code');
			if (empty($prefix)){
				$prefix = 'ULP';
			}
			while (strlen($order_id)<6){
				$order_id = '0' . $order_id;
			}
			$the_code = $prefix . $order_id;
			return $the_code;
	}
}
