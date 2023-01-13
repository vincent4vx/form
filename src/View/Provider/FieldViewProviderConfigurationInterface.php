<?php

namespace Quatrevieux\Form\View\Provider;

use Quatrevieux\Form\RegistryInterface;

/**
 * Type for configure the field view generation behavior
 *
 * It should be used as attribute on the corresponding property on the DTO.
 * If no configuration is provided, an empty {@see FieldViewConfiguration} will be used.
 */
interface FieldViewProviderConfigurationInterface
{
    /**
     * Get the actual view provider instance
     *
     * @param RegistryInterface $registry
     * @return FieldViewProviderInterface<static>
     */
    public function getViewProvider(RegistryInterface $registry): FieldViewProviderInterface;
}
