<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\Outlook;

use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\Framework\Support\Arr;

class OutlookCalendar
{
    public $lastError = null;

    private $metaModel;

    public $settings;

    public function __construct(Meta $meta)
    {
        $this->settings = $meta->value;
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

    public function getCalendarEvents($calendarId, $args = [])
    {
        $defaults = [
            'maxResults'    => 2000,
            'startDateTime' => gmdate('Y-m-d') . 'T00:00:00Z',
            'endDateTime'   => gmdate('Y-m-d', strtotime('+1 month')) . 'T00:00:00Z'
        ];

        $args = array_merge($defaults, $args);

        if ($this->lastError) {
            return $this->lastError;
        }

        return ($this->getAccessClient())->getCalendarEvents($calendarId, $args);
    }

    private function getAccessClient()
    {
        return (OutlookHelper::getApiClient($this->getAccessToken()));
    }

    public function getAccessToken()
    {
        $settings = $this->metaModel->value;
        return Helper::decryptKey($settings['access_token']);
    }

    public function deleteEvent($eventId)
    {
        return ($this->getAccessClient())->deleteEvent($eventId);
    }

    private function normalizeUserAccessMeta()
    {
        $metaModel = $this->metaModel;
        $settings = $this->settings;

        if (Arr::get($settings, 'expires_in', 0) - 10 <= time()) {
            $settings['refresh_token'] = Helper::decryptKey(Arr::get($settings, 'refresh_token'));

            $newTokens = (OutlookHelper::getApiClient())->reGenerateToken($settings['refresh_token']);

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
        if (empty($eventData['start']) || empty($eventData['end'])) {
            return new \WP_Error('invalid_data', __('start and end data is required', 'fluent-booking-pro'));
        }

        return ($this->getAccessClient())->createEvent($calendarId, $eventData, $queryArgs);
    }

    public function patchEvent($eventId, $eventData, $queryArgs = [])
    {
        return ($this->getAccessClient())->patchEvent($eventId, $eventData, $queryArgs);
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
