<?php if (!defined('ABSPATH')) exit;
/** @var Quform_Admin_Page_Help $page */
?><div id="top" class="qfb qfb-cf">
    <?php
        echo $page->getMessagesHtml();
        echo $page->getNavHtml();
    ?>

    <div class="qfb-help qfb-cf">

        <div class="qfb-db-row qfb-cf">
            <div class="qfb-db-col">

                <div class="qfb-box">
                    <h3 class="qfb-box-heading qfb-db-heading"><i class="qfb-mdi qfb-mdi-help_outline"></i> <?php esc_html_e('Support', 'quform'); ?></h3>
                    <div class="qfb-content">
                        <div class="qfb-cf qfb-db-about-text">
                            <h2><?php esc_html_e('If you need assistance, see our help resources.', 'quform'); ?></h2>
                            <p><?php esc_html_e('Please make a search to find help with your problem, or head over to our support forum to ask a question.', 'quform'); ?></p>
                            <div class="qfb-db-row qfb-cf">
                                <div class="qfb-db-col">
                                    <a class="qfb-db-forum-button" href="https://support.themecatcher.net/"><i class="qfb-icon qfb-icon-life-ring"></i><?php esc_html_e('Visit help site', 'quform'); ?></a>
                                </div>
                                <div class="qfb-db-col">
                                    <a class="qfb-db-forum-button" href="https://support.themecatcher.net/quform-wordpress-v2/"><i class="qfb-icon qfb-icon-book"></i><?php esc_html_e('View documentation', 'quform'); ?></a>
                                </div>
                                <div class="qfb-db-col">
                                    <a class="qfb-db-forum-button" href="https://support.themecatcher.net/forums"><i class="qfb-icon qfb-icon-comments"></i><?php esc_html_e('Support forum', 'quform'); ?></a>
                                </div>
                            </div>
                        </div>
                        <div class="qfb-cf qfb-db-form">
                            <form action="https://support.themecatcher.net/" method="get">
                                <input type="hidden" name="c" value="5">
                                <div class="qfb-db-form-button">
                                    <button class="qfb-button"><?php esc_html_e('Search', 'quform'); ?></button>
                                </div>
                                <div class="qfb-db-form-input">
                                    <input type="text" name="s" placeholder="<?php esc_attr_e('Enter search query', 'quform'); ?>"><i class="qfb-icon qfb-icon-search"></i>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
            <div class="qfb-db-col">

                <div class="qfb-db-col qfb-last">
                    <div class="qfb-box">
                        <h3 class="qfb-box-heading qfb-db-heading"><i class="qfb-mdi qfb-mdi-favorite_border"></i> <?php esc_html_e('About us', 'quform'); ?><a class="qfb-logo-icon" href="https://www.quform.com"></a></h3>
                        <div class="qfb-content">

                            <div class="qfb-cf qfb-db-about-text">
                                <h2><?php esc_html_e('Quform is a drag and drop form builder to make building forms easy and enjoyable.', 'quform'); ?></h2>

                                <p>
                                    <?php
                                        printf(
                                            /* translators: %1$s: open link tag, %2$s: close link tag */
                                            esc_html__('Quform was developed by ThemeCatcher and is %1$savailable for purchase%2$s on CodeCanyon.', 'quform'),
                                            '<a href="https://www.quform.com/buy" target="_blank">',
                                            '</a>'
                                        );
                                    ?>
                                </p>

                                <p><?php esc_html_e('We work hard to give you an exceptional premium products and 5 star support. To show your appreciation you can buy us a coffee or simply by sharing or follow us on social media.', 'quform'); ?></p>
                            </div>

                            <div class="qfb-cf qfb-db-social-links">
                                <ul>
                                    <li class="qfb-buy-coffee"><a href="https://www.themecatcher.net/#buy-us-a-coffee"><i class="qfb-icon qfb-icon-coffee"></i> <?php esc_html_e('Buy us a coffee', 'quform'); ?></a></li>
                                    <li class="qfb-facebook"><a href="https://www.facebook.com/ThemeCatcher"><i class="qfb-icon qfb-icon-facebook"></i> <?php esc_html_e('Like us', 'quform'); ?></a></li>
                                    <li class="qfb-twitter"><a href="https://twitter.com/ThemeCatcher"><i class="qfb-icon qfb-icon-twitter"></i> <?php esc_html_e('Tweet us', 'quform'); ?></a></li>
                                    <li class="qfb-envato"><a href="https://codecanyon.net/user/themecatcher"><i class="qfb-icon qfb-icon-leaf"></i> <?php esc_html_e('Follow us', 'quform'); ?></a></li>
                                </ul>
                            </div>

                            <p><a href="https://www.themecatcher.net" class="qfb-link">www.themecatcher.net</a> | <a href="https://www.quform.com" class="qfb-link">www.quform.com</a></p>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>
