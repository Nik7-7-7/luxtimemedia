<?php

namespace FluentBookingPro\App\Modules\SingleEvent;

use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Models\Booking;

class SingleEventService
{
    public static function sanitizeAvailableTimes($availableTimes = [])
    {
        $validTimes = [];
        foreach ($availableTimes as $date => $times) {
            foreach ($times as $time) {
                $time = sanitize_text_field($time);
                $validTimes[$date][] = $time;
            }
            sort($validTimes[$date]);
        }

        return $validTimes;
    }

    public static function getFirstAvailableTime($availableTimes = [], $fromTimeZone = 'UTC', $toTimeZone = 'UTC')
    {
        if (empty($availableTimes)) {
            return '';
        }

        $firstDate = key($availableTimes);
        $firstTime = reset($availableTimes[$firstDate]);

        $firstDateTime = gmdate('Y-m-d H:i', strtotime($firstDate . ' ' . $firstTime)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        if ($fromTimeZone != $toTimeZone) {
            $firstDateTime = DateTimeHelper::convertToTimeZone($firstDateTime, $fromTimeZone, $toTimeZone);
        }

        return $firstDateTime;
    }

    public static function getLastAvailableTime($availableTimes = [], $fromTimeZone = 'UTC', $toTimeZone = 'UTC')
    {
        if (empty($availableTimes)) {
            return '';
        }

        $lastDate = key(array_slice($availableTimes, -1, 1, true));
        $lastTime = end($availableTimes[$lastDate]);

        $lastDateTime = gmdate('Y-m-d H:i', strtotime($lastDate . ' ' . $lastTime)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        if ($fromTimeZone != $toTimeZone) {
            $lastDateTime = DateTimeHelper::convertToTimeZone($lastDateTime, $fromTimeZone, $toTimeZone);
        }

        return $lastDateTime;
    }

    public static function getReservedTimes($booking)
    {
        return Booking::where('group_id', $booking->group_id)
            ->where('status', 'reserved')
            ->pluck('start_time')
            ->toArray();
    }
}