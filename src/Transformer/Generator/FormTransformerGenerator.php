<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Transformer\FormTransformerInterface;
use Quatrevieux\Form\Transformer\RuntimeFormTransformer;

/**
 * Generate the {@see FormTransformerInterface} class for a data class
 */
final class FormTransformerGenerator
{
    public function __construct(
        private readonly FieldTransformerRegistryInterface $registry,

        /**
         * Default code generator to use the field transformer do not implement {@see FieldTransformerGeneratorInterface}
         *
         * @var FieldTransformerGeneratorInterface<FieldTransformerInterface>
         */
        private readonly FieldTransformerGeneratorInterface $genericTransformerGenerator = new GenericFieldTransformerGenerator(),

        /**
         * Default code generator to use the field transformer implementation do not implement {@see FieldTransformerGeneratorInterface}
         *
         * @var FieldTransformerGeneratorInterface<DelegatedFieldTransformerInterface>
         */
        private readonly FieldTransformerGeneratorInterface $delegatedFieldTransformerGenerator = new DelegatedFieldTransformerGenerator(),
    ) {
    }

    /**
     * Compile given FormTransformer to a class
     *
     * @param string $transformerClassName Class name of the generated FormTransformerInterface class
     * @param RuntimeFormTransformer $transformer Transformer to compile
     *
     * @return string The generated code
     */
    public function generate(string $transformerClassName, RuntimeFormTransformer $transformer): string
    {
        $classHelper = new FormTransformerClass($transformerClassName);
        $fieldNameMapping = $transformer->getFieldsNameMapping();
        $fieldErrorConfigurations = $transformer->getFieldsTransformationErrors();

        foreach ($transformer->getFieldsTransformers() as $fieldName => $transformers) {
            $classHelper->declareField(
                $fieldName,
                $fieldNameMapping[$fieldName] ?? $fieldName,
                $fieldErrorConfigurations[$fieldName] ?? null
            );

            foreach ($transformers as $transformer) {
                $generator = $this->resolveGenerator($transformer);
                $canThrowError = !$transformer instanceof FieldTransformerInterface || $transformer->canThrowError();

                $classHelper->addFieldTransformationExpression(
                    $fieldName,
                    fn (string $previousExpression) => $generator->generateTransformFromHttp($transformer, $previousExpression),
                    fn (string $previousExpression) => $generator->generateTransformToHttp($transformer, $previousExpression),
                    $canThrowError
                );
            }
        }

        $classHelper->generateFromHttp();
        $classHelper->generateToHttp();

        return $classHelper->code();
    }

    /**
     * Resolve the generator to use for given field transformer
     * - If the field transformer implements {@see FieldTransformerGeneratorInterface}, use it
     * - If the field transformer implements {@see DelegatedFieldTransformerInterface}, check if the delegate implements {@see FieldTransformerGeneratorInterface}
     * - Otherwise, use the generic generator
     *
     * @param object $transformer
     * @return FieldTransformerGeneratorInterface
     */
    private function resolveGenerator(object $transformer): FieldTransformerGeneratorInterface
    {
        if ($transformer instanceof FieldTransformerGeneratorInterface) {
            return $transformer;
        }

        if ($transformer instanceof DelegatedFieldTransformerInterface) {
            if (($generator = $transformer->getTransformer($this->registry)) instanceof FieldTransformerGeneratorInterface) {
                return $generator;
            }

            return $this->delegatedFieldTransformerGenerator;
        }

        return $this->genericTransformerGenerator;
    }
}
