<?php
if (!defined('ABSPATH')) {
    exit;
}

$js_urls = $request->get_param('js_urls');

if (empty($js_urls)) {
    return new WP_REST_Response('No JavaScript URLs provided.', 400);
}

$return_url = [];

foreach ($js_urls as $js_url) {
    // $response = wp_remote_get($js_url);
    $response = bwp_wp_remote_get($js_url);

    // Download and save the content of each JS file
    $content = file_get_contents($js_url);
    $file_name = basename(parse_url($js_url, PHP_URL_PATH)); // Remove GET parameters
    $file_path = optifer_cache . 'js/' . md5($js_url) . '_' . $file_name;

    // Create the directory if it doesn't exist
    if (!file_exists(optifer_cache . 'js/')) {
        mkdir(optifer_cache . 'js/', 0755, true);
    }

    // Save the content to the cache directory
    file_put_contents($file_path, $content);

    unset($content, $response);

    $return_url[md5($js_url)] = get_site_url() . '/wp-content/cache/berqwp/js/' . md5($js_url) . '_' . $file_name;
    
}

echo json_encode(
    [
        "status" => "success",
        "urls" => $return_url
    ]
);
exit;