<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Builder
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $factory;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Themes
     */
    protected $themes;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $factory
     * @param  Quform_Options       $options
     * @param  Quform_Themes        $themes
     * @param  Quform_ScriptLoader  $scriptLoader
     */
    public function __construct(Quform_Repository $repository, Quform_Form_Factory $factory, Quform_Options $options,
                                Quform_Themes $themes, Quform_ScriptLoader $scriptLoader)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->options = $options;
        $this->themes = $themes;
        $this->scriptLoader = $scriptLoader;
    }

    /**
     * Get the localisation / variables to pass to the builder JS
     *
     * @return array
     */
    public function getScriptL10n()
    {
        $data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'pluginUrl' => Quform::url(),
            'saveFormNonce' => wp_create_nonce('quform_save_form'),
            'formSaved' => __('Form saved', 'quform'),
            'confirmRemoveElement' => __('Are you sure you want to remove this element? Any previously submitted form data for this element will no longer be accessible.', 'quform'),
            'confirmRemoveGroup' => __('Are you sure you want to remove this group? All elements inside this group will also be removed. Any previously submitted form data for elements inside this group will no longer be accessible.', 'quform'),
            'confirmRemovePage' => __('Are you sure you want to remove this page? All elements inside this page will also be removed. Any previously submitted form data for elements inside this page will no longer be accessible.', 'quform'),
            'confirmRemoveRow' => __('Are you sure you want to remove this row? All elements inside this row will also be removed. Any previously submitted form data for elements inside this row will no longer be accessible.', 'quform'),
            'confirmRemoveColumn' => __('Are you sure you want to remove this column? All elements inside this column will also be removed. Any previously submitted form data for elements inside this column will no longer be accessible.', 'quform'),
            'confirmRemoveOptgroup' => __('Are you sure you want to remove this optgroup? Any options inside of it will also be removed.', 'quform'),
            'confirmRemoveSubmit' => __('Are you sure you want to remove this submit button?', 'quform'),
            'nestingOptgroupError' => __('Nested optgroups are not supported.', 'quform'),
            'errorSavingForm' => __('Error saving the form', 'quform'),
            'atLeastOneToCcBccRequired' => __('At least one To, Cc or Bcc address is required', 'quform'),
            'correctHighlightedFields' => __('Please correct the highlighted fields and save the form again', 'quform'),
            'inherit' => __('Inherit', 'quform'),
            'field' => __('Field', 'quform'),
            'icon' => __('Icon', 'quform'),
            'above' => __('Above', 'quform'),
            'left' => __('Left', 'quform'),
            'inside' => __('Inside', 'quform'),
            'atLeastOnePage' => __('The form must have at least one page', 'quform'),
            'loadedPreviewLocales' => $this->getLoadedPreviewLocales(),
            'exampleTooltip' => __('This is an example tooltip!', 'quform'),
            'remove' => _x('Remove', 'delete', 'quform'),
            'selectOptionHtml' => $this->getOptionHtml('select'),
            'checkboxOptionHtml' => $this->getOptionHtml('checkbox'),
            'radioOptionHtml' => $this->getOptionHtml('radio'),
            'multiselectOptionHtml' => $this->getOptionHtml('multiselect'),
            'optgroupHtml' => $this->getOptgroupHtml(),
            'bulkOptions' => $this->getBulkOptions(),
            'defaultOptions' => $this->getDefaultOptions(),
            'defaultOptgroups' => $this->getDefaultOptgroups(),
            'logicRuleHtml' => $this->getLogicRuleHtml(),
            'noLogicElements' => __('There are no elements available to use for logic rules.', 'quform'),
            'noLogicRules' => __('There are no logic rules yet, click "Add logic rule" to add one.', 'quform'),
            'logicSourceTypes' => $this->getLogicSourceTypes(),
            'thisFieldMustBePositiveNumberOrZero' => __('This field must be a positive number or zero', 'quform'),
            'atLeastOneLogicRuleRequired' => __('At least one logic rule is required', 'quform'),
            'showThisGroup' => __('Show this group', 'quform'),
            'hideThisGroup' => __('Hide this group', 'quform'),
            'showThisField' => __('Show this field', 'quform'),
            'hideThisField' => __('Hide this field', 'quform'),
            'showThisPage' => __('Show this page', 'quform'),
            'hideThisPage' => __('Hide this page', 'quform'),
            'useThisConfirmationIfAll' => __('Use this confirmation if all of these rules match', 'quform'),
            'useThisConfirmationIfAny' => __('Use this confirmation if any of these rules match', 'quform'),
            'sendToTheseRecipientsIfAll' => __('Send to these recipients if all of these rules match', 'quform'),
            'sendToTheseRecipientsIfAny' => __('Send to these recipients if any of these rules match', 'quform'),
            'ifAllOfTheseRulesMatch' => __('if all of these rules match', 'quform'),
            'ifAnyOfTheseRulesMatch' => __('if any of these rules match', 'quform'),
            'addRecipient' => __('Add recipient', 'quform'),
            'addLogicRule' => __('Add logic rule', 'quform'),
            'noConditionals' => __('There are no conditionals yet, click "Add conditional" to add one.', 'quform'),
            'is' => __('is', 'quform'),
            'isNot' => __('is not', 'quform'),
            'isEmpty' => __('is empty', 'quform'),
            'isNotEmpty' => __('is not empty', 'quform'),
            'greaterThan' => __('is greater than', 'quform'),
            'lessThan' => __('is less than', 'quform'),
            'contains' => __('contains', 'quform'),
            'startsWith' => __('starts with', 'quform'),
            'endsWith' => __('ends with', 'quform'),
            'enterValue' => __('Enter a value', 'quform'),
            'unsavedChanges' => __('You have unsaved changes.', 'quform'),
            'previewError' => __('An error occurred loading the preview', 'quform'),
            'untitled' =>  __('Untitled', 'quform'),
            'pageTabNavHtml' => $this->getPageTabNavHtml(),
            /* translators: %s: the page number */
            'pageTabNavText' => __('Page %s', 'quform'),
            'elements' => $this->getElements(),
            'elementHtml' => $this->getDefaultElementHtml('text'),
            'groupHtml' => $this->getDefaultElementHtml('group'),
            'pageHtml' => $this->getDefaultElementHtml('page'),
            'rowHtml' => $this->getDefaultElementHtml('row'),
            'columnHtml' => $this->getDefaultElementHtml('column'),
            'styles' => $this->getStyles(),
            'styleHtml' => $this->getStyleHtml(),
            'globalStyles' => $this->getGlobalStyles(),
            'globalStyleHtml' => $this->getGlobalStyleHtml(),
            'visibleStyles' => $this->getVisibleStyles(),
            'filters' => $this->getFilters(),
            'filterHtml' => $this->getFilterHtml(),
            'visibleFilters' => $this->getVisibleFilters(),
            'validators' => $this->getValidators(),
            'validatorHtml' => $this->getValidatorHtml(),
            'visibleValidators' => $this->getVisibleValidators(),
            'notification' => Quform_Notification::getDefaultConfig(),
            'notificationHtml' => $this->getNotificationHtml(),
            'notificationConfirmRemove' => __('Are you sure you want to remove this notification?', 'quform'),
            'sendThisNotification' => __('Send this notification', 'quform'),
            'doNotSendThisNotification' => __('Do not send this notification', 'quform'),
            'recipientHtml' => $this->getRecipientHtml(),
            'popupTriggerText' => __('Click me', 'quform'),
            'attachmentHtml' => $this->getAttachmentHtml(),
            'selectFiles' => __('Select Files', 'quform'),
            'selectElement' => __('Select an element', 'quform'),
            'attachmentSourceTypes' => $this->getAttachmentSourceTypes(),
            'noAttachmentSourcesFound' => __('No attachment sources found', 'quform'),
            'noAttachments' => __('There are no attachments yet, click "Add attachment" to add one.', 'quform'),
            'selectOneFile' => __('Select at least one file', 'quform'),
            'confirmation' => Quform_Confirmation::getDefaultConfig(),
            'confirmationHtml' => $this->getConfirmationHtml(),
            'cannotRemoveDefaultConfirmation' => __('The default confirmation cannot be removed', 'quform'),
            'confirmationConfirmRemove' => __('Are you sure you want to remove this confirmation?', 'quform'),
            'dbPasswordHtml' => $this->getDbPasswordHtml(),
            'dbColumnHtml' => $this->getDbColumnHtml(),
            'areYouSure' => __('Are you sure?', 'quform'),
            'emailRemoveBrackets' => __('Please remove the brackets from the email address', 'quform'),
            'themes' => $this->getThemes(),
            'collapse' => __('Collapse', 'quform'),
            'expand' => __('Expand', 'quform'),
            /* translators: %s: the column number */
            'columnNumber' => __('Column %d', 'quform'),
            'columnWidthMustBeNumeric' => __('Column width must be numeric', 'quform'),
            'columnWidthTotalTooHigh' => __('Total of column widths must not be higher than 100', 'quform'),
            'pageSettings' => __('Page settings', 'quform'),
            'groupSettings' => __('Group settings', 'quform'),
            'rowSettings' => __('Row settings', 'quform'),
            'elementSettings' => __('Element settings', 'quform'),
            'pleaseSelect' => __('Please select', 'quform'),
            'buttonIcon' => __('Button icon', 'quform'),
            'buttonIconPosition' => __('Button icon position', 'quform'),
            'dropzoneIcon' => __('Dropzone icon', 'quform'),
            'dropzoneIconPosition' => __('Dropzone icon position', 'quform'),
            'displayAMessage' => __('Display a message', 'quform'),
            'redirectTo' => __('Redirect to', 'quform'),
            'reloadThePage' => __('Reload the page', 'quform'),
            'enableCustomizeValuesToChange' => __('Enable the "Customize values" setting to change the value', 'quform'),
            'everyone' => __('Everyone', 'quform'),
            'adminOnly' => __('Admin only', 'quform'),
            'loggedInUsersOnly' => __('Logged in users only', 'quform'),
            'loggedOutUsersOnly' => __('Logged out users only', 'quform'),
            /* translators: %1$s: element admin label, %2$s: element unique ID */
            'adminLabelElementId' => __('%1$s (%2$s)', 'quform'),
            'loadingDots' => __('Loading...', 'quform'),
            /* translators: %s: the post ID */
            'errorLoadingPageTitle' => __('Error loading the title for post ID %s', 'quform'),
            'searchPostsNonce' => wp_create_nonce('quform_builder_search_posts'),
            'getPostTitleNonce' => wp_create_nonce('quform_builder_get_post_title')
        );

        $params = array(
            'l10n_print_after' => 'quformBuilderL10n = ' . wp_json_encode($data)
        );

        return $params;
    }

    /**
     * Prepare a form for editing
     *
     * @param   array  $form
     * @return  array
     */
    public function prepare(array $form) {
        Quform::set(
            $form,
            'scheduleStart',
            $this->convertDateTimeToWpTimezone($this->getFormConfigValue($form, 'scheduleStart'))
        );

        Quform::set(
            $form,
            'scheduleEnd',
            $this->convertDateTimeToWpTimezone($this->getFormConfigValue($form, 'scheduleEnd'))
        );

        return $form;
    }

    /**
     * Get the HTML for an option for a select element
     *
     * @param   string  $type  The element type, 'select', 'radio', 'checkbox' or 'multiselect'
     * @return  string
     */
    protected function getOptionHtml($type)
    {
        $output = sprintf('<div class="qfb-option qfb-option-type-%s qfb-box qfb-cf">', $type);

        $output .= '<div class="qfb-option-left"><div class="qfb-option-left-inner">';
        $output .= '<div class="qfb-settings-row qfb-settings-row-2">';
        $output .= '<div class="qfb-settings-column">';
        $output .= sprintf('<input class="qfb-option-label" type="text" placeholder="%s">', esc_attr__('Label', 'quform'));
        $output .= '</div>';
        $output .= '<div class="qfb-settings-column">';
        $output .= sprintf('<input class="qfb-option-value" type="text" placeholder="%s">', esc_attr__('Value', 'quform'));
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div></div>';

        $output .= '<div class="qfb-option-right">';

        $output .= '<div class="qfb-option-actions">';

        $output .= sprintf('<span class="qfb-option-action-set-default" title="%s"><i class="qfb-icon qfb-icon-check"></i></span>', esc_attr__('Default value', 'quform'));
        $output .= '<span class="qfb-option-action-add"><i class="qfb-icon qfb-icon-plus"></i></span>';
        $output .= '<span class="qfb-option-action-duplicate"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>';
        $output .= '<span class="qfb-option-action-remove"><i class="qfb-icon qfb-icon-trash"></i></span>';
        if ($type == 'radio' || $type == 'checkbox') {
            $output .= '<span class="qfb-option-action-settings"><i class="qfb-mdi qfb-mdi-settings"></i></span>';
        }
        $output .= '<span class="qfb-option-action-move"><i class="qfb-icon qfb-icon-arrows"></i></span>';

        $output .= '</div>';
        $output .= '</div>';

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for an optgroup for a select element
     *
     * @return  string
     */
    protected function getOptgroupHtml()
    {
        $output = '<div class="qfb-optgroup qfb-box qfb-cf"><div class="qfb-optgroup-top qfb-cf">';
        $output .= '<div class="qfb-optgroup-left"><div class="qfb-optgroup-left-inner">';
        $output .= sprintf('<input class="qfb-optgroup-label" type="text" placeholder="%s">', esc_attr__('Optgroup label', 'quform'));
        $output .= '</div></div>';
        $output .= '<div class="qfb-optgroup-right">';
        $output .= '<div class="qfb-optgroup-actions">';
        $output .= '<span class="qfb-optgroup-action-add"><i class="qfb-icon qfb-icon-plus"></i></span>';
        $output .= '<span class="qfb-optgroup-action-remove"><i class="qfb-icon qfb-icon-trash"></i></span>';
        $output .= '<span class="qfb-optgroup-action-move"><i class="qfb-icon qfb-icon-arrows"></i></span>';
        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div></div>';

        return $output;
    }

    /**
     * Get the default option config for each element type
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return array(
            'select' => Quform_Element_Select::getDefaultOptionConfig(),
            'checkbox' => Quform_Element_Checkbox::getDefaultOptionConfig(),
            'radio' => Quform_Element_Radio::getDefaultOptionConfig(),
            'multiselect' => Quform_Element_Multiselect::getDefaultOptionConfig()
        );
    }

    /**
     * Get the default optgroup config for each element type
     *
     * @return array
     */
    protected function getDefaultOptgroups()
    {
        return array(
            'select' => Quform_Element_Select::getDefaultOptgroupConfig(),
            'multiselect' => Quform_Element_Multiselect::getDefaultOptgroupConfig()
        );
    }

    /*
     * Get the predefined bulk options
     *
     * @return array
     */
    public function getBulkOptions()
    {
        return apply_filters('quform_bulk_options', array(
            'countries' => array(
                'name' => __('Countries', 'quform'),
                'options' => $this->getCountries()
            ),
            'usStates' => array(
                'name' => __('U.S. States', 'quform'),
                'options' => $this->getUsStates()
            ),
            'canadianProvinces' => array(
                'name' => __('Canadian Provinces', 'quform'),
                'options' => $this->getCanadianProvinces()
            ),
            'ukCounties' => array(
                'name' => __('UK Counties', 'quform'),
                'options' => $this->getUkCounties()
            ),
            'germanStates' => array(
                'name' => __('German States', 'quform'),
                'options' => array('Baden-Wurttemberg', 'Bavaria', 'Berlin', 'Brandenburg', 'Bremen', 'Hamburg', 'Hesse', 'Mecklenburg-West Pomerania', 'Lower Saxony', 'North Rhine-Westphalia', 'Rhineland-Palatinate', 'Saarland', 'Saxony', 'Saxony-Anhalt', 'Schleswig-Holstein', 'Thuringia')
            ),
            'dutchProvinces' => array(
                'name' => __('Dutch Provinces', 'quform'),
                'options' => array('Drente', 'Flevoland', 'Friesland', 'Gelderland', 'Groningen', 'Limburg', 'Noord-Brabant', 'Noord-Holland', 'Overijssel', 'Zuid-Holland', 'Utrecht', 'Zeeland')
            ),
            'continents' => array(
                'name' => __('Continents', 'quform'),
                'options' => array(__('Africa', 'quform'), __('Antarctica', 'quform'), __('Asia', 'quform'), __('Australia', 'quform'), __('Europe', 'quform'), __('North America', 'quform'), __('South America', 'quform'))
            ),
            'gender' => array(
                'name' => __('Gender', 'quform'),
                'options' => array(__('Male', 'quform'), __('Female', 'quform'))
            ),
            'age' => array(
                'name' => __('Age', 'quform'),
                'options' => array(__('Under 18', 'quform'), __('18-24', 'quform'), __('25-34', 'quform'), __('35-44', 'quform'), __('45-54', 'quform'), __('55-64', 'quform'), __('65 or over', 'quform'))
            ),
            'maritalStatus' => array(
                'name' => __('Marital Status', 'quform'),
                'options' => array(__('Single', 'quform'), __('Married', 'quform'), __('Divorced', 'quform'), __('Widowed', 'quform'))
            ),
            'income' => array(
                'name' => __('Income', 'quform'),
                'options' => array(__('Under $20,000', 'quform'), __('$20,000 - $30,000', 'quform'), __('$30,000 - $40,000', 'quform'), __('$40,000 - $50,000', 'quform'), __('$50,000 - $75,000', 'quform'), __('$75,000 - $100,000', 'quform'), __('$100,000 - $150,000', 'quform'), __('$150,000 or more', 'quform'))
            ),
            'days' => array(
                'name' => __('Days', 'quform'),
                'options' => array(__('Monday', 'quform'), __('Tuesday', 'quform'), __('Wednesday', 'quform'), __('Thursday', 'quform'), __('Friday', 'quform'), __('Saturday', 'quform'), __('Sunday', 'quform'))
            ),
            'months' => array(
                'name' => __('Months', 'quform'),
                'options' => array_values($this->getAllMonths())
            )
        ));
    }

    /**
     * Returns an array of all countries
     *
     * @return array
     */
    protected function getCountries()
    {
        return apply_filters('quform_countries', array(
            __('Afghanistan', 'quform'), __('Albania', 'quform'), __('Algeria', 'quform'), __('American Samoa', 'quform'), __('Andorra', 'quform'), __('Angola', 'quform'), __('Anguilla', 'quform'), __('Antarctica', 'quform'), __('Antigua And Barbuda', 'quform'), __('Argentina', 'quform'), __('Armenia', 'quform'), __('Aruba', 'quform'), __('Australia', 'quform'), __('Austria', 'quform'), __('Azerbaijan', 'quform'), __('Bahamas', 'quform'), __('Bahrain', 'quform'), __('Bangladesh', 'quform'), __('Barbados', 'quform'), __('Belarus', 'quform'), __('Belgium', 'quform'),
            __('Belize', 'quform'), __('Benin', 'quform'), __('Bermuda', 'quform'), __('Bhutan', 'quform'), __('Bolivia', 'quform'), __('Bosnia And Herzegovina', 'quform'), __('Botswana', 'quform'), __('Bouvet Island', 'quform'), __('Brazil', 'quform'), __('British Indian Ocean Territory', 'quform'), __('Brunei Darussalam', 'quform'), __('Bulgaria', 'quform'), __('Burkina Faso', 'quform'), __('Burundi', 'quform'), __('Cambodia', 'quform'), __('Cameroon', 'quform'), __('Canada', 'quform'), __('Cape Verde', 'quform'), __('Cayman Islands', 'quform'), __('Central African Republic', 'quform'), __('Chad', 'quform'),
            __('Chile', 'quform'), __('China', 'quform'), __('Christmas Island', 'quform'), __('Cocos (Keeling) Islands', 'quform'), __('Colombia', 'quform'), __('Comoros', 'quform'), __('Congo', 'quform'), __('Congo, The Democratic Republic Of The', 'quform'), __('Cook Islands', 'quform'), __('Costa Rica', 'quform'), __('Cote D\'Ivoire', 'quform'), __('Croatia (Local Name: Hrvatska)', 'quform'), __('Cuba', 'quform'), __('Cyprus', 'quform'), __('Czech Republic', 'quform'), __('Denmark', 'quform'), __('Djibouti', 'quform'), __('Dominica', 'quform'), __('Dominican Republic', 'quform'), __('East Timor', 'quform'), __('Ecuador', 'quform'),
            __('Egypt', 'quform'), __('El Salvador', 'quform'), __('Equatorial Guinea', 'quform'), __('Eritrea', 'quform'), __('Estonia', 'quform'), __('Ethiopia', 'quform'), __('Falkland Islands (Malvinas)', 'quform'), __('Faroe Islands', 'quform'), __('Fiji', 'quform'), __('Finland', 'quform'), __('France', 'quform'), __('France, Metropolitan', 'quform'), __('French Guiana', 'quform'), __('French Polynesia', 'quform'), __('French Southern Territories', 'quform'), __('Gabon', 'quform'), __('Gambia', 'quform'), __('Georgia', 'quform'), __('Germany', 'quform'), __('Ghana', 'quform'), __('Gibraltar', 'quform'),
            __('Greece', 'quform'), __('Greenland', 'quform'), __('Grenada', 'quform'), __('Guadeloupe', 'quform'), __('Guam', 'quform'), __('Guatemala', 'quform'), __('Guinea', 'quform'), __('Guinea-Bissau', 'quform'), __('Guyana', 'quform'), __('Haiti', 'quform'), __('Heard And Mc Donald Islands', 'quform'), __('Holy See (Vatican City State)', 'quform'), __('Honduras', 'quform'), __('Hong Kong', 'quform'), __('Hungary', 'quform'), __('Iceland', 'quform'), __('India', 'quform'), __('Indonesia', 'quform'), __('Iran (Islamic Republic Of)', 'quform'), __('Iraq', 'quform'), __('Ireland', 'quform'),
            __('Israel', 'quform'), __('Italy', 'quform'), __('Jamaica', 'quform'), __('Japan', 'quform'), __('Jordan', 'quform'), __('Kazakhstan', 'quform'), __('Kenya', 'quform'), __('Kiribati', 'quform'), __('Korea, Democratic People\'s Republic Of', 'quform'), __('Korea, Republic Of', 'quform'), __('Kuwait', 'quform'), __('Kyrgyzstan', 'quform'), __('Lao People\'s Democratic Republic', 'quform'), __('Latvia', 'quform'), __('Lebanon', 'quform'), __('Lesotho', 'quform'), __('Liberia', 'quform'), __('Libyan Arab Jamahiriya', 'quform'), __('Liechtenstein', 'quform'), __('Lithuania', 'quform'), __('Luxembourg', 'quform'),
            __('Macau', 'quform'), __('Macedonia, Former Yugoslav Republic Of', 'quform'), __('Madagascar', 'quform'), __('Malawi', 'quform'), __('Malaysia', 'quform'), __('Maldives', 'quform'), __('Mali', 'quform'), __('Malta', 'quform'), __('Marshall Islands', 'quform'), __('Martinique', 'quform'), __('Mauritania', 'quform'), __('Mauritius', 'quform'), __('Mayotte', 'quform'), __('Mexico', 'quform'), __('Micronesia, Federated States Of', 'quform'), __('Moldova, Republic Of', 'quform'), __('Monaco', 'quform'), __('Mongolia', 'quform'), __('Montserrat', 'quform'), __('Morocco', 'quform'), __('Mozambique', 'quform'),
            __('Myanmar', 'quform'), __('Namibia', 'quform'), __('Nauru', 'quform'), __('Nepal', 'quform'), __('Netherlands', 'quform'), __('Netherlands Antilles', 'quform'), __('New Caledonia', 'quform'), __('New Zealand', 'quform'), __('Nicaragua', 'quform'), __('Niger', 'quform'), __('Nigeria', 'quform'), __('Niue', 'quform'), __('Norfolk Island', 'quform'), __('Northern Mariana Islands', 'quform'), __('Norway', 'quform'), __('Oman', 'quform'), __('Pakistan', 'quform'), __('Palau', 'quform'), __('Panama', 'quform'), __('Papua New Guinea', 'quform'), __('Paraguay', 'quform'),
            __('Peru', 'quform'), __('Philippines', 'quform'), __('Pitcairn', 'quform'), __('Poland', 'quform'), __('Portugal', 'quform'), __('Puerto Rico', 'quform'), __('Qatar', 'quform'), __('Reunion', 'quform'), __('Romania', 'quform'), __('Russian Federation', 'quform'), __('Rwanda', 'quform'), __('Saint Kitts And Nevis', 'quform'), __('Saint Lucia', 'quform'), __('Saint Vincent And The Grenadines', 'quform'), __('Samoa', 'quform'), __('San Marino', 'quform'), __('Sao Tome And Principe', 'quform'), __('Saudi Arabia', 'quform'), __('Senegal', 'quform'), __('Seychelles', 'quform'), __('Sierra Leone', 'quform'),
            __('Singapore', 'quform'), __('Slovakia (Slovak Republic)', 'quform'), __('Slovenia', 'quform'), __('Solomon Islands', 'quform'), __('Somalia', 'quform'), __('South Africa', 'quform'), __('South Georgia, South Sandwich Islands', 'quform'), __('Spain', 'quform'), __('Sri Lanka', 'quform'), __('St. Helena', 'quform'), __('St. Pierre And Miquelon', 'quform'), __('Sudan', 'quform'), __('Suriname', 'quform'), __('Svalbard And Jan Mayen Islands', 'quform'), __('Swaziland', 'quform'), __('Sweden', 'quform'), __('Switzerland', 'quform'), __('Syrian Arab Republic', 'quform'), __('Taiwan', 'quform'), __('Tajikistan', 'quform'), __('Tanzania, United Republic Of', 'quform'),
            __('Thailand', 'quform'), __('Togo', 'quform'), __('Tokelau', 'quform'), __('Tonga', 'quform'), __('Trinidad And Tobago', 'quform'), __('Tunisia', 'quform'), __('Turkey', 'quform'), __('Turkmenistan', 'quform'), __('Turks And Caicos Islands', 'quform'), __('Tuvalu', 'quform'), __('Uganda', 'quform'), __('Ukraine', 'quform'), __('United Arab Emirates', 'quform'), __('United Kingdom', 'quform'), __('United States', 'quform'), __('United States Minor Outlying Islands', 'quform'), __('Uruguay', 'quform'), __('Uzbekistan', 'quform'), __('Vanuatu', 'quform'), __('Venezuela', 'quform'), __('Vietnam', 'quform'),
            __('Virgin Islands (British)', 'quform'), __('Virgin Islands (U.S.)', 'quform'), __('Wallis And Futuna Islands', 'quform'), __('Western Sahara', 'quform'), __('Yemen', 'quform'), __('Yugoslavia', 'quform'), __('Zambia', 'quform'), __('Zimbabwe', 'quform')
        ));
    }

    /**
     * Returns an array of US states
     *
     * @return array
     */
    protected function getUsStates()
    {
        return array(
            'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware',
            'District Of Columbia', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas',
            'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi',
            'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York',
            'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island',
            'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington',
            'West Virginia', 'Wisconsin', 'Wyoming'
        );
    }

    /**
     * Returns an array of Canadian Provinces / Territories
     *
     * @return array
     */
    protected function getCanadianProvinces()
    {
        return array(
            'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick', 'Newfoundland & Labrador',
            'Northwest Territories', 'Nova Scotia', 'Nunavut','Ontario', 'Prince Edward Island', 'Quebec',
            'Saskatchewan', 'Yukon'
        );
    }

    /**
     * Returns an array of UK counties
     *
     * @return array
     */
    protected function getUkCounties()
    {
        return array(
            'Aberdeen City', 'Aberdeenshire', 'Angus', 'Antrim', 'Argyll and Bute', 'Armagh', 'Avon', 'Banffshire',
            'Bedfordshire', 'Berkshire', 'Blaenau Gwent', 'Borders', 'Bridgend', 'Bristol', 'Buckinghamshire',
            'Caerphilly', 'Cambridgeshire', 'Cardiff', 'Carmarthenshire', 'Ceredigion', 'Channel Islands', 'Cheshire',
            'Clackmannan', 'Cleveland', 'Conwy', 'Cornwall', 'Cumbria', 'Denbighshire', 'Derbyshire', 'Devon', 'Dorset',
            'Down', 'Dumfries and Galloway', 'Durham', 'East Ayrshire', 'East Dunbartonshire', 'East Lothian',
            'East Renfrewshire', 'East Riding of Yorkshire', 'East Sussex', 'Edinburgh City', 'Essex', 'Falkirk',
            'Fermanagh', 'Fife', 'Flintshire', 'Glasgow (City of)', 'Gloucestershire', 'Greater Manchester', 'Gwynedd',
            'Hampshire', 'Herefordshire', 'Hertfordshire', 'Highland', 'Humberside', 'Inverclyde', 'Isle of Anglesey',
            'Isle of Man', 'Isle of Wight', 'Isles of Scilly', 'Kent', 'Lancashire', 'Leicestershire', 'Lincolnshire',
            'London', 'Londonderry', 'Merseyside', 'Merthyr Tydfil', 'Middlesex', 'Midlothian', 'Monmouthshire',
            'Moray', 'Neath Port Talbot', 'Newport', 'Norfolk', 'North Ayrshire', 'North East Lincolnshire',
            'North Lanarkshire', 'North Yorkshire', 'Northamptonshire', 'Northumberland', 'Nottinghamshire',
            'Orkney', 'Oxfordshire', 'Pembrokeshire', 'Perthshire and Kinross', 'Powys', 'Renfrewshire',
            'Rhondda Cynon Taff', 'Roxburghshire', 'Rutland', 'Shetland', 'Shropshire', 'Somerset', 'South Ayrshire',
            'South Lanarkshire', 'South Yorkshire', 'Staffordshire', 'Stirling', 'Suffolk', 'Surrey', 'Swansea',
            'The Vale of Glamorgan', 'Torfaen', 'Tyne and Wear', 'Tyrone', 'Warwickshire', 'West Dunbartonshire',
            'West Lothian', 'West Midlands', 'West Sussex', 'West Yorkshire', 'Western Isles', 'Wiltshire',
            'Worcestershire', 'Wrexham'
        );
    }

    /**
     * Get all the months in the year
     *
     * @return array
     */
    protected function getAllMonths()
    {
        return apply_filters('quform_get_all_months', array(
            1  => __('January', 'quform'),
            2  => __('February', 'quform'),
            3  => __('March', 'quform'),
            4  => __('April', 'quform'),
            5  => __('May', 'quform'),
            6  => __('June', 'quform'),
            7  => __('July', 'quform'),
            8  => __('August', 'quform'),
            9  => __('September', 'quform'),
            10 => __('October', 'quform'),
            11 => __('November', 'quform'),
            12 => __('December', 'quform')
        ));
    }

    /**
     * Get the core form elements config
     *
     * @param   string|null  $type  The element type or null for all elements
     * @return  array
     */
    public function getElements($type = null)
    {
        $elements = array(
            'text' => array(
                'name' => _x('Text', 'text input field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-pencil"></i>',
                'config' => Quform_Element_Text::getDefaultConfig()
            ),
            'textarea' => array(
                'name' => _x('Textarea', 'textarea input field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-align-left"></i>',
                'config' => Quform_Element_Textarea::getDefaultConfig()
            ),
            'email' => array(
                'name' => _x('Email', 'email address field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-envelope"></i>',
                'config' => Quform_Element_Email::getDefaultConfig()
            ),
            'select' => array(
                'name' => _x('Select Menu', 'select menu field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-caret-square-o-down"></i>',
                'config' => Quform_Element_Select::getDefaultConfig()
            ),
            'checkbox' => array(
                'name' => _x('Checkboxes', 'checkboxes field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-check-square-o"></i>',
                'config' => Quform_Element_Checkbox::getDefaultConfig()
            ),
            'radio' => array(
                'name' => _x('Radio Buttons', 'radio buttons field', 'quform'),
                'icon' => '<i class="qfb-mdi qfb-mdi-radio_button_checked"></i>',
                'config' => Quform_Element_Radio::getDefaultConfig()
            ),
            'multiselect' => array(
                'name' => _x('Multi Select', 'multi select field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-list-ul"></i>',
                'config' => Quform_Element_Multiselect::getDefaultConfig()
            ),
            'file' => array(
                'name' => __('File Upload', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-upload"></i>',
                'config' => Quform_Element_File::getDefaultConfig()
            ),
            'date' => array(
                'name' => _x('Date', 'date field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-calendar"></i>',
                'config' => Quform_Element_Date::getDefaultConfig()
            ),
            'time' => array(
                'name' => _x('Time', 'time field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-clock-o"></i>',
                'config' => Quform_Element_Time::getDefaultConfig()
            ),
            'name' => array(
                'name' => _x('Name', 'name field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-user"></i>',
                'config' => Quform_Element_Name::getDefaultConfig()
            ),
            'password' => array(
                'name' => _x('Password', 'password input field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-lock"></i>',
                'config' => Quform_Element_Password::getDefaultConfig()
            ),
            'html' => array(
                'name' => __('HTML', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-code"></i>',
                'config' => Quform_Element_Html::getDefaultConfig()
            ),
            'hidden' => array(
                'name' => __('Hidden', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-eye-slash"></i>',
                'config' => Quform_Element_Hidden::getDefaultConfig()
            ),
            'captcha' => array(
                'name' => _x('CAPTCHA', 'captcha field', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-handshake-o"></i>',
                'config' => Quform_Element_Captcha::getDefaultConfig()
            ),
            'recaptcha' => array(
                'name' => __('reCAPTCHA', 'quform'),
                'icon' => '<i class="qfb-mdi qfb-mdi-face"></i>',
                'config' => Quform_Element_Recaptcha::getDefaultConfig()
            ),
            'submit' => array(
                'name' => _x('Submit', 'submit button element', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-paper-plane"></i>',
                'config' => Quform_Element_Submit::getDefaultConfig()
            ),
            'page' => array(
                'name' => __('Page', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-file-o"></i>',
                'config' => Quform_Element_Page::getDefaultConfig()
            ),
            'group' => array(
                'name' => __('Group', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-object-group"></i>',
                'config' => Quform_Element_Group::getDefaultConfig()
            ),
            'row' => array(
                'name' => __('Column Layout', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-columns"></i>',
                'config' => Quform_Element_Row::getDefaultConfig()
            ),
            'column' => array(
                'name' => __('Column', 'quform'),
                'icon' => '<i class="qfb-icon qfb-icon-columns"></i>',
                'config' => Quform_Element_Column::getDefaultConfig()
            )
        );

        $elements = apply_filters('quform_admin_elements', $elements);

        if (is_string($type) && isset($elements[$type])) {
            return $elements[$type];
        }

        return $elements;
    }

    /**
     * Get the default config for the element of the given type
     *
     * @param   string  $type  The element type
     * @return  array          The default element config
     */
    protected function getDefaultElementConfig($type)
    {
        $element = $this->getElements($type);

        return $element['config'];
    }

    /**
     * Get the element styles data
     *
     * @return array
     */
    public function getStyles()
    {
        $styles = array(
            'element' => array('name' => __('Outer wrapper', 'quform')),
            'elementSpacer' => array('name' => __('Spacer', 'quform')),
            'elementLabel' => array('name' => __('Label', 'quform')),
            'elementLabelText' => array('name' => __('Label text', 'quform')),
            'elementRequiredText' => array('name' => __('Required text', 'quform')),
            'elementInner' => array('name' => __('Inner wrapper', 'quform')),
            'elementInput' => array('name' => __('Input wrapper', 'quform')),
            'elementText' => array('name' => __('Text input field', 'quform')),
            'elementTextHover' => array('name' => __('Text input field (hover)', 'quform')),
            'elementTextFocus' => array('name' => __('Text input field (focus)', 'quform')),
            'elementTextarea' => array('name' => __('Textarea field', 'quform')),
            'elementTextareaHover' => array('name' => __('Textarea field (hover)', 'quform')),
            'elementTextareaFocus' => array('name' => __('Textarea field (focus)', 'quform')),
            'elementSelect' => array('name' => __('Select field', 'quform')),
            'elementSelectHover' => array('name' => __('Select field (hover)', 'quform')),
            'elementSelectFocus' => array('name' => __('Select field (focus)', 'quform')),
            'elementIcon' => array('name' => __('Text input icons', 'quform')),
            'elementIconHover' => array('name' => __('Text input icons (hover)', 'quform')),
            'elementSubLabel' => array('name' => __('Sub label', 'quform')),
            'elementDescription' => array('name' => __('Description', 'quform')),
            'options' => array('name' => __('Options outer wrapper', 'quform')),
            'option' => array('name' => __('Option wrapper', 'quform')),
            'optionRadioButton' => array('name' => __('Option radio button', 'quform')),
            'optionCheckbox' => array('name' => __('Option checkbox', 'quform')),
            'optionLabel' => array('name' => __('Option label', 'quform')),
            'optionLabelHover' => array('name' => __('Option label (hover)', 'quform')),
            'optionLabelSelected' => array('name' => __('Option label (when selected)', 'quform')),
            'optionIcon' => array('name' => __('Option icon', 'quform')),
            'optionIconSelected' => array('name' => __('Option icon (when selected)', 'quform')),
            'optionText' => array('name' => __('Option text', 'quform')),
            'optionTextSelected' => array('name' => __('Option text (when selected)', 'quform')),
            'page' => array('name' => __('Page wrapper', 'quform')),
            'pageTitle' => array('name' => __('Page title', 'quform')),
            'pageDescription' => array('name' => __('Page description', 'quform')),
            'pageElements' => array('name' => __('Page elements wrapper', 'quform')),
            'group' => array('name' => __('Group wrapper', 'quform')),
            'groupSpacer' => array('name' => __('Group spacer', 'quform')),
            'groupTitle' => array('name' => __('Group title', 'quform')),
            'groupDescription' => array('name' => __('Group description', 'quform')),
            'groupElements' => array('name' => __('Group elements wrapper', 'quform')),
            'submit' => array('name' => __('Submit button outer wrapper', 'quform')),
            'submitInner' => array('name' => __('Submit button wrapper', 'quform')),
            'submitButton' => array('name' => __('Submit button', 'quform')),
            'submitButtonHover' => array('name' => __('Submit button (hover)', 'quform')),
            'submitButtonActive' => array('name' => __('Submit button (active)', 'quform')),
            'submitButtonText' => array('name' => __('Submit button text', 'quform')),
            'submitButtonTextHover' => array('name' => __('Submit button text (hover)', 'quform')),
            'submitButtonTextActive' => array('name' => __('Submit button text (active)', 'quform')),
            'submitButtonIcon' => array('name' => __('Submit button icon', 'quform')),
            'submitButtonIconHover' => array('name' => __('Submit button icon (hover)', 'quform')),
            'submitButtonIconActive' => array('name' => __('Submit button icon (active)', 'quform')),
            'backInner' => array('name' => __('Back button wrapper', 'quform')),
            'backButton' => array('name' => __('Back button', 'quform')),
            'backButtonHover' => array('name' => __('Back button (hover)', 'quform')),
            'backButtonActive' => array('name' => __('Back button (active)', 'quform')),
            'backButtonText' => array('name' => __('Back button text', 'quform')),
            'backButtonTextHover' => array('name' => __('Back button text (hover)', 'quform')),
            'backButtonTextActive' => array('name' => __('Back button text (active)', 'quform')),
            'backButtonIcon' => array('name' => __('Back button icon', 'quform')),
            'backButtonIconHover' => array('name' => __('Back button icon (hover)', 'quform')),
            'backButtonIconActive' => array('name' => __('Back button icon (active)', 'quform')),
            'nextInner' => array('name' => __('Next button wrapper', 'quform')),
            'nextButton' => array('name' => __('Next button', 'quform')),
            'nextButtonHover' => array('name' => __('Next button (hover)', 'quform')),
            'nextButtonActive' => array('name' => __('Next button (active)', 'quform')),
            'nextButtonText' => array('name' => __('Next button text', 'quform')),
            'nextButtonTextHover' => array('name' => __('Next button text (hover)', 'quform')),
            'nextButtonTextActive' => array('name' => __('Next button text (active)', 'quform')),
            'nextButtonIcon' => array('name' => __('Next button icon', 'quform')),
            'nextButtonIconHover' => array('name' => __('Next button icon (hover)', 'quform')),
            'nextButtonIconActive' => array('name' => __('Next button icon (active)', 'quform')),
            'uploadButton' => array('name' => __('Upload button', 'quform')),
            'uploadButtonHover' => array('name' => __('Upload button (hover)', 'quform')),
            'uploadButtonActive' => array('name' => __('Upload button (active)', 'quform')),
            'uploadButtonText' => array('name' => __('Upload button text', 'quform')),
            'uploadButtonTextHover' => array('name' => __('Upload button text (hover)', 'quform')),
            'uploadButtonTextActive' => array('name' => __('Upload button text (active)', 'quform')),
            'uploadButtonIcon' => array('name' => __('Upload button icon', 'quform')),
            'uploadButtonIconHover' => array('name' => __('Upload button icon (hover)', 'quform')),
            'uploadButtonIconActive' => array('name' => __('Upload button icon (active)', 'quform')),
            'uploadDropzone' => array('name' => __('Upload dropzone', 'quform')),
            'uploadDropzoneHover' => array('name' => __('Upload dropzone (hover)', 'quform')),
            'uploadDropzoneActive' => array('name' => __('Upload dropzone (active)', 'quform')),
            'uploadDropzoneText' => array('name' => __('Upload dropzone text', 'quform')),
            'uploadDropzoneTextHover' => array('name' => __('Upload dropzone text (hover)', 'quform')),
            'uploadDropzoneTextActive' => array('name' => __('Upload dropzone text (active)', 'quform')),
            'uploadDropzoneIcon' => array('name' => __('Upload dropzone icon', 'quform')),
            'uploadDropzoneIconHover' => array('name' => __('Upload dropzone icon (hover)', 'quform')),
            'uploadDropzoneIconActive' => array('name' => __('Upload dropzone icon (active)', 'quform')),
            'datepickerHeader' => array('name' => __('Datepicker header', 'quform')),
            'datepickerHeaderText' => array('name' => __('Datepicker header text', 'quform')),
            'datepickerHeaderTextHover' => array('name' => __('Datepicker header text (hover)', 'quform')),
            'datepickerFooter' => array('name' => __('Datepicker footer', 'quform')),
            'datepickerFooterText' => array('name' => __('Datepicker footer text', 'quform')),
            'datepickerFooterTextHover' => array('name' => __('Datepicker footer text (hover)', 'quform')),
            'datepickerSelection' => array('name' => __('Datepicker selection', 'quform')),
            'datepickerSelectionActive' => array('name' => __('Datepicker selection (chosen)', 'quform')),
            'datepickerSelectionText' => array('name' => __('Datepicker selection text', 'quform')),
            'datepickerSelectionTextHover' => array('name' => __('Datepicker selection text (hover)', 'quform')),
            'datepickerSelectionActiveText' => array('name' => __('Datepicker selection text (active)', 'quform')),
            'datepickerSelectionActiveTextHover' => array('name' => __('Datepicker selection text (chosen) (hover)', 'quform'))
        );

        foreach ($styles as $key => $style) {
            $styles[$key]['config'] = array('type' => $key, 'css' => '');
        }

        return apply_filters('quform_admin_styles', $styles);
    }

    /**
     * Get all available global styles
     *
     * @param  string  $key  Only get the style with this key
     * @return array
     */
    public function getGlobalStyles($key = null)
    {
        $styles = array(
            'formOuter' => array('name' => _x('Form outer wrapper', 'the outermost HTML wrapper around the form', 'quform')),
            'formInner' => array('name' => _x('Form inner wrapper', 'the inner HTML wrapper around the form', 'quform')),
            'formSuccess' => array('name' => __('Success message', 'quform')),
            'formSuccessIcon' => array('name' => __('Success message icon', 'quform')),
            'formSuccessContent' => array('name' => __('Success message content', 'quform')),
            'formTitle' => array('name' => __('Form title', 'quform')),
            'formDescription' => array('name' => __('Form description', 'quform')),
            'formElements' => array('name' => _x('Form elements wrapper', 'the HTML wrapper around the form elements', 'quform')),
            'formError' => array('name' => __('Form error message', 'quform')),
            'formErrorInner' => array('name' => __('Form error message inner wrapper', 'quform')),
            'formErrorTitle' => array('name' => __('Form error message title', 'quform')),
            'formErrorContent' => array('name' => __('Form error message content', 'quform')),
            'element' => array('name' => _x('Element outer wrapper', 'outermost wrapping HTML element around an element', 'quform')),
            'elementSpacer' => array('name' => __('Element spacer', 'quform')),
            'elementLabel' => array('name' => __('Element label', 'quform')),
            'elementLabelText' => array('name' => __('Element label text', 'quform')),
            'elementRequiredText' => array('name' => __('Element required text', 'quform')),
            'elementInner' => array('name' => _x('Element inner wrapper', 'the inner HTML wrapper around the element', 'quform')),
            'elementInput' => array('name' => _x('Element input wrapper', 'the HTML wrapper around just the input', 'quform')),
            'elementText' => array('name' => __('Text input fields', 'quform')),
            'elementTextHover' => array('name' => __('Text input fields (hover)', 'quform')),
            'elementTextFocus' => array('name' => __('Text input fields (focus)', 'quform')),
            'elementTextarea' => array('name' => __('Textarea fields', 'quform')),
            'elementTextareaHover' => array('name' => __('Textarea fields (hover)', 'quform')),
            'elementTextareaFocus' => array('name' => __('Textarea fields (focus)', 'quform')),
            'elementSelect' => array('name' => __('Select fields', 'quform')),
            'elementSelectHover' => array('name' => __('Select fields (hover)', 'quform')),
            'elementSelectFocus' => array('name' => __('Select fields (focus)', 'quform')),
            'elementIcon' => array('name' => __('Text input icons', 'quform')),
            'elementIconHover' => array('name' => __('Text input icons (hover)', 'quform')),
            'elementSubLabel' => array('name' => __('Element sub label', 'quform')),
            'elementDescription' => array('name' => __('Element description', 'quform')),
            'options' => array('name' => _x('Options outer wrapper', 'the wrapper around the list of options for checkboxes and radio buttons', 'quform')),
            'option' => array('name' => _x('Option wrappers', 'the wrapper around each option for checkboxes and radio buttons', 'quform')),
            'optionRadioButton' => array('name' => __('Option radio button', 'quform')),
            'optionCheckbox' => array('name' => __('Option checkbox', 'quform')),
            'optionLabel' => array('name' => __('Option labels', 'quform')),
            'optionLabelHover' => array('name' => __('Option labels (hover)', 'quform')),
            'optionLabelSelected' => array('name' => __('Option labels (when selected)', 'quform')),
            'optionIcon' => array('name' => __('Option icons', 'quform')),
            'optionIconSelected' => array('name' => __('Option icons (when selected)', 'quform')),
            'optionText' => array('name' => __('Option text', 'quform')),
            'optionTextSelected' => array('name' => __('Option text (when selected)', 'quform')),
            'elementError' => array('name' => __('Element error', 'quform')),
            'elementErrorInner' => array('name' => __('Element error inner wrapper', 'quform')),
            'elementErrorText' => array('name' => __('Element error text', 'quform')),
            'page' => array('name' => __('Page wrapper', 'quform')),
            'pageTitle' => array('name' => __('Page title', 'quform')),
            'pageDescription' => array('name' => __('Page description', 'quform')),
            'pageElements' => array('name' => __('Page elements wrapper', 'quform')),
            'group' => array('name' => __('Group wrapper', 'quform')),
            'groupTitle' => array('name' => __('Group title', 'quform')),
            'groupDescription' => array('name' => __('Group description', 'quform')),
            'groupElements' => array('name' => __('Group elements wrapper', 'quform')),
            'pageProgress' => array('name' => __('Page progress wrapper', 'quform')),
            'pageProgressBar' => array('name' => __('Page progress bar', 'quform')),
            'pageProgressBarText' => array('name' => __('Page progress bar text', 'quform')),
            'pageProgressTabs' => array('name' => __('Page progress tabs', 'quform')),
            'pageProgressTab' => array('name' => __('Page progress tab', 'quform')),
            'pageProgressTabActive' => array('name' => __('Page progress tab (active)', 'quform')),
            'submit' => array('name' => __('Submit button outer wrapper', 'quform')),
            'submitInner' => array('name' => __('Submit button wrapper', 'quform')),
            'submitButton' => array('name' => __('Submit button', 'quform')),
            'submitButtonHover' => array('name' => __('Submit button (hover)', 'quform')),
            'submitButtonActive' => array('name' => __('Submit button (active)', 'quform')),
            'submitButtonText' => array('name' => __('Submit button text', 'quform')),
            'submitButtonTextHover' => array('name' => __('Submit button text (hover)', 'quform')),
            'submitButtonTextActive' => array('name' => __('Submit button text (active)', 'quform')),
            'submitButtonIcon' => array('name' => __('Submit button icon', 'quform')),
            'submitButtonIconHover' => array('name' => __('Submit button icon (hover)', 'quform')),
            'submitButtonIconActive' => array('name' => __('Submit button icon (active)', 'quform')),
            'backInner' => array('name' => __('Back button wrapper', 'quform')),
            'backButton' => array('name' => __('Back button', 'quform')),
            'backButtonHover' => array('name' => __('Back button (hover)', 'quform')),
            'backButtonActive' => array('name' => __('Back button (active)', 'quform')),
            'backButtonText' => array('name' => __('Back button text', 'quform')),
            'backButtonTextHover' => array('name' => __('Back button text (hover)', 'quform')),
            'backButtonTextActive' => array('name' => __('Back button text (active)', 'quform')),
            'backButtonIcon' => array('name' => __('Back button icon', 'quform')),
            'backButtonIconHover' => array('name' => __('Back button icon (hover)', 'quform')),
            'backButtonIconActive' => array('name' => __('Back button icon (active)', 'quform')),
            'nextInner' => array('name' => __('Next button wrapper', 'quform')),
            'nextButton' => array('name' => __('Next button', 'quform')),
            'nextButtonHover' => array('name' => __('Next button (hover)', 'quform')),
            'nextButtonActive' => array('name' => __('Next button (active)', 'quform')),
            'nextButtonText' => array('name' => __('Next button text', 'quform')),
            'nextButtonTextHover' => array('name' => __('Next button text (hover)', 'quform')),
            'nextButtonTextActive' => array('name' => __('Next button text (active)', 'quform')),
            'nextButtonIcon' => array('name' => __('Next button icon', 'quform')),
            'nextButtonIconHover' => array('name' => __('Next button icon (hover)', 'quform')),
            'nextButtonIconActive' => array('name' => __('Next button icon (active)', 'quform')),
            'uploadButton' => array('name' => __('Upload button', 'quform')),
            'uploadButtonHover' => array('name' => __('Upload button (hover)', 'quform')),
            'uploadButtonActive' => array('name' => __('Upload button (active)', 'quform')),
            'uploadButtonText' => array('name' => __('Upload button text', 'quform')),
            'uploadButtonTextHover' => array('name' => __('Upload button text (hover)', 'quform')),
            'uploadButtonTextActive' => array('name' => __('Upload button text (active)', 'quform')),
            'uploadButtonIcon' => array('name' => __('Upload button icon', 'quform')),
            'uploadButtonIconHover' => array('name' => __('Upload button icon (hover)', 'quform')),
            'uploadButtonIconActive' => array('name' => __('Upload button icon (active)', 'quform')),
            'uploadDropzone' => array('name' => __('Upload dropzone', 'quform')),
            'uploadDropzoneHover' => array('name' => __('Upload dropzone (hover)', 'quform')),
            'uploadDropzoneActive' => array('name' => __('Upload dropzone (active)', 'quform')),
            'uploadDropzoneText' => array('name' => __('Upload dropzone text', 'quform')),
            'uploadDropzoneTextHover' => array('name' => __('Upload dropzone text (hover)', 'quform')),
            'uploadDropzoneTextActive' => array('name' => __('Upload dropzone text (active)', 'quform')),
            'uploadDropzoneIcon' => array('name' => __('Upload dropzone icon', 'quform')),
            'uploadDropzoneIconHover' => array('name' => __('Upload dropzone icon (hover)', 'quform')),
            'uploadDropzoneIconActive' => array('name' => __('Upload dropzone icon (active)', 'quform')),
            'datepickerHeader' => array('name' => __('Datepicker header', 'quform')),
            'datepickerHeaderText' => array('name' => __('Datepicker header text', 'quform')),
            'datepickerHeaderTextHover' => array('name' => __('Datepicker header text (hover)', 'quform')),
            'datepickerFooter' => array('name' => __('Datepicker footer', 'quform')),
            'datepickerFooterText' => array('name' => __('Datepicker footer text', 'quform')),
            'datepickerFooterTextHover' => array('name' => __('Datepicker footer text (hover)', 'quform')),
            'datepickerSelection' => array('name' => __('Datepicker selection', 'quform')),
            'datepickerSelectionActive' => array('name' => __('Datepicker selection (chosen)', 'quform')),
            'datepickerSelectionText' => array('name' => __('Datepicker selection text', 'quform')),
            'datepickerSelectionTextHover' => array('name' => __('Datepicker selection text (hover)', 'quform')),
            'datepickerSelectionActiveText' => array('name' => __('Datepicker selection text (active)', 'quform')),
            'datepickerSelectionActiveTextHover' => array('name' => __('Datepicker selection text (chosen) (hover)', 'quform'))
        );

        foreach ($styles as $k => $style) {
            $styles[$k]['config'] = array('type' => $k, 'css' => '');
        }

        $styles = apply_filters('quform_admin_global_styles', $styles);

        if (is_string($key)) {
            if (isset($styles[$key])) {
                return $styles[$key];
            } else {
                return null;
            }
        }

        return $styles;
    }

    /**
     * Get the HTML for a style
     *
     * @return string
     */
    protected function getStyleHtml()
    {
        ob_start(); ?>
        <div class="qfb-style qfb-box">
            <div class="qfb-style-inner qfb-cf">
                <div class="qfb-style-actions">
                    <span class="qfb-style-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                    <span class="qfb-style-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
                </div>
                <div class="qfb-style-title"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a global style
     *
     * @param   array   $style
     * @return  string
     */
    public function getGlobalStyleHtml(array $style = array())
    {
        $styles = $this->getGlobalStyles();
        $name = ! empty($style) && isset($styles[$style['type']]) ? $styles[$style['type']]['name'] : '';

        ob_start(); ?>
        <div class="qfb-global-style qfb-box"<?php echo !empty($style) ? sprintf(' data-style="%s"', Quform::escape(wp_json_encode($style))) : ''; ?>>
            <div class="qfb-global-style-inner qfb-cf">
                <div class="qfb-global-style-actions">
                    <span class="qfb-global-style-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                    <span class="qfb-global-style-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
                </div>
                <div class="qfb-global-style-title"><?php echo esc_html($name); ?></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Gets the list of styles that are visible for each element
     *
     * @return array
     */
    protected function getVisibleStyles()
    {
        $visible = array(
            'text' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'email' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'password' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'captcha' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementText', 'elementTextHover', 'elementTextFocus', 'elementSubLabel', 'elementDescription'),
            'textarea' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementTextarea', 'elementTextareaHover', 'elementTextareaFocus', 'elementSubLabel', 'elementDescription'),
            'select' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementSelect', 'elementSelectHover', 'elementSelectFocus', 'elementSubLabel', 'elementDescription'),
            'file' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'uploadButton', 'uploadButtonHover', 'uploadButtonActive', 'uploadButtonText', 'uploadButtonTextHover', 'uploadButtonTextActive', 'uploadButtonIcon', 'uploadButtonIconHover', 'uploadButtonIconActive', 'uploadDropzone', 'uploadDropzoneHover', 'uploadDropzoneActive', 'uploadDropzoneText', 'uploadDropzoneTextHover', 'uploadDropzoneTextActive', 'uploadDropzoneIcon', 'uploadDropzoneIconHover', 'uploadDropzoneIconActive', 'elementSubLabel', 'elementDescription'),
            'recaptcha' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementSubLabel', 'elementDescription'),
            'date' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementText', 'elementIcon', 'elementIconHover', 'elementSubLabel', 'elementDescription', 'datepickerSelection', 'datepickerSelectionActive', 'datepickerSelectionText', 'datepickerSelectionTextHover', 'datepickerSelectionActiveText', 'datepickerSelectionActiveTextHover', 'datepickerFooter', 'datepickerFooterText', 'datepickerFooterTextHover', 'datepickerHeader', 'datepickerHeaderText', 'datepickerHeaderTextHover',),
            'time' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementText', 'elementIcon', 'elementIconHover', 'elementSubLabel', 'elementDescription'),
            'radio' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'options', 'option', 'optionRadioButton', 'optionLabel', 'optionLabelHover', 'optionLabelSelected', 'optionIcon', 'optionIconSelected', 'optionText', 'optionTextSelected', 'elementSubLabel', 'elementDescription'),
            'checkbox' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'options', 'option', 'optionCheckbox', 'optionLabel', 'optionLabelHover', 'optionLabelSelected', 'optionIcon', 'optionIconSelected', 'optionText', 'optionTextSelected', 'elementSubLabel', 'elementDescription'),
            'multiselect' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementSubLabel', 'elementDescription'),
            'name' => array('element', 'elementSpacer', 'elementLabel', 'elementLabelText', 'elementRequiredText', 'elementInner', 'elementInput', 'elementSubLabel', 'elementDescription'),
            'html' => array('element', 'elementSpacer'),
            'page' => array('page', 'pageTitle', 'pageDescription', 'pageElements'),
            'group' => array('group', 'groupSpacer', 'groupTitle', 'groupDescription', 'groupElements'),
            'submit' => array('submit', 'submitInner', 'submitButton', 'submitButtonHover', 'submitButtonActive', 'submitButtonText', 'submitButtonTextHover', 'submitButtonTextActive', 'submitButtonIcon', 'submitButtonIconHover', 'submitButtonIconActive', 'backInner', 'backButton', 'backButtonHover', 'backButtonActive', 'backButtonText', 'backButtonTextHover', 'backButtonTextActive', 'backButtonIcon', 'backButtonIconHover', 'backButtonIconActive', 'nextInner', 'nextButton', 'nextButtonHover', 'nextButtonActive', 'nextButtonText', 'nextButtonTextHover', 'nextButtonTextActive', 'nextButtonIcon', 'nextButtonIconHover', 'nextButtonIconActive')
        );

        $visible = apply_filters('quform_visible_styles', $visible);

        return $visible;
    }

    /**
     * Get the list of filters
     *
     * @return array
     */
    public function getFilters()
    {
        $filters = array(
            'alpha' => array(
                'name' => _x('Alpha', 'the alphabet filter', 'quform'),
                'tooltip' => __('Removes any non-alphabet characters', 'quform'),
                'config' => Quform_Filter_Alpha::getDefaultConfig()
            ),
            'alphaNumeric' => array(
                'name' => _x('Alphanumeric', 'the alphanumeric filter', 'quform'),
                'tooltip' => __('Removes any non-alphabet characters and non-digits', 'quform'),
                'config' => Quform_Filter_AlphaNumeric::getDefaultConfig()
            ),
            'digits' => array(
                'name' => _x('Digits', 'the digits filter', 'quform'),
                'tooltip' => __('Removes any non-digits', 'quform'),
                'config' => Quform_Filter_Digits::getDefaultConfig()
            ),
            'regex' => array(
                'name' => _x('Regex', 'the regex filter', 'quform'),
                'tooltip' => __('Removes characters matching the given regular expression', 'quform'),
                'config' => Quform_Filter_Regex::getDefaultConfig()
            ),
            'stripTags' => array(
                'name' => _x('Strip Tags', 'the strip tags filter', 'quform'),
                'tooltip' => __('Removes any HTML tags', 'quform'),
                'config' => Quform_Filter_StripTags::getDefaultConfig()
            ),
            'trim' => array(
                'name' => _x('Trim', 'the trim filter', 'quform'),
                'tooltip' => __('Removes white space from the start and end', 'quform'),
                'config' => Quform_Filter_Trim::getDefaultConfig()
            )
        );

        $filters = apply_filters('quform_admin_filters', $filters);

        return $filters;
    }

    /**
     * Get the HTML for a filter
     *
     * @return string
     */
    protected function getFilterHtml()
    {
        ob_start();
        ?>
        <div class="qfb-filter qfb-box">
            <div class="qfb-filter-inner qfb-cf">
                <div class="qfb-filter-actions">
                    <span class="qfb-filter-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                    <span class="qfb-filter-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
                </div>
                <div class="qfb-filter-title"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the list of visible filters for the elements
     *
     * @return array
     */
    protected function getVisibleFilters()
    {
        $visible = array(
            'text' => array('alpha', 'alphaNumeric', 'digits', 'stripTags', 'trim', 'regex'),
            'email' => array('trim'),
            'textarea' => array('alpha', 'alphaNumeric', 'digits', 'stripTags', 'trim', 'regex')
        );

        $visible = apply_filters('quform_visible_filters', $visible);

        return $visible;
    }

    /**
     * Get the validator configurations
     *
     * @return array
     */
    public function getValidators()
    {
        $validators = array(
            'alpha' => array(
                'name' => _x('Alpha', 'the alphabet validator', 'quform'),
                'tooltip' => __('Checks that the value contains only alphabet characters', 'quform'),
                'config' => Quform_Validator_Alpha::getDefaultConfig()
            ),
            'alphaNumeric' => array(
                'name' => _x('Alphanumeric', 'the alphanumeric validator', 'quform'),
                'tooltip' => __('Checks that the value contains only alphabet or digits', 'quform'),
                'config' => Quform_Validator_AlphaNumeric::getDefaultConfig()
            ),
            'digits' => array(
                'name' => _x('Digits', 'the digits validator', 'quform'),
                'tooltip' => __('Checks that the value contains only digits', 'quform'),
                'config' => Quform_Validator_Digits::getDefaultConfig()
            ),
            'email' => array(
                'name' => _x('Email', 'the strip tags validator', 'quform'),
                'tooltip' => __('Checks that the value is a valid email address', 'quform'),
                'config' => Quform_Validator_Email::getDefaultConfig()
            ),
            'greaterThan' => array(
                'name' => _x('Greater Than', 'the greater than validator', 'quform'),
                'tooltip' => __('Checks that the value is numerically greater than the given minimum', 'quform'),
                'config' => Quform_Validator_GreaterThan::getDefaultConfig()
            ),
            'identical' => array(
                'name' => _x('Identical', 'the identical validator', 'quform'),
                'tooltip' => __('Checks that the value is identical to the given token', 'quform'),
                'config' => Quform_Validator_Identical::getDefaultConfig()
            ),
            'inArray' => array(
                'name' => _x('In Array', 'the in array validator', 'quform'),
                'tooltip' => __('Checks that the value is in a list of allowed values', 'quform'),
                'config' => Quform_Validator_InArray::getDefaultConfig()
            ),
            'length' => array(
                'name' => _x('Length', 'the length validator', 'quform'),
                'tooltip' => __('Checks that the length of the value is between the given maximum and minimum', 'quform'),
                'config' => Quform_Validator_Length::getDefaultConfig()
            ),
            'lessThan' => array(
                'name' => _x('Less Than', 'the less than validator', 'quform'),
                'tooltip' => __('Checks that the value is numerically less than the given maximum', 'quform'),
                'config' => Quform_Validator_LessThan::getDefaultConfig()
            ),
            'duplicate' => array(
                'name' => _x('Prevent Duplicates', 'the duplicate validator', 'quform'),
                'tooltip' => __('Checks that the same value has not already been submitted', 'quform'),
                'config' => Quform_Validator_Duplicate::getDefaultConfig()
            ),
            'regex' => array(
                'name' => _x('Regex', 'the regex validator', 'quform'),
                'tooltip' => __('Checks that the value matches the given regular expression', 'quform'),
                'config' => Quform_Validator_Regex::getDefaultConfig()
            )
        );

        $validators = apply_filters('quform_admin_validators', $validators);

        return $validators;
    }

    /**
     * Get the HTML for a validator
     *
     * @return string
     */
    protected function getValidatorHtml()
    {
        ob_start();
        ?>
        <div class="qfb-validator qfb-box">
            <div class="qfb-validator-inner qfb-cf">
                <div class="qfb-validator-actions">
                    <span class="qfb-validator-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                    <span class="qfb-validator-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
                </div>
                <div class="qfb-validator-title"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the list of visible validators for the elements
     *
     * @return array
     */
    protected function getVisibleValidators()
    {
        $visible = array(
            'text' => array('alpha', 'alphaNumeric', 'digits', 'duplicate', 'email', 'greaterThan', 'identical', 'inArray', 'length', 'lessThan', 'regex'),
            'textarea' => array('alpha', 'alphaNumeric', 'digits', 'duplicate', 'email', 'greaterThan', 'identical', 'inArray', 'length', 'lessThan', 'regex'),
            'email' => array('duplicate', 'inArray', 'regex'),
            'password' => array('alpha', 'alphaNumeric', 'digits', 'identical', 'inArray', 'length', 'regex'),
            'select' => array('duplicate', 'greaterThan', 'identical', 'inArray', 'lessThan', 'regex'),
            'checkbox' => array('duplicate'),
            'radio' => array('duplicate', 'greaterThan', 'identical', 'inArray', 'lessThan', 'regex'),
            'multiselect' => array('duplicate'),
            'date' => array('duplicate', 'inArray'),
            'time' => array('duplicate', 'inArray'),
            'name' => array('duplicate')
        );

        $visible = apply_filters('quform_visible_validators', $visible);

        return $visible;
    }

    /**
     * Get the HTML for a notification
     *
     * @param   array   $notification
     * @return  string
     */
    public function getNotificationHtml($notification = null)
    {
        if ( ! is_array($notification)) {
            $notification = Quform_Notification::getDefaultConfig();
            $notification['id'] = 0;
        }

        ob_start();
        ?>
        <div class="qfb-notification qfb-box qfb-cf" data-id="<?php echo esc_attr($notification['id']); ?>">
            <div class="qfb-notification-name"><?php echo esc_html($notification['name']); ?></div>
            <div class="qfb-notification-actions">
                <span class="qfb-notification-action-toggle" title="<?php esc_attr_e('Toggle enabled/disabled', 'quform'); ?>"><input type="checkbox" id="qfb-notification-toggle-<?php echo esc_attr($notification['id']); ?>" class="qfb-notification-toggle qfb-mini-toggle" <?php checked($notification['enabled']); ?>><label for="qfb-notification-toggle-<?php echo esc_attr($notification['id']); ?>"></label></span>
                <span class="qfb-notification-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                <span class="qfb-notification-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>
                <span class="qfb-notification-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
            </div>
            <div class="qfb-notification-subject"><span class="qfb-notification-subject-text"><?php echo esc_html($notification['subject']); ?></span></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for an email recipient
     *
     * @return  string
     */
    public function getRecipientHtml()
    {
        ob_start();
        ?>
        <div class="qfb-recipient">
            <div class="qfb-recipient-inner qfb-cf">
                <div class="qfb-recipient-left">
                    <select class="qfb-recipient-type">
                        <option value="to"><?php echo esc_html_x('To', 'email', 'quform'); ?></option>
                        <option value="cc"><?php esc_html_e('Cc', 'quform'); ?></option>
                        <option value="bcc"><?php esc_html_e('Bcc', 'quform'); ?></option>
                        <option value="reply"><?php esc_html_e('Reply-To', 'quform'); ?></option>
                    </select>
                </div>
                <div class="qfb-recipient-right">
                    <div class="qfb-recipient-right-inner">
                        <div class="qfb-settings-row qfb-settings-row-2">
                            <div class="qfb-settings-column">
                                <div class="qfb-input-variable">
                                    <input class="qfb-recipient-address" type="text" placeholder="<?php esc_attr_e('Email address (required)', 'quform'); ?>">
                                    <?php echo $this->getInsertVariableHtml(); ?>
                                </div>
                            </div>
                            <div class="qfb-settings-column">
                                <div class="qfb-input-variable">
                                    <input class="qfb-recipient-name" type="text" placeholder="<?php esc_attr_e('Name (optional)', 'quform'); ?>">
                                    <?php echo $this->getInsertVariableHtml(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="qfb-small-remove-button qfb-icon qfb-icon-trash" title="<?php esc_attr_e('Remove', 'quform'); ?>"></span>
                <span class="qfb-small-add-button qfb-mdi qfb-mdi-add_circle" title="<?php esc_attr_e('Add', 'quform'); ?>"></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for the insert variable button
     *
     * @param   string  $targetId    The unique ID of the target field
     * @param   bool    $preProcess  Whether it is the pre process variables
     * @return  string
     */
    public function getInsertVariableHtml($targetId = '', $preProcess = false)
    {
        return sprintf(
            '<span class="qfb-insert-variable%s" title="%s"%s><i class="qfb-icon qfb-icon-code"></i></span>',
            $preProcess ? ' qfb-insert-variable-pre-process' : '',
            esc_attr__('Insert variable...', 'quform'),
            $targetId ? ' data-target-id="' . esc_attr($targetId) . '"' : ''
        );
    }

    /**
     * Get the HTML for a confirmation
     *
     * @param   array   $confirmation
     * @return  string
     */
    public function getConfirmationHtml($confirmation = null)
    {
        if ( ! is_array($confirmation)) {
            $confirmation = Quform_Confirmation::getDefaultConfig();
            $confirmation['id'] = 0;
        }

        ob_start();
        ?>
        <div class="qfb-confirmation qfb-box qfb-cf" data-id="<?php echo esc_attr($confirmation['id']); ?>">
            <div class="qfb-confirmation-name"><?php echo esc_html($confirmation['name']); ?></div>
            <div class="qfb-confirmation-actions">
                <?php if ($confirmation['id'] != 1) : ?>
                    <span class="qfb-confirmation-action-toggle" title="<?php esc_attr_e('Toggle enabled/disabled', 'quform'); ?>"><input type="checkbox" id="qfb-confirmation-toggle-<?php echo esc_attr($confirmation['id']); ?>" class="qfb-confirmation-toggle qfb-mini-toggle" <?php checked($confirmation['enabled']); ?>><label for="qfb-confirmation-toggle-<?php echo esc_attr($confirmation['id']); ?>"></label></span>
                    <span class="qfb-confirmation-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                <?php endif; ?>
                <span class="qfb-confirmation-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>
                <span class="qfb-confirmation-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
            </div>
            <div class="qfb-confirmation-description"><?php echo $this->getConfirmationDescription($confirmation); ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the confirmation description
     *
     * Changes should be mirrored in builder.confirmations.js:getConfirmationDescription
     *
     * @param   array   $confirmation
     * @return  string
     */
    protected function getConfirmationDescription(array $confirmation)
    {
        $type = $confirmation['type'];

        $output = sprintf(
            '<div class="qfb-settings-row%s">',
            $type == 'message-redirect-page' || $type == 'message-redirect-url' ? ' qfb-settings-row-2' : ''
        );

        $output .= '<div class="qfb-settings-column">';

        switch ($type) {
            case 'message':
            case 'message-redirect-page':
            case 'message-redirect-url':
                $output .= sprintf('<i class="qfb-mdi qfb-mdi-message" title="%s"></i>', esc_attr__('Display a message', 'quform'));
                $output .= sprintf(
                    '<span class="qfb-confirmation-description-message">%s</span>',
                    Quform::escape(mb_substr(strip_tags($confirmation['message']), 0, 64))
                );
                break;
            case 'redirect-page';
                $output .= sprintf('<i class="qfb-mdi qfb-mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                $output .= sprintf(
                    '<span class="qfb-confirmation-description-redirect-page">%s</span>',
                    Quform::escape(Quform::getPostTitleById((int) $confirmation['redirectPage']))
                );
                break;
            case 'redirect-url';
                $output .= sprintf('<i class="qfb-mdi qfb-mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                $output .= sprintf(
                    '<span class="qfb-confirmation-description-redirect-url">%s</span>',
                    Quform::escape($confirmation['redirectUrl'])
                );
                break;
            case 'reload';
                $output .= sprintf('<i class="qfb-mdi qfb-mdi-refresh" title="%s"></i>', esc_attr__('Reload the page', 'quform'));
                break;
        }

        $output .= '</div>';

        if ($type == 'message-redirect-page' || $type == 'message-redirect-url') {
            $output .= '<div class="qfb-settings-column">';

            switch ($type) {
                case 'message-redirect-page';
                    $output .= sprintf('<i class="qfb-mdi qfb-mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                    $output .= sprintf(
                        '<span class="qfb-confirmation-description-redirect-page">%s</span>',
                        Quform::escape(Quform::getPostTitleById((int) $confirmation['redirectPage']))
                    );
                    break;
                case 'message-redirect-url';
                    $output .= sprintf('<i class="qfb-mdi qfb-mdi-arrow_forward" title="%s"></i>', esc_attr__('Redirect to', 'quform'));
                    $output .= sprintf(
                        '<span class="qfb-confirmation-description-redirect-url">%s</span>',
                        Quform::escape($confirmation['redirectUrl'])
                    );
                    break;
            }

            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for a select menu of available title tag options
     *
     * @param   string  $id        The ID of the field
     * @param   string  $selected  The selected value
     * @return  string
     */
    public function getTitleTagSelectHtml($id, $selected = '')
    {
        $tags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span');
        $output = sprintf('<select id="%s">', $id);

        foreach ($tags as $tag) {
            $output .= sprintf('<option value="%1$s"%2$s>%1$s</option>', Quform::escape($tag), $selected == $tag ? ' selected="selected"' : '');
        }

        $output .= '</select>';

        return $output;
    }

    /**
     * The supported autocomplete attributes from https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofill
     *
     * @return array
     */
    public function getAutocompleteValues()
    {
        $values = array(
            'off', 'on', 'name', 'honorific-prefix', 'given-name', 'additional-name', 'family-name', 'honorific-suffix',
            'nickname', 'username', 'new-password', 'current-password', 'one-time-code', 'organization-title',
            'organization', 'street-address', 'address-line1', 'address-line2', 'address-line3', 'address-level4',
            'address-level3', 'address-level2', 'address-level1', 'country', 'country-name', 'postal-code', 'cc-name',
            'cc-given-name', 'cc-additional-name', 'cc-family-name', 'cc-number', 'cc-exp', 'cc-exp-month',
            'cc-exp-year', 'cc-csc', 'cc-type', 'transaction-currency', 'transaction-amount', 'language', 'bday',
            'bday-day', 'bday-month', 'bday-year', 'sex', 'url', 'photo', 'home', 'work', 'mobile', 'fax', 'pager',
            'tel', 'tel-country-code', 'tel-national', 'tel-area-code', 'tel-local', 'tel-local-prefix',
            'tel-local-suffix', 'tel-extension', 'email', 'impp', 'webauthn', 'shipping', 'billing',
        );

        $values = apply_filters('quform_autocomplete_values', $values);

        sort($values);

        return $values;
    }

    /**
     * Get the HTML for the custom database column settings
     *
     * @param   array|null  $column  The existing column config
     * @return  string
     */
    public function getDbColumnHtml($column = null)
    {
        if ( ! is_array($column)) {
            $column = array(
                'name' => '',
                'value' => ''
            );
        }

        $variableId = uniqid('q');
        ob_start();
        ?>
        <div class="qfb-form-db-column qfb-cf">
            <input type="text" class="qfb-form-db-column-name" placeholder="<?php esc_attr_e('Column', 'quform'); ?>" value="<?php echo esc_attr($column['name']); ?>">
            <input id="<?php echo esc_attr($variableId); ?>" type="text" class="qfb-form-db-column-value" placeholder="<?php esc_attr_e('Value', 'quform'); ?>" value="<?php echo esc_attr($column['value']); ?>">
            <?php echo $this->getInsertVariableHtml($variableId); ?>
            <span class="qfb-small-remove-button qfb-icon qfb-icon-trash" title="<?php esc_attr_e('Remove', 'quform'); ?>"></span>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the default form configuration array with populated default settings
     *
     * @return array
     */
    public function getDefaultForm()
    {
        $page = $this->getDefaultElementConfig('page');
        $page['id'] = 1;
        $page['parentId'] = 0;
        $page['position'] = 0;

        $submit = $this->getDefaultElementConfig('submit');
        $submit['id'] = 2;
        $submit['parentId'] = 1;
        $submit['position'] = 0;

        $page['elements'] = array($submit);

        $notification = Quform_Notification::getDefaultConfig();
        $notification['id'] = 1;
        $notification['name'] = __('Admin notification', 'quform');

        $confirmation = Quform_Confirmation::getDefaultConfig();
        $confirmation['id'] = 1;
        $confirmation['name'] = __('Default confirmation', 'quform');
        $confirmation['message'] = __('Your message has been sent, thanks.', 'quform');
        $confirmation['messageIcon'] = 'qicon-check';

        $form = Quform_Form::getDefaultConfig();
        $form['nextElementId'] = 3;
        $form['elements'] = array($page);
        $form['nextNotificationId'] = 2;
        $form['notifications'] = array($notification);
        $form['nextConfirmationId'] = 2;
        $form['confirmations'] = array($confirmation);

        $form = apply_filters('quform_default_form', $form);

        return $form;
    }

    /**
     * @param   array   $form
     * @param   string  $key
     * @return  mixed
     */
    public function getFormConfigValue($form, $key)
    {
        $value = Quform::get($form, $key);

        if ($value === null) {
            $value = Quform::get(Quform_Form::getDefaultConfig(), $key);
        }

        return $value;
    }

    /**
     * Get the HTML for all pages and elements for the form builder
     *
     * @param   array   $elements  The array of element configs
     * @return  string
     */
    public function renderFormElements($elements)
    {
        $output = '';

        foreach ($elements as $element) {
            $output .= $this->getElementHtml($element);
        }

        return $output;
    }

    /**
     * Get the HTML for an element in the form builder
     *
     * @param   array   $element  The element config
     * @return  string
     */
    protected function getElementHtml(array $element)
    {
        switch ($element['type']) {
            case 'page':
                $output = $this->getPageHtml($element);
                break;
            case 'group':
                $output = $this->getGroupHtml($element);
                break;
            case 'row':
                $output = $this->getRowHtml($element);
                break;
            case 'column':
                $output = $this->getColumnHtml($element);
                break;
            default:
                $output = $this->getFieldHtml($element);
                break;
        }

        return $output;
    }

    /**
     * Get the HTML for a page for the form builder
     *
     * @param   array   $element  The page config
     * @return  string
     */
    protected function getPageHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-page" data-id="<?php echo esc_attr($element['id']); ?>" data-type="page">
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf">
                <?php
                    foreach ($element['elements'] as $child) {
                        echo $this->getElementHtml($child);
                    }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a group for the form builder
     *
     * @param   array  $element  The group config
     * @return  string           The HTML
     */
    protected function getGroupHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-group" data-id="<?php echo esc_attr($element['id']); ?>" data-type="group">
            <div class="qfb-element-inner qfb-cf">
                <span class="qfb-element-type-icon"><i class="qfb-icon qfb-icon-object-group"></i></span>
                <label class="qfb-preview-label<?php echo ( ! Quform::isNonEmptyString($element['label']) ? ' qfb-hidden' : ''); ?>"><span id="qfb-plc-<?php echo esc_attr($element['id']); ?>" class="qfb-preview-label-content"><?php echo Quform::escape($element['label']); ?></span></label>
                <div class="qfb-element-actions">
                    <span class="qfb-element-action-collapse" title="<?php esc_attr_e('Collapse', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-remove_circle_outline"></i></span>
                    <span class="qfb-element-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                    <span class="qfb-element-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>
                    <span class="qfb-element-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
                </div>
            </div>
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf">
                <?php
                    foreach ($element['elements'] as $child) {
                        echo $this->getElementHtml($child);
                    }
                ?>
            </div>
            <div class="qfb-element-group-empty-indicator"><span class="qfb-element-group-empty-indicator-arrow"><i class="qfb-icon qfb-icon-arrow-down"></i></span><span class="qfb-element-group-empty-add-row" title="<?php esc_attr_e('Add column layout', 'quform'); ?>"><i class="qfb-icon qfb-icon-columns"></i><i class="qfb-mdi qfb-mdi-add_circle"></i></span></div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a row for the form builder
     *
     * @param   array   $element  The row config
     * @return  string
     */
    protected function getRowHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-row" data-id="<?php echo esc_attr($element['id']); ?>" data-type="row">
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf qfb-<?php echo esc_attr(count($element['elements'])); ?>-columns">
            <?php
                foreach ($element['elements'] as $child) {
                    echo $this->getElementHtml($child);
                }
            ?>
            </div>
            <div class="qfb-row-actions">
                <span class="qfb-row-action-add-column" title="<?php esc_attr_e('Add column', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-add_circle"></i></span>
                <span class="qfb-row-action-remove-column" title="<?php esc_attr_e('Remove column', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-remove_circle"></i></span>
                <span class="qfb-row-action-remove" title="<?php esc_attr_e('Remove row', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                <span class="qfb-row-action-duplicate" title="<?php esc_attr_e('Duplicate row', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>
                <span class="qfb-row-action-settings" title="<?php esc_attr_e('Row settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
                <span class="qfb-row-action-move" title="<?php esc_attr_e('Move row', 'quform'); ?>"><i class="qfb-icon qfb-icon-arrows"></i></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a column for the form builder
     *
     * @param   array   $element  The column config
     * @return  string
     */
    protected function getColumnHtml(array $element)
    {
        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-column" data-id="<?php echo esc_attr($element['id']); ?>" data-type="column">
            <div id="qfb-child-elements-<?php echo esc_attr($element['id']); ?>" class="qfb-child-elements qfb-cf">
                <?php
                foreach ($element['elements'] as $child) {
                    echo $this->getElementHtml($child);
                }
                ?>
            </div>
            <div class="qfb-column-actions">
                <span class="qfb-column-action-remove" title="<?php esc_attr_e('Remove column', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                <span class="qfb-column-action-duplicate" title="<?php esc_attr_e('Duplicate column', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>
                <span class="qfb-column-action-move" title="<?php esc_attr_e('Move column', 'quform'); ?>"><i class="qfb-icon qfb-icon-arrows"></i></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a field for the form builder
     *
     * @param   array  $element  The element config
     * @return  string           The HTML
     */
    protected function getFieldHtml(array $element)
    {
        $data = $this->getElements($element['type']);

        ob_start(); ?>
        <div id="qfb-element-<?php echo esc_attr($element['id']); ?>" class="qfb-element qfb-element-<?php echo esc_attr($element['type']) . (isset($element['required']) && $element['required'] ? ' qfb-element-required' : ''); ?>" data-id="<?php echo esc_attr($element['id']); ?>" data-type="<?php echo esc_attr($element['type']); ?>">
            <div class="qfb-element-inner qfb-cf">
                <span class="qfb-element-type-icon"><?php echo $data['icon']; ?></span>
                <label class="qfb-preview-label<?php echo ( ! Quform::isNonEmptyString($element['label']) ? ' qfb-hidden' : ''); ?>"><span id="qfb-plc-<?php echo esc_attr($element['id']); ?>" class="qfb-preview-label-content"><?php echo Quform::escape($element['label']); ?></span></label>
                <div class="qfb-element-actions">
                    <span class="qfb-element-action-required" title="<?php esc_attr_e('Toggle required', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-done"></i></span>
                    <span class="qfb-element-action-remove" title="<?php esc_attr_e('Remove', 'quform'); ?>"><i class="qfb-icon qfb-icon-trash"></i></span>
                    <span class="qfb-element-action-duplicate" title="<?php esc_attr_e('Duplicate', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>
                    <span class="qfb-element-action-settings" title="<?php esc_attr_e('Settings', 'quform'); ?>"><i class="qfb-mdi qfb-mdi-settings"></i></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a default element with the given type
     *
     * @param   string  $type  The element type
     * @return  string
     */
    protected function getDefaultElementHtml($type)
    {
        $element = $this->getDefaultElementConfig($type);
        $element['id'] = 0;

        return $this->getElementHtml($element);
    }

    /**
     * Get the HTML for a single page tab nav
     *
     * @param   int     $key
     * @param   array   $elementId
     * @param   string  $label
     * @return  string
     */
    public function getPageTabNavHtml($key = null, $elementId = null, $label = null)
    {
        $output = '<li class="qfb-page-tab-nav k-item' . ($key === 0 ? ' qfb-current-page k-state-active' : '') . '"' . (is_numeric($elementId) ? sprintf(' data-id="%d"', esc_attr($elementId)) : '') . '>';
        $output .= '<span class="qfb-page-tab-nav-label">';

        if (Quform::isNonEmptyString($label)) {
            $output .= esc_html($label);
        } else if (is_numeric($key)) {
            $output .= esc_html(sprintf(__('Page %s', 'quform'), $key + 1));
        }

        $output .= '</span>';
        $output .= '<span class="qfb-page-actions">';
        $output .= '<span class="qfb-page-action-settings" title="' . esc_attr__('Settings', 'quform') . '"><i class="qfb-mdi qfb-mdi-settings"></i></span>';
        $output .= '<span class="qfb-page-action-duplicate" title="' . esc_attr__('Duplicate', 'quform') . '"><i class="qfb-mdi qfb-mdi-content_copy"></i></span>';
        $output .= '<span class="qfb-page-action-remove" title="' . esc_attr__('Remove', 'quform') . '"><i class="qfb-icon qfb-icon-trash"></i></span>';
        $output .= '</span></li>';

        return $output;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        $variables = $this->getPreProcessVariables();

        $variables['general']['variables']['{entry_id}'] = __('Entry ID', 'quform');
        $variables['general']['variables']['{form_name}'] = __('Form Name', 'quform');
        $variables['general']['variables']['{all_form_data}'] = __('All Form Data', 'quform');
        $variables['general']['variables']['{default_email_address}'] = __('Default Email Address', 'quform');
        $variables['general']['variables']['{default_email_name}'] = __('Default Email Name', 'quform');
        $variables['general']['variables']['{default_from_email_address}'] = __('Default "From" Email Address', 'quform');
        $variables['general']['variables']['{default_from_email_name}'] = __('Default "From" Email Name', 'quform');
        $variables['general']['variables']['{admin_email}'] = __('Admin Email', 'quform');

        return apply_filters('quform_variables', $variables);
    }

    /**
     * @return array
     */
    public function getPreProcessVariables()
    {
        return apply_filters('quform_pre_process_variables', array(
            'general' => array(
                'heading' => __('General', 'quform'),
                'variables' => array(
                    '{url}' => __('Form URL', 'quform'),
                    '{referring_url}' => __('Referring URL', 'quform'),
                    '{post|ID}' => __('Post ID', 'quform'),
                    '{post|post_title}' => __('Post Title', 'quform'),
                    '{custom_field|my_custom_field}' => __('Custom Field', 'quform'),
                    '{date}' => __('Date', 'quform'),
                    '{time}' => __('Time', 'quform'),
                    '{datetime}' => __('DateTime', 'quform'),
                    '{site_title}' => __('Site Title', 'quform'),
                    '{site_tagline}' => __('Site Description', 'quform'),
                    '{uniqid}' => __('Random Unique ID', 'quform')
                )
            ),
            'user' => array(
                'heading' => __('User', 'quform'),
                'variables' => array(
                    '{ip}' => __('IP Address', 'quform'),
                    '{user_agent}' => __('User Agent', 'quform'),
                    '{user|display_name}' => __('Display Name', 'quform'),
                    '{user|user_email}' => __('Email', 'quform'),
                    '{user|user_login}' => __('Login', 'quform'),
                    '{user_meta|my_user_meta_key}' => __('User Metadata', 'quform')
                )
            )
        ));
    }

    /**
     * The supported reCAPTCHA languages from https://developers.google.com/recaptcha/docs/language
     *
     * @return array
     */
    public function getRecaptchaLanguages()
    {
        return array(
            '' => __('Autodetect', 'quform'),
            'ar' => 'Arabic',
            'af' => 'Afrikaans',
            'am' => 'Amharic',
            'hy' => 'Armenian',
            'az' => 'Azerbaijani',
            'eu' => 'Basque',
            'bn' => 'Bengali',
            'bg' => 'Bulgarian',
            'ca' => 'Catalan',
            'zh-HK' => 'Chinese (Hong Kong)',
            'zh-CN' => 'Chinese (Simplified)',
            'zh-TW' => 'Chinese (Traditional)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en-GB' => 'English (UK)',
            'en' => 'English (US)',
            'et' => 'Estonian',
            'fil' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'fr-CA' => 'French (Canadian)',
            'gl' => 'Galician',
            'ka' => 'Georgian',
            'de' => 'German',
            'de-AT' => 'German (Austria)',
            'de-CH' => 'German (Switzerland)',
            'el' => 'Greek',
            'gu' => 'Gujarati',
            'iw' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarain',
            'is' => 'Icelandic',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'kn' => 'Kannada',
            'ko' => 'Korean',
            'lo' => 'Laothian',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'ml' => 'Malayalam',
            'mr' => 'Marathi',
            'mn' => 'Mongolian',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'pt-BR' => 'Portuguese (Brazil)',
            'pt-PT' => 'Portuguese (Portugal)',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'si' => 'Sinhalese',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'es-419' => 'Spanish (Latin America)',
            'sw' => 'Swahili',
            'sv' => 'Swedish',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'vi' => 'Vietnamese',
            'zu' => 'Zulu',
        );
    }

    /**
     * The supported hCaptcha languages from https://docs.hcaptcha.com/languages/
     *
     * @return array
     */
    public function getHcaptchaLanguages()
    {
        return array(
            '' => __('Autodetect', 'quform'),
            'af' => 'Afrikaans',
            'sq' => 'Albanian',
            'am' => 'Amharic',
            'ar' => 'Arabic',
            'hy' => 'Armenian',
            'az' => 'Azerbaijani',
            'eu' => 'Basque',
            'be' => 'Belarusian',
            'bn' => 'Bengali',
            'bg' => 'Bulgarian',
            'bs' => 'Bosnian',
            'my' => 'Burmese',
            'ca' => 'Catalan',
            'ceb' => 'Cebuano',
            'zh' => 'Chinese',
            'zh-CN' => 'Chinese Simplified',
            'zh-TW' => 'Chinese Traditional',
            'co' => 'Corsican',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'eo' => 'Esperanto',
            'et' => 'Estonian',
            'fa' => 'Farsi / Persian',
            'fi' => 'Finnish',
            'fr' => 'French',
            'fy' => 'Frisian',
            'gd' => 'Gaelic',
            'gl' => 'Galacian',
            'ka' => 'Georgian',
            'de' => 'German',
            'el' => 'Greek',
            'gu' => 'Gujurati',
            'ht' => 'Haitian',
            'ha' => 'Hausa',
            'haw' => 'Hawaiian',
            'he' => 'Hebrew',
            'hi' => 'Hindi',
            'hmn' => 'Hmong',
            'hu' => 'Hungarian',
            'is' => 'Icelandic',
            'ig' => 'Igbo',
            'id' => 'Indonesian',
            'ga' => 'Irish',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'jw' => 'Javanese',
            'kn' => 'Kannada',
            'kk' => 'Kazakh',
            'km' => 'Khmer',
            'rw' => 'Kinyarwanda',
            'ky' => 'Kirghiz',
            'ko' => 'Korean',
            'ku' => 'Kurdish',
            'lo' => 'Lao',
            'la' => 'Latin',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'lb' => 'Luxembourgish',
            'mk' => 'Macedonian',
            'mg' => 'Malagasy',
            'ms' => 'Malay',
            'ml' => 'Malayalam',
            'mt' => 'Maltese',
            'mi' => 'Maori',
            'mr' => 'Marathi',
            'mn' => 'Mongolian',
            'ne' => 'Nepali',
            'no' => 'Norwegian',
            'ny' => 'Nyanja',
            'or' => 'Oriya',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ps' => 'Pashto',
            'pa' => 'Punjabi',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sm' => 'Samoan',
            'sn' => 'Shona',
            'sd' => 'Sindhi',
            'si' => 'Singhalese',
            'sr' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'so' => 'Somani',
            'st' => 'Southern Sotho',
            'es' => 'Spanish',
            'su' => 'Sundanese',
            'sw' => 'Swahili',
            'sv' => 'Swedish',
            'tl' => 'Tagalog',
            'tg' => 'Tajik',
            'ta' => 'Tamil',
            'tt' => 'Tatar',
            'te' => 'Teluga',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'tk' => 'Turkmen',
            'ug' => 'Uyghur',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'cy' => 'Welsh',
            'xh' => 'Xhosa',
            'yi' => 'Yiddish',
            'yo' => 'Yoruba',
            'zu' => 'Zulu',
        );
    }

    /**
     * The supported Turnstile languages from https://developers.cloudflare.com/turnstile/get-started/client-side-rendering/#configurations
     *
     * @return array
     */
    public function getTurnstileLanguages()
    {
        return array(
            '' => __('Autodetect', 'quform'),
            'ar-EG' => 'Arabic (Egypt)',
            'zh-CN' => 'Chinese Simplified',
            'zh-TW' => 'Chinese Traditional',
            'nl' => 'Dutch',
            'en' => 'English',
            'fa' => 'Farsi / Persian',
            'fr' => 'French',
            'de' => 'German',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'pl' => 'Polish',
            'pt-BR' => 'Portuguese (Brazil)',
            'ru' => 'Russian',
            'es' => 'Spanish',
            'tr' => 'Turkish',
        );
    }

    /**
     * Get the HTML for a blank logic rule
     *
     * @return string
     */
    protected function getLogicRuleHtml()
    {
        $output = '<div class="qfb-logic-rule qfb-box">';
        $output .= '<div class="qfb-logic-rule-columns qfb-cf">';
        $output .= '<div class="qfb-logic-rule-column qfb-logic-rule-column-element"></div>';
        $output .= '<div class="qfb-logic-rule-column qfb-logic-rule-column-operator"></div>';
        $output .= '<div class="qfb-logic-rule-column qfb-logic-rule-column-value"></div>';
        $output .= '</div>';
        $output .= sprintf('<span class="qfb-small-add-button qfb-mdi qfb-mdi-add_circle" title="%s"></span>', esc_attr__('Add new logic rule', 'quform'));
        $output .= sprintf('<span class="qfb-small-remove-button qfb-icon qfb-icon-trash" title="%s"></span>', esc_attr__('Remove logic rule', 'quform'));
        $output .= '</div>';

        return $output;
    }

    /**
     * Get the element types that can be used as a source for conditional logic
     *
     * @return array
     */
    protected function getLogicSourceTypes()
    {
        return apply_filters('quform_logic_source_types', array(
            'text', 'textarea', 'email', 'select', 'radio', 'checkbox', 'multiselect', 'file', 'date', 'time', 'hidden', 'password'
        ));
    }

    /**
     * Get the element types than can be used as a source for attachments
     *
     * @return array
     */
    protected function getAttachmentSourceTypes()
    {
        return apply_filters('quform_attachment_source_types', array(
            'file'
        ));
    }

    /**
     * Handle the request to save the form via Ajax
     */
    public function save()
    {
        $this->validateSaveRequest();

        $config = json_decode(stripslashes($_POST['form']), true);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Malformed form configuration', 'quform')
            ));
        }

        $config = $this->sanitizeForm($config);

        $this->validateForm($config);

        $config = $this->repository->save($config);

        $this->scriptLoader->handleSaveForm($config);

        wp_send_json(array(
            'type' => 'success'
        ));
    }

    /**
     * Validate the request to save the form
     */
    protected function validateSaveRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['form'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_edit_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_save_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Sanitize the given form config and return it
     *
     * @param   array  $config
     * @return  array
     */
    public function sanitizeForm(array $config)
    {
        // General - Form
        $config['name'] = isset($config['name']) && is_string($config['name']) ? sanitize_text_field($config['name']) : '';
        $config['title'] = isset($config['title']) && is_string($config['title']) ? wp_kses_post($config['title']) : '';
        $config['titleTag'] = isset($config['titleTag']) && is_string($config['titleTag']) ? sanitize_text_field($config['titleTag']) : 'h2';
        $config['description'] = isset($config['description']) && is_string($config['description']) ? wp_kses_post($config['description']) : '';
        $config['active'] = isset($config['active']) && is_bool($config['active']) ? $config['active'] : true;
        $config['inactiveMessage'] = isset($config['inactiveMessage']) && is_string($config['inactiveMessage']) ? wp_kses_post($config['inactiveMessage']) : '';
        $config['trashed'] = isset($config['trashed']) && is_bool($config['trashed']) ? $config['trashed'] : false;
        $config['ajax'] = isset($config['ajax']) && is_bool($config['ajax']) ? $config['ajax'] : true;
        $config['saveEntry'] = isset($config['saveEntry']) && is_bool($config['saveEntry']) ? $config['saveEntry'] : true;
        $config['honeypot'] = isset($config['honeypot']) && is_bool($config['honeypot']) ? $config['honeypot'] : true;
        $config['logicAnimation'] = isset($config['logicAnimation']) && is_bool($config['logicAnimation']) ? $config['logicAnimation'] : true;

        // General - Limits
        $config['oneEntryPerUser'] = isset($config['oneEntryPerUser']) && is_bool($config['oneEntryPerUser']) ? $config['oneEntryPerUser'] : false;
        $config['oneEntryPer'] = isset($config['oneEntryPer']) && is_string($config['oneEntryPer']) ? sanitize_text_field($config['oneEntryPer']) : 'logged-in-user';
        $config['limitEntries'] = isset($config['limitEntries']) && is_bool($config['limitEntries']) ? $config['limitEntries'] : false;
        $config['entryLimit'] = isset($config['entryLimit']) && is_numeric($config['entryLimit']) && $config['entryLimit'] > 0 ? (string) (int) $config['entryLimit'] : '';
        $config['entryLimitReachedMessage'] = isset($config['entryLimitReachedMessage']) && is_string($config['entryLimitReachedMessage']) ? wp_kses_post($config['entryLimitReachedMessage']) : '';

        // General - Scheduling
        $config['enableSchedule'] = isset($config['enableSchedule']) && is_bool($config['enableSchedule']) ? $config['enableSchedule'] : false;
        $config['scheduleStart'] = isset($config['scheduleStart']) && is_string($config['scheduleStart']) ? $this->sanitizeDateTime($config['scheduleStart']) : '';
        $config['scheduleStartMessage'] = isset($config['scheduleStartMessage']) && is_string($config['scheduleStartMessage']) ? wp_kses_post($config['scheduleStartMessage']) : '';
        $config['scheduleEnd'] = isset($config['scheduleEnd']) && is_string($config['scheduleEnd']) ? $this->sanitizeDateTime($config['scheduleEnd']) : '';
        $config['scheduleEndMessage'] = isset($config['scheduleEndMessage']) && is_string($config['scheduleEndMessage']) ? wp_kses_post($config['scheduleEndMessage']) : '';

        // Style - Global
        $config['theme'] = isset($config['theme']) && is_string($config['theme']) ? sanitize_text_field($config['theme']) : '';
        $config['themePrimaryColor'] = isset($config['themePrimaryColor']) && is_string($config['themePrimaryColor']) ? sanitize_text_field($config['themePrimaryColor']) : '';
        $config['themeSecondaryColor'] = isset($config['themeSecondaryColor']) && is_string($config['themeSecondaryColor']) ? sanitize_text_field($config['themeSecondaryColor']) : '';
        $config['themePrimaryForegroundColor'] = isset($config['themePrimaryForegroundColor']) && is_string($config['themePrimaryForegroundColor']) ? sanitize_text_field($config['themePrimaryForegroundColor']) : '';
        $config['themeSecondaryForegroundColor'] = isset($config['themeSecondaryForegroundColor']) && is_string($config['themeSecondaryForegroundColor']) ? sanitize_text_field($config['themeSecondaryForegroundColor']) : '';
        $config['responsiveElements'] = isset($config['responsiveElements']) && is_string($config['responsiveElements']) ? sanitize_text_field($config['responsiveElements']) : 'phone-landscape';
        $config['responsiveElementsCustom'] = isset($config['responsiveElementsCustom']) && is_string($config['responsiveElementsCustom']) ? sanitize_text_field($config['responsiveElementsCustom']) : '';
        $config['responsiveColumns'] = isset($config['responsiveColumns']) && is_string($config['responsiveColumns']) ? sanitize_text_field($config['responsiveColumns']) : 'phone-landscape';
        $config['responsiveColumnsCustom'] = isset($config['responsiveColumnsCustom']) && is_string($config['responsiveColumnsCustom']) ? sanitize_text_field($config['responsiveColumnsCustom']) : '';
        $config['verticalElementSpacing'] = isset($config['verticalElementSpacing']) && is_string($config['verticalElementSpacing']) ? sanitize_text_field($config['verticalElementSpacing']) : '';
        $config['width'] = isset($config['width']) && is_string($config['width']) ? sanitize_text_field($config['width']) : '';
        $config['position'] = isset($config['position']) && is_string($config['position']) ? sanitize_text_field($config['position']) : '';
        $config['previewColor'] = isset($config['previewColor']) && is_string($config['previewColor']) ? sanitize_text_field($config['previewColor']) : '';
        $config['styles'] = isset($config['styles']) && is_array($config['styles']) ? $this->sanitizeGlobalStyles($config['styles']) : array();

        // Style - Labels
        $config['labelTextColor'] = isset($config['labelTextColor']) && is_string($config['labelTextColor']) ? sanitize_text_field($config['labelTextColor']) : '';
        $config['labelPosition'] = isset($config['labelPosition']) && is_string($config['labelPosition']) ? sanitize_text_field($config['labelPosition']) : '';
        $config['labelWidth'] = isset($config['labelWidth']) && is_string($config['labelWidth']) ? sanitize_text_field($config['labelWidth']) : '150px';
        $config['requiredText'] = isset($config['requiredText']) && is_string($config['requiredText']) ? sanitize_text_field($config['requiredText']) : '*';
        $config['requiredTextColor'] = isset($config['requiredTextColor']) && is_string($config['requiredTextColor']) ? sanitize_text_field($config['requiredTextColor']) : '';

        // Style - Fields
        $config['fieldSize'] = isset($config['fieldSize']) && is_string($config['fieldSize']) ? sanitize_text_field($config['fieldSize']) : '';
        $config['fieldWidth'] = isset($config['fieldWidth']) && is_string($config['fieldWidth']) ? sanitize_text_field($config['fieldWidth']) : '';
        $config['fieldWidthCustom'] = isset($config['fieldWidthCustom']) && is_string($config['fieldWidthCustom']) ? sanitize_text_field($config['fieldWidthCustom']) : '';
        $config['fieldBackgroundColor'] = isset($config['fieldBackgroundColor']) && is_string($config['fieldBackgroundColor']) ? sanitize_text_field($config['fieldBackgroundColor']) : '';
        $config['fieldBackgroundColorHover'] = isset($config['fieldBackgroundColorHover']) && is_string($config['fieldBackgroundColorHover']) ? sanitize_text_field($config['fieldBackgroundColorHover']) : '';
        $config['fieldBackgroundColorFocus'] = isset($config['fieldBackgroundColorFocus']) && is_string($config['fieldBackgroundColorFocus']) ? sanitize_text_field($config['fieldBackgroundColorFocus']) : '';
        $config['fieldBorderColor'] = isset($config['fieldBorderColor']) && is_string($config['fieldBorderColor']) ? sanitize_text_field($config['fieldBorderColor']) : '';
        $config['fieldBorderColorHover'] = isset($config['fieldBorderColorHover']) && is_string($config['fieldBorderColorHover']) ? sanitize_text_field($config['fieldBorderColorHover']) : '';
        $config['fieldBorderColorFocus'] = isset($config['fieldBorderColorFocus']) && is_string($config['fieldBorderColorFocus']) ? sanitize_text_field($config['fieldBorderColorFocus']) : '';
        $config['fieldTextColor'] = isset($config['fieldTextColor']) && is_string($config['fieldTextColor']) ? sanitize_text_field($config['fieldTextColor']) : '';
        $config['fieldTextColorHover'] = isset($config['fieldTextColorHover']) && is_string($config['fieldTextColorHover']) ? sanitize_text_field($config['fieldTextColorHover']) : '';
        $config['fieldTextColorFocus'] = isset($config['fieldTextColorFocus']) && is_string($config['fieldTextColorFocus']) ? sanitize_text_field($config['fieldTextColorFocus']) : '';
        $config['fieldPlaceholderStyles'] = isset($config['fieldPlaceholderStyles']) && is_string($config['fieldPlaceholderStyles']) ? wp_strip_all_tags($config['fieldPlaceholderStyles']) : '';

        // Style - Buttons
        $config['buttonStyle'] = isset($config['buttonStyle']) && is_string($config['buttonStyle']) ? sanitize_text_field($config['buttonStyle']) : 'theme';
        $config['buttonSize'] = isset($config['buttonSize']) && is_string($config['buttonSize']) ? sanitize_text_field($config['buttonSize']) : '';
        $config['buttonWidth'] = isset($config['buttonWidth']) && is_string($config['buttonWidth']) ? sanitize_text_field($config['buttonWidth']) : '';
        $config['buttonWidthCustom'] = isset($config['buttonWidthCustom']) && is_string($config['buttonWidthCustom']) ? sanitize_text_field($config['buttonWidthCustom']) : '';
        $config['buttonAnimation'] = isset($config['buttonAnimation']) && is_string($config['buttonAnimation']) ? sanitize_text_field($config['buttonAnimation']) : '';
        $config['buttonBackgroundColor'] = isset($config['buttonBackgroundColor']) && is_string($config['buttonBackgroundColor']) ? sanitize_text_field($config['buttonBackgroundColor']) : '';
        $config['buttonBackgroundColorHover'] = isset($config['buttonBackgroundColorHover']) && is_string($config['buttonBackgroundColorHover']) ? sanitize_text_field($config['buttonBackgroundColorHover']) : '';
        $config['buttonBackgroundColorActive'] = isset($config['buttonBackgroundColorActive']) && is_string($config['buttonBackgroundColorActive']) ? sanitize_text_field($config['buttonBackgroundColorActive']) : '';
        $config['buttonBorderColor'] = isset($config['buttonBorderColor']) && is_string($config['buttonBorderColor']) ? sanitize_text_field($config['buttonBorderColor']) : '';
        $config['buttonBorderColorHover'] = isset($config['buttonBorderColorHover']) && is_string($config['buttonBorderColorHover']) ? sanitize_text_field($config['buttonBorderColorHover']) : '';
        $config['buttonBorderColorActive'] = isset($config['buttonBorderColorActive']) && is_string($config['buttonBorderColorActive']) ? sanitize_text_field($config['buttonBorderColorActive']) : '';
        $config['buttonTextColor'] = isset($config['buttonTextColor']) && is_string($config['buttonTextColor']) ? sanitize_text_field($config['buttonTextColor']) : '';
        $config['buttonTextColorHover'] = isset($config['buttonTextColorHover']) && is_string($config['buttonTextColorHover']) ? sanitize_text_field($config['buttonTextColorHover']) : '';
        $config['buttonTextColorActive'] = isset($config['buttonTextColorActive']) && is_string($config['buttonTextColorActive']) ? sanitize_text_field($config['buttonTextColorActive']) : '';
        $config['buttonIconColor'] = isset($config['buttonIconColor']) && is_string($config['buttonIconColor']) ? sanitize_text_field($config['buttonIconColor']) : '';
        $config['buttonIconColorHover'] = isset($config['buttonIconColorHover']) && is_string($config['buttonIconColorHover']) ? sanitize_text_field($config['buttonIconColorHover']) : '';
        $config['buttonIconColorActive'] = isset($config['buttonIconColorActive']) && is_string($config['buttonIconColorActive']) ? sanitize_text_field($config['buttonIconColorActive']) : '';
        $config['submitType'] = isset($config['submitType']) && is_string($config['submitType']) ? sanitize_text_field($config['submitType']) : 'default';
        $config['submitText'] = isset($config['submitText']) && is_string($config['submitText']) ? sanitize_text_field($config['submitText']) : '';
        $config['submitIcon'] = isset($config['submitIcon']) && is_string($config['submitIcon']) ? sanitize_text_field($config['submitIcon']) : '';
        $config['submitIconPosition'] = isset($config['submitIconPosition']) && is_string($config['submitIconPosition']) ? sanitize_text_field($config['submitIconPosition']) : 'right';
        $config['submitImage'] = isset($config['submitImage']) && is_string($config['submitImage']) ? esc_url_raw($config['submitImage']) : '';
        $config['submitHtml'] = isset($config['submitHtml']) && is_string($config['submitHtml']) ? wp_kses_post($config['submitHtml']) : '';
        $config['nextType'] = isset($config['nextType']) && is_string($config['nextType']) ? sanitize_text_field($config['nextType']) : 'default';
        $config['nextText'] = isset($config['nextText']) && is_string($config['nextText']) ? sanitize_text_field($config['nextText']) : '';
        $config['nextIcon'] = isset($config['nextIcon']) && is_string($config['nextIcon']) ? sanitize_text_field($config['nextIcon']) : '';
        $config['nextIconPosition'] = isset($config['nextIconPosition']) && is_string($config['nextIconPosition']) ? sanitize_text_field($config['nextIconPosition']) : 'right';
        $config['nextImage'] = isset($config['nextImage']) && is_string($config['nextImage']) ? esc_url_raw($config['nextImage']) : '';
        $config['nextHtml'] = isset($config['nextHtml']) && is_string($config['nextHtml']) ? wp_kses_post($config['nextHtml']) : '';
        $config['backType'] = isset($config['backType']) && is_string($config['backType']) ? sanitize_text_field($config['backType']) : 'default';
        $config['backText'] = isset($config['backText']) && is_string($config['backText']) ? sanitize_text_field($config['backText']) : '';
        $config['backIcon'] = isset($config['backIcon']) && is_string($config['backIcon']) ? sanitize_text_field($config['backIcon']) : '';
        $config['backIconPosition'] = isset($config['backIconPosition']) && is_string($config['backIconPosition']) ? sanitize_text_field($config['backIconPosition']) : 'left';
        $config['backImage'] = isset($config['backImage']) && is_string($config['backImage']) ? esc_url_raw($config['backImage']) : '';
        $config['backHtml'] = isset($config['backHtml']) && is_string($config['backHtml']) ? wp_kses_post($config['backHtml']) : '';
        $config['backLocation'] = isset($config['backLocation']) && is_string($config['backLocation']) ? sanitize_text_field($config['backLocation']) : '';
        $config['optionsStyle'] = isset($config['optionsStyle']) && is_string($config['optionsStyle']) ? sanitize_text_field($config['optionsStyle']) : '';
        $config['optionsButtonStyle'] = isset($config['optionsButtonStyle']) && is_string($config['optionsButtonStyle']) ? sanitize_text_field($config['optionsButtonStyle']) : '';
        $config['optionsButtonSize'] = isset($config['optionsButtonSize']) && is_string($config['optionsButtonSize']) ? sanitize_text_field($config['optionsButtonSize']) : '';
        $config['optionsButtonWidth'] = isset($config['optionsButtonWidth']) && is_string($config['optionsButtonWidth']) ? sanitize_text_field($config['optionsButtonWidth']) : '';
        $config['optionsButtonWidthCustom'] = isset($config['optionsButtonWidthCustom']) && is_string($config['optionsButtonWidthCustom']) ? sanitize_text_field($config['optionsButtonWidthCustom']) : '';
        $config['optionsButtonIconPosition'] = isset($config['optionsButtonIconPosition']) && is_string($config['optionsButtonIconPosition']) ? sanitize_text_field($config['optionsButtonIconPosition']) : '';

        // Style - Pages
        $config['pageProgressType'] = isset($config['pageProgressType']) && is_string($config['pageProgressType']) ? sanitize_text_field($config['pageProgressType']) : 'numbers';

        // Style - Loading
        $config['loadingType'] = isset($config['loadingType']) && is_string($config['loadingType']) ? sanitize_text_field($config['loadingType']) : 'spinner-1';
        $config['loadingCustom'] = isset($config['loadingCustom']) && is_string($config['loadingCustom']) ? wp_kses_post($config['loadingCustom']) : '';
        $config['loadingPosition'] = isset($config['loadingPosition']) && is_string($config['loadingPosition']) ? sanitize_text_field($config['loadingPosition']) : 'left';
        $config['loadingColor'] = isset($config['loadingColor']) && is_string($config['loadingColor']) ? sanitize_text_field($config['loadingColor']) : '';
        $config['loadingOverlay'] = isset($config['loadingOverlay']) && is_bool($config['loadingOverlay']) ? $config['loadingOverlay'] : false;
        $config['loadingOverlayColor'] = isset($config['loadingOverlayColor']) && is_string($config['loadingOverlayColor']) ? sanitize_text_field($config['loadingOverlayColor']) : '';

        // Style - Tooltips
        $config['tooltipsEnabled'] = isset($config['tooltipsEnabled']) && is_bool($config['tooltipsEnabled']) ? $config['tooltipsEnabled'] : true;
        $config['tooltipType'] = isset($config['tooltipType']) && is_string($config['tooltipType']) ? sanitize_text_field($config['tooltipType']) : 'icon';
        $config['tooltipEvent'] = isset($config['tooltipEvent']) && is_string($config['tooltipEvent']) ? sanitize_text_field($config['tooltipEvent']) : 'hover';
        $config['tooltipIcon'] = isset($config['tooltipIcon']) && is_string($config['tooltipIcon']) ? sanitize_text_field($config['tooltipIcon']) : 'qicon-question-circle';
        $config['tooltipStyle'] = isset($config['tooltipStyle']) && is_string($config['tooltipStyle']) ? sanitize_text_field($config['tooltipStyle']) : 'qtip-quform-dark';
        $config['tooltipCustom'] = isset($config['tooltipCustom']) && is_string($config['tooltipCustom']) ? Quform::sanitizeClass($config['tooltipCustom']) : '';
        $config['tooltipMy'] = isset($config['tooltipMy']) && is_string($config['tooltipMy']) ? sanitize_text_field($config['tooltipMy']) : 'left center';
        $config['tooltipAt'] = isset($config['tooltipAt']) && is_string($config['tooltipAt']) ? sanitize_text_field($config['tooltipAt']) : 'right center';
        $config['tooltipShadow'] = isset($config['tooltipShadow']) && is_bool($config['tooltipShadow']) ? $config['tooltipShadow'] : true;
        $config['tooltipRounded'] = isset($config['tooltipRounded']) && is_bool($config['tooltipRounded']) ? $config['tooltipRounded'] : false;
        $config['tooltipClasses'] = isset($config['tooltipClasses']) && is_string($config['tooltipClasses']) ? Quform::sanitizeClass($config['tooltipClasses']) : 'qtip-quform-dark qtip-shadow';

        // Notifications
        $config['notifications'] = isset($config['notifications']) && is_array($config['notifications']) ? $this->sanitizeNotifications($config['notifications']) : array();
        $config['nextNotificationId'] = isset($config['nextNotificationId']) && is_numeric($config['nextNotificationId']) ? (int) $config['nextNotificationId'] : 1;

        // Confirmations
        $config['confirmations'] = isset($config['confirmations']) && is_array($config['confirmations']) ? $this->sanitizeConfirmations($config['confirmations']) : array();
        $config['nextConfirmationId'] = isset($config['nextConfirmationId']) && is_numeric($config['nextConfirmationId']) ? (int) $config['nextConfirmationId'] : 1;

        // Errors
        $config['errorsPosition'] = isset($config['errorsPosition']) && is_string($config['errorsPosition']) ? sanitize_text_field($config['errorsPosition']) : '';
        $config['errorsIcon'] = isset($config['errorsIcon']) && is_string($config['errorsIcon']) ? sanitize_text_field($config['errorsIcon']) : '';
        $config['errorEnabled'] = isset($config['errorEnabled']) && is_bool($config['errorEnabled']) ? $config['errorEnabled'] : false;
        $config['errorTitle'] = isset($config['errorTitle']) && is_string($config['errorTitle']) ? wp_kses_post($config['errorTitle']) : '';
        $config['errorContent'] = isset($config['errorContent']) && is_string($config['errorContent']) ? wp_kses_post($config['errorContent']) : '';

        // Language
        $config['locale'] = isset($config['locale']) && is_string($config['locale']) ? sanitize_text_field($config['locale']) : '';
        $config['rtl'] = isset($config['rtl']) && is_string($config['rtl']) ? sanitize_text_field($config['rtl']) : 'global';
        $config['messageRequired'] = isset($config['messageRequired']) && is_string($config['messageRequired']) ? wp_kses_post($config['messageRequired']) : '';
        $config['pageProgressNumbersText'] = isset($config['pageProgressNumbersText']) && is_string($config['pageProgressNumbersText']) ? sanitize_text_field($config['pageProgressNumbersText']) : '';
        $config['onlyOneSubmissionAllowed'] = isset($config['onlyOneSubmissionAllowed']) && is_string($config['onlyOneSubmissionAllowed']) ? sanitize_text_field($config['onlyOneSubmissionAllowed']) : '';
        $config['thisFormIsCurrentlyClosed'] = isset($config['thisFormIsCurrentlyClosed']) && is_string($config['thisFormIsCurrentlyClosed']) ? sanitize_text_field($config['thisFormIsCurrentlyClosed']) : '';
        $config['formIsNotYetOpenForSubmissions'] = isset($config['formIsNotYetOpenForSubmissions']) && is_string($config['formIsNotYetOpenForSubmissions']) ? sanitize_text_field($config['formIsNotYetOpenForSubmissions']) : '';
        $config['formIsNoLongerOpenForSubmissions'] = isset($config['formIsNoLongerOpenForSubmissions']) && is_string($config['formIsNoLongerOpenForSubmissions']) ? sanitize_text_field($config['formIsNoLongerOpenForSubmissions']) : '';

        // Database
        $config['databaseEnabled'] = isset($config['databaseEnabled']) && is_bool($config['databaseEnabled']) ? $config['databaseEnabled'] : false;
        $config['databaseWordpress'] = isset($config['databaseWordpress']) && is_bool($config['databaseWordpress']) ? $config['databaseWordpress'] : true;
        $config['databaseHost'] = isset($config['databaseHost']) && is_string($config['databaseHost']) ? wp_strip_all_tags($config['databaseHost']) : '';
        $config['databaseUsername'] = isset($config['databaseUsername']) && is_string($config['databaseUsername']) ? wp_kses_no_null($config['databaseUsername'], array('slash_zero' => 'keep')) : '';
        $config['databasePassword'] = isset($config['databasePassword']) && is_string($config['databasePassword']) ? wp_kses_no_null($config['databasePassword'], array('slash_zero' => 'keep')) : '';
        $config['databaseDatabase'] = isset($config['databaseDatabase']) && is_string($config['databaseDatabase']) ? sanitize_text_field($config['databaseDatabase']) : '';
        $config['databaseTable'] = isset($config['databaseTable']) && is_string($config['databaseTable']) ? sanitize_text_field($config['databaseTable']) : '';
        $config['databaseColumns'] = isset($config['databaseColumns']) && is_array($config['databaseColumns']) ? $this->sanitizeDatabaseColumns($config['databaseColumns']) : array();

        // Feature cache & locales
        $config['hasDatepicker'] = isset($config['hasDatepicker']) && is_bool($config['hasDatepicker']) ? $config['hasDatepicker'] : false;
        $config['hasTimepicker'] = isset($config['hasTimepicker']) && is_bool($config['hasTimepicker']) ? $config['hasTimepicker'] : false;
        $config['hasEnhancedUploader'] = isset($config['hasEnhancedUploader']) && is_bool($config['hasEnhancedUploader']) ? $config['hasEnhancedUploader'] : false;
        $config['hasEnhancedSelect'] = isset($config['hasEnhancedSelect']) && is_bool($config['hasEnhancedSelect']) ? $config['hasEnhancedSelect'] : false;
        $config['locales'] = isset($config['locales']) && is_array($config['locales']) ? array_map('sanitize_text_field', $config['locales']) : array();

        // Elements
        if ( ! isset($config['elements']) || ! is_array($config['elements'])) {
            // Every form needs at least one page
            $page = $this->getDefaultElementConfig('page');
            $page['id'] = 1;
            $page['parentId'] = 0;
            $page['position'] = 0;

            $config['elements'] = array($page);
            $config['nextElementId'] = 2;
        }

        foreach ($config['elements'] as $key => $page) {
            if (is_array($page)) {
                $config['elements'][$key] = $this->sanitizeContainer($page);
            }
        }

        $config['nextElementId'] = isset($config['nextElementId']) && is_numeric($config['nextElementId']) ? (int) $config['nextElementId'] : 1;

        // Misc
        $config['entriesTableColumns'] = isset($config['entriesTableColumns']) && is_array($config['entriesTableColumns']) ? array_map('sanitize_key', $config['entriesTableColumns']) : array();
        $config['environment'] = isset($config['environment']) && is_string($config['environment']) ? sanitize_text_field($config['environment']) : 'frontend';

        return $config;
    }

    /**
     * Sanitize the given datetime value and converts it to UTC
     *
     * @param   string  $datetime
     * @return  string
     */
    protected function sanitizeDateTime($datetime, $format = 'Y-m-d H:i:s')
    {
        if (Quform::isNonEmptyString($datetime)) {
            $date = DateTime::createFromFormat($format, $datetime, Quform::getTimezone());

            if ($date instanceof DateTime && $date->format($format) == $datetime) {
                return $date->setTimezone(new DateTimeZone('UTC'))->format($format);
            }
        }

        return '';
    }

    /**
     * Converts a datetime string to the WP timezone
     *
     * @param   string  $datetime
     * @return  string
     */
    protected function convertDateTimeToWpTimezone($datetime, $format = 'Y-m-d H:i:s')
    {
        if (Quform::isNonEmptyString($datetime)) {
            $date = DateTime::createFromFormat($format, $datetime, new DateTimeZone('UTC'));

            if ($date instanceof DateTime && $date->format($format) == $datetime) {
                return $date->setTimezone(Quform::getTimezone())->format($format);
            }
        }

        return '';
    }

    /**
     * Sanitize the given form config and return it
     *
     * @deprecated  2.4.0
     * @param       array  $config
     * @return      array
     */
    public function sanitiseForm(array $config)
    {
        _deprecated_function(__METHOD__, '2.4.0', 'Quform_Builder::sanitizeForm()');

        return $this->sanitizeForm($config);
    }

    /**
     * Sanitize the settings for the given global styles
     *
     * @param   array   $styles
     * @return  array
     */
    protected function sanitizeGlobalStyles(array $styles)
    {
        $allStyles = $this->getGlobalStyles();
        $sanitizedStyles = array();

        foreach ($styles as $style) {
            if ( ! isset($style['type']) || ! is_string($style['type']) || ! array_key_exists($style['type'], $allStyles)) {
                continue;
            }

            $style['css'] = isset($style['css']) && is_string($style['css']) ? wp_strip_all_tags($style['css']) : '';

            $sanitizedStyles[] = $style;
        }

        return $sanitizedStyles;
    }

    /**
     * Sanitize the given array of notifications
     *
     * @param   array  $notifications
     * @return  array
     */
    protected function sanitizeNotifications(array $notifications)
    {
        foreach ($notifications as $key => $notification) {
            $notifications[$key]['name'] = isset($notification['name']) && is_string($notification['name']) ? sanitize_text_field($notification['name']) : '';
            $notifications[$key]['enabled'] = isset($notification['enabled']) && is_bool($notification['enabled']) ? $notification['enabled'] : true;
            $notifications[$key]['subject'] = isset($notification['subject']) && is_string($notification['subject']) ? wp_kses_no_null($notification['subject'], array('slash_zero' => 'keep')) : '';
            $notifications[$key]['format'] = isset($notification['format']) && is_string($notification['format']) ? sanitize_text_field($notification['format']) : '';
            $notifications[$key]['html'] = isset($notification['html']) && is_string($notification['html']) ? $notification['html'] : ''; // unfiltered on purpose
            $notifications[$key]['autoFormat'] = isset($notification['autoFormat']) && is_bool($notification['autoFormat']) ? $notification['autoFormat'] : true;
            $notifications[$key]['padding'] = isset($notification['padding']) && is_string($notification['padding']) ? sanitize_text_field($notification['padding']) : '20';
            $notifications[$key]['text'] = isset($notification['text']) && is_string($notification['text']) ? $notification['text'] : ''; // unfiltered on purpose
            $notifications[$key]['recipients'] = isset($notification['recipients']) && is_array($notification['recipients']) ? $this->sanitizeNotificationRecipients($notification['recipients']) : array();
            $notifications[$key]['conditional'] = isset($notification['conditional']) && is_bool($notification['conditional']) ? $notification['conditional'] : false;
            $notifications[$key]['conditionals'] = isset($notification['conditionals']) && is_array($notification['conditionals']) ? $this->sanitizeNotificationConditionals($notification['conditionals']) : array();
            $notifications[$key]['from'] = isset($notification['from']) && is_array($notification['from']) ? $this->sanitizeNotificationFrom($notification['from']) : array('address' => '', 'name' => '');
            $notifications[$key]['logicEnabled'] = isset($notification['logicEnabled']) && is_bool($notification['logicEnabled']) ? $notification['logicEnabled'] : false;
            $notifications[$key]['logicAction'] = isset($notification['logicAction']) && is_bool($notification['logicAction']) ? $notification['logicAction'] : true;
            $notifications[$key]['logicMatch'] = isset($notification['logicMatch']) && is_string($notification['logicMatch']) ? sanitize_text_field($notification['logicMatch']) : 'all';
            $notifications[$key]['logicRules'] = isset($notification['logicRules']) && is_array($notification['logicRules']) ? $this->sanitizeLogicRules($notification['logicRules']) : array();
            $notifications[$key]['attachments'] = isset($notification['attachments']) && is_array($notification['attachments']) ? $this->sanitizeNotificationAttachments($notification['attachments']) : array();
        }

        return $notifications;
    }

    /**
     * Sanitize the given array of notification recipients
     *
     * @param   array  $recipients
     * @return  array
     */
    protected function sanitizeNotificationRecipients(array $recipients)
    {
        foreach ($recipients as $key => $recipient) {
            $recipients[$key]['type'] = isset($recipient['type']) && is_string($recipient['type']) ? sanitize_text_field($recipient['type']) : '';
            $recipients[$key]['address'] = isset($recipient['address']) && is_string($recipient['address']) ? sanitize_text_field($recipient['address']) : '';
            $recipients[$key]['name'] = isset($recipient['name']) && is_string($recipient['name']) ? sanitize_text_field($recipient['name']) : '';
        }

        return $recipients;
    }

    /**
     * Sanitize the given array of notification conditionals
     *
     * @param   array  $conditionals
     * @return  array
     */
    protected function sanitizeNotificationConditionals(array $conditionals)
    {
        foreach ($conditionals as $key => $conditional) {
            $conditionals[$key]['recipients'] = isset($conditional['recipients']) && is_array($conditional['recipients']) ? $this->sanitizeNotificationRecipients($conditional['recipients']) : array();
            $conditionals[$key]['logicAction'] = isset($conditional['logicAction']) && is_bool($conditional['logicAction']) ? $conditional['logicAction'] : true;
            $conditionals[$key]['logicMatch'] = isset($conditional['logicMatch']) && is_string($conditional['logicMatch']) ? sanitize_text_field($conditional['logicMatch']) : 'all';
            $conditionals[$key]['logicRules'] = isset($conditional['logicRules']) && is_array($conditional['logicRules']) ? $this->sanitizeLogicRules($conditional['logicRules']) : array();
        }

        return $conditionals;
    }

    /**
     * Sanitize the given notification "From" address array
     *
     * @param   array  $from
     * @return  array
     */
    protected function sanitizeNotificationFrom(array $from)
    {
        $from['address'] = isset($from['address']) && is_string($from['address']) ? sanitize_text_field($from['address']) : '';
        $from['name'] = isset($from['name']) && is_string($from['name']) ? sanitize_text_field($from['name']) : '';

        return $from;
    }

    /**
     * Sanitize the given array of notification attachments
     *
     * @param   array  $attachments
     * @return  array
     */
    protected function sanitizeNotificationAttachments(array $attachments)
    {
        foreach ($attachments as $key => $attachment) {
            $attachments[$key]['source'] = isset($attachment['source']) && is_string($attachment['source']) ? sanitize_text_field($attachment['source']) : 'media';
            $attachments[$key]['element'] = isset($attachment['element']) && is_numeric($attachment['element']) ? (string) (int) $attachment['element'] : '';
            $attachments[$key]['media'] = isset($attachment['media']) && is_array($attachment['media']) ? $this->sanitizeNotificationAttachmentMedia($attachment['media']) : array();
        }

        return $attachments;
    }

    /**
     * Sanitize the given array of notification attachment media
     *
     * @param   array  $media
     * @return  array
     */
    protected function sanitizeNotificationAttachmentMedia(array $media)
    {
        foreach ($media as $key => $medium) {
            $media[$key]['id'] = isset($medium['id']) && is_numeric($medium['id']) ? (int) $medium['id'] : 0;
            $media[$key]['filename'] = isset($medium['filename']) && is_string($medium['filename']) ? sanitize_file_name($medium['filename']) : '';
            $media[$key]['filesizeHumanReadable'] = isset($medium['filesizeHumanReadable']) && is_string($medium['filesizeHumanReadable']) ? sanitize_text_field($medium['filesizeHumanReadable']) : '0 B';
            $media[$key]['icon'] = isset($medium['icon']) && is_string($medium['icon']) ? esc_url_raw($medium['icon']) : '';
        }

        return $media;
    }

    /**
     * Sanitize the given array of confirmations
     *
     * @param   array  $confirmations
     * @return  array
     */
    protected function sanitizeConfirmations(array $confirmations)
    {
        foreach ($confirmations as $key => $confirmation) {
            $confirmations[$key]['name'] = isset($confirmation['name']) && is_string($confirmation['name']) ? sanitize_text_field($confirmation['name']) : '';
            $confirmations[$key]['enabled'] = isset($confirmation['enabled']) && is_bool($confirmation['enabled']) ? $confirmation['enabled'] : true;
            $confirmations[$key]['type'] = isset($confirmation['type']) && is_string($confirmation['type']) ? sanitize_text_field($confirmation['type']) : 'message';
            $confirmations[$key]['message'] = isset($confirmation['message']) && is_string($confirmation['message']) ? $this->sanitizeHtml($confirmation['message']) : '';
            $confirmations[$key]['messageAutoFormat'] = isset($confirmation['messageAutoFormat']) && is_bool($confirmation['messageAutoFormat']) ? $confirmation['messageAutoFormat'] : true;
            $confirmations[$key]['messageIcon'] = isset($confirmation['messageIcon']) && is_string($confirmation['messageIcon']) ? sanitize_text_field($confirmation['messageIcon']) : '';
            $confirmations[$key]['messagePosition'] = isset($confirmation['messagePosition']) && is_string($confirmation['messagePosition']) ? sanitize_text_field($confirmation['messagePosition']) : 'above';
            $confirmations[$key]['messageTimeout'] = isset($confirmation['messageTimeout']) && is_numeric($confirmation['messageTimeout']) ? (string) Quform::clamp((float) $confirmation['messageTimeout'], 0, 3600) : '10';
            $confirmations[$key]['redirectPage'] = isset($confirmation['redirectPage']) && is_numeric($confirmation['redirectPage']) ? (string) (int) $confirmation['redirectPage'] : '';
            $confirmations[$key]['redirectUrl'] = isset($confirmation['redirectUrl']) && is_string($confirmation['redirectUrl']) ? sanitize_text_field($confirmation['redirectUrl']) : '';
            $confirmations[$key]['redirectQuery'] = isset($confirmation['redirectQuery']) && is_string($confirmation['redirectQuery']) ? Quform::sanitizeTextareaField($confirmation['redirectQuery']) : '';
            $confirmations[$key]['redirectDelay'] = isset($confirmation['redirectDelay']) && is_numeric($confirmation['redirectDelay']) ? (string) Quform::clamp((float) $confirmation['redirectDelay'], 0, 3600) : '3';
            $confirmations[$key]['logicAction'] = isset($confirmation['logicAction']) && is_bool($confirmation['logicAction']) ? $confirmation['logicAction'] : true;
            $confirmations[$key]['logicMatch'] = isset($confirmation['logicMatch']) && is_string($confirmation['logicMatch']) ? sanitize_text_field($confirmation['logicMatch']) : 'all';
            $confirmations[$key]['logicRules'] = isset($confirmation['logicRules']) && is_array($confirmation['logicRules']) ? $this->sanitizeLogicRules($confirmation['logicRules']) : array();
            $confirmations[$key]['hideForm'] = isset($confirmation['hideForm']) && is_bool($confirmation['hideForm']) ? $confirmation['hideForm'] : false;
            $confirmations[$key]['resetForm'] = isset($confirmation['resetForm']) && is_string($confirmation['resetForm']) ? sanitize_text_field($confirmation['resetForm']) : '';
        }

        return $confirmations;
    }

    /**
     * Sanitize the settings for the given database columns
     *
     * @param   array   $columns
     * @return  array
     */
    protected function sanitizeDatabaseColumns(array $columns)
    {
        foreach ($columns as $key => $column) {
            $columns[$key]['name'] = isset($column['name']) && is_string($column['name']) ? sanitize_text_field($column['name']) : '';
            $columns[$key]['value'] = isset($column['value']) && is_string($column['value']) ? sanitize_text_field($column['value']) : '';
        }

        return $columns;
    }

    /**
     * Sanitize the given container config and return it
     *
     * @param   array  $container
     * @return  array
     */
    protected function sanitizeContainer(array $container)
    {
        $container = $this->sanitizeElement($container);

        foreach ($container['elements'] as $key => $element) {
            if ($element['type'] == 'group' || $element['type'] == 'row' || $element['type'] == 'column') {
                $container['elements'][$key] = $this->sanitizeContainer($element);
            } else {
                $container['elements'][$key] = $this->sanitizeElement($element);
            }
        }

        return $container;
    }

    /**
     * Sanitize the given element config and return it
     *
     * @param   array  $element
     * @return  array
     */
    protected function sanitizeElement(array $element)
    {
        switch ($element['type']) {
            case 'text':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldIconLeft'] = isset($element['fieldIconLeft']) && is_string($element['fieldIconLeft']) ? sanitize_text_field($element['fieldIconLeft']) : '';
                $element['fieldIconRight'] = isset($element['fieldIconRight']) && is_string($element['fieldIconRight']) ? sanitize_text_field($element['fieldIconRight']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['placeholder'] = isset($element['placeholder']) && is_string($element['placeholder']) ? sanitize_text_field($element['placeholder']) : '';
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? sanitize_text_field($element['defaultValue']) : '';
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['autocomplete'] = isset($element['autocomplete']) && is_string($element['autocomplete']) ? sanitize_text_field($element['autocomplete']) : '';
                $element['maxLength'] = isset($element['maxLength']) && is_numeric($element['maxLength']) ? (string) (int) $element['maxLength'] : '';
                $element['readOnly'] = isset($element['readOnly']) && is_bool($element['readOnly']) ? $element['readOnly'] : false;
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['filters'] = isset($element['filters']) && is_array($element['filters']) ? $this->sanitizeFilters($element['filters'], $element['type']) : array();
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageLengthTooLong'] = isset($element['messageLengthTooLong']) && is_string($element['messageLengthTooLong']) ? wp_kses_post($element['messageLengthTooLong']) : '';
                break;
            case 'textarea':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldIconLeft'] = isset($element['fieldIconLeft']) && is_string($element['fieldIconLeft']) ? sanitize_text_field($element['fieldIconLeft']) : '';
                $element['fieldIconRight'] = isset($element['fieldIconRight']) && is_string($element['fieldIconRight']) ? sanitize_text_field($element['fieldIconRight']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['enableEditor'] = isset($element['enableEditor']) && is_bool($element['enableEditor']) ? $element['enableEditor'] : false;
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['placeholder'] = isset($element['placeholder']) && is_string($element['placeholder']) ? sanitize_text_field($element['placeholder']) : '';
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? Quform::sanitizeTextareaField($element['defaultValue']) : '';
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['autocomplete'] = isset($element['autocomplete']) && is_string($element['autocomplete']) ? sanitize_text_field($element['autocomplete']) : '';
                $element['maxLength'] = isset($element['maxLength']) && is_numeric($element['maxLength']) ? (string) (int) $element['maxLength'] : '';
                $element['readOnly'] = isset($element['readOnly']) && is_bool($element['readOnly']) ? $element['readOnly'] : false;
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['filters'] = isset($element['filters']) && is_array($element['filters']) ? $this->sanitizeFilters($element['filters'], $element['type']) : array();
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageLengthTooLong'] = isset($element['messageLengthTooLong']) && is_string($element['messageLengthTooLong']) ? wp_kses_post($element['messageLengthTooLong']) : '';
                break;
            case 'email':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Email address', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : true;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldIconLeft'] = isset($element['fieldIconLeft']) && is_string($element['fieldIconLeft']) ? sanitize_text_field($element['fieldIconLeft']) : '';
                $element['fieldIconRight'] = isset($element['fieldIconRight']) && is_string($element['fieldIconRight']) ? sanitize_text_field($element['fieldIconRight']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['placeholder'] = isset($element['placeholder']) && is_string($element['placeholder']) ? sanitize_text_field($element['placeholder']) : '';
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? sanitize_text_field($element['defaultValue']) : '';
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['autocomplete'] = isset($element['autocomplete']) && is_string($element['autocomplete']) ? sanitize_text_field($element['autocomplete']) : '';
                $element['maxLength'] = isset($element['maxLength']) && is_numeric($element['maxLength']) ? (string) (int) $element['maxLength'] : '';
                $element['readOnly'] = isset($element['readOnly']) && is_bool($element['readOnly']) ? $element['readOnly'] : false;
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['filters'] = isset($element['filters']) && is_array($element['filters']) ? $this->sanitizeFilters($element['filters'], $element['type']) : array();
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageLengthTooLong'] = isset($element['messageLengthTooLong']) && is_string($element['messageLengthTooLong']) ? wp_kses_post($element['messageLengthTooLong']) : '';
                $element['messageEmailAddressInvalidFormat'] = isset($element['messageEmailAddressInvalidFormat']) && is_string($element['messageEmailAddressInvalidFormat']) ? wp_kses_post($element['messageEmailAddressInvalidFormat']) : '';
                break;
            case 'select':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled', 'quform');
                $element['options'] = isset($element['options']) && is_array($element['options']) ? $this->sanitizeSelectOptions($element['options']) : array();
                $element['nextOptionId'] = isset($element['nextOptionId']) && is_numeric($element['nextOptionId']) ? (int) $element['nextOptionId'] : 1;
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? wp_kses_post($element['defaultValue']) : '';
                $element['customiseValues'] = isset($element['customiseValues']) && is_bool($element['customiseValues']) ? $element['customiseValues'] : false;
                $element['noneOption'] = isset($element['noneOption']) && is_bool($element['noneOption']) ? $element['noneOption'] : true;
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['enhancedSelectEnabled'] = isset($element['enhancedSelectEnabled']) && is_bool($element['enhancedSelectEnabled']) ? $element['enhancedSelectEnabled'] : false;
                $element['enhancedSelectSearch'] = isset($element['enhancedSelectSearch']) && is_bool($element['enhancedSelectSearch']) ? $element['enhancedSelectSearch'] : true;
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'icon';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['inArrayValidator'] = isset($element['inArrayValidator']) && is_bool($element['inArrayValidator']) ? $element['inArrayValidator'] : true;
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['autocomplete'] = isset($element['autocomplete']) && is_string($element['autocomplete']) ? sanitize_text_field($element['autocomplete']) : '';
                $element['submitOnChoice'] = isset($element['submitOnChoice']) && is_bool($element['submitOnChoice']) ? $element['submitOnChoice'] : false;
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['noneOptionText'] = isset($element['noneOptionText']) && is_string($element['noneOptionText']) ? wp_kses_post($element['noneOptionText']) : '';
                $element['enhancedSelectNoResultsFound'] = isset($element['enhancedSelectNoResultsFound']) && is_string($element['enhancedSelectNoResultsFound']) ? wp_kses_post($element['enhancedSelectNoResultsFound']) : '';
                break;
            case 'checkbox':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled', 'quform');
                $element['options'] = isset($element['options']) && is_array($element['options']) ? $this->sanitizeCheckboxRadioOptions($element['options']) : array();
                $element['nextOptionId'] = isset($element['nextOptionId']) && is_numeric($element['nextOptionId']) ? (int) $element['nextOptionId'] : 1;
                $element['defaultValue'] = isset($element['defaultValue']) && is_array($element['defaultValue']) ? array_map('wp_kses_post', $element['defaultValue']) : array();
                $element['customiseValues'] = isset($element['customiseValues']) && is_bool($element['customiseValues']) ? $element['customiseValues'] : false;
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['optionsLayout'] = isset($element['optionsLayout']) && is_string($element['optionsLayout']) ? sanitize_text_field($element['optionsLayout']) : 'block';
                $element['optionsLayoutResponsiveColumns'] = isset($element['optionsLayoutResponsiveColumns']) && is_string($element['optionsLayoutResponsiveColumns']) ? sanitize_text_field($element['optionsLayoutResponsiveColumns']) : 'phone-landscape';
                $element['optionsLayoutResponsiveColumnsCustom'] = isset($element['optionsLayoutResponsiveColumnsCustom']) && is_string($element['optionsLayoutResponsiveColumnsCustom']) ? sanitize_text_field($element['optionsLayoutResponsiveColumnsCustom']) : '';
                $element['optionsStyle'] = isset($element['optionsStyle']) && is_string($element['optionsStyle']) ? sanitize_text_field($element['optionsStyle']) : '';
                $element['optionsButtonStyle'] = isset($element['optionsButtonStyle']) && is_string($element['optionsButtonStyle']) ? sanitize_text_field($element['optionsButtonStyle']) : '';
                $element['optionsButtonSize'] = isset($element['optionsButtonSize']) && is_string($element['optionsButtonSize']) ? sanitize_text_field($element['optionsButtonSize']) : '';
                $element['optionsButtonWidth'] = isset($element['optionsButtonWidth']) && is_string($element['optionsButtonWidth']) ? sanitize_text_field($element['optionsButtonWidth']) : '';
                $element['optionsButtonWidthCustom'] = isset($element['optionsButtonWidthCustom']) && is_string($element['optionsButtonWidthCustom']) ? sanitize_text_field($element['optionsButtonWidthCustom']) : '';
                $element['optionsButtonIconPosition'] = isset($element['optionsButtonIconPosition']) && is_string($element['optionsButtonIconPosition']) ? sanitize_text_field($element['optionsButtonIconPosition']) : 'left';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'icon';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['inArrayValidator'] = isset($element['inArrayValidator']) && is_bool($element['inArrayValidator']) ? $element['inArrayValidator'] : true;
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                break;
            case 'radio':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled', 'quform');
                $element['options'] = isset($element['options']) && is_array($element['options']) ? $this->sanitizeCheckboxRadioOptions($element['options']) : array();
                $element['nextOptionId'] = isset($element['nextOptionId']) && is_numeric($element['nextOptionId']) ? (int) $element['nextOptionId'] : 1;
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? wp_kses_post($element['defaultValue']) : '';
                $element['customiseValues'] = isset($element['customiseValues']) && is_bool($element['customiseValues']) ? $element['customiseValues'] : false;
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['optionsLayout'] = isset($element['optionsLayout']) && is_string($element['optionsLayout']) ? sanitize_text_field($element['optionsLayout']) : 'block';
                $element['optionsLayoutResponsiveColumns'] = isset($element['optionsLayoutResponsiveColumns']) && is_string($element['optionsLayoutResponsiveColumns']) ? sanitize_text_field($element['optionsLayoutResponsiveColumns']) : 'phone-landscape';
                $element['optionsLayoutResponsiveColumnsCustom'] = isset($element['optionsLayoutResponsiveColumnsCustom']) && is_string($element['optionsLayoutResponsiveColumnsCustom']) ? sanitize_text_field($element['optionsLayoutResponsiveColumnsCustom']) : '';
                $element['optionsStyle'] = isset($element['optionsStyle']) && is_string($element['optionsStyle']) ? sanitize_text_field($element['optionsStyle']) : '';
                $element['optionsButtonStyle'] = isset($element['optionsButtonStyle']) && is_string($element['optionsButtonStyle']) ? sanitize_text_field($element['optionsButtonStyle']) : '';
                $element['optionsButtonSize'] = isset($element['optionsButtonSize']) && is_string($element['optionsButtonSize']) ? sanitize_text_field($element['optionsButtonSize']) : '';
                $element['optionsButtonWidth'] = isset($element['optionsButtonWidth']) && is_string($element['optionsButtonWidth']) ? sanitize_text_field($element['optionsButtonWidth']) : '';
                $element['optionsButtonWidthCustom'] = isset($element['optionsButtonWidthCustom']) && is_string($element['optionsButtonWidthCustom']) ? sanitize_text_field($element['optionsButtonWidthCustom']) : '';
                $element['optionsButtonIconPosition'] = isset($element['optionsButtonIconPosition']) && is_string($element['optionsButtonIconPosition']) ? sanitize_text_field($element['optionsButtonIconPosition']) : 'left';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'icon';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['inArrayValidator'] = isset($element['inArrayValidator']) && is_bool($element['inArrayValidator']) ? $element['inArrayValidator'] : true;
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['submitOnChoice'] = isset($element['submitOnChoice']) && is_bool($element['submitOnChoice']) ? $element['submitOnChoice'] : false;
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                break;
            case 'multiselect':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled', 'quform');
                $element['options'] = isset($element['options']) && is_array($element['options']) ? $this->sanitizeSelectOptions($element['options']) : array();
                $element['nextOptionId'] = isset($element['nextOptionId']) && is_numeric($element['nextOptionId']) ? (int) $element['nextOptionId'] : 1;
                $element['defaultValue'] = isset($element['defaultValue']) && is_array($element['defaultValue']) ? array_map('wp_kses_post', $element['defaultValue']) : array();
                $element['customiseValues'] = isset($element['customiseValues']) && is_bool($element['customiseValues']) ? $element['customiseValues'] : false;
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['enhancedSelectEnabled'] = isset($element['enhancedSelectEnabled']) && is_bool($element['enhancedSelectEnabled']) ? $element['enhancedSelectEnabled'] : false;
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['sizeAttribute'] = isset($element['sizeAttribute']) && is_string($element['sizeAttribute']) ? sanitize_text_field($element['sizeAttribute']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'icon';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['inArrayValidator'] = isset($element['inArrayValidator']) && is_bool($element['inArrayValidator']) ? $element['inArrayValidator'] : true;
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['enhancedSelectPlaceholder'] = isset($element['enhancedSelectPlaceholder']) && is_string($element['enhancedSelectPlaceholder']) ? wp_kses_post($element['enhancedSelectPlaceholder']) : '';
                $element['enhancedSelectNoResultsFound'] = isset($element['enhancedSelectNoResultsFound']) && is_string($element['enhancedSelectNoResultsFound']) ? wp_kses_post($element['enhancedSelectNoResultsFound']) : '';
                break;
            case 'file':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['enhancedUploadEnabled'] = isset($element['enhancedUploadEnabled']) && is_bool($element['enhancedUploadEnabled']) ? $element['enhancedUploadEnabled'] : true;
                $element['enhancedUploadStyle'] = isset($element['enhancedUploadStyle']) && is_string($element['enhancedUploadStyle']) ? sanitize_text_field($element['enhancedUploadStyle']) : 'button';
                $element['buttonStyle'] = isset($element['buttonStyle']) && is_string($element['buttonStyle']) ? sanitize_text_field($element['buttonStyle']) : 'inherit';
                $element['buttonSize'] = isset($element['buttonSize']) && is_string($element['buttonSize']) ? sanitize_text_field($element['buttonSize']) : 'inherit';
                $element['buttonWidth'] = isset($element['buttonWidth']) && is_string($element['buttonWidth']) ? sanitize_text_field($element['buttonWidth']) : 'inherit';
                $element['buttonWidthCustom'] = isset($element['buttonWidthCustom']) && is_string($element['buttonWidthCustom']) ? sanitize_text_field($element['buttonWidthCustom']) : '';
                $element['buttonIcon'] = isset($element['buttonIcon']) && is_string($element['buttonIcon']) ? sanitize_text_field($element['buttonIcon']) : 'qicon-file_upload';
                $element['buttonIconPosition'] = isset($element['buttonIconPosition']) && is_string($element['buttonIconPosition']) ? sanitize_text_field($element['buttonIconPosition']) : 'right';
                $element['uploadListLayout'] = isset($element['uploadListLayout']) && is_string($element['uploadListLayout']) ? sanitize_text_field($element['uploadListLayout']) : '';
                $element['uploadListSize'] = isset($element['uploadListSize']) && is_string($element['uploadListSize']) ? sanitize_text_field($element['uploadListSize']) : '';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'icon';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['minimumNumberOfFiles'] = isset($element['minimumNumberOfFiles']) && is_string($element['minimumNumberOfFiles']) && is_numeric($element['minimumNumberOfFiles']) ? (string) (int) $element['minimumNumberOfFiles'] : '0';
                $element['maximumNumberOfFiles'] = isset($element['maximumNumberOfFiles']) && is_string($element['maximumNumberOfFiles']) && is_numeric($element['maximumNumberOfFiles']) ? (string) (int) $element['maximumNumberOfFiles'] : '1';
                $element['allowedExtensions'] = isset($element['allowedExtensions']) && is_string($element['allowedExtensions']) ? sanitize_text_field($element['allowedExtensions']) : 'jpg, jpeg, png, gif';
                $element['maximumFileSize'] = isset($element['maximumFileSize']) && is_string($element['maximumFileSize']) && is_numeric($element['maximumFileSize']) ? sanitize_text_field($element['maximumFileSize']) : '10';
                $element['saveToServer'] = isset($element['saveToServer']) && is_bool($element['saveToServer']) ? $element['saveToServer'] : true;
                $element['savePath'] = isset($element['savePath']) && is_string($element['savePath']) ? sanitize_text_field($element['savePath']) : 'quform/{form_id}/{year}/{month}/';
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';

                // Translations
                $element['browseText'] = isset($element['browseText']) && is_string($element['browseText']) ? wp_kses_post($element['browseText']) : '';
                $element['messageFileUploadRequired'] = isset($element['messageFileUploadRequired']) && is_string($element['messageFileUploadRequired']) ? wp_kses_post($element['messageFileUploadRequired']) : '';
                $element['messageFileNumRequired'] = isset($element['messageFileNumRequired']) && is_string($element['messageFileNumRequired']) ? wp_kses_post($element['messageFileNumRequired']) : '';
                $element['messageFileTooMany'] = isset($element['messageFileTooMany']) && is_string($element['messageFileTooMany']) ? wp_kses_post($element['messageFileTooMany']) : '';
                $element['messageFileTooBigFilename'] = isset($element['messageFileTooBigFilename']) && is_string($element['messageFileTooBigFilename']) ? wp_kses_post($element['messageFileTooBigFilename']) : '';
                $element['messageFileTooBig'] = isset($element['messageFileTooBig']) && is_string($element['messageFileTooBig']) ? wp_kses_post($element['messageFileTooBig']) : '';
                $element['messageNotAllowedTypeFilename'] = isset($element['messageNotAllowedTypeFilename']) && is_string($element['messageNotAllowedTypeFilename']) ? wp_kses_post($element['messageNotAllowedTypeFilename']) : '';
                $element['messageNotAllowedType'] = isset($element['messageNotAllowedType']) && is_string($element['messageNotAllowedType']) ? wp_kses_post($element['messageNotAllowedType']) : '';
                break;
            case 'captcha':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Please type the characters', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : __('This helps us prevent spam, thank you.', 'quform');
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldIconLeft'] = isset($element['fieldIconLeft']) && is_string($element['fieldIconLeft']) ? sanitize_text_field($element['fieldIconLeft']) : '';
                $element['fieldIconRight'] = isset($element['fieldIconRight']) && is_string($element['fieldIconRight']) ? sanitize_text_field($element['fieldIconRight']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['captchaLength'] = isset($element['captchaLength']) && is_numeric($element['captchaLength']) ? (string) Quform::clamp((int) $element['captchaLength'], 2, 32) : '5';
                $element['captchaWidth'] = isset($element['captchaWidth']) && is_numeric($element['captchaWidth']) ? (string) Quform::clamp((int) $element['captchaWidth'], 20, 300) : '115';
                $element['captchaHeight'] = isset($element['captchaHeight']) && is_numeric($element['captchaHeight']) ? (string) Quform::clamp((int) $element['captchaHeight'], 10, 300) : '40';
                $element['captchaBgColor'] = isset($element['captchaBgColor']) && Quform::isNonEmptyString($element['captchaBgColor']) ? sanitize_text_field($element['captchaBgColor']) : '#FFFFFF';
                $element['captchaBgColorRgba'] = is_array($element['captchaBgColorRgba']) ? $this->sanitizeRgbColorArray($element['captchaBgColorRgba']) : array('r' => 255, 'g' => 255, 'b' => 255);
                $element['captchaTextColor'] = isset($element['captchaTextColor']) && Quform::isNonEmptyString($element['captchaTextColor']) ? sanitize_text_field($element['captchaTextColor']) : '#222222';
                $element['captchaTextColorRgba'] = is_array($element['captchaTextColorRgba']) ? $this->sanitizeRgbColorArray($element['captchaTextColorRgba']) : array('r' => 34, 'g' => 34, 'b' => 34);
                $element['captchaFont'] = isset($element['captchaFont']) && Quform::isNonEmptyString($element['captchaFont']) ? sanitize_text_field($element['captchaFont']) : 'Typist.ttf';
                $element['captchaMinFontSize'] = isset($element['captchaMinFontSize']) && is_numeric($element['captchaMinFontSize']) ? (string) Quform::clamp((int) $element['captchaMinFontSize'], 5, 72) : '12';
                $element['captchaMaxFontSize'] = isset($element['captchaMaxFontSize']) && is_numeric($element['captchaMaxFontSize']) ? (string) Quform::clamp((int) $element['captchaMaxFontSize'], 5, 72) : '19';
                $element['captchaMinAngle'] = isset($element['captchaMinAngle']) && is_numeric($element['captchaMinAngle']) ? (string) Quform::clamp((int) $element['captchaMinAngle'], 0, 360) : '0';
                $element['captchaMaxAngle'] = isset($element['captchaMaxAngle']) && is_numeric($element['captchaMaxAngle']) ? (string) Quform::clamp((int) $element['captchaMaxAngle'], 0, 360) : '20';
                $element['captchaRetina'] = isset($element['captchaRetina']) ? (bool) $element['captchaRetina'] : true;

                // If any minimums are greater than maximums, swap them around
                if ($element['captchaMinFontSize'] > $element['captchaMaxFontSize']) {
                    $tmp = $element['captchaMaxFontSize'];
                    $element['captchaMaxFontSize'] = $element['captchaMinFontSize'];
                    $element['captchaMinFontSize'] = $tmp;
                }

                if ($element['captchaMinAngle'] > $element['captchaMaxAngle']) {
                    $tmp = $element['captchaMaxAngle'];
                    $element['captchaMaxAngle'] = $element['captchaMinAngle'];
                    $element['captchaMinAngle'] = $tmp;
                }

                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['placeholder'] = isset($element['placeholder']) && is_string($element['placeholder']) ? sanitize_text_field($element['placeholder']) : '';
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageCaptchaNotMatch'] = isset($element['messageCaptchaNotMatch']) && is_string($element['messageCaptchaNotMatch']) ? wp_kses_post($element['messageCaptchaNotMatch']) : '';
                break;
            case 'recaptcha':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Are you human?', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['provider'] = isset($element['provider']) && is_string($element['provider']) ? sanitize_key($element['provider']) : 'recaptcha';
                $element['recaptchaVersion'] = isset($element['recaptchaVersion']) && is_string($element['recaptchaVersion']) ? sanitize_key($element['recaptchaVersion']) : 'v2';
                $element['recaptchaSize'] = isset($element['recaptchaSize']) && is_string($element['recaptchaSize']) ? sanitize_text_field($element['recaptchaSize']) : 'normal';
                $element['recaptchaType'] = isset($element['recaptchaType']) && is_string($element['recaptchaType']) ? sanitize_text_field($element['recaptchaType']) : 'image';
                $element['recaptchaTheme'] = isset($element['recaptchaTheme']) && is_string($element['recaptchaTheme']) ? sanitize_text_field($element['recaptchaTheme']) : 'light';
                $element['recaptchaBadge'] = isset($element['recaptchaBadge']) && is_string($element['recaptchaBadge']) ? sanitize_text_field($element['recaptchaBadge']) : 'bottomright';
                $element['recaptchaLang'] = isset($element['recaptchaLang']) && is_string($element['recaptchaLang']) ? sanitize_text_field($element['recaptchaLang']) : '';
                $element['hcaptchaLang'] = isset($element['hcaptchaLang']) && is_string($element['hcaptchaLang']) ? sanitize_text_field($element['hcaptchaLang']) : '';
                $element['turnstileLang'] = isset($element['turnstileLang']) && is_string($element['turnstileLang']) ? sanitize_text_field($element['turnstileLang']) : '';
                $element['recaptchaThreshold'] = isset($element['recaptchaThreshold']) && is_numeric($element['recaptchaThreshold']) ? (string) Quform::clamp((float) $element['recaptchaThreshold'], 0, 1) : '0.5';

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'icon';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageRecaptchaMissingInputSecret'] = isset($element['messageRecaptchaMissingInputSecret']) && is_string($element['messageRecaptchaMissingInputSecret']) ? wp_kses_post($element['messageRecaptchaMissingInputSecret']) : '';
                $element['messageRecaptchaInvalidInputSecret'] = isset($element['messageRecaptchaInvalidInputSecret']) && is_string($element['messageRecaptchaInvalidInputSecret']) ? wp_kses_post($element['messageRecaptchaInvalidInputSecret']) : '';
                $element['messageRecaptchaMissingInputResponse'] = isset($element['messageRecaptchaMissingInputResponse']) && is_string($element['messageRecaptchaMissingInputResponse']) ? wp_kses_post($element['messageRecaptchaMissingInputResponse']) : '';
                $element['messageRecaptchaInvalidInputResponse'] = isset($element['messageRecaptchaInvalidInputResponse']) && is_string($element['messageRecaptchaInvalidInputResponse']) ? wp_kses_post($element['messageRecaptchaInvalidInputResponse']) : '';
                $element['messageRecaptchaError'] = isset($element['messageRecaptchaError']) && is_string($element['messageRecaptchaError']) ? wp_kses_post($element['messageRecaptchaError']) : '';
                $element['messageRecaptchaScoreTooLow'] = isset($element['messageRecaptchaScoreTooLow']) && is_string($element['messageRecaptchaScoreTooLow']) ? wp_kses_post($element['messageRecaptchaScoreTooLow']) : '';
                break;
            case 'html':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('HTML', 'quform');
                $element['content'] = isset($element['content']) && is_string($element['content']) ? $this->sanitizeHtml($element['content']) : '';
                $element['autoFormat'] = isset($element['autoFormat']) && is_bool($element['autoFormat']) ? $element['autoFormat'] : false;

                // Styles
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : false;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : false;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                break;
            case 'date':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Date', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldIconLeft'] = isset($element['fieldIconLeft']) && is_string($element['fieldIconLeft']) ? sanitize_text_field($element['fieldIconLeft']) : '';
                $element['fieldIconRight'] = isset($element['fieldIconRight']) && is_string($element['fieldIconRight']) ? sanitize_text_field($element['fieldIconRight']) : 'qicon-calendar';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['placeholder'] = isset($element['placeholder']) && is_string($element['placeholder']) ? sanitize_text_field($element['placeholder']) : '';
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? sanitize_text_field($element['defaultValue']) : '';
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['dateMin'] = isset($element['dateMin']) && is_string($element['dateMin']) ? sanitize_text_field($element['dateMin']) : '';
                $element['dateMax'] = isset($element['dateMax']) && is_string($element['dateMax']) ? sanitize_text_field($element['dateMax']) : '';
                $element['dateViewStart'] = isset($element['dateViewStart']) && is_string($element['dateViewStart']) ? sanitize_text_field($element['dateViewStart']) : 'month';
                $element['dateViewDepth'] = isset($element['dateViewDepth']) && is_string($element['dateViewDepth']) ? sanitize_text_field($element['dateViewDepth']) : 'month';
                $element['dateShowFooter'] = isset($element['dateShowFooter']) && is_bool($element['dateShowFooter']) ? $element['dateShowFooter'] : false;
                $element['dateLocale'] = isset($element['dateLocale']) && is_string($element['dateLocale']) ? sanitize_text_field($element['dateLocale']) : '';
                $element['dateFormatJs'] = isset($element['dateFormatJs']) && is_string($element['dateFormatJs']) ? sanitize_text_field($element['dateFormatJs']) : '';
                $element['dateFormat'] = isset($element['dateFormat']) && is_string($element['dateFormat']) ? sanitize_text_field($element['dateFormat']) : '';
                $element['dateAutoOpen'] = isset($element['dateAutoOpen']) && is_bool($element['dateAutoOpen']) ? $element['dateAutoOpen'] : false;
                $element['readOnly'] = isset($element['readOnly']) && is_bool($element['readOnly']) ? $element['readOnly'] : false;
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageDateInvalidDate'] = isset($element['messageDateInvalidDate']) && is_string($element['messageDateInvalidDate']) ? wp_kses_post($element['messageDateInvalidDate']) : '';
                $element['messageDateTooEarly'] = isset($element['messageDateTooEarly']) && is_string($element['messageDateTooEarly']) ? wp_kses_post($element['messageDateTooEarly']) : '';
                $element['messageDateTooLate'] = isset($element['messageDateTooLate']) && is_string($element['messageDateTooLate']) ? wp_kses_post($element['messageDateTooLate']) : '';
                break;
            case 'time':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Time', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldIconLeft'] = isset($element['fieldIconLeft']) && is_string($element['fieldIconLeft']) ? sanitize_text_field($element['fieldIconLeft']) : '';
                $element['fieldIconRight'] = isset($element['fieldIconRight']) && is_string($element['fieldIconRight']) ? sanitize_text_field($element['fieldIconRight']) : 'qicon-schedule';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['placeholder'] = isset($element['placeholder']) && is_string($element['placeholder']) ? sanitize_text_field($element['placeholder']) : '';
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? sanitize_text_field($element['defaultValue']) : '';
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['timeMin'] = isset($element['timeMin']) && is_string($element['timeMin']) ? sanitize_text_field($element['timeMin']) : '';
                $element['timeMax'] = isset($element['timeMax']) && is_string($element['timeMax']) ? sanitize_text_field($element['timeMax']) : '';
                $element['timeInterval'] = isset($element['timeInterval']) && is_numeric($element['timeInterval']) ? (string) Quform::clamp((int) $element['timeInterval'], 1, 60) : '';
                $element['timeLocale'] = isset($element['timeLocale']) && is_string($element['timeLocale']) ? sanitize_text_field($element['timeLocale']) : '';
                $element['timeFormatJs'] = isset($element['timeFormatJs']) && is_string($element['timeFormatJs']) ? sanitize_text_field($element['timeFormatJs']) : '';
                $element['timeFormat'] = isset($element['timeFormat']) && is_string($element['timeFormat']) ? sanitize_text_field($element['timeFormat']) : '';
                $element['timeAutoOpen'] = isset($element['timeAutoOpen']) && is_bool($element['timeAutoOpen']) ? $element['timeAutoOpen'] : false;
                $element['readOnly'] = isset($element['readOnly']) && is_bool($element['readOnly']) ? $element['readOnly'] : false;
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageTimeInvalidTime'] = isset($element['messageTimeInvalidTime']) && is_string($element['messageTimeInvalidTime']) ? wp_kses_post($element['messageTimeInvalidTime']) : '';
                $element['messageTimeTooEarly'] = isset($element['messageTimeTooEarly']) && is_string($element['messageTimeTooEarly']) ? wp_kses_post($element['messageTimeTooEarly']) : '';
                $element['messageTimeTooLate'] = isset($element['messageTimeTooLate']) && is_string($element['messageTimeTooLate']) ? wp_kses_post($element['messageTimeTooLate']) : '';
                break;
            case 'password':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Password', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['required'] = isset($element['required']) && is_bool($element['required']) ? $element['required'] : false;

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldIconLeft'] = isset($element['fieldIconLeft']) && is_string($element['fieldIconLeft']) ? sanitize_text_field($element['fieldIconLeft']) : '';
                $element['fieldIconRight'] = isset($element['fieldIconRight']) && is_string($element['fieldIconRight']) ? sanitize_text_field($element['fieldIconRight']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['placeholder'] = isset($element['placeholder']) && is_string($element['placeholder']) ? sanitize_text_field($element['placeholder']) : '';
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['autocomplete'] = isset($element['autocomplete']) && is_string($element['autocomplete']) ? sanitize_text_field($element['autocomplete']) : '';
                $element['maxLength'] = isset($element['maxLength']) && is_numeric($element['maxLength']) ? (string) (int) $element['maxLength'] : '';

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                $element['messageLengthTooLong'] = isset($element['messageLengthTooLong']) && is_string($element['messageLengthTooLong']) ? wp_kses_post($element['messageLengthTooLong']) : '';
                break;
            case 'hidden':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Hidden', 'quform');

                // Styles
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';

                // Data
                $element['defaultValue'] = isset($element['defaultValue']) && is_string($element['defaultValue']) ? sanitize_text_field($element['defaultValue']) : '';
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                break;
            case 'name':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Name', 'quform');
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';
                $element['descriptionAbove'] = isset($element['descriptionAbove']) && is_string($element['descriptionAbove']) ? wp_kses_post($element['descriptionAbove']) : '';
                $element['prefixEnabled'] = isset($element['prefixEnabled']) && is_bool($element['prefixEnabled']) ? $element['prefixEnabled'] : false;
                $element['prefixRequired'] = isset($element['prefixRequired']) && is_bool($element['prefixRequired']) ? $element['prefixRequired'] : false;
                $element['prefixOptions'] = isset($element['prefixOptions']) && is_array($element['prefixOptions']) ? $this->sanitizeSelectOptions($element['prefixOptions']) : array();
                $element['prefixNextOptionId'] = isset($element['prefixNextOptionId']) && is_numeric($element['prefixNextOptionId']) ? (int) $element['prefixNextOptionId'] : 1;
                $element['prefixDefaultValue'] = isset($element['prefixDefaultValue']) && is_string($element['prefixDefaultValue']) ? wp_kses_post($element['prefixDefaultValue']) : '';
                $element['prefixCustomiseValues'] = isset($element['prefixCustomiseValues']) && is_bool($element['prefixCustomiseValues']) ? $element['prefixCustomiseValues'] : false;
                $element['prefixNoneOption'] = isset($element['prefixNoneOption']) && is_bool($element['prefixNoneOption']) ? $element['prefixNoneOption'] : true;
                $element['prefixSubLabel'] = isset($element['prefixSubLabel']) && is_string($element['prefixSubLabel']) ? wp_kses_post($element['prefixSubLabel']) : __('Prefix', 'quform');
                $element['prefixSubLabelAbove'] = isset($element['prefixSubLabelAbove']) && is_string($element['prefixSubLabelAbove']) ? wp_kses_post($element['prefixSubLabelAbove']) : '';
                $element['prefixCustomClass'] = isset($element['prefixCustomClass']) && is_string($element['prefixCustomClass']) ? Quform::sanitizeClass($element['prefixCustomClass']) : '';
                $element['prefixCustomElementClass'] = isset($element['prefixCustomElementClass']) && is_string($element['prefixCustomElementClass']) ? Quform::sanitizeClass($element['prefixCustomElementClass']) : '';
                $element['firstEnabled'] = isset($element['firstEnabled']) && is_bool($element['firstEnabled']) ? $element['firstEnabled'] : true;
                $element['firstRequired'] = isset($element['firstRequired']) && is_bool($element['firstRequired']) ? $element['firstRequired'] : false;
                $element['firstPlaceholder'] = isset($element['firstPlaceholder']) && is_string($element['firstPlaceholder']) ? sanitize_text_field($element['firstPlaceholder']) : '';
                $element['firstSubLabel'] = isset($element['firstSubLabel']) && is_string($element['firstSubLabel']) ? wp_kses_post($element['firstSubLabel']) : __('First', 'quform');
                $element['firstSubLabelAbove'] = isset($element['firstSubLabelAbove']) && is_string($element['firstSubLabelAbove']) ? wp_kses_post($element['firstSubLabelAbove']) : '';
                $element['firstDefaultValue'] = isset($element['firstDefaultValue']) && is_string($element['firstDefaultValue']) ? sanitize_text_field($element['firstDefaultValue']) : '';
                $element['firstCustomClass'] = isset($element['firstCustomClass']) && is_string($element['firstCustomClass']) ? Quform::sanitizeClass($element['firstCustomClass']) : '';
                $element['firstCustomElementClass'] = isset($element['firstCustomElementClass']) && is_string($element['firstCustomElementClass']) ? Quform::sanitizeClass($element['firstCustomElementClass']) : '';
                $element['firstAutocomplete'] = isset($element['firstAutocomplete']) && is_string($element['firstAutocomplete']) ? sanitize_text_field($element['firstAutocomplete']) : '';
                $element['middleEnabled'] = isset($element['middleEnabled']) && is_bool($element['middleEnabled']) ? $element['middleEnabled'] : false;
                $element['middleRequired'] = isset($element['middleRequired']) && is_bool($element['middleRequired']) ? $element['middleRequired'] : false;
                $element['middlePlaceholder'] = isset($element['middlePlaceholder']) && is_string($element['middlePlaceholder']) ? sanitize_text_field($element['middlePlaceholder']) : '';
                $element['middleSubLabel'] = isset($element['middleSubLabel']) && is_string($element['middleSubLabel']) ? wp_kses_post($element['middleSubLabel']) : __('Middle', 'quform');
                $element['middleSubLabelAbove'] = isset($element['middleSubLabelAbove']) && is_string($element['middleSubLabelAbove']) ? wp_kses_post($element['middleSubLabelAbove']) : '';
                $element['middleDefaultValue'] = isset($element['middleDefaultValue']) && is_string($element['middleDefaultValue']) ? sanitize_text_field($element['middleDefaultValue']) : '';
                $element['middleCustomClass'] = isset($element['middleCustomClass']) && is_string($element['middleCustomClass']) ? Quform::sanitizeClass($element['middleCustomClass']) : '';
                $element['middleCustomElementClass'] = isset($element['middleCustomElementClass']) && is_string($element['middleCustomElementClass']) ? Quform::sanitizeClass($element['middleCustomElementClass']) : '';
                $element['middleAutocomplete'] = isset($element['middleAutocomplete']) && is_string($element['middleAutocomplete']) ? sanitize_text_field($element['middleAutocomplete']) : '';
                $element['lastEnabled'] = isset($element['lastEnabled']) && is_bool($element['lastEnabled']) ? $element['lastEnabled'] : true;
                $element['lastRequired'] = isset($element['lastRequired']) && is_bool($element['lastRequired']) ? $element['lastRequired'] : false;
                $element['lastPlaceholder'] = isset($element['lastPlaceholder']) && is_string($element['lastPlaceholder']) ? sanitize_text_field($element['lastPlaceholder']) : '';
                $element['lastSubLabel'] = isset($element['lastSubLabel']) && is_string($element['lastSubLabel']) ? wp_kses_post($element['lastSubLabel']) : __('Last', 'quform');
                $element['lastSubLabelAbove'] = isset($element['lastSubLabelAbove']) && is_string($element['lastSubLabelAbove']) ? wp_kses_post($element['lastSubLabelAbove']) : '';
                $element['lastDefaultValue'] = isset($element['lastDefaultValue']) && is_string($element['lastDefaultValue']) ? sanitize_text_field($element['lastDefaultValue']) : '';
                $element['lastCustomClass'] = isset($element['lastCustomClass']) && is_string($element['lastCustomClass']) ? Quform::sanitizeClass($element['lastCustomClass']) : '';
                $element['lastCustomElementClass'] = isset($element['lastCustomElementClass']) && is_string($element['lastCustomElementClass']) ? Quform::sanitizeClass($element['lastCustomElementClass']) : '';
                $element['lastAutocomplete'] = isset($element['lastAutocomplete']) && is_string($element['lastAutocomplete']) ? sanitize_text_field($element['lastAutocomplete']) : '';
                $element['suffixEnabled'] = isset($element['suffixEnabled']) && is_bool($element['suffixEnabled']) ? $element['suffixEnabled'] : false;
                $element['suffixRequired'] = isset($element['suffixRequired']) && is_bool($element['suffixRequired']) ? $element['suffixRequired'] : false;
                $element['suffixPlaceholder'] = isset($element['suffixPlaceholder']) && is_string($element['suffixPlaceholder']) ? sanitize_text_field($element['suffixPlaceholder']) : '';
                $element['suffixSubLabel'] = isset($element['suffixSubLabel']) && is_string($element['suffixSubLabel']) ? wp_kses_post($element['suffixSubLabel']) : __('Suffix', 'quform');
                $element['suffixSubLabelAbove'] = isset($element['suffixSubLabelAbove']) && is_string($element['suffixSubLabelAbove']) ? wp_kses_post($element['suffixSubLabelAbove']) : '';
                $element['suffixDefaultValue'] = isset($element['suffixDefaultValue']) && is_string($element['suffixDefaultValue']) ? sanitize_text_field($element['suffixDefaultValue']) : '';
                $element['suffixCustomClass'] = isset($element['suffixCustomClass']) && is_string($element['suffixCustomClass']) ? Quform::sanitizeClass($element['suffixCustomClass']) : '';
                $element['suffixCustomElementClass'] = isset($element['suffixCustomElementClass']) && is_string($element['suffixCustomElementClass']) ? Quform::sanitizeClass($element['suffixCustomElementClass']) : '';

                // Styles
                $element['labelIcon'] = isset($element['labelIcon']) && is_string($element['labelIcon']) ? sanitize_text_field($element['labelIcon']) : '';
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['responsiveColumns'] = isset($element['responsiveColumns']) && is_string($element['responsiveColumns']) ? sanitize_text_field($element['responsiveColumns']) : 'inherit';
                $element['responsiveColumnsCustom'] = isset($element['responsiveColumnsCustom']) && is_string($element['responsiveColumnsCustom']) ? sanitize_text_field($element['responsiveColumnsCustom']) : '';
                $element['customClass'] = isset($element['customClass']) && is_string($element['customClass']) ? Quform::sanitizeClass($element['customClass']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['subLabel'] = isset($element['subLabel']) && is_string($element['subLabel']) ? wp_kses_post($element['subLabel']) : '';
                $element['subLabelAbove'] = isset($element['subLabelAbove']) && is_string($element['subLabelAbove']) ? wp_kses_post($element['subLabelAbove']) : '';
                $element['adminLabel'] = isset($element['adminLabel']) && is_string($element['adminLabel']) ? wp_kses_post($element['adminLabel']) : '';
                $element['tooltip'] = isset($element['tooltip']) && is_string($element['tooltip']) ? wp_kses_post($element['tooltip']) : '';
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'icon';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                // Data
                $element['dynamicDefaultValue'] = isset($element['dynamicDefaultValue']) && is_bool($element['dynamicDefaultValue']) ? $element['dynamicDefaultValue'] : false;
                $element['dynamicKey'] = isset($element['dynamicKey']) && is_string($element['dynamicKey']) ? sanitize_text_field($element['dynamicKey']) : '';
                $element['showInEmail'] = isset($element['showInEmail']) && is_bool($element['showInEmail']) ? $element['showInEmail'] : true;
                $element['saveToDatabase'] = isset($element['saveToDatabase']) && is_bool($element['saveToDatabase']) ? $element['saveToDatabase'] : true;

                // Advanced
                $element['visibility'] = isset($element['visibility']) && is_string($element['visibility']) ? sanitize_text_field($element['visibility']) : '';
                $element['validators'] = isset($element['validators']) && is_array($element['validators']) ? $this->sanitizeValidators($element['validators'], $element['type']) : array();

                // Translations
                $element['prefixNoneOptionText'] = isset($element['prefixNoneOptionText']) && is_string($element['prefixNoneOptionText']) ? wp_kses_post($element['prefixNoneOptionText']) : '';
                $element['messageRequired'] = isset($element['messageRequired']) && is_string($element['messageRequired']) ? wp_kses_post($element['messageRequired']) : '';
                break;
            case 'group':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Untitled group', 'quform');
                $element['title'] = isset($element['title']) && is_string($element['title']) ? wp_kses_post($element['title']) : '';
                $element['titleTag'] = isset($element['titleTag']) && is_string($element['titleTag']) ? sanitize_text_field($element['titleTag']) : 'h4';
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';

                // Styles
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['groupStyle'] = isset($element['groupStyle']) && is_string($element['groupStyle']) ? sanitize_text_field($element['groupStyle']) : 'plain';
                $element['borderColor'] = isset($element['borderColor']) && is_string($element['borderColor']) ? sanitize_text_field($element['borderColor']) : '';
                $element['backgroundColor'] = isset($element['backgroundColor']) && is_string($element['backgroundColor']) ? sanitize_text_field($element['backgroundColor']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';
                $element['showLabelInEmail'] = isset($element['showLabelInEmail']) && is_bool($element['showLabelInEmail']) ? $element['showLabelInEmail'] : false;
                $element['showLabelInEntry'] = isset($element['showLabelInEntry']) && is_bool($element['showLabelInEntry']) ? $element['showLabelInEntry'] : false;

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                if ( ! isset($element['elements']) || ! is_array($element['elements'])) {
                    $element['elements'] = array();
                }
                break;
            case 'page':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : '';
                $element['title'] = isset($element['title']) && is_string($element['title']) ? wp_kses_post($element['title']) : '';
                $element['titleTag'] = isset($element['titleTag']) && is_string($element['titleTag']) ? sanitize_text_field($element['titleTag']) : 'h3';
                $element['description'] = isset($element['description']) && is_string($element['description']) ? wp_kses_post($element['description']) : '';

                // Styles
                $element['fieldSize'] = isset($element['fieldSize']) && is_string($element['fieldSize']) ? sanitize_text_field($element['fieldSize']) : 'inherit';
                $element['fieldWidth'] = isset($element['fieldWidth']) && is_string($element['fieldWidth']) ? sanitize_text_field($element['fieldWidth']) : 'inherit';
                $element['fieldWidthCustom'] = isset($element['fieldWidthCustom']) && is_string($element['fieldWidthCustom']) ? sanitize_text_field($element['fieldWidthCustom']) : '';
                $element['groupStyle'] = isset($element['groupStyle']) && is_string($element['groupStyle']) ? sanitize_text_field($element['groupStyle']) : 'plain';
                $element['borderColor'] = isset($element['borderColor']) && is_string($element['borderColor']) ? sanitize_text_field($element['borderColor']) : '';
                $element['backgroundColor'] = isset($element['backgroundColor']) && is_string($element['backgroundColor']) ? sanitize_text_field($element['backgroundColor']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Labels
                $element['tooltipType'] = isset($element['tooltipType']) && is_string($element['tooltipType']) ? sanitize_text_field($element['tooltipType']) : 'inherit';
                $element['tooltipEvent'] = isset($element['tooltipEvent']) && is_string($element['tooltipEvent']) ? sanitize_text_field($element['tooltipEvent']) : 'inherit';
                $element['labelPosition'] = isset($element['labelPosition']) && is_string($element['labelPosition']) ? sanitize_text_field($element['labelPosition']) : 'inherit';
                $element['labelWidth'] = isset($element['labelWidth']) && is_string($element['labelWidth']) ? sanitize_text_field($element['labelWidth']) : '';
                $element['showLabelInEmail'] = isset($element['showLabelInEmail']) && is_bool($element['showLabelInEmail']) ? $element['showLabelInEmail'] : false;
                $element['showLabelInEntry'] = isset($element['showLabelInEntry']) && is_bool($element['showLabelInEntry']) ? $element['showLabelInEntry'] : false;

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();

                if ( ! isset($element['elements']) || ! is_array($element['elements'])) {
                    $element['elements'] = array();
                }
                break;
            case 'submit':
                // Basic
                $element['label'] = isset($element['label']) && is_string($element['label']) ? wp_kses_post($element['label']) : __('Submit', 'quform');
                $element['submitType'] = isset($element['submitType']) && is_string($element['submitType']) ? sanitize_text_field($element['submitType']) : 'inherit';
                $element['submitText'] = isset($element['submitText']) && is_string($element['submitText']) ? sanitize_text_field($element['submitText']) : '';
                $element['submitIcon'] = isset($element['submitIcon']) && is_string($element['submitIcon']) ? sanitize_text_field($element['submitIcon']) : '';
                $element['submitIconPosition'] = isset($element['submitIconPosition']) && is_string($element['submitIconPosition']) ? sanitize_text_field($element['submitIconPosition']) : 'inherit';
                $element['submitImage'] = isset($element['submitImage']) && is_string($element['submitImage']) ? esc_url_raw($element['submitImage']) : '';
                $element['submitHtml'] = isset($element['submitHtml']) && is_string($element['submitHtml']) ? wp_kses_post($element['submitHtml']) : '';
                $element['nextType'] = isset($element['nextType']) && is_string($element['nextType']) ? sanitize_text_field($element['nextType']) : 'inherit';
                $element['nextText'] = isset($element['nextText']) && is_string($element['nextText']) ? sanitize_text_field($element['nextText']) : '';
                $element['nextIcon'] = isset($element['nextIcon']) && is_string($element['nextIcon']) ? sanitize_text_field($element['nextIcon']) : '';
                $element['nextIconPosition'] = isset($element['nextIconPosition']) && is_string($element['nextIconPosition']) ? sanitize_text_field($element['nextIconPosition']) : 'inherit';
                $element['nextImage'] = isset($element['nextImage']) && is_string($element['nextImage']) ? esc_url_raw($element['nextImage']) : '';
                $element['nextHtml'] = isset($element['nextHtml']) && is_string($element['nextHtml']) ? wp_kses_post($element['nextHtml']) : '';
                $element['backType'] = isset($element['backType']) && is_string($element['backType']) ? sanitize_text_field($element['backType']) : 'inherit';
                $element['backText'] = isset($element['backText']) && is_string($element['backText']) ? sanitize_text_field($element['backText']) : '';
                $element['backIcon'] = isset($element['backIcon']) && is_string($element['backIcon']) ? sanitize_text_field($element['backIcon']) : '';
                $element['backIconPosition'] = isset($element['backIconPosition']) && is_string($element['backIconPosition']) ? sanitize_text_field($element['backIconPosition']) : 'inherit';
                $element['backImage'] = isset($element['backImage']) && is_string($element['backImage']) ? esc_url_raw($element['backImage']) : '';
                $element['backHtml'] = isset($element['backHtml']) && is_string($element['backHtml']) ? wp_kses_post($element['backHtml']) : '';

                // Styles
                $element['buttonStyle'] = isset($element['buttonStyle']) && is_string($element['buttonStyle']) ? sanitize_text_field($element['buttonStyle']) : 'inherit';
                $element['buttonSize'] = isset($element['buttonSize']) && is_string($element['buttonSize']) ? sanitize_text_field($element['buttonSize']) : 'inherit';
                $element['buttonWidth'] = isset($element['buttonWidth']) && is_string($element['buttonWidth']) ? sanitize_text_field($element['buttonWidth']) : 'inherit';
                $element['buttonWidthCustom'] = isset($element['buttonWidthCustom']) && is_string($element['buttonWidthCustom']) ? sanitize_text_field($element['buttonWidthCustom']) : '';
                $element['customElementClass'] = isset($element['customElementClass']) && is_string($element['customElementClass']) ? Quform::sanitizeClass($element['customElementClass']) : '';
                $element['styles'] = isset($element['styles']) && is_array($element['styles']) ? $this->sanitizeStyles($element['styles'], $element['type']) : array();

                // Logic
                $element['logicEnabled'] = isset($element['logicEnabled']) && is_bool($element['logicEnabled']) ? $element['logicEnabled'] : false;
                $element['logicAction'] = isset($element['logicAction']) && is_bool($element['logicAction']) ? $element['logicAction'] : true;
                $element['logicMatch'] = isset($element['logicMatch']) && is_string($element['logicMatch']) ? sanitize_text_field($element['logicMatch']) : 'all';
                $element['logicRules'] = isset($element['logicRules']) && is_array($element['logicRules']) ? $this->sanitizeLogicRules($element['logicRules']) : array();
                break;
            case 'row':
                $element['columnSize'] = isset($element['columnSize']) && is_string($element['columnSize']) ? sanitize_text_field($element['columnSize']) : 'fixed';
                $element['responsiveColumns'] = isset($element['responsiveColumns']) && is_string($element['responsiveColumns']) ? sanitize_text_field($element['responsiveColumns']) : 'inherit';
                $element['responsiveColumnsCustom'] = isset($element['responsiveColumnsCustom']) && is_string($element['responsiveColumnsCustom']) ? sanitize_text_field($element['responsiveColumnsCustom']) : '';

                if ( ! isset($element['elements']) || ! is_array($element['elements'])) {
                    $element['elements'] = array();
                }
                break;
            case 'column':
                $element['width'] = isset($element['width']) && is_string($element['width']) && is_numeric($element['width']) ? (string) (float) $element['width'] : '';

                if ( ! isset($element['elements']) || ! is_array($element['elements'])) {
                    $element['elements'] = array();
                }
                break;
        }

        return $element;
    }

    /**
     * Sanitize the HTML in the given string
     *
     * @param   string  $value
     * @return  string
     */
    protected function sanitizeHtml($value)
    {
        return current_user_can('unfiltered_html') ? $value : wp_kses_post($value);
    }

    /**
     * Sanitize the settings for the given styles
     *
     * @param   array   $styles
     * @param   string  $elementType
     * @return  array
     */
    protected function sanitizeStyles(array $styles, $elementType)
    {
        $allStyles = $this->getStyles();
        $visibleStyles = $this->getVisibleStyles();
        $sanitizedStyles = array();

        foreach ($styles as $key => $style) {
            if ( ! isset($style['type']) ||
                 ! is_string($style['type']) ||
                 ! array_key_exists($style['type'], $allStyles) ||
                 ! array_key_exists($elementType, $visibleStyles) ||
                 ! in_array($style['type'], $visibleStyles[$elementType], true)
            ) {
                continue;
            }

            $style['css'] = isset($style['css']) && is_string($style['css']) ? wp_strip_all_tags($style['css']) : '';

            $sanitizedStyles[] = $style;
        }

        return $sanitizedStyles;
    }

    /**
     * Sanitize the settings for the given logic rules
     *
     * @param   array  $rules  The logic rules to sanitize
     * @return  array          The sanitized logic rules
     */
    protected function sanitizeLogicRules(array $rules)
    {
        foreach ($rules as $key => $rule) {
            $rules[$key]['elementId'] = isset($rule['elementId']) && is_numeric($rule['elementId']) ? (string) (int) $rule['elementId'] : '';
            $rules[$key]['operator'] = isset($rule['operator']) && is_string($rule['operator']) ? sanitize_text_field($rule['operator']) : 'eq';
            $rules[$key]['optionId'] = isset($rule['optionId']) && is_numeric($rule['optionId']) ? (string) (int) $rule['optionId'] : null;
            $rules[$key]['value'] = isset($rule['value']) && is_string($rule['value']) ? wp_kses_no_null($rule['value'], array('slash_zero' => 'keep')) : '';
        }

        return $rules;
    }

    /**
     * Sanitize the settings for the given filters
     *
     * @param   array   $filters      The filters to sanitize
     * @param   string  $elementType  The element type
     * @return  array                 The sanitized filters
     */
    protected function sanitizeFilters(array $filters, $elementType)
    {
        $allFilters = $this->getFilters();
        $visibleFilters = $this->getVisibleFilters();
        $sanitizedFilters = array();

        foreach ($filters as $key => $filter) {
            if ( ! isset($filter['type']) ||
                 ! is_string($filter['type']) ||
                 ! array_key_exists($filter['type'], $allFilters) ||
                 ! array_key_exists($elementType, $visibleFilters) ||
                 ! in_array($filter['type'], $visibleFilters[$elementType], true)
            ) {
                continue;
            }

            switch ($filter['type']) {
                case 'alpha':
                    $filter['allowWhiteSpace'] = isset($filter['allowWhiteSpace']) && is_bool($filter['allowWhiteSpace']) ? $filter['allowWhiteSpace'] : false;
                    break;
                case 'alphaNumeric':
                    $filter['allowWhiteSpace'] = isset($filter['allowWhiteSpace']) && is_bool($filter['allowWhiteSpace']) ? $filter['allowWhiteSpace'] : false;
                    break;
                case 'digits':
                    $filter['allowWhiteSpace'] = isset($filter['allowWhiteSpace']) && is_bool($filter['allowWhiteSpace']) ? $filter['allowWhiteSpace'] : false;
                    break;
                case 'stripTags':
                    $filter['allowableTags'] = isset($filter['allowableTags']) && is_string($filter['allowableTags']) ? wp_kses_post($filter['allowableTags']) : '';
                    break;
                case 'regex':
                    $filter['pattern'] = isset($filter['pattern']) && is_string($filter['pattern']) ? wp_kses_no_null($filter['pattern'], array('slash_zero' => 'keep')) : '';
                    break;
            }

            $sanitizedFilters[] = $filter;
        }

        return $sanitizedFilters;
    }

    /**
     * Sanitize the settings for the given validators
     *
     * @param   array   $validators   The validators to sanitize
     * @param   string  $elementType  The element type
     * @return  array                 The sanitized validators
     */
    protected function sanitizeValidators(array $validators, $elementType)
    {
        $allValidators = $this->getValidators();
        $visibleValidators = $this->getVisibleValidators();
        $sanitizedValidators = array();

        foreach ($validators as $validator) {
            if ( ! isset($validator['type']) ||
                ! is_string($validator['type']) ||
                ! array_key_exists($validator['type'], $allValidators) ||
                ! array_key_exists($elementType, $visibleValidators) ||
                ! in_array($validator['type'], $visibleValidators[$elementType], true)
            ) {
                continue;
            }

            switch ($validator['type']) {
                case 'alpha':
                    $validator['allowWhiteSpace'] = isset($validator['allowWhiteSpace']) && is_bool($validator['allowWhiteSpace']) ? $validator['allowWhiteSpace'] : false;

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['notAlpha'] = isset($validator['messages']['notAlpha']) && is_string($validator['messages']['notAlpha']) ? wp_kses_post($validator['messages']['notAlpha']) : '';
                    break;
                case 'alphaNumeric':
                    $validator['allowWhiteSpace'] = isset($validator['allowWhiteSpace']) && is_bool($validator['allowWhiteSpace']) ? $validator['allowWhiteSpace'] : false;

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['notAlphaNumeric'] = isset($validator['messages']['notAlphaNumeric']) && is_string($validator['messages']['notAlphaNumeric']) ? wp_kses_post($validator['messages']['notAlphaNumeric']) : '';
                    break;
                case 'digits':
                    $validator['allowWhiteSpace'] = isset($validator['allowWhiteSpace']) && is_bool($validator['allowWhiteSpace']) ? $validator['allowWhiteSpace'] : false;

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['notDigits'] = isset($validator['messages']['notDigits']) && is_string($validator['messages']['notDigits']) ? wp_kses_post($validator['messages']['notDigits']) : '';
                    break;
                case 'duplicate':
                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['isDuplicate'] = isset($validator['messages']['isDuplicate']) && is_string($validator['messages']['isDuplicate']) ? wp_kses_post($validator['messages']['isDuplicate']) : '';
                    break;
                case 'email':
                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['emailAddressInvalidFormat'] = isset($validator['messages']['emailAddressInvalidFormat']) && is_string($validator['messages']['emailAddressInvalidFormat']) ? wp_kses_post($validator['messages']['emailAddressInvalidFormat']) : '';
                    break;
                case 'greaterThan':
                    $validator['min'] = isset($validator['min']) && is_numeric($validator['min']) ? (string) (float) $validator['min'] : '0';
                    $validator['inclusive'] = isset($validator['inclusive']) && is_bool($validator['inclusive']) ? $validator['inclusive'] : false;

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['notGreaterThan'] = isset($validator['messages']['notGreaterThan']) && is_string($validator['messages']['notGreaterThan']) ? wp_kses_post($validator['messages']['notGreaterThan']) : '';
                    $validator['messages']['notGreaterThanInclusive'] = isset($validator['messages']['notGreaterThanInclusive']) && is_string($validator['messages']['notGreaterThanInclusive']) ? wp_kses_post($validator['messages']['notGreaterThanInclusive']) : '';
                    break;
                case 'identical':
                    $validator['token'] = isset($validator['token']) && is_string($validator['token']) ? wp_kses_no_null($validator['token'], array('slash_zero' => 'keep')) : '';

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['notSame'] = isset($validator['messages']['notSame']) && is_string($validator['messages']['notSame']) ? wp_kses_post($validator['messages']['notSame']) : '';
                    break;
                case 'inArray':
                    if (isset($validator['haystack']) && is_array($validator['haystack'])) {
                        foreach ($validator['haystack'] as $key => $item) {
                            $validator['haystack'][$key] = is_string($item) ? wp_kses_no_null($item, array('slash_zero' => 'keep')) : '';
                        }
                    } else {
                        $validator['haystack'] = array();
                    }

                    $validator['invert'] = isset($validator['invert']) && is_bool($validator['invert']) ? $validator['invert'] : false;

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['notInArray'] = isset($validator['messages']['notInArray']) && is_string($validator['messages']['notInArray']) ? wp_kses_post($validator['messages']['notInArray']) : '';
                    break;
                case 'length':
                    $validator['min'] = isset($validator['min']) && is_numeric($validator['min']) ? (string) (int) $validator['min'] : '0';
                    $validator['max'] = isset($validator['max']) && is_numeric($validator['max']) ? (string) (int) $validator['max'] : '';

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['lengthTooShort'] = isset($validator['messages']['lengthTooShort']) && is_string($validator['messages']['lengthTooShort']) ? wp_kses_post($validator['messages']['lengthTooShort']) : '';
                    $validator['messages']['lengthTooLong'] = isset($validator['messages']['lengthTooLong']) && is_string($validator['messages']['lengthTooLong']) ? wp_kses_post($validator['messages']['lengthTooLong']) : '';
                    break;
                case 'lessThan':
                    $validator['max'] = isset($validator['max']) && is_numeric($validator['max']) ? (string) (float) $validator['max'] : '10';
                    $validator['inclusive'] = isset($validator['inclusive']) && is_bool($validator['inclusive']) ? $validator['inclusive'] : false;

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['notLessThan'] = isset($validator['messages']['notLessThan']) && is_string($validator['messages']['notLessThan']) ? wp_kses_post($validator['messages']['notLessThan']) : '';
                    $validator['messages']['notLessThanInclusive'] = isset($validator['messages']['notLessThanInclusive']) && is_string($validator['messages']['notLessThanInclusive']) ? wp_kses_post($validator['messages']['notLessThanInclusive']) : '';
                    break;
                case 'regex':
                    $validator['pattern'] = isset($validator['pattern']) && is_string($validator['pattern']) ? wp_kses_no_null($validator['pattern'], array('slash_zero' => 'keep')) : '';
                    $validator['invert'] = isset($validator['invert']) && is_bool($validator['invert']) ? $validator['invert'] : false;

                    if ( ! isset($validator['messages']) || ! is_array($validator['messages'])) {
                        $validator['messages'] = array();
                    }

                    $validator['messages']['regexNotMatch'] = isset($validator['messages']['regexNotMatch']) && is_string($validator['messages']['regexNotMatch']) ? wp_kses_post($validator['messages']['regexNotMatch']) : '';
                    break;
            }

            $sanitizedValidators[] = $validator;
        }

        return $sanitizedValidators;
    }

    /**
     * Sanitize the given select/multiselect options
     *
     * @param   array   $options The options to sanitize
     * @return  array            The sanitized options
     */
    protected function sanitizeSelectOptions(array $options)
    {
        $sanitizedOptions = array();

        foreach ($options as $key => $option) {
            if ( ! is_array($option)) {
                continue;
            }

            if (isset($option['options'])) {
                $option['id'] = isset($option['id']) && is_numeric($option['id']) ? (int) $option['id'] : 0;
                $option['label'] = isset($option['label']) && is_string($option['label']) ? wp_kses_no_null($option['label'], array('slash_zero' => 'keep')) : __('Untitled', 'quform');

                if (is_array($option['options'])) {
                    foreach ($option['options'] as $optgroupOptionKey => $optgroupOption) {
                        $option['options'][$optgroupOptionKey] = $this->sanitizeSelectOption($optgroupOption);
                    }
                } else {
                    $option['options'] = array();
                }
            } else {
                $option = $this->sanitizeSelectOption($option);
            }

            $sanitizedOptions[] = $option;
        }

        return $sanitizedOptions;
    }

    /**
     * Sanitize the given select/multiselect option
     *
     * @param   array   $option  The option to sanitize
     * @return  array            The sanitized option
     */
    protected function sanitizeSelectOption(array $option)
    {
        $option['id'] = isset($option['id']) && is_numeric($option['id']) ? (int) $option['id'] : 0;
        $option['label'] = isset($option['label']) && is_string($option['label']) ? wp_kses_no_null($option['label'], array('slash_zero' => 'keep')) : '';
        $option['value'] = isset($option['value']) && is_string($option['value']) ? wp_kses_no_null($option['value'], array('slash_zero' => 'keep')) : '';

        return $option;
    }

    /**
     * Sanitize the given checkbox/radio element options
     *
     * @param   array   $options  The options to sanitize
     * @return  array             The sanitized options
     */
    protected function sanitizeCheckboxRadioOptions(array $options)
    {
        foreach ($options as $key => $option) {
            $options[$key]['id'] = isset($option['id']) && is_numeric($option['id']) ? (int) $option['id'] : 0;
            $options[$key]['label'] = isset($option['label']) && is_string($option['label']) ? wp_kses_post($option['label']) : '';
            $options[$key]['value'] = isset($option['value']) && is_string($option['value']) ? wp_kses_no_null($option['value'], array('slash_zero' => 'keep')) : '';
            $options[$key]['image'] = isset($option['image']) && is_string($option['image']) ? esc_url_raw($option['image']) : '';
            $options[$key]['imageSelected'] = isset($option['imageSelected']) && is_string($option['imageSelected']) ? esc_url_raw($option['imageSelected']) : '';
            $options[$key]['width'] = isset($option['width']) && is_string($option['width']) ? sanitize_text_field($option['width']) : '';
            $options[$key]['height'] = isset($option['height']) && is_string($option['height']) ? sanitize_text_field($option['height']) : '';
            $options[$key]['icon'] = isset($option['icon']) && is_string($option['icon']) ? sanitize_text_field($option['icon']) : '';
            $options[$key]['iconSelected'] = isset($option['iconSelected']) && is_string($option['iconSelected']) ? sanitize_text_field($option['iconSelected']) : '';
        }

        return $options;
    }

    /**
     * Sanitize the given RGB color array
     *
     * @param   array  $color
     * @return  array
     */
    protected function sanitizeRgbColorArray(array $color)
    {
        $color = array(
            'r' => Quform::clamp((int) $color['r'], 0, 255),
            'g' => Quform::clamp((int) $color['g'], 0, 255),
            'b' => Quform::clamp((int) $color['b'], 0, 255),
            'a' => Quform::clamp((float) $color['a'], 0, 1)
        );

        return $color;
    }

    /**
     * Handle the Ajax request to add a new form
     */
    public function add()
    {
        $this->validateAddRequest();

        $name = sanitize_text_field(wp_unslash($_POST['name']));

        $nameLength = Quform::strlen($name);

        if ($nameLength == 0) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-forms-add-name' => __('This field is required', 'quform')
                )
            ));
        } elseif ($nameLength > 64) {
            wp_send_json(array(
                'type' => 'error',
                'errors' => array(
                    'qfb-forms-add-name' => __('The form name must be no longer than 64 characters', 'quform')
                )
            ));
        }

        $config = $this->getDefaultForm();
        $config['name'] = $name;

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

        wp_send_json(array(
            'type' => 'success',
            'url' => admin_url('admin.php?page=quform.forms&sp=edit&id=' . $config['id'])
        ));
    }

    /**
     * Validate the request to add a new form
     */
    protected function validateAddRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['name']) || ! is_string($_POST['name'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_add_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_add_form', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request to preview the form via Ajax
     */
    public function preview()
    {
        $this->validatePreviewRequest();

        $config = json_decode(stripslashes(Quform::get($_POST, 'form')), true);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        // Ajax must be enable to submit the form in the preview
        $config = $this->sanitizeForm($config);
        $config['ajax'] = true;
        $config['environment'] = 'preview';

        $form = $this->factory->create($config);
        $form->setCurrentPageById(Quform::get($_POST, 'page'));

        wp_send_json(array(
            'type' => 'success',
            'form' => $form->render(),
            'css' => $form->getCss()
        ));
    }

    /**
     * Validate the request to preview the form
     */
    protected function validatePreviewRequest()
    {
        if ( ! Quform::isPostRequest() || ! isset($_POST['form'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_edit_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }
    }

    /**
     * @param array $config
     */
    protected function validateForm(array $config)
    {
        if ( ! Quform::isNonEmptyString($config['name'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('A form name is required.', 'quform')
            ));
        }
    }

    /**
     * @return array
     */
    public function getThemes()
    {
        return $this->themes->getThemes();
    }

    /**
     * return array
     */
    public function getLocales()
    {
        return array('' => array('name' => __('Default', 'quform'))) + Quform::getLocales();
    }

    /**
     * @return array
     */
    protected function getLoadedPreviewLocales()
    {
        $activeLocales = array();

        foreach ($this->options->get('activeLocales') as $locales) {
            $activeLocales = array_merge($activeLocales, $locales);
        }

        return $activeLocales;
    }

    /**
     * @return string
     */
    protected function getAttachmentHtml()
    {
        ob_start();
        ?>
        <div class="qfb-attachment qfb-box qfb-cf">
            <div class="qfb-attachment-inner">
                <span class="qfb-attachment-remove qfb-small-remove-button qfb-icon qfb-icon-trash" title="<?php esc_attr_e('Remove', 'quform'); ?>"></span>
                <div class="qfb-sub-setting">
                    <div class="qfb-sub-setting-label">
                        <label><?php esc_html_e('Source', 'quform'); ?></label>
                    </div>
                    <div class="qfb-sub-setting-inner">
                        <div class="qfb-sub-setting-input">
                            <select class="qfb-attachment-source">
                                <option value="media"><?php esc_html_e('Media library', 'quform'); ?></option>
                                <option value="element"><?php esc_html_e('Form element', 'quform'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="qfb-sub-setting">
                    <div class="qfb-sub-setting-label">
                        <label><?php esc_html_e('Element', 'quform'); ?></label>
                    </div>
                    <div class="qfb-sub-setting-inner">
                        <div class="qfb-sub-setting-input">
                            <select class="qfb-attachment-element"></select>
                        </div>
                    </div>
                </div>
                <div class="qfb-sub-setting">
                    <div class="qfb-sub-setting-label">
                        <label><?php esc_html_e('File(s)', 'quform'); ?></label>
                    </div>
                    <div class="qfb-sub-setting-inner">
                        <div class="qfb-sub-setting-input">
                            <div class="qfb-cf">
                                <span class="qfb-button-blue qfb-attachment-browse"><i class="qfb-mdi qfb-mdi-panorama"></i><?php esc_html_e('Browse', 'quform'); ?></span>
                            </div>
                            <div class="qfb-attachment-media"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for the database password field
     *
     * @return string
     */
    public function getDbPasswordHtml()
    {
        ob_start();
        ?>
        <input type="text" id="qfb_form_db_password" value="">
        <p class="qfb-description"><?php esc_html_e('The password for the user above.', 'quform'); ?></p>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the HTML for a select menu
     *
     * @param   string  $id             The ID of the field
     * @param   array   $options        The select options
     * @param   string  $selectedValue  The selected value
     * @return  string
     */
    protected function getSelectHtml($id, array $options, $selectedValue = '')
    {
        $output = sprintf('<select id="%s">', Quform::escape($id));

        foreach ($options as $value => $label) {
            $output .= sprintf(
                '<option value="%s"%s>%s</option>',
                Quform::escape($value),
                $selectedValue == $value ? ' selected="selected"' : '',
                Quform::escape($label)
            );
        }

        $output .= '</select>';

        return $output;
    }

    /**
     * Get the HTML for the responsive setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue           The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getResponsiveSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            '' => __('Off', 'quform'),
            'phone-portrait' => __('Phone portrait (479px)', 'quform'),
            'phone-landscape' => __('Phone landscape (767px)', 'quform'),
            'tablet-landscape' => __('Tablet landscape (1024px)', 'quform'),
            'custom' => __('Custom...', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the element size setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getSizeSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            '' => __('Default', 'quform'),
            'slim' => __('Slim', 'quform'),
            'medium' => __('Medium', 'quform'),
            'fat' => __('Fat', 'quform'),
            'huge' => __('Huge', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the field width setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getFieldWidthSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            'tiny' => __('Tiny', 'quform'),
            'small' => __('Small', 'quform'),
            'medium' => __('Medium', 'quform'),
            'large' => __('Large', 'quform'),
            '' => __('100% (default)', 'quform'),
            'custom' => __('Custom...', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the button style setting select menu
     *
     * @param   string       $id                 The ID of the field
     * @param   string       $selectedValue      The selected value
     * @param   bool         $showInheritOption  Shows the "Inherit" option if true
     * @param   string|null  $emptyOptionText    The text for the empty option
     * @return  string
     */
    public function getButtonStyleSelectHtml($id, $selectedValue = '', $showInheritOption = true, $emptyOptionText = null)
    {
        $options = array(
            '' => is_string($emptyOptionText) ? $emptyOptionText : __('Default', 'quform'),
            'theme' => __('Use form theme button style', 'quform'),
            'sexy-silver' => __('Sexy Silver', 'quform'),
            'classic' => __('Classic', 'quform'),
            'background-blending-gradient' => __('Blending Gradient', 'quform'),
            'shine-gradient' => __('Shine Gradient', 'quform'),
            'blue-3d' => __('3D', 'quform'),
            'hollow' => __('Hollow', 'quform'),
            'hollow-rounded' => __('Hollow Rounded', 'quform'),
            'chilled' => __('Chilled', 'quform'),
            'pills' => __('Pill', 'quform'),
            'bootstrap' => __('Bootstrap', 'quform'),
            'bootstrap-primary' => __('Bootstrap Primary', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the button width setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getButtonWidthSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            '' => __('Auto (default)', 'quform'),
            'tiny' => __('Tiny', 'quform'),
            'small' => __('Small', 'quform'),
            'medium' => __('Medium', 'quform'),
            'large' => __('Large', 'quform'),
            'full' => __('100%', 'quform'),
            'custom' => __('Custom...', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the options style setting select menu
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getOptionsStyleSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            '' => __('Default', 'quform'),
            'input-hidden' => __('Hide input', 'quform'),
            'button' => __('Button', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        return $this->getSelectHtml($id, $options, $selectedValue);
    }

    /**
     * Get the HTML for the select icon field
     *
     * @param   string  $id        The ID of the field
     * @param   string  $selected  The selected icon
     * @return  string
     */
    public function getSelectIconHtml($id, $selected = '')
    {
        $output = '<div class="qfb-select-icon qfb-cf">';

        $output .= sprintf(
            '<input type="text" id="%s"%s class="qfb-select-icon-value">',
            esc_attr($id),
            Quform::isNonEmptyString($selected) ? sprintf(' value="%s"', esc_attr($selected)) : ''
        );

        $isCoreIcon = $this->isCoreIcon($selected);
        $output .= sprintf('<div class="qfb-select-icon-button qfb-button">%s</div>', esc_html__('Choose', 'quform'));
        $output .= sprintf('<div class="qfb-select-icon-preview%s">', $isCoreIcon ? '' : ' qfb-hidden');

        if ($isCoreIcon) {
            $output .= sprintf('<i class="%s"></i>', esc_attr(preg_replace('/^fa fa-/', 'qfb-icon qfb-icon-', $selected)));
        }

        $output .= '</div>';

        $output .= sprintf(
            '<div class="qfb-select-icon-clear%s">%s</div>',
            ! Quform::isNonEmptyString($selected) ? ' qfb-hidden' : '',
            esc_html__('Clear', 'quform')
        );

        $output .= '</div>';

        return $output;
    }

    /**
     * Is the given icon class one of the core plugin icons?
     *
     * @param   string  $icon  The icon classes e.g. 'fa fa-check'
     * @return  bool
     */
    protected function isCoreIcon($icon)
    {
        if (!Quform::isNonEmptyString($icon)) {
            return false;
        }

        $icon = trim(str_replace('fa ', '', $icon));

        if (!Quform::isNonEmptyString($icon)) {
            return false;
        }

        $quformIcons = $this->getQuformIcons();
        $fontAwesomeIcons = $this->getFontAwesomeIcons();

        return in_array($icon, $quformIcons) || in_array($icon, $fontAwesomeIcons);
    }

    /**
     * Get the HTML for the icon position select
     *
     * @param   string  $id                 The ID of the field
     * @param   string  $selectedValue      The selected value
     * @param   bool    $showInheritOption  Shows the "Inherit" option if true
     * @return  string
     */
    public function getIconPositionSelectHtml($id, $selectedValue = '', $showInheritOption = true)
    {
        $options = array(
            'left' => __('Left', 'quform'),
            'right' => __('Right', 'quform'),
            'above' => __('Above', 'quform')
        );

        if ($showInheritOption) {
            $options = array('inherit' => __('Inherit', 'quform')) + $options;
        }

        $output = sprintf('<select id="%s">', Quform::escape($id));

        foreach ($options as $value => $label) {
            $output .= sprintf(
                '<option value="%s"%s>%s</option>',
                Quform::escape($value),
                $selectedValue == $value ? ' selected="selected"' : '',
                Quform::escape($label)
            );
        }

        $output .= '</select>';

        return $output;
    }

    /**
     * Get the HTML for the CSS helper widget
     *
     * @return string
     */
    public function getCssHelperHtml()
    {
        $output = '';

        $helpers = array(
            array('css' => 'background-color: ;', 'icon' => 'qfb-mdi qfb-mdi-format_color_fill', 'title' => __('Background color', 'quform')),
            array('css' => 'background: url() top left no-repeat;', 'icon' => 'qfb-mdi qfb-mdi-wallpaper', 'title' => __('Background image', 'quform')),
            array('css' => 'border-color: ;', 'icon' => 'qfb-mdi qfb-mdi-border_color', 'title' => __('Border color', 'quform')),
            array('css' => 'color: ;', 'icon' => 'qfb-mdi qfb-mdi-format_color_text', 'title' => __('Text color', 'quform')),

            array('css' => 'padding: ;', 'icon' => 'qfb-icon-external-link-square', 'title' => __('Padding', 'quform')),
            array('css' => 'margin: ;', 'icon' => 'qfb-icon-external-link', 'title' => __('Margin', 'quform')),
            array('css' => 'border-radius: ;', 'icon' => 'qfb-mdi qfb-mdi-crop_free', 'title' => __('Border radius', 'quform')),

            array('css' => 'font-size: ;', 'icon' => 'qfb-mdi qfb-mdi-format_size', 'title' => __('Font size', 'quform')),
            array('css' => 'line-height: ;', 'icon' => 'qfb-mdi qfb-mdi-format_line_spacing', 'title' => __('Line height', 'quform')),
            array('css' => 'font-weight: bold;', 'icon' => 'qfb-mdi qfb-mdi-format_bold', 'title' => __('Bold', 'quform')),
            array('css' => 'text-decoration: underline;', 'icon' => 'qfb-mdi qfb-mdi-format_underlined', 'title' => __('Underline', 'quform')),
            array('css' => 'text-transform: uppercase;', 'icon' => 'qfb-mdi qfb-mdi-title', 'title' => __('Uppercase', 'quform')),

            array('css' => 'text-align: left;', 'icon' => 'qfb-mdi qfb-mdi-format_align_left', 'title' => __('Text align left', 'quform')),
            array('css' => 'text-align: center;', 'icon' => 'qfb-mdi qfb-mdi-format_align_center', 'title' => __('Text align center', 'quform')),
            array('css' => 'text-align: right;', 'icon' => 'qfb-mdi qfb-mdi-format_align_right', 'title' => __('Text align right', 'quform')),

            array('css' => 'width: ;', 'icon' => 'qfb-mdi qfb-mdi-keyboard_tab', 'title' => __('Width', 'quform')),
            array('css' => 'height: ;', 'icon' => 'qfb-mdi qfb-mdi-vertical_align_top', 'title' => __('Height', 'quform')),

            array('css' => 'display: none;', 'icon' => 'qfb-mdi qfb-mdi-visibility_off', 'title' => __('Hide', 'quform')),
        );

        foreach ($helpers as $helper) {
            $output .= sprintf(
                '<span class="qfb-css-helper" data-css="%s" title="%s"><i class="%s"></i></span>',
                esc_attr($helper['css']),
                esc_attr($helper['title']),
                esc_attr($helper['icon'])
            );
        }

        return $output;
    }

    /**
     * Format the given variables array to display in a &lt;pre&gt; tag
     *
     * @param   array   $variables
     * @return  string
     */
    public function formatVariables(array $variables)
    {
        $lines = array();

        foreach ($variables as $tag => $description) {
            $lines[] = sprintf('%s = %s', $tag, $description);
        }

        return join("\n", $lines);
    }

    /**
     * Handle the request to search posts via Ajax
     */
    public function searchPosts()
    {
        $this->validateSearchPostsRequest();

        $search = sanitize_text_field(wp_unslash($_GET['search']));
        $results = array();

        foreach (Quform::searchPosts($search) as $post) {
            $results[] = array('id' => $post->ID, 'text' => $post->post_title);
        }

        wp_send_json(array(
            'type' => 'success',
            'results' => $results
        ));
    }

    /**
     * Validate the request to search posts via Ajax
     */
    protected function validateSearchPostsRequest()
    {
        if ( ! Quform::isGetRequest() || ! isset($_GET['search']) || ! is_string($_GET['search'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_edit_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_builder_search_posts', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Handle the request to get a post title via Ajax
     */
    public function getPostTitle()
    {
        $this->validateGetPostTitleRequest();

        wp_send_json(array(
            'type' => 'success',
            'title' => Quform::getPostTitleById((int) $_GET['id'])
        ));
    }

    /**
     * Validate the request to get a post title via Ajax
     */
    protected function validateGetPostTitleRequest()
    {
        if ( ! Quform::isGetRequest() || ! isset($_GET['id']) || ! is_numeric($_GET['id'])) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_edit_forms')) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        if ( ! check_ajax_referer('quform_builder_get_post_title', false, false)) {
            wp_send_json(array(
                'type'    => 'error',
                'message' => __('Nonce check failed', 'quform')
            ));
        }
    }

    /**
     * Get the array of available Quform icons
     *
     * @return array
     */
    public function getQuformIcons()
    {
        return array(
            'qicon-add_circle', 'qicon-arrow_back', 'qicon-arrow_forward', 'qicon-check', 'qicon-close',
            'qicon-remove_circle', 'qicon-schedule', 'qicon-mode_edit', 'qicon-favorite_border', 'qicon-file_upload', 'qicon-star',
            'qicon-keyboard_arrow_down', 'qicon-keyboard_arrow_up', 'qicon-send', 'qicon-thumb_down', 'qicon-thumb_up',
            'qicon-refresh', 'qicon-question-circle', 'qicon-calendar', 'qicon-qicon-star-half', 'qicon-paper-plane',
            'qicon-search'
        );
    }

    /**
     * Get the array of available FontAwesome icons
     *
     * Updated for v4.7.0
     *
     * @return array
     */
    public function getFontAwesomeIcons()
    {
        return array('fa-glass', 'fa-music', 'fa-search', 'fa-envelope-o', 'fa-heart', 'fa-star', 'fa-star-o',
            'fa-user', 'fa-film', 'fa-th-large', 'fa-th', 'fa-th-list', 'fa-check', 'fa-remove', 'fa-close',
            'fa-times', 'fa-search-plus', 'fa-search-minus', 'fa-power-off', 'fa-signal', 'fa-gear', 'fa-cog',
            'fa-trash-o', 'fa-home', 'fa-file-o', 'fa-clock-o', 'fa-road', 'fa-download', 'fa-arrow-circle-o-down',
            'fa-arrow-circle-o-up', 'fa-inbox', 'fa-play-circle-o', 'fa-rotate-right', 'fa-repeat', 'fa-refresh',
            'fa-list-alt', 'fa-lock', 'fa-flag', 'fa-headphones', 'fa-volume-off', 'fa-volume-down', 'fa-volume-up',
            'fa-qrcode', 'fa-barcode', 'fa-tag', 'fa-tags', 'fa-book', 'fa-bookmark', 'fa-print', 'fa-camera',
            'fa-font', 'fa-bold', 'fa-italic', 'fa-text-height', 'fa-text-width', 'fa-align-left', 'fa-align-center',
            'fa-align-right', 'fa-align-justify', 'fa-list', 'fa-dedent', 'fa-outdent', 'fa-indent', 'fa-video-camera',
            'fa-photo', 'fa-image', 'fa-picture-o', 'fa-pencil', 'fa-map-marker', 'fa-adjust', 'fa-tint', 'fa-edit',
            'fa-pencil-square-o', 'fa-share-square-o', 'fa-check-square-o', 'fa-arrows', 'fa-step-backward',
            'fa-fast-backward', 'fa-backward', 'fa-play', 'fa-pause', 'fa-stop', 'fa-forward', 'fa-fast-forward',
            'fa-step-forward', 'fa-eject', 'fa-chevron-left', 'fa-chevron-right', 'fa-plus-circle', 'fa-minus-circle',
            'fa-times-circle', 'fa-check-circle', 'fa-question-circle', 'fa-info-circle', 'fa-crosshairs',
            'fa-times-circle-o', 'fa-check-circle-o', 'fa-ban', 'fa-arrow-left', 'fa-arrow-right', 'fa-arrow-up',
            'fa-arrow-down', 'fa-mail-forward', 'fa-share', 'fa-expand', 'fa-compress', 'fa-plus', 'fa-minus',
            'fa-asterisk', 'fa-exclamation-circle', 'fa-gift', 'fa-leaf', 'fa-fire', 'fa-eye', 'fa-eye-slash',
            'fa-warning', 'fa-exclamation-triangle', 'fa-plane', 'fa-calendar', 'fa-random', 'fa-comment', 'fa-magnet',
            'fa-chevron-up', 'fa-chevron-down', 'fa-retweet', 'fa-shopping-cart', 'fa-folder', 'fa-folder-open',
            'fa-arrows-v', 'fa-arrows-h', 'fa-bar-chart-o', 'fa-bar-chart', 'fa-twitter-square', 'fa-facebook-square',
            'fa-camera-retro', 'fa-key', 'fa-gears', 'fa-cogs', 'fa-comments', 'fa-thumbs-o-up', 'fa-thumbs-o-down',
            'fa-star-half', 'fa-heart-o', 'fa-sign-out', 'fa-linkedin-square', 'fa-thumb-tack', 'fa-external-link',
            'fa-sign-in', 'fa-trophy', 'fa-github-square', 'fa-upload', 'fa-lemon-o', 'fa-phone', 'fa-square-o',
            'fa-bookmark-o', 'fa-phone-square', 'fa-twitter', 'fa-facebook-f', 'fa-facebook', 'fa-github', 'fa-unlock',
            'fa-credit-card', 'fa-feed', 'fa-rss', 'fa-hdd-o', 'fa-bullhorn', 'fa-bell', 'fa-certificate',
            'fa-hand-o-right', 'fa-hand-o-left', 'fa-hand-o-up', 'fa-hand-o-down', 'fa-arrow-circle-left',
            'fa-arrow-circle-right', 'fa-arrow-circle-up', 'fa-arrow-circle-down', 'fa-globe', 'fa-wrench', 'fa-tasks',
            'fa-filter', 'fa-briefcase', 'fa-arrows-alt', 'fa-group', 'fa-users', 'fa-chain', 'fa-link', 'fa-cloud',
            'fa-flask', 'fa-cut', 'fa-scissors', 'fa-copy', 'fa-files-o', 'fa-paperclip', 'fa-save', 'fa-floppy-o',
            'fa-square', 'fa-navicon', 'fa-reorder', 'fa-bars', 'fa-list-ul', 'fa-list-ol', 'fa-strikethrough',
            'fa-underline', 'fa-table', 'fa-magic', 'fa-truck', 'fa-pinterest', 'fa-pinterest-square',
            'fa-google-plus-square', 'fa-google-plus', 'fa-money', 'fa-caret-down', 'fa-caret-up', 'fa-caret-left',
            'fa-caret-right', 'fa-columns', 'fa-unsorted', 'fa-sort', 'fa-sort-down', 'fa-sort-desc', 'fa-sort-up',
            'fa-sort-asc', 'fa-envelope', 'fa-linkedin', 'fa-rotate-left', 'fa-undo', 'fa-legal', 'fa-gavel',
            'fa-dashboard', 'fa-tachometer', 'fa-comment-o', 'fa-comments-o', 'fa-flash', 'fa-bolt', 'fa-sitemap',
            'fa-umbrella', 'fa-paste', 'fa-clipboard', 'fa-lightbulb-o', 'fa-exchange', 'fa-cloud-download',
            'fa-cloud-upload', 'fa-user-md', 'fa-stethoscope', 'fa-suitcase', 'fa-bell-o', 'fa-coffee', 'fa-cutlery',
            'fa-file-text-o', 'fa-building-o', 'fa-hospital-o', 'fa-ambulance', 'fa-medkit', 'fa-fighter-jet',
            'fa-beer', 'fa-h-square', 'fa-plus-square', 'fa-angle-double-left', 'fa-angle-double-right',
            'fa-angle-double-up', 'fa-angle-double-down', 'fa-angle-left', 'fa-angle-right', 'fa-angle-up',
            'fa-angle-down', 'fa-desktop', 'fa-laptop', 'fa-tablet', 'fa-mobile-phone', 'fa-mobile', 'fa-circle-o',
            'fa-quote-left', 'fa-quote-right', 'fa-spinner', 'fa-circle', 'fa-mail-reply', 'fa-reply', 'fa-github-alt',
            'fa-folder-o', 'fa-folder-open-o', 'fa-smile-o', 'fa-frown-o', 'fa-meh-o', 'fa-gamepad', 'fa-keyboard-o',
            'fa-flag-o', 'fa-flag-checkered', 'fa-terminal', 'fa-code', 'fa-mail-reply-all', 'fa-reply-all',
            'fa-star-half-empty', 'fa-star-half-full', 'fa-star-half-o', 'fa-location-arrow', 'fa-crop', 'fa-code-fork',
            'fa-unlink', 'fa-chain-broken', 'fa-question', 'fa-info', 'fa-exclamation', 'fa-superscript',
            'fa-subscript', 'fa-eraser', 'fa-puzzle-piece', 'fa-microphone', 'fa-microphone-slash', 'fa-shield',
            'fa-calendar-o', 'fa-fire-extinguisher', 'fa-rocket', 'fa-maxcdn', 'fa-chevron-circle-left',
            'fa-chevron-circle-right', 'fa-chevron-circle-up', 'fa-chevron-circle-down', 'fa-html5', 'fa-css3',
            'fa-anchor', 'fa-unlock-alt', 'fa-bullseye', 'fa-ellipsis-h', 'fa-ellipsis-v', 'fa-rss-square',
            'fa-play-circle', 'fa-ticket', 'fa-minus-square', 'fa-minus-square-o', 'fa-level-up', 'fa-level-down',
            'fa-check-square', 'fa-pencil-square', 'fa-external-link-square', 'fa-share-square', 'fa-compass',
            'fa-toggle-down', 'fa-caret-square-o-down', 'fa-toggle-up', 'fa-caret-square-o-up', 'fa-toggle-right',
            'fa-caret-square-o-right', 'fa-euro', 'fa-eur', 'fa-gbp', 'fa-dollar', 'fa-usd', 'fa-rupee', 'fa-inr',
            'fa-cny', 'fa-rmb', 'fa-yen', 'fa-jpy', 'fa-ruble', 'fa-rouble', 'fa-rub', 'fa-won', 'fa-krw', 'fa-bitcoin',
            'fa-btc', 'fa-file', 'fa-file-text', 'fa-sort-alpha-asc', 'fa-sort-alpha-desc', 'fa-sort-amount-asc',
            'fa-sort-amount-desc', 'fa-sort-numeric-asc', 'fa-sort-numeric-desc', 'fa-thumbs-up', 'fa-thumbs-down',
            'fa-youtube-square', 'fa-youtube', 'fa-xing', 'fa-xing-square', 'fa-youtube-play', 'fa-dropbox',
            'fa-stack-overflow', 'fa-instagram', 'fa-flickr', 'fa-adn', 'fa-bitbucket', 'fa-bitbucket-square',
            'fa-tumblr', 'fa-tumblr-square', 'fa-long-arrow-down', 'fa-long-arrow-up', 'fa-long-arrow-left',
            'fa-long-arrow-right', 'fa-apple', 'fa-windows', 'fa-android', 'fa-linux', 'fa-dribbble', 'fa-skype',
            'fa-foursquare', 'fa-trello', 'fa-female', 'fa-male', 'fa-gittip', 'fa-gratipay', 'fa-sun-o', 'fa-moon-o',
            'fa-archive', 'fa-bug', 'fa-vk', 'fa-weibo', 'fa-renren', 'fa-pagelines', 'fa-stack-exchange',
            'fa-arrow-circle-o-right', 'fa-arrow-circle-o-left', 'fa-toggle-left', 'fa-caret-square-o-left',
            'fa-dot-circle-o', 'fa-wheelchair', 'fa-vimeo-square', 'fa-turkish-lira', 'fa-try', 'fa-plus-square-o',
            'fa-space-shuttle', 'fa-slack', 'fa-envelope-square', 'fa-wordpress', 'fa-openid', 'fa-institution',
            'fa-bank', 'fa-university', 'fa-mortar-board', 'fa-graduation-cap', 'fa-yahoo', 'fa-google', 'fa-reddit',
            'fa-reddit-square', 'fa-stumbleupon-circle', 'fa-stumbleupon', 'fa-delicious', 'fa-digg',
            'fa-pied-piper-pp', 'fa-pied-piper-alt', 'fa-drupal', 'fa-joomla', 'fa-language', 'fa-fax', 'fa-building',
            'fa-child', 'fa-paw', 'fa-spoon', 'fa-cube', 'fa-cubes', 'fa-behance', 'fa-behance-square', 'fa-steam',
            'fa-steam-square', 'fa-recycle', 'fa-automobile', 'fa-car', 'fa-cab', 'fa-taxi', 'fa-tree', 'fa-spotify',
            'fa-deviantart', 'fa-soundcloud', 'fa-database', 'fa-file-pdf-o', 'fa-file-word-o', 'fa-file-excel-o',
            'fa-file-powerpoint-o', 'fa-file-photo-o', 'fa-file-picture-o', 'fa-file-image-o', 'fa-file-zip-o',
            'fa-file-archive-o', 'fa-file-sound-o', 'fa-file-audio-o', 'fa-file-movie-o', 'fa-file-video-o',
            'fa-file-code-o', 'fa-vine', 'fa-codepen', 'fa-jsfiddle', 'fa-life-bouy', 'fa-life-buoy', 'fa-life-saver',
            'fa-support', 'fa-life-ring', 'fa-circle-o-notch', 'fa-ra', 'fa-resistance', 'fa-rebel', 'fa-ge',
            'fa-empire', 'fa-git-square', 'fa-git', 'fa-y-combinator-square', 'fa-yc-square', 'fa-hacker-news',
            'fa-tencent-weibo', 'fa-qq', 'fa-wechat', 'fa-weixin', 'fa-send', 'fa-paper-plane', 'fa-send-o',
            'fa-paper-plane-o', 'fa-history', 'fa-circle-thin', 'fa-header', 'fa-paragraph', 'fa-sliders',
            'fa-share-alt', 'fa-share-alt-square', 'fa-bomb', 'fa-soccer-ball-o', 'fa-futbol-o', 'fa-tty',
            'fa-binoculars', 'fa-plug', 'fa-slideshare', 'fa-twitch', 'fa-yelp', 'fa-newspaper-o', 'fa-wifi',
            'fa-calculator', 'fa-paypal', 'fa-google-wallet', 'fa-cc-visa', 'fa-cc-mastercard', 'fa-cc-discover',
            'fa-cc-amex', 'fa-cc-paypal', 'fa-cc-stripe', 'fa-bell-slash', 'fa-bell-slash-o', 'fa-trash', 'fa-copyright',
            'fa-at', 'fa-eyedropper', 'fa-paint-brush', 'fa-birthday-cake', 'fa-area-chart', 'fa-pie-chart',
            'fa-line-chart', 'fa-lastfm', 'fa-lastfm-square', 'fa-toggle-off', 'fa-toggle-on', 'fa-bicycle', 'fa-bus',
            'fa-ioxhost', 'fa-angellist', 'fa-cc', 'fa-shekel', 'fa-sheqel', 'fa-ils', 'fa-meanpath', 'fa-buysellads',
            'fa-connectdevelop', 'fa-dashcube', 'fa-forumbee', 'fa-leanpub', 'fa-sellsy', 'fa-shirtsinbulk',
            'fa-simplybuilt', 'fa-skyatlas', 'fa-cart-plus', 'fa-cart-arrow-down', 'fa-diamond', 'fa-ship',
            'fa-user-secret', 'fa-motorcycle', 'fa-street-view', 'fa-heartbeat', 'fa-venus', 'fa-mars', 'fa-mercury',
            'fa-intersex', 'fa-transgender', 'fa-transgender-alt', 'fa-venus-double', 'fa-mars-double', 'fa-venus-mars',
            'fa-mars-stroke', 'fa-mars-stroke-v', 'fa-mars-stroke-h', 'fa-neuter', 'fa-genderless',
            'fa-facebook-official', 'fa-pinterest-p', 'fa-whatsapp', 'fa-server', 'fa-user-plus', 'fa-user-times',
            'fa-hotel', 'fa-bed', 'fa-viacoin', 'fa-train', 'fa-subway', 'fa-medium', 'fa-yc', 'fa-y-combinator',
            'fa-optin-monster', 'fa-opencart', 'fa-expeditedssl', 'fa-battery-4', 'fa-battery', 'fa-battery-full',
            'fa-battery-3', 'fa-battery-three-quarters', 'fa-battery-2', 'fa-battery-half', 'fa-battery-1',
            'fa-battery-quarter', 'fa-battery-0', 'fa-battery-empty', 'fa-mouse-pointer', 'fa-i-cursor',
            'fa-object-group', 'fa-object-ungroup', 'fa-sticky-note', 'fa-sticky-note-o', 'fa-cc-jcb',
            'fa-cc-diners-club', 'fa-clone', 'fa-balance-scale', 'fa-hourglass-o', 'fa-hourglass-1',
            'fa-hourglass-start', 'fa-hourglass-2', 'fa-hourglass-half', 'fa-hourglass-3', 'fa-hourglass-end',
            'fa-hourglass', 'fa-hand-grab-o', 'fa-hand-rock-o', 'fa-hand-stop-o', 'fa-hand-paper-o',
            'fa-hand-scissors-o', 'fa-hand-lizard-o', 'fa-hand-spock-o', 'fa-hand-pointer-o', 'fa-hand-peace-o',
            'fa-trademark', 'fa-registered', 'fa-creative-commons', 'fa-gg', 'fa-gg-circle', 'fa-tripadvisor',
            'fa-odnoklassniki', 'fa-odnoklassniki-square', 'fa-get-pocket', 'fa-wikipedia-w', 'fa-safari', 'fa-chrome',
            'fa-firefox', 'fa-opera', 'fa-internet-explorer', 'fa-tv', 'fa-television', 'fa-contao', 'fa-500px',
            'fa-amazon', 'fa-calendar-plus-o', 'fa-calendar-minus-o', 'fa-calendar-times-o', 'fa-calendar-check-o',
            'fa-industry', 'fa-map-pin', 'fa-map-signs', 'fa-map-o', 'fa-map', 'fa-commenting', 'fa-commenting-o',
            'fa-houzz', 'fa-vimeo', 'fa-black-tie', 'fa-fonticons', 'fa-reddit-alien', 'fa-edge', 'fa-credit-card-alt',
            'fa-codiepie', 'fa-modx', 'fa-fort-awesome', 'fa-usb', 'fa-product-hunt', 'fa-mixcloud', 'fa-scribd',
            'fa-pause-circle', 'fa-pause-circle-o', 'fa-stop-circle', 'fa-stop-circle-o', 'fa-shopping-bag',
            'fa-shopping-basket', 'fa-hashtag', 'fa-bluetooth', 'fa-bluetooth-b', 'fa-percent', 'fa-gitlab',
            'fa-wpbeginner', 'fa-wpforms', 'fa-envira', 'fa-universal-access', 'fa-wheelchair-alt',
            'fa-question-circle-o', 'fa-blind', 'fa-audio-description', 'fa-volume-control-phone', 'fa-braille',
            'fa-assistive-listening-systems', 'fa-asl-interpreting', 'fa-american-sign-language-interpreting',
            'fa-deafness', 'fa-hard-of-hearing', 'fa-deaf', 'fa-glide', 'fa-glide-g', 'fa-signing', 'fa-sign-language',
            'fa-low-vision', 'fa-viadeo', 'fa-viadeo-square', 'fa-snapchat', 'fa-snapchat-ghost', 'fa-snapchat-square',
            'fa-pied-piper', 'fa-first-order', 'fa-yoast', 'fa-themeisle', 'fa-google-plus-circle',
            'fa-google-plus-official', 'fa-fa', 'fa-font-awesome', 'fa-handshake-o', 'fa-envelope-open',
            'fa-envelope-open-o', 'fa-linode', 'fa-address-book', 'fa-address-book-o', 'fa-vcard', 'fa-address-card',
            'fa-vcard-o', 'fa-address-card-o', 'fa-user-circle', 'fa-user-circle-o', 'fa-user-o', 'fa-id-badge',
            'fa-drivers-license', 'fa-id-card', 'fa-drivers-license-o', 'fa-id-card-o', 'fa-quora', 'fa-free-code-camp',
            'fa-telegram', 'fa-thermometer-4', 'fa-thermometer', 'fa-thermometer-full', 'fa-thermometer-3',
            'fa-thermometer-three-quarters', 'fa-thermometer-2', 'fa-thermometer-half', 'fa-thermometer-1',
            'fa-thermometer-quarter', 'fa-thermometer-0', 'fa-thermometer-empty', 'fa-shower', 'fa-bathtub',
            'fa-s15', 'fa-bath', 'fa-podcast', 'fa-window-maximize', 'fa-window-minimize', 'fa-window-restore',
            'fa-times-rectangle', 'fa-window-close', 'fa-times-rectangle-o', 'fa-window-close-o', 'fa-bandcamp',
            'fa-grav', 'fa-etsy', 'fa-imdb', 'fa-ravelry', 'fa-eercast', 'fa-microchip', 'fa-snowflake-o',
            'fa-superpowers', 'fa-wpexplorer', 'fa-meetup'
        );
    }
}
