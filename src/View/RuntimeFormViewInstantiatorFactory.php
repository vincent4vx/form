<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\HttpField;
use Quatrevieux\Form\View\Provider\FieldViewConfiguration;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

/**
 * Loads metadata from class attributes and create the configured {@see RuntimeFormViewInstantiator}.
 */
final class RuntimeFormViewInstantiatorFactory implements FormViewInstantiatorFactoryInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClassName): FormViewInstantiatorInterface
    {
        $fieldsNameMapping = [];
        $providerConfigurations = [];

        foreach ((new ReflectionClass($dataClassName))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $providerConfiguration = null;
            $fieldName = $property->getName();

            foreach ($property->getAttributes(HttpField::class) as $httpFieldAttribute) {
                $fieldsNameMapping[$fieldName] = $httpFieldAttribute->newInstance()->name;
            }

            foreach ($property->getAttributes(FieldViewProviderConfigurationInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $fieldViewProviderConfigurationAttribute) {
                $providerConfiguration = $fieldViewProviderConfigurationAttribute->newInstance();
            }

            $providerConfigurations[$fieldName] = $providerConfiguration ?? new FieldViewConfiguration();
        }

        return new RuntimeFormViewInstantiator(
            $this->registry,
            $providerConfigurations,
            $fieldsNameMapping,
        );
    }
}
