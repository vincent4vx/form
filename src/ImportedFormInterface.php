<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Validator\FieldError;

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
     * @return array
     */
    public function httpValue(): array;
}
