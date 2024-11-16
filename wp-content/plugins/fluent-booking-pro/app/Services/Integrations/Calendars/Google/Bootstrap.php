<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\BaseCalendar;
use FluentBooking\App\Services\Integrations\Calendars\CalendarCache;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Support\Arr;

class Bootstrap extends BaseCalendar
{
    public function register()
    {
        $app = App::getInstance();

        $this->calendarKey = 'google';
        $this->calendarTitle = __('Google Calendar / Meet', 'fluent-booking-pro');
        $this->logo = $app['url.assets'] . 'images/gg-calendar.svg';
        $this->boot();

        add_action('wp_ajax_fluent_booking_g_auth', [$this, 'handleAuthCallback']);

        add_action('fluent_booking/before_get_all_calendars', function () {
            if (!$this->isConfigured()) {
                return;
            }
            // Show the Google last error
            add_action('fluent_booking/calendar', [$this, 'addErrorMessage'], 10, 2);
        });

        add_filter('fluent_booking/get_location_fields', [$this, 'addLocationField'], 10, 2);

        add_action('fluent_booking/delete_booking_async_google', [$this, 'asyncDeleteEvent'], 10, 4);
    }

    public function pushToGlobalMenu($menuItems)
    {
        $menuItems[$this->calendarKey]['disable'] = false;

        return $menuItems;
    }

    public function addErrorMessage(&$calendar, $type)
    {
        if ($type != 'lists' || $calendar->type == 'team') {
            return $calendar;
        }

        $meta = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $calendar->user_id)
            ->first();

        if (!$meta || empty(Arr::get($meta->value, 'last_error'))) {
            return $calendar;
        }
        $error = Arr::get($meta->value, 'last_error');
        $calendar->generic_error = '<p style="color: red; margin:0;">' . __('Google Calendar API Error:', 'fluent-booking-pro') . ' ' . $error . '. <a href="' . Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars') . '">' . __('Click Here to Review', 'fluent-booking-pro') . '</a></p>';

