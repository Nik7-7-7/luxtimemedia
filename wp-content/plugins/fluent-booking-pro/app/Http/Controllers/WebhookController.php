<?php

namespace FluentBookingPro\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Http\Request\Request;
use FluentBooking\Framework\Support\Arr;

class WebhookController extends Controller
{
    public function getFeeds(Request $request, $calendarId, $eventId)
    {
        $calendarEvent = CalendarSlot::findOrFail($eventId);
        $feeds = Meta::where('object_id', $calendarEvent->id)
            ->where('object_type', 'calendar_event')
            ->where('key', 'webhook_feeds')
            ->get();

        $formattedFeeds = [];

        foreach ($feeds as $feed) {
            $formattedFeeds[] = [
                'id'       => $feed->id,
                'settings' => $feed->value,
            ];
        }

        return [
            'feeds'          => $formattedFeeds,
            'event_triggers' => $this->eventTriggers(),
            'smart_codes'    => [
                'texts' => Helper::getEditorShortCodes($calendarEvent),
                'html'  => Helper::getEditorShortCodes($calendarEvent, true)
            ]
        ];
    }

    public function saveFeed(Request $request, $calendarId, $eventId)
    {
        $calendarEvent = CalendarSlot::findOrFail($eventId);
        $webhookFeed = $request->get('webhook', []);
        $settings = Arr::get($webhookFeed, 'settings', []);

        $this->validate($settings, [
            'name'           => 'required',
            'request_url'    => 'required',
            'request_body'   => 'required',
            'request_method' => 'required',
            'event_triggers' => 'required',
        ]);

        $settings['enabled'] = Arr::isTrue($settings, 'enabled');

        if ($webhookFeed['id']) {
            $webhook = Meta::where('object_type', 'calendar_event')
                ->where('key', 'webhook_feeds')
                ->where('object_id', $calendarEvent->id)
                ->where('id', $webhookFeed['id'])
                ->first();

            if (!$webhook) {
                return $this->sendError([
                    'message' => __('WebHook not found', 'fluent-booking-pro')
                ], 422);
            }

            $webhook->value = $settings;
            $webhook->save();
            return [
                'message' => __('WebHook Successfully Updated', 'fluent-booking-pro'),
                'id'      => $webhook->id
            ];
        }

        // create new
        $data = [
            'value'       => $settings,
            'object_type' => 'calendar_event',
            'object_id'   => $calendarEvent->id,
            'key'         => 'webhook_feeds'
        ];

        $createdHook = Meta::create($data);

        return [
            'message' => __('WebHook Successfully Created', 'fluent-booking-pro'),
            'id'      => $createdHook->id
        ];
    }

    public function deleteFeed(Request $request, $calendarId, $eventId, $webhookId)
    {
        Meta::where('id', $webhookId)
            ->where('key', 'webhook_feeds')
            ->where('object_id', $eventId)
            ->delete();

        return [
            'message' => __('Selected Webhook has been deleted', 'fluent-booking-pro')
        ];
    }

    public function cloneFeeds(Request $request, $calendarId, $eventId)
    {
        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($eventId);

        $fromEventId = intval($request->get('from_event_id'));
        
        $fromEventFeeds = Meta::where('object_id', $fromEventId)
            ->where('object_type', 'calendar_event')
            ->where('key', 'webhook_feeds')
            ->get();
        
        if ($fromEventFeeds->isEmpty()) {
            return $this->sendError([
                'message' => __('WebHook feeds not found', 'fluent-booking-pro')
            ], 422);
        }

        foreach ($fromEventFeeds as $feed) {
            $cloneFeed = $feed->replicate();
            $cloneFeed->object_id = $calendarEvent->id;
            $cloneFeed->save();
        }

        return [
            'message' => __('WebHook Successfully Cloned', 'fluent-booking-pro')
        ];
    }

    protected function eventTriggers()
    {
        return [
            [
                'label' => __('Booking Confirmed', 'fluent-booking-pro'),
                'value' => 'after_booking_scheduled'
            ],
            [
                'label' => __('Booking Canceled', 'fluent-booking-pro'),
                'value' => 'booking_schedule_cancelled'
            ],
            [
                'label' => __('Booking Completed', 'fluent-booking-pro'),
                'value' => 'booking_schedule_completed'
            ],
            [
                'label' => __('Booking Rescheduled', 'fluent-booking-pro'),
                'value' => 'after_booking_rescheduled'
            ],
            [
                'label' => __('Booking Rejected', 'fluent-booking-pro'),
                'value' => 'booking_schedule_rejected'
            ]
        ];
    }
}
