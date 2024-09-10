<?php

declare(strict_types=1);

namespace Koddlo\Es\Booking\Domain;

use DateTimeImmutable;
use Koddlo\Es\Shared\Domain\DomainEvent;
use Koddlo\Es\Shared\Domain\Version;

final readonly class WorkingDayCreated extends DomainEvent
{
    public const int EVENT_VERSION = 1;

    public const string EVENT_NAME = 'working_day_created';

    public string $stafferId;

    public string $date;

    /**
     * @var array<int, array{from: string, to: string}>
     */
    public array $workingHours;

    /**
     * @param array<int, array{from: string, to: string}> $workingHours
     */
    public function __construct(
        string $aggregateId,
        int $number,
        int $occurredAt,
        string $stafferId,
        string $date,
        array $workingHours
    ) {
        $this->stafferId = $stafferId;
        $this->date = $date;
        $this->workingHours = $workingHours;

        parent::__construct($aggregateId, self::EVENT_NAME, $number, self::EVENT_VERSION, $occurredAt);
    }

    /**
     * @param Duration[] $workingHours
     */
    public static function occur(
        WorkingDayId $aggregateId,
        Version $aggregateVersion,
        StafferId $stafferId,
        Date $date,
        array $workingHours
    ): self {
        $hours = [];
        foreach ($workingHours as $duration) {
            $hours[] = [
                'from' => $duration->getFrom()->toString(),
                'to' => $duration->getTo()->toString(),
            ];
        }

        return new self(
            $aggregateId->toString(),
            $aggregateVersion->asNumber(),
            (new DateTimeImmutable())->getTimestamp(),
            $stafferId->toString(),
            $date->toString(),
            $hours
        );
    }
}
