<?php

declare(strict_types=1);

namespace Koddlo\Es\Booking\Domain;

final readonly class Booking
{
    private function __construct(
        private Duration $duration,
        private Booker $booker
    ) {
    }

    public static function create(
        Duration $duration,
        Booker $booker
    ): self {
        return new self($duration, $booker);
    }

    public static function restore(
        Duration $duration,
        Booker $booker
    ): self {
        return new self($duration, $booker);
    }

    public function getDuration(): Duration
    {
        return $this->duration;
    }

    public function getBooker(): Booker
    {
        return $this->booker;
    }
}
