<?php

namespace Quatrevieux\Form\Validator;

/**
 * @template T as object
 *
 * @todo class name as method ?
 */
interface ValidatorInterface
{
    /**
     * @param T $data
     * @return array<string, FieldError>
     *
     * @todo embedded
     */
    public function validate(object $data): array;
}
