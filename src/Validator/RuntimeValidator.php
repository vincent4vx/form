<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;

/**
 * Simple runtime implementation of form validator using associative array to map constraints on each field.
 * Field validation will be stopped on the first constraint violation.
 *
 * @template T as object
 * @implements ValidatorInterface<T>
 */
final class RuntimeValidator implements ValidatorInterface
{
    public function __construct(
        private readonly ConstraintValidatorRegistryInterface $validatorRegistry,
        /**
         * Map field name to list of constraints
         *
         * @var array<string, list<ConstraintInterface>>
         */
        private readonly array $fieldsConstraints,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(object $data, array $previousErrors = []): array
    {
        $registry = $this->validatorRegistry;
        $errors = $previousErrors;

        /**
         * @var string $fieldName
         * @var list<ConstraintInterface> $constraints
         */
        foreach ($this->fieldsConstraints as $fieldName => $constraints) {
            if (isset($previousErrors[$fieldName])) {
                continue;
            }

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
     * Get configured constraints indexed by field name
     *
     * @return array<string, list<ConstraintInterface>>
     */
    public function getFieldsConstraints(): array
    {
        return $this->fieldsConstraints;
    }
}
