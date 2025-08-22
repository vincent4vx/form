<?php

namespace Quatrevieux\Form\Embedded;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

use function is_array;

/**
 * @implements ConstraintValidatorInterface<ArrayOf>
 * @implements ConstraintValidatorGeneratorInterface<ArrayOf>
 *
 * @internal Instantiated by {@see ArrayOf::getValidator()}
 */
final class ArrayOfValidator implements ConstraintValidatorInterface, ConstraintValidatorGeneratorInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {}

    /**
     * {@inheritdoc}
     *
     * @return FieldError[]|mixed[]|null
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $validator = $this->registry->getValidatorFactory()->create($constraint->class);
        $errors = [];

        foreach ($value as $key => $item) {
            if ($itemErrors = $validator->validate($item)) {
                $errors[$key] = $itemErrors;
            }
        }

        // Return null if there is no error
        return $errors ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        return FieldErrorExpression::aggregate(function (string $fieldAccessor) use ($constraint) {
            $validator = Call::object('$this->registry->getValidatorFactory()')->create($constraint->class);
            $body = 'function ($value) {'
                . '$validator = ' . $validator . ';'
                . '$errors = [];'
                . 'foreach ($value as $key => $item) {'
                    . 'if ($itemErrors = $validator->validate($item)) {'
                        . '$errors[$key] = $itemErrors;'
                    . '}'
                . '}'
                . 'return $errors ?: null;'
            . '}';

            return "!is_array({$fieldAccessor}) ? null : ({$body})({$fieldAccessor})";
        });
    }
}
