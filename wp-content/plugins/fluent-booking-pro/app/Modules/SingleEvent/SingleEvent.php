<?php

namespace FluentBookingPro\App\Modules\SingleEvent;

use FluentBooking\App\Services\Helper;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\Framework\Support\Arr;

class SingleEvent
{
    public function register()
    {
        add_filter('fluent_booking/calendar_event_setting_menu_items', [$this, 'updateEventSettingMenu'], 10, 2);
        add_filter('fluent_booking/get_calendar_event_settings', [$this, 'getEventSettings'], 10, 2);
        add_filter('fluent_booking/create_calendar_event_data', [$this, 'updateCreateEventData'], 10, 1);
        add_filter('fluent_booking/public_event_vars', [$this, 'updateEventVars'], 10, 2);
        add_action('fluent_booking/calendar_slot', [$this, 'addCalendarSlotData'], 10, 1);
        add_action('fluent_booking/after_booking_scheduled', [$this, 'handleCalendarEvent'], 10, 2);
        add_action('fluent_booking/format_booking_schedule', [$this, 'formatBookingSchedule'], 10, 1);
        add_filter('fluent_booking/booking_data', [$this, 'updateBookingData'], 10, 2);
        add_action('fluent_booking/landing_page_event', [$this, 'updateProcessEventData'], 10, 1);
        add_action('fluent_booking/processed_event', [$this, 'updateProcessEventData'], 10, 1);
    }

