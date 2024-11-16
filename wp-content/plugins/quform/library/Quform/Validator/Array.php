<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_Array extends Quform_Validator_Abstract
{
    const INVALID = 'arrayInvalid';

    /**
     * @var Quform_Validator_Abstract
     */
    protected $validator;

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setValidator($options['validator']);
        unset($options['validator']);

        parent::__construct($options);
    }

    /**
     * @param Quform_Validator_Abstract $validator
     */
    public function setValidator(Quform_Validator_Abstract $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return Quform_Validator_Abstract
     */
    public function getValidator()
    {
        return $this->validator;
    }

    /**
     * Run the set validator against each value in the given array
     *
     * @param   array    $values
     * @return  boolean
     */
    public function isValid($values)
    {
        $this->reset();

        if ( ! is_array($values)) {
            $this->error(self::INVALID);
            return false;
        }

        foreach ($values as $value) {
            if ( ! $this->validator->isValid($value)) {
                $this->setMessage($this->validator->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * Get all message templates or the single message with the given key
     *
     * @param   string|null   $key
     * @return  array|string
     */
    public static function getMessageTemplates($key = null)
    {
        $messageTemplates = array(
            self::INVALID => __('Invalid data type, array expected',  'quform')
        );

        if (is_string($key)) {
            return array_key_exists($key, $messageTemplates) ? $messageTemplates[$key] : null;
        }

        return $messageTemplates;
    }

    /**
     * Get the default config for this validator
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_validator_array', array(
            'messages' => array(
                self::INVALID => ''
            )
        ));

        $config['type'] = 'array';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
