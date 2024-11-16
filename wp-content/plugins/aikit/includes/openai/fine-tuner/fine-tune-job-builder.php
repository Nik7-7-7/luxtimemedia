<?php

use \AIKit\Dependencies\GuzzleHttp\Client;
class AIKIT_Fine_Tune_Job_Builder {
    // singleton
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Fine_Tune_Job_Builder();
        }
        return self::$instance;
    }

    public function build($job)
    {
        if (!isset($job->openai_training_file_id)) {

            $file = $this->create_jsonl_file($job);
            $file_upload_result = $this->openai_upload_file($file);

            if (isset($file_upload_result['id'])) {
                $job->openai_training_file_id = $file_upload_result['id'];

                global $wpdb;

                $wpdb->update(
                    $wpdb->prefix . AIKIT_Fine_Tuner::TABLE_NAME_FINE_TUNE_JOBS,
                    array(
                        'openai_training_file_id' => $file_upload_result['id'],
                        'date_modified' => current_time('mysql', true),
                    ),
                    array('id' => $job->id)
                );
            } else {
                throw new Exception('Failed to upload fine-tune file to OpenAI');
            }
        }

        $result = $this->openai_create_fine_tune($job);

        if (isset($result['id'])) {
            global $wpdb;
            $job->openai_fine_tune_id = $result['id'];

            $wpdb->update(
                $wpdb->prefix . AIKIT_Fine_Tuner::TABLE_NAME_FINE_TUNE_JOBS,
                array(
                    'openai_fine_tune_id' => $result['id'],
                    'date_modified' => current_time('mysql', true),
                ),
                array('id' => $job->id)
            );
        } else {
            throw new Exception('Failed to create fine-tune job on OpenAI');
        }

        return $job;
    }

    public function check_fine_tune_status($job)
    {
        ###['openai-fine-tune-status']

        $client = new Client();

        try {

            $res = $client->request('GET', 'https://api.openai.com/v1/fine-tunes/' . $job->openai_fine_tune_id, [
                'headers' => [
                    'Authorization' => 'Bearer ' . get_option('aikit_setting_openai_key'),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $body = $res->getBody();

            return json_decode($body, true);

        } catch (\Throwable $th) {
            throw $th;
        }

    }

    private function create_jsonl_file($job)
    {
        ###['openai-fine-tune-create-file']

        $entered_prompt_completion_pairs = json_decode($job->entered_prompt_completion_pairs, true);

        // temp file
        $temp_file = tmpfile();

        $prompt_stop_sequence = $job->prompt_stop_sequence;
        $completion_stop_sequence = $job->completion_stop_sequence;

        // write to temp file
        foreach ($entered_prompt_completion_pairs as $pair) {
            $line = [];
            $line['prompt'] = trim($pair['prompt']) . $prompt_stop_sequence;
            $line['completion'] = ' ' . trim($pair['completion']) . $completion_stop_sequence;
            $line = json_encode($line) . "\n";
            fwrite($temp_file, $line);
        }

        return $temp_file;
    }

    private function openai_upload_file($file)
    {
        ###['openai-fine-tune-upload-file']

        $client = new Client();

        $file_path = stream_get_meta_data($file)['uri'];
        $file_name = basename($file_path);

        try {
            $response = $client->request('POST', 'https://api.openai.com/v1/files', [
                'headers' => [
                    'Authorization' => 'Bearer ' . get_option('aikit_setting_openai_key'),
                ],
                'multipart' => [
                    [
                        'name' => 'purpose',
                        'contents' => 'fine-tune'
                    ],
                    [
                        'name' => 'file',
                        'contents' =>  fopen($file_path, 'r'),
                        'filename' => $file_name,
                        'headers' => [
                            'Content-Type' => 'application/json-lines',
                        ],
                    ],
                ],
            ]);

            return json_decode($response->getBody(), true);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function openai_create_fine_tune($job)
    {
        ###['openai-fine-tune-create-job']

        $client = new Client();

        try {

            $res = $client->request('POST', 'https://api.openai.com/v1/fine-tunes', [
                'body' => json_encode([
                    'training_file' => $job->openai_training_file_id,
                    'model' => $job->base_model,
                    'suffix' => $job->output_model_name,
                ]),
                'headers' => [
                    'Authorization' => 'Bearer ' . get_option('aikit_setting_openai_key'),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $body = $res->getBody();

            return json_decode($body, true);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function cancel_fine_tune($id)
    {
        ###['openai-fine-tune-cancel-job']

        $client = new Client();

        try {

            $res = $client->request('POST', 'https://api.openai.com/v1/fine-tunes/' . $id . '/cancel', [
                'headers' => [
                    'Authorization' => 'Bearer ' . get_option('aikit_setting_openai_key'),
                    'Content-Type' => 'application/json',
                ],
            ]);

            $body = $res->getBody();

            return json_decode($body, true);
        } catch (\Throwable $th) {
            return null;
        }
    }
}
