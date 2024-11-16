<?php
if (!defined('ABSPATH')){
   exit();
}
if (class_exists('Ulp_Global_Settings')){
   return;
}
class Ulp_Global_Settings{
    private static $data = [];
    public static function get($var_name=''){
        if (isset(self::$data [$var_name])){
            return self::$data [$var_name];
        } else {
            self::$data [$var_name] = stripslashes(get_option($var_name));
            return self::$data [$var_name];
        }
    }
}
