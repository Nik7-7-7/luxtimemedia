<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('UlpPostAbstract')){
   require_once ULP_PATH . 'classes/Abstracts/UlpPostAbstract.class.php';
}
if (class_exists('Ulp_Course_Reviews')){
   return;
}
class Ulp_Course_Reviews extends UlpPostAbstract{
}
