<?php

declare(strict_types=1);

namespace Koddlo\Es\Tests\Unit\Booking\Domain;

use Koddlo\Es\Booking\Domain\AppointmentBooked;
use Koddlo\Es\Booking\Domain\Booker;
use Koddlo\Es\Booking\Domain\Duration;
use Koddlo\Es\Booking\Domain\Time;
use Koddlo\Es\Booking\Domain\WorkingDayId;
use Koddlo\Es\Shared\Domain\Version;
use PHPUnit\Framework\TestCase;

final class AppointmentBookedTest extends TestCase
{
    public function testOccurGivenDataIsValidShouldCreateEvent(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $version = Version::zero();
        $duration = Duration::restore(Time::fromString('10:30'), Time::fromString('12:00'));
        $booker = Booker::restore('John', 'Doe', 'john.doe@test.com');

        $SUT = AppointmentBooked::occur($id, $version, $duration, $booker);

        self::assertSame(AppointmentBooked::EVENT_NAME, $SUT->name);
        self::assertSame(AppointmentBooked::EVENT_VERSION, $SUT->version);
        self::assertSame($id->toString(), $SUT->aggregateId);
        self::assertSame($version->asNumber(), $SUT->number);
        self::assertEquals(
            [
                'from' => '10:30',
                'to' => '12:00',
            ],
            $SUT->duration
        );
        self::assertEquals(
            [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'email' => 'john.doe@test.com',
            ],
            $SUT->booker
        );
    }
}
