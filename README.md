# spiks/user-input-processor

Denormalizes and validates any kind of user input, so it may be easily used in:

- HTML forms,
- APIs,
- console command arguments,
- ... and in a lot of other scenarios.

## Installation

_**Warning:** At this moment the library is being tested in real projects to detect possible problems in its design, so API changes are possible. Please wait for stable version._

PHP 8.1 or newer is required. The library is available in [Packagist](https://packagist.org/packages/spiks/user-input-processor) and may be installed with Composer:

```console
composer require spiks/user-input-processor
```

## Features

The main principles behind the library:

- **Keep it simple**. The library implements only necessary subset of features. It's easy to extend the library to add more. The library is designed as a base for your own implementation, it's not swiss knife that tries to do everything.
- **Full type coverage for static analysis tools.** Public API and the library internals follow the strictest rules of [Psalm](https://psalm.dev) and [PHPStan](https://phpstan.org) - most popular static analysis tools for PHP. We are designing the library keeping type safety in mind. You will appreciate it if you are using static analysis in your projects too.

## Motivation

In our internal projects we use Symfony framework which offers [Symfony Forms](https://symfony.com/doc/current/forms.html) package for user input validation. Symfony Forms has a lot if disadvantages:

- Its internals are very complex,
- It's designed only for HTML forms, and it's not suitable for JSON APIs,
- It's pain to use Symfony Forms with static analysis tools,
- Difficult to maintain forms with complex logic within.

There aren't lots of alternative solutions for user input validation. That's why we decided to create our own.

## Usage

This section will be written when we will be sure API and design is stable enough.
