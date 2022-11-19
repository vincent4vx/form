<?php

namespace Quatrevieux\Form\Transformer\Field;

use BadMethodCallException;

/**
 * Null-object for transformer registry
 */
final class NullFieldTransformerRegistry implements FieldTransformerRegistryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTransformer(string $className): ConfigurableFieldTransformerInterface
    {
        throw new BadMethodCallException('Cannot use delegated transformer : no container or custom registry defined.');
    }
}
