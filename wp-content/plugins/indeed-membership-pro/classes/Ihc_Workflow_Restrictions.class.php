<?php
if (!class_exists('Ihc_Workflow_Restrictions')):

class Ihc_Workflow_Restrictions{
	private static $metas = array();
	private static $just_once = FALSE;
	private static $defaultPages = array();

	public function __construct(){
		/*
		 * @param none
		 * @return none
		 */
		if (empty(self::$metas)){
			self::$metas = ihc_return_meta_arr('workflow_restrictions');
			if (self::$metas['ihc_workflow_restrictions_on']){
				/// set cookie
				add_action('init', array($this, 'set_cookie'), 9999);
				/// view posts
				add_filter('filter_on_ihc_test_if_must_block', array($this, 'user_can_view_the_post'), 99999, 6 );
				/// insert comment
				add_action('comment_post', array($this, 'user_can_add_comments'), 999, 2);
				/// insert posts
				add_filter('wp_insert_post_data', array($this, 'user_can_add_posts'), 999, 2);
			}
		}
	}


	/*
	 * Set the cookie with current post_id, perform this action in init.
	 * If post id cannot be determined the cookie will be set on filter_on_ihc_test_if_must_block filter.
	 * @param none
	 * @return none
	 */
	public function set_cookie(){
		if ( current_user_can('manage_options') || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ){
			 return;
		}

		global $current_user;
		$uid = (isset($current_user->ID)) ? $current_user->ID : 0;
		if ( !isset( $_SERVER['HTTP_HOST'] ) || !isset( $_SERVER['REQUEST_URI'] ) ){
				return;
		}
		$post_id = url_to_postid(IHC_PROTOCOL . sanitize_url($_SERVER['HTTP_HOST']) . sanitize_url($_SERVER['REQUEST_URI']) );

		if ($post_id && $uid){
			 $this->increment_user_posts_views($uid, $post_id);
		}
	}


	public function user_can_add_posts($data, $postarr){
		/*
		 * @param
		 * @return none
		 */
		 if ($data){
		 	 global $current_user;
			 $uid = (isset($current_user->ID)) ? $current_user->ID : 0;
			 if ($uid && !current_user_can('manage_options')){
			 	 $since = indeed_get_unixtimestamp_with_timezone() - ((int)self::$metas['ihc_workflow_restrictions_timelimit'] * 24 * 3600);
			 	 $count_posts = Ihc_Db::user_get_inserted_posts_count($uid, $since);
				 $limit = $this->get_limit_count_for_user($uid, 'add_posts');

				 if ($limit!==FALSE && $data['post_status']!='auto-draft'){
				 	 if ($limit<=$count_posts){
				 	 	$data['post_status'] = 'pending';
				 	 }
				 }
			 }
		 }
		 return $data;
	}

	public function user_can_add_comments($comment_id=0, $comment_approved=null){
		/*
		 * @param int, boolean
		 * @return none
		 */
		 global $current_user;
		 $uid = (isset($current_user->ID)) ? $current_user->ID : 0;
		 if ($uid && !current_user_can('manage_options')){
		 	 $since = indeed_get_unixtimestamp_with_timezone() - ((int)self::$metas['ihc_workflow_restrictions_timelimit'] * 24 * 3600);
		 	 $count_comments = Ihc_Db::user_get_inserted_comments_count($uid, $since);
		 	 $limit = $this->get_limit_count_for_user($uid, 'comments');
			 $count_comments = (int)$count_comments;
			 $limit = (int)$limit;
		   if ($limit!==FALSE){
			   if ($limit<=$count_comments){
			  	 	 IHc_Db::do_delete_comment($comment_id);
			 	 }
			 }
		 }
	}

