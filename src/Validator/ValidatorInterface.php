<?php

namespace Quatrevieux\Form\Validator;

/**
 * Type for validate a form data class
 *
 * @template T as object
 */
interface ValidatorInterface
{
    /**
     * Validate DTO fields values
     *
     * This method should return an empty array if the DTO is valid
     * The validation of each field must stop at the first error
     *
     * @param T $data Object to validate
     * @param array<string, FieldError> $previousErrors Errors occurring on previous stages (ex: transformation errors)
     *
     * @return array<string, FieldError> Errors for each field, indexed by the field name
     */
    public function validate(object $data, array $previousErrors = []): array;
}
