<?php
if (!defined('ABSPATH'))
    exit;

$webp_img = base64_decode($request->get_param('image'));
$webp_url = sanitize_text_field($request->get_param('url'));
$webp_path = str_replace(get_site_url(), ABSPATH, $webp_url);

// Validate file extension
$valid_extensions = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
$url_path = parse_url($webp_url, PHP_URL_PATH);
$file_extension = strtolower(pathinfo($url_path, PATHINFO_EXTENSION));

// Check the file extension
if (!in_array($file_extension, $valid_extensions)) {
    return false;
}

// Save the image to the specified path
$store = file_put_contents($webp_path, $webp_img);
unset($webp_img);