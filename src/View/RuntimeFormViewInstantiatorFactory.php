<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\HttpField;
use Quatrevieux\Form\View\Provider\FieldChoiceProviderInterface;
use Quatrevieux\Form\View\Provider\FieldViewAttributesProviderInterface;
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
        $attributesByField = [];
        $choicesProviderByField = [];

        foreach ((new ReflectionClass($dataClassName))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $fieldName = $property->getName();

            $providerConfigurations[$fieldName] = $this->providerConfiguration($property);

            if ($httpFieldName = $this->fieldNameMapping($property)) {
                $fieldsNameMapping[$fieldName] = $httpFieldName;
            }

            if ($attributes = $this->attributes($property)) {
                $attributesByField[$fieldName] = $attributes;
            }

            if ($choicesProvider = $this->choicesProvider($property)) {
                $choicesProviderByField[$fieldName] = $choicesProvider;
            }
        }

        return new RuntimeFormViewInstantiator(
            $this->registry,
            $dataClassName,
            $providerConfigurations,
            $fieldsNameMapping,
            $attributesByField,
            $choicesProviderByField,
        );
    }

    /**
     * Get the HTTP field name for the given property
     *
     * @param ReflectionProperty $property
     *
     * @return string|null The HTTP field name, or null if the property name should be used
     */
    private function fieldNameMapping(ReflectionProperty $property): ?string
    {
        foreach ($property->getAttributes(HttpField::class) as $httpFieldAttribute) {
            return $httpFieldAttribute->newInstance()->name;
        }

        return null;
    }

    /**
     * Load the view provider configuration for the given property
     * If no configuration is found, an empty {@see FieldViewConfiguration} will be used.
     *
     * @param ReflectionProperty $property
     *
     * @return FieldViewProviderConfigurationInterface
     */
    private function providerConfiguration(ReflectionProperty $property): FieldViewProviderConfigurationInterface
    {
        foreach ($property->getAttributes(FieldViewProviderConfigurationInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $fieldViewProviderConfigurationAttribute) {
            return $fieldViewProviderConfigurationAttribute->newInstance();
        }

        return FieldViewConfiguration::default();
    }

    /**
     * Get field view attributes for the given property
     *
     * @param ReflectionProperty $property
     *
     * @return array<string, scalar>
     * @see FieldView::$attributes
     */
    private function attributes(ReflectionProperty $property): array
    {
        $attributes = [];

        if ($property->getType()?->allowsNull() === false) {
            $attributes['required'] = true;
        }

        foreach ($property->getAttributes(FieldViewAttributesProviderInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $attributes += $attribute->newInstance()->getAttributes();
        }

        return $attributes;
    }

    private function choicesProvider(ReflectionProperty $property): ?FieldChoiceProviderInterface
    {
        foreach ($property->getAttributes(FieldChoiceProviderInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }
}
