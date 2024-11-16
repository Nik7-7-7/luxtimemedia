<?php

namespace FluentBookingPro\App\Services;

use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\Framework\Support\Arr;

class MultiTimeSlotService extends TimeSlotService
{
    public function __construct(Calendar $calendar, CalendarSlot $calendarSlot)
    {
        parent::__construct(
            $calendar,
            $calendarSlot
        );
    }

    public function isSpotAvailable($fromTime, $toTime, $duration = null, $hostId = null)
    {
        if (!is_array($fromTime)) {
            return call_user_func_array(
                [parent::class, 'isSpotAvailable'],
                func_get_args()
            );
        }

        $fromTimeStamps = array_map('strtotime', $fromTime);
        $toTimeStamps = array_map('strtotime', $toTime);

        $duration = $this->calendarSlot->getDuration($duration);

        list($scheduleTimezone, $dstTime) = $this->getTimezoneInfo();

        $fromStartTime = $this->maybeDayLightSavingTime(min($fromTime), $dstTime, $scheduleTimezone);
        $toEndTime = $this->maybeDayLightSavingTime(max($toTime), $dstTime, $scheduleTimezone);

        $fromTime = gmdate('Y-m-d 00:00:00', strtotime($fromStartTime)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $toTime = gmdate('Y-m-d 23:59:59', strtotime($toEndTime)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $slots = $this->getDates($fromTime, $toTime, $duration, true);

        $conbineTimeStamps = array_combine($fromTimeStamps, $toTimeStamps);

        foreach ($conbineTimeStamps as $fromTimeStamp => $toTimeStamp) {
            $fromDate = gmdate('Y-m-d', $fromTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $toDate = gmdate('Y-m-d', $toTimeStamp); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

            $availableSlots = $slots[$fromDate] ?? [];

            if ($fromDate != $toDate) {
                $availableSlots = array_merge($availableSlots, $slots[$toDate] ?? []);
            }

            $isSlotExists = $this->isSlotExists($availableSlots, $fromTimeStamp, $toTimeStamp);

            if (!$isSlotExists) {
                return false;
            }
        }

        return true;
    }

    protected function getMaxBookingTimestamp($fromDate, $toDate, $timeZone)
    {
        $maxBookingTime = $this->calendarSlot->getMaxBookableDateTime($toDate, $timeZone, 'Y-m-d H:i:s');

        return strtotime($maxBookingTime);
    }
}