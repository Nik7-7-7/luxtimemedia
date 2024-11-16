<?php
if (!defined('ABSPATH')) exit;

// Page exclusions
$exclude_urls = get_option('berq_exclude_urls', []);
$url_lines = implode("\n", $exclude_urls);

// Ignore URL params
$ignore_params = get_option('berq_ignore_urls_params', []);
$param_lines = implode("\n", $ignore_params);

$post_type_names = get_post_types(array(
    'public' => true,
    'exclude_from_search' => false,
), 'names');
unset($post_type_names['attachment']);

$taxonomy_names = get_taxonomies(array(
    'public' => true,
    'show_in_rest' => true
), 'names');



?>
<div id="cache-management" style="display:none">
    <h2 class="berq-tab-title"><?php esc_html_e('Cache Management', 'searchpro'); ?></h2>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Page exclusions', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e('Exclude pages from being cached. Enter page URLs, one URL per line.', 'searchpro'); ?> <a href="https://berqwp.com/help-center/exclude-pages-from-being-cached/" target="_blank"><?php esc_html_e('Learn more', 'searchpro'); ?></a></p>
            <textarea name="berq_exclude_urls" cols="30" rows="10"><?php echo esc_textarea($url_lines); ?></textarea>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Content types', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e('Choose which post types and archive pages should be cached.', 'searchpro'); ?></p>
            <div class="optimize-post-types">
            <?php
                foreach($post_type_names as $key => $value) {
                    ?>
                    <div>
                        <input type="checkbox" name="berqwp_optimize_post_types[]" value="<?php echo esc_attr($key); ?>" <?php checked(1, in_array($value, get_option('berqwp_optimize_post_types')), true); ?> >

                        <?php
                        $post_counts = wp_count_posts($value);
                        $published_count = isset($post_counts->publish) ? $post_counts->publish : 0; 
                        echo esc_html(ucfirst($key) . " ($published_count)"); 

                        ?>
                    </div>
                    <?php
                } 
                foreach($taxonomy_names as $key => $value) {
                    ?>
                    <div>
                        <input type="checkbox" name="berqwp_optimize_taxonomies[]" value="<?php echo esc_attr($key); ?>" <?php checked(1, in_array($value, get_option('berqwp_optimize_taxonomies')), true); ?> >

                        <?php
                        echo esc_html(ucfirst($key)); 
                        ?>
                    </div>
                    <?php
                } 
            ?>

            </div>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Ignore URL parameters', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e("Ignore page URL parameters, these parameters will be disregarded and won't be cached separately. Enter one parameter per line."); ?> <a href="https://berqwp.com/help-center/ignore-url-parameters/" target="_blank"><?php esc_html_e('Learn more', 'searchpro'); ?></a></p>
            <textarea name="berq_ignore_urls_params" cols="30" rows="10"><?php echo esc_textarea($param_lines); ?></textarea>
        </div>
    </div>
    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e("Save changes", "searchpro"); ?></button>
</div>