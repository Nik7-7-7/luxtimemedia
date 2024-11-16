<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Instructor_About')){
   return;
}

class Ulp_Instructor_About
{
    private $attributes = [];

    public function __construct(){}

    public function setAttributes($args=[])
    {
        $this->attributes = $args;
        return $this;
    }

    public function output()
    {
        if (empty($this->attributes['instructor_id'])){
           return '';
        }
        require_once ULP_PATH . 'classes/Entity/UlpInstructor.class.php';
        $UlpInstructor = new UlpInstructor();
        $UlpInstructor->setUid($this->attributes['instructor_id']);
        $data = $UlpInstructor->gettingAllInstructorData();

        $template = ULP_PATH . 'views/templates/instructors/single_instructor_box.php';
        $template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'single_instructor_box.php' );

        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData($data);
        return $view->getOutput();
    }

}
