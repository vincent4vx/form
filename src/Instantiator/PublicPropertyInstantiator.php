<?php

namespace Quatrevieux\Form\Instantiator;

/**
 * @template T as object
 * @implements InstantiatorInterface<T>
 */
final class PublicPropertyInstantiator implements InstantiatorInterface
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
            $object->$name = $value;
        }

        return $object;
    }
}
