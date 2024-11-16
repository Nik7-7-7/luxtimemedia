<?php
if (!defined('ABSPATH')){
	 exit();
}
if ( !class_exists('Indeed_List_Table')){
	 require_once ULP_PATH . 'classes/Abstracts/Indeed_List_Table.class.php';
}
if (class_exists('ListCourses')){
	 return;
}
class ListCourses extends Indeed_List_Table{
	protected $post_type = 'ulp_course';
	protected $label = 'Course';
	protected $label_plural = 'Courses';
	private $_currency = '';
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
		    'title' => esc_html__('Title', 'ulp'),
		    'content' => esc_html__('Curriculum', 'ulp'),
		    'students' => esc_html__('Students', 'ulp'),
		    'price' => esc_html__('Price', 'ulp'),
				'certificates' => esc_html__('Certificates', 'ulp'),
		    //'category' => esc_html__('Category', 'ulp'),
				'announcements' => esc_html__('Announcements', 'ulp'),
				'qanda' => esc_html__('Q&A', 'ulp'),
				'instructors' => esc_html__('Instructors', 'ulp'),
				'reviews' => esc_html__('Reviews', 'ulp'),
				'points' => esc_html__('Rewards', 'ulp'),
		    'author' => esc_html__('Author', 'ulp'),
		    'date' => esc_html__('Date', 'ulp'),
	  	);
			if (!get_option('ulp_course_reviews_enabled')){
					unset($columns['reviews']);
			}
			if (!get_option('ulp_certificates_enable')){
					unset($columns['certificates']);
			}
			if (!get_option('ulp_multiple_instructors_enable')){
					unset($columns['instructors']);
			}
			if (!get_option('ulp_announcements_enabled')){
					unset($columns['announcements']);
			}

	  	$columns = apply_filters("manage_" . $this->post_type . "_posts_columns", $columns);
	  	$columns = apply_filters("manage_edit-" . $this->post_type . "_columns",  $columns);
		return $columns;
	}
	function column_default($post, $column_name){
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
				case 'content':
					$object = new UlpCourse($post['ID'], FALSE);
					$total_modules = $object->TotalModules();
					$total_quizes = $object->TotalQuizes();
					$total_lessons = $object->TotalLessons();
					$string = '';
					if ($total_lessons){
								$string .= '<a href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_lesson&list_by_course_id=' . $post['ID']) . '">'
									 .'<div class="ulp-admin-item-box ulp-admin-quiz-box">'
									  . $total_lessons
									  . '<span>'. esc_html__(' Lessons', 'ulp') .'</span>'
									  .'</div>'
									.'</a>'
									;
					}
					if ($total_quizes){
						$string .= '<a href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_quiz&list_by_course_id=' . $post['ID']).'">'
									 .'<div class="ulp-admin-item-box ulp-admin-quiz-box">'
									  . $total_quizes
									  . '<span>'. esc_html__(' Quizzes', 'ulp') .'</span>'
									  .'</div>'
									.'</a>'
									;
					}
					return $string;
					break;
				case 'students':
					$object = new UlpCourse($post['ID'], FALSE);
					$count_students = $object->TotalStudents();
					if ($count_students){
							return '<a class="ulp-count-link ulp-enrolled-students-table-row" href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=students&list_students_by_course_id=' . $post['ID']) . '">'. $count_students .'</a>'.  esc_html__('Enrolled ', 'ulp') ;
					}
					return esc_html__('No students yet', 'ulp');
					break;
				case 'price':
					$object = new UlpCourse($post['ID'], true);
					$price = $object->Price(true);
					if ($object->IsFree() == 0){
							return '<div class="ulp-courses-prices-table-row">' . esc_html__('Free', 'ulp') . '</div>';
					} else {
							if (empty($this->_currency)){
									$this->_currency = ulp_currency();
							}
							return '<div class="ulp-courses-prices-table-row">' . $price . '</div>' . $this->_currency;
					}
					/// ulp-courses-prices-table-row
					break;
				case 'category':
					break;
				case 'certificates':
					$certificate = DbUlp::getCertificateForCourse($post['ID']);
					if ($certificate){
							$certificate_label = DbUlp::getPostTitleByPostId($certificate);
							return '<div class="ulp-course-certificate"><a href="' . admin_url('post.php?post=' . $post['ID'] . '&action=edit') . '">' . $certificate_label . '</a></div>';
					}
					return '-';
					break;
				case 'instructors':
						$instructors = get_post_meta($post['ID'], 'ulp_additional_instructors', TRUE);
						if ($instructors)	{
								$instructors_arr = explode(',', $instructors);
								echo esc_ulp_content('<ul>');
								foreach ($instructors_arr as $uid){
										$name = DbUlp::getUsernameByUID($uid);
										$avatar = DbUlp::getAuthorImage($uid);
										if ( $name !== null && $name !== false && $name !== '' ){
												 $name = substr($name, 0, 22);
										}
										echo esc_ulp_content('<li>'.'<img src="'.$avatar.'"  />' . '<a href="' . admin_url('user-edit.php?user_id=' . $uid) . '">' . $name . '</a>' . '</li>');
								}
								echo esc_ulp_content('</ul>');
						}
					break;
				case 'reviews':
					require_once ULP_PATH . 'classes/Db/Db_Ulp_Course_Reviews.class.php';
					$Db_Ulp_Course_Reviews = new Db_Ulp_Course_Reviews();
					$count = $Db_Ulp_Course_Reviews->countAllByCourse($post['ID']);
					if ($count){
							$average = $Db_Ulp_Course_Reviews->getRatingAverageForCourse($post['ID']);
							echo esc_ulp_content('<div>' . ulp_generate_stars($average) . '</div>');
							echo esc_ulp_content('<a class="ulp-review-link" href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_course_review&list_by_course_id=' . $post['ID']) . '">' . $count . esc_html__(' reviews', 'ulp') . '</a>');
					} else {
					}
					break;
				case 'points':
					$object = new UlpCourse($post['ID']);
					$points  = $object->RewardPoints();
					if ($points){
						return $points . esc_html__(' points', 'ulp');
					}
					return '-';
					break;
				case 'announcements':
					$object = new \Indeed\Ulp\Db\Announcements();
					$count = $object->countAllByCourse($post['ID']);
					return  '<a href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_announcement&course_id=' . $post['ID']) . '">' .$count . esc_html__(' items', 'ulp').'<div class="ulp-small-action-button">' . esc_html__(' Add new', 'ulp') . '</div></a>';
					break;
				case 'qanda':
					$object = new \Indeed\Ulp\Db\QandA();
					$count = $object->countAllByCourse($post['ID']);
					return  '<a href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=ulp_qanda&course_id=' . $post['ID']) . '">' .$count . esc_html__(' questions', 'ulp') . '<div class="ulp-small-action-button ulp-light-red-button">' . esc_html__(' Respond', 'ulp') . '</div></a>';
					break;
	  	}
	  	do_action("manage_{$this->post_type}_posts_custom_column", $column_name, $post['ID']);
	}
	function column_title($item) {
			$string = '';
			$object = new UlpCourse($item->ID);
			$cats  = $object->Categories(TRUE);
			if ($cats != '-'){
				$cats = ' - <span class="ulp-courses-list-category">'.$cats.'</span>';
			}else{
				$cats ='';
			}
			$is_featured = get_post_meta($item->ID, 'ulp_course_featured', true);
			if ($is_featured){
					$string .= ' <div class="ulp-admin-featured-label" title="' . esc_html__('Featured Course', 'ulp') . '">' . '<i class="fa-ulp fa-ulp_check-ulp"></i>' . '</div>';
			}
			$cssClass = '';
			if (strcmp($item->post_status, 'pending')==0){
				 $cssClass = 'ulp-pending-item';
			}
			$string .= "<span class='$cssClass'>{$item->post_title}{$cats}</span>";
			$wpnonce = wp_create_nonce('ulp_nonce');
	  	$actions = array(
	            'edit'      => "<a href='" . admin_url('post.php?post=' . $item->ID . '&action=edit') . "'>" . esc_html__("Edit", 'ulp') . "</a>",
	            'settings'      => "<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=post_special_settings&post_type=' . $this->post_type . '&id=' . $item->ID) . "'>" . esc_html__("Special Settings", 'ulp') . "</a>",
	            'delete'    => "<span class='js-ulp-do-delete-post ulp-delete-link ulp-white-label' data-id='{$item->ID}'>". esc_html__("Delete", 'ulp') . "</span>",//"<a href='" . admin_url('admin.php?page=ultimate_learning_pro&tab=' . $this->post_type . '&action=trash&id=' . $item->ID . '&_wpnonce=' . $wpnonce) . "'>" . esc_html__("Delete", 'ulp') . "</a>",
	            'view'      => "<a href='" . \Ulp_Permalinks::getForCourse($item->ID) . "' target='_blank' >" . esc_html__("View", 'ulp') . "</a>",//get_permalink($item->ID)
							'duplicate' => "<a onClick='ulpDuplicatePost({$item->ID});' href='javascript:void(0);' >" . esc_html__("Clone", 'ulp') . "</a>",
	  	);
			$actions = apply_filters('ulp_filter_custom_post_type_dashboard_action_links', $actions);
	  	return sprintf('%1$s %2$s', $string, $this->row_actions($actions) );
	}
 protected function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );
		$i = 0;
		if ( !$action_count ){
			return '';
		}
		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
		++$i;
		( $i == $action_count ) ? $sep = '' : $sep = '';
		$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';
		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__( 'Show more details' ) . '</span></button>';
		return $out;
	}
}
