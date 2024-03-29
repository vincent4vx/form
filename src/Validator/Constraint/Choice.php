<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\View\ChoiceView;
use Quatrevieux\Form\View\Provider\FieldChoiceProviderInterface;
use Stringable;

use function array_values;
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
 * You can define labels for the choices by using a string key in the choices array.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     #[Choice(['foo', 'bar'])]
 *     public string $foo;
 *
 *     // Define labels for the choices
 *     #[Choice([
 *         'My first label' => 'foo',
 *         'My other label' => 'bar',
 *     ])]
 *     public string $bar;
 * }
 * </code>
 *
 * @implements ConstraintValidatorGeneratorInterface<Choice>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Choice extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface, FieldChoiceProviderInterface
{
    public const CODE = '41ac8b62-e143-5644-a3eb-0fbfff5a2064';

    public function __construct(
        /**
         * List of available choices.
         * Use a string key to define a label for the choice.
         *
         * @var mixed[]
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
     * {@inheritdoc}
     */
    public function choices(mixed $currentValue, FieldTransformerInterface $transformer): array
    {
        $choices = [];

        foreach ($this->choices as $label => $choice) {
            /** @var scalar $current */
            $current = $transformer->transformToHttp($choice);
            $choices[] = new ChoiceView($current, is_string($label) ? $label : null, $current == $currentValue);
        }

        return $choices;
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
        $choices = array_values($this->choices);

        if ($this->choicesCanBeUsedAsKey($choices)) {
            $values = array_combine($choices, $choices);
            $inArray = $accessor->format('((is_int({}) || is_string({})) && (({values}[{}] ?? null) === {}))', values: $values);
        } else {
            $inArray = Call::in_array($accessor, $choices, true);
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
        $choices = array_values($this->choices);

        if ($this->choicesCanBeUsedAsKey($choices)) {
            $choices = Code::value(array_combine($choices, $choices));
            $inArray = '((is_int($value) || is_string($value)) && (($choices[$value] ?? null) === $value))';
        } else {
            $choices = Code::value($choices);
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
