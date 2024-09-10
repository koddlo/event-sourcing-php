<?php

declare(strict_types=1);

namespace Koddlo\Es\Tests\Unit\Booking\Domain;

use Koddlo\Es\Booking\Domain\Date;
use Koddlo\Es\Booking\Domain\Duration;
use Koddlo\Es\Booking\Domain\StafferId;
use Koddlo\Es\Booking\Domain\Time;
use Koddlo\Es\Booking\Domain\WorkingDayCreated;
use Koddlo\Es\Booking\Domain\WorkingDayId;
use Koddlo\Es\Shared\Domain\Version;
use PHPUnit\Framework\TestCase;

final class WorkingDayCreatedTest extends TestCase
{
    public function testOccurGivenDataIsValidShouldCreateEvent(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $version = Version::zero();
        $stafferId = new StafferId('e35b98e1-3cec-494c-8b88-e715201e8b75');
        $date = Date::fromString('2023-01-01');
        $workingHours = [Duration::restore(Time::fromString('10:30'), Time::fromString('12:00'))];

        $SUT = WorkingDayCreated::occur($id, $version, $stafferId, $date, $workingHours);

        self::assertSame($id->toString(), $SUT->aggregateId);
        self::assertSame($version->asNumber(), $SUT->number);
        self::assertSame($stafferId->toString(), $SUT->stafferId);
        self::assertSame($date->toString(), $SUT->date);
        self::assertSame(
            [
                [
                    'from' => '10:30',
                    'to' => '12:00',
                ],
            ],
            $SUT->workingHours
        );
        self::assertSame(WorkingDayCreated::EVENT_NAME, $SUT->name);
        self::assertSame(WorkingDayCreated::EVENT_VERSION, $SUT->version);
    }
}
