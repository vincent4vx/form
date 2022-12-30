<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\RegistryInterface;

/**
 * Base type for field transformers using an external implementation for perform transformation
 * This type only contains transformer configuration
 *
 * Delegation is useful when transformer needs some external dependencies, for example access to repository to load an entity
 */
interface DelegatedFieldTransformerInterface
{
    /**
     * Get the transformer implementation
     *
     * @param RegistryInterface $registry
     * @return ConfigurableFieldTransformerInterface<static>
     */
    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface;
}
