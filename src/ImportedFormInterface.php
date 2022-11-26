<?php

namespace Quatrevieux\Form;

/**
 * @template T as object
 */
interface ImportedFormInterface
{
    /**
     * Get imported value
     *
     * @return T
     */
    public function value(): object;

    /**
     * Get imported value normalized as HTTP value
     *
     * @return array<string, mixed>
     */
    public function httpValue(): array;
}
