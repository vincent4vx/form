<?php

namespace Quatrevieux\Form\DataMapper;

/**
 * Base type for perform creation of DataMapperInterface instance
 * Should be used as attribute
 */
interface DataMapperProviderInterface
{
    /**
     * Create the data mapper instance which handle given DTO class
     *
     * @param class-string<T> $dataClassName DTO class name
     *
     * @return DataMapperInterface<T>
     * @template T as object
     */
    public function getDataMapper(string $dataClassName): DataMapperInterface;
}
