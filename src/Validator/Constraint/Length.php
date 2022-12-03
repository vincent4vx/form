<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use LogicException;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;

/**
 * @implements ConstraintValidatorGeneratorInterface<static>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Length extends SelfValidatedConstraint implements ConstraintValidatorGeneratorInterface
{
    public function __construct(
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly string $message = 'Invalid length', // @todo parameters
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
        if (!is_string($value)) {
            return null;
        }

        $length = strlen($value);

        if (($this->min !== null && $length < $this->min) || ($this->max !== null && $length > $this->max)) {
            return new FieldError($this->message); // @todo parameters
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @param Length $constraint
     */
    public function generate(ConstraintInterface $constraint, string $fieldAccessor): string
    {
        $lenVarName = Code::varName($fieldAccessor, 'len');
        $lenVarNameInit = "$lenVarName = strlen($fieldAccessor)";
        $expression = '';
        $errorMessage = Code::value($constraint->message);

        if ($constraint->min !== null) {
            $expression .= "({$lenVarNameInit}) < {$constraint->min}";
        }

        if ($constraint->max !== null) {
            if ($expression) {
                $expression .= " || {$lenVarName} > {$constraint->max}";
            } else {
                $expression .= "({$lenVarNameInit}) > {$constraint->max}";
            }
        }

        return "is_string($fieldAccessor) && ($expression) ? new FieldError($errorMessage) : null";
    }
}
