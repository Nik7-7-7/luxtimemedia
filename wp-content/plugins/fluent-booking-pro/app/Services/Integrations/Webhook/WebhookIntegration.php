<?php

namespace FluentBookingPro\App\Services\Integrations\Webhook;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\EditorShortCodeParser;
use FluentBooking\Framework\Support\Arr;

class WebhookIntegration
{
    public function register()
    {
        add_action('fluent_booking/after_booking_scheduled', [$this, 'maybeHandleWebHookAsync'], 10, 2);
        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'maybeHandleWebHookAsync'], 10, 2);
        add_action('fluent_booking/booking_schedule_completed', [$this, 'maybeHandleWebHookAsync'], 10, 2);
        add_action('fluent_booking/after_booking_rescheduled', [$this, 'maybeHandleRescheduled'], 10, 3);
        add_action('fluent_booking/booking_schedule_rejected', [$this, 'maybeHandleWebHookAsync'], 10, 2);

        add_action('fluent_booking/run_webhook', [$this, 'runWebhook'], 10, 2);
    }

    public function maybeHandleRescheduled($booking, $previousBooking, $calendarEvent)
    {
        $booking->status = 'rescheduled';
        $this->maybeHandleWebHookAsync($booking, $calendarEvent);
    }

    public function maybeHandleWebHookAsync($booking, $calendarSlot)
    {
        $status = $booking->status;

        $maps = [
            'scheduled'   => 'after_booking_scheduled',
            'cancelled'   => 'booking_schedule_cancelled',
            'completed'   => 'booking_schedule_completed',
            'rescheduled' => 'after_booking_rescheduled',
            'rejected'    => 'booking_schedule_rejected'
        ];

        if (!isset($maps[$status])) {
            return;
        }
        $currentHook = $maps[$status];

        $webHooks = Meta::where('object_id', $calendarSlot->id)
            ->where('object_type', 'calendar_event')
            ->where('key', 'webhook_feeds')
            ->get();

        foreach ($webHooks as $webHook) {
            $item = $webHook->value;
            $triggers = Arr::get($item, 'event_triggers', []);
            if (!in_array($currentHook, $triggers) || !Arr::isTrue($item, 'enabled')) {
                continue;
            }

            as_enqueue_async_action('fluent_booking/run_webhook', [$webHook->id, $booking->id], 'fluent-booking');
        }
    }

    public function runWebhook($webhookId, $bookingId)
    {
        $webHook = Meta::where('key', 'webhook_feeds')->find($webhookId);
        if (!$webHook) {
            return;
        }

        $feed = $webHook->value;
        if (!Arr::isTrue($feed, 'enabled')) {
            return;
        }

        $booking = Booking::find($bookingId);

        if (!$booking) {
            return;
        }

        $body = [];
        if ($feed['request_body'] == 'all_data') {
            $bookingData = $booking->toArray();
            $bookingData['custom_fields'] = $booking->getCustomFormData(false);

            $rescheduleReason = $booking->getRescheduleReason();
            if ($rescheduleReason) {
                $bookingData['reschedule_reason'] = $rescheduleReason;
            }

            $cancelReason = $booking->getCancelReason(true);
            if ($cancelReason) {
                $bookingData['cancellation_reason'] = $cancelReason;
            }

            $body = [
                'booking'        => $bookingData,
                'calendar_event' => $booking->calendar_event ? $booking->calendar_event->toArray() : null,
            ];
        } else if ($feed['request_body'] == 'selected_fields') {
            $booking->load(['calendar_event', 'calendar']);
            // We have to loop the data
            foreach ($feed['fields'] as $item) {
                if (empty($item['key']) || empty($item['value'])) {
                    continue;
                }
                $body[$item['key']] = EditorShortCodeParser::parse($item['value'], $booking, false);
            }
        }

        $remoteUrl = Arr::get($feed, 'request_url');

        if (!$remoteUrl) {
            return;
        }

        $headers = [];

        if (Arr::get($feed, 'with_header') == 'yup') {
            foreach ($feed['request_headers'] as $item) {
                if (empty($item['key']) || empty($item['value'])) {
                    continue;
                }
                $headers[$item['key']] = EditorShortCodeParser::parse($item['value'], $booking, false);
            }
        }

        $sendingMethod = Arr::get($feed, 'request_method', 'POST');
        $requestFormat = Arr::get($feed, 'request_format', 'JSON');
        $isJson = $requestFormat == 'JSON' && ($sendingMethod == 'POST' || $sendingMethod == 'PUT');

        if ($isJson) {
            $headers['Content-Type'] = 'application/json; charset=utf-8';
        }

        if ($sendingMethod == 'GET') {
            $remoteUrl = add_query_arg($body, $remoteUrl);
        }

        $response = wp_remote_request($remoteUrl, apply_filters('fluent_booking/booking_webhook_request', [
            'method'      => $sendingMethod,
            'headers'     => $headers,
            'body'        => $isJson ? wp_json_encode($body) : $body,
            'redirection' => 0,
            'timeout'     => 20,
            'sslverify'   => false,
        ], $booking, $webHook));

        $responseCode = wp_remote_retrieve_response_code($response);
        $success = $responseCode >= 200 && $responseCode < 300;

        if (is_wp_error($responseCode)) {
            $success = false;
            $logDescription = __('Failed to send webhook to', 'fluent-booking-pro') . ' ' . $remoteUrl . ' due to ' . $responseCode->get_error_message();
        } else if ($success) {
            $logDescription = __('Webhook sent successfully to', 'fluent-booking-pro') . ' ' . $remoteUrl;
        } else {
            $logDescription = __('Failed to send webhook to', 'fluent-booking-pro') . ' ' . $remoteUrl . '. Remote Response Code: ' . wp_remote_retrieve_response_message($response);
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => $success ? 'success' : 'error',
            'title'       => $success ? __('Webhook sent successfully', 'fluent-booking-pro') : __('Failed to send webhook', 'fluent-booking-pro'),
            'description' => $logDescription
        ]);

        return true;
    }
}
