<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;

/**
 * Runtime implementation of {@see FormViewInstantiatorInterface}
 */
final class RuntimeFormViewInstantiator implements FormViewInstantiatorInterface
{
    public function __construct(
        private readonly RegistryInterface $registry,

        /**
         * Map a field name to its view configuration
         *
         * @var array<string, FieldViewProviderConfigurationInterface>
         */
        private readonly array $providerConfigurations,

        /**
         * Map DTO field name to HTTP field name
         * If a mapping is not found, the field name is used as HTTP field name
         *
         * @var array<string, string>
         */
        private readonly array $fieldsNameMapping,
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

        foreach ($this->providerConfigurations as $name => $configuration) {
            $fieldName = $this->fieldsNameMapping[$name] ?? $name;
            $fullFieldName = $rootField ? $rootField . '[' . $fieldName . ']' : $fieldName;

            $fields[$name] = $configuration->getViewProvider($registry)->view(
                $configuration,
                $fullFieldName,
                $value[$fieldName] ?? null,
                $errors[$name] ?? null,
                [] // @todo attributes
            );
        }

        return new FormView($fields, $value);
    }
}
