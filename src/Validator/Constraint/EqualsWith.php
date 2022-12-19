<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

/**
 * The current field value must be equals to other field value
 *
 * @implements ConstraintValidatorGeneratorInterface<self>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class EqualsWith extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public function __construct(
        /**
         * The other field name
         * Must be defined on the same data object
         */
        public readonly string $field,

        /**
         * Error message displayed if values are different
         */
        public readonly string $message = 'Two fields are different',

        /**
         * If true, use a strict comparison (i.e. ===), so type and value will be compared
         */
        public readonly bool $strict = true,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        $other = $data->{$constraint->field} ?? null;

        if ($constraint->strict ? $value !== $other : $value != $other) {
            return new FieldError($this->message);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param EqualsWith $constraint
     */
    public function generate(ConstraintInterface $constraint, string $fieldAccessor, ValidatorGenerator $generator): string
    {
        $otherAccessor = '($data->' . $constraint->field . ' ?? null)';
        $error = 'new FieldError(' . Code::value($constraint->message) . ')';

        if ($constraint->strict) {
            return "$fieldAccessor !== $otherAccessor ? $error : null";
        } else {
            return "$fieldAccessor != $otherAccessor ? $error : null";
        }
    }
}
