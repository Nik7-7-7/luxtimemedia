<?php
if (!defined('ABSPATH')){
	 exit();
}
if ( !class_exists('Indeed_List_Table'))
	require_once ULP_PATH . 'classes/Abstracts/Indeed_List_Table.class.php';
if (class_exists('ListLessons')){
	 return;
}
class ListLessons extends Indeed_List_Table{
	protected $post_type = 'ulp_lesson';
	protected $label = 'Lessons';
	protected $label_plural = 'Lessons';
	/**
	 * @param none
	 * @return string
	 */
	public function finalOutput(){
		parent::prepare_items();
		parent::display();
	}
	public function get_columns(){
	  	$columns = array(
		  	'cb' => '<input type="checkbox" />',
		    'title' =>  esc_html__('Title', 'ulp'),
		    'course' =>  esc_html__('Course', 'ulp'),
		    'duration' =>  esc_html__('Duration', 'ulp'),
			'points' =>  esc_html__('Rewards', 'ulp'),
		    'author' =>  esc_html__('Author', 'ulp'),
		    'date' =>  esc_html__('Date', 'ulp'),
	  	);
		$columns = apply_filters("manage_" . $this->post_type . "_posts_columns", $columns);
	  	$columns = apply_filters("manage_edit-" . $this->post_type . "_columns",  $columns);
		return $columns;
	}
	public function column_default($post, $column_name){
		$post = (array)$post;
	  	switch ($column_name){
		    case 'title':
					return $post['post_title'];
					break;
			case 'date':
				return ulp_print_date_like_wp($post['post_date'], FALSE);
				break;
			case 'course':
				$items = DbUlp::getCoursesForLessonId($post['ID']);
				$str = '';
				if ($items){
					foreach ($items as $item){
						$course_label = DbUlp::getPostTitleByPostId($item['course_id']);
						if ($course_label)
								$str .= '<div class="ulp-property"><a target="_blank" href="' . admin_url('post.php?post=' . $item['course_id'] . '&action=edit') . '">' . $course_label . '</a></div>';
					}
				} else {
						$str .= '-';
				}
				return $str;
				break;
			case 'author':
				$author = DbUlp::getUsernameByUID($post['post_author']);
				$avatar = DbUlp::getAuthorImage($post['post_author']);
				return '<a href="' . admin_url('user-edit.php?user_id=' . $post['post_author']) . '" target="_blank">' . $author . '</a>'
									. '<img src="' . $avatar . '"  />';
				break;
			case 'duration':
				$object = new UlpLesson($post['ID'], TRUE);
				$types = ulp_get_time_types();
				$duration = $object->Duration();
				if ($duration && isset($types[$object->DurationType()])){
					return $duration . ' ' . $types[$object->DurationType()];
				} else {
					return  esc_html__('Not set', 'ulp');
				}
				break;
			case 'points':
				$object = new UlpLesson($post['ID'], TRUE);
				$points = $object->RewardPoints();
				if($points > 0)
					return $points. esc_html__(' points', 'ulp');
				else
					return '-';

				break;
	  	}
	  	do_action("manage_{$this->post_type}_posts_custom_column", $column_name, $post->ID);
	}
	public function get_sortable_columns() {
	  $sortable_columns = array(
	    'title'  => array('post_title',false),
	    'date' => array('post_date', false),
	  );
	  return $sortable_columns;
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

		$onlyForAuthorId = apply_filters('ulp_admin_filter_show_entities_only_for', 0);
		if ($onlyForAuthorId){
				$this->extra_query_params .= " AND {$alias}post_author=$onlyForAuthorId ";
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
				if (isset($_GET['list_by_course_id'])){
					$q = "SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,a.comment_status,a.ping_status,
												a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,a.post_content_filtered,a.post_parent,a.guid,a.menu_order,
												a.post_type,a.post_mime_type,a.comment_count
									FROM {$wpdb->posts} a
									INNER JOIN {$wpdb->prefix}ulp_course_modules_items b
									ON a.ID=b.item_id
									WHERE
									a.post_type='{$this->post_type}'
									AND
									a.post_status NOT IN ('trash', 'auto-draft')
									AND
									b.course_id={$_GET['list_by_course_id']}
					";
						$q .= $this->extra_query_params;

						if (!empty($_REQUEST['orderby'])){
								$q .= ' ORDER BY a.' . sanitize_text_field( $_REQUEST['orderby'] );
								$q .= !empty( $_REQUEST['order'] ) ? ' ' . sanitize_text_field( $_REQUEST['order'] ) : ' ASC';
						}else{
							$q .= ' ORDER by post_date DESC';
						}
				} else {
						$q = "SELECT `ID`, `post_author`, `post_date`, `post_date_gmt`, `post_content`, `post_title`, `post_excerpt`, `post_status`, `comment_status`,
						`ping_status`, `post_password`, `post_name`, `to_ping`, `pinged`, `post_modified`, `post_modified_gmt`, `post_content_filtered`, `post_parent`, `guid`,
						`menu_order`, `post_type`, `post_mime_type`, `comment_count`
						 					FROM {$wpdb->posts} WHERE post_type='{$this->post_type}' AND post_status NOT IN ('trash', 'auto-draft') ";
						$q .= $this->extra_query_params;
						if (!empty($_REQUEST['orderby'])){
				    		$q .= ' ORDER BY ' . sanitize_text_field( $_REQUEST['orderby'] );
				    		$q .= !empty( $_REQUEST['order'] ) ? ' ' . sanitize_text_field( $_REQUEST['order'] ) : ' ASC';
				  	}else{
							$q .= ' ORDER by post_date DESC';
						}
				}
		 		$q .= " LIMIT $per_page";
				$q .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
				$data = $wpdb->get_results($q);
				return $data;//indeed_convert_to_array($data);
    }
		function column_title($item) {
				$preview = get_post_meta($item->ID, 'ulp_lesson_preview', true);					$extra = '';
				$cssClass = '';
				$string = '';
				$video = get_post_meta( $item->ID, 'ulp_lesson_is_video', true );
				if ( $video ){
						$string .= '<i class="fa-ulp fa-video-ulp"></i> ';
				}
				if (strcmp($item->post_status, 'pending')==0){
					 $cssClass = 'ulp-pending-item';
				}
				$string .= "<span class='$cssClass'>{$item->post_title}</span>";

				if ($preview){
						$string .= ' <div class="ulp-admin-preview-label">' .  esc_html__('Preview', 'ulp') . '</div>';
				}

				$wpnonce = wp_create_nonce('ulp_nonce');
		  	$actions = array(
		            'edit'      => "<a href='" . admin_url('post.php?post=' . $item->ID . '&action=edit') . "'>" .  esc_html__("Edit", 'ulp') . "</a>",
		            'settings'      => "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $this->post_type . '&id=' . $item->ID) . "'>" .  esc_html__("Special Settings", 'ulp') . "</a>",
		            'delete'    => "<span class='js-ulp-do-delete-post ulp-delete-link' data-id='{$item->ID}'>".  esc_html__("Delete", 'ulp') . "</span>",///"<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=' . $this->post_type . '&action=trash&id=' . $item->ID . '&_wpnonce=' . $wpnonce) . "'>" .  esc_html__("Delete", 'ulp') . "</a>",
		            'view'      => "<a href='" . get_permalink($item->ID) . "' target='_blank' >" .  esc_html__("View", 'ulp') . "</a>",
								'duplicate' => "<a onClick='ulpDuplicatePost({$item->ID});' href='javascript:void(0);' >" .  esc_html__("Duplicate", 'ulp') . "</a>",
		  	);

				$actions = apply_filters('ulp_filter_custom_post_type_dashboard_action_links', $actions);
		  	return sprintf('%1$s %2$s', $string, $this->row_actions($actions) );
		}
}
