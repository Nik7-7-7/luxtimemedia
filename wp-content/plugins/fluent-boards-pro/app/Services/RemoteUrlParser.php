<?php

namespace FluentBoardsPro\App\Services;

class RemoteUrlParser
{

    private static $instance;

    /*
     * Create a method to call the getInfoFromRemoteUrl static method as magic method
     */
    public static function parse($url)
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

//        $oEmbed = self::$instance->getOembed($url);
//
//        if ($oEmbed) {
//            return $oEmbed;
//        }

        return self::$instance->getInfoFromRemoteUrl($url);
    }

    public function getOembed($url)
    {
        $supportedDomains = [
            'youtube.com',
            'youtu.be',
            'vimeo.com',
            'dailymotion.com',
            'wordpress.tv',
            'screencast.com',
        ];


        // Using regex to check if the $url is matched with any of the supported domains
        $pattern = '#https?://(?:www\.)?(' . implode('|', $supportedDomains) . ')/#i';
        if (!preg_match($pattern, $url)) {
            return null;
        }

        $data = (new \WP_oEmbed())->get_data($url);

        if (!$data || is_wp_error($data) || empty($data->provider_name)) {
            return null;
        }

        return array_filter([
            'title'        => $data->title,
            'author_name'  => $data->author_name,
            'type'         => 'oembed',
            'provider'     => strtolower($data->provider_name),
            'content_type' => $data->type,
            'url'          => $url,
            'html'         => $data->html,
            'image'        => $data->thumbnail_url,
        ]);
    }

    public function getInfoFromRemoteUrl($url)
    {
        $url = untrailingslashit($url);

        if (empty($url)) {
            return new \WP_Error('rest_invalid_url', __('Invalid URL'), array('status' => 404));
        }

        $cacheKey = 'g_url_details_response_' . md5($url);
        $remote_url_response = get_site_transient($cacheKey);

        if (!$remote_url_response) {
            $remote_url_response = $this->getRemoteBody($url);
            if (is_wp_error($remote_url_response) || empty($remote_url_response)) {
                return $remote_url_response;
            }

            set_site_transient($cacheKey, $remote_url_response, apply_filters('rest_url_details_cache_expiration', HOUR_IN_SECONDS));
        }

        $html_head = $this->getDocumentHead($remote_url_response);
        $title = $this->getTitle($html_head);
        if (!$title) {
            return new \WP_Error('rest_invalid_url', __('Invalid URL'), array('status' => 404));
        }

        $meta_elements = $this->getMetaWithContentElements($html_head);
        return array_filter([
            'title'       => $title,
            'image'       => $this->getImage($meta_elements, $url),
            'description' => $this->getDescription($meta_elements),
            'icon'        => $this->getIcon($html_head, $url),
            'type'        => 'meta_data',
            'url'         => $url
        ]);
    }

    private function getRemoteBody($url)
    {
        $modified_user_agent = 'WP-URLDetails/' . get_bloginfo('version') . ' (+' . get_bloginfo('url') . ')';

        $args = array(
            'limit_response_size' => 150 * KB_IN_BYTES,
            'user-agent'          => $modified_user_agent,
        );

        /**
         * Filters the HTTP request args for URL data retrieval.
         *
         * Can be used to adjust response size limit and other WP_Http::request() args.
         *
         * @param array $args Arguments used for the HTTP request.
         * @param string $url The attempted URL.
         * @since 5.9.0
         *
         */
        $args = apply_filters('rest_url_details_http_request_args', $args, $url);

        $response = wp_safe_remote_get($url, $args);

        if (\WP_Http::OK !== wp_remote_retrieve_response_code($response)) {
            // Not saving the error response to cache since the error might be temporary.
            return new \WP_Error(
                'no_response',
                __('URL not found. Response returned a non-200 status code for this URL.'),
                array('status' => \WP_Http::NOT_FOUND)
            );
        }

        $remote_body = wp_remote_retrieve_body($response);

        if (empty($remote_body)) {
            return new \WP_Error(
                'no_content',
                __('Unable to retrieve body from response at this URL.'),
                array('status' => \WP_Http::NOT_FOUND)
            );
        }

        return $remote_body;
    }

    private function getDocumentHead($html)
    {
        $head_html = $html;

        // Find the opening `<head>` tag.
        $head_start = strpos($html, '<head');
        if (false === $head_start) {
            // Didn't find it. Return the original HTML.
            return $html;
        }

        // Find the closing `</head>` tag.
        $head_end = strpos($head_html, '</head>');
        if (false === $head_end) {
            // Didn't find it. Find the opening `<body>` tag.
            $head_end = strpos($head_html, '<body');

            // Didn't find it. Return the original HTML.
            if (false === $head_end) {
                return $html;
            }
        }

        // Extract the HTML from opening tag to the closing tag. Then add the closing tag.
        $head_html = substr($head_html, $head_start, $head_end);
        $head_html .= '</head>';

        return $head_html;
    }

    private function getDescription($meta_elements)
    {
        // Bail out if there are no meta elements.
        if (empty($meta_elements[0])) {
            return '';
        }

        $description = $this->getMetadataFromMetaElement(
            $meta_elements,
            'name',
            '(?:description|og:description)'
        );

        // Bail out if description not found.
        if ('' === $description) {
            return '';
        }

        return $this->prepare_metadata_for_output($description);
    }

    private function getImage($meta_elements, $url)
    {
        $image = $this->getMetadataFromMetaElement(
            $meta_elements,
            'property',
            '(?:og:image|og:image:url)'
        );

        // Bail out if image not found.
        if ('' === $image) {
            return '';
        }

        // Attempt to convert relative URLs to absolute.
        $parsed_url = parse_url($url);
        if (isset($parsed_url['scheme']) && isset($parsed_url['host'])) {
            $root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/';
            $image = \WP_Http::make_absolute_url($image, $root_url);
        }

        return $image;
    }

    private function getTitle($html)
    {
        $pattern = '#<title[^>]*>(.*?)<\s*/\s*title>#is';
        preg_match($pattern, $html, $match_title);

        if (empty($match_title[1]) || !is_string($match_title[1])) {
            return '';
        }

        $title = trim($match_title[1]);

        return $this->prepare_metadata_for_output($title);
    }

    private function getMetaWithContentElements($html)
    {
        $pattern = '#<meta\s' .
                   '[^>]*' .
                   'content=(["\']??)(.*)\1' .
                   '[^>]*' .
                   '\/?>#' .
                   'isU';

        preg_match_all($pattern, $html, $elements);

        return $elements;
    }

    private function prepare_metadata_for_output($metadata)
    {
        $metadata = html_entity_decode($metadata, ENT_QUOTES, get_bloginfo('charset'));
        $metadata = wp_strip_all_tags($metadata);
        return $metadata;
    }

    private function getMetadataFromMetaElement($meta_elements, $attr, $attr_value)
    {
        // Bail out if there are no meta elements.
        if (empty($meta_elements[0])) {
            return '';
        }

        $metadata = '';
        $pattern = '#' .
                   $attr . '=([\"\']??)\s*' . $attr_value . '\s*\1' .
                   '#isU';

        foreach ($meta_elements[0] as $index => $element) {
            preg_match($pattern, $element, $match);

            if (empty($match)) {
                continue;
            }

            if (isset($meta_elements[2][$index]) && is_string($meta_elements[2][$index])) {
                $metadata = trim($meta_elements[2][$index]);
            }

            break;
        }

        return $metadata;
    }

    private function getIcon($html, $url)
    {
        // Grab the icon's link element.
        $pattern = '#<link\s[^>]*rel=(?:[\"\']??)\s*(?:icon|shortcut icon|icon shortcut)\s*(?:[\"\']??)[^>]*\/?>#isU';
        preg_match($pattern, $html, $element);
        if (empty($element[0]) || !is_string($element[0])) {
            return '';
        }
        $element = trim($element[0]);

        // Get the icon's href value.
        $pattern = '#href=([\"\']??)([^\" >]*?)\\1[^>]*#isU';
        preg_match($pattern, $element, $icon);
        if (empty($icon[2]) || !is_string($icon[2])) {
            return '';
        }
        $icon = trim($icon[2]);

        // If the icon is a data URL, return it.
        $parsed_icon = parse_url($icon);
        if (isset($parsed_icon['scheme']) && 'data' === $parsed_icon['scheme']) {
            return $icon;
        }

        // Attempt to convert relative URLs to absolute.
        if (!is_string($url) || '' === $url) {
            return $icon;
        }
        $parsed_url = parse_url($url);
        if (isset($parsed_url['scheme']) && isset($parsed_url['host'])) {
            $root_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . '/';
            $icon = \WP_Http::make_absolute_url($icon, $root_url);
        }

        return $icon;
    }
}