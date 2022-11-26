<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerInterface;
use Quatrevieux\Form\Transformer\RuntimeFormTransformer;

/**
 * Generate the {@see FormTransformerInterface} class for a data class
 */
final class FormTransformerGenerator
{
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

        foreach ($transformer->getFieldsTransformers() as $fieldName => $transformers) {
            $classHelper->declareField($fieldName);

            foreach ($transformers as $transformer) {
                if ($transformer instanceof DelegatedFieldTransformerInterface) {
                    $generator = new DelegatedFieldTransformerGenerator(); // @todo keep instance

                    $classHelper->addFieldTransformationExpression(
                        $fieldName,
                        fn(string $previousExpression) => $generator->generateTransformFromHttp($transformer, $previousExpression),
                        fn(string $previousExpression) => $generator->generateTransformToHttp($transformer, $previousExpression)
                    );
                } else {
                    $generator = $transformer instanceof FieldTransformerGeneratorInterface ? $transformer : new GenericFieldTransformerGenerator(); // @todo keep instance

                    $classHelper->addFieldTransformationExpression(
                        $fieldName,
                        fn(string $previousExpression) => $generator->generateTransformFromHttp($transformer, $previousExpression),
                        fn(string $previousExpression) => $generator->generateTransformToHttp($transformer, $previousExpression)
                    );
                }
            }
        }

        $classHelper->generateFromHttp();
        $classHelper->generateToHttp();

        return $classHelper->code();
    }
}
