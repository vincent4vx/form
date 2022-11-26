<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

/**
 * Code generator for a field transformer
 * This generator should be implemented by the {@see FieldTransformerInterface} class.
 * If not {@see GenericFieldTransformerGenerator} will be used.
 *
 * @template T as FieldTransformerInterface
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
     *
     * @return string Generated PHP expression
     */
    public function generateTransformFromHttp(FieldTransformerInterface $transformer, string $previousExpression): string;

    /**
     * Generate the {@see FieldTransformerInterface::transformToHttp()} inlined code
     *
     * Note: expression passed as parameter is not guaranteed to be constant, so you must store it into a temporary variable
     *       if you have to reuse this value on the transformation process.
     *
     * @param T $transformer Transformer instance to compile
     * @param string $previousExpression Expression of the previous transformer call, or DTO property value
     *
     * @return string Generated PHP expression
     */
    public function generateTransformToHttp(FieldTransformerInterface $transformer, string $previousExpression): string;
}
