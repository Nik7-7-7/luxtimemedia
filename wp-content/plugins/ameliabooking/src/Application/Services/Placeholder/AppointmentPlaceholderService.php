<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Services\Placeholder;

use AmeliaBooking\Application\Services\Booking\AppointmentApplicationService;
use AmeliaBooking\Application\Services\Helper\HelperService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\AbstractBookable;
use AmeliaBooking\Domain\Entity\Bookable\Service\Category;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\User\AbstractUser;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Bookable\Service\ServiceFactory;
use AmeliaBooking\Domain\Factory\Booking\Appointment\CustomerBookingFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\CategoryRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\Location\LocationRepository;
use AmeliaBooking\Infrastructure\Repository\User\UserRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use DateTime;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class AppointmentPlaceholderService
 *
 * @package AmeliaBooking\Application\Services\Placeholder
 */
class AppointmentPlaceholderService extends PlaceholderService
{
    /**
     *
     * @return array
     *
     * @throws ContainerException
     */
    public function getEntityPlaceholdersDummyData($type)
    {
        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        $companySettings = $settingsService->getCategorySettings('company');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        $timeZone = get_option('timezone_string');

        if (empty($timeZone)) {
            $gmtOffset = get_option('gmt_offset');
            $timeZone = sprintf('Etc/GMT%+d', -$gmtOffset);
        }

        $dateTime = new DateTime("now", new \DateTimeZone($timeZone));

        $appointment_date = $dateTime->format($dateFormat);
        $appointment_date_time = $dateTime->format($dateFormat . ' ' . $timeFormat);
        $appointment_start_time = $dateTime->format($timeFormat);
        $appointment_end_time = (new DateTime("now +1 hour", new \DateTimeZone($timeZone)))->format($timeFormat);

        return [
            'appointment_id'          => '1',
            'appointment_date'        => $appointment_date,
            'appointment_date_time'   => $appointment_date_time,
            'appointment_start_time'  => $appointment_start_time,
            'appointment_end_time'    => $appointment_end_time,
            'appointment_notes'       => 'Appointment note',
            'appointment_price'       => $helperService->getFormattedPrice(100),
            'appointment_cancel_url'  => 'http://cancel_url.com',
            'appointment_approve_url' => 'http://approve_url.com',
            'appointment_reject_url'  => 'http://reject_url.com',
            'zoom_join_url'           => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a>' : 'https://join_zoom_link.com',
            'zoom_host_url'           => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a>' : 'https://start_zoom_link.com',
            'google_meet_url'          => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['google_meet_join'] . '</a>' : 'https://join_google_meet_link.com',
            'lesson_space_url'       => $type === 'email' ?
                '<a href="#">' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a>' : 'https://lessonspace.com/room-id',
            'appointment_duration'    => $helperService->secondsToNiceDuration(1800),
            'appointment_deposit_payment'     => $helperService->getFormattedPrice(20),
            'appointment_status'      => BackendStrings::getCommonStrings()['approved'],
            'category_name'           => 'Category Name',
            'service_description'     => 'Service Description',
            'reservation_description' => 'Service Description',
            'service_duration'        => $helperService->secondsToNiceDuration(5400),
            'service_name'            => 'Service Name',
            'service_id'              => '123',
            'reservation_name'        => 'Service Name',
            'service_price'           => $helperService->getFormattedPrice(100),
            'service_extras'          => 'Extra1, Extra2, Extra3',
            'service_extras_details'  => '<p>Extra1: ($1.00 x 1) x 3</p><p>Extra2: ($2.00 x 2)</p><p>Extra3: ($3.00 x 3)</p>' .
                '<p>-------------------------</p>' . '<p>' . BackendStrings::getCommonStrings()['extras_total_price'] . ' $16.00</p>'

        ];
    }

