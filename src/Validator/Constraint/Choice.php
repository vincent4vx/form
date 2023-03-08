<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\FieldError;

use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Stringable;

use function in_array;
use function is_array;
use function is_int;
use function is_scalar;
use function is_string;
use function print_r;

/**
 * Check if the value is in the given choices.
 *
 * The value is checked with strict comparison, so ensure that the value is correctly cast.
 * This constraint supports multiple choices (i.e. input value is an array).
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     #[Choice(['foo', 'bar'])]
 *     public string $foo;
 * }
 * </code>
 *
 * @implements ConstraintValidatorGeneratorInterface<Choice>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Choice extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public const CODE = '41ac8b62-e143-5644-a3eb-0fbfff5a2064';

    public function __construct(
        /**
         * List of available choices.
         *
         * @var list<mixed>
         */
        public readonly array $choices,

        /**
         * Error message.
         * Use {{ value }} as placeholder for the invalid value.
         */
        public readonly string $message = 'The value is not a valid choice.',
    ) {
    }

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
                if ($error = $constraint->validateOne($v)) {
                    $errors[$k] = $error;
                }
            }

            return $errors ?: null;
        }

        return $constraint->validateOne($value);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::undefined(function (string $accessor) use ($constraint): string {
            return "{$accessor} === null ? null : (is_array({$accessor}) ? {$constraint->generateAggregateInArray($accessor)} : {$constraint->generateSingleInArray($accessor)})";
        });
    }

    /**
     * Validate if the given value is in the choices.
     *
     * @param mixed $value
     *
     * @return FieldError|null The error if the value is not in the choices, null otherwise
     */
    private function validateOne(mixed $value): ?FieldError
    {
        $choices = $this->choices;

        if (in_array($value, $choices, true)) {
            return null;
        }

        if (!is_scalar($value) && !$value instanceof Stringable) {
            $value = print_r($value, true);
        }

        return new FieldError($this->message, ['value' => $value], self::CODE);
    }

    /**
     * Generate the code for check a scalar value in the choices.
     *
     * @param string $accessor Value accessor
     *
     * @return string
     */
    private function generateSingleInArray(string $accessor): string
    {
        $accessor = Code::expr($accessor);

        if ($this->choicesCanBeUsedAsKey()) {
            $values = array_combine($this->choices, $this->choices);
            $inArray = $accessor->format('((is_int({}) || is_string({})) && (({values}[{}] ?? null) === {}))', values: $values);
        } else {
            $inArray = Call::in_array($accessor, $this->choices, true);
        }

        $debugValue = $accessor->format('is_scalar({}) || {} instanceof \Stringable ? {} : print_r({}, true)');
        $fieldError = Code::new(FieldError::class, [$this->message, ['value' => $debugValue], self::CODE]);

        return "(!{$inArray} ? {$fieldError} : null)";
    }

    /**
     * Generate the code for check a multiple choice.
     *
     * @param string $accessor Value accessor
     *
     * @return string
     */
    private function generateAggregateInArray(string $accessor): string
    {
        if ($this->choicesCanBeUsedAsKey()) {
            $choices = Code::value(array_combine($this->choices, $this->choices));
            $inArray = '((is_int($value) || is_string($value)) && (($choices[$value] ?? null) === $value))';
        } else {
            $choices = Code::value($this->choices);
            $inArray = 'in_array($value, $choices, true)';
        }

        $debugValue = 'is_scalar($value) || $value instanceof \Stringable ? $value : print_r($value, true)';
        $fieldError = Code::new(FieldError::class, [$this->message, ['value' => Code::raw($debugValue)], self::CODE]);

        $inArrayAggregate = Code::expr(
            'function ($values) {' .
                '$errors = [];' .
                '$choices = ' . $choices . ';' .
                'foreach ($values as $key => $value) {' .
                    "if (!{$inArray}) {" .
                        '$errors[$key] = ' . $fieldError . ';' .
                    '}' .
                '}' .
                'return $errors ?: null;' .
            '}'
        );

        return $inArrayAggregate(Code::raw($accessor));
    }

    /**
     * Check if all choices can be used as key (i.e. are string or int).
     * This is used to optimize the generated code.
     *
     * @return bool
     *
     * @phpstan-assert-if-true list<string|int> $this->choices
     */
    private function choicesCanBeUsedAsKey(): bool
    {
        foreach ($this->choices as $choice) {
            if (!is_string($choice) && !is_int($choice)) {
                return false;
            }
        }

        return true;
    }
}
