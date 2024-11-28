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

    public function testAddAndSubSeconds(): void
    {
        $plastic = new Plastic('2024-01-01 00:00:00');
        $plastic = $plastic->addSeconds(30);
        $this->assertEquals('2024-01-01 00:00:30', $plastic->format('Y-m-d H:i:s'));

        $plastic = $plastic->subSeconds(30);
        $this->assertEquals('2024-01-01 00:00:00', $plastic->format('Y-m-d H:i:s'));
    }

    public function testAddAndSubMinutes(): void
    {
        $plastic = new Plastic('2024-01-01 00:00');
        $plastic = $plastic->addMinutes(30);
        $this->assertEquals('2024-01-01 00:30', $plastic->format('Y-m-d H:i'));

        $plastic = $plastic->subMinutes(30);
        $this->assertEquals('2024-01-01 00:00', $plastic->format('Y-m-d H:i'));
    }

    public function testAddAndSubHours(): void
    {
        $plastic = new Plastic('2024-01-01');
        $plastic = $plastic->addHours(24);
        $this->assertEquals('2024-01-02', $plastic->format('Y-m-d'));

        $plastic = $plastic->subHours(24);
        $this->assertEquals('2024-01-01', $plastic->format('Y-m-d'));
    }

    public function testAddAndSubDays(): void
    {
        $plastic = new Plastic('2024-01-01');
        $plastic = $plastic->addDays(1);
        $this->assertEquals('2024-01-02', $plastic->format('Y-m-d'));

        $plastic = $plastic->subDays(2);
        $this->assertEquals('2023-12-31', $plastic->format('Y-m-d'));
    }

    public function testAddAndSubMonths(): void
    {
        $plastic = new Plastic('2024-02-01');
        $plastic = $plastic->addMonths(1);
        $this->assertEquals('2024-03-01', $plastic->format('Y-m-d'));

        $plastic = $plastic->subMonths(2);
        $this->assertEquals('2024-01-01', $plastic->format('Y-m-d'));
    }

    public function testAddAndSubYears(): void
    {
        $plastic = new Plastic('2024-02-01');
        $plastic = $plastic->addYears(1);
        $this->assertEquals('2025-02-01', $plastic->format('Y-m-d'));

        $plastic = $plastic->subYears(2);
        $this->assertEquals('2023-02-01', $plastic->format('Y-m-d'));
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

        $nextWeek = new Plastic();
        $nextWeek = $nextWeek->addDays(7);
        $this->assertFalse($nextWeek->isThisWeek());

        $lastWeek = new Plastic();
        $lastWeek = $lastWeek->subDays(7);
        $this->assertFalse($lastWeek->isThisWeek());
    }

    public function testIsThisMonth(): void
    {
        $now = new Plastic();
        $this->assertTrue($now->isThisMonth());

        $startOfMonth = $now->startOfMonth();
        $this->assertTrue($startOfMonth->isThisMonth());

        $endOfMonth = $now->endOfMonth();
        $this->assertTrue($endOfMonth->isThisMonth());

        $nextMonth = new Plastic();
        $nextMonth = $nextMonth->addDays(35);
        $this->assertFalse($nextMonth->isThisMonth());

        $lastMonth = new Plastic();
        $lastMonth = $lastMonth->subDays(35);
        $this->assertFalse($lastMonth->isThisMonth());
    }

    public function testIsThisYear(): void
    {
        $now = new Plastic();
        $this->assertTrue($now->isThisYear());

        $startOfYear = $now->startOfYear();
        $this->assertTrue($startOfYear->isThisYear());

        $endOfYear = $now->endOfYear();
        $this->assertTrue($endOfYear->isThisYear());

        $nextYear = new Plastic();
        $nextYear = $nextYear->addDays(366);
        $this->assertFalse($nextYear->isThisYear());

        $lastYear = new Plastic();
        $lastYear = $lastYear->subDays(366);
        $this->assertFalse($lastYear->isThisYear());
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

    public function testDiffForHumansNow()
    {
        $now = Plastic::parse('2024-02-23 10:30', 'Europe/Amsterdam');
        $compare = Plastic::parse('2024-02-23 10:30', 'Europe/Amsterdam');

        $this->assertEquals('just now', $now->diffForHumans($compare));
    }

    public function testDiffForHumansPast()
    {
        $oneHourAgo = Plastic::parse('2024-02-23 10:30')->subHours(1);
        $compare = Plastic::parse('2024-02-23 10:30');

        $this->assertEquals('1 hour ago', $oneHourAgo->diffForHumans($compare));
    }

    public function testDiffForHumansFuture()
    {
        $oneHourLater = Plastic::parse('2024-02-23 10:30')->addHours(1);
        $compare = Plastic::parse('2024-02-23 10:30');

        $this->assertEquals('in 1 hour', $oneHourLater->diffForHumans($compare));
    }

    public function testDiffForHumansAbsolute()
    {
        $fiveMinutesAgo = Plastic::parse('2024-02-23 10:30')->subMinutes(5)->subHours(1);
        $compare = Plastic::parse('2024-02-23 10:30');

        $this->assertEquals('1 hour and 5 minutes', $fiveMinutesAgo->diffForHumans($compare, absolute: true));
    }

    public function testParseWithTimezone()
    {
        $dateString = '2024-02-23 14:00:00';
        $timezone = 'Europe/London';
        $plastic = Plastic::parse($dateString, $timezone);

        $this->assertEquals($timezone, $plastic->getTimezone()->getName());
    }

    public function testParseWithInvalidTimezone()
    {
        $this->expectException(Exception::class);

        $dateString = '2024-02-23 14:00:00';
        $invalidTimezone = 'Invalid/Timezone';
        $plastic = Plastic::parse($dateString, $invalidTimezone);
    }

    public function testDiffForHumansSegments()
    {
        $date = Plastic::parse('2024-02-23 14:00:00');
        $complexDate = Plastic::parse('2025-04-28 19:00:00');

        // Only show the two most significant segments (year and months)
        $this->assertEquals('in 1 year and 2 months', $complexDate->diffForHumans($date, false, 2));

        // Show all available segments
        $this->assertEquals('in 1 year, 2 months and 5 days', $complexDate->diffForHumans($date, false, 3));
    }

    public function testDiffForHumansWithTranslations()
    {
        $twoDaysAgo = Plastic::parse('2024-02-21 10:36:35');
        $compare = Plastic::parse('2024-02-23 10:30');

        //dutch translations
        $twoDaysAgo->setTranslations([
            'year' => 'jaar',
            'years' => 'jaar',
            'month' => 'maand',
            'months' => 'maanden',
            'day' => 'dag',
            'days' => 'dagen',
            'hour' => 'uur',
            'hours' => 'uur',
            'minute' => 'minuut',
            'minutes' => 'minuten',
            'second' => 'seconde',
            'seconds' => 'seconden',
            'just now' => 'zojuist',
            'and' => ' en ',
            'ago' => '%s geleden',
            'in' => 'over %s',
        ]);

        $this->assertEquals('1 dag, 23 uur, 53 minuten en 25 seconden geleden', $twoDaysAgo->diffForHumans($compare, segments: 10));
    }

    public function testWeekdayChecks(): void
    {
        $monday = new Plastic('2024-02-19'); // Known Monday
        $this->assertTrue($monday->isMonday());
        $this->assertFalse($monday->isTuesday());
        $this->assertFalse($monday->isWednesday());
        $this->assertFalse($monday->isThursday());
        $this->assertFalse($monday->isFriday());
        $this->assertFalse($monday->isSaturday());
        $this->assertFalse($monday->isSunday());

        $friday = new Plastic('2024-02-23'); // Known Friday
        $this->assertFalse($friday->isMonday());
        $this->assertFalse($friday->isTuesday());
        $this->assertFalse($friday->isWednesday());
        $this->assertFalse($friday->isThursday());
        $this->assertTrue($friday->isFriday());
        $this->assertFalse($friday->isSaturday());
        $this->assertFalse($friday->isSunday());
    }

    public function testDateStringFormatters(): void
    {
        $plastic = new Plastic('2024-02-23 14:30:45');

        $this->assertEquals('2024-02-23 14:30:45', $plastic->toDateTimeString());
        $this->assertEquals('2024-02-23', $plastic->toDateString());
        $this->assertEquals('14:30:45', $plastic->toTimeString());
        $this->assertEquals(1708698645, $plastic->toTimeStamp());
    }

    public function testDateStringFormattersWithDifferentTimes(): void
    {
        // Test with single-digit hours/minutes/seconds
        $plastic = Plastic::parse('2024-02-23 09:05:02');

        $this->assertEquals('2024-02-23 09:05:02', $plastic->toDateTimeString());
        $this->assertEquals('2024-02-23', $plastic->toDateString());
        $this->assertEquals('09:05:02', $plastic->toTimeString());

        // Test with midnight
        $plastic = new Plastic('2024-02-23 00:00:00');

        $this->assertEquals('2024-02-23 00:00:00', $plastic->toDateTimeString());
        $this->assertEquals('2024-02-23', $plastic->toDateString());
        $this->assertEquals('00:00:00', $plastic->toTimeString());
    }

    public function testParseFromDateTime(): void
    {
        $original = new DateTime('2024-02-23 14:00:00', new DateTimeZone('Europe/Paris'));

        // Test preserving original timezone
        $plastic = Plastic::parse($original);
        $this->assertEquals('Europe/Paris', $plastic->getTimezone()->getName());
        $this->assertEquals($original->getTimestamp(), $plastic->getTimestamp());
    }

    public function testParseFromTimestamp(): void
    {
        $timestamp = 1708675200; // 2024-02-23 12:00:00 UTC

        $plastic = Plastic::parse($timestamp);
        $this->assertEquals('+00:00', $plastic->getTimezone()->getName());
        $this->assertEquals($timestamp, $plastic->getTimestamp());
    }

    public function testParseFromIsoString(): void
    {
        // Test ISO 8601 format
        $plastic = Plastic::parse('2024-02-23T14:00:00');
        $this->assertEquals('UTC', $plastic->getTimezone()->getName());
        $this->assertEquals('2024-02-23 14:00:00', $plastic->format('Y-m-d H:i:s'));

        // Test with specific timezone
        $plastic = Plastic::parse('2024-02-23T14:00:00', 'Europe/Paris');
        $this->assertEquals('Europe/Paris', $plastic->getTimezone()->getName());

        // Test ISO 8601 with timezone offset
        $plastic = Plastic::parse('2024-02-23T14:00:00+02:00');
        $this->assertEquals('+02:00', $plastic->getTimezone()->getName());
        $this->assertEquals('2024-02-23 14:00:00', $plastic->format('Y-m-d H:i:s'));
    }

    public function testParseFromSimpleString(): void
    {
        // Test simple datetime string
        $plastic = Plastic::parse('2024-02-23 14:00:00');
        $this->assertEquals('UTC', $plastic->getTimezone()->getName());
        $this->assertEquals('2024-02-23 14:00:00', $plastic->format('Y-m-d H:i:s'));

        // Test date only string
        $plastic = Plastic::parse('2024-02-23');
        $this->assertEquals('2024-02-23 00:00:00', $plastic->format('Y-m-d H:i:s'));

        // Test with specific timezone
        $plastic = Plastic::parse('2024-02-23 14:00:00', 'Europe/Paris');
        $this->assertEquals('Europe/Paris', $plastic->getTimezone()->getName());
    }

    public function testParseInvalidInput(): void
    {
        $this->expectException(Exception::class);
        Plastic::parse('invalid date string');
    }

    public function testParseInvalidTimezone(): void
    {
        $this->expectException(Exception::class);
        Plastic::parse('2024-02-23', 'Invalid/Timezone');
    }
}