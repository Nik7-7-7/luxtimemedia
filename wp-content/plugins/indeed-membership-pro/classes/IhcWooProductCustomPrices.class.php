<?php
if (!class_exists('IhcWooProductCustomPrices')):

class IhcWooProductCustomPrices{
	/**
	 * @var boolean
	 */
	private static $user_set = FALSE;
	/**
	 * @var array
	 */
	private static $output_prices = array();
	/**
	 * @var array(input prices)
	 */
	private static $input_prices = array();
	/**
	 * @var array
	 */
	private $user_levels = array();
	/**
	 * @var array
	 */
	private $module_metas = array();


	/**
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$this->setModuleMetas();
		if (!empty($this->module_metas['ihc_woo_product_custom_prices_enabled'])){
			add_action('init', array($this, 'register'));
		}
	}


	/**
	 * @param none
	 * @return none
	 */
	public function register(){
		if (!defined('WC_VERSION')){
			return;
		}
		if ( is_admin() ){
				return;
		}
		if (version_compare(WC_VERSION, '3.0.1')==-1){
			/// < v3.0.1
			add_filter('woocommerce_get_price', array($this, 'return_price'), 999, 2);
		} else {
			add_filter('woocommerce_product_get_price', array($this, 'return_price'), 999, 2);
			add_filter('woocommerce_product_variation_get_price', array($this, 'return_price'), 999, 2);
		}

		if (!empty($this->module_metas['ihc_woo_product_custom_prices_like_discount']) ){
			add_filter('woocommerce_get_price_html', array($this, 'print_as_discount'), 999, 2);

		} else {
			add_filter( 'woocommerce_format_sale_price', [ $this, 'get_sale_price' ], 999, 3 );
		}

		add_filter('woocommerce_variable_price_html', array($this, 'variable_price'), 999, 2);

	}


	/**
	 * @param none
	 * @return none
	 */



