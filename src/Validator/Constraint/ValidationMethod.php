<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;

/**
 * Validate a field value using a method call.
 *
 * This method can be a static method on a given class or an instance method declared on the data object.
 * The method will be called with the following arguments:
 * - the field value
 * - the data object (on the instance method, this parameter is same as $this)
 * - extra parameters, as variadic arguments
 *
 * The method can return one of the following:
 * - null: the field is valid
 * - true: the field is valid
 * - a string: the field is invalid, the string is the error message
 * - a FieldError instance: the field is invalid
 * - false: the field is invalid, the error message is the default one
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     // Calling validateFoo() on this instance
 *     #[ValidateMethod(method: 'validateFoo', parameters: [15], message: 'Invalid checksum')]
 *     public string $foo;
 *
 *     // Calling Functions::validateFoo()
 *     #[ValidateMethod(class: Functions::class, method: 'validateFoo', parameters: [15], message: 'Invalid checksum')]
 *     public string $foo;
 *
 *     // Return a boolean, so the default error message is used
 *     public function validateFoo(string $value, object $data, int $checksum)
 *     {
 *         return crc32($value) % 32 === $checksum;
 *     }
 *
 *     // Return a string, so the string is used as error message
 *     public function validateFoo(string $value, object $data, int $checksum)
 *     {
 *         if (crc32($value) % 32 !== $checksum) {
 *             return 'Invalid checksum';
 *         }
 *
 *         return null;
 *     }
 *
 *     // Return a FieldError instance
 *     public function validateFoo(string $value, object $data, int $checksum)
 *     {
 *         if (crc32($value) % 32 !== $checksum) {
 *             return new FieldError('Invalid checksum');
 *         }
 *
 *         return null;
 *     }
 * }
 *
 * class Functions
 * {
 *     // You can also use a static method
 *     public static function validateFoo(string $value, object $data, int $checksum): bool
 *     {
 *         return crc32($value) % 32 === $checksum;
 *     }
 * }
 *
 * @implements ConstraintValidatorGeneratorInterface<self>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ValidationMethod extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public function __construct(
        /**
         * Method name to call
         *
         * @var string
         */
        public readonly string $method,

        /**
         * Class name to call the method on
         * If null, the method will be called on the data object
         *
         * @var class-string|null
         */
        public readonly ?string $class = null,

        /**
         * Extra parameters to pass to the method
         * Those parameters will be appended to parameters, after the field value and the data object
         * So first element of this array will be the third parameter of the method
         *
         * @var list<mixed>
         */
        public readonly array $parameters = [],

        /**
         * Default error message to use if the method returns false
         *
         * @var string
         */
        public string $message = 'Invalid value',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, string $fieldAccessor): string
    {
        $className = $constraint->class;
        $methodName = $constraint->method;
        $extraParameters = array_map(fn (mixed $parameter) => Code::value($parameter), $constraint->parameters);

        $parameters = [$fieldAccessor, '$data', ...$extraParameters];
        $parameters = implode(', ', $parameters);

        if ($className === null) {
            $expression = "\$data->{$methodName}({$parameters})";
        } else {
            $expression = "\\{$className}::{$methodName}({$parameters})";
        }

        return '\\' . self::class . '::toFieldError(' . $expression . ', ' . Code::value($constraint->message) . ')';
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        $result = $constraint->class === null
            ? $data->{$constraint->method}($value, $data, ...$constraint->parameters)
            : $constraint->class::{$constraint->method}($value, $data, ...$constraint->parameters)
        ;

        return self::toFieldError($result, $constraint->message);
    }

    /**
     * Convert a method result to a FieldError instance (or null if the result is valid)
     *
     * @param string|null|bool|FieldError $result Result of the method call
     * @param string $message Error message to use if the method returns false
     *
     * @return FieldError|null
     *
     * @internal This method should only be called by generated code or by the validate() method
     */
    public static function toFieldError(mixed $result, string $message): ?FieldError
    {
        if ($result === null || $result === true) {
            return null;
        }

        if ($result === false) {
            return new FieldError($message);
        }

        if ($result instanceof FieldError) {
            return $result;
        }

        return new FieldError($result);
    }
}
