<?php

class AIKIT_Embeddings_Connector {

    private $local_connector;
    private $qdrant_connector;
    private static $instance = null;
    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Embeddings_Connector();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->local_connector = AIKIT_Embeddings_Local_Connector::get_instance();
        $this->qdrant_connector = AIKIT_Embeddings_Qdrant_Connector::get_instance();
    }

    public function create_embeddings($job)
    {
        $this->resolve_connector_by_job($job)->create_embeddings($job);
    }

    public function query_embeddings($id, $query, $num_results = 1)
    {
        $embedding = $this->get_embedding_by_id($id);

        if (!$embedding) {
            throw new Exception('Embedding not found');
        }

        return $this->resolve_connector($id)->query_embeddings($embedding, $query, $num_results);
    }

    public function delete_index($job)
    {
        $embedding = $this->get_embedding_by_id($job->id);

        if (!$embedding) {
            throw new Exception('Embedding not found');
        }

        return $this->resolve_connector_by_job($job)->delete_index($job);
    }

    public function ping()
    {
        $this->qdrant_connector->ping();
    }

    public function get_embedding_by_id($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . AIKIT_Embeddings::TABLE_NAME_EMBEDDINGS_JOBS;

        $id = intval($id);
        // sql injection safe

        $job = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
        );

        return $job;
    }

    private function resolve_connector($id)
    {
        $embedding = $this->get_embedding_by_id($id);

        if (!$embedding) {
            throw new Exception('Embedding not found');
        }

        if ($embedding->type == 'local') {
            return $this->local_connector;
        }

        return $this->qdrant_connector;
    }

    private function resolve_connector_by_job($job)
    {
        if ($job->type == 'local') {
            return $this->local_connector;
        }

        return $this->qdrant_connector;
    }
}