<?php

declare(strict_types=1);

namespace Koddlo\Es\Tests\Unit\Booking\Domain;

use Koddlo\Es\Booking\Domain\AppointmentBooked;
use Koddlo\Es\Booking\Domain\AppointmentCanceled;
use Koddlo\Es\Booking\Domain\Booker;
use Koddlo\Es\Booking\Domain\CannotBeBooked;
use Koddlo\Es\Booking\Domain\CannotBeCanceled;
use Koddlo\Es\Booking\Domain\Date;
use Koddlo\Es\Booking\Domain\Duration;
use Koddlo\Es\Booking\Domain\ServiceType;
use Koddlo\Es\Booking\Domain\StafferId;
use Koddlo\Es\Booking\Domain\Time;
use Koddlo\Es\Booking\Domain\WorkingDay;
use Koddlo\Es\Booking\Domain\WorkingDayCreated;
use Koddlo\Es\Booking\Domain\WorkingDayId;
use Koddlo\Es\Shared\Domain\Version;
use PHPUnit\Framework\TestCase;

final class WorkingDayTest extends TestCase
{
    public function testCreateWhenDataIsValidShouldCreateWorkingDay(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $stafferId = new StafferId('e35b98e1-3cec-494c-8b88-e715201e8b75');
        $date = Date::fromString('2023-01-01');
        $workingHours = [Duration::create(Time::fromString('10:30'), Time::fromString('12:00'))];
        $expectedEvent = WorkingDayCreated::occur($id, new Version(1), $stafferId, $date, $workingHours);

        $SUT = WorkingDay::create($id, $stafferId, $date, $workingHours);

        $events = $SUT->pullEvents();
        self::assertCount(1, $events);
        self::assertEquals($expectedEvent, $events[0]);
    }

    public function testBookWhenDataIsValidShouldBookAppointment(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $expectedEvent = AppointmentBooked::occur(
            $id,
            new Version(2),
            Duration::restore(Time::fromString('10:30'), Time::fromString('12:00')),
            Booker::restore('John', 'Doe', 'john.doe@test.com')
        );
        $SUT = WorkingDay::reconstitute(
            $id,
            new WorkingDayCreated(
                $id->toString(),
                1,
                1672560000,
                'e35b98e1-3cec-494c-8b88-e715201e8b75',
                '2023-01-01',
                [
                    [
                        'from' => '10:30',
                        'to' => '12:00',
                    ],
                ]
            )
        );

        $SUT->book(
            ServiceType::COMBO,
            Time::fromString('10:30'),
            Booker::create('John', 'Doe', 'john.doe@test.com')
        );

        $events = $SUT->pullEvents();
        self::assertCount(1, $events);
        self::assertEquals($expectedEvent, $events[0]);
    }

    public function testBookWhenBookIsNotWithinWorkingHoursShouldThrowException(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $SUT = WorkingDay::reconstitute(
            $id,
            new WorkingDayCreated(
                $id->toString(),
                1,
                1672560000,
                'e35b98e1-3cec-494c-8b88-e715201e8b75',
                '2023-01-01',
                []
            )
        );

        self::expectException(CannotBeBooked::class);

        $SUT->book(
            ServiceType::COMBO,
            Time::fromString('10:30'),
            Booker::create('John', 'Doe', 'john.doe@test.com')
        );
    }

    public function testBookWhenTimeIsAlreadyBookedShouldThrowException(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $version = new Version(2);
        $SUT = WorkingDay::reconstitute(
            $id,
            new WorkingDayCreated(
                $id->toString(),
                1,
                1672560000,
                'e35b98e1-3cec-494c-8b88-e715201e8b75',
                '2023-01-01',
                [
                    [
                        'from' => '10:30',
                        'to' => '12:00',
                    ],
                ]
            ),
            new AppointmentBooked(
                $id->toString(),
                $version->asNumber(),
                1672563600,
                [
                    'from' => '10:30',
                    'to' => '12:00',
                ],
                [
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'email' => 'john.doe@test.com',
                ],
            )
        );

        self::expectException(CannotBeBooked::class);

        $SUT->book(
            ServiceType::COMBO,
            Time::fromString('10:30'),
            Booker::create('John', 'Doe', 'john.doe@test.com')
        );
    }

    public function testCancelWhenBookingExistsShouldCancelAppointment(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $duration = Duration::restore(Time::fromString('10:30'), Time::fromString('12:00'));
        $booker = Booker::restore('John', 'Doe', 'john.doe@test.com');
        $expectedEvent = AppointmentCanceled::occur($id, new Version(3), $duration->getFrom());
        $SUT = WorkingDay::reconstitute(
            $id,
            new WorkingDayCreated(
                $id->toString(),
                1,
                1672560000,
                'e35b98e1-3cec-494c-8b88-e715201e8b75',
                '2023-01-01',
                [
                    [
                        'from' => '10:30',
                        'to' => '12:00',
                    ],
                ]
            ),
            new AppointmentBooked(
                $id->toString(),
                2,
                1672563600,
                [
                    'from' => $duration->getFrom()->toString(),
                    'to' => $duration->getTo()->toString(),
                ],
                [
                    'firstName' => $booker->getFirstName(),
                    'lastName' => $booker->getLastName(),
                    'email' => $booker->getEmail(),
                ],
            )
        );

        $SUT->cancel($duration->getFrom());

        $events = $SUT->pullEvents();
        self::assertCount(1, $events);
        self::assertEquals($expectedEvent, $events[0]);
    }

    public function testCancelWhenBookingDoesNotExistShouldThrowException(): void
    {
        $id = new WorkingDayId('7fd1ad2c-05b1-4814-af37-d74d81a69f1d');
        $SUT = WorkingDay::reconstitute(
            $id,
            new WorkingDayCreated(
                $id->toString(),
                1,
                1672560000,
                'e35b98e1-3cec-494c-8b88-e715201e8b75',
                '2023-01-01',
                [
                    [
                        'from' => '10:30',
                        'to' => '12:00',
                    ],
                ]
            )
        );

        self::expectException(CannotBeCanceled::class);

        $SUT->cancel(Time::fromString('10:30'));
    }
}
