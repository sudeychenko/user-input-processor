# flaksp/user-input-deserializer

Deserializes and validates any kind of user input, so it may be easily used in:

* HTML forms
* APIs
* console command arguments

... and in a lot of other scenarios.

## Installation

***Warning:** At this moment the library is being tested in real projects to detect possible problems in its design, so API changes are possible. Please wait for stable version.*

PHP 8.0 or newer is required. The library is available in [Packagist](https://packagist.org/packages/flaksp/user-input-deserializer) and may be installed with Composer:

```console
composer require flaksp/user-input-deserializer
```

## Conception

### Deserializer

Deserializer is something that should be used to validate and deserialize data. It may also re-use other deserializers, which should be passed via [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection).

* If deserialization was successful, deserializer may return anything: unmodified data, [DTO](https://en.wikipedia.org/wiki/Data_transfer_object) or [value object](https://en.wikipedia.org/wiki/Value_object).
* If validation error happened (e.g. email has invalid format), deserializer throws [`ValidationError`](src/Exception/ValidationError.php) exception with collection of constraint violations.

### Constraint violation

Constraint violation object describes which field contains invalid value. [`ConstraintViolationInterface`](src/ConstraintViolation/ConstraintViolationInterface.php) has several public methods:

* `public static function getType(): string` — returns constraint violation type. It is a string identifier of an error (e.g. `string_is_too_long`).
* `public function getDescription(): string` — returns human-readable description of an error. Content of this field is a message for development purposes, and it's not intended to be shown to the end user.
* `public function getPointer(): JsonPointer` — returns path to the invalid property in [JSON Pointer](https://tools.ietf.org/html/rfc6901) format (e.g. `#/users/1/id`).

Any class implementing the interface may add its own public methods specific to its kind of constraint. For example, class [`StringIsTooLong`](src/ConstraintViolation/StringIsTooLong.php) has extra public method `public function getMaxLength(): int` that allows to get max length from the violation.