	public function user_can_view_the_post($block, $block_or_show, $user_levels, $target_levels, $post_id, $usedLocation=''){
		/*
		 * @param :
		 * block = int (0 or 1)
		 * block_or_show = string 'block' or 'show'
	 	 * user_levels = string (all current user levels seppareted by comma)
	  	 * target levels = array (show/hide content for users with these levels)
		 * post_id = int
		 * @return int (0 or 1)
		 */
		 if ( $usedLocation === 'wp_menu' || $usedLocation === 'pre_get_posts' ){
				return $block;
		 }

		 if (empty($block)){

			 /// ump pages can be seen everytime
			 if ( $this->isAUmpPage( $post_id ) || !$post_id ){
			 		return $block;
			 }


		 	 /// only if user can view the post (has passed the previous tests)
		 	 global $current_user;
			 $uid = (isset($current_user->ID)) ? $current_user->ID : 0;
			 $cookie_name = 'ihc_workflow_restrictions_' . $uid;
			 if (isset($_COOKIE[$cookie_name])){
			 	 $cookie_post_arr = sanitize_text_field($_COOKIE[$cookie_name]);
			 	 $cookie_post_arr = stripslashes($cookie_post_arr);
				 $cookie_post_arr = json_decode($cookie_post_arr, true );
				 $count_views = count($cookie_post_arr);
			 } else {
			 	 $cookie_post_arr = array();
				 $count_views = 0;
			 }

			 if ($uid && !current_user_can('manage_options')){
				 $limit = $this->get_limit_count_for_user($uid, 'view_posts');

				 if ($limit!==FALSE && !in_array($post_id, $cookie_post_arr) && $limit<=$count_views){
					 return 1; /// do block
				 } else {
				  	 $this->increment_user_posts_views($uid, $post_id);
				 }
			 } else if ($uid==0){

			 	/// unreg user
			 	$limit = self::$metas['ihc_workflow_restrictions_post_views']['unreg'];

				if ($limit>0 && $limit!='' && $limit!==FALSE && !in_array($post_id, $cookie_post_arr) && $limit<=$count_views){
					 return 1; /// do block
				} else {

				 	 $this->increment_user_posts_views($uid, $post_id);
				}
			 }
		 }
		 return $block;
	}

	private function increment_user_posts_views($uid=0, $post_id=0){
		/*
		 * @param int, int
		 * @return none
		 */

		 if ( headers_sent() ){
			 	// prevent set the cookie after the headers was sent
			 	return;
		 }

		 if ( $this->isAUmpPage( $post_id ) || !$post_id ){
				return;
		 }

		 if ($post_id){
		 	 $cookie_name = 'ihc_workflow_restrictions_' . $uid;
		 	 if (isset($_COOKIE[$cookie_name])){
		 	 	 $array = sanitize_text_field($_COOKIE[$cookie_name]);
				 $array = stripslashes($array);
				 $array = json_decode($array, true);
		 	 } else {
		 	 	 $array = array();
		 	 }
			 if (!in_array($post_id, $array)){
			 	 $array[] = $post_id;
			 }

			 if (self::$just_once){
			 		return;
			 } else {
			 		self::$just_once = TRUE;
			 }

			 $cookie_time = indeed_get_unixtimestamp_with_timezone() + ((int)self::$metas['ihc_workflow_restrictions_timelimit'] * 24 * 3600);
		 	 setcookie($cookie_name, json_encode($array), $cookie_time, '/');

		 }
	}

	private function isAUmpPage( $postId=0 )
	{
			if ( empty(self::$defaultPages) ){
					self::$defaultPages = \Ihc_Db::getUmpDefaultPages();
			}
			if ( in_array( $postId, self::$defaultPages ) ){
					return true;
			}
			return false;
	}


	private function get_limit_count_for_user($uid=0, $type=''){
		/*
		 * @param int, string
		 * @return int or boolean
		 */
		 if ($uid && $type && self::$metas){
			 switch ($type){
			  	case 'view_posts':
					$meta_name = 'ihc_workflow_restrictions_post_views';
					break;
				case 'comments':
					$meta_name = 'ihc_workflow_restrictions_comments_created';
					break;
				case 'add_posts':
					$meta_name = 'ihc_workflow_restrictions_posts_created';
					break;
			 }
		 	 $uid_levels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $uid, true );
			 if ($uid_levels && count($uid_levels)>0 && $meta_name && isset(self::$metas[$meta_name])){
				 $return_value = FALSE;
				 foreach ($uid_levels as $level_data){
					$lid = (isset($level_data['level_id'])) ? $level_data['level_id'] : '';
					if ($return_value==FALSE){
						if (isset(self::$metas[$meta_name][$lid]) && self::$metas[$meta_name][$lid]!=''){
							$return_value = self::$metas[$meta_name][$lid];
						}
					} else if (isset(self::$metas[$meta_name][$lid]) && $return_value<self::$metas[$meta_name][$lid]){
						/// BIGGEST VALUE
						$return_value = self::$metas[$meta_name][$lid];
					}
				 }
				 return $return_value;
			 } else {
			 	/// Registered user with no active level
			 	$lid = 'reg';
				if (isset(self::$metas[$meta_name][$lid]) && self::$metas[$meta_name][$lid]!=''){
					return self::$metas[$meta_name][$lid];
				}
			 }
		 }
		 return FALSE;
	}

}

endif;
