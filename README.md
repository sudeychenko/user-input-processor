# flaksp/user-input-processor

Deserializes and validates any kind of user input, so it may be easily used in:

* HTML forms
* APIs
* console command arguments

... and in a lot of other scenarios.

## Installation

***Warning:** At this moment the library is being tested in real projects to detect possible problems in its design, so API changes are possible. Please wait for stable version.*

PHP 8.0 or newer is required. The library is available in [Packagist](https://packagist.org/packages/flaksp/user-input-processor) and may be installed with Composer:

```console
composer require flaksp/user-input-processor
```

## Conception

### Deserializer

Deserializer is something that should be used to validate and deserialize data. It may also re-use other deserializers, which should be passed via [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection).

* If deserialization was successful, deserializer may return anything: unmodified data, [DTO](https://en.wikipedia.org/wiki/Data_transfer_object) or [value object](https://en.wikipedia.org/wiki/Value_object).
* If validation error happened (e.g. email has invalid format), deserializer throws [`ValidationError`](src/Exception/ValidationError.php) exception that has [`ConstraintViolationCollection`](src/ConstraintViolation/ConstraintViolationCollection.php).

The library is bundled with some basic deserializers for each type that may appear in JSON. Most of them come with validation options inspired by [JSON Schema specification](https://json-schema.org/specification.html). Opinionated deserializers and constraint violations for emails, phone numbers, IP addresses and for other cases are out of scope of the library.

### Constraint violation

Constraint violation object describes which field contains invalid value. [`ConstraintViolationInterface`](src/ConstraintViolation/ConstraintViolationInterface.php) has several public methods:

* `public static function getType(): string` — returns constraint violation type. It is a string identifier of an error (e.g. `string_is_too_long`).
* `public function getDescription(): string` — returns human-readable description of an error. Content of this field is a message for development purposes, and it's not intended to be shown to the end user.
* `public function getPointer(): JsonPointer` — returns path to the invalid property in [JSON Pointer](https://tools.ietf.org/html/rfc6901) format (e.g. `#/users/0/id`).

Any class implementing the interface may add its own public methods specific to its kind of constraint. For example, class [`StringIsTooLong`](src/ConstraintViolation/StringIsTooLong.php) has extra public method `public function getMaxLength(): int` that allows to get max length from the violation.

## FAQ

### How to get localized validation error message for user?

There is no such functionality out-of-the-box, because formatting error messages for end-user is not something deserializer and validator should do. It should be implemented on another abstraction layer. It should be a method in another service that accepts [`ConstraintViolationInterface`](src/ConstraintViolation/ConstraintViolationInterface.php) and returns localized error message for user.

`public function getDescription(): string` method exists only for debugging and logging purposes. This value is not recommended being rendered in UI because some constraints may contain very nerd messages like [`ValueDoesNotMatchRegex`](src/ConstraintViolation/ValueDoesNotMatchRegex.php) violation has:

> Property "#/id" does not match regex "/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/".

### Why [`ValidationError`](src/Exception/ValidationError.php) exception contains [`ConstraintViolationCollection`](src/ConstraintViolation/ConstraintViolationCollection.php) (collection of constraint violations), not a single violation?

Your deserializers should return as much constraint violations as possible in one time for better user experience. Check out simple [`StringDeserializer`](src/Deserializer/StringDeserializer.php) to see how it may be implemented in your deserializers.
