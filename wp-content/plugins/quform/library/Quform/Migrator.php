<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Migrator
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Builder
     */
    protected $builder;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Builder       $builder
     * @param  Quform_ScriptLoader  $scriptLoader
     * @param  Quform_Options       $options
     * @param  Quform_Form_Factory  $formFactory
     */
    public function __construct(
        Quform_Repository $repository,
        Quform_Builder $builder,
        Quform_ScriptLoader $scriptLoader,
        Quform_Options $options,
        Quform_Form_Factory $formFactory
    ) {
        $this->repository = $repository;
        $this->builder = $builder;
        $this->scriptLoader = $scriptLoader;
        $this->options = $options;
        $this->formFactory = $formFactory;
    }

    /**
     * Handle the request to get all 1.x form IDs via Ajax
     */
    public function getAllFormIds()
    {
        if ( ! Quform::isPostRequest()) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_full_access')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        $oldForms = $this->get1xForms();
        $formIds = array();

        foreach ($oldForms as $oldForm) {
            $oldFormId = (int) $oldForm['id'];

            if ($oldFormId > 0) {
                $formIds[] = $oldFormId;
            }
        }

        wp_send_json(array(
            'type' => 'success',
            'formIds' => $formIds
        ));
    }

    /**
     * Handle the request to migrate a 1.x form via Ajax
     */
    public function migrateForm()
    {
        $this->validateMigrateFormRequest();

        @set_time_limit(3600);

        $formId = (int) $_POST['form_id'];

        $form = $this->get1xForm($formId);

        if (is_array($form)) {
            $result = $this->convertForm($form);

            if ($result['type'] == 'success') {
                $config = $this->builder->sanitizeForm($result['config']);

                $config = $this->repository->add($config);

                if ( ! is_array($config)) {
                    wp_send_json(array(
                        'type' => 'error',
                        'message' => wp_kses(sprintf(
                            /* translators: %1$s: open link tag, %2$s: close link tag */
                            __('Failed to insert into database, check the %1$serror log%2$s for more information', 'quform'),
                            '<a href="https://support.themecatcher.net/quform-wordpress-v2/guides/advanced/enabling-debug-logging">',
                            '</a>'
                        ), array('a' => array('href' => array())))
                    ));
                }

                $this->scriptLoader->handleSaveForm($config);

                if ($_POST['migrate_entries'] === '1') {
                    $this->migrateEntries($form, $config);
                }

                wp_send_json(array(
                    'type' => 'success'
                ));
            } else {
                wp_send_json(array(
                    'type' => 'error',
                    'message' => $result['message']
                ));
            }
        } else {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Form not found', 'quform')
            ));
        }
    }

    /**
     * Convert the given 1.x form config to a 2.x form config
     *
     * @param   array  $form
     * @return  array
     */
    protected function convertForm(array $form)
    {
        $config = Quform_Form::getDefaultConfig();

        $config['name'] = $form['name'];
        $config['title'] = $form['title'];
        $config['active'] = $form['active'];
        $config['description'] = $form['description'];

        $confirmation = Quform_Confirmation::getDefaultConfig();

        $confirmation['id'] = 1;
        $confirmation['name'] = __('Default confirmation', 'quform');

        if ($form['success_type'] == 'message') {
            $confirmation['type'] = 'message';
            $confirmation['message'] = $this->convertPlaceholders($form['success_message']);
            $confirmation['messageAutoFormat'] = false;
            $confirmation['messagePosition'] = $form['success_message_position'];
            $confirmation['messageTimeout'] = $form['success_message_timeout'];
        } else if ($form['success_type'] == 'redirect') {
            if (in_array($form['success_redirect_type'], array('post', 'page'))) {
                $confirmation['type'] = 'redirect-page';
                $confirmation['redirectPage'] = $form['success_redirect_value'];
            } else if ($form['success_redirect_type'] == 'url') {
                $confirmation['type'] = 'redirect-url';
                $confirmation['redirectUrl'] = $form['success_redirect_value'];
            }
        }

        $confirmation['resetForm'] = Quform::get($form, 'reset_form_values', '');

        $config['confirmations'] = array($confirmation);
        $config['nextConfirmationId'] = 2;

        $config['submitText'] = $form['submit_button_text'];
        $config['ajax'] = $form['use_ajax'];
        $config['honeypot'] = $form['use_honeypot'];
        $config['logicAnimation'] = $form['conditional_logic_animation'];

        if (Quform::isNonEmptyString($form['theme'])) {
            $theme = explode('|', $form['theme']);
            $config['theme'] = $theme[0];
        }

        $config['responsiveElements'] = $form['responsive'] ? 'phone-landscape' : '';
        $config['responsiveColumns'] = $form['responsive'] ? 'phone-landscape' : '';
        $config['labelPosition'] = str_replace('above', '', $form['label_placement']);
        $config['labelWidth'] = $form['label_width'];
        $config['requiredText'] = $form['required_text'];
        $config['tooltipsEnabled'] = $form['use_tooltips'];
        $config['tooltipType'] = $form['tooltip_type'];
        $config['tooltipEvent'] = $form['tooltip_event'];
        $config['tooltipCustom'] = $form['tooltip_custom'];
        $config['tooltipStyle'] = $form['tooltip_style'];
        $config['tooltipMy'] = $form['tooltip_my'];
        $config['tooltipAt'] = $form['tooltip_at'];
        $config['tooltipShadow'] = $form['tooltip_shadow'];
        $config['tooltipRounded'] = $form['tooltip_rounded'];
        $config['tooltipClasses'] = $this->getTooltipClasses($form);
        $config['fieldBackgroundColor'] = $form['element_background_colour'];
        $config['fieldBackgroundColorHover'] = $form['element_background_colour'];
        $config['fieldBackgroundColorFocus'] = $form['element_background_colour'];
        $config['fieldBorderColor'] = $form['element_border_colour'];
        $config['fieldBorderColorHover'] = $form['element_border_colour'];
        $config['fieldBorderColorFocus'] = $form['element_border_colour'];
        $config['fieldTextColor'] = $form['element_text_colour'];
        $config['fieldTextColorHover'] = $form['element_text_colour'];
        $config['fieldTextColorFocus'] = $form['element_text_colour'];
        $config['labelTextColor'] = $form['label_text_colour'];

        $config['styles'] = $this->convertStyles($form['styles'], false);

        if (isset($form['entries_table_layout']['active']) && is_array($form['entries_table_layout']['active'])) {
            foreach ($form['entries_table_layout']['active'] as $column) {
                if ($column['type'] == 'element') {
                    $config['entriesTableColumns'][] = sprintf('element_%d', $column['id']);
                } else if ($column['type'] == 'column') {
                    if ($column['id'] == 'date_added') {
                        $config['entriesTableColumns'][] = 'created_at';
                    } else if (in_array($column['id'], array('form_url', 'referring_url', 'post_id', 'created_by', 'updated_at', 'ip', 'id'))) {
                        $config['entriesTableColumns'][] = $column['id'];
                    }
                }
            }
        }

        $config['saveEntry'] = $form['save_to_database'];
        $config['databaseEnabled'] = is_array($form['db_fields']) && count($form['db_fields']);
        $config['databaseWordpress'] = $form['use_wp_db'];
        $config['databaseHost'] = $form['db_host'];
        $config['databaseUsername'] = $form['db_username'];
        $config['databasePassword'] = $form['db_password'];
        $config['databaseDatabase'] = $form['db_name'];
        $config['databaseTable'] = $form['db_table'];
        $config['databaseColumns'] = array();

        if (is_array($form['db_fields'])) {
            foreach ($form['db_fields'] as $dbFieldName => $dbFieldValue) {
                $config['databaseColumns'][] = array(
                    'name' => $dbFieldName,
                    'value' => $this->convertPlaceholders($dbFieldValue)
                );
            }
        }

        $config['elements'] = array();

        // Get the next element ID
        $config['nextElementId'] = 1;
        foreach ($form['elements'] as $element) {
            $config['nextElementId'] = max($config['nextElementId'], $element['id']);
        }
        $config['nextElementId']++;

        $page1 = Quform_Element_Page::getDefaultConfig();
        $page1['id'] = $config['nextElementId']++;
        $page1['parentId'] = 0;
        $page1['position'] = 0;
        $page1['elements'] = array();

        $stack = array();
        $notificationAttachments = array();
        $allOptions = array();

        foreach ($form['elements'] as $element) {
            if ( ! isset($element['type']) || ! Quform::isNonEmptyString($element['type'])) {
                continue;
            }

            if ($element['type'] == 'groupend') {
                if ( ! count($stack)) {
                    return array(
                        'type' => 'error',
                        'message' => __('Group end element found before group start element', 'quform')
                    );
                }

                $group = array_pop($stack);

                if (count($stack)) {
                    $columnIndex = $stack[count($stack) - 1][2] % $stack[count($stack) - 1][1];
                    $stack[count($stack) - 1][0]['elements'][0]['elements'][$columnIndex]['elements'][] = $group[0];
                    $stack[count($stack) - 1][2]++;
                } else {
                    $page1['elements'][] = $group[0];
                }

                continue;
            } else if ($element['type'] == 'groupstart') {
                $element['type'] = 'group';
            }

            $class = 'Quform_Element_' . ucfirst($element['type']);

            if ( ! class_exists($class)) {
                continue;
            }

            $eConfig = call_user_func(array($class, 'getDefaultConfig'));
            $eConfig['id'] = $element['id'];

            switch ($element['type']) {
                case 'text':
                case 'textarea':
                case 'email':
                    $eConfig['label'] = $element['label'];
                    $eConfig['placeholder'] = Quform::get($element, 'placeholder', '');
                    $eConfig['description'] = $element['description'];
                    $eConfig['required'] = $element['required'];
                    $eConfig['defaultValue'] = $this->convertPlaceholders($element['default_value']);
                    $eConfig['dynamicDefaultValue'] = $element['dynamic_default_value'];
                    $eConfig['dynamicKey'] = $element['dynamic_key'];
                    $eConfig['tooltip'] = $element['tooltip'];
                    $eConfig['adminLabel'] = $element['admin_label'];
                    $eConfig['messageRequired'] = $element['required_message'];
                    $eConfig['showInEmail'] = ! $element['is_hidden'];
                    $eConfig['saveToDatabase'] = $element['save_to_database'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['filters'] = $this->convertFilters($element['filters']);

                    if ($element['type'] == 'email') {
                        foreach ($element['validators'] as $key => $validator) {
                            if ($validator['type'] == 'email') {
                                array_splice($element['validators'], $key, 1);
                            }
                        }
                    }

                    $eConfig['validators'] = $this->convertValidators($element['validators']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);

                    if ($element['prevent_duplicates']) {
                        $eConfig['validators'][] = $this->createPreventDuplicatesValidator($element['duplicate_found_message']);
                    }
                    break;
                case 'select':
                case 'checkbox':
                case 'radio':
                    $eConfig['label'] = $element['label'];
                    $eConfig['description'] = $element['description'];
                    $eConfig['required'] = $element['required'];
                    $eConfig['options'] = array();
                    $eConfig['nextOptionId'] = 1;

                    foreach ($element['options'] as $option) {
                        $newOption = array(
                            'id' => $eConfig['nextOptionId']++,
                            'label' => $option['label'],
                            'value' => $option['value']
                        );

                        $eConfig['options'][] = $newOption;
                        $allOptions[$element['id']][] = $newOption;
                    }

                    if ($element['type'] == 'checkbox' || $element['type'] == 'radio') {
                        $eConfig['optionsLayout'] = $element['options_layout'];
                    }

                    $eConfig['customiseValues'] = $element['customise_values'];
                    $eConfig['defaultValue'] = $element['default_value'];

                    // Convert array default value to string
                    if ($element['type'] == 'select' || $element['type'] == 'radio') {
                        if (is_array($eConfig['defaultValue']) && count($eConfig['defaultValue']) && is_string($eConfig['defaultValue'][0])) {
                            $eConfig['defaultValue'] = $eConfig['defaultValue'][0];
                        } else if ( ! is_string($eConfig['defaultValue'])) {
                            $eConfig['defaultValue'] = '';
                        }
                    }

                    if ($element['type'] == 'select') {
                        $eConfig['noneOption'] = false;
                    }

                    $eConfig['dynamicDefaultValue'] = $element['dynamic_default_value'];
                    $eConfig['dynamicKey'] = $element['dynamic_key'];
                    $eConfig['tooltip'] = $element['tooltip'];
                    $eConfig['adminLabel'] = $element['admin_label'];
                    $eConfig['messageRequired'] = $element['required_message'];
                    $eConfig['showInEmail'] = ! $element['is_hidden'];
                    $eConfig['saveToDatabase'] = $element['save_to_database'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);

                    if ($element['prevent_duplicates']) {
                        $eConfig['validators'][] = $this->createPreventDuplicatesValidator($element['duplicate_found_message']);
                    }
                    break;
                case 'file':
                    $eConfig['label'] = $element['label'];
                    $eConfig['description'] = $element['description'];
                    $eConfig['required'] = $element['required'];
                    $eConfig['enhancedUploadEnabled'] = $element['enable_swf_upload'];
                    $eConfig['maximumNumberOfFiles'] = $element['allow_multiple_uploads'] ? '0' : '1';
                    $eConfig['allowedExtensions'] = $element['upload_allowed_extensions'];
                    $eConfig['maximumFileSize'] = $element['upload_maximum_size'];
                    $eConfig['tooltip'] = $element['tooltip'];
                    $eConfig['adminLabel'] = $element['admin_label'];
                    $eConfig['showInEmail'] = ! $element['is_hidden'];
                    $eConfig['saveToDatabase'] = $element['save_to_database'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];

                    if ($element['add_as_attachment']) {
                        $notificationAttachments[] = $element['id'];
                    }

                    $eConfig['saveToServer'] = $element['save_to_server'];
                    $eConfig['savePath'] = $element['save_path'];
                    $eConfig['browseText'] = $element['browse_text'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);
                    $eConfig['messageFileUploadRequired'] = $element['messages']['field_required'];
                    $eConfig['messageFileTooBigFilename'] = $element['messages']['too_big_with_filename'];
                    $eConfig['messageFileTooBig'] = $element['messages']['too_big'];
                    $eConfig['messageNotAllowedTypeFilename'] = $element['messages']['not_allowed_type_with_filename'];
                    $eConfig['messageNotAllowedType'] = $element['messages']['not_allowed_type'];

                    if ($eConfig['enhancedUploadEnabled']) {
                        $config['hasEnhancedUploader'] = true;
                    }
                    break;
                case 'captcha':
                    $eConfig['label'] = $element['label'];
                    $eConfig['placeholder'] = Quform::get($element, 'placeholder', '');
                    $eConfig['description'] = $element['description'];
                    $eConfig['captchaLength'] = $element['options']['length'];
                    $eConfig['captchaWidth'] = $element['options']['width'];
                    $eConfig['captchaHeight'] = $element['options']['height'];
                    $eConfig['captchaBgColor'] = $element['options']['bgColour'];
                    $eConfig['captchaBgColorRgba'] = $this->hexToRgb($element['options']['bgColour']);
                    $eConfig['captchaTextColor'] = $element['options']['textColour'];
                    $eConfig['captchaTextColorRgba'] = $this->hexToRgb($element['options']['textColour']);
                    $eConfig['captchaFont'] = $element['options']['font'];
                    $eConfig['captchaMinFontSize'] = $element['options']['minFontSize'];
                    $eConfig['captchaMaxFontSize'] = $element['options']['maxFontSize'];
                    $eConfig['captchaMinAngle'] = $element['options']['minAngle'];
                    $eConfig['captchaMaxAngle'] = $element['options']['maxAngle'];
                    $eConfig['tooltip'] = $element['tooltip'];
                    $eConfig['messageRequired'] = $element['required_message'];
                    $eConfig['messageCaptchaNotMatch'] = $element['invalid_message'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);
                    break;
                case 'recaptcha':
                    $eConfig['label'] = $element['label'];
                    $eConfig['description'] = $element['description'];
                    $eConfig['recaptchaTheme'] = $element['recaptcha_theme'];
                    $eConfig['recaptchaType'] = $element['recaptcha_type'];
                    $eConfig['recaptchaSize'] = $element['recaptcha_size'];
                    $eConfig['recaptchaBadge'] = $element['recaptcha_badge_position'];
                    $eConfig['recaptchaLang'] = $element['recaptcha_lang'];
                    $eConfig['tooltip'] = $element['tooltip'];
                    $eConfig['messageRequired'] = $element['required_message'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['messageRecaptchaMissingInputSecret'] = $element['messages']['missing-input-secret'];
                    $eConfig['messageRecaptchaInvalidInputSecret'] = $element['messages']['invalid-input-secret'];
                    $eConfig['messageRecaptchaMissingInputResponse'] = $element['messages']['missing-input-response'];
                    $eConfig['messageRecaptchaInvalidInputResponse'] = $element['messages']['invalid-input-response'];
                    $eConfig['messageRecaptchaError'] = $element['messages']['error'];
                    $eConfig['styles'] = $this->convertStyles($element['styles']);
                    break;
                case 'html':
                    $eConfig['content'] = $element['content'];
                    $eConfig['showInEntry'] = $element['show_in_entry'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    break;
                case 'date':
                    $eConfig['label'] = $element['label'];
                    $eConfig['description'] = $element['description'];
                    $eConfig['required'] = $element['required'];
                    $eConfig['tooltip'] = $element['tooltip'];

                    $day = $element['default_value']['day'];
                    $month = $element['default_value']['month'];
                    $year = $element['default_value']['year'];

                    if (is_numeric($day) && is_numeric($month) && is_numeric($year)) {
                        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
                        $month = str_pad($month, 2, '0', STR_PAD_LEFT);

                        if (checkdate($month, $day, $year)) {
                            $eConfig['defaultValue'] = $year . '-' . $month . '-' . $day;
                        }
                    }

                    $eConfig['dynamicDefaultValue'] = $element['dynamic_default_value'];
                    $eConfig['dynamicKey'] = $element['dynamic_key'];
                    $eConfig['adminLabel'] = $element['admin_label'];
                    $eConfig['messageRequired'] = $element['required_message'];
                    $eConfig['messageDateInvalidDate'] = $element['date_validator_message_invalid'];
                    $eConfig['showInEmail'] = ! $element['is_hidden'];
                    $eConfig['saveToDatabase'] = $element['save_to_database'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);

                    if ($element['prevent_duplicates']) {
                        $eConfig['validators'][] = $this->createPreventDuplicatesValidator($element['duplicate_found_message']);
                    }

                    $config['hasDatepicker'] = true;
                    $config['locales'][] = '';
                    break;
                case 'time':
                    $eConfig['label'] = $element['label'];
                    $eConfig['description'] = $element['description'];
                    $eConfig['required'] = $element['required'];
                    $eConfig['tooltip'] = $element['tooltip'];

                    $hour = $element['default_value']['hour'];
                    $minute = $element['default_value']['minute'];

                    if ($element['time_12_24'] == '12') {
                        $ampm = $element['default_value']['ampm'];

                        if (is_numeric($hour) && is_numeric($minute) && in_array($ampm, array('am', 'pm'))) {
                            try {
                                $eConfig['defaultValue'] = Quform::date(
                                    'H:i',
                                    new DateTime("$hour:$minute$ampm", new DateTimeZone('UTC')),
                                    new DateTimeZone('UTC')
                                );
                            } catch (Exception $e) {
                                // If there was an error creating the DateTime object, don't set a default value
                            }
                        }
                    } else {
                        if (is_numeric($hour) && is_numeric($minute)) {
                            $eConfig['defaultValue'] = "$hour:$minute";
                        }
                    }

                    if (is_numeric($element['start_hour'])) {
                        $eConfig['timeMin'] = $element['start_hour'] . ':00';
                    }

                    if (is_numeric($element['end_hour'])) {
                        $eConfig['timeMax'] = $element['end_hour'] . ':00';
                    }

                    $eConfig['timeInterval'] = $element['minute_granularity'];
                    $eConfig['messageTimeInvalidTime'] = $element['time_validator_message_invalid'];
                    $eConfig['adminLabel'] = $element['admin_label'];
                    $eConfig['messageRequired'] = $element['required_message'];
                    $eConfig['showInEmail'] = ! $element['is_hidden'];
                    $eConfig['saveToDatabase'] = $element['save_to_database'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['dynamicDefaultValue'] = $element['dynamic_default_value'];
                    $eConfig['dynamicKey'] = $element['dynamic_key'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);

                    if ($element['prevent_duplicates']) {
                        $eConfig['validators'][] = $this->createPreventDuplicatesValidator($element['duplicate_found_message']);
                    }

                    $config['hasTimepicker'] = true;
                    $config['locales'][] = '';
                    break;
                case 'password':
                    $eConfig['label'] = $element['label'];
                    $eConfig['placeholder'] = Quform::get($element, 'placeholder', '');
                    $eConfig['description'] = $element['description'];
                    $eConfig['required'] = $element['required'];
                    $eConfig['tooltip'] = $element['tooltip'];
                    $eConfig['adminLabel'] = $element['admin_label'];
                    $eConfig['messageRequired'] = $element['required_message'];
                    $eConfig['showInEmail'] = ! $element['is_hidden'];
                    $eConfig['saveToDatabase'] = $element['save_to_database'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['validators'] = $this->convertValidators($element['validators']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);
                    break;
                case 'hidden':
                    $eConfig['label'] = $element['label'];
                    $eConfig['defaultValue'] = $this->convertPlaceholders($element['default_value']);
                    $eConfig['dynamicDefaultValue'] = $element['dynamic_default_value'];
                    $eConfig['dynamicKey'] = $element['dynamic_key'];
                    $eConfig['showInEmail'] = ! $element['is_hidden'];
                    $eConfig['saveToDatabase'] = $element['save_to_database'];
                    break;
                case 'group':
                    $eConfig['label'] = $element['admin_title'];
                    $eConfig['title'] = $element['title'];
                    $eConfig['showLabelInEmail'] = $element['show_name_in_email'];
                    $eConfig['description'] = $element['description'];
                    $eConfig['labelPosition'] = str_replace('above', '', $element['label_placement']);
                    $eConfig['labelWidth'] = $element['label_width'];
                    $eConfig['tooltipType'] = $element['tooltip_type'];
                    $eConfig['tooltipEvent'] = $element['tooltip_event'];
                    $eConfig['groupStyle'] = $element['group_style'];
                    $eConfig['borderColor'] = $element['border_colour'];
                    $eConfig['backgroundColor'] = $element['background_colour'];
                    $eConfig['logicEnabled'] = $element['logic'];
                    $eConfig['logicAction'] = Quform::get($element, 'logic_action') != 'hide';
                    $eConfig['logicMatch'] = $element['logic_match'];
                    $eConfig['logicRules'] = $this->convertLogicRules($element['logic_rules']);
                    $eConfig['styles'] = $this->convertStyles($element['styles']);
                    $eConfig['elements'] = array();

                    $row = Quform_Element_Row::getDefaultConfig();
                    $row['id'] = $config['nextElementId']++;
                    $row['columnSize'] = $element['column_alignment'] == 'proportional' ? 'fixed' : 'float';
                    $row['elements'] = array();

                    $columnCount = (int) $element['number_of_columns'];
                    $columnCount = $columnCount > 0 ? $columnCount : 1;

                    for ($i = 0; $i < $columnCount; $i++) {
                        $column = Quform_Element_Column::getDefaultConfig();
                        $column['id'] = $config['nextElementId']++;
                        $row['elements'][] = $column;
                    }

                    $eConfig['elements'][] = $row;
                    $stack[] = array($eConfig, $columnCount, 0);
                    break;
            }

            if ($element['type'] != 'group') {
                if (count($stack)) {
                    $columnIndex = $stack[count($stack) - 1][2] % $stack[count($stack) - 1][1];
                    $stack[count($stack) - 1][0]['elements'][0]['elements'][$columnIndex]['elements'][] = $eConfig;
                    $stack[count($stack) - 1][2]++;
                } else {
                    $page1['elements'][] = $eConfig;
                }
            }
        }

        $submit = Quform_Element_Submit::getDefaultConfig();
        $submit['id'] = $config['nextElementId']++;
        $submit['parentId'] = $page1['id'];
        $submit['position'] = count($page1['elements']);

        $page1['elements'][] = $submit;

        $config['elements'][] = $page1;

        $config['notifications'] = array();

        $notification1 = Quform_Notification::getDefaultConfig();

        $notification1['id'] = 1;
        $notification1['name'] = __('Admin notification', 'quform');
        $notification1['enabled'] = $form['send_notification'];
        $notification1['subject'] = $this->convertPlaceholders($form['subject']);
        $notification1['format'] = $form['customise_email_content'] && $form['notification_format'] == 'plain' ? 'text' : 'html';

        if ($form['customise_email_content']) {
            $notification1['html'] = $form['notification_format'] == 'html' ? $this->convertPlaceholders($form['notification_email_content']) : '';
        } else {
            $notification1['html'] = ! empty($form['notification_show_empty_fields']) ? '{all_form_data|showEmptyFields:true}' : '{all_form_data}';
        }

        $notification1['autoFormat'] = false;

        if ($form['customise_email_content']) {
            $notification1['text'] = $form['notification_format'] == 'plain' ? $this->convertPlaceholders($form['notification_email_content']) : '';
        } else {
            $notification1['text'] = ! empty($form['notification_show_empty_fields']) ? '{all_form_data|showEmptyFields:true}' : '{all_form_data}';
        }

        $notification1['recipients'] = array();

        if (is_array($form['recipients'])) {
            foreach ($form['recipients'] as $recipient) {
                $notification1['recipients'][] = array('type' => 'to', 'address' => $recipient, 'name' => '');
            }
        }

        if ( ! empty($form['bcc']) && is_array($form['bcc'])) {
            foreach ($form['bcc'] as $recipient) {
                $notification1['recipients'][] = array('type' => 'bcc', 'address' => $recipient, 'name' => '');
            }
        }

        if (Quform::isNonEmptyString($form['notification_reply_to_element'])) {
            $notification1['recipients'][] = array('type' => 'reply', 'address' => '{element|id:' . $form['notification_reply_to_element'] . '}', 'name' => '');
        }

        if (is_array($form['conditional_recipients'])) {
            if (count($form['conditional_recipients'])) {
                $notification1['conditional'] = true;

                foreach ($form['conditional_recipients'] as $conditionalRecipient) {
                    $conditional = array(
                        'recipients' => array(array('type' => 'to', 'address' => $conditionalRecipient['recipient'], 'name' => '')),
                        'logicAction' => true,
                        'logicMatch' => 'all',
                        'logicRules' => array(array(
                            'elementId' => $conditionalRecipient['element'],
                            'operator' => $conditionalRecipient['operator'],
                            'value' => $conditionalRecipient['value'],
                            'optionId' => null
                        ))
                    );

                    $notification1['conditionals'][] = $conditional;
                }
            }
        }

        if ($form['notification_from_type'] == 'element') {
            $notification1['from'] = array('address' => '{element|id:' . $form['notification_from_element'] . '}', 'name' => '');
        } else {
            $notification1['from'] = array('address' => str_replace('{admin_email}', '{default_from_email_address}', $form['from_email']), 'name' => $form['from_name']);
        }

        if (count($notificationAttachments)) {
            $notification1['attachments'] = array();

            foreach ($notificationAttachments as $notificationAttachment) {
                $notification1['attachments'][] = array(
                    'source' => 'element',
                    'element' => (string) $notificationAttachment,
                    'media' => array()
                );
            }
        }

        $config['notifications'][] = $notification1;
        $config['nextNotificationId'] = 2;

        if ($form['send_autoreply'] &&
            Quform::isNonEmptyString($form['autoreply_recipient_element']) &&
            is_numeric($form['autoreply_recipient_element']) &&
            Quform::isNonEmptyString($form['autoreply_email_content'])
        ) {
            $notification2 = Quform_Notification::getDefaultConfig();
            $notification2['id'] = 2;
            $notification2['name'] = 'Autoreply email';
            $notification2['enabled'] = true;
            $notification2['subject'] = $this->convertPlaceholders($form['autoreply_subject']);
            $notification2['format'] = $form['autoreply_format'] == 'plain' ? 'text' : 'html';
            $notification2['html'] = $form['autoreply_format'] == 'html' ? $this->convertPlaceholders($form['autoreply_email_content']) : '';
            $notification2['autoFormat'] = false;
            $notification2['text'] = $form['autoreply_format'] == 'plain' ? $this->convertPlaceholders($form['autoreply_email_content']) : '';

            $notification2['recipients'] = array(
                array('type' => 'to', 'address' => '{element|id:' . $form['autoreply_recipient_element'] . '}', 'name' => '')
            );

            if ($form['autoreply_from_type'] == 'element') {
                $notification2['from'] = array('address' => '{element|id:' . $form['autoreply_from_element'] . '}', 'name' => '');
            } else {
                $notification2['from'] = array('address' => str_replace('{admin_email}', '{default_from_email_address}', $form['autoreply_from_email']), 'name' => $form['autoreply_from_name']);
            }

            $config['notifications'][] = $notification2;
            $config['nextNotificationId'] = 3;
        }

        $config = $this->setPositionData($config);
        $config = $this->convertLogic($config, $allOptions);

        return array(
            'type' => 'success',
            'config' => $config
        );
    }

    /**
     * Get the tooltip classes
     *
     * This option did not exist in 1.x
     *
     * @param   array   $form
     * @return  string
     */
    protected function getTooltipClasses(array $form)
    {
        $classes = array();

        if ($form['tooltip_style'] == 'custom') {
            if (isset($form['tooltip_custom']) && Quform::isNonEmptyString($form['tooltip_custom'])) {
                $classes[] = $form['tooltip_custom'];
            }
        } else {
            $classes[] = $form['tooltip_style'];
        }

        if (isset($form['tooltip_shadow']) && is_bool($form['tooltip_shadow']) && $form['tooltip_shadow']) {
            $classes[] = 'qtip-shadow';
        }

        if (isset($form['tooltip_rounded']) && is_bool($form['tooltip_rounded']) && $form['tooltip_rounded']) {
            $classes[] = 'qtip-rounded';
        }

        return join(' ', $classes);
    }

    /**
     * Convert 1.x placeholders to 2.x in the given value
     *
     * @param   string  $value
     * @return  string
     */
    protected function convertPlaceholders($value)
    {
        $value = str_replace(
            array(
                '{post_id}',
                '{post_title}',
                '{user_display_name}',
                '{user_email}',
                '{user_login}',
                '{current_date}',
                '{submit_date}',
                '{current_time}',
                '{submit_time}'
            ),
            array(
                '{post|ID}',
                '{post|post_title}',
                '{user|display_name}',
                '{user|user_email}',
                '{user|user_login}',
                '{date}',
                '{date}',
                '{time}',
                '{time}'
            ),
            $value
        );

        $value = preg_replace(
            array(
                '/\{current_date\|(.+?)\}/',
                '/\{submit_date\|(.+?)\}/',
                '/\{current_time\|(.+?)\}/',
                '/\{submit_time\|(.+?)\}/',
                '/{([^}\r\n]*?)\|(\d+)}/'
            ),
            array(
                '{date|format:$1}',
                '{date|format:$1}',
                '{time|format:$1}',
                '{time|format:$1}',
                '{element|id:$2|$1}'
            ),
            $value
        );

        return $value;
    }

    /**
     * Set the parentId and position values of children of the given form config
     *
     * @param   array  $config
     * @return  array
     */
    protected function setPositionData($config)
    {
        for ($i = 0; $i < count($config['elements']); $i++) {
            $config['elements'][$i]['parentId'] = 0;
            $config['elements'][$i]['position'] = $i;

            if (in_array($config['elements'][$i]['type'], array('page', 'group', 'row', 'column'))) {
                $config['elements'][$i]['elements'] = $this->setContainerPositionData($config['elements'][$i]);
            }
        }

        return $config;
    }

    /**
     * Set the parentId and position values of children of the given container config
     *
     * @param   array  $container
     * @return  array
     */
    protected function setContainerPositionData(array $container)
    {
        for ($i = 0; $i < count($container['elements']); $i++) {
            $container['elements'][$i]['parentId'] = $container['id'];
            $container['elements'][$i]['position'] = $i;

            if (in_array($container['elements'][$i]['type'], array('page', 'group', 'row', 'column'))) {
                $container['elements'][$i]['elements'] = $this->setContainerPositionData($container['elements'][$i]);
            }
        }

        return $container['elements'];
    }

    /**
     * Convert all logic rules to new format
     *
     * Logic in 1.x used option values, logic in 2.x uses option IDs
     *
     * @param   array  $config      The form config in 2.x format
     * @param   array  $allOptions  All options within the new form as a flat array
     * @return  array
     */
    protected function convertLogic(array $config, array $allOptions)
    {
        // Deal with conditional recipients
        foreach ($config['notifications'][0]['conditionals'] as $key => $conditional) {
            foreach ($config['notifications'][0]['conditionals'][$key]['logicRules'] as $conditionalLogicRuleKey => $conditionalLogicRule) {
                $config['notifications'][0]['conditionals'][$key]['logicRules'][$conditionalLogicRuleKey] = $this->setLogicRuleOptionId($conditionalLogicRule, $allOptions);
            }
        }

        $config = $this->convertContainerLogic($config, $allOptions);

        return $config;
    }

    /**
     * Convert the logic rules for elements in the given container rules to the new format
     *
     * @param   array  $container   The container element
     * @param   array  $allOptions  All options within the new form as a flat array
     * @return  array
     */
    protected function convertContainerLogic(array $container, array $allOptions)
    {
        foreach ($container['elements'] as $key => $element) {
            if (isset($element['logicRules']) && is_array($element['logicRules'])) {
                foreach ($element['logicRules'] as $logicRuleKey => $logicRule) {
                    $container['elements'][$key]['logicRules'][$logicRuleKey] = $this->setLogicRuleOptionId($logicRule, $allOptions);
                }
            }

            if (in_array($container['elements'][$key]['type'], array('page', 'group', 'row', 'column'))) {
                $container['elements'][$key] = $this->convertContainerLogic($container['elements'][$key], $allOptions);
            }
        }

        return $container;
    }

    /**
     * Set the option ID on the given logic rule
     *
     * @param   array  $rule        The logic rule data
     * @param   array  $allOptions  All options within the new form as a flat array
     * @return  array
     */
    protected function setLogicRuleOptionId(array $rule, array $allOptions)
    {
        if (isset($allOptions[$rule['elementId']])) {
            foreach ($allOptions[$rule['elementId']] as $option) {
                if ($option['value'] == $rule['value']) {
                    $rule['optionId'] = $option['id'];
                    break;
                }
            }
        }

        return $rule;
    }

    /**
     * Convert the given styles to the new format
     *
     * @param   array  $styles     The styles array
     * @param   bool   $isElement  True for element styles, false for form styles
     * @return  array
     */
    protected function convertStyles($styles, $isElement = true)
    {
        $converted = array();

        if (is_array($styles)) {
            foreach ($styles as $style) {
                if (in_array($style['type'], array('submitOuter', 'submit', 'submitButton', 'submitSpan', 'submitEm'))) {
                    continue;
                }

                $converted[] = array(
                    'type' => $this->convertStyleType($style['type'], $isElement),
                    'css' => $style['css']
                );
            }
        }

        return $converted;
    }

    /**
     * Convert the given styles to the new format
     *
     * @param   string  $type       The style type
     * @param   bool    $isElement  True for element styles, false for form styles
     * @return  string
     */
    protected function convertStyleType($type, $isElement)
    {
        switch($type) {
            case 'success';
                $type = 'formSuccess';
                break;
            case 'title';
                $type = 'formTitle';
                break;
            case 'description';
                $type = $isElement ? 'groupDescription' : 'formDescription';
                break;
            case 'elements';
                $type = 'formElements';
                break;
            case 'outer';
                $type = 'element';
                break;
            case 'label';
                $type = 'elementLabel';
                break;
            case 'inner';
                $type = 'elementInner';
                break;
            case 'input';
                $type = 'elementText';
                break;
            case 'textarea';
                $type = 'elementTextarea';
                break;
            case 'select';
                $type = 'elementSelect';
                break;
            case 'optionUl';
                $type = 'options';
                break;
            case 'optionLi';
                $type = 'option';
                break;
        }

        return $type;
    }

    /**
     * Convert the given logic rules to the new format
     *
     * @param   array  $logicRules
     * @return  array
     */
    protected function convertLogicRules($logicRules)
    {
        $converted = array();

        if (is_array($logicRules)) {
            foreach ($logicRules as $logicRule) {
                $converted[] = array(
                    'elementId' => $logicRule['element_id'],
                    'operator' => $logicRule['operator'],
                    'value' => $logicRule['value'],
                    'optionId' => null
                );
            }
        }

        return $converted;
    }

    /**
     * Convert the given logic rules to the new format
     *
     * @param   array  $filters
     * @return  array
     */
    protected function convertFilters($filters)
    {
        $converted = array();

        if (is_array($filters)) {
            foreach ($filters as $filter) {
                switch ($filter['type']) {
                    case 'alpha':
                    case 'alphaNumeric':
                    case 'digits':
                        $converted[] = array(
                            'type' => $filter['type'],
                            'allowWhiteSpace' => $filter['allow_white_space']
                        );
                        break;
                    case 'regex':
                        $converted[] = array(
                            'type' => 'regex',
                            'pattern' => $filter['pattern']
                        );
                        break;
                    case 'stripTags';
                        $converted[] = array(
                            'type' => 'stripTags',
                            'allowableTags' => $filter['allowable_tags']
                        );
                        break;
                    case 'trim':
                        $converted[] = array(
                            'type' => 'trim'
                        );
                        break;
                }
            }
        }

        return $converted;
    }

    /**
     * Convert the given logic rules to the new format
     *
     * @param   array  $validators
     * @return  array
     */
    protected function convertValidators($validators)
    {
        $converted = array();

        if (is_array($validators)) {
            foreach ($validators as $validator) {
                switch ($validator['type']) {
                    case 'alpha':
                        $converted[] = array(
                            'type' => 'alpha',
                            'allowWhiteSpace' => $validator['allow_white_space'],
                            'messages' => array(
                                'notAlpha' => $validator['messages']['invalid']
                            )
                        );
                        break;
                    case 'alphaNumeric':
                        $converted[] = array(
                            'type' => 'alphaNumeric',
                            'allowWhiteSpace' => $validator['allow_white_space'],
                            'messages' => array(
                                'notAlphaNumeric' => $validator['messages']['invalid']
                            )
                        );
                        break;
                    case 'digits':
                        $converted[] = array(
                            'type' => 'digits',
                            'allowWhiteSpace' => $validator['allow_white_space'],
                            'messages' => array(
                                'notDigits' => $validator['messages']['invalid']
                            )
                        );
                        break;
                    case 'email':
                        $converted[] = array(
                            'type' => 'email',
                            'messages' => array(
                                'emailAddressInvalidFormat' => $validator['messages']['invalid']
                            )
                        );
                        break;
                    case 'greaterThan':
                        $converted[] = array(
                            'type' => 'greaterThan',
                            'min' => $validator['min'],
                            'messages' => array(
                                'notGreaterThan' => $validator['messages']['not_greater_than']
                            )
                        );
                        break;
                    case 'identical':
                        $converted[] = array(
                            'type' => 'identical',
                            'token' => $validator['token'],
                            'messages' => array(
                                'notSame' => $validator['messages']['not_match']
                            )
                        );
                        break;
                    case 'lessThan':
                        $converted[] = array(
                            'type' => 'lessThan',
                            'max' => $validator['max'],
                            'messages' => array(
                                'notLessThan' => $validator['messages']['not_less_than']
                            )
                        );
                        break;
                    case 'length':
                        $converted[] = array(
                            'type' => 'length',
                            'min' => $validator['min'],
                            'max' => $validator['max'],
                            'messages' => array(
                                'lengthTooShort' => $validator['messages']['too_short'],
                                'lengthTooLong' => $validator['messages']['too_long']
                            )
                        );
                        break;
                    case 'regex':
                        $converted[] = array(
                            'type' => 'regex',
                            'pattern' => $validator['pattern'],
                            'messages' => array(
                                'regexNotMatch' => $validator['messages']['invalid']
                            )
                        );
                        break;
                }
            }
        }

        return $converted;
    }

    /**
     * Create a duplicate validator config and set the error message if not empty
     *
     * @param   string  $message
     * @return  array
     */
    protected function createPreventDuplicatesValidator($message)
    {
        $validator = Quform_Validator_Duplicate::getDefaultConfig();

        if (Quform::isNonEmptyString($message)) {
            $validator['messages']['isDuplicate'] = $message;
        }

        return $validator;
    }

    /**
     * Credit: https://stackoverflow.com/a/31934345/1915260
     *
     * @param   string  $hex
     * @return  mixed
     */
    protected function hexToRgb($hex) {
        $hex = str_replace('#', '', $hex);
        $length = strlen($hex);

        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

        return $rgb;
    }

    /**
     * Migrate the entries for the given form
     *
     * @param  array  $oldConfig  The old form config
     * @param  array  $newConfig  The new form config
     */
    protected function migrateEntries(array $oldConfig, array $newConfig)
    {
        global $wpdb;
        $oldEntriesTableName = $wpdb->prefix . 'iphorm_form_entries';
        $oldEntryDataTableName = $wpdb->prefix . 'iphorm_form_entry_data';

        $newForm = $this->formFactory->create($newConfig);

        $oldEntries = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $oldEntriesTableName . " WHERE form_id = %d", $oldConfig['id']), ARRAY_A);

        if (is_array($oldEntries) && count($oldEntries)) {
            foreach ($oldEntries as $oldEntry) {
                $currentTime = Quform::date('Y-m-d H:i:s', null, new DateTimeZone('UTC'));

                $createdBy = null;
                if (Quform::isNonEmptyString(Quform::get($oldEntry, 'user_login'))) {
                    $createdBy = get_user_by('login', $oldEntry['user_login']);

                    if ($createdBy instanceof WP_User) {
                        $createdBy = $createdBy->ID;
                    }
                }

                $newEntry = array(
                    'form_id'       => $newForm->getId(),
                    'unread'        => Quform::get($oldEntry, 'unread') == 1 ? 1 : 0,
                    'ip'            => Quform::substr(Quform::get($oldEntry, 'ip'), 0, 45),
                    'form_url'      => Quform::substr(Quform::get($oldEntry, 'form_url'), 0, 512),
                    'referring_url' => Quform::substr(Quform::get($oldEntry, 'referring_url'), 0, 512),
                    'post_id'       => is_numeric($postId = Quform::get($oldEntry, 'post_id')) && $postId > 0 ? (int) $postId : null,
                    'created_by'    => $createdBy,
                    'created_at'    => Quform::get($oldEntry, 'date_added'),
                    'updated_at'    => $currentTime
                );

                $newEntry['data'] = array();

                $oldEntryDataRows = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $oldEntryDataTableName . " WHERE entry_id = %d", $oldEntry['id']), ARRAY_A);

                foreach ($oldEntryDataRows as $oldEntryDataRow) {
                    $element = $newForm->getElementById($oldEntryDataRow['element_id']);

                    if ($element instanceof Quform_Element_Editable && $element->config('saveToDatabase')) {
                        $element->setValue($this->convertEntryValue($element, $oldEntryDataRow['value']));

                        if ( ! $element->isEmpty()) {
                            $newEntry['data'][$element->getId()] = $element->getValueForStorage();
                        }
                    }
                }

                $this->repository->saveEntry($newEntry);
            }
        }
    }

    /**
     * Convert a 1.x entry data value to the new format
     *
     * @param   Quform_Element  $element
     * @param   string          $value
     * @return  string
     */
    protected function convertEntryValue(Quform_Element $element, $value)
    {
        $originalValue = $value;

        switch ($element->config('type')) {
            case 'checkbox':
                $values = preg_split('#(<br>|<br />)#', $value);
                $newValue = array();

                foreach ($values as $key => $value) {
                    if (is_string($value)) {
                        $newValue[$key] = $this->undoEscHtml($value);
                    }
                }

                $value = $newValue;
                break;
            case 'date':
                if (Quform::isNonEmptyString($value)) {
                    try {
                        $value =  Quform::date(
                            'Y-m-d',
                            new DateTime($value, new DateTimeZone('UTC')),
                            new DateTimeZone('UTC')
                        );
                    } catch (Exception $e) {
                        // If there was an error creating the DateTime object, keep the value as it is
                    }
                }
                break;
            case 'email':
                $value = $this->undoEscHtml(wp_strip_all_tags($value, true));
                break;
            case 'file':
                $files = preg_split('#(<br>|<br />)#', $value);
                $newValue = array();

                foreach ($files as $file) {
                    if (Quform::isNonEmptyString($file)) {
                        if (preg_match('#<a href="(.+)">(.+)</a>#', $file, $matches)) {
                            $newValue[] = array(
                                'name' => $matches[2],
                                'url' => $matches[1],
                                'quform_upload_uid' => Quform::randomString(40)
                            );
                        } else {
                            $newValue[] = array(
                                'name' => $file,
                                'quform_upload_uid' => Quform::randomString(40)
                            );
                        }
                    }
                }

                $value = $newValue;
                break;
            case 'text':
            case 'hidden':
            case 'password':
            case 'radio':
            case 'select':
                $value = $this->undoEscHtml($value);
                break;
            case 'textarea':
                $value = $this->undoEscHtml(str_replace(array('<br>', '<br />'), '', $value));
                break;
            case 'time':
                if (Quform::isNonEmptyString($value)) {
                    try {
                        $value =  Quform::date(
                            'H:i',
                            new DateTime($value, new DateTimeZone('UTC')),
                            new DateTimeZone('UTC')
                        );
                    } catch (Exception $e) {
                        // If there was an error creating the DateTime object, keep the value as it is
                    }
                }
                break;
        }

        $value = apply_filters('quform_migrator_convert_entry_value', $value, $element, $originalValue, $this);

        return $value;
    }

    /**
     * Perform the opposite of esc_html on $value and return it
     *
     * @param   string  $value
     * @return  string
     */
    protected function undoEscHtml($value)
    {
        return htmlspecialchars_decode($value, ENT_QUOTES);
    }

    /*
     * Validate the request to migrate a form
     */
    protected function validateMigrateFormRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['form_id'], $_POST['migrate_entries'])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_full_access')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_migrate_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request to import a 1.x form
     */
    public function migrateImportForm()
    {
        $this->validateMigrateImportFormRequest();

        $form = maybe_unserialize(base64_decode(wp_unslash($_POST['config'])));

        if ( ! is_array($form)) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-import-form-data' => __('The import data is invalid', 'quform')
                )
            ));
        }

        $result = $this->convertForm($form);

        if ($result['type'] == 'success') {
            $config = $this->builder->sanitizeForm($result['config']);

            $config = $this->repository->add($config);

            if ( ! is_array($config)) {
                wp_send_json(array(
                    'type' => 'error',
                    'message' => wp_kses(sprintf(
                        /* translators: %1$s: open link tag, %2$s: close link tag */
                        __('Failed to insert into database, check the %1$serror log%2$s for more information', 'quform'),
                        '<a href="https://support.themecatcher.net/quform-wordpress-v2/guides/advanced/enabling-debug-logging">',
                        '</a>'
                    ), array('a' => array('href' => array())))
                ));
            }

            $this->scriptLoader->handleSaveForm($config);

            wp_send_json(array(
                'type' => 'success',
                'message' => wp_kses(sprintf(
                    /* translators: %1$s: open link tag, %2$s: close link tag */
                    __('Form imported successfully, %1$sedit the form%2$s', 'quform'),
                    '<a href="' . esc_url(admin_url('admin.php?page=quform.forms&sp=edit&id=' . $config['id'])) . '">',
                    '</a>'
                ), array('a' => array('href' => array())))
            ));
        } else {
            wp_send_json(array(
                'type' => 'error',
                'message' => $result['message']
            ));
        }
    }

    /**
     * Validate the request to import a 1.x form
     */
    protected function validateMigrateImportFormRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['config']) || ! is_string($_POST['config'])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_import_forms')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_migrate_import_form', false, false)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request to migrate the plugin settings
     */
    public function migrateSettings()
    {
        $this->validateMigrateSettingsRequest();

        $options = array();

        if ($_POST['migrate_license_key'] === '1') {
            $licenseKey = get_option('iphorm_licence_key');

            if (Quform::isNonEmptyString($licenseKey)) {
                $options['licenseKey'] = $licenseKey;
            }
        }

        if ($_POST['migrate_recaptcha_keys'] === '1') {
            $siteKey = get_option('iphorm_recaptcha_site_key');
            $secretKey = get_option('iphorm_recaptcha_secret_key');

            if ( ! Quform::isNonEmptyString($siteKey) && ! Quform::isNonEmptyString($secretKey)) {
                // Check the options that existed pre v1.4.18
                $siteKey = get_option('iphorm_recaptcha_public_key');
                $secretKey = get_option('iphorm_recaptcha_private_key');
            }

            if (Quform::isNonEmptyString($siteKey)) {
                $options['recaptchaSiteKey'] = $siteKey;
            }

            if (Quform::isNonEmptyString($secretKey)) {
                $options['recaptchaSecretKey'] = $secretKey;
            }
        }

        if (count($options)) {
            $this->options->set($options);
        }

        wp_send_json(array(
            'type' => 'success'
        ));
    }

    /**
     * Validate the request to migrate the plugin settings
     */
    protected function validateMigrateSettingsRequest()
    {
        if (
            ! Quform::isPostRequest() ||
            ! isset($_POST['migrate_license_key'], $_POST['migrate_recaptcha_keys']) ||
            ! is_string($_POST['migrate_license_key']) ||
            ! is_string($_POST['migrate_recaptcha_keys'])
        ) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_full_access')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_migrate_settings', false, false)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Get forms from the 1.x plugin version
     *
     * @return array
     */
    public function get1xForms()
    {
        global $wpdb;

        $table = $wpdb->prefix . 'iphorm_forms';
        $forms = array();

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE '%s'", $wpdb->esc_like($table))) == $table) {
            $results = $wpdb->get_results("SELECT * FROM " . $table . " ORDER BY id ASC", ARRAY_A);

            foreach ($results as $result) {
                $form = maybe_unserialize($result['config']);

                if (is_array($form)) {
                    $forms[] = $form;
                }
            }
        }

        return $forms;
    }

    /**
     * Get forms from the 1.x plugin version
     *
     * @param   int    $formId
     * @return  array
     */
    public function get1xForm($formId)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'iphorm_forms';
        $form = null;

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE '%s'", $wpdb->esc_like($table))) == $table) {
            $form = $wpdb->get_var($wpdb->prepare("SELECT config FROM " . $table . " WHERE id = %d", (int) $formId));
            $form = maybe_unserialize($form);
        }

        return $form;
    }
}
