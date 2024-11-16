<?php

use AIKit\Dependencies\duzun\hQuery;
use AIKit\Dependencies\Qdrant\Exception\InvalidArgumentException;

class AIKIT_Embeddings extends AIKIT_Page {
    const TABLE_NAME_EMBEDDINGS_JOBS = 'aikit_embeddings_jobs';
    const OPENAI_EMBEDDINGS_MODEL_NAME = 'text-embedding-ada-002';

    const MAX_ERROR_COUNT = 3;
    private $post_finder = null;
    private $tokenizer = null;

    private $embeddings_connector = null;

    // singleton instance

    private static $instance = null;

    // singleton constructor

    private function __construct() {

        add_action( 'rest_api_init', function () {
            register_rest_route( 'aikit/embeddings/v1', '/create', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_create_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/embeddings/v1', '/edit', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_edit_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/embeddings/v1', '/count-tokens', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_token_count_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/embeddings/v1', '/delete', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_delete_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

        });

        add_action('aikit_embeddings', array($this, 'execute'));
        $this->post_finder = AIKIT_Post_Finder::get_instance();
        $this->tokenizer = AIKIT_Gpt3Tokenizer::getInstance();
        $this->embeddings_connector = AIKIT_Embeddings_Connector::get_instance();
    }

    public function activate_scheduler()
    {
        if (! wp_next_scheduled ( 'aikit_embeddings')) {
            wp_schedule_event( time(), 'every_10_minutes', 'aikit_embeddings');
        }
    }

    public function deactivate_scheduler()
    {
        wp_clear_scheduled_hook('aikit_embeddings');
    }

    public function execute()
    {
        $this->execute_collecting_data_from_posts();
        $this->execute_creation_in_progress();
        $this->embeddings_connector->ping();
    }

    private function execute_creation_in_progress()
    {
        ###['embeddings-execute']

        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;

        $jobs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'creation_in_progress' AND is_running = 0 ORDER BY date_created ASC");

        foreach ($jobs as $job) {

            // mark job as running
            $wpdb->update(
                $table_name,
                array(
                    'is_running' => 1,
                    'date_modified' => current_time('mysql', true)
                ),
                array('id' => $job->id)
            );

            try {

                $index_name = !empty($job->index_name) ? $job->index_name : $this->create_random_index_name($job->name);
                $job->index_name = $index_name;
                $this->embeddings_connector->create_embeddings($job);

                $wpdb->update(
                    $table_name,
                    array(
                        'date_modified' => current_time('mysql', true),
                        'is_running' => 0,
                        'index_name' => $index_name,
                        'status' => 'completed',
                    ),
                    array('id' => $job->id)
                );

            } catch (\Throwable $th) {

                $message = $th->getMessage();
                if ($th instanceof InvalidArgumentException) {
                    $message .= ' ' . json_encode($th->getResponse()->__toArray());
                }

                // append to logs
                $logs = json_decode($job->logs);
                $logs[] = array(
                    'message' => $message,
                    'date' => current_time( 'mysql', true )
                );

                $error_count = intval($job->error_count);
                $error_count++;

                $updated_entity_fields = array(
                    'logs' => json_encode($logs),
                    'date_modified' => current_time( 'mysql', true ),
                    'is_running' => 0,
                    'error_count' => $error_count,
                );

                if ($error_count > self::MAX_ERROR_COUNT) {
                    $updated_entity_fields['status'] = 'failed';
                }

                $wpdb->update(
                    $table_name,
                    $updated_entity_fields,
                    array('id' => $job->id)
                );

            }
        }
    }

    private function create_random_index_name($name)
    {
        // remove all special characters and keep only letters and numbers
        $name = preg_replace('/[^A-Za-z0-9]/', '', $name);

        $index_name =  $name . rand(100000, 9999999999);

        // max length of index name is 255
        if (strlen($index_name) > 255) {
            $index_name = substr($index_name, 0, 255);
        }

        return strtolower($index_name);
    }

    private function execute_collecting_data_from_posts() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;

        $jobs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'collecting_data_from_posts' AND is_running = 0 ORDER BY date_modified ASC");

        foreach ($jobs as $job) {

            // mark job as running
            $wpdb->update(
                $table_name,
                array(
                    'is_running' => 1,
                    'date_modified' => current_time( 'mysql', true )
                ),
                array('id' => $job->id)
            );

            try {
                $chosen_post_paragraphs = json_decode($job->chosen_post_paragraphs);

                if (empty($chosen_post_paragraphs)) {
                    // loop through chosen posts and get paragraphs
                    $chosen_post_ids = json_decode($job->chosen_post_ids);
                    $chosen_post_paragraphs = array();
                    foreach ($chosen_post_ids as $post_id) {
                        $post = get_post($post_id);
                        $paragraphs = $this->get_paragraphs($post->post_content);
                        $chosen_post_paragraphs = array_merge($chosen_post_paragraphs, $paragraphs);
                    }
                }

                // save chosen post paragraphs
                $wpdb->update(
                    $table_name,
                    array(
                        'collected_data' => json_encode($chosen_post_paragraphs),
                        'date_modified' => current_time( 'mysql', true ),
                        'is_running' => 0,
                        'status' => 'pending_final_approval_on_collected_data_pairs'
                    ),
                    array('id' => $job->id)
                );

            } catch (\Throwable $th) {
                // append to logs
                $logs = json_decode($job->logs);
                $logs[] = array(
                    'message' => $th->getMessage(),
                    'date' => current_time( 'mysql', true )
                );

                $wpdb->update(
                    $table_name,
                    array(
                        'logs' => json_encode($logs),
                        'date_modified' => current_time( 'mysql', true ),
                        'is_running' => 0,
                    ),
                    array('id' => $job->id)
                );
            }
        }
    }

