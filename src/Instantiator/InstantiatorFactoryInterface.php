<?php

namespace Quatrevieux\Form\Instantiator;

use Quatrevieux\Form\FormInterface;

/**
 *
 */
interface InstantiatorFactoryInterface
{
    /**
     * Create instantiator instance which handle given DTO class
     *
     * @param class-string<T> $dataClass DTO class name
     *
     * @return InstantiatorInterface<T>
     *
     * @template T as object
     */
    public function create(string $dataClass): InstantiatorInterface;
}
