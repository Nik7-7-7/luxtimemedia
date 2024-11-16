<?php

namespace AmeliaBooking\Domain\Services\User;

use AmeliaBooking\Domain\Collection\Collection;
use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\Entity\Bookable\Service\Service;
use AmeliaBooking\Domain\Entity\Booking\Appointment\Appointment;
use AmeliaBooking\Domain\Entity\Booking\Appointment\CustomerBooking;
use AmeliaBooking\Domain\Entity\Location\Location;
use AmeliaBooking\Domain\Entity\Schedule\DayOff;
use AmeliaBooking\Domain\Entity\Schedule\Period;
use AmeliaBooking\Domain\Entity\Schedule\PeriodLocation;
use AmeliaBooking\Domain\Entity\Schedule\SpecialDay;
use AmeliaBooking\Domain\Entity\Schedule\SpecialDayPeriod;
use AmeliaBooking\Domain\Entity\Schedule\SpecialDayPeriodLocation;
use AmeliaBooking\Domain\Entity\Schedule\WeekDay;
use AmeliaBooking\Domain\Entity\User\Provider;
use AmeliaBooking\Domain\Factory\Bookable\Service\ServiceFactory;
use AmeliaBooking\Domain\Factory\Booking\Appointment\AppointmentFactory;
use AmeliaBooking\Domain\Factory\Schedule\PeriodFactory;
use AmeliaBooking\Domain\Factory\Schedule\WeekDayFactory;
use AmeliaBooking\Domain\Services\DateTime\DateTimeService;
use AmeliaBooking\Domain\Services\Interval\IntervalService;
use AmeliaBooking\Domain\ValueObjects\DateTime\DateTimeValue;
use AmeliaBooking\Domain\ValueObjects\Duration;
use AmeliaBooking\Domain\ValueObjects\String\Status;
use DateTime;
use DateTimeZone;
use Exception;
use Slim\Exception\ContainerValueNotFoundException;

/**
 * Class ProviderService
 *
 * @package AmeliaBooking\Domain\Services\User
 */
class ProviderService
{
    /** @var IntervalService */
    private $intervalService;

    /**
     * ProviderService constructor.
     *
     * @param IntervalService $intervalService
     */
    public function __construct(
        $intervalService
    ) {
        $this->intervalService = $intervalService;
    }

    /**
     * @param WeekDay|SpecialDay $day
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function makePeriodsAvailable($day)
    {
        $periodsInSeconds = [];

        /** @var Period $period */
        foreach ($day->getPeriodList()->getItems() as $key => $period) {
            if ($period->getLocationId()) {
                $startInSeconds = $this->intervalService->getSeconds(
                    $period->getStartTime()->getValue()->format('H:i:s')
                );

                $periodsInSeconds[$startInSeconds] = $this->intervalService->getSeconds(
                    $period->getEndTime()->getValue()->format('H:i:s')
                );
            }
        }

        if (!$periodsInSeconds) {
            $day->getPeriodList()->addItem(
                PeriodFactory::create(
                    [
                        'startTime'          => '00:00:00',
                        'endTime'            => '24:00:00',
                        'periodServiceList'  => [],
                        'periodLocationList' => [],
                    ]
                )
            );

            return;
        }


        $periodsStartTimes = array_keys($periodsInSeconds);

        sort($periodsStartTimes);

        $sortedPeriodsInSeconds = [];

        foreach ($periodsStartTimes as $seconds) {
            $sortedPeriodsInSeconds[$seconds] = $periodsInSeconds[$seconds];
        }

        $periodsInSeconds = $sortedPeriodsInSeconds;

        $extraPeriods = [];

        $i = 0;

        $periodsCount = sizeof($periodsInSeconds);

        $periodsStarts = array_keys($periodsInSeconds);

        foreach ($periodsInSeconds as $secondsStart => $secondsEnd) {
            if ($i === 0) {
                if ($secondsStart !== 0) {
                    $extraPeriods[0] = $secondsStart;
                }

                if (!empty($periodsStarts[$i + 1]) && $periodsStarts[$i + 1] !== $secondsEnd) {
                    $extraPeriods[$secondsEnd] = $periodsStarts[$i + 1];
                }

                if ($periodsCount === 1 && $secondsEnd !== 86400) {
                    $extraPeriods[$secondsEnd] = 86400;
                }
            } elseif ($i === $periodsCount - 1) {
                if ($secondsEnd !== 86400) {
                    $extraPeriods[$secondsEnd] = 86400;
                }
            } else {
                if ($periodsStarts[$i + 1] !== $secondsEnd) {
                    $extraPeriods[$secondsEnd] = $periodsStarts[$i + 1];
                }
            }

            $i++;
        }

