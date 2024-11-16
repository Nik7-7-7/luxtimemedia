<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Notification\EmailNotificationService;
use AmeliaBooking\Application\Services\Notification\SMSNotificationService;
use AmeliaBooking\Application\Services\Notification\AbstractWhatsAppNotificationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Application\Services\WebHook\AbstractWebHookApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Services\Google\AbstractGoogleCalendarService;
use AmeliaBooking\Infrastructure\Services\Outlook\AbstractOutlookCalendarService;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class BookingEditedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Appointment
 */
class BookingEditedEventHandler
{
    /** @var string */
    const BOOKING_STATUS_UPDATED = 'bookingStatusUpdated';

    /** @var string */
    const BOOKING_CANCELED = 'bookingCanceled';

    /** @var string */
    const BOOKING_ADDED = 'bookingAdded';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
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
        /** @var AbstractWhatsAppNotificationService $whatsAppNotificationService */
        $whatsAppNotificationService = $container->get('application.whatsAppNotification.service');
        /** @var SettingsService $settingsService */
        $settingsService = $container->get('domain.settings.service');
        /** @var AbstractWebHookApplicationService $webHookService */
        $webHookService = $container->get('application.webHook.service');
        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $container->get('application.payment.service');

        $appointment = $commandResult->getData()[$commandResult->getData()['type']];
        $booking     = $commandResult->getData()[Entities::BOOKING];
        $bookingStatusChanged = $commandResult->getData()['bookingStatusChanged'];

        if ($bookingStatusChanged) {
            /** @var EventRepository $eventRepository */
            $eventRepository   = $container->get('domain.booking.event.repository');
            $reservationObject = $eventRepository->getById($appointment['id']);

            if ($commandResult->getData()['createPaymentLinks']) {
                $paymentId    = $booking['payments'][0]['id'];
                $paymentData  = [
                    'booking' => $booking,
                    'type' => Entities::EVENT,
                    'event' => $appointment,
                    'paymentId' => $paymentId,
                    'bookable' => $reservationObject->toArray(),
                    'customer' => $booking['customer']
                ];
                $bookingIndex = array_search($booking['id'], array_column($appointment['bookings'], 'id'));
                if ($bookingIndex !== false && !empty($paymentId)) {
                    $appointment['bookings'][$bookingIndex]['payments'][0]['paymentLinks'] = $paymentAS->createPaymentLink($paymentData, $bookingIndex);
                }
            }


            if ($googleCalendarService) {
                if ($booking['status'] === BookingStatus::APPROVED) {
                    $googleCalendarService->handleEventPeriodsChange($reservationObject, self::BOOKING_ADDED, $reservationObject->getPeriods());
                } else if ($booking['status'] === BookingStatus::CANCELED || $booking['status'] === BookingStatus::REJECTED) {
                    $googleCalendarService->handleEventPeriodsChange($reservationObject, self::BOOKING_CANCELED, $reservationObject->getPeriods());
                }
            }

            if ($outlookCalendarService) {
                if ($booking['status'] === BookingStatus::APPROVED) {
                    $outlookCalendarService->handleEventPeriod($reservationObject, self::BOOKING_ADDED, $reservationObject->getPeriods());
                } else if ($booking['status'] === BookingStatus::CANCELED || $booking['status'] === BookingStatus::REJECTED) {
                    $outlookCalendarService->handleEventPeriod($reservationObject, self::BOOKING_CANCELED, $reservationObject->getPeriods());
                }
            }
            $emailNotificationService->sendCustomerBookingNotification($appointment, $booking);
            $emailNotificationService->sendProviderBookingNotification($appointment, $booking);

            if ($settingsService->getSetting('notifications', 'smsSignedIn') === true) {
                $smsNotificationService->sendCustomerBookingNotification($appointment, $booking);
                $smsNotificationService->sendProviderBookingNotification($appointment, $booking);
            }

            if ($whatsAppNotificationService->checkRequiredFields()) {
                $whatsAppNotificationService->sendCustomerBookingNotification($appointment, $booking);
                $whatsAppNotificationService->sendProviderBookingNotification($appointment, $booking);
            }

            $webHookService->process(self::BOOKING_STATUS_UPDATED, $appointment, [$booking]);
        }
    }
}
