# Plastic - Simple DateTime Utility

Plastic is a friendly DateTime manipulation class that extends PHP's native `DateTime` class. It provides an expressive, user-friendly interface for working with dates and times in your applications.

## Installation

You can install the package via composer:

```bash
composer require avmg/php-simple-utilities
```

## Basic Usage

### Creating Plastic Instances

You can create a new Plastic instance in several ways:

```php
// Create from current timestamp
$now = Plastic::now();

// Create from a specific date string
$date = Plastic::parse('2024-01-15 14:30:00');

// Create from a DateTime instance
$datetime = new DateTime('2024-01-15');
$date = Plastic::parse($datetime);

// Create from a timestamp
$date = Plastic::parse(1708694045);

// Create with a specific timezone
$date = Plastic::parse('2024-01-15', 'America/New_York');
```

### Date Manipulation

Plastic provides fluent methods for adding and subtracting time:

```php
$date = Plastic::parse('2024-01-15');

// Adding time
$date->addDays(5);           // 2024-01-20
$date->addMonths(1);         // 2024-02-15
$date->addYears(1);          // 2025-01-15
$date->addHours(3);          // 2024-01-15 03:00:00

// Subtracting time
$date->subDays(5);           // 2024-01-10
$date->subMonths(1);         // 2023-12-15
$date->subYears(1);          // 2023-01-15
$date->subHours(3);          // 2024-01-14 21:00:00
```

### Date Comparison

Compare dates easily with human-readable methods:

```php
$date = Plastic::parse('2024-01-15');
$otherDate = Plastic::parse('2024-02-01');

// Basic comparisons
$date->lt($otherDate);               // true (less than)
$date->gt($otherDate);               // false (greater than)
$date->isInBetween($start, $end);    // true/false

// Day comparisons
$date->isToday();                    // true/false
$date->isTomorrow();                 // true/false
$date->isYesterday();                // true/false

// Time period comparisons
$date->isThisWeek();                 // true/false
$date->isThisMonth();                // true/false
$date->isThisYear();                 // true/false

// Day of week checks
$date->isMonday();                   // true/false
$date->isFriday();                   // true/false
```

### Time Differences

Calculate differences between dates:

```php
$date1 = Plastic::parse('2024-01-15 10:00:00');
$date2 = Plastic::parse('2024-02-20 15:30:00');

// Get differences in various units
$date1->diffInDays($date2);          // 36
$date1->diffInHours($date2);         // 869
$date1->diffInMinutes($date2);       // 52170

// Human-readable differences
$date1->diffForHumans();             // "1 month and 5 days ago"
$date1->diffForHumans($date2);       // "1 month and 5 days before"

// Absolute differences (no ago/before)
$date1->diffForHumans($date2, true); // "1 month and 5 days"

// Control number of time segments shown
$date1->diffForHumans($date2, true, 3); // "1 month, 5 days and 5 hours"
```

### Date Start/End Helpers

Easily snap to common date boundaries:

```php
$date = Plastic::parse('2024-01-15 15:30:45');

// Day boundaries
$date->startOfDay();     // 2024-01-15 00:00:00
$date->endOfDay();       // 2024-01-15 23:59:59

// Week boundaries
$date->startOfWeek();    // 2024-01-15 00:00:00 (Monday)
$date->endOfWeek();      // 2024-01-21 23:59:59 (Sunday)

// Month boundaries
$date->startOfMonth();   // 2024-01-01 00:00:00
$date->endOfMonth();     // 2024-01-31 23:59:59

// Year boundaries
$date->startOfYear();    // 2024-01-01 00:00:00
$date->endOfYear();      // 2024-12-31 23:59:59
```

### Formatting

Convert dates to commonly used formats:

```php
$date = Plastic::parse('2024-01-15 15:30:45');

$date->toDateTimeString();  // "2024-01-15 15:30:45"
$date->toDateString();      // "2024-01-15"
$date->toTimeString();      // "15:30:45"
$date->toTimestamp();       // 1705330245
```

### Localization

Plastic supports translations for human-readable date differences:

```php
$date = Plastic::parse('2024-01-15');

// Set custom translations
$date->setTranslations([
    'year' => 'año',
    'years' => 'años',
    'month' => 'mes',
    'months' => 'meses',
    'day' => 'día',
    'days' => 'días',
    'hour' => 'hora',
    'hours' => 'horas',
    'minute' => 'minuto',
    'minutes' => 'minutos',
    'second' => 'segundo',
    'seconds' => 'segundos',
    'just now' => 'justo ahora',
    'and' => ' y ',
    'ago' => 'hace %s',
    'in' => 'en %s',
]);

// Now diffForHumans() will use Spanish translations
$date->diffForHumans(); // "hace 1 mes y 5 días"
```

## Full Method List

See [Method Reference](#method-reference) for a complete list of available methods.

## Method Reference

- [constructor()](#constructor) - Constructs a new Plastic instance
- [now()](#now) - Returns current date/time
- [parse()](#parse) - Creates instance from date/time string
- [addSeconds(), addMinutes(), addHours(), addDays()](#date-manipulation) - Add time units
- [subSeconds(), subMinutes(), subHours(), subDays()](#date-manipulation) - Subtract time units
- [diffInSeconds(), diffInMinutes(), diffInHours(), diffInDays()](#time-differences) - Get time differences
- [startOfDay(), endOfDay()](#date-startend-helpers) - Day boundary helpers
- [startOfWeek(), endOfWeek()](#date-startend-helpers) - Week boundary helpers
- [startOfMonth(), endOfMonth()](#date-startend-helpers) - Month boundary helpers
- [startOfYear(), endOfYear()](#date-startend-helpers) - Year boundary helpers
- [isToday(), isTomorrow(), isYesterday()](#date-comparison) - Date comparisons
- [isThisWeek(), isThisMonth(), isThisYear()](#date-comparison) - Time period comparisons
- [isMonday() through isSunday()](#date-comparison) - Day of week checks
- [lt(), gt()](#date-comparison) - Date comparisons
- [isInBetween()](#date-comparison) - Range check
- [diffForHumans()](#time-differences) - Human-readable differences
- [toDateTimeString(), toDateString(), toTimeString()](#formatting) - Formatting helpers
- [toTimestamp()](#formatting) - Unix timestamp conversion