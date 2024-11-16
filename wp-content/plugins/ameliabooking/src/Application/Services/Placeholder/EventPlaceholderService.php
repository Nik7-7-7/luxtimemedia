<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Event\CustomerBookingEventTicket;
use AmeliaBooking\Domain\Entity\Booking\Event\EventTicket;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Factory\Booking\Appointment\CustomerBookingFactory;
use AmeliaBooking\Domain\Factory\Booking\Event\EventFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\CustomerBookingEventTicketRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventTicketRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use DateTime;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class EventPlaceholderService
 *
 * @package AmeliaBooking\Application\Services\Placeholder
 */
class EventPlaceholderService extends PlaceholderService
{
    /**
     *
     * @return array
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function getEntityPlaceholdersDummyData($type)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $companySettings = $settingsService->getCategorySettings('company');

        $liStartTag = $type === 'email' ? '<li>' : '';
        $liEndTag   = $type === 'email' ? '</li>' : ($type === 'whatsapp' ? '; ' : PHP_EOL);
        $ulStartTag = $type === 'email' ? '<ul>' : '';
        $ulEndTag   = $type === 'email' ? '</ul>' : '';

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        $timestamp = new DateTime();

        $periodStartTime = $timestamp->format($timeFormat);
        $periodStartDate = $timestamp->format($dateFormat);
        $periodEndDate   = $timestamp->modify('+1 day');
        $periodEndTime   = $periodEndDate->add(new \DateInterval('PT1H'))->format($timeFormat);
        $periodEndDate   = $periodEndDate->format($dateFormat);

        $dateTimeString = $periodStartDate . ' - ' . $periodEndDate . ' (' . $periodStartTime . ' - ' . $periodEndTime . ')';


        return [
            'attendee_code'             => '12345',
            'event_id'                  => '123',
            'event_name'                => 'Event Name',
            'reservation_name'          => 'Reservation Name',
            'event_location'            => $companySettings['address'],
            'event_cancel_url'          => 'http://event_cancel_url.com',
            'event_periods'             =>
                $ulStartTag .
                    $liStartTag . date_i18n($dateFormat, $periodStartDate) . $liEndTag .
                    $liStartTag . date_i18n($dateFormat, $periodEndDate) . $liEndTag .
                $ulEndTag,
            'event_period_date'         =>
                $ulStartTag .
                    $liStartTag . date_i18n($dateFormat, $periodStartDate) . $liEndTag .
                    $liStartTag . date_i18n($dateFormat, $periodEndDate) . $liEndTag .
                $ulEndTag,
            'event_period_date_time'    =>
                $ulStartTag .
                    $liStartTag . $dateTimeString . $liEndTag .
                $ulEndTag,
            'event_start_date'          => date_i18n($dateFormat, $periodStartDate),
            'event_start_time'          => date_i18n($timeFormat, $periodStartTime),
            'event_start_date_time'     => date_i18n($dateFormat . ' ' . $timeFormat, $timestamp),
            'event_end_date'            => date_i18n($dateFormat, $periodEndDate),
            'event_end_time'            => date_i18n($timeFormat, $periodEndTime),
            'event_end_date_time'       => date_i18n($dateFormat . ' ' . $timeFormat, $periodEndDate),
            'event_deposit_payment'     => $helperService->getFormattedPrice(20),
            'event_price'               => $helperService->getFormattedPrice(100),
            'zoom_host_url_date'        => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodStartDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] .'</a></li>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodEndDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat, $periodStartDate) . ': ' . 'http://start_zoom_meeting_link.com',
            'zoom_host_url_date_time'   => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . ': ' . 'http://start_zoom_meeting_link.com',
            'zoom_join_url_date'        => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodStartDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] .'</a></li>' .
                    '<li><a href="#">' . date_i18n($dateFormat, $periodEndDate) . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat, $periodStartDate) . ': ' . 'http://join_zoom_meeting_link.com',
            'zoom_join_url_date_time'   => $type === 'email' ?
                '<ul>' .
                    '<li><a href="#">' . date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . ': ' . 'http://join_zoom_meeting_link.com' ,
            'google_meet_url_date'        => $type === 'email' ?
                '<ul>' .
                '<li><a href="#">' . date_i18n($dateFormat, $periodStartDate) . ' ' . BackendStrings::getCommonStrings()['google_meet_join'] .'</a></li>' .
                '<li><a href="#">' . date_i18n($dateFormat, $periodEndDate) . ' ' . BackendStrings::getCommonStrings()['google_meet_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat, $periodStartDate) . ': ' . 'https://join_google_meet_link.com',
            'google_meet_url_date_time'   => $type === 'email' ?
                '<ul>' .
                '<li><a href="#">' . date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . BackendStrings::getCommonStrings()['google_meet_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . ': ' . 'https://join_google_meet_link.com' ,
            'lesson_space_url_date'        => $type === 'email' ?
                '<ul>' .
                '<li><a href="#">' . date_i18n($dateFormat, $periodStartDate) . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] .'</a></li>' .
                '<li><a href="#">' . date_i18n($dateFormat, $periodEndDate) . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat, $periodStartDate) . ': ' . 'https://lesson_space.com/room-id',
            'lesson_space_url_date_time'   => $type === 'email' ?
                '<ul>' .
                '<li><a href="#">' . date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' .
                '</ul>' : date_i18n($dateFormat . ' ' . $timeFormat, $timestamp) . ': ' . 'https://lesson_space.com/room-id',
            'event_description'         => 'Event Description',
            'reservation_description'   => 'Reservation Description',
            'employee_name_email_phone' =>
                $ulStartTag .
                    $liStartTag . 'John Smith, 555-0120' . $liEndTag .
                    $liStartTag . 'Edward Williams, 555-3524' . $liEndTag .
                $ulEndTag,
        ];
    }

    /**
     * @param array        $event
     * @param int          $bookingKey
     * @param string       $type
     * @param AbstractUser $customer
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    public function getPlaceholdersData($event, $bookingKey = null, $type = null, $customer = null, $allBookings = null)
    {
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        $this->setData($event, $bookingKey);

        $locale = $this->getLocale($event, $bookingKey);

        $token = isset($event['bookings'][$bookingKey]) ?
            $bookingRepository->getToken($event['bookings'][$bookingKey]['id']) : null;

        $token = isset($token['token']) ? $token['token'] : null;

        $data = [];

        $data = array_merge($data, $this->getEventData($event, $bookingKey, $token, $type));
        if (empty($customer)) {
            $data = array_merge($data, $this->getGroupedEventData($event, $bookingKey, $type, $allBookings));
        }
        $data = array_merge($data, $this->getBookingData($event, $type, $bookingKey, $token));
        $data = array_merge($data, $this->getCompanyData($bookingKey !== null ? $locale : null));
        $data = array_merge($data, $this->getCustomersData($event, $type, $bookingKey, $customer));
        $data = array_merge($data, $this->getCustomFieldsData($event, $type, $bookingKey));
        $data = array_merge($data, $this->getCouponsData($event, $type, $bookingKey));

        return $data;
    }

    /**
     * @param array  $event
     * @param int    $bookingKey
     * @param string $token
     * @param string $type
     *
     * @return array
     *
     * @throws \Slim\Exception\ContainerValueNotFoundException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException
     * @throws \AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Exception
     */
    private function getEventData($event, $bookingKey = null, $token = null, $type = null)
    {
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var UserRepository $userRepo */
        $userRepo = $this->container->get('domain.users.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        $locale = $this->getLocale($event, $bookingKey);

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        $dateTimes = [];

        $locationName        = '';
        $locationAddress     = '';
        $locationDescription = '';
        $locationPhone       = '';
        $location            = null;

        if ($event['locationId']) {
            /** @var LocationRepository $locationRepository */
            $locationRepository = $this->container->get('domain.locations.repository');

            /** @var Location $location */
            $location = $locationRepository->getById($event['locationId']);

            $locationName = $helperService->getBookingTranslation(
                $bookingKey !== null ? $locale : null,
                $location->getTranslations() ? $location->getTranslations()->getValue() : null,
                'name'
            ) ?: $location->getName()->getValue();

            $locationDescription = $helperService->getBookingTranslation(
                $bookingKey !== null ? $locale : null,
                $location->getTranslations() ? $location->getTranslations()->getValue() : null,
                'description'
            ) ?: ($location->getDescription() ? $location->getDescription()->getValue() : '');

            $locationAddress = $location->getAddress() ? $location->getAddress()->getValue() : '';
            $locationPhone   = $location->getPhone() ? $location->getPhone()->getValue() : '';
        } elseif ($event['customLocation']) {
            $locationName = $event['customLocation'];
        }

        $staff = [];

        /** @var string $liStartTag */
        $liStartTag = $type === 'email' ? '<li>' : '';

        /** @var string $liEndTag */
        $liEndTag = $type === 'email' ? '</li>' : ($type === 'whatsapp' ? '; ' : PHP_EOL);

        /** @var string $ulStartTag */
        $ulStartTag = $type === 'email' ? '<ul>' : '';

        /** @var string $ulEndTag */
        $ulEndTag = $type === 'email' ? '</ul>' : '';

        $providers = (array)$event['providers'];

        if (isset($event['organizerId']) && !in_array($event['organizerId'], array_column($providers, 'id'))) {
            $providers[] = $userRepo->getById($event['organizerId'])->toArray();
        }

        $timeZones = [];

        foreach ($providers as $provider) {
            $firstName = $helperService->getBookingTranslation(
                $bookingKey !== null ? $locale : null,
                $provider['translations'],
                'firstName'
            ) ?: $provider['firstName'];

            $lastName = $helperService->getBookingTranslation(
                $bookingKey !== null ? $locale : null,
                $provider['translations'],
                'lastName'
            ) ?: $provider['lastName'];

            $userDescription = $helperService->getBookingTranslation(
                $bookingKey !== null ? $locale : null,
                $provider['translations'],
                'description'
            ) ?: $provider['description'];

            $staff[] = [
                'employee_first_name'       => $firstName,
                'employee_last_name'        => $lastName,
                'employee_full_name'        => $firstName . ' ' . $lastName,
                'employee_note'             => $provider['note'],
                'employee_description'      => $userDescription,
                'employee_phone'            => $provider['phone'],
                'employee_email'            => $provider['email'],
                'employee_name_email_phone' =>
                    (sizeof($event['providers']) > 1 ? $liStartTag : '') .
                    $firstName . ' ' . $lastName .
                    ($provider['phone'] ? ', ' . $provider['phone'] : '') .
                    (sizeof($event['providers']) > 1 ? $liEndTag : ''),
            ];

            $timeZones[] = $provider['timeZone'];
        }

        $timeZone = $providers && $timeZones && count(array_unique($timeZones)) === 1 ? array_unique($timeZones)[0] : null;

        $staff = [
            'employee_first_name'       =>
                implode(', ', array_column($staff, 'employee_first_name')),
            'employee_last_name'        =>
                implode(', ', array_column($staff, 'employee_last_name')),
            'employee_full_name'        =>
                implode(', ', array_column($staff, 'employee_full_name')),
            'employee_note'             =>
                implode(', ', array_column($staff, 'employee_note')),
            'employee_description'      =>
                implode(', ', array_column($staff, 'employee_description')),
            'employee_phone'            =>
                implode(', ', array_column($staff, 'employee_phone')),
            'employee_email'            =>
                implode(', ', array_column($staff, 'employee_email')),
            'employee_name_email_phone' =>
                $ulStartTag . implode('', array_column($staff, 'employee_name_email_phone')) . $ulEndTag,
        ];

        $oldEventStart = '';
        $oldEventEnd = '';
        foreach ((array)$event['periods'] as $index => $period) {
            if ($bookingKey !== null &&
                $event['bookings'][$bookingKey]['utcOffset'] !== null &&
                $settingsService->getSetting('general', 'showClientTimeZone')
            ) {
                $utcOffset = $event['bookings'][$bookingKey]['utcOffset'];

                $info = $event['bookings'][$bookingKey]['info'] ?
                    json_decode($event['bookings'][$bookingKey]['info'], true) : null;

                // fix for daylight saving for multiple periods
                if ($info && $info['timeZone'] && sizeof($event['periods']) > 1) {
                    $utcOffset = DateTimeService::getCustomDateTimeObject(
                        $period['periodStart']
                    )->setTimezone(new \DateTimeZone($info['timeZone']))->getOffset() / 60;
                }

                $dateTimes[] = [
                    'start' => DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc($period['periodStart']),
                        $utcOffset
                    ),
                    'end'   => DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc($period['periodEnd']),
                        $utcOffset
                    )
                ];

                if (!empty($event['initialEventStart']) && $index === 0) {
                    $oldEventStart = DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc($event['initialEventStart']),
                        $utcOffset
                    );

                    $oldEventEnd = DateTimeService::getClientUtcCustomDateTimeObject(
                        DateTimeService::getCustomDateTimeInUtc($event['initialEventEnd']),
                        $utcOffset
                    );
                }
            } else if ($bookingKey === null && $timeZone) {
                $dateTimes[] = [
                    'start' => DateTimeService::getDateTimeObjectInTimeZone(
                        DateTimeService::getCustomDateTimeObject(
                            $period['periodStart']
                        )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                        'UTC'
                    ),
                    'end'   => DateTimeService::getDateTimeObjectInTimeZone(
                        DateTimeService::getCustomDateTimeObject(
                            $period['periodEnd']
                        )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                        'UTC'
                    )
                ];

                if (!empty($event['initialEventStart']) && $index === 0) {
                    $oldEventStart = DateTimeService::getDateTimeObjectInTimeZone(
                        DateTimeService::getCustomDateTimeObject(
                            $event['initialEventStart']
                        )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                        'UTC'
                    );

                    $oldEventEnd = DateTimeService::getDateTimeObjectInTimeZone(
                        DateTimeService::getCustomDateTimeObject(
                            $event['initialEventEnd']
                        )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                        'UTC'
                    );
                }
            } else {
                $dateTimes[] = [
                    'start' => DateTime::createFromFormat('Y-m-d H:i:s', $period['periodStart']),
                    'end'   => DateTime::createFromFormat('Y-m-d H:i:s', $period['periodEnd'])
                ];
                if (!empty($event['initialEventStart']) && $index === 0) {
                    $oldEventStart = DateTime::createFromFormat('Y-m-d H:i:s', $event['initialEventStart']);
                    $oldEventEnd   = DateTime::createFromFormat('Y-m-d H:i:s', $event['initialEventEnd']);
                }
            }
        }

        $eventDateList          = [];
        $eventDateTimeList      = [];
        $eventZoomStartDateList = [];
        $eventZoomStartDateTimeList   = [];
        $eventZoomJoinDateList        = [];
        $eventZoomJoinDateTimeList    = [];
        $eventGoogleMeetDateList      = [];
        $eventGoogleMeetDateTimeList  = [];
        $eventLessonSpaceDateList     = [];
        $eventLessonSpaceDateTimeList = [];


        foreach ($dateTimes as $key => $dateTime) {
            /** @var \DateTime $startDateTime */
            $startDateTime = $dateTime['start'];

            /** @var \DateTime $endDateTime */
            $endDateTime = $dateTime['end'];

            $startDateString = $startDateTime->format('Y-m-d');
            $endDateString   = $endDateTime->format('Y-m-d');

            $periodStartDate = date_i18n($dateFormat, $startDateTime->getTimestamp());
            $periodEndDate   = date_i18n($dateFormat, $endDateTime->getTimestamp());

            $periodStartTime = date_i18n($timeFormat, $startDateTime->getTimestamp());
            $periodEndTime   = date_i18n($timeFormat, $endDateTime->getTimestamp());

            $dateString = $startDateString === $endDateString ?
                $periodStartDate :
                $periodStartDate . ' - ' . $periodEndDate;

            $dateTimeString = $startDateString === $endDateString ?
                $periodStartDate . ' (' . $periodStartTime . ' - ' . $periodEndTime . ')' :
                $periodStartDate . ' - ' . $periodEndDate . ' (' . $periodStartTime . ' - ' . $periodEndTime . ')';

            $eventDateList[]     = "$liStartTag{$dateString}$liEndTag";
            $eventDateTimeList[] = "$liStartTag{$dateTimeString}$liEndTag";

            if ($event['zoomUserId'] && $event['periods'][$key]['zoomMeeting']) {
                $startUrl = $event['periods'][$key]['zoomMeeting']['startUrl'];
                $joinUrl  = $event['periods'][$key]['zoomMeeting']['joinUrl'];

                $eventZoomStartDateList[]     =  $type === 'email' ? '<li><a href="' . $startUrl . '">' . $dateString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' : $dateString . ': ' . $startUrl;
                $eventZoomStartDateTimeList[] = $type === 'email' ? '<li><a href="' . $startUrl . '">' . $dateTimeString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a></li>' : $dateTimeString . ': ' . $startUrl;
                $eventZoomJoinDateList[]      = $type === 'email' ? '<li><a href="' . $joinUrl . '">' . $dateString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' : $dateString . ': ' . $joinUrl;
                $eventZoomJoinDateTimeList[]  = $type === 'email' ? '<li><a href="' . $joinUrl . '">' . $dateTimeString . ' ' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a></li>' : $dateTimeString . ': ' . $joinUrl;
            }

            if ($event['periods'][$key]['googleMeetUrl']) {
                $googleMeetUrl = $event['periods'][$key]['googleMeetUrl'];

                $eventGoogleMeetDateList[]     = $type === 'email' ? '<li><a href="' . $googleMeetUrl . '">' . $dateString . ' ' . BackendStrings::getCommonStrings()['google_meet_join'] . '</a></li>' : $dateString . ': ' . $googleMeetUrl;
                $eventGoogleMeetDateTimeList[] = $type === 'email' ? '<li><a href="' . $googleMeetUrl . '">' . $dateTimeString . ' ' . BackendStrings::getCommonStrings()['google_meet_join'] . '</a></li>' : $dateTimeString . ': ' . $googleMeetUrl;
            }

            if ($event['periods'][$key]['lessonSpace']) {
                $lessonSpaceUrl = $event['periods'][$key]['lessonSpace'];

                $eventLessonSpaceDateList[]     = $type === 'email' ? '<li><a href="' . $lessonSpaceUrl . '">' . $dateString . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' : $dateString . ': ' . $lessonSpaceUrl;
                $eventLessonSpaceDateTimeList[] = $type === 'email' ? '<li><a href="' . $lessonSpaceUrl . '">' . $dateTimeString . ' ' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a></li>' : $dateTimeString . ': ' . $lessonSpaceUrl;
            }
        }

        if (sizeof($dateTimes) > 1) {
            usort(
                $dateTimes,
                function ($a, $b) {
                    return ($a['start']->getTimestamp() < $b['start']->getTimestamp()) ? -1 : 1;
                }
            );
        }

        /** @var \DateTime $eventStartDateTime */
        $eventStartDateTime = $dateTimes[0]['start'];

        /** @var \DateTime $eventEndDateTime */
        $eventEndDateTime = $dateTimes[sizeof($dateTimes) - 1]['end'];

        if ($bookingKey !== null) {
            $attendeeCode = $token ?: '';
        } else {
            $attendeeCode = $bookingRepository->getTokensByEventId($event['id']);
        }


        $eventName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $event['translations'],
            'name'
        ) ?: $event['name'];

        $eventDescription = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $event['translations'],
            'description'
        ) ?: $event['description'];

        $eventTickets = [];

        /** @var EventTicketRepository $eventTicketRepository */
        $eventTicketRepository = $this->container->get('domain.booking.event.ticket.repository');

        $ticketsPrice = null;
        $eventPrices  = [];
        if ($bookingKey !== null) {
            /** @var CustomerBookingEventTicketRepository $bookingEventTicketRepository */
            $bookingEventTicketRepository =
                $this->container->get('domain.booking.customerBookingEventTicket.repository');

            $ticketsBookings = $bookingEventTicketRepository->getByEntityId(
                $event['bookings'][$bookingKey]['id'],
                'customerBookingId'
            );

            if ($ticketsBookings->length()) {
                $ticketsPrice = 0;
                $eventPrices  = [];
                /** @var CustomerBookingEventTicket $bookingToEventTicket */
                foreach ($ticketsBookings->getItems() as $key => $bookingToEventTicket) {
                    /** @var EventTicket $ticket */
                    $ticket = $eventTicketRepository->getById($bookingToEventTicket->getEventTicketId()->getValue());

                    $ticketName = $helperService->getBookingTranslation(
                        $locale,
                        $ticket->getTranslations() ? $ticket->getTranslations()->getValue() : null,
                        null
                    ) ?: $ticket->getName()->getValue();

                    if ($bookingToEventTicket->getPersons()->getValue() > 0) {
                        $eventTickets[] = $bookingToEventTicket->getPersons()->getValue() . ' x ' . $ticketName;
                    }

                    $eventPrices[] = $helperService->getFormattedPrice($ticket->getPrice()->getValue());
                    $ticketsPrice +=
                        $bookingToEventTicket->getPersons()->getValue() * $bookingToEventTicket->getPrice()->getValue();
                }
            } elseif (!empty($event['bookings'][$bookingKey]['ticketsData'])) {
                $ticketsPrice = 0;
                $eventPrices  = [];
                foreach ($event['bookings'][$bookingKey]['ticketsData'] as $key => $bookingToEventTicket) {
                    if ($bookingToEventTicket['price']) {
                        /** @var EventTicket $ticket */
                        $ticket = $eventTicketRepository->getById($bookingToEventTicket['eventTicketId']);

                        $ticketName = $helperService->getBookingTranslation(
                            $locale,
                            $ticket->getTranslations() ? $ticket->getTranslations()->getValue() : null,
                            null
                        ) ?: $ticket->getName()->getValue();

                        if ($bookingToEventTicket['persons']) {
                            $eventTickets[] = $bookingToEventTicket['persons'] . ' x ' . $ticketName;
                        }

                        $eventPrices[] = $helperService->getFormattedPrice($ticket->getPrice()->getValue());
                        $ticketsPrice +=
                            $bookingToEventTicket['persons'] * $bookingToEventTicket['price'];
                    }
                }
            }
        } else {
            $sendAttendeeCode = [];
            foreach ($event['bookings'] as $booking) {
                $bookingIndex = array_search(
                    $booking['id'],
                    array_column($attendeeCode, 'id')
                );
                if ($bookingIndex !== false) {
                    $sendAttendeeCode[] = substr($attendeeCode[$bookingIndex]['token'], 0, 5);
                }

                /** @var CustomerBookingEventTicketRepository $bookingEventTicketRepository */
                $bookingEventTicketRepository =
                    $this->container->get('domain.booking.customerBookingEventTicket.repository');

                $ticketsBookings = $bookingEventTicketRepository->getByEntityId(
                    $booking['id'],
                    'customerBookingId'
                );

                if ($ticketsBookings->length()) {
                    $ticketsPrice = 0;
                    $eventPrices  = [];
                    /** @var CustomerBookingEventTicket $bookingToEventTicket */
                    foreach ($ticketsBookings->getItems() as $key => $bookingToEventTicket) {
                        /** @var EventTicket $ticket */
                        $ticket = $eventTicketRepository->getById($bookingToEventTicket->getEventTicketId()->getValue());

                        $eventTickets[] = $bookingToEventTicket->getPersons()->getValue() . ' x ' . $ticket->getName()->getValue();

                        $eventPrices[] = $helperService->getFormattedPrice($ticket->getPrice()->getValue());
                        $ticketsPrice +=
                            $bookingToEventTicket->getPersons()->getValue() * $bookingToEventTicket->getPrice()->getValue();
                    }
                } elseif (!empty($booking['ticketsData'])) {
                    $ticketsPrice = 0;
                    $eventPrices  = [];
                    foreach ($booking['ticketsData'] as $key => $bookingToEventTicket) {
                        if ($bookingToEventTicket['price']) {
                            /** @var EventTicket $ticket */
                            $ticket = $eventTicketRepository->getById($bookingToEventTicket['eventTicketId']);

                            $ticketName = $helperService->getBookingTranslation(
                                $locale,
                                $ticket->getTranslations() ? $ticket->getTranslations()->getValue() : null,
                                null
                            ) ?: $ticket->getName()->getValue();

                            $eventTickets[] = $bookingToEventTicket['persons'] . ' x ' . $ticketName;

                            $eventPrices[] = $helperService->getFormattedPrice($ticket->getPrice()->getValue());
                            $ticketsPrice +=
                                $bookingToEventTicket['persons'] * $bookingToEventTicket['price'];
                        }
                    }
                }
            }
            $attendeeCode = $sendAttendeeCode;
        }

        $timeZone = get_option('timezone_string');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        if ($bookingKey !== null &&
            $settingsService->getSetting('general', 'showClientTimeZone')
        ) {
            $info = !empty(!empty($event['bookings'][$bookingKey]['info']))
                ? json_decode($event['bookings'][$bookingKey]['info'], true)
                : null;

            $timeZone = !empty($info['timeZone']) ? $info['timeZone'] : '';
        }

        return array_merge(
            [
            'attendee_code'            => is_array($attendeeCode) ?  implode(', ', $attendeeCode) : substr($attendeeCode, 0, 5),
            'reservation_name'         => $eventName,
            'event_id'                 => $event['id'],
            'event_name'               => $eventName,
            'event_name_url'           => sanitize_title($event['name']),
            'event_price'              => $ticketsPrice !== null ?
                implode(', ', $eventPrices) : $helperService->getFormattedPrice($event['price']),
            'event_description'        => $eventDescription,
            'event_tickets'            => $eventTickets ? implode(', ', $eventTickets) : '',
            'reservation_description'  => $eventDescription,
            'event_location'           => $locationName,
            'location_name'            => $locationName,
            'location_latitude'        => $location && $location->getCoordinates() ? $location->getCoordinates()->getLatitude() : null,
            'location_longitude'       => $location && $location->getCoordinates() ? $location->getCoordinates()->getLongitude() : null,
            'location_address'         => $locationAddress,
            'location_description'     => $locationDescription,
            'location_phone'           => $locationPhone,
            'event_period_date'        => $ulStartTag . implode('', $eventDateList) . $ulEndTag,
            'event_period_date_time'   => $ulStartTag . implode('', $eventDateTimeList) . $ulEndTag,
            'time_zone'                => $timeZone,
            'lesson_space_url_date'       => count($eventLessonSpaceDateList) === 0 ?
                '' : $ulStartTag . implode('', $eventLessonSpaceDateList) . $ulEndTag,
            'lesson_space_url_date_time'  => count($eventLessonSpaceDateTimeList) === 0 ?
                '' : $ulStartTag . implode('', $eventLessonSpaceDateTimeList) . $ulEndTag,
            'google_meet_url_date'     => count($eventGoogleMeetDateList) === 0 ?
                '' : $ulStartTag . implode('', $eventGoogleMeetDateList) . $ulEndTag,
            'google_meet_url_date_time' => count($eventGoogleMeetDateTimeList) === 0 ?
                '' : $ulStartTag . implode('', $eventGoogleMeetDateTimeList) . $ulEndTag,
            'zoom_host_url_date'       => count($eventZoomStartDateList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomStartDateList) . $ulEndTag,
            'zoom_host_url_date_time'  => count($eventZoomStartDateTimeList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomStartDateTimeList) . $ulEndTag,
            'zoom_join_url_date'       => count($eventZoomJoinDateList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomJoinDateList) . $ulEndTag,
            'zoom_join_url_date_time'  => count($eventZoomJoinDateTimeList) === 0 ?
                '' : $ulStartTag . implode('', $eventZoomJoinDateTimeList) . $ulEndTag,
            'event_start_date'         => date_i18n($dateFormat, $eventStartDateTime->getTimestamp()),
            'event_end_date'           => date_i18n($dateFormat, $eventEndDateTime->getTimestamp()),
            'event_start_date_time'    => date_i18n(
                $dateFormat . ' ' . $timeFormat,
                $eventStartDateTime->getTimestamp()
            ),
            'event_end_date_time'      => date_i18n(
                $dateFormat . ' ' . $timeFormat,
                $eventEndDateTime->getTimestamp()
            ),
            'event_start_time'         => date_i18n(
                $timeFormat,
                $eventStartDateTime->getTimestamp()
            ),
            'event_end_time'           => date_i18n(
                $timeFormat,
                $eventEndDateTime->getTimestamp()
            ),
            'initial_event_start_date'         => !empty($oldEventStart) ? date_i18n($dateFormat, $oldEventStart->getTimestamp()) : '',
            'initial_event_end_date'           => !empty($oldEventEnd) ? date_i18n($dateFormat, $oldEventEnd->getTimestamp()) : '',
            'initial_event_start_date_time'    => !empty($oldEventStart) ? date_i18n(
                $dateFormat . ' ' . $timeFormat,
                $oldEventStart->getTimestamp()
            ) : '',
            'initial_event_end_date_time'      => !empty($oldEventEnd) ? date_i18n(
                $dateFormat . ' ' . $timeFormat,
                $oldEventEnd->getTimestamp()
            ) : '',
            'initial_event_start_time'         => !empty($oldEventStart) ? date_i18n(
                $timeFormat,
                $oldEventStart->getTimestamp()
            ) : '',
            'initial_event_end_time'           => !empty($oldEventEnd) ? date_i18n(
                $timeFormat,
                $oldEventEnd->getTimestamp()
            ) : ''
            ],
            $staff
        );
    }

    /**
     * @param array $event
     * @param int $bookingKey
     * @param string $type
     * @param array $allBookings
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException|InvalidArgumentException
     */
    public function getGroupedEventData($event, $bookingKey, $type, $allBookings)
    {
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.appointment.service");

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $appointmentsSettings = $settingsService->getCategorySettings('appointments');

        $groupEventDetails = [];

        if ($bookingKey) {
            return [
                "group_event_details" => ''
            ];
        }

        if ($allBookings) {
            $event['bookings'] = $allBookings;
        }

        $attendeeCodes = $bookingRepository->getTokensByEventId($event['id']);
        foreach ($event['bookings'] as $bookingId => $booking) {
            $bookingIndex = array_search(
                $booking['id'],
                array_column($attendeeCodes, 'id')
            );
            if ($bookingIndex !== false) {
                $token = $attendeeCodes[$bookingIndex]['token'];
            }
            $groupPlaceholders = array_merge(
                $this->getEventData($event, $bookingId, isset($token) ? $token : null),
                $this->getCustomFieldsData($event, $type, $bookingId),
                $this->getCustomersData($event, $type, $bookingId),
                $this->getBookingData(
                    $event,
                    $type,
                    $bookingId,
                    isset($token) ? $token : null
                )
            );

            $content = $appointmentsSettings['groupEventPlaceholder'. ($type === null || $type === 'email' ? '' : 'Sms')];
            if ($type === 'email') {
                $content = str_replace(array("\n","\r"), '', $content);
            } else if ($type === 'whatsapp') {
                $content = str_replace(array("\n","\r"), '; ', $content);
                $content = preg_replace('!\s+!', ' ', $content);
            }

            $groupEventDetails[] = $placeholderService->applyPlaceholders(
                $content,
                $groupPlaceholders
            );
        }

        return [
            "group_event_details" => $groupEventDetails ? implode(
                $type === 'email' ? '<p><br></p>' : ($type === 'whatsapp' ? '; ' : PHP_EOL),
                $groupEventDetails
            ) : ''
        ];
    }

    /**
     * @param array $bookingArray
     * @param array $entity
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function getAmountData(&$bookingArray, $entity)
    {
        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get(Entities::EVENT);

        if (!empty($bookingArray['couponId']) && empty($bookingArray['coupon'])) {
            /** @var CouponRepository $couponRepository */
            $couponRepository = $this->container->get('domain.coupon.repository');

            /** @var Coupon $coupon */
            $coupon = $couponRepository->getById($bookingArray['couponId']);

            $bookingArray['coupon'] = $coupon ? $coupon->toArray() : null;
        }

        $eventCustomPricing = [];

        foreach ($entity['customTickets'] as $customTicket) {
            $eventCustomPricing[$customTicket['id']] = [
                'dateRanges' => '[]',
                'price'      => !empty($customTicket['dateRangePrice'])
                    ? $customTicket['dateRangePrice'] : $customTicket['price'],
            ];
        }

        $bookable = EventFactory::create(
            [
                'price'           => $entity['price'],
                'aggregatedPrice' => !!$bookingArray['aggregatedPrice'],
                'customPricing'   => !empty($eventCustomPricing),
                'customTickets'   => $eventCustomPricing,
            ]
        );

        $booking = CustomerBookingFactory::create(
            [
                'persons'         => $bookingArray['persons'],
                'aggregatedPrice' => !!$bookingArray['aggregatedPrice'],
                'ticketsData'     => !empty($bookingArray['ticketsData']) ? $bookingArray['ticketsData'] : null,
                'coupon'          => $bookingArray['coupon'],
                'tax'             => $bookingArray['tax'],
            ]
        );

        return [
            'price'     => $reservationService->getPaymentAmount($booking, $bookable),
            'discount'  => $reservationService->getPaymentAmount($booking, $bookable, 'discount'),
            'deduction' => $reservationService->getPaymentAmount($booking, $bookable, 'deduction'),
        ];
    }
}