        return $calendar;
    }

    public function addLocationField($fields, $calendarEvent)
    {
        $hostIds = $calendarEvent->getHostIds();

        if ($calendarEvent->isOneOffEvent()) {
            $hostIds = [$calendarEvent->user_id];
        }

        $errorText = '';
        foreach ($hostIds as $hostId) {
            $meetExist = Meta::where('object_type', '_google_user_token')
                ->where('object_id', $hostId)
                ->first();

            $errorText = !$meetExist ? __('Connect Google Meet First', 'fluent-booking-pro') : '';

            if (!$meetExist) {
                break;
            }

            if (!$errorText) {
                // now check if the user calendar event create enabled
                $calConfig = RemoteCalendarHelper::getUserRemoteCreatableCalendarSettings($hostId);
                if (!$calConfig || Arr::get($calConfig, 'driver') != 'google') {
                    $errorText = __('Set Google Event Creat First', 'fluent-booking-pro');
                    $meetExist = false;
                    break;
                }
            }
        }

        if ($calendarEvent->isTeamEvent()) {
            $errorText = $errorText ? __('All Hosts Need to ', 'fluent-booking-pro') . $errorText : '';
        }

        if ($calendarEvent->isOneOffEvent()) {
            $errorText = $errorText ? __('Organizer Needs to ', 'fluent-booking-pro') . $errorText : '';
        }

        $message = $errorText ? ' (' . $errorText . ')' : '';

        $defaulData = $fields['conferencing']['options']['google_meet'];

        $updatedData = [
            'title'    => __('Google Meet', 'fluent-booking-pro') . $message,
            'error'    => $errorText ?: false,
            'disabled' => false
        ];

        $fields['conferencing']['options']['google_meet'] = wp_parse_args($updatedData, $defaulData);

        return $fields;
    }

    public function getClientSettingsForView($settings)
    {
        $config = GoogleHelper::getApiConfig();
        $config['redirect_url'] = GoogleHelper::getAppRedirectUrl();

        if (!empty($config['constant_defined'])) {
            $config['client_secret'] = '**********';
            $config['client_id'] = '**********';
        } else if (!empty($config['client_secret'])) {
            $config['client_secret'] = '********************';
        }

        return $config;
    }

    public function getClientFieldSettings($settings)
    {
        $config = GoogleHelper::getApiConfig();

        $description = '<p>' . __('Login to your Google account, go to Google Cloud Console, create a project, complete OAuth Consent screen process, click on Create Credentials, and you will get your client id and secret key. If you get the ID and Keys for Google Calendar, Google Meet will be integrated automatically. For full details read the', 'fluent-booking-pro') . ' <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/google-calendar-meet-integration-with-fluent-booking/">' . __('documentation', 'fluent-booking-pro') . '</a></p>';
        $fields = $this->getStanadrdFields();

        if (!empty($config['constant_defined'])) {
            $description = '<p>' . __('Google Calendar/Meet integration is configured by wp-config.php constants. No action required here', 'fluent-booking-pro') . '</p>';
        }

        return [
            'logo'          => $this->logo,
            'title'         => $this->calendarTitle,
            'subtitle'      => __('Configure Google Calendar/Meet to sync your events', 'fluent-booking-pro'),
            'description'   => $description,
            'save_btn_text' => __('Save Google API Configuration', 'fluent-booking-pro'),
            'fields'        => $fields,
            'will_encrypt'  => true
        ];
    }

    public function saveClientSettings($settings)
    {
        GoogleHelper::updateApiConfig($settings);
    }

    public function pushFeeds($feeds, $userId)
    {
        if (!$this->isConfigured()) {
            return $feeds;
        }

        $items = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->get();

        foreach ($items as $item) {

            CalendarCache::deleteAllParentCache($item->id);

            $errors = '';

            $remoteCalendars = $this->getRemoteCalendarsList($item, true);

            if (is_wp_error($remoteCalendars)) {
                $errors = $remoteCalendars->get_error_message() . ' ' . __('Please remove the connection and reconnect again.', 'fluent-booking-pro');
                $remoteCalendars = [];
            }

            $additionalSettings = $this->getAdditionalSettings($item->value);

            $additionalSettingFields = $this->getAdditionalSettingFields();

            $feeds[] = [
                'driver'                    => 'google',
                'db_id'                     => $item->id,
                'identifier'                => $item->key,
                'remote_calendars'          => $remoteCalendars,
                'errors'                    => $errors,
                'conflict_check_ids'        => Arr::get($item->value, 'conflict_check_ids', []),
                'additional_settings'       => $additionalSettings,
                'additional_setting_fields' => $additionalSettingFields
            ];
        }

        return $feeds;
    }

    public function authDisconnect($meta)
    {
        // Let's remove the cache first
        CalendarCache::deleteAllParentCache($meta->id);
        (new GoogleCalendar($meta))->revoke();
        $meta->delete();
    }

    public function getBookedSlots($books, $calendarSlot, $toTimeZone, $dateRange, $hostId, $isDoingBooking)
    {
        $config = GoogleHelper::getApiConfig();

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return $books;
        }

        $cacheTime = Arr::get($config, 'caching_time', 5);

        $hostIds = $calendarSlot->getHostIds($hostId);

        $items = GoogleHelper::getConflictCheckCalendars($hostIds);

        if (!$items) {
            return $books;
        }

        $start = gmdate('Y-m-d 00:00:00', strtotime($dateRange[0]) - 86400); // just the previous day
        $fromDate = new \DateTime($start, new \DateTimeZone('UTC'));

        $toDate = new \DateTime($dateRange[1], new \DateTimeZone('UTC'));
        $toDate->modify('first day of next month');
        $toDate->setTime(23, 59, 59);

        $startDate = $fromDate->format('Y-m-d\TH:i:s\Z');
        $endDate = $toDate->format('Y-m-d\TH:i:s\Z');
        $cacheKeyPrefix = $toDate->format('YmdHi');

        $allRemoteBookedSlots = [];

        foreach ($items as $item) {
            $meta = $item['item'];
            $cacheKey = md5($cacheKeyPrefix . '_' . $meta->id);
            $calendarApi = new GoogleCalendar($meta);
            if ($calendarApi->lastError) {
                continue;
            }

            $remoteSlots = CalendarCache::getCache($meta->id, $cacheKey, function () use ($calendarApi, $startDate, $endDate, $item) {
                $freeBusy = $calendarApi->getBusyTimes($item['check_ids'], [
                    'timeMin'  => $startDate,
                    'timeMax'  => $endDate,
                    'timeZone' => 'UTC'
                ]);

                if (is_wp_error($freeBusy)) {
                    if ($freeBusy->get_error_code() == 'api_error') {
                        return []; // it's an api error so let's not call again and again
                    }
                    return $freeBusy; //  it's an wp error so we will call again
                }
                return $freeBusy;
            }, $cacheTime);

            if (!is_wp_error($remoteSlots) && $remoteSlots) {
                foreach ($remoteSlots as $slot) {
                    $books[] = [
                        'type'     => 'remote',
                        'start'    => DateTimeHelper::convertFromUtc($slot['start'], $toTimeZone),
                        'end'      => DateTimeHelper::convertFromUtc($slot['end'], $toTimeZone),
                        'source'   => 'google',
                        'event_id' => null,
                        'host_id'  => $meta->object_id
                    ];
                }
            }
        }

        return $books;
    }

    public function createEvent($config, Booking $booking)
    {
        if (!$this->isConfigured() || $booking->status != 'scheduled') {
            return false;
        }

        if ($booking->getMeta('__google_calendar_event') && $booking->isMultiGuestBooking()) {
            return false; // Already created
        }

        $meta = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $booking->host_user_id)
            ->where('id', $config['db_id'])
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        $settings = $meta->value;

        $isValid = false;

        $calendarOwner = [];

        $calendarLists = Arr::get($settings, 'calendar_lists', []);

        foreach ($calendarLists as $item) {
            if ($item['can_write'] != 'yes') {
                continue;
            }

            if ($item['id'] == $config['remote_calendar_id']) {
                $calendarOwner = $item;
                $isValid = true;
            }
        }

        if (!$isValid) {
            return false; // invalid id of the remote calendar
        }

        $api = new GoogleCalendar($meta);

        if ($api->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Google Calendar API. */
                'description' => sprintf(__('Failed to connect with google calendar API. API Response: %s', 'fluent-booking-pro'), $api->lastError->get_error_message())
            ]);
            return false;
        }

        $guestListEnabled = Arr::get($settings, 'additional_settings.guest_list_enabled', 'no') === 'yes';

        $author = $booking->getHostDetails(false);

        $mainHost = [
            'display_name'   => $author['name'],
            'email'          => $author['email'],
            'organizer'      => true,
            'responseStatus' => 'accepted'
        ];

        $mainGuest = [
            'display_name' => trim($booking->first_name . ' ' . $booking->last_name),
            'email'        => $booking->email,
            'comment'      => $booking->message,
            'responseStatus' => 'accepted'
        ];

        $additionalHosts = $booking->getHostEmails($booking->host_user_id);
        $additionalGuests = $booking->getAdditionalGuests();
        $allGuests = array_merge($additionalHosts, $additionalGuests);

        $guestAttendees = array_merge(
            [array_filter($mainGuest)],
            array_map(function ($guest) {
                return [
                    'email' => $guest,
                    'responseStatus' => 'accepted'
                ];
            }, $allGuests ?? [])
        );

        $attendees = $guestAttendees;
        if ($calendarOwner['id'] == $author['email']) {
            $attendees[] = $mainHost;
        }

        $data = [
            'start'              => [
                'dateTime' => gmdate('Y-m-d\TH:i:s\Z', strtotime($booking->start_time))
            ],
            'end'                => [
                'dateTime' => gmdate('Y-m-d\TH:i:s\Z', strtotime($booking->end_time))
            ],
            'attendees'          => $attendees,
            "organizer"          => [
                'display_name' => $calendarOwner['title'],
                'email'        => $calendarOwner['id']
            ],
            'source'             => [
                'title' => $booking->calendar_event->title,
                'url'   => $booking->source_url
            ],
            'location'           => $booking->getLocationAsText(),
            'summary'            => $booking->getBookingTitle(),
            'guestsCanInviteOthers'   => false,
            'guestsCanSeeOtherGuests' => $guestListEnabled ? true : false,
            'extendedProperties' => [
                'shared' => [
                    'created_by' => 'fluent-booking-pro',
                    'site_uid'   => GoogleHelper::getUniqueSiteIdHash(),
                    'event_id'   => $booking->event_id,
                    'booking_id' => $booking->id
                ],
            ],
        ];

        if ($booking->isMultiGuestBooking()) {
            $data['summary'] = $booking->calendar_event->title;
        }
        
        if (!$booking->isMultiGuestBooking()) {
            $data['description'] = $this->getBookingDescription($booking);
        }

        $isGoogleMeet = false;

        if (Arr::get($booking->location_details, 'type') == 'google_meet') {
            $data['conferenceData'] = [
                'createRequest' => [
                    'requestId'             => $booking->hash,
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ]
                ]
            ];
            $isGoogleMeet = true;
        }

        $notificationEnabled = Arr::get($settings, 'additional_settings.notification_enabled', 'no') === 'yes';

        $queryArgs = [
            'sendUpdates' => $notificationEnabled ? 'all' : 'none'
        ];

        $data = apply_filters('fluent_booking/google_event_data', $data, $booking, $queryArgs);

        $response = $api->createEvent($config['remote_calendar_id'], $data, $queryArgs);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Google Calendar API. */
                'description' => sprintf(__('Failed to create event in Google calendar. API Response: %s', 'fluent-booking-pro'), $response->get_error_message())
            ]);
            return false;
        }

        $responseData = [
            'id'                 => $response['id'],
            'remote_link'        => $response['htmlLink'],
            'remote_calendar_id' => $config['remote_calendar_id'],
            'access_db_id'       => $meta->id,
        ];

        if ($isGoogleMeet && !empty($response['hangoutLink'])) {
            $responseData['google_meet_link'] = $response['hangoutLink'];
            $location = $booking->location_details;
            $location['online_platform_link'] = $responseData['google_meet_link'];
            $booking->location_details = $location;
            $booking->save();
        }

        $booking->updateMeta('__google_calendar_event', $responseData);

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Google Calendar event created', 'fluent-booking-pro'),
            /* translators: %s is the error message returned by the Google Calendar API. */
            'description' => sprintf(__('Google calendar event has been created. %s',  'fluent-booking-pro'), '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>')
        ]);

        return true;
    }

    public function cancelEvent($config, Booking $booking)
    {
        return $this->patchEvent($config, $booking, [
            'status' => 'cancelled'
        ], false);
    }

    public function patchEvent($config, Booking $booking, $updateData, $isRescheduling)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $bookingMeta = $booking->getMeta('__google_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to update as there is no previous response of this booking
        }

        $meta = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $booking->host_user_id)
            ->where('id', $config['db_id'])
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        $settings = $meta->value;

        $isValid = false;

        $calendarLists = Arr::get($settings, 'calendar_lists', []);

        foreach ($calendarLists as $item) {
            if ($item['can_write'] != 'yes') {
                continue;
            }

            if ($item['id'] == $config['remote_calendar_id']) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return false; // invalid id of the remote calendar
        }

        $api = new GoogleCalendar($meta);

        if ($api->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Google Calendar API. */
                'description' => sprintf(__('Failed to connect with google calendar API. API Response: %s', 'fluent-booking-pro'), $api->lastError->get_error_message())
            ]);
            return false;
        }

        $googleEventId = Arr::get($bookingMeta, 'id');
        $remoteCalendarId = Arr::get($bookingMeta, 'remote_calendar_id');

        if (!$googleEventId) {
            return false;
        }

        $validKeys = [
            'start',
            'end',
            'status',
            'attendees',
            'email',
            'old_email'
        ];

        $updateData = Arr::only($updateData, $validKeys);

        if (!empty($updateData['start'])) {
            $updateData['start'] = [
                'dateTime' => gmdate('Y-m-d\TH:i:s\Z', strtotime($booking->start_time))
            ];

            $updateData['end'] = [
                'dateTime' => gmdate('Y-m-d\TH:i:s\Z', strtotime($booking->end_time))
            ];
        }

        if (Arr::get($updateData, 'email')) {
            $calendarApi = GoogleHelper::getApiClientByUserId($booking->host_user_id, $config['remote_calendar_id']);
            if (!$calendarApi) {
                return false;
            }
    
            $updatedEvent = $calendarApi->getEvent($remoteCalendarId, $googleEventId);
            
            $attendees = $updatedEvent['attendees'] ?? [];

            $attendees = array_map(function ($attendee) use ($updateData) {
                if ($attendee['email'] == $updateData['old_email']) {
                    $attendee['email'] = $updateData['email'];
                }
                return $attendee;
            }, $attendees);

            $updateData['attendees'] = $attendees;

            $updateData['description'] = $this->getBookingDescription($booking);
        }

        if (!array_filter($updateData)) {
            return false;
        }

        $notificationEnabled = Arr::get($settings, 'additional_settings.notification_enabled', 'yes') != 'no';

        $queryArgs = [
            'sendUpdates' => $notificationEnabled ? 'all' : 'none'
        ];

        $response = $api->patchEvent($config['remote_calendar_id'], $googleEventId, $updateData, $queryArgs);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Google Calendar API. */
                'description' => sprintf(__('Failed to update event in Google calendar. API Response: %s', 'fluent-booking-pro'), $api->lastError->get_error_message())
            ]);
            return false;
        }

        $responseData = [
            'id'                 => $response['id'],
            'remote_link'        => $response['htmlLink'],
            'remote_calendar_id' => $config['remote_calendar_id'],
            'access_db_id'       => $meta->id,
        ];

        $booking->updateMeta('__google_calendar_event', $responseData);

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Google Calendar event updated', 'fluent-booking-pro'),
            /* translators: %s is the error message returned by the Google Calendar API. */
            'description' => sprintf(__('Google calendar event has been updated. %s', 'fluent-booking-pro'), '<a target="_blank" href="' . $response['htmlLink'] . '">' . __('View on Google Calendar', 'fluent-booking-pro') . '</a>')
        ]);

        return true;
    }

    public function maybeAddOrRemoveGroupMembers($config, Booking $booking, $allGroupBookings, $isRescheduling)
    {
        $parentMeta = null;

        $missingEventBookings = [];

        foreach ($allGroupBookings as $parentBooking) {
            $meta = $parentBooking->getMeta('__google_calendar_event', []);
            if (!$meta) {
                $missingEventBookings[] = $parentBooking;
            } else if (!$parentMeta) {
                $parentMeta = $meta;
            }
        }

        if (!$parentMeta || empty($parentMeta['id'])) {
            return $this->createEvent($config, $booking);
        }

        $parentEventId    = $parentMeta['id'];
        $parentCalendarId = Arr::get($parentMeta, 'remote_calendar_id');

        $calendarApi = GoogleHelper::getApiClientByUserId($booking->host_user_id, $config['remote_calendar_id']);
        if (!$calendarApi) {
            return false;
        }

        $updatedEvent = $calendarApi->getEvent($parentCalendarId, $parentEventId);

        if (is_wp_error($updatedEvent)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Google Calendar API. */
                'description' => sprintf(__('Failed to add attendee in Google calendar. API Response: %s', 'fluent-booking-pro'), $updatedEvent->get_error_message())
            ]);
            return false;
        }

        $attendees = $updatedEvent['attendees'] ?? [];

        if ($booking->status == 'scheduled') {
            $attendee = array_filter([
                'display_name'   => trim($booking->first_name . ' ' . $booking->last_name),
                'email'          => $booking->email,
                'comment'        => $booking->message,
                'responseStatus' => 'accepted'
            ]);
            $attendees[] = $attendee;
        } else {
            $attendeeIndex = array_search($booking->email, array_column($attendees, 'email'));
            if ($attendeeIndex !== false) {
                unset($attendees[$attendeeIndex]);
            }
        }

        $response = $calendarApi->patchEvent($parentCalendarId, $parentEventId, [
            'attendees' => array_values($attendees)
        ]);

        if (is_wp_error($response)) {
            if (!$isRescheduling) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Google Calendar API Error', 'fluent-booking-pro'),
                    /* translators: %s is the error message returned by the Google Calendar API. */
                    'description' => sprintf(__('Failed to connect with Google calendar API. API Response: %s', 'fluent-booking-pro'), $response->get_error_message())
                ]);
            }
            return false;
        }

        if (!$isRescheduling) {
            if ($booking->status == 'scheduled') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'info',
                    'title'       => __('Added to Google calendar', 'fluent-booking-pro'),
                    'description' => __('Guest has been added to Google calendar event', 'fluent-booking-pro')
                ]);
            } else if ($booking->status == 'cancelled') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'info',
                    'title'       => __('Removed from Google calendar', 'fluent-booking-pro'),
                    'description' => __('Guest has been removed from Google calendar event', 'fluent-booking-pro')
                ]);
            }
        }

        if (!empty($response['hangoutLink'])) {
            $parentMeta['google_meet_link'] = $response['hangoutLink'];
        }

        if (!empty($response['htmlLink'])) {
            $parentMeta['remote_link'] = $response['htmlLink'];
        }

        foreach ($missingEventBookings as $missingBooking) {
            if (!empty($parentMeta['google_meet_link'])) {
                $location = $booking->location_details;
                $location['online_platform_link'] = $parentMeta['google_meet_link'];
                $booking->location_details = $location;
                $booking->save();
            }

            if ($missingBooking->status != 'cancelled') {
                $missingBooking->updateMeta('__google_calendar_event', $parentMeta);
            }

            return true;
        }
    }

    public function deleteEvent($config, Booking $booking)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $bookingMeta = $booking->getMeta('__google_calendar_event');

        if (!$bookingMeta || !($googleEventId = Arr::get($bookingMeta, 'id'))) {
            return false; // Nothing to update as there is no previous response of this booking
        }

        as_enqueue_async_action('fluent_booking/delete_booking_async_' . $config['driver'], [
            $booking->host_user_id,
            $config['db_id'],
            $config['remote_calendar_id'],
            $googleEventId
        ], 'fluent-booking');
    }

    public function asyncDeleteEvent($hostId, $dbId, $remoteCalendarId, $googleEventId)
    {
        $meta = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $hostId)
            ->where('id', $dbId)
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }
        
        $updateData = [
            'status' => 'cancelled'
        ];

        $api = new GoogleCalendar($meta);

        $api->patchEvent($remoteCalendarId, $googleEventId, $updateData);
    }

    public function getAuthUrl($userId = null)
    {
        if (!$userId) {
            return '';
        }

        return (GoogleHelper::getApiClient())->getAuthUrl($userId);
    }

    public function isConfigured()
    {
        return GoogleHelper::isConfigured();
    }

    /*
     * Internals
     */
    public function handleAuthCallback()
    {
        if (!isset($_GET['code'], $_GET['scope'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $code = sanitize_text_field($_GET['code']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $scope = sanitize_text_field($_GET['scope']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $userId = sanitize_text_field($_GET['state']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $calendar = Calendar::where('user_id', $userId)->where('type', 'simple')->first();

        if (!$calendar || !PermissionManager::hasCalendarAccess($calendar)) {
            return;
        }

        $client = GoogleHelper::getApiClient();

        $response = $client->generateAuthCode($code);

        if (is_wp_error($response)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Failed to connect Calendar API', 'fluent-booking-pro'),
                'body'     => __('Google API Response Error:', 'fluent-booking-pro') . ' ' . $response->get_error_message(),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
            return;
        }

        $requiredScopes = [];

        // Verify the scopes
        $returnedScope = $response['scope'];
        if (!strpos($returnedScope, 'googleapis.com/auth/calendar.events') !== false) {
            $requiredScopes[] = 'https://www.googleapis.com/auth/calendar.events';
        }

        if (!strpos($returnedScope, 'googleapis.com/auth/calendar.readonly') !== false) {
            $requiredScopes[] = 'https://www.googleapis.com/auth/calendar.readonly';
        }

        if ($requiredScopes) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Required scopes missing', 'fluent-booking-pro'),
                'body'     => __('Looks like you did not allow the required scopes. Please try again with the following scopes:', 'fluent-booking-pro') . ' ' . implode(', ', $requiredScopes),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
        }

        $calendarEmail = GoogleHelper::getEmailByIdToken($response['id_token']);

        if (is_wp_error($calendarEmail)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Google API Error', 'fluent-booking-pro'),
                'body'     => __('We could not authenticate your account. Please try again later.', 'fluent-booking-pro'),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
        }

        unset($response['id_token']);
        $response['remote_email'] = $calendarEmail;

        $response['expires_in'] += time();
        $response['access_token'] = Helper::encryptKey($response['access_token']);
        $response['refresh_token'] = Helper::encryptKey($response['refresh_token']);

        $this->addFeedIntegration($userId, $response);

        wp_redirect(Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),);
        exit;
    }

    private function getRemoteCalendarsList($item, $fromApi = false)
    {
        $settings = $item->value;
        if (!empty($settings['calendar_lists']) && !$fromApi) {
            $lastChecked = Arr::get($settings, 'last_calendar_lists_fetched');
            if ($lastChecked && ($lastChecked + 86400) > time()) {
                return $settings['calendar_lists'];
            }
        }

        $calendarClient = new GoogleCalendar($item);

        if ($calendarClient->lastError) {
            return $calendarClient->lastError;
        }

        $remoteCalendars = $calendarClient->getCalendarLists();
        if (is_wp_error($remoteCalendars)) {
            return $remoteCalendars;
        }

        $calendarClient->updateSettinsValueByKey('calendar_lists', $remoteCalendars);
        $calendarClient->updateSettinsValueByKey('last_calendar_lists_fetched', time());

        return $remoteCalendars;
    }

    private function addFeedIntegration($userId, $tokenData)
    {
        $exist = Meta::where('object_type', '_google_user_token')
            ->where('object_id', $userId)
            ->where('key', $tokenData['remote_email'])
            ->first();

        if ($exist) {
            $exist->value = $tokenData;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_google_user_token',
            'object_id'   => $userId,
            'key'         => $tokenData['remote_email'],
            'value'       => $tokenData
        ]);
    }

    private function getBookingDescription($booking)
    {
        $description = $booking->getConfirmationData();

        if ($booking->message) {
            $description .= __('Note: ', 'fluent-booking-pro') . PHP_EOL . $booking->message . PHP_EOL . PHP_EOL;
        }
    
        if ($booking->getAdditionalData(false)) {
            $description .= $booking->getAdditionalData(false);
        }
    
        return $description;
    }

    private function getAdditionalSettings($settings)
    {
        $additionalSettings = Arr::get($settings, 'additional_settings', '');

        if (!$additionalSettings) {
            return [
                'notification_enabled' => 'yes',
                'guest_list_enabled'   => 'no'
            ];
        }

        return $additionalSettings;
    }

    private function getAdditionalSettingFields()
    {
        $fields = [
            'notification_enabled' => [
                'type'           => 'yes_no_checkbox',
                'checkbox_label' => __('Enable Google Calendar Notification', 'fluent-booking-pro'),
            ],
            'guest_list_enabled' => [
                'type'           => 'yes_no_checkbox',
                'checkbox_label' => __('Guests can see other guests of the slot', 'fluent-booking-pro'),
            ]
        ];

        return apply_filters('fluent_booking/google_additional_setting_fields', $fields);
    }
}
