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
 * Mark a field as required
 * The field will be considered as valid if it is not null and not an empty string or array
 * Any other value will be considered as valid, like 0, false, etc.
 *
 * Note: this constraint is not required if the field is typed as non-nullable
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // Explicitly mark the field as required because it is nullable
 *     #[Required]
 *     public $foo;
 *
 *     // The field is required because it is not nullable
 *     public string $bar;
 *
 *    // You can define a custom error message to override the default one
 *    #[Required('This field is required')]
 *    public string $baz;
 * }
 *
 * @implements ConstraintValidatorGeneratorInterface<static>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Required extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public const CODE = 'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5';

    public function __construct(
        public readonly string $message = 'This value is required',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if ($value === null || $value === '' || $value === []) {
            return new FieldError($this->message, code: self::CODE);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        $error = Code::new('FieldError', [$constraint->message, [], self::CODE]);

        return FieldErrorExpression::single(fn (string $fieldAccessor) => "$fieldAccessor === null || $fieldAccessor === '' || $fieldAccessor === [] ? $error : null");
    }
}
