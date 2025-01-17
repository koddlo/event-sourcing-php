<?php

declare(strict_types=1);

namespace Koddlo\Es\Booking\Domain;

interface WorkingDayRepository
{
    public function save(WorkingDay $workingDay): void;

    /**
     * @throws WorkingDayNotFound
     */
    public function get(WorkingDayId $id): WorkingDay;
}
