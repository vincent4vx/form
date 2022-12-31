<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;

use function is_object;

/**
 * @implements ConstraintValidatorInterface<Embedded>
 * @implements ConstraintValidatorGeneratorInterface<Embedded>
 *
 * @internal Used and instantiated by {@see Embedded::getValidator()}
 */
final class EmbeddedValidator implements ConstraintValidatorInterface, ConstraintValidatorGeneratorInterface
{
    public function __construct(
        private readonly ValidatorFactoryInterface $validatorFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, FieldError|mixed[]>
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?array
    {
        // Skip invalid embedded data
        if (!is_object($value)) {
            return null;
        }

        return $this->validatorFactory->create($constraint->class)->validate($value);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        $classNameString = Code::value($constraint->class);

        return FieldErrorExpression::aggregate(fn (string $propertyAccessor) => "is_object($propertyAccessor) ? \$this->registry->getValidatorFactory()->create($classNameString)->validate($propertyAccessor) : null");
    }
}
