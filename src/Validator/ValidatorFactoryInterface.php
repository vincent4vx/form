<?php

namespace Quatrevieux\Form\Validator;

interface ValidatorFactoryInterface
{
    /**
     * @param class-string<T> $dataClass
     * @return ValidatorInterface<T>
     * @template T as object
     */
    public function create(string $dataClass): ValidatorInterface;
}
