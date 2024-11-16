<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Google;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class GoogleCalendar
{

    public $lastError = null;

    private $metaModel;

    public function __construct(Meta $meta)
    {
        $this->metaModel = $meta;
        $this->normalizeUserAccessMeta();
    }

    public function getMetaModel()
    {
        return $this->metaModel;
    }

    public function getCalendarLists()
    {
        if ($this->lastError) {
            return $this->lastError;
        }

        return ($this->getAccessClient())->getCalendarLists();
    }

    public function getBusyTimes($ids, $args = [])
    {
        $holidayCalId = '';

        $args['items'] = [];

        foreach ($ids as $id) {
            $args['items'][] = ['id' => $id];
            if (!$holidayCalId && strpos($id, 'holiday@group') !== false) {
                $holidayCalId = $id;
            }
        }

        $result = ($this->getAccessClient())->getFreeBusy($args);

        if (is_wp_error($result)) {
            return $result;
        }

        $calendars = Arr::get($result, 'calendars', []);

        $holidayCalendar = !empty($calendars[$holidayCalId]) ? $calendars[$holidayCalId] : [];

        if (Arr::has($holidayCalendar, 'errors')) {
            $eventArgs = Arr::only($args, ['timeMin', 'timeMax', 'timeZone']);
            $holidayEvents = $this->getCalendarEvents(urlencode($holidayCalId), $eventArgs);
            if (!is_wp_error($holidayEvents)) {
                $calendars[$holidayCalId] = ['busy' => $holidayEvents];
            }
        }

        $busyTimes = [];
        foreach ($calendars as $calendarId => $calendar) {

            if (!Arr::has($calendar, 'busy') || Arr::has($calendar, 'errors')) {
                continue;
            }

            $items = Arr::get($calendar, 'busy', []);
            $busyTimes = array_merge($busyTimes, $items);
        }

        return $busyTimes;
    }

    public function getCalendarEvents($calendarId, $args = [])
    {
        $defaults = [
            'maxResults' => 2000,
            'timeZone'   => 'UTC'
        ];

        $args = array_merge($defaults, $args);

        if ($this->lastError) {
            return $this->lastError;
        }

        return ($this->getAccessClient())->getCalendarEvents($calendarId, $args);
    }

    private function getAccessClient()
    {
        return (GoogleHelper::getApiClient($this->getAccessToken()));
    }

    public function getAccessToken()
    {
        $settings = $this->metaModel->value;
        return Helper::decryptKey($settings['access_token']);
    }

    private function normalizeUserAccessMeta()
    {
        $metaModel = $this->metaModel;
        $settings = $metaModel->value;

        if (Arr::get($settings, 'expires_in', 0) - 10 <= time()) {
            $settings['refresh_token'] = Helper::decryptKey(Arr::get($settings, 'refresh_token'));

            $newTokens = (GoogleHelper::getApiClient())->reGenerateToken($settings['refresh_token']);

            if (is_wp_error($newTokens)) {
                $settings['refresh_token'] = Helper::encryptKey($settings['refresh_token']);
                $settings['last_error'] = $newTokens->get_error_message();
                $metaModel->value = $settings;
                $metaModel->save();

                $this->lastError = $newTokens;
                return;
            }

            Helper::debugLog(['google_calendar' => __('Access Token Refreshed', 'fluent-booking-pro')]);

            $settings['access_token'] = Helper::encryptKey($newTokens['access_token']);
            if (!empty($newTokens['refresh_token'])) {
                $settings['refresh_token'] = Helper::encryptKey($newTokens['refresh_token']);
            } else {
                $settings['refresh_token'] = Helper::encryptKey($settings['refresh_token']);
            }

            $settings['expires_in'] = $newTokens['expires_in'];
            $settings['last_error'] = '';
            $metaModel->value = $settings;
            $metaModel->save();
            $this->metaModel = $metaModel;
        }
    }

    public function updateSettinsValueByKey($key, $value)
    {
        if ($key == 'access_token') {
            $value = Helper::encryptKey($value);
        }

        $metaModel = $this->metaModel;
        $settings = $metaModel->value;
        $settings[$key] = $value;
        $metaModel->value = $settings;
        $metaModel->save();
        $this->metaModel = $metaModel;
        return $this;
    }

    public function createEvent($calendarId, $eventData, $queryArgs = [])
    {
        $argsDefaults = [
            'sendUpdates' => 'all'
        ];

        $queryArgs = wp_parse_args($queryArgs, $argsDefaults);

        if (empty($eventData['start']) || empty($eventData['end'])) {
            return new \WP_Error('invalid_data', __('start and end data is required', 'fluent-booking-pro'));
        }

        return ($this->getAccessClient())->createEvent($calendarId, $eventData, $queryArgs);
    }

    public function patchEvent($calendarId, $eventId, $eventData, $queryArgs = [])
    {
        $argsDefaults = [
            'sendUpdates' => 'all'
        ];

        $queryArgs = wp_parse_args($queryArgs, $argsDefaults);

        return ($this->getAccessClient())->patchEvent($calendarId, $eventId, $eventData, $queryArgs);
    }

    public function getEvent($calendarId, $eventId, $queryArgs = [])
    {
        return ($this->getAccessClient())->getEvent($calendarId, $eventId, $queryArgs);
    }

    public function revoke()
    {
        if ($this->lastError) {
            return $this->lastError;
        }
        return ($this->getAccessClient())->revokeConnection();
    }
}
