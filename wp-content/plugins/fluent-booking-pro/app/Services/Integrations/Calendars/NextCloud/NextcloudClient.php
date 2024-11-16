<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\NextCloud;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\Package\CalDav\Clients\NextCloud;
use FluentBooking\Package\CalDav\ICal\Event;
use FluentBooking\Package\CalDav\ICal\Event as ICalEvent;

class NextcloudClient
{
    private $client;

    private $baseUrl = '';

    public function __construct($baseUrl, $userName, $password)
    {
        $this->baseUrl = $baseUrl;
        $this->client = new NextCloud([
            'base_url' => $this->baseUrl,
            'username' => $userName,
            'password' => $password
        ]);
    }

    public function getCalendars()
    {
        try {
            $calendars = $this->client->getCalendars();

            $formattedCalendars = [];

            foreach ($calendars as $calendar) {
                $formattedCalendars[] = [
                    'id'        => $calendar->href,
                    'title'     => $calendar->displayname,
                    'can_write' => 'yes',
                    'getctag'   => $calendar->getctag
                ];
            }

            return $formattedCalendars;

        } catch (\Exception $e) {
            return new \WP_Error($e->getCode(), $e->getMessage());
        }
    }

    public function getEvents($calendarId, $args)
    {
        return $this->client->getEvents($calendarId, $args);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function createEvent($calendarId, $data = [])
    {

        $event = new Event();

        $values = array_filter(Arr::only($data, [
            'dtstart', 'dtend', 'status', 'summary', 'description', 'location'
        ]));

        if (empty($values['status'])) {
            $values['status'] = 'confirmed';
        }

        foreach ($values as $key => $value) {
            $event->{$key} = $value;
        }

        try {

            return $this->client->addEvent($calendarId, $event);

        } catch (\Exception $e) {
            return new \WP_Error($e->getCode(), $e->getMessage());
        }
    }

}
