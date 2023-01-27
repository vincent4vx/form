<?php

namespace Quatrevieux\Form\View\Generator;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\RuntimeFormViewInstantiator;

/**
 * Generates {@see RuntimeFormViewInstantiator} class for a data class
 */
final class FormViewInstantiatorGenerator
{
    public function __construct(
        private readonly RegistryInterface $registry,

        /**
         * @var FieldViewProviderGeneratorInterface<FieldViewProviderConfigurationInterface>
         */
        private readonly FieldViewProviderGeneratorInterface $fallbackFieldViewProviderGenerator = new GenericFieldViewProviderGenerator(),
    ) {
    }

    /**
     * Generate the PHP code of the {@see RuntimeFormViewInstantiator} class
     *
     * @param string $generatedClassName The name of the generated class
     * @param RuntimeFormViewInstantiator $viewInstantiator The view instantiator to generate
     *
     * @return string The PHP code of the generated class
     */
    public function generate(string $generatedClassName, RuntimeFormViewInstantiator $viewInstantiator): string
    {
        $classHelper = new FormViewInstantiatorClass($generatedClassName);

        $fieldsNameMapping = $viewInstantiator->fieldsNameMapping;
        $attributesByField = $viewInstantiator->attributesByField;

        foreach ($viewInstantiator->providerConfigurations as $field => $configuration) {
            $provider = $configuration->getViewProvider($this->registry);
            $generator = $provider instanceof FieldViewProviderGeneratorInterface
                ? $provider
                : $this->fallbackFieldViewProviderGenerator
            ;

            $httpField = $fieldsNameMapping[$field] ?? $field;

            $classHelper->declareFieldView(
                $field,
                $httpField,
                $generator->generateFieldViewExpression($configuration, $httpField, $attributesByField[$field] ?? [])
            );
        }

        $classHelper->generateSubmitted();
        $classHelper->generateDefault();

        return $classHelper->code();
    }
}
