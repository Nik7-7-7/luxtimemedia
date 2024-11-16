<?php

namespace AmeliaBooking\Infrastructure\Services\Outlook;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use DateTime;
use Exception;
use Interop\Container\Exception\ContainerException;
use Microsoft\Graph\Exception\GraphException;
use WP_Error;

/**
 * Interface AbstractOutlookCalendarService
 *
 * @package AmeliaBooking\Infrastructure\Services\Outlook
 */
abstract class AbstractOutlookCalendarService
{
    public static $providersOutlookEvents = [];

    /**
     * Create a URL to obtain user authorization.
     *
     * @param $providerId
     *
     * @return string
     * @throws ContainerException
     */
    abstract public function createAuthUrl($providerId);

    /**
     * @return void
     */
    public static function handleCallback()
    {
    }

    /**
     * @param $authCode
     * @param $redirectUri
     *
     * @return array
     */
    abstract public function fetchAccessTokenWithAuthCode($authCode, $redirectUri);

    /**
     * @param Provider $provider
     *
     * @return void
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws Exception
     */
    abstract public function authorizeProvider($provider);

    /**
     * @param Provider $provider
     *
     * @return array
     * @throws ContainerException
     * @throws GraphException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    abstract public function listCalendarList($provider);

    /**
     * Get Provider's Outlook Calendar ID.
     *
     * @param Provider $provider
     *
     * @return null|string
     * @throws GraphException|ContainerException|QueryExecutionException
     * @throws InvalidArgumentException
     */
    abstract public function getProviderOutlookCalendarId($provider);

    /**
     * @param Appointment $appointment
     * @param string      $commandSlug
     * @param null|string $oldStatus
     *
     * @return void
     * @throws ContainerException
     * @throws GraphException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    abstract public function handleEvent($appointment, $commandSlug, $oldStatus = null);

    /**
     * @param \AmeliaBooking\Domain\Entity\Booking\Event\Event $event
     * @param string $commandSlug
     * @param Collection $periods
     *
     * @return void
     * @throws ContainerException
     * @throws GraphException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    abstract public function handleEventPeriod($event, $commandSlug, $periods, $newProviders = null, $removeProviders = null);

    /**
     * Get providers events within date range
     *
     * @param array $providerArr
     * @param string $dateStart
     * @param string $dateStartEnd
     * @param string $dateEnd
     * @param array $eventIds
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     * @throws GraphException
     * @throws Exception
     */
    abstract public function getEvents($providerArr, $dateStart, $dateStartEnd, $dateEnd, $eventIds);


    /**
     * Create fake appointments in provider's list so that these slots will not be available for booking
     *
     * @param Collection $providers
     * @param int        $excludeAppointmentId
     * @param DateTime  $startDateTime
     * @param DateTime  $endDateTime
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws Exception
     * @throws ContainerException
     */
    abstract public function removeSlotsFromOutlookCalendar(
        $providers,
        $excludeAppointmentId,
        $startDateTime,
        $endDateTime
    );
}