    private function get_paragraphs($html)
    {
        $paragraphs = preg_split('/\n\s*\n/', $html);

        // strip html tags
        $paragraphs = array_map(function($paragraph) {
            return strip_tags($paragraph);
        }, $paragraphs);

        // trim paragraphs
        $paragraphs = array_map(function($paragraph) {
            return trim($paragraph);
        }, $paragraphs);

        // remove empty paragraphs
        $paragraphs = array_filter($paragraphs, function($paragraph) {
            return !empty($paragraph);
        });

        $final_paragraphs = array();
        foreach ($paragraphs as $paragraph) {
            $token_count = $this->tokenizer->count($paragraph);

            $number_of_paragraphs = ceil($token_count / 1000);
             // split by space (utf-8 safe)
            $splitted = preg_split('/\s+/u', $paragraph);
            $splitted_count = count($splitted);

            $max_number_of_words_per_paragraph = ceil($splitted_count / $number_of_paragraphs);

            $chunks = array_chunk($splitted, $max_number_of_words_per_paragraph);

            foreach ($chunks as $chunk) {
                $final_paragraphs[] = implode(' ', $chunk);
            }
        }

        // merge paragraphs that are less than 100 tokens long to make sure we have at least 100 tokens per paragraph
        $final_paragraphs = array_reduce($final_paragraphs, function($carry, $paragraph) {
            $last_paragraph = end($carry);

            if (empty($last_paragraph)) {
                $carry[] = $paragraph;
                return $carry;
            }

            $last_paragraph_token_count = $this->tokenizer->count($last_paragraph);
            $current_paragraph_token_count = $this->tokenizer->count($paragraph);

            if ($last_paragraph_token_count + $current_paragraph_token_count < 100) {
                $carry[count($carry) - 1] = $last_paragraph . "\n" . $paragraph;
            } else {
                $carry[] = $paragraph;
            }

            return $carry;
        }, array());

        return $final_paragraphs;
    }

    public function handle_delete_request($data)
    {
        $id = $data['id'] ?? null;

        if (empty($id)) {
            return new WP_REST_Response(['success' => false], 400);
        }

        $job = $this->get_job_by_id($id);

        if (!$job) {
            return new WP_REST_Response(['success' => false], 404);
        }

        $chatbot_embedding_id = get_option('aikit_setting_chatbot_selected_embedding');

        if ($chatbot_embedding_id == $id) {
            return new WP_REST_Response([
                'success' => false,
                'message' => __('This embedding is currently selected for the chatbot. Please select another embedding for the chatbot before deleting this one.', 'aikit'),
            ], 400);
        }

        $this->embeddings_connector->delete_index($job);

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;

        $wpdb->delete(
            $table_name,
            array('id' => $id)
        );

        return new WP_REST_Response(['success' => true], 200);
    }

    public function handle_token_count_request($data)
    {
        $rows = $data['rows'] ?? array();

        $count = 0;
        foreach ($rows as $row) {
            $count += $this->tokenizer->count($row);
        }

        return new WP_REST_Response(['count' => $count], 200);
    }

