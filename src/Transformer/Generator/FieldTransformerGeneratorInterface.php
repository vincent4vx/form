<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

/**
 * Code generator for a field transformer
 * This generator should be implemented by the {@see FieldTransformerInterface} or {@see ConfigurableFieldTransformerInterface} class.
 * If not {@see GenericFieldTransformerGenerator} will be used.
 *
 * @template T as object
 */
interface FieldTransformerGeneratorInterface
{
    /**
     * Generate the {@see FieldTransformerInterface::transformFromHttp()} inlined code
     *
     * Note: expression passed as parameter is not guaranteed to be constant, so you must store it into a temporary variable
     *       if you have to reuse this value on the transformation process.
     *
     * @param T $transformer Transformer instance to compile
     * @param string $previousExpression Expression of the previous transformer call, or HTTP field value
     * @param FormTransformerGenerator $generator The generator instance for generating sub-transformers
     *
     * @return string Generated PHP expression
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string;

    /**
     * Generate the {@see FieldTransformerInterface::transformToHttp()} inlined code
     *
     * Note: expression passed as parameter is not guaranteed to be constant, so you must store it into a temporary variable
     *       if you have to reuse this value on the transformation process.
     *
     * @param T $transformer Transformer instance to compile
     * @param string $previousExpression Expression of the previous transformer call, or DTO property value
     * @param FormTransformerGenerator $generator The generator instance for generating sub-transformers
     *
     * @return string Generated PHP expression
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string;
}
