<?php
if (!defined('ABSPATH'))
    exit;

if (class_exists('berqWP')) {
    class berqImages extends berqWP
    {
        function __construct()
        {

            // add_action('add_attachment', [$this, 'generate_webp_images']);
            // add_filter('wp_calculate_image_srcset', [$this, 'replace_srcset_with_webp'], 999, 5);

        }

        function replace_srcset_with_webp($sources, $size_array, $image_src, $image_meta, $attachment_id)
        {
            if (isset($_GET['creating_cache'])) {
                foreach ($sources as &$source) {

                    if (
                        (stripos($source['url'], '.jpg') !== false ||
                        stripos($source['url'], '.jpeg') !== false ||
                        stripos($source['url'], '.png') !== false) && strpos($source['url'], get_site_url()) !== false
                    ) {
                        $webp_url = str_replace(array('.jpg', '.jpeg', '.png'), '.webp', $source['url']);
                        $file_path = str_replace(get_site_url(), ABSPATH, $webp_url);

                        if (!file_exists($file_path) && isset($_GET['creating_cache'])) {
                            $this->generate_webp_images($attachment_id);
                        }

                        if (file_exists($file_path) && filesize($file_path) > 0) {
                            $source['url'] = $webp_url;
                        }
                    }
                }
            }
            return $sources;
        }

        function delete_generated_webp_images($attachment_id)
        {
            $file = get_attached_file($attachment_id);
            $file_info = pathinfo($file);

            // Check if the uploaded image is in a supported format
            $supported_formats = array('jpg', 'jpeg', 'png');
            if (in_array(strtolower($file_info['extension']), $supported_formats)) {
                // Get the available image sizes defined in WordPress settings
                $image_sizes = get_intermediate_image_sizes();

                // Generate webP version of the image for each size
                foreach ($image_sizes as $size_name) {
                    if ($size = wp_get_attachment_image_src($attachment_id, $size_name)) {
                        $webp_file = $file_info['dirname'] . '/' . $file_info['filename'] . '-' . $size[1] . 'x' . $size[2] . '.webp';
                        // var_dump($webp_file);

                        if (file_exists($webp_file)) {
                            wp_delete_file($webp_file);
                        }
                    }
                }

                // Generate webP version for the original size
                $webp_file_original = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';

                if (file_exists($webp_file_original)) {
                    wp_delete_file($webp_file_original);
                }
            }

        }

        function send_image_to_api($image_url, $quality, $maxwidth)
        {
            // Define the API endpoint
            $api_url = 'https://berqwp.com/wp-json/berqwp-api/webp';

            // Use the following format for file uploads with wp_remote_post
            $body = array(
                'image' => $image_url,
                'quality' => $quality,
                'maxwidth' => $maxwidth
            );

            // You don't need to manually set the Content-Type header to 'multipart/form-data';
            // wp_remote_post() will handle it for you when it detects you're sending files.

            // Send the request
            $response = wp_remote_post(
                $api_url,
                array(
                    'body' => $body,
                    'timeout' => 45 // You can adjust the timeout as needed
                )
            );

            // Check for errors
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                return "Something went wrong: $error_message";
            } else {
                return wp_remote_retrieve_body($response);
            }
        }

        function handle_not_webp($image_url, $quality, $max_width, $webp_path)
        {

            if (!function_exists('download_url')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }

            if (is_array($image_url)) {
                $image_url = $image_url[0];
            }

            $resp = $this->send_image_to_api($image_url, $quality, $max_width);

            $json = json_decode($resp, true);

            if (empty($json['success']) || $json['success'] !== true) {
                return false;
            }

            // Use the WordPress function to download the file
            $temp_file = download_url($json['webp_url']);

            // Check for errors
            if (is_wp_error($temp_file)) {
                return false;
            }

            // Copy the temp file to the specified path
            $result = copy($temp_file, $webp_path);

            // Clean up the temporary file
            unlink($temp_file);
        }

        function is_webp_supported()
        {
            if (function_exists('imagewebp') && function_exists('imagecreatetruecolor')) {
                $test_image = imagecreatetruecolor(1, 1); // Create a 1x1 blank image

                if ($test_image === false) {
                    return false;
                }

                ob_start();
                $webp_support = imagewebp($test_image, null, 100);
                ob_end_clean();
                imagedestroy($test_image);

                return $webp_support;
            }
            return false;
        }


        function generate_webp_images($attachment_id)
        {
            $file = get_attached_file($attachment_id);
            $file_info = pathinfo($file);

            // Check if the uploaded image is in a supported format
            $supported_formats = array('jpg', 'jpeg', 'png');
            if (in_array(strtolower($file_info['extension']), $supported_formats)) {
                // Get the available image sizes defined in WordPress settings
                $image_sizes = get_intermediate_image_sizes();

                // Set the quality of the image (optional)
                $quality = (int) get_option('berqwp_webp_quality');
                $max_width = (int) get_option('berqwp_webp_max_width');

                // Generate webP version of the image for each size
                foreach ($image_sizes as $size_name) {
                    if ($size = wp_get_attachment_image_src($attachment_id, $size_name)) {
                        $image_url = wp_get_attachment_image_src($attachment_id, $size_name);
                        $webp_file = $file_info['dirname'] . '/' . $file_info['filename'] . '-' . $size[1] . 'x' . $size[2] . '.webp';

                        if (!file_exists($webp_file)) {

                            // if webp generation is not supported
                            if (!$this->is_webp_supported()) {
                                $this->handle_not_webp($image_url, $quality, $max_width, $webp_file);
                                continue;
                            }

                            // Load the original image
                            $image = null;
                            $extension = strtolower($file_info['extension']);
                            if ($extension === 'jpg' || $extension === 'jpeg') {
                                $image = imagecreatefromjpeg($file);
                            } elseif ($extension === 'png') {
                                $image = imagecreatefrompng($file);

                                // // Preserve transparency for WebP conversion
                                // imagesavealpha($image, true);
                                // $transparentColor = imagecolorallocatealpha($image, 0, 0, 0, 127);
                                // imagefill($image, 0, 0, $transparentColor);


                                // Check if the PNG image has an alpha channel (transparency)
                                // $has_alpha = imagecolortransparent($image);
                                // if ($has_alpha || imagecolorstotal($image) > 256) {
                                //     // Preserve transparency for WebP conversion
                                //     imagepalettetotruecolor($image);
                                //     imagesavealpha($image, true);
                                //     $transparentColor = imagecolorallocatealpha($image, 0, 0, 0, 127);
                                //     imagefill($image, 0, 0, $transparentColor);
                                // }
                            }

                            if ($image) {
                                // Resize the image to the defined size
                                $resized_image = imagescale($image, $size[1], $size[2]);

                                if (!$resized_image) {
                                    continue;
                                }

                                // Check if the width exceeds the maximum width
                                if ($size[1] > $max_width) {
                                    $resized_image = imagescale($resized_image, $max_width);
                                }

                                // Save the resized image as WebP
                                imagewebp($resized_image, $webp_file, $quality);

                                // Free up memory
                                imagedestroy($image);
                                imagedestroy($resized_image);
                            }
                        }
                    }
                }

                // Generate webP version for the original size
                $webp_file_original = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
                $image_url = wp_get_attachment_url($attachment_id);

                if (!file_exists($webp_file_original)) {

                    // if webp generation is not supported
                    if (!$this->is_webp_supported()) {
                        $this->handle_not_webp($image_url, $quality, $max_width, $webp_file_original);
                        return;
                    }

                    // Load the original image
                    $image_original = null;
                    $extension_original = strtolower($file_info['extension']);
                    if ($extension_original === 'jpg' || $extension_original === 'jpeg') {
                        $image_original = imagecreatefromjpeg($file);
                    } elseif ($extension_original === 'png') {
                        $image_original = imagecreatefrompng($file);

                        // Ensure the image is true color, not palette-based
                        imagepalettetotruecolor($image_original);
                    }

                    if ($image_original) {
                        // Check if the width exceeds the maximum width
                        $image_width = imagesx($image_original); // get image width
                        if ($image_width > $max_width) {
                            $image_original = imagescale($image_original, $max_width);
                        }

                        // Save the original image as WebP
                        imagewebp($image_original, $webp_file_original, $quality);

                        // Free up memory
                        imagedestroy($image_original);
                    }
                }
            }
        }

        function delete_webp_images($attachment_id)
        {
            $file = get_attached_file($attachment_id);
            $file_info = pathinfo($file);

            // Check if the uploaded image is in a supported format
            $supported_formats = array('jpg', 'jpeg', 'png');
            if (in_array(strtolower($file_info['extension']), $supported_formats)) {
                // Get the available image sizes defined in WordPress settings
                $image_sizes = get_intermediate_image_sizes();

                // Set the quality of the image (optional)
                $quality = (int) get_option('berqwp_webp_quality');
                $max_width = (int) get_option('berqwp_webp_max_width');

                // Generate webP version of the image for each size
                foreach ($image_sizes as $size_name) {
                    if ($size = wp_get_attachment_image_src($attachment_id, $size_name)) {
                        $webp_file = $file_info['dirname'] . '/' . $file_info['filename'] . '-' . $size[1] . 'x' . $size[2] . '.webp';
                        // var_dump($webp_file);

                        if (file_exists($webp_file)) {
                            unlink($webp_file);
                        }
                    }
                }

                // Generate webP version for the original size
                $webp_file_original = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
                // var_dump($webp_file_original);

                if (file_exists($webp_file_original)) {
                    unlink($webp_file_original);
                }
            }
        }




        function remove_webp_image($attachment_id)
        {
            $file = get_attached_file($attachment_id);
            $file_info = pathinfo($file);

            // Check if the uploaded image is in a supported format
            $supported_formats = array('jpg', 'jpeg', 'png');
            if (isset($file_info['extension']) && in_array(strtolower($file_info['extension']), $supported_formats)) {
                // Remove the corresponding WebP image if it exists
                $webp_file = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
                if (file_exists($webp_file)) {
                    unlink($webp_file);
                }
            }
        }

        // Generate webP images for existing images in the media library
        function generate_webp_images_for_existing()
        {
            $args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'posts_per_page' => -1,
            );

            $attachments = get_posts($args);

            foreach ($attachments as $attachment) {
                $this->generate_webp_images($attachment->ID);
            }
        }

        function delete_webp_images_for_existing()
        {
            $args = array(
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'post_status' => 'inherit',
                'posts_per_page' => -1,
            );

            $attachments = get_posts($args);

            foreach ($attachments as $attachment) {
                $this->remove_webp_image($attachment->ID);
            }
        }
    }

    $berqImages = new berqImages();
}