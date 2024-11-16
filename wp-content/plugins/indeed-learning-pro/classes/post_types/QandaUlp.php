<?php
namespace Indeed\Ulp\PostType;
if (!defined('ABSPATH')){
   exit();
}

class QandaUlp extends \CustomPostTypeUlp
{
    protected $post_type_slug = 'ulp_qanda';
	  protected $supports = [
                            'title',
                            'editor',
                            'author',
                            'comments',
    ];

    public function __construct(){
  		/// post type
  		$this->labels = array(
  								    'name'               => esc_html__('Q&A', 'ulp'),
  								    'singular_name'      => esc_html__('Q&A', 'ulp'),
  								    'add_new'            => esc_html__('Add new Q&A', 'ulp'),
  								    'add_new_item'       => esc_html__('Add new Q&A', 'ulp'),
  								    'edit_item'          => esc_html__('Edit Q&A', 'ulp'),
  								    'new_item'           => esc_html__('New Q&A', 'ulp'),
  								    'all_items'          => esc_html__('All Q&A', 'ulp'),
  								    'view_item'          => esc_html__('View Q&A', 'ulp'),
  								    'search_items'       => esc_html__('Search Q&A', 'ulp'),
  								    'not_found'          => '',
  								    'not_found_in_trash' => '',
  								    'parent_item_colon'  => '',
  								    'menu_name'          => esc_html__('Q&A', 'ulp'),
  		);
  		/// taxonomy
  		$this->taxonomy_labels = array(
  									'name'              => esc_html__('Q&A Categories', 'ulp'),
  									'singular_name'     => esc_html__('Q&A Category', 'ulp'),
  									'search_items'      => esc_html__('Search Q&A Category', 'ulp'),
  									'all_items'         => esc_html__('Q&A Categories', 'ulp'),
  									'parent_item'       => '',
  									'parent_item_colon' => '',
  									'edit_item'         => esc_html__('Edit', 'ulp'),
  									'update_item'       => esc_html__('Update', 'ulp'),
  									'add_new_item'      => esc_html__('Add new Q&A category', 'ulp'),
  									'new_item_name'     => esc_html__('Add new Q&A category', 'ulp'),
  									'menu_name'         => esc_html__('Q&A Categories', 'ulp'),
  		);
  		$this->taxonomy_slug = 'ulp_qanda_categories';
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
      $data = [
          'post_id' => isset($post->ID) ? $post->ID : 0,
          'courses' => \DbUlp::getAllCourses(),
      ];
      $data['ulp_course_id'] = get_post_meta($data['post_id'], 'ulp_qanda_course_id', true);
      if (empty($data['ulp_course_id']) && isset($_GET['course_id'])){
          $data['ulp_course_id'] = sanitize_text_field($_GET['course_id']);
      }
      $view = new \ViewUlp();
      $view->setTemplate(ULP_PATH . 'views/admin/meta_boxes/qanda_select_course.php');
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
        if (isset($_POST['ulp_course_id'])){
            //$_POST['ulp_course_id'] = sanitize_text_field( $_POST['ulp_course_id'] );
            update_post_meta( $post_id, 'ulp_qanda_course_id', sanitize_text_field($_POST['ulp_course_id']) );
        }
    }

    public function getPostMetas($post_id=0, $keys=[])
    {

    }



}
