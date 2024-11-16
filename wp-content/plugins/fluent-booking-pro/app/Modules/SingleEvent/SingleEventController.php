<?php

namespace FluentBookingPro\App\Modules\SingleEvent;

use FluentBooking\App\Hooks\Handlers\TimeSlotServiceHandler;
use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Http\Request\Request;

class SingleEventController extends Controller
{
    public function updateEventAvailability(Request $request, $calendarId, $eventId)
    {
        $data = $request->all();

        $calendarEvent = CalendarSlot::where('calendar_id', $calendarId)
            ->with(['bookings' => function ($query) {
                $query->whereIn('status', ['pending', 'scheduled', 'completed']);
            }])->findOrFail($eventId);

        if ($calendarEvent->bookings->isNotEmpty()) {
            wp_send_json([
                'message' => __('This event has already been booked. You can not change the availability now', 'fluent-booking-pro'),
            ], 422);
        }

        $oldReserveTime = $calendarEvent->isReserveTime();
        $timeZone = $calendarEvent->calendar->author_timezone;

        $reserveTime = Arr::isTrue($data, 'reserve_time');
        $availableTimes = Arr::get($data, 'available_times', []);

        if (empty($availableTimes)) {
            wp_send_json([
                'message' => __('Please select at least one time slot', 'fluent-booking-pro'),
            ], 422);
        }

        if ($calendarEvent->isGroupEvent()) {
            $times = array_values($availableTimes)[0];
            if (count($availableTimes) > 1 || count($times) > 1) {
                wp_send_json([
                    'message' => __('Group event can only have one time slot', 'fluent-booking-pro'),
                ], 422);
            }
        }

        $availableTimes = SingleEventService::sanitizeAvailableTimes($availableTimes);

        $calendarEvent->settings = [
            'reserve_time'    => $reserveTime,
            'available_times' => $availableTimes
        ];

        $expireTime = SingleEventService::getLastAvailableTime($availableTimes, $timeZone, 'UTC');

        $calendarEvent->updateMeta('expire_time', $expireTime);

        $calendarEvent->save();

        if ($reserveTime) {
            $this->createReserveBookings($calendarEvent, $availableTimes, $timeZone);
        }

        if (!$reserveTime && $oldReserveTime) {
            $calendarEvent->bookings()->delete();
        }

        return [
            'message' => __('Data has been updated', 'fluent-booking-pro'),
            'event'   => $calendarEvent
        ];
    }

    public function getEventAvailableSlots(Request $request, $calendarId, $eventId)
    {
        $data = $request->all();

        $calendarEvent = CalendarSlot::findOrfail($eventId);

        if (!$calendarEvent) {
            return [];
        }

        $calendar = $calendarEvent->calendar;

        $startDate = sanitize_text_field(Arr::get($data, 'start_date'));

        $timeZone = $calendar->author_timezone;

        $timeSlotService = TimeSlotServiceHandler::initService($calendar, $calendarEvent);

        if (is_wp_error($timeSlotService)) {
            return TimeSlotServiceHandler::sendError($timeSlotService, $calendarEvent, $timeZone);
        }

        $availableSpots = $timeSlotService->generateAvailableSlots($startDate, $timeZone);

        if (is_wp_error($availableSpots)) {
            return TimeSlotServiceHandler::sendError($availableSpots, $calendarEvent, $timeZone);
        }

        $minLookupDate = $calendarEvent->getMinLookUpDate($calendar->author_timezone);

        $availableSpots = array_filter($availableSpots);
        $availableSpots = apply_filters('fluent_booking/available_slots_for_view', $availableSpots, $calendarEvent, $calendar, $timeZone);

        return [
            'available_slots' => $availableSpots,
            'min_lookup_date' => $minLookupDate
        ];
    }

    public function createReserveBookings(CalendarSlot $calendarEvent, $availableTimes, $timeZone)
    {
        $calendarEvent->bookings()->delete();

        $period = $calendarEvent->duration * 60;

        $bookingGroupId = Booking::assignNextGroupId();

        $bookings = [];
        foreach ($availableTimes as $date => $times) {
            foreach ($times as $time) {
                $startDateTime = DateTimeHelper::convertToTimeZone($date . ' ' . $time, $timeZone, 'UTC');
                $endDateTime = gmdate('Y-m-d H:i:s', strtotime($startDateTime) + $period); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

                $booking = [
                    'event_id'     => $calendarEvent->id,
                    'group_id'     => $bookingGroupId,
                    'calendar_id'  => $calendarEvent->calendar_id,
                    'host_user_id' => $calendarEvent->user_id,
                    'event_type'   => $calendarEvent->event_type,
                    'slot_minutes' => $calendarEvent->duration,
                    'start_time'   => $startDateTime,
                    'end_time'     => $endDateTime,
                    'status'       => 'reserved'
                ];

                $bookings[] = $booking;
            }
        }

        $createdBookings = $calendarEvent->bookings()->createMany($bookings);

        $hosts = $calendarEvent->getHostIds();

        $hostData = [];
        foreach ($hosts as $hostId) {
            $hostData[$hostId] = ['status' => 'confirmed'];
        }

        foreach ($createdBookings as $booking) {
            $booking->hosts()->attach($hostData);
        }

        return $createdBookings;
    }
}