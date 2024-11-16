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
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Application\Services\WebHook\AbstractWebHookApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Google\AbstractGoogleCalendarService;
use AmeliaBooking\Application\Services\Zoom\AbstractZoomApplicationService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\AbstractLessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\AbstractOutlookCalendarService;
use Interop\Container\Exception\ContainerException;

/**
 * Class AppointmentAddedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class AppointmentAddedEventHandler
{
    /** @var string */
    const APPOINTMENT_ADDED = 'appointmentAdded';

    /** @var string */
    const BOOKING_ADDED = 'bookingAdded';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws \Exception
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
        /** @var SettingsService $settingsService */
        $settingsService = $container->get('domain.settings.service');
        /** @var AbstractWebHookApplicationService $webHookService */
        $webHookService = $container->get('application.webHook.service');
        /** @var AbstractZoomApplicationService $zoomService */
        $zoomService = $container->get('application.zoom.service');
        /** @var AbstractLessonSpaceService $lessonSpaceService */
        $lessonSpaceService = $container->get('infrastructure.lesson.space.service');
        /** @var AbstractWhatsAppNotificationService $whatsAppNotificationService */
        $whatsAppNotificationService = $container->get('application.whatsAppNotification.service');
        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $container->get('application.payment.service');
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $container->get('application.booking.booking.service');

        $recurringData = $commandResult->getData()['recurring'];

        $appointment = $commandResult->getData()[Entities::APPOINTMENT];

        /** @var Appointment $appointmentObject */
        $appointmentObject = AppointmentFactory::create($appointment);

        /** @var Collection $appointments */
        $appointments = new Collection();

        $bookingApplicationService->setAppointmentEntities($appointmentObject, $appointments);

        $appointments->addItem($appointmentObject, $appointmentObject->getId()->getValue(), true);

        $pastAppointment = $appointmentObject->getBookingStart()->getValue() < DateTimeService::getNowDateTimeObject();

        if ($zoomService && !$pastAppointment) {
            $zoomService->handleAppointmentMeeting($appointmentObject, self::APPOINTMENT_ADDED);

            if ($appointmentObject->getZoomMeeting()) {
                $appointment['zoomMeeting'] = $appointmentObject->getZoomMeeting()->toArray();
            }
        }

        if ($lessonSpaceService && !$pastAppointment) {
            $lessonSpaceService->handle($appointmentObject, Entities::APPOINTMENT);
            if ($appointmentObject->getLessonSpace()) {
                $appointment['lessonSpace'] = $appointmentObject->getLessonSpace();
            }
        }

        if ($googleCalendarService) {
            try {
                $googleCalendarService->handleEvent($appointmentObject, self::APPOINTMENT_ADDED);
            } catch (\Exception $e) {
            }

            if ($appointmentObject->getGoogleCalendarEventId() !== null) {
                $appointment['googleCalendarEventId'] = $appointmentObject->getGoogleCalendarEventId()->getValue();
            }
            if ($appointmentObject->getGoogleMeetUrl() !== null) {
                $appointment['googleMeetUrl'] = $appointmentObject->getGoogleMeetUrl();
            }
        }

        if ($outlookCalendarService) {
            try {
                $outlookCalendarService->handleEvent($appointmentObject, self::APPOINTMENT_ADDED);
            } catch (\Exception $e) {
            }

            if ($appointmentObject->getOutlookCalendarEventId() !== null) {
                $appointment['outlookCalendarEventId'] = $appointmentObject->getOutlookCalendarEventId()->getValue();
            }
        }

        foreach ($recurringData as $key => $recurringReservationData) {
            /** @var Appointment $recurringReservationObject */
            $recurringReservationObject = AppointmentFactory::create($recurringReservationData[Entities::APPOINTMENT]);

            $bookingApplicationService->setAppointmentEntities($recurringReservationObject, $appointments);

            $appointments->addItem($recurringReservationObject, $recurringReservationObject->getId()->getValue(), true);

            $pastRecurringAppointment = $recurringReservationObject->getBookingStart()->getValue() < DateTimeService::getNowDateTimeObject();

            if ($zoomService && !$pastRecurringAppointment) {
                $zoomService->handleAppointmentMeeting($recurringReservationObject, self::BOOKING_ADDED);

                if ($recurringReservationObject->getZoomMeeting()) {
                    $recurringData[$key][Entities::APPOINTMENT]['zoomMeeting'] =
                        $recurringReservationObject->getZoomMeeting()->toArray();
                }
            }

            if ($lessonSpaceService && !$pastRecurringAppointment) {
                $lessonSpaceService->handle($recurringReservationObject, Entities::APPOINTMENT);
                if ($recurringReservationObject->getLessonSpace()) {
                    $recurringData[$key][Entities::APPOINTMENT]['lessonSpace'] = $recurringReservationObject->getLessonSpace();
                }
            }

            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEvent($recurringReservationObject, self::BOOKING_ADDED);
                } catch (\Exception $e) {
                }

                if ($recurringReservationObject->getGoogleCalendarEventId() !== null) {
                    $recurringData[$key][Entities::APPOINTMENT]['googleCalendarEventId'] =
                        $recurringReservationObject->getGoogleCalendarEventId()->getValue();
                }
                if ($recurringReservationObject->getGoogleMeetUrl() !== null) {
                    $recurringData[$key][Entities::APPOINTMENT]['googleMeetUrl'] =
                        $recurringReservationObject->getGoogleMeetUrl();
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEvent($recurringReservationObject, self::BOOKING_ADDED);
                } catch (\Exception $e) {
                }

                if ($recurringReservationObject->getOutlookCalendarEventId() !== null) {
                    $recurringData[$key][Entities::APPOINTMENT]['outlookCalendarEventId'] =
                        $recurringReservationObject->getOutlookCalendarEventId()->getValue();
                }
            }
        }

        $appointment['recurring'] = $recurringData;

        if (!$pastAppointment) {
            /** @var IcsApplicationService $icsService */
            $icsService = $container->get('application.ics.service');

            foreach ($appointment['bookings'] as $index => $booking) {
                if ($booking['status'] === BookingStatus::APPROVED || $booking['status'] === BookingStatus::PENDING) {
                    $appointment['bookings'][$index]['icsFiles'] = $icsService->getCustomerAppointmentsIcsCalendars(
                        $booking['customerId'],
                        $appointments
                    );

                    $paymentId = !empty($booking['payments'][0]['id']) ? $booking['payments'][0]['id'] : null;

                    $data = [
                        'booking' => $booking,
                        'type' => Entities::APPOINTMENT,
                        'appointment' => $appointmentObject->toArray(),
                        'paymentId' => $paymentId,
                        'bookable' => $appointmentObject->getService()->toArray(),
                        'customer' => $booking['customer']
                    ];

                    if (!empty($paymentId)) {
                        $appointment['bookings'][$index]['payments'][0]['paymentLinks'] = $paymentAS->createPaymentLink($data, $index);
                    }
                }
            }

            $emailNotificationService->sendAppointmentStatusNotifications($appointment, false, true, true);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendAppointmentStatusNotifications($appointment, false, true);
            }

            if ($whatsAppNotificationService->checkRequiredFields()) {
                $whatsAppNotificationService->sendAppointmentStatusNotifications($appointment, false, true);
            }
        }

        $firstAppointmentCustomersIds = array_column($appointment['bookings'], 'customerId');

        foreach ($recurringData as $key => $recurringReservationData) {
            /** @var Appointment $recurringReservationObject */
            $recurringReservationObject = AppointmentFactory::create($recurringReservationData[Entities::APPOINTMENT]);

            $pastRecurringAppointment =  $recurringReservationObject->getBookingStart()->getValue() < DateTimeService::getNowDateTimeObject();

            if ($recurringReservationData[Entities::APPOINTMENT]['isChangedStatus'] === true && !$pastRecurringAppointment) {
                foreach ($recurringReservationData[Entities::APPOINTMENT]['bookings'] as $bookingKey => $recurringReservationBooking) {
                    if (in_array($recurringReservationBooking['customerId'], $firstAppointmentCustomersIds)) {
                        $recurringData[$key][Entities::APPOINTMENT]['bookings'][$bookingKey]['skipNotification'] = true;
                    }
                }

                $emailNotificationService->sendAppointmentStatusNotifications(
                    $recurringData[$key][Entities::APPOINTMENT],
                    true,
                    true
                );

                if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                    $smsNotificationService->sendAppointmentStatusNotifications(
                        $recurringData[$key][Entities::APPOINTMENT],
                        true,
                        true
                    );
                }

                if ($whatsAppNotificationService->checkRequiredFields()) {
                    $whatsAppNotificationService->sendAppointmentStatusNotifications(
                        $recurringData[$key][Entities::APPOINTMENT],
                        true,
                        true
                    );
                }
            }
        }

        $webHookService->process(self::BOOKING_ADDED, $appointment, $appointment['bookings']);

        foreach ($recurringData as $key => $recurringReservationData) {
            $webHookService->process(
                self::BOOKING_ADDED,
                $recurringReservationData[Entities::APPOINTMENT],
                $recurringReservationData[Entities::APPOINTMENT]['bookings']
            );
        }
    }
}
