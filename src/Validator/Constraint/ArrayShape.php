<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\Constraint\Type\ArrayType;
use Quatrevieux\Form\Validator\Constraint\Type\TypeInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

/**
 * Check if the current field is an array and if it has the expected keys and values types.
 * The type syntax follows PHP's disjunctive normal form : https://wiki.php.net/rfc/dnf_types
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     #[ArrayShape([
 *         'firstName' => 'string',
 *         'lastName' => 'string',
 *         // Use ? to mark the field as optional
 *         'age?' => 'int',
 *         // You can declare a sub array shape
 *         'address' => [
 *            'street' => 'string',
 *             'city' => 'string',
 *             'zipCode' => 'string|int', // You can use multiple types
 *          ],
 *     ])]
 *     public array $person;
 *
 *     // You can define array as dynamic list
 *     #[ArrayShape(key: 'int', value: 'int|float')]
 *     public array $listOfNumbers;
 *
 *     // You can disable extra keys
 *     #[ArrayShape(['foo' => 'string', 'bar' => 'int'], allowExtraKeys: false)]
 *     public array $fixed;
 * }
 * </code>
 *
 * @implements ConstraintValidatorGeneratorInterface<ArrayShape>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayShape extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public const CODE = 'd0909170-b496-5bb5-8cc6-efe839722a8c';

    public function __construct(
        /**
         * Define array fields and their types.
         *
         * The key is the field name. If the field is optional, add a question mark `?` at the end of the name.
         *
         * The value is the type of the field. The type can be a string, following PHP's disjunctive normal form,
         * a TypeInterface instance, or an array which will be converted to an array type.
         *
         * @var array<string, string|TypeInterface|mixed[]>
         */
        public readonly array $shape = [],

        /**
         * The key type for extra keys.
         * The type can be a string, following PHP's disjunctive normal form, a TypeInterface instance.
         *
         * Note: this option is ignored for all keys that are defined in the shape.
         */
        public readonly string|TypeInterface $key = 'string|int',

        /**
         * The value type.
         *
         * The type can be a string, following PHP's disjunctive normal form,
         * a TypeInterface instance, or an array which will be converted to an array type.
         *
         * Note: this option is ignored for all values that are defined in the shape.
         *
         * @var string|TypeInterface|array<string, string|TypeInterface|mixed[]>
         */
        public readonly string|TypeInterface|array $value = 'mixed',

        /**
         * Allow extra keys which are not defined in the shape.
         * All these keys will be validated with the key type and the value type.
         */
        public readonly bool $allowExtraKeys = true,

        /**
         * The error message to display
         */
        public readonly string $message = 'This value does not match the expected array shape.',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if ($value !== null && !$constraint->type()->check($value)) {
            return new FieldError($constraint->message, code: self::CODE);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::single(function (string $accessor) use ($constraint) {
            $expression = $constraint->type()->generateCheck($accessor);

            $error = Code::new(FieldError::class, [
                $this->message,
                [],
                self::CODE,
            ]);

            return "{$accessor} !== null && !({$expression}) ? {$error} : null";
        });
    }

    private function type(): ArrayType
    {
        return new ArrayType($this->shape, $this->key, $this->value, $this->allowExtraKeys);
    }
}
