<?php

namespace Quatrevieux\Form\Transformer;

/**
 * Base type for perform creation of FormTransformerInterface instance
 */
interface FormTransformerFactoryInterface
{
    /**
     * Create the transformer instance for the given data class
     * The transformer must return an array with all data class properties as array key and transformed value as array value
     *
     * @param class-string $dataClassName Class name of the data object
     * @return FormTransformerInterface
     */
    public function create(string $dataClassName): FormTransformerInterface;
}
