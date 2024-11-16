<?php
if (!defined('ABSPATH')){
	 exit();
}
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
abstract class Indeed_List_Table extends WP_List_Table{
	protected $post_type = '';
	protected $extra_query_params = '';
	protected $item_per_page = 25;
	public function __construct($args=array()){
		parent::__construct( array(
			'plural' => $this->post_type,
			'screen' => $this->post_type,
		) );
	}
	public function get_columns(){
	  	$columns = array(
		  	'cb' => '<input type="checkbox" />',
		    'title' => 'Title',
		    'date' => 'Date',
	  	);
	  	$columns = apply_filters("manage_" . $this->post_type . "_posts_columns", $columns);
	  	$columns = apply_filters("manage_edit-" . $this->post_type . "_columns",  $columns);
		return $columns;
	}
	function prepare_items() {
		$this->process_bulk_action();
		$this->setExtraQueryParams();
		$per_page = $this->get_items_per_page('items_per_page', $this->item_per_page);
		$current_page = $this->get_pagenum();
	  	$columns = $this->get_columns();
	  	$hidden = $this->get_hidden_columns();
	  	$sortable = $this->get_sortable_columns();
		$total_items = $this->getTotalItems();
	  	$this->_column_headers = array($columns, $hidden, $sortable);
	  	$this->items = $this->table_data($per_page, $current_page);
		$this->set_pagination_args( array('total_items' => $total_items, 'per_page' => $per_page) );
	}
	protected function setExtraQueryParams(){
		$this->extra_query_params = '';
		if (!empty($_GET['s'])){
			$like = sanitize_text_field($_GET['s']);
			$this->extra_query_params .= " AND post_title LIKE '%{$like}%' ";
		}

		$onlyForAuthorId = apply_filters('ulp_admin_filter_show_entities_only_for', 0);
		if ($onlyForAuthorId){
				$this->extra_query_params .= " AND post_author=$onlyForAuthorId ";
		}

		if (!empty($_GET['m'])){
			$y = substr($_GET['m'], 0, 4);
			$m = substr($_GET['m'], 4, 5);
			$this->extra_query_params .= " AND YEAR(post_date)=$y AND MONTH(post_date)=$m ";
		}
	}
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns(){
        return array();
    }
	function column_default($post, $column_name){
		$post = (array)$post;
	  	switch ($column_name){
	    	case 'title':
					return $post->post_title;
					break;
				case 'date':
					return $post->post_date;
					break;
	  	}
	  	do_action("manage_{$this->post_type}_posts_custom_column", $column_name, $post['ID']);
	}
	function get_sortable_columns() {
	  $sortable_columns = array(
	    'title'  => array('post_title',false),
	    'date' => array('post_date', false),
	  );
	  return $sortable_columns;
	}
	function usort_reorder($a, $b) {
	  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'title';
	  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
	  $result = strcmp( $a[$orderby], $b[$orderby] );
	  return ( $order === 'asc' ) ? $result : -$result;
	}
	function column_title($item) {
			$wpnonce = wp_create_nonce('ulp_nonce');
	  	$actions = array(
	            'edit'      => "<a href='" . admin_url('post.php?post=' . $item->ID . '&action=edit') . "'>" .  esc_html__("Edit", 'ulp') . "</a>",
	            'settings'      => "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $this->post_type . '&id=' . $item->ID) . "'>" .  esc_html__("Special Settings", 'ulp') . "</a>",
	            'delete'    => "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=' . $this->post_type . '&action=trash&id=' . $item->ID . '&_wpnonce=' . $wpnonce) . "'>" .  esc_html__("Delete", 'ulp') . "</a>",
	            'view'      => "<a href='" . get_permalink($item->ID) . "' target='_blank' >" .  esc_html__("View", 'ulp') . "</a>",
							'duplicate' => "<a onClick='ulpDuplicatePost({$item->ID});' href='javascript:void(0);' >" .  esc_html__("Duplicate", 'ulp') . "</a>",
	  	);

			$cssClass = '';
			if (strcmp($item->post_status, 'pending')==0){
				 $cssClass = 'ulp-pending-item';
			}
			$title = "<span class='$cssClass'>{$item->post_title}</span>";

			$actions = apply_filters('ulp_filter_custom_post_type_dashboard_action_links', $actions);
	  	return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );
	}
	function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="ID[]" value="%s" />', $item->ID
        );
    }
    /**
     * Get the table data
     *
     * @return Array
     */
    protected function table_data($per_page=5, $page_number=1){
				global $wpdb;
				$q = $wpdb->prepare("SELECT `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`, `ping_status`,
										`post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`,
										`menu_order`, `post_type`, `post_mime_type`, `comment_count` FROM {$wpdb->posts} WHERE post_type=%s  ", $this->post_type ); /// version <1,4 AND post_status NOT IN ('trash', 'auto-draft')
				$q .= $this->extra_query_params;
				if (!empty($_REQUEST['orderby'])){
		    		$q .= ' ORDER BY ' . sanitize_text_field( $_REQUEST['orderby'] );
		    		$q .= !empty( $_REQUEST['order'] ) ? ' ' . sanitize_text_field( $_REQUEST['order'] ) : ' ASC';
		  		}else{
					$q .= ' ORDER by post_date DESC';
				}
		 		$q .= " LIMIT $per_page";
				$q .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
				$data = $wpdb->get_results($q);
				return $data;
				//return indeed_convert_to_array($data);
    }
	protected function getTotalItems(){
		global $wpdb;
		$array = array();
		$q = $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->posts} WHERE post_type=%s ", $this->post_type);/// version <1,4 AND post_status NOT IN ('trash', 'auto-draft')
		$q .= $this->extra_query_params;
		$data = $wpdb->get_var($q);
		return $data;
	}
	public function process_bulk_action(){
			if (isset($_GET['action']) && 'trash'==$_GET['action']){
				$nonce = esc_attr( $_REQUEST['ulp_admin_nonce'] ); // prev was _wpnonce
				if (wp_verify_nonce($nonce, 'ulp_admin_nonce')){// prev was ulp_nonce
					if ( isset( $_GET['ID'] ) && is_array( $_GET['ID'] ) ){
							foreach ( $_GET['ID'] as $targetId ){
									$this->doDeletePost( $targetId );
							}
					} else {
							$this->doDeletePost($_GET['id']);
					}

				}
			}
	  	if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' ) || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )){
	    	$delete_ids = ulp_sanitize_array( $_POST['bulk-delete'] );
		    foreach ($delete_ids as $id){
		    	$this->doDeletePost($id);
	    	}
	  	}
	}
	protected function doDeletePost($id=0){
		global $wpdb;
		$query = $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE ID=%d ;", $id );
		$wpdb->query( $query );
	}
	protected function get_table_classes(){
		$classes = parent::get_table_classes();
		$classes[] = 'ulp-custom-table';
		return $classes;
	}
	public function extra_tablenav($which='top'){
		global $cat;
?>
		<div class="alignleft actions">
<?php
		if ( 'top' === $which && !is_singular() ) {
			$this->months_dropdown($this->post_type);
			if ( is_object_in_taxonomy( $this->post_type, 'category' ) ) {
				$dropdown_options = array(
					'show_option_all' => get_taxonomy( 'category' )->labels->all_items,
					'hide_empty' => 0,
					'hierarchical' => 1,
					'show_count' => 0,
					'orderby' => 'name',
					'selected' => $cat
				);
				echo esc_ulp_content('<label class="screen-reader-text" for="cat">' .  esc_html__( 'Filter by category' ) . '</label>');
				wp_dropdown_categories($dropdown_options);
			}
			do_action( 'restrict_manage_posts', $this->post_type, 'top' );
			submit_button(  esc_html__( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
		}
		if ( current_user_can( get_post_type_object( $this->post_type )->cap->edit_others_posts ) ) { // $this->is_trash &&
			submit_button(  esc_html__( 'Empty Trash' ), 'apply', 'delete_all', false );
		}
?>
		</div>
<?php
		do_action( 'manage_posts_extra_tablenav', $which );
	}
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'] );
		}
		?>

	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce('ulp_admin_nonce');?>" />
	<div class="tablenav <?php echo esc_attr( $which ); ?>">
		<?php if ('top'==$which):?>
				<div class="ulp-search-wrapper"><?php parent::search_box( esc_html__('Search', 'ulp'), $this->post_type);?></div>
		<?php endif;?>
		<?php if ( $this->has_items() ): ?>
		<div class="alignleft actions bulkactions">
			<?php $this->bulk_actions( $which ); ?>
		</div>
		<?php endif;
		$this->extra_tablenav( $which );
		$this->pagination( $which );
?>
		<br class="clear" />
	</div>
<?php
	}
	protected function get_bulk_actions() {
		$actions = array();
		$post_type_obj = get_post_type_object( $this->post_type );
		if ( current_user_can( $post_type_obj->cap->delete_posts ) ) {
			if ( ! EMPTY_TRASH_DAYS ) { // $this->is_trash ||
				$actions['delete'] =  esc_html__( 'Delete Permanently' );
			} else {
				$actions['trash'] =  esc_html__( 'Move to Trash' );
			}
		}
		return $actions;
	}
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$this->_actions = $this->get_bulk_actions();
			/**
			 * Filters the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
			$two = '';
		} else {
			$two = '2';
		}
		if ( empty( $this->_actions ) )
			return;
		echo esc_ulp_content('<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' .  esc_html__( 'Select bulk action' ) . '</label>');
		echo esc_ulp_content('<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n");
		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
			echo esc_ulp_content("\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n");
		}
		echo esc_ulp_content("</select>\n");
		submit_button(  esc_html__( 'Apply' ), 'action', '', false, array( 'id' => "doaction$two" ) );
		echo esc_ulp_content("\n");
	}
}
