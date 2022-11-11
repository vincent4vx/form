<?php

namespace Quatrevieux\Form\Transformer;

interface FormTransformerFactoryInterface
{
    /**
     * @param string $dataClassName
     * @return FormTransformerInterface
     */
    public function create(string $dataClassName): FormTransformerInterface;
}
