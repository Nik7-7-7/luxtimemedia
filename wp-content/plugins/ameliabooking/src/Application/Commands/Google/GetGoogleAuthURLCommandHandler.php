<?php

namespace AmeliaBooking\Application\Commands\Google;

use AmeliaBooking\Application\Commands\CommandHandler;
use AmeliaBooking\Application\Commands\CommandResult;
use AmeliaBooking\Infrastructure\Services\Google\AbstractGoogleCalendarService;
use Interop\Container\Exception\ContainerException;

/**
 * Class GetGoogleAuthURLCommandHandler
 *
 * @package AmeliaBooking\Application\Commands\Google
 */
class GetGoogleAuthURLCommandHandler extends CommandHandler
{
    /**
     * @param GetGoogleAuthURLCommand $command
     *
     * @return CommandResult
     * @throws ContainerException
     */
    public function handle(GetGoogleAuthURLCommand $command)
    {
        $result = new CommandResult();

        /** @var AbstractGoogleCalendarService $googleCalendarService */
        $googleCalendarService = $this->container->get('infrastructure.google.calendar.service');

        $authUrl = $googleCalendarService->createAuthUrl(
            (int)$command->getField('id'),
            $command->getField('redirectUri')
        );

        $authUrl = apply_filters('amelia_get_google_calendar_auth_url_filter', $authUrl, $command->getField('id'));

        do_action('amelia_get_google_calendar_auth_url', $authUrl, $command->getField('id'));

        $result->setResult(CommandResult::RESULT_SUCCESS);
        $result->setMessage('Successfully retrieved google authorization URL');
        $result->setData(
            [
                 'authUrl' => filter_var($authUrl, FILTER_SANITIZE_URL)
            ]
        );

        return $result;
    }
}
