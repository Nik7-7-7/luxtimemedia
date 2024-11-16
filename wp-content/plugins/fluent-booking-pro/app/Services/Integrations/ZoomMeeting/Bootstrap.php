<?php

namespace FluentBookingPro\App\Services\Integrations\ZoomMeeting;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\Framework\Support\Arr;

class Bootstrap
{
    public function register()
    {
        /*
         * Global Settings
         */
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 11, 1);

        /*
        * Calendar Slot
        */
        add_filter('fluent_booking/calendar_setting_menu_items', [$this, 'addConnectMenu'], 10, 2);


        /*
         * Booking Level Hooks
         */
        add_action('fluent_booking/pre_after_booking_scheduled', [$this, 'maybeCreateZoomMeeting'], 10, 2);
        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'maybeCancelZoomMeeting'], 10, 1);
        add_action('fluent_booking/after_booking_rescheduled', [$this, 'maybeRescheduleZoomMeeting'], 10, 2);
        
        add_action('fluent_booking/before_delete_booking', [$this, 'maybeDeleteZoomMeeting'], 10, 1);
        add_action('fluent_booking/delete_booking_async_zoom', [$this, 'asyncDeleteZoomMeeting'], 10, 2);

        add_filter('fluent_booking/get_location_fields', [$this, 'addLocationField'], 10, 2);
    }

    public function addGlobalMenu($menuItems)
    {
        $menuItems['zoom_meeting']['disable'] = false;

        return $menuItems;
    }

    public function addConnectMenu($menuItems, $calendar)
    {
        $menuItems['zoom_meeting']['disabled'] = false;

        return $menuItems;
    }

    public function addLocationField($fields, $calendarEvent)
    {
        $hostIds = $calendarEvent->getHostIds();

        if ($calendarEvent->isOneOffEvent()) {
            $hostIds = [$calendarEvent->user_id];
        }

        $errorText = '';
        foreach ($hostIds as $hostId) {
            if (!ZoomHelper::isZoomConfigured($hostId)) {
                $errorText = __('Connect Zoom Account First', 'fluent-booking-pro');
            }
        }

        $message = $errorText ? ' (' . $errorText . ')' : '';

        $defaulData = $fields['conferencing']['options']['zoom_meeting'];

        $updatedData = [
            'title'    => __('Zoom Video', 'fluent-booking-pro') . $message,
            'error'    => $errorText ?: false,
            'disabled' => false
        ];

        $fields['conferencing']['options']['zoom_meeting'] = wp_parse_args($updatedData, $defaulData);

        return $fields;
    }

    public function maybeCreateZoomMeeting($booking, $calendarSlot)
    {
        if (Arr::get($booking, 'location_details.type') !== 'zoom_meeting') {
            return false; // not our location
        }

        $apiClient = ZoomHelper::getZoomClient($booking->host_user_id);

        if (is_wp_error($apiClient)) {
            return false;
        }

        $bookingMeta = $booking->getMeta('__zoom_meeting_details');
        if ($bookingMeta) {
            return false; // Already created
        }

        if ($booking->isMultiGuestBooking()) {
            // Handling Group Meeting
            $totalBooked = Booking::where('group_id', $booking->group_id)
                ->where('status', 'scheduled')
                ->count();

            if ($totalBooked > 1) {
                return $this->updateAttendees($booking);
            }
        }

        // let's prepare the booking data
        $data = [
            'agenda'       => $calendarSlot->title,
            'duration'     => $booking->slot_minutes,
            'type'         => 2,
            'settings'     => [
                'meeting_invitees' => [
                    [
                        'email' => $booking->email
                    ]
                ],
            ],
            'schedule_for' => Arr::get($apiClient, 'origin_email'),
            'start_time'   => gmdate('Y-m-d\TH:i:s\Z', strtotime($booking->start_time)),
            'topic'        => $booking->getBookingTitle(),
        ];

        if ($booking->isMultiGuestBooking()) {
            $data['topic'] = $calendarSlot->title;
        }

        $data = apply_filters('fluent_booking/zoom_meeting_data', $data, $booking, $calendarSlot);

        $response = $apiClient->createMeeting($data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Zoom API Error', 'fluent-booking-pro'),
                /* translators: Notification message for the failure to create a meeting with Zoom API. %s is the error message returned by the API */
                'description' => sprintf(__('Failed to create meeting with Zoom API. API Response: %1s', 'fluent-booking-pro'), esc_attr($response->get_error_message()))
            ]);
            return false;
        }

        $responseData = Arr::only($response, ['id', 'start_url', 'join_url', 'password']);
        $booking->updateMeta('__zoom_meeting_details', $responseData);

        $location = $booking->location_details;
        $location['online_platform_link'] = Arr::get($responseData, 'join_url');
        $location['online_platform_start_link'] = Arr::get($responseData, 'start_url');
        $booking->location_details = $location;
        $booking->save();

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Zoom Meeting has been created', 'fluent-booking-pro'),
            /* translators: Notification message for the successful scheduling of a Zoom meeting. %1$s is a link to start the meeting */
            'description' => sprintf(__('Zoom Meeting has been scheduled. %1$s', 'fluent-booking-pro'), '<a target="_blank" href="' . $location['online_platform_start_link'] . '">' . __('Start Meeting URL', 'fluent-booking-pro') . '</a>')
        ]);

        return true;
    }

    public function maybeCancelZoomMeeting($booking)
    {
        if (Arr::get($booking->location_details, 'type') !== 'zoom_meeting') {
            return false; // not our location
        }

        if ($booking->status != 'cancelled') {
            return false;
        }

        $apiClient = ZoomHelper::getZoomClient($booking->host_user_id);
        if (is_wp_error($apiClient)) {
            return;
        }

        if ($booking->isMultiGuestBooking()) {
            $bookingExist = Booking::where('group_id', $booking->group_id)->count();

            if ($bookingExist > 1) {
                return $this->updateAttendees($booking);
            }
        }

        $bookingMeta = $booking->getMeta('__zoom_meeting_details');
        if (!$bookingMeta) {
            return false; // Nothing to cancel as there is no previous record
        }

        $zoomMeetingId = Arr::get($bookingMeta, 'id');

        if (!$zoomMeetingId) {
            return false;
        }

        $response = $apiClient->deleteMeeting($zoomMeetingId);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Zoom API Error', 'fluent-booking-pro'),
                'description' => __('Failed to delete meeting with Zoom API', 'fluent-booking-pro')
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Zoom Meeting has been deleted', 'fluent-booking-pro'),
            'description' => __('Zoom Meeting has been deleted', 'fluent-booking-pro')
        ]);

        return true;
    }

    public function maybeRescheduleZoomMeeting($updatedBooking, $previousBooking)
    {
        if (Arr::get($updatedBooking->location_details, 'type') !== 'zoom_meeting') {
            return false; // not our location
        }

        if (!ZoomHelper::isZoomConfigured($updatedBooking->host_user_id)) {
            return false;
        }

        $data = [
            'start_time' => gmdate('Y-m-d\TH:i:s\Z', strtotime($updatedBooking->start_time))
        ];

        if ($previousBooking->isMultiGuestBooking()) {
            $totalBooked = Booking::where('group_id', $previousBooking->group_id)
                ->where('status', 'scheduled')
                ->count();

            if ($totalBooked == 0) {
                $this->maybeDeleteZoomMeeting($previousBooking);
                $previousBooking->deleteMeta('__zoom_meeting_details');
            } else {
                $this->updateAttendees($updatedBooking);
            }

            $this->maybeCreateZoomMeeting($updatedBooking, $updatedBooking->calendar_event);
            return false;
        }

        $this->updateZoomMeeting($updatedBooking, $data);
    }

    private function updateAttendees($booking)
    {
        $attendeesEmails = Booking::where('group_id', $booking->group_id)
            ->where('status', 'scheduled')
            ->pluck('email')
            ->toArray();

        $attendees = [];
        foreach ($attendeesEmails as $email) {
            $attendees[] = ['email' => $email];
        }

        $data = [
            'settings' => [
                'meeting_invitees' => $attendees
            ],
        ];

        $this->updateZoomMeeting($booking, $data);
    }

    public function updateZoomMeeting($booking, $data)
    {
        $existingBooking = Booking::where('group_id', $booking->group_id)
            ->where('status', 'scheduled')
            ->first();

        if (!$existingBooking) {
            return false;
        }

        $bookingMeta = $existingBooking->getMeta('__zoom_meeting_details');

        if (!$bookingMeta) {
            return false;
        }

        $api = ZoomHelper::getZoomClient($booking->host_user_id);

        if (is_wp_error($api)) {
            return false;
        }

        $meetingId = Arr::get($bookingMeta, 'id');

        if (!$meetingId) {
            return false;
        }

        $response = $api->patchMeeting($meetingId, $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Zoom API Error', 'fluent-booking-pro'),
                'description' => __('Failed to update meeting with Zoom API', 'fluent-booking-pro')
            ]);
            return false;
        }

        $location = $booking->location_details;
        $location['online_platform_link'] = Arr::get($bookingMeta, 'join_url');
        $location['online_platform_start_link'] = Arr::get($bookingMeta, 'start_url');
        $booking->location_details = $location;
        $booking->save();

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Zoom Meeting has been updated', 'fluent-booking-pro'),
            'description' => __('Zoom Meeting has been updated with the new data', 'fluent-booking-pro')
        ]);

        return true;
    }

    public function asyncDeleteZoomMeeting($hostId, $zoomMeetingId)
    {
        $apiClient = ZoomHelper::getZoomClient($hostId);

        $apiClient->deleteMeeting($zoomMeetingId);
    }

    public function maybeDeleteZoomMeeting(Booking $booking)
    {
        if (Arr::get($booking->location_details, 'type') !== 'zoom_meeting') {
            return false; // not our location
        }

        $bookingMeta = $booking->getMeta('__zoom_meeting_details');

        if (!$bookingMeta || !($zoomMeetingId = Arr::get($bookingMeta, 'id'))) {
            return false; // Nothing to cancel as there is no previous record
        }

        as_enqueue_async_action('fluent_booking/delete_booking_async_zoom', [
            $booking->host_user_id,
            $zoomMeetingId
        ], 'fluent-booking');
    }
}