    /**
     * @param array        $appointment
     * @param int          $bookingKey
     * @param string       $type
     * @param AbstractUser $customer
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getPlaceholdersData($appointment, $bookingKey = null, $type = null, $customer = null, $allBookings = null)
    {
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        $bookingKeyForEmployee = null;

        if ($bookingKey === null) {
            $bookingKeyForEmployee = $this->getBookingKeyForEmployee($appointment);
        }

        $token = isset($appointment['bookings'][$bookingKey]) ?
            $bookingRepository->getToken($appointment['bookings'][$bookingKey]['id']) :
            ($bookingKeyForEmployee ? $bookingRepository->getToken($bookingKeyForEmployee) : null);

        $token = isset($token['token']) ? $token['token'] : null;

        $data = [];

        $this->setData($appointment, $bookingKey);

        $locale = $this->getLocale($appointment, $bookingKey);

        $data = array_merge($data, $this->getAppointmentData($appointment, $bookingKey, $type));
        $data = array_merge($data, $this->getServiceData($appointment, $bookingKey, $type));
        $data = array_merge($data, $this->getEmployeeData($appointment, $bookingKey));
        $data = array_merge($data, $this->getRecurringAppointmentsData($appointment, $bookingKey, $type, 'recurring', $bookingKeyForEmployee));
        if (empty($customer)) {
            $data = array_merge($data, $this->getGroupedAppointmentData($appointment, $bookingKey, $type, $token));
        }
        $data = array_merge($data, $this->getBookingData($appointment, $type, $bookingKey, $token, $data['deposit']));
        $data = array_merge($data, $this->getCompanyData($bookingKey !== null ? $locale : null));
        $data = array_merge($data, $this->getCustomersData($appointment, $type, $bookingKey, $customer));
        $data = array_merge($data, $this->getCustomFieldsData($appointment, $type, $bookingKey));
        $data = array_merge($data, $this->getCouponsData($appointment, $type, $bookingKey));

        return $data;
    }

    /**
     * @param        $appointment
     * @param null   $bookingKey
     * @param string $type
     *
     * @return array
     *
     * @throws Exception
     */
    protected function getAppointmentData($appointment, $bookingKey = null, $type = null)
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $dateFormat = $settingsService->getSetting('wordpress', 'dateFormat');
        $timeFormat = $settingsService->getSetting('wordpress', 'timeFormat');

        if ($appointment['providerId'] && empty($appointment['provider'])) {
            /** @var Provider $user */
            $user = $userRepository->getById($appointment['providerId']);

            $appointment['provider'] = $user->toArray();
        }

        if ($bookingKey !== null && $appointment['bookings'][$bookingKey]['utcOffset'] !== null
            && $settingsService->getSetting('general', 'showClientTimeZone')
        ) {
            $info = !empty($appointment['bookings'][$bookingKey]['info'])
                ? json_decode($appointment['bookings'][$bookingKey]['info'], true)
                : null;

            $timeZone = !empty($info['timeZone']) ? $info['timeZone'] : '';

            $bookingStart = DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['bookingStart']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            );

