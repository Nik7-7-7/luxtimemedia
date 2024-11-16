<?php
namespace Indeed\Ulp\PublicSection;
class SingleCourseMenu
{
    public function __construct(){}

    public function doPrint()
    {
        $tabs = [
              'overview'      => esc_html__('Overview', 'ulp'),
              'curriculum'    => esc_html__('Curriculum', 'ulp'),
              'announcements' => esc_html__('announcements', 'ulp'),
              'qanda'         => esc_html__('Q&A', 'ulp'),
			        'notes'         => esc_html__('Course Notes', 'ulp'),
        ];
        if (!get_option('ulp_announcements_enabled')){
            unset($tabs['announcements']);
        }
        if (!get_option('ulp_qanda_enabled')){
            unset($tabs['qanda']);
        }
		    if (!get_option('lesson_notes_enable')){
            unset($tabs['notes']);
        }
        if (!get_option('ulp_show_curriculum_as_tab')){
            unset($tabs['curriculum']);
        }
        if (count($tabs)==1 && !empty($tabs['overview'])){
            return '';
        }
        $view = new \ViewUlp();
        $view->setTemplate(ULP_PATH . 'views/templates/course/top_menu.php');
        $view->setContentData([
                                  'tabs' => $tabs
        ], true);
        echo esc_ulp_content($view->getOutput());
    }
}
