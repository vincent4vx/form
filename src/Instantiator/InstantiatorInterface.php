<?php

namespace Quatrevieux\Form\Instantiator;

/**
 * @template T as object
 *
 * @todo rename to serializer or hydrator ?
 */
interface InstantiatorInterface
{
    /**
     * @param array<string, mixed> $fields
     * @return T
     */
    public function instantiate(array $fields): object;

    /**
     * @param T $data
     * @return array<string, mixed>
     */
    public function export(object $data): array;

    /**
     * @return class-string<T>
     */
    public function className(): string;
}
