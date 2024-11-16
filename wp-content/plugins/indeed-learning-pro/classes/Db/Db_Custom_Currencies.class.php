<?php
if (!defined('ABSPATH')){
   exit();
}
if (!class_exists('Indeed_Abstract_Custom_Currencies')){
   require_once ULP_PATH . 'classes/Abstracts/Indeed_Abstract_Custom_Currencies.class.php';
}
if (class_exists('Db_Custom_Currencies')){
   return;
}
class Db_Custom_Currencies extends Indeed_Abstract_Custom_Currencies{
    protected $_option_name = 'ulp_custom_currencies_list';
    protected $_post_key_name = 'new_currency_code';
    protected $_post_value_name = 'new_currency_name';
}
