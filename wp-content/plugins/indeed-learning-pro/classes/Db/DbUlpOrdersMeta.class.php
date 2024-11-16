<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('DbIndeedAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/DbIndeedAbstract.class.php';
}
if (class_exists('DbUlpOrdersMeta')){
   return;
}
class DbUlpOrdersMeta extends DbIndeedAbstract{
  	/**
  	 * @var string
  	 */
	  protected $table = '';

    /**
  	 * @param none
  	 * @reutrn none
  	 */
  	public function __construct(){
    		global $wpdb;
    		$this->table = $wpdb->prefix . 'ulp_order_meta';
  	}

  	/**
  	 * @param int
  	 * @param string
  	 * @return mixed
  	 */
  	public function get($order_id=0, $meta_key=''){
        global $wpdb;
				$order_id = sanitize_text_field($order_id);
				$meta_key = sanitize_text_field($meta_key);
        $where = $wpdb->prepare( " order_id=%d AND meta_key=%s ", $order_id, $meta_key );
    		$data = $this->getVar('meta_value', $where );
    		return $data;
  	}

		public function allMetaNames(){
				return [
						'amount',
						'course',
						'user_id',
						'unique_identificator',
						'course_id',
						'used'
				];
		}

		public function getAllMetasAsArray($order_id=0){
				$names = $this->allMetaNames();
				$data = array();
				foreach ($names as $name){
						$data [$name] = $this->get($order_id, $name);
				}
				return $data;
		}

		public function getAll($order_id=0){
        global $wpdb;
				$order_id = sanitize_text_field($order_id);
        $where = $wpdb->prepare( " order_id=%d ", $order_id );
				return $this->getResults('meta_key, meta_value', $where );
		}

		public function getSource($order_id=0){
        global $wpdb;
				$order_id = sanitize_text_field($order_id);
        $where = $wpdb->prepare( " order_id=%d AND meta_key='unique_identificator' ", $order_id );
				$data = $this->getVar('meta_value', $where );
				if ($data){
						if (strpos($data, 'woocommerce')!==FALSE){
								return 'Woocommerce';
						} else if (strpos($data, 'ump')!==FALSE){
								return 'Ultimate Membership Pro';
						} else if (strpos($data, 'edd')!==FALSE){
								return 'Easy Download Digital';
						} else if (strpos($data, 'paypal')!==FALSE){
								return 'checkout - PayPal';
						} else if (strpos($data, 'bt')!==FALSE){
								return 'checkout - Bank transfer';
						} else if (strpos($data, 'stripe')!==FALSE){
								return 'checkout - Stripe';
						} else if (strpos($data, '2checkout')!==FALSE){
								return 'checkout - 2Checkout';
						}
				}
				return 'Unknown';
		}

  	/**
  	 * @param int
  	 * @param string
  	 * @param string
  	 * @return bool
  	 */
  	public function save($order_id=0, $meta_key='', $meta_value=''){
    		global $wpdb;
				$meta_key = sanitize_text_field($meta_key);
				$meta_value = sanitize_textarea_field($meta_value);
				$order_id = sanitize_text_field($order_id);
    		if ($this->get($order_id, $meta_key)!==null){
    			/// update
          $update = $wpdb->prepare( " meta_value=%s ", $meta_value );
          $where = $wpdb->prepare( " order_id=%d AND meta_key=%s ", $order_id, $meta_key );
    			return $this->update( $update, $where );
    		} else {
    			/// create
          $insert = $wpdb->prepare( " null, %d, %s, %s ", $order_id, $meta_key, $meta_value );
    			return $this->insert( $insert );
    		}
  	}

    /**
  	 * @param int
  	 * @param string
  	 * @param string
  	 * @return bool
  	 */
  	public function delete($order_id=0, $meta_key='', $meta_value=''){
        global $wpdb;
				$order_id = sanitize_text_field($order_id);
				$meta_key = sanitize_text_field($meta_key);
				$meta_value = sanitize_textarea_field($meta_value);
        $delete = $wpdb->prepare( " order_id=%d AND meta_key=%s ", $order_id, $meta_key );
  		  return parent::delete( $delete );
  	}

    public function deleteAllByOrderId($order_id=0){
        global $wpdb;
				$order_id = sanitize_text_field($order_id);
        $delete = $wpdb->prepare( " order_id=%d ", $order_id );
        return parent::delete( $delete );
    }
}
