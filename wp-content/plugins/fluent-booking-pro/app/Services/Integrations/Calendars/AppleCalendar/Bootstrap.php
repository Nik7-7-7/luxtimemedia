<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\AppleCalendar;

use FluentBooking\App\App;
use FluentBooking\App\Models\Booking;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\DateTimeHelper;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\BaseCalendar;
use FluentBooking\App\Services\Integrations\Calendars\CalendarCache;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\Framework\Support\DateTime;
use FluentBooking\Package\CalDav\Entities\Calendar;

class Bootstrap extends BaseCalendar
{
    public function register()
    {
        $app = App::getInstance();

        $this->calendarKey = 'apple_calendar';
        $this->calendarTitle = __('Apple Calendar', 'fluent-booking-pro');
        $this->logo = $app['url.assets'] . 'images/a-cal.svg';
        $this->boot();

        add_action('fluent_booking/before_get_all_calendars', function () {
            if (!$this->isConfigured()) {
                return;
            }
            // Show the Google last error
            add_action('fluent_booking/calendar', [$this, 'addErrorMessage'], 10, 2);
        });

        add_filter('fluent_booking/verify_save_caldav_credential_' . $this->calendarKey, [$this, 'saveUserCredentials'], 10, 3);

        add_action('fluent_booking/delete_booking_async_apple_calendar', [$this, 'asyncDeleteEvent'], 10, 4);
    }

    public function addErrorMessage(&$calendar, $type)
    {
        if ($type != 'lists' || $calendar->type == 'team') {
            return $calendar;
        }

        $metas = Meta::where('object_type', '_apple_calendar_user_token')
            ->where('object_id', $calendar->user_id)
            ->get();

        foreach ($metas as $meta) {
            if (empty(Arr::get($meta->value, 'last_error'))) {
                continue;
            }

            $error = Arr::get($meta->value, 'last_error');
            $calendar->generic_error = '<p style="color: red; margin:0;">' . __('Apple Calendar API Error:', 'fluent-booking-pro') . ' ' . $error . '. <a href="' . Helper::getAppBaseUrl('calendars/' . $calendar->id . '/settings/remote-calendars') . '">' . __('Click Here to Review', 'fluent-booking-pro') . '</a></p>';
        }

        return $calendar;
    }

    public function getClientSettingsForView($settings)
    {
        return AppleHelper::getApiConfig();
    }

    public function getClientFieldSettings($settings)
    {
        $description = '<p>' . __('To use Apple Calendar Integration for your Booking forms, please enable the integration.', 'fluent-booking-pro') . ' <a target="_blank" rel="noopener" href="https://fluentbooking.com/docs/apple-calendar-integration-with-fluent-booking/">' . __('Read the documentation', 'fluent-booking-pro') . '</a></p>';

        $fields = [
            'is_enabled' => [
                'type'           => 'yes_no_checkbox',
                'label'          => __('Status', 'fluent-booking-pro'),
                'checkbox_label' => __('Enable Apple Calendar Integration', 'fluent-booking-pro'),
            ],
            'caching_time'  => [
                'type'        => 'select',
                'options'     => [
                    '1'  => __('1 minute', 'fluent-booking-pro'),
                    '5'  => __('5 minutes', 'fluent-booking-pro'),
                    '10' => __('10 minutes', 'fluent-booking-pro'),
                    '15' => __('15 minutes', 'fluent-booking-pro'),
                ],
                'label'       => __('Caching Time', 'fluent-booking-pro'),
                /* translators: Explanation for the cache duration setting. %1$s is the calendar title, %2$s is the calendar title repeated. */
                'inline_help' => sprintf(__('Select for how many minutes the %1$s event API call will be cached. Recommended 5/10 minutes. If you add lots of manual events in %2$s then you may lower the value',  'fluent-booking-pro'), $this->calendarTitle, $this->calendarTitle)
            ],
        ];

        return [
            'logo'          => $this->logo,
            'title'         => $this->calendarTitle,
            'subtitle'      => __('Enable/Disable Apple Calendar to sync your events', 'fluent-booking-pro'),
            'description'   => $description,
            'save_btn_text' => __('Save Settings', 'fluent-booking-pro'),
            'fields'        => $fields,
            'will_encrypt'  => false
        ];
    }

