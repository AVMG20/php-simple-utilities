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

    public function testAddAndSubDays(): void
    {
        $plastic = new Plastic('2024-01-01');
        $plastic->addDays(1);
        $this->assertEquals('2024-01-02', $plastic->format('Y-m-d'));

        $plastic->subDays(2);
        $this->assertEquals('2023-12-31', $plastic->format('Y-m-d'));
    }

    public function testAddAndSubMonths(): void
    {
        $plastic = new Plastic('2024-02-01');
        $plastic->addMonths(1);
        $this->assertEquals('2024-03-01', $plastic->format('Y-m-d'));

        $plastic->subMonths(2);
        $this->assertEquals('2024-01-01', $plastic->format('Y-m-d'));
    }

    public function testAddAndSubYears(): void
    {
        $plastic = new Plastic('2024-02-01');
        $plastic->addYears(1);
        $this->assertEquals('2025-02-01', $plastic->format('Y-m-d'));

        $plastic->subYears(2);
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
        $nextWeek->addDays(7);
        $this->assertFalse($nextWeek->isThisWeek());

        $lastWeek = new Plastic();
        $lastWeek->subDays(7);
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
        $nextMonth->addDays(35);
        $this->assertFalse($nextMonth->isThisMonth());

        $lastMonth = new Plastic();
        $lastMonth->subDays(35);
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
        $nextYear->addDays(366);
        $this->assertFalse($nextYear->isThisYear());

        $lastYear = new Plastic();
        $lastYear->subDays(366);
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
        $now = Plastic::parse('2024-02-23 10:30');
        $compare = Plastic::parse('2024-02-23 10:30');

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

    public function testParseFromString()
    {
        $dateString = '2024-02-23 14:00:00';
        $plastic = Plastic::parse($dateString);

        $this->assertInstanceOf(Plastic::class, $plastic);
        $this->assertEquals($dateString, $plastic->format('Y-m-d H:i:s'));
    }

    public function testParseFromDateTimeInterface()
    {
        $dateTime = new DateTime('2024-02-23 14:00:00', new DateTimeZone('UTC'));
        $plastic = Plastic::parse($dateTime);

        $this->assertInstanceOf(Plastic::class, $plastic);
        $this->assertEquals($dateTime->format('Y-m-d H:i:s'), $plastic->format('Y-m-d H:i:s'));
        $this->assertEquals($dateTime->getTimezone()->getName(), $plastic->getTimezone()->getName());
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
}