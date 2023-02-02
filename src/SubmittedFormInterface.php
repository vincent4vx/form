<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FormView;

/**
 * Type for form which has been submitted and validated
 *
 * @template T as object
 * @extends FilledFormInterface<T>
 *
 * @see FormInterface::submit() For create a submitted form
 */
interface SubmittedFormInterface extends FilledFormInterface
{
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
