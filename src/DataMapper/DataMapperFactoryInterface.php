<?php

namespace Quatrevieux\Form\DataMapper;

/**
 * Base type for perform creation of DataMapperInterface instance
 */
interface DataMapperFactoryInterface
{
    /**
     * Create data mapper instance which handle given DTO class
     *
     * @param class-string<T> $dataClass DTO class name
     *
     * @return DataMapperInterface<T>
     *
     * @template T as object
     */
    public function create(string $dataClass): DataMapperInterface;
}
