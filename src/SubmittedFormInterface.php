<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Validator\FieldError;

/**
 * @template T as object
 */
interface SubmittedFormInterface
{
    /**
     * Get validated and normalized value
     *
     * @return T
     */
    public function value(): object;

    /**
     * Does submitted data are valid ?
     *
     * @return bool true if the form is valid. false otherwise.
     */
    public function valid(): bool;

    /**
     * Get fields errors
     *
     * Errors are indexed by the field name
     * If the field is an array or object, the value can be an array of errors.
     *
     * @return array<string, FieldError|mixed[]>
     */
    public function errors(): array;
}
