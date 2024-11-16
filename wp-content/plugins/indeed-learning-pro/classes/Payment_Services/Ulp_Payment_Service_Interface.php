<?php
if (!defined('ABSPATH')){
   exit();
}
if (interface_exists('Ulp_Payment_Service_Interface')){
   return;
}
/**
 *
 */
interface Ulp_Payment_Service_Interface
{
    
    public function setTransactionDetails();
    public function pay();
}
