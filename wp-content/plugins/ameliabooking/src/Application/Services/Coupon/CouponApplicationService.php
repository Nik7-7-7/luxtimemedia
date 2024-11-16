<?php

namespace AmeliaBooking\Application\Services\Coupon;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\CouponInvalidException;
use AmeliaBooking\Domain\Common\Exceptions\CouponUnknownException;
use AmeliaBooking\Domain\Common\Exceptions\CouponExpiredException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Package;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Event\Event;
use AmeliaBooking\Domain\Entity\Coupon\Coupon;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\WholeNumber;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Event\EventRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponEventRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Coupon\CouponPackageRepository;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class CouponApplicationService
 *
 * @package AmeliaBooking\Application\Services\Coupon
 */
class CouponApplicationService extends AbstractCouponApplicationService
{
    /**
     * @param Coupon $coupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function add($coupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        /** @var CouponServiceRepository $couponServiceRepository */
        $couponServiceRepo = $this->container->get('domain.coupon.service.repository');

        /** @var CouponEventRepository $couponEventRepo */
        $couponEventRepo = $this->container->get('domain.coupon.event.repository');

        /** @var CouponPackageRepository $couponPackageRepo */
        $couponPackageRepo = $this->container->get('domain.coupon.package.repository');

        $couponId = $couponRepository->add($coupon);

        $coupon->setId(new Id($couponId));

        /** @var Service $service */
        foreach ($coupon->getServiceList()->getItems() as $service) {
            $couponServiceRepo->add($coupon, $service);
        }

        /** @var Event $event */
        foreach ($coupon->getEventList()->getItems() as $event) {
            $couponEventRepo->add($coupon, $event);
        }

        /** @var Package $package */
        foreach ($coupon->getPackageList()->getItems() as $package) {
            $couponPackageRepo->add($coupon, $package);
        }

