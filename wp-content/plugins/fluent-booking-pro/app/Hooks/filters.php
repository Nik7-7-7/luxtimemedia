<?php

defined( 'ABSPATH' ) || exit;

/**
 * All registered filter's handlers should be in app\Hooks\Handlers,
 * addFilter is similar to add_filter and addCustomFlter is just a
 * wrapper over add_filter which will add a prefix to the hook name
 * using the plugin slug to make it unique in all wordpress plugins,
 * ex: $app->addCustomFilter('foo', ['FooHandler', 'handleFoo']) is
 * equivalent to add_filter('slug-foo', ['FooHandler', 'handleFoo']).
 */

use FluentBookingPro\App\Services\ReceiptHelper;
use FluentBookingPro\App\Services\Integrations\PaymentMethods\PaymentHelper;

/**
 * @var $app WPFluentMicro\Framework\Foundation\Application
 */

// Used to encrypt/decrypt any value. The same
// key is required to decrypt the encrypted value.
$app->addFilter($app->config->get('app.slug') . '_encryption_key', function($default) {
	// must return a 16 characters long string, for example:
	return implode('', range('a', 'p')); // abcdefghijklmnop
});

$app->addFilter('fluent_booking/payment_receipt_html', function ($html, $bookingHash) {
    return (new ReceiptHelper())->getReceipt($bookingHash);
}, 10, 2);

$app->addFilter('fluent_booking/payment_booking_field', function ($paymentField, $calendarEvent, $existingFields) {
    return PaymentHelper::getPaymentField($paymentField, $calendarEvent, $existingFields);
}, 10, 3);

$app->addFilter('fluent_booking/total_payment_widget', function ($widget, $paymentWidget) {
    return PaymentHelper::getTotalPaymentWidget($widget, $paymentWidget);
}, 10, 2);

$app->addFilter('fluent_booking/settings_menu_items', function ($items) {
    $items['team_members']['disable'] = false;
    $items['license']['disable'] = false;
    return $items;
}, 10, 1);

$app->addFilter('fluent_booking/calendar_setting_menu_items', function ($items) {
    $items['remote_calendars']['disable'] = false;
    $items['zoom_meeting']['disable'] = false;
    return $items;
}, 10, 1);

$app->addFilter('fluent_booking/calendar_event_setting_menu_items', function ($items) {
    foreach ($items as &$item) {
        $item['disable'] = false;
    }
    return $items;
}, 10, 1);
