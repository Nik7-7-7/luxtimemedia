<?php

/**
 * @var $router \FluentBoards\Framework\Http\Router
 */

$router->namespace('FluentBoardsPro\App\Http\Controllers')->group(function($router) {
    require_once __DIR__ . '/api.php';
});
