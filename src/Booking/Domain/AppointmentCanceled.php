<?php

declare(strict_types=1);

namespace Koddlo\Es\Booking\Domain;

use DateTimeImmutable;
use Koddlo\Es\Shared\Domain\DomainEvent;
use Koddlo\Es\Shared\Domain\Version;

final readonly class AppointmentCanceled extends DomainEvent
{
    public const int EVENT_VERSION = 1;

    public const string EVENT_NAME = 'appointment_canceled';

    public string $time;

    public function __construct(
        string $aggregateId,
        int $number,
        int $occurredAt,
        string $time
    ) {
        $this->time = $time;

        parent::__construct($aggregateId, self::EVENT_NAME, $number, self::EVENT_VERSION, $occurredAt);
    }

    public static function occur(WorkingDayId $aggregateId, Version $aggregateVersion, Time $time): self
    {
        return new self(
            $aggregateId->toString(),
            $aggregateVersion->asNumber(),
            (new DateTimeImmutable())->getTimestamp(),
            $time->toString()
        );
    }
}
