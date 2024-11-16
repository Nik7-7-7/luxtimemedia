<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form
{
    /**
     * @var int
     */
    protected $id;

    /**
     * The unique ID is unique to each form instance, even the same form on the same page
     *
     * @var string
     */
    protected $uniqueId;

    /**
     * @var Quform_Element_Page[]
     */
    protected $pages = array();

    /**
     * @var array
     */
    protected $config = array();

    /**
     * Notifications to send
     *
     * @var Quform_Notification[]
     */
    protected $notifications = array();

    /**
     * Confirmations
     *
     * @var Quform_Confirmation[]
     */
    protected $confirmations = array();

    /**
     * The single confirmation to use for the submission
     *
     * @var Quform_Confirmation
     */
    protected $confirmation;

    /**
     * @var Quform_TokenReplacer
     */
    protected $tokenReplacer;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * Is the form active?
     *
     * @var bool
     */
    protected $active = true;

    /**
     * Has the form been successfully submitted?
     *
     * @var boolean
     */
    protected $submitted = false;

    /**
     * The flag for showing the global form error message for non-Ajax forms
     *
     * @var boolean
     */
    protected $showGlobalError = false;

    /**
     * Character encoding to use
     *
     * @var string
     */
    protected $charset = 'UTF-8';

    /**
     * The dynamic values
     *
     * @var array
     */
    protected $dynamicValues = array();

    /**
     * The current submitted entry ID
     *
     * @var int|null
     */
    protected $entryId;

    /**
     * The current page being viewed/processed
     *
     * @var Quform_Element_Page
     */
    protected $currentPage;

    /**
     * @param  int                   $id
     * @param  string                $uniqueId
     * @param  Quform_Session        $session
     * @param  Quform_TokenReplacer  $tokenReplacer
     * @param  Quform_Options        $options
     */
    public function __construct($id, $uniqueId, Quform_Session $session, Quform_TokenReplacer $tokenReplacer, Quform_Options $options)
    {
        $this->setId($id);
        $this->setUniqueId($uniqueId);
        $this->session = $session;
        $this->tokenReplacer = $tokenReplacer;
        $this->options = $options;
    }

    /**
     * Set the ID of the form
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the ID of the form
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the config value for the given $key
     *
     * If the value is null, the default will be returned
     *
     * @param   string|null  $key      The config key
     * @param   null|mixed   $default  The default value to return if the value key not exist
     * @return  mixed                  The config value or $default if not set
     */
    public function config($key = null, $default = null)
    {
        $value = Quform::get($this->config, $key, $default);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($this), 'getDefaultConfig')), $key, $default);
        }

        return $value;
    }

    /**
     * Set the config value for the given $key or multiple values using an array
     *
     * @param   string|array  $key    Key or array of key/values
     * @param   mixed         $value  Value or null if $key is array
     * @return  $this
     */
    public function setConfig($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->config[$k] = $v;
            }
        } else {
            $this->config[$key] = $value;
        }

        return $this;
    }

    /**
     * Render the form and return the HTML
     *
     * @param   array   $options         The options array
     * @param   string  $outputOverride  Display this HTML instead of rendering the form
     * @return  string
     */
    public function render(array $options = array(), $outputOverride = '')
    {
        $outputOverride = apply_filters('quform_form_output_override', $outputOverride, $this);
        $outputOverride = apply_filters('quform_form_output_override_' . $this->getId(), $outputOverride, $this);

        if (Quform::isNonEmptyString($outputOverride)) {
            return $outputOverride;
        }

        $options = wp_parse_args($options, array(
            'show_title' => true,
            'show_description' => true
        ));

        ob_start();
        do_action('quform_pre_display', $this);
        do_action('quform_pre_display_' . $this->getId(), $this);
        $output = ob_get_clean();

        $output .= sprintf('<div id="quform-%s" class="%s">', Quform::escape($this->getUniqueId()), Quform::escape(Quform::sanitizeClass($this->getContainerClasses())));

        $formAttributes = array(
            'id' => sprintf('quform-form-%s', $this->getUniqueId()),
            'class' => sprintf('quform-form quform-form-%d', $this->getId()),
            'action' => $this->getAction(),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
            'novalidate' => 'novalidate',
            'data-options' => $this->getJsConfig()
        );

        $formAttributes = apply_filters('quform_form_attributes', $formAttributes, $this);
        $formAttributes = apply_filters('quform_form_attributes_' . $this->getId(), $formAttributes, $this);

        $output .= sprintf('<form%s>', Quform::parseHtmlAttributes($formAttributes));

        if (!apply_filters('quform_remove_hidden_submit_button', false)) {
            $output .= '<button class="quform-default-submit" name="quform_submit" type="submit" value="submit" aria-hidden="true" tabindex="-1"></button>';
        }

        $output .= sprintf('<div class="quform-form-inner quform-form-inner-%d">', Quform::escape($this->getId()));

        $output .= $this->getHiddenInputHtml();

        $output .= $this->getTitleDescriptionHtml($options['show_title'], $options['show_description']);

        $output .= $this->getSuccessMessageHtml('above');

        $output .= sprintf('<div class="%s">', Quform::escape(Quform::sanitizeClass($this->getElementsClasses())));

        $output .= $this->getPageProgressHtml();

        $output .= $this->getGlobalErrorHtml();

        foreach ($this->pages as $page) {
            $output .= $page->render($this->getContext());
        }

        $output .= '</div>';

        $output .= $this->getReferralLinkHtml();

        $output .= $this->getUploadProgressHtml();

        $output .= $this->getSuccessMessageHtml('below');

        $output .= $this->getLoadingHtml();

        $output .= '</div>';

        $output .= $this->getEditFormLinkHtml();

        $output .= '</form></div>';

        return $output;
    }

    /**
     * Render a popup form
     *
     * @param   array   $options         The options array
     * @param   string  $outputOverride  Display this HTML instead of rendering the form
     * @return  string
     */
    public function renderPopup(array $options = array(), $outputOverride = '')
    {
        $options = wp_parse_args($options, array(
            'content' => '',
            'options' => '',
            'width' => '',
            'show_title' => true,
            'show_description' => true
        ));

        if (Quform::isNonEmptyString($options['options'])) {
            $options['options'] = json_decode($options['options'], true);
        }

        if ( ! is_array($options['options'])) {
            $options['options'] = array();
        }

        if (Quform::isNonEmptyString($options['width'])) {
            $options['options']['width'] = $options['width'];
        }

        $classes = array('quform-popup-link', 'quform-popup-link-' . $this->getId());

        $attributes = array(
            'class' => join(' ', $classes),
            'data-unique-id' => $this->getUniqueId()
        );

        if ( ! empty($options['options'])) {
            $attributes['data-options'] = wp_json_encode($options['options']);
        }

        ob_start();
        do_action('quform_pre_display_popup', $this);
        do_action('quform_pre_display_popup_' . $this->getId(), $this);
        $output = ob_get_clean();

        $output .= Quform::getHtmlTag('span', $attributes, do_shortcode($options['content']));

        $output .= '<div class="quform-popup">';

        $output .= $this->render($options, $outputOverride);

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for the title and description
     *
     * @param   bool    $showTitle
     * @param   bool    $showDescription
     * @return  string
     */
    protected function getTitleDescriptionHtml($showTitle = true, $showDescription = true)
    {
        $output = '';
        $title = $this->config('title');
        $description = $this->config('description');
        $showTitle = Quform::isNonEmptyString($title) && $showTitle;
        $showDescription = Quform::isNonEmptyString($description) && $showDescription;

        if ($showTitle || $showDescription) {
            $output .= '<div class="quform-form-title-description">';

            if ($showTitle) {
                $output .= Quform::getHtmlTag($this->config('titleTag'), array('class' => 'quform-form-title'), do_shortcode($title));
            }

            if ($showDescription) {
                $output .= Quform::getHtmlTag('p', array('class' => 'quform-form-description'), do_shortcode($description));
            }

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the classes for the outermost wrapper
     *
     * @return array
     */
    protected function getContainerClasses()
    {
        $classes = array('quform', sprintf('quform-%d', $this->getId()));

        if (Quform::isNonEmptyString($this->config('theme'))) {
            $classes[] = sprintf('quform-theme-%s', $this->config('theme'));
        }

        if (Quform::isNonEmptyString($this->config('width')) && Quform::isNonEmptyString($this->config('position'))) {
            $classes[] = sprintf('quform-position-%s', $this->config('position'));
        }

        if ($this->isRtl()) {
            $classes[] = 'quform-rtl';
        }

        if ($this->options->get('preventFouc')) {
            $classes[] = 'quform-prevent-fouc';
        }

        if ($this->options->get('supportPageCaching') && ! Quform::isPostRequest()) {
            $classes[] = 'quform-support-page-caching';
        }

        if ( ! $this->config('ajax')) {
            $classes[] = 'quform-no-ajax';
        }

        if ($this->hasPages()) {
            if ($this->getCurrentPage()->getId() === $this->getFirstPage()->getId()) {
                $classes[] = 'quform-is-first-page';
            } elseif ($this->getCurrentPage()->getId() === $this->getLastPage()->getId()) {
                $classes[] = 'quform-is-last-page';
            }
        }

        if (Quform::isNonEmptyString($this->config('errorsPosition'))) {
            $classes[] = sprintf('quform-errors-%s', $this->config('errorsPosition'));
        }

        $classes = apply_filters('quform_form_container_classes', $classes, $this);
        $classes = apply_filters('quform_form_container_classes_' . $this->getId(), $classes, $this);

        return $classes;
    }

    /**
     * Is the form RTL?
     *
     * @return bool
     */
    public function isRtl()
    {
        if ($this->config('rtl') == 'enabled') {
            return true;
        }

        if ($this->config('rtl') == 'global') {
            return $this->options->get('rtl') == 'enabled' || ($this->options->get('rtl') === '' && is_rtl());
        }

        return false;
    }

    /**
     * Get the locale for this form
     *
     * @return string
     */
    public function getLocale()
    {
        $locale = $this->config('locale');

        if (Quform::isNonEmptyString($locale)) {
            $locale = apply_filters('quform_form_locale', $locale, $this);
            $locale = apply_filters('quform_form_locale_' . $this->getId(), $locale, $this);

            return $locale;
        }

        return $this->options->getLocale();
    }

    /**
     * Get the date format for PHP
     *
     * @return string
     */
    public function getDateFormat()
    {
        return Quform::isNonEmptyString($this->config('dateFormat')) ? $this->config('dateFormat') : $this->options->get('dateFormat');
    }

    /**
     * Get the date format for JavaScript
     *
     * @return string
     */
    public function getDateFormatJs()
    {
        return Quform::isNonEmptyString($this->config('dateFormatJs')) ? $this->config('dateFormatJs') : $this->options->get('dateFormatJs');
    }

    /**
     * Get the time format for PHP
     *
     * @return string
     */
    public function getTimeFormat()
    {
        return Quform::isNonEmptyString($this->config('timeFormat')) ? $this->config('timeFormat') : $this->options->get('timeFormat');
    }

    /**
     * Get the time format for JavaScript
     *
     * @return string
     */
    public function getTimeFormatJs()
    {
        return Quform::isNonEmptyString($this->config('timeFormatJs')) ? $this->config('timeFormatJs') : $this->options->get('timeFormatJs');
    }

    /**
     * Get the datetime format for PHP
     *
     * @return string
     */
    public function getDateTimeFormat()
    {
        return Quform::isNonEmptyString($this->config('dateTimeFormat')) ? $this->config('dateTimeFormat') : $this->options->get('dateTimeFormat');
    }

    /**
     * Get the datetime format for JavaScript
     *
     * @return string
     */
    public function getDateTimeFormatJs()
    {
        return Quform::isNonEmptyString($this->config('dateTimeFormatJs')) ? $this->config('dateTimeFormatJs') : $this->options->get('dateTimeFormatJs');
    }

    /**
     * Get the classes for the elements wrapper div
     *
     * @return array
     */
    protected function getElementsClasses()
    {
        $classes = array(
            'quform-elements',
            sprintf('quform-elements-%d', $this->getId()),
            'quform-cf'
        );

        if (Quform::isNonEmptyString($this->config('responsiveElements')) && $this->config('responsiveElements') != 'custom') {
            $classes[] = sprintf('quform-responsive-elements-%s', $this->config('responsiveElements'));
        }

        if ($this->isSubmitted()) {
            $confirmation = $this->getConfirmation();

            if ($confirmation instanceof Quform_Confirmation && $confirmation->config('hideForm')) {
                $classes[] = 'quform-hidden';
            }
        }

        return $classes;
    }

    /**
     * Get the action attribute for the &lt;form&gt;
     *
     * @return string
     */
    protected function getAction()
    {
        $useAnchor = apply_filters('quform_use_anchor', true);
        $useAnchor = apply_filters("quform_use_anchor_{$this->getId()}", $useAnchor);

        return add_query_arg(array()) . ($useAnchor ? "#quform-{$this->getUniqueId()}" : '');
    }

    /**
     * Get the HTML for the hidden inputs
     *
     * @return string
     */
    protected function getHiddenInputHtml()
    {
        $output = '';

        $inputs = array(
            'quform_form_id' => $this->getId(),
            'quform_form_uid' => $this->getUniqueId(),
            'quform_count' => $this->config('count'),
            'form_url' => Quform::getCurrentUrl(),
            'referring_url' => Quform::getHttpReferer(),
            'post_id' => Quform::getPostProperty('ID'),
            'post_title' => Quform::getPostProperty('post_title'),
            'quform_current_page_id' => $this->getCurrentPage()->getId(),
        );

        if ($this->options->get('csrfProtection')) {
            $inputs['quform_csrf_token'] = $this->getCsrfToken();
        }

        foreach ($inputs as $name => $value) {
            $output .= sprintf('<input type="hidden" name="%s" value="%s" />', Quform::escape($name), Quform::escape($value));
        }

        return $output;
    }

    /**
     * Get the context (inheritable settings) for passing to child elements when rendering the form and generating CSS
     *
     * @return array
     */
    public function getContext()
    {
        return array(
            'labelPosition' => $this->config('labelPosition'),
            'labelWidth' => $this->config('labelWidth'),
            'tooltipType' => $this->config('tooltipType'),
            'tooltipEvent' => $this->config('tooltipEvent'),
            'fieldSize' => $this->config('fieldSize'),
            'fieldWidth' => $this->config('fieldWidth'),
            'fieldWidthCustom' => $this->config('fieldWidthCustom'),
            'buttonStyle' => $this->config('buttonStyle'),
            'buttonSize' => $this->config('buttonSize'),
            'buttonWidth' => $this->config('buttonWidth'),
            'buttonWidthCustom' => $this->config('buttonWidthCustom'),
            'responsiveColumns' => $this->config('responsiveColumns'),
            'responsiveColumnsCustom' => $this->config('responsiveColumnsCustom'),
            'optionsStyle' => $this->config('optionsStyle'),
            'optionsButtonStyle' => $this->config('optionsButtonStyle'),
            'optionsButtonSize' => $this->config('optionsButtonSize'),
            'optionsButtonWidth' => $this->config('optionsButtonWidth'),
            'optionsButtonWidthCustom' => $this->config('optionsButtonWidthCustom'),
            'optionsButtonIconPosition' => $this->config('optionsButtonIconPosition')
        );
    }

    /**
     * Get the HTML for the Quform referral link
     *
     * @return string
     */
    protected function getReferralLinkHtml()
    {
        $output = '';

        if ($this->options->get('referralEnabled') && Quform::isNonEmptyString($this->options->get('referralLink'))) {
            $output .= '<div class="quform-referral-link">';
            $output .= sprintf('<a href="%s">%s</a>', esc_url($this->options->get('referralLink')), $this->options->get('referralText'));
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the HTML for the file upload progress bar
     *
     * @return string
     */
    protected function getUploadProgressHtml()
    {
        $output = '';

        if ($this->hasEnhancedFileUploadElement()) {
            $classes = array('quform-upload-progress-wrap');

            if (Quform::isNonEmptyString($this->config('loadingType')) &&
                $this->config('loadingOverlay') &&
                ($this->config('loadingPosition') == 'over-form' || $this->config('loadingPosition') == 'over-screen')
            ) {
                $classes[] = sprintf('quform-loading-position-%s', $this->config('loadingPosition'));
            }

            $output .= sprintf('<div class="%s">', Quform::escape(join(' ', $classes)));
            $output .= '<div class="quform-upload-progress-bar-wrap"><div class="quform-upload-progress-bar"></div></div>';
            $output .= '<div class="quform-upload-info quform-cf"><div class="quform-upload-filename"></div></div>';
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the HTML for the edit form link
     *
     * @return string
     */
    protected function getEditFormLinkHtml()
    {
        $output = '';

        if ($this->options->get('showEditLink') && $this->config('environment') != 'preview' && is_user_logged_in() && current_user_can('quform_edit_forms')) {
            $output .= '<div class="quform-edit-form">';

            $editUrl = sprintf(admin_url('admin.php?page=quform.forms&sp=edit&id=%d'), $this->getId());

            $output .= sprintf('<a class="quform-edit-form-link" href="%s"><i class="qicon qicon-mode_edit"></i>%s</a>', esc_url($editUrl), esc_html__('Edit this form', 'quform'));

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Add a form notification
     *
     * @param   Quform_Notification  $notification
     * @return  Quform_Form
     */
    public function addNotification(Quform_Notification $notification)
    {
        $this->notifications[] = $notification;

        return $this;
    }

    /**
     * Get the form notifications
     *
     * @return Quform_Notification[]
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * Get a notification by identifier
     *
     * @param   string  $identifier  The notification identifier (aka unique ID)
     * @return  Quform_Notification|null
     */
    public function getNotification($identifier)
    {
        foreach ($this->getNotifications() as $notification) {
            if ($notification->getIdentifier() == $identifier) {
                return $notification;
            }
        }

        return null;
    }

    /**
     * Replace the variables in the given string
     *
     * @param   string  $text
     * @param   string  $format
     * @return  string
     */
    public function replaceVariables($text, $format = 'text')
    {
        return $this->tokenReplacer->replaceVariables($text, $format, $this);
    }

    /**
     * Replace the variables in the given text before the form is processed
     *
     * @param   string  $text
     * @param   string  $format
     * @return  string
     */
    public function replaceVariablesPreProcess($text, $format = 'text')
    {
        return $this->tokenReplacer->replaceVariablesPreProcess($text, $format, $this);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set whether the form is active
     *
     * @param   boolean  $flag
     * @return  Quform_Form
     */
    public function setIsActive($flag)
    {
        $this->active = $flag;

        return $this;
    }

    /**
     * Add a confirmation
     *
     * @param Quform_Confirmation $confirmation
     */
    public function addConfirmation(Quform_Confirmation $confirmation)
    {
        $this->confirmations[] = $confirmation;
    }

    /**
     * Set the confirmation to use for this submission
     *
     * If there is only one confirmation, it will be used, otherwise the first matching conditional logic
     */
    public function setConfirmation()
    {
        $this->confirmation = $this->confirmations[0];
        $count = count($this->confirmations);

        if ($count > 1) {
            for ($i = 1; $i < $count; $i++) {
                $confirmation = $this->confirmations[$i];

                if ($confirmation->config('enabled') && count($confirmation->config('logicRules')) && $this->checkLogicAction($confirmation->config('logicAction'), $confirmation->config('logicMatch'), $confirmation->config('logicRules'))) {
                    $this->confirmation = $confirmation;
                    break;
                }
            }
        }
    }

    /**
     * Get the confirmation to use for this submission
     *
     * @return Quform_Confirmation
     */
    public function getConfirmation()
    {
        return $this->confirmation;
    }

    /**
     * Get the HTML for the success message
     *
     * @param   string  $position
     * @return  string
     */
    public function getSuccessMessageHtml($position = 'above')
    {
        $confirmation = $this->getConfirmation();
        $output = '';

        if ($this->isSubmitted() &&
            $confirmation->config('messagePosition') == $position &&
            Quform::isNonEmptyString($message = $confirmation->getMessage())
        ) {
            $hasIcon = Quform::isNonEmptyString($confirmation->config('messageIcon'));

            $output = sprintf(
                '<div class="quform-success-message quform-success-message-%s%s">',
                $this->getId(),
                $hasIcon ? ' quform-success-message-has-icon' : ''
            );

            if ($hasIcon) {
                $output .= sprintf('<div class="quform-success-message-icon"><i class="%s"></i></div>', $confirmation->config('messageIcon'));
            }

            $output .= sprintf('<div class="quform-success-message-content">%s</div>', $message);

            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Get the HTML for the page progress
     *
     * @return string
     */
    public function getPageProgressHtml()
    {
        $type = $this->config('pageProgressType');

        if ( ! $this->hasPages() || ! Quform::isNonEmptyString($type)) {
            return '';
        }

        $currentPage = $this->getCurrentPage();
        $currentPageIndex = 1;

        foreach ($this->pages as $pages) {
            if ($pages == $currentPage) {
                break;
            }
            $currentPageIndex++;
        }

        $output = sprintf('<div class="quform-page-progress quform-page-progress-type-%s">', $type);

        if ($type == 'numbers' || $type == 'percentage') {
            $percent = round(($currentPageIndex / $this->getPageCount()) * 100);

            $output .= sprintf('<div class="quform-page-progress-bar" style="width: %d%%;">', esc_attr($percent));
            $output .= '<span class="quform-page-progress-text">';

            if ($type == 'numbers') {
                $output .= sprintf(
                    /* translators: Page x of x, %1$s: the current page number, %2$s: the total number of pages */
                    esc_html($this->getTranslation('pageProgressNumbersText', __('Page %1$s of %2$s', 'quform'))),
                    sprintf('<span class="quform-page-progress-number">%s</span>', $currentPageIndex),
                    $this->getPageCount()
                );
            } else {
                $output .= sprintf(
                    /* translators: page progress percentage, %s: the page progress percentage, %%: the percentage sign */
                    esc_html__('%s%%', 'quform'),
                    sprintf('<span class="quform-page-progress-percentage">%s</span>', $percent)
                );
            }

            $output .= '</span>';
            $output .= '</div>';
        } else if ($type == 'tabs') {
            $output .= sprintf('<div class="quform-page-progress-tabs quform-%d-pages quform-cf">', count($this->pages));

            foreach ($this->pages as $page) {
                $output .= sprintf(
                    '<div class="quform-page-progress-tab%s" data-id="%d">%s</div>',
                    $page == $this->currentPage ? ' quform-current-tab' : '',
                    $page->getId(),
                    esc_html($page->getLabel())
                );
            }

            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Get the HTML for the loading indicator
     *
     * @return string
     */
    protected function getLoadingHtml()
    {
        if ( ! Quform::isNonEmptyString($this->config('loadingType')) ||
            ($this->config('loadingPosition') != 'over-form' && $this->config('loadingPosition') != 'over-screen'))
        {
            return '';
        }

        $classes = array(
            'quform-loading',
            sprintf('quform-loading-position-%s', $this->config('loadingPosition')),
            sprintf('quform-loading-type-%s', $this->config('loadingType'))
        );

        $output = sprintf('<div class="%s">', esc_attr(join(' ', $classes)));

        if ($this->config('loadingOverlay')) {
            $output .= '<div class="quform-loading-overlay"></div>';
        }

        $output .= '<div class="quform-loading-inner">';

        if ($this->config('loadingType') == 'custom') {
            $output .= do_shortcode($this->config('loadingCustom'));
        } else {
            $output .= '<div class="quform-loading-spinner"><div class="quform-loading-spinner-inner"></div></div>';
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Set the unique form ID
     *
     * The unique ID is unique to each form instance, even the same form on the same page
     *
     * @param string $uniqueId
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    /**
     * Get the unique form ID
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * Get the JavaScript configuration for the frontend
     *
     * @return string
     */
    public function getJsConfig()
    {
        $config = array(
            'id' => $this->getId(),
            'uniqueId' => $this->getUniqueId(),
            'theme' => $this->config('theme'),
            'ajax' => $this->config('ajax'),
            'logic' => $this->getLogicConfig(),
            'currentPageId' => $this->getCurrentPage()->getId(),
            'errorsIcon' => $this->config('errorsIcon'),
            'updateFancybox' => apply_filters('quform_update_fancybox', true, $this),
            'hasPages' => $this->hasPages(),
            'pages' => $this->getPageIds(),
            'pageProgressType' => $this->config('pageProgressType'),
            'tooltipsEnabled' => $this->config('tooltipsEnabled'),
            'tooltipClasses' => $this->config('tooltipClasses'),
            'tooltipMy' => $this->config('tooltipMy'),
            'tooltipAt' => $this->config('tooltipAt'),
            'isRtl' => $this->isRtl()
        );

        if (is_numeric($this->options->get('scrollOffset'))) {
            $config['scrollOffset'] = ((int) $this->options->get('scrollOffset')) * -1;
        }

        if (is_numeric($this->options->get('scrollSpeed'))) {
            $config['scrollSpeed'] = (int) $this->options->get('scrollSpeed');
        }

        $config = apply_filters('quform_form_js_config', $config, $this);
        $config = apply_filters('quform_form_js_config_' . $this->getId(), $config, $this);

        return wp_json_encode($config);
    }

    /**
     * Get the character encoding
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Set the character encoding
     *
     * @param string $charset
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return array
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Add a page to the form
     *
     * @param Quform_Element_Page $page The page to add
     */
    public function addPage(Quform_Element_Page $page)
    {
        $this->pages[$page->getName()] = $page;
    }

    /**
     * Set the values for all form elements
     *
     * @param  array  $values          The values to set
     * @param  bool   $setOthersEmpty  Set the value of elements that are not in $values to empty
     */
    public function setValues($values, $setOthersEmpty = false)
    {
        foreach ($this->getRecursiveIterator() as $element) {
            if ( ! ($element instanceof Quform_Element_Field) || $element instanceof Quform_Element_File) {
                continue;
            }

            if ( ! $element->isVisible()) {
                // For non-visible fields, set the default or the empty value if there is no default
                $element->setValue($element->hasDefaultValue() ? $element->getDefaultValue() : $element->getEmptyValue());
            } else if (isset($values[$element->getName()])) {
                $element->setValue($values[$element->getName()]);
            } else if ($setOthersEmpty) {
                $element->setValue($element->getEmptyValue());
            }
        }
    }

    /**
     * Is the form valid?
     *
     * @return array The first value is valid boolean, second is the first page instance that has an error
     */
    public function isValid()
    {
        $valid = true;
        $firstErrorPage = null;

        foreach ($this->pages as $page) {
            if ( ! $page->isValid()) {
                $valid = false;

                if (is_null($firstErrorPage)) {
                    $firstErrorPage = $page;
                }
            }
        }

        return array($valid, $firstErrorPage);
    }

    /**
     * Get the validation errors
     *
     * @return array
     */
    public function getErrors()
    {
        $errors = array();

        foreach ($this->getRecursiveIterator() as $element) {
            if ( ! $element instanceof Quform_Element_Field) {
                continue;
            }

            if ($element->hasError()) {
                foreach ($element->getErrorArray() as $identifier => $message) {
                    $errors[$identifier] = $message;
                }
            }
        }

        return $errors;
    }

    /**
     * Set the flag to show the global form error message
     *
     * @param bool $flag
     */
    public function setShowGlobalError($flag)
    {
        $this->showGlobalError = $flag;
    }

    /**
     * Get the flag to show the global form error message
     *
     * @return bool
     */
    public function getShowGlobalError()
    {
        return $this->showGlobalError;
    }

    /**
     * Get the global error message data
     *
     * @return array
     */
    public function getGlobalError()
    {
        return array(
            'enabled' => $this->config('errorEnabled'),
            'title' => $this->config('errorTitle'),
            'content' => $this->config('errorContent')
        );
    }

    /**
     * Get the HTML for the global form error message if enabled
     *
     * @return string
     */
    protected function getGlobalErrorHtml()
    {
        $output = '';

        if ($this->getShowGlobalError() && Quform::isNonEmptyString($this->config('errorContent'))) {
            $output .= '<div class="quform-error-message"><div class="quform-error-message-inner">';

            if (Quform::isNonEmptyString($this->config('errorTitle'))) {
                $output .= sprintf('<div class="quform-error-message-title">%s</div>', $this->config('errorTitle'));
            }

            $output .= sprintf('<div class="quform-error-message-content">%s</div>', $this->config('errorContent'));


            $output .= '</div></div>';
        }

        return $output;
    }

    /**
     * Get the element with the given name
     *
     * Returns the element or null if it does not exist
     *
     * @param   string               $nameOrId
     * @return  Quform_Element|null
     */
    public function getElement($nameOrId)
    {
        return is_numeric($nameOrId) ? $this->getElementById($nameOrId) : $this->getElementByName($nameOrId);
    }

    /**
     * Set the value of the element with the given name
     *
     * @param   string|int   $nameOrId
     * @param   mixed        $value
     * @return  Quform_Form
     */
    public function setValue($nameOrId, $value)
    {
        $element = $this->getElement($nameOrId);

        if ($element instanceof Quform_Element_Field) {
            $element->setValue($value);
        }

        return $this;
    }

    /**
     * Set the value of the element with the given name
     *
     * @param   string|int  $nameOrId
     * @param   mixed       $value
     * @return  $this
     */
    public function setValueFromStorage($nameOrId, $value)
    {
        $element = $this->getElement($nameOrId);

        if ($element instanceof Quform_Element_Field) {
            $element->setValueFromStorage($value);
        }

        return $this;
    }

    /**
     * Get the values of all fields
     *
     * @return array The values of all fields
     */
    public function getValues()
    {
        $values = array();

        foreach ($this->getRecursiveIterator() as $element) {
            if ($element instanceof Quform_Element_Field) {
                $values[$element->getName()] = $element->getValue();
            }
        }

        return $values;
    }

    /**
     * Get the values of a single field
     *
     * @param   string|int  $nameOrId
     * @return  mixed       The value of the given field or null
     */
    public function getValue($nameOrId)
    {
        $element = $this->getElement($nameOrId);

        return $element instanceof Quform_Element_Field ? $element->getValue() : null;
    }

    /**
     * Get the value of the element with the given name, formatted in HTML
     *
     * @param   string  $nameOrId  The unique element name
     * @return  string             The formatted HTML
     */
    public function getValueHtml($nameOrId)
    {
        $element = $this->getElement($nameOrId);

        return $element instanceof Quform_Element_Field ? $element->getValueHtml() : '';
    }

    /**
     * Get the value of the element with the given name, formatted in plain text
     *
     * @param   string $nameOrId   The unique element name
     * @param   string $separator  The separator for array types
     * @return  string             The formatted plain text
     */
    public function getValueText($nameOrId, $separator = ', ')
    {
        $element = $this->getElement($nameOrId);

        return $element instanceof Quform_Element_Field ? $element->getValueText($separator) : '';
    }

    /**
     * Get the element with the given ID.
     *
     * Returns the element or null if no element was found.
     *
     * @param   int                  $id
     * @return  Quform_Element|null
     */
    public function getElementById($id)
    {
        foreach ($this->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
            if ($element->getId() == $id) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Get the element with the given name.
     *
     * Returns the element or null if no element was found.
     *
     * @param   string               $name
     * @return  Quform_Element|null
     */
    public function getElementByName($name)
    {
        foreach ($this->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
            if ($element->getName() == $name) {
                return $element;
            }
        }

        return null;
    }

    /**
     * Get the conditional logic configuration array
     *
     * @return array
     */
    public function getLogicConfig()
    {
        $logic = array();
        $dependents = array();
        $elementIds = array();
        $dependentElementIds = array();

        foreach ($this->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
            if ($element->config('logicEnabled') && count($element->config('logicRules'))) {
                $elementId = $element->getId();
                $elementIds[] = $elementId;
                $logic[$elementId] = array(
                    'action' => $element->config('logicAction'),
                    'match' => $element->config('logicMatch'),
                    'rules' => $element->config('logicRules')
                );

                foreach ($element->config('logicRules') as $elementLogicRule) {
                    if ( ! isset($dependents[$elementLogicRule['elementId']])) {
                        $dependents[$elementLogicRule['elementId']] = array();
                    }

                    $dependents[$elementLogicRule['elementId']][] = $elementId;
                    $dependentElementIds[] = $elementLogicRule['elementId'];
                }
            }
        }

        return array(
            'logic' => $logic,
            'dependents' => $dependents,
            'elementIds' => $elementIds,
            'dependentElementIds' => array_values(array_unique($dependentElementIds)),
            'animate' => $this->config('logicAnimation')
        );
    }

    /**
     * Get the CSS for this form
     *
     * @return  string
     */
    public function getCss()
    {
        $css = '';
        $styles = array();

        // Mapping of form style options to global styles
        $map = array(
            array('verticalElementSpacing', 'padding-bottom: %s;', 'elementSpacer'),
            array('width', 'width: %s;', 'formOuter'),
            array('labelTextColor', 'color: %s;', 'elementLabelText'),
            array('requiredTextColor', 'color: %s;', 'elementRequiredText'),
            array('fieldBackgroundColor', 'background-color: %s;', 'elementText', 'elementTextarea', 'elementSelect'),
            array('fieldBackgroundColorHover', 'background-color: %s;', 'elementTextHover', 'elementTextareaHover', 'elementSelectHover'),
            array('fieldBackgroundColorFocus', 'background-color: %s;', 'elementTextFocus', 'elementTextareaFocus', 'elementSelectFocus', 'timeDropdown', 'select2Drop', 'select2DropBorders'),
            array('fieldBackgroundColorFocus', 'border-color: rgba(0,0,0,0.2);', 'select2DropBorders'),
            array('fieldBorderColor', 'border-color: %s;', 'elementText', 'elementTextarea', 'elementSelect'),
            array('fieldBorderColorHover', 'border-color: %s;', 'elementTextHover', 'elementTextareaHover', 'elementSelectHover'),
            array('fieldBorderColorFocus', 'border-color: %s;', 'elementTextFocus', 'elementTextareaFocus', 'elementSelectFocus', 'timeDropdown', 'select2Drop'),
            array('fieldTextColor', 'color: %s;', 'elementText', 'elementTextarea', 'elementSelect', 'elementIcon', 'select2DropText'),
            array('fieldTextColorHover', 'color: %s;', 'elementTextHover', 'elementTextareaHover', 'elementSelectHover', 'elementIconHover'),
            array('fieldTextColorFocus', 'color: %s;', 'elementTextFocus', 'elementTextareaFocus', 'elementSelectFocus', 'timeDropdown', 'select2Drop', 'timeDropdownText', 'select2DropTextFocus', 'select2DropBorders'),
            array('buttonBackgroundColor', 'background-color: %s;', 'submitButton', 'backButton', 'nextButton', 'uploadButton'),
            array('buttonBackgroundColorHover', 'background-color: %s;', 'submitButtonHover', 'backButtonHover', 'nextButtonHover', 'uploadButtonHover'),
            array('buttonBackgroundColorActive', 'background-color: %s;', 'submitButtonActive', 'backButtonActive', 'nextButtonActive', 'uploadButtonActive'),
            array('buttonBorderColor', 'border-color: %s;', 'submitButton', 'backButton', 'nextButton', 'uploadButton'),
            array('buttonBorderColorHover', 'border-color: %s;', 'submitButtonHover', 'backButtonHover', 'nextButtonHover', 'uploadButtonHover'),
            array('buttonBorderColorActive', 'border-color: %s;', 'submitButtonActive', 'backButtonActive', 'nextButtonActive', 'uploadButtonActive'),
            array('buttonTextColor', 'color: %s;', 'submitButtonText', 'backButtonText', 'nextButtonText', 'uploadButtonText'),
            array('buttonTextColorHover', 'color: %s;', 'submitButtonTextHover', 'backButtonTextHover', 'nextButtonTextHover', 'uploadButtonTextHover'),
            array('buttonTextColorActive', 'color: %s;', 'submitButtonTextActive', 'backButtonTextActive', 'nextButtonTextActive', 'uploadButtonTextActive'),
            array('buttonIconColor', 'color: %s;', 'submitButtonIcon', 'backButtonIcon', 'nextButtonIcon', 'uploadButtonIcon'),
            array('buttonIconColorHover', 'color: %s;', 'submitButtonIconHover', 'backButtonIconHover', 'nextButtonIconHover', 'uploadButtonIconHover'),
            array('buttonIconColorActive', 'color: %s;', 'submitButtonIconActive', 'backButtonIconActive', 'nextButtonIconActive', 'uploadButtonIconActive'),
        );

        foreach ($map as $selectors) {
            $value = $this->config(array_shift($selectors));

            if (Quform::isNonEmptyString($value)) {
                $rule = sprintf(array_shift($selectors), $value);

                foreach ($selectors as $selector) {
                    $styles[] = array('type' => trim($selector), 'css' => $rule);
                }
            }
        }

        $styles = array_merge($styles, $this->config('styles'));

        foreach ($styles as $style) {
            $selector = $this->getCssSelector($style['type']);

            if ( ! empty($selector) && ! empty($style['css'])) {
                $css .= sprintf('%s { %s }', $selector, $style['css']);
            }
        }

        $theme = $this->config('theme');
        if (Quform::isNonEmptyString($theme)) {
            $css .= Quform_Themes::getPrimaryColorCustomCss($theme, $this->getId(), $this->config('themePrimaryColor'));
            $css .= Quform_Themes::getSecondaryColorCustomCss($theme, $this->getId(), $this->config('themeSecondaryColor'));
            $css .= Quform_Themes::getPrimaryForegroundColorCustomCss($theme, $this->getId(), $this->config('themePrimaryForegroundColor'));
            $css .= Quform_Themes::getSecondaryForegroundColorCustomCss($theme, $this->getId(), $this->config('themeSecondaryForegroundColor'));
        }

        if ($this->config('responsiveElements') == 'custom' && Quform::isNonEmptyString($this->config('responsiveElementsCustom'))) {
            $css .= sprintf('@media (max-width: %s) {', Quform::addCssUnit($this->config('responsiveElementsCustom')));
            $css .= sprintf('
                .quform-%1$d .quform-input,
                .quform-%1$d .quform-upload-dropzone {
                    width: 100%% !important;
                    min-width: 10px;
                }
                .quform-%1$d .quform-error > .quform-error-inner {
                    float: none;
                    display: block;
                }
                .quform-%1$d .quform-element-submit button {
                    margin: 0;
                    width: 100%%;
                }
                .quform-%1$d .quform-element-submit.quform-button-width-full > .quform-button-submit-default,
                .quform-%1$d .quform-element-submit.quform-button-width-full > .quform-button-back-default,
                .quform-%1$d .quform-element-submit.quform-button-width-full > .quform-button-next-default {
                    width: 100%%;
                    float: none;
                }
                .quform-%1$d .quform-button-next-default,
                .quform-%1$d .quform-button-back-default,
                .quform-%1$d .quform-button-submit-default {
                    float: none;
                    margin: 5px 0;
                }
                .quform-%1$d .quform-loading-position-left {
                    padding-left: 0;
                }
                .quform-%1$d .quform-loading-position-right {
                    padding-right: 0;
                }
                .quform-%1$d .quform-labels-left > .quform-spacer > .quform-label {
                    float: none;
                    width: auto;
                }
                .quform-%1$d .quform-labels-left.quform-element > .quform-spacer > .quform-inner {
                    margin-left: 0 !important;
                    padding-left: 0 !important;
                    margin-right: 0 !important;
                    padding-right: 0 !important;
                }
                .quform-%1$d .select2-container--quform .select2-selection--multiple .select2-selection__choice {
                    display: block;
                    float: none;
                    width: auto;
                    padding-top: 10px;
                    padding-bottom: 10px;
                    margin-right: 25px;
                }
                ', $this->getId());
            $css .= '}';
        }

        if ($this->config('responsiveColumns') == 'custom' && Quform::isNonEmptyString($this->config('responsiveColumnsCustom'))) {
            $css .= sprintf('@media (max-width: %s) {', Quform::addCssUnit($this->config('responsiveColumnsCustom')));
            $css .= sprintf('
                .quform-%1$d .quform-element-row > .quform-element-column,
                .quform-%1$d .quform-options-columns > .quform-option {
                    float: none;
                    width: 100%% !important;
                }
                ', $this->getId());
            $css .= '}';
        }

        if (Quform::isNonEmptyString($this->config('fieldPlaceholderStyles'))) {
            // The selectors must be separate for this to work
            $css .= sprintf(
                '.quform-%1$d ::-webkit-input-placeholder {
                    %2$s
                }
                .quform-%1$d :-moz-placeholder {
                    %2$s
                }
                .quform-%1$d ::-moz-placeholder {
                    %2$s
                }
                .quform-%1$d :-ms-input-placeholder {
                    %2$s
                }
                .quform-%1$d ::placeholder {
                    %2$s
                }',
                $this->getId(),
                $this->config('fieldPlaceholderStyles')
            );
        }

        if (Quform::isNonEmptyString($this->config('loadingColor'))) {
            if ($this->config('loadingType') == 'spinner-1') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-spinner-1 .quform-loading-spinner,
                    .quform-%1$d .quform-loading-type-spinner-1 .quform-loading-spinner:after { border-top-color: %2$s; }',
                    $this->getId(),
                    $this->config('loadingColor')
                );
            } else if ($this->config('loadingType') == 'spinner-2') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-spinner-2 .quform-loading-spinner { background-color: %2$s; }',
                    $this->getId(),
                    $this->config('loadingColor')
                );
            } else if ($this->config('loadingType') == 'spinner-3') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-spinner-3 .quform-loading-spinner,
                    .quform-%1$d .quform-loading-type-spinner-3 .quform-loading-spinner:after { color: %2$s; }',
                    $this->getId(),
                    $this->config('loadingColor')
                );
            } else if ($this->config('loadingType') == 'spinner-4') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-spinner-4 .quform-loading-spinner:after { background-color: %2$s; }',
                    $this->getId(),
                    $this->config('loadingColor')
                );
            } else if ($this->config('loadingType') == 'spinner-5') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-spinner-5 .quform-loading-spinner { border-left-color: %2$s; }',
                    $this->getId(),
                    $this->config('loadingColor')
                );
            } else if ($this->config('loadingType') == 'spinner-6') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-spinner-6 .quform-loading-spinner-inner { color: %2$s; }',
                    $this->getId(),
                    $this->config('loadingColor')
                );
            } else if ($this->config('loadingType') == 'spinner-7') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-spinner-7 .quform-loading-spinner-inner,
                    .quform-%1$d .quform-loading-type-spinner-7 .quform-loading-spinner-inner:before,
                    .quform-%1$d .quform-loading-type-spinner-7 .quform-loading-spinner-inner:after { background-color: %2$s; color: %2$s; }',
                     $this->getId(),
                    $this->config('loadingColor')
                );
            } else if ($this->config('loadingType') == 'custom') {
                $css .= sprintf(
                    '.quform-%1$d .quform-loading-type-custom .quform-loading-inner { color: %2$s; }',
                    $this->getId(),
                    $this->config('loadingColor')
                );
            }
        }

        if ($this->config('loadingOverlay') && Quform::isNonEmptyString($this->config('loadingOverlayColor'))) {
            $css .= sprintf(
                '.quform-%1$d .quform-loading-overlay, .quform-%1$d .quform-loading.quform-loading-triggered.quform-loading-position-over-button { background-color: %2$s; }',
                $this->getId(),
                $this->config('loadingOverlayColor')
            );
        }

        foreach ($this->pages as $page) {
            $css .= $page->getCss($this->getContext());
        }

        $css .= apply_filters('quform_custom_css_' . $this->getId(), '', $this);

        return $css;
    }

    /**
     * Get the list of CSS selectors
     *
     * @return array
     */
    protected function getCssSelectors()
    {
        return array(
            'formOuter' => '%s',
            'formInner' => '%s .quform-form-inner',
            'formSuccess' => '%s .quform-success-message',
            'formSuccessIcon' => '%s .quform-success-message-icon',
            'formSuccessContent' => '%s .quform-success-message-content',
            'formTitle' => '%s .quform-form-title',
            'formDescription' => '%s .quform-form-description',
            'formElements' => '%s .quform-elements',
            'formError' => '%s .quform-error-message',
            'formErrorInner' => '%s .quform-error-message-inner',
            'formErrorTitle' => '%s .quform-error-message-title',
            'formErrorContent' => '%s .quform-error-message-content',
            'element' => '%s .quform-element',
            'elementLabel' => '%s .quform-label',
            'elementLabelText' => '%s .quform-label > label',
            'elementRequiredText' => '%s .quform-label > label > .quform-required',
            'elementInner' => '%s .quform-inner',
            'elementInput' => '%s .quform-input',
            'elementText' => '
                %1$s .select2-container--quform .select2-selection,
                %1$s .quform-field-text,
                %1$s .quform-field-email,
                %1$s .quform-field-date,
                %1$s .quform-field-time,
                %1$s .quform-field-captcha,
                %1$s .quform-field-password',
            'elementTextHover' => '
                %1$s .select2-container--quform .select2-selection:hover,
                %1$s .quform-field-text:hover,
                %1$s .quform-field-email:hover,
                %1$s .quform-field-date:hover,
                %1$s .quform-field-time:hover,
                %1$s .quform-field-captcha:hover,
                %1$s .quform-field-password:hover',
            'elementTextFocus' => '
                %1$s .select2-container--quform.select2-container--open .select2-selection,
                %1$s .quform-field-text:focus,
                %1$s .quform-field-email:focus,
                %1$s .quform-field-date:focus,
                %1$s .quform-field-time:focus,
                %1$s .quform-field-captcha:focus,
                %1$s .quform-field-password:focus,
                %1$s .quform-field-text:active,
                %1$s .quform-field-email:active,
                %1$s .quform-field-date:active,
                %1$s .quform-field-time:active,
                %1$s .quform-field-captcha:active,
                %1$s .quform-field-password:active',
            'elementTextarea' => '%s .quform-field-textarea',
            'elementTextareaHover' => '%s .quform-field-textarea:hover',
            'elementTextareaFocus' => '
                %1$s .quform-field-textarea:focus,
                %1$s .quform-field-textarea:active',
            'elementSelect' => '
                %1$s .quform-field-select,
                %1$s .quform-field-multiselect',
            'elementSelectHover' => '
                %1$s .quform-field-select:hover,
                %1$s .quform-field-multiselect:hover',
            'elementSelectFocus' => '
                %1$s .quform-field-select:focus,
                %1$s .quform-field-select:active,
                %1$s .quform-field-multiselect:focus,
                %1$s .quform-field-multiselect:active',
            'elementIcon' => '
                %1$s .select2-container--quform .select2-selection--multiple .select2-selection__rendered:before,
                %1$s .select2-container--quform .select2-selection__arrow b,
                %1$s-select2.select2-container--quform .select2-search--dropdown:before,
                %1$s .quform-field-icon',
            'elementIconHover' => '
                %1$s .select2-container--quform .select2-selection--multiple .select2-selection__rendered:hover:before,
                %1$s .select2-container--quform .select2-selection__arrow b:hover,
                %1$s-select2.select2-container--quform .select2-search--dropdown:hover:before,
                %1$s .quform-field-icon:hover',
            'timeDropdown' => '
                %s-timepicker.quform-timepicker.k-list-container.k-popup',
            'timeDropdownText' => '
                %1$s-timepicker.quform-timepicker.k-popup ul.k-list li.k-item.k-state-selected,
                %1$s-timepicker.quform-timepicker.k-popup ul.k-list li.k-item,
                %1$s-timepicker.quform-timepicker.k-popup ul.k-list li.k-item.k-state-hover',
            'select2Drop' => '
                %s-select2.select2-container--quform .select2-dropdown',
            'select2DropBorders' => '
                %1$s-timepicker.quform-timepicker.k-popup ul.k-list li.k-item.k-state-hover,
                %1$s-select2.select2-container--quform .select2-dropdown--below .select2-results__options,
                %1$s-select2.select2-container--quform .select2-results__option--highlighted[aria-selected],
                %1$s-select2.select2-container--quform .select2-search--dropdown .select2-search__field',
            'select2DropText' => '%s .select2-container--quform .select2-search--inline .select2-search__field',
            'select2DropTextFocus' => '
                %1$s-select2.select2-container--quform .select2-results__options[aria-multiselectable="true"] .select2-results__option[aria-selected="true"],
                %1$s .select2-container--quform .select2-search--inline .select2-search__field:focus,
                %1$s-select2.select2-container--quform .select2-results__option,
                %1$s-select2.quform-timepicker.k-popup ul.k-list li.k-item,
                %1$s-select2.quform-timepicker.k-popup ul.k-list li.k-item.k-state-hover',
            'elementDescription' => '%s .quform-description',
            'elementSpacer' => '%1$s .quform-spacer,
%1$s .quform-element-group.quform-group-style-bordered > .quform-spacer,
%1$s .quform-group-style-bordered > .quform-child-elements',
            'elementSubLabel' => '%s .quform-sub-label',
            'options' => '%s .quform-options',
            'option' => '%s .quform-option',
            'optionRadioButton' => '%s .quform-option .quform-field-radio',
            'optionCheckbox' => '%s .quform-option .quform-field-checkbox',
            'optionLabel' => '%1$s .quform-options .quform-option .quform-option-label',
            'optionLabelHover' => '%1$s .quform-options .quform-option .quform-option-label:hover',
            'optionLabelSelected' => '%1$s .quform-options .quform-option .quform-field:checked + .quform-option-label',
            'optionIcon' => '%1$s .quform-option .quform-option-icon',
            'optionIconSelected' => '%1$s .quform-option .quform-option-icon-selected',
            'optionText' => '%1$s .quform-option .quform-option-text',
            'optionTextSelected' => '%1$s .quform-option .quform-field:checked + .quform-option-label .quform-option-text',
            'elementError' => '%s .quform-error',
            'elementErrorInner' => '%s .quform-error > .quform-error-inner',
            'elementErrorText' => '%s .quform-error > .quform-error-inner > .quform-error-text',
            'page' => '%s .quform-element-page',
            'pageTitle' => '%s .quform-page-title',
            'pageDescription' => '%s .quform-page-description',
            'pageElements' => '%s .quform-element-page > .quform-child-elements',
            'group' => '%s .quform-element-group',
            'groupTitle' => '%s .quform-spacer > .quform-group-title-description quform-group-title',
            'groupDescription' => '%s .quform-spacer > .quform-group-title-description p.quform-group-description',
            'groupElements' => '%s .quform-element-group > .quform-spacer > .quform-child-elements',
            'pageProgress' => '%s .quform-page-progress',
            'pageProgressBar' => '%s .quform-page-progress-bar',
            'pageProgressBarText' => '%s .quform-page-progress-text',
            'pageProgressTabs' => '%s .quform-page-progress-tabs',
            'pageProgressTab' => '%s .quform-page-progress-tab',
            'pageProgressTabActive' => '%s .quform-page-progress-tab.quform-current-tab',
            'submit' => '%s .quform-element-submit',
            'submitInner' => '%s .quform-button-submit',
            'submitButton' => '%1$s .quform-button-submit button, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button',
            'submitButtonHover' => '%1$s .quform-button-submit button:hover, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button:hover',
            'submitButtonActive' => '%1$s .quform-button-submit button:active, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button:active',
            'submitButtonText' => '%1$s .quform-button-submit button .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button .quform-button-text',
            'submitButtonTextHover' => '%1$s .quform-button-submit button:hover .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button:hover .quform-button-text',
            'submitButtonTextActive' => '%1$s .quform-button-submit button:active .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button:active .quform-button-text',
            'submitButtonIcon' => '%1$s .quform-button-submit button .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button .quform-button-icon',
            'submitButtonIconHover' => '%1$s .quform-button-submit button:hover .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button:hover .quform-button-icon',
            'submitButtonIconActive' => '%1$s .quform-button-submit button:active .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-submit button:active .quform-button-icon',
            'backInner' => '%s .quform-button-back',
            'backButton' => '%1$s .quform-button-back button, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button',
            'backButtonHover' => '%1$s .quform-button-back button:hover, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button:hover',
            'backButtonActive' => '%1$s .quform-button-back button:active, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button:active',
            'backButtonText' => '%1$s .quform-button-back button .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button .quform-button-text',
            'backButtonTextHover' => '%1$s .quform-button-back button:hover .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button:hover .quform-button-text',
            'backButtonTextActive' => '%1$s .quform-button-back button:active .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button:active .quform-button-text',
            'backButtonIcon' => '%1$s .quform-button-back button .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button .quform-button-icon',
            'backButtonIconHover' => '%1$s .quform-button-back button:hover .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button:hover .quform-button-icon',
            'backButtonIconActive' => '%1$s .quform-button-back button:active .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-back button:active .quform-button-icon',
            'nextInner' => '%s .quform-button-next',
            'nextButton' => '%1$s .quform-button-next button, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button',
            'nextButtonHover' => '%1$s .quform-button-next button:hover, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button:hover',
            'nextButtonActive' => '%1$s .quform-button-next button:active, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button:active',
            'nextButtonText' => '%1$s .quform-button-next button .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button .quform-button-text',
            'nextButtonTextHover' => '%1$s .quform-button-next button:hover .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button:hover .quform-button-text',
            'nextButtonTextActive' => '%1$s .quform-button-next button:active .quform-button-text, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button:active .quform-button-text',
            'nextButtonIcon' => '%1$s .quform-button-next button .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button .quform-button-icon',
            'nextButtonIconHover' => '%1$s .quform-button-next button:hover .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button:hover .quform-button-icon',
            'nextButtonIconActive' => '%1$s .quform-button-next button:active .quform-button-icon, %1$s .quform-element-submit.quform-button-style-theme .quform-button-next button:active .quform-button-icon',
            'uploadButton' => '%1$s .quform-upload-button, %1$s .quform-button-style-theme .quform-upload-button',
            'uploadButtonHover' => '%1$s .quform-upload-button:hover, %1$s .quform-button-style-theme .quform-upload-button:hover',
            'uploadButtonActive' => '%1$s .quform-upload-button:active, %1$s .quform-button-style-theme .quform-upload-button:active',
            'uploadButtonText' => '%1$s .quform-upload-button .quform-upload-button-text, %1$s .quform-button-style-theme .quform-upload-button .quform-upload-button-text',
            'uploadButtonTextHover' => '%1$s .quform-upload-button:hover .quform-upload-button-text, %1$s .quform-button-style-theme .quform-upload-button:hover .quform-upload-button-text',
            'uploadButtonTextActive' => '%1$s .quform-upload-button:active .quform-upload-button-text, %1$s .quform-button-style-theme .quform-upload-button:active .quform-upload-button-text',
            'uploadButtonIcon' => '%1$s .quform-upload-button .quform-upload-button-icon, %1$s .quform-button-style-theme .quform-upload-button .quform-upload-button-icon',
            'uploadButtonIconHover' => '%1$s .quform-upload-button:hover .quform-upload-button-icon, %1$s .quform-button-style-theme .quform-upload-button:hover .quform-upload-button-icon',
            'uploadButtonIconActive' => '%1$s .quform-upload-button:active .quform-upload-button-icon, %1$s .quform-button-style-theme .quform-upload-button:active .quform-upload-button-icon',
            'uploadDropzone' => '%1$s .quform-upload-dropzone',
            'uploadDropzoneHover' => '%1$s .quform-upload-dropzone:hover',
            'uploadDropzoneActive' => '%1$s .quform-upload-dropzone:active',
            'uploadDropzoneText' => '%1$s .quform-upload-dropzone .quform-upload-dropzone-text',
            'uploadDropzoneTextHover' => '%1$s .quform-upload-dropzone:hover .quform-upload-dropzone-text',
            'uploadDropzoneTextActive' => '%1$s .quform-upload-dropzone:active .quform-upload-dropzone-text',
            'uploadDropzoneIcon' => '%1$s .quform-upload-dropzone .quform-upload-dropzone-icon',
            'uploadDropzoneIconHover' => '%1$s .quform-upload-dropzone:hover .quform-upload-dropzone-icon',
            'uploadDropzoneIconActive' => '%1$s .quform-upload-dropzone:active .quform-upload-dropzone-icon',
            'datepickerHeader' => '%1$s-datepicker.quform-datepicker .k-calendar .k-header, %1$s-datepicker.quform-datepicker .k-calendar .k-header .k-state-hover',
            'datepickerHeaderText' => '%s-datepicker.quform-datepicker .k-calendar .k-header .k-link',
            'datepickerHeaderTextHover' => '%s-datepicker.quform-datepicker .k-calendar .k-header .k-link:hover',
            'datepickerFooter' => '%s-datepicker.quform-datepicker .k-calendar .k-footer',
            'datepickerFooterText' => '%s-datepicker.quform-datepicker .k-calendar .k-footer .k-link',
            'datepickerFooterTextHover' => '%s-datepicker.quform-datepicker .k-calendar .k-footer .k-link:hover',
            'datepickerSelectionText' => '%s-datepicker.quform-datepicker .k-calendar td.k-state-focused .k-link',
            'datepickerSelectionTextHover' => '%s-datepicker.quform-datepicker .k-calendar td.k-state-focused .k-link:hover',
            'datepickerSelectionActiveText' => '%s-datepicker.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link',
            'datepickerSelectionActiveTextHover' => '%s-datepicker.quform-datepicker .k-calendar td.k-state-selected.k-state-focused .k-link:hover',
            'datepickerSelection' => '%s-datepicker.quform-datepicker .k-calendar td.k-state-focused',
            'datepickerSelectionActive' => '%s-datepicker.quform-datepicker .k-calendar td.k-state-selected.k-state-focused',
        );
    }

    /**
     * Get the CSS selector for the given style type
     *
     * @param   string  $type
     * @return  string
     */
    protected function getCssSelector($type)
    {
        $selector = '';
        $selectors = $this->getCssSelectors();

        if (array_key_exists($type, $selectors)) {
            $prefix = sprintf('.quform-%d', $this->getId());
            $selector = sprintf($selectors[$type], $prefix);
        }

        return $selector;
    }

    /**
     * Does the form have at least one File Upload element with enhanced upload enabled?
     *
     * @return boolean
     */
    public function hasEnhancedFileUploadElement()
    {
        foreach ($this->getRecursiveIterator() as $element) {
            if ($element instanceof Quform_Element_File && $element->config('enhancedUploadEnabled')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets whether the form been successfully submitted
     *
     * @param boolean $flag
     */
    public function setSubmitted($flag)
    {
        $this->submitted = (bool) $flag;
    }

    /**
     * Has the form been successfully submitted?
     *
     * @return boolean
     */
    public function isSubmitted()
    {
        return $this->submitted;
    }

    /**
     * Reset all form values to their default
     */
    public function reset()
    {
        foreach ($this->getRecursiveIterator() as $element) {
            if ( ! $element instanceof Quform_Element_Field) {
                continue;
            }

            switch ($this->getConfirmation()->config('resetForm')) {
                default:
                case '':
                    $element->reset();
                    break;
                case 'clear':
                    $element->setValue($element->getEmptyValue());
                    break;
                case 'keep':
                    /* Do nothing */
                    break;
            }
        }

        $this->setCurrentPageById($this->getFirstPage()->getId());
    }

    /**
     * Sets the dynamic values
     *
     * @param string|array $dynamicValues
     */
    public function setDynamicValues($dynamicValues)
    {
        if (is_string($dynamicValues)) {
            parse_str($dynamicValues, $dynamicValues);
        }

        $this->dynamicValues = $dynamicValues;
    }

    /**
     * Get the dynamic values
     *
     * @return array
     */
    public function getDynamicValues()
    {
        return $this->dynamicValues;
    }

    /**
     * Server-side conditional logic processing, determines which form elements are hidden by conditional logic
     */
    public function calculateElementVisibility()
    {
        $ancestors = array();
        $currentPage = null;
        $this->calculateElementVisibilityHelper($this->pages, $ancestors, $currentPage);
    }

    /**
     * Recursive helper function for calculateElementVisibility
     *
     * @param  array                     $elements
     * @param  array                     $ancestors
     * @param  Quform_Element_Page|null  $currentPage
     */
    protected function calculateElementVisibilityHelper($elements, &$ancestors, &$currentPage)
    {
        foreach ($elements as $element) {
            if ( ! $element instanceof Quform_Element_Field && ! $element instanceof Quform_Element_Container && ! $element instanceof Quform_Element_Html) {
                // Skip non-fields and containers
                continue;
            }

            $isConditionallyHidden = false;
            $hasNonVisibleAncestor = false;
            $elementIsEmpty = $element->isEmpty();

            if ($elementIsEmpty && $element instanceof Quform_Element_File) {
                // Check for uploaded files pending processing
                $elementName = $element->getName();

                if (
                    array_key_exists($elementName, $_FILES) &&
                    is_array($_FILES[$elementName]) &&
                    array_key_exists('error', $_FILES[$elementName]) &&
                    is_array($_FILES[$elementName]['error'])
                ) {
                    foreach ($_FILES[$elementName]['error'] as $error) {
                        if ($error == UPLOAD_ERR_OK) {
                            $elementIsEmpty = false;
                            break;
                        }
                    }
                }
            }

            // Check if this element is hidden by its ancestor's conditional logic rules
            foreach ($ancestors as $ancestor) {
                if ($ancestor->isConditionallyHidden()) {
                    $isConditionallyHidden = true;
                }

                if ( ! $ancestor->isVisible()) {
                    $hasNonVisibleAncestor = true;
                }

                if ( ! $elementIsEmpty) {
                    $ancestor->setHasNonEmptyChild(true);
                }
            }

            // If this page is hidden, so are all elements inside it
            if ( ! $element instanceof Quform_Element_Page && $currentPage instanceof Quform_Element_Page && $currentPage->isConditionallyHidden()) {
                $isConditionallyHidden = true;
            }

            // Calculate the visibility based on the logic rules
            if ( ! $isConditionallyHidden && $element->config('logicEnabled') && count($element->config('logicRules'))) {
                if ( ! $this->checkLogicAction($element->config('logicAction'), $element->config('logicMatch'), $element->config('logicRules'))) {
                    $isConditionallyHidden = true;
                }
            }

            // Set the flags on the parent groups if we have a visible or non-empty element
            foreach ($ancestors as $ancestor) {
                if ( ! $isConditionallyHidden) {
                    $ancestor->setHasVisibleChild(true);
                }
            }

            // Set the flag that this element is conditionally hidden, either by its own rules or by parent group rules
            $element->setConditionallyHidden($isConditionallyHidden);
            $element->setHasNonVisibleAncestor($hasNonVisibleAncestor);

            if ($element instanceof Quform_Element_Page) {
                $currentPage = $element;
            }

            if ($element instanceof Quform_Element_Group || $element instanceof Quform_Element_Row || $element instanceof Quform_Element_Column) {
                array_push($ancestors, $element);
                $this->calculateElementVisibilityHelper($element->getElements(), $ancestors, $currentPage);
                array_pop($ancestors);
            }
        }
    }

    /**
     * Set the current page to the one with the given ID
     *
     * @param int $pageId
     */
    public function setCurrentPageById($pageId)
    {
        foreach ($this->pages as $pages) {
            if ($pages->getId() == $pageId) {
                $this->currentPage = $pages;
            }
        }
    }

    /**
     * Get the current page being viewed/processed
     *
     * If no page is found it's set to the first page
     *
     * @return Quform_Element_Page
     */
    public function getCurrentPage()
    {
        if ( ! $this->currentPage) {
            $this->currentPage = $this->getFirstPage();
        }

        return $this->currentPage;
    }

    /**
     * Get the ID of the next page that isn't conditionally hidden
     *
     * @param   bool      $reverse  If true it will search in reverse and get the previous page
     * @return  int|null            The ID of the next page or null if none found
     */
    public function getNextPageId($reverse = false)
    {
        $pages = $reverse ? array_reverse($this->pages, true) : $this->pages;
        $currentPage = $this->getCurrentPage();
        $foundCurrentPage = false;

        foreach ($pages as $page) {
            if ($foundCurrentPage && ! $page->isConditionallyHidden()) {
                return $page->getId();
            }

            if ($page == $currentPage) {
                $foundCurrentPage = true;
            }
        }

        return null;
    }

    /**
     * Get the total number of pages
     *
     * @return integer
     */
    public function getPageCount()
    {
        return count($this->pages);
    }

    /**
     * Does the form have more than one page?
     *
     * @return boolean
     */
    public function hasPages()
    {
        return $this->getPageCount() > 1;
    }

    /**
     * Get the array of page IDs
     *
     * Send to the JavaScript to determine progress percentage
     *
     * @return array
     */
    protected function getPageIds()
    {
        $pageIds = array();

        foreach ($this->pages as $page) {
            $pageIds[] = $page->getId();
        }

        return $pageIds;
    }

    /**
     * Set the current submitted entry ID
     *
     * @param   int|null     $entryId
     * @return  Quform_Form
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * Get the current submitted entry ID
     *
     * @return int|null
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * Check the given logic data against the form data and return the action
     *
     * @param   string  $action
     * @param   string  $match
     * @param   array   $rules
     * @return  bool
     */
    public function checkLogicAction($action, $match, array $rules)
    {
        $matches = 0;
        $ruleCount = count($rules);

        for ($i = 0; $i < $ruleCount; $i++) {
            if ($this->isLogicRuleMatch($rules[$i])) {
                $matches++;
            }
        }

        if ( ! (($match == 'any' && $matches > 0) || ($match == 'all' && $matches == $ruleCount))) {
            // Invert the action, the rules don't match
            $action = ! $action;
        }

        return $action;
    }

    /**
     * Does the given logic rule pass with the current form data?
     *
     * @param   array  $rule
     * @return  bool
     */
    protected function isLogicRuleMatch(array $rule)
    {
        $element = $this->getElementById($rule['elementId']);

        if ( ! $element instanceof Quform_Element_Field) {
            return false;
        }

        return $element->isLogicRuleMatch($rule);
    }

    /**
     * Get the first page of the form
     *
     * @return Quform_Element_Page|null
     */
    public function getFirstPage()
    {
        return reset($this->pages);
    }

    /**
     * Get the last page of the form
     *
     * @return Quform_Element_Page|null
     */
    public function getLastPage()
    {
        return end($this->pages);
    }

    /**
     * Get the session storage key for this form
     *
     * @return string
     */
    public function getSessionKey()
    {
        return 'quform-' . $this->getUniqueId();
    }

    /**
     * Get the CSRF protection token
     *
     * @return string
     */
    public function getCsrfToken()
    {
        return $this->session->getToken();
    }

    /**
     * Get the recursive iterator to iterate over the form elements
     *
     * Modes:
     * RecursiveIteratorIterator::LEAVES_ONLY
     * RecursiveIteratorIterator::SELF_FIRST
     * RecursiveIteratorIterator::CHILD_FIRST
     * RecursiveIteratorIterator::CATCH_GET_CHILD
     *
     * @param  int $mode
     * @return RecursiveIteratorIterator
     */
    public function getRecursiveIterator($mode = RecursiveIteratorIterator::LEAVES_ONLY)
    {
        return new RecursiveIteratorIterator(
            new Quform_Form_Iterator($this),
            $mode
        );
    }

    /**
     * Is the given unique ID valid?
     *
     * @param   string  $id
     * @return  bool
     */
    public static function isValidUniqueId($id)
    {
        return is_string($id) && preg_match('/^[a-f0-9]{6}$/', $id);
    }

    /**
     * Generate a unique ID (6 digit hex)
     *
     * @return string
     */
    public static function generateUniqueId()
    {
        return sprintf('%06x', mt_rand(0, 0xffffff));
    }

    /**
     * @param   string  $key
     * @param   string  $default
     * @return  string
     */
    public function getTranslation($key, $default = '')
    {
        $string = $this->config($key);

        if (Quform::isNonEmptyString($string)) {
            return $string;
        }

        return $default;
    }

    /**
     * Get the default form configuration
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_form', array(
            // General - Form
            'name' => '',
            'title' => '',
            'titleTag' => 'h2',
            'description' => '',
            'active' => true,
            'inactiveMessage' => '',
            'trashed' => false,
            'ajax' => true,
            'saveEntry' => true,
            'honeypot' => true,
            'logicAnimation' => true,

            // General - Limits
            'oneEntryPerUser' => false,
            'oneEntryPer' => 'logged-in-user',
            'limitEntries' => false,
            'entryLimit' => '',
            'entryLimitReachedMessage' => sprintf('<p>%s</p>', esc_html__('This form is currently closed for submissions.', 'quform')),

            // General - Scheduling
            'enableSchedule' => false,
            'scheduleStart' => '',
            'scheduleStartMessage' => sprintf('<p>%s</p>', esc_html__('This form is not yet open for submissions.', 'quform')),
            'scheduleEnd' => '',
            'scheduleEndMessage' => sprintf('<p>%s</p>', esc_html__('This form is currently closed for submissions.', 'quform')),

            // Style - Global
            'theme' => '',
            'themePrimaryColor' => '',
            'themeSecondaryColor' => '',
            'themePrimaryForegroundColor' => '',
            'themeSecondaryForegroundColor' => '',
            'responsiveElements' => 'phone-landscape',
            'responsiveElementsCustom' => '',
            'responsiveColumns' => 'phone-landscape',
            'responsiveColumnsCustom' => '',
            'verticalElementSpacing' => '',
            'width' => '',
            'position' => '',
            'previewColor' => '',
            'styles' => array(),

            // Style - Labels
            'labelTextColor' => '',
            'labelPosition' => '',
            'labelWidth' => '150px',
            'requiredText' => '*',
            'requiredTextColor' => '',

            // Style - Fields
            'fieldSize' => '',
            'fieldWidth' => '',
            'fieldWidthCustom' => '',
            'fieldBackgroundColor' => '',
            'fieldBackgroundColorHover' => '',
            'fieldBackgroundColorFocus' => '',
            'fieldBorderColor' => '',
            'fieldBorderColorHover' => '',
            'fieldBorderColorFocus' => '',
            'fieldTextColor' => '',
            'fieldTextColorHover' => '',
            'fieldTextColorFocus' => '',
            'fieldPlaceholderStyles' => '',

            // Style - Buttons
            'buttonStyle' => 'theme',
            'buttonSize' => '',
            'buttonWidth' => '',
            'buttonWidthCustom' => '',
            'buttonAnimation' => '',
            'buttonBackgroundColor' => '',
            'buttonBackgroundColorHover' => '',
            'buttonBackgroundColorActive' => '',
            'buttonBorderColor' => '',
            'buttonBorderColorHover' => '',
            'buttonBorderColorActive' => '',
            'buttonTextColor' => '',
            'buttonTextColorHover' => '',
            'buttonTextColorActive' => '',
            'buttonIconColor' => '',
            'buttonIconColorHover' => '',
            'buttonIconColorActive' => '',
            'submitType' => 'default',
            'submitText' => '',
            'submitIcon' => '',
            'submitIconPosition' => 'right',
            'submitImage' => '',
            'submitHtml' => '',
            'nextType' => 'default',
            'nextText' => '',
            'nextIcon' => '',
            'nextIconPosition' => 'right',
            'nextImage' => '',
            'nextHtml' => '',
            'backType' => 'default',
            'backText' => '',
            'backIcon' => '',
            'backIconPosition' => 'left',
            'backImage' => '',
            'backHtml' => '',
            'backLocation' => '',
            'optionsStyle' => '',
            'optionsButtonStyle' => '',
            'optionsButtonSize' => '',
            'optionsButtonWidth' => '',
            'optionsButtonWidthCustom' => '',
            'optionsButtonIconPosition' => '',

            // Style - Pages
            'pageProgressType' => 'numbers',

            // Style - Loading
            'loadingType' => 'spinner-1',
            'loadingCustom' => '',
            'loadingPosition' => 'left',
            'loadingColor' => '',
            'loadingOverlay' => false,
            'loadingOverlayColor' => '',

            // Style - Tooltips
            'tooltipsEnabled' => true,
            'tooltipType' => 'icon',
            'tooltipEvent' => 'hover',
            'tooltipIcon' => 'qicon-question-circle',
            'tooltipStyle' => 'qtip-quform-dark',
            'tooltipCustom' => '',
            'tooltipMy' => 'left center',
            'tooltipAt' => 'right center',
            'tooltipShadow' => true,
            'tooltipRounded' => false,
            'tooltipClasses' => 'qtip-quform-dark qtip-shadow',

            // Notifications
            'notifications' => array(),
            'nextNotificationId' => 1,

            // Confirmations
            'confirmations' => array(),
            'nextConfirmationId' => 1,

            // Errors
            'errorsPosition' => '',
            'errorsIcon' => '',
            'errorEnabled' => false,
            'errorTitle' => '',
            'errorContent' => '',

            // Language
            'locale' => '',
            'rtl' => 'global',
            'dateFormatJs' => '',
            'timeFormatJs' => '',
            'dateTimeFormatJs' => '',
            'dateFormat' => '',
            'timeFormat' => '',
            'dateTimeFormat' => '',
            'messageRequired' => '',
            'pageProgressNumbersText' => '',
            'onlyOneSubmissionAllowed' => '',
            'thisFormIsCurrentlyClosed' => '',
            'formIsNotYetOpenForSubmissions' => '',
            'formIsNoLongerOpenForSubmissions' => '',

            // Database
            'databaseEnabled' => false,
            'databaseWordpress' => true,
            'databaseHost' => 'localhost',
            'databaseUsername' => '',
            'databasePassword' => '',
            'databaseDatabase' => '',
            'databaseTable' => '',
            'databaseColumns' => array(),

            // Feature cache & locales
            'hasDatepicker' => false,
            'hasTimepicker' => false,
            'hasEnhancedUploader' => false,
            'hasEnhancedSelect' => false,
            'locales' => array(),

            // Elements
            'elements' => array(),
            'nextElementId' => 1,

            // Misc
            'entriesTableColumns' => array(),
            'environment' => 'frontend'
        ));

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
