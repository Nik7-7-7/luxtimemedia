<?php
namespace Indeed\Ulp;
if (!defined('ABSPATH')){
   exit();
}
class Filters
{
    public function __construct()
    {
        add_filter('ulp_filter_price_html', [$this, 'courseSpecialPrice'], 99, 2);
        add_filter('comments_template', [$this, 'commentsTemplateForCPT'], 99, 1);
    }

    public function courseSpecialPrice($price='', $postId=0)
    {
        if (!$postId){
           return $price;
        }
        $initialPrice = get_post_meta($postId, 'ulp_course_initial_price', TRUE);
        if ($initialPrice!==null && $initialPrice>0){

            $view = new \ViewUlp();
            $view->setTemplate(ULP_PATH . 'views/templates/course/initial_price.php');
            $view->setContentData([
                                    'initialPrice' => ulp_format_price($initialPrice),
                                    'price' => $price
            ], true);
            return $view->getOutput();
        }
        return $price;
    }


    public function commentsTemplateForCPT($templatePath='')
    {
        global $post;
        $postId = isset($post->ID) ? $post->ID : 0;
        if (empty($postId)){
            return $templatePath;
        }
        $postType = \DbUlp::getPostTypeById($postId);
        if (empty($postType)){
            return $templatePath;
        }
        if ($postType=='ulp_announcement'){
            $template = ULP_PATH . 'views/templates/comments-announcements.php';
            $template = apply_filters('ulp_filter_shortcodes_template', $template, 'comments-announcements.php' );
            return $template;
        } else if ($postType=='ulp_qanda'){
            $template = ULP_PATH . 'views/templates/comments-qanda.php';
            $template = apply_filters('ulp_filter_shortcodes_template', $template, 'comments-qanda.php' );
            return $template;
        }
        return $templatePath;
    }



}
