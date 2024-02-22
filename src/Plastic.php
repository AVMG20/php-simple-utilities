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
     * Add a certain number of days to the instance.
     *
     * @param int $days The number of days to add.
     * @return static The new instance.
     */
    public function addDays(int $days): static
    {
        return $this->add(new DateInterval("P{$days}D"));
    }

    /**
     * Subtract a certain number of days from the instance.
     *
     * @param int $days The number of days to subtract.
     * @return static The new instance.
     */
    public function subDays(int $days): static
    {
        return $this->sub(new DateInterval("P{$days}D"));
    }

    /**
     * Add a certain number of seconds to the instance.
     *
     * @param int $seconds The number of seconds to add.
     * @return static The new instance.
     */
    public function addSeconds(int $seconds): static
    {
        return $this->add(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Subtract a certain number of seconds from the instance.
     *
     * @param int $seconds The number of seconds to subtract.
     * @return static The new instance.
     */
    public function subSeconds(int $seconds): static
    {
        return $this->sub(new DateInterval("PT{$seconds}S"));
    }

    /**
     * Add a certain number of minutes to the instance.
     *
     * @param int $minutes The number of minutes to add.
     * @return static The new instance.
     */
    public function addMinutes(int $minutes): static
    {
        return $this->add(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Subtract a certain number of minutes from the instance.
     *
     * @param int $minutes The number of minutes to subtract.
     * @return static The new instance.
     */
    public function subMinutes(int $minutes): static
    {
        return $this->sub(new DateInterval("PT{$minutes}M"));
    }

    /**
     * Add a certain number of hours to the instance.
     *
     * @param int $hours The number of hours to add.
     * @return static The new instance.
     */
    public function addHours(int $hours): static
    {
        return $this->add(new DateInterval("PT{$hours}H"));
    }

    /**
     * Subtract a certain number of hours from the instance.
     *
     * @param int $hours The number of hours to subtract.
     * @return static The new instance.
     */
    public function subHours(int $hours): static
    {
        return $this->sub(new DateInterval("PT{$hours}H"));
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
        return (int) ($this->diffInSeconds($date, $absolute) / 60);
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
        return (int) ($this->diffInMinutes($date, $absolute) / 60);
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
        return (int) $this->diff($date)->format("%a");
    }

    /**
     * Set the instance to the start of the day.
     *
     * @return static The new instance.
     */
    public function startOfDay(): static
    {
        return $this->setTime(0, 0, 0);
    }

    /**
     * Set the instance to the end of the day.
     *
     * @return static The new instance.
     */
    public function endOfDay(): static
    {
        return $this->setTime(23, 59, 59);
    }

    /**
     * Set the instance to the start of the week.
     *
     * @return static The new instance.
     */
    public function startOfWeek(): static
    {
        return $this->modify('monday this week')->startOfDay();
    }

    /**
     * Set the instance to the end of the week.
     *
     * @return static
     */
    public function endOfWeek(): static
    {
        return $this->modify('sunday this week')->endOfDay();
    }

    /**
     * Set the instance to the start of the month.
     *
     * @return static The new instance.
     */
    public function startOfMonth(): static
    {
        return $this->modify('first day of this month')->startOfDay();
    }

    /**
     * Set the instance to the end of the month.
     *
     * @return static The new instance.
     */
    public function endOfMonth(): static
    {
        return $this->modify('last day of this month')->endOfDay();
    }

    /**
     * Set the instance to the start of the year.
     *
     * @return static The new instance.
     */
    public function startOfYear(): static
    {
        return $this->modify('first day of january this year')->startOfDay();
    }

    /**
     * Set the instance to the end of the year.
     *
     * @return static The new instance.
     */
    public function endOfYear(): static
    {
        return $this->modify('last day of december this year')->endOfDay();
    }

    /**
     * Check if the current instance is today
     *
     * @return bool True if this is today, false otherwise.
     */
    public function isToday(): bool
    {
        return $this->format('Y-m-d') === (new static())->format('Y-m-d');
    }

    /**
     * Check if the current instance is tomorrow
     *
     * @return bool True if this is tomorrow, false otherwise.
     */
    public function isTomorrow(): bool
    {
        return $this->format('Y-m-d') === (new static('tomorrow'))->format('Y-m-d');
    }

    /**
     * Check if the current instance is yesterday
     *
     * @return bool True if this is yesterday, false otherwise.
     */
    public function isYesterday(): bool
    {
        return $this->format('Y-m-d') === (new static('yesterday'))->format('Y-m-d');
    }

    /**
     * Check if the current instance is this week
     *
     * @return bool True if this is this week, false otherwise.
     */
    public function isThisWeek(): bool
    {
        return $this->startOfWeek() <= $this && $this->endOfWeek() >= $this;
    }

    /**
     * Check if the current instance is in the past compared to now or another date.
     *
     * @param DateTimeInterface|null $date The date to compare with, or null to compare with now.
     * @return bool True if this is in the past, false otherwise.
     */
    public function lt(?DateTimeInterface $date = null): bool
    {
        return $this < ($date ?: new static());
    }

    /**
     * Check if the current instance is in the future compared to now or another date.
     *
     * @param DateTimeInterface|null $date The date to compare with, or null to compare with now.
     * @return bool True if this is in the future, false otherwise.
     */
    public function gt(?DateTimeInterface $date = null): bool
    {
        return $this > ($date ?: new static());
    }

    /**
     *  Check if the current instance is in between two other dates.
     *
     * @param DateTimeInterface $start The start date.
     * @param DateTimeInterface $end The end date.
     * @return bool True if this is in between the two dates, false otherwise.
     */
    public function isInBetween(DateTimeInterface $start, DateTimeInterface $end): bool
    {
        return $this->gt($start) && $this->lt($end);
    }
}