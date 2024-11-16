<?php

/**
 * @var $router FluentBooking\Framework\Http\Router
 */
$router->namespace('\FluentBookingPro\App\Http\Controllers')->group(function($router) {
    require_once __DIR__ . '/api.php';
});
