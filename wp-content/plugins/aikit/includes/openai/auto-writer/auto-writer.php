<?php

class AIKIT_Auto_Writer
{
    const TABLE_NAME = 'aikit_scheduled_ai_generators';
    const POST_META_AIKIT_AI_GENERATOR_ID = 'aikit_ai_generator_id';
    const GENERATOR_VERSION = 1;

    // singleton
    private static $instance = null;

    /* @var AIKIT_Auto_Writer_Form|null */
    private $auto_writer_form = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Auto_Writer();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->auto_writer_form = AIKIT_Auto_Writer_Form::get_instance();

        add_action( 'rest_api_init', function () {
            register_rest_route( 'aikit/auto-writer/v1', '/write', array(
                'methods' => 'POST',
                'callback' => array($this, 'handle_request'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/auto-writer/v1', '/list', array(
                'methods' => 'GET',
                'callback' => array($this, 'list_posts'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/auto-writer/v1', '/delete-generator', array(
                'methods' => 'POST',
                'callback' => array($this, 'delete_generator'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/auto-writer/v1', '/deactivate-all', array(
                'methods' => 'POST',
                'callback' => array($this, 'deactivate_all_jobs'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/auto-writer/v1', '/activate-all', array(
                'methods' => 'POST',
                'callback' => array($this, 'activate_all_jobs'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/auto-writer/v1', '/delete-all', array(
                'methods' => 'POST',
                'callback' => array($this, 'delete_all_jobs'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

            register_rest_route( 'aikit/auto-writer/v1', '/reset-prompts', array(
                'methods' => 'POST',
                'callback' => array($this, 'reset_prompts'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));
        });

        add_action('aikit_scheduler', array($this, 'execute'));
    }

    public function reset_prompts($data)
    {
        $lang = get_option('aikit_setting_openai_language', 'en');
        delete_option('aikit_auto_writer_prompts_' . $lang);

        return new WP_REST_Response(array(
            'success' => true,
        ));
    }

    public function deactivate_all_jobs($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->update(
            $table_name,
            array(
                'is_active' => 0,
            ),
            array('is_active' => 1)
        );

        return new WP_REST_Response(array(
            'success' => true,
        ));
    }

    public function delete_all_jobs($data){
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->query("DELETE from $table_name where 1=1");

        return new WP_REST_Response(array(
            'success' => true,
        ));
    }

    public function activate_all_jobs($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->update(
            $table_name,
            array(
                'is_active' => 1,
            ),
            array('is_active' => 0)
        );

        return new WP_REST_Response(array(
            'success' => true,
        ));
    }

    public function render_auto_writer()
    {
        ?>
        <div class="wrap">
        <h1><?php echo esc_html__( 'AIKit Auto Writer', 'aikit' ); ?></h1>
        <p><?php echo esc_html__( 'AIKit Auto Writer is a tool helps you write drafts quickly, but please review and edit before publishing for best results. This is not a substitute for human editing, but a drafting aid. Happy writing!', 'aikit' ); ?></p>
        <?php

        $this->auto_writer_form->show();

        echo $this->get_posts();

        ?>
        </div>
        <?php
    }

    public function list_posts($data)
    {
        return new WP_REST_Response([
            'body' => $this->get_posts($data['paged'] ?? 1),
        ], 200);
    }

    private function get_posts($page = 1)
    {
        $html = '<table class="table" id="aikit-auto-writer-posts">
            <thead>
            <tr>
                <th scope="col">'.esc_html__( 'Type', 'aikit' ).'</th>
                <th scope="col">'.esc_html__( 'Title', 'aikit' ).'</th>
                <th scope="col">'.esc_html__( 'Date created', 'aikit' ).'</th>
            </tr>
            </thead>
            <tbody>';

        $posts = new WP_Query(array(
            'post_type' => 'any',
            'post_status' => 'any',
            'posts_per_page' => 50,
            'paged' => $page,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'aikit_auto_written',
                    'compare' => '=',
                    'value' => '1'
                ),
                array(
                    'key' => 'aikit_auto_written',
                    'compare' => 'EXISTS'
                )
            )
        ));

        while ( $posts->have_posts() ) {
            $posts->the_post();
            $html .= '<tr>
                <td>'.esc_html( get_post_type_object( get_post_type() )->labels->singular_name).'</td>
                <td><a target="_blank" href="'.esc_url( get_the_permalink() ).'">'.esc_html( get_the_title() ).'</a></td>
                <td>'.esc_html( aikit_date(get_the_date('U')) ).'</td>
            </tr>';
        }

        if ( $posts->found_posts === 0 ) {
            $html .= '<tr>
                <td colspan="3">'.esc_html__( 'No auto-written posts found.', 'aikit' ).'</td>
            </tr>';
        }

        wp_reset_postdata();

        $big = 999999999;
        $html .= '<tr class="aikit-auto-writer-nav">
            <td colspan="3">'.
            paginate_links(array(
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'total' => $posts->max_num_pages,
                'current' => $page,
                'prev_text' => __('« Previous', 'aikit'),
                'next_text' => __('Next »', 'aikit'),
            ))
            .'</td>
        </tr>
    </tbody>
</table>';

        return $html;
    }

    public function handle_request($data)
    {
        if ($data['save_prompts']) {
            $selected_language = aikit_get_language_used();
            update_option('aikit_auto_writer_prompts_' . $selected_language, json_encode($data['prompts']));
        }

        if (isset($data['id'])) {
            return $this->edit_generator($data);
        }

        return $this->add_generator($data);
    }

    public function generate_post($data, $scheduled_generator_id)
    {
        set_time_limit(0);

        $type = $data['type'] ?? 'single-topic';
        $strategy = $data['strategy'] ?? 'random';
        $topic = $data['topic'];
        $include_outline = boolval($data['include_outline']);
        $include_featured_image = boolval($data['include_featured_image']);
        $include_section_images = boolval($data['include_section_images']);
        $include_tldr = boolval($data['include_tldr']);
        $include_conclusion = boolval($data['include_conclusion']);
        $post_type = $data['post_type'];
        $post_category = $data['post_category'];
        $post_status = $data['post_status'];
        $number_of_sections = intval($data['number_of_sections']) ?? 3;
        $section_max_length_in_words = ($data['section_max_length'] ?? 1000);
        $section_max_tokens = intval($section_max_length_in_words * 1.33);
        $temperature = $data['temperature'] ?? 0.7;
        $number_of_articles = intval($data['number_of_articles']) ?? 1;
        $seo_keywords = $data['seo_keywords'] ?? '';
        $prompts = $data['prompts'];
        $post_author = $data['post_author'] ?? get_current_user_id();
        $remaining_topics = $data['remaining_topics'] ?? '';
        $model = $data['model'] ?? get_option('aikit_setting_openai_model');

        if (empty(trim($topic))) {
            return new WP_Error('aikit_auto_writer_missing_topic', __('Please enter a topic.', 'aikit'));
        }

        $resultPosts = [];

        $prompt_name_suffix = '';
        if (!empty(trim($seo_keywords))) {
            $prompt_name_suffix = '-with-seo-keywords';
        }

        try {
            // if type is "multiple-topics", select a random topic from the list if strategy is "random"
            $selected_topic = $topic;
            if ($type === 'multiple-topics') {
                if ($strategy === 'random') {
                    $selected_topic = $this->get_random_topic($topic);
                } else {
                    $selected_topic = $this->get_first_topic($remaining_topics);
                }

                if (empty(trim($selected_topic))) {
                    return new WP_REST_Response([
                        'posts' => $resultPosts,
                    ], 200);
                }
            }

            for ($i=0; $i < $number_of_articles; $i++) {

                $content = '';
                $generated_segments = [];

                $section_headlines = aikit_openai_text_generation_request(
                    $this->build_prompt($prompts['section-headlines' . $prompt_name_suffix], array(
                            'description' => $selected_topic,
                            'number-of-headlines' => $number_of_sections,
                            'keywords' => $seo_keywords,
                        )
                    ), 2000, $temperature, $model);

                $article_intro = aikit_openai_text_generation_request(
                    $this->build_prompt($prompts['article-intro' . $prompt_name_suffix], array(
                            'description' => $selected_topic,
                            'section-headlines' => $section_headlines,
                            'keywords' => $seo_keywords,
                        )
                    ), 2000, $temperature, $model);

                $content = $this->add_text($content, $article_intro);
                $generated_segments['article-intro'] = $article_intro;

                $section_headlines = explode("\n", $section_headlines);
                $section_headlines = array_filter($section_headlines, function ($headline) {
                    return strlen($headline) > 0;
                });
                $section_headlines = array_slice($section_headlines, 0, $number_of_sections);

                if ($include_outline) {
                    $content = $this->add_outline($content, $section_headlines);
                    $generated_segments['section-headlines'] = $section_headlines;
                }

                $title = aikit_openai_text_generation_request(
                    $this->build_prompt($prompts['article-title' . $prompt_name_suffix], array(
                            'description' => $selected_topic,
                            'section-headlines' => implode("\n", $section_headlines),
                            'keywords' => $seo_keywords,
                        )
                    ), 60, $temperature, $model
                );

                $title = $this->clean_title($title);

                $section_summaries = [];

                foreach ($section_headlines as $headline) {
                    $section_content = aikit_openai_text_generation_request(
                        $this->build_prompt($prompts['section' . $prompt_name_suffix], array(
                                'description' => $selected_topic,
                                'section-headline' => $headline,
                                'keywords' => $seo_keywords,
                            )
                        ), $section_max_tokens, $temperature, $model);

                    if ($include_tldr) {
                        $section_summaries[] = aikit_openai_text_generation_request(
                            $this->build_prompt($prompts['section-summary' . $prompt_name_suffix], array(
                                    'section' => $section_content,
                                    'keywords' => $seo_keywords,
                                )
                            ), 2000, $temperature, $model);
                    }

                    $content = $this->add_section_anchor($content, $headline);
                    $content = $this->add_subtitle($content, $headline);

                    if ($include_section_images) {
                        $section_image = aikit_openai_text_generation_request(
                            $this->build_prompt($prompts['image'], array(
                                    'text' => $section_content,
                                )
                            ), 2000, $temperature, $model);

                        $images = aikit_image_generation_request($section_image);
                        $content = $this->add_images($content, $images, $section_image);
                    }

                    $content = $this->add_text($content, $section_content);
                    $generated_segments['section-' . $headline] = $section_content;
                }

                if ($include_conclusion) {
                    $article_conclusion = aikit_openai_text_generation_request(
                        $this->build_prompt($prompts['article-conclusion' . $prompt_name_suffix], array(
                                'description' => $selected_topic,
                                'section-headlines' => implode("\n", $section_headlines),
                                'keywords' => $seo_keywords,
                            )
                        ), 2000, $temperature, $model);

                    $content = $this->add_text($content, $article_conclusion);
                    $generated_segments['article-conclusion'] = $article_conclusion;
                }

                if ($include_tldr) {
                    $text = implode("\n", $section_summaries);

                    $tldr_for_all_sections = aikit_openai_text_generation_request(
                        $this->build_prompt($prompts['tldr' . $prompt_name_suffix], array(
                                'text' => $text,
                                'keywords' => $seo_keywords,
                            )
                        ), 2000, $temperature, $model);

                    $content = $this->prepend_text($content, $tldr_for_all_sections);
                    $generated_segments['tldr'] = $tldr_for_all_sections;
                }

                $post = array(
                    'post_title' => $title,
                    'post_content' => $content,
                    'post_status' => $post_status,
                    'post_author' => $post_author,
                    'post_type' => $post_type,
                    'post_category' => array($post_category)
                );

                aikit_reconnect_db_if_needed();

                $post_id = wp_insert_post($post);

                if (!$post_id) {
                    return new WP_Error( 'auto_writer_error', json_encode([
                        'message' => 'Failed to create post',
                    ]), array( 'status' => 500 ) );
                }

                if ($include_featured_image) {
                    $featured_image = aikit_openai_text_generation_request(
                        $this->build_prompt($prompts['image'], array(
                                'text' => $article_intro,
                            )
                        ), 2000, $temperature, $model);

                    $images = aikit_image_generation_request($featured_image);
                    if (count($images) > 0) {
                        set_post_thumbnail($post_id, $images[0]['id']);
                    }
                }

                add_post_meta($post_id, 'aikit_auto_written', true);

                if (isset($data['scheduled_generator_id'])) {
                    add_post_meta($post_id, self::POST_META_AIKIT_AI_GENERATOR_ID, $data['scheduled_generator_id']);
                }

                if (!empty($seo_keywords)) {
                    wp_set_post_tags($post_id, explode(',', $seo_keywords), true);
                }

                $post_type_obj = get_post_type_object( get_post_type() );

                $resultPosts[] = [
                    'post_id' => $post_id,
                    'post_title' => $title,
                    'post_link' => get_permalink($post_id),
                    'post_date' => get_the_date('Y-m-d H:i:s', $post_id),
                    'post_type' => $post_type_obj->labels->singular_name ?? $post_type,
                    'generated_segments' => $generated_segments,
                ];
            }

        } catch (\Throwable $e) {
            return new WP_Error( 'openai_error', json_encode([
                'message' => 'error while calling openai',
                'responseBody' => $e->getMessage(),
            ]), array( 'status' => 500 ) );
        }

        if ($type === 'multiple-topics' && $strategy === 'once') {
            // remove the topic from the list
            $this->update_generator_remaining_topics(
                $scheduled_generator_id, $this->remove_first_topic($remaining_topics)
            );
        }

        return new WP_REST_Response([
            'posts' => $resultPosts,
        ], 200);
    }

    private function get_random_topic ($topics)
    {
        $exploded = explode("\n", $topics);
        $topics = array_map('trim', $exploded);
        $topics = array_filter($topics);

        return $topics[array_rand($topics)];
    }


    private function get_first_topic($topics)
    {
        $exploded = explode("\n", $topics);
        $topics = array_map('trim', $exploded);
        $topics = array_filter($topics);

        return $topics[0] ?? '';
    }

    private function remove_first_topic($topics)
    {
        $exploded = explode("\n", $topics);
        $topics = array_map('trim', $exploded);
        $topics = array_filter($topics);

        array_shift($topics);

        return implode("\n", $topics);
    }

    private function build_prompt($prompt, $keyValueArray)
    {
        foreach ($keyValueArray as $key => $value) {
            $prompt = str_replace('[[' . $key . ']]', $value, $prompt);
        }

        return $prompt;
    }

    private function add_images($content, $images, $alt = '')
    {
        foreach ($images as $image) {
            $content .= '<img alt="' . $alt . '" src="' . $image['url'] . '" class="wp-image-' . $image['id'] . '" />';
        }

        return $content;
    }


    public function enqueue_scripts($hook)
    {
        // check if the page is not aikit_auto_writer
        if ( 'aikit_page_aikit_auto_writer' !== $hook && 'aikit_page_aikit_scheduler' !== $hook && 'toplevel_page_aikit_auto_writer' !== $hook) {
            return;
        }

        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_style( 'aikit_bootstrap_css', plugins_url( '../../css/bootstrap.min.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_bootstrap_icons_css', plugins_url( '../../css/bootstrap-icons.css', __FILE__ ), array(), $version );
        wp_enqueue_style( 'aikit_auto_writer_css', plugins_url( '../../css/auto-writer.css', __FILE__ ), array(), $version );

        wp_enqueue_script( 'aikit_bootstrap_js', plugins_url('../../js/bootstrap.bundle.min.js', __FILE__ ), array(), $version );
        wp_enqueue_script( 'aikit_jquery_ui_js', plugins_url('../../js/jquery-ui.min.js', __FILE__ ), array('jquery'), $version );
        wp_enqueue_script( 'aikit_auto_writer_js', plugins_url( '../../js/auto-writer.js', __FILE__ ), array( 'jquery' ), $version );
    }

    private function add_outline($content, $section_headlines)
    {
        // add section_headlines in an ul list to the content
        $ul = '<ul>';
        foreach ($section_headlines as $headline) {
            $id = $this->generate_link_anchor_id($headline);
            $ul .= '<li>' . '<a href="#' . $id . '">' . htmlentities($headline) . '</a>' . '</li>';
        }

        $ul .= '</ul>';

        return $content . $ul;
    }

    private function add_section_anchor($content, $headline)
    {
        $id = $this->generate_link_anchor_id($headline);
        return $content . '<section id="' . $id . '"></section>';
    }

    private function generate_link_anchor_id($headline)
    {
        return strtolower(str_replace(' ', '-', $headline));
    }

    private function prepend_text($content, $text)
    {
        // divide text into paragraphs
        $paragraphs = explode("\n", $text);

        $text_to_prepend = '';
        foreach ($paragraphs as $paragraph) {
            if (empty(trim($paragraph))) {
                continue;
            }

            $text_to_prepend .= '<p>' . htmlentities($paragraph) . '</p>';
        }

        return $text_to_prepend . $content;
    }


    private function add_text($content, $text)
    {
        // divide text into paragraphs
        $paragraphs = explode("\n", $text);

        foreach ($paragraphs as $paragraph) {
            if (empty(trim($paragraph))) {
                continue;
            }

            $content .= '<p>' . htmlentities($paragraph) . '</p>';
        }

        return $content;
    }

    private function clean_title($title)
    {
        // remove " and ' from the beginning and end of the title
        $title = trim($title, '"');
        return trim($title, "'");

    }

    private function add_subtitle($content, $subtitle)
    {
        return $content . '<h2>' . htmlentities($subtitle) . '</h2>';
    }

    //////////////////////////////////
    /// Scheduled generators
    //////////////////////////////////
    ///

    public function delete_generator($data)
    {
        if (!isset($data['id'])) {
            return new WP_Error( 'aikit_error', json_encode([
                'message' => 'id is missing',
            ]), array( 'status' => 500 ) );
        }

        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->delete($table_name, ['id' => $data['id']]);

        return new WP_REST_Response([
            'message' => 'ok',
        ], 200);
    }

    public function add_generator($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $next_generation_at = date("Y-m-d H:i:s");

        $entry = [
            'generator_version' => self::GENERATOR_VERSION,
            'post_type' => $data['post_type'],
            'post_status' => $data['post_status'],
            'post_author' => get_current_user_id(),
            'post_category' => $data['post_category'],
            'description' => $data['topic'],
            'remaining_topics' => $data['topic'],
            'keywords' => $data['seo_keywords'],
            'number_of_articles' => $data['number_of_articles'],
            'number_of_sections_per_article' => $data['number_of_sections'],
            'maximum_number_of_words_per_section' => $data['section_max_length'],
            'include_outline' => $data['include_outline'],
            'include_featured_article_image' => $data['include_featured_image'],
            'include_section_images' => $data['include_section_images'],
            'include_conclusion' => $data['include_conclusion'],
            'include_tldr' => $data['include_tldr'],
            'is_active' => true,
            'prompts' => json_encode($data['prompts']),
            'generation_interval' => $data['interval'] ?? 'hourly',
            'last_generated_at' => null,
            'next_generation_at' => $next_generation_at,
            'date_created' => date("Y-m-d H:i:s"),
            'date_modified' => date("Y-m-d H:i:s"),
            'logs' => '{}',
            'is_running' => false,
            'max_number_of_runs' => intval($data['max_runs'] ?? 1),
            'type' => $data['type'],
            'strategy' => $data['strategy'],
            'model' => $data['model'] ?? null,
        ];

        $result = $wpdb->insert( $table_name, $entry );

        if (!$result) {
            return new WP_Error('aikit_auto_writer_schedule_error', __('There was an error scheduling the post generation.', 'aikit'), array('status' => 500));
        }

        $url = admin_url('admin.php?page=aikit_scheduler');

        return new WP_REST_Response([
            'message' => __('Post generation scheduled.', 'aikit'),
            'url' => $url,
        ], 200);
    }

    public function edit_generator($data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $entry = [
            'generator_version' => self::GENERATOR_VERSION,
            'post_type' => $data['post_type'],
            'post_status' => $data['post_status'],
            'post_category' => $data['post_category'],
            'description' => $data['topic'],
            'remaining_topics' => $data['topic'],
            'keywords' => $data['seo_keywords'],
            'number_of_articles' => $data['number_of_articles'],
            'number_of_sections_per_article' => $data['number_of_sections'],
            'maximum_number_of_words_per_section' => $data['section_max_length'],
            'include_outline' => $data['include_outline'],
            'include_featured_article_image' => $data['include_featured_image'],
            'include_section_images' => $data['include_section_images'],
            'include_conclusion' => $data['include_conclusion'],
            'include_tldr' => $data['include_tldr'],
            'is_active' => $data['active'],
            'prompts' => json_encode($data['prompts']),
            'generation_interval' => $data['interval'],
            'date_modified' => date("Y-m-d H:i:s"),
            'max_number_of_runs' => intval($data['max_runs']),
            'type' => $data['type'],
            'strategy' => $data['strategy'],
            'model' => $data['model'] ?? null,
        ];

        $wpdb->update( $table_name, $entry, ['id' => $data['id']] );

        return new WP_REST_Response([
            'message' => __('Post generation edited.', 'aikit'),
        ], 200);
    }

    private function update_generator_remaining_topics($id, $remaining_topics)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $wpdb->update( $table_name, ['remaining_topics' => $remaining_topics], ['id' => $id] );
    }

    public function activate_scheduler()
    {
        if (! wp_next_scheduled ( 'aikit_scheduler')) {
            wp_schedule_event( time(), 'every_10_minutes', 'aikit_scheduler');
        }
    }

    public function deactivate_scheduler()
    {
        wp_clear_scheduled_hook('aikit_scheduler');
    }

    public function render_scheduled_generators()
    {
        if (isset($_GET['id'])) {
            $this->show_edit_view($_GET['id']);

            return;
        }

        $this->show_generators_view();
    }

    public function show_generators_view()
    {
        // url of wp-cron.php
        $cron_url = get_site_url() . '/wp-cron.php';
        $auto_writer_url = admin_url('admin.php?page=aikit_auto_writer');
        ?>
        <div class="wrap">
        <h1>
            <?php echo esc_html__( 'AIKit Scheduled AI Generators', 'aikit' ); ?>
        </h1>

        <p>
            <?php echo esc_html__( 'AIKit Scheduled AI Generators allow you to schedule content to be generated by AI. Please review and edit before publishing for best results. This is not a substitute for human editing, but a drafting aid. Happy writing!', 'aikit' ); ?>
            <a href="#" class="aikit-top-hidden-toggle  btn btn-outline-secondary btn-sm"><i class="bi bi-info-lg"></i> <?php echo esc_html__( 'How to setup?', 'aikit' ); ?></a>
        </p>
        <p class="aikit-top-hidden-note">
            <strong><?php echo esc_html__( 'Note:', 'aikit' ); ?></strong>
            <?php echo esc_html__( 'By default, WordPress scheduled jobs only runs when someone visits your site. To ensure that your scheduled AI generators runs even if nobody visits your site, you can set up a cron job on your server to call the WordPress cron system at regular intervals. Please ask your host provider to do that for you. Here is the cron job definition:', 'aikit' ); ?>
            <code>
                */5 * * * * curl -I <?php echo $cron_url ?> >/dev/null 2>&1
            </code>

            <br>
            <br>

            <strong><?php echo esc_html__( 'Important:', 'aikit' ); ?></strong>
            <?php echo esc_html__( 'Please consider the rate limits of your OpenAI account when setting up generators in order to avoid errors. You can find the rate limits ', 'aikit' ); ?> <a href="https://platform.openai.com/docs/guides/rate-limits" target="_blank"><?php echo esc_html__( 'here', 'aikit' ); ?></a>.
        </p>
        <?php

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $total_jobs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $deactivate_all_url = get_site_url() . '/?rest_route=/aikit/auto-writer/v1/deactivate-all';
        $activate_all_url = get_site_url() . '/?rest_route=/aikit/auto-writer/v1/activate-all';
        $delete_all_url = get_site_url() . '/?rest_route=/aikit/auto-writer/v1/delete-all';

        if ($total_jobs > 0) { ?>
            <div class="row mb-2 float-end">
                <div class="col">
                    <button data-confirm-message="<?php echo esc_html__('Are you sure you want to activate all jobs?', 'aikit') ?>" id="aikit-auto-writer-activate-all" class="btn btn-sm btn-outline-primary ms-2" type="button" href="<?php echo $activate_all_url ?>"><i class="bi bi-play-fill" ></i> <?php echo esc_html__( 'Activate all', 'aikit' ); ?></button>
                    <button data-confirm-message="<?php echo esc_html__('Are you sure you want to deactivate all jobs?', 'aikit') ?>" id="aikit-auto-writer-deactivate-all" class="btn btn-sm btn-outline-primary ms-2" type="button" href="<?php echo $deactivate_all_url ?>"><i class="bi bi-pause-fill" ></i> <?php echo esc_html__( 'Deactivate all', 'aikit' ); ?></button>
                    <button data-confirm-message="<?php echo esc_html__('Are you sure you want to delete all jobs?', 'aikit') ?>" id="aikit-auto-writer-delete-all" class="btn btn-sm btn-outline-danger ms-2" type="button" href="<?php echo $delete_all_url ?>"><i class="bi bi-trash3-fill"></i> <?php echo esc_html__( 'Delete all', 'aikit' ); ?></button>
                </div>
            </div>
            <?php } ?>

            <a href="<?php echo $auto_writer_url?>" data-edit="1" class="btn btn-sm btn-primary d-inline mb-2 float-end" type="button"><i class="bi bi-save"></i> <?php echo esc_html__( 'Add new', 'aikit' ); ?></a>
        <?php

        echo $this->get_ai_generators($_GET['paged'] ?? 1);

        ?>
        </div>
        <?php
    }

    public function show_edit_view ($id) {
        $id = $_GET['id'];
        $generator = $this->get_ai_generator($id);

        if ($generator === null) {
            echo '<h4>' . esc_html__( 'No record found.', 'aikit' ) . '</h4>';
            return;
        }

        ?>
        <div class="wrap">
        <h1><?php echo esc_html__( 'AIKit Scheduled AI Generator', 'aikit' ) . esc_html__( ' - Edit', 'aikit' ) ?></h1>
        <?php

        $this->auto_writer_form->show(
            false,
            true,
            true,
            $generator->description,
            $generator->keywords,
            $generator->post_type,
            $generator->post_category,
            $generator->post_status,
            $generator->number_of_articles,
            $generator->number_of_sections_per_article,
            $generator->maximum_number_of_words_per_section,
            $generator->include_outline,
            $generator->include_featured_article_image,
            $generator->include_section_images,
            $generator->include_conclusion,
            $generator->include_tldr,
            json_decode($generator->prompts, true),
            $generator->id,
            $generator->generation_interval,
            $generator->is_active,
            $generator->max_number_of_runs,
            $generator->type,
            $generator->strategy,
            $generator->model,
        );

        $logs = json_decode($generator->logs, true);
        $generated_posts = $this->get_generated_posts_by_ai_generator_id($id);

        $logsCount = count($logs) == 0 ? '' : ' (' . count($logs) . ')';

        ?>
        <ul class="nav nav-tabs mt-3" id="aikit-scheduler-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#aikit-scheduler-tabs-posts" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true"><?php echo esc_html__( 'Generated Posts', 'aikit' ); ?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#aikit-scheduler-tabs-logs" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false"><?php echo esc_html__( 'Logs', 'aikit' ) . $logsCount ?> </button>
            </li>
        </ul>
        <div class="tab-content mt-2" id="aikit-scheduler-tab-panes">
            <div class="tab-pane fade show active" id="aikit-scheduler-tabs-posts" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                <?php
                $html = '<table class="table" id="aikit-auto-writer-posts">
                                <thead>
                                <tr>
                                    <th scope="col">'.esc_html__( 'Type', 'aikit' ).'</th>
                                    <th scope="col">'.esc_html__( 'Title', 'aikit' ).'</th>
                                    <th scope="col">'.esc_html__( 'Date created', 'aikit' ).'</th>
                                </tr>
                                </thead>
                                <tbody>';

                if (count($generated_posts) === 0) {
                    $html .= '<tr><td colspan="3">'.esc_html__( 'No posts generated yet.', 'aikit' ).'</td></tr>';
                }

                foreach ($generated_posts as $post) {
                    $formatted_date = aikit_date($post->post_date);
                    $html .= '<tr>
                                    <td>'.esc_html($post->post_type).'</td>
                                    <td><a href="'.esc_url(get_permalink($post->ID)).'">'.esc_html($post->post_title).'</a></td>
                                    <td>'.esc_html($formatted_date).'</td>
                                </tr>';
                }

                $html .= '</tbody></table>';

                echo $html;

                ?>
            </div>
            <div class="tab-pane fade" id="aikit-scheduler-tabs-logs" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
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
        </div>

        <?php
    }

    public function do_db_migration()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table_name = $wpdb->prefix . self::TABLE_NAME;
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            ai_provider varchar(255) DEFAULT 'openai',
            generator_version mediumint(9) NOT NULL,
            type varchar(255) NOT NULL DEFAULT 'single-topic',
            strategy varchar(255) NOT NULL DEFAULT 'random',
            post_type varchar(255) NOT NULL,
            post_status varchar(255) NOT NULL,
            post_author mediumint(9) NOT NULL,
            post_category mediumint(9) NOT NULL,
            description TEXT NOT NULL,
            remaining_topics TEXT DEFAULT NULL,
            keywords TEXT DEFAULT NULL,
            number_of_articles mediumint(9) NOT NULL,
            number_of_sections_per_article mediumint(9) NOT NULL,
            maximum_number_of_words_per_section mediumint(9) NOT NULL,
            include_outline BOOLEAN NOT NULL,
            include_featured_article_image BOOLEAN NOT NULL,
            include_section_images BOOLEAN NOT NULL,
            include_conclusion BOOLEAN NOT NULL,
            include_tldr BOOLEAN NOT NULL,
            is_active BOOLEAN NOT NULL,
            prompts TEXT NOT NULL,
            generation_interval varchar(255) NOT NULL,
            last_generated_at datetime DEFAULT NULL,
            next_generation_at datetime DEFAULT NULL,
            date_created datetime DEFAULT NULL,
            date_modified datetime DEFAULT NULL,
            logs TEXT NOT NULL,
            is_running BOOLEAN DEFAULT FALSE,
            max_number_of_runs mediumint(9) DEFAULT 0,
            run_count mediumint(9) DEFAULT 0,
            model varchar(255) DEFAULT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function execute() {

        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $generators = $wpdb->get_results("SELECT * FROM $table_name WHERE is_active = 1 AND is_running = 0 AND (max_number_of_runs = 0 OR run_count < max_number_of_runs)");

        foreach ($generators as $generator) {
            $should_keep_running = true;
            $now = new DateTime();
            $next_generation_at = new DateTime($generator->next_generation_at);

            if ($now >= $next_generation_at || $generator->next_generation_at === null) {
                $wpdb->update(
                    $table_name,
                    [
                        'is_running' => 1,
                    ],
                    [
                        'id' => $generator->id,
                    ]
                );

                try {
                    $result = $this->generate_post([
                        'topic' => $generator->description,
                        'include_outline' => boolval($generator->include_outline),
                        'include_featured_image' => boolval($generator->include_featured_article_image),
                        'include_section_images' => boolval($generator->include_section_images),
                        'include_tldr' => boolval($generator->include_tldr),
                        'include_conclusion' => boolval($generator->include_conclusion),
                        'post_type' => $generator->post_type,
                        'post_category' => $generator->post_category,
                        'post_status' => $generator->post_status,
                        'number_of_sections' => $generator->number_of_sections_per_article,
                        'section_max_length' => $generator->maximum_number_of_words_per_section,
                        'temperature' => 0.7,
                        'number_of_articles' => $generator->number_of_articles,
                        'seo_keywords' => $generator->keywords,
                        'prompts' => json_decode($generator->prompts, true),
                        'post_author' => $generator->post_author,
                        'scheduled_generator_id' => $generator->id,
                        'type' => $generator->type,
                        'strategy' => $generator->strategy,
                        'remaining_topics' => $generator->remaining_topics,
                        'model' => $generator->model,
                    ], $generator->id);

                } catch (Throwable $e) {
                    // if message contains 429 then we should stop the job
                    if (strpos($e->getMessage(), '429') !== false) {
                        $should_keep_running = false;
                    }

                    $result = new WP_Error('aikit_auto_writer_error', $e->getMessage());
                }

                $logs = json_decode($generator->logs, true);
                $newLogEntry = [
                    'date' => $now->format('Y-m-d H:i:s'),
                ];

                $is_success = false;
                if ($result instanceof WP_Error) {
                    $newLogEntry['success'] = false;
                    $newLogEntry['error'] = $result->get_error_message();
                    $newLogEntry['code'] = $result->get_error_code();
                } else if ($result instanceof WP_REST_Response) {
                    $newLogEntry['success'] = true;
                    $post_ids = [];
                    if (isset($result->data['posts']) && is_array($result->data['posts'])) {
                        foreach ($result->data['posts'] as $post) {
                            $post_ids[] = $post['post_id'];
                        }
                    }
                    $newLogEntry['post_ids'] = $post_ids;
                    $is_success = true;
                }

                // make sure only 25 logs are stored
                if (count($logs) > 25) {
                    array_shift($logs);
                }

                $entry = [
                    'is_running' => 0,
                    'last_generated_at' => $now->format('Y-m-d H:i:s'),
                    'next_generation_at' => $this->calculate_next_generation_date($now->format('Y-m-d H:i:s'), $generator->generation_interval, $is_success),
                    'logs' => json_encode(array_merge($logs, [$newLogEntry])),
                ];

                if ($is_success) {
                    $entry['run_count'] = $generator->run_count + 1;
                }

                aikit_reconnect_db_if_needed();

                $wpdb->update(
                    $table_name,
                    $entry,
                    [
                        'id' => $generator->id,
                    ]
                );
            }

            if (!$should_keep_running) {
                break;
            }
        }
    }

    private function calculate_next_generation_date($last_generation_time, $interval, $is_success = true)
    {
        // if it failed, try again in 10 minutes
        if (!$is_success) {
            return date('Y-m-d H:i:s', strtotime($last_generation_time) + (10 * 60));
        }

        $hours_to_add = 1;

        if ($interval === 'every30mins') {
            $hours_to_add = 0.5;
        } else if ($interval === 'twicedaily') {
            $hours_to_add = 12;
        } else if ($interval === 'daily') {
            $hours_to_add = 24;
        } else if ($interval === 'everyotherday') {
            $hours_to_add = 24 * 2;
        } else if ($interval === 'twiceweekly') {
            $hours_to_add = intval(24 * 3.5);
        } else if ($interval === 'weekly') {
            $hours_to_add = 24 * 7;
        } else if ($interval === 'fortnightly') {
            $hours_to_add = 24 * 14;
        } else if ($interval === 'monthly') {
            $hours_to_add = 24 * 30;
        } else if ($interval === 'once') {
            return null;
        }

        $next_generation_time = strtotime($last_generation_time) + ($hours_to_add * 60 * 60);

        return date('Y-m-d H:i:s', $next_generation_time);
    }

    private function get_generated_posts_by_ai_generator_id($id)
    {
        $args = [
            'post_type' => 'any',
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => self::POST_META_AIKIT_AI_GENERATOR_ID,
                    'value' => $id,
                    'compare' => '=',
                ],
                [
                    'key' => self::POST_META_AIKIT_AI_GENERATOR_ID,
                    'compare' => 'EXISTS',
                ],
            ],
        ];

        $query = new WP_Query($args);

        return $query->posts;
    }

    private function get_ai_generators($paged = 1)
    {
        $per_page = 25;
        $html = '<table class="table" id="aikit-scheduler-generators">
            <thead>
            <tr>
                <th scope="col">' . esc_html__('Topic(s)', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Keywords', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Status', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Interval', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Last generation', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Next generation', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Is running', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Run count', 'aikit') . '</th>
                <th scope="col">' . esc_html__('Max run count', 'aikit') . '</th>               
                <th scope="col">' . esc_html__('Actions', 'aikit') . '</th>
            </tr>
            </thead>
            <tbody>';

        // get all generators from DB
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        // prepared statement to prevent SQL injection with pagination
        $generators = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY id DESC LIMIT %d, %d", ($paged - 1) * $per_page, $per_page));

        if (empty($generators)) {
            $html .= '<tr>
                <td colspan="10">' . esc_html__('No entries found', 'aikit') . '</td>
            </tr>';
        }

        $scheduler_page_url = get_admin_url() . 'admin.php?page=aikit_scheduler';
        $delete_url = get_site_url() . '/?rest_route=/aikit/auto-writer/v1/delete-generator';

        foreach ($generators as $generator) {
            $current_page_url = $scheduler_page_url . '&id=' . $generator->id;

            if ($generator->max_number_of_runs > 0 && $generator->run_count >= $generator->max_number_of_runs) {
                $next_generation_at = '-';
            } else {
                $next_generation_at = (empty($generator->next_generation_at) ? '-' : aikit_date($generator->next_generation_at));
            }

            // no more than 100 characters
            $description = $generator->description;
            if (strlen($description) > 100) {
                $description = substr($description, 0, 100) . '...';
            }

            $html .= '<tr>
                <td>' . '<a href="' . $current_page_url . '">' . esc_html($description) . '</a></td>
                <td>' . (empty($generator->keywords) ? '-' : $generator->keywords) . '</td>
                <td>' . ($generator->is_active ? ('<span class="badge badge-pill badge-dark aikit-badge-active">' . __('Active', 'aikit')) : ('<span class="badge badge-pill badge-dark aikit-badge-inactive">' . __('Inactive', 'aikit'))) . '</span></td>
                <td>' . $this->auto_writer_form->intervals($generator->generation_interval) . '</td>
                <td>' . (empty($generator->last_generated_at) ? '-' : aikit_date( strtotime($generator->last_generated_at) )) . '</td>
                <td>' . $next_generation_at . '</td>
                <td>' . ($generator->is_running ? ('<span class="badge badge-pill badge-dark aikit-badge-active">' . __('Yes', 'aikit')) : ('<span class="badge badge-pill badge-dark aikit-badge-inactive">' . __('No', 'aikit'))) . '</span></td>
                <td>' . $generator->run_count . '</td>
                <td>' . ($generator->max_number_of_runs === 0 ? '-' : $generator->max_number_of_runs) . '</td>
                <td>
                    <a href="' . $scheduler_page_url . '&id=' . $generator->id . '" title="' . __('Edit', 'aikit') . '" class="aikit-scheduler-generators-edit" data-id="' . $generator->id . '"><i class="bi bi-pencil"></i></a>
                    <a href="' . $delete_url . '" title="' . __('Delete', 'aikit') . '" class="aikit-scheduler-generators-delete" data-confirm-message="' . __('Are you sure you want to delete this scheduled generator?', 'aikit') . '" data-id="' . $generator->id . '"><i class="bi bi-trash-fill"></i></a>
                </td>
            </tr>';
        }

        // pagination
        $total_generators = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $total_pages = ceil($total_generators / $per_page);
        $html .= '<tr>
            <td colspan="10">';

        $url = get_admin_url() . 'admin.php?page=aikit_scheduler';
        // previous page

        $html .= '<div class="aikit-scheduler-generators-pagination">';
        if ($paged > 1) {
            $html .= '<a href="' . $url . '&paged=' . ($paged - 1) . '">' . __('« Previous', 'aikit') . '</a>';
        }

        for ($i = 1; $i <= $total_pages; $i++) {
            // add class to current page
            $current_page_class = '';
            if ($paged == $i) {
                $current_page_class = 'aikit-scheduler-generators-pagination-current';
            }

            $html .= '<a class="' . $current_page_class . '" href="' . $url . '&paged=' . $i . '" data-page="' . $i . '">' . $i . '</a>';
        }

        // next page
        if ($paged < $total_pages) {
            $html .= '<a href="' . $url . '&paged=' . ($paged + 1) . '">' . __('Next »', 'aikit') . '</a>';
        }

        $html .= '</div>';

        $html .= '</td>
            </tr>';
        $html .= '</tbody>
        
        </table>';

        return $html;
    }

    private function get_ai_generator($id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $generator = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id));

        if (empty($generator)) {
            return false;
        }

        return $generator;
    }
}
