<?php
namespace Indeed\Ulp\PublicSection;
class SingleCoursePages
{
    public function __construct()
    {

    }

    public function announcements($postId=0)
    {
        $view = new \ViewUlp();
        $view->setTemplate(ULP_PATH . 'views/templates/course/announcements.php');
        $view->setContentData([
            'post_id' => $postId
        ], true);
        echo esc_ulp_content($view->getOutput());
    }

    public function qAndA($postId=0)
    {
        $view = new \ViewUlp();
        $view->setTemplate(ULP_PATH . 'views/templates/course/qanda.php');
        $view->setContentData([
              'post_id' => $postId
        ], true);
        echo esc_ulp_content($view->getOutput());
    }

    public function ulpCourseCurriculum($postId=0)
    {
        $isOn = get_option('ulp_show_curriculum_as_tab');
        if (empty($isOn)){
            return;
        }
        echo do_shortcode("[ulp-course-curriculum course_id=$postId force_print=1]");
    }

}
