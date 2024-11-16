<?php
if (!defined('ABSPATH')){
	 exit();
}
if (class_exists('Indeed_Duplicate_Posts')){
   return;
}
abstract class Indeed_Duplicate_Posts{
    protected $_copy_post_id = 0;
    protected $_created_post_id = 0;
    protected $_error = '';
    protected $_post_type = '';
    /**
     * @param INT
     * @return none
     */
    public function __construct($input=0){
        $this->_copy_post_id = $input;
    }
    public function Run(){
        $this->_duplicate_wp_posts_data();
        $this->_duplicate_taxonomies();
        $this->_duplicate_wp_postmeta_data();
        $this->_duplicate_custom_data();
        return $this->_created_post_id;
    }
    protected function _duplicate_wp_posts_data(){
        $post = get_post($this->_copy_post_id);
        if (empty($post)){
            $this->error = "Post dont exists";
            return;
        } else {
						$i = 2;
						while ( $this->postNameExists( $post->post_name . '-' . $i ) !== false ){
								$i++;
						}
            $post_data = array(
                  'post_name'      => $post->post_name . '-' . $i,
            			'post_title'     => $post->post_title,
            			'post_content'   => $post->post_content,
            			'post_type'      => $post->post_type,
            			'post_excerpt'   => $post->post_excerpt,
            			'post_author'    => $post->post_author,
            			'comment_status' => $post->comment_status,
            			'ping_status'    => $post->ping_status,
            			'post_parent'    => $post->post_parent,
            			'post_password'  => $post->post_password,
            			'post_status'    => 'draft',
            			'to_ping'        => $post->to_ping,
            			'menu_order'     => $post->menu_order,
            );
            $this->_created_post_id = wp_insert_post($post_data);
            $this->_post_type = $post->post_type;
        }
    }
    protected function _duplicate_taxonomies(){
        if (!$this->_created_post_id){
            retunr;
        }
        $taxonomies = get_object_taxonomies($this->_post_type);
        if ($taxonomies){
            foreach ($taxonomies as $taxonomy) {
                $post_terms = wp_get_object_terms($this->_copy_post_id, $taxonomy, array('fields' => 'slugs'));
                wp_set_object_terms($this->_created_post_id, $post_terms, $taxonomy, false);
            }
        }
    }
    protected function _duplicate_wp_postmeta_data(){
        global $wpdb;
				$query = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id=%d ;", $this->_copy_post_id );
        $post_meta_infos = $wpdb->get_results( $query );
      	if (count($post_meta_infos)>0) {
      			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
      			foreach ($post_meta_infos as $meta_info) {
        				$meta_key = $meta_info->meta_key;
        				if ($meta_key=='_wp_old_slug'){
                   continue;
                }
        				$meta_value = addslashes($meta_info->meta_value);
        				$sql_query_sel[]= "SELECT {$this->_created_post_id}, '$meta_key', '$meta_value'";
      			}
      			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
      			$wpdb->query($sql_query);
      	}
    }
    protected abstract function _duplicate_custom_data();

		/**
		 * @param string
		 * @return bool
		 */
		protected function postNameExists( $postName='' )
		{
				global $wpdb;
				$query = $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name=%s", $postName );
				if ( $wpdb->get_var( $query ) === null ){
						return false;
				}
				return true;
		}
}
