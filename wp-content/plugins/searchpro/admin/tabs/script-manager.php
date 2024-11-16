<?php
if (!defined('ABSPATH'))
    exit;

// JS and CSS exclusions
$exclude_urls = get_option('berq_exclude_js_css', []);
$url_lines = implode("\n", $exclude_urls);

?>
<div id="script-manager" style="display:none">
    <h2 class="berq-tab-title">
        <?php esc_html_e('Script Manager', 'searchpro'); ?>
    </h2>
    <?php if ($this->is_key_verified) { ?>
        <div class="berq-info-box">
            <h3 class="berq-box-title">
                <?php esc_html_e('Exclude JS & CSS', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Enter external JavaScript & CSS file URLs to exclude from optimization, one URL per line. Add relative URLs or filenames with no query parameters, like /wp-includes/js/jquery/jquery.min.js', 'searchpro'); ?>
                </p>
                <textarea name="berq_exclude_js_css" cols="30" rows="10"><?php echo esc_textarea($url_lines); ?></textarea>
            </div>
        </div>
    <?php } ?>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Preload font faces', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('Preload font faces along with the critical CSS upon the initial page load.', 'searchpro'); ?>
            </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_preload_fontfaces" <?php checked(1, get_option('berqwp_preload_fontfaces'), true); ?>>
                <?php esc_html_e('Enable preload font faces', 'searchpro'); ?>
            </label>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('Disable emojis', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <p>
                <?php esc_html_e('The WordPress emoji script loads on all pages, which can slow down the loading speed.
                If
                your website doesn\'t use emojis, it would be better to disable them.', 'searchpro'); ?>
            </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_disable_emojis" <?php checked(1, get_option('berqwp_disable_emojis'), true); ?>>
                <?php esc_html_e('Disable WordPress emojis', 'searchpro'); ?>
            </label>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title">
            <?php esc_html_e('LazyLoad YouTube embeds', 'searchpro'); ?>
        </h3>
        <div class="berq-box-content">
            <label class="berq-check">
                <input type="checkbox" name="berqwp_lazyload_youtube_embed" <?php checked(1, get_option('berqwp_lazyload_youtube_embed'), true); ?>>
                <?php esc_html_e('Enable lazyload for YouTube videos', 'searchpro'); ?>
            </label>
        </div>
    </div>

    <?php if ($this->is_key_verified) { ?>
        <div class="berq-info-box">
            <h3 class="berq-box-title">
                <?php esc_html_e('JavaScript execution mode', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('BerqWP offers three JavaScript optimization modes, so every JavaScript-heavy website can unlock its true potential.', 'searchpro'); ?>
                </p>
                <label class="berq-check">
                    <input type="radio" name="berqwp_javascript_execution_mode" value="1" <?php echo get_option('berqwp_javascript_execution_mode') == 1 ? 'checked' : ''; ?>>
                    <?php esc_html_e('Delay & inline first execution (Recommended)', 'searchpro'); ?>
                </label>
                <label class="berq-check">
                    <input type="radio" name="berqwp_javascript_execution_mode" value="0" <?php echo get_option('berqwp_javascript_execution_mode') == 0 ? 'checked' : ''; ?>>
                    <?php esc_html_e('Sequential execution', 'searchpro'); ?>
                </label>
                <label class="berq-check">
                    <input type="radio" name="berqwp_javascript_execution_mode" value="2" <?php echo get_option('berqwp_javascript_execution_mode') == 2 ? 'checked' : ''; ?>>
                    <?php esc_html_e('Defer JavaScript (Safe)', 'searchpro'); ?>
                </label>
            </div>
        </div>
    <?php } ?>

    <?php if ($this->is_key_verified) { ?>
        <div class="berq-info-box">
            <h3 class="berq-box-title">
                <?php esc_html_e('Trigger Interaction', 'searchpro'); ?>
            </h3>
            <div class="berq-box-content">
                <p>
                    <?php esc_html_e('Trigger an interaction after the page has been loaded. The default is empty.', 'searchpro'); ?>
                </p>
                <table class="berq-image-settings">
                    <tr>
                        <td>
                            <p>
                                <?php esc_html_e('Trigger after:', 'searchpro'); ?>
                            </p>
                        </td>
                        <td><input type="number" min="0" name="berqwp_interaction_delay"
                                value="<?php echo esc_attr(get_option('berqwp_interaction_delay')); ?>" style="width:60px">
                            ms</td>
                    </tr>
                </table>
            </div>
        </div>
    <?php } ?>
    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e('Save changes', 'searchpro'); ?>
    </button>
</div>