<?php
if (!class_exists('ListingUsers')){
	class ListingUsers{
		private $args = array();
		private $total_pages = 0;
		private $users = array();
		private $div_parent_id = '';
		private $li_width = '';
		private $user_fields = array();
		private $total_users;
		private $single_item_template = '';
		private $general_settings = array();
		private $link_user_page = '';
		private $fields_label = array();
		private $permalink_type = '';
		private $filter_form_fields = array();
		private $search_get_filter = array();
		private $uid_arr;
		private $fields_type = [];

		public function __construct($input=array()){
			/*
			 * @param array
			 * @return none
			 */
			if (empty($input)){
				return;
			} else {
				$this->args = $input;

				global $wpdb;
				foreach ($this->args as $key=>$value){
					$this->args[$key] = $wpdb->_real_escape($value);
				}

				$this->general_settings = ihc_return_meta_arr('listing_users');
				$link_user_page = get_option('ihc_general_register_view_user');
				if (!empty($link_user_page)){
					$link_user_page = get_permalink($link_user_page);
					if (!empty($link_user_page)){
						$this->link_user_page = $link_user_page;
					}
					$this->permalink_type = get_option('permalink_structure');

					/// flush rewrite
					$this->rewriteFlush();

				}
			}
		}

		private function rewriteFlush()
		{
				if ( empty( $this->args['inside_page'] ) ){
						return;
				}
				$linkUserPage = get_option('ihc_general_register_view_user');
				if ( $linkUserPage && !defined('DOING_AJAX') ){
						$pageSlug = \Ihc_Db::get_page_slug( $linkUserPage );
						add_rewrite_rule( $pageSlug . "/([^/]+)/?",'index.php?pagename=' . $pageSlug . '&ihc_name=$matches[1]', 'top');
						flush_rewrite_rules();
				}
		}

		public function run(){
			/*
			 * @param none
			 * @return string
			 */
			if (empty($this->args)){
				return;
			}

			$output = '';
			$html = '';
			$js = '';
			$css = '';
			$js_after_html = '';
			$pagination = '';
			$search_bar = '';
			$search_filter = '';

			if (empty($this->args['entries_per_page'])){
				 $this->args['entries_per_page'] = 25;
			}
			$search_by = empty($this->args['search_by']) ? '' : $this->args['search_by'];
			$search_q = empty($_GET['ihc_search_u']) ? '' : sanitize_text_field($_GET['ihc_search_u']);
			//$search_q = sanitize_text_field( $search_q );
			//$search_q = esc_attr( $search_q );

			////// FILTER BY LEVELs
			if (!empty($this->args['filter_by_level']) && !empty($this->args['levels_in'])){
				if (strpos($this->args['levels_in'], ',')!==FALSE){
					$inner_join_levels = explode(',', $this->args['levels_in']);
				} else {
					$inner_join_levels = array($this->args['levels_in']);
				}
			} else {
				$inner_join_levels = array();
			}

			////////// ORDER
			$order_by = $this->args['order_by'];
			if ($order_by=='random'){
				$order_by = '';
			}
			$order_type = $this->args['order_type'];

			$this->set_filter_form_fields();

			//// FILTER
			if (!empty($_GET['iump_filter'])){
				foreach ($_GET as $get_key=>$get_value){
					$get_key = sanitize_text_field( $get_key );
					if (isset($this->filter_form_fields[$get_key]) && $_GET[$get_key]!=''){
						if (is_array($get_value)){
							if (isset($get_value[0]) && $get_value[0]!=''){
								foreach ( $get_value as $subkey => $subvalue){
										$get_value[$subkey] = sanitize_text_field($subvalue);
								}
								$this->search_get_filter[$get_key] = $get_value;
							}
						} else {
							$this->search_get_filter[$get_key] = sanitize_text_field($get_value);
						}
					}
				}
			}

			//////////TOTAL USERS
			$this->total_users = $this->get_users($order_by, $order_type, -1, -1, TRUE, $inner_join_levels, $search_by, $search_q);

			if ($this->total_users>$this->args['num_of_entries']){
				$this->total_users = $this->args['num_of_entries'];
			}

			$this->set_filter_form_possible_values();

			//limit && offset
			if (empty($this->args['slider_set'])){
				//// NO SLIDER + PAGINATION
				if (!empty($this->args['current_page'])){
					$current_page = $this->args['current_page'];
					$offset = ( $current_page - 1 ) * ((int)$this->args['entries_per_page']); //start from
				} else {
					$offset = 0;
				}
				$limit = $this->args['entries_per_page'];
				if ($offset + $limit>$this->total_users){
					$limit = $this->total_users - $offset;
				}
			} else {
				////SLIDER
				$offset = 0;
				$limit = $this->args['num_of_entries'];
			}

			///GETTING USER IDS
			$user_ids = $this->get_users($order_by, $order_type, (int)$offset, (int)$limit, FALSE, $inner_join_levels, $search_by, $search_q);

			if (!empty($user_ids)){
				/// we have users
				$this->set_users_data($user_ids);////SET USERS DATA
				$this->single_item_template = IHC_PATH . 'public/listing_users/themes/' . $this->args['theme'] . "/index.php";
				///SET FIELDS LABEL
				$this->set_fields_label();

				if (!empty($this->users) && file_exists($this->single_item_template)){
					$html .= $this->create_the_html();
					$js .= $this->create_the_js();
					$css .= $this->create_the_css();
					$js_after_html .= $this->create_the_js_after_html();
				}

				/// PAGINATION
				if (empty($this->args['slider_set']) && $this->args['entries_per_page']<$this->total_users){
					///adding pagination
					$pagination .= $this->print_pagination();
				}
				if (empty($this->args['pagination_pos'])){
					$this->args['pagination_pos'] = 'top';
				}
				switch ($this->args['pagination_pos']){
					case 'top':
						$html = $pagination . $html;
						break;
					case 'bottom':
						$html = $html . $pagination;
						break;
					case 'both':
						$html = $pagination . $html . $pagination;
						break;
				}
			} else {
				$html .= '<h3>' . esc_html__("No Users Found", 'ihc') . '</h3>';
			}

			/// SEARCH BAR
			if (!empty($this->args['show_search'])){
				$search_bar .= '<form  method="get">';
				$search_bar .= '<div class="ihc-search-bar-wrapper">';
				$search_bar .= '<div class="ihc-input-pre"><i class="fa-ihc fa-srch-ihc"></i></div>';
				$get_val = empty($_GET['ihc_search_u']) ? '' : sanitize_text_field($_GET['ihc_search_u']);
				$get_val = sanitize_text_field( $get_val );
				$search_bar .= '<input type="text" name="ihc_search_u" value="" class="ihc-search-bar" placeholder="'.esc_html__('Search for...','ihc').'" />';
				$search_bar .= '</div>';
				$search_bar .= '</form>';
			}

			/// SHOW FILTER
			if (!empty($this->args['show_search_filter']) && !empty($this->args['search_filter_items'])){
				$search_filter = $this->print_filter_form();
				$html = '<div class="iump-listing-users-pre-wrapp">' . $search_bar . $html . '</div>';
			} else {
				$html = $search_bar . $html;
			}

			$output = $css . $js . $search_filter . $html . $js_after_html;
			return $output;
		}

		private function set_users_data($user_ids){
			/*
			 * @param array
			 * @return none
			 */
			$this->user_fields = explode(',', $this->args['user_fields']);
			if ($this->args['order_by']=='random'){
				shuffle($user_ids);
			}
			foreach ($user_ids as $k=>$id){
				foreach ($this->user_fields as $field){
					if (empty($users[$id][$field])){
						$user_data = get_userdata($id);
						if (isset($user_data->$field)){
							$this->users[$id][$field] = $user_data->$field;
						} else {
							if(get_user_meta($id, $field, TRUE) !== NULL){
								$this->users[$id][$field] = get_user_meta($id, $field, TRUE);
							}
						}
					}
				}
			}
		}

		private function set_fields_label(){
			/*
			 * @param none
			 * @return none
			 */
			$fields_data = ihc_get_user_reg_fields();
			foreach ($this->user_fields as $field){

				$key = ihc_array_value_exists($fields_data, $field, 'name');

				if ($key!==FALSE && !empty($fields_data[$key]) && !empty($fields_data[$key]['label'])){
					$this->fields_label[$field] = $fields_data[$key]['label'];
				}
				if ($key!==FALSE && !empty($fields_data[$key]) && !empty($fields_data[$key]['type'])){
					$this->fields_type[$field] = $fields_data[$key]['type'];
				}

			}
		}

		private function get_users($order_by='', $order_type='', $offset=-1, $limit=-1, $count=FALSE, $inner_join_levels=array(), $search_by='', $search_q='', $skip_filter=FALSE){
			/*
			 * GETTING USERS FROM DB, COUNT USERS FROM DB
			 * @param: string, string, int, int, boolean, array, string, string
			 * @return array
			 */
			global $wpdb;

			///secure variables from input
			$order_by = $wpdb->_real_escape($order_by);
			$order_type = $wpdb->_real_escape($order_type);
			$offset = $wpdb->_real_escape($offset);
			$limit = $wpdb->_real_escape($limit);
			$search_q = $wpdb->_real_escape($search_q);

			$data = ihc_get_admin_ids_list();
			$not_in = implode(',', $data);

			$q = 'SELECT';
			if ($count){
				if (!empty($inner_join_levels)){
					$q .= " DISTINCT b.user_id as uid ";
				} else {
					$q .= " DISTINCT c.user_id as uid ";
				}
			} else {
				if (!empty($inner_join_levels)){
					$q .= " DISTINCT b.user_id as user_id";
				} else {
					$q .= " DISTINCT c.user_id as user_id";
				}
			}
			$q .= " FROM " . $wpdb->base_prefix ."users as a";
			if (!empty($inner_join_levels)){
				$q .= " INNER JOIN " . $wpdb->prefix . "ihc_user_levels as b";
				$q .= " ON a.ID=b.user_id";
			}

			$q .= " INNER JOIN {$wpdb->usermeta} as c on a.ID=c.user_id";
			$q .= " INNER JOIN {$wpdb->usermeta} as d on a.ID=d.user_id";

			if (is_multisite()){
					$q .= " INNER JOIN {$wpdb->usermeta} as e ON a.ID=e.user_id ";
			}

			/// lst name
			$q .= " INNER JOIN {$wpdb->usermeta} as f ON a.ID=f.user_id ";
			///

			$checkIfUserAgree = $this->checkIfUserAcceptedToBeDisplayed();
			if ( $checkIfUserAgree ){
					$q .= " INNER JOIN {$wpdb->usermeta} as checkDisplay on a.ID=checkDisplay.user_id ";
			}

			/// FILTER
			if (!empty($this->search_get_filter) && $skip_filter===FALSE){
				$alias_array = array();
				foreach ($this->search_get_filter as $filter_key=>$filter_value){
					$alias = ihc_generate_alias_name(7, $alias_array);
					$alias_array[$filter_key] = $alias;
					$q .= " INNER JOIN " . $wpdb->base_prefix . "usermeta as $alias on a.ID=$alias.user_id";
				}
			}
			/// FILTER

			$q .= " WHERE 1=1";
			if (!empty($inner_join_levels)){
				$q .= " AND (";
				for ($i=0; $i<count($inner_join_levels); $i++){
					if ($i>0){
						$q .= " OR";
					}
					$inner_join_levels[$i] = $wpdb->_real_escape($inner_join_levels[$i]);
					$q .= " b.level_id='" . $inner_join_levels[$i] . "'";
				}
				$q .= ") ";

				$now = indeed_get_current_time_with_timezone();
				$q .= " AND b.expire_time>'$now' ";
			}

			//EXCLUDE ADMINISTRATORS
			if (!empty($not_in)){
				$q .= " AND a.ID NOT IN (" . $not_in . ")";
			}

			if ($search_q && $search_by){
				$q .= " AND (";
				$search_fields = explode(',', $search_by);
				$mail_in = array_search('user_email', $search_fields);
				if ($mail_in!==FALSE && isset($search_fields[$mail_in])){
					$q .= " (a.user_email LIKE '%$search_q%') ";
					unset($search_fields[$mail_in]);
				}
				if (!empty($search_fields)){
					if ($mail_in!==FALSE){
						$q .= " OR ";
					}
					$fields_str = '';
					foreach ($search_fields as $field_val){
						if ($fields_str){
							$fields_str .= ",";
						}
						$field_val = $wpdb->_real_escape($field_val);
						$fields_str .= "'$field_val'";
					}
					if (strpos($search_q, ' ')!==FALSE){
							$search_var = str_replace(' ', '|', $search_q);
							$q .= " (c.meta_key IN ($fields_str) AND c.meta_value REGEXP '$search_var') ";
					} else {
							$q .= " (c.meta_key IN ($fields_str) AND c.meta_value LIKE '%$search_q%') ";
					}
				}
							$q .= ")";
			}

			/// EXCLUDE PENDING
			if (!empty($this->args['exclude_pending'])){
				$capabilities = $wpdb->prefix . 'capabilities';
				$q .= " AND ( d.meta_key='$capabilities' AND CAST(d.meta_value as CHAR) NOT LIKE '%pending_user%' ) ";
			}

			if (is_multisite()){
				global $blog_id;
				$role_key = $wpdb->get_blog_prefix( $blog_id ) . 'capabilities';
				$q .= $wpdb->prepare(" AND (e.meta_key=%s AND e.meta_value IS NOT NULL) ", $role_key );
			}

			/// FILTER
			if (!empty($this->search_get_filter) && $skip_filter===FALSE){
				foreach ($this->search_get_filter as $filter_key=>$filter_value){

					///security
					$filter_key = $wpdb->_real_escape($filter_key);
					if ($filter_value && is_array($filter_value)){
						foreach ($filter_value as $sk=>$sv){
							$filter_value[$sk] = $wpdb->_real_escape($sv);
						}
					}

					$alias = $alias_array[$filter_key];
					$temp_field_type = ihc_register_field_get_type_by_slug($filter_key);
					if (is_array($filter_value)){
						if ($temp_field_type=='number'){
							$q .= "	AND $alias.meta_key='$filter_key' ";
							if (isset($filter_value[0]) && isset($filter_value[1])){
								$q .= " AND ( CAST($alias.meta_value as SIGNED) BETWEEN '{$filter_value[0]}' AND '{$filter_value[1]}' ) ";
							} else if (isset($filter_value[0])){
								$q .= " AND ( CAST($alias.meta_value as SIGNED))>(CAST('{$filter_value[0]}' as SIGNED)) ";
							} else if (isset($filter_value[1])){
								$q .= " AND (CAST($alias.meta_value as SIGNED))<(CAST('{$filter_value[1]}' as SIGNED)) ";
							}
						} else if ($temp_field_type=='date'){
							$q .= "	AND $alias.meta_key='$filter_key' ";
							if (isset($filter_value[0]) && isset($filter_value[1])){
								$q .= " AND ( STR_TO_DATE($alias.meta_value, '%d-%m-%Y') BETWEEN STR_TO_DATE('{$filter_value[0]}', '%d-%m-%Y') AND STR_TO_DATE('{$filter_value[1]}', '%d-%m-%Y') ) ";
							} else if (isset($filter_value[0])){
								$q .= " AND STR_TO_DATE($alias.meta_value, '%d-%m-%Y')>=STR_TO_DATE('{$filter_value[0]}', '%d-%m-%Y') ";
							} else if (isset($filter_value[1])){
								$q .= " AND STR_TO_DATE($alias.meta_value, '%d-%m-%Y')<=STR_TO_DATE('{$filter_value[1]}', '%d-%m-%Y') ";
							}
						} else if ($temp_field_type=='checkbox' || $temp_field_type=='multi_select'){
							$q .= "	AND $alias.meta_key='$filter_key' AND (";
							$add_or_sign = FALSE;
							foreach ($filter_value as $temp_filter_val){
								if ($add_or_sign){
									$q .= ' OR ';
								}
								$q .= " $alias.meta_value LIKE '%$temp_filter_val%' ";
								$add_or_sign = TRUE;
							}
							$q .= ") ";
						}
					} else {
						$q .= "	AND $alias.meta_key='$filter_key' ";
						$q .= " AND $alias.meta_value='$filter_value' ";
					}
				}
			}
			/// FILTER

			if ( $checkIfUserAgree ){
					$q .= " AND ( checkDisplay.meta_key='ihc_memberlist_accept' AND checkDisplay.meta_value='1' ) ";
			}


			$q .= " AND f.meta_key='last_name' ";
			if ( isset( $order_by ) && $order_by == 'last_name' ){
					$order_by_str = 'f.meta_value';
			} else if ( isset( $order_by ) && $order_by !== '' ){
					$order_by_str = "a." . $order_by;
			}

			if ($order_type && $order_by && isset( $order_by_str ) && $order_by_str !== '' ){
				$q .= " ORDER BY $order_by_str " . $order_type;
			}


			if ($limit>-1 && $offset>-1){
				$q .= $wpdb->prepare(" LIMIT %d OFFSET %d ", $limit, $offset );
			}

			$data = $wpdb->get_results($q);
			if ($count){
				/// all ids
				if (!empty($data)){
					foreach ($data as $object){
						if (isset($object->uid)){
							$this->uid_arr[] = $object->uid;
						}
					}
					return count($data);
				}
				return 0;
			} else {
				$return = array();
				if (!empty($data) && is_array($data)){
					foreach ($data as $obj){
						if (isset($obj->user_id)){
							$return[] = $obj->user_id;
						}
					}
				}

				return $return;
			}
			return array();
		}

		private function checkIfUserAcceptedToBeDisplayed()
		{
				$registerFields = get_option( 'ihc_user_fields' );
				if ( !$registerFields ){
						return false;
				}
				$accept = ihc_array_value_exists( $registerFields, 'ihc_memberlist_accept', 'name' );
				if ( !$accept ){
						return false;
				}
				if ( empty( $registerFields[$accept] ) || empty( $registerFields[$accept]['display_public_reg'] ) ){
						return false;
				}
				return true;
		}

		private function create_the_js_after_html(){
			/*
			 * @param
			 * @return string
			 */
			$str = '';
			if (!empty($this->args['slider_set'])){
				$total_pages = count($this->users) / $this->args['items_per_slide'];

				if ($total_pages>1){
					$navigation = (empty($this->args['nav_button'])) ? 'false' : 'true';
					$bullets = (empty($this->args['bullets'])) ? 'false' : 'true';
					if (empty($this->args['autoplay'])){
						$autoplay = 'false';
						$autoplayTimeout = 5000;
					} else {
						$autoplay = 'true';
						$autoplayTimeout = $this->args['speed'];
					}
					$autoheight = (empty($this->args['autoheight'])) ? 'false' : 'true';
					$stop_hover = (empty($this->args['stop_hover'])) ? 'false' : 'true';
					$loop = (empty($this->args['loop'])) ? 'false' : 'true';
					$responsive = (empty($this->args['responsive'])) ? 'false' : 'true';
					$lazy_load = (empty($this->args['lazy_load'])) ? 'false' : 'true';
					$animation_in = (($this->args['animation_in'])=='none') ? 'false' : "'{$this->args['animation_in']}'";
					$animation_out = (($this->args['animation_out'])=='none') ? 'false' : "'{$this->args['animation_out']}'";
					$slide_pagination_speed = $this->args['pagination_speed'];

					$str .= "
										<span class='ihc-js-owl-settings-data'
												data-selector='#" . $this->div_parent_id . "'
												data-autoHeight='$autoheight'
												data-animateOut='$animation_out'
												data-animateIn='$animation_in'
												data-lazyLoad='$lazy_load'
												data-loop='$loop'
												data-autoplay='$autoplay'
												data-autoplayTimeout='$autoplayTimeout'
												data-autoplayHoverPause='$stop_hover'
												data-autoplaySpeed='$slide_pagination_speed'
												data-nav='$navigation'
												data-navSpeed='$slide_pagination_speed'
												data-dots='$bullets'
												data-dotsSpeed='$slide_pagination_speed'
												data-responsiveClass='$responsive'
												data-navigation='$navigation'
										></span>";
				}
			}
			return $str;
		}

		private function create_the_css(){
			/*
			 * @param none
			 * @return string
			 */
			//add the themes and the rest of CSS here...
			$str = '';
			if (!empty($this->args['slider_set']) && !defined('IHC_SLIDER_LOAD_CSS')){
				///// SLIDER CSS
				$str .= '<link rel="stylesheet" type="text/css" href="' . IHC_URL . 'public/listing_users/assets/css/owl.carousel.css">';
				$str .= '<link rel="stylesheet" type="text/css" href="' . IHC_URL . 'public/listing_users/assets/css/owl.theme.css">';
				$str .= '<link rel="stylesheet" type="text/css" href="' . IHC_URL . 'public/listing_users/assets/css/owl.transitions.css">';
				define('IHC_SLIDER_LOAD_CSS', TRUE);
			}
			if (!empty($this->args['theme'])){
				///// THEME
				$str .= '<link rel="stylesheet" type="text/css" href="' . IHC_URL . 'public/listing_users/themes/' . $this->args['theme'] . '/style.css">';
			}
			if (!defined('IHC_COLOR_CSS_FILE')){
				////// COLOR EXTERNAL CSS
				$str .= '<link rel="stylesheet" type="text/css" href="' . IHC_URL . 'public/listing_users/assets/css/layouts.css">';
				define('IHC_COLOR_CSS_FILE', TRUE);
			}
			$custom_css = '';
			///// SLIDER COLORS
			if (!empty($this->args['color_scheme']) && !empty($this->args['slider_set'])){
				$custom_css .= '
							.style_'.$this->args['color_scheme'].' .owl-ihc-theme .owl-ihc-dots .owl-ihc-dot.active span, .style_'.$this->args['color_scheme'].'  .owl-ihc-theme .owl-ihc-dots .owl-ihc-dot:hover span { background: #'.$this->args['color_scheme'].' !important; }
							.style_'.$this->args['color_scheme'].' .pag-theme1 .owl-ihc-theme .owl-ihc-nav [class*="owl-ihc-"]:hover{ background-color: #'.$this->args['color_scheme'].'; }
							.style_'.$this->args['color_scheme'].' .pag-theme2 .owl-ihc-theme .owl-ihc-nav [class*="owl-ihc-"]:hover{ color: #'.$this->args['color_scheme'].'; }
							.style_'.$this->args['color_scheme'].' .pag-theme3 .owl-ihc-theme .owl-ihc-nav [class*="owl-ihc-"]:hover{ background-color: #'.$this->args['color_scheme'].';}
						';
			}
			////// ALIGN CENTER
			if (!empty($this->args['align_center'])) {
				$custom_css .= '#'.$this->div_parent_id.' ul{text-align: center;}';
			}
			///// CUSTOM CSS
			if (!empty($this->general_settings['ihc_listing_users_custom_css'])){
				$custom_css .= stripslashes($this->general_settings['ihc_listing_users_custom_css']);
			}
			//// RESPONSIVE
			if (!empty($this->general_settings['ihc_listing_users_responsive_small'])){
				$width = 100 / $this->general_settings['ihc_listing_users_responsive_small'];
				$custom_css .= '
						@media only screen and (max-width: 479px){
							#' . $this->div_parent_id . ' ul li{
								width: calc(' . $width . '% - 1px) !important;
							}
						}
				';
			}
			if (!empty($this->general_settings['ihc_listing_users_responsive_medium'])){
				$width = 100 / $this->general_settings['ihc_listing_users_responsive_medium'];
				$custom_css .= '
						@media only screen and (min-width: 480px) and (max-width: 767px){
							#' . $this->div_parent_id . ' ul li{
								width: calc(' . $width . '% - 1px) !important;
							}
						}
				';
			}
			if (!empty($this->general_settings['ihc_listing_users_responsive_large'])){
				$width = 100 / $this->general_settings['ihc_listing_users_responsive_large'];
				$custom_css .= '
						@media only screen and (min-width: 768px) and (max-width: 959px){
							#' . $this->div_parent_id . ' ul li{
								width: calc(' . $width . '% - 1px) !important;
							}
						}
				';
			}

			wp_register_style( 'dummy-handle', false );
			wp_enqueue_style( 'dummy-handle' );
			wp_add_inline_style( 'dummy-handle', stripslashes($custom_css) );

			return $str;
		}

		private function create_the_js(){
			/*
			 * @param
			 * @return string
			 */
			$str = '';
			wp_enqueue_script( 'ihc-listing-users', IHC_URL . 'assets/js/listing-users.js', [ 'jquery' ], '12.8' );
			if (!empty($this->args['slider_set']) && !defined('IHC_SLIDER_LOAD_JS')){
				wp_enqueue_script( 'ihc-owl-carousel', IHC_URL . 'public/listing_users/assets/js/owl.carousel.js', [ 'jquery' ], '12.8' );
				define('IHC_SLIDER_LOAD_JS', TRUE);
			}
			return $str;
		}

		private function print_pagination(){
			/*
			 * @param none
			 * @return string
			 */
			$str = '';
			$current_page = (empty($this->args['current_page'])) ? 1 : $this->args['current_page'];
			$this->total_pages = ceil($this->total_users/$this->args['entries_per_page']);

			$url = IHC_PROTOCOL . sanitize_text_field($_SERVER['HTTP_HOST']) . sanitize_url($_SERVER['REQUEST_URI']);

			$str = '';

			if ($this->total_pages<=5){
				//show all the links
				for ($i=1; $i<=$this->total_pages; $i++){
					$show_links[] = $i;
				}
			} else {
				// we want to show only first, last, and the first neighbors of current page
				$show_links = array(1, $this->total_pages, $current_page, $current_page+1, $current_page-1);
			}

			for ($i=1; $i<=$this->total_pages; $i++){
				if (in_array($i, $show_links)){
					$href = (defined('IS_PREVIEW')) ? '#' : add_query_arg('ihcUserList_p', $i, $url);
					$selected = ($current_page==$i) ? '-selected' : '';
					$str .= "<a href='$href' class='ihc-user-list-pagination-item" . $selected . "'>" . $i . '</a>';
					$dots_on = TRUE;
				} else {
					if (!empty($dots_on)){
						$str .= '<span class="ihc-user-list-pagination-item-break">...</span>';
						$dots_on = FALSE;
					}
				}
			}
			/// Back link
			if ($current_page>1){
				$prev_page = $current_page - 1;
				$href = (defined('IS_PREVIEW')) ? '#' : add_query_arg('ihcUserList_p', $prev_page, $url);
				$str = "<a href='" . $href . "' class='ihc-user-list-pagination-item'> < </a>" . $str;
			}
			///Forward link
			if ($current_page<$this->total_pages){
				$next_page = $current_page + 1;
				$href = (defined('IS_PREVIEW')) ? '#' : add_query_arg('ihcUserList_p', $next_page, $url);
				$str = $str . "<a href='" . $href . "' class='ihc-user-list-pagination-item'> > </a>";
			}

			//Wrappers
			$str = "<div class='ihc-user-list-pagination'>" . $str . "</div><div class='ihc-clear'></div>";
			return $str;
		}

		private function create_the_html(){
			/*
			 * @param none
			 * @return string
			 */
			$str = '';
			$total_items = count($this->users);
			$items_per_slide = (empty($this->args['slider_set'])) ? $total_items : $this->args['items_per_slide'];

			include $this->single_item_template;
			if (empty($list_item_template)){
				return '';
			}

			$this->li_width = 'calc(' . 100/$this->args['columns'] . '% - 1px)';
			$i = 1;
			$breaker_div = 1;
			$new_div = 1;
			$color_class = (empty($this->args['color_scheme'])) ? 'style_0a9fd8' : 'style_' . $this->args['color_scheme'];
			$parent_class = (empty($this->args['slider_set'])) ? 'ihc-content-user-list' : 'ihc-carousel-view';//carousel_view
			$num = rand(1, 10000);
			$this->div_parent_id = 'indeed_carousel_view_widget_' . $num;
			$arrow_wrapp_id = 'wrapp_arrows_widget_' . $num;
			$ul_id = 'ihc_list_users_ul_' . rand(1, 10000);

			///// WRAPPERS
			$extra_class = (empty($this->args['pagination_theme'])) ? '' : $this->args['pagination_theme'];
			$str .= "<div id='ihc_public_list_users_" . rand(1, 10000) . "'>";
			$str .= "<div class='$color_class'>";
			$str .= "<div class='" . $this->args['theme'] . " " . $extra_class . "'>";
			$str .= "<div class='ihc-wrapp-list-users'>";
			$str .= "<div class='$parent_class' id='" . $this->div_parent_id . "' >";

			////// ITEMS
			foreach ($this->users as $uid=>$arr){
				if (!empty($new_div)){
					$div_id = $ul_id . '_' . $breaker_div;
					$str .= "<ul id='$div_id'>"; /////ADDING THE UL
				}

				$str .= $this->print_item($uid, $list_item_template, $socials_arr);///// PRINT SINGLE ITEM

				if ($i % $items_per_slide==0 || $i==$total_items){
					$breaker_div++;
					$new_div = 1;
					$str .= "<div class='ihc-clear'></div></ul>";
				} else {
					$new_div = 0;
				}
				$i++;
			}

			///// CLOSE WRAPPERS
			$str .= '</div>'; /// end of $parent_class
			$str .= '</div>'; /// end of ihc-wrapp-list-users
			$str .= '</div>'; /// end of $args['theme'] . " " . $args['pagination_theme']
			$str .= '</div>'; /// end of $color_class
			$str .= '</div>'; //// end of ihc_public_list_users_

			return $str;
		}

		private function print_item($uid, $template, $socials_arr){
			/*
			 * SINGLE ITEM
			 * @param int, string, array
			 * @return string
			 */
			$fields = $this->user_fields;

			$str = '';
			$str .= "<li style = ' width: $this->li_width' >";

			//AVATAR
			$this->users[$uid]['ihc_avatar'] = ihc_get_avatar_for_uid($uid);

			///STANDARD FIELDS
			$standard_fields = array(
										"user_login" => "IHC_USERNAME",
										"first_name" => "IHC_FIRST_NAME",
										"last_name" => "IHC_LAST_NAME",
										"user_email" => "IHC_EMAIL",
										"ihc_avatar" => "IHC_AVATAR",
 			);

			foreach ($standard_fields as $k=>$v){
				$data = '';
				if (in_array($k, $fields)){
					$data = $this->users[$uid][$k];
				}
				$template = str_replace($v, $data, $template);
				$key = array_search($k, $fields);
				if ($key!==FALSE){
					unset($fields[$key]);
				}
			}

			///SOCIAL MEDIA STUFF
			if (in_array('ihc_sm', $fields)){
				$key = array_search('ihc_sm', $fields);
				unset($fields[$key]);
				$social_media_string = '';
				$sm_arr = array(
						'ihc_fb' => 'FB',
						'ihc_tw' => 'TW',
						'ihc_in' => 'LIN',
						'ihc_tbr' => 'TBR',
						'ihc_ig' => 'INS',
						'ihc_vk' => 'VK',
						'ihc_goo' => 'GP',
				);
				$sm_base = array(
									'ihc_fb' => 'https://www.facebook.com/',///old version was : profile.php?id=
									'ihc_tw' => 'https://twitter.com/intent/user?user_id=',
									'ihc_in' => 'https://www.linkedin.com/profile/view?id=',
									'ihc_tbr' => 'https://www.tumblr.com/blog/',
									'ihc_ig' => 'http://instagram.com/_u/',
									'ihc_vk' => 'http://vk.com/id',
									'ihc_goo' => 'https://plus.google.com/',
								);
				foreach ($sm_arr as $k=>$v){
					$data = get_user_meta($uid, $k, TRUE);
					if (!empty($data)){
						$data = $sm_base[$k] . $data;
						$social_media_string .= str_replace($v, $data, $socials_arr[$k]);
					}
				}
				$template = str_replace("IHC_SOCIAL_MEDIA", $social_media_string, $template);
			} else if (strpos($template, 'IHC_SOCIAL_MEDIA')!==FALSE) {
				$template = str_replace("IHC_SOCIAL_MEDIA", '', $template);
			}

			/// SOME EXTRA FIELDS

			$extra_fields = '';
			if ($fields){
				foreach ($fields as $value){
					$extra_fields_str = '';
					if (!empty($this->users[$uid][$value])){
						if (!empty($this->args['include_fields_label']) && !empty($this->fields_label[$value])){
							$extra_fields_str .= '<span class="ihc-user-list-label">' . $this->fields_label[$value] . ' </span>';
							$extra_fields_str .= '<span class="ihc-user-list-label-result">';
						}else{
							$extra_fields_str .= '<span class="ihc-user-list-result">';
						}
						if ($value === 'ihc_country'){
							$countries = ihc_get_countries();
							$extra_fields_str .= $countries[$this->users[$uid][$value]];
						} else {
							if (is_array($this->users[$uid][$value])){
								$extra_fields_str .= implode(', ', $this->users[$uid][$value]);
							} else {
								if ( strpos( $this->users[$uid][$value], 'http' ) === 0 ){
										$this->users[$uid][$value] = "<a href='{$this->users[$uid][$value]}' target='_blank' >" . $this->users[$uid][$value] . "</a>";
								} else if ( strpos( $this->users[$uid][$value], 'www.' ) === 0 ){
										$this->users[$uid][$value] = "<a href='{$this->users[$uid][$value]}' target='_blank' >" . $this->users[$uid][$value] . "</a>";
								} else if ( $this->fields_type[$value] === 'file' ){
										//$this->users[$uid][$value] = wp_get_attachment_url($this->users[$uid][$value]);
										$this->users[$uid][$value] = "<a href='" . wp_get_attachment_url($this->users[$uid][$value]) . "' target='_blank' >" . esc_html__( 'Download', 'ihc') . "</a>";
								}
								$extra_fields_str .= $this->users[$uid][$value];
							}
						}

						$extra_fields_str .= '</span>';
						$extra_fields_str .= '<div class="ihc-clear"></div>';
						if (!empty($extra_fields_str)){
							$extra_fields .= '<div class="member-extra-single-field">' . $extra_fields_str . '</div>';
						}
					}
				}
			}
			$template = str_replace('IHC_EXTRA_FIELDS', $extra_fields, $template);

			/// LINK TO USER PAGE
			$link = '#';
			if (!empty($this->args['inside_page']) && !empty($this->link_user_page)){
				$target_blank = (empty($this->general_settings['ihc_listing_users_target_blank'])) ? '' : 'target="_blank"';
				if (empty($this->users[$uid]['user_login'])){
					$username = Ihc_Db::get_username_by_wpuid($uid);
					$username = urlencode($username);
				} else {
					$username = urlencode($this->users[$uid]['user_login']);
				}

				if ($this->permalink_type){
					$link = trailingslashit(trailingslashit($this->link_user_page) . $username );
				} else {
					$link = add_query_arg('ihc_name', $username, $this->link_user_page);
				}

				$link = ' href="' . $link . '" ' . $target_blank;
			}
			$template = str_replace("#POST_LINK#", $link, $template);

			$str .= $template;
			$str .= '</li>';
			return $str;
		}

		private function set_filter_form_fields(){
			/*
			 * @param none
			 * @return string
			 */
			 if (isset($this->args['search_filter_items'])){
				 $fields = explode(',', $this->args['search_filter_items']);
				 $output = '';
				 global $post;
				 $base_url = get_permalink((isset($post->ID)) ? $post->ID : '');

				 if ($fields){
				 	foreach ($fields as $field){
				 		$temporary_array['type'] = ihc_register_field_get_type_by_slug($field);
						$temporary_array['label'] = ihc_get_custom_field_label($field);
						$temporary_array['name'] = $field;

						$field_temp_array[$field] = $temporary_array;
			 		}
					/// let's reorder the field so will look like on register form
					$the_correct_order = ihc_get_register_form_fields_order();
					foreach ($the_correct_order as $field_name=>$number){
						if (isset($field_temp_array[$field_name])){
							$this->filter_form_fields[$field_name] = $field_temp_array[$field_name];
						}
					}
				 }
			 }
		}

		private function set_filter_form_possible_values(){
			/*
			 * @param none
			 * @return none
			 */
			 if (!empty($this->filter_form_fields)){
			 	foreach ($this->filter_form_fields as $name=>$field_array){
					$this->filter_form_fields[$name]['values'] = $this->get_register_field_possible_values($name, $this->filter_form_fields[$name]['type']);
			 	}
			 }
		}

		private function print_filter_form(){
			/*
			 * @param none
			 * @return string
			 */
			 $output = '';
			 global $post;
			 $countries = ihc_get_countries();
			 $base_url = get_permalink((isset($post->ID)) ? $post->ID : '');

			 $fullPath = IHC_PATH . 'public/views/listing_users-filter.php';
			 $searchFilename = 'listing_users-filter.php';
			 $template = apply_filters('ihc_filter_on_load_template', $fullPath, $searchFilename );

			 ob_start();
			 require $template;
			 $output = ob_get_contents();
			 ob_end_clean();
			 return $output;
		}

		private function get_register_field_possible_values($field_slug='', $type=''){
			/*
			 * @param string, string
			 * @return array
			 */
			 $array = array();
			 if ($field_slug){
			 	 global $wpdb;
				 $table = $wpdb->base_prefix . 'usermeta';

				 $field_slug = $wpdb->_real_escape($field_slug);

				 $ids_in = '';
				 if (empty($this->uid_arr)){
				 	 $this->set_ids_again();
				 }
				 if (!empty($this->uid_arr)){
				 	 $ids_in = implode(',', $this->uid_arr);
					 $ids_in = $wpdb->_real_escape($ids_in);
				 }
				 switch ($type){
					case 'ihc_country':
					case 'select':
					case 'radio':
						$q = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table WHERE meta_key=%s ", $field_slug );
						if ($ids_in){
							$q .= " AND user_id IN ($ids_in) ";
						}
						$q .= " ORDER BY umeta_id DESC;";
						$data = $wpdb->get_results($q);
						if ($data){
						 	 foreach ($data as $object){
						 	 	if (isset($object->meta_value)){
							 	 	$array[] = $object->meta_value;
						 	 	}
						 	 }
						}
						$do_reorder = TRUE;
						break;
					case 'ihc_country':
						$q = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table WHERE meta_key=%s ", $field_slug );
						if ($ids_in){
							$q .= " AND user_id IN ($ids_in) ";
						}
						$q .= " ORDER BY umeta_id DESC;";
						$data = $wpdb->get_results($q);
						if ($data){
						 	 foreach ($data as $object){
						 	 	if (isset($object->meta_value)){
							 	 	$array[] = $object->meta_value;
						 	 	}
						 	 }
						}
						$do_reorder = TRUE;
						break;
					case 'checkbox':
					case 'multi_select':
						$q = $wpdb->prepare("SELECT DISTINCT meta_value FROM $table WHERE meta_key=%s ", $field_slug );
						if ($ids_in){
							$q .= " AND user_id IN ($ids_in) ";
						}
						$q .= " ORDER BY umeta_id DESC;";
						$data = $wpdb->get_results($q);
						if ($data){
						 	 foreach ($data as $object){
						 	 	if ( isset( $object->meta_value ) && !empty( $object->meta_value ) && is_serialized( $object->meta_value ) ){
									$temp = @unserialize($object->meta_value);
									if (is_array($temp)){
										foreach ($temp as $temp_val){
									 	 	if (!in_array($temp_val, $array)){
	 									 	 	$array[] = $temp_val;
									 	 	}
										}
									}
						 	 	}
						 	 }
						}
						$do_reorder = TRUE;
						break;
					case 'number':
						$q = $wpdb->prepare("SELECT CAST(meta_value AS SIGNED) as min FROM $table WHERE meta_key=%s AND meta_value!='' ", $field_slug );
						if ($ids_in){
							$q .= " AND user_id IN ($ids_in) ";
						}
						$q .= " ORDER BY CAST(meta_value AS SIGNED) ASC LIMIT 1;";
						$data = $wpdb->get_row($q);
						if (isset($data->min)){
							$array['min'] = $data->min;
						}
						$q = $wpdb->prepare("SELECT CAST(meta_value AS SIGNED) as max FROM $table WHERE meta_key=%s AND meta_value!='' ", $field_slug );
						if ($ids_in){
							$q .= " AND user_id IN ($ids_in) ";
						}
						$q .= " ORDER BY CAST(meta_value AS SIGNED) DESC LIMIT 1;";
						$data = $wpdb->get_row($q);
						if (isset($data->max)){
							$array['max'] = $data->max;
						}
						break;
					case 'date':
						$q = $wpdb->prepare( "SELECT meta_value FROM $table WHERE meta_key=%s AND meta_value!='' ", $field_slug );
						if ($ids_in){
							$q .= " AND user_id IN ($ids_in) ";
						}
						$q .= " ORDER BY meta_value ASC LIMIT 1;";
						$data = $wpdb->get_row($q);
						if (isset($data->meta_value)){
							$array['min'] = $data->meta_value;
						}
						$q = $wpdb->prepare( "SELECT meta_value FROM $table WHERE meta_key=%s AND meta_value!='' ", $field_slug );
						if ($ids_in){
							$q .= " AND user_id IN ($ids_in) ";
						}
						$q .= " ORDER BY meta_value DESC LIMIT 1;";
						$data = $wpdb->get_row($q);
						if (isset($data->meta_value)){
							$array['max'] = $data->meta_value;
						}
						break;
				}
			 }

			 /// REORDER
			 if ($array && !empty($do_reorder)){
			 	$values_in_corrent_order = ihc_register_form_get_order_values($field_slug);
			 	if ($values_in_corrent_order){
			 		$temp_arr = $array;
					unset($array);
					$array = array();
			 		foreach ($values_in_corrent_order as $key=>$value){
			 			if (in_array($value, $temp_arr)){
			 				$array[] = $value;
			 			}
			 		}
			 	}
			 }


			 return $array;
		}

		private function set_ids_again(){
			/*
			 * @param none
			 * @return none
			 */
			$search_bar = '';
			$search_filter = '';

			if (empty($this->args['entries_per_page'])){
				 $this->args['entries_per_page'] = 25;
			}
			$search_by = empty($this->args['search_by']) ? '' : $this->args['search_by'];
			$search_q = empty($_GET['ihc_search_u']) ? '' : sanitize_text_field($_GET['ihc_search_u']);
			$search_q = sanitize_text_field( $search_q );

			////// FILTER BY LEVELs
			if (!empty($this->args['filter_by_level']) && !empty($this->args['levels_in'])){
				if (strpos($this->args['levels_in'], ',')!==FALSE){
					$inner_join_levels = explode(',', $this->args['levels_in']);
				} else {
					$inner_join_levels = array($this->args['levels_in']);
				}
			} else {
				$inner_join_levels = array();
			}

			////////// ORDER
			$order_by = $this->args['order_by'];
			if ($order_by=='random'){
				$order_by = '';
			}
			$order_type = $this->args['order_type'];

			//// FILTER
			if (!empty($_GET['iump_filter'])){
				foreach ($_GET as $get_key=>$get_value){
					$get_key = sanitize_text_field( $get_key );
					if (isset($this->filter_form_fields[$get_key]) && $_GET[$get_key]!=''){
						if (is_array($get_value)){
							if (isset($get_value[0]) && $get_value[0]!=''){
								foreach ( $get_value as $subkey => $subvalue){
										$get_value[$subkey] = sanitize_text_field($subvalue);
								}
								$this->search_get_filter[$get_key] = $get_value;
							}
						} else {
							$this->search_get_filter[$get_key] = sanitize_text_field($get_value);
						}
					}
				}
			}

			//////////TOTAL USERS
			$this->get_users($order_by, $order_type, -1, -1, TRUE, $inner_join_levels, $search_by, $search_q, TRUE);

		}



	}
}
