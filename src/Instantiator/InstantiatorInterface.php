<?php

namespace Quatrevieux\Form\Instantiator;

/**
 * @template T as object
 *
 * @todo rename to serializer ?
 */
interface InstantiatorInterface
{
    /**
     * @param array $fields
     * @return T
     */
    public function instantiate(array $fields): object;

    /**
     * @return class-string<T>
     */
    public function className(): string;
}
