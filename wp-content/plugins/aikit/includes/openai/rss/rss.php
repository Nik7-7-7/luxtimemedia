<?php

class AIKIT_RSS {
    const TABLE_NAME_RSS_JOBS = 'aikit_rss_jobs';
    const TABLE_NAME_RSS_URLS = 'aikit_rss_urls';

    // singleton

    private static $instance = null;

    private $repurposer = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_RSS();
        }
        return self::$instance;
    }

    public function  __construct()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'aikit/rss/v1', '/job', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/rss/v1', '/is-valid', array(
                'methods' => 'POST',
                'callback' => array($this, 'is_valid_rss'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/rss/v1', '/delete', array(
                'methods' => 'POST',
                'callback' => array($this, 'delete_job'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/rss/v1', '/reset-prompts', array(
                'methods' => 'POST',
                'callback' => array($this, 'reset_prompts'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));
        });

        add_action('aikit_rss', array($this, 'execute'));
        $this->repurposer = AIKIT_Repurposer::get_instance();
    }

    public function reset_prompts($data)
    {
        $lang = get_option('aikit_setting_openai_language', 'en');
        delete_option('aikit_rss_prompts_' . $lang);

        return new WP_REST_Response(array(
            'success' => true,
        ));
    }

    public function activate_scheduler()
    {
        if (! wp_next_scheduled ( 'aikit_rss')) {
            wp_schedule_event( time(), 'every_10_minutes', 'aikit_rss');
        }
    }

    public function deactivate_scheduler()
    {
        wp_clear_scheduled_hook('aikit_rss');
    }

    public function is_valid_rss($data)
    {
        $rss = fetch_feed($data['url']);

        if (is_wp_error($rss)) {
            return new WP_REST_Response([
                'valid' => false,
                'message' => __('Invalid RSS feed.', 'aikit'),
            ], 200);
        }

        return new WP_REST_Response([
            'valid' => true,
            'message' => __('Valid RSS feed.', 'aikit'),
        ], 200);
    }

    public function handle_request($data)
    {
        if ($data['save_prompts']) {
            $selected_language = aikit_get_language_used();
            update_option('aikit_rss_prompts_' . $selected_language, json_encode($data['prompts']));
        }

        if (isset ($data['id'])) {
            return $this->edit_job($data);
        }

        return $this->add_job($data);
    }

    private function add_job($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_JOBS;

        $entity = array(
            'rss_url' => $data['url'],
            'include_featured_image' => boolval($data['include_featured_image']),
            'is_active' => 1,
            'number_of_articles' => $data['number_of_articles'],
            'output_post_type' => $data['post_type'],
            'output_post_status' => $data['post_status'],
            'output_post_author' => get_current_user_id(),
            'output_post_category' => $data['post_category'],
            'prompts' => json_encode($data['prompts']),
            'date_created' => current_time( 'mysql', true ),
            'date_modified' => current_time( 'mysql', true ),
            'refresh_interval' => $data['refresh_interval'],
            'generation_time_padding' => intval($data['generation_time_padding']),
            'logs' => '[]',
            'model' => $data['model'] ?? null,
        );

        $result = $wpdb->insert(
            $table_name,
            $entity
        );

        $insert_id = $result === false ? false : $wpdb->insert_id;

        if ($insert_id) {
            return new WP_REST_Response([
                'message' => __('Job created.', 'aikit'),
                'url' => admin_url('admin.php?page=aikit_rss&action=jobs&job_id=' . $insert_id),
            ], 200);
        }

        return new WP_REST_Response([
            'message' => __('Failed to create job.', 'aikit'),
        ], 500);
    }

    private function edit_job($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_JOBS;

        $entity = array(
            'rss_url' => $data['url'],
            'include_featured_image' => boolval($data['include_featured_image']),
            'is_active' => $data['is_active'],
            'number_of_articles' => $data['number_of_articles'],
            'output_post_type' => $data['post_type'],
            'output_post_status' => $data['post_status'],
            'output_post_author' => get_current_user_id(),
            'output_post_category' => $data['post_category'],
            'prompts' => json_encode($data['prompts']),
            'date_modified' => current_time( 'mysql', true ),
            'refresh_interval' => $data['refresh_interval'],
            'next_refresh' => current_time( 'mysql', true ),
            'generation_time_padding' => intval($data['generation_time_padding']),
            'model' => $data['model'] ?? null,
        );

        $result = $wpdb->update(
            $table_name,
            $entity,
            array('id' => $data['id'])
        );

        if ($result) {
            return new WP_REST_Response([
                'message' => __('Job updated.', 'aikit'),
                'url' => admin_url('admin.php?page=aikit_rss&action=jobs&job_id=' . $data['id']),
            ], 200);
        }

        return new WP_REST_Response([
            'message' => __('Failed to update job.', 'aikit'),
        ], 500);
    }

    public function delete_job($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_JOBS;

        $result = $wpdb->delete(
            $table_name,
            array('id' => $data['id'])
        );

        // delete all associated urls
        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_URLS;

        $result = $wpdb->delete(
            $table_name,
            array('rss_job_id' => $data['id'])
        );

        $this->repurposer->delete_rss_repurpose_jobs($data['id']);

        return new WP_REST_Response([
            'message' => __('Job deleted.', 'aikit'),
        ], 200);
    }

    public function do_db_migration()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name_rss_jobs = $wpdb->prefix . self::TABLE_NAME_RSS_JOBS;
        $table_name_rss_urls = $wpdb->prefix . self::TABLE_NAME_RSS_URLS;
        $sql = "CREATE TABLE $table_name_rss_jobs (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ai_provider varchar(255) DEFAULT 'openai',
            rss_url mediumtext NOT NULL,
            output_post_type varchar(255) NOT NULL,
            output_post_status varchar(255) NOT NULL,
            output_post_author mediumint(9) NOT NULL,
            output_post_category mediumint(9) NOT NULL,
            number_of_articles mediumint(9) NOT NULL,
            include_featured_image BOOLEAN NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT TRUE,
            prompts TEXT NOT NULL,
            date_created datetime DEFAULT NULL,
            date_modified datetime DEFAULT NULL,
            next_refresh datetime DEFAULT NULL,
            refresh_interval varchar(255) NOT NULL,
            is_running BOOLEAN NOT NULL DEFAULT FALSE,
            generation_time_padding mediumint(9) NOT NULL DEFAULT 30,
            logs TEXT NOT NULL,
            model varchar(255) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $sql = "CREATE TABLE $table_name_rss_urls (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            rss_job_id mediumint(9) NOT NULL,
            url mediumtext NOT NULL,
            repurpose_job_id mediumint(9) DEFAULT NULL,
            date_created datetime DEFAULT NULL,
            date_modified datetime DEFAULT NULL,
            PRIMARY KEY (id),
            INDEX (rss_job_id),
            UNIQUE (rss_job_id, url(255))
        ) $charset_collate;";

        dbDelta( $sql );
    }

    public function render()
    {
        $cron_url = get_site_url() . '/wp-cron.php';
        $active_tab = isset($_GET['action']) ? $_GET['action'] : 'create';

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'RSS Automatic Content Generation', 'aikit' ); ?></h1>
            <p>
                <?php echo esc_html__( 'AIKit RSS automatic generation jobs allow you to automatically create new posts out of articles extracted from RSS feeds. Whenever an article is added to RSS feed, a job will be scheduled to generate a post based on it in your site. ', 'aikit' ); ?>
                <?php echo esc_html__( 'Please review and edit before publishing for best results. This is not a substitute for human editing, but a drafting aid. Happy writing!', 'aikit' ); ?>
                <a href="#" class="aikit-top-hidden-toggle btn btn-outline-secondary btn-sm"><i class="bi bi-info-lg"></i> <?php echo esc_html__( 'How to setup?', 'aikit' ); ?></a>
            </p>

            <p class="aikit-top-hidden-note">
                <strong><?php echo esc_html__( 'Note:', 'aikit' ); ?></strong>
                <?php echo esc_html__('AIKit RSS generation jobs run in the background as scheduled jobs.', 'aikit'); ?>
                <?php echo esc_html__( 'By default, WordPress scheduled jobs only run when someone visits your site. To ensure that your jobs run even if nobody visits your site, you can set up a cron job on your server to call the WordPress cron system at regular intervals. Please ask your host provider to do that for you. Here is the cron job definition:', 'aikit' ); ?>
                <code>
                    */5 * * * * curl -I <?php echo $cron_url ?> >/dev/null 2>&1
                </code>
            </p>

            <ul class="nav nav-tabs aikit-rss-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'create' ? 'active' : ''; ?>" aria-current="page" href="<?php echo admin_url( 'admin.php?page=aikit_rss&action=create' ); ?>"><?php echo esc_html__( 'Add RSS Job', 'aikit' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'jobs' ? 'active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=aikit_rss&action=jobs' ); ?>"><?php echo esc_html__( 'Jobs', 'aikit' ); ?></a>
                </li>
            </ul>

            <?php
            if ($active_tab == 'create') {
                $this->render_create_tab();
            } else if ($active_tab == 'jobs') {
                if (isset($_GET['job_id'])) {
                    $job = $this->get_rss_job_by_id($_GET['job_id']);

                    if ($job) {
                        $this->render_create_tab(true, $job);
                    }

                    return new WP_REST_Response([
                        'message' => __('Job not found.', 'aikit'),
                    ], 404);
                } else {
                    $this->render_listing_tab();
                }
            }
            ?>

        </div>
        <?php
    }

    private function get_rss_job_by_id($job_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_JOBS;
        $sql = "SELECT * FROM $table_name WHERE id = %d";
        $sql = $wpdb->prepare($sql, $job_id);
        $job = $wpdb->get_row($sql);

        return $job;
    }

    public function render_create_tab($is_edit = false, $job = null)
    {
        $post_types = get_post_types( array( 'public' => true ), 'objects');
        $selected_language = aikit_get_language_used();
        $languages = AIKit_Admin::instance()->get_languages();
        $selected_language_name = $languages[$selected_language]['name'] ?? 'English';

        $available_statuses = [
            'draft' => esc_html__( 'Draft', 'aikit' ),
            'publish' => esc_html__( 'Publish', 'aikit' ),
        ];

        $job_statuses = [
            true => esc_html__( 'Active', 'aikit' ),
            false => esc_html__( 'Inactive', 'aikit' ),
        ];

        $translations = [
            'Created' => esc_html__( 'Created', 'aikit' ),
            'Updated' => esc_html__( 'Updated', 'aikit' ),
            'Repurpose job' => esc_html__( 'RSS job', 'aikit' ),
            'created Successfully.' => esc_html__( 'created Successfully.', 'aikit' ),
            'updated Successfully.' => esc_html__( 'updated Successfully.', 'aikit' ),
        ];

        $rss_urls = [];

        if ($is_edit) {
            $rss_urls = $this->get_rss_urls_by_job_id($job->id);
        }

        $models = aikit_rest_openai_get_available_models('text', true);

        $selected_model = $job->model ?? null;
        if ($selected_model === null) {
            $preferred_model = get_option('aikit_setting_openai_model');
            $selected_model = !empty($preferred_model) ? $preferred_model : 'gpt-3.5-turbo';
        }

        ?>
        <form id="aikit-rss-form" action="<?php echo get_site_url(); ?>/?rest_route=/aikit/rss/v1/job" method="post">
            <?php if (!$is_edit) { ?>
                <div class="row">
                    <div class="col">
                        <p>
                            <?php echo esc_html__( 'Selected language:', 'aikit' ); ?>
                            <span class="badge badge-pill badge-dark aikit-badge"><?php echo $selected_language_name?></span>
                            <a href="<?php echo admin_url( 'admin.php?page=aikit' ); ?>" ><?php echo esc_html__( 'Change language', 'aikit' ); ?></a>
                        </p>
                    </div>
                </div>
            <?php } ?>
            <?php if ($is_edit) { ?>
                <input type="hidden" name="id" id="aikit-rss-job-id" value="<?php echo $job->id; ?>" />
                <div class="row">
                    <div class="col mb-2">
                        <a href="<?php echo admin_url( 'admin.php?page=aikit_rss&action=jobs' ); ?>" class="aikit-rss-back"><?php echo esc_html__( '« Back to Jobs', 'aikit' ); ?></a>
                    </div>
                </div>
            <?php } ?>
            <div class="row mb-2">
                <div class="col">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="aikit-rss-url" placeholder="<?php echo esc_html__( 'RSS URL', 'aikit' ); ?>" value="<?php echo $is_edit ? esc_attr( $job->rss_url ) : ''; ?>" data-validation-message="<?php echo esc_html__( 'Please enter a valid RSS URL', 'aikit' ); ?>" required <?php echo $is_edit ? 'readonly' : ''; ?>/>
                        <label for="aikit-rss-url"><?php echo esc_html__( 'RSS URL', 'aikit' ); ?></label>
                    </div>
                </div>
                <div class="col">
                    <?php
                    $interval = $job ? $job->refresh_interval : null;
                    $this->render_intervals($interval);
                    ?>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="aikit-rss-generation-time-padding" placeholder="<?php echo esc_html__( 'Time to wait between generating articles (minutes)', 'aikit' ); ?>" min="5" step="1" value="<?php echo $is_edit ? esc_attr( $job->generation_time_padding ) : '30'; ?>"/>
                        <label for="aikit-rss-generation-time-padding"><?php echo esc_html__( 'Time to wait between generating articles (minutes)', 'aikit' ); ?></label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="aikit-rss-articles" placeholder="<?php echo esc_html__( 'Posts to generate: ', 'aikit' ); ?>" min="1" max="10" step="1" value="<?php echo $is_edit ? esc_attr( $job->number_of_articles ) : '1'; ?>"/>
                        <label for="aikit-rss-articles"><?php echo esc_html__( 'Posts to generate: ', 'aikit' ); ?></label>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <div class="form-floating">
                        <select class="form-select" id="aikit-rss-post-type" name="aikit-rss-post-type">
                            <?php foreach ($post_types as $type) { ?>
                                <option value="<?php echo esc_attr( $type->name ); ?>" <?php echo $is_edit && $job->output_post_type == $type->name ? 'selected' : ''; ?>><?php echo esc_html( $type->labels->singular_name ); ?></option>
                            <?php } ?>
                        </select>
                        <label for="aikit-rss-post-type"><?php echo esc_html__( 'Post type', 'aikit' ); ?></label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating">
                        <select class="form-select" id="aikit-rss-post-category" name="aikit-rss-post-category">
                            <?php foreach (get_categories(['hide_empty' => false]) as $category) { ?>
                                <option value="<?php echo esc_attr( $category->term_id ); ?>" <?php echo ($is_edit && $job->output_post_category == $category->term_id) ? 'selected' : ''; ?>><?php echo esc_html( $category->name ); ?></option>
                            <?php } ?>
                        </select>
                        <label for="aikit-rss-post-category"><?php echo esc_html__( 'Post category', 'aikit' ); ?></label>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col">
                    <div class="form-floating">
                        <select class="form-select" id="aikit-rss-post-status" name="aikit-rss-post-status">
                            <?php foreach ($available_statuses as $status => $status_name) { ?>
                                <option value="<?php echo esc_attr( $status ); ?>" <?php echo $is_edit && $job->output_post_status == $status ? 'selected' : ''; ?>><?php echo esc_html( $status_name ); ?></option>
                            <?php } ?>
                        </select>
                        <label for="aikit-rss-post-status"><?php echo esc_html__( 'Post status', 'aikit' ); ?></label>
                    </div>
                </div>
                <div class="col">
                    <div class="form-floating">
                        <select class="form-select" id="aikit-rss-model" name="aikit-rss-model">
                            <?php foreach ($models as $model) { ?>
                                <option value="<?php echo esc_attr( $model ); ?>" <?php echo ($selected_model == $model) ? 'selected' : ''; ?>><?php echo esc_html( $model ); ?></option>
                            <?php } ?>
                        </select>
                        <label for="aikit-rss-model"><?php echo esc_html__( 'Model', 'aikit' ); ?></label>
                    </div>
                </div>
            </div>
            <div class="row mb-2">
                <div class="col pt-3">
                    <input type="checkbox" class="form-check-input" id="aikit-rss-include-featured-image" name="aikit-rss-include-featured-image" <?php echo $is_edit && $job->include_featured_image ? 'checked' : ''; ?>/>
                    <label class="form-check-label aikit-rss" for="aikit-rss-include-featured-image"><?php echo esc_html__( 'Include featured image', 'aikit' ); ?></label>
                </div>
            </div>

            <div class="row">
                <?php if ($is_edit) { ?>
                    <div class="col-4">
                        <div class="form-floating">
                            <select class="form-select" id="aikit-rss-status" name="aikit-rss-status">
                                <?php foreach ($job_statuses as $status => $status_name) { ?>
                                    <option value="<?php echo esc_attr( $status ); ?>" <?php echo boolval($status) === boolval($job->is_active) ? 'selected' : ''; ?>><?php echo esc_html( $status_name ); ?></option>
                                <?php } ?>
                            </select>
                            <label for="aikit-rss-status"><?php echo esc_html__( 'Status', 'aikit' ); ?></label>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="row mb-2 justify-content-end text-end">
                <div class="col-6">
                    <?php if ($is_edit) { ?>
                        <button id="aikit-rss-save" data-edit="1" class="btn btn-primary" type="submit"><i class="bi bi-save"></i> <?php echo esc_html__( 'Update', 'aikit' ); ?></button>
                    <?php } else { ?>
                        <button id="aikit-rss-add" class="btn btn-primary ms-2" type="submit"><i class="bi bi-rss-fill"></i> <?php echo esc_html__( 'Add RSS Job', 'aikit' ); ?></button>
                    <?php } ?>
                </div>
            </div>


            <p class="ps-2"><?php echo esc_html__( 'If you like to edit the prompts used by the AI, click on the accordion below.', 'aikit' ); ?></p>
            <div class="accordion accordion-flush" id="aikit-rss-prompts">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#aikit-rss-prompts-pane" aria-expanded="false" aria-controls="flush-collapseOne">
                            <?php echo esc_html__( 'Prompts', 'aikit' ); ?>
                        </button>
                    </h2>
                    <div id="aikit-rss-prompts-pane" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <?php
                            $saved_prompts = $this->get_saved_prompts();
                            $prompts = ($job && !empty($job->prompts)) ? json_decode($job->prompts, true) : (!empty($saved_prompts) ? $saved_prompts : $this->get_prompts());

                            foreach ($prompts as $id => $prompt) {
                                // get all the placeholders in the prompt (e.g. [[noun]]) and list them
                                preg_match_all('/\[\[([^\]]+)\]\]/', $prompt, $matches);
                                $placeholders = $matches[1];
                                // surround each placeholder with <code> tags
                                $placeholders = array_map(function($placeholder) {
                                    return '<code>' . $placeholder . '</code>';
                                }, $placeholders);
                                $placeholderString = implode(', ', $placeholders);

                                echo '<div class="mb-2">';
                                echo '<label for="aikit-rss-prompt-'.$id.'" class="aikit-rss"><strong>'. $id  .':</strong></label>';
                                echo '<span class="aikit-rss-prompt-description">' . esc_html__(' uses', 'aikit')  . $placeholderString . '</span>';
                                echo '<textarea class="form-control aikit-rss-prompt" data-prompt-id="' . $id . '" id="aikit-rss-prompt-'.$id.'" name="aikit-rss-prompt-'.$id.'" rows="3">'. $prompt .'</textarea>';
                                echo '</div>';
                            }
                            ?>
                            <div class="row mt-3 mb-3">
                                <div class="col">
                                    <input type="checkbox" class="form-check-input" id="aikit-rss-save-prompts" name="aikit-rss-save-prompts">
                                    <label class="form-check-label" for="aikit-rss-save-prompts"><?php echo esc_html__( 'Save prompts for future use (for currently-selected language).', 'aikit' ); ?></label>
                                </div>
                            </div>
                            <div class="row mt-3 mb-3">
                                <div class="col">
                                    <a id="aikit-rss-reset-prompts" data-confirm-message="<?php echo __('Are you sure you want to reset saved prompts?', 'aikit') ?>" class="btn btn-outline-dark" type="button" href="<?php echo get_site_url(); ?>/?rest_route=/aikit/rss/v1/reset-prompts"><i class="bi bi-arrow-repeat me-2"></i><?php echo esc_html__( 'Reset prompts to default', 'aikit' ); ?></a>
                                </div>
                            </div>
                            <p class="aikit-rss-placeholder-descriptions">
                                <?php echo esc_html__( 'You can use the following placeholders in your prompts:', 'aikit' ); ?>
                            </p>

                            <ul class="aikit-rss-placeholder-descriptions">
                                <li><code>[[text]]</code> - <?php echo esc_html__( 'this will be replaced with text needed for that prompt.', 'aikit' ); ?></li>
                                <li><code>[[summaries]]</code> - <?php echo esc_html__( 'this will be replaced with the combination of all the summaries of all parts of the post.', 'aikit' ); ?></li>
                                <li><code>[[keywords]]</code> - <?php echo esc_html__( 'this will be replaced with the SEO keywords you entered.', 'aikit' ); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="aikit-rss-translations" value="<?php echo esc_attr(json_encode($translations)); ?>">

        </form>

        <?php if ($is_edit) {
            $logs = json_decode($job->logs, true);

            $logsCount = count($logs) == 0 ? '' : ' (' . count($logs) . ')';
            ?>
        <ul class="nav nav-tabs mt-3" id="aikit-rss-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#aikit-rss-tabs-urls" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true"><?php echo esc_html__( 'RSS Posts', 'aikit' ); ?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#aikit-rss-tabs-logs" type="button" role="tab" aria-controls="home-tab-pane"><?php echo esc_html__( 'Logs', 'aikit' ) .$logsCount ?></button>
            </li>
        </ul>
        <div class="tab-content mt-2" id="aikit-rss-tab-panes">
            <div class="tab-pane fade show active" id="aikit-rss-tabs-urls" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                <?php

                $html = '<table class="table" id="aikit-rss-urls-table">
                                <thead>
                                <tr>
                                    <th scope="col">'.esc_html__( 'URL', 'aikit' ).'</th>
                                    <th scope="col">'.esc_html__( 'Repurpose Job', 'aikit' ).'</th>
                                    <th scope="col">'.esc_html__( 'Date created', 'aikit' ).'</th>
                                </tr>
                                </thead>
                                <tbody>';

                if (count($rss_urls) === 0) {
                    $html .= '<tr><td colspan="3">'.esc_html__( 'No RSS post fetched yet.', 'aikit' ).'</td></tr>';
                }

                $repurpose_job_url = admin_url('admin.php?page=aikit_repurpose&action=jobs&job_id=');
                foreach ($rss_urls as $url) {
                    $html .= '<tr>
                                    <td><a href="'.esc_url($url->url).'" target="_blank">'.esc_html($url->url).'</a></td>
                                    <td><a href="'.esc_url($repurpose_job_url.$url->repurpose_job_id).'">'. __('Open', 'aikit').'</a></td>
                                    <td>'. aikit_date($job->date_created).'</td>
                                </tr>';
                }

                $html .= '</tbody></table>';

                echo $html;

                ?>
            </div>
            <div class="tab-pane fade" id="aikit-rss-tabs-logs" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
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
        </div>
        <?php } ?>
        <?php
    }

    private function get_rss_urls_by_job_id($id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_URLS;

        $sql = "SELECT * FROM $table_name WHERE rss_job_id = %d";

        $results = $wpdb->get_results($wpdb->prepare($sql, $id));

        return $results;
    }

    private function render_intervals($selected = null)
    {
        $available_intervals = [
            'hourly' => esc_html__( 'Hourly', 'aikit' ),
            'twicedaily' => esc_html__( 'Twice daily', 'aikit' ),
            'daily' => esc_html__( 'Daily', 'aikit' ),
        ];

        ?>
        <div class="form-floating">
            <select class="form-select" id="aikit-rss-schedule-interval" name="aikit-rss-schedule-interval">
                <?php foreach ($available_intervals as $value => $label) { ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php echo $selected === $value ? 'selected' : ''; ?>><?php echo esc_html($label); ?></option>
                <?php } ?>
            </select>
            <label for="aikit-rss-schedule-interval"><?php echo esc_html__( 'How often to check for new articles in RSS feed', 'aikit' ); ?></label>
        </div>
        <?php
    }

    private function get_prompts()
    {
        $lang = get_option('aikit_setting_openai_language', 'en');

        $result = AIKIT_REPURPOSER_PROMPTS[$lang]['prompts'];

        // remove all prompts where key ends with with-seo-keywords
        $result = array_filter($result, function($key) {
            return !preg_match('/-with-seo-keywords$/', $key);
        }, ARRAY_FILTER_USE_KEY);

        return $result;
    }

    private function get_saved_prompts()
    {
        $lang = get_option('aikit_setting_openai_language', 'en');

        $prompts = get_option('aikit_rss_prompts_' . $lang);

        if (empty($prompts)) {
            return [];
        }

        return json_decode($prompts, true);
    }

    public function render_listing_tab()
    {
        $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 25;
        $html = '<table class="table" id="aikit-rss-jobs">
            <thead>
            <tr>
                <th scope="col">' . esc_html__('RSS URL', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Refresh Interval', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Next Refresh', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Status', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Date created', 'aikit') . '</th>               
                <th scope="col">' . esc_html__('Actions', 'aikit') . '</th>
            </tr>
            </thead>
            <tbody>';

        // get all jobs from DB
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_JOBS;

        // prepared statement to prevent SQL injection with pagination
        $jobs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d, %d", ($paged - 1) * $per_page, $per_page));

        if (empty($jobs)) {
            $html .= '<tr>
                <td colspan="6">' . esc_html__('No entries found', 'aikit') . '</td>
            </tr>';
        }

        $page_url = get_admin_url() . 'admin.php?page=aikit_rss&action=jobs';
        $delete_url = get_site_url() . '/?rest_route=/aikit/rss/v1/delete';

        foreach ($jobs as $job) {
            $current_page_url = $page_url . '&job_id=' . $job->id;
            $html .= '<tr>
                <td>' . '<a href="' . $current_page_url . '">' . esc_html($job->rss_url) . '</a></td>
                <td>' . $job->refresh_interval . '</td>
                <td>' . (empty($job->next_refresh) ? __('In few minutes', 'aikit') : aikit_date($job->next_refresh)) . '</td>               
                <td>' . ($job->is_active ? ('<span class="badge badge-pill badge-dark aikit-badge-active">' . __('Active', 'aikit')) : ('<span class="badge badge-pill badge-dark aikit-badge-inactive">' . __('Inactive', 'aikit'))) . '</span></td>
                <td>' . (empty($job->date_created) ? '-' : aikit_date($job->date_created)) . '</td>               
                <td>
                    <a href="' . $page_url . '&job_id=' . $job->id . '" title="' . __('View', 'aikit') . '" class="aikit-rss-action" data-id="' . $job->id . '"><i class="bi bi-eye-fill"></i></a>
                    <a href="' . $delete_url . '" title="' . __('Delete', 'aikit') . '" class="aikit-rss-jobs-delete aikit-rss-action" data-confirm-message="' . __('Are you sure you want to delete this RSS job?', 'aikit') . '" data-id="' . $job->id . '"><i class="bi bi-trash-fill"></i></a>
                </td>
            </tr>';
        }

        // pagination
        $total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_pages = ceil($total_jobs / $per_page);
        $html .= '<tr>
            <td colspan="6">';

        // previous page

        $html .= '<div class="aikit-rss-jobs-pagination">';
        if ($paged > 1) {
            $html .= '<a href="' . $page_url . '&paged=' . ($paged - 1) . '">' . __('« Previous', 'aikit') . '</a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            // add class to current page
            $current_page_class = '';
            if ($paged == $i) {
                $current_page_class = 'aikit-rss-jobs-pagination-current';
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
        $html .= '</tbody>
        
        </table>';

        echo $html;
    }

    public function enqueue_scripts($hook)
    {
        if ( 'aikit_page_aikit_rss' !== $hook ) {
            return;
        }

        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_style( 'aikit_bootstrap_css', plugins_url( '../../css/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( '../../css/bootstrap-icons.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_rss_css', plugins_url( '../../css/rss.css', __FILE__ ), array(), $version );

        wp_enqueue_script( 'aikit_bootstrap_js', plugins_url('../../js/bootstrap.bundle.min.js', __FILE__ ), array(), $version );
        wp_enqueue_script( 'aikit_jquery_ui_js', plugins_url('../../js/jquery-ui.min.js', __FILE__ ), array('jquery'), $version );
        wp_enqueue_script( 'aikit_rss_js', plugins_url( '../../js/rss.js', __FILE__ ), array( 'jquery' ), array(), $version );
    }

    public function execute()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_RSS_JOBS;

        $jobs = $wpdb->get_results(
            "SELECT * FROM $table_name WHERE is_active = 1 AND is_running = 0 AND (next_refresh <= NOW() OR next_refresh IS NULL) ORDER BY id ASC"
        );

        $last_job_date = time();

        foreach ($jobs as $job) {
            $wpdb->update(
                $table_name,
                array(
                    'is_running' => 1,
                ),
                array('id' => $job->id)
            );

            $result = ['success' => true];
            try {
                $feed = fetch_feed($job->rss_url);
                // get the links and store them in the db
                $items = $feed->get_items();
                foreach ($items as $item) {
                    // if the url exists, skip it
                    $exists = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM $wpdb->prefix" . self::TABLE_NAME_RSS_URLS . " WHERE rss_job_id = %d AND url = %s",
                            $job->id,
                            $item->get_link()
                        )
                    );

                    if ($exists > 0) {
                        continue;
                    }

                    $job_padding = $job->generation_time_padding;

                    $repurpose_job_id = $this->repurposer->add_job([
                        'url' => $item->get_link(),
                        'job_type' => AIKIT_Repurposer::JOB_TYPE_URL,
                        'keywords' => '',
                        'include_featured_image' => boolval($job->include_featured_image),
                        'number_of_articles' => $job->number_of_articles,
                        'post_type' => $job->output_post_type,
                        'post_status' => $job->output_post_status,
                        'post_author' => get_current_user_id(),
                        'post_category' => $job->output_post_category,
                        'prompts' => json_decode($job->prompts, true),
                        'start_date' => date('Y-m-d H:i:s', $last_job_date),
                        'model' => $job->model,
                    ], $job->id);

                    if ($repurpose_job_id === false) {
                        throw new Exception('Failed to add repurpose job for: ' . $item->get_link());
                    }

                    $last_job_date += $job_padding * 60;

                    aikit_reconnect_db_if_needed();

                    $wpdb->insert(
                        $wpdb->prefix . self::TABLE_NAME_RSS_URLS,
                        array(
                            'rss_job_id' => $job->id,
                            'repurpose_job_id' => $repurpose_job_id,
                            'url' => $item->get_link(),
                            'date_created' => date('Y-m-d H:i:s', strtotime($item->get_date())),
                            'date_modified' => date('Y-m-d H:i:s', strtotime($item->get_date())),
                        )
                    );
                }


            } catch (\Throwable $th) {
                $result = [
                    'success' => false,
                    'message' => $th->getMessage(),
                ];
            }

            $now = new DateTime();

            aikit_reconnect_db_if_needed();

            $wpdb->update(
                $table_name,
                array(
                    'is_running' => 0,
                    'next_refresh' => $this->calculate_next_generation_date($now->format('Y-m-d H:i:s'), $job->refresh_interval),
                    'logs' => json_encode([$result]),
                ),
                array('id' => $job->id)
            );

        }
    }

    private function calculate_next_generation_date($last_generation_time, $interval)
    {
        $hours_to_add = 1;

        if ($interval === 'twicedaily') {
            $hours_to_add = 12;
        } else if ($interval === 'daily') {
            $hours_to_add = 24;
        }

        $next_generation_time = strtotime($last_generation_time) + ($hours_to_add * 60 * 60);

        return date('Y-m-d H:i:s', $next_generation_time);
    }
}