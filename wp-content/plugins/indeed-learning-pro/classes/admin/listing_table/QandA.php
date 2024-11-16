<?php
namespace Indeed\Ulp\Admin\Listing;

if (!defined('ABSPATH')){
   exit();
}
if ( !class_exists('Indeed_List_Table')){
   require_once ULP_PATH . 'classes/Abstracts/Indeed_List_Table.class.php';
}

class QandA extends \Indeed_List_Table
{
  protected $post_type = 'ulp_qanda';
	/**
	 * @var string
	 */
	protected $label = 'Q&A';
	/**
	 * @var string
	 */
	protected $label_plural = 'Q&A';

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
		    'title' => esc_html__('Q&A Title', 'ulp'),
			  'target_course' => esc_html__('Target course', 'ulp'),
			  'no_comments' => esc_html__('Comments', 'ulp'),
		    'author' => esc_html__('Author', 'ulp'),
			  'post_status' => esc_html__('Status', 'ulp'),
		    'date' => esc_html__('Date', 'ulp'),
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
					$cssClass = '';
					if ($post['post_status']=='pending'){
							$cssClass = 'ulp-pending-review';
					}
					return "<span class='$cssClass'>" . $post['post_title'] . "</span>";
					break;
				case 'date':
					return ulp_print_date_like_wp($post['post_date']);
					break;
				case 'author':
					$author = \DbUlp::getUsernameByUID($post['post_author']);
					$avatar = \DbUlp::getAuthorImage($post['post_author']);
					return '<a href="' . admin_url('user-edit.php?user_id=' . $post['post_author']) . '" target="_blank">' . $author . '</a>'
									. '<img src="' . $avatar . '"  />';
					break;
				case 'post_status':
					$str = '';
					if (strcmp($post['post_status'], 'pending')==0){
							$str .= '<div class="ulp-post-status-list ulp-pending" >' . ucfirst($post['post_status']) . '</div>';
					} else {
							$label = $post['post_status']=='publish' ? 'Published' : ucfirst($post['post_status']);
							$str .= '<div class="ulp-post-status-list">' . $label . '</div>';
					}
					return $str;
					break;
				case 'no_comments':
					$str = '';
					$str .= '<div class="ulp-post-no-comments" >' . \DbUlp::countPostComments($post['ID'], false) .' '. esc_html__('comments', 'ulp'). '</div>';
					$str .= '<div class="ulp-post-no-comments-pending" > (' . \DbUlp::countPostComments($post['ID'], 0) .' '. esc_html__('pending', 'ulp'). ')</div>';
					return $str;
					break;
				case 'target_course':
					$course = get_post_meta($post['ID'], 'ulp_qanda_course_id', true);
					if ($course){
							return '<div class="ulp-property"><a target="_blank" href="' . admin_url('post.php?post=' . $course . '&action=edit') . '">' . \DbUlp::getPostTitleByPostId($course) . '</a></div>';
					}
					return esc_html__('Unknown', 'ulp');
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
		            'edit'      => "<a href='" . admin_url('post.php?post=' . $item->ID . '&action=edit') . "'>" . esc_html__("Edit", 'ulp') . "</a>",
		            'delete'    => "<span class='js-ulp-do-delete-post ulp-delete-link' data-id='{$item->ID}'>". esc_html__("Delete", 'ulp') . "</span>",///"<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_qanda&action=trash&id=' . $item->ID . '&_wpnonce=' . $wpnonce) . "'>" . esc_html__("Delete", 'ulp') . "</a>",
		            'view'      => "<a href='" . get_permalink($item->ID) . "' target='_blank' >" . esc_html__("View", 'ulp') . "</a>",
								'duplicate' => "<a onClick='ulpDuplicatePost({$item->ID});' href='javascript:void(0);' >" . esc_html__("Duplicate", 'ulp') . "</a>",
		  	);

				if (strcmp($item->post_status, 'pending')==0){
						$actions ['post_status_action'] = '<span onclick="ulpChangePostStatus('.$item->ID.', \'publish\', \'\');" class="ulp-pointer ulp-like-link-span ulp-change-status-link">' . esc_html__('Publish', 'ulp') . '</span>';
				} else {
						$actions ['post_status_action'] = '<span onclick="ulpChangePostStatus('.$item->ID.', \'pending\', \'\');" class="ulp-pointer ulp-like-link-span ulp-change-status-link">' . esc_html__('Pending', 'ulp') . '</span>';
				}

				$cssClass = '';
				if (strcmp($item->post_status, 'pending')==0){
           $cssClass = 'ulp-pending-item';
        }
				$title = "<span class='$cssClass'>{$item->post_title}</span>";

				$actions = apply_filters('ulp_filter_custom_post_type_dashboard_action_links', $actions);
		  	return sprintf('%1$s %2$s', $title, $this->row_actions($actions) );
		}

		protected function setExtraQueryParams(){
				if (isset($_GET['list_by_course_id'])){
						$alias = 'a.';
				} else {
						$alias = '';
				}
				$this->extra_query_params = '';
				if (!empty($_GET['s'])){
					$like = sanitize_text_field($_GET['s']);
					$this->extra_query_params .= " AND {$alias}post_title LIKE '%{$like}%' ";
				}
				if (!empty($_GET['m'])){
					$y = substr($_GET['m'], 0, 4);
					$m = substr($_GET['m'], 4, 5);
					$this->extra_query_params .= " AND YEAR({$alias}post_date)=$y AND MONTH({$alias}post_date)=$m ";
				}
		}

		/**
		 * Get the table data
		 *
		 * @return Array
		 */
		protected function table_data($per_page=5, $page_number=1){
				global $wpdb;
				if (isset($_GET['course_id'])){
						$q = "SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,a.comment_status,a.ping_status,a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,a.post_content_filtered,a.post_parent,a.guid,a.menu_order,a.post_type,a.post_mime_type,a.comment_count
                    FROM {$wpdb->posts} a
										INNER JOIN {$wpdb->postmeta} b
										ON a.ID=b.post_id
										WHERE
										a.post_type='{$this->post_type}'
										AND
										b.meta_key='ulp_qanda_course_id'
										AND
										b.meta_value={$_GET['course_id']}
						";
						$q .= $this->extra_query_params;
						if (!empty($_REQUEST['orderby'])){
								$q .= ' ORDER BY a.' . sanitize_text_field( $_REQUEST['orderby'] );
								$q .= !empty( $_REQUEST['order'] ) ? ' ' . sanitize_text_field( $_REQUEST['order'] ) : ' ASC';
						} else {
								$q .= ' ORDER BY a.post_date DESC ';
						}
				} else {
						$q = "SELECT `ID`,`post_author`,`post_date`,`post_date_gmt`,`post_content`,`post_title`,`post_excerpt`,`post_status`,`comment_status`,`ping_status`,`post_password`,`post_name`,`to_ping`,`pinged`,`post_modified`,`post_modified_gmt`,`post_content_filtered`,`post_parent`,`guid`,`menu_order`,`post_type`,`post_mime_type`,`comment_count` FROM {$wpdb->posts} WHERE post_type='{$this->post_type}' ";
						$q .= $this->extra_query_params;
						if (!empty($_REQUEST['orderby'])){
								$q .= ' ORDER BY ' . sanitize_text_field( $_REQUEST['orderby'] );
								$q .= !empty( $_REQUEST['order'] ) ? ' ' . sanitize_text_field( $_REQUEST['order'] ) : ' ASC';
						} else {
								$q .= ' ORDER BY post_date DESC ';
						}
				}
				$q .= " LIMIT $per_page";
				$q .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
				$data = $wpdb->get_results($q);
				return $data;
		}

		protected function get_bulk_actions() {
				$actions = [
						'trash' => esc_html__( 'Move to Trash', 'ulp'),
						'bulk_publish' => esc_html__('Bulk publish', 'ulp'),
				];
				return $actions;
		}

    protected function getTotalItems(){
      global $wpdb;
      $array = array();
      $q = "SELECT COUNT(a.ID) FROM {$wpdb->posts} a
              INNER JOIN {$wpdb->postmeta} b
              ON a.ID=b.post_id
              WHERE
              a.post_type='{$this->post_type}'
              AND
              b.meta_key='ulp_qanda_course_id'
              AND
              b.meta_value=%d";/// version <1,4 AND post_status NOT IN ('trash', 'auto-draft')
      $q = $wpdb->prepare($q, $_GET['course_id']);
      $q .= $this->extra_query_params;
      $data = $wpdb->get_var($q);
      return $data;
    }
}
