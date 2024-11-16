<?php
spl_autoload_register('indeedIhcAutoloader');
function indeedIhcAutoloader($fullClassName='')
{
    if (strpos($fullClassName, "Indeed\Ihc\Db")!==FALSE){
        $path = IHC_PATH . 'classes/Db/';
    } else if (strpos($fullClassName, "Indeed\Ihc\Gateways\Libraries\PayPalExpress") !== FALSE){
        $path = IHC_PATH . 'classes/gateways/libraries/paypal-express/';
    } else if (strpos($fullClassName, "Indeed\Ihc\Gateways")!==FALSE){
        $path = IHC_PATH . 'classes/gateways/';
    } else if (strpos($fullClassName, "Indeed\Ihc\Payments")!==FALSE){
        $path = IHC_PATH . 'classes/payments/';
    } else if (strpos($fullClassName, "Indeed\Ihc\Services")!==FALSE){
        $path = IHC_PATH . 'classes/services/';
    } else if (strpos($fullClassName, "Indeed\Ihc\Admin")!==FALSE){
        $path = IHC_PATH . 'admin/classes/';
    } else if (strpos($fullClassName, "Indeed\Ihc")!==FALSE){
        $path = IHC_PATH . 'classes/';
    }
    if (empty($path)){
       return;
    }

    $classNameParts = explode('\\', $fullClassName);
    if (!$classNameParts){
       return;
    }
    $lastElement = count($classNameParts) - 1;
    if (empty($classNameParts[$lastElement])){
       return;
    }
    $fullPath = $path . $classNameParts[$lastElement] . '.php';

    if (!file_exists($fullPath)){
       return;
    }
    include $fullPath;
}
