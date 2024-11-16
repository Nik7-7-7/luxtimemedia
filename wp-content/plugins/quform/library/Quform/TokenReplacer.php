<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_TokenReplacer
{
    /**
     * Newline for email content
     *
     * @var string
     */
    const NEWLINE = "\r\n";

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param Quform_Options $options
     */
    public function __construct(Quform_Options $options)
    {
        $this->options = $options;
    }

    /**
     * Replace variables in the given text that are unaffected by form submission
     *
     * @param   string       $text
     * @param   string       $format
     * @param   Quform_Form  $form
     * @return  string
     */
    public function replaceVariablesPreProcess($text, $format, Quform_Form $form)
    {
        if ( ! Quform::isNonEmptyString($text)) {
            return '';
        }

        if (strpos($text, '{') === false) {
            return $text;
        }

        return preg_replace_callback('/({(.+?)})/', function ($matches) use ($format, $form) {
            return $this->replaceVariablePreProcess($matches, $format, $form);
        }, $text);
    }

    /**
     * Handle the callback for replaceVariablesPreProcess
     *
     * @param   array        $matches  The matches array
     * @param   string       $format   The format of the replacements: 'text', 'html', 'url' or 'rawurl'
     * @param   Quform_Form  $form     The form instance
     * @return  string
     */
    protected function replaceVariablePreProcess($matches, $format, Quform_Form $form)
    {
        $replaced = $matches[1];
        $token = $this->parseToken($matches[2]);

        switch ($token['name']) {
            case 'site_title':
                $replaced = get_bloginfo('name');
                break;
            case 'site_tagline':
                $replaced = get_bloginfo('description');
                break;
            case 'ip':
                $replaced = Quform::getClientIp();
                break;
            case 'post':
                $replaced = Quform::getPostProperty(count($token['params']) ? key($token['params']) : 'ID');
                break;
            case 'custom_field':
                $replaced = Quform::getPostMeta(count($token['params']) ? key($token['params']) : '');
                break;
            case 'url':
                $replaced = Quform::getCurrentUrl();
                break;
            case 'user':
                $replaced = Quform::getUserProperty(count($token['params']) ? key($token['params']) : 'display_name');
                break;
            case 'user_meta':
                $replaced = Quform::getUserMeta(count($token['params']) ? key($token['params']) : '');
                break;
            case 'referring_url': // TODO should this use entry source url if set
                $replaced = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                break;
            case 'user_agent':
                $replaced = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
                break;
            case 'date':
                if (isset($token['params']['format'])) {
                    $format = $token['params']['format'];
                } else {
                    $format = $this->getDateFormat($form);
                }

                $replaced = Quform::date($format);
                break;
            case 'time':
                if (isset($token['params']['format'])) {
                    $format = $token['params']['format'];
                } else {
                    $format = $this->getTimeFormat($form);
                }

                $replaced = Quform::date($format);
                break;
            case 'datetime':
                if (isset($token['params']['format'])) {
                    $format = $token['params']['format'];
                } else {
                    $format = $this->getDateTimeFormat($form);
                }

                $replaced = Quform::date($format);
                break;
            case 'uniqid':
                $prefix = isset($token['params']['prefix']) ? $token['params']['prefix'] : '';
                $moreEntropy = isset($token['params']['moreEntropy']) && $token['params']['moreEntropy'] === 'true';

                $replaced = uniqid($prefix, $moreEntropy);
                break;
        }

        $replaced = apply_filters('quform_replace_variables_pre_process', $replaced, $token, $format);

        if ($format == 'url') {
            $replaced = urlencode($replaced);
        } elseif ($format == 'rawurl') {
            $replaced = rawurlencode($replaced);
        }

        return $replaced;
    }

    /**
     * Get the format for the {date} variable
     *
     * @param   Quform_Form  $form  The form instance
     * @return  string
     */
    protected function getDateFormat(Quform_Form $form)
    {
        $format = $form->getDateFormat();

        if ( ! Quform::isNonEmptyString($format)) {
            $locale = Quform::getLocale($form->getLocale());
            $format = $locale['dateFormat'];
        }

        return $format;
    }

    /**
     * Get the format for the {time} variable
     *
     * @param   Quform_Form  $form  The form instance
     * @return  string
     */
    protected function getTimeFormat(Quform_Form $form)
    {
        $format = $form->getTimeFormat();

        if ( ! Quform::isNonEmptyString($format)) {
            $locale = Quform::getLocale($form->getLocale());
            $format = $locale['timeFormat'];
        }

        return $format;
    }

    /**
     * Get the format for the {datetime} variable
     *
     * @param   Quform_Form  $form  The form instance
     * @return string
     */
    protected function getDateTimeFormat(Quform_Form $form)
    {
        $format = $form->getDateTimeFormat();

        if ( ! Quform::isNonEmptyString($format)) {
            $locale = Quform::getLocale($form->getLocale());
            $format = $locale['dateTimeFormat'];
        }

        return $format;
    }

    /**
     * Replace all variables in the given text
     *
     * @param   string       $text    The text to check for replacements
     * @param   string       $format  The format of the replacements: 'text', 'html', 'url' or 'rawurl'
     * @param   Quform_Form  $form    The form instance
     * @return  string                The text with variables replaced
     */
    public function replaceVariables($text, $format, Quform_Form $form)
    {
        if ( ! Quform::isNonEmptyString($text)) {
            return '';
        }

        if (strpos($text, '{') === false) {
            return $text;
        }

        return preg_replace_callback('/({(.+?)})/', function ($matches) use ($format, $form) {
            return $this->replaceVariable($matches, $format, $form);
        }, $text);
    }

    /**
     * Handle the callback for replaceVariables
     *
     * @param   array        $matches  The matches array
     * @param   string       $format   The format of the replacements: 'text', 'html', 'url' or 'rawurl'
     * @param   Quform_Form  $form     The form instance
     * @return  string
     */
    protected function replaceVariable($matches, $format, Quform_Form $form)
    {
        $replaced = $original = $matches[1];
        $token = $this->parseToken($matches[2]);

        switch ($token['name']) {
            case 'post':
                $replaced = Quform::getPostProperty(count($token['params']) ? key($token['params']) : 'ID', Quform::get($_POST, 'post_id'));
                break;
            case 'custom_field':
                $replaced = Quform::getPostMeta(count($token['params']) ? key($token['params']) : '', Quform::get($_POST, 'post_id'));
                break;
            case 'referring_url':
                $replaced = Quform::get($_POST, 'referring_url');
                break;
            case 'default_email_address':
                $replaced = $this->options->get('defaultEmailAddress');
                break;
            case 'default_email_name':
                $replaced = $this->options->get('defaultEmailName');
                break;
            case 'default_from_email_address':
                $replaced = $this->options->get('defaultFromEmailAddress');
                break;
            case 'default_from_email_name':
                $replaced = $this->options->get('defaultFromEmailName');
                break;
            case 'admin_email':
                $replaced = get_bloginfo('admin_email');
                break;
            case 'element':
                $replaced = $this->replaceElement($token, $format, $form);
                break;
            case 'form_name':
                $replaced = $form->config('name');
                break;
            case 'entry_id':
                $replaced = $form->getEntryId();
                break;
            case 'all_form_data':
                $replaced = $this->replaceAllSubmittedData($token, $format, $form);
                break;
        }

        if ($replaced === $original) {
            $replaced = $this->replaceVariablesPreProcess($original, $format, $form);
        } else {
            $replaced = apply_filters('quform_replace_variables', $replaced, $token, $format, $form);

            if ($format == 'url') {
                $replaced = urlencode($replaced);
            } elseif ($format == 'rawurl') {
                $replaced = rawurlencode($replaced);
            }
        }

        return $replaced;
    }

    /**
     * Replace the element placeholder with its value
     *
     * @param   array        $token   The token parts
     * @param   string       $format  The format of the replacements: 'text', 'html', 'url' or 'rawurl'
     * @param   Quform_Form  $form    The form instance
     * @return  string
     */
    protected function replaceElement($token, $format, Quform_Form $form)
    {
        $value = '';

        if (isset($token['params']['id'])) {
            $id = $token['params']['id'];
            $part = null;

            if (strpos($id, '.') !== false) {
                list($id, $part) = explode('.', $id, 2);
            }

            $element = $form->getElementById($id);

            if ($element instanceof Quform_Element_Field && ! $element->isConditionallyHidden()) {
                $format = isset($token['params']['format']) ? $token['params']['format'] : $format;
                $separator = isset($token['params']['separator']) ? $token['params']['separator'] : ', ';

                if (Quform::isNonEmptyString($part)) {
                    // Get a single part from an array value
                    $elementValue = $element->getValue();

                    if (is_array($elementValue)) {
                        $partValue = Quform::get($elementValue, $part, '');

                        if (is_scalar($partValue)) {
                            $value = $format == 'html' ? Quform::escape($partValue) : $partValue;
                        }
                    }
                } else {
                    $value = $format == 'html' ? $element->getValueHtml() : $element->getValueText($separator);
                }

                $value = apply_filters('quform_element_token_value_' . $element->getIdentifier(), $value, $element, $format, $separator, $token);
            }
        }

        return $value;
    }

    /**
     * Replace the token to display all submitted form data
     *
     * @param   array        $token   The token parts
     * @param   string       $format  The format of the replacements: 'text', 'html', 'url' or 'rawurl'
     * @param   Quform_Form  $form    The form instance
     * @return  string
     */
    protected function replaceAllSubmittedData($token, $format, Quform_Form $form)
    {
        $showEmptyFields = isset($token['params']['showEmptyFields']) && $token['params']['showEmptyFields'] === 'true';

        if ($format == 'html') {
            $content = $this->renderAllSubmittedDataHtml($showEmptyFields, $form);
        } else {
            $content = $this->renderAllSubmittedDataText($showEmptyFields, $form);
        }

        return $content;
    }

    /**
     * Render the submitted form data in plain text
     *
     * @param   bool         $showEmptyFields  Whether to show empty fields
     * @param   Quform_Form  $form             The form instance
     * @return  string
     */
    protected function renderAllSubmittedDataText($showEmptyFields, Quform_Form $form)
    {
        $content = '';

        foreach ($form->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
            if ( ! $element instanceof Quform_Element_Field && ! $element instanceof Quform_Element_Container && ! $element instanceof Quform_Element_Html) {
                continue;
            }

            // Skip hidden elements
            if ($element->isHidden()) {
                continue;
            }

            // Skip empty elements
            if ($element->isEmpty() && ! $showEmptyFields) {
                continue;
            }

            if ($element instanceof Quform_Element_Html) {
                if ($element->config('showInEmail')) {
                    $content .= $element->getContent('text');
                    $content .= self::NEWLINE . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Group) {
                if ($element->config('showLabelInEmail') && Quform::isNonEmptyString($label = $element->getLabel())) {
                    $content .= str_repeat('=', 25) . self::NEWLINE;
                    $content .= $label . self::NEWLINE;
                    $content .= str_repeat('=', 25);
                    $content .= self::NEWLINE . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Field) {
                if ($element->config('showInEmail')) {
                    $content .= $element->getAdminLabel() . self::NEWLINE;
                    $content .= str_repeat('-', 25) . self::NEWLINE;
                    $content .= $element->getValueText(self::NEWLINE);
                    $content .= self::NEWLINE . self::NEWLINE;
                }
            }
        }

        $content = apply_filters('quform_all_form_data_text', $content, $form, $showEmptyFields);

        return $content;
    }

    /**
     * Render the HTML for the table containing all submitted form data
     *
     * @param   bool         $showEmptyFields  Whether to show empty fields
     * @param   Quform_Form  $form             The form instance
     * @return  string
     */
    protected function renderAllSubmittedDataHtml($showEmptyFields, Quform_Form $form)
    {
        $content = '<table width="100%" cellpadding="10" cellspacing="0" border="0" style="table-layout: fixed; background: #ffffff; border-spacing: 0; border-collapse: separate; border-bottom: 1px solid #d4d4d4; box-shadow: 0 2px 7px 0 rgba(0, 0, 0, 0.07);">' . self::NEWLINE;

        foreach ($form->getRecursiveIterator(RecursiveIteratorIterator::SELF_FIRST) as $element) {
            if ( ! $element instanceof Quform_Element_Field && ! $element instanceof Quform_Element_Container && ! $element instanceof Quform_Element_Html) {
                continue;
            }

            // Skip hidden elements
            if ($element->isHidden()) {
                continue;
            }

            // Skip empty elements
            if ($element->isEmpty() && ! $showEmptyFields) {
                continue;
            }

            if ($element instanceof Quform_Element_Html) {
                if ($element->config('showInEmail')) {
                    $content .= '<tr><td valign="top" style="padding: 10px; font-family: Helvetica, Arial, sans-serif; font-size: 16px; color: #282828; line-height: 130%; border: 1px solid #d4d4d4; border-bottom: 0; word-wrap: break-word;">' . $element->getContent() . '</td></tr>' . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Page) {
                if ($element->config('showLabelInEmail') && Quform::isNonEmptyString($label = $element->getLabel())) {
                    $content .= '<tr><td valign="top" style="padding: 10px; font-family: Helvetica, Arial, sans-serif; font-size: 22px; font-weight: bold; background-color: #c73412; color: #ffffff; border-bottom: 1px solid #e14e2c; word-wrap: break-word;">' . esc_html($label) . '</td></tr>' . self::NEWLINE;
                }
            }  else if ($element instanceof Quform_Element_Group) {
                if ($element->config('showLabelInEmail') && Quform::isNonEmptyString($label = $element->getLabel())) {
                    $content .= '<tr><td valign="top" style="padding: 10px; font-family: Helvetica, Arial, sans-serif; font-size: 17px; background-color: #c73412; color: #ffffff; word-wrap: break-word;">' . esc_html($label) . '</td></tr>' . self::NEWLINE;
                }
            } else if ($element instanceof Quform_Element_Field) {
                if ($element->config('showInEmail')) {
                    $content .= '<tr bgcolor="#efefef"><td valign="top" style="padding: 10px; font-family: Helvetica, Arial, sans-serif; font-size: 15px; font-weight: bold; color: #282828; border: 1px solid #d4d4d4; border-bottom: 0; word-wrap: break-word;">' . esc_html($element->getAdminLabel()) . '</td></tr>' . self::NEWLINE;
                    $content .= '<tr bgcolor="#fcfcfc"><td valign="top" style="padding: 10px; font-family: Helvetica, Arial, sans-serif; font-size: 14px; color: #282828; line-height: 130%; border: 1px solid #d4d4d4; border-bottom-color: #fff; word-wrap: break-word;">' . $element->getValueHtml() . '</td></tr>' . self::NEWLINE;
                }
            }
        }

        $content .= '</table>' . self::NEWLINE;

        $content = apply_filters('quform_all_form_data_html', $content, $form, $showEmptyFields);

        return $content;
    }

    /**
     * Parse a single token into an array with the name and parameters
     *
     * @param   string  $token
     * @return  array
     */
    protected function parseToken($token)
    {
        $parts = explode('|', $token);

        // Remove token name and leave params in $parts
        $name = trim(array_shift($parts));

        // Build the params array
        $params = array();
        foreach ($parts as $part) {
            $paramParts = explode(':', $part, 2);
            $params[$paramParts[0]] = isset($paramParts[1]) ? $paramParts[1] : true; // A parameter without a value is just "true"
        }

        return array(
            'name' => $name,
            'params' => $params
        );
    }
}
