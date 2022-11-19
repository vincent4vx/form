<?php

namespace Quatrevieux\Form\Transformer\Field;

/**
 * Registry of transformers instances
 */
interface FieldTransformerRegistryInterface
{
    /**
     * Get transformer implementation for a {@see DelegatedFieldTransformerInterface}
     *
     * @param class-string<T> $className Class name of the implementation
     *
     * @return T
     * @template T as ConfigurableFieldTransformerInterface
     */
    public function getTransformer(string $className): ConfigurableFieldTransformerInterface;
}
