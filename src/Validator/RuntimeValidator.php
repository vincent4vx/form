<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;

/**
 * @template T as object
 * @implements ValidatorInterface<T>
 */
class RuntimeValidator implements ValidatorInterface
{
    public function __construct(
        private readonly ConstraintValidatorRegistryInterface $validatorRegistry,
        /**
         * @var array<string, list<ConstraintInterface>>
         */
        private readonly array $fieldsConstraints,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(object $data): array
    {
        $registry = $this->validatorRegistry;
        $errors = [];

        /**
         * @var string $fieldName
         * @var ConstraintInterface[] $constraints
         */
        foreach ($this->fieldsConstraints as $fieldName => $constraints) {
            $fieldValue = $data->$fieldName ?? null;

            foreach ($constraints as $constraint) {
                $error = $constraint->getValidator($registry)->validate($constraint, $fieldValue, $data);

                if ($error) {
                    $errors[$fieldName] = $error;
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * @return array<string, list<ConstraintInterface>>
     */
    public function getFieldsConstraints(): array
    {
        return $this->fieldsConstraints;
    }
}
