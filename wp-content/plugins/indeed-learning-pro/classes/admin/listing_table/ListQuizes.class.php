<?php
if (!defined('ABSPATH')){
	 exit();
}
if ( !class_exists('Indeed_List_Table'))
	require_once ULP_PATH . 'classes/Abstracts/Indeed_List_Table.class.php';
if (class_exists('ListQuizes')){
	 return;
}
class ListQuizes extends Indeed_List_Table{
	protected $post_type = 'ulp_quiz';
	protected $label = 'Quizes';
	protected $label_plural = 'Quizes';
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
		    'questions' =>  esc_html__('Questions', 'ulp'),
				'grade_condition' =>  esc_html__('Grade condition', 'ulp'),
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
				case 'author':
					$author = DbUlp::getUsernameByUID($post['post_author']);
					$avatar = DbUlp::getAuthorImage($post['post_author']);
					return '<a href="' . admin_url('user-edit.php?user_id=' . $post['post_author']) . '" target="_blank">' . $author . '</a>' .
										'<img src="' . $avatar . '"  />';
					break;
				case 'questions':
					require_once ULP_PATH . 'classes/Db/DbQuizQuestions.class.php';
					$DbQuizQuestions = new DbQuizQuestions();
					$quiz_questions = $DbQuizQuestions->getQuizQuestions($post['ID']);
					if ($quiz_questions){
						return '<a href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_question&list_by_quiz_id=' . $post['ID']) . '">' . count($quiz_questions) .  esc_html__(' items', 'ulp') . '</a>';
					}
					return '-';
					break;
				case 'duration':
					$data = get_post_meta($post['ID'], 'quiz_time', TRUE);
					if ($data){
						return $data . esc_html__(' minutes', 'ulp');
					}
					return '-';
					break;
				case 'points':
					$data = get_post_meta($post['ID'], 'ulp_post_reward_points', TRUE);
					if ($data){
						return $data . esc_html__(' points', 'ulp');
					}
					return '-';
					break;

				case 'course':
					$items = DbUlp::getCoursesForQuizId($post['ID']);
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
				case 'grade_condition':
					$grade_type = get_post_meta($post['ID'], 'ulp_quiz_grade_type', TRUE);
					$grade_min_value = get_post_meta($post['ID'], 'ulp_quiz_grade_value', TRUE);
					if (empty($grade_min_value)){
							return '-';
					}
					if ($grade_type=='percentage'){
							echo esc_html($grade_min_value . '%');
					} else {
							echo esc_html($grade_min_value) .' '.  esc_html__('points', 'ulp');
					}
					break;
	  	}
	  	do_action("manage_{$this->post_type}_posts_custom_column", $column_name, $post['ID']);
	}

	public function get_sortable_columns() {
	  $sortable_columns = array(
	    'title'  => array('post_title',false),
	    'date' => array('post_date', false),
	  );
	  return $sortable_columns;
	}

	function column_title($item) {
			$wpnonce = wp_create_nonce('ulp_nonce');
			$ulp_admin_nonce = wp_create_nonce( 'ulp_admin_nonce');
			$actions = array(
							'edit'      => "<a href='" . admin_url('post.php?post=' . $item->ID . '&action=edit') . "'>" .  esc_html__("Edit", 'ulp') . "</a>",
							'settings'      => "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $this->post_type . '&id=' . $item->ID) . "'>" .  esc_html__("Special Settings", 'ulp') . "</a>",
							'delete'    => "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=' . $this->post_type . '&action=trash&id=' . $item->ID . '&ulp_admin_nonce=' . $ulp_admin_nonce) . "'>" .  esc_html__("Delete", 'ulp') . "</a>",
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
						$q = "SELECT a.ID,a.post_author,a.post_date,a.post_date_gmt,a.post_content,a.post_title,a.post_excerpt,a.post_status,a.comment_status,a.ping_status,a.post_password,a.post_name,a.to_ping,a.pinged,a.post_modified,a.post_modified_gmt,a.post_content_filtered,a.post_parent,a.guid,a.menu_order,a.post_type,a.post_mime_type,a.comment_count
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
						$q = "SELECT `ID`,`post_author`,`post_date`,`post_date_gmt`,`post_content`,`post_title`,`post_excerpt`,`post_status`,`comment_status`,`ping_status`,`post_password`,`post_name`,`to_ping`,`pinged`,`post_modified`,`post_modified_gmt`,`post_content_filtered`,`post_parent`,`guid`,`menu_order`,`post_type`,`post_mime_type`,`comment_count`
						 FROM {$wpdb->posts} WHERE post_type='{$this->post_type}' AND post_status NOT IN ('trash', 'auto-draft')";
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
				return $data;
    }
}
