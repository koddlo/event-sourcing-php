<?php

declare(strict_types=1);

namespace Koddlo\Es\Tests\Unit\Booking\Domain;

use Koddlo\Es\Booking\Domain\AppointmentCanceled;
use Koddlo\Es\Booking\Domain\Time;
use Koddlo\Es\Booking\Domain\WorkingDayId;
use Koddlo\Es\Shared\Domain\Version;
use PHPUnit\Framework\TestCase;

final class AppointmentCanceledTest extends TestCase
{
    public function testOccurGivenDataIsValidShouldCreateEvent(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $version = Version::zero();
        $time = Time::fromString('10:30');

        $SUT = AppointmentCanceled::occur($id, $version, $time);

        self::assertSame(AppointmentCanceled::EVENT_NAME, $SUT->name);
        self::assertSame(AppointmentCanceled::EVENT_VERSION, $SUT->version);
        self::assertSame($id->toString(), $SUT->aggregateId);
        self::assertSame($version->asNumber(), $SUT->number);
        self::assertEquals($time->toString(), $SUT->time);
    }
}
