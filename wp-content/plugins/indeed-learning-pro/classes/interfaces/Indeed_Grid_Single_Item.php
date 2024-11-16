<?php
if (!defined('ABSPATH')){
   exit();
}
if (interface_exists('Indeed_Grid_Single_Item')){
   return;
}
interface Indeed_Grid_Single_Item
{
    public function set_shortcode_attributes($input_data=array());
    public function build($item);
    public function get_output();
    public function reset();
}
