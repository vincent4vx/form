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
}
