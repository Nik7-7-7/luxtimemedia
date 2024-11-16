<?php if (!defined('ABSPATH')) exit;
/* @var Quform_Builder $builder */
?><div id="qfb-validator-settings">

    <div class="qfb-settings">

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('If checked, any spaces or tabs are allowed', 'quform'); ?></div></div>
                <label for="qfb_v_allow_white_space"><?php esc_html_e('Allow whitespace', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="checkbox" class="qfb-toggle" id="qfb_v_allow_white_space">
                    <label for="qfb_v_allow_white_space"></label>
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The submitted value must match this regular expression. The pattern should include start and end delimiters, see below for an example.', 'quform'); ?><pre><?php echo esc_html('/^[a-zA-Z0-9]+$/'); ?></pre></div></div>
                <label for="qfb_v_pattern"><?php esc_html_e('Pattern', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_v_pattern">
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Invert the check i.e. the submitted value must not match the regular expression', 'quform'); ?></div></div>
                <label for="qfb_v_invert"><?php esc_html_e('Invert', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="checkbox" class="qfb-toggle" id="qfb_v_invert">
                    <label for="qfb_v_invert"></label>
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The submitted value must be numerically greater than the minimum', 'quform'); ?></div></div>
                <label for="qfb_v_greater_than_min"><?php esc_html_e('Minimum', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_v_greater_than_min">
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Allow the minimum value too', 'quform'); ?></div></div>
                <label for="qfb_v_greater_than_inclusive"><?php esc_html_e('Inclusive', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="checkbox" class="qfb-toggle" id="qfb_v_greater_than_inclusive">
                    <label for="qfb_v_greater_than_inclusive"></label>
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The length of the submitted value must be greater than or equal to the minimum', 'quform'); ?></div></div>
                <label for="qfb_v_length_min"><?php esc_html_e('Minimum length', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_v_length_min">
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The length of the submitted value must be less than or equal to the maximum', 'quform'); ?></div></div>
                <label for="qfb_v_length_max"><?php esc_html_e('Maximum length', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_v_length_max">
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The token that the submitted value must be equal to', 'quform'); ?></div></div>
                <label for="qfb_v_token"><?php esc_html_e('Token', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_v_token">
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Enter one allowed value per line', 'quform'); ?></div></div>
                <label for="qfb_v_haystack"><?php esc_html_e('Allowed values', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <textarea id="qfb_v_haystack"></textarea>
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Invert the check i.e. the submitted value must not be in the allowed values list', 'quform'); ?></div></div>
                <label for="qfb_v_in_array_invert"><?php esc_html_e('Invert', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="checkbox" class="qfb-toggle" id="qfb_v_in_array_invert">
                    <label for="qfb_v_in_array_invert"></label>
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('The submitted value must be numerically less than this value', 'quform'); ?></div></div>
                <label for="qfb_v_less_than_max"><?php esc_html_e('Maximum', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="text" id="qfb_v_less_than_max">
                </div>
            </div>
        </div>

        <div class="qfb-setting">
            <div class="qfb-setting-label">
                <div class="qfb-tooltip-icon"><div class="qfb-tooltip-content"><?php esc_html_e('Allow the maximum value too', 'quform'); ?></div></div>
                <label for="qfb_v_less_than_inclusive"><?php esc_html_e('Inclusive', 'quform'); ?></label>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <input type="checkbox" class="qfb-toggle" id="qfb_v_less_than_inclusive">
                    <label for="qfb_v_less_than_inclusive"></label>
                </div>
            </div>
        </div>

        <div class="qfb-settings-heading"><?php esc_html_e('Error messages', 'quform'); ?></div>

        <div id="qfb-v-error-message-headings" class="qfb-setting">
            <div class="qfb-setting-label">
                <?php esc_html_e('Default', 'quform'); ?>
            </div>
            <div class="qfb-setting-inner">
                <div class="qfb-setting-input">
                    <?php esc_html_e('Custom message', 'quform'); ?>
                </div>
            </div>
        </div>

        <?php
        // Translations for validator error messages
        // 'class' => array('messageKeyConstant' => 'tooltip')
        $validatorTranslations = array(
            'Quform_Validator_Alpha' => array(
                'NOT_ALPHA' => array(
                    '%value%' => __('the submitted value', 'quform')
                )
            ),
            'Quform_Validator_AlphaNumeric' => array(
                'NOT_ALNUM' => array(
                    '%value%' => __('the submitted value', 'quform')
                )
            ),
            'Quform_Validator_Digits' => array(
                'NOT_DIGITS' => array(
                    '%value%' => __('the submitted value', 'quform')
                )
            ),
            'Quform_Validator_Duplicate' => array(
                'IS_DUPLICATE' => array()
            ),
            'Quform_Validator_Email' => array(
                'INVALID_FORMAT' => array(
                    '%value%' => __('the submitted value', 'quform')
                )
            ),
            'Quform_Validator_GreaterThan' => array(
                'NOT_GREATER' => array(
                    '%value%' => __('the submitted value', 'quform'),
                    '%min%' => __('the minimum allowed value', 'quform')
                ),
                'NOT_GREATER_INCLUSIVE' => array(
                    '%value%' => __('the submitted value', 'quform'),
                    '%min%' => __('the minimum allowed value', 'quform')
                )
            ),
            'Quform_Validator_Identical' => array(
                'NOT_SAME' => array(
                    '%value%' => __('the submitted value', 'quform'),
                    '%token%' => __('the token', 'quform')
                )
            ),
            'Quform_Validator_InArray' => array(
                'NOT_IN_ARRAY' => array('%value%' => __('the submitted value', 'quform'))
            ),
            'Quform_Validator_Length' => array(
                'TOO_SHORT' => array(
                    '%value%' => __('the submitted value', 'quform'),
                    '%min%' => __('the minimum allowed length', 'quform'),
                    '%length%' => __('the length of the submitted value', 'quform')
                ),
                'TOO_LONG' => array(
                    '%value%' => __('the submitted value', 'quform'),
                    '%max%' => __('the maximum allowed length', 'quform'),
                    '%length%' => __('the length of the submitted value', 'quform')
                )
            ),
            'Quform_Validator_LessThan' => array(
                'NOT_LESS' => array(
                    '%value%' => __('the submitted value', 'quform'),
                    '%max%' => __('the maximum allowed value', 'quform')
                ),
                'NOT_LESS_INCLUSIVE' => array(
                    '%value%' => __('the submitted value', 'quform'),
                    '%max%' => __('the maximum allowed value', 'quform')
                )
            ),
            'Quform_Validator_Regex' => array(
                'NOT_MATCH' => array(
                    '%value%' => __('the submitted value', 'quform')
                )
            )
        );

        foreach ($validatorTranslations as $class => $messages) :
            $wrapClass = 'qfb-' . strtolower(str_replace('Quform_Validator_', '', $class)) . '-translations';

            foreach ($messages as $constant => $variables) :
                $key = constant($class . '::' . $constant); ?>
                <div class="qfb-setting <?php echo esc_attr($wrapClass); ?>">
                    <div class="qfb-setting-label">
                        <?php if (count($variables)) : ?>
                            <div class="qfb-tooltip-icon">
                                <div class="qfb-tooltip-content">
                                    <div class="qfb-tooltip-title"><?php esc_html_e('Variables', 'quform'); ?></div>
                                    <pre><?php echo esc_html($builder->formatVariables($variables)); ?></pre>
                                </div>
                            </div>
                        <?php endif; ?>
                        <label for="qfb_v_message_<?php echo esc_attr($key); ?>"><?php echo esc_html(call_user_func(array($class, 'getMessageTemplates'), $key)); ?></label>
                    </div>
                    <div class="qfb-setting-inner">
                        <div class="qfb-setting-input">
                            <input type="text" id="qfb_v_message_<?php echo esc_attr($key); ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

    </div>

</div>