    public function updateEventSettingMenu($items, $calendarEvent)
    {
        if (!$calendarEvent->isOneOffEvent()) {
            return $items;
        }

        $items['availability_settings'] = [
            'type'    => 'route',
            'visible' => true,
            'route'   => [
                'name'   => 'event_availability_settings',
                'params' => [
                    'calendar_id' => $calendarEvent->calendar_id,
                    'event_id'    => $calendarEvent->id
                ]
            ],
            'label'   => __('Availability', 'fluent-booking-pro'),
            'svgIcon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6.66666 1.66699V4.16699" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.3333 1.66699V4.16699" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M2.91666 7.5752H17.0833" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M17.5 7.08366V14.167C17.5 16.667 16.25 18.3337 13.3333 18.3337H6.66667C3.75 18.3337 2.5 16.667 2.5 14.167V7.08366C2.5 4.58366 3.75 2.91699 6.66667 2.91699H13.3333C16.25 2.91699 17.5 4.58366 17.5 7.08366Z" stroke="#445164" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.0789 11.4167H13.0864" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M13.0789 13.9167H13.0864" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.99623 11.4167H10.0037" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.99623 13.9167H10.0037" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.91194 11.4167H6.91942" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.91194 13.9167H6.91942" stroke="#445164" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        ];

        return $items;
    }

    public function getEventSettings($settings, $calendarEvent)
    {
        if (!$calendarEvent->isOneOffEvent()) {
            return $settings;
        }

        if (!isset($settings['reserve_time'])) {
            $settings['reserve_time'] = false;
        }

        if (!isset($settings['available_times'])) {
            $settings['available_times'] = (object)[];
        }

        return $settings;
    }

    public function updateCreateEventData($eventData)
    {
        $eventType = Arr::get($eventData, 'event_type');

        if ($eventType != 'single_event' && $eventType != 'group_event') {
            return $eventData;
        }

        $eventData['availability_id'] = null;
        $eventData['availability_type'] = 'custom_availability';
        $eventData['settings']['schedule_type'] = 'available_times';
        $eventData['settings']['range_type'] = 'range_indefinite';
        $eventData['settings']['weekly_schedules'] = [];
        $eventData['settings']['schedule_conditions']['value'] = 1;

        return $eventData;
    }

    public function updateEventVars($eventVars, $calendarEvent)
    {
        if (!$calendarEvent->isOneOffEvent()) {
            return $eventVars;
        }

        $authorTimeZone = $calendarEvent->calendar->author_timezone;

        $availableTimes = $calendarEvent->getAvailableTimes();

        $availableTimeStart = SingleEventService::getFirstAvailableTime($availableTimes, $authorTimeZone, 'UTC');

        $eventVars['slot']['min_bookable_date'] = $availableTimeStart;

        if ($calendarEvent->isGroupEvent()) {
            $availableDate = gmdate('Y-m-d', strtotime($availableTimeStart)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $availableTime = gmdate('H:i', strtotime($availableTimeStart)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $eventVars['slot']['pre_selects']['date'] = $availableDate;
            $eventVars['slot']['pre_selects']['time'] = $availableTime;
        }

        if (count($calendarEvent->getHostIds()) <= 1) {
            $eventVars['team_member_profiles'] = null;
        }

        return $eventVars;
    }

    public function addCalendarSlotData(&$calendarEvent)
    {
        if (!$calendarEvent->isOneOffEvent()) {
            return;
        }

        if (empty($calendarEvent->settings['available_times'])) {
            $eventUrl = Helper::getAppBaseUrl("calendars/{$calendarEvent->calendar_id}/slot-settings/{$calendarEvent->id}/event-availability-settings");
            $calendarEvent->generic_error = '<p style="color: red; margin:0 0 4px 0;">' . __('Please add the availability of this event', 'fluent-booking-pro') . ' <a href="' . $eventUrl . '">' . __('click here', 'fluent-booking-pro') . '</a> </p>';
        }

        return $calendarEvent;
    }

    public function handleCalendarEvent($booking, $calendarEvent)
    {
        if (!$calendarEvent->isOneOffEvent() || $calendarEvent->status != 'active') {
            return;
        }

        if ($calendarEvent->isReserveTime()) {
            $calendarEvent->bookings()->where('status', 'reserved')->delete();
        }

        if ($booking->event_type == 'group_event') {
            $totalBooked = $calendarEvent->bookings()
                ->where('group_id', $booking->group_id)
                ->whereIn('status', ['scheduled', 'pending'])->count();

            if ($totalBooked < $calendarEvent->getMaxBookingPerSlot()) {
                return;
            }
        }

        $calendarEvent->update(['status' => 'expired']);

        $calendarId = $calendarEvent->calendar_id;

        $otherEventsExist = CalendarSlot::where('calendar_id', $calendarId)
            ->where('id', '!=', $calendarEvent->id)
            ->exists();
        
        if (!$otherEventsExist) {
            $booking->calendar->update(['status' => 'expired']);
        }
    }

    public function formatBookingSchedule(&$booking)
    {
        if ($booking->status != 'reserved') {
            return;
        }

        $calendarEvent = $booking->calendar_event;

        $availableTimes = $calendarEvent->getAvailableTimes();

        $authorTimeZone = $calendarEvent->calendar->author_timezone;

        $booking->expires = SingleEventService::getLastAvailableTime($availableTimes, $authorTimeZone, 'UTC');

        $booking->reserved_times = SingleEventService::getReservedTimes($booking);
    }

    public function updateBookingData($bookingData, $calendarEvent)
    {
        if (!$calendarEvent->isOneOffEvent()) {
            return $bookingData;
        }

        $hostIds = $calendarEvent->getHostIds();
        if (!in_array($bookingData['host_user_id'], $hostIds)) {
            $bookingData['host_user_id'] = $hostIds[0];
        }

        return $bookingData;
    }

    public function updateProcessEventData(&$calendarEvent)
    {
        if (!$calendarEvent->isGroupEvent()) {
            return $calendarEvent;
        }

        $availableTimes = $calendarEvent->getAvailableTimes();

        $authorTimeZone = $calendarEvent->calendar->author_timezone;

        $availableTimeStart = SingleEventService::getFirstAvailableTime($availableTimes, $authorTimeZone, 'UTC');

        $calendarEvent->event_time = $availableTimeStart;
    }
}
