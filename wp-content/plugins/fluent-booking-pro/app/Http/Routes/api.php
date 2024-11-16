<?php

/**
 * @var $router FluentBooking\Framework\Http\Router
 */

use FluentBooking\App\Http\Policies\CalendarPolicy;
use FluentBooking\App\Http\Policies\SettingsPolicy;
use FluentBookingPro\App\Http\Controllers\IntegrationSettingsController;
use FluentBookingPro\App\Http\Controllers\CalendarController;
use FluentBookingPro\App\Http\Controllers\LicenseController;
use FluentBookingPro\App\Http\Controllers\PaymentMethodController;
use FluentBookingPro\App\Http\Controllers\TeamController;
use FluentBookingPro\App\Http\Controllers\TwilioController;
use FluentBookingPro\App\Http\Controllers\WebhookController;
use FluentBookingPro\App\Http\Controllers\ZoomController;
use FluentBookingPro\App\Modules\SingleEvent\SingleEventController;

$router->prefix('settings')->withPolicy(SettingsPolicy::class)->group(function ($router) {
    // Team Management Permissions
    $router->get('/team', [TeamController::class, 'getTeamMembers']);
    $router->post('/team', [TeamController::class, 'updateMemberPermission']);
    $router->delete('/team/{id}',  [TeamController::class, 'deleteMember'])->int('id');
    $router->get('license', [LicenseController::class, 'getStatus']);
    $router->post('license', [LicenseController::class, 'saveLicense']);
    $router->delete('license', [LicenseController::class, 'deactivateLicense']);
});

$router->prefix('calendars')->withPolicy(CalendarPolicy::class)->group(function ($router) {
    // Integrations
    $router->get('/{id}/integrations/remote-calendars', [IntegrationSettingsController::class, 'getRemoteCalendars'])->int('id');
    $router->post('/{id}/integrations/remote-calendars/patch-conflicts', [IntegrationSettingsController::class, 'patchRemoteCalendarConflictSettings'])->int('id');
    $router->post('/{id}/integrations/remote-calendars/patch-settings', [IntegrationSettingsController::class, 'patchRemoteCalendarAdditionalSettings'])->int('id');
    $router->post('/{id}/integrations/remote-calendars/sync-settings', [IntegrationSettingsController::class, 'syncCreatbleRemoteCalSettings'])->int('id');
    $router->post('/{id}/integrations/remote-calendars/disconnect-calendar', [IntegrationSettingsController::class, 'disconnectRemoteCalendar'])->int('id');
    $router->post('/{id}/integrations/remote-calendars/cal-dav-auth', [IntegrationSettingsController::class, 'addCalDavCredential'])->int('id');

    // Zoom Integrations - User Level
    $router->get('/{id}/integrations/zoom-connection', [ZoomController::class, 'getZoomConnectionByCalendarId'])->int('id');
    $router->post('/{id}/integrations/zoom-connection/add', [ZoomController::class, 'addConnectionByCalendarId'])->int('id');
    $router->post('/{id}/integrations/zoom-connection/disconnect', [ZoomController::class, 'disconnectByCalendarId'])->int('id');

    // Calendar Settings
    $router->post('/{id}/events/{event_id}/assignments', [CalendarController::class, 'updateAssignments'])->int('id')->int('event_id');
    $router->post('/{id}/events/{event_id}/advanced-settings', [CalendarController::class, 'updateAdvancedSettings'])->int('id')->int('event_id');

    // Twilio Integrations
    $router->get('/{id}/events/{event_id}/sms-notifications', [TwilioController::class, 'getSlotSmsNotifications'])->int('id')->int('event_id');
    $router->post('/{id}/events/{event_id}/sms-notifications', [TwilioController::class, 'saveSlotSmsNotifications'])->int('id')->int('event_id');
    $router->post('/{id}/events/{event_id}/sms-notifications/clone', [TwilioController::class, 'cloneSlotSmsNotifications'])->int('id')->int('event_id');

    // webhooks
    $router->get('/{id}/events/{event_id}/webhooks', [WebhookController::class, 'getFeeds'])->int('id')->int('event_id');
    $router->post('/{id}/events/{event_id}/webhooks', [WebhookController::class, 'saveFeed'])->int('id')->int('event_id');
    $router->post('/{id}/events/{event_id}/webhooks/clone', [WebhookController::class, 'cloneFeeds'])->int('id')->int('event_id');
    $router->delete('/{id}/events/{event_id}/webhooks/{webhook_id}', [WebhookController::class, 'deleteFeed'])->int('id')->int('event_id')->int('webhook_id');

    // Payment settings route
    $router->get('/{id}/events/{event_id}/payment-settings', [PaymentMethodController::class, 'getCalendarEventSettings'])->int('id')->int('event_id');
    $router->post('/{id}/events/{event_id}/payment-settings', [PaymentMethodController::class, 'updateSettings'])->int('id')->int('event_id');

    // General Integrations
    $router->get('/{id}/integrations/general_integration_feed', IntegrationSettingsController::class, 'getGeneralIntegrationFeed')->int('id');
    $router->post('/{id}/integrations/general_integration_feed/disconnect', IntegrationSettingsController::class, 'disconnectGeneralIntegrationFeed')->int('id');

    // Single Event Module
    $router->get('/{id}/events/{event_id}/event-available-slots', [SingleEventController::class, 'getEventAvailableSlots'])->int('id')->int('event_id');
    $router->post('/{id}/events/{event_id}/event-availability', [SingleEventController::class, 'updateEventAvailability'])->int('id')->int('event_id');
});

$router->prefix('integrations')->withPolicy(SettingsPolicy::class)->group(function ($router) {
    // Integration Settings
    $router->get('/{host_id}/settings', [IntegrationSettingsController::class, 'index'])->int('host_id');
    $router->post('/{host_id}/settings', [IntegrationSettingsController::class, 'update'])->int('host_id');
    $router->post('/{host_id}/disconnect', [IntegrationSettingsController::class, 'revoke'])->int('host_id');
    $router->get('/menu', [IntegrationSettingsController::class, 'getIntegrationsMenu']);

    // Zoom Integrations
    $router->get('zoom/connected-users', [ZoomController::class, 'get']);
    $router->post('zoom/save-user-account', [ZoomController::class, 'save']);
    $router->post('zoom/disconnect', [ZoomController::class, 'disconnectByConnectId']);

    $router->prefix('settings/payment-methods')->group(function ($router) {
        $router->get('/all', [PaymentMethodController::class, 'index']);

        $router->post('/', [PaymentMethodController::class, 'store']);
        $router->get('/', [PaymentMethodController::class, 'getSettings']);

        $router->get('connect/info', [PaymentMethodController::class, 'connectInfo']);
        $router->post('disconnect', [PaymentMethodController::class, 'disconnect']);

        $router->get('currencies', [PaymentMethodController::class, 'currencies']);
    });

    $router->get('/options/woo-products', [PaymentMethodController::class, 'getWooProducts']);
});
