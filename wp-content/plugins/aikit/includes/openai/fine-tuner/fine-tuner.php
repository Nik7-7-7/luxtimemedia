<?php

use AIKit\Dependencies\duzun\hQuery;

class AIKIT_Fine_Tuner extends AIKIT_Page {
    const TABLE_NAME_FINE_TUNE_JOBS = 'aikit_fine_tune_jobs';
    private $post_finder = null;
    private $tokenizer = null;
    private $fine_tune_job_builder = null;

    // singleton instance

    private static $instance = null;

    // singleton constructor

    private function __construct() {

        add_action( 'rest_api_init', function () {
            register_rest_route( 'aikit/fine-tune/v1', '/create', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_create_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/fine-tune/v1', '/edit', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_edit_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/fine-tune/v1', '/count-tokens', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_token_count_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/fine-tune/v1', '/delete', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_delete_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

        });
        add_action('aikit_fine_tune', array($this, 'execute'));
        $this->post_finder = AIKIT_Post_Finder::get_instance();
        $this->tokenizer = AIKIT_Gpt3Tokenizer::getInstance();
        $this->fine_tune_job_builder = AIKIT_Fine_Tune_Job_Builder::get_instance();
    }

    public function activate_scheduler()
    {
        if (! wp_next_scheduled ( 'aikit_fine_tune')) {
            wp_schedule_event( time(), 'every_10_minutes', 'aikit_fine_tune');
        }
    }

    public function deactivate_scheduler()
    {
        wp_clear_scheduled_hook('aikit_fine_tune');
    }

    public function execute()
    {
        $this->execute_collecting_data_from_posts();
        $this->execute_generating_prompt_completion_pairs();
        $this->execute_training_in_progress();
    }

    private function execute_training_in_progress()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;

        $jobs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'training_in_progress' AND is_running = 0 ORDER BY date_created ASC");

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
                if (empty($job->openai_fine_tune_id)) {
                    $this->fine_tune_job_builder->build($job);

                    $wpdb->update(
                        $table_name,
                        array(
                            'date_modified' => current_time('mysql', true),
                            'is_running' => 0,
                        ),
                        array('id' => $job->id)
                    );
                } else { // check for status
                    $fine_tune_status_result = $this->fine_tune_job_builder->check_fine_tune_status($job);

                    $entity = array(
                        'date_modified' => current_time('mysql', true),
                        'is_running' => 0,
                    );

                    $fine_tune_status = $fine_tune_status_result['status'];

                    if ($fine_tune_status == 'succeeded') {
                        $entity['status'] = 'completed';
                        $entity['openai_model_name'] = $fine_tune_status_result['fine_tuned_model'];
                    }

                    $wpdb->update(
                        $table_name,
                        $entity,
                        array('id' => $job->id)
                    );
                }

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

    private function execute_collecting_data_from_posts() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;

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

                aikit_reconnect_db_if_needed();

                // save chosen post paragraphs
                $wpdb->update(
                    $table_name,
                    array(
                        'chosen_post_paragraphs' => json_encode($chosen_post_paragraphs),
                        'date_modified' => current_time( 'mysql', true ),
                        'is_running' => 0,
                        'status' => 'generating_prompt_completion_pairs'
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

                aikit_reconnect_db_if_needed();

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

    private function execute_generating_prompt_completion_pairs()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;

        $jobs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'generating_prompt_completion_pairs' AND is_running = 0 ORDER BY date_created ASC");

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

                // get chosen post paragraphs
                $chosen_post_paragraphs = json_decode($job->chosen_post_paragraphs);

                if (empty($chosen_post_paragraphs)) {
                    // no more paragraphs, mark job as pending_final_approval_on_generated_prompt_completion_pairs
                    $wpdb->update(
                        $table_name,
                        array(
                            'status' => 'pending_final_approval_on_generated_prompt_completion_pairs',
                            'date_modified' => current_time('mysql', true),
                            'is_running' => 0,
                        ),
                        array('id' => $job->id)
                    );
                    continue;
                }

                // loop until we get a paragraph that is not empty
                $first_paragraph = '';
                while (empty($first_paragraph) && !empty($chosen_post_paragraphs)) {
                    $first_paragraph = array_shift($chosen_post_paragraphs);
                }

                if (empty($first_paragraph)) {
                    // no more paragraphs, mark job as pending_final_approval_on_generated_prompt_completion_pairs
                    $wpdb->update(
                        $table_name,
                        array(
                            'status' => 'pending_final_approval_on_generated_prompt_completion_pairs',
                            'date_modified' => current_time('mysql', true),
                            'is_running' => 0,
                        ),
                        array('id' => $job->id)
                    );
                    continue;
                }

                // get prompt
                $prompt = $job->prompt_completion_generation_prompt;
                $paragraph_count = $job->prompt_completion_generation_count;
                $model = $job->prompt_completion_generation_model;

                $prompt = str_replace('[[paragraph]]', $first_paragraph, $prompt);
                $prompt = str_replace('[[number]]', $paragraph_count, $prompt);

                // generate completion

                $result = aikit_openai_text_generation_request($prompt, 800, 0.7, $model);

                $pairs = $this->extract_question_answer_pairs($result);

                // save pairs

                $current_pairs = $job->generated_prompt_completion_pairs == null ? [] : json_decode($job->generated_prompt_completion_pairs);
                $current_pairs = array_merge($current_pairs, $pairs);

                $wpdb->update(
                    $table_name,
                    array(
                        'generated_prompt_completion_pairs' => json_encode($current_pairs),
                        'chosen_post_paragraphs' => json_encode($chosen_post_paragraphs),
                        'date_modified' => current_time( 'mysql', true ),
                        'is_running' => 0,
                    ),
                    array('id' => $job->id)
                );

                break; // only do one job at a time to avoid rate limiting

            } catch (\Throwable $e) {
                // append to logs
                $logs = json_decode($job->logs);
                $logs[] = array(
                    'message' => $e->getMessage(),
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

                continue;
            }
        }
    }

    private function extract_question_answer_pairs($string)
    {
        ###['openai-fine-tune-pairs']

        $question_answer_pairs = array();

        // split by newline
        $lines = explode("\n", $string);
        $question = '';
        $answer = '';
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            if (substr($line, -1) === '?') {
                // question
                $question = $line;
            } else {
                // answer
                // add it to the answer string
                $answer .= $line . ' ';
            }

            if (!empty($question) && !empty($answer)) {
                $question_answer_pairs[] = array(
                    'prompt' => $this->cleanup_question($question),
                    'completion' => $this->cleanup_answer($answer),
                );
                $question = '';
                $answer = '';
            }
        }

        return $question_answer_pairs;
    }

