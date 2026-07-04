<?php

namespace Quatrevieux\Form\Choice;

use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Stringable;

use function array_combine;
use function array_values;
use function assert;
use function in_array;
use function is_array;
use function is_int;
use function is_scalar;
use function is_string;
use function print_r;

/**
 * @implements ConstraintValidatorInterface<Choice>
 * @implements ConstraintValidatorGeneratorInterface<Choice>
 */
final class ChoiceValidator implements ConstraintValidatorInterface, ConstraintValidatorGeneratorInterface
{
    public function __construct(
        private readonly ?ChoicesProviderInterface $choicesProvider,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): FieldError|array|null
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $errors = [];

            foreach ($value as $k => $v) {
                if ($error = $this->validateOne($constraint, $v)) {
                    $errors[$k] = $error;
                }
            }

            return $errors ?: null;
        }

        return $this->validateOne($constraint, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::undefined(function (string $accessor) use ($constraint): string {
            return "{$accessor} === null ? null : (is_array({$accessor}) ? {$this->generateAggregateInArray($constraint, $accessor)} : {$this->generateSingleInArray($constraint, $accessor)})";
        });
    }

    /**
     * Validate if the given value is in the choices.
     *
     * @param mixed $value
     *
     * @return FieldError|null The error if the value is not in the choices, null otherwise
     */
    private function validateOne(Choice $constraint, mixed $value): ?FieldError
    {
        if ($this->choicesProvider !== null) {
            if ($this->choicesProvider->contains($value)) {
                return null;
            }
        } else {
            assert(is_array($constraint->choices));

            if (in_array($value, $constraint->choices, true)) {
                return null;
            }
        }

        if (!is_scalar($value) && !$value instanceof Stringable) {
            $value = print_r($value, true);
        }

        return new FieldError($constraint->message, ['value' => $value], Choice::CODE);
    }

    /**
     * Generate the code for check a scalar value in the choices.
     *
     * @param string $accessor Value accessor
     *
     * @return string
     */
    private function generateSingleInArray(Choice $constraint, string $accessor): string
    {
        $accessor = Code::expr($accessor);

        if ($this->choicesProvider !== null) {
            assert(is_string($constraint->choices));
            $contains = Expr::this()->registry->getService($constraint->choices)->contains($accessor);
        } else {
            assert(is_array($constraint->choices));
            $choices = array_values($constraint->choices);

            if ($this->choicesCanBeUsedAsKey($choices)) {
                $values = array_combine($choices, $choices);
                $contains = $accessor->format('((is_int({}) || is_string({})) && (({values}[{}] ?? null) === {}))', values: $values);
            } else {
                $contains = Call::in_array($accessor, $choices, true);
            }
        }

        $debugValue = $accessor->format('is_scalar({}) || {} instanceof \Stringable ? {} : print_r({}, true)');
        $fieldError = Code::new(FieldError::class, [$constraint->message, ['value' => $debugValue], Choice::CODE]);

        return "(!{$contains} ? {$fieldError} : null)";
    }

    /**
     * Generate the code for check a multiple choice.
     *
     * @param string $accessor Value accessor
     *
     * @return string
     */
    private function generateAggregateInArray(Choice $constraint, string $accessor): string
    {
        if ($this->choicesProvider !== null) {
            assert(is_string($constraint->choices));
            $contains = (new Expr('$provider'))->contains(Code::raw('$value'));
            $before = '$provider = ' . Expr::this()->registry->getService($constraint->choices) . ';';
        } else {
            assert(is_array($constraint->choices));
            $choices = array_values($constraint->choices);

            if ($this->choicesCanBeUsedAsKey($choices)) {
                $choices = Code::value(array_combine($choices, $choices));
                $contains = '((is_int($value) || is_string($value)) && (($choices[$value] ?? null) === $value))';
            } else {
                $choices = Code::value($choices);
                $contains = 'in_array($value, $choices, true)';
            }

            $before = '$choices = ' . $choices . ';';
        }

        $debugValue = 'is_scalar($value) || $value instanceof \Stringable ? $value : print_r($value, true)';
        $fieldError = Code::new(FieldError::class, [$constraint->message, ['value' => Code::raw($debugValue)], Choice::CODE]);

        $containsAggregate = Code::expr(
            'function ($values) {'
                . '$errors = [];'
                . $before
                . 'foreach ($values as $key => $value) {'
                    . "if (!{$contains}) {"
                        . '$errors[$key] = ' . $fieldError . ';'
                    . '}'
                . '}'
                . 'return $errors ?: null;'
            . '}',
        );

        return $containsAggregate(Code::raw($accessor));
    }

    /**
     * Check if all choices can be used as key (i.e. are string or int).
     * This is used to optimize the generated code.
     *
     * @param mixed[] $choices
     * @return bool
     *
     * @phpstan-assert-if-true array<string|int> $choices
     */
    private function choicesCanBeUsedAsKey(array $choices): bool
    {
        foreach ($choices as $choice) {
            if (!is_string($choice) && !is_int($choice)) {
                return false;
            }
        }

        return true;
    }
}
