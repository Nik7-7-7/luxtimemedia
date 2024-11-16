<?php

namespace FluentBookingPro\App\Modules\SingleEvent;

use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\Framework\Support\Arr;

class SingleTimeSlotService extends TimeSlotService
{
    public function __construct(Calendar $calendar, CalendarSlot $calendarSlot)
    {
        parent::__construct(
            $calendar,
            $calendarSlot
        );
    }

    public function getDates($fromDate = false, $toDate = false, $duration = null, $isDoingBooking = false, $timeZone = 'UTC')
    {
        $fromDate = $fromDate ?: gmdate('Y-m-d'); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $toDate = $toDate ?: gmdate('Y-m-t 23:59:59', strtotime($fromDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $period = $this->calendarSlot->duration * 60;

        $bookedSlots = $this->getBookedSlots([$fromDate, $toDate], 'UTC', $isDoingBooking);

        $fromDate = gmdate('Y-m-d', strtotime($fromDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        $toDate = gmdate('Y-m-d', strtotime($toDate)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

        $bufferTime = $this->calendarSlot->getTotalBufferTime() * 60;

        $cutOutTime = DateTimeHelper::getTimestamp() + $this->calendarSlot->getCutoutSeconds();

        $todayDate = DateTimeHelper::getTodayDate();

        $authorTimeZone = $this->calendar->author_timezone;

        $availableTimes = $this->calendarSlot->getAvailableTimes();

        $rangedSlots = [];
        foreach ($availableTimes as $date => $times) {
            foreach ($times as $time) {
                $endTime = gmdate('H:i', strtotime($time) + $period); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                $endDate = $time < $endTime ? $date : gmdate('Y-m-d', strtotime($date) + 86400); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

                $slot['start'] = DateTimeHelper::convertToTimeZone($date . ' ' . $time, $authorTimeZone, 'UTC');
                $slot['end'] = DateTimeHelper::convertToTimeZone($endDate . ' ' . $endTime, $authorTimeZone, 'UTC');

                $convertedDate = gmdate('Y-m-d', strtotime($slot['start'])); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

                if (($convertedDate == $todayDate && strtotime($slot['start']) < $cutOutTime)) {
                    continue;
                }

                if ($convertedDate < $fromDate) {
                    continue;
                }

                if ($convertedDate > $toDate) {
                    return $rangedSlots;
                }

                $currentBookedSlots = $bookedSlots[$convertedDate] ?? [];

                $isSlotAvailable = $this->isSlotAvailable($slot, $currentBookedSlots, $bufferTime, null);

                if ($isSlotAvailable) {
                    $rangedSlots[$convertedDate] = $rangedSlots[$convertedDate] ?? [];
                    $rangedSlots[$convertedDate][] = $slot;
                }
            }
        }

        return $rangedSlots;
    }

    public function generateAvailableSlots($startDate, $timeZone = 'UTC')
    {
        $event = $this->calendarSlot;

        $startDate = $this->adjustStartDate($startDate, $timeZone);

        $endDate   = $event->getMaxBookableDateTime($startDate, $timeZone);
        $startDate = $event->getMinBookableDateTime($startDate, $timeZone);

        $startDate = DateTimeHelper::convertToTimeZone($startDate, 'UTC', $timeZone);

        $duration = $event->getDuration();

        $period = $duration * 60;

        $bufferTime = $this->calendarSlot->getTotalBufferTime() * 60;

        $cutOutTime = DateTimeHelper::getTimestamp($timeZone) + $event->getCutoutSeconds();

        $todayDate = DateTimeHelper::getTodayDate($timeZone);

        $daySlots = $this->getDefaultDateSlots($duration);

        $ranges = $this->getCurrentDateRange($startDate, $endDate);

        $bookedSlots = $this->getBookedSlots([$startDate, $endDate], $timeZone);

        $availableSlots = [];
        foreach ($ranges as $date) {
            $isToday = $date == $todayDate;
            $currentBookedSlots = $bookedSlots[$date] ?? [];
            $validSlots = [];
            foreach ($daySlots as $start) {
                $end = gmdate('H:i', strtotime($start) + $period); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                $endDate = $start < $end ? $date : gmdate('Y-m-d', strtotime($date) + 86400); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

                $slot = [
                    'start' => $date . ' ' . $start . ':00',
                    'end'   => $endDate . ' ' . $end . ':00'
                ];

                if ($isToday && strtotime($slot['start']) < $cutOutTime) {
                    continue;
                }

                $isSlotAvailable = $this->isSlotAvailable($slot, $currentBookedSlots, $bufferTime, null);

                if ($isSlotAvailable) {
                    $validSlots[] = $slot;
                }
            }
            if ($validSlots) {
                $availableSlots[$date] = $validSlots;
            }
        }

        return $availableSlots;
    }

    private function getDefaultDateSlots($duration)
    {
        $period = $duration * 60;

        $interval = $this->calendarSlot->getSlotInterval($duration) * 60;

        $start = strtotime('00:00');
        $end = strtotime('24:00');

        while ($start + $period <= $end) {
            $daySlots[] = gmdate('H:i', $start); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            $start += $interval;
        }

        return $daySlots;
    }
}