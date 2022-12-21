<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Validator\FieldError;

/**
 * Constraint for array validation
 * Will apply validation to each element of the array
 *
 * Note: If the value is not an array, the validation will be skipped
 *
 * Example:
 * <code>
 * class MyRequest
 * {
 *     #[ValidateArray(constraints: [
 *         new Length(min: 3, max: 10),
 *         new Regex(pattern: '/^[a-z]+$/'),
 *     ])]
 *     public ?array $values;
 * }
 * </code>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class ValidateArray implements ConstraintInterface
{
    public function __construct(
        /**
         * List of constraints to apply to each element of the array
         *
         * @var non-empty-list<ConstraintInterface>
         */
        public array $constraints,

        /**
         * Global error message to show if at least one element of the array is invalid
         * This message will be used only if {@see ValidateArray::$aggregateErrors} is true
         *
         * Use {{ item_errors }} as placeholder for the list of item errors
         *
         * @var string
         */
        public string $message = 'Some values are invalid :' . PHP_EOL . '{{ item_errors }}',

        /**
         * Error message format for each item error
         *
         * Use as placeholders:
         * - {{ key }} for the item array key. If the array is not associative, the key will be the item index. Be aware that the key value is not escaped.
         * - {{ error }} for the item error message.
         *
         * @var string
         */
        public string $itemMessage = '- On item {{ key }}: {{ error }}' . PHP_EOL,

        /**
         * Aggregate all item errors in a single {@see FieldError}
         * The error message will be the {@see ValidateArray::$message} with the item errors concatenated
         *
         * If this option is false, the validation will return a {@see FieldError} for each invalid item
         *
         * @var bool
         */
        public bool $aggregateErrors = false,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(ConstraintValidatorRegistryInterface $registry): ConstraintValidatorInterface
    {
        return new ValidateArrayValidator($registry);
    }
}
