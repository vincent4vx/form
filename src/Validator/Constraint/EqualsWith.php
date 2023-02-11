<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

/**
 * The current field value must be equals to other field value
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     #[EqualsWith('password', message: 'Passwords must be equals')]
 *     public string $passwordConfirm;
 *     public string $password;
 * }
 * </code>
 *
 * @implements ConstraintValidatorGeneratorInterface<EqualsWith>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class EqualsWith extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public const CODE = '35ef0ca6-ee68-5f99-a87d-b2f635ea4a4a';

    public function __construct(
        /**
         * The other field name
         * Must be defined on the same data object
         */
        public readonly string $field,

        /**
         * Error message displayed if values are different
         * Use `{{ field }}` placeholder to display the other field name
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
        $field = $constraint->field;
        $other = $data->{$field} ?? null;

        if ($constraint->strict ? $value !== $other : $value != $other) {
            return new FieldError($this->message, ['field' => $field], self::CODE);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param EqualsWith $constraint
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        $otherAccessor = '($data->' . $constraint->field . ' ?? null)';
        $error = Code::new('FieldError', [$constraint->message, ['field' => $constraint->field], self::CODE]);

        if ($constraint->strict) {
            return FieldErrorExpression::single(fn (string $fieldAccessor) => "$fieldAccessor !== $otherAccessor ? $error : null");
        } else {
            return FieldErrorExpression::single(fn (string $fieldAccessor) => "$fieldAccessor != $otherAccessor ? $error : null");
        }
    }
}
