<?php
if (!defined('ABSPATH')){
	 exit();
}
if ( !class_exists('Indeed_List_Table')){
	 require_once ULP_PATH . 'classes/Abstracts/Indeed_List_Table.class.php';
}
if (class_exists('ListOrders')){
	 return;
}
class ListOrders extends Indeed_List_Table{
	protected $post_type = 'ulp_order';
	protected $label = 'Order';
	protected $label_plural = 'Orders';
  private $_order_meta_object = null;
	private $showOnlyCompletedInvoices = false;
	/**
	 * @param none
	 * @return string
	 */
	public function finalOutput(){
    require_once ULP_PATH . 'classes/Db/DbUlpOrdersMeta.class.php';
    $this->_order_meta_object = new DbUlpOrdersMeta();
		parent::prepare_items();
		parent::display();
	}
	/**
	 * @param none
	 * @return array
	 */
	public function get_columns(){
	  	$columns = array(
		  	'cb' => '<input type="checkbox" />',
		    'ID' =>  esc_html__('Order ID', 'ulp'),
				'code' =>  esc_html__('Code', 'ulp'),
		    'student' =>  esc_html__('Student', 'ulp'),
		    'course' =>  esc_html__('Course', 'ulp'),
		    'price' =>  esc_html__('Price', 'ulp'),
		    'source' =>  esc_html__('Source', 'ulp'),
		    'date' =>  esc_html__('Date', 'ulp'),
		    'status' =>  esc_html__('Status', 'ulp'),
	  	);
			if (get_option('ulp_invoices_enable')){
					$this->showOnlyCompletedInvoices = get_option( 'ulp_invoices_only_completed_payments' );
					$columns ['invoice'] =  esc_html__('Invoice', 'ulp');
			}
	  	$columns = apply_filters("manage_" . $this->post_type . "_posts_columns", $columns);
	  	$columns = apply_filters("manage_edit-" . $this->post_type . "_columns",  $columns);
		return $columns;
	}
	/**
	 * @param object
	 * @param string
	 * @return string
	 */
	public function column_default($post, $column_name){
	  	switch ($column_name){
	    	case 'ID':
					return $post->ID;
					break;
				case 'code':
					$code = $this->_order_meta_object->get($post->ID, 'code');
					if (empty($code)){
							echo esc_ulp_content('-');
							return;
					}
					echo esc_html($code);
					break;
				case 'date':
					return ulp_print_date_like_wp($post->post_date);
					break;
				case 'course':
          $course_id = $this->_order_meta_object->get($post->ID, 'course_id');
          if ($course_id){
              $course_name = DbUlp::getPostTitleByPostId($course_id);
              if ($course_name){
                  echo esc_html($course_name);
              }
          } else {
              echo esc_ulp_content('<span class="ulp-color-red">' .  esc_html__("The course no longer exists. (ID=", 'ulp') . $post->ID . ')' . '</span>');
          }
					break;
        case 'student':
          $uid = $this->_order_meta_object->get($post->ID, 'user_id');
          if ($uid){
							$avatar = DbUlp::getAuthorImage($uid);
							$username = DbUlp::getUsernameByUID($uid);
							return '<img src="' . $avatar . '"  />'.'<a href="' . admin_url('user-edit.php?user_id=' . $uid) . '" target="_blank">' . $username . '</a>' ;
              return;
          }
          esc_html_e('Unknown student', 'ulp');
          break;
        case 'price':
          $course_id = $this->_order_meta_object->get($post->ID, 'course_id');
          if (get_post_meta($course_id, 'ulp_course_payment', true)){
							$price = $this->_order_meta_object->get($post->ID, 'amount');
							if ($price){
                  echo ulp_format_price($price);
              }
          }
          break;
        case 'source':
          $source = $this->_order_meta_object->getSource($post->ID);
          if ($source){
              echo ucfirst($source);
          }
          break;
        case 'status':
          switch ( $post->post_status ){
              case 'ulp_complete':
                esc_html_e('Completed', 'ulp');
                break;
              case 'ulp_fail':
                esc_html_e('Fail', 'ulp');
                break;
              case 'ulp_pending':
                esc_html_e('Pending', 'ulp');
                break;
          }
          break;
        case 'invoice':
					if ( !empty( $this->showOnlyCompletedInvoices ) && isset( $post->post_status ) && $post->post_status !== 'ulp_complete' ) {
							return '-';
					} else {
							return '<i class="fa-ulp fa-invoice-preview-ulp ulp-pointer" onClick="ulpOpenInvoiceAdmin(' . $post->ID . ', ' . $post->post_author . ');"></i>';
					}
          break;
	  	}
	  	do_action("manage_{$this->post_type}_posts_custom_column", $column_name, $post->ID);
	}
  public function column_ID($item) {
      $wpnonce = wp_create_nonce('ulp_nonce');
      $actions = array(
              'edit'      => "<a href='" . admin_url('post.php?post=' . $item->ID . '&action=edit') . "'>" .  esc_html__("Edit", 'ulp') . "</a>",
              'delete'    => "<span class='js-ulp-do-delete-post ulp-delete-link' data-id='{$item->ID}'>".  esc_html__("Delete", 'ulp') . "</span>",///"<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_order&action=trash&id=' . $item->ID . '&_wpnonce=' . $wpnonce) . "'>" .  esc_html__("Delete", 'ulp') . "</a>",
      );

			$actions = apply_filters('ulp_filter_custom_post_type_dashboard_action_links', $actions);
      return $item->ID . sprintf('%1$s %2$s', $item->post_content, $this->row_actions($actions) );
  }
	/**
	 * @param none
	 * @return array
	 */
	public function get_sortable_columns() {
	  $sortable_columns = array(
	    'title'  => array('post_title',false),
	    'date' => array('post_date', false),
	  );
	  return $sortable_columns;
	}