    private function cleanup_question($question)
    {
        // remove any number at the start like 1. or 1)
        $question = preg_replace('/^\d+\.\s+/', '', $question);
        $question = preg_replace('/^\d+\)\s+/', '', $question);

        $question = preg_replace('/^Q:\s+/', '', $question);
        $question = preg_replace('/^Question:\s+/', '', $question);

        return trim($question);
    }

    private function cleanup_answer($answer)
    {
        // remove any dashes at the start
        $answer = preg_replace('/^-+/', '', $answer);
        // remove any A: or Answer: at the start
        $answer = preg_replace('/^A:\s+/', '', $answer);
        $answer = preg_replace('/^Answer:\s+/', '', $answer);

        return trim($answer);
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

        if (!empty($job->openai_fine_tune_id)) {
            $this->fine_tune_job_builder->cancel_fine_tune($job->openai_fine_tune_id);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;

        $wpdb->delete(
            $table_name,
            array('id' => $id)
        );

        return new WP_REST_Response(['success' => true], 200);
    }

    public function handle_token_count_request($data)
    {
        $pairs = $data['pairs'] ?? array();

        $count = 0;
        foreach ($pairs as $pair) {
            $count += $this->tokenizer->count($pair['prompt']) +  $this->tokenizer->count($pair['completion']);
        }

        $count *= 4; // assuming epoch is 4

        return new WP_REST_Response(['count' => $count], 200);
    }

    public function handle_create_request($data)
    {
        $model = $data['fine_tune_model'];
        $output_model_name = $data['fine_tune_output_model_name'];
        $prompt_stop_sequence = $data['fine_tune_prompt_stop_sequence'];
        $completion_stop_sequence = $data['fine_tune_completion_stop_sequence'];

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;
        $wpdb->insert(
            $table_name,
            array(
                'base_model' => $model,
                'output_model_name' => $output_model_name,
                'prompt_stop_sequence' => $prompt_stop_sequence,
                'completion_stop_sequence' => $completion_stop_sequence,
                'status' => 'pending_training_data',
                'chosen_post_ids' => '[]',
                'entered_prompt_completion_pairs' => '[]',
                'logs' => '[]',
                'date_created' => current_time( 'mysql', true ),
                'date_modified' => current_time( 'mysql', true )
            )
        );

        $job_id = $wpdb->insert_id;

        return new WP_REST_Response(array(
            'id' => $job_id,
            'redirect_url' => admin_url( 'admin.php?page=aikit_fine_tune&action=view&job_id=' . $job_id )
        ), 200);
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

        if (isset($data['prompt_completion_generation_model'])) {
            $entity['prompt_completion_generation_model'] = $data['prompt_completion_generation_model'];
        }

        if (isset($data['prompt_completion_generation_count'])) {
            $entity['prompt_completion_generation_count'] = $data['prompt_completion_generation_count'];
        }

        if (isset($data['prompt_completion_generation_prompt'])) {
            $entity['prompt_completion_generation_prompt'] = $data['prompt_completion_generation_prompt'];
        }

        if (isset($data['entered_prompt_completion_pairs'])) {
            $entity['entered_prompt_completion_pairs'] = json_encode($data['entered_prompt_completion_pairs']);
        }

        if (isset($data['training_data_source'])) {
            $entity['training_data_source'] = $data['training_data_source'];
        }

        $entity['date_modified'] = current_time( 'mysql', true );

        if (isset($data['next_step'])) {
            if (isset($data['training_data_source']) && $data['training_data_source'] == 'manual') {
                $entity['status'] = 'training_in_progress';
            } else {
                if ($job->status == 'pending_training_data') {
                    $entity['status'] = 'collecting_data_from_posts';
                } else if ($job->status == 'pending_final_approval_on_generated_prompt_completion_pairs') {
                    $entity['status'] = 'training_in_progress';
                }
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;
        $wpdb->update(
            $table_name,
            $entity,
            array(
                'id' => $job_id
            )
        );

        return new WP_REST_Response(array(
            'id' => $job_id,
            'redirect_url' => admin_url( 'admin.php?page=aikit_fine_tune&action=jobs' )
        ), 200);
    }

    public static function get_instance() {

        if ( self::$instance == null ) {

            self::$instance = new AIKIT_Fine_Tuner();
        }

        return self::$instance;
    }

    public function render()
    {
        $active_tab = isset( $_GET['action'] )  ? $_GET['action'] : 'jobs';

        $second_tab_title = isset($_GET['job_id']) ? esc_html__( 'View / Edit Job', 'aikit' ) : esc_html__( 'Create Fine-tune Job', 'aikit' );
        $second_tab_link = isset($_GET['job_id']) ? admin_url( 'admin.php?page=aikit_fine_tune&action=view&job_id=' . $_GET['job_id'] ) : admin_url( 'admin.php?page=aikit_fine_tune&action=create' );
        
        ?>
        <div class="wrap">
        <h1><?php echo esc_html__( 'Fine-tune Models', 'aikit' ); ?></h1>
        <p>
            <?php echo esc_html__( 'AIKit Fine-tuner is a tool that allows you to fine-tune your AI models. Fine-tuning a model allows you to feed in data about your product or service and train the model on it so that it can generate better results for your specific use case. You can use it to create a new model based on your existing model, or to improve your existing model by adding new data to it.', 'aikit' ); ?>
        </p>

        <ul class="nav nav-tabs aikit-fine-tuner-tabs">
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'jobs' ? 'active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=aikit_fine_tune&action=jobs' ); ?>"><?php echo esc_html__( 'Jobs', 'aikit' ); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $active_tab == 'create' || $active_tab == 'view' ? 'active' : ''; ?>" aria-current="page" href="<?php echo $second_tab_link ?>"><?php echo $second_tab_title; ?></a>
            </li>
        </ul>

            <div class="aikit-fine-tuner-content">
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

        if ($job->status == 'pending_training_data') {
            $this->render_pending_training_data_status_job($job);
        } else if ($job->status == 'generating_prompt_completion_pairs' || $job->status == 'collecting_data_from_posts') {
            $this->render_generating_prompt_completion_pairs_status_job($job);
        } else if ($job->status == 'training_in_progress') {
            $this->render_training_in_progress_status_job($job);
        } else if ($job->status == 'pending_final_approval_on_generated_prompt_completion_pairs') {
            $this->render_pending_final_approval_on_generated_prompt_completion_pairs_status_job($job);
        } else if ($job->status == 'completed') {
            $this->render_completed_status_job($job);
        }
    }

    private function render_generating_prompt_completion_pairs_status_job($job)
    {
        ?>
        <p class="aikit-notice-message">
            <i class="bi bi-hourglass-split"></i>
            <?php
                echo esc_html__( 'Please set back and relax while the prompt/completion pairs are being generated for your training data. Status of the job will change to reflect when the generation is done.', 'aikit');
            ?>
        </p>
        <?php

        $this->render_job_info_tabs($job, true);
    }

    private function render_training_in_progress_status_job($job)
    {
        ?>
        <p class="aikit-notice-message">
            <i class="bi bi-hourglass-split"></i>
            <?php
            echo esc_html__( 'Please set back and relax while the your model is being fine-tuned. Status of the job will change to reflect when this is done.', 'aikit');
            ?>
        </p>
        <?php

        $this->render_job_info_tabs($job, true, 'entered');
    }

    private function render_pending_final_approval_on_generated_prompt_completion_pairs_status_job($job)
    {
        ?>
        <p class="aikit-paragraph">
            <?php
            echo esc_html__( 'Please have a look at the generated prompt/completion pairs and approve them. Feel free to edit them as you see fit.', 'aikit');
            ?>
        </p>

        <input type="hidden" id="aikit-fine-tune-job-id" value="<?php echo $job->id; ?>" />

        <?php

        $pairs = $job->generated_prompt_completion_pairs == null ? [] : json_decode($job->generated_prompt_completion_pairs, true);

        if (count($pairs) === 0) {
            echo esc_html__( 'No pairs found.', 'aikit' );
        }

        ?>
        <div class="row mt-5">
            <div class="col">
                <h6><?php echo esc_html__( 'Prompts', 'aikit' ); ?></h6>
            </div>
            <div class="col-5">
                <h6><?php echo esc_html__( 'Completions', 'aikit' ); ?></h6>
            </div>
            <div class="col-2">

            </div>
        </div>

        <div class="tab-pane aikit-fine-tune-training-data-manual-inputs">
        <?php

        foreach ($pairs as $pair) {
            ?>
            <div class="row mt-2 aikit-pair">
                <div class="col-5">
                    <textarea class="form-control aikit-prompt" rows="3"><?php echo esc_html__($pair['prompt']); ?></textarea>
                </div>
                <div class="col-5">
                    <textarea class="form-control aikit-completion" rows="3"><?php echo esc_html__($pair['completion']); ?></textarea>
                </div>
                <div class="col-2">
                    <button class="btn btn-sm btn-outline-danger aikit-remove-pair" tabindex="-1"><i class="bi bi-trash"></i></button>
                </div>
            </div>
            <?php
        }
        ?>
        </div>

        <button class="btn btn-primary aikit-fine-tune-approve-and-start-fine-tuning-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-play-fill"></i> <?php echo esc_html__( 'Start Fine-tuning', 'aikit' ); ?></button>

        <?php

        $this->render_job_info_tabs($job);
    }

    private function render_completed_status_job($job)
    {
        ?>
        <p class="aikit-notice-message">
            <i class="bi bi-check-circle-fill"></i>
            <?php
            echo esc_html__( 'Congratulations! Your model is ready to be used. You you should see it in the list of models in the settings page or in the Chatbot setting page.', 'aikit');
            echo '<br />';
            echo esc_html__('For best results, please make sure to set the same "Prompt Stop Sequence" and "Completion Stop Sequence" in the settings page or Chatbot settings page when using this model.', 'aikit');
            ?>
        </p>
        <?php

        $this->render_job_info_tabs($job, true, 'entered');
    }

    private function render_job_info_tabs($job, $show_generate_prompt_completion_pairs = false, $pairs_to_show = 'generated')
    {
        ?>
        <ul class="nav nav-tabs aikit-fine-tuner-job-info-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#job-info" data-bs-toggle="tab"><?php echo esc_html__( 'Job Info', 'aikit' ); ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#logs" data-bs-toggle="tab"><?php echo esc_html__( 'Logs', 'aikit' ); ?></a>
            </li>
            <?php

            if ($show_generate_prompt_completion_pairs) {
            ?>
                <li class="nav-item">
                    <a class="nav-link" href="#pairs" data-bs-toggle="tab"><?php echo esc_html__( 'Prompt / Completion Pairs', 'aikit' ); ?></a>
                </li>

            <?php } ?>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="job-info">

                <div class="row mt-2">
                    <div class="col">
                        <span class=""><?php echo __('Status', 'aikit'); ?></span>: <span class="badge badge-pill badge-dark aikit-badge-active ms-1"><?php echo $this->map_fine_tune_status_map($job->status) ?></span>
                    </div>
                </div>

                <?php
                    if (!empty($job->openai_model_name)) {
                        ?>
                        <div class="row mt-2">
                            <div class="col">
                                <?php
                                $this->_text_box(
                                    'aikit-fine-tune-output-model-name',
                                    __('Model Name', 'aikit'),
                                    null,
                                    'text',
                                    $job->openai_model_name,
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
                        <?php
                    } else {
                        ?>
                        <div class="row mt-2">
                            <div class="col">
                                <?php
                                $this->_text_box(
                                    'aikit-fine-tune-output-model-name',
                                    __('Model Name Suffix', 'aikit'),
                                    null,
                                    'text',
                                    $job->output_model_name,
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
                        <?php
                    }
                ?>

                <div class="row mt-2">
                    <div class="col">
                        <?php
                            $this->_text_box(
                                'aikit-fine-tune-prompt-stop-sequence',
                                __('Prompt Stop Sequence', 'aikit'),
                                null,
                                'text',
                                $job->prompt_stop_sequence,
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
                <div class="row mt-2">
                    <div class="col">
                        <?php
                            $this->_text_box(
                                'aikit-fine-tune-completion-stop-sequence',
                                __('Completion Stop Sequence', 'aikit'),
                                null,
                                'text',
                                $job->completion_stop_sequence,
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

                <div class="row mt-2">
                    <div class="col">
                        <?php
                        $this->_text_box(
                            'aikit-fine-tune-model',
                            __('Base Model', 'aikit'),
                            null,
                            'text',
                            $job->base_model,
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

                <div class="row mt-2">
                    <div class="col">
                        <?php
                            $this->_text_box(
                                'aikit-fine-tune-training-data-source',
                                __('Source of your training data', 'aikit'),
                                null,
                                'text',
                                $job->training_data_source,
                                null,
                                null,
                                null,
                                null,
                                null,
                                true
                            );

                            if ($job->training_data_source == 'posts') {

                                ?>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col">
                        <?php
                                $this->_text_box(
                                    'aikit-fine-question-prompt-completion-generation-model',
                                    __('Prompt / Completion Generation Model', 'aikit'),
                                    null,
                                    'text',
                                    $job->prompt_completion_generation_model,
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
                <div class="row mt-2">
                    <div class="col">
                        <?php

                                $this->_text_box(
                                    'aikit-fine-tune-prompt-completion-generation-count',
                                    __('How Many Prompt/Completion Pairs To Generate Per Paragraph', 'aikit'),
                                    null,
                                    'number',
                                    $job->prompt_completion_generation_count,
                                    1,
                                    5,
                                    1,
                                    '',
                                    null,
                                    true
                                );
                            }
                        ?>
                        </div>
                </div>
            </div>
            <div class="tab-pane fade" id="logs">
                <div class="alert alert-success my-4" role="alert">
                    <?php echo esc_html__( 'API errors can happen due to many reasons (such as API being down or rate limits have been exceeded, etc). In case of errors, do not worry, AIKit will keep retrying the job till it succeeds.', 'aikit' ); ?>
                </div>

                <?php

                $logs = json_decode($job->logs, true);

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
            if ($show_generate_prompt_completion_pairs) {
            ?>
            <div class="tab-pane fade" id="pairs">
                <div class="aikit-export-as-csv-container">
                    <a href="#" class="aikit-export-as-csv"><i class="bi bi-download"></i> <?php echo esc_html__( 'Export as CSV', 'aikit' ); ?></a>
                </div>
                <?php

                if ($pairs_to_show == 'generated') {
                    $pairs = $job->generated_prompt_completion_pairs == null ? [] : json_decode($job->generated_prompt_completion_pairs, true);
                } else {
                    $pairs = $job->entered_prompt_completion_pairs == null ? [] : json_decode($job->entered_prompt_completion_pairs, true);
                }

                if (count($pairs) === 0) {
                    echo esc_html__( 'No pairs found.', 'aikit' );
                }

                ?>
                <p><?php if ($pairs_to_show == 'generated') {
                        if (count($pairs) > 0) {
                            echo esc_html__('Here are the prompt/completion pairs generated so far.', 'aikit');
                        }
                    } else {
                        echo esc_html__('Here are the prompt/completion pairs you entered.', 'aikit');
                    }
                    ?>
                </p>
                <?php
                    if (count($pairs) > 0) {
                ?>
                    <div class="row mt-2">
                        <div class="col">
                            <h6><?php echo esc_html__( 'Prompts', 'aikit' ); ?></h6>
                        </div>
                        <div class="col">
                            <h6><?php echo esc_html__( 'Completions', 'aikit' ); ?></h6>
                        </div>
                    </div>
                <?php
                    }

                foreach ($pairs as $pair) {
                    ?>
                    <div class="row mt-2 aikit-pair">
                        <div class="col">
                            <textarea class="form-control aikit-prompt" rows="3" disabled><?php echo esc_html__($pair['prompt']); ?></textarea>
                        </div>
                        <div class="col">
                            <textarea class="form-control aikit-completion" rows="3" disabled><?php echo esc_html__($pair['completion']); ?></textarea>
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

    private function render_pending_training_data_status_job($job)
    {
        $key = get_option('aikit_setting_openai_key');

        if (empty($key)) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php echo esc_html__( 'Please set your OpenAI API key in the AIKit settings page in order to use Chatbot.', 'aikit' ); ?>
                </p>
            </div>
            <?php
        }

        $models = aikit_rest_openai_get_available_models();
        $models = $models === false ? [] : $models;

        if (empty($models)) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php echo esc_html__( 'Please make sure you are using a valid OpenAI API key to be able to load all available models.', 'aikit' ); ?>
                </p>
            </div>
            <?php

            $models = aikit_openai_get_default_model_list();
        }

        $models = array_combine($models, $models);

        ?>
            <p>
                <?php echo esc_html__( 'OpenAI expects the training data to be formatted in a prompt/completion pair format. This will be used to fine-tune the model, where each prompt is kinda like a question, and the completion could be the answer to that question.', 'aikit' ); ?>
                <?php echo esc_html__( 'Each prompt (question) could be a question about your product or service, where the completion is the best (answer) response to that question.', 'aikit' ); ?>
            </p>
            <p>
                <strong><?php echo esc_html__( 'Important:', 'aikit' ); ?></strong>
                <?php echo esc_html__( 'OpenAI recommends 500 pairs of prompts/completions for best fine-tuning results.', 'aikit' ); ?>
                <?php echo esc_html__( 'For more information, please refer to the', 'aikit' ); ?> <a href="https://platform.openai.com/docs/guides/fine-tuning" target="_blank"><?php echo esc_html__( 'OpenAI fine-tuning guide', 'aikit' ); ?></a>.
            </p>

        <input type="hidden" id="aikit-fine-tune-job-id" value="<?php echo $job->id; ?>" />

        <div class="row mt-4">
            <div class="col">
                <?php
                    $this->_radio_button_set(
                        'aikit-fine-tune-training-data-source',
                        __('Choose the source of your training data', 'aikit'),
                        [
                            'manual' => __('Manual', 'aikit'),
                            'posts' => __('Posts / Pages', 'aikit'),
                        ],
                        $job->training_data_source ?? 'manual'
                    );
                ?>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <div class="aikit-fine-tune-training-data-manual">
                    <p>
                        <?php echo esc_html__( 'Please enter your training data below. Keep writing and new inputs will be added automatically to fill in your data.', 'aikit' ); ?>
                    </p>
                    <div class="aikit-file-upload-container">
                        <div class="row mt-2">
                            <div class="col">
                                <a href="#" class="aikit-fine-tune-training-data-file-upload-button"><i class="bi bi-download"></i> <?php echo esc_html__( 'Have a CSV file? Import it!', 'aikit' ); ?></a>
                                <input type="file" id="aikit-fine-tune-training-data-file" accept=".csv" />
                            </div>
                        </div>
                    </div>

                    <div class="aikit-fine-tune-training-data-manual-inputs">
                        <div class="row mt-2">
                            <div class="col-5">
                                <h6><?php echo esc_html__( 'Prompts', 'aikit' ); ?></h6>
                            </div>
                            <div class="col-5">
                                <h6><?php echo esc_html__( 'Completions', 'aikit' ); ?></h6>
                            </div>
                            <div class="col-2">
                            </div>
                        </div>

                        <?php

                        $prompt_completion_pairs = json_decode($job->entered_prompt_completion_pairs);

                        if (!empty($prompt_completion_pairs)) {
                            foreach ($prompt_completion_pairs as $pair) {
                                ?>
                                <div class="row mt-2 aikit-pair">
                                    <div class="col-5">
                                        <textarea class="form-control aikit-prompt" rows="3"><?php echo esc_html__($pair->prompt); ?></textarea>
                                    </div>
                                    <div class="col-5">
                                        <textarea class="form-control aikit-completion" rows="3"><?php echo esc_html__($pair->completion); ?></textarea>
                                    </div>
                                    <div class="col-2">
                                        <button class="btn btn-sm btn-outline-danger aikit-remove-pair" tabindex="-1"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                                <?php
                            }
                        }

                        ?>
                        <div class="row mt-2 aikit-pair">
                            <div class="col-5">
                                <textarea class="form-control aikit-prompt" rows="3"></textarea>
                            </div>
                            <div class="col-5">
                                <textarea class="form-control aikit-completion" rows="3"></textarea>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-sm btn-outline-danger aikit-remove-pair" tabindex="-1"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="aikit-fine-tune-training-data-posts">
                    <p>
                        <?php echo esc_html__( 'AIKit allows you to pick posts and pages from your site to use as training data. The content of these pages & posts will be divided into paragraphs, and AIKit will generate prompt/completion pairs based on them by using the model you select below.', 'aikit' ); ?>

                        <?php echo esc_html__( 'These prompt/completion pairs (question & answers about your content) will be put together and sent to OpenAI as the training data to fine-tune your model. You will have a chance to review and edit these pairs once they are generated and before the fine-tuning process starts in the next step.', 'aikit' ); ?>
                    </p>

                    <div class="row mt-2">
                        <div class="col">
                            <?php
                            $this->_drop_down(
                                'aikit-fine-question-prompt-completion-generation-model',
                                __('Prompt/Completion Generation Model', 'aikit'),
                                $models,
                                $job->prompt_completion_generation_model ?? 'gpt-3.5-turbo',
                                __('As part of preparing your training data, this model will be used to generate one or more prompt/completion pairs, which will be used to train the final model. It\'s highly recommended to choose a capable model here, like "gpt-3.5-turbo" or better in order for the generated prompt/completion pairs to be as good as possible.', 'aikit')
                            );
                            ?>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col">
                            <?php
                            $this->_text_box(
                                'aikit-fine-tune-prompt-completion-generation-count',
                                __('How Many Prompt/Completion Pairs To Generate Per Paragraph', 'aikit'),
                                null,
                                'number',
                                $job->prompt_completion_generation_count ?? 2,
                                1,
                                5,
                                1,
                                __('Choose how many prompt/completion pairs to generate per paragraph. The more pairs you generate, the more training data you will have which will improve the quality of your fine-tuned model, but it will affect the time it takes to fine-tune your model and the costs of that.', 'aikit')
                            );
                            ?>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col">
                            <?php
                            $this->_text_area(
                                'aikit-fine-tune-generation-prompt',
                                __('Generation Prompt', 'aikit'),
                                $job->prompt_completion_generation_prompt ?? "Generate [[number]] short question/answer pairs about the following paragraph:\n\n[[paragraph]]\n---\nEach question and each each answer should be written in a new line.\nQuestion/answer pairs:\n",
                                __('This prompt will be used to generate the prompt/completion pairs. You can use the following variables in your prompt: [[number]] (the number of pairs to generate), [[paragraph]] (the paragraph to generate pairs for).', 'aikit')
                            );
                            ?>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col">

                            <?php
                                $post_ids = $job->chosen_post_ids !== null ? json_decode($job->chosen_post_ids, true) : [];
                                $this->post_finder->render(
                                    $post_ids,
                                    esc_html__( 'Please select the posts/pages you want to use as training data. You can filter by post type and search for a specific post/page.', 'aikit' ),
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
                <div class="float-end me-2 aikit-token-count-container mt-3"><strong><?php echo esc_html__( 'Number of training tokens:', 'aikit' ); ?></strong> <span class="aikit-token-count">~</span></div>
            </div>
        </div>

        <div class="row">
            <div class="col">

            </div>
        </div>

        <div class="row aikit-token-count-description">
            <div class="col">
                <small class="float-end me-2"><?php echo esc_html__( 'Token count is multiplied by 4 to reflect actual tokens used in training (with epoch=4). You can use the number of tokens to calculate the costs you will pay to fine-tune your model. Check ', 'aikit' ); ?> <a href="https://openai.com/pricing" target="_blank"><?php echo esc_html__( 'OpenAI pricing', 'aikit' ); ?></a> <?php echo esc_html__( 'for more information.', 'aikit' ); ?></small>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col">
                <button class="btn btn-primary aikit-fine-tune-start-fine-tuning-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-play-fill"></i> <?php echo esc_html__( 'Start Fine-tuning', 'aikit' ); ?></button>
                <button class="btn btn-primary aikit-fine-tune-preprocess-data-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-play-fill"></i> <?php echo esc_html__( 'Preprocess Data', 'aikit' ); ?></button>
                <button class="btn btn-outline-primary aikit-fine-tune-save-training-data-button float-end m-2" data-job-id="<?php echo $job->id; ?>"><i class="bi bi-save"></i> <?php echo esc_html__( 'Save & Continue Later', 'aikit' ); ?></button>
            </div>
        </div>

        <?php

    }

    private function get_job_by_id($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;
        $job = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        return $job;
    }

    private function render_create_tab()
    {
        $models = [
            'davinci',
            'curie',
            'babbage',
            'ada',
        ];

        $key = get_option('aikit_setting_openai_key');

        $models_that_can_be_fined_tuned = aikit_rest_openai_get_available_models('fine-tuning');

        if ($models_that_can_be_fined_tuned === false) {
            $models_that_can_be_fined_tuned = [];
        }

        $models = array_unique(array_merge($models, $models_that_can_be_fined_tuned));

        $models = array_combine($models, $models);

        ?>
        <form class="aikit-fine-tune-form clearfix" id="aikit-fine-tune-form-first-step" action="<?php echo get_site_url(); ?>/?rest_route=/aikit/fine-tune/v1/create" method="post" >

            <p>
                <?php echo esc_html__( 'Fine-tuning allows you to train your own AI model based on an existing model. You can use this feature to create a model that is more specific to your needs, for example: to answer questions about your products or services.', 'aikit' ); ?>
            </p>

            <div class="row mb-2 mt-4">
                <div class="col">
                    <?php
                        $this->_drop_down(
                            'aikit-fine-tune-model',
                            __('Base Model', 'aikit'),
                            $models,
                            'davinci',
                            __('Select a model as a base for your fine-tuning. The base model used in fine-tuning will greatly affect the quality of the results you will get. "davinci" is currently the most capable model while also being the most expensive to fine-tune. Please around and try different models to find the best model for your case.', 'aikit')
                        );
                    ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <?php
                        $this->_text_box(
                            'aikit-fine-tune-output-model-name',
                            __('Model Name Suffix', 'aikit'),
                            null,
                            'text',
                            null,
                            null,
                            null,
                            null,
                            __('Enter a suffix name for your fine-tuned model. This will be used along with the base model name to generate a unique name for your model automatically.', 'aikit'),
                            __('Please enter a name for your fine-tuned model', 'aikit')
                        );
                    ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <?php
                        $this->_text_box(
                            'aikit-fine-tune-prompt-stop-sequence',
                            __('Prompt Stop Sequence', 'aikit'),
                            null,
                            'text',
                            '->',
                            null,
                            null,
                            null,
                            __('Enter a sequence of characters that will be used to stop the prompt. Please leave default value if you are not sure what to enter.', 'aikit'),
                            __('Please enter a sequence of characters that will be used to stop the prompt', 'aikit')
                        );
                    ?>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <?php
                        $this->_text_box(
                            'aikit-fine-tune-completion-stop-sequence',
                            __('Completion Stop Sequence', 'aikit'),
                            null,
                            'text',
                            'END',
                            null,
                            null,
                            null,
                            __('Enter a sequence of characters that will be used to stop the completion. Please leave default value if you are not sure what to enter.', 'aikit'),
                            __('Please enter a sequence of characters that will be used to stop the completion', 'aikit')
                        );
                    ?>
                </div>
            </div>

            <button type="submit" id="aikit-fine-tune-next-step-add-data" class="btn btn-primary float-end"><i class="bi bi-database"></i> <?php echo esc_html__( 'Add Training Data', 'aikit' ); ?></button>


        </form>

        <?php
    }

    private function render_listing_tab()
    {
        $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 25;
        $columns = [
            esc_html__('Model Name Suffix', 'aikit'),
            esc_html__('Model Name', 'aikit'),
            esc_html__('Base Model', 'aikit'),
            esc_html__('Status', 'aikit'),
            esc_html__('Date created', 'aikit'),
            esc_html__('Actions', 'aikit'),
        ];
        $html = '<table class="table" id="aikit-fine-tune-jobs">
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
        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;

        // prepared statement to prevent SQL injection with pagination
        $jobs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d, %d", ($paged - 1) * $per_page, $per_page));

        if (empty($jobs)) {
            $html .= '<tr>
                <td colspan="' . count($columns) . '">' . esc_html__('No entries found', 'aikit') . '</td>
            </tr>';
        }

        $page_url = get_admin_url() . 'admin.php?page=aikit_fine_tune&action=view';
        $delete_url = get_site_url() . '/?rest_route=/aikit/fine-tuner/v1/delete';

        foreach ($jobs as $job) {
            $current_page_url = $page_url . '&job_id=' . $job->id;
            $html .= '<tr>
                <td>' . '<a href="' . $current_page_url . '">' . esc_html($job->output_model_name) . '</a></td>
                <td>' . '<a href="' . $current_page_url . '">' . esc_html($job->openai_model_name ?? '-') . '</a></td>
                <td>' . $job->base_model . '</td>
                <td><span class="badge badge-pill badge-dark aikit-badge-active">' . $this->map_fine_tune_status_map($job->status) . '</span></td>
                <td>' . (empty($job->date_created) ? '-' : aikit_date($job->date_created)) . '</td>               
                <td>
                    <a href="' . $page_url . '&job_id=' . $job->id . '" title="' . __('View', 'aikit') . '" class="aikit-fine-tune-action" data-id="' . $job->id . '"><i class="bi bi-eye-fill"></i></a>
                    <a href="' . $delete_url . '" title="' . __('Delete', 'aikit') . '" class="aikit-fine-tune-job-delete aikit-fine-tune-action" data-confirm-message="' . __('Are you sure you want to delete this fine-tune job?', 'aikit') . '" data-id="' . $job->id . '"><i class="bi bi-trash-fill"></i></a>
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

    private function map_fine_tune_status_map($status)
    {
        return [
            'new' => esc_html__('New', 'aikit'),
            'pending_training_data' => esc_html__('Pending training data', 'aikit'),
            'collecting_data_from_posts' => esc_html__('Collecting data from posts', 'aikit'),
            'generating_prompt_completion_pairs' => esc_html__('Generating prompt completion pairs', 'aikit'),
            'pending_final_approval_on_generated_prompt_completion_pairs' => esc_html__('Pending final approval on generated prompt completion pairs', 'aikit'),
            'training_in_progress' => esc_html__('Training in progress', 'aikit'),
            'completed' => esc_html__('Completed', 'aikit'),
        ][strtolower($status)];
    }

    public function do_db_migration()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . self::TABLE_NAME_FINE_TUNE_JOBS;
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            status varchar(255) NOT NULL,
            type varchar(255) NOT NULL,
            openai_fine_tune_id varchar(255) DEFAULT NULL,
            openai_training_file_id varchar(255) DEFAULT NULL,
            openai_model_name varchar(255) DEFAULT NULL,
            base_model varchar(255) DEFAULT NULL,
            output_model_name varchar(255) DEFAULT NULL,
            prompt_stop_sequence varchar(255) DEFAULT NULL,
            completion_stop_sequence varchar(255) DEFAULT NULL,
            training_data_source varchar(255) DEFAULT NULL,
            chosen_post_ids MEDIUMTEXT NULL,
            chosen_post_paragraphs MEDIUMTEXT NULL,
            entered_prompt_completion_pairs LONGTEXT NULL,
            generated_prompt_completion_pairs LONGTEXT NULL,
            prompt_completion_generation_model varchar(255) DEFAULT NULL,
            prompt_completion_generation_count mediumint(9) DEFAULT NULL,
            prompt_completion_generation_prompt mediumtext DEFAULT NULL,
            description TEXT NULL,
            date_created datetime DEFAULT NULL,
            date_modified datetime DEFAULT NULL,
            logs LONGTEXT NULL,
            is_running BOOLEAN DEFAULT FALSE,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function enqueue_scripts($hook)
    {
        if ( 'aikit_page_aikit_fine_tune' !== $hook ) {
            return;
        }

        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_style( 'aikit_bootstrap_css', plugins_url( '../../css/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( '../../css/bootstrap-icons.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_fine_tuner_css', plugins_url( '../../css/fine-tuner.css', __FILE__ ), array(), $version );

        wp_enqueue_script( 'aikit_bootstrap_js', plugins_url('../../js/bootstrap.bundle.min.js', __FILE__ ), array(), $version );
        wp_enqueue_script( 'aikit_jquery_ui_js', plugins_url('../../js/jquery-ui.min.js', __FILE__ ), array('jquery'), $version );
        wp_enqueue_script( 'aikit_fine_tuner_js', plugins_url( '../../js/fine-tuner.js', __FILE__ ), array( 'jquery' ), array(), $version );

        $this->post_finder->enqueue_scripts();
    }
}
