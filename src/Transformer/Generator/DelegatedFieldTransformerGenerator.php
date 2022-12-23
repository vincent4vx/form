<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Util\Code;

/**
 * Generate inline expression for call a delegated field transformer
 *
 * This generator will simply inline transformer instantiation, and call `->getTransformer($this->registry)->transformXxxHttp()`.
 * To instantiate transformer, promoted property will be used.
 *
 * Generated code example:
 * `($__transformer_e78fe5 = new MyTransformer(foo: "bar"))->getTransformer($this->registry)->transformFormHttp($__transformer_e78fe5, $data['foo'] ?? null)`
 *
 * @see DelegatedFieldTransformerInterface
 * @implements FieldTransformerGeneratorInterface<DelegatedFieldTransformerInterface>
 */
final class DelegatedFieldTransformerGenerator implements FieldTransformerGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $newTransformerExpression = Code::newExpression($transformer);
        $tmpVarName = Code::varName($newTransformerExpression, 'transformer');

        return "($tmpVarName = $newTransformerExpression)->getTransformer(\$this->registry)->transformFromHttp($tmpVarName, $previousExpression)";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $newTransformerExpression = Code::newExpression($transformer);
        $tmpVarName = Code::varName($newTransformerExpression, 'transformer');

        return "($tmpVarName = $newTransformerExpression)->getTransformer(\$this->registry)->transformToHttp($tmpVarName, $previousExpression)";
    }
}
