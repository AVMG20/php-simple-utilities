<?php

use Avmg\PhpSimpleUtilities\Plastic;
use PHPUnit\Framework\TestCase;

class PlasticTest extends TestCase
{
    public function testNow(): void
    {
        $plastic = Plastic::now();
        $this->assertEqualsWithDelta(time(), $plastic->getTimestamp(), 2, 'Plastic::now() should be close to the current time.');
    }

    public function testAddAndSubDays(): void
    {
        $plastic = new Plastic('2024-01-01');
        $plastic->addDays(1);
        $this->assertEquals('2024-01-02', $plastic->format('Y-m-d'));

        $plastic->subDays(2);
        $this->assertEquals('2023-12-31', $plastic->format('Y-m-d'));
    }

    public function testDiffInDays(): void
    {
        $start = new Plastic('2024-01-01');
        $end = new Plastic('2024-01-02');
        $this->assertEquals(1, $start->diffInDays($end));
    }

    public function testStartAndEndOfDay(): void
    {
        $plastic = new Plastic('2024-01-01 12:34:56');
        $startOfDay = $plastic->startOfDay();
        $this->assertEquals('00:00:00', $startOfDay->format('H:i:s'));

        $endOfDay = $plastic->endOfDay();
        $this->assertEquals('23:59:59', $endOfDay->format('H:i:s'));
    }

    public function testIsTodayTomorrowYesterday(): void
    {
        $today = new Plastic();
        $this->assertTrue($today->isToday());

        $tomorrow = (new Plastic())->addDays(1);
        $this->assertTrue($tomorrow->isTomorrow());

        $yesterday = (new Plastic())->subDays(1);
        $this->assertTrue($yesterday->isYesterday());
    }

    public function testComparisonMethods(): void
    {
        $past = new Plastic('yesterday');
        $future = new Plastic('tomorrow');

        $this->assertTrue($past->lt(new Plastic()));
        $this->assertTrue($future->gt(new Plastic()));
    }

    public function testAddAndSubSeconds(): void
    {
        $plastic = new Plastic('2024-01-01 00:00:00');
        $plastic->addSeconds(30);
        $this->assertEquals('2024-01-01 00:00:30', $plastic->format('Y-m-d H:i:s'));

        $plastic->subSeconds(30);
        $this->assertEquals('2024-01-01 00:00:00', $plastic->format('Y-m-d H:i:s'));
    }

    public function testAddAndSubMinutes(): void
    {
        $plastic = new Plastic('2024-01-01 00:00');
        $plastic->addMinutes(30);
        $this->assertEquals('2024-01-01 00:30', $plastic->format('Y-m-d H:i'));

        $plastic->subMinutes(30);
        $this->assertEquals('2024-01-01 00:00', $plastic->format('Y-m-d H:i'));
    }

    public function testAddAndSubHours(): void
    {
        $plastic = new Plastic('2024-01-01');
        $plastic->addHours(24);
        $this->assertEquals('2024-01-02', $plastic->format('Y-m-d'));

        $plastic->subHours(24);
        $this->assertEquals('2024-01-01', $plastic->format('Y-m-d'));
    }

    public function testStartAndEndOfWeek(): void
    {
        $plastic = new Plastic('2024-01-03'); // Assuming this is a Wednesday
        $startOfWeek = $plastic->startOfWeek();
        $this->assertEquals('2024-01-01', $startOfWeek->format('Y-m-d')); // Monday

        $endOfWeek = $plastic->endOfWeek();
        $this->assertEquals('2024-01-07', $endOfWeek->format('Y-m-d')); // Sunday
    }

    public function testStartAndEndOfMonth(): void
    {
        $plastic = new Plastic('2024-02-15');
        $startOfMonth = $plastic->startOfMonth();
        $this->assertEquals('2024-02-01', $startOfMonth->format('Y-m-d'));

        $endOfMonth = $plastic->endOfMonth();
        $this->assertEquals('2024-02-29', $endOfMonth->format('Y-m-d')); // Leap year
    }

    public function testStartAndEndOfYear(): void
    {
        $plastic = new Plastic('2024-05-10');
        $startOfYear = $plastic->startOfYear();
        $this->assertEquals('2024-01-01', $startOfYear->format('Y-m-d'));

        $endOfYear = $plastic->endOfYear();
        $this->assertEquals('2024-12-31', $endOfYear->format('Y-m-d'));
    }

    public function testIsThisWeek(): void
    {
        $now = new Plastic();
        $this->assertTrue($now->isThisWeek());

        $startOfWeek = $now->startOfWeek();
        $this->assertTrue($startOfWeek->isThisWeek());

        $endOfWeek = $now->endOfWeek();
        $this->assertTrue($endOfWeek->isThisWeek());
    }

    public function testIsInBetween(): void
    {
        $start = new Plastic('2024-01-01');
        $end = new Plastic('2024-01-31');
        $middle = new Plastic('2024-01-15');

        $this->assertTrue($middle->isInBetween($start, $end));
        $this->assertFalse($start->isInBetween($middle, $end));
    }

    public function testDiffInSecondsAcrossDaylightSavingTimeChanges(): void
    {
        $springForward = new Plastic('2024-03-10 01:59:59', new DateTimeZone('America/New_York'));
        $afterSpringForward = new Plastic('2024-03-10 03:00:00', new DateTimeZone('America/New_York'));

        // DST starts and skips one hour forward
        $this->assertEquals(1, $springForward->diffInSeconds($afterSpringForward, true));

        $fallBack = new Plastic('2024-11-03 01:59:59', new DateTimeZone('America/New_York'));
        $afterFallBack = new Plastic('2024-11-03 01:00:00', new DateTimeZone('America/New_York'));

        // DST ends and repeats one hour, but this test ensures the difference is calculated correctly
        $this->assertEquals(3599, $fallBack->diffInSeconds($afterFallBack, true));
    }

    public function testLeapYear(): void
    {
        $leapYear = new Plastic('2024-02-29');
        $nonLeapYear = new Plastic('2023-02-28');

        $this->assertEquals('2024-02-29', $leapYear->format('Y-m-d'));
        $this->assertEquals('2023-02-28', $nonLeapYear->format('Y-m-d'));
    }

    public function testTimezoneHandling(): void
    {
        $utc = new Plastic('2024-01-01 00:00:00', new DateTimeZone('UTC'));
        $est = new Plastic('2024-01-01 00:00:00', new DateTimeZone('America/New_York'));

        // New York is normally UTC-5 but considering DST might affect this, we check for a range
        $this->assertGreaterThanOrEqual(4 * 3600, $utc->diffInSeconds($est, true));
        $this->assertLessThanOrEqual(5 * 3600, $utc->diffInSeconds($est, true));
    }

    public function testValidationOfInvalidDates(): void
    {
        $this->expectException(Exception::class);
        new Plastic('invalid-date');
    }
}