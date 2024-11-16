<?php

namespace FluentBoardsPro\App\Hooks\Handlers;

use FluentBoardsPro\App\Services\HelperInstaller;

class InstallationHandler
{

    public function acceptedPlugins($accepted_plugins)
    {
        $accepted_plugins[] = 'fluent-roadmap/fluent-roadmap.php';
        return $accepted_plugins;
    }

    public function addOnSettings($addOns)
    {
        $roadmapAddon = [
            'title'          => __('Fluent Roadmap', 'fluent-boards-pro'),
            'logo'           => fluent_boards_mix('images/addons/fluent-roadmap.png'),
            'is_installed'   => defined('FLUENT_ROADMAP'),
            'learn_more_url' => 'https://fluentboards.com/blog/introducing-fluentroadmap/',
            'associate_doc'  => 'https://fluentboards.com/blog/introducing-fluentroadmap/',
            'action_text'    => \FluentBoardsPro\App\Services\ProHelper::isPluginInstalled('fluent-roadmap/fluent-roadmap.php') ? __('Activate Fluent Roadmap', 'fluent-boards-pro') : __('Install Fluent Roadmap', 'fluent-boards-pro'),
            'description'    => __('FluentRoadmap is an add-on for FluentBoards that provides a clear view of user requests, project progress, and priorities. Share Feature Request Boards to gather new ideas, discuss, and upvote suggestions.', 'fluent-boards')
        ];

        // push to first index of array
        array_unshift($addOns, $roadmapAddon);

        return $addOns;
    }

    public function installPlugin($pluginToInstall, $plugin)
    {
        $plugin_id = 'fluent_roadmap';
        $plugin = [
            'name'      => __('Fluent Roadmap', 'fluent-boards-pro'),
            'repo-slug' => 'fluent-roadmap',
            'file'      => 'fluent-roadmap.php',
        ];

        $pluginUrl = 'https://s3.amazonaws.com/wpcolorlab/fluent-roadmap.zip';
        $this->processInstallation($plugin, $plugin_id, $pluginUrl);

    }

    private function processInstallation($plugin_to_install, $plugin_id, $url)
    {
        (new HelperInstaller())->backgroundInstaller($plugin_to_install, $plugin_id, $url);
    }

}