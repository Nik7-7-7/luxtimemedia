<?php

namespace AmeliaBooking\Domain\Services\Resource;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;

/**
 * Class BasicResourceService
 *
 * @package AmeliaBooking\Domain\Services\Resource
 */
class BasicResourceService extends AbstractResourceService
{

    /**
     * set substitute resources instead of resources that are not shred between services/locations
     *
     * @param Collection $resources
     * @param array $entitiesIds
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setNonSharedResources($resources, $entitiesIds)
    {
    }

    /**
     * get collection of resources for service
     *
     * @param Collection $resources
     * @param int $serviceId
     *
     * @return Collection
     * @throws InvalidArgumentException
     */
    public function getServiceResources($resources, $serviceId)
    {
        return new Collection();
    }

    /**
     * get providers id values for resources
     *
     * @param Collection $resources
     *
     * @return array
     */
    public function getResourcesProvidersIds($resources)
    {
        return [];
    }

    /**
     * set unavailable intervals (fake appointments) to providers in moments when resources are used up
     * return intervals of resources with locations that are used up
     *
     * @param Collection $resources
     * @param Collection $appointments
     * @param Collection $allLocations
     * @param Service    $service
     * @param Collection $providers
     * @param int|null   $locationId
     * @param int|null   $excludeAppointmentId
     * @param int        $personsCount
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function manageResources(
        $resources,
        $appointments,
        $allLocations,
        $service,
        $providers,
        $locationId,
        $excludeAppointmentId,
        $personsCount
    ) {
        return [];
    }
}
