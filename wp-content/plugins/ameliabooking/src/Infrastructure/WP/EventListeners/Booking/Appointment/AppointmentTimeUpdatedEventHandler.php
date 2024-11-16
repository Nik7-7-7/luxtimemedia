<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Application\Services\Notification\SMSNotificationService;
use AmeliaBooking\Application\Services\Notification\AbstractWhatsAppNotificationService;
use AmeliaBooking\Application\Services\WebHook\AbstractWebHookApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Google\AbstractGoogleCalendarService;
use AmeliaBooking\Application\Services\Zoom\AbstractZoomApplicationService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\AbstractLessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\AbstractOutlookCalendarService;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class AppointmentTimeUpdatedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class AppointmentTimeUpdatedEventHandler
{
    /** @var string */
    const TIME_UPDATED = 'bookingTimeUpdated';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws ContainerValueNotFoundException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
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

        /** @var Appointment|Event $reservationObject */
        $reservationObject = AppointmentFactory::create($commandResult->getData()[Entities::APPOINTMENT]);

        /** @var Collection $appointments */
        $appointments = new Collection();

        $bookingApplicationService->setAppointmentEntities($reservationObject, $appointments);

        $appointment = $reservationObject->toArray();

        if ($zoomService) {
            $zoomService->handleAppointmentMeeting($reservationObject, self::TIME_UPDATED);

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

        try {
            $googleCalendarService->handleEvent($reservationObject, self::TIME_UPDATED);
        } catch (Exception $e) {
        }

        if ($reservationObject->getGoogleCalendarEventId() !== null) {
            $appointment['googleCalendarEventId'] = $reservationObject->getGoogleCalendarEventId()->getValue();
        }

        if ($reservationObject->getGoogleMeetUrl() !== null) {
            $appointment['googleMeetUrl'] = $reservationObject->getGoogleMeetUrl();
        }

        try {
            $outlookCalendarService->handleEvent($reservationObject, self::TIME_UPDATED);
        } catch (Exception $e) {
        }

        if ($reservationObject->getOutlookCalendarEventId() !== null) {
            $appointment['outlookCalendarEventId'] = $reservationObject->getOutlookCalendarEventId()->getValue();
        }

        $appointment['initialAppointmentDateTime'] = $commandResult->getData()['initialAppointmentDateTime'];

        $emailNotificationService->sendAppointmentRescheduleNotifications($appointment);

        if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
            $smsNotificationService->sendAppointmentRescheduleNotifications($appointment);
        }

        if ($whatsAppNotificationService->checkRequiredFields()) {
            $whatsAppNotificationService->sendAppointmentRescheduleNotifications($appointment);
        }

        $webHookService->process(self::TIME_UPDATED, $appointment, []);
    }
}
