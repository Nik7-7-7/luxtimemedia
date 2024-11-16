<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Captcha
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
     * @param Quform_Repository $repository
     * @param Quform_Form_Factory $factory
     */
    public function __construct(Quform_Repository $repository, Quform_Form_Factory $factory)
    {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * Handle the Ajax request to regenerate the captcha image
     *
     * Sends the base64 encoded image data in JSON
     */
    public function regenerate()
    {
        $formId = (int) Quform::get($_GET, 'quform_form_id');
        $elementId = (int) Quform::get($_GET, 'quform_element_id');
        $uniqueId = Quform_Form::isValidUniqueId(Quform::get($_GET, 'quform_unique_id')) ? Quform::get($_GET, 'quform_unique_id') : null;

        if ( ! $formId || ! $elementId || ! $uniqueId) {
            wp_send_json(array('type' => 'error'));
        }

        $config = $this->repository->getConfig($formId);

        if ( ! is_array($config)) {
            wp_send_json(array('type' => 'error'));
        }

        $config['uniqueId'] = $uniqueId;

        $form = $this->factory->create($config);

        if ( ! ($form instanceof Quform_Form)) {
            wp_send_json(array('type' => 'error'));
        }

        $element = $form->getElementById($elementId);

        if ( ! $element instanceof Quform_Element_Captcha) {
            wp_send_json(array('type' => 'error'));
        }

        wp_send_json(array(
            'type' => 'success',
            'image' => $element->generateImageData()
        ));
    }
}
