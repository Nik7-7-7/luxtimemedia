<?php
/**
 * @copyright © TMS-Plugins. All rights reserved.
 * @licence   See LICENCE.md for license details.
 */

namespace AmeliaBooking\Application\Commands\Payment;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Application\Services\Booking\BookingApplicationService;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class CalculatePaymentAmountCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Payment
 */
class CalculatePaymentAmountCommandHandler extends CommandHandler
{
    /**
     * @param CalculatePaymentAmountCommand $command
     *
     * @return CommandResult
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws ContainerException
     * @throws Exception
     */
    public function handle(CalculatePaymentAmountCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get($command->getField('type'));

        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        $reservation = $reservationService->getNew(true, true, true);

        $reservationService->processBooking(
            $result,
            $bookingAS->getAppointmentData($command->getFields()),
            $reservation,
            false
        );

        $paymentAmount = $reservationService->getReservationPaymentAmount($reservation);

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setData(
            [
                'amount'   => $paymentAmount,
                'currency' => $settingsService->getCategorySettings('payments')['currency'],
            ]
        );

        return $result;
    }
}
