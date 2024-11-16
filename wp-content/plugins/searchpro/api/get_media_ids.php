<?php
if (!defined('ABSPATH')) exit;

$args = array(
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'post_status' => 'inherit',
    'posts_per_page' => -1,
);

$attachments = get_posts($args);
$ids = [
    'optimized' => [],
    'unoptimized' => [],
];

foreach ($attachments as $attachment) {
    $file = get_attached_file($attachment->ID);
    $file_info = pathinfo($file);

    // Check if the uploaded image is in a supported format
    $supported_formats = array('jpg', 'jpeg', 'png');
    if (in_array(strtolower($file_info['extension']), $supported_formats)) {
        $webp_file_original = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';

        if (file_exists($webp_file_original)) {
            $ids['optimized'][] = $attachment->ID;
        } else {
            $ids['unoptimized'][] = $attachment->ID;
        }
    }
}

echo json_encode($ids);

unset($attachments, $ids);