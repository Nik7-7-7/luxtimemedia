<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Form_Factory
{
    /**
     * @var Quform_Element_Factory
     */
    protected $elementFactory;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_TokenReplacer
     */
    protected $tokenReplacer;

    /**
     * @param  Quform_Element_Factory  $elementFactory
     * @param  Quform_Options          $options
     * @param  Quform_Session          $session
     * @param  Quform_TokenReplacer    $tokenReplacer
     */
    public function __construct(
        Quform_Element_Factory $elementFactory,
        Quform_Options $options,
        Quform_Session $session,
        Quform_TokenReplacer $tokenReplacer
    ) {
        $this->elementFactory = $elementFactory;
        $this->options = $options;
        $this->session = $session;
        $this->tokenReplacer = $tokenReplacer;
    }

    /**
     * Create and configure the Quform_Form instance
     *
     * @param   array        $config  The form configuration
     * @return  Quform_Form           The configured form instance
     */
    public function create(array $config = array())
    {
        $config = apply_filters('quform_form_factory_pre_create', $config);

        if ( ! array_key_exists('uniqueId', $config) || ! Quform_Form::isValidUniqueId($config['uniqueId'])) {
            $config['uniqueId'] = Quform_Form::generateUniqueId();
        }

        $form = new Quform_Form($config['id'], $config['uniqueId'], $this->session, $this->tokenReplacer, $this->options);

        $form->setCharset(get_bloginfo('charset'));
        $form->setIsActive($this->getConfigValue($config, 'active'));

        if (array_key_exists('dynamicValues', $config)) {
            $form->setDynamicValues($config['dynamicValues']);
        }

        // Add notifications
        foreach ($this->getConfigValue($config, 'notifications') as $notification) {
            $form->addNotification(new Quform_Notification($notification, $form, $this->options));
        }

        // Add confirmations
        foreach ($this->getConfigValue($config, 'confirmations') as $confirmation) {
            $form->addConfirmation(new Quform_Confirmation($confirmation, $form));
        }

        // Save config parts we need later
        $elements = $this->getConfigValue($config, 'elements');

        // Clean up the config & set it on the form
        unset($config['notifications'], $config['confirmations'], $config['elements']);

        if (array_key_exists('entryId', $config)) {
            $form->setEntryId($config['entryId']);
            unset($config['entryId']);
        }

        $form->setConfig($config);

        // Add form elements
        foreach ($elements as $eConfig) {
            $page = $this->elementFactory->create($eConfig, $form);

            if ($page instanceof Quform_Element_Page) {
                $form->addPage($page);
            }
        }

        // Add honeypot element
        $lastPage = $form->getLastPage();
        if ($form->config('honeypot') && ! in_array($form->config('environment'), array('viewEntry', 'editEntry', 'listEntry')) && $lastPage instanceof Quform_Element_Page) {
            $lastPage->addElement($this->elementFactory->create(array(
                'type' => 'honeypot',
                'id' => 0
            ), $form));
        }

        return $form;
    }

    /**
     * Get the value from $config with the given $key
     *
     * If the value does not exist, it returns the value from the Quform_Form default config
     *
     * @param   array   $config
     * @param   string  $key
     * @return  mixed
     */
    protected function getConfigValue(array $config, $key)
    {
        $value = Quform::get($config, $key);

        if ($value === null) {
            $value = Quform::get(Quform_Form::getDefaultConfig(), $key);
        }

        return $value;
    }
}
