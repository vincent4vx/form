<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;

use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;

use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

use function filter_var;
use function is_scalar;

/**
 * Validate field value using {@see filter_var()} with FILTER_VALIDATE_* constant
 *
 * Usage:
 * <code>
 * class MyRequest
 * {
 *     #[ValidateVar(ValidateVar::EMAIL)]
 *     public ?string $email;
 *
 *     #[ValidateVar(ValidateVar::DOMAIN, options: FILTER_FLAG_HOSTNAME)] // You can add flags as an int
 *     public ?string $domain;
 *
 *     #[ValidateVar(ValidateVar::INT, options: ['options' => ['min_range' => 0, 'max_range' => 100]])] // You can add options as an array
 *     public ?float $int;
 * }
 * </code>
 *
 * @implements ConstraintValidatorGeneratorInterface<ValidateVar>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class ValidateVar extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public const CODE = '2aaca916-3129-5920-a443-4968910199c4';

    public const DOMAIN = FILTER_VALIDATE_DOMAIN;
    public const EMAIL = FILTER_VALIDATE_EMAIL;
    public const FLOAT = FILTER_VALIDATE_FLOAT;
    public const INT = FILTER_VALIDATE_INT;
    public const IP = FILTER_VALIDATE_IP;
    public const URL = FILTER_VALIDATE_URL;

    public function __construct(
        /**
         * The id of the validation filter to apply
         * Should be one of the constants of this class or FILTER_VALIDATE_*
         */
        public readonly int $filter,

        /**
         * Filter options or flags
         * To use flags with options, use an array with the key "flags", and options with the key "options"
         *
         * @var array{options?: array<string, mixed>, flags?: int}|int
         */
        public readonly array|int $options = 0,

        /**
         * The error message
         */
        public readonly string $message = 'This value is not a valid.',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): FieldError|array|null
    {
        if (!is_scalar($value)) {
            return null;
        }

        if (filter_var($value, $this->filter, $this->options) === false) {
            return new FieldError($this->message, code: self::CODE);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::single(fn (string $accessor) => (string) Code::expr($accessor)->format(
            'is_scalar({}) && filter_var({}, {filter}, {options}) === false ? {error} : null',
            filter: $constraint->filter,
            options: $constraint->options,
            error: new FieldError($this->message, code: self::CODE),
        ));
    }
}
