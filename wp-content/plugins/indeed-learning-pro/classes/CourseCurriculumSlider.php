<?php
namespace Indeed\Ulp;

class CourseCurriculumSlider
{
    private $settings = [];
    private $show     = true;

    public function __construct()
    {
        $this->settings = \DbUlp::getOptionMetaGroup('curriculum_slider');
        if ( !$this->settings['ulp_curriculum_slider_enabled'] ){
            return;
        }
        add_action( 'ulp_course_curriculum_item', [ $this, 'printCurriculum' ], 1, 2 );
    }


    public function printCurriculum( $courseId=0, $currentEntity=0 )
    {
        $this->show = apply_filters( 'ulp_course_curriculum_show', $this->show );
        if ( !$courseId || !$this->show ){
            return ;
        }
        $data = [
            'courseId'          => $courseId,
            'label'             => $this->settings['ulp_curriculum_slider_label'],
            'style'             => $this->settings['ulp_curriculum_slider_custom_css'],
            'currentEntity'     => $currentEntity,
            'courseTitle'       => \DbUlp::getPostTitleByPostId( $courseId ),
            'courseCategories'  => \DbUlp::getCategoriesForPost( $courseId ),
        ];
        $template = ULP_PATH . 'views/templates/course_curriculum_slider.php';
        $view = new \ViewUlp();
        echo esc_ulp_content($view->setTemplate( $template )->setContentData( $data, true )->getOutput());
    }



}
