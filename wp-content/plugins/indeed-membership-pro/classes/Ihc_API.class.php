<?php
if (!class_exists('Ihc_API')):

class Ihc_API{
	/*
	 * @var string
	 */
	private $hash;

	/*
	 * @var array
	 */
	private $settings;


	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){
		$this->settings = ihc_return_meta_arr('api');
	}


	/*
	 * @param none
	 * @return int (0 or 1)
	 */
	public function is_enabled(){
		return $this->settings['ihc_api_enabled'];
	}


	/*
	 * @param none
	 * @return bool
	 */
	public function is_safe(){
		if (!empty($_GET['ihch'])){
			$this->hash = $this->secure_input_var($_GET['ihch'], TRUE);
		}
		if (!empty($this->hash) && !empty($this->settings['ihc_api_hash']) && strcmp(md5($this->settings['ihc_api_hash']), md5($this->hash))===0){
			return TRUE;
		}
		return FALSE;
	}


	/*
	 * @param none
	 * @return string
	 */
	public function get_result(){
		$response = FALSE;
		if (!empty($_GET['action'])){
			$action = $this->secure_input_var($_GET['action']);
			if (!empty($action)){
				if (empty($this->settings['ihc_api_actions'][$action])){
					$response = 'Access Denied';
				} else {
					switch ($action){
						///// USER
						case 'verify_user_level':
							$response = $this->user_verify_level();
							break;
						case 'user_approve':
							$response = $this->user_do_approve();
							break;
						case 'user_add_level':
							$response = $this->user_add_new_level();
							break;
						case 'user_get_details':
							$response = $this->user_get_details();
							break;
						case 'user_activate_level':
							$response = $this->user_do_activate_level();
							break;
						case 'get_user_field_value':
							$response = $this->user_get_field_value();
							break;
						case 'get_user_levels':
							$response = $this->user_get_levels();
							break;
						case 'get_user_level_details':
							$response = $this->user_get_level_details();
							break;
						case 'get_user_posts':
							$response = $this->user_get_posts();
							break;
						case 'search_users':
							$response = $this->search_user();
							break;
						///// LEVELS
						case 'list_levels':
							$response = $this->levels_list_all();
							break;
						case 'get_level_users':
							$response = $this->get_level_users();
							break;
						case 'get_level_details':
							$response = $this->get_level_details();
							break;
						////// ORDERS
						case 'orders_listing':
							$response = $this->orders_listing();
							break;
						case 'order_get_status':
							$response = $this->order_get_status();
							break;
						case 'order_get_data':
							$response = $this->order_get_data();
							break;
						default:
							$response = 'Unknown Action';
							break;
					}
				}
			}
		}
		echo json_encode( array( 'response' => $response ) );
	}


	/*
	 * @param string, bool
	 * @return stirng
	 */
	 private function secure_input_var($string='', $remove_special_chars=FALSE){
             $string = sanitize_text_field( $string );
			$string = preg_replace('/\s+/', '', $string);/// remove all white spaces
			if ($remove_special_chars){
				$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
			}
			return $string;
	 }


	 /*
	  * Check if user has the provided level and it's active.
	  * @param none
	  * @return int
	  */
	  private function user_verify_level(){
		  	$return_value = 0;
			$uid = $this->secure_input_var($_GET['uid'], TRUE);
			$lid = $this->secure_input_var($_GET['lid'], TRUE);
			if ($uid && $lid!=''){
				if ( \Indeed\Ihc\UserSubscriptions::userHasSubscription( $uid, $lid ) && \Indeed\Ihc\UserSubscriptions::isActive( $uid, $lid ) ){
					$return_value = 1;
				}
			}
			return $return_value;
	  }


	  /*
	   * @param none
	   * @return bool
	   */
	   private function user_do_approve(){
	   		$uid = $this->secure_input_var($_GET['uid'], TRUE);
				if ($uid){
					return ihc_do_user_approve($uid);
				}
				return false;
	   }


	   /*
	    * @param none
	    * @return bool
	    */
	   private function user_add_new_level()
		 {
	    	$uid = $this->secure_input_var($_GET['uid'], TRUE);
				$lid = $this->secure_input_var($_GET['lid'], TRUE);
				if ($uid && $lid!=''){
					return \Indeed\Ihc\UserSubscriptions::assign( $uid, $lid );
				}
				return FALSE;
	    }


		/*
		 * @param none
		 * @return array
		 */
		private function user_get_details(){
			$uid = $this->secure_input_var($_GET['uid'], TRUE);
			if ($uid){
				$data = Ihc_Db::user_get_all_data($uid);
				return $data;
			}
			return array();
		}


		/*
		 * @param none
		 * @return array
		 */
		private function user_do_activate_level(){
	    	$uid = $this->secure_input_var($_GET['uid'], TRUE);
			$lid = $this->secure_input_var($_GET['lid'], TRUE);
			if ($uid && $lid!=''){
				$level_data = ihc_get_level_by_id($lid);
				\Indeed\Ihc\UserSubscriptions::makeComplete( $uid, $lid );
				return TRUE;
			}
			return FALSE;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function levels_list_all(){
			$array = array();
			$data = \Indeed\Ihc\Db\Memberships::getAll();
			if ($data){
				foreach ($data as $lid=>$temp){
					$array[] = array(
									'level_id' => $lid,
									'label' => $temp['label'],
									'slug' => $temp['name'],
					);
				}
			}
			return $array;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function orders_listing(){
			$array = array();
			$limit = $this->secure_input_var((isset($_GET['limit'])) ? $_GET['limit'] : '', TRUE);
			$uid = $this->secure_input_var($_GET['uid'], TRUE);
			if (empty($limit)){
				$limit = 999;
			}
			$data = Ihc_Db::get_all_order($limit, 0, $uid);
			if ($data){
				foreach ($data as $temp){
					$temp_array['code'] = (isset($temp['metas']['code'])) ? $temp['metas']['code'] : '';
					$temp_array['transaction_id'] = (isset($temp['transaction_id'])) ? $temp['transaction_id'] : '';
					$temp_array['id'] = (isset($temp['id'])) ? $temp['id'] : '';
					$temp_array['user'] = (isset($temp['user'])) ? $temp['user'] : '';
					$temp_array['level'] = (isset($temp['level'])) ? $temp['level'] : '';
					$temp_array['payment_type'] = (isset($temp['metas']['ihc_payment_type'])) ? $temp['metas']['ihc_payment_type'] : '';
					$temp_array['amount'] = ((isset($temp['amount_value'])) ? $temp['amount_value'] : '') . ' ' . ((isset($temp['amount_type'])) ? $temp['amount_type'] : '');
					$temp_array['status'] = (isset($temp['status'])) ? $temp['status'] : '';
					$array[] = $temp_array;
					unset($temp_array);
				}
			}
			return $array;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function order_get_status(){
		 	$order_id = $this->secure_input_var($_GET['order_id'], TRUE);
			if ($order_id){
				$data = Ihc_Db::get_order_data_by_id($order_id);
				if (isset($data['status'])){
					return $data['status'];
				}
			}
			return 'Unknown';
		}


		/*
		 * @param none
		 * @return array
		 */
		private function order_get_data(){
		 	$array = array();
		 	$order_id = $this->secure_input_var($_GET['order_id'], TRUE);
			if ($order_id){
				$array = Ihc_Db::get_order_data_by_id($order_id);
			}
			return $array;
		}


		/*
		 * @param none
		 * @return string
		 */
		private function user_get_field_value(){
		 	$output = '';
	    	$uid = $this->secure_input_var($_GET['uid'], TRUE);
			$field = $this->secure_input_var($_GET['field'], FALSE);
			if ($uid && $field){
				$serch_field = "{" . $field . "}";
				$output = ihc_replace_constants($serch_field, $uid);
				if (strcmp($serch_field, $output)==0){
					$serch_field = "{CUSTOM_FIELD_" . $field . "}";
					$output = ihc_replace_constants($serch_field, $uid);
				}
			}
			return $output;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function user_get_levels(){
		 	$uid = $this->secure_input_var($_GET['uid'], TRUE);
			if (isset($_GET['only_active'])){
				$only_active = $this->secure_input_var($_GET['only_active'], TRUE);
			} else {
				$only_active = FALSE;
			}
			if ($uid){
			 	$data = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, $only_active );
			}
			return $data;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function user_get_level_details(){
		 	$data = array();
		 	$uid = $this->secure_input_var($_GET['uid'], TRUE);
			$lid = $this->secure_input_var($_GET['lid'], TRUE);
			if ($uid && $lid!=''){
				$data = \Indeed\Ihc\UserSubscriptions::getOne( $uid, $lid );
			}
			return $data;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function user_get_posts(){
		 	$data = array();
		 	$uid = $this->secure_input_var($_GET['uid'], TRUE);
			if ($uid){
				 require_once IHC_PATH . 'classes/ListOfAccessPosts.class.php';
				 $levels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, true );
				 $levels = array_keys($levels);
				 $metas = ihc_return_meta_arr('list_access_posts');

				 if (isset($_GET['limit'])){
				 	$metas['ihc_list_access_posts_order_limit'] = $this->secure_input_var($_GET['limit'], TRUE);
				 }
				 if (empty($metas['ihc_list_access_posts_order_limit']) && !empty($attr['limit'])){
				 	$metas['ihc_list_access_posts_order_limit'] = $attr['limit'];
				 }

				 if (isset($_GET['order_by'])){
				 	$metas['ihc_list_access_posts_order_by'] = $this->secure_input_var($_GET['order_by'], FALSE);
				 }
				 if (empty($metas['ihc_list_access_posts_order_by']) && !empty($attr['order_by'])){
				 	$metas['ihc_list_access_posts_order_by'] = $attr['order_by'];
				 }

				 if (isset($_GET['order'])){
				 	$metas['ihc_list_access_posts_order_type'] = $this->secure_input_var($_GET['order'], TRUE);
				 }
				 if (empty($metas['ihc_list_access_posts_order_type']) &&!empty($attr['order'])){
				 	$metas['ihc_list_access_posts_order_type'] = $attr['order'];
				 }

				 if (isset($_GET['post_types'])){
				 	$metas['ihc_list_access_posts_order_post_type'] = $this->secure_input_var($_GET['post_types'], FALSE);
				 }
				 if (empty($metas['ihc_list_access_posts_order_post_type']) && !empty($attr['post_types'])){
				 	$metas['ihc_list_access_posts_order_post_type'] = $attr['post_types'];
				 }
			 	   if (!empty($metas['ihc_list_access_posts_order_exclude_levels'])){
					 $exclude = explode(',', $metas['ihc_list_access_posts_order_exclude_levels']);
						 if ($exclude){
				 			$levels = array_diff($levels, $exclude);
						 }
				 }
				 $metas['ihc_list_access_posts_per_page_value'] = $metas['ihc_list_access_posts_order_limit'];
			 	 $object = new ListOfAccessPosts($levels, $metas);
				 $data = $object->get_id_list();
			}
			return $data;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function search_user(){
		 	$data = array();
		 	$term_name = $this->secure_input_var($_GET['term_name'], FALSE);
			$term_value = $this->secure_input_var($_GET['term_value'], FALSE);
			if ($term_name){
				$data = Ihc_Db::search_user_by_term_name_term_value($term_name, $term_value);
			}
			return $data;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function get_level_users(){
			$data = array();
		 	$lid = $this->secure_input_var($_GET['lid'], TRUE);
			if ($lid!=''){
				$data = \Indeed\Ihc\UserSubscriptions::getSubscriptionsUsersList( $lid );
			}
			return $data;
		}


		/*
		 * @param none
		 * @return array
		 */
		private function get_level_details(){
			$data = array();
		 	$lid = $this->secure_input_var($_GET['lid'], TRUE);
			if ($lid!=''){
				$data = \Indeed\Ihc\Db\Memberships::getAll();
				if ($data[$lid]){
					return $data[$lid];
				}
			}
			return array();
		}

}
endif;
