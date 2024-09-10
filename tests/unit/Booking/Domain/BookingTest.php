<?php

declare(strict_types=1);

namespace Koddlo\Es\Tests\Unit\Booking\Domain;

use Koddlo\Es\Booking\Domain\Booker;
use Koddlo\Es\Booking\Domain\Booking;
use Koddlo\Es\Booking\Domain\Duration;
use Koddlo\Es\Booking\Domain\Time;
use PHPUnit\Framework\TestCase;

final class BookingTest extends TestCase
{
    public function testCreateWhenDataIsValidShouldCreateBooking(): void
    {
        $duration = Duration::create(Time::fromString('10:30'), Time::fromString('12:00'));
        $booker = Booker::create('John', 'Doe', 'john.doe@test.com');

        $SUT = Booking::create($duration, $booker);

        self::assertEquals($duration, $SUT->getDuration());
        self::assertEquals($booker, $SUT->getBooker());
    }

    public function testRestoreWhenDataIsValidShouldRestoreBooking(): void
    {
        $duration = Duration::restore(Time::fromString('10:30'), Time::fromString('12:00'));
        $booker = Booker::create('John', 'Doe', 'john.doe@test.com');

        $SUT = Booking::restore($duration, $booker);

        self::assertEquals($duration, $SUT->getDuration());
        self::assertEquals($booker, $SUT->getBooker());
    }
}
