<?php

namespace Quatrevieux\Form\DataMapper;

use Quatrevieux\Form\Transformer\FormTransformerInterface;

/**
 * Handle instantiation of data objects from an associative array of fields and vice versa
 *
 * On form submission, it's used after transformation, but before validation.
 * On import, it's used before transformation.
 *
 * This type is not responsible for data or field transformation.
 *
 * @template T as object
 */
interface DataMapperInterface
{
    /**
     * Create the data object from the given fields
     * A new object will always be created.
     *
     * Fields passed to this method must be transformed to the correct type.
     *
     * @param array<string, mixed> $fields Associative array of fields, where keys are field names and values are field values.
     * @return T
     *
     * @see FormTransformerInterface::transformFromHttp() For converting HTTP data to the correct type, to be passed to this method
     * @see DataMapperInterface::toArray() For the reverse operation
     */
    public function toDataObject(array $fields): object;

    /**
     * Extract the data object into an associative array of fields
     *
     * @param T $data
     * @return array<string, mixed>
     *
     * @see DataMapperInterface::toDataObject() For the reverse operation
     */
    public function toArray(object $data): array;

    /**
     * Get the handled data class name
     *
     * @return class-string<T>
     */
    public function className(): string;
}
