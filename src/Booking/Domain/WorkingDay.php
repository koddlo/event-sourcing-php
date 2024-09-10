<?php

declare(strict_types=1);

namespace Koddlo\Es\Booking\Domain;

use Koddlo\Es\Shared\Domain\DomainEvent;
use Koddlo\Es\Shared\Domain\EventBasedAggregateRoot;
use Koddlo\Es\Shared\Domain\InvalidEventException;

final class WorkingDay extends EventBasedAggregateRoot
{
    private WorkingDayId $id;

    private StafferId $stafferId;

    private Date $date;

    /**
     * @var Duration[]
     */
    private array $workingHours;

    /**
     * @var Booking[]
     */
    private array $bookings;

    protected function __construct(
        WorkingDayId $id
    ) {
        $this->id = $id;
        $this->bookings = [];

        parent::__construct();
    }

    /**
     * @param Duration[] $workingHours
     */
    public static function create(
        WorkingDayId $id,
        StafferId $stafferId,
        Date $date,
        array $workingHours
    ): self {
        $workingDay = new self($id);
        $workingDay->record(
            WorkingDayCreated::occur($id, $workingDay->version()->next(), $stafferId, $date, $workingHours)
        );

        return $workingDay;
    }

    /**
     * @throws CannotBeBooked
     */
    public function book(ServiceType $serviceType, Time $time, Booker $booker): void
    {
        $duration = Duration::create(
            $time,
            $time->addMinutes($serviceType->durationInMinutes())
        );

        if (! $this->isAvailable($duration)) {
            throw new CannotBeBooked();
        }

        $this->record(
            AppointmentBooked::occur($this->id, $this->version()->next(), $duration, $booker)
        );
    }

    public function cancel(Time $time): void
    {
        if (! isset($this->bookings[$time->toString()])) {
            throw new CannotBeCanceled();
        }

        $this->record(AppointmentCanceled::occur($this->id, $this->version()->next(), $time));
    }

    protected function apply(DomainEvent $event): void
    {
        match ($event::class) {
            WorkingDayCreated::class => $this->applyWorkingDayCreated($event),
            AppointmentBooked::class => $this->applyAppointmentBooked($event),
            AppointmentCanceled::class => $this->applyAppointmentCanceled($event),
            default => throw new InvalidEventException()
        };
    }

    private function applyWorkingDayCreated(WorkingDayCreated $event): void
    {
        $this->stafferId = new StafferId($event->stafferId);
        $this->date = Date::fromString($event->date);
        $this->workingHours = array_map(
            static fn (array $duration) => Duration::create(
                Time::fromString($duration['from']),
                Time::fromString($duration['to'])
            ),
            $event->workingHours
        );
    }

    private function applyAppointmentBooked(AppointmentBooked $event): void
    {
        $this->bookings[$event->duration['from']] = Booking::create(
            Duration::create(Time::fromString($event->duration['from']), Time::fromString($event->duration['to'])),
            Booker::create($event->booker['firstName'], $event->booker['lastName'], $event->booker['email'])
        );
    }

    private function applyAppointmentCanceled(AppointmentCanceled $event): void
    {
        unset($this->bookings[$event->time]);
    }

    private function isAvailable(Duration $duration): bool
    {
        if (! $this->isDurationWithinWorkingHours($duration)) {
            return false;
        }

        if ($this->isAlreadyBooked($duration)) {
            return false;
        }

        return true;
    }

    private function isDurationWithinWorkingHours(Duration $duration): bool
    {
        foreach ($this->workingHours as $hourRange) {
            if ($hourRange->isWithin($duration)) {
                return true;
            }
        }

        return false;
    }

    private function isAlreadyBooked(Duration $duration): bool
    {
        foreach ($this->bookings as $booking) {
            if ($duration->isOverlapping($booking->getDuration())) {
                return true;
            }
        }

        return false;
    }
}
