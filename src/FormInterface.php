<?php

namespace Quatrevieux\Form;

/**
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
}
