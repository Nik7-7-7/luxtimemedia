<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Upgrader
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Permissions
     */
    protected $permissions;

    /**
     * @var Quform_Uploader
     */
    protected $uploader;

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_ScriptLoader
     */
    protected $scriptLoader;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Permissions   $permissions
     * @param  Quform_Uploader      $uploader
     * @param  Quform_Session       $session
     * @param  Quform_ScriptLoader  $scriptLoader
     */
    public function __construct(
        Quform_Repository $repository,
        Quform_Permissions $permissions,
        Quform_Uploader $uploader,
        Quform_Session $session,
        Quform_ScriptLoader $scriptLoader
    ) {
        $this->repository = $repository;
        $this->permissions = $permissions;
        $this->uploader = $uploader;
        $this->session = $session;
        $this->scriptLoader = $scriptLoader;
    }

    /**
     * Check if any upgrades need to be processed
     */
    public function upgradeCheck()
    {
        if (get_option('quform_activated') === '1' || get_option('quform_version') != QUFORM_VERSION) {
            // Trigger plugin activation
            $this->activate();

            // Get the version again (as it can change during plugin activation, this will be the previously installed version)
            $version = get_option('quform_version');

            // Save the new DB version
            update_option('quform_version', QUFORM_VERSION);
            delete_option('quform_activated');

            // Process any upgrades as required
        }
    }

    /**
     * Run the plugin activation functions
     */
    public function activate()
    {
        add_option('quform_version', QUFORM_VERSION);

        $this->repository->activate();
        $this->permissions->activate();
        $this->uploader->activate();
        $this->session->activate();
        $this->scriptLoader->activate();
    }

    /**
     * On plugin uninstall remove the plugin version
     */
    public function uninstall()
    {
        delete_option('quform_version');
    }
}
