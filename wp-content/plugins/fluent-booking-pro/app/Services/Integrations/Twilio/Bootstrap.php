<?php

namespace FluentBookingPro\App\Services\Integrations\Twilio;

use FluentBooking\App\Services\EditorShortCodeParser;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\App;

class Bootstrap
{
    public function register()
    {
        $this->registerClientHooks();
        $this->registerBookingHooks();
    }

    protected function registerClientHooks()
    {
        add_filter('fluent_booking/settings_menu_items', [$this, 'addGlobalMenu'], 12, 1);
        add_filter('fluent_booking/get_client_settings_twilio', [$this, 'getOauthClientSettings']);
        add_filter('fluent_booking/get_client_field_settings_twilio', [$this, 'getOauthClientSettingsFields']);
        add_action('fluent_booking/save_client_settings_twilio', [$this, 'saveOauthClientSettings'], 10, 1);
    }

    protected function registerBookingHooks()
    {
        if (!TwilioHelper::isConnected()) {
            return;
        }
        add_action('fluent_booking/after_booking_scheduled', [$this, 'pushBookingScheduledToQueue'], 10, 2);
        add_action('fluent_booking/after_booking_scheduled_sms_async', [$this, 'bookingScheduledSms'], 10, 1);
        add_action('fluent_booking/after_booking_pending', [$this, 'pushBookingPendingToQueue'], 10, 2);
        add_action('fluent_booking/after_booking_pending_sms_async', [$this, 'bookingRequestSms'], 10, 2);
        add_action('fluent_booking/after_booking_rescheduled', [$this, 'smsOnBookingRescheduled'], 10, 2);
        add_action('fluent_booking/booking_schedule_reminder_sms', [$this, 'bookingReminderSms'], 10, 2);
        add_action('fluent_booking/booking_schedule_cancelled', [$this, 'smsOnBookingCancelled'], 10, 1);
        add_action('fluent_booking/booking_schedule_rejected', [$this, 'smsOnBookingRejected'], 10, 2);
    }

    public function addGlobalMenu($menuItems)
    {
        $menuItems['twilio']['disable'] = false;

        return $menuItems;
    }

    public function saveOauthClientSettings($settings)
    {        
        return TwilioHelper::updateApiConfig($settings);
    }

    public function getOauthClientSettings($settings)
    {
        $config = TwilioHelper::getApiConfig();

        if ($config['auth_token']) {
            $config['auth_token'] = '********************';
        }

        return $config;
    }

    public function getOauthClientSettingsFields($items)
    {
        $app = App::getInstance();

        $fields = [
            'sender_number' => [
                'type'        => 'text',
                'label'       => __('SMS Number', 'fluent-booking-pro'),
                'placeholder' => __('Enter Twilio Sender SMS Number', 'fluent-booking-pro'),
            ],
            'sender_whatsapp' => [
                'type'        => 'text',
                'label'       => __('WhatsApp Number (Optional)', 'fluent-booking-pro'),
                'placeholder' => __('Enter Twilio Sender WhatsApp Number', 'fluent-booking-pro'),
            ],
            'account_sid' => [
                'type'        => 'text',
                'label'       => __('Account SID', 'fluent-booking-pro'),
                'placeholder' => __('Enter Twilio Account SID', 'fluent-booking-pro'),
            ],
            'auth_token'  => [
                'type'        => 'text',
                'label'       => __('Auth Token', 'fluent-booking-pro'),
                'placeholder' => __('Enter Twilio API Auth Token', 'fluent-booking-pro'),
            ],
        ];

        $description = '<p>' . __('Please read the step-by-step documentation to setup Account SID and Auth Token and get the Sender Numbers for your app.', 'fluent-booking-pro') . ' <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/twilio-integration-with-fluentbooking/">' . __('Go to the documentation article', 'fluent-booking-pro') . '</a></p>';

        return [
            'logo'            => $app['url.assets'] . 'images/tw.svg',
            'title'           => __('Twilio SMS Integration', 'fluent-booking-pro'),
            'subtitle'        => __('Configure Twilio API to send SMS/WhatsApp notifications on booking events', 'fluent-booking-pro'),
            'description'     => $description,
            'is_connected'    => TwilioHelper::isConnected(),
            'is_configured'   => TwilioHelper::isConfigured(),
            'check_validation'=> true,
            'valid_message'   => __('Your Twilio API integration is up and running.', 'fluent-booking-pro'),
            'invalid_message' => __('Your Twilio API Key is not valid.', 'fluent-booking-pro'),
            'save_btn_text'   => __('Save Settings', 'fluent-booking-pro'),
            'fields'          => $fields,
            'will_encrypt'    => true
        ];
    }

