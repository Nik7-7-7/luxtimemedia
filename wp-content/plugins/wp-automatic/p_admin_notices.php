<?php

//message handler to display message we add using add_settings_error function used on the file upload hander function named wp_automatic_handle_file_upload
function wp_automatic_add_settings_errors() {
	
    settings_errors();
    
}
add_action('admin_notices', 'wp_automatic_add_settings_errors');