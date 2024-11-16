<?php

class AIKIT_Embeddings_Local_Connector {
    // singleton
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Embeddings_Local_Connector();
        }
        return self::$instance;
    }

    public function create_embeddings($job)
    {
        $data_records = json_decode($job->entered_data);

        $strings = array();
        foreach ($data_records as $data_record) {
            $split_strings = aikit_adjust_string_to_max_token_count($data_record);
            $strings = array_merge($strings, $split_strings);
        }

        $current_index_data = $job->local_embedding_index ? json_decode($job->local_embedding_index, true) : [];

        $index_data = [];
        foreach ($strings as $string) {

            if (isset($current_index_data[$string])) {
                $index_data[$string] = $current_index_data[$string];
                continue;
            }

            $hash = md5($string);

            $index_data[$hash] = [
                'text' => $string,
                'embedding' => aikit_build_embeddings_vector($string),
            ];
        }

        global $wpdb;
        $table_name = $wpdb->prefix . AIKIT_Embeddings::TABLE_NAME_EMBEDDINGS_JOBS;

        $wpdb->update(
            $table_name,
            [
                'local_embedding_index' => json_encode($index_data),
            ],
            [
                'id' => $job->id,
            ]
        );
    }

    public function delete_index($job)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . AIKIT_Embeddings::TABLE_NAME_EMBEDDINGS_JOBS;

        $wpdb->update(
            $table_name,
            [
                'local_embedding_index' => null,
            ],
            [
                'id' => $job->id,
            ]
        );
    }

    public function query_embeddings($job, $query, $num_results = 1)
    {
        $index_data = json_decode($job->local_embedding_index, true);

        $query_embedding = aikit_build_embeddings_vector($query);

        $similarities = [];

        foreach ($index_data as $embedding) {
            $similarities[] = [
                'text' => $embedding['text'],
                'score' => $this->cosineSimilarity($query_embedding, $embedding['embedding']),
            ];
        }

        usort($similarities, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        if (count($similarities) > 0 ) {
            $results = array_slice($similarities, 0, $num_results);
            $strings = array();
            foreach ($results as $result) {
                $strings[] = $result['text'];
            }

            return $strings;
        }

        return [];
    }

    private function dotProduct($vector1, $vector2) {
        $dotProduct = 0;
        foreach ($vector1 as $term => $count) {
            if (isset($vector2[$term])) {
                $dotProduct += floatval($count) * floatval($vector2[$term]);
            }
        }
        return $dotProduct;
    }

    private function magnitude($vector) {
        $sumOfSquares = 0;
        foreach ($vector as $count) {
            $sumOfSquares += $count * $count;
        }
        return sqrt($sumOfSquares);
    }

    private function cosineSimilarity($vector1, $vector2) {

        $dotProduct = $this->dotProduct($vector1, $vector2);
        $magnitude1 = $this->magnitude($vector1);
        $magnitude2 = $this->magnitude($vector2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0; // Prevent division by zero
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

}