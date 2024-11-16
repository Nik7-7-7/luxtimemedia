<?php

use AIKit\Dependencies\duzun\hQuery;

class AIKIT_Youtube_Subtitle_Reader {

    // singleton
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Youtube_Subtitle_Reader();
        }
        return self::$instance;
    }

    public function get_video_title($url)
    {
        try {
            $page_content = file_get_contents($url);
            $doc = hQuery::fromHTML($page_content);

            $title = $doc->find('title')->text();
        } catch (\Throwable $e) {
            $title = '';
        }

        return str_replace(' - YouTube', '', $title);
    }

    public function get_subtitles($url) {
        $video_id = $this->get_video_id_from_url($url);

        if (empty($video_id)) {
            throw new Exception('Could not get video id from url');
        }

        return $this->read_subtitles($video_id);
    }

    private function get_video_id_from_url($url)
    {
        // if youtu.be/abc123
        if (strpos($url, 'youtu.be') !== false) {
            $url = rtrim($url, '/');
            $parts = explode('/', $url);
            return $parts[count($parts) - 1] ?? '';
        }

        // parse url and return the v parameter
        $parsed_url = parse_url($url);
        parse_str($parsed_url['query'], $query);

        return $query['v'] ?? '';
    }

    private function read_subtitles($video_id)
    {
        ###['youtube-subtitles']

        $rapid_api_key = get_option('aikit_setting_rapidapi_key');

        if (empty($rapid_api_key)) {
            throw new Exception('Rapid API key is not set, please set it in the settings page. For more information, please see the description in the settings page on how to setup a Rapid API account.');
        }

        $client = new \AIKit\Dependencies\GuzzleHttp\Client();

        $response = $client->request('GET', 'https://subtitles-for-youtube.p.rapidapi.com/subtitles/' . $video_id, [
            'headers' => [
                'X-RapidAPI-Key' => $rapid_api_key,
                'X-RapidAPI-Host' => 'subtitles-for-youtube.p.rapidapi.com',
            ],
        ]);

        $status_code = $response->getStatusCode();

        if ($status_code === 403) {
            throw new Exception('Could not get subtitles for this video. It seems like the Rapid API key is not valid. For more information, please see the description in the settings page on how to setup a Rapid API account.');
        }

        if ($status_code !== 200) {
            throw new Exception('Could not get subtitles for this video. Are you sure it has subtitles?');
        }

        $body = $response->getBody();

        $subtitles = json_decode($body, true);

        if (empty($subtitles)) {
            throw new Exception('Could not get subtitles for this video. Are you sure it has subtitles?');
        }

        $subtitles_combined = '';
        foreach ($subtitles as $subtitle) {
            // convert from html entities to text
            $text = html_entity_decode($subtitle['text'], ENT_QUOTES, 'UTF-8');

            // remove any [] like [Music]
            $text = preg_replace('/\[[^\]]*\]/', '', $text);

            // replace newlines with spaces
            $text = str_replace("\n", ' ', $text);

            // replace multiple spaces with a single space
            $text = preg_replace('/\s+/', ' ', $text);
            $subtitles_combined .= $text . ' ';
        }

        return $this->turn_into_paragraphs($subtitles_combined);
    }

    private function turn_into_paragraphs($string) {
        $words = explode(' ', $string);
        $paragraphs = [];
        $paragraph = '';
        foreach ($words as $word) {
            $paragraph .= $word . ' ';
            if (aikit_calculate_word_count_utf8($paragraph) >= 200) {
                $paragraphs[] = $paragraph;
                $paragraph = '';
            }
        }
        if (!empty($paragraph)) {
            $paragraphs[] = $paragraph;
        }

        return implode("\n\n", $paragraphs);
    }
}
