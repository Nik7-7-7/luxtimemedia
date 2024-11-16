<?php

namespace AmeliaBooking\Application\Services\Booking;

use AmeliaBooking\Application\Services\Bookable\BookableApplicationService;
use AmeliaBooking\Application\Services\Bookable\AbstractPackageApplicationService;
use AmeliaBooking\Application\Services\CustomField\AbstractCustomFieldApplicationService;
use AmeliaBooking\Application\Services\Deposit\AbstractDepositApplicationService;
use AmeliaBooking\Application\Services\Payment\PaymentApplicationService;
use AmeliaBooking\Application\Services\TimeSlot\TimeSlotService as ApplicationTimeSlotService;
use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\BookingUnavailableException;
use AmeliaBooking\Domain\Common\Exceptions\CustomerBookedException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Extra;
use AmeliaBooking\Domain\Entity\Bookable\Service\PackageCustomerService;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBookingExtra;
use AmeliaBooking\Domain\Entity\Entities;
use AmeliaBooking\Domain\Entity\Payment\Payment;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Booking\Appointment\CustomerBookingFactory;
use AmeliaBooking\Domain\Services\Booking\AppointmentDomainService;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Reservation\ReservationServiceInterface;
use AmeliaBooking\Domain\Services\Settings\SettingsService;
use AmeliaBooking\Domain\ValueObjects\BooleanValueObject;
use AmeliaBooking\Domain\ValueObjects\PositiveDuration;
use AmeliaBooking\Domain\ValueObjects\String\BookingStatus;
use AmeliaBooking\Domain\ValueObjects\String\PaymentStatus;
use AmeliaBooking\Domain\ValueObjects\String\PaymentType;
use AmeliaBooking\Domain\ValueObjects\String\Token;
use AmeliaBooking\Infrastructure\Common\Exceptions\NotFoundException;
use AmeliaBooking\Infrastructure\Common\Exceptions\QueryExecutionException;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\ValueObjects\Number\Integer\Id;
use AmeliaBooking\Infrastructure\Common\Container;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\PackageCustomerServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Bookable\Service\ServiceRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\AppointmentRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingExtraRepository;
use AmeliaBooking\Infrastructure\Repository\Booking\Appointment\CustomerBookingRepository;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Domain\ValueObjects\Number\Float\Price;
use AmeliaBooking\Infrastructure\Repository\Payment\PaymentRepository;
use AmeliaBooking\Infrastructure\Repository\User\CustomerRepository;
use AmeliaBooking\Infrastructure\Repository\User\ProviderRepository;
use AmeliaBooking\Infrastructure\WP\Translations\FrontendStrings;
use DateTime;
use Exception;
use Interop\Container\Exception\ContainerException;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class AppointmentApplicationService
 *
 * @package AmeliaBooking\Application\Services\Booking
 */
class AppointmentApplicationService
{
    private $container;

    /**
     * AppointmentApplicationService constructor.
     *
     * @param Container $container
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param array $data
     *
     * @return array|null
     * @throws Exception
     */
    public function convertTime(&$data)
    {
        if (!empty($data['utc'])) {
            $data['bookingStart'] = DateTimeService::getCustomDateTimeFromUtc(
                $data['bookingStart']
            );
        } elseif (!empty($data['timeZone'])) {
            $data['bookingStart'] = DateTimeService::getDateTimeObjectInTimeZone(
                $data['bookingStart'],
                $data['timeZone']
            )->setTimezone(DateTimeService::getTimeZone())->format('Y-m-d H:i:s');
        }
    }

    /**
     * @param array   $data
     * @param Service $service
     *
     * @return Appointment
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function build($data, $service)
    {
        /** @var AppointmentDomainService $appointmentDS */
        $appointmentDS = $this->container->get('domain.booking.appointment.service');

        $data['bookingEnd'] = $data['bookingStart'];

        /** @var Appointment $appointment */
        $appointment = AppointmentFactory::create($data);

        $includedExtrasIds = [];

        /** @var CustomerBooking $customerBooking */
        foreach ($appointment->getBookings()->getItems() as $customerBooking) {
            /** @var CustomerBookingExtra $customerBookingExtra */
            foreach ($customerBooking->getExtras()->getItems() as $customerBookingExtra) {
                $extraId = $customerBookingExtra->getExtraId()->getValue();

                /** @var Extra $extra */
                $extra = $service->getExtras()->getItem($extraId);

                if (!in_array($extraId, $includedExtrasIds, true)) {
                    $includedExtrasIds[] = $extraId;
                }

                $customerBookingExtra->setPrice(new Price($extra->getPrice()->getValue()));
                $customerBookingExtra->setAggregatedPrice(new BooleanValueObject($extra->getAggregatedPrice()->getValue()));
            }

            $customerBooking->setPrice(
                new Price(
                    $this->getBookingPriceForServiceDuration(
                        $service,
                        $customerBooking->getDuration() ? $customerBooking->getDuration()->getValue() : null
                    )
                )
            );
        }

        // Set appointment status based on booking statuses
        $bookingsCount = $appointmentDS->getBookingsStatusesCount($appointment);

        $appointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment($service, $bookingsCount);
        $appointment->setStatus(new BookingStatus($appointmentStatus));

        $this->calculateAndSetAppointmentEnd($appointment, $service);

