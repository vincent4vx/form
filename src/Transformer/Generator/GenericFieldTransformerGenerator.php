<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Util\Code;

/**
 * Transformer generator used by default, when there is no available generator for the given transformer
 *
 * This generator will simply inline transformer instantiation, and call corresponding transformation method.
 * To instantiate transformer, promoted property will be used.
 *
 * Generated code example:
 * `(new MyTransformer(foo: "bar"))->transformFormHttp($data['foo'] ?? null)`
 *
 * @implements FieldTransformerGeneratorInterface<FieldTransformerInterface>
 */
final class GenericFieldTransformerGenerator implements FieldTransformerGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $newTransformerExpression = Code::instantiate($transformer);

        return "($newTransformerExpression)->transformFromHttp($previousExpression)";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $newTransformerExpression = Code::instantiate($transformer);

        return "($newTransformerExpression)->transformToHttp($previousExpression)";
    }
}
