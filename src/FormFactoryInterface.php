<?php

namespace Quatrevieux\Form;

/**
 * Factory for creates forms
 */
interface FormFactoryInterface
{
    /**
     * Create a form instance which handle given DTO class
     *
     * @param class-string<T> $dataClass DTO class name
     *
     * @return FormInterface<T>
     *
     * @template T as object
     */
    public function create(string $dataClass): FormInterface;

    /**
     * Create a form and import data into it
     * This is equivalent to calling `$factory->create(get_class($data))->import($data)`
     *
     * @param T $data DTO instance to import
     *
     * @return ImportedFormInterface<T>
     *
     * @template T as object
     */
    public function import(object $data): ImportedFormInterface;
}
