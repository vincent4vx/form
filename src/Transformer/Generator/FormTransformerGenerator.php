<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Transformer\RuntimeFormTransformer;

final class FormTransformerGenerator
{
    /**
     * @return string The generated code
     */
    public function generate(string $transformerClassName, RuntimeFormTransformer $transformer): string
    {
        $classHelper = new FormTransformerClass($transformerClassName);

        foreach ($transformer->getFieldsTransformers() as $fieldName => $transformers) {
            foreach ($transformers as $transformer) {
                $generator = $transformer instanceof FieldTransformerGeneratorInterface ? $transformer : new GenericFieldTransformerGenerator(); // @todo keep instance

                $classHelper->addFieldTransformationExpression(
                    $fieldName,
                    fn (string $previousExpression) => $generator->generateTransformFromHttp($transformer, $previousExpression),
                    fn (string $previousExpression) => $generator->generateTransformToHttp($transformer, $previousExpression)
                );
            }
        }

        $classHelper->generateFromHttp();
        $classHelper->generateToHttp();

        return $classHelper->code();
    }
}
