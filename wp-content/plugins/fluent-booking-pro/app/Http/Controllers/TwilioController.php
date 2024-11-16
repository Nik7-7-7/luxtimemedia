<?php

namespace FluentBookingPro\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Http\Request\Request;
use FluentBooking\Framework\Support\Arr;
use FluentBookingPro\App\Services\Integrations\Twilio\TwilioHelper;

class TwilioController extends Controller
{
    public function getSlotSmsNotifications(Request $request, $calendarId, $slotId)
    {
        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $data = [
            'notifications' => TwilioHelper::getSmsNotifications($calendarEvent, true)
        ];

        if (in_array('smart_codes', $request->get('with', []))) {
            $data['smart_codes'] = [
                'texts' => Helper::getEditorShortCodes($calendarEvent),
                'html'  => Helper::getEditorShortCodes($calendarEvent, true)
            ];
        }

        return $data;
    }

    public function saveSlotSmsNotifications(Request $request, $calendarId, $slotId)
    {
        $slot = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $notifications = $request->get('notifications', []);

        $formattedNotifications = [];

        foreach ($notifications as $key => $value) {
            $formattedNotifications[$key] = [
                'title'   => sanitize_text_field(Arr::get($value, 'title')),
                'enabled' => Arr::isTrue($value, 'enabled'),
                'sms'     => $this->sanitizeNotificationData(Arr::get($value, 'sms')),
                'is_host' => Arr::isTrue($value, 'is_host')
            ];
        }

        TwilioHelper::setSmsNotifications($formattedNotifications, $slot);

        return [
            'message' => __('Notifications has been saved', 'fluent-booking-pro')
        ];
    }

    public function cloneSlotSmsNotifications(Request $request, $calendarId, $slotId)
    {
        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)->findOrFail($slotId);

        $fromEventId = intval($request->get('from_event_id'));

        $fromCalendarEvent = CalendarSlot::findOrFail($fromEventId);

        $notification = TwilioHelper::getSmsNotifications($fromCalendarEvent, true);

        TwilioHelper::setSmsNotifications($notification, $calendarEvent);

        return [
            'message'       => __('The Notification has been cloned successfully', 'fluent-booking'),
            'notifications' => $notification
        ];
    }

    private function sanitizeNotificationData($settings)
    {
        $sanitizerMap = [
            'value'    => 'intval',
            'unit'     => 'sanitize_text_field',
            'body'     => 'fcal_sanitize_html',
            'number'   => 'sanitize_text_field',
            'reciever' => 'sanitize_text_field',
            'send_to'  => 'sanitize_text_field',
        ];

        return Helper::fcal_backend_sanitizer($settings, $sanitizerMap);
    }
}