        foreach ($extraPeriods as $extraPeriodStart => $extraPeriodEnd) {
            $day->getPeriodList()->addItem(
                PeriodFactory::create(
                    [
                        'startTime'          => sprintf('%02d', floor($extraPeriodStart / 3600)) . ':'
                            . sprintf('%02d', floor(($extraPeriodStart / 60) % 60)) . ':00',
                        'endTime'            => sprintf('%02d', floor($extraPeriodEnd / 3600)) . ':'
                            . sprintf('%02d', floor(($extraPeriodEnd / 60) % 60)) . ':00',
                        'periodServiceList'  => [],
                        'periodLocationList' => [],
                    ]
                )
            );
        }

        /** @var Collection $sortedPeriods */
        $sortedPeriods = new Collection();

        $allPeriodsInSeconds = [];

        /** @var Period $period */
        foreach ($day->getPeriodList()->getItems() as $key => $period) {
            $startInSeconds = $this->intervalService->getSeconds(
                $period->getStartTime()->getValue()->format('H:i:s')
            );

            $allPeriodsInSeconds[$startInSeconds] = $key;
        }

        $allPeriodsInSecondsKeys = array_keys($allPeriodsInSeconds);

        sort($allPeriodsInSecondsKeys);

        foreach ($allPeriodsInSecondsKeys as $periodStart) {
            $sortedPeriods->addItem($day->getPeriodList()->getItem($allPeriodsInSeconds[$periodStart]));
        }

