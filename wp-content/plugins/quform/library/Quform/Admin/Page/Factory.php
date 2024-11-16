<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Admin_Page_Factory
{
    /**
     * @var Quform_ViewFactory
     */
    protected $viewFactory;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @var Quform_License
     */
    protected $license;

    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @var Quform_Permissions
     */
    protected $permissions;

    /**
     * @var Quform_Builder
     */
    protected $builder;

    /**
     * @var Quform_Themes
     */
    protected $themes;

    /**
     * @var Quform_Entry_Exporter
     */
    protected $entryExporter;

    /**
     * @var Quform_Uploader
     */
    protected $uploader;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_Migrator
     */
    protected $migrator;

    /**
     * @var Quform_Upgrader
     */
    protected $upgrader;

    /**
     * @param  Quform_ViewFactory     $viewFactory
     * @param  Quform_Repository      $repository
     * @param  Quform_Options         $options
     * @param  Quform_License         $license
     * @param  Quform_Form_Factory    $formFactory
     * @param  Quform_ScriptLoader    $scriptLoader
     * @param  Quform_Permissions     $permissions
     * @param  Quform_Builder         $builder
     * @param  Quform_Themes          $themes
     * @param  Quform_Entry_Exporter  $entryExporter
     * @param  Quform_Uploader        $uploader
     * @param  Quform_Session         $session
     * @param  Quform_Migrator        $migrator
     * @param  Quform_Upgrader        $upgrader
     */
    public function __construct(Quform_ViewFactory $viewFactory, Quform_Repository $repository,
                                Quform_Options $options, Quform_License $license, Quform_Form_Factory $formFactory,
                                Quform_ScriptLoader $scriptLoader, Quform_Permissions $permissions,
                                Quform_Builder $builder, Quform_Themes $themes, Quform_Entry_Exporter $entryExporter,
                                Quform_Uploader $uploader, Quform_Session $session, Quform_Migrator $migrator,
                                Quform_Upgrader $upgrader)
    {
        $this->viewFactory = $viewFactory;
        $this->repository = $repository;
        $this->options = $options;
        $this->license = $license;
        $this->formFactory = $formFactory;
        $this->scriptLoader = $scriptLoader;
        $this->permissions = $permissions;
        $this->builder = $builder;
        $this->themes = $themes;
        $this->entryExporter = $entryExporter;
        $this->uploader = $uploader;
        $this->session = $session;
        $this->migrator = $migrator;
        $this->upgrader = $upgrader;
    }

    /**
     * @param   string                    $page  The name of the page
     * @return  Quform_Admin_Page                The page class instance
     * @throws  InvalidArgumentException         If the page class does not exist
     */
    public function create($page)
    {
        $method = 'create' . $page . 'Page';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        throw new InvalidArgumentException('Method not found to create page: ' . $page);
    }

    /**
     * @return Quform_Admin_Page_Dashboard
     */
    protected function createDashboardPage()
    {
        return new Quform_Admin_Page_Dashboard($this->viewFactory, $this->repository, $this->options);
    }

    /**
     * @return Quform_Admin_Page
     */
    protected function createFormsPage()
    {
        switch ($this->getSubPage()) {
            case 'list':
            default:
                $page = new Quform_Admin_Page_Forms_List($this->viewFactory, $this->repository, $this->scriptLoader, new Quform_Form_List_Table($this->repository, $this->options));
                break;
            case 'add':
                $page = new Quform_Admin_Page_Forms_Add($this->viewFactory, $this->repository);
                break;
            case 'edit':
                $page = new Quform_Admin_Page_Forms_Edit($this->viewFactory, $this->repository, $this->builder, $this->license, $this->options);
                break;
        }

        return $page;
    }

    /**
     * @return Quform_Admin_Page
     */
    protected function createEntriesPage()
    {
        switch ($this->getSubPage()) {
            case 'list':
            default:
                $page = new Quform_Admin_Page_Entries_List($this->viewFactory, $this->repository, $this->formFactory, $this->options);
                break;
            case 'view':
                $page = new Quform_Admin_Page_Entries_View($this->viewFactory, $this->repository, $this->formFactory, $this->options);
                break;
            case 'edit':
                $page = new Quform_Admin_Page_Entries_Edit($this->viewFactory, $this->repository, $this->formFactory, $this->uploader, $this->session);
                break;
        }

        return $page;
    }

    /**
     * @return Quform_Admin_Page
     */
    protected function createToolsPage()
    {
        switch ($this->getSubPage()) {
            case 'home':
            default:
                $page = new Quform_Admin_Page_Tools_Home($this->viewFactory, $this->repository);
                break;
            case 'export.entries':
                $page = new Quform_Admin_Page_Tools_ExportEntries($this->viewFactory, $this->repository, $this->entryExporter, $this->formFactory);
                break;
            case 'export.form':
                $page = new Quform_Admin_Page_Tools_ExportForm($this->viewFactory, $this->repository);
                break;
            case 'import.form':
                $page = new Quform_Admin_Page_Tools_ImportForm($this->viewFactory, $this->repository);
                break;
            case 'migrate':
                $page = new Quform_Admin_Page_Tools_Migrate($this->viewFactory, $this->repository, $this->migrator);
                break;
            case 'uninstall':
                $page = new Quform_Admin_Page_Tools_Uninstall($this->viewFactory, $this->repository, $this->options, $this->permissions, $this->uploader, $this->session, $this->upgrader);
                break;
        }

        return $page;
    }

    /**
     * @return Quform_Admin_Page_Settings
     */
    protected function createSettingsPage()
    {
        return new Quform_Admin_Page_Settings($this->viewFactory, $this->repository, $this->options, $this->license, $this->permissions, $this->scriptLoader);
    }

    /**
     * @return Quform_Admin_Page_Help
     */
    protected function createHelpPage()
    {
        return new Quform_Admin_Page_Help($this->viewFactory, $this->repository);
    }

    /**
     * @return Quform_Admin_Page_Preview
     */
    protected function createPreviewPage()
    {
        return new Quform_Admin_Page_Preview($this->viewFactory, $this->repository, $this->options, $this->scriptLoader, $this->themes);
    }

    /**
     * Get the sub page query var
     *
     * @return string|null
     */
    protected function getSubPage()
    {
        return Quform::get($_GET, 'sp');
    }
}
