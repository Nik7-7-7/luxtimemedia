<?php
// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if(!function_exists('jitsi_user_options')){
    function jitsi_user_options(){
        $users = [];
        $db_users = get_users(array('role__in' => ['author', 'editor', 'administrator']));
        foreach($db_users as $user){
            $users[$user->data->ID] = $user->data->display_name;
        }
        return $users;
    }
}

if(!function_exists('get_system_timezone')){
    function get_system_timezone() {
		$timezone = get_option( 'timezone_string' );

		if ( $timezone == false ) {
			$timezone = 'UTC';
		}

		return $timezone;
	}
}