<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Booking\IcsApplicationService;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Application\Services\Notification\SMSNotificationService;
use AmeliaBooking\Application\Services\Notification\AbstractWhatsAppNotificationService;
use AmeliaBooking\Application\Services\WebHook\AbstractWebHookApplicationService;
use AmeliaBooking\Application\Services\Zoom\AbstractZoomApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Google\AbstractGoogleCalendarService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\AbstractLessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\AbstractOutlookCalendarService;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class AppointmentStatusUpdatedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class AppointmentStatusUpdatedEventHandler
{
    /** @var string */
    const APPOINTMENT_STATUS_UPDATED = 'appointmentStatusUpdated';

    /** @var string */
    const BOOKING_STATUS_UPDATED = 'bookingStatusUpdated';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public static function handle($commandResult, $container)
    {
        /** @var AbstractGoogleCalendarService $googleCalendarService */
        $googleCalendarService = $container->get('infrastructure.google.calendar.service');
        /** @var AbstractOutlookCalendarService $outlookCalendarService */
        $outlookCalendarService = $container->get('infrastructure.outlook.calendar.service');
        /** @var EmailNotificationService $emailNotificationService */
        $emailNotificationService = $container->get('application.emailNotification.service');
        /** @var SMSNotificationService $smsNotificationService */
        $smsNotificationService = $container->get('application.smsNotification.service');
        /** @var AbstractWhatsAppNotificationService $whatsAppNotificationService */
        $whatsAppNotificationService = $container->get('application.whatsAppNotification.service');
        /** @var SettingsService $settingsService */
        $settingsService = $container->get('domain.settings.service');
        /** @var AbstractWebHookApplicationService $webHookService */
        $webHookService = $container->get('application.webHook.service');
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $container->get('application.booking.booking.service');
        /** @var AbstractZoomApplicationService $zoomService */
        $zoomService = $container->get('application.zoom.service');
        /** @var AbstractLessonSpaceService $lessonSpaceService */
        $lessonSpaceService = $container->get('infrastructure.lesson.space.service');

        $appointment = $commandResult->getData()[Entities::APPOINTMENT];

        $oldStatus = $commandResult->getData()['oldStatus'];

        /** @var Appointment|Event $reservationObject */
        $reservationObject = AppointmentFactory::create($appointment);

        $bookingApplicationService->setReservationEntities($reservationObject);

        if ($zoomService) {
            $zoomService->handleAppointmentMeeting($reservationObject, self::APPOINTMENT_STATUS_UPDATED);

            if ($reservationObject->getZoomMeeting()) {
                $appointment['zoomMeeting'] = $reservationObject->getZoomMeeting()->toArray();
            }
        }

        if ($lessonSpaceService) {
            $lessonSpaceService->handle($reservationObject, Entities::APPOINTMENT);
            if ($reservationObject->getLessonSpace()) {
                $appointment['lessonSpace'] = $reservationObject->getLessonSpace();
            }
        }

        $bookings = $commandResult->getData()['bookingsWithChangedStatus'];

        if ($appointment['status'] !== BookingStatus::NO_SHOW) {
            try {
                $googleCalendarService->handleEvent($reservationObject, self::APPOINTMENT_STATUS_UPDATED);
            } catch (Exception $e) {
            }

            if ($reservationObject->getGoogleCalendarEventId() !== null) {
                $appointment['googleCalendarEventId'] = $reservationObject->getGoogleCalendarEventId()->getValue();
            }
            if ($reservationObject->getGoogleMeetUrl() !== null) {
                $appointment['googleMeetUrl'] = $reservationObject->getGoogleMeetUrl();
            }

            try {
                $outlookCalendarService->handleEvent($reservationObject, self::APPOINTMENT_STATUS_UPDATED, $oldStatus);
            } catch (Exception $e) {
            }

            if ($reservationObject->getOutlookCalendarEventId() !== null) {
                $appointment['outlookCalendarEventId'] = $reservationObject->getOutlookCalendarEventId()->getValue();
            }
        }


        // if appointment approved add ics file to bookings
        if ($appointment['status'] === BookingStatus::APPROVED || $appointment['status'] === BookingStatus::PENDING) {
            /** @var IcsApplicationService $icsService */
            $icsService = $container->get('application.ics.service');

            foreach ($appointment['bookings'] as $index => $booking) {
                if ($appointment['bookings'][$index]['isChangedStatus'] === true) {
                    $appointment['bookings'][$index]['icsFiles'] = $icsService->getIcsData(
                        Entities::APPOINTMENT,
                        $booking['id'],
                        [],
                        true
                    );
                }
            }
        }

        $emailNotificationService->sendAppointmentStatusNotifications($appointment, false, true);

        if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
            $smsNotificationService->sendAppointmentStatusNotifications($appointment, false, true);
        }

        if ($whatsAppNotificationService->checkRequiredFields()) {
            $whatsAppNotificationService->sendAppointmentStatusNotifications($appointment, false, true);
        }

        if ($bookings) {
            if ($appointment['status'] === BookingStatus::CANCELED) {
                $webHookService->process(BookingCanceledEventHandler::BOOKING_CANCELED, $appointment, $bookings);
            } else {
                $webHookService->process(self::BOOKING_STATUS_UPDATED, $appointment, $bookings);
            }
        }
    }
}
