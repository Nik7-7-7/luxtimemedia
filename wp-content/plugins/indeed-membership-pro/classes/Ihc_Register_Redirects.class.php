<?php

if (class_exists('Ihc_Register_Redirects')){
   return;
}

class Ihc_Register_Redirects{

    public function __construct(){
        add_filter('ihc_register_redirect_filter', array($this, 'the_filter'), 10, 3);
    }


    public function the_filter($url='', $uid=0, $lid=0){
        $rules = get_option('ihc_register_redirects_by_level_rules');
        if (is_array($rules) && isset($rules[$lid]) && $rules[$lid]>-1){
            $temporary = get_permalink($rules[$lid]);
            if (!empty($temporary)){
                $url = $temporary;
            } else {
                // maybe its a custom link
                $temporary = ihc_get_redirect_link_by_label( $rules[$lid], $uid );
                if (!empty($temporary)){
                    $url = $temporary;
                } 
            }
        }
        return $url;
    }


}
