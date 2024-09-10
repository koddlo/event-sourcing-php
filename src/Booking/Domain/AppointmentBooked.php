<?php

declare(strict_types=1);

namespace Koddlo\Es\Booking\Domain;

use DateTimeImmutable;
use Koddlo\Es\Shared\Domain\DomainEvent;
use Koddlo\Es\Shared\Domain\Version;

final readonly class AppointmentBooked extends DomainEvent
{
    public const int EVENT_VERSION = 1;

    public const string EVENT_NAME = 'appointment_booked';

    /**
     * @var array{from: string, to: string}
     */
    public array $duration;

    /**
     * @var array{firstName: string, lastName: string, email: string}
     */
    public array $booker;

    /**
     * @param array{from: string, to: string}                           $duration
     * @param array{firstName: string, lastName: string, email: string} $booker
     */
    public function __construct(
        string $aggregateId,
        int $number,
        int $occurredAt,
        array $duration,
        array $booker
    ) {
        $this->duration = $duration;
        $this->booker = $booker;

        parent::__construct($aggregateId, self::EVENT_NAME, $number, self::EVENT_VERSION, $occurredAt);
    }

    public static function occur(
        WorkingDayId $aggregateId,
        Version $aggregateVersion,
        Duration $duration,
        Booker $booker
    ): self {
        return new self(
            $aggregateId->toString(),
            $aggregateVersion->asNumber(),
            (new DateTimeImmutable())->getTimestamp(),
            [
                'from' => $duration->getFrom()->toString(),
                'to' => $duration->getTo()->toString(),
            ],
            [
                'firstName' => $booker->getFirstName(),
                'lastName' => $booker->getLastName(),
                'email' => $booker->getEmail(),
            ]
        );
    }
}
