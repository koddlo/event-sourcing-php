<?php

declare(strict_types=1);

namespace Koddlo\Es\Shared\Domain;

abstract readonly class DomainEvent
{
    public function __construct(
        public string $aggregateId,
        public string $name,
        public int $number,
        public int $version,
        public int $occurredAt
    ) {
    }
}