        return $appointment;
    }

    /**
     * @param array $appointmentData
     * @param array $statuses
     *
     * @return Appointment|null
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function getAlreadyBookedAppointment($appointmentData, $statuses)
    {
        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');

        /** @var Collection $existingAppointments */
        $existingAppointments = $appointmentRepo->getFiltered(
            [
                'dates'         => [$appointmentData['bookingStart'], $appointmentData['bookingStart']],
                'services'      => [$appointmentData['serviceId']],
                'providers'     => [$appointmentData['providerId']],
                'skipServices'  => true,
                'skipProviders' => true,
                'skipCustomers' => true,
            ]
        );

        if ($existingAppointments->length()) {
            $freeAppointmentId = null;

            /** @var Appointment $existingAppointment */
            foreach ($existingAppointments->getItems() as $existingAppointment) {
                $freeAppointmentId = $existingAppointment->getId()->getValue();

                /** @var CustomerBooking $existingAppointmentBooking */
                foreach ($existingAppointment->getBookings()->getItems() as $existingAppointmentBooking) {
                    if (in_array($existingAppointmentBooking->getStatus()->getValue(), $statuses)) {
                        return null;
                    }
                }
            }

            if ($freeAppointmentId) {
                return $existingAppointments->getItem($freeAppointmentId);
            }
        }

        return null;
    }

    /**
     * @param Appointment $appointment
     * @param Appointment $oldAppointment
     * @param Service     $service
     * @param array       $newBookingsArray
     * @param array       $paymentData
     *
     * @return void
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     * @throws ContainerException
     * @throws CustomerBookedException
     * @throws BookingUnavailableException
     */
    public function addOrEditAppointment(
        $appointment,
        $oldAppointment,
        $service,
        $newBookingsArray,
        $paymentData
    ) {
        /** @var BookingApplicationService $bookingAS */
        $bookingAS = $this->container->get('application.booking.booking.service');

        $appointmentStatusChanged = false;

        if ($oldAppointment !== null) {
            /** @var AppointmentDomainService $appointmentDS */
            $appointmentDS = $this->container->get('domain.booking.appointment.service');

            foreach ($newBookingsArray as $bookingArray) {
                /** @var CustomerBooking $newBooking */
                $newBooking = CustomerBookingFactory::create($bookingArray);

                /** @var CustomerBooking $booking */
                foreach ($appointment->getBookings()->getItems() as $booking) {
                    if ($booking->getCustomerId()->getValue() === $newBooking->getCustomerId()->getValue()) {
                        throw new CustomerBookedException(FrontendStrings::getCommonStrings()['customer_already_booked_app']);
                    }
                }

                $newBooking->setChangedStatus(new BooleanValueObject(true));

                $newBooking->setAppointmentId($oldAppointment->getId());

                $newBooking->setPrice(
                    new Price(
                        $this->getBookingPriceForServiceDuration(
                            $service,
                            $newBooking->getDuration() ? $newBooking->getDuration()->getValue() : null
                        )
                    )
                );

                $newBooking->setAggregatedPrice($service->getAggregatedPrice());

                /** @var CustomerBookingExtra $bookingExtra */
                foreach ($newBooking->getExtras()->getItems() as $bookingExtra) {
                    /** @var Extra $selectedExtra */
                    $selectedExtra = $service->getExtras()->getItem($bookingExtra->getExtraId()->getValue());

                    $bookingExtra->setPrice($selectedExtra->getPrice());
                }

                $maximumDuration = $this->getMaximumBookingDuration($appointment, $service);

                if ($newBooking->getDuration() && $newBooking->getDuration()->getValue() > $maximumDuration) {
                    $service->setDuration(new PositiveDuration($maximumDuration));
                }

                $appointment->getBookings()->addItem($newBooking);
            }

            $bookingsCount = $appointmentDS->getBookingsStatusesCount($appointment);

            $appointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment($service, $bookingsCount);

            $appointment->setStatus(new BookingStatus($appointmentStatus));

            $appointmentStatusChanged =
                $appointment->getStatus()->getValue() !== BookingStatus::CANCELED &&
                $appointment->getStatus()->getValue() !== BookingStatus::REJECTED &&
                $this->isAppointmentStatusChanged($appointment, $oldAppointment);

            /** @var CustomerBooking $booking */
            foreach ($appointment->getBookings()->getItems() as $booking) {
                $booking->setChangedStatus(
                    new BooleanValueObject(
                        (
                            $appointmentStatusChanged &&
                            $booking->getId() &&
                            $booking->getId()->getValue() &&
                            $booking->getStatus()->getValue() === BookingStatus::APPROVED &&
                            $appointment->getStatus()->getValue() === BookingStatus::APPROVED
                        ) || (
                            !$booking->getId() ||
                            !$booking->getId()->getValue()
                        )
                    )
                );
            }

            $this->calculateAndSetAppointmentEnd($appointment, $service);
        } else {
            /** @var CustomerBooking $booking */
            foreach ($appointment->getBookings()->getItems() as $booking) {
                $booking->setChangedStatus(new BooleanValueObject(true));
            }
        }

        $appointment->setChangedStatus(new BooleanValueObject($appointmentStatusChanged));

        $personsCount = 0;

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            $personsCount += $bookingAS->isBookingApprovedOrPending($booking->getStatus()->getValue())
                ? $booking->getPersons()->getValue()
                : 0;
        }

        /** @var ApplicationTimeSlotService $applicationTimeSlotService */
        $applicationTimeSlotService = $this->container->get('application.timeSlot.service');

        $selectedExtras = [];

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings() as $booking) {
            /** @var CustomerBookingExtra $customerBookingExtra */
            foreach ($booking->getExtras() as $customerBookingExtra) {
                $selectedExtras[] = [
                    'id'       => $customerBookingExtra->getExtraId()->getValue(),
                    'quantity' => $customerBookingExtra->getQuantity()->getValue(),
                ];
            }
        }

        /** @var CustomerBookingExtra $customerBookingExtra */
        foreach ($booking->getExtras() as $customerBookingExtra) {
            $selectedExtras[] = [
                'id'       => $customerBookingExtra->getExtraId()->getValue(),
                'quantity' => $customerBookingExtra->getQuantity()->getValue(),
            ];
        }

        if (!$applicationTimeSlotService->isSlotFree(
            $service,
            $appointment->getBookingStart()->getValue(),
            $appointment->getBookingStart()->getValue(),
            $appointment->getBookingStart()->getValue(),
            $appointment->getProviderId()->getValue(),
            $appointment->getLocationId() ? $appointment->getLocationId()->getValue() : null,
            $selectedExtras,
            null,
            $personsCount,
            false
        )) {
            throw new BookingUnavailableException(FrontendStrings::getCommonStrings()['time_slot_unavailable']);
        }

        if ($oldAppointment === null) {
            $this->add($appointment, $service, $paymentData, true);
        } else {
            $this->update(
                $oldAppointment,
                $appointment,
                new Collection(),
                $service,
                $paymentData
            );
        }
    }

    /**
     * @param Appointment $appointment
     * @param Service     $service
     * @param array       $paymentData
     * @param bool        $isBackendBooking
     *
     * @return Appointment
     * @throws \Slim\Exception\ContainerException
     * @throws \InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     */
    public function add($appointment, $service, $paymentData, $isBackendBooking)
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');
        /** @var CustomerBookingExtraRepository $customerBookingExtraRepository */
        $customerBookingExtraRepository = $this->container->get('domain.booking.customerBookingExtra.repository');
        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get(Entities::APPOINTMENT);
        /** @var AbstractDepositApplicationService $depositAS */
        $depositAS = $this->container->get('application.deposit.service');

        $appointmentId = $appointmentRepository->add($appointment);
        $appointment->setId(new Id($appointmentId));

        /** @var CustomerBooking $customerBooking */
        foreach ($appointment->getBookings()->keys() as $customerBookingKey) {
            $customerBooking = $appointment->getBookings()->getItem($customerBookingKey);

            $customerBooking->setAppointmentId($appointment->getId());
            $customerBooking->setAggregatedPrice(new BooleanValueObject($service->getAggregatedPrice()->getValue()));
            $customerBooking->setToken(new Token());
            $customerBooking->setActionsCompleted(new BooleanValueObject($isBackendBooking));
            $customerBooking->setCreated(new DateTimeValue(DateTimeService::getNowDateTimeObject()));
            $customerBookingId = $bookingRepository->add($customerBooking);

            /** @var CustomerBookingExtra $customerBookingExtra */
            foreach ($customerBooking->getExtras()->keys() as $cbExtraKey) {
                $customerBookingExtra = $customerBooking->getExtras()->getItem($cbExtraKey);

                /** @var Extra $serviceExtra */
                $serviceExtra = $service->getExtras()->getItem($customerBookingExtra->getExtraId()->getValue());

                $customerBookingExtra->setAggregatedPrice(
                    new BooleanValueObject(
                        $reservationService->isExtraAggregatedPrice(
                            $serviceExtra->getAggregatedPrice(),
                            $service->getAggregatedPrice()
                        )
                    )
                );

                $customerBookingExtra->setCustomerBookingId(new Id($customerBookingId));
                $customerBookingExtraId = $customerBookingExtraRepository->add($customerBookingExtra);
                $customerBookingExtra->setId(new Id($customerBookingExtraId));
            }

            $customerBooking->setId(new Id($customerBookingId));

            if ($paymentData) {
                $paymentAmount = $reservationService->getPaymentAmount($customerBooking, $service);

                if ($customerBooking->getDeposit() &&
                    $customerBooking->getDeposit()->getValue() &&
                    $paymentData['gateway'] !== PaymentType::ON_SITE
                ) {
                    $paymentDeposit = $depositAS->calculateDepositAmount(
                        $paymentAmount,
                        $service,
                        $customerBooking->getPersons()->getValue()
                    );

                    $paymentData['deposit'] = $paymentAmount !== $paymentDeposit;

                    $paymentAmount = $paymentDeposit;
                }

                /** @var Payment $payment */
                $payment = $reservationService->addPayment(
                    !$customerBooking->getPackageCustomerService() ?
                        $customerBooking->getId()->getValue() : null,
                    $customerBooking->getPackageCustomerService() ?
                        $customerBooking->getPackageCustomerService()->getPackageCustomer()->getId()->getValue() : null,
                    $paymentData,
                    $paymentAmount,
                    $appointment->getBookingStart()->getValue(),
                    Entities::APPOINTMENT
                );

                /** @var Collection $payments */
                $payments = new Collection();

                $payments->addItem($payment);

                $customerBooking->setPayments($payments);
            }
        }

        return $appointment;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param Appointment $oldAppointment
     * @param Appointment $newAppointment
     * @param Collection  $removedBookings
     * @param Service     $service
     * @param array       $paymentData
     *
     * @return bool
     * @throws \Slim\Exception\ContainerException
     * @throws \InvalidArgumentException
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws ContainerException
     */
    public function update($oldAppointment, $newAppointment, $removedBookings, $service, $paymentData)
    {
        /** @var AppointmentRepository $appointmentRepo */
        $appointmentRepo = $this->container->get('domain.booking.appointment.repository');
        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');
        /** @var CustomerBookingExtraRepository $customerBookingExtraRepository */
        $customerBookingExtraRepository = $this->container->get('domain.booking.customerBookingExtra.repository');
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');
        /** @var ReservationServiceInterface $reservationService */
        $reservationService = $this->container->get('application.reservation.service')->get(Entities::APPOINTMENT);
        /** @var PaymentApplicationService $paymentAS */
        $paymentAS = $this->container->get('application.payment.service');
        /** @var AbstractDepositApplicationService $depositAS */
        $depositAS = $this->container->get('application.deposit.service');

        $appointmentRepo->update($oldAppointment->getId()->getValue(), $newAppointment);

        /** @var CustomerBooking $newBooking */
        foreach ($newAppointment->getBookings()->getItems() as $newBooking) {
            // Update Booking if ID exist
            if ($newBooking->getId() && $newBooking->getId()->getValue()) {
                $bookingRepository->update($newBooking->getId()->getValue(), $newBooking);

                if ($oldAppointment->getServiceId()->getValue() !== $newAppointment->getServiceId()->getValue()) {
                    $bookingRepository->updatePrice($newBooking->getId()->getValue(), $newBooking);

                    $bookingRepository->updateTax($newBooking->getId()->getValue(), $newBooking);
                }

                if ($oldAppointment->getBookings()->keyExists($newBooking->getId()->getValue())) {
                    /** @var CustomerBooking $oldBooking */
                    $oldBooking = $oldAppointment->getBookings()->getItem($newBooking->getId()->getValue());

                    $oldDuration = $oldBooking->getDuration()
                        ? $oldBooking->getDuration()->getValue() : $service->getDuration()->getValue();

                    if ($newBooking->getDuration() && $newBooking->getDuration()->getValue() !== $oldDuration) {
                        $bookingRepository->updatePrice($newBooking->getId()->getValue(), $newBooking);
                    }
                }
            }

            // Add Booking if ID does not exist
            if ($newBooking->getId() === null || ($newBooking->getId()->getValue() === 0)) {
                $newBooking->setAppointmentId($newAppointment->getId());
                $newBooking->setToken(new Token());
                $newBooking->setAggregatedPrice(new BooleanValueObject($service->getAggregatedPrice()->getValue()));
                $newBooking->setActionsCompleted(new BooleanValueObject(!empty($paymentData['isBackendBooking'])));
                $newBookingId = $bookingRepository->add($newBooking);

                $newBooking->setId(new Id($newBookingId));

                if ($paymentData) {
                    $paymentAmount = $reservationService->getPaymentAmount($newBooking, $service);

                    if ($newBooking->getDeposit() &&
                        $newBooking->getDeposit()->getValue() &&
                        $paymentData['gateway'] !== PaymentType::ON_SITE
                    ) {
                        $paymentDeposit = $depositAS->calculateDepositAmount(
                            $paymentAmount,
                            $service,
                            $newBooking->getPersons()->getValue()
                        );

                        $paymentData['deposit'] = $paymentAmount !== $paymentDeposit;

                        $paymentAmount = $paymentDeposit;
                    }

                    /** @var Payment $payment */
                    $payment = $reservationService->addPayment(
                        !$newBooking->getPackageCustomerService() ?
                            $newBooking->getId()->getValue() : null,
                        $newBooking->getPackageCustomerService() ?
                            $newBooking->getPackageCustomerService()->getPackageCustomer()->getId()->getValue() : null,
                        $paymentData,
                        $paymentAmount,
                        $newAppointment->getBookingStart()->getValue(),
                        Entities::APPOINTMENT
                    );

                    /** @var Collection $payments */
                    $payments = new Collection();

                    $payments->addItem($payment);

                    $newBooking->setPayments($payments);
                }
            }

            $newExtrasIds = [];

            /** @var CustomerBookingExtra $newExtra */
            foreach ($newBooking->getExtras()->getItems() as $newExtra) {
                // Update Extra if ID exist
                /** @var CustomerBookingExtra $newExtra */
                if ($newExtra->getId() && $newExtra->getId()->getValue()) {
                    $customerBookingExtraRepository->update($newExtra->getId()->getValue(), $newExtra);
                }

                // Add Extra if ID does not exist
                if ($newExtra->getId() === null || ($newExtra->getId()->getValue() === 0)) {
                    /** @var Extra $serviceExtra */
                    $serviceExtra = $service->getExtras()->getItem($newExtra->getExtraId()->getValue());

                    $newExtra->setAggregatedPrice(
                        new BooleanValueObject(
                            $reservationService->isExtraAggregatedPrice(
                                $serviceExtra->getAggregatedPrice(),
                                $service->getAggregatedPrice()
                            )
                        )
                    );

                    $newExtra->setCustomerBookingId($newBooking->getId());
                    $newExtraId = $customerBookingExtraRepository->add($newExtra);

                    $newExtra->setId(new Id($newExtraId));
                }

                $newExtrasIds[] = $newExtra->getId()->getValue();
            }

            if ($oldAppointment->getBookings()->keyExists($newBooking->getId()->getValue())) {
                /** @var CustomerBooking $oldBooking */
                $oldBooking = $oldAppointment->getBookings()->getItem($newBooking->getId()->getValue());

                /** @var CustomerBookingExtra $oldExtra */
                foreach ($oldBooking->getExtras()->getItems() as $oldExtra) {
                    if (!in_array($oldExtra->getId()->getValue(), $newExtrasIds)) {
                        $customerBookingExtraRepository->delete($oldExtra->getId()->getValue());
                    }
                }
            }
        }

        /** @var CustomerBooking $removedBooking */
        foreach ($removedBookings->getItems() as $removedBooking) {
            /** @var CustomerBookingExtra $removedExtra */
            foreach ($removedBooking->getExtras()->getItems() as $removedExtra) {
                $customerBookingExtraRepository->delete($removedExtra->getId()->getValue());
            }

            /** @var Collection $removedPayments */
            $removedPayments = $paymentRepository->getByEntityId(
                $removedBooking->getId()->getValue(),
                'customerBookingId'
            );

            /** @var Payment $payment */
            foreach ($removedPayments->getItems() as $payment) {
                if (!$paymentAS->delete($payment)) {
                    return false;
                }
            }

            $bookingRepository->delete($removedBooking->getId()->getValue());
        }

        return true;
    }

    /**
     * @param Appointment $appointment
     * @param array       $ignoredIds
     *
     * @return boolean
     *
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function delete($appointment, $ignoredIds = [])
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $this->container->get('application.booking.booking.service');

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            if ($appointment->getId() &&
                $appointment->getId()->getValue() &&
                $booking->getId() &&
                $booking->getId()->getValue() &&
                empty($ignoredIds[$appointment->getId()->getValue()]['bookingsIds'][$booking->getId()->getValue()]) &&
                !$bookingApplicationService->delete($booking)
            ) {
                return false;
            }
        }

        if ($appointment->getId() &&
            $appointment->getId()->getValue() &&
            empty($ignoredIds[$appointment->getId()->getValue()]) &&
            !$appointmentRepository->delete($appointment->getId()->getValue())
        ) {
            return false;
        }

        return true;
    }

    /**
     * @param Appointment $appointment
     * @param Appointment $oldAppointment
     *
     * @return bool
     */
    public function isAppointmentStatusChanged($appointment, $oldAppointment)
    {
        return $appointment->getStatus()->getValue() !== $oldAppointment->getStatus()->getValue();
    }

    /**
     * @param Appointment $appointment
     * @param Appointment $oldAppointment
     *
     * @return bool
     */
    public function isAppointmentRescheduled($appointment, $oldAppointment)
    {
        $start = $appointment->getBookingStart()->getValue()->format('Y-m-d H:i:s');

        $end = $appointment->getBookingStart()->getValue()->format('Y-m-d H:i:s');

        $oldStart = $oldAppointment->getBookingStart()->getValue()->format('Y-m-d H:i:s');

        $oldEnd = $oldAppointment->getBookingStart()->getValue()->format('Y-m-d H:i:s');

        return $start !== $oldStart || $end !== $oldEnd;
    }

    /**
     * Return required time for the appointment in seconds
     * and extras.
     *
     * @param Appointment $appointment
     * @param Service     $service
     *
     * @return mixed
     */
    public function getAppointmentLengthTime($appointment, $service)
    {
        $requiredTime = 0;

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            $bookingDuration = $this->getBookingLengthTime($booking, $service);

            if ($bookingDuration > $requiredTime &&
                (
                    $booking->getStatus()->getValue() === BookingStatus::APPROVED ||
                    $booking->getStatus()->getValue() === BookingStatus::PENDING
                )
            ) {
                $requiredTime = $bookingDuration;
            }
        }

        return $requiredTime;
    }

    /**
     * Return required time for the booking in seconds
     * and extras.
     *
     * @param CustomerBooking $booking
     * @param Service     $service
     *
     * @return mixed
     */
    public function getBookingLengthTime($booking, $service)
    {
        $duration = $booking->getDuration() && $booking->getDuration()->getValue()
            ? $booking->getDuration()->getValue() : $service->getDuration()->getValue();

        /** @var CustomerBookingExtra $bookingExtra */
        foreach ($booking->getExtras()->getItems() as $bookingExtra) {
            /** @var Extra $extra */
            foreach ($service->getExtras()->getItems() as $extra) {
                if ($extra->getId()->getValue() === $bookingExtra->getExtraId()->getValue()) {
                    $extraDuration = $extra->getDuration() ? $extra->getDuration()->getValue() : 0;

                    $duration += $extraDuration * $bookingExtra->getQuantity()->getValue();
                }
            }
        }

        return $duration;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param Appointment   $appointment
     * @param boolean       $isCustomer
     * @param DateTime|null $minimumAppointmentDateTime
     * @param DateTime|null $maximumAppointmentDateTime
     *
     * @return boolean
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     * @throws Exception
     * @throws ContainerException
     */
    public function canBeBooked($appointment, $isCustomer, $minimumAppointmentDateTime, $maximumAppointmentDateTime)
    {
        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        /** @var ApplicationTimeSlotService $applicationTimeSlotService */
        $applicationTimeSlotService = $this->container->get('application.timeSlot.service');

        $selectedExtras = [];

        foreach ($appointment->getBookings()->keys() as $bookingKey) {
            /** @var CustomerBooking $booking */
            $booking = $appointment->getBookings()->getItem($bookingKey);

            foreach ($booking->getExtras()->keys() as $extraKey) {
                $selectedExtras[] = [
                    'id'       => $booking->getExtras()->getItem($extraKey)->getExtraId()->getValue(),
                    'quantity' => $booking->getExtras()->getItem($extraKey)->getQuantity()->getValue(),
                ];
            }
        }

        /** @var Service $service */
        $service = $serviceRepository->getByIdWithExtras($appointment->getServiceId()->getValue());

        $maximumDuration = $this->getMaximumBookingDuration($appointment, $service);

        $service->setDuration(new PositiveDuration($maximumDuration));

        return $applicationTimeSlotService->isSlotFree(
            $service,
            $appointment->getBookingStart()->getValue(),
            $minimumAppointmentDateTime ?: $appointment->getBookingStart()->getValue(),
            $maximumAppointmentDateTime ?: $appointment->getBookingStart()->getValue(),
            $appointment->getProviderId()->getValue(),
            $appointment->getLocationId() ? $appointment->getLocationId()->getValue() : null,
            $selectedExtras,
            $appointment->getId() ? $appointment->getId()->getValue() : null,
            null,
            $isCustomer
        );
    }

    /**
     * @param int $appointmentId
     *
     * @return void
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws Exception
     */
    public function manageDeletionParentRecurringAppointment($appointmentId)
    {
        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var Collection $recurringAppointments */
        $recurringAppointments = $appointmentRepository->getFiltered(['parentId' => $appointmentId]);

        $isFirstRecurringAppointment = true;

        $newParentId = null;

        /** @var Appointment $recurringAppointment */
        foreach ($recurringAppointments->getItems() as $key => $recurringAppointment) {
            if ($isFirstRecurringAppointment) {
                $newParentId = $recurringAppointment->getId()->getValue();
            }

            $appointmentRepository->updateFieldById(
                $recurringAppointment->getId()->getValue(),
                $isFirstRecurringAppointment ? null : $newParentId,
                'parentId'
            );

            $isFirstRecurringAppointment = false;
        }
    }

    /**
     * @param string     $searchString
     *
     * @return array
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws Exception
     */
    public function getAppointmentEntitiesIdsBySearchString($searchString)
    {
        /** @var CustomerRepository $customerRepository */
        $customerRepository = $this->container->get('domain.users.customers.repository');

        /** @var ProviderRepository $providerRepository */
        $providerRepository = $this->container->get('domain.users.providers.repository');

        /** @var ServiceRepository $serviceRepository */
        $serviceRepository = $this->container->get('domain.bookable.service.repository');

        $customersArray = $customerRepository->getFiltered(
            [
                'ignoredBookings' => true,
                'search'          => $searchString,
            ],
            null
        );

        $result = [
            'customers' => array_column($customersArray, 'id'),
            'providers' => [],
            'services'  => [],
        ];

        /** @var Collection $providers */
        $providers = $providerRepository->getFiltered(['search' => $searchString], 0);

        /** @var Collection $services */
        $services = $serviceRepository->getByCriteria(['search' => $searchString]);

        /** @var Provider $provider */
        foreach ($providers->getItems() as $provider) {
            $result['providers'][] = $provider->getId()->getValue();
        }

        /** @var Service $service */
        foreach ($services->getItems() as $service) {
            $result['services'][] = $service->getId()->getValue();
        }

        return $result;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param Service         $service
     * @param Appointment     $appointment
     * @param Payment         $payment
     * @param CustomerBooking $booking
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function isAppointmentStatusChangedWithBooking($service, $appointment, $payment, $booking)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var AppointmentDomainService $appointmentDS */
        $appointmentDS = $this->container->get('domain.booking.appointment.service');

        /** @var SettingsService $settingsService */
        $settingsService = $this->container->get('domain.settings.service');

        $defaultBookingStatus = $settingsService
            ->getEntitySettings($service->getSettings())
            ->getGeneralSettings()
            ->getDefaultAppointmentStatus();

        if ($payment && $payment->getAmount()->getValue() > 0) {
            /** @var ReservationServiceInterface $reservationService */
            $reservationService = $this->container->get('application.reservation.service')->get(Entities::APPOINTMENT);

            $paymentRepository->updateFieldById(
                $payment->getId()->getValue(),
                $reservationService->getPaymentAmount($booking, $service) > $payment->getAmount()->getValue() ?
                    PaymentStatus::PARTIALLY_PAID : PaymentStatus::PAID,
                'status'
            );
        }

        if ($defaultBookingStatus === BookingStatus::APPROVED &&
            $booking->getStatus()->getValue() === BookingStatus::PENDING
        ) {
            $oldBookingsCount = $appointmentDS->getBookingsStatusesCount($appointment);

            $oldAppointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment(
                $service,
                $oldBookingsCount
            );

            $booking->setChangedStatus(new BooleanValueObject(true));
            $booking->setStatus(new BookingStatus(BookingStatus::APPROVED));


            $newBookingsCount = $appointmentDS->getBookingsStatusesCount($appointment);

            $newAppointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment(
                $service,
                $newBookingsCount
            );

            $appointmentRepository->updateFieldById(
                $appointment->getId()->getValue(),
                $newAppointmentStatus,
                'status'
            );

            $bookingRepository->updateFieldById(
                $booking->getId()->getValue(),
                $newAppointmentStatus,
                'status'
            );

            $appointment->setStatus(new BookingStatus($newAppointmentStatus));

            return $oldAppointmentStatus === BookingStatus::PENDING &&
                $newAppointmentStatus === BookingStatus::APPROVED;
        }

        return false;
    }

    /**
     * @param Appointment $appointment
     * @param CustomerBooking $removedBooking
     *
     * @return array
     *
     * @throws ContainerException
     * @throws InvalidArgumentException
     * @throws NotFoundException
     * @throws QueryExecutionException
     */
    public function removeBookingFromGroupAppointment($appointment, $removedBooking)
    {
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $this->container->get('application.booking.booking.service');

        /** @var AppointmentApplicationService $appointmentApplicationService */
        $appointmentApplicationService = $this->container->get('application.booking.appointment.service');

        /** @var BookableApplicationService $bookableApplicationService */
        $bookableApplicationService = $this->container->get('application.bookable.service');

        /** @var AppointmentDomainService $appointmentDomainService */
        $appointmentDomainService = $this->container->get('domain.booking.appointment.service');

        /** @var AbstractCustomFieldApplicationService $customFieldService */
        $customFieldService = $this->container->get('application.customField.service');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var Appointment $originalAppointment */
        $originalAppointment = AppointmentFactory::create($appointment->toArray());

        /** @var Service $service */
        $service = $bookableApplicationService->getAppointmentService(
            $appointment->getServiceId()->getValue(),
            $appointment->getProviderId()->getValue()
        );

        $appointment->getBookings()->deleteItem($removedBooking->getId()->getValue());

        $appointmentStatus = $appointmentDomainService->getAppointmentStatusWhenEditAppointment(
            $service,
            $appointmentDomainService->getBookingsStatusesCount($appointment)
        );

        $appointment->setStatus(new BookingStatus($appointmentStatus));

        $appointmentStatusChanged = $appointmentApplicationService->isAppointmentStatusChanged(
            $appointment,
            $originalAppointment
        );

        if ($appointmentStatusChanged) {
            $appointmentRepository->updateFieldById(
                $appointment->getId()->getValue(),
                $appointment->getStatus()->getValue(),
                'status'
            );

            /** @var CustomerBooking $booking */
            foreach ($appointment->getBookings()->getItems() as $booking) {
                if ((
                    $booking->getStatus()->getValue() === BookingStatus::APPROVED &&
                    $appointment->getStatus()->getValue() === BookingStatus::PENDING
                )
                ) {
                    $booking->setChangedStatus(new BooleanValueObject(true));
                }
            }
        }

        $appointment->setRescheduled(new BooleanValueObject(false));

        $appointmentArray = $appointment->toArray();

        $bookingsWithChangedStatus = $bookingApplicationService->getBookingsWithChangedStatus(
            $appointmentArray,
            $originalAppointment->toArray()
        );

        /** @var Collection $removedBookings */
        $removedBookings = new Collection();

        $removedBookings->addItem(
            CustomerBookingFactory::create($removedBooking->toArray()),
            $removedBooking->getId()->getValue()
        );

        $customFieldService->deleteUploadedFilesForDeletedBookings(
            $appointment->getBookings(),
            $removedBookings
        );

        return [
            Entities::APPOINTMENT          => $appointmentArray,
            'bookingsWithChangedStatus'    => $bookingsWithChangedStatus,
            'bookingDeleted'               => true,
            'appointmentDeleted'           => false,
            'appointmentStatusChanged'     => $appointmentStatusChanged,
            'appointmentRescheduled'       => false,
            'appointmentEmployeeChanged'   => null,
            'appointmentZoomUserChanged'   => false,
            'appointmentZoomUsersLicenced' => false,
        ];
    }

    /**
     * @param Appointment     $appointment
     * @param CustomerBooking $removedBooking
     *
     * @return array
     *
     * @throws ContainerException
     * @throws InvalidArgumentException
     */
    public function removeBookingFromNonGroupAppointment($appointment, $removedBooking)
    {
        /** @var BookingApplicationService $bookingApplicationService */
        $bookingApplicationService = $this->container->get('application.booking.booking.service');

        /** @var AbstractCustomFieldApplicationService $customFieldService */
        $customFieldService = $this->container->get('application.customField.service');

        /** @var Collection $removedBookings */
        $removedBookings = new Collection();

        $removedBookings->addItem(
            CustomerBookingFactory::create($removedBooking->toArray()),
            $removedBooking->getId()->getValue()
        );

        $appointment->setStatus(new BookingStatus(BookingStatus::REJECTED));

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            if ($bookingApplicationService->isBookingApprovedOrPending($booking->getStatus()->getValue())) {
                $booking->setChangedStatus(new BooleanValueObject(true));
            }
        }

        $customFieldService->deleteUploadedFilesForDeletedBookings(
            new Collection(),
            $appointment->getBookings()
        );

        return [
            Entities::APPOINTMENT       => $appointment->toArray(),
            'bookingsWithChangedStatus' => $removedBookings->toArray(),
            'bookingDeleted'            => true,
            'appointmentDeleted'        => true,
        ];
    }

    /**
     * @param CustomerBooking $booking
     * @param Collection      $ignoredBookings
     * @param int             $serviceId
     * @param array           $paymentData
     *
     * @return boolean
     * @throws InvalidArgumentException
     * @throws QueryExecutionException
     */
    public function processPackageAppointmentBooking($booking, $ignoredBookings, $serviceId, &$paymentData)
    {
        /** @var PackageCustomerServiceRepository $packageCustomerServiceRepository */
        $packageCustomerServiceRepository = $this->container->get('domain.bookable.packageCustomerService.repository');

        /** @var CustomerBookingRepository $customerBookingRepository */
        $customerBookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var AbstractPackageApplicationService $packageApplicationService */
        $packageApplicationService = $this->container->get('application.bookable.package');

        if ((!$booking->getId() || !$ignoredBookings->keyExists($booking->getId()->getValue())) &&
            $booking->getPackageCustomerService() &&
            $booking->getPackageCustomerService()->getPackageCustomer() &&
            $booking->getPackageCustomerService()->getPackageCustomer()->getId()
        ) {
            /** @var Collection $packageCustomerServices */
            $packageCustomerServices = $packageCustomerServiceRepository->getByEntityId(
                $booking->getPackageCustomerService()->getPackageCustomer()->getId()->getValue(),
                'packageCustomerId'
            );

            $newPackageCustomerService = null;

            /** @var PackageCustomerService $packageCustomerService */
            foreach ($packageCustomerServices->getItems() as $packageCustomerService) {
                if ($packageCustomerService->getServiceId()->getValue() === $serviceId) {
                    $newPackageCustomerService = $packageCustomerService;

                    break;
                }
            }

            if (!$newPackageCustomerService ||
                !$packageApplicationService->isBookingAvailableForPurchasedPackage(
                    $newPackageCustomerService->getId()->getValue(),
                    $booking->getCustomerId()->getValue(),
                    false
                )
            ) {
                return false;
            }

            $booking->getPackageCustomerService()->setId(new Id($newPackageCustomerService->getId()->getValue()));

            if ($booking->getId() && $booking->getId()->getValue()) {
                $customerBookingRepository->updateFieldById(
                    $booking->getId()->getValue(),
                    $newPackageCustomerService->getId()->getValue(),
                    'packageCustomerServiceId'
                );
            }

            $paymentData = null;
        }

        return true;
    }

    /**
     * @param Appointment $newAppointment
     * @param Appointment $oldAppointment
     *
     * @return bool
     *
     * @throws ContainerValueNotFoundException
     */
    public function appointmentDetailsChanged($newAppointment, $oldAppointment)
    {
        if (($oldAppointment->getLocationId() ? $oldAppointment->getLocationId()->getValue() : null) !==
            ($newAppointment->getLocationId() ? $newAppointment->getLocationId()->getValue() : null)) {
            return true;
        }
        if ($oldAppointment->getLessonSpace() !== $newAppointment->getLessonSpace()) {
            return true;
        }
        return $oldAppointment->getProviderId()->getValue() !== $newAppointment->getProviderId()->getValue();
    }

    /**
     * @param CustomerBooking $newBooking
     * @param CustomerBooking     $oldBooking
     *
     * @return bool
     *
     * @throws ContainerValueNotFoundException
     */
    public function bookingDetailsChanged($newBooking, $oldBooking)
    {
        if ($oldBooking->getPersons()->getValue() !== $newBooking->getPersons()->getValue()) {
            return true;
        }
        if (($oldBooking->getDuration() ? $oldBooking->getDuration()->getValue() : null) !==
            ($newBooking->getDuration() ? $newBooking->getDuration()->getValue() : null)) {
            return true;
        }
        if ($newBooking->getExtras()->length() !== $oldBooking->getExtras()->length()) {
            return true;
        } else {
            foreach ($newBooking->getExtras()->toArray() as $newExtra) {
                $extraIndex = array_search($newExtra['id'], array_column($oldBooking->getExtras()->toArray(), 'id'));
                if ($extraIndex === false || $newExtra['quantity'] !== $oldBooking->getExtras()->toArray()[$extraIndex]['quantity']) {
                    return true;
                }
            }
        }

        $newCustomFields = $newBooking->getCustomFields() && $newBooking->getCustomFields()->getValue() ?
            json_decode($newBooking->getCustomFields()->getValue(), true) : null;
        $oldCustomFields = $oldBooking->getCustomFields() && $oldBooking->getCustomFields()->getValue() ?
            json_decode($oldBooking->getCustomFields()->getValue(), true) : null;

        if ($newCustomFields) {
            $newCustomFields = array_filter($newCustomFields, function($k) {
                return !empty($k['value']);
            });
        }
        if ($oldCustomFields) {
            $oldCustomFields = array_filter($oldCustomFields, function($k) {
                return !empty($k['value']);
            });
        }

        if (($newCustomFields ? count($newCustomFields) : null) !== ($oldCustomFields ? count($oldCustomFields) : null)) {
            return true;
        } else {
            foreach ((array)$newCustomFields as $index => $newCf) {
                $cfIndex = is_array($oldCustomFields) && !empty($oldCustomFields[$index]) ? $index : false;
                if ($cfIndex === false || $newCf['value'] !== $oldCustomFields[$cfIndex]['value']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param Appointment $appointment
     * @param Service     $service
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function calculateAndSetAppointmentEnd($appointment, $service)
    {
        $appointment->setBookingEnd(
            new DateTimeValue(
                DateTimeService::getCustomDateTimeObject(
                    $appointment->getBookingStart()->getValue()->format('Y-m-d H:i:s')
                )->modify('+' . $this->getAppointmentLengthTime($appointment, $service) . ' second')
            )
        );
    }

    /**
     * @param Service $service
     * @param int     $duration
     *
     * @return float
     *
     * @throws ContainerValueNotFoundException
     */
    public function getBookingPriceForServiceDuration($service, $duration)
    {
        if ($duration && $service->getCustomPricing()) {
            $customPricing = json_decode($service->getCustomPricing()->getValue(), true);

            if ($customPricing !== null &&
                $customPricing['enabled'] &&
                array_key_exists($duration, $customPricing['durations'])
            ) {
                return $customPricing['durations'][$duration]['price'];
            }
        }

        return $service->getPrice()->getValue();
    }

    /**
     * @param Appointment $appointment
     * @param Service     $service
     *
     * @return int
     *
     * @throws ContainerValueNotFoundException
     */
    public function getMaximumBookingDuration($appointment, $service)
    {
        $maximumDuration = 0;

        /** @var CustomerBooking $booking */
        foreach ($appointment->getBookings()->getItems() as $booking) {
            if ((
                    $booking->getStatus()->getValue() === BookingStatus::APPROVED ||
                    $booking->getStatus()->getValue() === BookingStatus::PENDING
                ) &&
                $booking->getDuration() &&
                $booking->getDuration()->getValue() &&
                $booking->getDuration()->getValue() > $maximumDuration
            ) {
                $maximumDuration = $booking->getDuration()->getValue();
            }
        }

        return $maximumDuration ? $maximumDuration : $service->getDuration()->getValue();
    }

    /**
     * @param int $paymentId
     *
     *
     * @throws ContainerValueNotFoundException
     * @throws QueryExecutionException
     * @throws InvalidArgumentException
     */
    public function updateBookingStatus($paymentId, $newStatus = BookingStatus::APPROVED)
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->container->get('domain.payment.repository');

        /** @var AppointmentRepository $appointmentRepository */
        $appointmentRepository = $this->container->get('domain.booking.appointment.repository');

        /** @var CustomerBookingRepository $bookingRepository */
        $bookingRepository = $this->container->get('domain.booking.customerBooking.repository');

        /** @var AppointmentDomainService $appointmentDS */
        $appointmentDS = $this->container->get('domain.booking.appointment.service');

        /** @var Payment $payment */
        $payment = $paymentRepository->getById($paymentId);

        if ($payment->getCustomerBookingId() && $payment->getEntity()->getValue() === Entities::APPOINTMENT) {
            $id = $payment->getCustomerBookingId()->getValue();
            $bookingRepository->updateFieldById($id, $newStatus, 'status');

            $appointments = $appointmentRepository->getFiltered(
                [
                    'bookingId' => $id,
                    'skipCustomers' => true,
                    'skipPayments' => true,
                    'skipExtras' => true,
                    'skipCoupons' => true
                ]
            );

            /** @var Appointment $appointment **/
            $appointment = $appointments->getItem($appointments->keys()[0]);

            if ($appointment instanceof Appointment) {
                $appointmentStatus = $appointmentDS->getAppointmentStatusWhenEditAppointment(
                    $appointment->getService(),
                    $appointmentDS->getBookingsStatusesCount($appointment)
                );

                $appointmentRepository->updateFieldById($appointment->getId()->getValue(), $appointmentStatus, 'status');
            }
        }
        if ($payment->getPackageCustomerId() && $payment->getEntity()->getValue() === Entities::APPOINTMENT) {
            /** @var PackageCustomerRepository $packageCustomerRepository */
            $packageCustomerRepository = $this->container->get('domain.bookable.packageCustomer.repository');
            $id = $payment->getPackageCustomerId()->getValue();
            $packageCustomerRepository->updateFieldById($id, $newStatus, 'status');
        }
    }
}
