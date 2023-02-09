<?php

namespace Quatrevieux\Form\DataMapper;

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
     * @return DataMapperInterface<T>
     *
     * @template T as object
     */
    public function create(string $dataClass): DataMapperInterface;
}
