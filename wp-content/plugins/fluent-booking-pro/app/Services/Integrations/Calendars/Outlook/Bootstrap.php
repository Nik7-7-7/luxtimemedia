<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Outlook;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\BaseCalendar;
use FluentBooking\App\Services\Integrations\Calendars\CalendarCache;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Support\Arr;

class Bootstrap extends BaseCalendar
{
    public function __construct()
    {
        $this->calendarKey = 'outlook';
        $app = App::getInstance();
        $this->logo = $app['url.assets'] . 'images/ol-icon-color.svg';
        $this->calendarTitle = __('Outlook Calendar / MS Teams', 'fluent-booking-pro');
    }

    public function register()
    {
        $this->boot();

        add_action('wp_ajax_fluent_booking_outlook_auth', [$this, 'handleAuthCallback']);

        add_action('fluent_booking/before_get_all_calendars', function () {
            if (!OutlookHelper::isConfigured()) {
                return;
            }
            // Show the Outlook last error
            add_action('fluent_booking/calendar', [$this, 'addErrorMessage'], 10, 2);
        });

        add_filter('fluent_booking/get_location_fields', [$this, 'addLocationField'], 10, 2);

        add_action('fluent_booking/delete_booking_async_outlook', [$this, 'asyncDeleteEvent'], 10, 3);
    }

    public function addErrorMessage(&$calendar, $type)
    {
        if ($type != 'lists' || $calendar->type == 'team') {
            return $calendar;
        }

        $meta = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $calendar->user_id)
            ->first();

        if (!$meta || empty(Arr::get($meta->value, 'last_error'))) {
            return $calendar;
        }