            $bookingEnd = DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['bookingEnd']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            );

            $oldBookingStart = !empty($appointment['initialAppointmentDateTime']) ? DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['initialAppointmentDateTime']['bookingStart']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            ) : '';

            $oldBookingEnd = !empty($appointment['initialAppointmentDateTime']) ? DateTimeService::getClientUtcCustomDateTimeObject(
                DateTimeService::getCustomDateTimeInUtc($appointment['initialAppointmentDateTime']['bookingEnd']),
                $appointment['bookings'][$bookingKey]['utcOffset']
            ) : '';
        } else if ($bookingKey === null && !empty($appointment['provider']['timeZone'])) {
            $timeZone = $appointment['provider']['timeZone'];

            $bookingStart = DateTimeService::getDateTimeObjectInTimeZone(
                DateTimeService::getCustomDateTimeObject(
                    $appointment['bookingStart']
                )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                'UTC'
            );

            $bookingEnd = DateTimeService::getDateTimeObjectInTimeZone(
                DateTimeService::getCustomDateTimeObject(
                    $appointment['bookingEnd']
                )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                'UTC'
            );

            $oldBookingStart = !empty($appointment['initialAppointmentDateTime']) ?
                DateTimeService::getDateTimeObjectInTimeZone(
                    DateTimeService::getCustomDateTimeObject(
                        $appointment['initialAppointmentDateTime']['bookingStart']
                    )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                    'UTC'
                ) : '';

            $oldBookingEnd = !empty($appointment['initialAppointmentDateTime']) ?
                DateTimeService::getDateTimeObjectInTimeZone(
                    DateTimeService::getCustomDateTimeObject(
                        $appointment['initialAppointmentDateTime']['bookingEnd']
                    )->setTimezone(new \DateTimeZone($timeZone))->format('Y-m-d H:i:s'),
                    'UTC'
                ) : '';
        } else {
            $timeZone = get_option('timezone_string');

            $bookingStart = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['bookingStart']);

            $bookingEnd = DateTime::createFromFormat('Y-m-d H:i:s', $appointment['bookingEnd']);

            $oldBookingStart = !empty($appointment['initialAppointmentDateTime']) ?
                DateTime::createFromFormat('Y-m-d H:i:s', $appointment['initialAppointmentDateTime']['bookingStart']) : '';

            $oldBookingEnd = !empty($appointment['initialAppointmentDateTime']) ?
                DateTime::createFromFormat('Y-m-d H:i:s', $appointment['initialAppointmentDateTime']['bookingEnd']) : '';
        }

        $zoomStartUrl = '';
        $zoomJoinUrl  = '';

        $lessonSpaceLink = '';
        if (array_key_exists('lessonSpace', $appointment) && $appointment['lessonSpace']) {
            $lessonSpaceLink = $type === 'email' ?
                '<a href="' . $appointment['lessonSpace'] . '">' . BackendStrings::getCommonStrings()['lesson_space_join'] . '</a>'
                : $appointment['lessonSpace'];
        }

        if (isset($appointment['zoomMeeting']['joinUrl'], $appointment['zoomMeeting']['startUrl'])) {
            $zoomStartUrl = $appointment['zoomMeeting']['startUrl'];
            $zoomJoinUrl  = $appointment['zoomMeeting']['joinUrl'];
        }

        $googleMeetUrl = '';
        if (array_key_exists('googleMeetUrl', $appointment) && $appointment['googleMeetUrl']) {
            $googleMeetUrl = $type === 'email' ?
                '<a href="' . $appointment['googleMeetUrl'] . '">' . BackendStrings::getCommonStrings()['google_meet_join'] . '</a>'
                : $appointment['googleMeetUrl'];
        }

        return [
            'appointment_id'         => !empty($appointment['id']) ? $appointment['id'] : '',
            'appointment_status'     => BackendStrings::getCommonStrings()[$appointment['status']],
            'appointment_notes'      => !empty($appointment['internalNotes']) ? $appointment['internalNotes'] : '',
            'appointment_date'       => date_i18n($dateFormat, $bookingStart->getTimestamp()),
            'appointment_date_time'  => date_i18n($dateFormat . ' ' . $timeFormat, $bookingStart->getTimestamp()),
            'appointment_start_time' => date_i18n($timeFormat, $bookingStart->getTimestamp()),
            'appointment_end_time'   => date_i18n($timeFormat, $bookingEnd->getTimestamp()),
            'initial_appointment_date' => !empty($oldBookingStart) ? date_i18n($dateFormat, $oldBookingStart->getTimestamp()) : '',
            'initial_appointment_date_time' => !empty($oldBookingStart) ? date_i18n($dateFormat . ' ' . $timeFormat, $oldBookingStart->getTimestamp()) : '',
            'initial_appointment_start_time' => !empty($oldBookingStart) ? date_i18n($timeFormat, $oldBookingStart->getTimestamp()) : '',
            'initial_appointment_end_time' => !empty($oldBookingEnd) ? date_i18n($timeFormat, $oldBookingEnd->getTimestamp()) : '',
            'lesson_space_url'       => $lessonSpaceLink,
            'zoom_host_url'          => $zoomStartUrl && $type === 'email' ?
                '<a href="' . $zoomStartUrl . '">' . BackendStrings::getCommonStrings()['zoom_click_to_start'] . '</a>'
                : $zoomStartUrl,
            'zoom_join_url'          => $zoomJoinUrl && $type === 'email' ?
                '<a href="' . $zoomJoinUrl . '">' . BackendStrings::getCommonStrings()['zoom_click_to_join'] . '</a>'
                : $zoomJoinUrl,
            'google_meet_url'        => $googleMeetUrl,
            'time_zone'              => $timeZone,
        ];
    }

    /**
     * @param $appointmentArray
     * @param $bookingKey
     *
     * @return array
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    private function getServiceData($appointmentArray, $bookingKey, $type)
    {
        /** @var CategoryRepository $categoryRepository */
        $categoryRepository = $this->container->get('domain.bookable.category.repository');
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');
        /** @var AppointmentApplicationService $appointmentAS */
        $appointmentAS = $this->container->get('application.booking.appointment.service');

        /** @var Service $service */
        $service = $serviceRepository->getByIdWithExtras($appointmentArray['serviceId']);
        /** @var Category $category */
        $category = $categoryRepository->getById($service->getCategoryId()->getValue());

        $locale = $this->getLocale($appointmentArray, $bookingKey);

        $categoryName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $category->getTranslations() ? $category->getTranslations()->getValue() : null,
            'name'
        ) ?: $category->getName()->getValue();

        $serviceName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $service->getTranslations() ? $service->getTranslations()->getValue() : null,
            'name'
        ) ?: $service->getName()->getValue();

        $serviceDescription = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $service->getTranslations() ? $service->getTranslations()->getValue() : null,
            'description'
        ) ?: ($service->getDescription() ? $service->getDescription()->getValue() : '');

        $servicePrices = [];

        $serviceDurations = [];

        if ($bookingKey === null) {
            foreach ($appointmentArray['bookings'] as $booking) {
                if ($booking['status'] === BookingStatus::CANCELED || $booking['status'] === BookingStatus::REJECTED) {
                    continue;
                }

                $duration = $booking['duration'] ? $booking['duration'] : $service->getDuration()->getValue();

                $price = $appointmentAS->getBookingPriceForServiceDuration(
                    $service,
                    $duration
                );

                $servicePrices[] = $helperService->getFormattedPrice($price);

                $serviceDurations[$duration] = $helperService->secondsToNiceDuration($duration);
            }
        } else {
            $duration = !empty($appointmentArray['bookings'][$bookingKey]['duration'])
                ? $appointmentArray['bookings'][$bookingKey]['duration'] : $service->getDuration()->getValue();

            $price = $appointmentAS->getBookingPriceForServiceDuration(
                $service,
                $duration
            );

            $servicePrices[] = $helperService->getFormattedPrice(
                $price
            );

            $serviceDurations[$duration] = $helperService->secondsToNiceDuration(
                $duration
            );
        }

        $data = [
            'category_name'           => $categoryName,
            'category_id'             => $category->getId()->getValue(),
            'service_description'     => $serviceDescription,
            'reservation_description' => $serviceDescription,
            'service_duration'        => implode(', ', $serviceDurations),
            'service_name'            => $serviceName,
            'service_id'              => $service->getId()->getValue(),
            'reservation_name'        => $serviceName,
            'service_price'           => implode(', ', array_unique($servicePrices)),
        ];

        $bookingExtras = [];

        $persons = 1;

        foreach ((array)$appointmentArray['bookings'] as $key => $booking) {
            if (
                ($bookingKey === null && ($booking['isChangedStatus'] || $booking['status'] === BookingStatus::APPROVED
                    || $booking['status'] === BookingStatus::PENDING)) || $bookingKey === $key
                )
            {
                foreach ((array)$booking['extras'] as $bookingExtra) {
                    $bookingExtras[$bookingExtra['extraId']] = [
                        'quantity' => $bookingExtra['quantity']
                    ];
                }

                $persons = $booking['persons'];
            }
        }

        /** @var ExtraRepository $extraRepository */
        $extraRepository = $this->container->get('domain.bookable.extra.repository');

        /** @var Collection $extras */
        $extras = $extraRepository->getAllIndexedById();

        $duration = $service->getDuration()->getValue();

        if ($bookingKey !== null) {
            $duration = !empty($appointmentArray['bookings'][$bookingKey]['duration']) ?
                $appointmentArray['bookings'][$bookingKey]['duration'] : $duration;

            foreach ($appointmentArray['bookings'][$bookingKey]['extras'] as $bookingExtra) {
                /** @var Extra $extra */
                $extra = $extras->getItem($bookingExtra['extraId']);

                $duration += $extra->getDuration() ? $extra->getDuration()->getValue() * $bookingExtra['quantity'] : 0;
            }
        } else {
            $maxBookingDuration = 0;

            foreach ($appointmentArray['bookings'] as $booking) {
                $bookingDuration = $booking['duration'] ? $booking['duration'] : $duration;

                foreach ($booking['extras'] as $bookingExtra) {
                    /** @var Extra $extra */
                    $extra = $extras->getItem($bookingExtra['extraId']);

                    $bookingDuration += $extra->getDuration() ?
                        $extra->getDuration()->getValue() * $bookingExtra['quantity'] : 0;
                }

                if ($bookingDuration > $maxBookingDuration &&
                    ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING)
                ) {
                    $maxBookingDuration = $bookingDuration;
                }
            }

            $duration = $maxBookingDuration;
        }

        $data['appointment_duration'] = $helperService->secondsToNiceDuration($duration);

        /** @var string $break */
        $break = $type === 'whatsapp' ? '; ' : PHP_EOL;

        $lastBookingExtraIds = [];
        if ($bookingKey === null) {
            $lastBookingId = $this->getBookingKeyForEmployee($appointmentArray);

            $lastBooking = array_filter(
                $appointmentArray['bookings'],
                function ($b) use ($lastBookingId) {
                    return $b['id'] === $lastBookingId;
                }
            );

            foreach (array_shift($lastBooking)['extras'] as $ex) {
                $lastBookingExtraIds[$ex['extraId']] = $ex;
            }
        }

        $allExtraNames = "";
        $allExtraDetails = "";
        $allExtraSum = 0;
        /** @var Extra $extra */
        foreach ($extras->getItems() as $extra) {
            $extraId = $extra->getId()->getValue();

            $data["service_extra_{$extraId}_name"] =
                array_key_exists($extraId, $bookingExtras) ? $extra->getName()->getValue() : '';

            $data["service_extra_{$extraId}_name"] = $helperService->getBookingTranslation(
                $bookingKey !== null ? $locale : null,
                $data["service_extra_{$extraId}_name"] && $extra->getTranslations() ?
                    $extra->getTranslations()->getValue() : null,
                'name'
            ) ?: $data["service_extra_{$extraId}_name"];

            $data["service_extra_{$extraId}_quantity"] =
                array_key_exists($extraId, $bookingExtras) ? $bookingExtras[$extraId]['quantity'] : '';

            $data["service_extra_{$extraId}_price"] = array_key_exists($extraId, $bookingExtras) ?
                $helperService->getFormattedPrice($extra->getPrice()->getValue()) : '';

            $multiplyByNumberOfPeople = ($extra->getAggregatedPrice() === null ? $service->getAggregatedPrice()->getValue()
                : $extra->getAggregatedPrice()->getValue()) && $persons !== 1;

            if (!empty($data["service_extra_{$extraId}_name"])) {
                $allExtraNames .= $data["service_extra_{$extraId}_name"].', ';
            }

            if (array_key_exists($extraId, $bookingExtras) && $bookingExtras[$extraId]['quantity'] !== 0) {
                if ($bookingKey === null) {
                    if (!array_key_exists($extraId, $lastBookingExtraIds)) {
                        continue;
                    }
                }
                $allExtraDetails .= ($type === 'email' ? '<p>' : '') . $extra->getName()->getValue() . ': (' .
                    $helperService->getFormattedPrice($extra->getPrice()->getValue()) . ' x ' .
                    $bookingExtras[$extraId]['quantity'] . ') ' .
                    ($multiplyByNumberOfPeople ? ('x ' . $persons) : '') .
                    ($type === 'email' ? '</p>' : $break);

                $allExtraSum += $extra->getPrice()->getValue() * $bookingExtras[$extraId]['quantity'] *
                    ($multiplyByNumberOfPeople ? $persons : 1);
            }
        }

        $data["service_extras"]         = substr($allExtraNames, 0, -2);

        $data['deposit']                = $service->getDeposit() && $service->getDeposit()->getValue();

        $data["service_extras_details"] = $allExtraDetails ? ($allExtraDetails . ($type !== 'whatsapp' ?
            ($type === 'email' ? '<p>' : $break) . "-------------------------" . ($type === 'email' ? '</p>' : $break) : '') .
            ($type === 'email' ? '<p>' : '') . BackendStrings::getCommonStrings()['extras_total_price'] .
            " {$helperService->getFormattedPrice($allExtraSum)}" . ($type === 'email' ? '</p>' : '')) : "";

        return $data;
    }

    /**
     * @param $appointment
     * @param $bookingKey
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function getEmployeeData($appointment, $bookingKey = null)
    {
        /** @var HelperService $helperService */
        $helperService = $this->container->get('application.helper.service');

        /** @var UserRepository $userRepository */
        $userRepository = $this->container->get('domain.users.repository');

        /** @var LocationRepository $locationRepository */
        $locationRepository = $this->container->get('domain.locations.repository');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var Provider $user */
        $user = $userRepository->getById($appointment['providerId']);

        $locale = $this->getLocale($appointment, $bookingKey);

        if (!empty($appointment['locationId'])) {
            $locationId = $appointment['locationId'];
        } else {
            $locationId = $user->getLocationId() ? $user->getLocationId()->getValue() : null;
        }

        /** @var Location $location */
        $location = $locationId ? $locationRepository->getById($locationId) : null;

        $locationName = $settingsService->getSetting('company', 'address');

        $locationDescription = '';

        if ($location) {
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
        }

        $firstName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $user->getTranslations() ? $user->getTranslations()->getValue() : null,
            'firstName'
        ) ?: $user->getFirstName()->getValue();

        $lastName = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $user->getTranslations() ? $user->getTranslations()->getValue() : null,
            'lastName'
        ) ?: $user->getLastName()->getValue();

        $userDescription = $helperService->getBookingTranslation(
            $bookingKey !== null ? $locale : null,
            $user->getTranslations() ? $user->getTranslations()->getValue() : null,
            'description'
        ) ?: ($user->getDescription() ? $user->getDescription()->getValue() : '');

        return [
            'employee_id'          => $user->getId()->getValue(),
            'employee_email'       => $user->getEmail()->getValue(),
            'employee_first_name'  => $firstName,
            'employee_last_name'   => $lastName,
            'employee_full_name'   => $firstName . ' ' . $lastName,
            'employee_phone'       => $user->getPhone()->getValue(),
            'employee_note'        => $user->getNote() ? $user->getNote()->getValue() : '',
            'employee_description' => $userDescription,
            'employee_panel_url'  => trim(
                $this->container->get('domain.settings.service')
                ->getSetting('roles', 'providerCabinet')['pageUrl']
            ),
            'location_address'     => !$location ?
                $settingsService->getSetting('company', 'address') : $location->getAddress()->getValue(),
            'location_phone'       => !$location ?
                $settingsService->getSetting('company', 'phone') : $location->getPhone()->getValue(),
            'location_id'          => $locationId,
            'location_name'        => $locationName,
            'location_description' => $locationDescription,
            'location_latitude'    => $location && $location->getCoordinates() ? $location->getCoordinates()->getLatitude() : null,
            'location_longitude'   => $location && $location->getCoordinates() ? $location->getCoordinates()->getLongitude() : null,
        ];
    }

    /**
     * @param array  $appointment
     * @param int    $bookingKey
     * @param string $type
     * @param string $placeholderType
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getRecurringAppointmentsData($appointment, $bookingKey, $type, $placeholderType, $bookingKeyForEmployee = null)
    {
        if (!array_key_exists('recurring', $appointment)) {
            return [
                "{$placeholderType}_appointments_details" => ''
            ];
        }

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.appointment.service");

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $appointmentsSettings = $settingsService->getCategorySettings('appointments');

        $recurringAppointmentDetails = [];

        foreach ($appointment['recurring'] as $recurringData) {
            $recurringBookingKey = null;
            $recurringBookingKeyForEmployee = null;

            $isForCustomer =
                $bookingKey !== null ||
                (isset($appointment['isForCustomer']) && $appointment['isForCustomer']);

            if ($isForCustomer) {
                foreach ($recurringData['appointment']['bookings'] as $key => $recurringBooking) {
                    if (isset($recurringData['booking']['id'])) {
                        if ($recurringBooking['id'] === $recurringData['booking']['id']) {
                            $recurringBookingKey = $key;
                        }
                    } else if ($recurringBooking['customerId'] === $appointment['bookings'][$bookingKey]['customerId']) {
                        $recurringBookingKey = $key;
                    }
                }

                if ($recurringBookingKey === null) {
                    return [];
                }
            } elseif ($bookingKeyForEmployee !== null) {
                foreach ($recurringData['appointment']['bookings'] as $key => $recurringBooking) {
                    if (isset($recurringData['booking']['id'])) {
                        if ($recurringBooking['id'] === $recurringData['booking']['id']) {
                            $recurringBookingKeyForEmployee = $key;
                        }
                    } else {
                        $recurringBookingKeyForEmployee = $bookingKeyForEmployee;
                    }
                }
            }

            $token =
                $recurringBookingKey !== null &&
                isset(
                    $recurringData['appointment']['bookings'][$recurringBookingKey],
                    $recurringData['appointment']['bookings'][$recurringBookingKey]['id']
                ) ? $bookingRepository->getToken($recurringData['appointment']['bookings'][$recurringBookingKey]['id']) :(
                    $recurringBookingKeyForEmployee !== null &&
                    isset(
                        $recurringData['appointment']['bookings'][$recurringBookingKeyForEmployee],
                        $recurringData['appointment']['bookings'][$recurringBookingKeyForEmployee]['id']
                    ) ? $bookingRepository->getToken($recurringData['appointment']['bookings'][$recurringBookingKeyForEmployee]['id']) :
            null);

            $recurringPlaceholders = [];

            $recurringPlaceholders = array_merge(
                $recurringPlaceholders,
                $this->getEmployeeData($recurringData['appointment'], $recurringBookingKey)
            );

            $recurringPlaceholders = array_merge(
                $recurringPlaceholders,
                $this->getAppointmentData($recurringData['appointment'], $recurringBookingKey, $type)
            );

            $recurringPlaceholders = array_merge(
                $recurringPlaceholders,
                $this->getServiceData($recurringData['appointment'], $recurringBookingKey, $type)
            );

            $recurringPlaceholders = array_merge(
                $recurringPlaceholders,
                $this->getCustomFieldsData($recurringData['appointment'], $type, $recurringBookingKey)
            );

            $recurringPlaceholders = array_merge(
                $recurringPlaceholders,
                $this->getBookingData(
                    $recurringData['appointment'],
                    $type,
                    $recurringBookingKey,
                    isset($token['token']) ? $token['token'] : null,
                    $recurringPlaceholders['deposit']
                )
            );

            unset($recurringPlaceholders['icsFiles']);

            if (!$isForCustomer) {
                if (isset($recurringPlaceholders['appointment_cancel_url'])) {
                    $recurringPlaceholders['appointment_cancel_url'] = '';
                }

                $recurringPlaceholders['zoom_join_url'] = '';
            } else {
                $recurringPlaceholders['employee_panel_url'] = '';

                $recurringPlaceholders['zoom_host_url'] = '';
            }

            $placeholderString =
                $placeholderType .
                'Placeholders' .
                ($isForCustomer && $placeholderType === 'package' ? 'Customer' : '') .
                ($isForCustomer && $placeholderType === 'recurring' ? 'Customer' : '') .
                ($isForCustomer && $placeholderType === 'cart' ? 'Customer' : '') .
                ($type === 'email' ? '' : 'Sms');

            /** @var HelperService $helperService */
            $helperService = $this->container->get('application.helper.service');

            $content = $helperService->getBookingTranslation(
                $recurringBookingKey !== null ? $helperService->getLocaleFromBooking($recurringData['appointment']['bookings'][$recurringBookingKey]['info']) : null,
                json_encode($appointmentsSettings['translations']),
                $placeholderString
            ) ?: $appointmentsSettings[$placeholderString];

            if ($type === 'whatsapp') {
                $content = str_replace(array("\n","\r"), '; ', $content);
                $content = preg_replace('!\s+!', ' ', $content);
            }

            $recurringAppointmentDetails[] = $placeholderService->applyPlaceholders(
                $content,
                $recurringPlaceholders
            );
        }

        return [
            "{$placeholderType}_appointments_details" => $recurringAppointmentDetails ? implode(
                $type === 'email' ? '<p><br></p>' :  ($type === 'whatsapp' ? '; ' : PHP_EOL),
                $recurringAppointmentDetails
            ) : ''
        ];
    }

    /**
     * @param array  $appointment
     * @param int    $bookingKey
     * @param string $type
     *
     * @return array
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public function getGroupedAppointmentData($appointment, $bookingKey, $type)
    {
        /** @var PlaceholderService $placeholderService */
        $placeholderService = $this->container->get("application.placeholder.appointment.service");

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $appointmentsSettings = $settingsService->getCategorySettings('appointments');

        $groupAppointmentDetails = [];

        if ($bookingKey) {
            return [
                "group_appointment_details" => ''
            ];
        }

        foreach ($appointment['bookings'] as $bookingId => $booking) {
            if ($booking['status'] === BookingStatus::CANCELED || $booking['status'] === BookingStatus::REJECTED || $booking['status'] === BookingStatus::NO_SHOW) {
                continue;
            }

            /** @var CustomerBookingRepository $bookingRepository */
            $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

            $token = $bookingRepository->getToken($appointment['bookings'][$bookingId]['id']);

            $groupPlaceholders = array_merge(
                $this->getAppointmentData($appointment, $bookingId, $type),
                $this->getServiceData($appointment, $bookingId, $type),
                $this->getCustomFieldsData($appointment, $type, $bookingId),
                $this->getCustomersData($appointment, $type, $bookingId),
                $this->getBookingData(
                    $appointment,
                    $type,
                    $bookingId,
                    isset($token['token']) ? $token['token'] : null,
                    null,
                    true
                )
            );

            $content = $appointmentsSettings['groupAppointmentPlaceholder' . ($type === null || $type === 'email' ? '' : 'Sms')] ;
            if ($type === 'email') {
                $content = str_replace(array("\n","\r"), '', $content);
            } else if ($type === 'whatsapp') {
                $content = str_replace(array("\n","\r"), '; ', $content);
                $content = preg_replace('!\s+!', ' ', $content);
            }

            $groupAppointmentDetails[] = $placeholderService->applyPlaceholders(
                $content,
                $groupPlaceholders
            );
        }

        return [
            "group_appointment_details" => $groupAppointmentDetails ? implode(
                $type === 'email' ? '<p><br></p>' :  ($type === 'whatsapp' ? '; ' : PHP_EOL),
                $groupAppointmentDetails
            ) : ''
        ];
    }

    /**
     * @param array $bookingArray
     * @param array $entity
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function getAmountData(&$bookingArray, $entity)
    {
        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get(Entities::APPOINTMENT);

        if (!empty($bookingArray['couponId']) && empty($bookingArray['coupon'])) {
            /** @var CouponRepository $couponRepository */
            $couponRepository = $this->container->get('domain.coupon.repository');

            /** @var Coupon $coupon */
            $coupon = $couponRepository->getById($bookingArray['couponId']);

            $bookingArray['coupon'] = $coupon ? $coupon->toArray() : null;
        }

        $extras = [];

        foreach ($bookingArray['extras'] as $extra) {
            $extras[$extra['extraId']] = [
                'price'           => $extra['price'],
                'aggregatedPrice' => !!$extra['aggregatedPrice'],
            ];
        }

        /** @var AbstractBookable $bookable */
        $bookable = ServiceFactory::create(
            [
                'price'           => $bookingArray['price'],
                'aggregatedPrice' => !!$bookingArray['aggregatedPrice'],
                'extras'          => $extras,
            ]
        );

        /** @var CustomerBooking $booking */
        $booking = CustomerBookingFactory::create(
            [
                'persons' => $bookingArray['persons'],
                'coupon'  => $bookingArray['coupon'],
                'extras'  => $bookingArray['extras'],
                'tax'     => $bookingArray['tax'],
            ]
        );

        return [
            'price'     => $reservationService->getPaymentAmount($booking, $bookable),
            'discount'  => $reservationService->getPaymentAmount($booking, $bookable, 'discount'),
            'deduction' => $reservationService->getPaymentAmount($booking, $bookable, 'deduction'),
        ];
    }
}
