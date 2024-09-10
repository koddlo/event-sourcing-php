<?php

declare(strict_types=1);

namespace Koddlo\Es\Shared\Domain;

final readonly class Version
{
    private const int MIN_VERSION = 0;

    public function __construct(
        private int $version
    ) {
        $this->guard();
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function next(): self
    {
        return new self($this->version + 1);
    }

    public function asNumber(): int
    {
        return $this->version;
    }

    private function guard(): void
    {
        if ($this->version < self::MIN_VERSION) {
            throw new InvalidVersionException('Invalid aggregate version number.');
        }
    }
}
