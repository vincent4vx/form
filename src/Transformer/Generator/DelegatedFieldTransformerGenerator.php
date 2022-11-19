<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use ReflectionClass;

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
 */
final class DelegatedFieldTransformerGenerator
{
    /**
     * Generate the transformFromHttp inlined code
     *
     * @param DelegatedFieldTransformerInterface $transformer Transformer instance to compile
     * @param string $previousExpression Expression of the previous transformer call, or HTTP field value
     *
     * @return string Generated PHP expression
     */
    public function generateTransformFromHttp(DelegatedFieldTransformerInterface $transformer, string $previousExpression): string
    {
        $newTransformerExpression = $this->generateInstantiation($transformer);
        $tmpVarName = '$__transformer_' . md5($newTransformerExpression);

        return "($tmpVarName = $newTransformerExpression)->getTransformer(\$this->registry)->transformFromHttp($tmpVarName, $previousExpression)";
    }

    /**
     * Generate the transformToHttp inlined code
     *
     * @param DelegatedFieldTransformerInterface $transformer Transformer instance to compile
     * @param string $previousExpression Expression of the previous transformer call, or DTO property value
     *
     * @return string Generated PHP expression
     */
    public function generateTransformToHttp(DelegatedFieldTransformerInterface $transformer, string $previousExpression): string
    {
        $newTransformerExpression = $this->generateInstantiation($transformer);
        $tmpVarName = '$__transformer_' . md5($newTransformerExpression);

        return "($tmpVarName = $newTransformerExpression)->getTransformer(\$this->registry)->transformToHttp($tmpVarName, $previousExpression)";
    }

    private function generateInstantiation(DelegatedFieldTransformerInterface $transformer): string
    {
        $newTransformerExpression = 'new \\'.get_class($transformer).'(';
        $reflection = new ReflectionClass($transformer);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPromoted()) {
                $newTransformerExpression .= $property->name . ': ' . var_export($property->getValue($transformer), true) . ', ';
            }
        }

        $newTransformerExpression .= ')';

        return $newTransformerExpression;
    }
}
