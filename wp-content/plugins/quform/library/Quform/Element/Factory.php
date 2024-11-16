<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Element_Factory
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param  Quform_Options     $options
     * @param  Quform_Session     $session
     * @param  Quform_Repository  $repository
     */
    public function __construct(Quform_Options $options, Quform_Session $session, Quform_Repository $repository)
    {
        $this->options = $options;
        $this->session = $session;
        $this->repository = $repository;
    }

    /**
     * Create and configure a form element according to the given config
     *
     * @param   array                $config  The element configuration
     * @param   Quform_Form          $form    The form instance
     * @return  Quform_Element|null           The element instance or null if the config is invalid
     */
    public function create(array $config, Quform_Form $form)
    {
        if (isset($config['type'])) {
            $type = $config['type'];

            $method = 'create' . ucfirst($type) . 'Element';
            if (method_exists($this, $method)) {
                return call_user_func_array(array($this, $method), array($config, $form));
            }

            $element = apply_filters('quform_create_element_' . $type, null, $config, $form, $this);

            if ( ! $element instanceof Quform_Element) {
                throw new InvalidArgumentException(sprintf("Method not found to create element of type '%s'", $type));
            }

            return $element;
        }

        return null;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Text
     */
    protected function createTextElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Text($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Textarea
     */
    protected function createTextareaElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Textarea($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @return  Quform_Element_Email
     */
    protected function createEmailElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Email($config['id'], $form);

        $this->configureField($element, $config, $form);

        $options = array();
        $invalidEmailMessage = $this->getConfigValue($config, 'messageEmailAddressInvalidFormat', $element);
        if (Quform::isNonEmptyString($invalidEmailMessage)) {
            $options['messages'][Quform_Validator_Email::INVALID_FORMAT] = $invalidEmailMessage;
        }

        $element->addValidator(new Quform_Validator_Email($options));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Select
     */
    protected function createSelectElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Select($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Checkbox
     */
    protected function createCheckboxElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Checkbox($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @return  Quform_Element_Radio
     */
    protected function createRadioElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Radio($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                       $config
     * @param   Quform_Form                 $form
     * @return  Quform_Element_Multiselect
     */
    protected function createMultiselectElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Multiselect($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->configureMultiOptions($element, $config);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                   $config
     * @param   Quform_Form             $form
     * @return  Quform_Element_Captcha
     */
    protected function createCaptchaElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Captcha($config['id'], $form, $this->session);

        $element->addValidator(new Quform_Validator_Required());

        $options = array(
            'session' => $this->session,
            'sessionKey' => $form->getSessionKey() . '.captcha.' . $element->getName()
        );

        $notMatchMessage = $this->getConfigValue($config, 'messageCaptchaNotMatch', $element);
        if (Quform::isNonEmptyString($notMatchMessage)) {
            $options['messages'][Quform_Validator_Captcha::NOT_MATCH] = $notMatchMessage;
        }

        $element->addValidator(new Quform_Validator_Captcha($options));

        $this->configureField($element, $config, $form);

        unset($config['showInEmail'], $config['saveToDatabase']); // Bug fix for forms created in <2.1.0

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Page
     */
    protected function createPageElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Page($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @return  Quform_Element_Group
     */
    protected function createGroupElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Group($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array               $config
     * @param   Quform_Form         $form
     * @return  Quform_Element_Row
     */
    protected function createRowElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Row($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Column
     */
    protected function createColumnElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Column($config['id'], $form);

        $this->configureContainer($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Submit
     */
    protected function createSubmitElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Submit($config['id'], $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_File
     */
    protected function createFileElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_File($config['id'], $form);

        $this->configureField($element, $config, $form);

        $options = array(
            'name' => $element->getName(),
            'required' => $this->getConfigValue($config, 'required', $element),
            'allowAllFileTypes' => $this->options->get('allowAllFileTypes')
        );

        $allowedExtensions = array();
        $allowedExtensionsStr = $this->getConfigValue($config, 'allowedExtensions', $element);

        if (Quform::isNonEmptyString($allowedExtensionsStr)) {
            $allowedExtensions = explode(',', $allowedExtensionsStr);
            $allowedExtensions = array_map('trim', array_map('strtolower', $allowedExtensions));
        }

        $options['allowedExtensions'] = $allowedExtensions;

        $maximumFileSize = $this->getConfigValue($config, 'maximumFileSize', $element);
        if (is_numeric($maximumFileSize)) {
            $options['maximumFileSize'] = $maximumFileSize * 1048576;
        }

        $minimumNumberOfFiles = $this->getConfigValue($config, 'minimumNumberOfFiles', $element);
        if (is_numeric($minimumNumberOfFiles)) {
            $options['minimumNumberOfFiles'] = (int) $minimumNumberOfFiles;

            if ($options['minimumNumberOfFiles'] > 0) {
                $options['required'] = true;
            }
        }

        $maximumNumberOfFiles = $this->getConfigValue($config, 'maximumNumberOfFiles', $element);
        if (is_numeric($maximumNumberOfFiles)) {
            $options['maximumNumberOfFiles'] = (int) $maximumNumberOfFiles;
        }

        $messageMap = array(
            'messageFileUploadRequired' => Quform_Validator_FileUpload::REQUIRED,
            'messageFileNumRequired' => Quform_Validator_FileUpload::NUM_REQUIRED,
            'messageFileTooMany' => Quform_Validator_FileUpload::TOO_MANY,
            'messageFileTooBigFilename' => Quform_Validator_FileUpload::TOO_BIG_FILENAME,
            'messageFileTooBig' => Quform_Validator_FileUpload::TOO_BIG,
            'messageNotAllowedTypeFilename' => Quform_Validator_FileUpload::NOT_ALLOWED_TYPE_FILENAME,
            'messageNotAllowedType' => Quform_Validator_FileUpload::NOT_ALLOWED_TYPE
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = $this->getConfigValue($config, $configKey, $element);

            if (Quform::isNonEmptyString($message)) {
                $options['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_FileUpload($options));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                     $config
     * @param   Quform_Form               $form
     * @return  Quform_Element_Recaptcha
     */
    protected function createRecaptchaElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Recaptcha($config['id'], $form);

        $element->addValidator(new Quform_Validator_Required());

        $provider = $this->getConfigValue($config, 'provider', $element);

        if ($provider == 'hcaptcha') {
            $options = array(
                'provider' => $provider,
                'secretKey' => $this->options->get('hcaptchaSecretKey'),
            );

            $config['hcaptchaSiteKey'] = $this->options->get('hcaptchaSiteKey');
        } elseif ($provider == 'turnstile') {
            $options = array(
                'provider' => $provider,
                'secretKey' => $this->options->get('turnstileSecretKey'),
            );

            $config['turnstileSiteKey'] = $this->options->get('turnstileSiteKey');
        } else {
            $version = $this->getConfigValue($config, 'recaptchaVersion', $element);

            if ($version == 'v3') {
                $config['recaptchaSize'] = 'invisible';
            }

            $options = array(
                'provider' => $provider,
                'secretKey' => $this->options->get('recaptchaSecretKey'),
                'version' => $version,
                'threshold' => $this->getConfigValue($config, 'recaptchaThreshold', $element)
            );

            $config['recaptchaSiteKey'] = $this->options->get('recaptchaSiteKey');
        }

        $messageMap = array(
            'messageRecaptchaMissingInputSecret' => Quform_Validator_Recaptcha::MISSING_INPUT_SECRET,
            'messageRecaptchaInvalidInputSecret' => Quform_Validator_Recaptcha::INVALID_INPUT_SECRET,
            'messageRecaptchaMissingInputResponse' => Quform_Validator_Recaptcha::MISSING_INPUT_RESPONSE,
            'messageRecaptchaInvalidInputResponse' => Quform_Validator_Recaptcha::INVALID_INPUT_RESPONSE,
            'messageRecaptchaError' => Quform_Validator_Recaptcha::ERROR,
            'messageRecaptchaScoreTooLow' => Quform_Validator_Recaptcha::SCORE_TOO_LOW
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = Quform::get($config, $configKey);

            if (Quform::isNonEmptyString($message)) {
                $options['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_Recaptcha($options));

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Html
     */
    protected function createHtmlElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Html($config['id'], $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Date
     */
    protected function createDateElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Date($config['id'], $form);

        $this->configureRequired($element, $config);
        $this->configureRequiredMessage($element, $config, $form);

        $defaultValue = $this->getConfigValue($config, 'defaultValue', $element);
        if (Quform::isNonEmptyString($defaultValue)) {
            $defaultValue = $this->parseDate($defaultValue);
            $element->setDefaultValue($defaultValue);
            $element->setValue($element->getDefaultValue());
        }

        $this->configureDynamicDefaultValue($element, $config);
        $this->configureValidators($element, $config, $form);

        $dateLocale = $this->getConfigValue($config, 'dateLocale', $element);
        $config['dateLocale'] = Quform::isNonEmptyString($dateLocale) ? $dateLocale : $form->getLocale();

        $dateFormatJs = $this->getConfigValue($config, 'dateFormatJs', $element);
        $config['dateFormatJs'] = Quform::isNonEmptyString($dateFormatJs) ? $dateFormatJs : $form->getDateFormatJs();

        $dateFormat = $this->getConfigValue($config, 'dateFormat', $element);
        $config['dateFormat'] = Quform::isNonEmptyString($dateFormat) ? $dateFormat : $form->getDateFormat();

        if ( ! Quform::isNonEmptyString($config['dateFormat'])) {
            $locale = Quform::getLocale($config['dateLocale']);
            $config['dateFormat'] = $locale['dateFormat'];
        }

        $validatorOptions = array('format' => $config['dateFormat']);

        $dateMin = $this->getConfigValue($config, 'dateMin', $element);
        if (Quform::isNonEmptyString($dateMin)) {
            $dateMin = $this->parseDate($dateMin);
            $config['dateMin'] = $dateMin;
            $validatorOptions['min'] = $dateMin;
        }

        $dateMax = $this->getConfigValue($config, 'dateMax', $element);
        if (Quform::isNonEmptyString($dateMax)) {
            $dateMax = $this->parseDate($dateMax);
            $config['dateMax'] = $dateMax;
            $validatorOptions['max'] = $dateMax;
        }

        $messageMap = array(
            'messageDateInvalidDate' => Quform_Validator_Date::INVALID_DATE,
            'messageDateTooEarly' => Quform_Validator_Date::TOO_EARLY,
            'messageDateTooLate' => Quform_Validator_Date::TOO_LATE
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = Quform::get($config, $configKey);

            if (Quform::isNonEmptyString($message)) {
                $validatorOptions['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_Date($validatorOptions));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * Parse the given date, replacing {today} and modifiers with an actual date in the format YYYY-MM-DD
     *
     * @param   string  $date
     * @return  string
     */
    protected function parseDate($date)
    {
        if (preg_match('/^\{today(|.+)?\}$/', $date,$matches)) {
            try {
                $date = new DateTime('now', new DateTimeZone('UTC'));
                $date->setTimezone(Quform::getTimezone());

                if (Quform::isNonEmptyString($matches[1])) {
                    $date->modify(substr($matches[1], 1));
                }

                $date = $date->format('Y-m-d');
            } catch (Exception $e) {
                $date = '';
            }
        }

        return $date;
    }

    /**
     * @param   array                $config
     * @param   Quform_Form          $form
     * @return  Quform_Element_Time
     */
    protected function createTimeElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Time($config['id'], $form);

        $this->configureRequired($element, $config);
        $this->configureRequiredMessage($element, $config, $form);

        $interval = $this->getConfigValue($config, 'timeInterval', $element);
        if (Quform::isNonEmptyString($interval)) {
            $interval = (string) Quform::clamp($interval, 1, 60);
            $config['timeInterval'] = $interval;
        } else {
            $config['timeInterval'] = '30';
        }

        $defaultValue = $this->getConfigValue($config, 'defaultValue', $element);
        if (Quform::isNonEmptyString($defaultValue)) {
            $defaultValue = $this->parseTime($defaultValue, $config['timeInterval']);
            $element->setDefaultValue($defaultValue);
            $element->setValue($element->getDefaultValue());
        }

        $this->configureDynamicDefaultValue($element, $config);
        $this->configureValidators($element, $config, $form);

        $timeLocale = $this->getConfigValue($config, 'timeLocale', $element);
        $config['timeLocale'] = Quform::isNonEmptyString($timeLocale) ? $timeLocale : $form->getLocale();

        $timeFormatJs = $this->getConfigValue($config, 'timeFormatJs', $element);
        $config['timeFormatJs'] = Quform::isNonEmptyString($timeFormatJs) ? $timeFormatJs : $form->getTimeFormatJs();

        $timeFormat = $this->getConfigValue($config, 'timeFormat', $element);
        $config['timeFormat'] = Quform::isNonEmptyString($timeFormat) ? $timeFormat : $form->getTimeFormat();

        if ( ! Quform::isNonEmptyString($config['timeFormat'])) {
            $locale = Quform::getLocale($config['timeLocale']);
            $config['timeFormat'] = $locale['timeFormat'];
        }

        $validatorOptions = array(
            'format' => $config['timeFormat'],
            'interval' => $config['timeInterval']
        );

        $timeMin = $this->getConfigValue($config, 'timeMin', $element);
        if (Quform::isNonEmptyString($timeMin)) {
            $timeMin = $this->parseTime($timeMin, $config['timeInterval']);
            $config['timeMin'] = $timeMin;
            $validatorOptions['min'] = $timeMin;
        }

        $timeMax = $this->getConfigValue($config, 'timeMax', $element);
        if (Quform::isNonEmptyString($timeMax)) {
            $timeMax = $this->parseTime($timeMax, $config['timeInterval']);
            $config['timeMax'] = $timeMax;
            $validatorOptions['max'] = $config['timeMax'];
        }

        $messageMap = array(
            'messageTimeInvalidTime' => Quform_Validator_Time::INVALID_TIME,
            'messageTimeTooEarly' => Quform_Validator_Time::TOO_EARLY,
            'messageTimeTooLate' => Quform_Validator_Time::TOO_LATE
        );

        foreach ($messageMap as $configKey => $messageKey) {
            $message = Quform::get($config, $configKey);

            if (Quform::isNonEmptyString($message)) {
                $validatorOptions['messages'][$messageKey] = $message;
            }
        }

        $element->addValidator(new Quform_Validator_Time($validatorOptions));

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * Parse the given time, replacing {now} and modifiers with an actual time in the format HH:MM
     *
     * @param   string  $time
     * @param   string  $interval
     * @return  string
     */
    protected function parseTime($time, $interval)
    {
        if (preg_match('/^\{now(|.+)?\}$/', $time,$matches)) {
            try {
                $time = new DateTime('now', new DateTimeZone('UTC'));
                $time->setTimezone(Quform::getTimezone());

                if (Quform::isNonEmptyString($matches[1])) {
                    $time->modify(substr($matches[1], 1));
                }
            } catch (Exception $e) {
                $time = '';
            }
        } else {
            try {
                $time = new DateTime($time);
            } catch (Exception $e) {
                $time = '';
            }
        }

        // Round time to the closest interval
        if ($time instanceof DateTime) {
            $interval = (int) $interval;
            $hours = (int) $time->format('H');
            $minutes = (int) $time->format('i');
            $minutes = ceil($minutes / $interval) * $interval;
            $time->setTime($hours, $minutes);
            $time = $time->format('H:i');
        }

        return $time;
    }

    /**
     * @param   array                  $config
     * @param   Quform_Form            $form
     * @return  Quform_Element_Hidden
     */
    protected function createHiddenElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Hidden($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Honeypot
     */
    protected function createHoneypotElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Honeypot($config['id'], $form);

        $element->addValidator(new Quform_Validator_Honeypot());

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Password
     */
    protected function createPasswordElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Password($config['id'], $form);

        $this->configureField($element, $config, $form);

        $this->setConfig($element, $config);

        return $element;
    }

    /**
     * @param   array                    $config
     * @param   Quform_Form              $form
     * @return  Quform_Element_Name
     */
    protected function createNameElement(array $config, Quform_Form $form)
    {
        $element = new Quform_Element_Name($config['id'], $form);

        $this->configureValidators($element, $config, $form);

        $this->setConfig($element, $config);

        if ($element->config('prefixEnabled')) {
            $prefixElement = $this->createSelectElement(array(
                'id' => 1,
                'label' => '',
                'required' => $element->config('prefixRequired'),
                'options' => $element->config('prefixOptions'),
                'noneOption' => $element->config('prefixNoneOption'),
                'noneOptionText' => $element->config('prefixNoneOptionText'),
                'subLabel' => $element->config('prefixSubLabel'),
                'subLabelAbove' => $element->config('prefixSubLabelAbove'),
                'defaultValue' => $element->config('prefixDefaultValue'),
                'customClass' => Quform::sanitizeClass(array($element->config('customClass'), $element->config('prefixCustomClass'))),
                'customElementClass' => $element->config('prefixCustomElementClass'),
                'messageRequired' => $element->config('messageRequired'),
            ), $form);

            $prefixElement->setBelongsTo($element);

            $element->setPart('1', $prefixElement);
        }

        if ($element->config('firstEnabled')) {
            $firstNameElement = $this->createTextElement(array(
                'id' => 2,
                'label' => '',
                'required' => $element->config('firstRequired'),
                'placeholder' => $element->config('firstPlaceholder'),
                'subLabel' => $element->config('firstSubLabel'),
                'subLabelAbove' => $element->config('firstSubLabelAbove'),
                'defaultValue' => $element->config('firstDefaultValue'),
                'customClass' => Quform::sanitizeClass(array($element->config('customClass'), $element->config('firstCustomClass'))),
                'customElementClass' => $element->config('firstCustomElementClass'),
                'autocomplete' => $element->config('firstAutocomplete'),
                'messageRequired' => $element->config('messageRequired')
            ), $form);

            $firstNameElement->setBelongsTo($element);

            $element->setPart('2', $firstNameElement);
        }

        if ($element->config('middleEnabled')) {
            $middleNameElement = $this->createTextElement(array(
                'id' => 3,
                'label' => '',
                'required' => $element->config('middleRequired'),
                'placeholder' => $element->config('middlePlaceholder'),
                'subLabel' => $element->config('middleSubLabel'),
                'subLabelAbove' => $element->config('middleSubLabelAbove'),
                'defaultValue' => $element->config('middleDefaultValue'),
                'customClass' => Quform::sanitizeClass(array($element->config('customClass'), $element->config('middleCustomClass'))),
                'customElementClass' => $element->config('middleCustomElementClass'),
                'autocomplete' => $element->config('middleAutocomplete'),
                'messageRequired' => $element->config('messageRequired')
            ), $form);

            $middleNameElement->setBelongsTo($element);

            $element->setPart('3', $middleNameElement);
        }

        if ($element->config('lastEnabled')) {
            $lastNameElement = $this->createTextElement(array(
                'id' => 4,
                'label' => '',
                'required' => $element->config('lastRequired'),
                'placeholder' => $element->config('lastPlaceholder'),
                'subLabel' => $element->config('lastSubLabel'),
                'subLabelAbove' => $element->config('lastSubLabelAbove'),
                'defaultValue' => $element->config('lastDefaultValue'),
                'customClass' => Quform::sanitizeClass(array($element->config('customClass'), $element->config('lastCustomClass'))),
                'customElementClass' => $element->config('lastCustomElementClass'),
                'autocomplete' => $element->config('lastAutocomplete'),
                'messageRequired' => $element->config('messageRequired')
            ), $form);

            $lastNameElement->setBelongsTo($element);

            $element->setPart('4', $lastNameElement);
        }

        if ($element->config('suffixEnabled')) {
            $suffixElement = $this->createTextElement(array(
                'id' => 5,
                'label' => '',
                'required' => $element->config('suffixRequired'),
                'placeholder' => $element->config('suffixPlaceholder'),
                'subLabel' => $element->config('suffixSubLabel'),
                'subLabelAbove' => $element->config('suffixSubLabelAbove'),
                'defaultValue' => $element->config('suffixDefaultValue'),
                'customClass' => Quform::sanitizeClass(array($element->config('customClass'), $element->config('suffixCustomClass'))),
                'customElementClass' => $element->config('suffixCustomElementClass'),
                'messageRequired' => $element->config('messageRequired')
            ), $form);

            $suffixElement->setBelongsTo($element);

            $element->setPart('5', $suffixElement);
        }

        $this->configureDynamicDefaultValue($element, $config); // Must be after adding parts

        return $element;
    }

    /**
     * Configure container elements (Group, Page)
     *
     * @param   Quform_Element_Container  $container
     * @param   array                     $config
     * @param   Quform_Form               $form
     * @return  array
     */
    protected function configureContainer(Quform_Element_Container $container, array $config, Quform_Form $form)
    {
        $elements = $this->getConfigValue($config, 'elements', $container);

        if (is_array($elements)) {
            foreach ($elements as $eConfig) {
                $element = $this->create($eConfig, $form);

                if ($element instanceof Quform_Element) {
                    if (in_array($form->config('environment'), array('viewEntry', 'editEntry', 'listEntry')) && in_array(get_class($element), array('Quform_Element_Captcha', 'Quform_Element_Recaptcha'))) {
                        // Ignore captcha when interacting with entries
                        continue;
                    }

                    $container->addElement($element);
                }
            }
        }

        return $config;
    }

    /**
     * Configure common field settings
     *
     * @param   array                 $config
     * @param   Quform_Form           $form
     * @param   Quform_Element_Field  $element
     */
    public function configureField(Quform_Element_Field $element, array $config, Quform_Form $form)
    {
        if ( ! in_array(get_class($element), array('Quform_Element_Captcha', 'Quform_Element_Recaptcha', 'Quform_Element_File'))) { // Captcha always have a required validator, file has it's own
            $this->configureRequired($element, $config);
        }

        $this->configureRequiredMessage($element, $config, $form);
        $this->configureDefaultValue($element, $config);
        $this->configureDynamicDefaultValue($element, $config);
        $this->configureMaxLength($element, $config, $form);
        $this->configureFilters($element, $config);
        $this->configureValidators($element, $config, $form);
    }

    /**
     * @param Quform_Element_Field $element
     * @param array $config
     */
    protected function configureRequired(Quform_Element_Field $element, array $config)
    {
        if ($this->getConfigValue($config, 'required', $element)) {
            $element->addValidator(new Quform_Validator_Required());
        }
    }

    /**
     * @param Quform_Element_Field $element
     * @param array $config
     * @param Quform_Form $form
     */
    protected function configureRequiredMessage(Quform_Element_Field $element, array $config, Quform_Form $form)
    {
        if ($element->hasValidator('required')) {
            $requiredMessage = $this->getConfigValue($config, 'messageRequired', $element);

            if ( ! Quform::isNonEmptyString($requiredMessage)) {
                $requiredMessage = $form->config('messageRequired');
            }

            if (Quform::isNonEmptyString($requiredMessage)) {
                $element->getValidator('required')->setConfig('messages.' . Quform_Validator_Required::REQUIRED, $requiredMessage);
            }
        }
    }

    /**
     * @param Quform_Element_Field $element
     * @param array $config
     */
    protected function configureDefaultValue(Quform_Element_Field $element, array $config)
    {
        $defaultValue = $this->getConfigValue($config, 'defaultValue', $element);
        if ($defaultValue !== null) {
            $element->setDefaultValue($defaultValue);
            $element->setValue($element->getDefaultValue());
        }
    }

    /**
     * @param Quform_Element_Field $element
     * @param array $config
     */
    protected function configureDynamicDefaultValue(Quform_Element_Field $element, array $config)
    {
        if ($this->getConfigValue($config, 'dynamicDefaultValue', $element)) {
            $dynamicKey = $this->getConfigValue($config, 'dynamicKey', $element);
            if (Quform::isNonEmptyString($dynamicKey)) {
                $element->setDynamicDefaultValue($dynamicKey);
            }
        }
    }

    /**
     * @param Quform_Element_Field $element
     * @param array $config
     * @param Quform_Form $form
     */
    protected function configureMaxLength(Quform_Element_Field $element, array $config, Quform_Form $form)
    {
        $maxLength = $this->getConfigValue($config, 'maxLength', $element);
        if (is_numeric($maxLength)) {
            $lengthValidator = new Quform_Validator_Length(array(
                'max' => $maxLength
            ));

            $lengthValidatorMessage = $this->getConfigValue($config, 'messageLengthTooLong', $element);

            if (Quform::isNonEmptyString($lengthValidatorMessage)) {
                $lengthValidator->setConfig('messages.' . Quform_Validator_Length::TOO_LONG, $lengthValidatorMessage);
            }

            $element->addValidator($lengthValidator);
        }
    }

    /**
     * @param Quform_Element_Field $element
     * @param array $config
     */
    protected function configureFilters(Quform_Element_Field $element, array $config)
    {
        $filters = $this->getConfigValue($config, 'filters', $element);
        if (is_array($filters)) {
            foreach ($filters as $fConfig) {
                if (isset($fConfig['type'])) {
                    $fClass = 'Quform_Filter_' . ucfirst($fConfig['type']);
                    if (class_exists($fClass)) {
                        $element->addFilter(new $fClass($fConfig));
                    }
                }
            }
        }
    }

    /**
     * @param Quform_Element_Field $element
     * @param array $config
     * @param Quform_Form $form
     */
    protected function configureValidators(Quform_Element_Field $element, array $config, Quform_Form $form)
    {
        $validators = $this->getConfigValue($config, 'validators', $element);

        if (is_array($validators)) {
            foreach ($validators as $vConfig) {
                if (isset($vConfig['type'])) {
                    $vClass = 'Quform_Validator_' . ucfirst($vConfig['type']);

                    if (class_exists($vClass)) {
                        if ($vClass == 'Quform_Validator_Email') {
                            $vConfig['charset'] = $form->getCharset();
                        } else if ($vClass == 'Quform_Validator_Duplicate') {
                            $vConfig['element'] = $element;
                            $vConfig['repository'] = $this->repository;
                        }

                        $element->addValidator(new $vClass($vConfig));
                    }
                }
            }
        }
    }

    /**
     * @param   Quform_Element_Multi  $element
     * @param   array                 $config
     * @return  array
     */
    protected function configureMultiOptions(Quform_Element_Multi $element, array $config)
    {
        $options = $this->getConfigValue($config, 'options', $element);
        if (is_array($options)) {
            $element->addOptions($options);
        }

        if ($this->getConfigValue($config, 'inArrayValidator', $element)) {
            $haystack = array();

            foreach ($element->getOptions() as $option) {
                if (isset($option['options'])) {
                    foreach ($option['options'] as $optgroupOption) {
                        $haystack[] = $optgroupOption['value'];
                    }
                } else {
                    $haystack[] = $option['value'];
                }
            }

            if ($element instanceof Quform_Element_Checkbox || $element instanceof Quform_Element_Multiselect) {
                $element->addValidator(new Quform_Validator_Array(array(
                    'validator' => new Quform_Validator_InArray(array(
                        'haystack' => $haystack
                    ))
                )));
            } else {
                $element->addValidator(new Quform_Validator_InArray(array(
                    'haystack' => $haystack
                )));
            }
        }

        return $config;
    }

    /**
     * Remove redundant data from the $config array and set the remaining data as the element config
     *
     * @param  Quform_Element  $element
     * @param  array           $config
     */
    public function setConfig(Quform_Element $element, array $config)
    {
        unset($config['elements'], $config['filters'], $config['validators']);

        $element->setConfig($config);
    }

    /**
     * Get the config value with the given key, if it doesn't exist it will get the default config value from the element
     *
     * @param   array           $config
     * @param                   $key
     * @param   Quform_Element  $element
     * @return  mixed
     */
    public function getConfigValue(array $config, $key, Quform_Element $element)
    {
        $value = Quform::get($config, $key);

        if ($value === null) {
            $value = Quform::get(call_user_func(array(get_class($element), 'getDefaultConfig')), $key);
        }

        return $value;
    }
}
