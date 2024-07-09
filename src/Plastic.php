<?php

declare(strict_types=1);

namespace Avmg\PhpSimpleUtilities;

use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Exception;

class Plastic extends DateTime
{
    protected array $translations = [
        'year' => 'year',
        'years' => 'years',
        'month' => 'month',
        'months' => 'months',
        'day' => 'day',
        'days' => 'days',
        'hour' => 'hour',
        'hours' => 'hours',
        'minute' => 'minute',
        'minutes' => 'minutes',
        'second' => 'second',
        'seconds' => 'seconds',
        'just now' => 'just now',
        'and' => ' and ',
        'ago' => '%s ago',
        'in' => 'in %s',
    ];

    /**
     * Create a new Plastic instance representing the current time.
     *
     * @param string|null $timezone Optional. The timezone in which to create the instance.
     * @return static The new instance.
     * @throws Exception If the timezone is invalid.
     */
    public static function now(?string $timezone = null): static
    {
        return new static('now', new DateTimeZone($timezone ?: date_default_timezone_get()));
    }

    /**
     * Create a new Plastic instance from a specific date and time.
     *
     * @param string|DateTimeInterface $datetime The date and time to create the instance from.
     * @param string|null $timezone Optional. The timezone in which to create the instance.
     * @return static The new instance.
     * @throws Exception If the timezone is invalid.
     */
    public static function parse(string|DateTimeInterface $datetime, ?string $timezone = null): static
    {
        return $datetime instanceof DateTimeInterface ? new static($datetime->format('Y-m-d H:i:s'), $datetime->getTimezone()) : new static($datetime, new DateTimeZone($timezone ?: date_default_timezone_get()));
    }

    /**
     * Create a new copy of the existing instance.
     *
     * @return static
     */
    public function copy(): static
    {
        return clone $this;
    }