    public function addAsProvider($providers, $userId)
    {
        $providers[$this->calendarKey] = [
            'key'                  => $this->calendarKey,
            'icon'                 => $this->logo,
            'is_caldav'            => true,
            'caldav_settings'      => [
                'heading'        => __('Connect to Apple Server', 'fluent-booking-pro'),
                /* translators: Instruction for connecting to Apple Server. %s is a link to manage Apple ID */
                'description'    => sprintf(__('To connect to Apple Server, please enter your Apple Email and app specific password. Generate App Specific Password at %s Your credentials will be stored as encrypted.', 'fluent-booking-pro'), '<a target="_blank" rel="noopener" href="https://appleid.apple.com/account/manage">https://appleid.apple.com/account/manage</a>'),
                'username_label' => __('Apple ID (Email)', 'fluent-booking-pro'),
                'password_label' => __('App Specific Password', 'fluent-booking-pro'),
                'button_text'    => __('Connect with Apple Calendar', 'fluent-booking-pro')
            ],
            'title'                => $this->calendarTitle,
            /* translators: %s is the name of the calendar title. */
            'subtitle'             => sprintf(__('Configure %s to sync your events', 'fluent-booking-pro'), $this->calendarTitle),
            /* translators: %s is the name of the calendar title. */
            'btn_text'             => sprintf(__('Connect with %s', 'fluent-booking-pro'), $this->calendarTitle),
            'auth_url'             => $this->getAuthUrl($userId),
            'is_global_configured' => $this->isConfigured(),
            'global_config_url'    => admin_url('admin.php?page=fluent-booking#/settings/configure-integrations/' . $this->calendarKey),
        ];

        return $providers;
    }

    public function saveClientSettings($settings)
    {
        AppleHelper::updateConfig($settings);
    }

    public function pushFeeds($feeds, $userId)
    {
        if (!$this->isConfigured()) {
            return $feeds;
        }

        $items = Meta::where('object_type', '_apple_calendar_user_token')
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

            $feeds[] = [
                'driver'             => $this->calendarKey,
                'db_id'              => $item->id,
                'identifier'         => $item->key,
                'remote_calendars'   => $remoteCalendars,
                'errors'             => $errors,
                'conflict_check_ids' => Arr::get($item->value, 'conflict_check_ids', [])
            ];
        }

