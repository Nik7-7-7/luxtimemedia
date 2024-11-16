<?php
class AIKIT_Text_To_Speech extends AIKIT_Page
{
    const TABLE_NAME_TTS_JOBS = 'aikit_tts_jobs';
    private $audio_player;
    private static $instance = null;

    // singleton
    public static function get_instance()
    {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Text_To_Speech();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action( 'rest_api_init', function () {

            register_rest_route( 'aikit/tts/v1', '/delete', array(
                'methods' => 'POST',
                'callback' => array($this, 'delete_job'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/tts/v1', '/delete-all', array(
                'methods' => 'POST',
                'callback' => array($this, 'delete_all_jobs'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

        });


        $this->audio_player = AIKIT_Audio_Player::get_instance();
        add_action('aikit_tts', array($this, 'execute'));

        add_action('add_meta_boxes', array($this, 'add_tts_meta_box'));
        add_action('save_post', array($this, 'save_tts_meta_box'));
        add_action('template_redirect', array($this, 'render_tts_audio_player'));
        add_shortcode('aikit_audio_player', array($this, 'audio_player_shortcode'));
    }

    public function do_db_migration()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            status varchar(255) NOT NULL,
            post_id int(11) NOT NULL,
            type varchar(255) DEFAULT 'elevenlabs',
            add_shortcode BOOLEAN DEFAULT FALSE,
            content longtext DEFAULT NULL,
            audio_files longtext DEFAULT NULL,
            date_created datetime DEFAULT NULL,
            date_modified datetime DEFAULT NULL,
            logs LONGTEXT NULL,
            is_running BOOLEAN DEFAULT FALSE,
            error_count INT DEFAULT 0,
            UNIQUE INDEX aikit_ttx_unq_post_id (post_id),
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
    }

    public function render()
    {
        $cron_url = get_site_url() . '/wp-cron.php';

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__( 'Text to Speech', 'aikit' ); ?></h1>
            <p>
                <a href="#" class="aikit-util-button float-end mx-1 aikit-top-hidden-toggle btn btn-outline-secondary btn-sm"><i class="bi bi-info-lg"></i> <?php echo esc_html__( 'How to setup?', 'aikit' ); ?></a>
                <a href="https://youtu.be/Gdh8GiQeBAk" target="_blank" class="aikit-util-button float-end mx-1 btn btn-outline-secondary btn-sm"><i class="bi bi-youtube"></i> <?php echo esc_html__( 'Watch Video', 'aikit' ); ?></a>
                <?php echo esc_html__( 'AIKit Text to Speech allows you to convert your post content to audio and can then add an audio player to your post to allow your visitors to listen to your post content.', 'aikit' ); ?>
            </p>
            <p>
                <?php echo esc_html__( 'AIKit uses ElevenLabs to turn text to speech, and due to the maximum limitations on characters that can be processed per request, an article can be divided into different audio files that will be properly played in correct sequence in the audio player. AIKit also will make its best effort not to regenerate all the article audio in case it\'s updated, but rather regenerate only the updated paragraphs.', 'aikit' ); ?>
            </p>

            <div class="aikit-top-hidden-note">
                <p>
                    <strong><?php echo esc_html__( 'Note:', 'aikit' ); ?></strong>
                    <?php echo esc_html__('AIKit text to speech creation jobs run in the background as scheduled jobs.', 'aikit'); ?>
                    <?php echo esc_html__( 'By default, WordPress scheduled jobs only run when someone visits your site. To ensure that your text to speech creation jobs run even if nobody visits your site, you can set up a cron job on your server to call the WordPress cron system at regular intervals. Please ask your host provider to do that for you. Here is the cron job definition:', 'aikit' ); ?>
                    <code>
                        */5 * * * * curl -I <?php echo $cron_url ?> >/dev/null 2>&1
                    </code>
                </p>
            </div>

            <ul class="nav nav-tabs aikit-tts-tabs">
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo admin_url( 'admin.php?page=aikit_text_to_speech&action=jobs' ); ?>"><?php echo esc_html__( 'Jobs', 'aikit' ); ?></a>
                </li>
            </ul>

            <div class="aikit-tts-content">
                <?php
                if (isset($_GET['job_id']))
                    $this->render_view_tab($_GET['job_id']);
                else {
                    $this->render_listing_tab();
                }
                ?>
            </div>

        </div>

        <?php
    }

    private function get_job_by_id($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;

        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);

        $result = $wpdb->get_results($sql);
        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    public function render_view_tab($job_id)
    {
        $job = $this->get_job_by_id($job_id);

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

        $status_message = '<i class="bi bi-hourglass-split"></i> ' . esc_html__( 'Please set back and relax while the your text to speech is being processed. Status of the job will change to reflect when this is done.', 'aikit');

        if ($job->status === 'completed') {
            $status_message = '<i class="bi bi-check-circle-fill"></i> ' . esc_html__( 'Congratulations! Your text to speech job is processed. If you chose to include the audio player in the post automatically, you don\'t need to do anything else. Otherwise, you can include the shortcode "[aikit_audio_player]" in your post to display the audio player.', 'aikit');
        }

        ?>
        <p class="aikit-notice-message">

            <?php
            echo $status_message;
            ?>
        </p>
        <?php

        $this->render_job_info_tabs($job);
    }

    private function render_job_info_tabs($job)
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
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="job-info">

                <div class="row mt-2">
                    <div class="col">
                        <span class=""><?php echo __('Status', 'aikit'); ?></span>: <span class="badge badge-pill badge-dark aikit-badge-active ms-1"><?php echo $this->map_tts_status_map($job->status) ?></span>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col">

                        <?php
                        if ($job->audio_files) {

                            $audio_files = json_decode($job->audio_files, true);

                            if (!empty($audio_files)) {
                                echo '<h6>' . esc_html__('Generated audio files:', 'aikit') . '</h6>';
                                $i = 1;
                                foreach ($audio_files as $audio_file) {

                                    $text = $audio_file['text'];
                                    if (strlen($text) > 100) {
                                        $text = substr($text, 0, 100) . '...';
                                    }

                                    echo '<a href="' . $audio_file['url'] . '" target="_blank">' . $i . '. ' . $text . '</a><br>';
                                    $i++;

                                }
                            }
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
        <?php
    }

    public function render_listing_tab()
    {
        // get all jobs from DB
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;

        $delete_all_url = get_site_url() . '/?rest_route=/aikit/tts/v1/delete-all';

        $total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

        if ($total_jobs > 0) {
            ?>
            <div class="row mb-2 float-end">
                <div class="col">
                    <button data-confirm-message="<?php echo esc_html__('Are you sure you want to delete all pending jobs?', 'aikit') ?>" id="aikit-tts-delete-all" class="btn btn-sm btn-outline-danger ms-2" type="button" href="<?php echo $delete_all_url ?>"><i class="bi bi-trash3-fill"></i> <?php echo esc_html__( 'Delete all pending jobs', 'aikit' ); ?></button>
                </div>
            </div>
            <?php
        }

        $paged = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $per_page = 25;
        $columns = [
            esc_html__('Post title', 'aikit'),
            esc_html__('Status', 'aikit'),
            esc_html__('Date created', 'aikit'),
            esc_html__('Actions', 'aikit'),
        ];
        $html = '<table class="table" id="aikit-tts-jobs">
            <thead>
            <tr>';

        foreach ($columns as $column) {
            $html .= '<th scope="col">' . $column . '</th>';
        }

        $html .= '
            </tr>
            </thead>
            <tbody>';

        // prepared statement to prevent SQL injection with pagination
        $jobs = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d, %d", ($paged - 1) * $per_page, $per_page));

        if (empty($jobs)) {
            $html .= '<tr>
                <td colspan="' . count($columns) . '">' . esc_html__('No entries found', 'aikit') . '</td>
            </tr>';
        }

        $page_url = get_admin_url() . 'admin.php?page=aikit_text_to_speech&action=view';

        foreach ($jobs as $job) {
            $current_page_url = $page_url . '&job_id=' . $job->id;
            $title = get_the_title($job->post_id);
            if (empty($title)) {
                $title = esc_html__('(no title)', 'aikit');
            }

            $delete_url = get_site_url() . '/?rest_route=/aikit/tts/v1/delete';

            // link to edit post on admin
            $editPostUrl = get_edit_post_link($job->post_id);
            $html .= '<tr>
                <td>' . '<a href="' . $current_page_url . '">' . $title . '</a> <a class="aikit-view-post-link" href="' . $editPostUrl . '" target="_blank"><i class="bi bi-box-arrow-up-right"></i> ' . '</a>' . '</td>
                <td><span class="badge badge-pill badge-dark aikit-badge-active">' . $this->map_tts_status_map($job->status) . '</span></td>
                <td>' . (empty($job->date_created) ? '-' : aikit_date($job->date_created)) . '</td>               
                <td>
                    <a href="' . $page_url . '&job_id=' . $job->id . '" title="' . __('View', 'aikit') . '" class="aikit-tts-action" data-id="' . $job->id . '"><i class="bi bi-eye-fill"></i></a>
                    <a href="' . $delete_url . '" title="' . __('Delete', 'aikit') . '" class="aikit-tts-job-delete aikit-tts-action" data-confirm-message="' . __('Are you sure you want to delete this text to speech job?', 'aikit') . '" data-id="' . $job->id . '"><i class="bi bi-trash-fill"></i></a>                  
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

    public function delete_job($data)
    {
        $id = $data['id'] ?? null;

        if (empty($id)) {
            return new WP_REST_Response(['success' => false], 400);
        }

        $job = $this->get_job_by_id($id);

        if (!$job) {
            return new WP_REST_Response(['success' => false], 404);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;

        $wpdb->delete(
            $table_name,
            array('id' => $id)
        );

        return new WP_REST_Response(['success' => true], 200);
    }

    public function delete_all_jobs($data){
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;

        $wpdb->query("DELETE FROM $table_name WHERE status = 'pending'");

        return new WP_REST_Response(array(
            'success' => true,
        ));
    }

    private function map_tts_status_map($status)
    {
        return [
            'pending' => esc_html__('Pending', 'aikit'),
            'completed' => esc_html__('Completed', 'aikit'),
        ][strtolower($status)];
    }

    public function activate_scheduler()
    {
        if (! wp_next_scheduled ( 'aikit_tts')) {
            wp_schedule_event( time(), 'every_10_minutes', 'aikit_tts');
        }
    }

    public function deactivate_scheduler()
    {
        wp_clear_scheduled_hook('aikit_tts');
    }

    public function add_job_for_post($post_id, $add_shortcode = false)
    {
        global $wpdb;

        $tts_job = $this->get_job_for_post($post_id);
        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;

        if ($tts_job) {
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'pending',
                    'date_modified' => current_time('mysql', true),
                    'add_shortcode' => $add_shortcode,
                ),
                array(
                    'id' => $tts_job->id,
                )
            );

            return;

        }

        $wpdb->insert(
            $table_name,
            array(
                'status' => 'pending',
                'post_id' => $post_id,
                'add_shortcode' => $add_shortcode,
                'date_created' => current_time('mysql', true),
                'date_modified' => current_time('mysql', true),
                'logs' => json_encode([]),
            )
        );
    }

    public function get_job_for_post($post_id, $status = null)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;

        if ($status === null) {
            $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d", $post_id);
        } else {
            $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE post_id = %d AND status = %s", $post_id, $status);
        }

        $result = $wpdb->get_results($sql);
        if (count($result) > 0) {
            return $result[0];
        }

        return null;
    }

    public function execute()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME_TTS_JOBS;

        $jobs = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'pending' AND is_running = 0 ORDER BY date_created ASC");

        $api_to_use = get_option('aikit_setting_default_tts_api') === 'openai' ? 'openai' : 'elevenlabs';

        foreach ($jobs as $job) {

            $wpdb->update(
                $table_name,
                array(
                    'is_running' => 1,
                    'type' => $api_to_use,
                    'date_modified' => current_time('mysql', true),
                ),
                array(
                    'id' => $job->id,
                )
            );

            try {

                $max_length = 2500;
                if ($api_to_use === 'openai') {
                    $max_length = 4000;
                } else {
                    $eleven_labs_user_info = aikit_elevenlabs_get_user_info();
                    if (isset($eleven_labs_user_info['subscription']['tier'])) {
                        $max_length = $eleven_labs_user_info['subscription']['tier'] == 'free' ? 2500 : 5000;
                    }
                }

                $chunks = $this->get_post_content_chunks($job->post_id, $max_length);

                $audio_files = $job->audio_files ? json_decode($job->audio_files, true) : [];

                $new_audio_files = [];
                foreach ($chunks as $chunk) {
                    $chunk_hash = $this->hash_string($chunk);

                    if (isset($audio_files[$chunk_hash])) {
                        $new_audio_files[] = $audio_files[$chunk_hash];
                        continue;
                    }

                    if ($api_to_use === 'openai') {
                        $result = aikit_openai_text_to_speech($chunk);
                    } else {
                        $result = aikit_elevenlabs_text_to_speech($chunk);
                    }

                    $result['text'] = $chunk;

                    $new_audio_files[$chunk_hash] = $result;
                }

                if ($job->add_shortcode) {
                    $post_content = get_post_field('post_content', $job->post_id);
                    if (strpos($post_content, '[aikit_audio_player]') === false) {
                        $audio_player_shortcode = "[aikit_audio_player]\n";
                        $content = $audio_player_shortcode . $post_content;

                        wp_update_post(array(
                            'ID' => $job->post_id,
                            'post_content' => $content,
                        ));
                    }
                }

            } catch (Throwable $e) {
                $logs = json_decode($job->logs);
                $logs[] = array(
                    'message' => $e->getMessage(),
                    'date' => current_time( 'mysql', true )
                );

                $error_count = intval($job->error_count);
                $error_count++;

                $new_audio_files = array_merge($audio_files, $new_audio_files);  // to avoid losing audio files that were already generated in case of error

                $wpdb->update(
                    $table_name,
                    array(
                        'is_running' => 0,
                        'date_modified' => current_time('mysql', true),
                        'logs' => json_encode($logs),
                        'error_count' => $error_count,
                        'audio_files' => json_encode($new_audio_files),
                    ),
                    array(
                        'id' => $job->id,
                    )
                );

                continue;
            }

            $wpdb->update(
                $table_name,
                array(
                    'is_running' => 0,
                    'date_modified' => current_time('mysql', true),
                    'audio_files' => json_encode($new_audio_files),
                    'status' => 'completed',
                ),
                array(
                    'id' => $job->id,
                )
            );
        }
    }

    private function hash_string($string)
    {
        return md5(trim($string));
    }


    private function get_post_content_chunks($post_id, $max_length)
    {
        $content = get_post_field('post_content', $post_id);

        // remove captions tags
        $content = preg_replace('/<caption.*?>(.*?)<\/caption>/s', '', $content);
        // remove figcaption tags
        $content = preg_replace('/<figcaption.*?>(.*?)<\/figcaption>/s', '', $content);

        // remove html tags
        $content = strip_tags($content);

        // turn html entities into text
        $content = html_entity_decode($content);

        // remove shortcodes
        $content = preg_replace('/\[.*?\]/', '', $content);

        // remove multiple spaces
        $content = preg_replace('/ +/', ' ', $content);

        // split by paragraphs
        $paragraphs = explode("\n", $content);

        // remove empty paragraphs
        $paragraphs = array_filter($paragraphs, function($paragraph) {
            return mb_strlen(trim($paragraph)) > 0;
        });

        $paragraphs = array_values($paragraphs); // reindex array (remove empty indexes)

        $min_paragraph_length = 500;

        $merged_paragraphs = [];

        // any string that is less than $min_paragraph_length characters will be merged with the next paragraph
        for ($i = 0; $i < count($paragraphs); $i++) {
            $paragraph = $paragraphs[$i];

            if (mb_strlen($paragraph) < $min_paragraph_length) {
                if (isset($paragraphs[$i + 1])) {
                    $paragraphs[$i + 1] = $paragraph . ' ' . $paragraphs[$i + 1];
                } else {
                    $merged_paragraphs[] = $paragraph;
                }
            } else {
                $merged_paragraphs[] = $paragraph;
            }
        }

        // if $merged_paragraph is more than $max_length characters, split it into smaller chunks. Divide into statements (sentences ending with a dot) and keep adding them to the current chunk until it reaches $max_length characters
        $chunks = [];
        $chunk = '';

        foreach ($merged_paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            if (mb_strlen($paragraph) == 0) {
                continue;
            }

            $statements = preg_split('/(\.|\n)\s+/', $paragraph, -1, PREG_SPLIT_DELIM_CAPTURE);

            foreach ($statements as $statement) {
                $statement = trim($statement);

                if (mb_strlen($statement) == 0) {
                    continue;
                }

                $statement = preg_replace('/\s+/', ' ', $statement);

                if (mb_strlen($chunk) + mb_strlen($statement) + 1 < $max_length) {
                    $chunk .= ' ' . $statement;
                } else {
                    $chunks[] = $chunk;
                    $chunk = $statement;
                }
            }

            if (mb_strlen($chunk) > 0) {
                $chunks[] = $chunk;
                $chunk = '';
            }
        }

        $trimmed_chunks = [];
        foreach ($chunks as $chunk) {
            $chunk = preg_replace('/\s/u', ' ', $chunk);
            $trimmed_chunks[] = trim($chunk);
        }

        // replace " ." or " ," with "." or ","
        return preg_replace('/\s([.,])/', '$1', $trimmed_chunks);
    }

    function add_tts_meta_box() {
        add_meta_box(
            'aikit_text_to_speech_meta_box',
            __('AIKit Text-to-Speech', 'aikit'),
            array($this, 'render_text_to_speech_meta_box'),
            ['post', 'page'],
            'side', // This places the box in the sidebar
            'core'
        );
    }

    function render_text_to_speech_meta_box($post) {
        $tts = AIKIT_Text_To_Speech::get_instance();
        $tts_job = $tts->get_job_for_post($post->ID);

        if ($tts_job) {
            $link = get_admin_url() . 'admin.php?page=aikit_text_to_speech&action=view&job_id=' . $tts_job->id;
            ?>
            <p>
                <?php esc_html_e('A job already exists for this post with status ', 'aikit'); ?> <a href="<?php echo $link; ?>"><?php echo $this->map_tts_status_map($tts_job->status); ?></a>
            </p>
            <?php
        }

        ?>
        <label for="aikit_generate_tts_option">
            <input type="checkbox" name="aikit_generate_tts_option" id="aikit_generate_tts_option" value="1">
            <?php echo $tts_job ? esc_html__('Force regenerate audio', 'aikit') : esc_html__('Generate audio', 'aikit'); ?>
        </label>
        <br>
        <label for="aikit_add_shortcode_tts_option">
            <input type="checkbox" name="aikit_add_shortcode_tts_option" id="aikit_add_shortcode_tts_option" value="1">
            <?php echo esc_html__('Add audio player to post (once ready)', 'aikit'); ?>
        </label>
        <br>
        <br>
        <code>[aikit_audio_player]</code>
        <p><?php echo __('If you select this option, once the audio is ready, the shortcode will be replaced on top of the post to show the audio player.', 'aikit'); ?></p>
        <?php
    }

    function save_tts_meta_box($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (wp_is_post_revision($post_id)) return;

        $enable_option = isset($_POST['aikit_generate_tts_option']) && sanitize_text_field($_POST['aikit_generate_tts_option']);
        $add_shortcode_option = isset($_POST['aikit_add_shortcode_tts_option']) && sanitize_text_field($_POST['aikit_add_shortcode_tts_option']);

        if ($enable_option) {
            $tts = AIKIT_Text_To_Speech::get_instance();
            $tts->add_job_for_post($post_id, $add_shortcode_option);
        }
    }

    function render_tts_audio_player() {
        if (isset($_GET['aikit-tts-post-id'])) {
            $is_preview = false;
            if (isset($_GET['aikit-preview'])) {
                $is_preview = true;
            }

            if (!$is_preview) {
                $job = $this->get_job_for_post($_GET['aikit-tts-post-id']);

                if (!$job) {
                    exit;
                }

                $audio_files = json_decode($job->audio_files, true);
            } else {
                $audio_files = [];
            }

            $audio_files_for_player = [];
            foreach ($audio_files as $audio_file) {
                $text = $audio_file['text'];
                // limit to 20 characters
                if (mb_strlen($text) > 20) {
                    $text = mb_substr($text, 0, 20) . '...';
                }
                $audio_files_for_player[] = [
                    'title' => $text,
                    'url' => $audio_file['url'],
                ];
            }

            $this->audio_player->render($audio_files_for_player);
            exit;
        }
    }

    public function audio_player_shortcode($args)
    {
        // display iframe with audio player
        $post_id = $args['post_id'] ?? null;

        if (!$post_id) {
            // get the id of the current post
            $post_id = get_the_ID();
        }

        $job = $this->get_job_for_post($post_id, 'completed');

        if (!$job) {
            return '';
        }

        $message = get_option('aikit_setting_audio_player_message');

        // iframe should be website domain with the "aikit-tts-post-id" query param (avoid xss)
        $iframe_url = get_site_url() . '?aikit-tts-post-id=' . urlencode($post_id);

        return '<span style="text-align: center; align-content: center; width: 100%; margin-left: 20px; font-size: 14px;">' . esc_html($message) . '</span>
        <iframe src="' . $iframe_url . '" width="100%" height="120px" style="display: block; border: none; border-radius: 20px"></iframe>';
    }

    public function generate_audio_player_preview_iframe()
    {
        $primary_color = get_option('aikit_setting_audio_player_primary_color');
        $secondary_color = get_option('aikit_setting_audio_player_secondary_color');

        return '<iframe class="aikit-audio-player-iframe" src="' . get_site_url() . '?aikit-tts-post-id=0&aikit-preview=1&aikit-primary-color=' . urlencode($primary_color) . '&aikit-secondary-color=' . urlencode($secondary_color) . '" width="500px" height="120px" style="display: block; border: none; border-radius: 20px"></iframe>';
    }

    public function enqueue_scripts($hook)
    {
        if ( 'aikit_page_aikit_text_to_speech' !== $hook ) {
            return;
        }

        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_style( 'aikit_bootstrap_css', plugins_url( '../css/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( '../css/bootstrap-icons.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_text_to_speech_css', plugins_url( '../css/text-to-speech.css', __FILE__ ), array(), $version );

        wp_enqueue_script( 'aikit_bootstrap_js', plugins_url('../js/bootstrap.bundle.min.js', __FILE__ ), array(), $version );
        wp_enqueue_script( 'aikit_jquery_ui_js', plugins_url('../js/jquery-ui.min.js', __FILE__ ), array('jquery'), $version );
        wp_enqueue_script( 'aikit_text_to_speech_js', plugins_url( '../js/text-to-speech.js', __FILE__ ), array( 'jquery' ), array(), $version );
    }

}
