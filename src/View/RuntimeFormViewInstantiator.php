<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerInterface;
use Quatrevieux\Form\View\Provider\FieldChoiceProviderInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;

/**
 * Runtime implementation of {@see FormViewInstantiatorInterface}
 */
final class RuntimeFormViewInstantiator implements FormViewInstantiatorInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,

        public readonly string $dataClassName,

        /**
         * Map a field name to its view configuration
         *
         * @var array<string, FieldViewProviderConfigurationInterface>
         */
        public readonly array $providerConfigurations,

        /**
         * Map DTO field name to HTTP field name
         * If a mapping is not found, the field name is used as HTTP field name
         *
         * @var array<string, string>
         */
        public readonly array $fieldsNameMapping,

        /**
         * Get provided attributes for a field
         * The key is the field name, the value is the attributes
         *
         * @var array<string, array<string, scalar>>
         */
        public readonly array $attributesByField,

        /**
         * @var array<string, FieldChoiceProviderInterface>
         */
        public readonly array $choicesProviderByField,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function default(?string $rootField = null): FormView
    {
        return $this->submitted([], [], $rootField);
    }

    /**
     * {@inheritdoc}
     */
    public function submitted(array $value, array $errors, ?string $rootField = null): FormView
    {
        $fields = [];
        $registry = $this->registry;
        $formTransformer = null;

        foreach ($this->providerConfigurations as $name => $configuration) {
            $fieldName = $this->fieldsNameMapping[$name] ?? $name;
            $fullFieldName = $rootField ? $rootField . '[' . $fieldName . ']' : $fieldName;

            $fields[$name] = $fieldView = $configuration->getViewProvider($registry)->view(
                $configuration,
                $fullFieldName,
                $value[$fieldName] ?? null,
                $errors[$name] ?? null,
                $this->attributesByField[$name] ?? [],
            );

            if (isset($this->choicesProviderByField[$name])) {
                $formTransformer ??= $this->registry->getTransformerFactory()->create($this->dataClassName);
                $fieldTransformer = $formTransformer->fieldTransformer($name);

                $fieldView->choices = ($this->choicesProviderByField[$name]->choices($value[$fieldName] ?? null, $fieldTransformer));
            }
        }

        return new FormView($fields, $value);
    }
}
