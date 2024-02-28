## AVMG PHP Simple Utilities

This package, `avmg/php-simple-utilities`, provides a set of simple utility classes including Collections and DTOs (Data Transfer Objects) with a focus on type safety. Designed with simplicity in mind, these utilities are perfect for integrating standard data manipulation and collection handling capabilities into your PHP projects.

### Features

This package includes the following utilities:

- **[Collection Class](docs/Collection.md)**: Offers a fluent interface for array manipulation, providing methods for filtering, mapping, reducing, and more, all while maintaining type safety.
- **[Data Class](docs/Data.md)**: An abstract class aimed at creating type-safe DTOs, ensuring data integrity throughout your application.
- **[FileCache Class](docs/FileCache.md)**: A simple interface for storing, retrieving, and managing cache data in the filesystem.
- **[EventDispatcher Class](docs/EventDispatcher.md)**: A simple, yet powerful way to manage and dispatch events throughout your PHP application.
- **[Plastic Class](docs/Plastic.md)**: A Simple Carbon inspired class for working with dates and times, with no dependencies and a focus on simplicity and type safety.
- **[FileStorage Class](docs/FileStorage.md)**: A simple and efficient way to handle file storage operations within a filesystem, providing functionalities such as creating directories, storing, retrieving, and deleting files.

### Dependencies

This package has **no dependenci*e*s**. It is designed to be lightweight, self-contained, fast and easy to integrate into any PHP project.

### Usage

All classes are single file based and self-contained, so you can easily copy them into your project if you prefer not to require another Composer package. <br />
This package is aimed at developers looking for simple, type-safe utilities to enhance their PHP applications and workflows.

### Installation

To add this library to your project, use Composer:

```bash
composer require avmg/php-simple-utilities
```

### Requirements

- PHP 8.1.0 or higher

### Development

To contribute or run tests, you'll need PHPUnit. The library already includes PHPUnit as a dev dependency.

Run tests with:

```bash
composer test
```

### License

This project is open-sourced under the MIT License.

### Authors

- AVMG
