<?php

class AIKIT_Post_Finder extends AIKIT_Page{

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance == null) {
            self::$instance = new AIKIT_Post_Finder();
        }
        return self::$instance;
    }

    public function __construct()
    {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'aikit/post-finder/v1', '/find', array(
                'methods' => 'POST',
                'callback' => array($this, 'search_posts'),
                'permission_callback' => function () {
                    return is_user_logged_in() && current_user_can( 'edit_posts' );
                }
            ));

        });
    }

    public function search_posts($data)
    {
        $search_string = $data['search_term'];
        $post_type = $data['post_type'] ?? 'any';

        // match any post status
        $args = array(
            'posts_per_page' => -1,
            's' => $search_string,
            'post_type' => $post_type,
            'post_status' => 'any',
        );

        $query = new WP_Query($args);

        // Check if any posts were found
        if ($query->have_posts()) {
            $posts = $query->posts;

            $return_array = array();
            foreach ($posts as $post) {
                $return_array[] = array(
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'url' => get_permalink($post->ID),
                    'type' => $post->post_type,
                );
            }
        } else {
            $return_array = array();
        }

        return new WP_REST_Response($return_array, 200);
    }

    public function render($post_ids = [], $heading_message = '')
    {
        $post_type_array = array('any'=> __('Any', 'aikit'));

        $post_types = get_post_types( array( 'public' => true ), 'objects' );
        foreach ( $post_types as $post_type ) {
            $post_type_array[$post_type->name] = $post_type->label;
        }

        ?>
       <div class="aikit-post-finder">
           <p>
               <?php echo $heading_message; ?>
           </p>
        <div class="row mb-2 ">
            <div class="col-4">
                <?php
                $this->_text_box(
                    'aikit-post-finder-search',
                    __('Search for a post/page', 'aikit'),
                    null,
                    'text',
                    null,
                );
                ?>
            </div>
            <div class="col-2">
                <?php
                $this->_drop_down(
                    'aikit-post-finder-post-type',
                    __('Post Type', 'aikit'),
                    $post_type_array,
                    null
                );
                ?>
            </div>

            <div class="col-2 mt-3">
                <button class="btn btn-outline-primary" type="button" id="aikit-post-finder-search-button"> <i class="bi bi-search"></i> <?php _e('Search', 'aikit'); ?></button>
            </div>

        </div>

        <div class="row mb-2">

        </div>

        <div class="row mb-2">
            <div class="col">
                <div class="aikit-post-finder-search-result-container">
                    <h6><?php _e('Search Results', 'aikit'); ?></h6>
                    <a class="float-end" href="#" id="aikit-post-finder-add-all"><i class="bi bi-plus-circle-fill"></i> <?php _e('Add All', 'aikit'); ?></a>
                </div>
                <div id="aikit-post-finder-search-results"></div>
            </div>
            <div class="col">
                <h6><?php _e('Selected Posts', 'aikit'); ?></h6>

                <div id="aikit-post-finder-selected-results">
                    <?php

                    if (!empty($post_ids)) {

                        foreach ($post_ids as $post_id) {
                            // display the posts
                            $post = get_post($post_id);
                            ?>
                                <div class="aikit-post-finder-result">
                                    <a href="<?php echo get_permalink($post_id); ?>" target="_blank"><?php echo $post->post_title; ?></a>
                                    <a class="aikit-post-finder-remove-post" data-post-id="<?php echo $post_id ?>"><i class="bi bi-dash-circle-fill"></i></a>
                                </div>
                            <?php
                        }

                    }

                    ?>
                </div>
            </div>
        </div>
       </div>

        <input type="hidden" id="aikit-post-finder-selected-posts" name="aikit-post-finder-selected-posts" value="<?php echo implode(',', $post_ids); ?>">

        <?php
    }

    public function enqueue_scripts()
    {
        $version = aikit_get_plugin_version();
        if ($version === false) {
            $version = rand( 1, 10000000 );
        }

        wp_enqueue_script( 'aikit_post_finder_js', plugins_url( '../js/post-finder.js', __FILE__ ), array( 'jquery' ), array(), $version );
        wp_enqueue_style( 'aikit_post_finder_css', plugins_url( '../css/post-finder.css', __FILE__ ), array(), $version );
    }


}