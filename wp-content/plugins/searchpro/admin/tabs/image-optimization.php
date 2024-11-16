<?php
if (!defined('ABSPATH'))
    exit;
?>
<div id="image-optimization" style="display:none">
    <h2 class="berq-tab-title"><?php esc_html_e('Image Optimization', 'searchpro'); ?></h2>
    <div class="berqwp-webp">
        <div class="berqwp-webp-content">
            <div class="berq-box-content">
                <h3 class="berq-box-title"><?php esc_html_e('WebP conversion', 'searchpro'); ?></h3>
                <p><?php esc_html_e('BerqWP automatically converts images into the WebP format when creating the page cache. This helps improve website performance by reducing image file sizes without compromising quality. It does not modify the original images.', 'searchpro'); ?></p>
                <table class="berq-image-settings">
                    <tr>
                        <td>
                            <p><?php esc_html_e('Max image width:', 'searchpro'); ?></p>
                        </td>
                        <td><input type="number" min="0" name="berqwp_webp_max_width"
                                value="<?php echo esc_attr(get_option('berqwp_webp_max_width')); ?>" style="width:100px">
                            px</td>
                    </tr>
                    <tr>
                        <td>
                            <p><?php esc_html_e('Image quality:', 'searchpro'); ?></p>
                        </td>
                        <td><input type="number" min="0" max="100" name="berqwp_webp_quality"
                                value="<?php echo esc_attr(get_option('berqwp_webp_quality')); ?>" style="width:100px"> %
                        </td>
                    </tr>
                </table>

                <div class="berq-img-action">
                    <div class="berq-convert-webp" style="display:none"><?php esc_html_e('Bulk convert to WebP', 'searchpro'); ?></div>
                    <div class="berq-delete-webp"><?php esc_html_e('Delete all WebP images', 'searchpro'); ?></div>
                </div>
            </div>
            <div class="berqwp-webp-chart" style="display: none;">
                <div class="berq-progress-circles">
                    <div class="berq-progress-optimized-images"></div>
                    <div class="berq-generating-images">
                        <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                    </div>
                    <div class="berq-optimized-images"><?php esc_html_e('Optimized Images:', 'searchpro'); ?> <span></span></div>
                    <div class="berq-unoptimized-images"><?php esc_html_e('Unoptimized Images:', 'searchpro'); ?> <span></span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('Disable WebP images', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e('Disabling WebP images may be helpful when you\'re using other tools to generate WebP images.', 'searchpro'); ?>
            </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_disable_webp" <?php checked(1, get_option('berqwp_disable_webp'), true); ?>>
                <?php esc_html_e('Disable WebP images', 'searchpro'); ?>
            </label>
        </div>
    </div>
    <div class="berq-info-box">
        <h3 class="berq-box-title"><?php esc_html_e('LazyLoad images', 'searchpro'); ?></h3>
        <div class="berq-box-content">
            <p><?php esc_html_e('Optimize your web page loading time by loading only the images that are visible on
                the screen. The remaining images will be loaded as soon as the user scrolls to them.', 'searchpro'); ?>
            </p>
            <label class="berq-check">
                <input type="checkbox" name="berqwp_image_lazyloading" <?php checked(1, get_option('berqwp_image_lazyloading'), true); ?>>
                <?php esc_html_e('Enable lazyload for images', 'searchpro'); ?>
            </label>
        </div>
    </div>
    <button type="submit" class="berqwp-save"><svg width="20" height="20" viewBox="0 0 20 20" fill="none"
            xmlns="http://www.w3.org/2000/svg">
            <path d="M4.16663 10.8333L7.49996 14.1667L15.8333 5.83334" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <?php esc_html_e('Save changes', 'searchpro'); ?></button>
</div>