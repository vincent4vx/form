<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\View\FormView;

/**
 * Base type for form operations
 * This type is immutable, so all operations return a new instance of the form
 *
 * @template T as object
 */
interface FormInterface
{
    /**
     * Submit HTTP data (or any associative array data) to the form
     *
     * @param array<string, mixed> $data Data to validate
     *
     * @return SubmittedFormInterface<T>
     */
    public function submit(array $data): SubmittedFormInterface;

    /**
     * Import data object into form
     *
     * @param T $data Data object
     *
     * @return ImportedFormInterface<T>
     */
    public function import(object $data): ImportedFormInterface;

    /**
     * Create the view object for the form
     * If the form is submitted or imported, the view will be created with the submitted data and errors
     *
     * @return FormView
     *
     * @throws \BadMethodCallException If the view system is disabled
     */
    public function view(): FormView;
}
