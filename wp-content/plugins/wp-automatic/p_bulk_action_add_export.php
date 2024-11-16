<?php

// Add custom bulk action for post type 'wp_automatic'
add_filter('bulk_actions-edit-wp_automatic', 'wp_automatic_add_bulk_action');
function wp_automatic_add_bulk_action($bulk_actions)
{
    $bulk_actions['custom_action'] = __('Export', 'textdomain');
    return $bulk_actions;
}

// Handle custom bulk action
add_filter('handle_bulk_actions-edit-wp_automatic', 'wp_automatic_handle_custom_bulk_action', 10, 3);
function wp_automatic_handle_custom_bulk_action($redirect_to, $action, $post_ids)
{
    if ($action !== 'custom_action')
    {
        return $redirect_to;
    }

    global $wpdb;
    $prefix = $wpdb->prefix;

    // Perform custom action on selected posts
    $campaigns = array(); //array to hold the campaigns data
    foreach ($post_ids as $post_id)
    {
        //get the campaign record from db table wp_automatic_campaigns 
        $campaign = $wpdb->get_row("select * from {$prefix}automatic_camps where camp_id = $post_id");

        //add the campaign to the campaigns array
        $campaigns[] = $campaign;

    }

    //return a json file for download 
    $json = json_encode($campaigns);
    $now = date('Y-m-d-H-i-s');
    $filename = 'wp_automatic_export_'.$now.'.json';
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    echo $json;
    exit;

 
}


?>