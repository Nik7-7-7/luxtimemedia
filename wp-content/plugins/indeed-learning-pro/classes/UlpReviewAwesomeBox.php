<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('UlpReviewAwesomeBox')){
   return;
}
class UlpReviewAwesomeBox
{
    private $_attributes = [];

    public function __construct($args=[])
    {
        $this->_attributes = $args;
    }

    public function output()
    {
        if (empty($this->_attributes['course_id'])){
            return '';
        }
        require_once ULP_PATH . 'classes/Entity/UlpCourse.class.php';
        $UlpCourse = new UlpCourse($this->_attributes['course_id'], true);
        $ratingPercentages = $UlpCourse->RatingPercentages();
        $averageRating = $UlpCourse->Rating();

        $template = ULP_PATH . 'views/templates/reviews_awesome_box.php';
        $template = apply_filters( 'ulp_filter_shortcodes_template', $template, 'reviews_awesome_box.php' );

        $view = new ViewUlp();
        $view->setTemplate($template);
        $view->setContentData([
            'course_id' => $this->_attributes['course_id'],
            'averageRating' => $averageRating,
            'ratingPercentages' => $ratingPercentages,
        ]);
        return $view->getOutput();
    }

}
