<?php

namespace Quatrevieux\Form\Instantiator;

use TypeError;

use function get_object_vars;

/**
 * @template T as object
 * @implements DataMapperInterface<T>
 */
final class PublicPropertyDataMapper implements DataMapperInterface
{
    public function __construct(
        /**
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
    public function instantiate(array $fields): object
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
    public function export(object $data): array
    {
        return get_object_vars($data);
    }
}
