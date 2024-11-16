<?php if (!defined('ABSPATH')) exit; ?><div id="qfb-filter-settings">

    <div class="qfb-settings">

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If checked, any spaces or tabs will not be stripped', 'quform'); ?></div></div>
                <label for="qfb_f_allow_white_space"><?php esc_html_e('Allow whitespace', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="checkbox" class="qfb-toggle" id="qfb_f_allow_white_space">
                    <label for="qfb_f_allow_white_space"></label>
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Any text matching this regular expression pattern will be stripped. The pattern should include start and end delimiters, see below for an example.', 'quform'); ?><pre><?php echo esc_html('/[a-zA-Z0-9]+/'); ?></pre></div></div>
                <label for="qfb_f_pattern"><?php esc_html_e('Pattern', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_f_pattern">
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter allowable tags, one after the other, for example:', 'quform'); ?><pre>&lt;p&gt;&lt;br&gt;&lt;span&gt;</pre></div></div>
                <label for="qfb_f_allowable_tags"><?php esc_html_e('Allowable tags', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_f_allowable_tags">
                </div>
            </div>
        </div>

    </div>

</div>