        $error = Arr::get($meta->value, 'last_error');
        $calendar->generic_error = '<p style="color: red; margin:0;">' . __('Outlook Calendar API Error:', 'fluent-booking-pro') . ' ' . $error . '. <a href="' . Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars') . '">' . __('Click Here to Review', 'fluent-booking-pro') . '</a></p>';

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
            $outlookQuery = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $hostId);

            $teamsExist = $outlookQuery->first();
            $errorText = !$teamsExist ? __('Connect Outlook First', 'fluent-booking-pro') : '';

            if (!$teamsExist) {
                break;
            }

            if (!$errorText) {
                // now check if the user calendar event create enabled
                $calConfig = RemoteCalendarHelper::getUserRemoteCreatableCalendarSettings($hostId);
                if (!$calConfig || Arr::get($calConfig, 'driver') != 'outlook') {
                    $errorText = __('Set Outlook Event Creat First', 'fluent-booking-pro');
                    $teamsExist = false;
                    break;
                } else if (!empty($configId = Arr::get($calConfig, 'id'))) {
                    $metaId = explode('__||__', $configId)[0];
                    $meta = $outlookQuery->where('id', $metaId)->first();
                    $isEnabled = Arr::get($meta->value, 'additional_settings.teams_enabled', '');
                    if ($isEnabled != 'yes') {
                        $errorText = __('Enable MS Teams From Outlook Settings', 'fluent-booking-pro');
                        $teamsExist = false;
                        break;
                    }
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

        $defaulData = $fields['conferencing']['options']['ms_teams'];

        $updatedData = [
            'title'    => __('MS Teams', 'fluent-booking-pro') . $message,
            'error'    => $errorText ?: false,
            'disabled' => false
        ];

        $fields['conferencing']['options']['ms_teams'] = wp_parse_args($updatedData, $defaulData);

        return $fields;
    }

    public function getClientSettingsForView($settings)
    {
        $config = OutlookHelper::getApiConfig();
        $config['redirect_url'] = OutlookHelper::getAppRedirectUrl();

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
        $fields = $this->getStanadrdFields();

        $description = '<p>' . __('Your outlook API configaration is already. You can connect your outlook calendar from your host settings.', 'fluent-booking-pro') . ' <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/outlook-calendar-integration-with-fluent-booking/">' . __('Read the documentation', 'fluent-booking-pro') . '</a></p>';

        return [
            'logo'          => $this->logo,
            'title'         => $this->calendarTitle,
            'subtitle'      => __('Use Outlook Calendar to sync your Fluent Booking events', 'fluent-booking-pro'),
            'description'   => $description,
            'save_btn_text' => __('Update Caching Time', 'fluent-booking-pro'),
            'fields'        => [
                'caching_time' => $fields['caching_time']
            ],
            'will_encrypt'  => false
        ];
    }

    public function saveClientSettings($settings)
    {
        OutlookHelper::updateApiConfig($settings);
    }

    public function handleAuthCallback()
    {
        if (!isset($_GET['code'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $code = sanitize_text_field($_GET['code']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        $userId = sanitize_text_field($_GET['state']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $calendar = Calendar::where('user_id', $userId)->where('type', 'simple')->first();

        if (!$calendar || !PermissionManager::hasCalendarAccess($calendar)) {
            return;
        }

        $client = OutlookHelper::getApiClient();

        $response = $client->generateAuthCode($code);

        if (is_wp_error($response)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Failed to connect Calendar API', 'fluent-booking-pro'),
                'body'     => __('Outlook API Response Error:', 'fluent-booking-pro') . ' ' . $response->get_error_message(),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
            return;
        }

        $requiredScopes = [];
        // Verify the scopes
        $returnedScope = $response['scope'];
        if (!strpos($returnedScope, 'ndars.ReadWrite')) {
            $requiredScopes[] = 'Calendars.ReadWrite';
        }

        if ($requiredScopes) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Required scopes missing', 'fluent-booking-pro'),
                'body'     => __('Looks like you did not allow the required scopes. Please try again with the following scopes:', 'fluent-booking-pro') . ' ' . implode(', ', $requiredScopes),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
        }
        
        $userEmail = OutlookHelper::getEmailByIdToken($response['id_token']);

        if (is_wp_error($userEmail)) {
            RemoteCalendarHelper::showGeneralError([
                'title'    => __('Failed to connect Calendar API', 'fluent-booking-pro'),
                'body'     => __('Outlook API Response Error:', 'fluent-booking-pro') . ' ' . $userEmail->get_error_message(),
                'btn_url'  => Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),
                'btn_text' => __('Back to Calendars Configuration', 'fluent-booking-pro')
            ]);
        }

        $response['expires_in'] += time();
        $response['access_token'] = Helper::encryptKey($response['access_token']);
        $response['refresh_token'] = Helper::encryptKey($response['refresh_token']);

        $data = Arr::only($response, ['access_token', 'expires_in', 'refresh_token', 'token_type']);
        $data['remote_email'] = $userEmail;
        $this->addFeedIntegration($userId, $data);

        wp_redirect(Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars'),);
        exit;
    }

    public function pushFeeds($feeds, $userId)
    {
        if (!$this->isConfigured()) {
            return $feeds;
        }

        $items = Meta::where('object_type', '_outlook_user_token')
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
                'driver'                    => 'outlook',
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

    public function getBookedSlots($books, $calendarSlot, $toTimeZone, $dateRange, $hostId, $isDoingBooking)
    {
        $config = OutlookHelper::getApiConfig();

        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return $books;
        }

        $hostIds = $calendarSlot->getHostIds($hostId);

        $items = OutlookHelper::getConflictCheckCalendars($hostIds);

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
        $cacheKeyPrefix = $toDate->format('YmdHis');

        $allRemoteBookedSlots = [];


        $cacheTime = Arr::get($config, 'caching_time', 5);
        foreach ($items as $item) {
            $meta = $item['item'];
            $calendarApi = new OutlookCalendar($meta);

            if ($calendarApi->lastError) {
                continue;
            }

            foreach ($item['check_ids'] as $remoteId) {
                $cacheKey = md5($cacheKeyPrefix . '_' . $remoteId);
                $remoteSlots = CalendarCache::getCache($meta->id, $cacheKey, function () use ($calendarApi, $startDate, $endDate, $remoteId) {
                    $events = $calendarApi->getCalendarEvents($remoteId, [
                        'startDateTime' => $startDate,
                        'endDateTime'   => $endDate
                    ]);

                    if (is_wp_error($events)) {
                        if ($events->get_error_code() == 'api_error') {
                            return []; // it's an api error so let's not call again and again
                        }

                        return $events; //  it's an wp error so we will call again
                    }

                    // We have to format it appropriately
                    return $events;
                }, $cacheTime * 60);

                if ($remoteSlots && !is_wp_error($remoteSlots)) {
                    foreach ($remoteSlots as $slot) {
                        $start = RemoteCalendarHelper::convertToTimeZoneOffset($slot['start'], $toTimeZone);
                        $end = RemoteCalendarHelper::convertToTimeZoneOffset($slot['end'], $toTimeZone);
                        $books[] = [
                            'type'     => 'remote',
                            'start'    => $start,
                            'end'      => $end,
                            'source'   => 'outlook',
                            'event_id' => null,
                            'host_id'  => $meta->object_id
                        ];
                    }
                }
            }
        }

        return $books;
    }

    public function createEvent($config, Booking $booking)
    {
        if (($booking->status != 'scheduled' || $booking->getMeta('__outlook_calendar_event') && !$booking->isMultiGuestBooking())) {
            return; // already created
        }

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id, $config['remote_calendar_id']);
        if (!$calendarApi) {
            return;
        }

        $isValid = false;

        $calendarLists = Arr::get($calendarApi->settings, 'calendar_lists', []);
        $remoteCreateId = Arr::get($config, 'remote_calendar_id');

        foreach ($calendarLists as $item) {
            if ($isValid || Arr::get($item, 'can_write') != 'yes') {
                continue;
            }
            if (Arr::get($item, 'id') == $remoteCreateId) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return false; // invalid id of the remote calendar
        }


        if ($calendarApi->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                /* translators: API response message when failed to connect with Outlook calendar API */
                'description' => sprintf(__('Failed to connect with Outlook calendar API. API Response: %s', 'fluent-booking-pro'), $calendarApi->lastError->get_error_message())
            ]);
            return false;
        }
        
        $author = $booking->getHostDetails(false);

        $calendarOwnerEmail = $calendarApi->getMetaModel()->key;
        $calendarOwnerName  = $author['name'];

        if ($user = get_user_by('email', $calendarOwnerEmail)) {
            $calendarOwnerName = trim($user->first_name . ' ' . $user->last_name) ?: $user->display_name;
        }

        $mainGuest = [
            'emailAddress' => array_filter([
                'name'    => trim($booking->first_name . ' ' . $booking->last_name),
                'address' => $booking->email
            ]),
            'type'         => 'required'
        ];

        $additionalHosts = $booking->getHostEmails($booking->host_user_id);
        $additionalGuests = $booking->getAdditionalGuests();
        $allGuests = array_merge($additionalHosts, $additionalGuests);

        $guestAttendees = array_merge(
            [$mainGuest],
            array_map(function ($guest) {
                return [
                    'emailAddress' => ['address' => $guest],
                    'type'         => 'required'
                ];
            }, $allGuests ?? [])
        );

        $data = [
            'start'                 => [
                'dateTime' => gmdate('Y-m-d\TH:i:s', strtotime($booking->start_time)),
                'timeZone' => 'UTC'
            ],
            'end'                   => [
                'dateTime' => gmdate('Y-m-d\TH:i:s', strtotime($booking->end_time)),
                'timeZone' => 'UTC'
            ],
            'attendees'             => $guestAttendees,
            'organizer'             => [
                'emailAddress' => [
                    'name'    => $calendarOwnerName,
                    'address' => $calendarOwnerEmail
                ]
            ],
            'allowNewTimeProposals' => false,
            'location'              => [
                'displayName' => $booking->getLocationAsText(),
            ],
            'subject'               => $booking->getBookingTitle(),
            'transactionId'         => $booking->id,
        ];

        if ($booking->isMultiGuestBooking()) {
            $data['subject'] = $booking->calendar_event->title;
        }

        if (!$booking->isMultiGuestBooking()) {
            $data['body'] = [
                'contentType' => 'text',
                'content'     => $this->getBookingDescription($booking)
            ];
        }

        $isMsTeamMeeting = false;
        if (Arr::get($booking->location_details, 'type') == 'ms_teams') {
            $isMsTeamMeeting = true;
            $data['isOnlineMeeting'] = true;
            $data['onlineMeetingProvider'] = 'teamsForBusiness';
        }

        $data = apply_filters('fluent_booking/outlook_event_data', $data, $booking);
        $response = $calendarApi->createEvent($config['remote_calendar_id'], $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                /* translators: API response message when failed to create event in Outlook calendar */
                'description' => sprintf(__('Failed to create event in Outlook calendar. API Response: %s', 'fluent-booking-pro'), $response->get_error_message())
            ]);
            return false;
        }

        $responseData = [
            'id'                 => $response['id'],
            'remote_link'        => $response['webLink'],
            'remote_calendar_id' => $config['remote_calendar_id'],
            'access_db_id'       => $calendarApi->getMetaModel()->id,
        ];

        if ($isMsTeamMeeting && !empty($response['onlineMeeting']['joinUrl'])) {
            $responseData['ms_team_link'] = $response['onlineMeeting']['joinUrl'];
            $location = $booking->location_details;
            $location['online_platform_link'] = $response['onlineMeeting']['joinUrl'];
            $booking->location_details = $location;
            $booking->save();
        }

        $booking->updateMeta('__outlook_calendar_event', $responseData);

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Outlook Calendar event created', 'fluent-booking-pro'),
            /* translators: Notification message for the creation of an Outlook calendar event. %s is a link to view the event on Outlook Calendar */
            'description' => sprintf(__('Outlook calendar event has been created. %s', 'fluent-booking-pro'), '<a target="_blank" href="' . $response['webLink'] . '">' . __('View on Outlook Calendar', 'fluent-booking-pro') . '</a>'),
        ]);

        return true;
    }

    public function maybeAddOrRemoveGroupMembers($config, $booking, $allGroupBookings, $isRescheduling)
    {
        $parentMeta = null;

        $missingEventBookings = [];

        foreach ($allGroupBookings as $parentBooking) {
            $meta = $parentBooking->getMeta('__outlook_calendar_event', []);
            if (!$meta) {
                $missingEventBookings[] = $parentBooking;
            } else if (!$parentMeta) {
                $parentMeta = $meta;
            }
        }

        if (!$parentMeta || empty($parentMeta['id'])) {
            return $this->createEvent($config, $booking);
        }

        $parentEventId = $parentMeta['id'];
        $attendees = [];

        foreach ($allGroupBookings as $groupBooking) {
            if ($groupBooking->status != 'scheduled') {
                continue;
            }
            $attendees[] = [
                'emailAddress' => [
                    'name'    => trim($groupBooking->first_name . ' ' . $groupBooking->last_name),
                    'address' => $groupBooking->email
                ],
                'type'        => 'required'
            ];
        }

        if (!$attendees) {
            return;
        }

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id, $config['remote_calendar_id']);
        if (!$calendarApi) {
            return false;
        }

        if ($calendarApi->lastError) {
            if (!$isRescheduling) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                    /* translators: API response message when failed to connect with Outlook calendar API */
                    'description' => sprintf(__('Failed to connect with Outlook calendar API. API Response: %s', 'fluent-booking-pro'), $calendarApi->lastError->get_error_message()),
                ]);
            }
            return false;
        }

        $response = $calendarApi->patchEvent($parentEventId, [
            'attendees'     => $attendees,
            'hideAttendees' => true
        ]);

        if (is_wp_error($response)) {
            if (!$isRescheduling) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                    /* translators: API response message when failed to connect with Outlook calendar API */
                    'description' => sprintf(__('Failed to connect with Outlook calendar API. API Response: %s', 'fluent-booking-pro'), $response->get_error_message()),
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
                    'title'       => __('Added to Outlook calendar', 'fluent-booking-pro'),
                    'description' => __('Guest has been added to outlook calendar event', 'fluent-booking-pro')
                ]);
            } else if ($booking->status == 'cancelled') {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'info',
                    'title'       => __('Removed from Outlook calendar', 'fluent-booking-pro'),
                    'description' => __('Guest has been removed from outlook calendar event', 'fluent-booking-pro')
                ]);
            }
        }

        foreach ($missingEventBookings as $missingBooking) {
            if (!empty($parentMeta['onlineMeeting']['joinUrl'])) {
                $location = $missingBooking->location_details;
                if (empty($location['online_platform_link'])) {
                    $location['online_platform_link'] = $parentMeta['onlineMeeting']['joinUrl'];
                    $missingBooking->location_details = $location;
                    $booking->save();
                }
            }

            if ($missingBooking->status != 'cancelled') {
                $missingBooking->updateMeta('__outlook_calendar_event', $parentMeta);
            }
        }

    }

    public function patchEvent($config, Booking $booking, $updateData, $isRescheduling)
    {
        $bookingMeta = $booking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta || empty($bookingMeta['id'])) {
            return $this->createEvent($config, $booking);
        }

        $data = [
            'start' => [
                'dateTime' => gmdate('Y-m-d\TH:i:s', strtotime($booking->start_time)),
                'timeZone' => 'UTC'
            ],
            'end'   => [
                'dateTime' => gmdate('Y-m-d\TH:i:s', strtotime($booking->end_time)),
                'timeZone' => 'UTC'
            ],
        ];

        if (Arr::get($updatedData, 'email')) {
            $data['attendees'] = [
                'emailAddress' => array_filter([
                    'name'    => trim($booking->first_name . ' ' . $booking->last_name),
                    'address' => $booking->email
                ]),
                'type'         => 'required'
            ];
            $data['body'] = [
                'contentType' => 'text',
                'content'     => $this->getBookingDescription($booking)
            ];
        }

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id, $config['remote_calendar_id']);
        if (!$calendarApi) {
            return false;
        }

        if ($calendarApi->lastError) {
            if (!$isRescheduling) {
                do_action('fluent_booking/log_booking_activity', [
                    'booking_id'  => $booking->id,
                    'status'      => 'closed',
                    'type'        => 'error',
                    'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                    /* translators: API response message when failed to connect with Outlook calendar API */
                    'description' => sprintf(__('Failed to connect with Outlook calendar API. API Response: %s', 'fluent-booking-pro'), $calendarApi->lastError->get_error_message()),
                ]);
            }
            return false;
        }

        $response = $calendarApi->patchEvent($bookingMeta['id'], $data);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                /* translators: API response message when failed to connect with Outlook calendar API */
                'description' => sprintf(__('Failed to connect with Outlook calendar API. API Response: %s', 'fluent-booking-pro'), $response->get_error_message()),
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'error',
            'title'       => __('Outlook Event Updated', 'fluent-booking-pro'),
            'description' => __('Event in outlook has been updated with new dates', 'fluent-booking-pro')
        ]);
    }

    public function cancelEvent($config, Booking $booking)
    {
        $bookingMeta = $booking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta) {
            return false; // Nothing to update as there is no previous response of this booking
        }

        $calendarApi = OutlookHelper::getApiClientByUserId($booking->host_user_id, $config['remote_calendar_id']);
        if (!$calendarApi) {
            return;
        }

        if ($calendarApi->lastError) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                /* translators: API response message when failed to connect with Outlook calendar API */
                'description' => sprintf(__('Failed to connect with Outlook calendar API. API Response: %s', 'fluent-booking-pro'), $calendarApi->lastError->get_error_message()),
            ]);
            return false;
        }

        $outlookEventId = Arr::get($bookingMeta, 'id');

        if (!$outlookEventId) {
            return false;
        }

        // Let's cancel the event
        $response = $calendarApi->deleteEvent($outlookEventId);

        if (is_wp_error($response)) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Outlook Calendar API Error', 'fluent-booking-pro'),
                /* translators: API response message when failed to delete event in Outlook calendar */
                'description' => sprintf(__('Failed to delete event in Outlook calendar. API Response: %s', 'fluent-booking-pro'), $response->get_error_message()),
            ]);
            return false;
        }

        do_action('fluent_booking/log_booking_activity', [
            'booking_id'  => $booking->id,
            'status'      => 'closed',
            'type'        => 'success',
            'title'       => __('Outlook event has been deleted', 'fluent-booking-pro'),
            'description' => __('Outlook calendar event has been deleted', 'fluent-booking-pro')
        ]);

        return true;
    }

    public function deleteEvent($config, Booking $booking)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $bookingMeta = $booking->getMeta('__outlook_calendar_event');

        if (!$bookingMeta || !($outlookEventId = Arr::get($bookingMeta, 'id'))) {
            return false; // Nothing to delete as there is no previous response of this booking
        }

        as_enqueue_async_action('fluent_booking/delete_booking_async_' . $config['driver'], [
            $booking->host_user_id,
            $config['remote_calendar_id'],
            $outlookEventId
        ], 'fluent-booking');
    }

    public function asyncDeleteEvent($hostId, $remoteCalendarId, $outlookEventId)
    {
        $calendarApi = OutlookHelper::getApiClientByUserId($hostId, $remoteCalendarId);
        if (!$calendarApi) {
            return;
        }

        // Let's delete the event
        $calendarApi->deleteEvent($outlookEventId);
    }
    
    public function authDisconnect($meta)
    {
        // Let's remove the cache first
        CalendarCache::deleteAllParentCache($meta->id);
        (new OutlookCalendar($meta))->revoke();
        $meta->delete();
    }

    public function getAuthUrl($userId = null)
    {
        if (!$userId) {
            return '';
        }

        return (OutlookHelper::getApiClient())->getAuthUrl($userId);
    }

    public function isConfigured()
    {
        return OutlookHelper::isConfigured();
    }

    /*
     * Internals
     */
    private function addFeedIntegration($userId, $tokenData)
    {
        $exist = Meta::where('object_type', '_outlook_user_token')
            ->where('object_id', $userId)
            ->where('key', $tokenData['remote_email'])
            ->first();

        if ($exist) {
            $exist->value = $tokenData;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_outlook_user_token',
            'object_id'   => $userId,
            'key'         => $tokenData['remote_email'],
            'value'       => $tokenData
        ]);
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

        $calendarClient = new OutlookCalendar($item);

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
                'teams_enabled' => 'no'
            ];
        }

        return $additionalSettings;
    }

    private function getAdditionalSettingFields()
    {
        $fields = [
            'teams_enabled' => [
                'type'           => 'yes_no_checkbox',
                'checkbox_label' => __('Enable Microsoft Teams (Requires work/school account)', 'fluent-booking-pro'),
            ]
        ];

        return apply_filters('fluent_booking/outlook_additional_setting_fields', $fields);
    }
}
