<?php

namespace FluentBookingPro\App\Services\Integrations\Calendars\AppleCalendar;

use FluentBooking\Framework\Support\Arr;
use FluentBooking\Package\CalDav\Clients\ICloud;
use FluentBooking\Package\CalDav\ICal\Event;
use FluentBooking\Package\CalDav\ICal\Event as ICalEvent;

class IcloudClient
{
    private $client;

    private $baseUrl = 'https://caldav.icloud.com';

    public function __construct($userName, $password)
    {
        $this->client = new ICloud($this->baseUrl, $userName, $password);
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