        $day->setPeriodList($sortedPeriods);
    }

    /**
     * @param Collection $providers
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function setProvidersAlwaysAvailable($providers)
    {
        /** @var Provider $provider */
        foreach ($providers->getItems() as $k => $provider) {
            $providerWeekDayIndexes = [];

            /** @var WeekDay $weekDay */
            foreach ($provider->getWeekDayList()->getItems() as $weekDay) {
                $providerWeekDayIndexes[] = $weekDay->getDayIndex()->getValue();
            }

            for ($i = 1; $i <= 7; $i++) {
                if (!in_array($i, $providerWeekDayIndexes)) {
                    $provider->getWeekDayList()->addItem(
                        WeekDayFactory::create(
                            [
                                'dayIndex'    => $i,
                                'startTime'   => '00:00:00',
                                'endTime'     => '00:00:00',
                                'timeOutList' => new Collection(),
                                'periodList'  => new Collection(
                                    [
                                        'startTime'         => '00:00:00',
                                        'endTime'           => '24:00:00',
                                        'periodServiceList' => [],
                                    ]
                                )
                            ]
                        )
                    );
                }
            }

            /** @var WeekDay $weekDay */
            foreach ($provider->getWeekDayList()->getItems() as $index => $weekDay) {
                $this->makePeriodsAvailable($weekDay);
            }

            /** @var SpecialDay $specialDay */
            foreach ($provider->getSpecialDayList()->getItems() as $specialDay) {
                $this->makePeriodsAvailable($specialDay);
            }

            $provider->setDayOffList(new Collection());

            /** @var Collection $sortedPeriods */
            $sortedWeekDays = new Collection();

            $allWeekDaysIndexes = [];

            /** @var WeekDay $weekDay */
            foreach ($provider->getWeekDayList()->getItems() as $key => $weekDay) {
                $allWeekDaysIndexes[$weekDay->getDayIndex()->getValue()] = $key;
            }

            $allWeekDaysKeysKeys = array_keys($allWeekDaysIndexes);

            sort($allWeekDaysKeysKeys);

            foreach ($allWeekDaysKeysKeys as $weekDayIndex) {
                $sortedWeekDays->addItem($provider->getWeekDayList()->getItem($allWeekDaysIndexes[$weekDayIndex]));
            }

            $provider->setWeekDayList($sortedWeekDays);
        }
    }

    /**
     *
     * @param Period|SpecialDayPeriod $period
     * @param Location                $providerLocation
     * @param Collection              $locations
     * @param bool                    $hasVisibleLocations
     *
     * @return Collection
     *
     * @throws ContainerValueNotFoundException
     * @throws InvalidArgumentException
     */
    public function getProviderPeriodLocations($period, $providerLocation, $locations, $hasVisibleLocations)
    {
        /** @var Collection $availablePeriodLocations */
        $availablePeriodLocations = new Collection();

        if ($period->getPeriodLocationList()->length()) {
            /** @var PeriodLocation|SpecialDayPeriodLocation $periodLocation */
            foreach ($period->getPeriodLocationList()->getItems() as $periodLocation) {
                if ($providerLocation &&
                    $periodLocation->getLocationId()->getValue() === $providerLocation->getId()->getValue() &&
                    ($hasVisibleLocations ? $providerLocation->getStatus()->getValue() === Status::VISIBLE : true)
                ) {
                    $availablePeriodLocations->addItem($providerLocation, $providerLocation->getId()->getValue());
                }
            }

            /** @var PeriodLocation|SpecialDayPeriodLocation $periodLocation */
            foreach ($period->getPeriodLocationList()->getItems() as $periodLocation) {
                /** @var Location $availableLocation */
                $availableLocation = $locations->keyExists($periodLocation->getLocationId()->getValue()) ?
                    $locations->getItem($periodLocation->getLocationId()->getValue()) : null;

                if ($availableLocation &&
                    (
                    $providerLocation ?
                        $periodLocation->getLocationId()->getValue() !== $providerLocation->getId()->getValue() : true
                    ) &&
                    ($hasVisibleLocations ? $availableLocation->getStatus()->getValue() === Status::VISIBLE : true)
                ) {
                    $availablePeriodLocations->addItem($availableLocation, $availableLocation->getId()->getValue());
                }
            }
        } elseif ($period->getLocationId() && $period->getLocationId()->getValue()) {
            /** @var Location $availableLocation */
            $availableLocation = $locations->keyExists($period->getLocationId()->getValue()) ?
                $locations->getItem($period->getLocationId()->getValue()) : null;

            if ($availableLocation &&
                ($hasVisibleLocations ? $availableLocation->getStatus()->getValue() === Status::VISIBLE : true)
            ) {
                $availablePeriodLocations->addItem($availableLocation, $availableLocation->getId()->getValue());
            }
        } elseif ($providerLocation &&
            ($hasVisibleLocations ? $providerLocation->getStatus()->getValue() === Status::VISIBLE : true)
        ) {
            $availablePeriodLocations->addItem($providerLocation, $providerLocation->getId()->getValue());
        }

        return $availablePeriodLocations;
    }

    /**
     * @param Provider   $provider
     * @param Collection $services
     * @param bool       $allowHiddenServices
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function setProviderServices($provider, $services, $allowHiddenServices)
    {
        /** @var Collection $providerServiceList */
        $providerServiceList = new Collection();

        /** @var Service $providerService */
        foreach ($provider->getServiceList()->getItems() as $providerService) {
            if ($services->keyExists($providerService->getId()->getValue())) {
                /** @var Service $service */
                $service = $services->getItem($providerService->getId()->getValue());

                if ($allowHiddenServices ? true : $service->getStatus()->getValue() === Status::VISIBLE) {
                    $providerServiceList->addItem(
                        ServiceFactory::create(
                            array_merge(
                                $service->toArray(),
                                [
                                    'price'         => $providerService->getPrice()->getValue(),
                                    'minCapacity'   => $providerService->getMinCapacity()->getValue(),
                                    'maxCapacity'   => $providerService->getMaxCapacity()->getValue(),
                                    'customPricing' => $providerService->getCustomPricing() ?
                                        $providerService->getCustomPricing()->getValue() : null,
                                ]
                            )
                        ),
                        $service->getId()->getValue()
                    );
                }
            }
        }

        $provider->setServiceList($providerServiceList);
    }

    /**
     * Add appointments to provider's appointments list
     *
     * @param Collection $providers
     * @param Collection $appointments
     * @param bool       $isGloballyBusySlot
     *
     * @throws InvalidArgumentException
     */
    public function addAppointmentsToAppointmentList($providers, $appointments, $isGloballyBusySlot)
    {
        $appointmentsDateData = [];

        $appointmentsIdOrder = [];

        /** @var Appointment $appointment */
        foreach ($appointments->getItems() as $appointmentId => $appointment) {
            $providerId = $appointment->getProviderId()->getValue();

            $appointmentStart = $appointment->getBookingStart()->getValue()->format('Y-m-d H:i:s');

            $appointmentEnd = $appointment->getBookingEnd()->getValue()->format('Y-m-d H:i:s');

            if (!array_key_exists($providerId, $appointmentsDateData)) {
                $appointmentsDateData[$providerId] = [
                ];
            }

            $appointmentsIdOrder[] = $appointmentId;

            $lastIndex = sizeof($appointmentsIdOrder) - 1;

            if (!array_key_exists($appointmentStart, $appointmentsDateData[$providerId])) {
                $appointmentsDateData[$providerId][$appointmentStart] = [
                    'id'    => $appointmentId,
                    'end'   => $appointmentEnd,
                    'index' => $lastIndex
                ];
            } else if ($appointmentsDateData[$providerId][$appointmentStart]['end'] !== $appointmentEnd &&
                DateTimeService::getCustomDateTimeObject($appointmentEnd) >
                DateTimeService::getCustomDateTimeObject($appointmentsDateData[$providerId][$appointmentStart]['end'])
            ) {
                $appointmentsIdOrder[$appointmentsDateData[$providerId][$appointmentStart]['index']] = $appointmentId;

                $appointmentsIdOrder[$lastIndex] = $appointmentsDateData[$providerId][$appointmentStart]['id'];
            }
        }

        $providerStarts = [];

        foreach ($appointmentsIdOrder as $index) {
            /** @var Appointment $appointment */
            $appointment = $appointments->getItem($index);

            /** @var Provider $provider */
            foreach ($providers->getItems() as $provider) {
                if (!array_key_exists($provider->getId()->getValue(), $providerStarts)) {
                    $providerStarts[$provider->getId()->getValue()] = [];
                }

                $appointmentStartString = $appointment->getBookingStart()->getValue()->format('Y-m-d H:i:s');

                if ($appointment->getProviderId()->getValue() === $provider->getId()->getValue() &&
                    array_key_exists($appointmentStartString, $providerStarts[$provider->getId()->getValue()])
                ) {
                    /** @var Appointment $duplicatedAppointment */
                    foreach ($provider->getAppointmentList()->getItems() as $duplicatedAppointment) {
                        if ($duplicatedAppointment->getId() &&
                            in_array(
                                $duplicatedAppointment->getId()->getValue(),
                                $providerStarts[$provider->getId()->getValue()][$appointmentStartString]
                            )
                        ) {
                            /** @var CustomerBooking $booking */
                            foreach ($appointment->getBookings()->getItems() as $booking) {
                                $duplicatedAppointment->getBookings()->addItem($booking, $booking->getId()->getValue());
                            }
                        }
                    }

                    continue;
                }

                if ($appointment->getProviderId()->getValue() === $provider->getId()->getValue()) {
                    $providerStarts[$provider->getId()->getValue()][$appointmentStartString][] = $appointment->getId()->getValue();

                    $provider->getAppointmentList()->addItem($appointment);

                    if (!$isGloballyBusySlot) {
                        break;
                    }
                } else if ($isGloballyBusySlot) {
                    $providerStarts[$provider->getId()->getValue()][$appointmentStartString][] = $appointment->getId()->getValue();

                    /** @var Appointment $fakeAppointment */
                    $fakeAppointment = AppointmentFactory::create(
                        array_merge(
                            $appointment->toArray(),
                            ['providerId' => $provider->getId()->getValue()]
                        )
                    );

                    if (!$fakeAppointment->getService()->getTimeBefore()) {
                        $fakeAppointment->getService()->setTimeBefore(new Duration(0));
                    }

                    if (!$fakeAppointment->getService()->getTimeAfter()) {
                        $fakeAppointment->getService()->setTimeAfter(new Duration(0));
                    }

                    $provider->getAppointmentList()->addItem($fakeAppointment);
                }
            }
        }
    }

    /**
     * @param Provider $provider
     * @param array    $globalDaysOff
     * @param DateTime $startDateTime
     * @param DateTime $endDateTime
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function modifyProviderTimeZone($provider, $globalDaysOff, $startDateTime, $endDateTime)
    {
        /** @var Appointment $appointment */
        foreach ($provider->getAppointmentList()->getItems() as $appointment) {
            $appointment->getBookingStart()->getValue()->setTimezone(
                new DateTimeZone($provider->getTimeZone()->getValue())
            );

            $appointment->getBookingEnd()->getValue()->setTimezone(
                new DateTimeZone($provider->getTimeZone()->getValue())
            );
        }

        /** @var SpecialDay $specialDay */
        foreach ($provider->getSpecialDayList()->getItems() as $specialDay) {
            $specialDay->setStartDate(
                new DateTimeValue(
                    DateTimeService::getDateTimeObjectInTimeZone(
                        $specialDay->getStartDate()->getValue()->format('Y-m-d') . ' 00:00:00',
                        $provider->getTimeZone()->getValue()
                    )
                )
            );

            $specialDay->setEndDate(
                new DateTimeValue(
                    DateTimeService::getDateTimeObjectInTimeZone(
                        $specialDay->getEndDate()->getValue()->format('Y-m-d') . ' 00:00:00',
                        $provider->getTimeZone()->getValue()
                    )
                )
            );
        }

        /** @var DayOff $dayOff */
        foreach ($provider->getDayOffList()->getItems() as $dayOff) {
            $dayOff->setStartDate(
                new DateTimeValue(
                    DateTimeService::getDateTimeObjectInTimeZone(
                        $dayOff->getStartDate()->getValue()->format('Y-m-d') . ' 00:00:00',
                        $provider->getTimeZone()->getValue()
                    )
                )
            );

            $dayOff->setEndDate(
                new DateTimeValue(
                    DateTimeService::getDateTimeObjectInTimeZone(
                        $dayOff->getEndDate()->getValue()->format('Y-m-d') . ' 00:00:00',
                        $provider->getTimeZone()->getValue()
                    )
                )
            );
        }

        $yearsDiff = $startDateTime->diff($endDateTime)->format('%y');

        $startYear = $startDateTime->format('Y');

        /** @var Collection $fakeAppointments */
        $fakeAppointments = new Collection();

        foreach ($globalDaysOff as $globalDayOff) {
            $dayOffParts = explode('-', $globalDayOff);

            if (sizeof($dayOffParts) === 2) {
                for ($i = 0; $i <= $yearsDiff; $i++) {
                    $dateOffStart = DateTimeService::getCustomDateTimeObject(
                        $startYear . '-' . $globalDayOff . ' 00:00'
                    )->modify("+$i years");

                    $dateOffEnd = DateTimeService::getCustomDateTimeObject(
                        $startYear . '-' . $globalDayOff . ' 00:00'
                    )->modify("+$i years")->modify('+1 days');

                    /** @var Appointment $fakeAppointment */
                    $fakeAppointment = AppointmentFactory::create(
                        [
                            'bookingStart'       => $dateOffStart->format('Y-m-d H:i'),
                            'bookingEnd'         => $dateOffEnd->format('Y-m-d H:i'),
                            'notifyParticipants' => false,
                            'serviceId'          => 0,
                            'providerId'         => $provider->getId()->getValue(),
                        ]
                    );

                    $fakeAppointment->getBookingStart()->getValue()->setTimezone(
                        new DateTimeZone($provider->getTimeZone()->getValue())
                    );

                    $fakeAppointment->getBookingEnd()->getValue()->setTimezone(
                        new DateTimeZone($provider->getTimeZone()->getValue())
                    );

                    $fakeAppointments->addItem($fakeAppointment);
                }
            } elseif (sizeof($dayOffParts) === 3) {
                /** @var Appointment $fakeAppointment */
                $fakeAppointment = AppointmentFactory::create(
                    [
                        'bookingStart'       => $globalDayOff . ' 00:00',
                        'bookingEnd'         => DateTimeService::getCustomDateTimeObject(
                            $globalDayOff . ' 00:00'
                        )->modify('+1 days')->format('Y-m-d H:i'),
                        'notifyParticipants' => false,
                        'serviceId'          => 0,
                        'providerId'         => $provider->getId()->getValue(),
                    ]
                );

                $fakeAppointment->getBookingStart()->getValue()->setTimezone(
                    new DateTimeZone($provider->getTimeZone()->getValue())
                );

                $fakeAppointment->getBookingEnd()->getValue()->setTimezone(
                    new DateTimeZone($provider->getTimeZone()->getValue())
                );

                $fakeAppointments->addItem($fakeAppointment);
            }
        }

        /** @var Appointment $fakeAppointment */
        foreach ($fakeAppointments->getItems() as $fakeAppointment) {
            $provider->getAppointmentList()->addItem($fakeAppointment);
        }
    }
}
