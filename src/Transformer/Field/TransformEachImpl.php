<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Code;

use function array_reverse;

/**
 * Transformer implementation for {@see TransformEach}
 * This class must be instantiated by the {@see TransformEach::getTransformer()} method for allowing the injection of transformer registry
 *
 * @internal
 * @implements ConfigurableFieldTransformerInterface<TransformEach>
 * @implements FieldTransformerGeneratorInterface<TransformEach>
 */
final class TransformEachImpl implements ConfigurableFieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private RegistryInterface $registry,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param TransformEach $configuration
     * @return mixed[]|null
     */
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $transformers = $configuration->transformers;
        $transformed = [];

        foreach ((array) $value as $key => $item) {
            foreach ($transformers as $transformer) {
                $item = $transformer instanceof DelegatedFieldTransformerInterface
                    ? $transformer->getTransformer($this->registry)->transformFromHttp($transformer, $item)
                    : $transformer->transformFromHttp($item)
                ;
            }

            $transformed[$key] = $item;
        }

        return $transformed;
    }

    /**
     * {@inheritdoc}
     *
     * @param TransformEach $configuration
     * @return mixed[]|null
     */
    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $transformers = array_reverse($configuration->transformers);
        $transformed = [];

        foreach ((array) $value as $key => $item) {
            foreach ($transformers as $transformer) {
                $item = $transformer instanceof DelegatedFieldTransformerInterface
                    ? $transformer->getTransformer($this->registry)->transformToHttp($transformer, $item)
                    : $transformer->transformToHttp($item)
                ;
            }

            $transformed[$key] = $item;
        }

        return $transformed;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);
        $expression = '$item';

        foreach ($transformer->transformers as $transformer) {
            $expression = $generator->generateTransformFromHttp($transformer, $expression);
        }

        return "({$varName} = {$previousExpression}) === null ? null : \array_map(fn (\$item) => {$expression}, (array) {$varName})";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $varName = Code::varName($previousExpression);
        $expression = '$item';

        foreach (array_reverse($transformer->transformers) as $transformer) {
            $expression = $generator->generateTransformToHttp($transformer, $expression);
        }

        return "({$varName} = {$previousExpression}) === null ? null : \array_map(fn (\$item) => {$expression}, (array) {$varName})";
    }
}
