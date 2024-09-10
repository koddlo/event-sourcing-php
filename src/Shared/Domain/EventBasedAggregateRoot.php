<?php

declare(strict_types=1);

namespace Koddlo\Es\Shared\Domain;

abstract class EventBasedAggregateRoot
{
    private Version $version;

    /**
     * @var DomainEvent[]
     */
    private array $events = [];

    public function __construct()
    {
        $this->version = Version::zero();
    }

    public static function reconstitute(Id $id, DomainEvent ...$events): static
    {
        $aggregate = new static($id);

        foreach ($events as $event) {
            $aggregate->upgrade($event);
        }

        return $aggregate;
    }

    /**
     * @return DomainEvent[]
     */
    final public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    final public function version(): Version
    {
        return $this->version;
    }

    /**
     * @throws InvalidEventException
     */
    abstract protected function apply(DomainEvent $event): void;

    final protected function record(DomainEvent $event): void
    {
        $this->upgrade($event);

        $this->events[] = $event;
    }

    private function upgrade(DomainEvent $event): void
    {
        $this->apply($event);

        $this->nextVersion();
    }

    private function nextVersion(): void
    {
        $this->version = $this->version->next();
    }
}
