<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

/**
 * @template T as FieldTransformerGeneratorInterface
 */
interface FieldTransformerGeneratorInterface
{
    /**
     * @param T $transformer
     * @param string $previousExpression
     * @return string
     */
    public function generateTransformFromHttp(FieldTransformerInterface $transformer, string $previousExpression): string;

    /**
     * @param T $transformer
     * @param string $previousExpression
     * @return string
     */
    public function generateTransformToHttp(FieldTransformerInterface $transformer, string $previousExpression): string;
}
