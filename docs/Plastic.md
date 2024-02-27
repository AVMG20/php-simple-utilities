# Plastic Date and Time Utility
The `Plastic` class extends PHP's native `DateTime` class, providing additional, convenient methods for date and time manipulation without external dependencies.

## All methods available in the Plastic class
- [constructor()](#constructor)   Constructs a new Plastic instance.
- [now()](#now)  Returns a new Plastic instance representing the current date and time.
- [parse()](#parse)  Create a new Plastic instance from a specific date and time.
- [addSeconds()](#addSeconds)  Adds a number of seconds to the date.
- [subSeconds()](#subSeconds)  Subtracts a number of seconds from the date.
- [addMinutes()](#addMinutes)  Adds a number of minutes to the date.
- [subMinutes()](#subMinutes)  Subtracts a number of minutes from the date.
- [addHours()](#addHours)  Adds a number of hours to the date.
- [subHours()](#subHours)  Subtracts a number of hours from the date.
- [addDays()](#addDays)  Adds a number of days to the date.
- [subDays()](#subDays)  Subtracts a number of days from the date.
- [diffInSeconds()](#diffInSeconds)  Returns the difference in seconds between two dates.
- [diffInMinutes()](#diffInMinutes)  Returns the difference in minutes between two dates.
- [diffInHours()](#diffInHours)  Returns the difference in hours between two dates.
- [diffInDays()](#diffInDays)  Returns the difference in days between two dates.
- [startOfDay()](#startOfDay)  Sets the time to 00:00:00.
- [endOfDay()](#endOfDay)  Sets the time to 23:59:59.
- [startOfWeek()](#startOfWeek)  Sets the date to the first day of the week.
- [endOfWeek()](#endOfWeek)  Sets the date to the last day of the week.
- [startOfMonth()](#startOfMonth)  Sets the date to the first day of the month.
- [endOfMonth()](#endOfMonth)  Sets the date to the last day of the month.
- [startOfYear()](#startOfYear)  Sets the date to the first day of the year.
- [endOfYear()](#endOfYear)  Sets the date to the last day of the year.
- [isToday()](#isToday)  Checks if the date is today.
- [isTomorrow()](#isTomorrow)  Checks if the date is tomorrow.
- [isYesterday()](#isYesterday)  Checks if the date is yesterday.
- [isThisWeek()](#isThisWeek)  Checks if the date is this week.
- [isThisMonth()](#isThisMonth)  Checks if the date is this month.
- [isThisYear()](#isThisYear)  Checks if the date is this year.
- [lt()](#lt)  Checks if the date is less than another date.
- [gt()](#gt)  Checks if the date is greater than another date.
- [isInBetween()](#isInBetween)  Checks if the date is in between two other dates.
- [diffForHumans()](#diffForHumans)  Returns the difference between two dates in a human-readable format.

### constructor()
The `Plastic` class constructor accepts the same parameters as PHP's native `DateTime` class, with the addition of a `$timezone` parameter. If no date is provided, the current date and time will be used.

```php
$plastic = new Plastic('2022-01-01 00:00:00', new DateTimeZone('UTC'));
echo $plastic->format('Y-m-d H:i:s');
// Outputs: 2022-01-01 00:00:00
```

### now()
The `now` method returns a new `Plastic` instance representing the current date and time.

```php
$now = Plastic::now(); // Returns a new Plastic instance representing the current date and time.
echo $now->format('Y-m-d H:i:s');

// Outputs: 2022-01-01 00:00:00 
```

### parse()
The `parse` method creates a new `Plastic` instance from a specific date and time.

```php
$plastic = Plastic::parse('2022-01-01 00:00:00', new DateTimeZone('UTC'));
echo $plastic->format('Y-m-d H:i:s');
// Outputs: 2022-01-01 00:00:00
```
The `parse` method also accepts a `DateTime` instance as a parameter.

```php
$datetime = new DateTime('2022-01-01 00:00:00', new DateTimeZone('UTC'));
$plastic = Plastic::parse($datetime);
echo $plastic->format('Y-m-d H:i:s');
// Outputs: 2022-01-01 00:00:00
```
The `diffForHumans` method can also be customized to show only the most significant time segments and to remove the tense of time (past or future) by using the `absolute` and `segments` parameters respectively.
```php
$someTimeAgo = Plastic::parse('2024-01-01 09:15:30');
$currentDate = Plastic::parse('2024-02-25 12:00');

// Use the method without tense (absolute) and limit to 2 time segments
echo $someTimeAgo->diffForHumans($currentDate, true, 2);
// Outputs: 1 month and 24 days

// Now with tense and still limited to 2 segments
echo $someTimeAgo->diffForHumans($currentDate, false, 2);
// Outputs: 1 month and 24 days ago

// Example with more segments but still limiting the output
$anotherTime = Plastic::parse('2023-12-25 08:00');
echo $anotherTime->diffForHumans($currentDate, false, 4);
// Outputs: 1 year, 2 months, 1 day and 4 hours ago
```