    /**
     * Add a certain number of seconds to the instance.
     *
     * @param int $seconds The number of seconds to add.
     * @return static The new instance.
     * @throws Exception
     */
    public function addSeconds(int $seconds): static
    {
        return self::parse($this)->add(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Subtract a certain number of seconds from the instance.
     *
     * @param int $seconds The number of seconds to subtract.
     * @return static The new instance.
     * @throws Exception
     */
    public function subSeconds(int $seconds): static
    {
        return self::parse($this)->sub(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Add a certain number of minutes to the instance.
     *
     * @param int $minutes The number of minutes to add.
     * @return static The new instance.
     * @throws Exception
     */
    public function addMinutes(int $minutes): static
    {
        return self::parse($this)->add(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Subtract a certain number of minutes from the instance.
     *
     * @param int $minutes The number of minutes to subtract.
     * @return static The new instance.
     * @throws Exception
     */
    public function subMinutes(int $minutes): static
    {
        return self::parse($this)->sub(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Add a certain number of hours to the instance.
     *
     * @param int $hours The number of hours to add.
     * @return static The new instance.
     * @throws Exception
     */
    public function addHours(int $hours): static
    {
        return self::parse($this)->add(new DateInterval("PT{$hours}H"));
    }

    /**
     * Subtract a certain number of hours from the instance.
     *
     * @param int $hours The number of hours to subtract.
     * @return static The new instance.
     * @throws Exception
     */
    public function subHours(int $hours): static
    {
        return self::parse($this)->sub(new DateInterval("PT{$hours}H"));
    }

    /**
     * Add a certain number of days to the instance.
     *
     * @param int $days The number of days to add.
     * @return static The new instance.
     * @throws Exception
     */
    public function addDays(int $days): static
    {
        return self::parse($this)->add(new DateInterval("P{$days}D"));
    }

    /**
     * Subtract a certain number of days from the instance.
     *
     * @param int $days The number of days to subtract.
     * @return static The new instance.
     * @throws Exception
     */
    public function subDays(int $days): static
    {
        return self::parse($this)->sub(new DateInterval("P{$days}D"));
    }

    /**
     * Add a certain number of months to the instance.
     *
     * @param int $months The number of months to add.
     * @return static The new instance.
     * @throws Exception
     */
    public function addMonths(int $months): static
    {
        return self::parse($this)->add(new DateInterval("P{$months}M"));
    }

    /**
     * Subtract a certain number of months from the instance.
     *
     * @param int $months The number of months to subtract.
     * @return static The new instance.
     * @throws Exception
     */
    public function subMonths(int $months): static
    {
        return self::parse($this)->sub(new DateInterval("P{$months}M"));
    }

    /**
     * Add a certain number of years to the instance.
     *
     * @param int $years The number of years to add.
     * @return static The new instance.
     * @throws Exception
     */
    public function addYears(int $years): static
    {
        return self::parse($this)->add(new DateInterval("P{$years}Y"));
    }

    /**
     * Subtract a certain number of years from the instance.
     *
     * @param int $years The number of years to subtract.
     * @return static The new instance.
     * @throws Exception
     */
    public function subYears(int $years): static
    {
        return self::parse($this)->sub(new DateInterval("P{$years}Y"));
    }

    /**
     * Get the difference in seconds between this instance and another date.
     *
     * @param DateTimeInterface $date The date to compare with.
     * @param bool $absolute Whether to return an absolute difference or not.
     * @return int The difference in seconds.
     */
    public function diffInSeconds(DateTimeInterface $date, bool $absolute = true): int
    {
        return $absolute ? abs($this->getTimestamp() - $date->getTimestamp()) : $this->getTimestamp() - $date->getTimestamp();
    }

    /**
     * Get the difference in minutes between this instance and another date.
     *
     * @param DateTimeInterface $date The date to compare with.
     * @param bool $absolute Whether to return an absolute difference or not.
     * @return int The difference in minutes.
     */
    public function diffInMinutes(DateTimeInterface $date, bool $absolute = true): int
    {
        return (int)($this->diffInSeconds($date, $absolute) / 60);
    }

    /**
     * Get the difference in hours between this instance and another date.
     *
     * @param DateTimeInterface $date The date to compare with.
     * @param bool $absolute Whether to return an absolute difference or not.
     * @return int The difference in hours.
     */
    public function diffInHours(DateTimeInterface $date, bool $absolute = true): int
    {
        return (int)($this->diffInMinutes($date, $absolute) / 60);
    }

    /**
     * Get the difference in days between this instance and another date.
     *
     * @param DateTimeInterface $date The date to compare with.
     * @param bool $absolute Whether to return an absolute difference or not.
     * @return int The difference in days.
     */
    public function diffInDays(DateTimeInterface $date, bool $absolute = true): int
    {
        return (int)$this->diff($date, $absolute)->format("%a");
    }

    /**
     * Set the instance to the start of the day.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function startOfDay(): static
    {
        return self::parse($this)->setTime(0, 0, 0);
    }

    /**
     * Set the instance to the end of the day.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function endOfDay(): static
    {
        return self::parse($this)->setTime(23, 59, 59);
    }

    /**
     * Set the instance to the start of the week.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function startOfWeek(): static
    {
        return self::parse($this)->modify('monday this week')->startOfDay();
    }

    /**
     * Set the instance to the end of the week.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function endOfWeek(): static
    {
        return self::parse($this)->modify('sunday this week')->endOfDay();
    }

    /**
     * Set the instance to the start of the month.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function startOfMonth(): static
    {
        return self::parse($this)->modify('first day of this month')->startOfDay();
    }

    /**
     * Set the instance to the end of the month.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function endOfMonth(): static
    {
        return self::parse($this)->modify('last day of this month')->endOfDay();
    }

    /**
     * Set the instance to the start of the year.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function startOfYear(): static
    {
        return self::parse($this)->modify('first day of january this year')->startOfDay();
    }

    /**
     * Set the instance to the end of the year.
     *
     * @return static The new instance.
     * @throws Exception
     */
    public function endOfYear(): static
    {
        return self::parse($this)->modify('last day of december this year')->endOfDay();
    }

    /**
     * Check if the current instance is today
     *
     * @return bool True if this is today, false otherwise.
     * @throws Exception
     */
    public function isToday(): bool
    {
        return $this->format('Y-m-d') === (new static('now', $this->getTimezone()))->format('Y-m-d');
    }

    /**
     * Check if the current instance is tomorrow
     *
     * @return bool True if this is tomorrow, false otherwise.
     * @throws Exception
     */
    public function isTomorrow(): bool
    {
        return $this->format('Y-m-d') === (new static('tomorrow', $this->getTimezone()))->format('Y-m-d');
    }

    /**
     * Check if the current instance is yesterday
     *
     * @return bool True if this is yesterday, false otherwise.
     * @throws Exception
     */
    public function isYesterday(): bool
    {
        return $this->format('Y-m-d') === (new static('yesterday', $this->getTimezone()))->format('Y-m-d');
    }

    /**
     * Check if the current instance is this week
     *
     * @return bool True if this is this week, false otherwise.
     * @throws Exception
     */
    public function isThisWeek(): bool
    {
        return (new static('now', $this->getTimezone()))->startOfWeek()->startOfDay() <= $this && (new static('now', $this->getTimezone()))->endOfWeek()->endOfDay() >= $this;
    }

    /**
     * Check if the current instance is this month
     *
     * @return bool True if this is this month, false otherwise.
     * @throws Exception
     */
    public function isThisMonth(): bool
    {
        return (new static('now', $this->getTimezone()))->startOfMonth()->startOfDay() <= $this && (new static('now', $this->getTimezone()))->endOfMonth()->endOfDay() >= $this;
    }

    /**
     * Check if the current instance is this year
     *
     * @return bool True if this is this year, false otherwise.
     * @throws Exception
     */
    public function isThisYear(): bool
    {
        return (new static('now', $this->getTimezone()))->startOfYear()->startOfDay() <= $this && (new static('now', $this->getTimezone()))->endOfYear()->endOfDay() >= $this;
    }

    /**
     * Check if the current instance is in the past compared to now or another date.
     *
     * @param DateTimeInterface|null $date The date to compare with, or null to compare with now.
     * @return bool True if this is in the past, false otherwise.
     * @throws Exception
     */
    public function lt(?DateTimeInterface $date = null): bool
    {
        return $this < ($date ?: new static('now', $this->getTimezone()));
    }

    /**
     * Check if the current instance is in the future compared to now or another date.
     *
     * @param DateTimeInterface|null $date The date to compare with, or null to compare with now.
     * @return bool True if this is in the future, false otherwise.
     * @throws Exception
     */
    public function gt(?DateTimeInterface $date = null): bool
    {
        return $this > ($date ?: new static('now', $this->getTimezone()));
    }

    /**
     *  Check if the current instance is in between two other dates.
     *
     * @param DateTimeInterface $start The start date.
     * @param DateTimeInterface $end The end date.
     * @return bool True if this is in between the two dates, false otherwise.
     * @throws Exception
     */
    public function isInBetween(DateTimeInterface $start, DateTimeInterface $end): bool
    {
        return $this->gt($start) && $this->lt($end);
    }

    /**
     * Returns the difference between two dates in a human-readable format with support for translations.
     *
     * @param DateTimeInterface|null $otherDate The date to compare with, or null to compare with now.
     * @param bool $absolute Removes the past/future tense, making it just '5 minutes', not '5 minutes ago' or 'in 5 minutes'.
     * @param int $segments The number of time segments to include in the string. Example "2 weeks, 4 hours,  5 minutes and 36 seconds" would be 4 segments.
     *                      We only include the highest segments, so this string would become "2 weeks and 4 hours".
     * @return string The human-readable difference.
     * @throws Exception
     */
    public function diffForHumans(?DateTimeInterface $otherDate = null, bool $absolute = false, int $segments = 2): string
    {
        $dateToCompare = $otherDate ?: new static('now', $this->getTimezone());
        $interval = $this->diff($dateToCompare);

        $formatMap = [
            'y' => 'year',
            'm' => 'month',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];

        // create an array of the parts that are not 0, then slice to limit segments
        $parts = [];
        foreach ($formatMap as $key => $text) {
            $value = $interval->$key;
            if ($value > 0) {
                $transKey = $value === 1 ? $text : $text . 's';
                $textTranslated = $this->translations[$transKey];
                $parts[] = $value . ' ' . $textTranslated;
            }
        }

        // Limit the number of parts to the number of segments required
        $parts = array_slice($parts, 0, $segments);

        // if the difference is less than a minute, return 'just now'
        if (empty($parts)) return $this->translations['just now'];

        // Only add 'and' before the last part if there are more than one part
        $result = count($parts) > 1 ? implode(', ', array_slice($parts, 0, -1)) . $this->translations['and'] . end($parts) : $parts[0];

        // if absolute is true, return the result without the tense
        if ($absolute) return $result;

        // return the result with the tense
        $tenseKey = $this < $dateToCompare ? 'ago' : 'in';
        return sprintf($this->translations[$tenseKey], $result);
    }

    /**
     * @return array|string[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param array $translations
     * @return $this
     */
    public function setTranslations(array $translations): Plastic
    {
        $this->translations = $translations;
        return $this;
    }
}