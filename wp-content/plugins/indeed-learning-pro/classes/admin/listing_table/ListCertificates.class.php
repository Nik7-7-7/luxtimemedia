<?php
if (!defined('ABSPATH')){
	 exit();
}
if ( !class_exists('Indeed_List_Table')){
	 require_once ULP_PATH . 'classes/Abstracts/Indeed_List_Table.class.php';
}
if (class_exists('ListCertificates')){
	 return;
}
class ListCertificates extends Indeed_List_Table{
	/**
	 * @var string
	 */
	protected $post_type = 'ulp_certificate';
	/**
	 * @var string
	 */
	protected $label = 'Certificate';
	/**
	 * @var string
	 */
	protected $label_plural = 'Certificates';
	/**
	 * @param none
	 * @return string
	 */
	public function finalOutput(){
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
		    'title' =>  esc_html__('Title', 'ulp'),
				'courses' =>  esc_html__('Courses', 'ulp'),
		    'author' =>  esc_html__('Author', 'ulp'),
		    'date' =>  esc_html__('Date', 'ulp'),
	  	);
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
		$post = (array)$post;
	  	switch ($column_name){
	    	case 'title':
					return $post['post_title'];
					break;
				case 'date':
					return ulp_print_date_like_wp($post['post_date'], FALSE);
					break;
				case 'author':
					$author = DbUlp::getUsernameByUID($post['post_author']);
					$avatar = DbUlp::getAuthorImage($post['post_author']);
					return '<a href="' . admin_url('user-edit.php?user_id=' . $post['post_author']) . '" target="_blank">' . $author . '</a>'
									. '<img src="' . $avatar . '"  />';
					break;
				case 'courses':
					$courses = DbUlp::get_courses_for_certificate($post['ID']);
					if ($courses){
							foreach ($courses as $post_id){
									echo esc_ulp_content('<div class="ulp-property"><a target="_blank" href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '">' . DbUlp::getPostTitleByPostId($post_id) . '</a></div>');
							}
					}
					break;
	  	}
	  	do_action("manage_{$this->post_type}_posts_custom_column", $column_name, $post['ID']);
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
	public function column_title($item) {
			$wpnonce = wp_create_nonce('ulp_nonce');
	  	$actions = array(
	            'edit'      => "<a href='" . admin_url('post.php?post=' . $item->ID . '&action=edit') . "'>" .  esc_html__("Edit", 'ulp') . "</a>",
	            'settings'      => "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $this->post_type . '&id=' . $item->ID) . "'>" .  esc_html__("Special Settings", 'ulp') . "</a>",
	            'delete'    => "<span class='js-ulp-do-delete-post ulp-delete-link' data-id='{$item->ID}'>".  esc_html__("Delete", 'ulp') . "</span>",///"<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=' . $this->post_type . '&action=trash&id=' . $item->ID . '&_wpnonce=' . $wpnonce) . "'>" .  esc_html__("Delete", 'ulp') . "</a>",
	            'view'      => "<span onclick='ulpOpenCertificateFromAdmin(".$item->ID.");' class='ulp-like-link-span ulp-pointer' >" .  esc_html__("View", 'ulp') . "</span>",
							'duplicate' => "<a onClick='ulpDuplicatePost({$item->ID});' href='javascript:void(0);' >" .  esc_html__("Duplicate", 'ulp') . "</a>",
	  	);

			$actions = apply_filters('ulp_filter_custom_post_type_dashboard_action_links', $actions);
	  	return sprintf('%1$s %2$s', $item->post_title, $this->row_actions($actions) );
	}
}
