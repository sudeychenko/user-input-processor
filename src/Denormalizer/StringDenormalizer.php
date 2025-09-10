<?php

declare(strict_types=1);

namespace UserInputProcessor\Denormalizer;

use LogicException;
use UserInputProcessor\ConstraintViolation\ConstraintViolationInterface;
use UserInputProcessor\ConstraintViolation\StringIsTooLong;
use UserInputProcessor\ConstraintViolation\StringIsTooShort;
use UserInputProcessor\ConstraintViolation\ValueDoesNotMatchRegex;
use UserInputProcessor\ConstraintViolation\WrongPropertyType;
use UserInputProcessor\Exception\ValidationError;
use UserInputProcessor\Pointer;

/**
 * Denormalizer for fields where string is expected.
 *
 * It will fail if integer or float is passed. It should be cast to string before passing to the denormalizer.
 */
final readonly class StringDenormalizer
{
    /**
     * Validates and denormalizes passed data.
     *
     * It expects `$data` to be string type, but also accept additional validation requirements.
     *
     * @psalm-param mixed $data Data to validate and denormalize
     * @psalm-param Pointer $pointer Pointer containing path to current field
     * @psalm-param int<0,max>|null $minLength Minimum length of string
     * @psalm-param int<0,max>|null $maxLength Maximum length of string
     * @psalm-param string|null $pattern Regular expression to validate string against
     *
     * @throws ValidationError If `$data` does not meet the requirements of the denormalizer
     *
     * @psalm-return ($minLength is int<1,max> ? non-empty-string : string) The same string as the one that was passed to `$data` argument
     */
    public function denormalize(
        mixed $data,
        Pointer $pointer,
        ?int $minLength = null,
        ?int $maxLength = null,
        ?string $pattern = null,
    ): string {
        if (null !== $minLength && null !== $maxLength && $minLength > $maxLength) {
            throw new LogicException('Min length constraint can not be bigger than max length');
        }

        /** @psalm-var list<ConstraintViolationInterface> $violations */
        $violations = [];

        if (!\is_string($data)) {
            $violations[] = WrongPropertyType::guessGivenType($pointer, $data, [WrongPropertyType::JSON_TYPE_STRING]);

            throw new ValidationError($violations);
        }

        if (null !== $minLength && mb_strlen($data) < $minLength) {
            $violations[] = new StringIsTooShort($pointer, $minLength);
        }

        if (null !== $maxLength && mb_strlen($data) > $maxLength) {
            $violations[] = new StringIsTooLong($pointer, $maxLength);
        }

        if (null !== $pattern && '' !== $pattern && 1 !== preg_match($pattern, $data)) {
            $violations[] = new ValueDoesNotMatchRegex($pointer, $pattern);
        }

        if (\count($violations) > 0) {
            throw new ValidationError($violations);
        }

        return $data;
    }
}
