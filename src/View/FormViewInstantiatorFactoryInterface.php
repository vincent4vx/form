<?php

namespace Quatrevieux\Form\View;

/**
 * Loads form metadata from a class and create the corresponding {@see FormViewInstantiatorInterface} instance.
 */
interface FormViewInstantiatorFactoryInterface
{
    /**
     * Create the {@see FormViewInstantiatorInterface} instance for the given DTO class
     *
     * @param class-string $dataClassName DTO class name to load
     * @return FormViewInstantiatorInterface
     */
    public function create(string $dataClassName): FormViewInstantiatorInterface;
}