        return $feeds;
    }

    public function authDisconnect($meta)
    {
        // Let's remove the cache first
        CalendarCache::deleteAllParentCache($meta->id);
        $meta->delete();
    }

    public function getBookedSlots($books, $calendarSlot, $toTimeZone, $dateRange, $hostId, $isDoingBooking)
    {
        if (!$this->isConfigured()) {
            return $books;
        }
        
        $hostIds = $calendarSlot->getHostIds($hostId);

        $conflictItems = $this->getConflictCheckCalendars($hostIds);

        if (!$conflictItems) {
            return $books;
        }

        $start = gmdate('Y-m-d 00:00:00', strtotime($dateRange[0]) - 86400); // just the previous day
        $fromDate = new \DateTime($start, new \DateTimeZone('UTC'));

        $toDate = new \DateTime($dateRange[1], new \DateTimeZone('UTC'));
        $toDate->modify('first day of next month');
        $toDate->setTime(23, 59, 59);

        $dateRange = [
            $fromDate->format('Y-m-d H:i:s'),
            $toDate->format('Y-m-d H:i:s')
        ];

        $cacheKeyPrefix = $toDate->format('YmdHis');

        $config = AppleHelper::getApiConfig();
        $cacheTime = Arr::get($config, 'caching_time', 5);

        $remoteBooks = [];
        foreach ($conflictItems as $item) {
            $meta = $item['item'];
            $userName = Arr::get($meta->value, 'remote_email');

            $password = Helper::decryptKey(Arr::get($meta->value, 'remote_pass'));

            $client = new IcloudClient($userName, $password);

            $remoteCalendarIds = Arr::get($item, 'check_ids', []);
            $timeZone = null;

            foreach ($remoteCalendarIds as $calendarId) {
                $cacheKey = md5($cacheKeyPrefix . '_' . $calendarId . '_' . $toTimeZone . '_' . $this->calendarKey);
                $events = CalendarCache::getCache($meta->id, $cacheKey, function () use ($client, $calendarId, $dateRange) {
                    try {
                        return $client->getEvents($calendarId, [
                            new \DateTime($dateRange[0]),
                            new \DateTime($dateRange[1])
                        ]);
                    } catch (\Exception $exception) {
                        return new \WP_Error($exception->getCode(), $exception->getMessage());
                    }
                }, $cacheTime * 60);

                $missedSlots = [];

                $bookedEvents = Arr::get($events, 'events', []);
                foreach ($bookedEvents as $event) {
                    if ($event->timezone) {
                        $timeZone = $event->timezone;
                    }

                    if (!empty($event->rrule)) {
                        $recurringDates = RemoteCalendarHelper::getRruleDates([
                            'RRULE:' . $event->rrule
                        ], [
                            $event->dtstart,
                            $event->dtend
                        ], $dateRange[0], $dateRange[1], [
                            'type'     => 'remote',
                            'source'   => 'apple_calendar',
                            'event_id' => null,
                            'host_id'  => $meta->object_id
                        ], $timeZone);

                        if ($recurringDates) {
                            if ($toTimeZone != $timeZone) {
                                foreach ($recurringDates as $recurringDate) {
                                    $recurringDate['start'] = DateTimeHelper::convertToTimeZone($recurringDate['start'], $timeZone, $toTimeZone);
                                    $recurringDate['end'] = DateTimeHelper::convertToTimeZone($recurringDate['end'], $timeZone, $toTimeZone);
                                    $books[] = $recurringDate;
                                }
                            } else {
                                $remoteBooks = array_merge($remoteBooks, $recurringDates);
                            }
                        }

                        continue;
                    }

                    // check if it's UTC Already
                    if (strpos($event->dtstart, 'Z')) {
                        $remoteBooks[] = [
                            'type'     => 'remote',
                            'start'    => DateTimeHelper::convertFromUtc(gmdate('Y-m-d H:i:s', strtotime($event->dtstart)), $toTimeZone),
                            'end'      => DateTimeHelper::convertFromUtc(gmdate('Y-m-d H:i:s', strtotime($event->dtend)), $toTimeZone),
                            'source'   => 'apple_calendar',
                            'event_id' => null,
                            'host_id'  => $meta->object_id
                        ];
                        continue;
                    }

                    $event->dtstart = gmdate('Y-m-d H:i:s', strtotime($event->dtstart));
                    $event->dtend = gmdate('Y-m-d H:i:s', strtotime($event->dtend));

                    $eventData = [
                        'type'     => 'remote',
                        'start'    => $event->dtstart,
                        'end'      => $event->dtend,
                        'source'   => 'apple_calendar',
                        'event_id' => null,
                        'host_id'  => $meta->object_id
                    ];

                    if ($timeZone) {
                        $eventData['start'] = DateTimeHelper::convertToTimeZone($eventData['start'], $timeZone, $toTimeZone);
                        $eventData['end'] = DateTimeHelper::convertToTimeZone($eventData['end'], $timeZone, $toTimeZone);
                        $remoteBooks[] = $eventData;
                    } else {
                        $missedSlots[] = $eventData;
                    }
                }
                if ($missedSlots) {
                    if ($timeZone) {
                        foreach ($missedSlots as $missedSlot) {
                            $missedSlot['start'] = DateTimeHelper::convertToTimeZone($missedSlot['start'], $timeZone, $toTimeZone);
                            $missedSlot['end'] = DateTimeHelper::convertToTimeZone($missedSlot['end'], $timeZone, $toTimeZone);
                            $remoteBooks[] = $missedSlot;
                        }
                    } else {
                        $remoteBooks = array_merge($remoteBooks, $missedSlots);
                    }
                }
            }
        }

        if ($remoteBooks) {
            $books = array_merge($books, $remoteBooks);
        }

        return $books;
    }

    public function createEvent($config, Booking $booking)
    {
        if (!$this->isConfigured() || $booking->status != 'scheduled') {
            return false;
        }

        if ($booking->getMeta('__apple_calendar_event') && !$booking->isMultiGuestBooking()) {
            return false; // Already created
        }

        $client = $this->getClientFromBookingConfig($config, $booking);

        if (!$client) {
            return false;
        }

        $data = $this->prepareEventData($config, $booking);

        try {
            $apiCalendar = new Calendar([
                'href' => $config['remote_calendar_id']
            ], $client->getClient());

            $event = $apiCalendar->createEvent();
            foreach ($data as $key => $datum) {
                $event->{$key} = $datum;
            }

            $event->save();

            $booking->updateMeta('__apple_calendar_event', [
                'remote_event_id' => $event->uid,
                'remote_calendar' => $config['remote_calendar_id']
            ]);

            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'success',
                'title'       => __('Apple Calendar event created', 'fluent-booking-pro'),
                /* translators: %s is the event id of apple calendar */
                'description' => sprintf(__('Aplle calendar event has been created. EventID: %s', 'fluent-booking-pro'), $event->uid)
            ]);

        } catch (\Exception $exception) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Apple Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Apple Calendar API. */
                'description' => sprintf(__('Failed to create event in Apple calendar. API Response: %s', 'fluent-booking-pro'), $exception->getMessage())
            ]);
        }
    }

    public function cancelEvent($config, Booking $booking)
    {
        if (!$this->isConfigured() || $booking->status != 'cancelled') {
            return false;
        }

        $appleEvent = $booking->getMeta('__apple_calendar_event');

        if (!$appleEvent) {
            return false;
        }

        $client = $this->getClientFromBookingConfig($config, $booking);

        if (!$client) {
            return false;
        }

        try {

            $apiCalendar = new Calendar([
                'href' => Arr::get($appleEvent, 'remote_calendar')
            ], $client->getClient());

            $apiCalendar->deleteEvent(Arr::get($appleEvent, 'remote_event_id'));

            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'success',
                'title'       => __('Apple event has been deleted', 'fluent-booking-pro'),
                'description' => __('Apple calendar event has been deleted', 'fluent-booking-pro')
            ]);
        } catch (\Exception $exception) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Apple Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Apple Calendar API. */
                'description' => sprintf(__('Failed to delete event in Apple calendar. API Response: %s', 'fluent-booking-pro'), $exception->getMessage())
            ]);
            return false;
        }
    }

    public function patchEvent($config, Booking $booking, $updateData, $isRescheduling)
    {
        $appleEvent = $booking->getMeta('__apple_calendar_event');

        if (!$appleEvent) {
            return false;
        }
        $client = $this->getClientFromBookingConfig($config, $booking);

        if (!$client) {
            return false;
        }

        try {
            $apiCalendar = new Calendar([
                'href' => Arr::get($appleEvent, 'remote_calendar')
            ], $client->getClient());

            $apiEvent = $apiCalendar->getEvent(Arr::get($appleEvent, 'remote_event_id'));

            $eventData = $this->prepareEventData($config, $booking);

            if (Arr::get($updateData, 'email')) {
                $eventData['attendees'] = [
                    [
                        'name'     => trim($booking->first_name . ' ' . $booking->last_name),
                        'email'    => $booking->email,
                        'rsvp'     => true,
                        'partstat' => 'accepted'
                    ]
                ];

                $eventData['description'] = $booking->getIcsBookingDescription();
            }

            foreach ($eventData as $key => $datum) {
                $apiEvent->{$key} = $datum;
            }
            $apiEvent->save();

            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'success',
                'title'       => __('Apple event has been updated', 'fluent-booking-pro'),
                'description' => __('Apple calendar event has been updated', 'fluent-booking-pro')
            ]);
        } catch (\Exception $exception) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Apple Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Apple Calendar API. */
                'description' => sprintf(__('Failed to update event in Apple calendar. API Response: %s', 'fluent-booking-pro'), $exception->getMessage())
            ]);
            return false;
        }

        return true;
    }

    public function maybeAddOrRemoveGroupMembers($config, Booking $booking, $allGroupBookings, $isRescheduling)
    {
        $parentMeta = $parentBooking = null;

        $missingEventBookings = [];

        foreach ($allGroupBookings as $groupBooking) {
            $meta = $groupBooking->getMeta('__apple_calendar_event', []);
            if (!$meta) {
                $missingEventBookings[] = $groupBooking;
            } else if (!$parentMeta) {
                $parentMeta = $meta;
                $parentBooking = $groupBooking;
            }
        }

        if (!$parentMeta || empty($parentMeta['remote_event_id'])) {
            return $this->createEvent($config, $booking);
        }

        $attendees = [];

        foreach ($allGroupBookings as $groupBooking) {
            if ($groupBooking->status != 'scheduled') {
                continue;
            }
            $attendees[] = [
                'name'     => trim($groupBooking->first_name . ' ' . $groupBooking->last_name),
                'email'    => $groupBooking->email,
                'rsvp'     => true,
                'partstat' => 'accepted'
            ];
        }

        if (!$attendees) {
            return;
        }

        $parentEventId = $parentMeta['remote_event_id'];
        $parentCalendarId = $parentMeta['remote_calendar'];

        $client = $this->getClientFromBookingConfig($config, $booking);

        if (!$client) {
            return false;
        }

        try {
            $apiCalendar = new Calendar(['href' => $parentCalendarId], $client->getClient());
            $apiEvent = $apiCalendar->getEvent($parentEventId);
            $eventData = $this->prepareEventData($config, $parentBooking);
            $eventData['attendees'] = $attendees;
            $eventData['description'] = __('This is a group event.', 'fluent-booking-pro');
            foreach ($eventData as $key => $datum) {
                $apiEvent->{$key} = $datum;
            }
            $apiEvent->save();
        } catch (\Exception $exception) {
            do_action('fluent_booking/log_booking_activity', [
                'booking_id'  => $booking->id,
                'status'      => 'closed',
                'type'        => 'error',
                'title'       => __('Apple Calendar API Error', 'fluent-booking-pro'),
                /* translators: %s is the error message returned by the Apple Calendar API. */
                'description' => sprintf(__('Failed to update event in Apple calendar. API Response: %s', 'fluent-booking-pro'), $exception->getMessage())
            ]);
        }

        foreach ($missingEventBookings as $missingBooking) {
            if ($missingBooking->status != 'cancelled') {
                $missingBooking->updateMeta('__apple_calendar_event', $parentMeta);
            }
        }
    }

    public function deleteEvent($config, Booking $booking)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $appleEvent = $booking->getMeta('__apple_calendar_event');

        if (!$appleEvent || !($appleEventId = Arr::get($appleEvent, 'remote_event_id'))) {
            return false;
        }

        $remoteCalendar = Arr::get($appleEvent, 'remote_calendar');

        as_enqueue_async_action('fluent_booking/delete_booking_async_' . $config['driver'], [
            $booking->host_user_id,
            $config['db_id'],
            $remoteCalendar,
            $appleEventId
        ], 'fluent-booking');
    }

    public function asyncDeleteEvent($hostId, $dbId, $remoteCalendar, $appleEventId)
    {
        $meta = Meta::where('object_type', '_apple_calendar_user_token')
            ->where('object_id', $hostId)
            ->where('id', $dbId)
            ->first();

        if (!$meta) {
            return false;
        }

        $client = AppleHelper::getClientByMeta($meta);

        if (!$client) {
            return false;
        }

        $apiCalendar = new Calendar([
            'href' => $remoteCalendar
        ], $client->getClient());

        $apiCalendar->deleteEvent($appleEventId);
    }

    public function getAuthUrl($userId = null)
    {
        return '';
    }

    public function isConfigured()
    {
        $config = AppleHelper::getApiConfig();
        return $config['is_enabled'] == 'yes';
    }


    private function getRemoteCalendarsList($item, $fromApi = false)
    {
        return Arr::get($item->value, 'calendar_lists', []);
    }

    private function addFeedIntegration($userId, $tokenData)
    {
        $exist = Meta::where('object_type', '_apple_calendar_user_token')
            ->where('object_id', $userId)
            ->where('key', $tokenData['remote_email'])
            ->first();

        if ($exist) {
            $exist->value = $tokenData;
            $exist->save();
            return $exist;
        }

        return Meta::create([
            'object_type' => '_apple_calendar_user_token',
            'object_id'   => $userId,
            'key'         => $tokenData['remote_email'],
            'value'       => $tokenData
        ]);
    }

    public function saveUserCredentials($response, $data, $userId)
    {
        $userEmail = Arr::get($data, 'username');
        if (!is_email($userEmail)) {
            $response['message'] = 'Please enter a valid email address';
            return $response;
        }

        $password = Arr::get($data, 'password');
        if (!$password) {
            $response['message'] = 'Please enter a valid password';
            return $response;
        }

        // Let's validate the CalDav credential

        $icloud = new IcloudClient($userEmail, $password);

        $calendars = $icloud->getCalendars();

        if (is_wp_error($calendars)) {
            $response['message'] = $calendars->get_error_message();
            return $response;
        }

        $tokenData = [
            'remote_email'                => $userEmail,
            'remote_pass'                 => Helper::encryptKey($password),
            'calendar_lists'              => $calendars,
            'last_calendar_lists_fetched' => time()
        ];

        $this->addFeedIntegration($userId, $tokenData);

        $response['success'] = true;
        $response['message'] = __('Your credential has been saved', 'fluent-booking-pro');

        return $response;
    }

    private function prepareEventData($config, Booking $booking)
    {
        $meta = Meta::where('object_type', '_apple_calendar_user_token')
            ->where('object_id', $booking->host_user_id)
            ->where('id', $config['db_id'])
            ->first();

        if (!$meta) {
            return;
        }

        $host = $booking->getHostDetails(false);

        $calendarOwnerEmail = $meta->key;
        $calendarOwnerName  = $host['name'];

        if ($user = get_user_by('email', $calendarOwnerEmail)) {
            $calendarOwnerName = trim($user->first_name . ' ' . $user->last_name) ?: $user->display_name;
        }

        $mainGuest = [
            'email'    => $booking->email,
            'name'     => trim($booking->first_name . ' ' . $booking->last_name),
            'rsvp'     => false,
            'partstat' => 'accepted'
        ];

        $additionalHosts = $booking->getHostEmails($booking->host_user_id);
        $additionalGuests = $booking->getAdditionalGuests();
        $allGuests = array_merge($additionalHosts, $additionalGuests);
        
        $attendees = array_merge(
            [$mainGuest],
            array_map(function ($guest) {
                return ['email' => $guest];
            }, $allGuests ?? [])
        );

        $data = [
            'dtstart'     => gmdate('Y-m-d\TH:i:s\Z', strtotime($booking->start_time)),
            'dtend'       => gmdate('Y-m-d\TH:i:s\Z', strtotime($booking->end_time)),
            'status'      => 'confirmed',
            'summary'     => $booking->getBookingTitle(),
            'location'    => $booking->getLocationAsText(),
            'description' => $booking->getIcsBookingDescription(),
            'attendees'   => $attendees,
            'organizer'   => [
                'email'   => $calendarOwnerEmail,
                'name'    => $calendarOwnerName
            ]
        ];

        if ($booking->isMultiGuestBooking()) {
            $data['summary'] = $booking->calendar_event->title;
        }

        return $data;
    }

    private function getClientFromBookingConfig($config, $booking)
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $meta = Meta::where('object_type', '_apple_calendar_user_token')
            ->where('object_id', $booking->host_user_id)
            ->where('id', $config['db_id'])
            ->first();

        if (!$meta) {
            return false; //  Meta could not be found
        }

        return AppleHelper::getClientByMeta($meta);
    }
}
