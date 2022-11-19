<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use ReflectionClass;

/**
 * Transformer generator used by default, when there is no available generator for the given transformer
 *
 * This generator will simply inline transformer instantiation, and call corresponding transformation method.
 * To instantiate transformer, promoted property will be used.
 *
 * Generated code example:
 * `(new MyTransformer(foo: "bar"))->transformFormHttp($data['foo'] ?? null)`
 */
final class GenericFieldTransformerGenerator implements FieldTransformerGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(FieldTransformerInterface $transformer, string $previousExpression): string
    {
        $newTransformerExpression = 'new \\'.get_class($transformer).'(';
        $reflection = new ReflectionClass($transformer);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPromoted()) {
                $newTransformerExpression .= $property->name . ': ' . var_export($property->getValue($transformer), true) . ', ';
            }
        }

        $newTransformerExpression .= ')';

        return "($newTransformerExpression)->transformFromHttp($previousExpression)";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(FieldTransformerInterface $transformer, string $previousExpression): string
    {
        // @todo refactor
        $newTransformerExpression = 'new \\'.get_class($transformer).'(';
        $reflection = new ReflectionClass($transformer);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPromoted()) {
                $newTransformerExpression .= $property->name . ': ' . var_export($property->getValue($transformer), true) . ', ';
            }
        }

        $newTransformerExpression .= ')';

        return "($newTransformerExpression)->transformToHttp($previousExpression)";
    }
}
