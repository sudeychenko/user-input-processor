# flaksp/user-input-processor

Denormalizes and validates any kind of user input, so it may be easily used in:

- HTML forms
- APIs
- console command arguments

... and in a lot of other scenarios.

## Installation

**\*Warning:** At this moment the library is being tested in real projects to detect possible problems in its design, so API changes are possible. Please wait for stable version.\*

PHP 8.0 or newer is required. The library is available in [Packagist](https://packagist.org/packages/flaksp/user-input-processor) and may be installed with Composer:

```console
composer require flaksp/user-input-processor
```

## Conception

### Denormalizer

Denormalizer is something that should be used to validate and denormalize data. It may also re-use other denormalizers, which should be passed via [dependency injection](https://en.wikipedia.org/wiki/Dependency_injection).

- If denormalization was successful, denormalizer may return anything: unmodified data, [DTO](https://en.wikipedia.org/wiki/Data_transfer_object) or [value object](https://en.wikipedia.org/wiki/Value_object).
- If validation error happened (e.g. email has invalid format), denormalizer throws [`ValidationError`](src/Exception/ValidationError.php) exception that has [`ConstraintViolationCollection`](src/ConstraintViolation/ConstraintViolationCollection.php).

The library is bundled with some basic denormalizers for each type that may appear in JSON. Most of them come with validation options inspired by [JSON Schema specification](https://json-schema.org/specification.html). Opinionated denormalizers and constraint violations for emails, phone numbers, IP addresses and for other cases are out of scope of the library.

### Constraint violation

Constraint violation object describes which field contains invalid value. [`ConstraintViolationInterface`](src/ConstraintViolation/ConstraintViolationInterface.php) has several public methods:

- `public static function getType(): string` — constraint violation type. It is a string identifier of an error (e.g. `string_is_too_long`).
- `public function getDescription(): string` — human-readable description of an error. Content of this field is a message for development purposes, and it's not intended to be shown to the end user.
- `public function getPointer(): Pointer` — path to the invalid property.

Any class implementing the interface may add its own public methods specific to its kind of constraint. For example, class [`StringIsTooLong`](src/ConstraintViolation/StringIsTooLong.php) has extra public method `public function getMaxLength(): int` that allows to get max length from the violation.

### Pointer

Every denormalizer and constraint violation accepts [`Pointer`](src/Pointer.php) as an argument. Pointer is a special object that contains path to your property. In most cases you will create only one pointer per form as `Pointer::empty()` for root object denormalizer.

Pointer may be easily converted to something specific to be shown to your client applications. For example, you may serialize it to [JSON Pointer](https://tools.ietf.org/html/rfc6901):

```php
public function getJsonPointer(Pointer $pointer): string
{
    $jsonPointer = '';

    foreach ($pointer->propertyPath as $pathItem) {
        $jsonPointer .= '/' . $pathItem;
    }

    return $jsonPointer;
}
```

Converting pointers to strings is out of scope of the library, so you should do it by yourself on another abstraction layer.

## Examples

### Your own object denormalizer

In most cases you will want to create your own object types. For example, it may be user profile structure that has display name, username and optional contact email. To implement such denormalizer that returns `UserProfileData` value object, you may write something like this:

```php
declare(strict_types=1);

namespace App\Denormalizer;

use App\Denormalizer\DisplayNameDenormalizer;
use App\Denormalizer\EmailDenormalizer;
use App\Denormalizer\UsernameDenormalizer;
use App\ValueObject\UserProfileData;
use Flaksp\UserInputProcessor\Denormalizer\ObjectDenormalizer;
use Flaksp\UserInputProcessor\ObjectField;
use Flaksp\UserInputProcessor\ObjectStaticFields;
use Flaksp\UserInputProcessor\Pointer;

final class UserProfileDenormalizer {
    public function __construct(
        private ObjectDenormalizer $objectDenormalizer,
        private EmailDenormalizer $emailDenormalizer,
        private DisplayNameDenormalizer $displayNameDenormalizer,
        private UsernameDenormalizer $usernameDenormalizer,
    ) {}

    public function denormalize(
        mixed $data
    ): UserProfileData {
        $processedData = $this->objectDenormalizer->denormalize(
            $data,
            Pointer::empty(),
            new ObjectStaticFields([
                'contactEmail' => new ObjectField(
                    static fn (mixed $fieldData, Pointer $fieldPointer) => $this->emailDenormalizer->denormalize($fieldData, $fieldPointer),
                    isMandatory: true,
                ),
                'displayName' => new ObjectField(
                    static fn (mixed $fieldData, Pointer $fieldPointer) => $this->displayNameDenormalizer->denormalize($fieldData, $fieldPointer),
                    isMandatory: true,
                ),
                'username' => new ObjectField(
                    static fn (mixed $fieldData, Pointer $fieldPointer) => $this->usernameDenormalizer->denormalize($fieldData, $fieldPointer),
                    isMandatory: true,
                ),
            ]),
        );

        return new UserProfileData(
            $processedData['contactEmail'],
            $processedData['displayName'],
            $processedData['username'],
        );
    }
}
```

[`ObjectDenormalizer`](src/Denormalizer/ObjectDenormalizer.php) accepts data (variable `$data`) in any format in the first argument. The second argument accepts pointer. And the third one allows us to describe structure of input data and denormalization rules for each field using [`ObjectStaticFields`](src/ObjectStaticFields.php) object. This object accepts associative array: key is field name and value is [`ObjectField`](src/ObjectField.php). `ObjectField` accepts callable as first argument, object denormalizer passes field's data and its pointer to this callable, so you may simply pass them into denormalizer as shown in the example above. `isMandatory` means the property must be presented in request payload, even if it has `null` value. If `isMandatory` is `false`, client application is allowed to omit the field from request, and `$processedData` variable will miss such key too.

## FAQ

### How to get localized validation error message for user?

There is no such functionality out-of-the-box, because formatting error messages for end-user is not something denormalizer and validator should do. It should be implemented on another abstraction layer. It should be a method in another service that accepts [`ConstraintViolationInterface`](src/ConstraintViolation/ConstraintViolationInterface.php) and returns localized error message for user.

`public function getDescription(): string` method exists only for debugging and logging purposes. This value is not recommended being rendered in UI because some constraints may contain very nerd messages like [`ValueDoesNotMatchRegex`](src/ConstraintViolation/ValueDoesNotMatchRegex.php) violation has:

> Property does not match regex "/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/".

### Why [`ValidationError`](src/Exception/ValidationError.php) exception contains [`ConstraintViolationCollection`](src/ConstraintViolation/ConstraintViolationCollection.php) (collection of constraint violations), not a single violation?

Your denormalizers should return as much constraint violations as possible in one time for better user experience. Check out simple [`StringDenormalizer`](src/Denormalizer/StringDenormalizer.php) to see how it may be implemented in your denormalizers.
