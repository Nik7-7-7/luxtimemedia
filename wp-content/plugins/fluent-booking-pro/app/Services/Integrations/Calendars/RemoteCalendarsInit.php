<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars;

use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;

class RemoteCalendarsInit
{
    public function boot()
    {
        (new \FluentBookingPro\App\Services\Integrations\Calendars\Google\Bootstrap())->register();
        (new \FluentBookingPro\App\Services\Integrations\Calendars\Outlook\Bootstrap())->register();
        (new \FluentBookingPro\App\Services\Integrations\Calendars\AppleCalendar\Bootstrap())->register();
        (new \FluentBookingPro\App\Services\Integrations\Calendars\NextCloud\Bootstrap())->register();

        add_action('fluent_booking/pre_after_booking_scheduled', [$this, 'checkForRemoteCalendarEventInsert'], 11, 2);

        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'checkForRemoteCalendarEventCancel'], 10, 1);

        add_action('fluent_booking/after_booking_rescheduled', [$this, 'checkForRemoteCalendarEventReschedule'], 10, 2);

        add_action('fluent_booking/before_delete_booking', [$this, 'checkForRemoteCalendarEventDelete'], 10, 1);

        add_action('fluent_booking/after_patch_booking_email', [$this, 'checkForRemoteCalendarEventEmailUpdate'], 10, 3);

        add_action('fluent_booking/after_disconnect_remote_calendar', function ($metaId, $calendar) {
            $config = RemoteCalendarHelper::getRemoteCalendarConfig($calendar->user_id);
            if (!$config) {
                return; // no integration available
            }

            $meta = Meta::where('id', $config['db_id'])->first();
            if (!$meta) {
                RemoteCalendarHelper::updateUserRemoteCreatableCalendarSettings($calendar->user_id, []);
            }
        }, 10, 2);
    }

    public function checkForRemoteCalendarEventInsert($booking, $slot)
    {
        $config = RemoteCalendarHelper::getRemoteCalendarConfig($booking->host_user_id);

        if (!$config) {
            return; // no integration available
        }

        if ($booking->isMultiGuestBooking()) {
            // this is a group event
            $allGroupBookings = Booking::query()->where('group_id', $booking->group_id)
                ->where('status', 'scheduled')
                ->orderBy('id', 'ASC')
                ->get();

            if ($allGroupBookings->count() > 1) {
                do_action('fluent_booking/refresh_remote_calendar_group_members_' . $config['driver'], $config, $booking, $allGroupBookings, false);
                return;
            }
        }

        do_action('fluent_booking/create_remote_calendar_event_' . $config['driver'], $config, $booking);
    }

    public function checkForRemoteCalendarEventCancel(Booking $booking)
    {
        if ($booking->status !== 'cancelled') {
            return false;
        }

        $config = RemoteCalendarHelper::getRemoteCalendarConfig($booking->host_user_id);

        if (!$config) {
            return; // no integration available
        }

        if ($booking->isMultiGuestBooking()) {
            // this is a group event
            $allGroupBookings = Booking::query()->where('group_id', $booking->group_id)
                ->where('status', 'scheduled')
                ->orderBy('id', 'ASC')
                ->get();

            if ($allGroupBookings->count()) {
                do_action('fluent_booking/refresh_remote_calendar_group_members_' . $config['driver'], $config, $booking, $allGroupBookings, false);
                return;
            }
        }

        do_action('fluent_booking/cancel_remote_calendar_event_' . $config['driver'], $config, $booking);
    }

    public function checkForRemoteCalendarEventReschedule(Booking $booking, $previousBooking)
    {
        $config = RemoteCalendarHelper::getRemoteCalendarConfig($booking->host_user_id);
        if (!$config) {
            return;
        }

        if ($booking->isMultiGuestBooking()) {
            // let's check if the previous booking group has any booking
            $previousGroupBookings = Booking::query()->where('group_id', $previousBooking->group_id)
                ->where('status', 'scheduled')
                ->orderBy('id', 'ASC')
                ->get();

            if ($previousGroupBookings->count()) {
                $booking->status = 'rescheduling';
                // we need to refresh the group members
                do_action('fluent_booking/refresh_remote_calendar_group_members_' . $config['driver'], $config, $booking, $previousGroupBookings, true);
                $booking->status = 'scheduled';
            } else {
                do_action('fluent_booking/delete_remote_calendar_event_' . $config['driver'], $config, $booking);
            }

            $newGroupings = Booking::query()->where('group_id', $booking->group_id)
                ->where('status', 'scheduled')
                ->orderBy('id', 'ASC')
                ->get();

            if ($newGroupings->count() > 1) {
                $booking->updateMeta('__' . $config['driver'] . '_calendar_event', []);
                do_action('fluent_booking/refresh_remote_calendar_group_members_' . $config['driver'], $config, $booking, $newGroupings, false);
                return;
            }

            do_action('fluent_booking/create_remote_calendar_event_' . $config['driver'], $config, $booking);
            return;
        }

        do_action('fluent_booking/patch_remote_calendar_event_' . $config['driver'], $config, $booking, [
            'start' => $booking->start_time,
            'end'   => $booking->end_time
        ], true);
    }

    public function checkForRemoteCalendarEventEmailUpdate(Booking $booking, $calendarEvent, $oldEmail)
    {
        $config = RemoteCalendarHelper::getRemoteCalendarConfig($booking->host_user_id);

        if (!$config) {
            return; // no integration available
        }

        do_action('fluent_booking/patch_remote_calendar_event_' . $config['driver'], $config, $booking, [
            'email'     => $booking->email,
            'old_email' => $oldEmail
        ], true);
    }

    public function checkForRemoteCalendarEventDelete(Booking $booking)
    {
        $config = RemoteCalendarHelper::getRemoteCalendarConfig($booking->host_user_id);

        if (!$config) {
            return; // no integration available
        }

        do_action('fluent_booking/delete_remote_calendar_event_' . $config['driver'], $config, $booking);
    }
}