	protected function table_data($per_page=5, $page_number=1){
			global $wpdb;
			$postTable = $wpdb->posts;
			$q = "SELECT {$postTable}.ID as ID, {$postTable}.post_author as post_author, {$postTable}.post_date as post_date,
									 {$postTable}.post_date_gmt as post_date_gmt, {$postTable}.post_content as post_content, {$postTable}.post_title as post_title,
									 {$postTable}.post_excerpt as post_excerpt, {$postTable}.post_status as post_status, {$postTable}.comment_status as comment_status,
									 {$postTable}.ping_status as ping_status, {$postTable}.post_password as post_password, {$postTable}.post_name as post_name, {$postTable}.to_ping as to_ping,
									 {$postTable}.pinged as pinged, {$postTable}.post_modified as post_modified, {$postTable}.post_modified_gmt as post_modified_gmt,
									 {$postTable}.post_content_filtered as post_content_filtered, {$postTable}.post_parent as post_parent, {$postTable}.guid as guid, {$postTable}.menu_order as menu_order,
									 {$postTable}.post_type as post_type, {$postTable}.post_mime_type as post_mime_type, {$postTable}.comment_count as comment_count
							FROM {$wpdb->posts} ";
			if ( isset( $_GET['s'] ) && $_GET['s'] !== '' ){
					$like = sanitize_text_field($_GET['s']);
					$maybeAmount = $like;
					$maybeAmount = (float)$like;
					$q .= " INNER JOIN {$wpdb->prefix}ulp_order_meta as m1 ON {$postTable}.ID=m1.order_id ";
					$q .= " INNER JOIN {$wpdb->prefix}ulp_order_meta as m2 ON {$postTable}.ID=m2.order_id ";
					$q .= " LEFT JOIN {$wpdb->users} as u ON u.ID=m2.meta_value ";
					$q .= " LEFT JOIN {$wpdb->posts} as p ON p.ID=m2.meta_value ";
					$q .= $wpdb->prepare(" WHERE 1=1
										AND
										(
											( {$postTable}.ID = %s )
											OR
											( m1.meta_key='code' AND m1.meta_value=%s )
											OR
											( m2.meta_key='user_id' AND u.user_login LIKE %s )
											OR
											( m2.meta_key='user_id' AND u.user_email LIKE %s )
											OR
											( m2.meta_key='course_id' AND p.post_title LIKE %s )
											OR
											( m1.meta_key='amount' AND m1.meta_value=%s )
										)

					", $like, $like, $like, $like, $like, $maybeAmount );
			} else {
					$q .= " WHERE 1=1 ";
			}
			$q .= " AND {$postTable}.post_type='ulp_order' AND {$postTable}.post_status
			NOT IN ('trash', 'auto-draft') ";
			$q .= " GROUP BY ID ";

			//$q .= $this->extra_query_params;
			if (!empty($_REQUEST['orderby'])){
					$q .= ' ORDER BY ' . sanitize_text_field( $_REQUEST['orderby'] );
					$q .= !empty( $_REQUEST['order'] ) ? ' ' . sanitize_text_field( $_REQUEST['order'] ) : ' DESC';
			} else {
					$q .= " ORDER BY ID DESC";
			}
			$q .= " LIMIT $per_page";
			$q .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
			$data = $wpdb->get_results($q);
			//dd($q);
			return $data;
	}


}