    public function sendSmsNotification($booking, $data)
    {
        $sendTo = Arr::get($data, 'send_to');
        $message = Arr::get($data, 'message');
        $receiverNumber = Arr::get($data, 'receiver_number');

        $config = TwilioHelper::getApiConfig();

        $isWhatsApp = $sendTo == 'whatsapp';

        $senderNumber = $isWhatsApp ? $config['sender_whatsapp'] : $config['sender_number'];

        if (!$message || !$receiverNumber || !$senderNumber) {
            return;
        }
        
        $message = str_replace('<br />', "\n", $message);
        $message = preg_replace('/\h+/', ' ', sanitize_textarea_field($message));

        $body = [
            'Body' => trim($message),
            'From' => $isWhatsApp ? 'whatsapp:' . $senderNumber : $senderNumber,
            'To'   => $isWhatsApp ? 'whatsapp:' . $receiverNumber : $receiverNumber
        ];

        $body = apply_filters('fluent_booking/before_send_integration_data_twilio', $body, $booking);

        $api = TwilioHelper::getApiClient();

        $response = $api->sendSMS($config['account_sid'], $body);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Twilio API Error', 'fluent-booking-pro'),
                'description' => __('Failed to send sms with Twilio API', 'fluent-booking-pro')
            ]);
            return false;
        }
        return true;
    }

    private function getReminderTime($time)
    {
        $timestamp = $time['value'] * 60;

        if ($time['unit'] == 'hours') {
            $timestamp = $timestamp * 60;
        } elseif ($time['unit'] == 'days') {
            $timestamp = $timestamp * 60 * 24;
        }

        return $timestamp;
    }

    private function pushRemindersToQueue($booking, $reminderTimes, $emailTo)
    {
        foreach ($reminderTimes as $time) {
            $reminderTimestamp = $this->getReminderTime($time);

            $happeningTimestamp = strtotime($booking->start_time);
            $startingTo = $happeningTimestamp - time();

            $bufferTime = 2 * 60; // 2 Minute Buffer Time
            if ($startingTo > ($reminderTimestamp + $bufferTime)) {
                as_schedule_single_action(($happeningTimestamp - $reminderTimestamp), 'fluent_booking/booking_schedule_reminder_sms', [
                    $booking->id,
                    $emailTo
                ], 'fluent-booking');
            }
        }
    }

    public function pushBookingScheduledToQueue($booking, $bookingEvent)
    {   
        $notifications = TwilioHelper::getSmsNotifications($bookingEvent);

        if (Arr::isTrue($notifications, 'booking_conf_attendee.enabled') || (Arr::isTrue($notifications, 'booking_conf_host.enabled'))) {
            as_enqueue_async_action('fluent_booking/after_booking_scheduled_sms_async', [
                $booking->id
            ], 'fluent-booking');
        }

        if (Arr::isTrue($notifications, 'reminder_to_attendee.enabled')) {
            $reminderTimes = Arr::get($notifications, 'reminder_to_attendee.sms.times', []);
            $this->pushRemindersToQueue($booking, $reminderTimes, 'guest');
        }

        if (Arr::isTrue($notifications, 'reminder_to_host.enabled')) {
            $reminderTimes = Arr::get($notifications, 'reminder_to_host.sms.times', []);
            $this->pushRemindersToQueue($booking, $reminderTimes, 'host');
        }
    }

    public function pushBookingPendingToQueue($booking, $bookingEvent)
    {
        if (!$bookingEvent->isConfirmationEnabled()) {
            return;
        }

        if ($booking->payment_method && $booking->payment_status != 'paid') {
            return;
        }
        
        $notifications = TwilioHelper::getSmsNotifications($bookingEvent);

        if (Arr::isTrue($notifications, 'booking_request_host.enabled') || (Arr::isTrue($notifications, 'booking_request_attendee.enabled'))) {
            as_enqueue_async_action('fluent_booking/after_booking_pending_sms_async', [
                $booking->id,
                $bookingEvent->id
            ], 'fluent-booking');
        }
    }

    public function bookingScheduledSms($bookingId)
    {
        $booking = Booking::with(['user', 'calendar_event'])->find($bookingId);

        if (!$booking || !$booking->calendar_event) {
            return '';
        }

        $notifications = TwilioHelper::getSmsNotifications($booking->calendar_event);

        if (Arr::isTrue($notifications, 'booking_conf_attendee.enabled')) {
            $sms = Arr::get($notifications, 'booking_conf_attendee.sms', []);

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = $booking->getInviteePhoneNumber($booking->calendar_event);
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);

            if ($smsSend) {
                do_action('fluent_booking/log_booking_note', [
                    'title'       => __('SMS Sent Successfully', 'fluent-booking-pro'),
                    'type'        => 'activity',
                    'description' => __('Booking Confirmation SMS has been sent to attendee'),
                    'booking_id'  => $booking->id
                ]);
            }
        }

        if (Arr::isTrue($notifications, 'booking_conf_host.enabled')) {
            $sms = Arr::get($notifications, 'booking_conf_host.sms', []);

            $hostPhone = $booking->user->getMeta('host_phone');

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = (Arr::get($sms, 'receiver') == 'host_number') ? $hostPhone : Arr::get($sms, 'number');
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);

            if ($smsSend) {
                do_action('fluent_booking/log_booking_note', [
                    'title'       => __('SMS Sent Successfully', 'fluent-booking-pro'),
                    'type'        => 'activity',
                    'description' => __('Booking Confirmation SMS has been sent to host'),
                    'booking_id'  => $booking->id
                ]);
            }
        }

        return true;
    }

    public function bookingReminderSms($bookingId, $emailTo)
    {
        if (!TwilioHelper::isConnected()) {
            return;
        }

        $booking = Booking::with(['user', 'calendar_event'])->find($bookingId);

        if (!$booking || $booking->status != 'scheduled') {
            return false;
        }

        $notifications = TwilioHelper::getSmsNotifications($booking->calendar_event);

        if (!$notifications) {
            return;
        }
        
        if ('guest' == $emailTo && Arr::isTrue($notifications, 'reminder_to_attendee.enabled')) {
            $sms = Arr::get($notifications, 'reminder_to_attendee.sms', []);

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = $booking->getInviteePhoneNumber($booking->calendar_event);
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);

            if ($smsSend) {
                do_action('fluent_booking/log_booking_note', [
                    'title'       => __('Reminder SMS Sent Successfully', 'fluent-booking-pro'),
                    'type'        => 'activity',
                    'description' => __('Booking Reminder SMS has been sent to attendee'),
                    'booking_id'  => $booking->id
                ]);
            }

        } elseif ('host' == $emailTo && Arr::isTrue($notifications, 'reminder_to_host.enabled')) {
            $sms = Arr::get($notifications, 'reminder_to_host.sms', []);

            $hostPhone = $booking->user->getMeta('host_phone');

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = (Arr::get($sms, 'receiver') == 'host_number') ? $hostPhone : Arr::get($sms, 'number');
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);

            if ($smsSend) {
                do_action('fluent_booking/log_booking_note', [
                    'title'       => __('Reminder SMS Sent Successfully', 'fluent-booking-pro'),
                    'type'        => 'activity',
                    'description' => __('Booking Reminder SMS has been sent to host'),
                    'booking_id'  => $booking->id
                ]);
            }
        }
    }

    public function bookingRequestSms($bookingId, $calendarEventId)
    {
        $booking = Booking::with(['calendar', 'calendar_event'])->find($bookingId);

        if (!$booking || !$booking->calendar_event) {
            return '';
        }

        $notifications = TwilioHelper::getSmsNotifications($booking->calendar_event);
        if (!$notifications) {
            return;
        }

        if (Arr::isTrue($notifications, 'booking_request_attendee.enabled')) {
            $sms = Arr::get($notifications, 'booking_request_attendee.sms', []);

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = $booking->getInviteePhoneNumber($booking->calendar_event);
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);
        }

        if (Arr::isTrue($notifications, 'booking_request_host.enabled')) {
            $sms = Arr::get($notifications, 'booking_request_host.sms', []);
            $hostPhone = $booking->user->getMeta('host_phone');

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = (Arr::get($sms, 'receiver') == 'host_number') ? $hostPhone : Arr::get($sms, 'number');
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);
        }
    }

    public function smsOnBookingCancelled(Booking $booking)
    {
        $calendarEvent = $booking->calendar_event;
        if (!$calendarEvent) {
            return;
        }

        $notifications = TwilioHelper::getSmsNotifications($calendarEvent);
        if (!$notifications) {
            return;
        }

        $cancelledBy = $booking->getMeta('cancelled_by_type', 'host');

        if ($cancelledBy == 'host') {
            if (Arr::isTrue($notifications, 'cancelled_by_host.enabled')) {
                // This from the host
                $sms = Arr::get($notifications, 'cancelled_by_host.sms', []);
    
                $smsData['send_to'] = Arr::get($sms, 'send_to');
                $smsData['receiver_number'] = $booking->getInviteePhoneNumber($booking->calendar_event);
                $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);
    
                $smsSend = $this->sendSmsNotification($booking, $smsData);
    
                if ($smsSend) {
                    do_action('fluent_booking/log_booking_note', [
                        'title'       => __('Cancellation SMS Sent Successfully', 'fluent-booking-pro'),
                        'type'        => 'activity',
                        'description' => __('Booking Cancellation SMS has been sent to the Attendee'),
                        'booking_id'  => $booking->id
                    ]);
                }
            }
            return;
        }

        if (Arr::isTrue($notifications, 'cancelled_by_attendee.enabled')) {
            $sms = Arr::get($notifications, 'cancelled_by_attendee.sms', []);

            $hostPhone = $booking->user->getMeta('host_phone');

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = (Arr::get($sms, 'receiver') == 'host_number') ? $hostPhone : Arr::get($sms, 'number');
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);

            if ($smsSend) {
                do_action('fluent_booking/log_booking_note', [
                    'title'       => __('Cancellation SMS Sent Successfully', 'fluent-booking-pro'),
                    'type'        => 'activity',
                    'description' => __('Booking Cancellation SMS has been sent to the host'),
                    'booking_id'  => $booking->id
                ]);
            }
        }
    }

    public function smsOnBookingRejected(Booking $booking, $calendarEvent)
    {
        if (!$calendarEvent) {
            return;
        }

        $notifications = TwilioHelper::getSmsNotifications($calendarEvent);
        if (!$notifications) {
            return;
        }

        if (Arr::isTrue($notifications, 'declined_by_host.enabled')) {
            $sms = Arr::get($notifications, 'declined_by_host.sms', []);

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = $booking->getInviteePhoneNumber($calendarEvent);
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);
        }
    }

    public function smsOnBookingRescheduled(Booking $booking)
    {
        $calendarEvent = $booking->calendar_event;
        if (!$calendarEvent) {
            return;
        }

        $notifications = TwilioHelper::getSmsNotifications($calendarEvent);
        if (!$notifications) {
            return;
        }

        $rescheduledBy = $booking->getMeta('rescheduled_by_type', 'host');

        if ($rescheduledBy == 'host') {
            if (Arr::isTrue($notifications, 'rescheduled_by_host.enabled')) {
                // This from the host
                $sms = Arr::get($notifications, 'rescheduled_by_host.sms', []);
                
                $hostPhone = $booking->user->getMeta('host_phone');

                $smsData['send_to'] = Arr::get($sms, 'send_to');
                $smsData['receiver_number'] = (Arr::get($sms, 'receiver') == 'host_number') ? $hostPhone : Arr::get($sms, 'number');
                $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);
    
                $smsSend = $this->sendSmsNotification($booking, $smsData);

                if ($smsSend) {
                    do_action('fluent_booking/log_booking_note', [
                        'title'       => __('Rescheduling SMS Sent Successfully', 'fluent-booking-pro'),
                        'type'        => 'activity',
                        'description' => __('Booking Rescheduling SMS has been sent to the host'),
                        'booking_id'  => $booking->id
                    ]);
                }
            }
            return;
        }

        if (Arr::isTrue($notifications, 'rescheduled_by_attendee.enabled')) {
            $sms = Arr::get($notifications, 'rescheduled_by_attendee.sms', []);

            $smsData['send_to'] = Arr::get($sms, 'send_to');
            $smsData['receiver_number'] = $booking->getInviteePhoneNumber($booking->calendar_event);
            $smsData['message'] = EditorShortCodeParser::parse(Arr::get($sms, 'body'), $booking);

            $smsSend = $this->sendSmsNotification($booking, $smsData);

            if ($smsSend) {
                do_action('fluent_booking/log_booking_note', [
                    'title'       => __('Rescheduling SMS Sent Successfully', 'fluent-booking-pro'),
                    'type'        => 'activity',
                    'description' => __('Booking Rescheduling SMS has been sent to the Attendee'),
                    'booking_id'  => $booking->id
                ]);
            }
        }
    }
}
