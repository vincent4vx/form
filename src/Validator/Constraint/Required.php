<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;

/**
 * @implements ConstraintValidatorGeneratorInterface<static>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Required extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public function __construct(
        public readonly string $message = 'This value is required',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value): ?FieldError
    {
        if ($value === null || $value === '' || $value === []) {
            return new FieldError($this->message);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, string $fieldAccessor): string
    {
        $errorMessage = var_export($constraint->message, true);

        return "$fieldAccessor === null || $fieldAccessor === '' || $fieldAccessor === [] ? new FieldError($errorMessage) : null";
    }
}
