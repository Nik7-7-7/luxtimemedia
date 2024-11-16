<?php
if (!class_exists('ListOfAccessPosts')):

class ListOfAccessPosts{
	private $levels = array();
	private $levels_conditions = '';
	private $metas = array();
	private $post_types_in = "'post','page'";
	private $posts_not_in = '';

	public function __construct($levels=array(), $metas=array()){
		/*
		 * @param array
		 * @return none
		 */
		$this->levels = $levels;
		$this->metas = $metas;
	}

	public function output(){
		/*
		 * @param none
		 * @return string
		 */

		if (!empty($this->metas['ihc_list_access_posts_per_page_value'])){
			$limit = $this->metas['ihc_list_access_posts_per_page_value'];
		} else {
			$limit = 25;
		}

		$this->set_level_conditions();

		$this->set_drip_content();

		$total = $this->get_count();

		$current_page = (empty($_GET['ihcdu_page'])) ? 1 : sanitize_text_field($_GET['ihcdu_page']);
		if ($current_page>1){
			$offset = ( $current_page - 1 ) * $limit;
		} else {
			$offset = 0;
		}
		require_once IHC_PATH . 'classes/Ihc_Pagination.class.php';
		$base_url = IHC_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$base_url = remove_query_arg('ihcdu_page', $base_url);
		$pagination_object = new Ihc_Pagination(array(
													'base_url' => $base_url,
													'param_name' => 'ihcdu_page',
													'total_items' => $total,
													'items_per_page' => $limit,
													'current_page' => $current_page,
		));
		$data['pagination'] = $pagination_object->output();
		if ($offset + $limit>$total){
			$limit = $total - $offset;
		}

		if ($this->metas['ihc_list_access_posts_order_limit'] && $this->metas['ihc_list_access_posts_order_limit']<$limit){
			$limit = $this->metas['ihc_list_access_posts_order_limit'];
		}
		$data['metas'] = $this->metas;
		$data['items'] = $this->get_data($limit, $offset);


		$fullPath = IHC_PATH . 'public/views/list_access_posts.php';
		$searchFilename = 'list_access_posts.php';
		$template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

		ob_start();
		require $template;
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	private function get_count(){
		/*
		 * @param int
		 * @return int
		 */
		global $wpdb;
		$posts = $wpdb->prefix . 'posts';
		$postmeta = $wpdb->prefix . 'postmeta';

		$limit = '';
		$max = 100;
		if (!empty($this->metas['ihc_list_access_posts_order_limit'])){
			$limit = ' LIMIT ' . sanitize_text_field($this->metas['ihc_list_access_posts_order_limit']);
			$max = sanitize_text_field($this->metas['ihc_list_access_posts_order_limit']);
		}
		$this->set_post_types();
		$q = "
				SELECT COUNT(DISTINCT(b.ID)) as count_value
				FROM $postmeta a
				INNER JOIN $posts b
				ON a.post_id=b.ID
				INNER JOIN $postmeta c
				ON c.post_id=a.post_id
				WHERE 1=1
		";
		if (!empty($this->posts_not_in)){
			$q .= " AND a.post_id NOT IN ({$this->posts_not_in}) ";
		}
		$q .= "
				AND
				( b.post_type IN ({$this->post_types_in}) )";

		if ( $this->levels_conditions !== '' ){
				$q .= "
				AND
				(
						(
								( a.meta_key='ihc_mb_type' AND a.meta_value='show' )
								AND
								( c.meta_key='ihc_mb_who' AND {$this->levels_conditions} )
						)

						OR
						(
							( a.meta_key='ihc_mb_type' AND a.meta_value='block' )
							AND
							( c.meta_key='ihc_mb_who' AND c.meta_value != '' )
							AND
							( c.meta_key='ihc_mb_who' AND !( {$this->levels_conditions} ) )
						)
				)
				";
		}
/*
OR
(
	( a.meta_key='ihc_mb_type' AND a.meta_value='show' )
	AND
	( c.meta_key='ihc_mb_who' AND c.meta_value='' )
)
OR
(
	( a.meta_key='ihc_mb_type' AND a.meta_value='block' )
	AND
	( c.meta_key='ihc_mb_who' AND c.meta_value='' )
)
*/
		$q .= " $limit
		";

		$data = $wpdb->get_row($q);

		if ($data && isset($data->count_value)){
			if($data->count_value > $max){
				return $max;
			}else{
				return $data->count_value;
			}
		}
		return 0;
	}

	private function get_data($limit=30, $offset=0){
		/*
		 * @param int
		 * @return int
		 */
		$array = array();
		$order_by = sanitize_text_field($this->metas['ihc_list_access_posts_order_by']);
		$order_type = sanitize_text_field($this->metas['ihc_list_access_posts_order_type']);
		global $wpdb;
		$posts = $wpdb->prefix . 'posts';
		$postmeta = $wpdb->prefix . 'postmeta';

		$select_fields = '';
		if (!empty($this->metas['ihc_list_access_posts_item_details'])){
			$select_array = explode(',', $this->metas['ihc_list_access_posts_item_details']);
			if (in_array('post_title', $select_array)){
				$select_fields .= ', b.post_title as title';
			}
			if (in_array('post_excerpt', $select_array)){
				$select_fields .= ', b.post_excerpt as post_excerpt';
			}
			if (in_array('feature_image', $select_array)){
				$get_image = TRUE;
			}
			if (in_array('post_date', $select_array)){
				$select_fields .= ', b.post_date as post_date';
			}
			if (in_array('post_author', $select_array)){
				$select_fields .= ', b.post_author as post_author';
			}
			if ( in_array( 'post_content', $select_array ) ){
					$select_fields .= ', b.post_content as post_content';
			}
		}

		$q = "
		SELECT DISTINCT(a.post_id) as id $select_fields
			FROM $postmeta a
			INNER JOIN $posts b
			ON a.post_id=b.ID
			INNER JOIN $postmeta c
			ON c.post_id=a.post_id
			WHERE 1=1
			";
		if (!empty($this->posts_not_in)){
			$q .= " AND a.post_id NOT IN ({$this->posts_not_in}) ";
		}
		$q .= "
			AND
			( b.post_type IN ({$this->post_types_in}) )";

			if ( $this->levels_conditions !== '' ){
				$q .= "
					AND
					(
							(
									( a.meta_key='ihc_mb_type' AND a.meta_value='show' )
									AND
									( c.meta_key='ihc_mb_who' AND {$this->levels_conditions} )
							)

							OR
							(
								( a.meta_key='ihc_mb_type' AND a.meta_value='block' )
								AND
								( c.meta_key='ihc_mb_who' AND c.meta_value != '' )
								AND
								( c.meta_key='ihc_mb_who' AND !( {$this->levels_conditions} ) )
							)
					)";
			}
/*
OR
(
	( a.meta_key='ihc_mb_type' AND a.meta_value='show' )
	AND
	( c.meta_key='ihc_mb_who' AND c.meta_value='' )
)
OR
(
	( a.meta_key='ihc_mb_type' AND a.meta_value='block' )
	AND
	( c.meta_key='ihc_mb_who' AND c.meta_value='' )
)
*/

		$q .= "
			GROUP BY id
			ORDER BY b.$order_by $order_type
		";
		if ( $limit > -1 ){
				$q .= "	LIMIT $limit OFFSET $offset ";
		}

		$db_result = $wpdb->get_results($q);

		foreach ($db_result as $db_object){
			$temp = (array)$db_object;
			$temp['drip_content_conditions'] = $this->get_drip_content_conditions($temp['id']);
			$temp['permalink'] = get_permalink($temp['id']);
			if (!empty($get_image)){
				$temp['feature_image'] = wp_get_attachment_image_src(get_post_thumbnail_id($temp['id']),'single-post-thumbnail');
				if (!empty($temp['feature_image']) && !empty($temp['feature_image'][0])){
					$temp['feature_image'] = $temp['feature_image'][0];
				}
			}
			if (!empty($temp['post_author'])){
				$temp_user = get_userdata($temp['post_author']);
				if (!empty($temp_user->first_name) && !empty($temp_user->last_name)){
					$temp['post_author'] = $temp_user->first_name . ' ' . $temp_user->last_name;
				} else if ( !empty( $temp_user->user_nicename ) ){
					$temp['post_author'] = $temp_user->user_nicename;
				} else {
					$temp['post_author'] = '';
				}
			}
			if (!empty($temp['post_date'])){
				$temp['post_date'] = ihc_convert_date_to_us_format($temp['post_date']);
			}
			$array[$temp['id']] = $temp;
		}

		return $array;
	}

	private function set_level_conditions(){
		/*
		 * @param none
		 * @return none
		 */
		if (count($this->levels)==0){
			$this->levels_conditions = " FIND_IN_SET('reg', c.meta_value) ";
		} else if (count($this->levels)==1){
			$cond_lid = (isset($this->levels[0])) ? $this->levels[0] : '';
			if ( $cond_lid !== '' ){
					$this->levels_conditions = " FIND_IN_SET($cond_lid, c.meta_value) ";
			}
		} else {
			$this->levels_conditions .= " ( ";
			foreach ($this->levels as $lid){
				if (!empty($or)){
					$this->levels_conditions .= " OR ";
				}
				$this->levels_conditions .= " FIND_IN_SET($lid, c.meta_value) ";
				$or = TRUE;
			}
			$this->levels_conditions .= " ) ";
		}
	}

	private function set_post_types(){
		/*
		 * @param none
		 * @return none
		 */
		if (!empty($this->metas['ihc_list_access_posts_order_post_type'])){
			$str = '';
			$this->metas['ihc_list_access_posts_order_post_type'] = explode(',', $this->metas['ihc_list_access_posts_order_post_type']);
			foreach ($this->metas['ihc_list_access_posts_order_post_type'] as $value){
				if ($str){
					$str .= ",";
				}
				$str .= "'$value'";
			}
			$this->post_types_in = $str;
		}
	}

	private function get_drip_content_conditions($post_id=0){
		/*
		 * @param int
		 * @return array
		 */
		 $array = array();
		 global $wpdb;
		 $table = $wpdb->prefix . 'postmeta';
		 $post_id = sanitize_text_field($post_id);
		 $query = $wpdb->prepare( "SELECT meta_key, meta_value
		 								FROM $table
		 								WHERE post_id=%d
		 								AND meta_key IN
		 								(
		 								 'ihc_drip_content',
		 								 'ihc_drip_start_type',
		 								 'ihc_drip_end_type',
		 								 'ihc_drip_start_numeric_type',
		 								 'ihc_drip_start_numeric_value',
		 								 'ihc_drip_end_numeric_type',
		 								 'ihc_drip_end_numeric_value',
		 								 'ihc_drip_start_certain_date',
		 								 'ihc_drip_end_certain_date'
									 );", $post_id );
		 $data = $wpdb->get_results( $query );
		if ($data){
			foreach ($data as $obj){
				$array[$obj->meta_key] = $obj->meta_value;
			}
		}
		return $array;
	}


	/*
	 * @param none
	 * @return array
	 */
	public function get_id_list(){
		if ( !empty($this->metas['ihc_list_access_posts_per_page_value']) || $this->metas['ihc_list_access_posts_per_page_value'] == -1 ){
			$limit = $this->metas['ihc_list_access_posts_per_page_value'];
		} else {
			$limit = 25;
		}


		$this->set_level_conditions();

		$this->set_drip_content();


		$data = $this->get_data($limit, 0);
		return $data;
	}


	/*
	 * @param none
	 * @return none
	 */
	public function set_drip_content(){
	    global $current_user;
	    $array = array();
	    $data = $this->drip_content_items();
	    if ($data){
	        $uid = (empty($current_user->ID)) ? 0 : $current_user->ID;
	        if ($uid){
	            foreach ($data as $post_id){
	                $temp_posts_who = get_post_meta($post_id, 'ihc_mb_who', TRUE);
	                $temp_posts_who_array = explode(',', $temp_posts_who);
	                foreach ($this->levels as $lid){
	                    if (!in_array($lid, $temp_posts_who_array)){
	                        continue;
	                    }
	                    $block = ihc_check_drip_content($uid, $lid, $post_id);
	                    if ($block){
	                        $array[] = $post_id;
	                        break;
	                    }
	                }
	            }
	        }
	    }
	    if (!empty($array)){
	        $this->posts_not_in = implode(',', $array);
	    }
	}

	/*
	 * @param
	 * @return array
	 */
	public function drip_content_items(){
		global $wpdb;
		$array = array();
		$posts = $wpdb->prefix . 'posts';
		$postmeta = $wpdb->prefix . 'postmeta';
		$q = "
		SELECT DISTINCT(a.post_id) as id
			FROM $postmeta a
			INNER JOIN $posts b
			ON a.post_id=b.ID
			INNER JOIN $postmeta c
			ON c.post_id=a.post_id
			INNER JOIN $postmeta d
			ON d.post_id=a.post_id
			WHERE 1=1
			AND
			( b.post_type IN ({$this->post_types_in}) )";

		if ( $this->levels_conditions !== '' ){
			$q .= "
				AND
				( a.meta_key='ihc_mb_type' AND a.meta_value='show' )
				AND
				( c.meta_key='ihc_mb_who' AND {$this->levels_conditions} )";
		}


		$q .= "
			AND
			( d.meta_key='ihc_drip_content' AND d.meta_value=1 )
		";
		$db_result = $wpdb->get_results($q);
		if ($db_result){
			foreach ($db_result as $object){
				$array[] = $object->id;
			}
		}
		return $array;
	}

}

endif;
