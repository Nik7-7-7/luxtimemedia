<?php

if (!defined('ABSPATH')) exit;

/**
 * @var $router \FluentBoards\Framework\Http\Router
 */

$router->prefix('projects/{board_id}/tasks/{task_id}')->withPolicy(\FluentBoards\App\Http\Policies\SingleBoardPolicy::class)->group(function ($router) {

    $router->get('/time-tracks', 'TimeTrackController@getTracks')->int('board_id')->int('task_id');
    $router->post('/time-tracks', 'TimeTrackController@manualCommitTrack')->int('board_id')->int('task_id');
    $router->post('/time-tracks/estimated-time', 'TimeTrackController@updateTimeEstimation')->int('board_id')->int('task_id');

    $router->put('/time-tracks/commit/{track_id}', 'TimeTrackController@updateCommitTrack')->int('board_id')->int('task_id')->int('track_id');

});

$router->prefix('projects/timesheet')->withPolicy(\FluentBoards\App\Http\Policies\AdminPolicy::class)->group(function ($router) {
    $router->get('/by-tasks', [\FluentBoardsPro\App\Modules\TimeTracking\Controllers\ReportController::class, 'getTracksByTasks']);
    $router->get('/by-users', [\FluentBoardsPro\App\Modules\TimeTracking\Controllers\ReportController::class, 'getTracksByUsers']);
});