    public function handle_create_request($data)
    {
        $name = $data['embeddings_name'];
        $type = $data['embeddings_type'];

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;
        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'type' => $type,
                'status' => 'pending_data',
                'chosen_post_ids' => '[]',
                'logs' => '[]',
                'openai_model_name' => self::OPENAI_EMBEDDINGS_MODEL_NAME, // we can change this later
                'date_created' => current_time( 'mysql', true ),
                'date_modified' => current_time( 'mysql', true )
            )
        );

        $job_id = $wpdb->insert_id;

        return new WP_REST_Response(array(
            'id' => $job_id,
            'redirect_url' => admin_url( 'admin.php?page=aikit_embeddings&action=view&job_id=' . $job_id )
        ), 200);
    }

    public function get_embeddings($status = 'completed')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;

        $jobs = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE status = '$status'"
        );

        return $jobs;
    }

    public function handle_edit_request($data)
    {
        $job_id = $data['job_id'];

        $job = $this->get_job_by_id($job_id);

        if (!$job) {
            return new WP_REST_Response(array(
                'message' => 'Job not found'
            ), 404);
        }

        $entity = [];

        if (isset($data['chosen_post_ids'])) {
            $entity['chosen_post_ids'] = json_encode($data['chosen_post_ids']);
        }

        if (isset($data['entered_data'])) {
            $entity['entered_data'] = json_encode($data['entered_data']);
        }

        if (isset($data['data_source'])) {
            $entity['data_source'] = $data['data_source'];
        }

        $entity['date_modified'] = current_time( 'mysql', true );

        if (isset($data['next_step'])) {
            if (isset($data['data_source']) && $data['data_source'] == 'manual') {
                $entity['status'] = 'creation_in_progress';
            } else {
                if ($job->status == 'pending_data') {
                    $entity['status'] = 'collecting_data_from_posts';
                } else if ($job->status == 'pending_final_approval_on_collected_data_pairs') {
                    $entity['status'] = 'creation_in_progress';
                }
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;
        $wpdb->update(
            $table_name,
            $entity,
            array(
                'id' => $job_id
            )
        );

        return new WP_REST_Response(array(
            'id' => $job_id,
            'redirect_url' => admin_url( 'admin.php?page=aikit_embeddings&action=jobs' )
        ), 200);
    }

    public static function get_instance() {

        if ( self::$instance == null ) {

            self::$instance = new AIKIT_Embeddings();
        }

        return self::$instance;
    }

    public function render()
    {
        $active_tab = isset( $_GET['action'] )  ? $_GET['action'] : 'jobs';

        $second_tab_title = isset($_GET['job_id']) ? esc_html__( 'View / Edit Job', 'aikit' ) : esc_html__( 'Create Embeddings Job', 'aikit' );
        $second_tab_link = isset($_GET['job_id']) ? admin_url( 'admin.php?page=aikit_embeddings&action=view&job_id=' . $_GET['job_id'] ) : admin_url( 'admin.php?page=aikit_embeddings&action=create' );
        $cron_url = get_site_url() . '/wp-cron.php';
        
        ?>
        <div class="wrap">
        <h1><?php echo esc_html__( 'Embeddings', 'aikit' ); ?></h1>
        <p>
            <a href="#" class="aikit-util-button float-end mx-1 aikit-top-hidden-toggle btn btn-outline-secondary btn-sm"><i class="bi bi-info-lg"></i> <?php echo esc_html__( 'How to setup?', 'aikit' ); ?></a>
            <a href="https://youtu.be/VKtTOJ4MmJY" target="_blank" class="aikit-util-button float-end mx-1 btn btn-outline-secondary btn-sm"><i class="bi bi-youtube"></i> <?php echo esc_html__( 'Watch Video', 'aikit' ); ?></a>
            <?php echo esc_html__( 'AIKit Embeddings allows you to turn your data into vector representation which can be used to build improved Chatbot experience to answer your customers\' questions about your product or service.', 'aikit' ); ?>
        </p>

        <div class="aikit-top-hidden-note">
            <p>
                <?php echo esc_html__( 'To use embeddings, you can either choose to store your data locally, which it the cheaper option (for small to moderate datasets), or use Qdrant in case you have much data to store and process. If you choose to store data locally, then you don\'t need to do anything. If you choode the Qdrant router, them you need to create an account, you can create one for free at', 'aikit' ); ?>
                <a href="https://qdrant.to/refer?ref=aikit" target="_blank">https://qdrant.io</a>
            </p>
            <p>
                <?php echo esc_html__( 'Once you have you account created, Qdrant gives you a free forever cluster that can be used to store and query your data (embeddings). To connect to your cluster you need to enter the host URL and the API key of it in the ', 'aikit' ); echo '<a href="' . admin_url( 'admin.php?page=aikit&tab=qdrant' ) . '">' . esc_html__( 'AIKit settings page', 'aikit' ) . '</a>'; ?>

            </p>

            <p>
                <strong><?php echo esc_html__( 'Note:', 'aikit' ); ?></strong>
                <?php echo esc_html__('AIKit embedding creation jobs run in the background as scheduled jobs.', 'aikit'); ?>
                <?php echo esc_html__( 'By default, WordPress scheduled jobs only run when someone visits your site. To ensure that your embedding creation jobs run even if nobody visits your site, you can set up a cron job on your server to call the WordPress cron system at regular intervals. Please ask your host provider to do that for you. Here is the cron job definition:', 'aikit' ); ?>
                <code>
                    */5 * * * * curl -I <?php echo $cron_url ?> >/dev/null 2>&1
                </code>
            </p>
        </div>

        <ul class="nav nav-tabs aikit-embeddings-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'jobs' ? 'active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=aikit_embeddings&action=jobs' ); ?>"><?php echo esc_html__( 'Jobs', 'aikit' ); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'create' || $active_tab == 'view' ? 'active' : ''; ?>" aria-current="page" href="<?php echo $second_tab_link ?>"><?php echo $second_tab_title; ?></a>
            </li>
        </ul>

            <div class="aikit-embeddings-content">
                <?php
                if ($active_tab == 'create') {
                    $this->render_create_tab();
                } else if (isset($_GET['job_id']))
                    $this->render_view_tab($_GET['job_id']);
                else {
                    $this->render_listing_tab();
                }
                ?>
            </div>

        </div>

        <?php
    }

    private function render_view_tab($id)
    {
        $job = $this->get_job_by_id($id);

        if (empty($job)) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php echo esc_html__( 'Job not found.', 'aikit' ); ?>
                </p>
            </div>
            <?php

            return;
        }

        if (isset($_GET['force_edit']) && $_GET['force_edit'] === '1') {
            $job->status = 'pending_data';
            // save in db
            global $wpdb;
            $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'pending_data',
                    'date_modified' => current_time( 'mysql', true )
                ),
                array('id' => $job->id)
            );
        }

        if ($job->status == 'pending_data') {
            $this->render_pending_data_status_job($job);
        } else if ($job->status == 'collecting_data_from_posts') {
            $this->render_collecting_data_from_posts_status_job($job);
        } else if ($job->status == 'pending_final_approval_on_collected_data_pairs') {
            $this->render_pending_final_approval_on_collected_data_status_job($job);
        } else if ($job->status == 'creation_in_progress') {
            $this->render_creation_in_progress_status_job($job);
        } else if ($job->status == 'completed') {
            $this->render_completed_status_job($job);
        } else if ($job->status == 'failed') {
            $this->render_failed_status_job($job);
        }
    }

    private function render_collecting_data_from_posts_status_job($job)
    {
        ?>
        <p class="aikit-notice-message">
            <i class="bi bi-hourglass-split"></i>
            <?php
                echo esc_html__( 'Please set back and relax while the data is being collected from your posts/pages. Status of the job will change to reflect when this operation is done.', 'aikit');
            ?>
        </p>
        <?php

        $this->render_job_info_tabs($job, true);
    }

    private function render_failed_status_job($job)
    {
        ?>
        <p class="aikit-notice-message">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?php
            echo esc_html__( 'Unfortunately, the embedding creation job failed. Please check the logs below for more information.', 'aikit');
            ?>
        </p>
        <?php

        $this->render_job_info_tabs($job, true);

    }

    private function render_creation_in_progress_status_job($job)
    {
        ?>
        <p class="aikit-notice-message">
            <i class="bi bi-hourglass-split"></i>
            <?php
            echo esc_html__( 'Please set back and relax while the your embedding is being created. Status of the job will change to reflect when this is done.', 'aikit');
            ?>
        </p>
        <?php

        $this->render_job_info_tabs($job, true, 'entered');
    }

    private function render_pending_final_approval_on_collected_data_status_job($job)
    {
        ?>
        <p class="aikit-paragraph">
            <?php
            echo esc_html__( 'Please have a look at the collected data and approve them. Feel free to edit them as you see fit.', 'aikit');
            ?>
        </p>

        <input type="hidden" id="aikit-embeddings-job-id" value="<?php echo $job->id; ?>" />

        <?php

        $collectedData = $job->collected_data == null ? [] : json_decode($job->collected_data, true);

        if (count($collectedData) === 0) {
            echo esc_html__( 'No data found.', 'aikit' );
        }

        ?>
        <div class="row mt-4">
            <div class="col">
                <h6><?php echo esc_html__( 'Collected data', 'aikit' ); ?></h6>
            </div>
            <div class="col-2">

            </div>
        </div>

        <div class="tab-pane aikit-embeddings-data-manual-inputs">
        <?php

        foreach ($collectedData as $row) {
            ?>
            <div class="row mt-2 aikit-entered-data-container">
                <div class="col">
                    <textarea class="form-control aikit-entered-data" rows="3"><?php echo esc_html__($row); ?></textarea>
                </div>
                <div class="col-2">
                    <button class="btn btn-sm btn-outline-danger aikit-remove-row" tabindex="-1"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            <?php
        }
        ?>
        </div>
        <button class="btn btn-primary aikit-embeddings-approve-and-create-embeddings-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-play-fill"></i> <?php echo esc_html__( 'Create Embedding', 'aikit' ); ?></button>



        <?php

        $this->render_job_info_tabs($job);
    }

    private function render_completed_status_job($job)
    {
        $page_url = get_admin_url() . 'admin.php?page=aikit_embeddings&action=view&force_edit=1&job_id=' . $job->id;

        $chatbot_embedding_id = get_option('aikit_setting_chatbot_selected_embedding');

        $used_with_chatbot = $chatbot_embedding_id == $job->id;

        ?>
        <p class="aikit-notice-message">
            <i class="bi bi-check-circle-fill"></i>
            <?php
            echo esc_html__( 'Congratulations! Your embedding is ready to be used. You you should see it in the list of embeddings available in the Chatbot setting page in case you want to use it in the Chatbot.', 'aikit');
            ?>
        </p>

        <div class="row">
            <div class="col">
                <div class="float-end">
                    <a type="button" class="btn btn-sm btn-primary float-end mb-1 <?php echo $used_with_chatbot ? 'disabled' : '' ?>" href="<?php echo $page_url ?>"><i class="bi bi-pencil"></i> <?php echo __('Edit', 'aikit') ?></a>
                </div>
            </div>
        </div>
        <?php if ($used_with_chatbot) {?>
        <div class="row">
            <div class="col">
                <div class="float-end">
                    <small class="d-block"><?php echo __('This embedding is currently used with the Chatbot. Please select another embedding for the Chatbot before editing this one.', 'aikit') ?></small>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php

        $this->render_job_info_tabs($job, true, 'entered');
    }

    private function render_job_info_tabs($job, $show_data = false, $pairs_to_show = 'collected')
    {
        $logs = json_decode($job->logs, true);

        $logsCount = count($logs) == 0 ? '' : ' (' . count($logs) . ')';
        ?>
        <ul class="nav nav-tabs aikit-embeddings-job-info-tabs mt-5">
            <li class="nav-item">
                <a class="nav-link active" href="#job-info" data-bs-toggle="tab"><?php echo esc_html__( 'Job Info', 'aikit' ); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#logs" data-bs-toggle="tab"><?php echo esc_html__( 'Logs', 'aikit' ) . $logsCount; ?></a>
            </li>
            <?php

            if ($show_data) {
            ?>
                <li class="nav-item">
                    <a class="nav-link" href="#pairs" data-bs-toggle="tab"><?php echo esc_html__( 'Entered / Collected Data', 'aikit' ); ?></a>
                </li>

            <?php } ?>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="job-info">

                <div class="row mt-3">
                    <div class="col">
                        <span class=""><?php echo __('Status', 'aikit'); ?></span>: <span class="badge badge-pill badge-dark aikit-badge-active ms-1"><?php echo $this->map_embeddings_status_map($job->status) ?></span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <?php
                        $this->_radio_button_set(
                            'aikit-embeddings-type',
                            __('Embeddings Storage Type', 'aikit'),
                            [
                                'local' => __('Local', 'aikit'),
                                'qdrant' => __('Qdrant', 'aikit'),
                            ],
                            $job->type ?? 'local',
                            null,
                            null,
                            null,
                            true
                        );
                        ?>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col">
                        <?php
                        $this->_text_box(
                            'aikit-name',
                            __('Name', 'aikit'),
                            null,
                            'text',
                            $job->name,
                            null,
                            null,
                            null,
                            null,
                            null,
                            true
                        );
                        ?>
                    </div>
                </div>


            </div>
            <div class="tab-pane fade" id="logs">
                <div class="alert alert-success my-4" role="alert">
                    <?php echo esc_html__( 'API errors can happen due to many reasons (such as API being down or rate limits have been exceeded, etc). In case of errors, do not worry, AIKit will keep retrying the job till it succeeds.', 'aikit' ); ?>
                </div>

                <?php

                if (count($logs) === 0) {
                    echo esc_html__( 'No logs found.', 'aikit' );
                }

                foreach ($logs as $log) {
                    ?>
                    <pre><code class="json"><?php echo esc_html(json_encode($log, JSON_PRETTY_PRINT)); ?></code></pre>
                    <?php
                }
                ?>
            </div>
            <?php
            if ($show_data) {
            ?>
            <div class="tab-pane fade" id="pairs">
                <?php

                if ($pairs_to_show == 'collected') {
                    $data = $job->collected_data == null ? [] : json_decode($job->collected_data, true);
                } else {
                    $data = $job->entered_data == null ? [] : json_decode($job->entered_data, true);
                }

                if (count($data) === 0) {
                    echo esc_html__( 'No data found.', 'aikit' );
                }

                $is_export_to_csv_hidden = count($data) === 0 ? 'd-none' : '';

                ?>
                <div class="aikit-export-as-csv-container <?php echo $is_export_to_csv_hidden; ?>">
                    <a href="#" class="aikit-export-as-csv"><i class="bi bi-download"></i> <?php echo esc_html__( 'Export as CSV', 'aikit' ); ?></a>
                </div>

                <p><?php
                    if (count($data) > 0) {
                        if ($pairs_to_show == 'generated') {
                            echo esc_html__('Here is the data collected so far.', 'aikit');
                        } else {
                            echo esc_html__('Here is the data you entered.', 'aikit');
                        }
                    }
                    ?>
                </p>
                <?php
                    if (count($data) > 0) {
                ?>
                    <div class="row mt-2">
                        <div class="col">
                            <h6><?php echo esc_html__( 'Data', 'aikit' ); ?></h6>
                        </div>
                    </div>
                <?php
                    }

                foreach ($data as $row) {
                    ?>
                    <div class="row mt-2 aikit-entered-data-container">
                        <div class="col">
                            <textarea class="form-control aikit-entered-data" rows="3" disabled><?php echo esc_html__($row); ?></textarea>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php } ?>

        </div>
        <?php
    }

    private function render_pending_data_status_job($job)
    {
        ?>
            <p>
                <?php echo esc_html__( 'Enter your embedding data. This data will be transformed into vectors later and stored in the embeddings store (Qdrant). In Chatbot, when your user asks a question, a similarity search will be done against this data to find the closest text to answer that question, then the result will be fed into a GPT model (of your choice) to answer that question using your data.', 'aikit' ); ?>
                <?php echo esc_html__( 'Each paragraph should be short and concise and discusses some information about your product or service.', 'aikit' ); ?>
            </p>

        <input type="hidden" id="aikit-embeddings-job-id" value="<?php echo $job->id; ?>" />

        <div class="row mt-4">
            <div class="col">
                <?php
                    $this->_radio_button_set(
                        'aikit-embeddings-data-source',
                        __('Choose the source of your embeddings data', 'aikit'),
                        [
                            'manual' => __('Manual', 'aikit'),
                            'posts' => __('Posts / Pages', 'aikit'),
                        ],
                        $job->data_source ?? 'manual'
                    );
                ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <div class="aikit-embeddings-data-manual">
                    <p>
                        <?php echo esc_html__( 'Please enter your embeddings data below. Keep writing and new inputs will be added automatically so you can fill in your data.', 'aikit' ); ?>
                    </p>
                    <div class="aikit-file-upload-container">
                        <div class="row mt-2">
                            <div class="col">
                                <a href="#" class="aikit-embeddings-data-file-upload-button"><i class="bi bi-download"></i> <?php echo esc_html__( 'Have a CSV file? Import it!', 'aikit' ); ?></a>
                                <input type="file" id="aikit-embeddings-data-file" accept=".csv" />
                            </div>
                        </div>
                    </div>

                    <div class="aikit-embeddings-data-manual-inputs">
                        <div class="row mt-2">
                            <div class="col">
                                <h6><?php echo esc_html__( 'Paragraphs', 'aikit' ); ?></h6>
                            </div>
                            <div class="col-2">
                            </div>
                        </div>

                        <?php

                        $entered_data = json_decode($job->entered_data);

                        if (!empty($entered_data)) {
                            foreach ($entered_data as $item) {
                                ?>
                                <div class="row mt-2 aikit-entered-data-container">
                                    <div class="col">
                                        <textarea class="form-control aikit-entered-data" rows="3"><?php echo esc_html__($item); ?></textarea>
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-sm btn-outline-danger aikit-remove-row" tabindex="-1"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <?php
                            }
                        }

                        ?>
                        <div class="row mt-2 aikit-entered-data-container">
                            <div class="col">
                                <textarea class="form-control aikit-entered-data" rows="3"></textarea>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-sm btn-outline-danger aikit-remove-row" tabindex="-1"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="aikit-embeddings-data-posts">
                    <p>
                        <?php echo esc_html__( 'AIKit allows you to pick posts and pages from your site to use as embeddings data. The content of these pages & posts will be divided into paragraphs, and AIKit will feed it into the vector database.', 'aikit' ); ?>

                        <?php echo esc_html__( 'You will have a chance to review and edit this data once it is divided into paragraphs and before storing it into the vector database.', 'aikit' ); ?>
                    </p>

                    <div class="row mt-4">
                        <div class="col">

                            <?php
                                $post_ids = $job->chosen_post_ids !== null ? json_decode($job->chosen_post_ids, true) : [];
                                $this->post_finder->render(
                                    $post_ids,
                                    esc_html__( 'Please select the posts/pages you want to use as embeddings data. You can filter by post type and search for a specific post/page.', 'aikit' ),
                                );
                             ?>
                        </div>
                    </div>
                 </div>
            </div>

        </div>

        <div class="row mt-3 aikit-token-count-row">
            <div class="col">
                <button href="#" class="aikit-count-tokens btn btn-sm btn-outline-secondary float-end m-2"><i class="bi bi-123"></i> <?php echo esc_html__( 'Count Tokens', 'aikit' ); ?></button>
                <div class="float-end me-2 aikit-token-count-container mt-3"><strong><?php echo esc_html__( 'Number of tokens:', 'aikit' ); ?></strong> <span class="aikit-token-count">~</span></div>
            </div>
        </div>

        <div class="row">
            <div class="col">

            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <button class="btn btn-primary aikit-create-embeddings-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-play-fill"></i> <?php echo esc_html__( 'Create Embeddings', 'aikit' ); ?></button>
                <button class="btn btn-primary aikit-preprocess-data-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-play-fill"></i> <?php echo esc_html__( 'Preprocess Data', 'aikit' ); ?></button>
                <button class="btn btn-outline-primary aikit-save-data-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-save"></i> <?php echo esc_html__( 'Save & Continue Later', 'aikit' ); ?></button>
            </div>
        </div>

        <?php

    }

    private function get_job_by_id($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;

        $job = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id)
        );

        return $job;
    }

    private function render_create_tab()
    {
        $qdrant_host = get_option( 'aikit_setting_qdrant_host' );
        $create_button_disabled = empty($qdrant_host) ? 'disabled' : '';

        ?>
        <form class="aikit-embeddings-form clearfix" id="aikit-embeddings-form-first-step" action="<?php echo get_site_url(); ?>/?rest_route=/aikit/embeddings/v1/create" method="post" >

            <p>
                <?php echo esc_html__( 'Creating an embedding is the first step in turning your data into vectors (numbers). This can allow you to do semantic search (search by meaning, not exact words), and that can be used to answer user questions with the most relevant data from your website. You can create embeddings from text data (manual input) or from posts and pages on your site.', 'aikit' ); ?>
            </p>

            <div class="row mb-2">
                <div class="col">
                    <?php
                    $this->_radio_button_set(
                        'aikit-embeddings-type',
                        __('Embeddings Storage Type', 'aikit'),
                        [
                            'local' => __('Local', 'aikit'),
                            'qdrant' => __('Qdrant', 'aikit'),
                        ],
                        $job->type ?? 'local',
                        __('Local embeddings are stored in your WordPress database. Qdrant embeddings are stored in the Qdrant vector database. Local storage is enough for small to moderate data sizes, but you can use Qdrant to store and process your embeddings in case your dataset is huge and experience increased Chatbot response times. Always start with "Local" and then upgrade to use Qdrant if necessary.', 'aikit')
                    );
                    ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col">
                    <?php
                        $this->_text_box(
                            'aikit-embeddings-name',
                            __('Name', 'aikit'),
                            null,
                            'text',
                            null,
                            null,
                            null,
                            null,
                            __('Enter a name for your embedding.', 'aikit'),
                            __('Please enter a name for your embedding.', 'aikit')
                        );
                    ?>
                </div>
            </div>

            <p>
                <small>
                    <i>
                        <?php echo esc_html__( 'AIKit will use the "text-embedding-ada-002" model for embeddings from OpenAI since it\'s the most recommended in terms of performance and the cheapest from cost perspective.', 'aikit' ); ?>
                    </i>
                </small>
            </p>

            <input type="hidden" id="aikit-qdrant-credentials-set" name="aikit-qdrant-credentials-set" value="<?php echo empty($qdrant_host) ? '0' : '1'; ?>" />

            <?php
            if (empty($qdrant_host)) {
                ?>
                    <div class="row mb-2 aikit-qdrant-credentials-warning">
                        <div class="col">
                        <p class="float-end">
                            <small>
                                <i>
                                    <?php echo esc_html__( 'Qdrant host is not set. In order to use embedding please set the Qdrant host (and API key if needed) in the ', 'aikit' ); echo '<a href="' . admin_url( 'admin.php?page=aikit&tab=qdrant' ) . '">' . esc_html__( 'AIKit settings page', 'aikit' ) . '</a>'; ?>
                                </i>
                            </small>
                        </p>
                        </div>
                    </div>
                <?php
            }
            ?>

            <button <?php echo $create_button_disabled ?> type="submit" id="aikit-embeddings-next-step-add-data" class="btn btn-primary float-end"><i class="bi bi-database"></i> <?php echo esc_html__( 'Add Data', 'aikit' ); ?></button>

        </form>

        <?php
    }

    private function render_listing_tab()
    {
        $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 25;
        $columns = [
            esc_html__('Name', 'aikit'),
            esc_html__('Status', 'aikit'),
            esc_html__('Date created', 'aikit'),
            esc_html__('Actions', 'aikit'),
        ];
        $html = '<table class="table" id="aikit-embeddings-jobs">
            <thead>
            <tr>';

        foreach ($columns as $column) {
            $html .= '<th scope="col">' . $column . '</th>';
        }

        $html .= '
            </tr>
            </thead>
            <tbody>';

        // get all jobs from DB
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;

        // prepared statement to prevent SQL injection with pagination
        $jobs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d, %d", ($paged - 1) * $per_page, $per_page));

        if (empty($jobs)) {
            $html .= '<tr>
                <td colspan="' . count($columns) . '">' . esc_html__('No entries found', 'aikit') . '</td>
            </tr>';
        }

        $page_url = get_admin_url() . 'admin.php?page=aikit_embeddings&action=view';
        $delete_url = get_site_url() . '/?rest_route=/aikit/embeddings/v1/delete';

        foreach ($jobs as $job) {
            $current_page_url = $page_url . '&job_id=' . $job->id;
            $html .= '<tr>
                <td>' . '<a href="' . $current_page_url . '">' . esc_html($job->name) . '</a></td>
                <td><span class="badge badge-pill badge-dark aikit-badge-active">' . $this->map_embeddings_status_map($job->status) . '</span></td>
                <td>' . (empty($job->date_created) ? '-' : aikit_date($job->date_created)) . '</td>               
                <td>
                    <a href="' . $page_url . '&job_id=' . $job->id . '" title="' . __('View', 'aikit') . '" class="aikit-embeddings-action" data-id="' . $job->id . '"><i class="bi bi-eye-fill"></i></a>
                    <a href="' . $delete_url . '" title="' . __('Delete', 'aikit') . '" class="aikit-embeddings-job-delete aikit-embeddings-action" data-confirm-message="' . __('Are you sure you want to delete this embeddings job?', 'aikit') . '" data-id="' . $job->id . '"><i class="bi bi-trash-fill"></i></a>
                </td>
            </tr>';
        }

        // pagination
        $total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_pages = ceil($total_jobs / $per_page);

        if ($total_pages > 1) {
            $html .= '<tr>
            <td colspan="' . count($columns) . '">';

            // previous page

            $html .= '<div class="aikit-jobs-pagination">';
            if ($paged > 1) {
                $html .= '<a href="' . $page_url . '&paged=' . ($paged - 1) . '">' . __('« Previous', 'aikit') . '</a>';
            }

            for ($i = 1; $i <= $total_pages; $i++) {
                // add class to current page
                $current_page_class = '';
                if ($paged == $i) {
                    $current_page_class = 'aikit-jobs-pagination-current';
                }

                $html .= '<a class="' . $current_page_class . '" href="' . $page_url . '&paged=' . $i . '" data-page="' . $i . '">' . $i . '</a>';
            }

            // next page
            if ($paged < $total_pages) {
                $html .= '<a href="' . $page_url . '&paged=' . ($paged + 1) . '">' . __('Next »', 'aikit') . '</a>';
            }

            $html .= '</div>';

            $html .= '</td>
            </tr>';
        }

        $html .= '</tbody>
        
        </table>';

        echo $html;
    }

    private function map_embeddings_status_map($status)
    {
        return [
            'new' => esc_html__('New', 'aikit'),
            'pending_data' => esc_html__('Pending data', 'aikit'),
            'collecting_data_from_posts' => esc_html__('Collecting data from posts', 'aikit'),
            'pending_final_approval_on_collected_data_pairs' => esc_html__('Pending final approval on collected data', 'aikit'),
            'creation_in_progress' => esc_html__('Creation in progress', 'aikit'),
            'completed' => esc_html__('Completed', 'aikit'),
            'failed' => esc_html__('Failed', 'aikit'),
        ][strtolower($status)];
    }

    public function do_db_migration()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . self::TABLE_NAME_EMBEDDINGS_JOBS;
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            status varchar(255) NOT NULL,
            name varchar(255) NOT NULL,
            index_name varchar(255) NOT NULL,
            openai_model_name varchar(255) NOT NULL,
            data_source varchar(255) DEFAULT NULL,
            chosen_post_ids MEDIUMTEXT NULL,
            entered_data LONGTEXT NULL,
            type varchar(255) DEFAULT 'qdrant',
            local_embedding_index LONGTEXT NULL,
            collected_data LONGTEXT NULL,
            date_created datetime DEFAULT NULL,
            date_modified datetime DEFAULT NULL,
            logs LONGTEXT NULL,
            is_running BOOLEAN DEFAULT FALSE,
            error_count INT DEFAULT 0,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function enqueue_scripts($hook)
    {
        if ( 'aikit_page_aikit_embeddings' !== $hook ) {
            return;
        }

        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_style( 'aikit_bootstrap_css', plugins_url( '../../css/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( '../../css/bootstrap-icons.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_embeddings_css', plugins_url( '../../css/embeddings.css', __FILE__ ), array(), $version );

        wp_enqueue_script( 'aikit_bootstrap_js', plugins_url('../../js/bootstrap.bundle.min.js', __FILE__ ), array(), $version );
        wp_enqueue_script( 'aikit_jquery_ui_js', plugins_url('../../js/jquery-ui.min.js', __FILE__ ), array('jquery'), $version );
        wp_enqueue_script( 'aikit_embeddings_js', plugins_url( '../../js/embeddings.js', __FILE__ ), array( 'jquery' ), $version );

        $this->post_finder->enqueue_scripts();
    }
}
