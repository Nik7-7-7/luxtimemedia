<?php

namespace AmeliaBooking\Application\Commands\User;

use AmeliaBooking\Application\Common\Exceptions\AccessDeniedException;
use AmeliaBooking\Application\Services\User\UserApplicationService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\WP\Translations\BackendStrings;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class GetUserDeleteEffectCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\User
 */
class GetUserDeleteEffectCommandHandler extends CommandHandler
{
    /**
     * @param GetUserDeleteEffectCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws AccessDeniedException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     * @throws ContainerException
     */
    public function handle(GetUserDeleteEffectCommand $command)
    {
        if (!$command->getPermissionService()->currentUserCanRead(Entities::EMPLOYEES) &&
            !$command->getPermissionService()->currentUserCanRead(Entities::CUSTOMERS)
        ) {
            throw new AccessDeniedException('You are not allowed to read user');
        }

        $result = new CommandResult();

        /** @var UserApplicationService $userAS */
        $userAS = $this->getContainer()->get('application.user.service');

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        $appointmentsCount = $userAS->getAppointmentsCountForUser($command->getArg('id'));

        /** @var Collection $events */
        $events = $eventRepository->getFiltered(
            [
                'customerId'    => $command->getArg('id'),
                'bookingStatus' => BookingStatus::APPROVED,
                'dates'         => [DateTimeService::getNowDateTime()],
            ]
        );

        $message = '';

        if ($appointmentsCount['futureAppointments'] > 0) {
            $appointmentString = $appointmentsCount['futureAppointments'] === 1 ? 'appointment' : 'appointments';

            $message = "Could not delete user.
                This user has {$appointmentsCount['futureAppointments']} {$appointmentString} in the future.";
        } elseif ($appointmentsCount['packageAppointments']) {
            $message = "This user has bookings in purchased package.
                Are you sure you want to delete this user?";
        } elseif ($appointmentsCount['pastAppointments'] > 0) {
            $appointmentString = $appointmentsCount['pastAppointments'] === 1 ? 'appointment' : 'appointments';

            $message = "This user has {$appointmentsCount['pastAppointments']} {$appointmentString} in the past.";
        } elseif ($events->length()) {
            $eventString = $events->length() > 1 ? 'events' : 'event';

            $message = "This user is an attendee in future {$eventString}.
                Are you sure you want to delete this user?";
        }

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved message.');
        $result->setData(
            [
                'valid'   => $appointmentsCount['futureAppointments'] ? false : true,
                'message' => $message
            ]
        );

        return $result;
    }
}
