<?php

namespace Quatrevieux\Form\View\Generator;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
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
        $choicesProviderByField = $viewInstantiator->choicesProviderByField;

        // Declare the transformer variable if needed
        if ($choicesProviderByField) {
            $classHelper->property(
                'transformer',
                Expr::this()
                    ->registry
                    ->getTransformerFactory()
                    ->create($viewInstantiator->dataClassName)
            );
        }

        foreach ($viewInstantiator->providerConfigurations as $field => $configuration) {
            $provider = $configuration->getViewProvider($this->registry);
            $generator = $provider instanceof FieldViewProviderGeneratorInterface
                ? $provider
                : $this->fallbackFieldViewProviderGenerator
            ;

            $httpField = $fieldsNameMapping[$field] ?? $field;
            $expression = $generator->generateFieldViewExpression($configuration, $httpField, $attributesByField[$field] ?? []);

            if ($choicesProvider = ($choicesProviderByField[$field] ?? null)) {
                $newExpression = function (...$args) use($expression, $choicesProvider, $viewInstantiator, $field, $httpField) {
                    $transformer = Expr::this()->transformer->fieldTransformer($field);
                    return '(' . $expression(...$args) . ')->choices((' . Code::instantiate($choicesProvider) . ')->choices('.$args[0].', ' . $transformer . '))';
                };

                $expression = $newExpression;
            }

            $classHelper->declareFieldView(
                $field,
                $httpField,
                $expression,
            );
        }

        $classHelper->generateSubmitted();
        $classHelper->generateDefault();

        return $classHelper->code();
    }
}
