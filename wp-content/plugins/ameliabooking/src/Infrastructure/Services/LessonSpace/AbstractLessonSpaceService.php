<?php

namespace AmeliaBooking\Infrastructure\Services\LessonSpace;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Infrastructure\Routes\Booking\Event\Event;
use Interop\Container\Exception\ContainerException;

abstract class AbstractLessonSpaceService
{
    /**
     * @param Appointment|Event $appointment
     * @param int $entity
     * @param Collection $periods
     * @param array $booking
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws ContainerException
     */
    abstract public function handle($appointment, $entity, $periods = null, $booking = null);

    /**
     * @param $apiKey
     *
     * @return array
     *
     */
    abstract public function getCompanyId($apiKey);

    /**
     * @param $apiKey
     * @param $companyId
     * @param null $searchTerm
     *
     * @return array
     */
    abstract public function getAllSpaces($apiKey, $companyId, $searchTerm = null);

    /**
     * @param $apiKey
     * @param $companyId
     * @param $spaceId
     *
     * @return array
     */
    abstract public function getSpaceUsers($apiKey, $companyId, $spaceId);

    /**
     * @param $apiKey
     * @param $companyId
     * @param $spaceId
     *
     * @return array
     */
    abstract public function getSpace($apiKey, $companyId, $spaceId);

    /**
     * @param $apiKey
     * @param $companyId
     *
     * @return array
     */
    abstract public function getAllTeachers($apiKey, $companyId);

    /**
     * @param $lessonSpaceApiKey
     * @param $data
     * @param $requestUrl
     * @param $method
     *
     * @return array
     */
    abstract public function execute($lessonSpaceApiKey, $data, $requestUrl, $method);
}
