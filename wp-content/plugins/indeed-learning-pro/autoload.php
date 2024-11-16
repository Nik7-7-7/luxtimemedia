<?php
spl_autoload_register('indeedUlpAutoloader');
function indeedUlpAutoloader($fullClassName=''){
    if (strpos($fullClassName, "Indeed\Ulp\PublicSection")!==false){
       $path = ULP_PATH . 'classes/public/';
    } else if (strpos($fullClassName, "Indeed\Ulp\Admin\Listing")!==false){
          $path = ULP_PATH . 'classes/admin/listing_table/';
    } else if (strpos($fullClassName, "Indeed\Ulp\Admin")!==false){
      $path = ULP_PATH . 'classes/admin/';
    } else if (strpos($fullClassName, "Indeed\Ulp\Db")!==false){
         $path = ULP_PATH . 'classes/Db/';
    } else if (strpos($fullClassName, "Indeed\Ulp\PostType")!==false){
         $path = ULP_PATH . 'classes/post_types/';
    } else if (strpos($fullClassName, "Indeed\Ulp\PaymentService")!==false){
          $path = ULP_PATH . 'classes/Payment_Services/';
    } else if (strpos($fullClassName, "Indeed\Ulp")!==false){
        $path = ULP_PATH . 'classes/';
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
