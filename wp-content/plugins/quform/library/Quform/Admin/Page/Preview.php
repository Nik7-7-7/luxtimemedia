<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Preview extends Quform_Admin_Page
{
    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @var Quform_Themes
     */
    protected $themes;

    /**
     * @param  Quform_ViewFactory   $viewFactory
     * @param  Quform_Repository    $repository
     * @param  Quform_Options       $options
     * @param  Quform_ScriptLoader  $scriptLoader
     * @param  Quform_Themes        $themes
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository, Quform_Options $options,
                                Quform_ScriptLoader $scriptLoader, Quform_Themes $themes)
    {
        parent::__construct($viewFactory, $repository);

        $this->options = $options;
        $this->scriptLoader = $scriptLoader;
        $this->viewFactory = $viewFactory;
        $this->themes = $themes;
    }

    public function init()
    {
        $this->template = QUFORM_TEMPLATE_PATH .  '/admin/preview.php';
    }

    public function process()
    {
        if ( ! current_user_can('quform_edit_forms')) {
            wp_die(__( 'You do not have sufficient permissions to access this page.', 'quform'), 403);
        }

        $l10n = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'notLoaded' => __('Sorry, the form preview could not be loaded.', 'quform')
        );

        $styles = $this->scriptLoader->getStyles(true);

        // Do not use the custom CSS in the preview, it could conflict with the "live" CSS from the form builder
        if (isset($styles['quform-custom'])) {
            unset($styles['quform-custom']);
        }

        foreach ($this->themes->getThemes() as $key => $theme) {
            $styles["quform-theme-$key"] = array(
                'url' => $theme['cssUrl'],
                'version' => $theme['version']
            );
        }

        $styles['quform-preview'] = array(
            'url' => Quform::adminUrl('css/preview.min.css'),
            'version' => QUFORM_VERSION
        );

        $scripts = $this->scriptLoader->getScripts(true);

        foreach ($this->themes->getThemes() as $key => $theme) {
            if (isset($theme['jsUrl'])) {
                $scripts["quform-theme-$key"] = array(
                    'url' => $theme['jsUrl'],
                    'version' => $theme['version']
                );
            }
        }

        $scripts['quform-preview'] = array(
            'url' => Quform::adminUrl('js/preview.min.js'),
            'version' => QUFORM_VERSION
        );

        $this->view->with(array(
            'l10n' => $l10n,
            'styles' => $styles,
            'scripts' => $scripts,
            'options' => $this->options,
            'scriptLoader' => $this->scriptLoader
        ));

        echo $this->display();
        exit;
    }
}