	private function setUserLevels(){
		global $current_user;
		$uid = isset($current_user->ID) ? $current_user->ID : 0;
		self::$user_set = TRUE;
		if ($uid){
			///getting levels for current user
			$data = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, true );
			if ($data){
				$this->user_levels = array_keys($data);
			}
		} else {
			/// remove filter
			remove_filter('woocommerce_get_price', array($this, 'return_price'));
		}
	}


	/**
	 * @param none
	 * @return none
	 */
	private function setModuleMetas(){
		$this->module_metas = ihc_return_meta_arr('woo_product_custom_prices');
	}


	/**
	 * @param float ($price)
	 * @param object ($product) - all infos about current product
	 * @return float (final price)
	 */
	public function return_price($price=0, $product=null ){///WC_Product
			if (empty(self::$user_set)){
				$this->setUserLevels();
			}
			if (version_compare(WC_VERSION, '3.0.1')==-1){
				/// < v3.0.1
				$product_id = isset($product->id) ? $product->id : 0;
			} else {
				$product_id = $product->get_id();
			}

			if ($this->user_levels && $product_id){

					$possible = array();

					/// Categories
					$cats_ids = array();
					$cats = Ihc_Db::woo_get_product_terms_as_string($product_id);
					$cats_ids = empty($cats) ? array() : explode(',', $cats);

					foreach ($this->user_levels as $lid){
						$settings = Ihc_Db::ihc_woo_products_get_discount_by_lid_prodid($product_id, $lid, $cats_ids);
						if ( empty( $settings ) && $product->get_parent_id() ){
							$cats = Ihc_Db::woo_get_product_terms_as_string( $product->get_parent_id() );
							$cats_ids = empty($cats) ? array() : explode(',', $cats);
							$settings = Ihc_Db::ihc_woo_products_get_discount_by_lid_prodid($product->get_parent_id(), $lid, $cats_ids);
						} else if ( empty( $settings ) && !$product->get_parent_id() ){
								$settings = Ihc_Db::ihc_woo_products_get_discount_by_lid_prodid( $product_id, $lid, $cats_ids );
						}
						if ($settings){
							$temp_possible = $this->get_possible_prices($price, $settings);
							if ($temp_possible>-1){
								$possible[] = $temp_possible;
							}
						}
					}
					if (isset($possible)){
						if (count($possible)==1 && isset($possible[0])){
							$result = $possible[0];
							self::$output_prices[$product_id] = $result;
							self::$input_prices[$product_id] = $price;
							return $result;
						} else {
							$result = $this->do_tiebreaker_between_possible_prices($possible);
							if ($result>-1){
								self::$output_prices[$product_id] = $result;
								self::$input_prices[$product_id] = $price;
								return $result;
							}
						}

			}

		}
		return $price;
	}

	/**
	 * @param float (base price)
	 * @param array (rule settings)
	 */
	private function get_possible_prices($base_price=0, $rule_settings=array()){
		if ($base_price && $rule_settings){
			$prices = array();
			foreach ($rule_settings as $settings){
				if ($settings['discount_type']=='%'){
					$prices[] = $base_price - ($base_price * $settings['discount_value'] / 100);
				} else {
					$prices[] = $base_price - $settings['discount_value'];
				}
			}
			if ($prices){
				if (count($prices)==1){
					return $prices[0];
				} else {
					$return = $this->do_tiebreaker_between_possible_prices($prices);
					if ($return>-1){
						return $return;
					}
				}
			}
		}
		return -1;
	}


	/**
	 * @param array
	 * @param float
	 */
	private function do_tiebreaker_between_possible_prices($prices=array()){
		if ($this->module_metas['ihc_woo_product_custom_prices_tiebreaker']=='biggest'){
			foreach ($prices as $price){
				if (!isset($return_value)){
					$return_value = $price;
				} else {
					if ($return_value<$price){
						$return_value = $price;
					}
				}
			}
		} else {
			foreach ($prices as $price){
				if (!isset($return_value)){
					$return_value = $price;
				} else {
					if ($return_value>$price){
						$return_value = $price;
					}
				}
			}
		}
		if (isset($return_value)){
			return $return_value;
		}
		return -1;
	}


	/**
	 * @param string (output html)
	 * @param object (the product object)
	 * @return string
	 */
	public function print_as_discount($price_html='', $object=array()){
		if (is_a($object, 'WC_Product_Variable')){
			return $price_html;
		} else {
			if (empty(self::$user_set)){
				$this->setUserLevels();
			}
			if ($this->user_levels){
				$product_price = $object->get_regular_price();

				if($object->is_on_sale()) {
					$product_price = $object->get_sale_price();
				}


				$new_price = $this->return_price($product_price, $object);

				if ( $new_price == $product_price ){
						return $price_html;
				}

				if (version_compare(WC_VERSION, '3.0.1')== -1){
				$price_html = $object->get_price_html_from_to($product_price, $new_price);
				} else {
					/// > v3.0.1
				$price_html = wc_format_sale_price($product_price, $new_price);

				}

			}
		}
		return $price_html;
	}




	public function variable_price($price, $object){
		if (empty(self::$user_set)){
			$this->setUserLevels();
		}
		if ($this->user_levels){
			$min = $object->get_variation_regular_price( 'min', TRUE );
			$max = $object->get_variation_regular_price( 'max', TRUE );

			if($object->is_on_sale()) {
				$min = $object->get_variation_sale_price( 'min', TRUE );
				$max = $object->get_variation_sale_price( 'max', TRUE );
			}
			$min_discount = $this->return_price( $min, $object );
			$max_discount = $this->return_price( $max, $object );

			if($min == $max) {
				if( !empty( $this->module_metas['ihc_woo_product_custom_prices_like_discount'] )) {
					return wc_format_sale_price( $min, $min_discount);
				} else {
					return wc_price( $min_discount );
				}
			} else {
					return wc_format_price_range( $min_discount, $max_discount ) . $object->get_price_suffix();
			}

		}
		return $price;
	}

	public function get_sale_price( $price, $regular_price, $sale_price ) {

	 global $current_user;

	 if (empty(self::$user_set)){
		 $this->setUserLevels();
	 }
	 if ($this->user_levels){

			 return wc_price( $sale_price );
	 } else {

			 return $price;
	 }

 }



}

endif;
