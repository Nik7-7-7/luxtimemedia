<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event;

use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Factory\Booking\Event\EventFactory;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Application\Services\Zoom\AbstractZoomApplicationService;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Google\AbstractGoogleCalendarService;
use AmeliaBooking\Infrastructure\Services\LessonSpace\AbstractLessonSpaceService;
use AmeliaBooking\Infrastructure\Services\Outlook\AbstractOutlookCalendarService;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class EventAddedEventHandler
 *
 * @package AmeliaBooking\Infrastructure\WP\EventListeners\Booking\Event
 */
class EventAddedEventHandler
{
    /** @var string */
    const EVENT_ADDED = 'eventAdded';

    /**
     * @param CommandResult $commandResult
     * @param Container     $container
     *
     * @throws NotFoundException
     * @throws InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws Exception
     */
    public static function handle($commandResult, $container)
    {
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $container->get('application.booking.booking.service');
        /** @var AbstractZoomApplicationService $zoomService */
        $zoomService = $container->get('application.zoom.service');
        /** @var AbstractLessonSpaceService $lessonSpaceService */
        $lessonSpaceService = $container->get('infrastructure.lesson.space.service');
        /** @var AbstractGoogleCalendarService $googleCalendarService */
        $googleCalendarService = $container->get('infrastructure.google.calendar.service');
        /** @var AbstractOutlookCalendarService $outlookCalendarService */
        $outlookCalendarService = $container->get('infrastructure.outlook.calendar.service');


        $events = $commandResult->getData()[Entities::EVENTS];

        foreach ($events as $event) {
            /** @var Event $reservationObject */
            $reservationObject = EventFactory::create($event);

            $bookingApplicationService->setReservationEntities($reservationObject);

            if ($zoomService) {
                $zoomService->handleEventMeeting($reservationObject, $reservationObject->getPeriods(), self::EVENT_ADDED);
            }
            if ($lessonSpaceService) {
                $lessonSpaceService->handle($reservationObject, Entities::EVENT, $reservationObject->getPeriods());
            }
            if ($googleCalendarService) {
                try {
                    $googleCalendarService->handleEventPeriodsChange($reservationObject, self::EVENT_ADDED, $reservationObject->getPeriods());
                } catch (Exception $e) {
                }
            }

            if ($outlookCalendarService) {
                try {
                    $outlookCalendarService->handleEventPeriod($reservationObject, self::EVENT_ADDED, $reservationObject->getPeriods());
                } catch (Exception $e) {
                }
            }
        }
    }
}
