<?php
namespace Indeed\Ulp\PublicSection;
if (!defined('ABSPATH')){
   exit();
}
class StudentsAlsoBought
{
    private $attributes = [];
    private $limit = 0;
    private $minimumLimit = 0;

    public function __construct($attr=[])
    {
        $this->attributes = $attr;
        $this->limit = get_option('ulp_student_also_bought_limit');
        $this->minimumLimit = get_option('ulp_student_also_bought_minimum_limit');
    }

    public function output()
    {
        $items = \DbUlp::studentsAlsoBought($this->attributes['course_id'], $this->limit, $this->minimumLimit);
        if (empty($items)){
           return;
        }

        $template = ULP_PATH . 'views/templates/course/students_also_bought.php';
      	$template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'students_also_bought.php' );

        $view = new \ViewUlp();
        $view->setTemplate($template);
        $view->setContentData([
                                'items' => $items
        ], true);
        return $view->getOutput();

    }

}
