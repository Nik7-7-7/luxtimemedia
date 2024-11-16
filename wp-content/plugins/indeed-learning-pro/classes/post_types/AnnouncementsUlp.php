<?php
namespace Indeed\Ulp\PostType;
if (!defined('ABSPATH')){
   exit();
}

class AnnouncementsUlp extends \CustomPostTypeUlp
{
    protected $post_type_slug = 'ulp_announcement';
	  protected $supports = [
                            'title',
                            'editor',
                            'thumbnail',
                            'author',
                            'comments',
    ];

    public function __construct(){
  		/// post type
  		$this->labels = array(
  								    'name'               => esc_html__('Announcements', 'ulp'),
  								    'singular_name'      => esc_html__('Announcement', 'ulp'),
  								    'add_new'            => esc_html__('Add new Announcement', 'ulp'),
  								    'add_new_item'       => esc_html__('Add new Announcement', 'ulp'),
  								    'edit_item'          => esc_html__('Edit Announcement', 'ulp'),
  								    'new_item'           => esc_html__('New Announcement', 'ulp'),
  								    'all_items'          => esc_html__('All Announcements', 'ulp'),
  								    'view_item'          => esc_html__('View Announcement', 'ulp'),
  								    'search_items'       => esc_html__('Search Announcement', 'ulp'),
  								    'not_found'          => '',
  								    'not_found_in_trash' => '',
  								    'parent_item_colon'  => '',
  								    'menu_name'          => esc_html__('Announcements', 'ulp'),
  		);
  		/// taxonomy
  		$this->taxonomy_labels = array(
  									'name'              => esc_html__('Announcements Categories', 'ulp'),
  									'singular_name'     => esc_html__('Announcement Category', 'ulp'),
  									'search_items'      => esc_html__('Search Announcement Category', 'ulp'),
  									'all_items'         => esc_html__('Announcements Categories', 'ulp'),
  									'parent_item'       => '',
  									'parent_item_colon' => '',
  									'edit_item'         => esc_html__('Edit', 'ulp'),
  									'update_item'       => esc_html__('Update', 'ulp'),
  									'add_new_item'      => esc_html__('Add new announcement category', 'ulp'),
  									'new_item_name'     => esc_html__('Add new announcement category', 'ulp'),
  									'menu_name'         => esc_html__('Announcements Categories', 'ulp'),
  		);
  		$this->taxonomy_slug = 'ulp_announcement_categories';
  		///meta boxes
  		$this->metaBoxes[] = array(
  												'slug' => 'courseMetaBox',
  												'title' => esc_html__('Course', 'ulp'),
  												'callback' => [$this, 'selectCourseMetaBox'],
  												'context' => 'normal', // normal || side || advanced
  												'priority' => 'high', /// high || low
  		);
  		$this->run(); /// from parent class
  	}

    public function selectCourseMetaBox($post=null)
    {
      global $current_user;
      $data = [
          'post_id' => isset($post->ID) ? $post->ID : 0
      ];
      if ( current_user_can( 'administrator' ) ){
          $data[ 'courses' ] = \DbUlp::getAllCourses();
      } else {
          $data[ 'courses' ] = \DbUlp::get_courses_for_instructor_as_array( $current_user->ID );
      }

      $data['ulp_course_id'] = get_post_meta($data['post_id'], 'ulp_course_id', true);
      if (empty($data['ulp_course_id']) && isset($_GET['course_id'])){
          $data['ulp_course_id'] = sanitize_text_field($_GET['course_id']);
      }
      $view = new \ViewUlp();
      $view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/announcement_select_course.php');
      $view->setContentData($data);
      echo esc_ulp_content($view->getOutput());
    }

    public function afterSavePost($post_id=0)
    {
        $postType = \DbUlp::getPostTypeById($post_id);
        if ($postType!=$this->post_type_slug){
            return;
        }
        
        $postStatus = \DbUlp::postStatus($post_id);
        if ($postStatus!=null && $postStatus!='auto-draft' && \DbUlp::get_post_meta($post_id, 'ulp_course_id')===null && \DbUlp::getPostTypeById($post_id)=='ulp_announcement' ){
          if(isset($_POST['ulp_course_id'])){
    				do_action('ulp_create_new_announcement', $post_id, sanitize_text_field($_POST['ulp_course_id']) );
    			}

        }
        if (isset($_POST['ulp_course_id'])){
            update_post_meta( $post_id, 'ulp_course_id', sanitize_text_field($_POST['ulp_course_id']) );
        }
    }

    public function getPostMetas($post_id=0, $keys=[])
    {

    }



}
