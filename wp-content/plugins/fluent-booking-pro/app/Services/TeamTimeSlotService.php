<?php

namespace FluentBookingPro\App\Services;

use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\TimeSlotService;
use FluentBooking\Framework\Support\Arr;

class TeamTimeSlotService extends TimeSlotService
{
    public $hostUserId = null;

    protected $isRoundRobinDefault = false;

    protected $isRoundRobinCommon = false;

    public function __construct(Calendar $calendar, CalendarSlot $calendarSlot)
    {
        parent::__construct(
            $calendar,
            $calendarSlot
        );

        $this->isRoundRobinDefault = $this->calendarSlot->isRoundRobinDefaultSchedule();
        $this->isRoundRobinCommon = $this->calendarSlot->isRoundRobinCommonSchedule();
    }

    public function isSpotAvailable($fromTime, $toTime, $duration = null, $hostId = null)
    {
        $hostIds = $this->calendarSlot->getHostIdsSortedByBookings($fromTime, $hostId);

        $parentInstance = new TimeSlotService($this->calendar, $this->calendarSlot);

        foreach ($hostIds as $id) {
            $isSpotAvailable = $parentInstance->isSpotAvailable($fromTime, $toTime, $duration, $id);
            if ($isSpotAvailable) {
                $this->hostUserId = $id;
                return true;
            }
        }

        return false;
    }

    protected function getRangedValidSlots($ranges, $duration, $bookedSlots, $cutOutTime, $maxBookingTime, $timezoneInfo, $rangedSlots = [], $hostId = null)
    {
        if (!$this->isRoundRobinDefault) {
            return call_user_func_array(
                [parent::class, 'getRangedValidSlots'],
                func_get_args()
            );
        }

        $hostIds = $this->calendarSlot->getHostIds($this->hostId);

        foreach ($hostIds as $hostId)
        {
            $timezoneInfo = $this->getTimezoneInfo($hostId);
            $rangedSlots = parent::getRangedValidSlots($ranges, $duration, $bookedSlots, $cutOutTime, $maxBookingTime, $timezoneInfo, $rangedSlots, $hostId);
        }

        return $rangedSlots;
    }

    protected function isSlotAvailable(&$slot, $currentBookedSlots, $bufferTime, $hostId)
    {
        if (!$currentBookedSlots) {
            return true;
        }

        $startTimeStamp = strtotime($slot['start']);
        $endTimeStamp = strtotime($slot['end']);

        if ($this->isRoundRobinDefault) {
            return $this->isSlotAvailableForRoundRobin($currentBookedSlots, $bufferTime, $startTimeStamp, $endTimeStamp, $hostId);
        }

        if ($this->isRoundRobinCommon) {
            return $this->isSlotAvailableForRoundRobinCommon($currentBookedSlots, $bufferTime, $startTimeStamp, $endTimeStamp);
        }
        
        return true;
    }

    private function isSlotAvailableForRoundRobin($currentBookedSlots, $bufferTime, $startTimeStamp, $endTimeStamp, $hostId)
    {
        if (empty($currentBookedSlots[$hostId])) {
            return true;
        }

        foreach ($currentBookedSlots[$hostId] as $bookedSlot) {
            $bookedStart = strtotime($bookedSlot['start']);
            $bookedEnd = strtotime($bookedSlot['end']);

            if (Arr::get($bookedSlot, 'source')) {
                $bookedStart = $bookedStart - $bufferTime;
                $bookedEnd = $bookedEnd + $bufferTime;
            }

            if (
                ($startTimeStamp >= $bookedStart && $startTimeStamp < $bookedEnd) ||
                ($endTimeStamp > $bookedStart && $endTimeStamp <= $bookedEnd) ||
                ($startTimeStamp <= $bookedStart && $endTimeStamp > $bookedStart) ||
                ($startTimeStamp < $bookedEnd && $endTimeStamp >= $bookedEnd)
            ) {
                return false;
            }
        }

        return true;
    }

    private function isSlotAvailableForRoundRobinCommon($currentBookedSlots, $bufferTime, $startTimeStamp, $endTimeStamp)
    {
        $totalHosts = count($this->calendarSlot->getHostIds());
        $totalBookedHosts = count($currentBookedSlots);

        if ($totalHosts != $totalBookedHosts) {
            return true;
        }

        $bookedHosts = 0;
        foreach ($currentBookedSlots as $bookedSlots) {
            foreach ($bookedSlots as $bookedSlot) {
                $bookedStart = strtotime($bookedSlot['start']);
                $bookedEnd = strtotime($bookedSlot['end']);
    
                if (Arr::get($bookedSlot, 'source')) {
                    $bookedStart = $bookedStart - $bufferTime;
                    $bookedEnd = $bookedEnd + $bufferTime;
                }

                if (
                    ($startTimeStamp >= $bookedStart && $startTimeStamp < $bookedEnd) ||
                    ($endTimeStamp > $bookedStart && $endTimeStamp <= $bookedEnd) ||
                    ($startTimeStamp <= $bookedStart && $endTimeStamp > $bookedStart) ||
                    ($startTimeStamp < $bookedEnd && $endTimeStamp >= $bookedEnd)
                ) {
                    $bookedHosts++;
                    break;
                }
            }
        }

        if ($bookedHosts == $totalHosts) {
            return false;
        }

        return true;
    }

    protected function processBookings($bookings, $toTimeZone, $maxBooking, $isGroupBooking)
    {
        $books = [];
        foreach ($bookings as $booking) {
            $booking = $booking[0];
            $hostId = $booking->host_user_id;

            if ($toTimeZone != 'UTC') {
                $booking->start_time = DateTimeHelper::convertToTimeZone($booking->start_time, 'UTC', $toTimeZone);
                $booking->end_time = DateTimeHelper::convertToTimeZone($booking->end_time, 'UTC', $toTimeZone);
            }

            $date = gmdate('Y-m-d', strtotime($booking->start_time)); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

            $books[$date] = $books[$date] ?? [];
            
            $bufferTime = $booking->calendar_event->getTotalBufferTime();

            if ($bufferTime) {
                $beforeBufferTime = gmdate('Y-m-d H:i:s', strtotime($booking->start_time . " -$bufferTime minutes")); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                $afterBufferTime = gmdate('Y-m-d H:i:s', strtotime($booking->end_time . " +$bufferTime minutes")); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date

                $booking->start_time = $beforeBufferTime;
                $booking->end_time = $afterBufferTime;
            }

            $rangedItems = $this->createDateRangeArrayFromSlotConfig([
                'event_id'  => $booking->event_id,
                'start'     => $booking->start_time,
                'end'       => $booking->end_time
            ]);

            foreach ($rangedItems as $date => $slot) {
                $books[$date][$hostId] = $books[$date][$hostId] ?? [];
                $books[$date][$hostId][] = $slot;
            }
        }

        return $books;
    }

    protected function processRemoteBookings($books, $remoteBookings)
    {
        if (!$remoteBookings) {
            return $books;
        }

        foreach ($remoteBookings as $slot) {
            $hostId = $slot['host_id'];

            $rangedItems = $this->createDateRangeArrayFromSlotConfig([
                'start'   => $slot['start'],
                'end'     => $slot['end'],
                'source'  => $slot['source']
            ]);

            foreach ($rangedItems as $rangedDate => $rangedSlot) {
                $books[$rangedDate][$hostId] = $books[$rangedDate][$hostId] ?? [];
            
                if (!$this->isLocalBooking($books[$rangedDate][$hostId], $rangedSlot)) {
                    $books[$rangedDate][$hostId][] = $rangedSlot;
                }
            }
        }
        return $books;
    }
}