<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

use function is_scalar;

/**
 * Base comparison constraint
 *
 * @implements ConstraintValidatorGeneratorInterface<static>
 */
abstract class AbstractComparisonConstraint extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public function __construct(
        /**
         * The value to compare with
         */
        public readonly int|float|string|bool $value,

        /**
         * The error message
         * Use {{ value }} as placeholder for the value
         *
         * @var string
         */
        public readonly string $message = 'This value is not valid.',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if (!is_scalar($value)) {
            return null;
        }

        if ($this->compare($value, $constraint->value)) {
            return null;
        }

        return new FieldError($constraint->message, ['value' => $this->value], static::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        $expected = Expr::value($constraint->value);
        $error = Code::new(FieldError::class, [
            $constraint->message,
            ['value' => $expected],
            static::CODE,
        ]);

        return FieldErrorExpression::single(fn(string $accessor): string => "is_scalar({$accessor}) && !({$accessor} {$this->operator()} {$expected}) ? {$error} : null");
    }

    /**
     * Performs the comparison.
     *
     * @param scalar $actual Actual value (from the field)
     * @param scalar $expected Expected value (from the constraint)
     *
     * @return bool true if the comparison is valid, false otherwise
     */
    abstract protected function compare(float|bool|int|string $actual, float|bool|int|string $expected): bool;

    /**
     * Get the comparison operator used in the generated code.
     * The generated code will be something like "{$accessor} {$this->operator()} {$expected}", and must return true if the comparison is valid.
     *
     * @return string
     */
    abstract protected function operator(): string;
}
