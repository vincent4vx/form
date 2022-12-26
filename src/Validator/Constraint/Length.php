<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use LogicException;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

/**
 * Validate the length of a string field
 * If the field is not a string, this validator will be ignored
 *
 * @implements ConstraintValidatorGeneratorInterface<static>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class Length extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public const MIN_MESSAGE = 'The value is too short. It should have {{ min }} characters or more.';
    public const MAX_MESSAGE = 'The value is too long. It should have {{ max }} characters or less.';
    public const INTERVAL_MESSAGE = 'The value length is invalid. It should be between {{ min }} and {{ max }} characters long.';

    public function __construct(
        /**
         * Minimum length (included)
         * If null, no minimum length will be checked
         */
        public readonly ?int $min = null,

        /**
         * Maximum length (included)
         * If null, no maximum length will be checked
         */
        public readonly ?int $max = null,

        /**
         * Error message displayed if the length is not between $min and $max
         * Use `{{ min }}` and `{{ max }}` placeholders to display the min and max parameters (if defined)
         *
         * If null, the default message will be used depending on defined parameters :
         * - Length::MIN_MESSAGE if only $min is defined
         * - Length::MAX_MESSAGE if only $max is defined
         * - Length::INTERVAL_MESSAGE if both $min and $max are defined
         *
         * @var string|null
         */
        public readonly ?string $message = null,
    ) {
        if ($this->min === null && $this->max === null) {
            throw new LogicException('At least one of parameters "min" or "max" must be set');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if (!is_scalar($value)) {
            return null;
        }

        $length = strlen((string) $value);

        if (($this->min === null || $length >= $this->min) && ($this->max === null || $length <= $this->max)) {
            return null;
        }

        $params = [];

        if ($this->min !== null) {
            $params['min'] = $this->min;
        }

        if ($this->max !== null) {
            $params['max'] = $this->max;
        }

        return new FieldError($this->message(), $params);
    }

    /**
     * {@inheritdoc}
     *
     * @param Length $constraint
     */
    public function generate(ConstraintInterface $constraint, string $fieldAccessor, ValidatorGenerator $generator): string
    {
        $lenVarName = Code::varName($fieldAccessor, 'len');
        $lenVarNameInit = "$lenVarName = strlen($fieldAccessor)";
        $expression = '';
        $errorMessage = Code::value($constraint->message());
        $errorParams = [];

        if ($constraint->min !== null) {
            $errorParams['min'] = $constraint->min;
            $expression .= "({$lenVarNameInit}) < {$constraint->min}";
        }

        if ($constraint->max !== null) {
            $errorParams['max'] = $constraint->max;

            if ($expression) {
                $expression .= " || {$lenVarName} > {$constraint->max}";
            } else {
                $expression .= "({$lenVarNameInit}) > {$constraint->max}";
            }
        }

        $errorParams = Code::value($errorParams);

        return "is_scalar($fieldAccessor) && ($expression) ? new FieldError($errorMessage, $errorParams) : null";
    }

    /**
     * Get the error message
     * If no message is defined, the default message will be used depending on defined parameters
     */
    public function message(): string
    {
        // @phpstan-ignore-next-line default is never reached because at least one of min or max is defined
        return $this->message ?? match (true) {
            $this->min !== null && $this->max !== null => self::INTERVAL_MESSAGE,
            $this->min !== null => self::MIN_MESSAGE,
            $this->max !== null => self::MAX_MESSAGE,
        };
    }
}
