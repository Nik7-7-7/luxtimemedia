<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('berqImages')) {
    require_once optifer_PATH . '/inc/class-berqimages.php';
}
$berqImages = new berqImages();
$images = json_decode($request->get_param('images'), true);
$done = 0;

foreach ($images as $image_id) {
    try {
        $berqImages->generate_webp_images($image_id);
        $done++;
    }
    //catch exception
    catch (Exception $e) {
        echo wp_kses_post('Message: ' . $e->getMessage());
    }
}

echo json_encode([$done]);

unset($images);