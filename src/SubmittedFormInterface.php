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
     * @return array<string, FieldError>
     */
    public function errors(): array;
}
