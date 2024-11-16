<?php

/**
 * @var $app FluentBoards\Framework\Foundation\Application
 */

(new \FluentBoardsPro\App\Hooks\Handlers\FrontendRenderer())->register();


$app->addAction('admin_post_myform', 'InvitationHandler@processInvitation', 10, 0);
$app->addAction('admin_post_nopriv_myform', 'InvitationHandler@processInvitation', 10, 0);
$app->addAction('wp_ajax_fluent_boards_export_timesheet', 'FluentBoardsPro\App\Hooks\Handlers\DataExporter@exportTimeSheet', 10, 0);

$app->addAction('fluent_boards/hourly_scheduler', 'ProScheduleHandler@hourlyScheduler', 10, 0);
$app->addAction('fluent_boards/daily_task_reminder', 'ProScheduleHandler@dailyTaskSummaryMail', 10, 0);
$app->addAction('fluent_boards/install_plugin', 'FluentBoardsPro\App\Hooks\Handlers\InstallationHandler@installPlugin', 10, 2);
$app->addFilter('fluent_boards/repeat_task', 'FluentBoardsPro\App\Hooks\Handlers\ProTaskHandler@repeatTask', 10, 1);
$app->addAction('fluent_boards/repeat_task_scheduler', 'FluentBoardsPro\App\Hooks\Handlers\ProScheduleHandler@repeatTasks', 10, 0);

/*
 * IMPORTANT
 * External Pages Handler
 * Each Request must have fbs=1 as a query parameter, then the plugin will handle the request.
 */
if(isset($_GET['fbs']) && $_GET['fbs'] == 1) {

    // For viewing attachment
    if(isset($_GET['fbs_attachment'])) {
        add_action('init', function() {
            (new \FluentBoardsPro\App\Hooks\Handlers\ExternalPages())->view_attachment();
        });
    }

    // Form page for invited user to join the board
    if(isset($_GET['invitation']) && $_GET['invitation'] == 'board') {
        add_action('init', function () {
            (new \FluentBoardsPro\App\Hooks\Handlers\ExternalPages())->boardMemberInvitation();
        });
    }
}