        return $couponId;
    }

    /**
     * @param Coupon $oldCoupon
     * @param Coupon $newCoupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function update($oldCoupon, $newCoupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        /** @var CouponServiceRepository $couponServiceRepository */
        $couponServiceRepository = $this->container->get('domain.coupon.service.repository');

        /** @var CouponEventRepository $couponEventRepository */
        $couponEventRepository = $this->container->get('domain.coupon.event.repository');

        /** @var CouponPackageRepository $couponPackageRepository */
        $couponPackageRepository = $this->container->get('domain.coupon.package.repository');

        $couponRepository->update($oldCoupon->getId()->getValue(), $newCoupon);

        /** @var Service $newService */
        foreach ($newCoupon->getServiceList()->getItems() as $key => $newService) {
            if (!$oldCoupon->getServiceList()->keyExists($key)) {
                $couponServiceRepository->add($newCoupon, $newService);
            }
        }

        /** @var Service $oldService */
        foreach ($oldCoupon->getServiceList()->getItems() as $key => $oldService) {
            if (!$newCoupon->getServiceList()->keyExists($key)) {
                $couponServiceRepository->deleteForService($oldCoupon->getId()->getValue(), $key);
            }
        }


        /** @var Event $newEvent */
        foreach ($newCoupon->getEventList()->getItems() as $key => $newEvent) {
            if (!$oldCoupon->getEventList()->keyExists($key)) {
                $couponEventRepository->add($newCoupon, $newEvent);
            }
        }

        /** @var Event $oldEvent */
        foreach ($oldCoupon->getEventList()->getItems() as $key => $oldEvent) {
            if (!$newCoupon->getEventList()->keyExists($key)) {
                $couponEventRepository->deleteForEvent($oldCoupon->getId()->getValue(), $key);
            }
        }


        /** @var Package $newPackage */
        foreach ($newCoupon->getPackageList()->getItems() as $key => $newPackage) {
            if (!$oldCoupon->getPackageList()->keyExists($key)) {
                $couponPackageRepository->add($newCoupon, $newPackage);
            }
        }

        /** @var Package $oldPackage */
        foreach ($oldCoupon->getPackageList()->getItems() as $key => $oldPackage) {
            if (!$newCoupon->getPackageList()->keyExists($key)) {
                $couponPackageRepository->deleteForPackage($oldCoupon->getId()->getValue(), $key);
            }
        }

        return true;
    }

    /**
     * @param Coupon $coupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     */
    public function delete($coupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        /** @var CouponServiceRepository $couponServiceRepository */
        $couponServiceRepository = $this->container->get('domain.coupon.service.repository');

        /** @var CouponEventRepository $couponEventRepository */
        $couponEventRepository = $this->container->get('domain.coupon.event.repository');

        /** @var CouponPackageRepository $couponPackageRepository */
        $couponPackageRepository = $this->container->get('domain.coupon.package.repository');

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var PackageCustomerRepository $packageCustomerRepository */
        $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');

        return $couponServiceRepository->deleteByEntityId($coupon->getId()->getValue(), 'couponId') &&
            $couponEventRepository->deleteByEntityId($coupon->getId()->getValue(), 'couponId') &&
            $couponPackageRepository->deleteByEntityId($coupon->getId()->getValue(), 'couponId') &&
            $customerBookingRepository->updateByEntityId($coupon->getId()->getValue(), null, 'couponId') &&
            $packageCustomerRepository->updateByEntityId($coupon->getId()->getValue(), null, 'couponId') &&
            $couponRepository->delete($coupon->getId()->getValue());
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param string $couponCode
     * @param array  $entityIds
     * @param string $entityType
     * @param int    $userId
     * @param bool   $inspectCoupon
     *
     * @return Coupon|null
     *
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws CouponUnknownException
     * @throws CouponInvalidException
     * @throws CouponExpiredException
     */
    public function processCoupon($couponCode, $entityIds, $entityType, $userId, $inspectCoupon)
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        $couponEntityType = '';

        switch ($entityType) {
            case Entities::APPOINTMENT:
                $couponEntityType = Entities::SERVICE;

                break;
            case Entities::EVENT:
                $couponEntityType = Entities::EVENT;

                break;
            case Entities::PACKAGE:
                $couponEntityType = Entities::PACKAGE;

                break;
        }

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $couponsCaseInsensitive = $settingsService->getSetting('payments', 'couponsCaseInsensitive');

        /** @var Collection $coupons */
        $coupons = $couponRepository->getAllByCriteria(
            [
                'code'                   => $couponCode,
                'entityType'             => $couponEntityType,
                'entityIds'              => $entityIds,
                'couponsCaseInsensitive' => $couponsCaseInsensitive,
            ]
        );

        if (!$coupons->length()) {
            throw new CouponUnknownException(FrontendStrings::getCommonStrings()['coupon_unknown']);
        }

        /** @var Coupon $coupon */
        $coupon = $coupons->getItem($coupons->keys()[0]);

        /** @var Collection $entitiesList */
        $entitiesList = new Collection();

        switch ($entityType) {
            case Entities::APPOINTMENT:
                $entitiesList = $coupon->getServiceList();

                break;
            case Entities::EVENT:
                $entitiesList = $coupon->getEventList();

                break;
            case Entities::PACKAGE:
                $entitiesList = $coupon->getPackageList();

                /** @var PackageCustomerRepository $packageCustomerRepository */
                $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');

                $couponWithUsedPackage = $packageCustomerRepository->getByEntityId(
                    $coupon->getId()->getValue(),
                    'couponId'
                );

                $coupon->setUsed(
                    new WholeNumber(
                        $coupon->getUsed()->getValue() + $couponWithUsedPackage->length()
                    )
                );

                break;
        }

        $isCouponEntity = false;

        foreach ($entityIds as $entityId) {
            if ($entitiesList->keyExists($entityId)) {
                $isCouponEntity = true;

                break;
            }
        }

        if (!$isCouponEntity) {
            throw new CouponUnknownException(FrontendStrings::getCommonStrings()['coupon_unknown']);
        }

        $this->inspectCoupon($coupon, $userId, $inspectCoupon);

        return $coupon;
    }

    /**
     * @param Coupon $coupon
     * @param int    $userId
     * @param bool   $inspectCoupon
     *
     * @return boolean
     *
     * @throws ContainerValueNotFoundException
     * @throws CouponInvalidException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws CouponExpiredException
     */
    public function inspectCoupon($coupon, $userId, $inspectCoupon)
    {
        if ($inspectCoupon &&
            (
                $coupon->getStatus()->getValue() === 'hidden' ||
                $coupon->getUsed()->getValue() >= $coupon->getLimit()->getValue()
            )
        ) {
            throw new CouponInvalidException(FrontendStrings::getCommonStrings()['coupon_invalid']);
        }

        if ($inspectCoupon && $userId && $coupon->getCustomerLimit()->getValue() > 0 &&
            $this->getCustomerCouponUsedCount($coupon->getId()->getValue(), $userId) >=
            $coupon->getCustomerLimit()->getValue()
        ) {
            throw new CouponInvalidException(FrontendStrings::getCommonStrings()['coupon_invalid']);
        }

        if ($coupon->getExpirationDate()) {
            $currentDate = DateTimeService::getNowDateTimeObject();

            $expirationDate = DateTimeService::getCustomDateTimeObject(
                $coupon->getExpirationDate()->getValue()->format('Y-m-d') . ' 23:59:59'
            );

            if ($inspectCoupon && $currentDate > $expirationDate) {
                throw new CouponExpiredException(FrontendStrings::getCommonStrings()['coupon_expired']);
            }
        }

        return true;
    }

    /**
     * @param int $couponId
     * @param int $userId
     *
     * @return int
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    private function getCustomerCouponUsedCount($couponId, $userId)
    {
        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');

        $customerAppointmentReservations = $appointmentRepo->getFiltered(
            [
                'customerId'      => $userId,
                'statuses'        => [BookingStatus::APPROVED, BookingStatus::PENDING],
                'bookingStatuses' => [BookingStatus::APPROVED, BookingStatus::PENDING],
                'bookingCouponId' => $couponId
            ]
        );

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->container->get('domain.booking.event.repository');

        $customerEventReservations = $eventRepository->getFiltered(
            [
                'customerId'      => $userId,
                'bookingStatus'   => BookingStatus::APPROVED,
                'bookingCouponId' => $couponId
            ]
        );

        /** @var PackageCustomerRepository $packageCustomerRepository */
        $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');

        $customerPackageReservations = $packageCustomerRepository->getFiltered(
            [
                'customerId'      => $userId,
                'bookingStatus'   => BookingStatus::APPROVED,
                'couponId'        => $couponId
            ]
        );

        return $customerAppointmentReservations->length() + $customerEventReservations->length() + count($customerPackageReservations);
    }

    /**
     * @param Coupon   $coupon
     * @param int|null $userId
     *
     * @return int
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getAllowedCouponLimit($coupon, $userId)
    {
        if ($coupon->getCustomerLimit()->getValue()) {
            $maxLimit = $coupon->getCustomerLimit()->getValue();

            $used = $userId ? $this->getCustomerCouponUsedCount($coupon->getId()->getValue(), $userId) : 0;
        } else {
            $maxLimit = $coupon->getLimit()->getValue();

            $used = $coupon->getUsed()->getValue();
        }

        return $maxLimit - $used;
    }

    /**
     * @return Collection
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function getAll()
    {
        /** @var CouponRepository $couponRepository */
        $couponRepository = $this->container->get('domain.coupon.repository');

        return $couponRepository->getAllIndexedById();
    }
}
