<?php

defined( 'ABSPATH' ) || exit;

/**
 * @var $app \FluentBooking\Framework\Foundation\Application
 */

// Global Payment Handler
(new FluentBookingPro\App\Hooks\Handlers\GlobalPaymentHandler)->register();
(new FluentBookingPro\App\Hooks\Handlers\FrontendRenderer())->register();
(new FluentBookingPro\App\Hooks\Handlers\UploadHandler($app));
