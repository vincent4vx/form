<?php

namespace Quatrevieux\Form\DataMapper;

use TypeError;

use function get_object_vars;

/**
 * Simple data mapper implementation using default constructor and fill directly public properties
 *
 * @template T as object
 * @implements DataMapperInterface<T>
 */
final class PublicPropertyDataMapper implements DataMapperInterface
{
    public function __construct(
        /**
         * Data transfer object class name
         *
         * @var class-string<T> $className
         */
        private readonly string $className,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function toDataObject(array $fields): object
    {
        $className = $this->className;
        $object = new $className();

        foreach ($fields as $name => $value) {
            try {
                $object->$name = $value;
            } catch (TypeError $e) {
                // Ignore type error : can occur when trying to set null on a non-nullable property
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(object $data): array
    {
        return get_object_vars($data);
    }
}
