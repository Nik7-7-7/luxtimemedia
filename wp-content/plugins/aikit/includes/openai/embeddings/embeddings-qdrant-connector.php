<?php

use AIKit\Dependencies\Qdrant\Http\GuzzleClient;
use AIKit\Dependencies\Qdrant\Models\Request\CreateCollection;
use AIKit\Dependencies\Qdrant\Models\Request\SearchRequest;
use AIKit\Dependencies\Qdrant\Models\Request\VectorParams;
use AIKit\Dependencies\Qdrant\Config;
use AIKit\Dependencies\Qdrant\Models\VectorStruct;
use AIKit\Dependencies\Qdrant\Qdrant;
use AIKit\Dependencies\Qdrant\Models\PointStruct;
use \AIKit\Dependencies\Qdrant\Models\PointsStruct;

class AIKIT_Embeddings_Qdrant_Connector {

    const QDRANT_VECTOR_NAME = 'chatbot';

    // singleton
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Embeddings_Qdrant_Connector();
        }
        return self::$instance;
    }

    public function create_embeddings($job)
    {
        $index_name = $job->index_name;
        $data_records = json_decode($job->entered_data);
        $this->create_collection($index_name);
        $client = $this->get_qdrant_client();

        $strings = array();
        foreach ($data_records as $data_record) {
            $split_strings = aikit_adjust_string_to_max_token_count($data_record);
            $strings = array_merge($strings, $split_strings);
        }

        foreach ($strings as $string) {
            $vector_data = aikit_build_embeddings_vector($string);

            $vector = new VectorStruct($vector_data, self::QDRANT_VECTOR_NAME);
            $id = $this->hash_string($string);

            $point = new PointStruct($id, $vector,
                [
                    'data' => $string

                ]);

            $points = new PointsStruct();

            $points->addPoint($point);

            $response = $client->collections($index_name)->points()->upsert(
                $points,
                [
                    'wait' => 'true'
                ]
            );

            if (!$this->is_qdrant_response_success($response)) {
                throw new Exception('Qdrant error - could not create embeddings. Error details: ' . json_encode($response->__toArray()));
            }
        }
    }

    public function delete_index($job)
    {
        if (!empty($job->index_name)) {
            $client = $this->get_qdrant_client();
            $client->collections($job->index_name)->delete();
        }
    }

    public function ping()
    {
        $host = get_option('aikit_setting_qdrant_host');
        $api_key = get_option('aikit_setting_qdrant_api_key');

        if (empty($host) || empty($api_key)) {
            return;
        }

        // do a listing collection request to avoid Qdrant inactivity removal of resources

        try {
            $client = $this->get_qdrant_client();
            $client->collections()->list();
        } catch (Throwable $e) {
            // do nothing
        }
    }

    private function hash_string($string, $digits = 10) {
        $m = pow(10, $digits + 1) - 1;
        $phi = pow(10, $digits) / 2 - 1;
        $n = 0;
        for ($i = 0; $i < strlen($string); $i++) {
            $n = ($n + $phi * ord($string[$i])) % $m;
        }

        return intval($n);
    }

    private function create_collection($collection_name)
    {
        $client = $this->get_qdrant_client();

        $createCollection = new CreateCollection();
        $createCollection->addVector(new VectorParams(1536, VectorParams::DISTANCE_COSINE), self::QDRANT_VECTOR_NAME);
        $collectionsResponse = $client->collections($collection_name)->list();

        // if collection already exists, return
        if ($collectionsResponse->offsetExists('result')) {
            $result = $collectionsResponse->offsetGet('result');
            $collections = $result['collections'] ?? array();

            foreach ($collections as $collection) {
                if ($collection['name'] == $collection_name) {
                    return;
                }
            }
        }

        $response = $client->collections($collection_name)->create($createCollection);

        if (!$this->is_qdrant_response_success($response)) {
            throw new Exception('Qdrant error - could not create collection. Error details: ' . json_encode($response->__toArray()));
        }
    }

    private function get_qdrant_client()
    {
        $config = new Config(get_option('aikit_setting_qdrant_host'));
        $api_key = get_option('aikit_setting_qdrant_api_key');

        if (!empty($api_key)) {
            $config->setApiKey($api_key);
        }

        return new Qdrant(new GuzzleClient($config));
    }

    public function query_embeddings($job, $query, $num_results = 1)
    {
        $client = $this->get_qdrant_client();

        $vector_data = aikit_build_embeddings_vector($query);
        $vector = new VectorStruct($vector_data, self::QDRANT_VECTOR_NAME);

        $search_request = new SearchRequest($vector);
        $search_request->setLimit($num_results);
        $search_request->setWithPayload(true);
        $search_request->setScoreThreshold(0.7);

        $response = $client->collections($job->index_name)->points()->search(
            $search_request
        );

        $results = $response->offsetGet('result');

        $results_to_return = array();
        foreach ($results as $result) {
            $results_to_return[] = $result['payload']['data'];
        }


        return $results_to_return;
    }

    private function is_qdrant_response_success ($response) {
        if ($response->offsetExists('result')) {
            $result = $response->offsetGet('result');

            return $result['status'] == 'completed' || $result === true;
        }

        return false;
    }

}