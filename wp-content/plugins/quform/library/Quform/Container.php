<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Container extends JuiceContainer
{
    public function __construct()
    {
        $this->setup();
    }

    /**
     * Container setup - register the service definitions
     */
    protected function setup()
    {
        $this['formFactory'] = new JuiceDefinition('Quform_Form_Factory', array('@elementFactory', '@options', '@session', '@tokenReplacer'));
        $this['elementFactory'] = new JuiceDefinition('Quform_Element_Factory', array('@options', '@session', '@repository'));
        $this['repository'] = new JuiceDefinition('Quform_Repository');
        $this['adminPageController'] = new JuiceDefinition('Quform_Admin_Page_Controller', array('@adminPageFactory'));
        $this['adminPageFactory'] = new JuiceDefinition('Quform_Admin_Page_Factory', array('@viewFactory', '@repository', '@options', '@license', '@formFactory', '@scriptLoader', '@permissions', '@builder', '@themes', '@entryExporter', '@uploader', '@session', '@migrator', '@upgrader'));
        $this['viewFactory'] = new JuiceDefinition('Quform_ViewFactory');
        $this['uploader'] = new JuiceDefinition('Quform_Uploader', array('@session', '@repository', '@formFactory'));
        $this['options'] = new JuiceDefinition('Quform_Options', array('quform_options'));
        $this['updater'] = new JuiceDefinition('Quform_Updater', array('@api', '@license'));
        $this['api'] = new JuiceDefinition('Quform_Api', array('@options'));
        $this['license'] = new JuiceDefinition('Quform_License', array('@api', '@options'));
        $this['shortcode'] = new JuiceDefinition('Quform_Shortcode', array('@formController', '@options'));
        $this['builder'] = new JuiceDefinition('Quform_Builder', array('@repository', '@formFactory', '@options', '@themes', '@scriptLoader'));
        $this['formController'] = new JuiceDefinition('Quform_Form_Controller', array('@formFactory', '@repository', '@formProcessor', '@session', '@uploader'));
        $this['formProcessor'] = new JuiceDefinition('Quform_Form_Processor', array('@repository', '@session', '@uploader', '@options'));
        $this['insertForm'] = new JuiceDefinition('Quform_Admin_InsertForm', array('@repository', '@options'));
        $this['entryExporter'] = new JuiceDefinition('Quform_Entry_Exporter', array('@repository', '@formFactory', '@options'));
        $this['dashboardWidget'] = new JuiceDefinition('Quform_Dashboard_Widget', array('@repository', '@viewFactory', '@options'));
        $this['translations'] = new JuiceDefinition('Quform_Translations');
        $this['scriptLoader'] = new JuiceDefinition('Quform_ScriptLoader', array('@options', '@themes', '@repository', '@formFactory', '@session'));
        $this['permissions'] = new JuiceDefinition('Quform_Permissions');
        $this['themes'] = new JuiceDefinition('Quform_Themes');
        $this['session'] = new JuiceDefinition('Quform_Session');
        $this['captcha'] = new JuiceDefinition('Quform_Captcha', array('@repository', '@formFactory'));
        $this['entryController'] = new JuiceDefinition('Quform_Entry_Controller', array('@formFactory', '@repository', '@entryProcessor'));
        $this['entryProcessor'] = new JuiceDefinition('Quform_Entry_Processor', array('@repository', '@session', '@uploader', '@options'));
        $this['tokenReplacer'] = new JuiceDefinition('Quform_TokenReplacer', array('@options'));
        $this['settings'] = new JuiceDefinition('Quform_Settings', array('@options', '@permissions', '@scriptLoader'));
        $this['formExporter'] = new JuiceDefinition('Quform_Form_Exporter', array('@repository'));
        $this['formImporter'] = new JuiceDefinition('Quform_Form_Importer', array('@repository', '@builder', '@scriptLoader'));
        $this['toolbar'] = new JuiceDefinition('Quform_Toolbar', array('@options', '@repository'));
        $this['formsListSettings'] = new JuiceDefinition('Quform_Form_List_Settings');
        $this['entriesListSettings'] = new JuiceDefinition('Quform_Entry_List_Settings', array('@repository'));
        $this['migrator'] = new JuiceDefinition('Quform_Migrator', array('@repository', '@builder', '@scriptLoader', '@options', '@formFactory'));
        $this['upgrader'] = new JuiceDefinition('Quform_Upgrader', array('@repository', '@permissions', '@uploader', '@session', '@scriptLoader'));
        $this['nonceRefresher'] = new JuiceDefinition('Quform_NonceRefresher');
        $this['block'] = new JuiceDefinition('Quform_Block', array('@formController', '@repository'));
        $this['notificationResender'] = new JuiceDefinition('Quform_Notification_Resender', array('@repository', '@formFactory'));
        $this['entriesUserSearcher'] = new JuiceDefinition('Quform_Entry_UserSearcher');

        do_action('quform_container_setup', $this);

        $this->lock();
    }
}
