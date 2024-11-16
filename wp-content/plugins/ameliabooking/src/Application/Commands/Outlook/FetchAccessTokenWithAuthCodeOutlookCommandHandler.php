<?php

namespace AmeliaBooking\Application\Commands\Outlook;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Factory\Outlook\OutlookCalendarFactory;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Services\Outlook\AbstractOutlookCalendarService;
use AmeliaBooking\Infrastructure\Repository\Outlook\OutlookCalendarRepository;

/**
 * Class FetchAccessTokenWithAuthCodeOutlookCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Outlook
 */
class FetchAccessTokenWithAuthCodeOutlookCommandHandler extends CommandHandler
{
    /** @var array */
    public $mandatoryFields = [
        'authCode',
        'userId'
    ];

    /**
     * @param FetchAccessTokenWithAuthCodeOutlookCommand $command
     *
     * @return CommandResult
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function handle(FetchAccessTokenWithAuthCodeOutlookCommand $command)
    {
        $result = new CommandResult();

        $this->checkMandatoryFields($command);

        /** @var OutlookCalendarRepository $outlookCalendarRepository */
        $outlookCalendarRepository = $this->container->get('domain.outlook.calendar.repository');

        /** @var AbstractOutlookCalendarService $outlookCalendarService */
        $outlookCalendarService = $this->container->get('infrastructure.outlook.calendar.service');

        $token = $outlookCalendarService->fetchAccessTokenWithAuthCode(
            $command->getField('authCode'),
            $command->getField('redirectUri')
        );

        if (!$token['outcome']) {
            $result->setResult(CommandResult::RESULT_ERROR);
            $result->setData($token);
            $result->setMessage($token['result']);

            return $result;
        }

        $token = apply_filters('amelia_before_outlook_calendar_added_filter', $token, $command->getField('userId'));

        $outlookCalendar = OutlookCalendarFactory::create(['token' => $token['result']]);

        $outlookCalendarRepository->beginTransaction();

        do_action('amelia_before_outlook_calendar_added', $outlookCalendar ? $outlookCalendar->toArray() : null, $command->getField('userId'));

        if (!$outlookCalendarRepository->add($outlookCalendar, $command->getField('userId'))) {
            $outlookCalendarRepository->rollback();
        }

        $outlookCalendarRepository->commit();

        do_action('amelia_after_outlook_calendar_added', $outlookCalendar ? $outlookCalendar->toArray() : null, $command->getField('userId'));

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully fetched access token');

        return $result;
    }
}
