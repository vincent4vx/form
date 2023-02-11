<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\RegistryInterface;

/**
 * Generic constraint to validate a field value using a custom validator instance.
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *    #[ValidateBy(MyValidator::class, ['checksum' => 15])]
 *    public string $foo;
 * }
 *
 * class MyValidator implements ConstraintValidatorInterface
 * {
 *     public function __construct(private ChecksumAlgorithmInterface $algo) {}
 *
 *     public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
 *     {
 *         if ($this->algo->checksum($value) !== $constraint->options['checksum']) {
 *             return new FieldError('Invalid checksum');
 *         }
 *
 *         return null;
 *     }
 * }
 * </code>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ValidateBy implements ConstraintInterface
{
    public function __construct(
        /**
         * Validator class to use
         * Must be registered in the ConstraintValidatorRegistry
         *
         * @var class-string<ConstraintValidatorInterface<ValidateBy>>
         */
        public readonly string $validatorClass,

        /**
         * Array of options to pass to the validator
         *
         * Note: prefer use a custom constraint class instead of using this option
         *
         * @var array<string, mixed>
         */
        public readonly array $options = [],
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface
    {
        return $registry->getConstraintValidator($this->validatorClass);
    }
}
