<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Block
{
    /**
     * @var Quform_Form_Controller
     */
    protected $controller;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @param Quform_Form_Controller $controller
     * @param Quform_Repository $repository
     */
    public function __construct(Quform_Form_Controller $controller, Quform_Repository $repository)
    {
        $this->controller = $controller;
        $this->repository = $repository;
    }

    /**
     * Register the block
     */
    public function register()
    {
        if ( ! function_exists('register_block_type')) {
            return;
        }

        $asset = include QUFORM_ADMIN_PATH . '/js/block/build/index.asset.php';

        wp_register_script(
            'quform-block',
            Quform::adminUrl('js/block/build/index.js'),
            $asset['dependencies'],
            $asset['version'],
            true
        );

        wp_localize_script('quform-block', 'quformBlockL10n', array(
            'forms' => $this->getForms()
        ));

        if (function_exists('gutenberg_get_jed_locale_data')) {
            $localeData = json_encode(gutenberg_get_jed_locale_data('quform'));

            wp_add_inline_script(
                'quform-block',
                "wp.i18n.setLocaleData($localeData, 'quform')",
                'before'
            );
        }

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations('quform-block', 'quform', QUFORM_PATH . '/languages');
        }

        register_block_type('quform/form', array(
            'editor_script' => 'quform-block',
            'render_callback' => array($this, 'render')
        ));
    }

    /**
     * Get the forms to select from
     *
     * @return array
     */
    protected function getForms()
    {
        $orderBy = get_user_meta(get_current_user_id(), 'quform_forms_order_by', true);
        $order = get_user_meta(get_current_user_id(), 'quform_forms_order', true);
        $forms = array(array('value' => '', 'label' => __('Please select', 'quform')));

        foreach ($this->repository->all(true, $orderBy, $order) as $form) {
            $forms[] = array('value' => $form['id'], 'label' => $form['name']);
        }

        return $forms;
    }

    /**
     * Render the block
     *
     * @param   array   $attributes
     * @param   string  $content
     * @return  string
     */
    public function render($attributes, $content)
    {
        $options = wp_parse_args($attributes, array(
            'id' => '',
            'show_title' => true,
            'show_description' => true,
            'popup' => false,
            'content' => '',
            'values' => ''
        ));

        return $this->controller->form($options);
    }
}
